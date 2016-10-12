=pod

=head1 LIBRARY
libmisc.pm

Miscellaneous functions

=head2 AUTHORS

Remo Rickli & NeDi Community

=cut

package misc;
use warnings;

use RRDs;
use IO::Socket;
use Socket6;

use vars qw($netfilter $webdev $nosnmpdev $border $ignoredesc $ignoreconf $getfwd $timeout $retry $ncmd);
use vars qw($nedipath $backend $dbname $dbuser $dbpass $dbhost $uselogin $usessh $sms $pol);
use vars qw($rrdcmd $rrdstep $rrdsize $nagpipe $snmpwrite $redbuild $guiauth $locsep $asset);
use vars qw($arpwatch $ignoredvlans $ignoredmacs $useivl $retire $revive $arppoison $macflood $seedlist);
use vars qw($notify $norep $latw $cpua $mema $tmpa $trfa $brca $poew $supa $pause $smtpserver $mailfrom $mailfoot);
use vars qw(%comm3 %login %map %usepoe %skippol %doip %seedini %sysobj %ifmac %ifip %useip %dlc);
use vars qw(%oui %arp  %arp6 %arpc %arpn %portprop %portdes %vlid %pol %act);
use vars qw(@todo @comms @seeds @dbseeds @users @curcfg);

our @donenam = @doneid = @doneip = @failid = @failip = ();
our $ipchg   = $ifchg = $mq = 0;
our $ouidev  = '';

=head2 FUNCTION ReadConf()

Searches for nedi.conf in nedi folder first then fall back to /etc. Parse
it if found or die if not.

locsep is set to a space if commented.

B<Options> -

B<Globals> various misc:: varables

B<Returns> dies on missing nedi.conf

=cut
sub ReadConf{

	my $nconf = ($_[0])?$_[0]:"$main::p/nedi.conf";

	$ignoredvlans = $ignoredmacs = $useivl = $border = $nosnmpdev = $ignoredesc = $ignoreconf = $usessh = $asset = "isch nid gsetzt!";
	$retry    = 1;
	$locsep   = " ";
	$rrdsize  = 1000;
	$macflood = 1000;

	if($main::opt{'U'}){
		$nconf = $main::opt{'U'}
	}
	my @conf = ();
	if($nconf eq '-'){
		@conf = <STDIN>
	}else{
		if(-e "$nconf"){
			open  ("CONF", $nconf);
		}elsif(-e "/etc/nedi.conf"){
			open  ("CONF", "/etc/nedi.conf");
		}else{
			die "Can't find $nconf: $!\n";
		}
		@conf = <CONF>;
		close("CONF");
	}

	foreach my $l (@conf){
		if($l !~ /^[#;]|^(\s)*$/){
			$l =~ s/[\r\n]//g;
			my @v  = split(/\s+/,$l);
			if($v[0] eq "comm"){
				push (@comms,$v[1]);
				$comm3{$v[1]}{aprot} = $v[2];
				$comm3{$v[1]}{apass} = $v[3];
				$comm3{$v[1]}{pprot} = $v[4];
				$comm3{$v[1]}{ppass} = $v[5];}
			elsif($v[0] eq "usr"){
				push (@users,$v[1]);
				$login{$v[1]}{pw} = $v[2];
				$login{$v[1]}{en} = $v[3] if $v[3];}
			elsif($v[0] eq "usrsec"){
				push (@users,$v[1]);
				$login{$v[1]}{pw} = XORpass( pack "H*",$v[2] );
				$login{$v[1]}{en} = XORpass( pack "H*",$v[3] ) if $v[3];}
			elsif($v[0] eq "useip"){$useip{$v[1]} = $v[2];}
			elsif($v[0] eq "uselogin"){$uselogin = $v[1]}
			elsif($v[0] eq "ignoreconf"){$ignoreconf = join ' ', splice @v,1}
			elsif($v[0] eq "snmpwrite"){$snmpwrite = $v[1]}
			elsif($v[0] eq "usessh"){$usessh = $v[1]}
			elsif($v[0] eq "skippol"){$skippol{$v[1]} = (defined $v[2])?$v[2]:''}		# Avoid undef...
			elsif($v[0] eq "usepoe"){$usepoe{$v[1]} = $v[2];}

			elsif($v[0] eq "mapip"){$map{$v[1]}{ip} = $v[2]}
			elsif($v[0] eq "maptp"){$map{$v[1]}{cp} = $v[2]}
			elsif($v[0] eq "mapsn"){$map{$v[1]}{sn} = $v[2]}
			elsif($v[0] eq "mapna"){$map{$v[1]}{na} = join ' ', splice @v,2}
			elsif($v[0] eq "maplo"){$map{$v[1]}{lo} = join ' ', splice @v,2}
			elsif($v[0] eq "mapco"){$map{$v[1]}{co} = join ' ', splice @v,2}
			elsif($v[0] eq "mapgr"){$map{$v[1]}{gr} = join ' ', splice @v,2}
			elsif($v[0] eq "mapn2l"){
				$map{$v[1]}{nlm} = $v[2];
				$map{$v[1]}{nlr} = $v[3];
			}
			elsif($v[0] eq "mapl2l"){
				$map{$v[1]}{llm} = $v[2];
				$map{$v[1]}{llr} = $v[3];
			}
			elsif($v[0] eq "nosnmpdev"){$nosnmpdev = $v[1]}
			elsif($v[0] eq "webdev"){$webdev = $v[1]}
			elsif($v[0] eq "netfilter"){$netfilter = $v[1]}
			elsif($v[0] eq "border"){$border = join ' ', splice @v,1}
			elsif($v[0] eq "ouidev"){$ouidev = join ' ', splice @v,1}
			elsif($v[0] eq "ignoredesc"){$ignoredesc = $v[1]}
			elsif($v[0] eq "asset"){$asset = $v[1]}

			elsif($v[0] eq "backend"){$backend = $v[1]}
			elsif($v[0] eq "dbname"){$dbname = $v[1]}
			elsif($v[0] eq "dbuser"){$dbuser = $v[1]}
			elsif($v[0] eq "dbpass"){$dbpass = (defined $v[1])?$v[1]:''}			# based on dirtyal's suggestion
			elsif($v[0] eq "dbhost"){$dbhost = $v[1]}

			elsif($v[0] eq "ignoredvlans"){$ignoredvlans = $v[1]}
			elsif($v[0] eq "ignoredmacs"){$ignoredmacs = $v[1]}
			elsif($v[0] eq "useivl"){$useivl = $v[1]}
			elsif($v[0] eq "getfwd"){$getfwd = $v[1]}
			elsif($v[0] eq "retire"){$retire = $main::now - $v[1] * 86400;$revive = $main::now - $v[1] * 43200;}
			elsif($v[0] eq "timeout"){$timeout = $v[1];$retry = (defined $v[2])?$v[2]:1}
			elsif($v[0] eq "arpwatch"){$arpwatch = $v[1]}
			elsif($v[0] eq "arppoison"){$arppoison = $v[1]}
			elsif($v[0] eq "macflood"){$macflood = $v[1]}

			elsif($v[0] eq "rrdstep"){$rrdstep = $v[1]}
			elsif($v[0] eq "rrdsize"){$rrdsize = $v[1]}
			elsif($v[0] eq "rrdcmd"){$rrdcmd = $v[1]}
			elsif($v[0] eq "nagpipe"){$nagpipe = $v[1]}

			elsif($v[0] eq "notify"){$notify = $v[1]}
			elsif($v[0] eq "noreply"){$norep = $v[1]}
			elsif($v[0] eq "latency-warn"){$latw = $v[1]}
			elsif($v[0] eq "cpu-alert"){$cpua = $v[1]}
			elsif($v[0] eq "mem-alert"){$mema = $v[1]}
			elsif($v[0] eq "temp-alert"){$tmpa = $v[1]}
			elsif($v[0] eq "traf-alert"){$trfa = $v[1]}
			elsif($v[0] eq "bcast-alert"){$brca = $v[1]}
			elsif($v[0] eq "poe-warn"){$poew = $v[1]}
			elsif($v[0] eq "supply-alert"){$supa = $v[1]}

			elsif($v[0] eq "pause"){$pause = $v[1]}
			elsif($v[0] eq "smtpserver"){$smtpserver = $v[1]}
			elsif($v[0] eq "mailfrom"){$mailfrom = $v[1]}
			elsif($v[0] eq "mailfooter"){$mailfoot = join ' ', splice @v,1}
			elsif($v[0] eq "sms"){$sms{$v[1]} = $v[2]}
			elsif($v[0] eq "guiauth"){$guiauth = $v[1]}
			elsif($v[0] eq "locsep"){$locsep = $v[1]}
			elsif($v[0] eq "redbuild"){$redbuild = $v[1]}

			elsif($v[0] eq "nedipath"){
				$nedipath = $v[1];
				if($main::p !~ /^\//){
					&Prt("RCFG:$0 path is relative\n");
					$nedipath = $main::p;
				}else{
					if($nedipath ne $main::p){die "Please configure nedipath!\n";}
				}
			}
		}
	}
}

=head2 FUNCTION XORpass()

XOR cleartext passwords with passphrase

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub XORpass {

	my $k = 'change for more security';
	my $r = '';
	for my $ch (split //, $_[0]){
		my $i = chop $k;
		$r .= chr(ord($ch) ^ ord($i));
		$k = $i . $k;
	}
	return $r;
}

=head2 FUNCTION ReadSysobj()

Reads Sysobj definition file

B<Options> -

B<Globals> misc::sysobj

B<Returns> -

=cut
sub ReadSysobj{

	my ($so) = @_;

	unless( exists $sysobj{$so} ){									# Load .def if not done already
		if(-e "$main::p/sysobj/$so.def"){
			open  ("DEF", "$main::p/sysobj/$so.def");
			&Prt("SOBJ:Reading $so.def ");
		}else{
			open  ("DEF","$main::p/sysobj/other.def");
			&Prt("SOBJ:$so.def not found, using other.def ");
		}
		my @def = <DEF>;
		chomp @def;
		close("DEF");
		$sysobj{$so}{ty} = $so;
		$sysobj{$so}{hc} = $sysobj{$so}{mv} = $sysobj{$so}{ib} = 0;
		$sysobj{$so}{pm} = '-';
		$sysobj{$so}{st} = '';
		$sysobj{$so}{en} = '';
		$sysobj{$so}{px} = '';
		$sysobj{$so}{ma} = '';
		$sysobj{$so}{mk} = '';
		$sysobj{$so}{ml} = '';
		$sysobj{$so}{cul}= '';
		$sysobj{$so}{vrf}= '';

		foreach my $l (@def){
			if($l !~ /^[#;]|^\s*$/){
				$l =~ s/[\r\n]|\s+$//g;							# Chomp doesn't remove \r and trailing spaces
				my @v  = split(/\t+/,$l);
				if(!defined $v[1]){$v[1] = ""}
				if($v[0] eq "Type")		{$sysobj{$so}{ty} = $v[1]}
				elsif($v[0] eq "OS")		{$sysobj{$so}{os} = $v[1]}
				elsif($v[0] eq "Icon")		{$sysobj{$so}{ic} = $v[1]}
				elsif($v[0] eq "Size")		{$sysobj{$so}{sz} = $v[1]}
				elsif($v[0] eq "SNMPv"){
					$sysobj{$so}{rv} = substr($v[1],0,1);
					if(substr($v[1],1,2) eq 'HC'){
						$sysobj{$so}{hc} = 128;					# Pure Highspeed 64bit counters
					}elsif(substr($v[1],1,2) eq 'MC'){
						$sysobj{$so}{hc} = 192;					# Merge Counters
					}else{
						$sysobj{$so}{hc} = 64;					# 32bit counters only
					}
				}
				elsif($v[0] eq "Serial")	{$sysobj{$so}{sn} = $v[1]}
				elsif($v[0] eq "Bimage")	{$sysobj{$so}{bi} = $v[1]}
				elsif($v[0] eq "Sysdes")	{$sysobj{$so}{de} = $v[1]}
				elsif($v[0] eq "Bridge")	{$sysobj{$so}{bf} = $v[1]}
				elsif($v[0] eq "ArpND")		{$sysobj{$so}{ar} = $v[1]}
				elsif($v[0] eq "Dispro")	{$sysobj{$so}{dp} = $v[1]}
				elsif($v[0] eq "Typoid")	{$sysobj{$so}{to} = $v[1]}		# tx vtur

				elsif($v[0] eq "VLnams")	{$sysobj{$so}{vn} = $v[1]}
				elsif($v[0] eq "VLnamx")	{$sysobj{$so}{vl} = $v[1]}
				elsif($v[0] eq "Group")		{$sysobj{$so}{dg} = $v[1]}
				elsif($v[0] eq "Mode")		{$sysobj{$so}{dm} = $v[1]}
				elsif($v[0] eq "CfgChg")	{$sysobj{$so}{cc} = $v[1]}
				elsif($v[0] eq "CfgWrt")	{$sysobj{$so}{cw} = $v[1]}

				elsif($v[0] eq "StartX")	{$sysobj{$so}{st} = $v[1]}
				elsif($v[0] eq "EndX")		{$sysobj{$so}{en} = $v[1]}
				elsif($v[0] eq "IFname")	{$sysobj{$so}{in} = $v[1]}
				elsif($v[0] eq "IFaddr")	{
					$sysobj{$so}{ia} = $v[1];
					$sysobj{$so}{vrf}= $v[2] if $v[2];
				}
				elsif($v[0] eq "IFalia")	{$sysobj{$so}{al} = $v[1]}
				elsif($v[0] eq "IFalix")	{$sysobj{$so}{ax} = $v[1]}
				elsif($v[0] eq "IFdupl")	{$sysobj{$so}{du} = $v[1]}
				elsif($v[0] eq "IFduix")	{$sysobj{$so}{dx} = $v[1]}
				elsif($v[0] eq "Halfdp")	{$sysobj{$so}{hd} = $v[1]}
				elsif($v[0] eq "Fulldp")	{$sysobj{$so}{fd} = $v[1]}
				elsif($v[0] eq "InBcast")	{$sysobj{$so}{ib} = $v[1]}
				elsif($v[0] eq "InDisc")	{$sysobj{$so}{id} = $v[1]}
				elsif($v[0] eq "OutDisc")	{$sysobj{$so}{od} = $v[1]}
				elsif($v[0] eq "IFvlan")	{$sysobj{$so}{vi} = $v[1]}
				elsif($v[0] eq "IFvlix")	{$sysobj{$so}{vx} = $v[1]}
				elsif($v[0] eq "IFpowr")	{
					$sysobj{$so}{pw} = $v[1];
					$sysobj{$so}{pm} = $v[2] if $v[2];
				}
				elsif($v[0] eq "IFpwix")	{$sysobj{$so}{px} = $v[1]}

				elsif($v[0] eq "Modesc")	{$sysobj{$so}{md} = $v[1]}
				elsif($v[0] eq "Moclas")	{$sysobj{$so}{mc} = $v[1]}
				elsif($v[0] eq "Movalu")	{$sysobj{$so}{mv} = $v[1]}
				elsif($v[0] eq "Mostep")	{$sysobj{$so}{mp} = $v[1]}
				elsif($v[0] eq "Moslot")	{$sysobj{$so}{mt} = $v[1]}
				elsif($v[0] eq "Mostat")	{$sysobj{$so}{ma} = $v[1]}
				elsif($v[0] eq "Mostok")	{$sysobj{$so}{mk} = $v[1]}
				elsif($v[0] eq "Modhw")		{$sysobj{$so}{mh} = $v[1]}
				elsif($v[0] eq "Modsw")		{$sysobj{$so}{ms} = $v[1]}
				elsif($v[0] eq "Modfw")		{$sysobj{$so}{mf} = $v[1]}
				elsif($v[0] eq "Modser")	{$sysobj{$so}{mn} = $v[1]}
				elsif($v[0] eq "Momodl")	{$sysobj{$so}{mm} = $v[1]}
				elsif($v[0] eq "Modloc")	{$sysobj{$so}{ml} = $v[1]}


				elsif($v[0] eq "CPUutl")	{
					$sysobj{$so}{cpu} = $v[1];
					$sysobj{$so}{cmu} = ($v[2])?$v[2]:1;
				}
				elsif($v[0] eq "MemCPU")	{
					$sysobj{$so}{mem} = $v[1];
					$sysobj{$so}{mmu} = ($v[2])?$v[2]:1;
				}
				elsif($v[0] eq "Temp")		{
					$sysobj{$so}{tmp} = $v[1];
					$sysobj{$so}{tmu} = ($v[2])?$v[2]:1;
				}
				elsif($v[0] eq "Custom" and $v[2]){$sysobj{$so}{cuv} = $v[2];$sysobj{$so}{cul} = $v[1]}
			}
		}
		&Prt("($sysobj{$so}{ty})\n");
	}
}

=head2 FUNCTION ReadOUIs()

Load NIC vendor database (extracts vendor information from the oui.txt and iab.txt files)
download to ./inc from:

L<http://standards.ieee.org/regauth/oui/index.shtml>

B<Options> -

B<Globals> misc::oui

B<Returns> -

=cut
sub ReadOUIs{

	open  ("OUI", "$main::p/inc/oui.txt" ) or die "no oui.txt in $main::p/inc!";			# Read OUI's first
	my @ouitxt = <OUI>;
	close("OUI");
	foreach my $l (@ouitxt){
		if( $l =~ /\(base 16\)/){
			$l =~ s/^\s*|[\r\n]$//g;
			my @m = split(/\s\s+/,$l);
			if(defined $m[2]){
				$oui{lc($m[0])} = substr($m[2],0,32);
			}
		}
	}
	open  ("IAB", "$main::p/inc/iab.txt" ) or die "no iab.txt in $main::p/inc!";			# Now add IAB's (00-50-C2)
	my @iabtxt = <IAB>;
	close("IAB");
	foreach my $l (@iabtxt){
		if( $l =~ /\(base 16\)/){
			$l =~ s/^\s*|[\r\n]$//g;
			my @m = split(/\t+/,$l);
			if(defined $m[2]){
				$m[0] = "0050C2".substr($m[0],0,3);
				$oui{lc($m[0])} = substr($m[2],0,32);
			}
		}
	}
	my $nnic = keys %oui;
	&Prt("OUI :$nnic NIC vendor entries read\n");
}


=head2 FUNCTION GetOui()

Returns OUI vendor.

B<Options> MAC address

B<Globals> -

B<Returns> vendor

=cut
sub GetOui{

	my $vendor = '';

	if($_[0] =~ /^0050C2/i) {
		$vendor = $oui{substr($_[0],0,9)};
	} else {
		$vendor = $oui{substr($_[0],0,6)};
	}
	return ($vendor)?$vendor:'?';
}


=head2 FUNCTION Strip()

Strips unwanted characters from a string. Additionally the return value
for an empty string (e.g. 0) can be specified.

B<Options> string, return

B<Globals> misc::oui

B<Returns> cleaned string

=cut
sub Strip{

	my ($str,$ret) = @_;

	$ret = (defined $ret)?$ret:'';
	if(defined $str and $str ne ''){								# only strip if it's worth it!
		$str =~ s/^\s*|\s*$//g;									# leading/trailing spaces
		$str =~ s/"//g;										# quotes
		$str =~ s/[\x00-\x1F]//g;								# below ASCII
		$str =~ s/[\x7F-\xff]//g;								# above ASCII
		$str =~ s/\s+/ /g;									# excess spaces
		$str = int($str + 0.5) if $str =~ /^\d+(\.\d+)?$/ and $ret =~ /^0$/;			# round to int, if it's float
		return $str;
	}else{
		return $ret;
	}
}


=head2 FUNCTION Shif()

Shorten interface names.

B<Options> IF name

B<Globals> -

B<Returns> shortened IF name

=cut
sub Shif{

	my ($n) = @_;

	if($n){
		$n =~ s/ten(-)?gigabitethernet/Te/i;
		$n =~ s/gigabit[\s]{0,1}ethernet/Gi/i;
		$n =~ s/fast[\s]{0,1}ethernet/Fa/i;
		$n =~ s/^eth(ernet)?/Et/i;								# NXOS uses Eth in CLI, but Ethernet in SNMP...tx sk95, Matthias
		$n =~ s/^Serial/Se/;
		$n =~ s/^Dot11Radio/Do/;
		$n =~ s/^Wireless port\s?/Wp/;								# Former Colubris controllers
		$n =~ s/^[F|G]EC-//;									# Doesn't match telnet CAM table!
		$n =~ s/^Alcatel-Lucent //;								# ALU specific
		$n =~ s/^BayStack (.*?)- //;								# Nortel specific
		$n =~ s/^Vlan/Vl/;									# MSFC2 and Cat6k5 discrepancy!
		$n =~ s/^Vethernet/Veth/;								# Cisco UCS
		$n =~ s/port-channel/Po/i;								# N5K requires this, Tx Matthias
		$n =~ s/(Port\d): .*/$1/g;								# Ruby specific
		$n =~ s/PIX Firewall|pci|motorola|power|switch|network|interface//ig;			# Strip other garbage (removed management for asa)
		$n =~ s/\s+|'//g;									# Strip unwanted characters
		return $n;
	}else{
		return "-";
	}
}

=head2 FUNCTION ProCount()

Process counter with respect to overflow and delta

B<Options> Device, IF index, abs index, delta index, status, value

B<Globals> Interface abs and delta value

B<Returns> -

=cut
sub ProCount{

	my ($dv,$i,$abs,$dlt,$stat,$val) = @_;
	if($stat){
		$main::int{$dv}{$i}{$abs} = 0 unless $main::int{$dv}{$i}{$abs};
		$main::int{$dv}{$i}{$dlt} = 0 unless $main::int{$dv}{$i}{$dlt};
	}else{
		if($main::int{$dv}{$i}{old}){
			my $dval = $val - $main::int{$dv}{$i}{$abs};
			if($dval == abs $dval){
				$main::int{$dv}{$i}{$dlt} = $dval;
			}else{
				&misc::Prt("ERR :$abs overflow, not updating\n",'');
			}
		}else{
			$main::int{$dv}{$i}{$dlt} = 0;
		}
		$main::int{$dv}{$i}{$abs} = $val;
	}
}

=head2 FUNCTION CheckIf()

Check interface against monitoring policy

B<Options> Device, IF name, Skipstring

B<Globals> -

B<Returns> -

=cut
sub CheckIF{

	my ($dv,$i,$skip) = @_;

	return unless $main::int{$dv}{$i}{old};

	my $ele = 0;
	my $lvl = 100;
	my $cla = "if";
	my $iftxt = $main::int{$dv}{$i}{ina};
	$iftxt .= " ($main::int{$dv}{$i}{ali})" if $main::int{$dv}{$i}{ali};
	if($main::int{$dv}{$i}{'lty'}){
		$iftxt .= ' '.$main::int{$dv}{$i}{'lty'};
		$lvl = 150;
		$cla = "ln";
		$ele = &mon::Elevate('L',0,$dv);
	}elsif($main::int{$dv}{$i}{'plt'}){
		$iftxt .= ' '.$main::int{$dv}{$i}{'lty'};
		$lvl = 150;
		$cla = "ln";
		$ele = &mon::Elevate('L',0,$dv);
	}
	if( $main::dev{$dv}{'ls'} > $main::lasdis and $main::int{$dv}{$i}{chg} != $main::now ){		# Avoid > 100% events due to offline dev being rediscovered or IF status change
		my $trfele = &mon::Elevate('T',$ele,$dv);
		my $errele = &mon::Elevate('E',$ele,$dv);
		my $dicele = &mon::Elevate('G',$ele,$dv);
		if($trfele and $main::int{$dv}{$i}{'spd'} and $skip !~ /t/){				# Ignore speed 0 and if traffic is skipped
			my $rioct = int( $main::int{$dv}{$i}{'dio'} / $main::int{$dv}{$i}{'spd'} / $rrdstep * 800 );
			my $rooct = int( $main::int{$dv}{$i}{'doo'} / $main::int{$dv}{$i}{'spd'} / $rrdstep * 800 );
			my $tral = ($main::int{$dv}{$i}{'tra'})?$main::int{$dv}{$i}{'tra'}:$trfa;
			if($tral and $tral < 100 and $rioct > $tral){							# Threshold of 0 means ignore
				$mq += &mon::Event($trfele,200,$cla.'ti',$dv,$dv,"$iftxt (".DecFix($main::int{$dv}{$i}{'spd'}).") has had $rioct% inbound traffic in the last ${rrdstep}s, exceeds alert threshold of ${tral}%!");
			}
			if($tral and $tral < 100 and $rooct > $tral){
				$mq += &mon::Event($trfele,200,$cla.'to',$dv,$dv,"$iftxt (".DecFix($main::int{$dv}{$i}{'spd'}).") has had $rooct% outbound traffic in the last ${rrdstep}s, exceeds alert threshold of ${tral}%!");
			}
			my $bcps = int($main::int{$dv}{$i}{'dib'}/$rrdstep);
			my $bral = ($main::int{$dv}{$i}{'bra'})?$main::int{$dv}{$i}{'bra'}:$brca;
			if( $skip !~ /b/ and $bral and $bcps > $bral){
				$mq += &mon::Event($trfele,200,$cla.'bi',$dv,$dv,"$iftxt has had $bcps inbound broadcasts/s, exceeds alert threshold of ${bral}/s!");
			}
		}
		if($errele and $main::int{$dv}{$i}{typ} != 71 and $skip !~ /e/){			# Ignore Wlan IF
			if($main::int{$dv}{$i}{die} > $rrdstep){
				$mq += &mon::Event($errele,$lvl,$cla.'ei',$dv,$dv,"$iftxt has had $main::int{$dv}{$i}{die} inbound errors in the last ${rrdstep}s!");
			}elsif($main::int{$dv}{$i}{die} > $rrdstep / 60){
				$mq += &mon::Event( ($errele > 1)?1:0,$lvl,$cla.'ei',$dv,$dv,"$iftxt has had some ($main::int{$dv}{$i}{die}) inbound errors in the last ${rrdstep}s");
			}
			if($main::int{$dv}{$i}{doe} > $rrdstep){
				$mq += &mon::Event($errele,$lvl,$cla.'eo',$dv,$dv,"$iftxt has had $main::int{$dv}{$i}{doe} outbound errors in the last ${rrdstep}s!");
			}elsif($main::int{$dv}{$i}{doe} > $rrdstep / 60){
				$mq += &mon::Event( ($errele > 1)?1:0,$lvl,$cla.'eo',$dv,$dv,"$iftxt has had some ($main::int{$dv}{$i}{doe}) outbound errors in the last ${rrdstep}s");
			}
		}
		if($dicele and $main::int{$dv}{$i}{typ} != 71 and $skip !~ /d/){			# Ignore Wlan IF
			if($main::int{$dv}{$i}{did} > $rrdstep * 1000){
				$mq += &mon::Event($errele,$lvl,$cla.'di',$dv,$dv,"$iftxt has had $main::int{$dv}{$i}{did} inbound discards in the last ${rrdstep}s!");
			}
			if($main::int{$dv}{$i}{dod} > $rrdstep * 1000){
				$mq += &mon::Event($errele,$lvl,$cla.'do',$dv,$dv,"$iftxt has had $main::int{$dv}{$i}{dod} outbound discards in the last ${rrdstep}s!");
			}
		}
	}

	if($main::int{$dv}{$i}{sta} == 0 and $main::int{$dv}{$i}{pst} != 0 and $skip !~ /a/){
		$mq += &mon::Event( &mon::Elevate('A',$ele,$dv),$lvl,$cla.'ad',$dv,$dv,"$iftxt has been disabled, previous status change on ".localtime($main::int{$dv}{$i}{pcg}) );
	}elsif($main::int{$dv}{$i}{sta} == 1 and $main::int{$dv}{$i}{pst} > 1 and $skip !~ /o/){
		$mq += &mon::Event( &mon::Elevate('O',$ele,$dv),$lvl,$cla.'op',$dv,$dv,"$iftxt went down, previous status change on ".localtime($main::int{$dv}{$i}{pcg}) );
	}

	if($main::int{$dv}{$i}{lty} or $main::int{$dv}{$i}{plt} and $skip !~ /p/){
		my $typc = ($main::int{$dv}{$i}{lty} ne $main::int{$dv}{$i}{plt})?" type ".(($main::int{$dv}{$i}{plt})?" from $main::int{$dv}{$i}{plt}":"").(($main::int{$dv}{$i}{lty})?" to $main::int{$dv}{$i}{lty}":""):"";
		my $spdc = ($main::int{$dv}{$i}{spd} ne $main::int{$dv}{$i}{psp})?" speed from ".&DecFix($main::int{$dv}{$i}{psp})." to ".&DecFix($main::int{$dv}{$i}{spd}):"";
		my $dupc = ($main::int{$dv}{$i}{dpx} ne $main::int{$dv}{$i}{pdp})?" duplex from $main::int{$dv}{$i}{pdp} to $main::int{$dv}{$i}{dpx}":"";
		my $ndio = (!$main::int{$dv}{$i}{dio} and $main::int{$dv}{$i}{sta} & 3)?" did not receive any traffic":"";
		my $ndoo = (!$main::int{$dv}{$i}{doo} and $main::int{$dv}{$i}{sta} & 3)?" did not send any traffic":"";
		if( $typc or $spdc or $dupc or $ndio or $ndoo ){
			my $msg  = "$iftxt ".(($typc or $spdc or $dupc)?"changed":"")."$typc$spdc$dupc$ndio$ndoo";
			$mq += &mon::Event($ele,$lvl,$cla.'c',$dv,$dv,$msg);
		}
	}
}

=head2 FUNCTION LinkType()

Return linktype based on neighbor, except DP, MAC, NOP or static was set before

B<Options> nbr, existing linktype

B<Globals> -

B<Returns> linktype

=cut
sub LinkType{
	
	my ($dv,$et) = @_;

	if( $et =~ /^[DFMS]$/ ){
		return $et;
	}elsif( !exists $main::dev{$dv} ){
		misc::Prt("ERR :No device $dv, nbrtrack zombie?\n",'');
		return '';
	}elsif( $main::dev{$dv}{os} eq 'ESX' ){
		return 'H';
	}elsif( $main::dev{$dv}{os} eq 'Printer' ){
		return 'P';
	}elsif( $main::dev{$dv}{os} eq 'UPS' ){
		return 'U';
	}elsif( $main::dev{$dv}{dm} == 8  ){
		return 'C';
	}else{
		return 'M';
	}
}


=head2 FUNCTION PrepLink()

Prepare L2 link of untypical devices for calculation with MacLinks()

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub PrepLink{

	my ($dv,$if,$imc, $st) = @_;

	if( $st == 2 ){
		my @nbr = keys %{$ifmac{$imc}};
		if( scalar @nbr == 1 ){
			$misc::portprop{$dv}{$if}{lnk} = LinkType( $nbr[0],$misc::portprop{$dv}{$if}{lnk} );
			if( $main::dev{$nbr[0]}{dm} =~ /^[1-6]$/ ){
				misc::Prt("DBG :$imc belongs to a switch $nbr[0], no need to prepare\n") if $main::opt{'d'};
			}elsif( $main::dev{$nbr[0]}{dm} == 8 ){						# It's a controlled AP, create fake IF
				$misc::portprop{$nbr[0]}{'eth'}{lnk} = 'B';
				$misc::portprop{$nbr[0]}{'eth'}{ntrk}{$dv} = $main::now;
				misc::Prt("DBG :Prepared '$misc::portprop{$dv}{$if}{lnk}' link on controlled AP $nbr[0],eth\n") if $main::opt{'d'};
			}elsif( scalar @{$misc::ifmac{$imc}{$nbr[0]}} == 1 ){# TODO check for single Ethernet instead?
				$misc::portprop{$nbr[0]}{$misc::ifmac{$imc}{$nbr[0]}[0]}{lnk} = 'B';	# B as in backlink for MacLinks()
				$misc::portprop{$nbr[0]}{$misc::ifmac{$imc}{$nbr[0]}[0]}{ntrk}{$dv} = $main::now;
				misc::Prt("DBG :Prepared '$misc::portprop{$dv}{$if}{lnk}' link on $main::dev{$nbr[0]}{os} device $nbr[0],$misc::ifmac{$imc}{$nbr[0]}[0]\n") if $main::opt{'d'};
			}else{
				misc::Prt("DBG :$imc belongs to multiple interfaces on $nbr[0]!\n") if $main::opt{'d'};
			}
		}else{
			misc::Prt("DBG :$imc belongs to multiple devices!\n") if $main::opt{'d'};
		}
	}else{
		$misc::portprop{$dv}{$if}{pop}++;
	}
}

=head2 FUNCTION MacLinks()

Calculate L2 links based on bridge-forwarding tables

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub MacLinks{

	my $iter  = 0;
	my $mlcnt = 0;
	my $tlcnt = 0;

	&misc::Prt("\nMacLinks ------------------------------------------------------------------------\n");

	my $start = time;
	do{
		$mlcnt = 0;
		my $curdv = 0;
		foreach my $dv ( sort keys %misc::portprop ){						# Key order in Perl is random if not sorted...
			foreach my $if ( sort keys %{$misc::portprop{$dv}} ){
				if( $misc::portprop{$dv}{$if}{lnk} ne 'B' and $misc::portprop{$dv}{$if}{lnk} ne 'X'  ){
					if($misc::portprop{$dv}{$if}{lnk} eq 'D'){
						if( exists $misc::portprop{$dv}{$if}{nbr} and exists $misc::portprop{$misc::portprop{$dv}{$if}{nbr}} ){
							if( exists $misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}} and
							    $misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}}{lnk} ne 'D'){#Check for half-link
								misc::Prt("HLNK:$dv,$if to $misc::portprop{$dv}{$if}{nbr},$misc::portprop{$dv}{$if}{nif} adding reverse link\n");
								db::WriteLink(	$misc::portprop{$dv}{$if}{nbr},
										$misc::portprop{$dv}{$if}{nif},
										$dv,
										$if,
										'MAC',
										$misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}}{spd},
										$misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}}{dpx},
										$misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}}{vid},
										"Constructed from halflink");
								$misc::portprop{$misc::portprop{$dv}{$if}{nbr}}{$misc::portprop{$dv}{$if}{nif}}{lnk} = 'D';
							}
							my @nbr = keys %{$misc::portprop{$dv}{$if}{ntrk}};# DP links need pruning as well
							PruNeb($dv,$nbr[0]) if scalar @nbr == 1;
						}
					}elsif( $misc::portprop{$dv}{$if}{lnk} =~ /^[CHMPU]$/ and $misc::portprop{$dv}{$if}{typ} =~ /^(6|7|117)$/ ){
						my @nbr = keys %{$misc::portprop{$dv}{$if}{ntrk}};
						&misc::Prt("DEV :#$curdv\t$dv\t$if\t".@nbr." MAC neighbors\n");
						if(scalar @nbr == 1){					# A single nbr can be linked
							my $nlnk = 0;
							my $nif  = '';
							my $enif = '';
							foreach my $cnif ( sort keys %{$misc::portprop{$nbr[0]}} ){
								if($misc::portprop{$nbr[0]}{$cnif}{lnk} eq 'M' or $misc::portprop{$nbr[0]}{$cnif}{lnk} eq 'B'){
									my @bnbr = sort keys %{$misc::portprop{$nbr[0]}{$cnif}{ntrk}};
									if(scalar @bnbr){		# Got a backlink
										misc::Prt("NEB :$nbr[0],$cnif\t".@bnbr." neighbors backlinked\n") if $main::opt{'d'};
										$nif = $cnif;
										$nlnk++;
										foreach my $blnb ( @bnbr ){# Try to find exact backlink in case we end up with more than one
											if($blnb eq $dv){
												&misc::Prt("NEB :$nbr[0],$cnif\tfound exact backlink to $blnb\n");
												$enif = $cnif;
											}
										}
									}
								}
							}
							if($nlnk == 1 or $enif){			# We got one or even exact backlink
								$nif = $enif if $enif;
								db::WriteLink(	$dv,
										$if,
										$nbr[0],
										$nif,
										'MAC',
										$misc::portprop{$nbr[0]}{$nif}{spd},
										$misc::portprop{$nbr[0]}{$nif}{dpx},
										$misc::portprop{$nbr[0]}{$nif}{vid},
										"Constructed (Hub)");
								db::WriteLink(	$nbr[0],
										$nif,
										$dv,
										$if,
										'MAC',
										$misc::portprop{$dv}{$if}{spd},
										$misc::portprop{$dv}{$if}{dpx},
										$misc::portprop{$dv}{$if}{vid},
										"Constructed from $nlnk backlinks".(($enif)?" and exact interface $enif":"") );
								PruNeb($dv,$nbr[0]);
								&misc::Prt("MLNK:$dv,$if <=====> $nbr[0],$nif\n");
								$misc::portprop{$dv}{$if}{lnk} = 'X';	# Processed MAC-link...
								$misc::portprop{$nbr[0]}{$nif}{lnk} = 'X'; # TODO remove? if exists $misc::portprop{$nbr[0]};
								$mlcnt++;
							}else{
								&misc::Prt("NEB :$nbr[0] got $nlnk backlinks!\n");
							}
						}
					}
				}
			}
			$curdv++;
		}
		$iter++;
		&misc::Prt("ITER:$iter found $mlcnt MAC links\n\n");
		$tlcnt += $mlcnt;
	}while($mlcnt);

	&misc::Prt("","MLNK:found $tlcnt MAC links in $iter iterations, ".(time - $start)." seconds\n");
}

=head2 FUNCTION PruNeb()

Prune linked neighbor from all links where it appears along with linked device

B<Options> device,neighbor

B<Globals> misc::portprop

B<Returns> -

=cut
sub PruNeb{
	foreach my $d ( keys %misc::portprop ){
		foreach my $i ( keys %{$misc::portprop{$d}} ){
			if( exists $misc::portprop{$d}{$i}{ntrk}{$_[0]} and exists $misc::portprop{$d}{$i}{ntrk}{$_[1]} ){
				delete $misc::portprop{$d}{$i}{ntrk}{$_[1]};
			}
		}
	}
}

=head2 FUNCTION MapIp()

Map values based on IP address if set in %misc::map.

The mapped value is returned with status=1 if a mapping exists, the given value along status=0 if not.
If typ is 'ip', it'll always return the IP address (value is ignored).
If typ is 'na' and nedi is called with -f (use IPs instead of names) the IP is returned as well.

B<Options> IP address, mode, value

B<Globals> -

B<Returns> mapped value

=cut
sub MapIp{
	my ($ip,$typ,$val) = @_;
	my @i = split('\.', $ip);
	if($typ eq 'na' and $main::opt{'f'}){
		return ($ip,5);
	}elsif( exists $map{$ip} and exists $map{$ip}{$typ} ){
		if($typ eq 'na' and $map{$ip}{$typ} eq 'map2DNS'){
			my $na = gethostbyaddr(inet_aton($ip), AF_INET);
			if($na){
				&Prt("MAP :Mapped name to DNS $na\n");
				return ($na,4);
			}else{
				&Prt("MAP :Error mapping name to DNS, mapped to IP $ip instead\n");
				return ($ip,4);
			}
		}elsif($typ eq 'na' and $map{$ip}{$typ} eq 'map2IP'){
			&Prt("MAP :Mapped name to IP $ip\n");
			return ($ip,4);
		}elsif($typ eq 'nlm'){
			&Prt("MAP :Mapped $val to location\n");
			$val =~ s/$map{$ip}{nlm}/$map{$ip}{nlr}/ee;
			return ($val,4);
		}elsif($typ eq 'llm'){
			&Prt("MAP :Mapped $val to location\n");
			{
				no warnings 'uninitialized';						# suppores warnings on incomplete matches
				$val =~ s/$map{$ip}{llm}/$map{$ip}{llr}/ee;
			}
			return ($val,4);
		}else{
			&Prt("MAP :Mapped $typ to $map{$ip}{$typ}\n");
			return ($map{$ip}{$typ},4);
		}
	}elsif( exists $map{"$i[0].$i[1].$i[2]"} and exists $map{"$i[0].$i[1].$i[2]"}{$typ} ){
		if($typ eq 'nlm'){
			&Prt("MAP :Mapped $val to location\n");
			$val =~ s/$map{"$i[0].$i[1].$i[2]"}{nlm}/$map{"$i[0].$i[1].$i[2]"}{nlr}/ee;
			return ($val,3);
		}elsif($typ eq 'llm'){
			&Prt("MAP :Mapped $val to location\n");
			{
				no warnings 'uninitialized';						# suppores warnings on incomplete matches
				$val =~ s/$map{"$i[0].$i[1].$i[2]"}{llm}/$map{"$i[0].$i[1].$i[2]"}{llr}/ee;
			}
			return ($val,3);
		}else{
			&Prt("MAP :Mapped $typ to ".$map{"$i[0].$i[1].$i[2]"}{$typ}."\n");
			return ($map{"$i[0].$i[1].$i[2]"}{$typ},3);
		}
	}elsif( exists $map{"$i[0].$i[1]"} and exists $map{"$i[0].$i[1]"}{$typ} ){
		if($typ eq 'nlm'){
			&Prt("MAP :Mapped $val to location\n");
			$val =~ s/$map{"$i[0].$i[1]"}{nlm}/$map{"$i[0].$i[1]"}{nlr}/ee;
			return ($val,2);
		}elsif($typ eq 'llm'){
			&Prt("MAP :Mapped $val to location\n");
			{
				no warnings 'uninitialized';						# suppores warnings on incomplete matches
				$val =~ s/$map{"$i[0].$i[1]"}{llm}/$map{"$i[0].$i[1]"}{llr}/ee;
			}
			return ($val,2);
		}else{
			&Prt("MAP :Mapped $typ to ".$map{"$i[0].$i[1]"}{$typ}."\n");
			return ($map{"$i[0].$i[1]"}{$typ},2);
		}
	}elsif( exists $map{'default'} and exists $map{'default'}{$typ} ){
		if($typ eq 'nlm'){
			&Prt("MAP :Mapped $val to location\n");
			$val =~ s/$map{default}{nlm}/$map{default}{nlr}/ee;
			return ($val,1);
		}elsif($typ eq 'llm'){
			&Prt("MAP :Mapped $val to location\n");
			{
				no warnings 'uninitialized';						# suppores warnings on incomplete matches
				$val =~ s/$map{default}{llm}/$map{default}{llr}/ee;
			}
			return ($val,1);
		}elsif($typ eq 'na' and $map{'default'}{$typ} eq 'map2IP'){
			&Prt("MAP :Mapped name to IP by default\n");
			return ($ip,1);
		}else{
			&Prt("MAP :Mapped $typ to $map{'default'}{$typ} by default\n");
			return ($map{'default'}{$typ},1);
		}
	}else{
		$val = $ip if $typ eq 'ip';
		return ($val,0);
	}
}

=head2 FUNCTION MSM2I()

Converts HP MSM (former Colubris) IF type to IEEE types

B<Options>IF type

B<Globals> -

B<Returns> IEEE type

=cut
sub MSM2I{

	my ($t) = @_;

	if($t == 2){
		return 6;
	}elsif($t == 3){
		return 53;
	}elsif($t == 4){
		return 209;
	}elsif($t == 5){
		return 71;
	}else{
		return $t;
	}
}

=head2 FUNCTION Ip2Dec()

Converts IP addresses to dec for efficiency in DB.

B<Options> IP address

B<Globals> -

B<Returns> dec IP

=cut
sub Ip2Dec{
	if(!$_[0]){$_[0] = 0}
	return unpack N => pack CCCC => split /\./ => shift;
}

=head2 FUNCTION Dec2Ip()

Of course we need to convert them back.

B<Options> dec IP

B<Globals> -

B<Returns> IP address

=cut
sub Dec2Ip{
	if(!$_[0]){$_[0] = 0}
	return join '.' => map { ($_[0] >> 8*(3-$_)) % 256 } 0 .. 3;
}

=head2 FUNCTION IP6toDB()

Convert IPv6 for writing to DB (e.g. mysql-binary).
It'll return undef, if ip6 is not true, thus can easily be
used in functions like db::WriteNet()

B<Options> dbhandle

B<Globals> -

B<Returns> -

=cut
sub IP6toDB{

	my ($addr,$ip6) = @_;

	if($misc::backend eq 'Pg'){
		if($addr and $ip6){
			return  $addr;
		}else{
			return  undef;									# Pg accepts NULL but not empty :-/
		}
	}elsif($addr and $ip6){
		return inet_pton(AF_INET6, $addr);
	}
}

=head2 FUNCTION IP6Text()

Returns binary IPv6 as text

B<Options> binary IPv6

B<Globals> -

B<Returns> IPv6 as text

=cut
sub IP6Text{
	return inet_ntop(AF_INET6, $_[0]) if defined $_[0];
}

=head2 FUNCTION IP2Name()

Returns DNS name

B<Options> IP Address

B<Globals> -

B<Returns> DNS Name

=cut
sub IP2Name{

	return '' if $main::opt{'n'};
	my($family, $socktype, $proto, $saddr, $canonname) = getaddrinfo( $_[0], 0 );
	my($name, $port) = getnameinfo($saddr);

	return $name if $name ne $_[0] and $name !~ /:/;

#use Socket qw(AF_INET6 inet_ntop inet_pton getaddrinfo getnameinfo); TODO use when  getaddrinfo is exported in most Socket version (Perl > 5.10)?
#	my ( $err, @addrs ) = getaddrinfo( $_[0], 0 );
#	if($err){
#		&misc::Prt("IP2N:$_[0] -> $err\n");
#	}else{
#		( $err, $name ) = getnameinfo( $addrs[0]->{addr},0 );
#		if($err){
#			&misc::Prt("IP2N:$_[0] -> $err\n");
#		}elsif( $name ne $_[0] and $name !~ /:/ ){
#			return $name;
#		}
#	}
}

=head2 FUNCTION Mask2Bit()

Converts IP mask to # of bits.

B<Options> IP address

B<Globals> -

B<Returns> bitcount

=cut
sub Mask2Bit{
	$_[0] = 0 if !$_[0];
	my $bit = sprintf("%b", unpack N => pack CCCC => split /\./ => shift);
	$bit =~ s/0//g;
	return length($bit);
}


=head2 FUNCTION DecFix()

Return big numbers in a more readable way

B<Options> number

B<Globals> -

B<Returns> readable number

=cut
sub DecFix{

	if($_[0] >= 1000000000){
		return int($_[0]/1000000000)."G";
	}elsif($_[0] >= 1000000){
		return int($_[0]/1000000)."M";
	}elsif($_[0] >= 1000){
		return int($_[0]/1000)."k";
	}else{
		return $_[0];
	}
}

=head2 FUNCTION NodeMetric()

 Return Node's metric letter:
 A-F	100G - <10M speed on FD nodes
 G-L	100G - <10M speed on HD nodes
 M-Z	SNR of wlan nodes (3db steps)

B<Options> SNR, or speed & duplex

B<Globals> -

B<Returns> letter

=cut
sub NodeMetric{

	my ($s,$d) = @_;

	if( $d eq 'SNR' ){
		$s = 0 if $s < 0;									# Negative SNR even exist (DD-WRT)? Just make it 0
		return  ($s < 52)?chr( 90 - int($s/4) ):'M';						# SNR >= 50 (if ever found) is the Max
	}else{
		my $off = ($d eq 'FD')?76:82;
		$s = 1000000 if($s < 10000000);
		return chr( $off - log($s)/log(10) );
	}
}


=head2 FUNCTION NagPipe()

Pipe NeDi events into Nagios

B<Options> string of values

B<Globals> -

B<Returns> -

=cut
sub NagPipe{

	my $nag_event_service = 'Events';

	if(-p $nagpipe) {										# Nagios Handler by S.Neuser
		my $valstr = $_[0];
		$valstr =~ s/["'\n]//g;
		my @vals   = split /,/, $valstr;
		my $level  = shift @vals;
		my $time   = shift @vals;
		my $source = lc shift @vals;
		my $class  = shift @vals;
		my $dev    = shift @vals;
		my $msg    = join(',', @vals);

		my $status = 3;
		if($level < 11)     { $status = 3; }							# UNKNOWN
		elsif($level < 100) { $status = 0; }							# OK
		elsif($level < 200) { $status = 1; }							# WARN
		else { $status = 2; }									# CRIT

		open (NPIPE, ">>$nagpipe");
		print NPIPE "[$time] PROCESS_SERVICE_CHECK_RESULT;$source;$nag_event_service;$status;NeDi:$msg\n";
		close NPIPE;
	}
}

=head2 FUNCTION Diff()

Find differences in to arrays.

B<Options> pointer to config arrays

B<Globals> -

B<Returns> differences as string

=cut
sub Diff{

	use Algorithm::Diff qw(diff);

	my $chg = '';
	my $row = 1000;
	my $accts_split_a = SplitArray(0,$row,@{$_[0]});						# tx dcec
	my $accts_split_b = SplitArray(0,$row,@{$_[1]});
	my $i = 0;
	foreach (@$accts_split_a){
		if (!defined(@$accts_split_a[$i])){ @$accts_split_a[$i] = [];}
		if (!defined(@$accts_split_b[$i])){ @$accts_split_b[$i] = [];}
		my $diffs = diff(@$accts_split_a[$i], @$accts_split_b[$i]);
		if( !@$diffs ){
			$i++;
			next;
		}
		foreach my $chunk (@$diffs) {
			foreach my $line (@$chunk) {
				my ($sign, $lineno, $l) = @$line;
				if( $l !~ /\#time:|ntp clock-period/){					# Ignore ever changing lines
					$chg .= sprintf "%4d$sign %s\n", $lineno+1+($row*$i), $l;
				}
			}
		}
		$i++
	}
	return $chg;
}

=head2 FUNCTION SplitArray()

Split Array in more SubArrays (tx dcec)

B<Options> pointer to use large arrays

B<Globals> -

B<Returns> pointer arrayjunks

=cut
sub SplitArray {

	my ($start, $length, @array) = @_;
	my @array_split;
	my $count =  @array / $length;
	for (my $i=0; $i <= $count; $i++){
		my $end = ($i == 9) ? $#array : $start + $length - 1;
		@{$array_split[$i]} = grep defined,@array[$start .. $end];
		$start += $length;
	}
	return \@array_split;
}

=head2 FUNCTION GetGw()

Get the default gateway of your system (should work on *nix and win).

B<Options> -

B<Globals> -

B<Returns> default gw IP

=cut
sub GetGw{

	my @routes = `netstat -rn`;
	my @l = grep(/^\s*(0\.0\.0\.0|default)/,@routes);
	return "" unless $l[0];

	my @gw = split(/\s+/,$l[0]);

	if($gw[1] eq "0.0.0.0"){
		return $gw[3] ;
	}else{
		return $gw[1] ;
	}
}


=head2 FUNCTION CheckTodo()

Add/remove entry to/from todolist

B<Options> -

B<Globals> misc::seedini, misc::doip, misc::todo

B<Returns> # of seeds queued

=cut
sub CheckTodo{

	my ($id,$tgt,$rc,$rv,$lo,$co) = @_;

	$tgt = $id if !defined $tgt;
	if($tgt =~ /^!/){
		my $del = substr($tgt,1);
		&Prt("TODO:".sprintf("%-15.15s %-15.15s ",$del,$id) );
		my ($i) = grep { $todo[$_] eq $del } (0 .. @todo-1);
		if( defined $i ){
			splice(@todo, $i, 1);
			delete $seedini{$del};
			delete $doip{$del};
			&Prt("removed\n");
			return -1 * scalar @dto;
		}else{
			&Prt("not in todo list\n");
			return 0;
		}
	}elsif( grep {$_ eq $id} (@doneid,@failid,@todo) ){						# Don't add if done or already queued
		&Prt("TODO:".sprintf("%-15.15s %-15.15s already processed\n",$tgt,$id) );
		return 0;
	}else{
		my $hexip = ($tgt)?gethostbyname($tgt):gethostbyname($id);				# Resolve $tgt (fallback to $id). If IP is given this should not create DNS query...
		if(defined $hexip){
			my $ip = join('.',unpack('C4',$hexip) );
			if( !ValidIP($ip) or grep {$_ eq $ip} (@doneip,@failip) ){
				&Prt("TODO:".sprintf("%-15.15s %-15.15s unusable or already processed\n",$ip,$id) );
				return 0;
			}else{
				$seedini{$ip}{rc} = ($rc)?$rc:'';
				$seedini{$ip}{rv} = ($rv)?$rv:'';
				$seedini{$ip}{lo} = $lo if $lo;						# Agents only
				$seedini{$ip}{co} = $co if $co;						# Agents only
				$doip{$id} = $ip;
				push(@todo,$id);
				&Prt("TODO:".sprintf("%-15.15s %-15.15s %8s %1s added\n",$ip,$id,$seedini{$ip}{rc},$seedini{$ip}{rv}) );
				return 1;
			}
		}else{
			&Prt("ERR :Resolving $id!\n");
			return 0;
		}
	}
}

=head2 FUNCTION InitSeeds()

Queue devices to discover based on the seedlist.

B<Options> master-mode

B<Globals> misc::doip, misc::todo

B<Returns> # of seeds queued

=cut
sub InitSeeds{

	my $s = 0;

	$seedlist = ($_[0])?"$nedipath/agentlist":"$nedipath/seedlist";

	@todo = ();
	%doip = ();

	if($main::opt{'u'}){
		$seedlist = "$main::opt{'u'}";
	}
	my $src = substr($seedlist, rindex($seedlist, '/')+1,5 );

	if($main::opt{'a'}){
		$seedlist = "-a $main::opt{'a'}";
		if( $main::opt{'a'} =~ /[a-zA-Z]+/ ){
			$s += CheckTodo( $main::opt{'a'} );
		}else{
			my @r = split(/\./,$main::opt{'a'});
			foreach my $ipa ( ExpandRange($r[0]) ){
				foreach my $ipb ( ExpandRange($r[1]) ){
					foreach my $ipc ( ExpandRange($r[2]) ){
						foreach my $ipd ( ExpandRange($r[3]) ){
							$s += CheckTodo( "$ipa.$ipb.$ipc.$ipd" ) unless $main::opt{'S'} =~ /X/ and exists $misc::seedini{"$ipa.$ipb.$ipc.$ipd"};
						}
					}
				}
			}
		}
	}elsif($main::opt{'A'}){
		$seedlist = "-A $main::opt{'A'}";
		my $devs = &db::Select('devices','','device,inet_ntoa(devip),readcomm,snmpversion & 3', ($main::opt{'A'} eq 'all')?'':$main::opt{'A'} );
		foreach my $dv ( @$devs ){
			if( $dv->[3] ){
				$s += CheckTodo( $dv->[0], $dv->[1], $dv->[2], $dv->[3] );
			}
		}
	}elsif($main::opt{'O'}){
		$seedlist = "-O $main::opt{'O'}";
		my $mtch = ($main::opt{'O'} eq 'all')?'':$main::opt{'O'};
		my $nods = &db::Select('nodarp','nodip','mac,nodip',$mtch,'nodes','mac');
		for my $nip (keys %{$nods}){
			my $ip = misc::Dec2Ip($nip);
			if( ValidIP( $ip ) ){
				if( $main::opt{'s'} ){
					misc::ScanIP($ip,$main::opt{'s'});
				}else{
					$s += CheckTodo( $nods->{$nip}{'mac'},$ip ) unless $main::opt{'S'} =~ /X/ and exists $misc::seedini{$ip};
				}
			}
		}
		exit if $main::opt{'s'};
	}elsif($arpwatch and $main::opt{'o'}) {
		$seedlist .= " arpwatch";
		$s += ArpWatch(1);
	}else{
		my $te = 0;
		if(-e "$seedlist"){
			&Prt("SEED:Using $seedlist\n");
			open  (LIST, "$seedlist");
			my @list = <LIST>;
			close(LIST);
			foreach my $l (@list){
				if($l !~ /^[#;]|^\s*$/){
					$l =~ s/[\r\n]//g;
					my @f = split(/\s+/,$l);
					if( $f[0] =~ /[a-zA-Z]+/ ){
						$s += CheckTodo( $f[0],$f[0],$f[1],$f[2], $f[3], $f[4] );
					}else{
						my @r = split(/\./,$f[0]);
						foreach my $ipa ( ExpandRange($r[0]) ){
							foreach my $ipb ( ExpandRange($r[1]) ){
								foreach my $ipc ( ExpandRange($r[2]) ){
									foreach my $ipd ( ExpandRange($r[3]) ){
										$s += CheckTodo( "$ipa.$ipb.$ipc.$ipd", "$ipa.$ipb.$ipc.$ipd", $f[1], $f[2], $f[3], $f[4] ) unless $main::opt{'S'} =~ /X/ and exists $misc::seedini{"$ipa.$ipb.$ipc.$ipd"};
									}
								}
							}
						}
					}
					$te++;
				}
			}
		}else{
			&Prt("SEED:$seedlist not found!\n");
		}
		$s += CheckTodo( 'Default GW', GetGw() ) unless $te;
	}

	return $s;
}

sub ExpandRange{

	my @ip = ();

	if(!defined $_[0]){
		@ip = (1..254);
	}elsif($_[0] =~ /,/){
		foreach my $d (split(/,/,$_[0])){
			push @ip, ExpandRange($d);							# Recursion allows for multiple ranges separated by ,
		}
	}elsif($_[0] =~ /-/){
		my @r = split(/-/,$_[0]);
		for my $d ($r[0]..$r[1]){
			push @ip,$d;
		}
	}else{
		push @ip,$_[0];
	}

	return @ip;
}

=head2 FUNCTION Discover()

Discover a single device.

B<Options> device ID

B<Globals> misc::curcfg

B<Returns> -

=cut
sub Discover{

	my ($id)    = @_;
	my $start   = time;
	my $clistat = 'init';										# CLI access status
	my $dv	    = '';
	my $skip    = $main::opt{'S'};
	my $doid    = 1;

	&misc::Prt("DISC:$doip{$id} ID $id\n",sprintf("%-15.15s ",$doip{$id}));
	if($doip{$id} !~ /$misc::netfilter/){
		#&mon::Event('d',50,'nedn',$id,'',"IP $doip{$id} not matching netfilter $misc::netfilter");
		&misc::Prt("DISC:Not matching netfilter $misc::netfilter\n","$id, netfilter $misc::netfilter\t");
		$doid = 0;
	}elsif( grep {$_ eq $doip{$id}} (@doneip,@failip) ){
		&misc::Prt("DISC:IP already discovered\n","Already discovered\t\t\t");
		$doid = 0;
	}elsif($main::opt{'P'}){									# Ping requested
		my $latency = mon::PingService($doip{$id},'icmp',$main::opt{'P'});
		if($main::opt{'t'} eq 'p'){
			&misc::Prt('',"$doip{$id} TCP-Ping:".(($latency eq -1)?"---   \t":"${latency}ms   \t") );
			$doid = 0;
		}elsif($latency eq -1){									# No response, not ok to indentify
			$doid = 0;
			&Prt('',"-$doip{$id}\t");
		}elsif($skip =~ /s/ and $seedini{$doip{$id}}{dv}){					# Skip system, create dv from DB if available...
			$dv = $seedini{$doip{$id}}{na};
			&ReadSysobj($main::dev{$dv}{so});
			$doid = 0;
			&Prt('',"s$doip{$id}\t$dv\t");
		}else{
			&Prt('','+');
		}
	}
	$dv  = &snmp::Identify($id,$skip) if $doid;							# ...identify device otherwise
	if($dv and $main::dev{$dv}{fs} == $main::now and $main::opt{'T'}){				# Install new device
		my $entst = &snmp::Enterprise($dv,$skip);						# Get enterprise info
		my $regex = ($misc::backend eq 'Pg')?'~':'regexp';
		my $inst  = &db::Select('install','','*',"'$main::dev{$dv}{ty}' $regex type AND '$main::dev{$dv}{ip}' $regex target AND  status = 10 order by name");
		if( scalar @$inst ){
			foreach my $ie ( @$inst ){
				my $newst = 10;
				&Prt("DISC:$ie->[0] and $ie->[1] match install entry $ie->[2]\n");
				if( &mon::PingService($ie->[3],'',0,$timeout) ne -1 ){
					$newst = 170;
					&Prt("DISC:IP $ie->[3] already in use, install entry disposed\n");
				}elsif( -e "$main::p/conf/$ie->[10].tmpl" ){
					open  ("TMPL", "$main::p/conf/$ie->[10].tmpl");
					my @tmpl = <TMPL>;
					close("TMPL");

					my @i = grep $tmpl[$_] =~ /^===$/, 0 .. $#tmpl;
					my @conf = splice(@tmpl,$i[0]);
					shift @conf;
					if( scalar @conf ){
						if( open (CFG, ">/var/tftpboot/$ie->[2].cfg" ) ){
							foreach my $l (@conf){
								$l =~ s/%NAME%/$ie->[2]/g;
								$l =~ s/%IPADDR%/$ie->[3]/g;
								$l =~ s/%MASK%/$ie->[4]/g;
								$l =~ s/%GATEWAY%/$ie->[5]/g;
								$l =~ s/%VLANID%/$ie->[6]/g;
								$l =~ s/%LOCATION%/$ie->[7]/g;
								$l =~ s/%CONTACT%/$ie->[8]/g;
								$l =~ s/%LOGIN%/$ie->[9]/g;
								$l =~ s/%PASSWORD%/$login{$ie->[9]}{pw}/g;
								$l =~ s/%ENABLEPW%/$login{$ie->[9]}{en}/g;
								print CFG $l;
							}
							close (CFG);
							&Prt("DISC:TFTP config file $ie->[2].cfg created\n");
						}else{
							&Prt("DISC:Can't create config \n");
							$newst = 200;
						}
					}
					if( scalar @tmpl and $newst != 200 ){
						if( open (CMD, ">$main::p/cli/cmd_$ie->[2]" ) ){
							foreach my $l (@tmpl){
								$l =~ s/%NAME%/$ie->[2]/g;
								$l =~ s/%IPADDR%/$ie->[3]/g;
								print CMD $l unless $l =~ /^#/;
							}
							close (CMD);
							$clistat = &cli::PrepDev($dv,'cmd');
							&Prt("DISC:Clistatus = $clistat\n");
							if($clistat =~ /^OK/){
								$clistat = &cli::Commands($dv, $main::dev{$dv}{ip}, $main::dev{$dv}{cp}, $main::dev{$dv}{us}, $pw, $main::dev{$dv}{os}, "cmd_$ie->[2]");
							}
							if($clistat =~ /^OK-/){
								$newst = 100;
							}else{
								$mq += &mon::Event('C',150,'nede',$dv,$dv,"Cli cmd error: $clistat");
								$newst = 200;
							}
						}else{
							&Prt("DISC:Can't create command file $main::p/cli/cmd_$ie->[2]\n");
						}
					}else{
						&Prt("DISC:No commands in template or creating config failed\n");
					}
				}else{
					&Prt("DISC:Can't find template $main::p/conf/$ie->[10].tmpl\n");
				}
				db::Update('install',"status=$newst",'name='.$db::dbh->quote($ie->[2]) ) unless $newst == 10;
				last if $newst == 100 or $newst == 200;
			}
		}else{
			&Prt("DISC:No matching install entry for $main::dev{$dv}{ip} ($main::dev{$dv}{ty})\n");
		}
	}elsif($dv){											# Success?
		if(exists $skippol{$main::dev{$dv}{ty}}){
			$skip .= $skippol{$main::dev{$dv}{ty}};
			&Prt("DISC:skippol policy for $main::dev{$dv}{ty}=$skippol{$main::dev{$dv}{ty}}\n");
		}elsif(exists $skippol{'default'}){
			$skip .= &Strip($skippol{'default'});
			&Prt("DISC:default skip policy=$skip\n");
		}elsif($skip){
			&Prt("DISC:no skip policy using -S $skip\n");
		}
		my $entst = &snmp::Enterprise($dv,$skip);						# Get enterprise info
		my $iferr = &snmp::Interfaces($dv,$skip);						# Get interface info
		DevRRD($dv,$skip) if !$iferr and $rrdcmd and $skip !~ /g/;

		&snmp::IfAddresses($dv) if $sysobj{$main::dev{$dv}{so}}{ia} and $skip !~ /j/;		# Get IP addresses
		if($main::dev{$dv}{pip} and $main::dev{$dv}{pip} ne $main::dev{$dv}{ip}){		# Previous IP was different...
			$mq += &mon::Event('I',150,'nedj',$dv,$dv,"IP changed from $main::dev{$dv}{pip} to $main::dev{$dv}{ip} (update monitoring)");
		}

		if($sysobj{$main::dev{$dv}{so}}{dp} and $skip !~ /p/){
			snmp::DisProtocol($dv,$id,$sysobj{$main::dev{$dv}{so}}{dp},$skip);		# Get neighbours via LLDP, CDP or FDP
		}

		my $moderr = 0;
		if($sysobj{$main::dev{$dv}{so}}{md}){							# Get modules if a module description OID exists
			if($skip =~ /m/){
				&Prt(""," ");
			}else{
				$moderr = &snmp::Modules($dv);
			}
		}else{
			$main::dev{$dv}{stk} = 0;
			&Prt(""," ");
		}

		&KeyScan($main::dev{$dv}{ip}) if $main::opt{'k'} or $main::opt{'K'};
		if( $sysobj{$main::dev{$dv}{so}}{ar} and $skip !~ /A/ ){				# Map IP to MAC addresses, if ARP/ND is in .def
			$clistat = &cli::PrepDev($dv,'arp');						# Prepare device for cli access
			&Prt("DISC:Clistatus = $clistat\n");
			if($clistat =~ /^OK/){
				$clistat = &cli::ArpND($dv);
			}else{
				&snmp::ArpND($dv);
			}
		}else{
			&Prt("","      ");								# Spacer instead of L3 info.
		}

		if($main::dev{$dv}{sv} & 4 and $main::opt{'r'}){					# User route discovery on L3 devs, if -r
			&snmp::Routes($dv);
		}else{
			&Prt(""," ");
		}

		if($sysobj{$main::dev{$dv}{so}}{bf} eq 'Aruba'){					# Discover Wlan APs on controllers
			&snmp::ArubaAP($dv,$skip);
		}elsif($sysobj{$main::dev{$dv}{so}}{bf} eq 'CW'){
			&snmp::CWAP($dv,$skip);
		}elsif($sysobj{$main::dev{$dv}{so}}{bf} eq 'MSM'){
			&snmp::MSMAP($dv,$skip);
		}elsif($sysobj{$main::dev{$dv}{so}}{bf} eq 'ZD'){
			&snmp::ZDAP($dv,$skip);
		}elsif($sysobj{$main::dev{$dv}{so}}{bf} =~ /WLC/){					# Cisco switches with integrated WLC as detected by SNMP::Interfaces()
			&snmp::WLCAP($dv,$skip);
		}
		if( $skip !~ /F/ ){
			if($sysobj{$main::dev{$dv}{so}}{bf} eq "CAP"){
				&snmp::CAPFwd($dv);
			}elsif($sysobj{$main::dev{$dv}{so}}{bf} eq "DDWRT"){
				&snmp::DDWRTFwd($dv);
			}elsif($sysobj{$main::dev{$dv}{so}}{bf} =~ /^(normal|qbri|VLX|VXP)/){		# Get mac address table, if  bridging is set in .def
				db::ReadNbr('device = '.$db::dbh->quote($dv) );				# Read nbrtrack entries
				if($getfwd =~ /dyn|sec/){						# Using CLI to fetch forwarding table is configured?
					$clistat = &cli::PrepDev($dv,'fwd');				# Prepare device for cli access
					&Prt("DISC:Clistatus = $clistat\n");
					if($clistat =~ /^OK/){
						$clistat = &cli::BridgeFwd($dv);
					}
				}
				if($clistat ne "OK-Bridge"){
					$mq += &mon::Event('C',150,'nede',$dv,$dv,"CLI Bridge Fwd error: $clistat") unless $clistat =~ /^(init|unsupported)/;
					if($sysobj{$main::dev{$dv}{so}}{bf} =~ /^V(LX|XP)$/ and  $skip =~ /v/){
						&Prt("ERR :Cannot get Vlan indexed forwarding entries with skipping v!\n");
					}else{
						&snmp::BridgeFwd($dv);						# Do SNMP if telnet fails or CLI not configured
					}
				}
				FloodFind($dv);
			}
		}

		if($main::opt{'b'} or defined $main::opt{'B'}){						# Backup configurations
			if($skip =~ /s/ or $main::dev{$dv}{fs} == $main::now or $main::dev{$dv}{bup} ne 'A'){# Skip sysinfo or new devs force backup (or non-active are updated)
				if($clistat =~ /^OK-/){							# Wait if we just got BridgeFWD or ARP via CLI to avoid hang
					&Prt("DISC:Cli waiting $cli::clipause seconds before reconnecting\n");
					select(undef, undef, undef, $cli::clipause);
				}else{
					$clistat = &cli::PrepDev($dv,'cfg');
				}

				if($clistat =~ /^OK/){
					@curcfg = ();							# Empty config (global due to efficiency)
					$clistat = cli::Config($dv);
					if($clistat =~ /^OK-/){
						Prt("\nConfigbackup ------------------------------------------------------------------\n");
						db::BackupCfg($dv);
						if( $main::dev{$dv}{cfc} ){
							$main::dev{$dv}{bup} = 'A';
						}else{
							$main::dev{$dv}{bup} = 'U';
						}
					}
				}elsif($clistat =~ /^unsupported/){
					$main::dev{$dv}{bup} = '-';
					misc::Prt("DBG :$clistat\n") if $main::opt{'d'} =~ /c/;
				}else{
					$mq += &mon::Event('B',150,'cfge',$dv,$dv,"Config backup error: $clistat");
					$main::dev{$dv}{bup} = 'E';
				}
			}else{
				&Prt("DISC:Config hasn't been changed. Not backing up.\n");
			}
		}

		my $pof = ProPolicy($dv,$skip);
		if( $pof and !$main::opt{'t'} ){							# Send policy file, if we're not testing
			if($clistat =~ /^OK-/){
				&Prt("DISC:Cli waiting $cli::clipause seconds before reconnecting\n");
				select(undef, undef, undef, $cli::clipause);
			}else{
				$clistat = &cli::PrepDev($dv,'cmd');
			}
			&Prt("DISC:Clistatus = $clistat\n");
			if($clistat =~ /^OK/){
				$clistat = 'safety on! Remove only, if you know what you are doing!!!!';
				#$clistat = cli::Commands($dv, $main::dev{$dv}{ip}, $main::dev{$dv}{cp}, $main::dev{$dv}{us}, $pw, $main::dev{$dv}{os}, $pof);
			}
			if($clistat !~ /^OK-/){
				$mq += &mon::Event('C',150,'nede',$dv,$dv,"Policy error: $clistat");
			}
		}

		if( $main::opt{'c'} ){									# Run CLI commands
			if($clistat =~ /^OK-/){
				&Prt("DISC:Cli waiting $cli::clipause seconds before reconnecting\n");
				select(undef, undef, undef, $cli::clipause);
			}else{
				$clistat = &cli::PrepDev($dv,'cmd');
			}
			&Prt("DISC:Clistatus = $clistat\n");
			if($clistat =~ /^OK/){
				$clistat = &cli::Commands($dv, $main::dev{$dv}{ip}, $main::dev{$dv}{cp}, $main::dev{$dv}{us}, $pw, $main::dev{$dv}{os}, $main::opt{'c'});
			}
			if($clistat !~ /^OK-/){
				$mq += &mon::Event('C',150,'nede',$dv,$dv,"Command error: $clistat");
			}
		}
		push (@doneid,$id);
		push (@doneip,$doip{$id});
		push (@donenam, $dv);
		unless($main::opt{'t'}){
			&Prt("\nWrite Device Info -------------------------------------------------------------\n");
			&db::WriteDev($dv);
			&db::WriteInt($dv,$skip)	unless $iferr;
			&db::WriteMod($dv)		unless $skip =~ /m/ or $moderr;
			&db::WriteVlan($dv) 		unless $skip =~ /v/;
			&db::WriteNet($dv)  		unless $skip =~ /j/;
			&db::Update('install',"status=150","name=".$db::dbh->quote($dv) );
			&db::Commit();
		}
		if($main::opt{'x'}){
			&Prt("\nCallout ----------------------------------------------------------------------\n");
			my $xst = system($main::opt{'x'},
						($main::dev{$dv}{fs} == $main::now)?'new':'existing',
						$dv,
						$main::dev{$dv}{ip},
						$main::dev{$dv}{rv},
						$main::dev{$dv}{rc},
						$main::dev{$dv}{wc},
						$main::dev{$dv}{so},
						$main::dev{$dv}{de}
					);
			&Prt("DISC:Executed $main::opt{'x'}, which returned $xst\n"," x$xst");
		}
		delete $main::mod{$dv};
		delete $main::vlan{$dv};
		delete $main::vlid{$dv};
		delete $main::int{$dv};
		delete $main::net{$dv};
		delete $main::act{$dv};
	}else{
		push (@failid,$id);
		push (@failip,$doip{$id});
	}
	my @t = localtime;
	my $s = sprintf ("%4d/%d-%ds",scalar(@todo),scalar(@donenam),(time - $start) );
	$s .= sprintf ("\t%02d:%02d:%02d",$t[2],$t[1],$t[0] ) if $notify =~ /x/;
	&Prt("DISC:ToDo/Done-Time\t\t\t\t\t$s\n\n"," $s\n");
}

=head2 FUNCTION UseThisPoE()

Returns whether PoE should be tracked with given method

B<Options> disprot,ifmib

B<Globals> -

B<Returns> true/false

=cut

sub UseThisPoE{

	if(exists $misc::usepoe{$_[0]} and $misc::usepoe{$_[0]} eq $_[1] ){
		return 1;
	}elsif(exists $misc::usepoe{'default'} and $misc::usepoe{'default'} eq $_[1] ){
		return 1;
	}else{
		return 0;
	}

}

=head2 FUNCTION ArpWatch()

Build arp table from Arpwatch files (if set in nedi.conf).
First loop picks latest entry, second builds proper arp hash.

B<Options> seedmode

B<Globals> misc::arp, misc::arpn, misc::arpc

B<Returns> -

=cut
sub ArpWatch{

	return unless defined $arpwatch;

	my $nad = 0;
	my %amc = ();
	my %arp = ();
	my @awf = glob($arpwatch);
	chomp @awf;

	&Prt("\nArpWatch     ------------------------------------------------------------------\n");
	foreach my $f (@awf){
		&Prt("ARPW:Reading $f\n");
		open  ("ARPDAT", $f ) or die "ARP:$f not found!";					# read arp.dat
		my @adat = <ARPDAT>;
		close("ARPDAT");
		foreach my $l (@adat){
			$l =~ s/[\r\n]//g;
			my @ad = split(/\s/,$l);
			my $mc = sprintf "%02s%02s%02s%02s%02s%02s",split(/:/,$ad[0]);
			if(!exists $amc{$mc} or $ad[2] > $amc{$mc}{'time'}){
				&Prt("ARPW:$mc $ad[1]");
				if($_[0]){
					my $oui = GetOui($mc);
					&Prt(" $oui ");
					if($mc =~ /$misc::border/ or $oui =~ /$misc::border/){
						&Prt(" matches border /$misc::border/\n");
						$bd++;
					}elsif($oui =~ /$misc::ouidev/i or $mc =~ /$misc::ouidev/){
						&Prt(" matches ouidev /$misc::ouidev/\n");
						$nad += CheckTodo($mc,$ad[1]) unless $main::opt{'S'} =~ /X/ and exists $misc::seedini{$ad[1]};
					}else{
						&Prt(" no match\n");
					}
				}else{
					$amc{$mc}{'ip'}   = $ad[1];
					$amc{$mc}{'time'} = $ad[2];
					$amc{$mc}{'name'} = ($ad[3] and $main::opt{'N'} !~ /-iponly$/)?$ad[3]:'';
					&Prt(" $amc{$mc}{'ip'}\t$amc{$mc}{'name'}\tOK\n");
				}
			}
		}
	}
	if($_[0]){
		Prt('',"$nad arpwatch entries added as seeds\n");
		return $nad;
	}else{
		foreach my $mc( keys %amc ){
			if( $amc{$mc}{'time'} > $revive ){						# Ignore older entries
				$arp{''}{$mc}{''}{$amc{$mc}{'ip'}} = $amc{$mc}{'time'};
				if($amc{$mc}{'name'} and $main::opt{'N'} !~ /-iponly$/){ db::WriteDNS($amc{$mc}{'ip'},0,0,$amc{$mc}{'name'}) }
				$nad++;
			}
		}
		&Prt("ARPW:$nad arpwatch entries used\n","$nad arpwatch entries used, ");
		&db::WriteArpND('',\%arp);
		&Prt(''," written to DB\n");
	}
}

=head2 FUNCTION FloodFind()

Detect potential Switch flooders, based on population.

B<Options> device

B<Globals> -

B<Returns> - (generates events)

=cut
sub FloodFind{

	my ($dv) = @_;
	my $nfld = 0;

	&Prt("\nFloodFind    ------------------------------------------------------------------\n");
	foreach my $if( keys %{$portprop{$dv}} ){
		my $mf = ($main::int{$dv}{$portprop{$dv}{$if}{idx}}{mcf})?$main::int{$dv}{$portprop{$dv}{$if}{idx}}{mcf}:$macflood;
		if( !$portprop{$dv}{$if}{lnk} and $portprop{$dv}{$if}{pop} > $mf and $mf ){
			$mq += &mon::Event('N',150,'secf',$dv,$dv,"$portprop{$dv}{$if}{pop} MAC entries exceed threshold of $mf on $dv,$if");
			$nfld++;
		}
	}
	&Prt("FLOD:$nfld IFs triggered a MACflood alert\n");
}

=head2 FUNCTION DevRRD()

Creates system and IF RRDs if necessary and then updates them.

B<Options> device name

B<Globals> -

B<Returns> -

=cut
sub DevRRD{

	my ($na,$skip) = @_;
	my $err = 0;
	my $dok = 1;
	my $dv  = $na;
	$dv     =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;
	my $rra = '-';
	my $typ = 'GAUGE';
	if($main::dev{$na}{cul}){
		($rra, $typ) = split(/;/, $main::dev{$na}{cul});
		$rra =~ s/[^-a-zA-Z0-9]//g;
		$typ = ($typ eq "C")?"COUNTER":"GAUGE";
	}
	&Prt("\nDevRRD       ------------------------------------------------------------------\n");
	$dok = mkdir ("$nedipath/rrd/$dv", 0755) unless -e "$nedipath/rrd/$dv";
	if($dok){
		unless($main::opt{'t'}){
			unless(-e "$nedipath/rrd/$dv/system.rrd"){
				my $ds = 2 * $rrdstep;
				RRDs::create("$nedipath/rrd/$dv/system.rrd","-s","$rrdstep",
						"DS:cpu:GAUGE:$ds:0:100",
						"DS:memcpu:GAUGE:$ds:0:U",
						"DS:".lc($rra).":$typ:$ds:0:U",
						"DS:temp:GAUGE:$ds:-1000:1000",
						"RRA:AVERAGE:0.5:1:$rrdsize",
						"RRA:AVERAGE:0.5:10:$rrdsize"
						);
				$err = RRDs::error;
			}
			if($err){
				&Prt("DRRD:Can't create $nedipath/rrd/$dv/system.rrd\n","Rs");
			}else{
				RRDs::update "$nedipath/rrd/$dv/system.rrd","N:$main::dev{$na}{cpu}:$main::dev{$na}{mcp}:$main::dev{$na}{cuv}:$main::dev{$na}{tmp}";
				$err = RRDs::error;
				if($err){
					&Prt("ERR :RRD $nedipath/rrd/$dv/system.rrd $err\n","Ru");
				}else{
					&Prt("DRRD:Updated $nedipath/rrd/$dv/system.rrd\n");
				}
			}
		}
		&Prt("DRRD:CPU=$main::dev{$na}{cpu} MEM=$main::dev{$na}{mcp} TEMP=$main::dev{$na}{tmp} CUS=$main::dev{$na}{cuv}\n");

		return if $skip =~ /t/ and $skip =~ /e/ and $skip =~ /d/ and $skip =~ /b/;
		$err = '';

		&Prt("DRRD:IFName        Inoct     Outoct  Inerr Outerr   Indis  Outdis Inbcst Stat\n");
		foreach my $i ( keys %{$main::int{$na}} ){
			if(exists $main::int{$na}{$i}{ina}){						# Avoid errors due empty ifnames
				$irf =  $main::int{$na}{$i}{ina};
				$irf =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;
				unless($main::opt{'t'}){
					unless(-e "$nedipath/rrd/$dv/$irf.rrd"){
						my $ds = 2 * $rrdstep;
						RRDs::create("$nedipath/rrd/$dv/$irf.rrd","-s","$rrdstep",
								"DS:inoct:COUNTER:$ds:0:1E12",
								"DS:outoct:COUNTER:$ds:0:1E12",
								"DS:inerr:COUNTER:$ds:0:1E9",
								"DS:outerr:COUNTER:$ds:0:1E9",
								"DS:indisc:COUNTER:$ds:0:1E9",
								"DS:outdisc:COUNTER:$ds:0:1E9",
								"DS:inbcast:COUNTER:$ds:0:1E9",
								"DS:status:GAUGE:$ds:0:3",
								"RRA:AVERAGE:0.5:1:$rrdsize",
								"RRA:AVERAGE:0.5:10:$rrdsize"
								);
						$err = RRDs::error;
					}
					if($err){
						&Prt("ERR :RRD $nedipath/rrd/$dv/$irf.rrd $err\n","Ri($irf)");
					}else{
						RRDs::update "$nedipath/rrd/$dv/$irf.rrd","N:$main::int{$na}{$i}{ioc}:$main::int{$na}{$i}{ooc}:$main::int{$na}{$i}{ier}:$main::int{$na}{$i}{oer}:$main::int{$na}{$i}{idi}:$main::int{$na}{$i}{odi}:$main::int{$na}{$i}{ibr}:".($main::int{$na}{$i}{sta} & 3);
						$err = RRDs::error;
						if($err){
							&Prt("ERR :$irf.rrd $err\n","Ru($irf)");
						}
					}
				}
				&Prt(sprintf ("DRRD:%-8.8s %10.10s %10.10s %6.6s %6.6s %7.7s %7.7s %6.6s %4.4s\n", $irf,$main::int{$na}{$i}{ioc},$main::int{$na}{$i}{ooc},$main::int{$na}{$i}{ier},$main::int{$na}{$i}{oer},$main::int{$na}{$i}{idi},$main::int{$na}{$i}{odi},$main::int{$na}{$i}{ibr},$main::int{$na}{$i}{sta}) );
			}else{
				&Prt("DRRD:No IF name for IF-index $i\n","Rn($i)");
			}
		}
	}else{
		&Prt("DRRD:Can't create directory $nedipath/rrd/$dv\n","Rd");
	}
}

=head2 FUNCTION TopRRD()

Update Top traffic, error, power & monitoring RRDs.

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub TopRRD{

	my (%ec, %ifs);
	my $err = "";
	my $mok = my $msl = my $mal = 0;
	$ec{'50'} = $ec{'100'} = $ec{'150'} = $ec{'200'} = $ec{'250'} = 0;
	$ifs{'0'} = $ifs{'1'} = $ifs{'3'} = 0;
	my $tinoct = my $toutoct = my $tinerr = my $touterr = my $tindis = my $toutdis = 0;
	# Access traffic using delta octets to avoid error from missing or rebooted switches. Needs to be divided by 1G*rrdstep to get GB/s
	my $tat = &db::Select('interfaces','',"round(sum(dinoct)/1000000000/$rrdstep,3),round(sum(doutoct)/1000000000/$rrdstep,3)","linktype = '' AND lastdis > $main::now - $rrdstep",'devices','device');
	if( defined $tat->[0][1] ){
		$tinoct  = $tat->[0][0];
		$toutoct = $tat->[0][1];
	}
	# Wired interface (type not 71) errors/s
	my $twe = &db::Select('interfaces','',"round(sum(dinerr)/$rrdstep,3),round(sum(douterr)/$rrdstep,3),round(sum(dindis)/$rrdstep,3),round(sum(doutdis)/$rrdstep,3)","iftype != 71 AND lastdis > $main::now - $rrdstep",'devices','device');
	if( defined $twe->[0][3] ){
		$tinerr  = $twe->[0][0];
		$touterr = $twe->[0][1];
		$tindis  = $twe->[0][2];
		$toutdis = $twe->[0][3];
	}

	# Total nodes lastseen
	my $nodl = &db::Select('nodes','',"count(lastseen)","lastseen = $main::now");

	# Total nodes firstseen
	my $nodf = &db::Select('nodes','',"count(firstseen)","firstseen = $main::now");

	# Total power in Watts
	my $pwr = &db::Select('devices','',"sum(totpoe)","lastdis > $main::now - $rrdstep");

	# Count IF ifstat up=3, down=1 and admin down=0
	my $ifdb = &db::Select('interfaces','ifstat','ifstat,count(ifstat) as c',"lastdis > $main::now - $rrdstep group by ifstat",'devices','device');
	foreach my $k (keys %$ifdb ){
		$ifs{$k} = $ifdb->{$k}{'c'} if $ifdb->{$k}{'c'};
	}

	# Number of monitored targets / check if moni's running...
	my $lck = &db::Select('monitoring','',"max(lastok)");
	if($lck and $lck > (time - 2 * $pause) ){
		$mok = &db::Select('monitoring','',"count(status)","test != '' AND latency < $latw AND status = 0");
		if($mok){
			# Number of slow targets
			$msl = &db::Select('monitoring','',"count(status)","test != '' AND latency > $latw AND status = 0");

			# Number of dead targets
			$mal = &db::Select('monitoring','',"count(status)","test != '' AND status > 0");
		}
	}else{
		my $msg = "No successful check at all, is moni running?";
		$msg = "Last successful check on ".localtime($lck).", is moni running?" if $lck;
		&db::Insert('events','level,time,source,class,device,info',"150,$main::now,'NeDi','mons','','$msg'");
		&Prt("TRRD:$msg\n");
	}

	# Number of cathegorized events during discovery cycle
	my $dbec = &db::Select('events','level',"level,count(*) as c","time > ".(time - $rrdstep)." GROUP BY level");
	foreach my $k (keys %$dbec ) {
		$ec{$k} = $dbec->{$k}{'c'} if $dbec->{$k}{'c'};
	}

	&Prt("TRRD:Trf=$tinoct/$toutoct Err=$tinerr/$touterr Dis=$tindis/$toutdis\n");
	&Prt("TRRD:Up/Dn/Dis=$ifs{'3'}/$ifs{'1'}/$ifs{'0'} Pwr=${pwr}W Nod=$nodl/$nodf Mon=$mok/$msl/$mal Event=$ec{'50'}/$ec{'100'}/$ec{'150'}/$ec{'200'}/$ec{'250'}\n");
	if( $main::opt{'t'} ){
		&Prt("TRRD:Not writing when testing\n");
	}else{
		unless(-e "$nedipath/rrd/top.rrd"){
			my $ds = 2 * $rrdstep;
			RRDs::create(	"$nedipath/rrd/top.rrd",
					"-s","$rrdstep",
					"DS:tinoct:GAUGE:$ds:0:U",
					"DS:totoct:GAUGE:$ds:0:U",
					"DS:tinerr:GAUGE:$ds:0:U",
					"DS:toterr:GAUGE:$ds:0:U",
					"DS:tindis:GAUGE:$ds:0:U",
					"DS:totdis:GAUGE:$ds:0:U",
					"DS:nodls:GAUGE:$ds:0:U",
					"DS:nodfs:GAUGE:$ds:0:U",
					"DS:tpoe:GAUGE:$ds:0:U",
					"DS:upif:GAUGE:$ds:0:U",
					"DS:downif:GAUGE:$ds:0:U",
					"DS:disif:GAUGE:$ds:0:U",
					"DS:monok:GAUGE:$ds:0:U",
					"DS:monsl:GAUGE:$ds:0:U",
					"DS:monal:GAUGE:$ds:0:U",
					"DS:msg50:GAUGE:$ds:0:U",
					"DS:msg100:GAUGE:$ds:0:U",
					"DS:msg150:GAUGE:$ds:0:U",
					"DS:msg200:GAUGE:$ds:0:U",
					"DS:msg250:GAUGE:$ds:0:U",
					"RRA:AVERAGE:0.5:1:$rrdsize",
					"RRA:AVERAGE:0.5:10:$rrdsize");
			$err = RRDs::error;
		}
		if($err){
			&Prt("ERR :$err\n");
		}else{
			RRDs::update "$nedipath/rrd/top.rrd","N:$tinoct:$toutoct:$tinerr:$touterr:$tindis:$toutdis:$nodl:$nodf:$pwr:$ifs{'3'}:$ifs{'1'}:$ifs{'0'}:$mok:$msl:$mal:$ec{'50'}:$ec{'100'}:$ec{'150'}:$ec{'200'}:$ec{'250'}";
			$err = RRDs::error;
			if($err){
				&Prt("ERR :$err\n");
			}else{
				&Prt("TRRD:$nedipath/rrd/top.rrd update OK\n");
			}
		}
	}
}

=head2 FUNCTION WriteCfg()

Creates a directory with device name, if necessary and writes its
configuration to a file (with a timestamp as name).

B<Options> device name

B<Globals> -

B<Returns> -

=cut
sub WriteCfg{

	use POSIX qw(strftime);

	my ($dv) = @_;
	$dv      =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;
	my $ok   = 1;
	unless(-e "$nedipath/conf/$dv"){
		&Prt("WCFF:Creating $nedipath/conf/$dv\n");
		$ok = mkdir ("$nedipath/conf/$dv", 0755);
	}
	my $wcf = "$nedipath/conf/$dv/".strftime ("%Y-%m%d-%H%M.cfg", localtime($main::now) );
	if($ok and open (CF, ">$wcf" ) ){
		foreach ( @curcfg ){ print CF "$_\n" }
		close (CF);
		&Prt("WCFF:Config written to $wcf\n");

		if($main::opt{'B'}){									# if >0 only keep that many, based on raider82's idea
			my @cfiles = sort {$b cmp $a} glob("$nedipath/conf/$dv/*.cfg");
			my $cur = 0;
			foreach my $cf (@cfiles) {
				$cur++;
				if($cur > $main::opt{'B'}){
					$dres = unlink ("$cf");
					if($dres){
						&Prt("WCFF:Deleted $cf\n");
					}else{
						&Prt("ERR :Deleting config $cf\n","Bd");
					}
				}
			}
		}
	}else{
		&Prt("ERR :Writing config $wcf","Bw");
	}
}


=head2 FUNCTION CheckPolicy()

Check given policy class for match and execute action
A reference to the target is used is it may contain a very long config
The $err argument skips all processing to avoid erratic actions (e.g. if walking Discovery Protocol had major errors)

B<Options> class,\target,device,vlan,interface

B<Globals> -

B<Returns> -

=cut
sub CheckPolicy{

	my ($cl,$tgtref,$dv,$vl,$if,$err) = @_;

	#return unless keys %{$misc::pol}; TODO faster with this?

	foreach my $r ( keys %{$pol->{$cl}} ){

		my $m = 0;
		my $t = ($cl eq 'cfg')?'Config':${$tgtref};
		my ($o,$ic,$lt,$ex) = split //, $pol->{$cl}{$r}{'polopts'};
		$ex = 'S' if $ex and $err;

		if( $pol->{$cl}{$r}{'target'} eq '' ){
			$m = 1;
			$o = '';
		}elsif( $o eq 'I' and ${$tgtref} =~ /$pol->{$cl}{$r}{'target'}/ ){
			$o = "$t matches";
			$m = 1;
		}elsif( $o eq 'E' and ${$tgtref} !~ /$pol->{$cl}{$r}{'target'}/ ){
			$o = "$t is missing";
			$m = 1;
		}

		if( $m ){
			if( !$pol->{$cl}{$r}{'device'} or $dv =~ $pol->{$cl}{$r}{'device'} ){
				if( !$pol->{$cl}{$r}{'type'} or $main::dev{$dv}{'ty'} =~ $pol->{$cl}{$r}{'type'} ){
					if( !$pol->{$cl}{$r}{'location'} or $main::dev{$dv}{'lo'} =~ $pol->{$cl}{$r}{'location'} ){
						if( !$pol->{$cl}{$r}{'contact'} or $main::dev{$dv}{'co'} =~ $pol->{$cl}{$r}{'devgroup'} ){
							if( !$pol->{$cl}{$r}{'devgroup'} or $main::dev{$dv}{'dg'} =~ $pol->{$cl}{$r}{'devgroup'} ){
								if( !$pol->{$cl}{$r}{'ifname'} or $if =~ $pol->{$cl}{$r}{'ifname'} ){
									if( !$pol->{$cl}{$r}{'vlan'} or $vl =~ $pol->{$cl}{$r}{'vlan'} ){
										if( $ic eq '-' or $if and exists $misc::portprop{$dv}{$if}{cnd} and $ic eq $misc::portprop{$dv}{$if}{cnd} ){
											if( $lt eq '-' or $if and $lt eq $misc::portprop{$dv}{$if}{lnk} ){
												my $msg = "$o $cl policy $r ".($if?" on interface $if":"").($vl?" Vl$vl":"");
												Prt("DBG :$msg, '$ex' action".($act{$dv}{$if}{'rp'}?", reset policy in $act{$dv}{$if}{'rp'}min":"")."\n") if $main::opt{'d'};
												$act{$dv}{$if}{'ex'} = $ex if !defined $act{$dv}{$if}{'ex'} or $ex eq 'S';
												$act{$dv}{$if}{'rp'} = $pol->{$cl}{$r}{'respolicy'};
												$act{$dv}{$if}{'al'} = $pol->{$cl}{$r}{'alert'};
												$act{$dv}{$if}{'cl'} = 'sp'.substr($cl,0,2);
												$act{$dv}{$if}{'em'} = "$pol->{$cl}{$r}{'info'} - $msg";
												$act{$dv}{$if}{'sm'} = "$o $cl policy $r";
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

}

=head2 FUNCTION ProPolicy()

Process policy actions for a device

B<Options> device

B<Globals> -

B<Returns> -

=cut
sub ProPolicy{
	
	my ($dv,$skip) = @_;

	my @aq = ();
	my $os = $main::dev{$dv}{'os'};
	Prt("\nProPolicy    -------------------------------------------------------------------\n");
	if( $skip =~ /P/ ){
		Prt("DBG :Skipping policy processing ($skip)\n") if $main::opt{'d'};
		return '';
	}elsif($skip !~ /[pF]/ ){									# No action whith DP or FWD-table skipped
		my $rpol = db::Select('policies','ifname','*',"status=10 AND device='$dv'");
		foreach my $if ( keys %{$rpol} ){
			if( $rpol->{$if}{'time'} < $main::now ){
				my ($o,$ic,$lt,$ex) = split //, $rpol->{$if}{'polopts'};
				if( $ex eq 'D' and exists $cli::cmd{$os}{'ifes'} ){
					push @aq, "$cli::cmd{$os}{'ifct'} $if";
					push @aq, " $cli::cmd{$os}{'ifes'}";
				}elsif( $ex eq 'P' and exists $cli::cmd{$os}{'ifep'} ){
					push @aq, "$cli::cmd{$os}{'ifct'} $if";
					push @aq, " $cli::cmd{$os}{'ifep'}";
				}
				my $dr = db::Delete('policies',"id=$rpol->{$if}{'id'}" );
				Prt("DBG :$dr reset policies processed\n") if $main::opt{'d'};
			}
		}
	}

	foreach my $if ( sort keys %{$misc::act{$dv}} ){
		if( $act{$dv}{$if}{'ex'} eq 'S' ){
			Prt("DBG :$act{$dv}{$if}{'em'} ignored due to skip action\n") if $main::opt{'d'};
		}elsif( $if and $skip =~ /[pF]/ ){							# No action whith DP or FWD-table skipped
			Prt("DBG :$act{$dv}{$if}{'em'} ignored due to skip ($skip)\n") if $main::opt{'d'};
		}else{
			if( exists $rpol->{$if} ){							# Process action unless reset policy is active
				Prt("DBG :Ignoring $act{$dv}{$if}{'em'} due to active reset policy\n") if $main::opt{'d'};
			}else{
				$mq += mon::Event( $act{$dv}{$if}{'al'},200,$act{$dv}{$if}{'cl'},$dv,$dv,$act{$dv}{$if}{'em'},$act{$dv}{$if}{'sm'} );
				if( $act{$dv}{$if}{'ex'} eq 'D' and exists $cli::cmd{$os}{'ifds'} ){
					push @aq, "$cli::cmd{$os}{'ifct'} $if";
					push @aq, " $cli::cmd{$os}{'ifds'}";
				}elsif( $act{$dv}{$if}{'ex'} eq 'P' and exists $cli::cmd{$os}{'ifdp'} ){
					push @aq, "$cli::cmd{$os}{'ifct'} $if";
					push @aq, " $cli::cmd{$os}{'ifdp'}";
				}
				if( $#aq and $act{$dv}{$if}{'rp'} ){					# Create a reset policy only if action was set
					db::Insert('policies','status,class,polopts,device,ifname,alert,info,time',"10,'res','---$act{$dv}{$if}{'ex'}',".$db::dbh->quote($dv).",'$if',$act{$dv}{$if}{'al'},'$act{$dv}{$if}{'em'}',".($main::now+$act{$dv}{$if}{'rp'}*60) );
				}
			}
		}
	}
	
	if( scalar @aq ){
		$dv =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;
		my $aqf = "$nedipath/cli/pol_$dv";
		if( open (CF, ">$aqf" ) ){
			print CF "$cli::cmd{$os}{'conf'}\n";
			foreach ( @aq ){ print CF "$_\n" }
			print CF "$cli::cmd{$os}{'end'}\n";
			close (CF);
			Prt("PPOL:$#aq actions written to $aqf\n");
		}else{
			Prt("ERR :Writing actions $aqf","Pw");
		}

		return "pol_$dv";
	}else{
		Prt("PPOL :No actions triggered\n");
		return '';
	}
}

=head2 FUNCTION Daemonize()

Fork current programm and detatch from cli.

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub Daemonize{

	use POSIX qw(setsid);

	&Prt(" daemonizing");
	defined(my $pid = fork)   or die "Can't fork: $!";
	exit if $pid;
	setsid                    or die "Can't start a new session: $!";
	umask 0;
}


=head2 FUNCTION RetrVar()

Retrieve variables previousely stored in .db files for debugging.

B<Options> -

B<Globals> all important globals (see code)

B<Returns> -

=cut
sub RetrVar{

	use Storable;

	my $seedini = retrieve("$main::p/seedini.db");
	%seedini = %$seedini;
	my $sysobj = retrieve("$main::p/sysobj.db");
	%sysobj = %$sysobj;
	my $portprop = retrieve("$main::p/portprop.db");
	%portprop = %$portprop;
	my $doip = retrieve("$main::p/doip.db");
	%doip = %$doip;
	my $arp = retrieve("$main::p/arp.db");
	%arp = %$arp;
	my $ifmac = retrieve("$main::p/ifmac.db");
	%ifmac = %$ifmac;
	my $ifip = retrieve("$main::p/ifip.db");
	%ifip = %$ifip;

	my $donenam = retrieve("$main::p/donenam.db");
	@donenam = @$donenam;
	my $doneid = retrieve("$main::p/doneid.db");
	@doneid = @$doneid;
	my $doneip = retrieve("$main::p/doneip.db");
	@doneip = @$doneip;


	my $dev = retrieve("$main::p/dev.db");
	%main::dev = %$dev;
	my $net = retrieve("$main::p/net.db");
	%main::net = %$net;
	my $int = retrieve("$main::p/int.db");
	%main::int = %$int;
	my $vlan = retrieve("$main::p/vlan.db");
	%main::vlan = %$vlan;
}


=head2 FUNCTION StorVar()
Write important variables in .db files for debugging.

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub StorVar{

	use Storable;

	store \%seedini, "$main::p/seedini.db";
	store \%sysobj, "$main::p/sysobj.db";
	store \%portprop, "$main::p/portprop.db";
	store \%doip, "$main::p/doip.db";
	store \%arp, "$main::p/arp.db";
	store \%ifmac, "$main::p/ifmac.db";
	store \%ifip, "$main::p/ifip.db";

	store \@donenam, "$main::p/donenam.db";
	store \@doneid, "$main::p/doneid.db";
	store \@doneip, "$main::p/doneip.db";

	store \%main::dev, "$main::p/dev.db";
	store \%main::int, "$main::p/int.db";
	store \%main::net, "$main::p/net.db";
	store \%main::vlan, "$main::p/vlan.db";
}


=head2 FUNCTION Prt()

Print output based on verbosity or buffer into variable in case
of multiple threads.

B<Options> Short output, verbose output

B<Globals> -

B<Returns> -

=cut
sub Prt{
	if($main::opt{'v'}){
		print "$_[0]" if $_[0];
	}elsif($_[1]){
		print "$_[1]";
	}
}

=head2 FUNCTION DevIcon()

Assign icon based on services or use existing one

B<Options> icon, services

B<Globals> -

B<Returns> icon

=cut
sub DevIcon{
	if($_[1]){
		return $_[1];
	}else{
		if($_[0] > 8){
			return 'csan';
		}elsif($_[0] > 4){
			return 'w3an';
		}elsif($_[0] > 1){
			return 'w2an';
		}else{
			return 'w1an';
		}
	}
}

=head2 FUNCTION DevVendor()

Return vendor based on enterprise#

B<Options> sysobjid

B<Globals> -

B<Returns> vendor

=cut
sub DevVendor{

	if( $_[0] =~ /^1.3.6.1.4.1.(9|5596|14179)\./ ){							# Not matching all literal dots, but easier to read!
		return'Cisco';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(259)\./ ){
		return'Accton';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(6486|637|6527)\./ ){
		return'Alcatel-Lucent';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(318)\./ ){
		return'APC';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(45|2272)\./ ){
		return'Avaya';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(10704)\./ ){
		return'Barracuda';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(3417)\./ ){
		return'Blue Coat';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(289\.|1588\.|1991\.|30803)/ ){
		return'Brocade';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(674|6027|8741)\./ ){
		return'Dell';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(1916)\./ ){
		return'Extreme Networks';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(12356)\./ ){
		return'Fortinet';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(11|43|8744|25506)\./ ){
		return'Hewlett-Packard';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(2636|3224|12532)\./ ){
		return'Juniper';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(1369)\./ ){
		return'McAfee';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(5651)\./ ){
		return'Maipu';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(1573|3181|3401)\./ ){
		return'Microsens';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(789)\./ ){
		return'Netapp';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(4526)\./ ){
		return'Netgear';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(266)\./ ){
		return'Nexans';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(94)\./ ){
		return'Nokia';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(24681)(\.|$)/ ){
		return'Qnap';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(8059)(\.|$)/ ){
		return'Paradyne';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(17163)\./ ){
		return'Riverbed';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(25053)\./ ){
		return'Ruckus';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(6876)\./ ){
		return'VMware';
	}elsif( $_[0] =~ /^1.3.6.1.4.1.(890)(\.|$)/ ){
		return'Zyxel';
	}else{
		return'Other';
	}
}

=head2 FUNCTION KeyScan()

Useful with strict host key checking enabled. Invoked with -k the ssh
keys will be stored in the users .ssh directory. Should only be used at
the first discovery.

B<Options> device IP

B<Globals> -

B<Returns> -

=cut
sub KeyScan{

	&Prt("\nKeyScan       -----------------------------------------------------------------\n");

	if($main::opt{'K'}){										# Delete stored key, based on raider82's idea
		my $res = `ssh-keygen -R $_[0] -f ~/.ssh/known_hosts`;
		&Prt("DISC:Cli: key removed for $_[0]\n","Kr");
	}

	if($main::opt{'k'}){										# Scan key (tx jug)
		my $res = `ssh-keyscan $_[0] 2>&1 >> ~/.ssh/known_hosts`;
		if( $res =~ m/^$|no hostkey alg/ ){
			&Prt("ERR :ssh-keyscan rsa failed, trying dsa\n");
			$res = `ssh-keyscan -t dsa $_[0] 2>&1 >> ~/.ssh/known_hosts`;
			if( $res =~ m/^$|no hostkey alg/ ){
				&Prt("ERR :ssh-keyscan dsa failed, trying rsa1 as last resort\n");
				$res = `ssh-keyscan -t rsa1 $_[0] 2>&1 >> ~/.ssh/known_hosts`;
				if( $res =~ m/^$|no hostkey alg/ ){
					&Prt("ERR :ssh-keyscan for $_[0] failed\n","Ke");
				} else {
					chomp($res);
					&Prt("KEY :$res (RSA1) added to ~/.ssh/known_hosts\n","Ks");
				}
			} else {
				chomp($res);
				&Prt("KEY :$res (DSA) added to ~/.ssh/known_hosts\n","Ks");
			}
		}else{
			chomp($res);
			&Prt("KEY :$res (RSA) added to ~/.ssh/known_hosts\n","Ks");
		}
	}
}

=head2 FUNCTION ResolveName()

Resolves IP via DNS or find in DB

B<Options> DNS Name

B<Globals> -

B<Returns> IP/0

=cut
sub ResolveName{
	my $hip = gethostbyname($_[0]);
	if(defined $hip){
		return join('.',unpack( 'C4',$hip ) );
	}elsif(exists $main::dev{$_[0]}){
		return $main::dev{$_[0]}{ip};
	}else{
		return 0;
	}
}

=head2 FUNCTION ValidIP()

Check whether IP is usable

B<Options> IP

B<Globals> -

B<Returns> Bool

=cut
sub ValidIP{
	if( defined $_[0] and $_[0] =~ /^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/ and $_[0] !~ /^[0]?$|^127\.0\.0\.|^0\.0\.0\.0$|^255\.255\.255\.255$/ ){
		return 1;
	}
}

=head2 FUNCTION ValidMAC()

Check whether MAC is usable and belongs to a device:

 0 = invalid MAC
 1 = valid node MAC
 2 = device MAC

B<Options> MAC address

B<Globals> -

B<Returns> MAC status

=cut
sub ValidMAC{
	if( defined $_[0] and $_[0] =~ /^[0-9a-f]{12}$/ ){
		if( exists $ifmac{$_[0]} ){
			return 2;
		}elsif(  $_[0] !~ /$ignoredmacs/ ){
			return 1;
		}
	}
}

=head2 FUNCTION CheckProduct()

B<Options> vendor, type

B<Globals> -

B<Returns> endwarranty,endlife,endsupport,migration info

=cut
sub CheckProduct{

	if($_[0] eq 'Cisco' and -e "$main::p/ciscoeol.csv"){
		my @l = `egrep -hi '^$_[1](\/K9)?;' $main::p/ciscoeol.csv`;
		if( @l and scalar @l < 4 ){								# Tolerating up to 3 duplicates and picking the 1st
			my @v = split(';',$l[0]);
			&misc::Prt("CHKP:$_[0] $_[1] is EoL, check PDID $v[5]");
			return( $v[1],$v[2],$v[3],", can be migrated to $v[4]" );
		}else{
			&misc::Prt("CHKP:".scalar @l." matches found with $_[1] in ciscoeol.csv\n");
			return( 0,0,0,'' );
		}
	}else{
		return( 0,0,0,'' );
	}
}

=head2 FUNCTION ScanIP()

Scans IP for open ports and identifies HTTP and SSH servers

B<Options> IP address

B<Globals> -

B<Returns> -

=cut
sub ScanIP{

	my ($ip,$opt) = @_;

	my $res = my $srv = my $os = '';
	my $svtcp = my $svudp = my $svtyp = my $svos = '';

	my $latency = 0;
	if( $main::opt{'P'} ){
		$latency = &mon::PingService($ip,'',0,$main::opt{'P'});
	}
	return if $latency eq -1;

	my @i = split /,/, $opt;
	foreach my $p (@i){
		if( $p eq 'id' ){
			( $res, $srv, $os ) = mon::CliStat($ip,22);
			if( $res ){
				$svtcp .= '22,';
				$svtyp .= "$srv,";
				$svos  = $os;
			}

			( $res, $srv, $os ) = mon::CliStat($ip,25);
			if( $res ){
				$svtcp .= '25,';
				$svtyp .= "$srv,";
				$svos  = $os;
			}

			if( $web::lwpok ){
				( $res, $srv, $os ) = web::GetHTTP($ip,'http');
				if( $res !~ /^500 / ){
					$svtcp .= '80,';
					$svtyp .= "$srv,";
					$svos  = $os unless $svos;
				}
				($res, $srv, $os ) = web::GetHTTP($ip,'https');
				if( $res !~ /^500 / ){
					$svtcp .= '443,';
					$svtyp .= "$srv," unless $svtyp;
					$svos  = $os unless $svos;
				}
			}

			($res, $srv, $err) = mon::NbtStat($ip,137);
			if( !$err ){
				$svudp .= '137,';
				$svtyp .= "$res/$srv,";
			}
		}
		if( $p =~ /^[0-9]+$/ ){
			$latency = mon::PingService($ip,'tcp',$p);
			$svtcp .= "$p," unless $latency == -1;
		}
	}

	if( $svtcp or $svudp ){
		my $dip   = &misc::Ip2Dec($ip);
		my $dbnam = &db::Select('nodarp','nodip','*',"nodip=$dip");
		my $nodex = (exists $dbnam->{$dip})?'existing':'new';
		if( $nodex ){
			db::Update('nodarp',"tcpports='$svtcp',udpports='$svudp',srvtype='$svtyp',srvos='$svos',srvupdate=$main::now","nodip=$dip");
		}else{
			$misc::mq += &mon::Event('J',100,'secr','NeDi','NeDi',"Found rogue IP $ip with $svtyp");
		}
		if( $main::opt{'x'} ){
			&Prt("\nCallout ----------------------------------------------------------------------\n");
			my $xst = system($main::opt{'x'},$nodex,$ip,$svtcp,$svudp,$svtyp,$svos );
			&Prt("DISC:Executed $main::opt{'x'}, which returned $xst\n"," $xst");		
		}
	}
}

1;
