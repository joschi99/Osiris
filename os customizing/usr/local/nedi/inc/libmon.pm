=pod

=head1 LIBRARY
libmon.pm

Functions for monitoring

=head2 AUTHORS

Remo Rickli & NeDi Community

=cut

package mon;

use warnings;
use Time::HiRes;
use Net::Ping;
#use Net::Ping::External;

=head2 FUNCTION InitMon()

Read monitoring targets and users

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub InitMon{

	%main::srcna= ();
	%main::mon  = ();
	%main::usr  = ();

	my $nt = 0;
	$nt  = &db::ReadMon('dev');
	$nt += &db::ReadMon('node');

	&db::ReadUser("groups & 8 = 8 AND (phone != '' OR email != '')");				# Read users for Mail alerts (Pg requires = 8)

	return $nt;
}

=head2 FUNCTION GetUptime()

Gets uptime via SNMP

B<Options> IP address, SNMP version and community

B<Globals> -

B<Returns> array with (latency, uptime) or (0,0) upon timeout

=cut
sub GetUptime{

	my ($ip, $ver, $comm) = @_;

	my $r;

	my $uptimeO = '1.3.6.1.2.1.1.3.0';
	#TODO migrate to 64bit counter my $uptimeO = '1.3.6.1.6.3.10.2.1.3';				# tx Juergen

	my ($session, $err) = &snmp::Connect($ip,$ver,$comm);
	my $start = Time::HiRes::time;
	if(defined $session){
		$r   = $session->get_request($uptimeO);
		$err = $session->error;
		$session->close;
	}

	if($err){
		&misc::Prt("ERR :$err\n");
		return -1,0;
	}else{
		my $lat = int(1000 * (Time::HiRes::time - $start) );
		&misc::Prt("SNMP:Latency=${lat}ms Uptime=$r->{$uptimeO}s\n");
		return $lat, $r->{$uptimeO};
	}
}

=head2 FUNCTION PingService()

Pings a tcp service.

B<Options> IP address, protocoll and name of service

B<Globals> -

B<Returns> latency or nothing upon timeout

=cut
sub PingService{

	my ($ip, $proto, $srv, $tout) = @_;

	if( $proto and $proto eq 'icmp' ){
		my $c = ($srv)?$srv:1;
		my $w = ( "$^O" eq 'openbsd' )?"-w $c":"-W $c";
		my $r = `ping -c1 $w $ip`;
		if( $r =~ /min\/avg\/max\/[\w-]+ = (\d+(?:\.\d+)?)\/(\d+(?:\.\d+)?)\/.*/ ){		# Based on Jörg's suggestion
			misc::Prt("PING:icmp average latency=$2ms\n");
			return int($2+0.5);
		}else{
			misc::Prt("PING:icmp fail\n");
			return -1;
		}
	}else{
		$tout = ($tout)?$tout:$misc::timeout;
		my $p = Net::Ping->new($proto);
		$p->hires();
		misc::Prt("PING:");
		if( $proto ){
			$srv = "microsoft-ds" if $srv eq "cifs";
			$p->tcp_service_check(1);
			$p->{port_num} = getservbyname($srv, $proto);
			misc::Prt("$ip proto=$proto srv=$srv ");
		}else{
			misc::Prt("$ip tcp echo ");
		}
		(my $ret, my $latency, my $rip) = $p->ping($ip, $tout);
		$p->close();

		if($ret){
			my $lat = int($latency * 1000);
			misc::Prt("latency=${lat}ms $ret\n");
			return $lat;
		}else{
			misc::Prt("fail!\n");
			return -1;
		}
	}
}

=head2 FUNCTION AlertFlush()

Sends Mails and SMS. If there are no queued mails, the SMTP connection won't be established. Look at commented lines to adjust SMS part...

B<Options> subject for mails, #mails queued

B<Globals> -

B<Returns> -
=cut

sub AlertFlush{

	my ($sub,$mq) = @_;

	use Net::SMTP;

	my $err = 0;
	my $nm  = 0;
	my $ns  = 0;
	
	if($mq){
		my $smtp = Net::SMTP->new($misc::smtpserver, Timeout => $misc::timeout * 3) || ($err = 1);
		if($err){
			&misc::Prt("ERR :Connecting to SMTP server $misc::smtpserver\n");
		}else{
			foreach my $u ( keys %main::usr ){
				if(@{$main::usr{$u}{mail}}){
					&misc::Prt("MAIL:$u/$main::usr{$u}{ml}\n");
					$smtp->mail($misc::mailfrom) || &ErrSMTP($smtp,"From");
					$smtp->to($main::usr{$u}{ml}) || &ErrSMTP($smtp,"To");
					$smtp->data();
					$smtp->datasend("To: $main::usr{$u}{ml}\n");
					$smtp->datasend("From: $misc::mailfrom\n");
					$smtp->datasend("Subject: ".((@{$main::usr{$u}{mail}} > 1)?@{$main::usr{$u}{mail}}." ${sub}s":$sub)."\n");
					#$smtp->datasend("Date: ".localtime($main::now)."\n");		# Format invalid for some
					#$smtp->datasend("MIME-Version: 1.0\n"); 			# Some need it, Exchange doesn't?
					$smtp->datasend("\n");
					$smtp->datasend("Hello $u\n");
					$smtp->datasend("\n");
					my $ln = 0;
					foreach my $l (@{$main::usr{$u}{mail}}){
						$ln++;
						$smtp->datasend("$ln) $l\n");
					}
					$smtp->datasend("\n");

					if($misc::mailfoot){
						foreach my $l (split /\\n/,$misc::mailfoot){
							$smtp->datasend("$l\n");
						}
					}
					$smtp->dataend() || &ErrSMTP($smtp,"End");

					@{$main::usr{$u}{mail}} = ();
					$nm++;
				}
			}
			$smtp->quit;
		}
	}

	foreach my $u ( keys %main::usr ){
		if($main::usr{$u}{sms}){
			if( exists $misc::sms{'spool'} ){						#1. Spooling to smsd
				if (!-e "/var/spool/sms/checked/$u"){					# Skip if previous SMS hasn't been sent, to avoid smsd crash! TODO use timestamp instead?
					&misc::Prt("SMS :$u/$main::usr{$u}{ph}\n");
					$ns++ if open(SMS, ">$misc::sms{'spool'}/$u");			# User is filename to avoid flooding
					print SMS "To:$main::usr{$u}{ph}\n\n$main::usr{$u}{sms}\n";
					close(SMS);
				}else{
					&misc::Prt("ERR :SMS skipped since previous message for $u is still being sent!\n");
				}
			}
			if( exists $misc::sms{'gammu'} ){						#2. Calling gammu server
				$ns++ if !system "gammu-smsd-inject TEXT $main::usr{$u}{ph} -text \"$main::usr{$u}{sms}\" >/dev/null";
			}
			if( exists $misc::sms{'smtp'} ){						#3.SMTP based SMS gateway
				my $smtp = Net::SMTP->new($misc::sms{'smtp'}, Timeout => $misc::timeout) || ($err = 1);
				if($err){
					&misc::Prt("ERR :Connecting to SMS gateway $misc::sms{'smtp'} via SMTP\n");
				}else{
					$smtp->mail($misc::mailfrom) || &ErrSMTP($smtp,"From");
					$smtp->to($main::usr{$u}{ph}) || &ErrSMTP($smtp,"To");
					$smtp->data();
					$smtp->datasend("To:$main::usr{$u}{ph}\n");
					$smtp->datasend("From: $misc::mailfrom\n");
					$smtp->datasend("Subject: $sub\n");
					#$smtp->datasend("MIME-Version: 1.0\n"); 			# Some need it, Exchange doesn't?
					$smtp->datasend("\n");
					$smtp->datasend("$main::usr{$u}{sms}\n");
					$smtp->dataend() || &ErrSMTP($smtp,"End");
					$smtp->quit;
				}
			}
			if( exists $misc::sms{'cmd'} ){							#2. Calling custom binary
				$ns++ if !system "$misc::sms{'cmd'} $main::usr{$u}{ph} \"$main::usr{$u}{sms}\" >/dev/null";
			}
			$main::usr{$u}{sms} = '';
		}
	}
	&misc::Prt("ALRT:$nm mails and $ns SMS sent with $mq events\n");
	
	return $nm;
}

=head2 FUNCTION ErrSMTP()

Handle SMTP errors

B<Options> SMTP code, Step of delivery

B<Globals> -

B<Returns> -
=cut

sub ErrSMTP{

	my ($smtp,$step) = @_;

	my $m = &misc::Strip(($smtp->message)[-1]);							# Avoid uninit with Strip()
	my $c = $smtp->code;
	chomp $m;
	&misc::Prt("ERR :$c, $m\n");
}


=head2 FUNCTION Elevate()

Returns elevation according to the notify string in nedi.conf, but if
min-elevation is higher this is returned instead.

Bits

1 = Create event

2 = Send mail

4 = Send sms

B<Options> mode,min-elevation

B<Globals> -

B<Returns> elevation
=cut

sub Elevate{

	my ($mode,$min,$tgt) = @_;

	my $nfy = ($tgt and exists $main::mon{$tgt} and $main::mon{$tgt}{no} )?$main::mon{$tgt}{no}:$misc::notify;

	my $elevate = 0;
	if($mode =~ /^[0-9]+$/){
			$elevate = $mode;
	}elsif($mode =~ /^[A-Z]$/){									# Only uppercase mode can elevate above 1
		if($nfy =~ /$mode/){
			$elevate = 3;
		}elsif($nfy =~ /$mode/i){
			$elevate = 1;
		} 
	}elsif($mode =~ /^[a-z]$/){									# Lowercase mode can still elevate to 1
		if($nfy =~ /$mode/i){
			$elevate = 1;
		}
	}
	&misc::Prt("DBG :Elevate=$elevate Min=$min Mode=$mode Notify=$nfy\n") if $main::opt{'d'};

	return ($elevate > $min)?$elevate:$min;
}

=head2 FUNCTION Event()

Print a message, insert event and queue alert if desired. If the mode argument is a letter, 
the event is elevated according to the notify string in nedi.conf. If mode is a number the
event is elevated accordingly. The monitoring settings for the target determine
final elevation and processing.

B<Options> mode,level,class,notify,target,device,message,sms

B<Globals> -

B<Returns> # of queued mails
=cut

sub Event{

	my ($mode,$level,$class,$tgt,$dv,$msg,$sms) = @_;
	
	my $elevate = &Elevate($mode,0,$tgt);
	if($elevate and $class !~ /moni|^sp/ and exists $main::mon{$tgt}){				# Using alert settings for moni and policy events and never elevate unmonitored sources or events not matching notify
		if($main::mon{$tgt}{ed} and $msg =~ /$main::mon{$tgt}{ed}/){
			$elevate = 0;
			&misc::Prt("DBG :$msg contains /$main::mon{$tgt}{ed}/, discarding\n") if $main::opt{'d'};
		}
		if( $main::mon{$tgt}{el} ){
			if($main::mon{$tgt}{el}%2 ){
				if( $level < $main::mon{$tgt}{el} ){
					$elevate = 0;
					&misc::Prt("DBG :Discard level limit $main::mon{$tgt}{el} > event level $level, discarding\n") if $main::opt{'d'};
				}
			}elsif( $level >= $main::mon{$tgt}{el} ){
				$elevate = 3;
				&misc::Prt("DBG :Forward level limit $main::mon{$tgt}{el} <= $level, forwarding\n") if $main::opt{'d'};
			}
		}
		if($main::mon{$tgt}{ef} and $msg =~ /$main::mon{$tgt}{ef}/){
			$elevate = 3;
			&misc::Prt("DBG :$msg contains /$main::mon{$tgt}{ef}/, forwarding\n") if $main::opt{'d'};
		}
		if($main::mon{$tgt}{em} and $msg =~ /$main::mon{$tgt}{em}/){
			$level = 250;
			&misc::Prt("DBG :$msg contains /$main::mon{$tgt}{em}/, increasing level to 250\n") if $main::opt{'d'};
		}
	}
	&misc::Prt("EVNT:MOD=$mode/$elevate L=$level CL=$class TGT=$tgt MSG=$msg\n");

	if($elevate){
		my $info = $msg;
		$info =~ s/[\r\n]/, /g;
		$info =~ s/\s\s+/ /g;
		my $qinf = $db::dbh->quote($info);
		$info = ((length $qinf > 250)?substr($qinf,0,250)."...'":$qinf);
		&db::Insert('events','level,time,source,class,device,info',"$level,$main::now,".$db::dbh->quote($tgt).",'$class',".$db::dbh->quote($dv).",$info" );

		if($elevate > 1){
			my $nm = 0;
			my $ns = 0;
			foreach my $u ( keys %main::usr ){

				my $viewdev = ($main::usr{$u}{vd})?&db::Select('devices','','device',"device=".$db::dbh->quote($dv)." AND $main::usr{$u}{vd}"):$dv;
				if(defined $viewdev and $viewdev eq $dv){				# Send mail only to those who can see the associated device

					if($main::usr{$u}{ml} and $msg and $elevate & 2){		# Usr has email, there's a msg and elevation bit 2 is set -> queue mail
						push (@{$main::usr{$u}{mail}}, "$tgt\t$msg");
						&misc::Prt("MLQ :$u $tgt $msg\n");
						$nm++;
					}

					if($main::usr{$u}{ph} and $sms and $elevate & 4){		# Usr has phone, there's a short message and elevation bit 4 is set -> queue sms
						$main::usr{$u}{sms} .= "$tgt:$sms ";
						&misc::Prt("SMSQ:$u $tgt:$sms\n");
						$ns++;
					}
				}
			}
			&misc::Prt("EFWD:$nm Mail and $ns SMS queued\n");
			return $nm;
		}
	}

	return 0;
}

=head2 FUNCTION SshStat()

Check SSH or telnet

B<Options> ip

B<Globals> -

B<Returns> Server string, status
=cut

sub CliStat{

	my ($dst, $port) = @_;
	my $err = $os = $srv = '';

	$sok = IO::Socket::INET->new(PeerAddr => $dst,PeerPort => $port,Proto => "tcp",Type => misc::SOCK_STREAM,Timeout => 1) or $err = $@;

	if( $err ){
		misc::Prt("CLI :$dst:$port $err\n");
		return '','','';
	}else{
		$res = <$sok>;
		close($sok);
		if( $res ){
			chomp $res;
			misc::Prt("CLI :$dst:$port returned $res\n");
			if( $port == 25 ){
				@sf = split(/[\s\/]/,$res);
				$srv = $sf[3].$sf[4];
			}else{
				if( $res =~ / .*/ ){
					$os  = $res;
					$os  =~ s/(.*) (\w*)(-.*)?/$2/;
				}
				$srv = $res;
				$srv =~ s/(.*) (\w*)(-.*)?/$1/;
			}
			return $res,$srv,$os;
		}else{
			misc::Prt("CLI :$dst:$port returned empty string\n");
			return '-','','';
		}
	}
}

=head2 FUNCTION NbtStat()

Check Netbios

B<Options> ip,port

B<Globals> -

B<Returns> workgroup, user, error
=cut

sub NbtStat{

	my ($dst, $port) = @_;

	my $nbts = pack(C50,129,98,00,00,00,01,00,00,00,00,00,00,32,67,75,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,00,00,33,00,01);
	my $wsnam = $data = $rin = '';
	my $wsgrp = '-';
	my $sockaddr = 'S n a4 x8';										# magic
	my $family   = 2;											# AF_INET (system dependent !)
	my $socktype = 1;											# SOCK_DGRA (system dependent !)

	($name, $aliases, $proto) = getprotobyname('udp');
	($name, $aliases, $type, $len, $peer_addr) = gethostbyname($dst);

	$me   = pack($sockaddr, $family, 0);
	$peer = pack($sockaddr, $family, $port, $peer_addr);
	socket(S, misc::PF_INET, misc::SOCK_DGRAM, $proto) || return '','',"Unable to create socket: $!";
	bind(S, $me) || return '','',"Unable to bind socket: $!";
	send(S, $nbts, 0, $peer) || return '','',"Couldn't send $!";

	# receive udp until timeout
	vec($rin, fileno(S), 1) = 1;
	while (select($rin, undef, undef, 0.5)) {
		recv(S, $data, 1024, 0) || die "recv: $!";
	}
	close(S);

	if ($data =~ /AAAAAAAAAA/){
		$num = unpack("C",substr($data,56,1));							# Get number of names
		$out = substr($data,57);								# get rid of WINS header

		for ($i = 0; $i < $num;$i++){
			$nam = substr($out,18*$i,15);
			$nam =~ s/ +//g;
			my $id = unpack("C",substr($out,18*$i+15,1));
			my $fl = unpack("C",substr($out,18*$i+16,1));
			$fl = ($fl < 128)?'UNIQUE':'GROUP';
			if( $id eq '0' ){
				$wsgrp = $nam;
			}elsif( $id eq '32' ){
				$wsnam = $nam if $fl eq 'UNIQUE' and !$wsnam;
			}
			misc::Prt( sprintf("NBT :%s	(%02X)	%s\n",$nam,$id,$fl) );
		}
		@mac = unpack("C6",substr($out,18*$i,6));
		misc::Prt( sprintf("NBT :MAC Address %02X-%02X-%02X-%02X-%02X-%02X\n",@mac) );
		return $wsgrp,$wsnam,'';
	}else{
		misc::Prt("NBT :$dst no response\n");
		return '','','no response';
	}
}

1;
