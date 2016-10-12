#!/usr/bin/perl

=pod

=head1 PROGRAM nedi.pl

Main program which can initialize the DB and perform discovery and
other useful tasks, see HELP (nedi.pl -h)!

=head2 LICENSE

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

=head2 AUTHORS

Remo Rickli & NeDi Community

Visit http://www.nedi.ch for more information.

=cut

our $VERSION = "1.5.225";

use strict;
use warnings;
no warnings qw(once);

use Getopt::Std;
use File::Path;

#use threads;
#use threads::shared;
#my $threads = 5;

use vars qw($p $warn $nediconf $now $lasdis);
use vars qw(%nod %dev %int %mod %vlan %opt %net %usr);
$misc::mq = 0;
$misc::ncmd = "$0 ".join(" ",@ARGV);

getopts('a:A:bB:c:C:d:DfFiIkKl:N:nM:oO:pP:rs:S:t:Tu:U:vV:wWx:yY:Z:',\%opt) || &HELP_MESSAGE;
if(!defined $opt{'S'}){$opt{'S'} = ''}									# Avoid warnings if unused
if(!defined $opt{'Y'}){$opt{'Y'} = ''}									# Avoid warnings if unused
if(!defined $opt{'d'}){$opt{'d'} = ''}									# Avoid warnings if unused
if(!defined $opt{'t'}){$opt{'t'} = ''}									# Avoid warnings if unused

select(STDOUT); $| = 1;											# Disable buffering

$p   = $0;
$p   =~ s/(.*)\/(.*)/$1/;
if($0 eq $p){$p = "."};
$now = time;
require "$p/inc/libmisc.pm";										# Use the miscellaneous nedi library
misc::ReadConf();											# Needs to be evaluated first (e.g. for usessh)
$misc::guiauth = '';											# Intended for PHP GUI, breaks SendCmd() if configured with -pass
require "$p/inc/libsnmp.pm";										# Use the SNMP function library
require "$p/inc/libmon.pm";										# Use the Monitoring lib for notifications
require "$p/inc/libcli.pm";										# Use the CLI function library
require "$p/inc/libdb.pm";										# Use the DB function library

$lasdis = $now - $misc::rrdstep * 1.1;									# Last discovery time with some slack

my $dsok = 0;
if($opt{'d'} =~ /s/){
	use Data::Dumper;
	eval 'use Devel::Size qw(size total_size);';
	if ($@){
		misc::Prt("PERL:devl::size not available\n");
	}else{
		$dsok = 1;
		misc::Prt("PERL:devel::size loaded\n");
	}
}

=head2 Debug Mode

The -D section lets you execute specific functions. misc::RetrVar restores previousely
dumped variables into .db files (with -dv). This works like a snapshot and allows for debugging the
post discovery functions, without having to discovery the network everytime.

The actual content below varies and doesn't necessarily make sense...

=cut
if ( $opt{'D'} ){
#	print misc::Date2Unix('2002-Oct-16');
#print misc::IP2Name('10.10.10.1');
#	db::Connect($misc::dbname,$misc::dbhost,$misc::dbuser,$misc::dbpass);
#	misc::TopRRD() if $opt{'S'} !~ /G/;
#	misc::ReadOUIs();
#	my $nseed = misc::InitSeeds(0);
#	print "Got $nseed seeds\n";
#	db::ReadDev();
#	db::ReadAddr();
#	misc::RetrVar();
#	db::Inventory();
#	db::Disconnect();
#misc::MacLinks() if $opt{'S'} !~ /L/;
#db::Commit();
#db::Disconnect();

#	print db::Select('system', '','value','name="first"');

#	misc::TopRRD();

# Debug VoIP Phones
#	require "$p/inc/libweb.pm";
#	$main::dev{'test'}{ip} = '10.10.10.10/i.html';
#	web::CiscoPhone('test');
#die;

# Debug Device name to location conversion
#	my $loc = 'M8000-KSL-4-S-S-45';
#	$loc =~ s/$misc::nam2loc[0]/$misc::nam2loc[1]/ee;
	#$loc =~ s/^(\w+)-(\w+)-(\w+)-.*/CH;$1;$2;$3;Rack;10/;
#	print "2LOC: s/$misc::nam2loc[0]/$misc::nam2loc[1]/ = $loc\n";
#	my $disdur = int(time - $now);
#	print "END :Took $disdur seconds\n\n";
}elsif( $opt{'Z'} ){
		my $pw = unpack "H*",misc::XORpass( $opt{'Z'} );
		print "\t$pw\n";
}elsif( $opt{'i'} or $opt{'I'} ){
	my $adminuser;
	my $adminpass;
	my $nedihost = 'localhost';
	print "\nInitialize NeDi DB".(($opt{'I'})?', delete configs and RRDs':'')."!!!\n";
	print "------------------------------------------------------------------------\n";
	if ($#ARGV ne -1){										# Not using getopt to allow OPTIONAL credentials
		$adminuser = $ARGV[0];
		$adminpass = (exists $ARGV[1])?$ARGV[1]:'';
	}else{												# interactive credentials then...
		print "$misc::backend admin user: ";
		$adminuser = <STDIN>;
		print "$misc::backend admin pass: ";
		$adminpass = <STDIN>;
	}
	if($misc::dbhost ne $nedihost){
		print "NeDi host (where the discovery runs on: ";
		$nedihost = <STDIN>;
	}
	$adminuser = (defined $adminuser)?$adminuser:"";						# Avoid errors on empty Webform
	$adminpass = (defined $adminpass)?$adminpass:"";
	chomp($adminuser,$adminpass,$nedihost);
	db::InitDB($adminuser, $adminpass, $nedihost);

	if( $opt{'I'} ){
		my $nrrd = rmtree( "$misc::nedipath/rrd", {keep_root => 1} );
		my $ncfg = rmtree( "$misc::nedipath/conf", {keep_root => 1} );
		mkpath("$misc::nedipath/rrd");
		mkpath("$misc::nedipath/conf");
		print "INIT: $nrrd RRDs and $ncfg configurations (with dirs) deleted!\n\n";
	}
}elsif( $opt{'y'} ){
	print "Supported Devices (NeDi ${VERSION}p3) ----------------------------------------\n";
	chdir("$p/sysobj");
	my $nd = 0;
	my @defs = glob("*.def");
	foreach my $df (sort @defs){
		$nd++;
		my $vn = misc::DevVendor($df);
		if( open F, $df ){
			while (<F>) {
				next unless s/^Type\s*//;
				$_ =~ s/\r|\n//g;
				printf ("%-20s%-30s%s\n",$vn,$_,$df );
			}
			close(F);
		}else{
			print "couldn't open $df\n"
		}
	}
	print "$nd Definitions (and counting) ------------------------------------------------\n\n";
}elsif( $opt{'N'} ){
	$misc::lwpok = 0;
	require "$p/inc/libweb.pm" if $opt{'s'};
	db::Connect($misc::dbname,$misc::dbhost,$misc::dbuser,$misc::dbpass);
	if($opt{'N'} =~ /^arpwatch/){
		misc::ArpWatch(0);
		#db::Commit();#TODO remove commit from Insert/Update/ and test?
	}else{
		my @r = split(/\./,$opt{'N'});
		foreach my $ipa ( misc::ExpandRange($r[0]) ){
			foreach my $ipb ( misc::ExpandRange($r[1]) ){
				foreach my $ipc ( misc::ExpandRange($r[2]) ){
					foreach my $ipd ( misc::ExpandRange($r[3]) ){
						if( !misc::ValidIP("$ipa.$ipb.$ipc.$ipd") ){
							die "Invalid IP: $ipa.$ipb.$ipc.$ipd";
						}elsif( $opt{'s'} ){
							misc::ScanIP("$ipa.$ipb.$ipc.$ipd",$opt{'s'});
						}else{
							db::WriteDNS("$ipa.$ipb.$ipc.$ipd");
						}
					}
				}
			}
		}
		#db::Commit();
	}
	db::Disconnect();
	print "END :Took ".int((time - $now)/60)." minutes\n\n";
	exit;
}else{
	$misc::lwpok = 0;
	require "$p/inc/libweb.pm" unless $opt{'S'} =~ /W/;						# Use the WEB functions for webdevs if not skipped

	db::Connect($misc::dbname,$misc::dbhost,$misc::dbuser,$misc::dbpass);
	db::ReadDev();
	misc::ReadOUIs();
	my $ntgt  = mon::InitMon();
	my $nseed = misc::InitSeeds(0);
	exit if $opt{'t'} eq 's';
	db::ReadAddr();

	if( $main::opt{'S'} !~ /P/ ){
		my @pkey   = ('class', 'id');
		$misc::pol = db::Select('policies',\@pkey,'*','status=100');
	}

	my $nthrd = db::Select('system','','value',"name='threads'");
	my $first = db::Select('system','','value',"name='first'");

	if ($opt{t}){
		print "MAIN:Ignoring $nthrd thread".(($nthrd != 1)?'s':'')." for testing\n" if $opt{'v'};
	}elsif ($nthrd > 0){
		print "MAIN:$nthrd thread".(($nthrd != 1)?'s':'').", 1st from ".localtime($first) if $opt{'v'};
		if ( ($now - $first) > 3 * $misc::rrdstep ){						# Check for stale threads (based on community ideas)
			print " is older than ".3*$misc::rrdstep."s, resetting!\n" if $opt{'v'};
			db::Update('system',"value='$now'","name='first'");
			db::Update('system',"value=1","name='threads'");				# Make sure we're back to 1!
			db::Insert('events','level,time,source,class,device,info',"200,$now,'NeDi','bugn','','$nthrd thread(s), 1st from ".localtime($first)." is older than $misc::rrdstep seconds!'",1);
		}else{
			print " seems ok adding this one\n" if $opt{'v'};
			my $casval = ($misc::backend eq 'Pg')?'cast(value as int)':'value';
			db::Update('system',"value = $casval+1","name='threads'");			# Register new thread
		}
	}else{
		if ($nthrd < 0){
			my $err = "$nthrd thread(s) error, 1st from ".localtime($first)." make sure interval is longer than discovery takes!";
			print "MAIN:$err" if $opt{'v'};
			db::Insert('events','level,time,source,class,device,info',"200,$now,'NeDi','bugn','','$err'",1);
		}else{
			print "MAIN:No threads, set 1st at ".localtime($now)."\n" if $opt{'v'};
		}
		db::Update('system',"value=1","name='threads'");					# Register first thread
		db::Update('system',"value=$now","name='first'");
	}
	print "\nNetwork Discovery (${VERSION}p3) $misc::ncmd\n";
	print "Started with $nseed seed".(($nthrd != 1)?'s':'')." at ". localtime($now)."\n";
	print "-------------------------------------------------------------------------------\n";
	print "Device				Status				Todo/Done-Time\n";
	print "===============================================================================\n";
	while ($#misc::todo ne "-1"){
		my $id = shift(@misc::todo);
		misc::Discover($id);

		if( $opt{'d'} =~ /s/ ){
			print "-------------------------------------------------------------------------------\n";
			system('ps aux|egrep "^USER|perl"');
			if( $dsok ){
				print "\%dev\t\t".misc::DecFix( total_size(\%dev) )."\n";
				print "\%int\t\t".misc::DecFix( total_size(\%int) )."\n";
				print "\%mod\t\t".misc::DecFix( total_size(\%mod) )."\n";
				print "\%vlan\t\t".misc::DecFix( total_size(\%vlan) )."\n";
				print "\%net\t\t".misc::DecFix( total_size(\%net) )."\n";

				print "\%misc::sysobj\t".misc::DecFix( total_size(\%misc::sysobj) )."\n";
				print "\%misc::ifmac\t".misc::DecFix( total_size(\%misc::ifmac) )."\n";
				print "\%misc::ifip\t".misc::DecFix( total_size(\%misc::ifip) )."\n";
				print "\%misc::useip\t".misc::DecFix( total_size(\%misc::useip) )."\n";
				print "\%misc::oui\t".misc::DecFix( total_size(\%misc::oui) )."\n";
				print "\%misc::arp\t".misc::DecFix( total_size(\%misc::arp) )."\n";
				print "\%misc::arp6\t".misc::DecFix( total_size(\%misc::arp6) )."\n";
				print "\%misc::arpc\t".misc::DecFix( total_size(\%misc::arpc) )."\n";
				print "\%misc::arpn\t".misc::DecFix( total_size(\%misc::arpn) )."\n";
				print "\%misc::portprop\t".misc::DecFix( total_size(\%misc::portprop) )."\n";
				print "\%misc::portnew\t".misc::DecFix( total_size(\%misc::portnew) )."\n";
				print "\%misc::portdes\t".misc::DecFix( total_size(\%misc::portdes) )."\n";
				print "\%misc::vlid\t".misc::DecFix( total_size(\%misc::vlid) )."\n";

				print "\@misc::todo\t".misc::DecFix( total_size(\@misc::todo) )."\n";
				print "\@misc::donenam\t".misc::DecFix( total_size(\@misc::donenam) )."\n";
				print "\@misc::doneid\t".misc::DecFix( total_size(\@misc::doneid) )."\n";
				print "\@misc::doneip\t".misc::DecFix( total_size(\@misc::doneip) )."\n";
				print "\@misc::failid\t".misc::DecFix( total_size(\@misc::failid) )."\n";
				print "\@misc::failip\t".misc::DecFix( total_size(\@misc::failip) )."\n";
			}
		}

		last if defined $opt{'l'} and scalar(@misc::donenam) >= $opt{'l'};			# A limit was specified and we hit it...
	}
	print "===============================================================================\n";
	my $ndev = scalar @misc::donenam;
	my $nnod = 0;
	if ($ndev){
		misc::Prt("MAIN:$ndev devices discovered\n","$ndev devices, retire: ");
		misc::StorVar() if $opt{'d'}  =~ /v/;
		unless( $opt{'t'} ){									# We're only testing
			my $nthrd = db::Select('system','','value',"name='threads'");
			misc::Prt("MAIN:$nthrd threads running right now\n");
			if( $nthrd == 1){
				if( $opt{'S'} !~ /R/ ){
					my $nod = db::Delete('nodes',"lastseen < $misc::retire");
					misc::Prt("MAIN:$nod nodes retired\n","$nod nod, ") if $nod;
					my $nbr = db::Delete('nbrtrack',"time < $misc::retire");
					misc::Prt("MAIN:$nbr neighbor track entries retired\n","$nbr nbr, ") if $nbr;
				}
				if( $opt{'S'} !~ /D/ ){
					my $dns4 = db::Delete('dns',"dnsupdate < $misc::retire");
					my $dns6 = db::Delete('dns6',"dns6update < $misc::retire");
					misc::Prt("MAIN:$dns4 Anames and $dns6 AAAAnames retired\n","$dns4/$dns6 dns ") if $dns4 or $dns6;
				}
				if( $opt{'S'} !~ /I/ ){
					my $arp = db::Delete('nodarp',"ipupdate < $misc::retire");
					my $nd  = db::Delete('nodnd',"ip6update < $misc::retire");
					misc::Prt("MAIN:$arp ARP and $nd ND entries retired\n","$arp/$nd arpnd ") if $arp or $nd;
				}
				if( $opt{'S'} !~ /T/ ){
					my $ift = db::Delete('iftrack',"ifupdate < $misc::retire");
					my $ipt = db::Delete('iptrack',"ipupdate < $misc::retire");
					misc::Prt("MAIN:$ift IF track and $ipt IP track entries retired\n","$ift iftrack, $ipt iptrack") if $ift or $ipt;
				}
				misc::TopRRD() if $opt{'S'} !~ /G/;
				
				my $ldel = db::Delete('links',"linktype != 'STAT' AND time < '$misc::retire'");
				misc::Prt("MAIN:$ldel dynamic links have been retired\n","$ldel links") if $ldel;
			}
			misc::Prt("","\n");
			mon::AlertFlush("Discovery Notification",$misc::mq);
			misc::MacLinks() if $opt{'S'} !~ /[FL]/;					# Mac links rely on bridge fwd...
		}
	}else{
		print "Nothing discovered, nothing written...\n";
	}
	my $casval = ($misc::backend eq 'Pg')?'cast(value as int)':'value';
	my $disdur = int((time - $now)/60);
	db::Update('system',"value=$casval-1","name='threads'") unless $opt{'t'};			# Deregister thread, if not testing
	db::Insert('events','level,time,source,class,device,info',"30,".time.",'NeDi','bugn','',".$db::dbh->quote("PID $$ ($misc::ncmd) took longer than 0.9 times rrdstep!") ) if $disdur > 0.015 * $misc::rrdstep;# 0.9/60 as in 90% rrdstep in minutes
	db::Insert('events','level,time,source,class,device,info',"30,".time.",'NeDi','bugx','',".$db::dbh->quote("PID $$ ($misc::ncmd) ran for $disdur minutes, discovered $nnod nodes, ".scalar(@misc::donenam)." devices and failed on ".scalar(@misc::failip) ) ) if $misc::notify =~ /x/;
	db::Commit();
	db::Disconnect();
	print "END :Took $disdur minutes\n\n";
}

=head2 FUNCTION HELP_MESSAGE()

Display some help

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub HELP_MESSAGE(){
	print "\n";
	print "usage: nedi.pl [Sources] [Control] [Actions] | Other Actions\n\n";
	print "Sources (how the Todo-list gets filled up) --------------------------------\n";
	print "-a ip	Add single device or ip range (e.g. 10.10.1-9.1 or 10.10.10.1,2,3)\n";
	print "-A cond	Add devices from DB all or e.g.\"loc LIKE 'here%'\"\n";
	print "-p 	Discover LLDP,CDP,FDP or NDP neighbours\n";
	print "-o	OUI discovery (based on ARP chache entries)\n";
	print "-O cond	OUI discovery (based on nodes in DB similar to -A)\n";
	print "-r	Route table discovery (on L3 devices)\n";
	print "-u file	Use specified seedlist\n";
	print "-U file	Use specified configuration (use - to read from stdin)\n\n";
	print "Control (how discovery should behave) -------------------------------------\n";
	print "-C cmty	Prefer this community over those in nedi.conf and DB\n";
	print "-d opt	b=basic debug,d=DB queries,s=sysload,c=clilog,v=vardumps\n";
	print "-D	Enters debug section in nedi.pl\n";
	print "-f 	Use discovered IPs as devicenames (useful, if they're not unique)\n";
	print "-F 	Use FQDN for device. Allows \".\" in dev name (can mess up links!)\n";
	print "-l x	Limit discovery to x devices\n";
	print "-n 	Don't resolve node names via DNS\n";
	print "-P x	Ping device (with x packets) prior SNMP (faster with IP ranges)\n";
	print "-v	Verbose output\n";
	print "-V ver	Prefer this SNMP version over those in nedi.conf and DB\n\n";
	print "Actions (applied to each found device) ------------------------------------\n";
	print "-b|Bx	Backup running config|also write files, delete old versions if > x\n";
	print "-c file	Send commands in cli/file (preceed with diff- to create events on\n";
	print "	changes) and write output to cli/file-ip.log\n";
	print "-k	Store ssh key in ~/.ssh/known_hosts\n";
	print "-K	Delete stored key from ~/.ssh/known_hosts\n";
	print "-Y opt	Add to inventory n=new s=snmp|a=all m=modules u=update only\n";
	print "\tl=use location c=use contact\n";
	print "-S opt	Skip s=sys v=vlans m=modules g=devrrd A=arp F=fwd p=dprot G=toprrd\n";
	print "	j=adr i=IFinf u=spd/dup t=trf e=err d=dscrd b=bcast w=poe a=adm o=opr\n";
	print "	W=web l=APloc L=linkcalc P=policies X=existing dev (except -A)\n";
	print "	retire: N=nodes, T=IF/IPtrack D=dns I=IPs\n";
	print "-t opt	Test only s=seeds a=access i=info p=ping (with -P)\n";
	print "-T 	Install new device\n";
	print "-W 	Retry SNMP writeaccess\n";
	print "-x file	Execute file and pass new or existing,name,IP,snmp version\n";
	print "	community,sysobjid,description as arguments for devices and\n";
	print "	new or existing,IP,TCP ports,UDP ports,type,OS for nodes (with -s)\n";

	print "Other Actions -------------------------------------------------------------\n";
	print "-i u pw	Initialize database (-I deletes all RRDs & Configs)\n";
	print "	u=nodrop overwrite existing DB as nediuser, u=updatedb adapts scheme\n";
	print "	If user is set to 'nodrop', an empty DB can be used without admin rights\n";
	print "-N opt	ip or range resolve names, arpwatch(-iponly)=import info (w/o name)\n";
	print "-s opt	(Requires -N or -O) scan nodes opt=id,tcp-ports (e.g. id,23,445)\n";
	print "-y	Show supported devices based on .def files (in sysobj)\n";
	print "-Z pw	Returns secured pw, which can be used in nedi.conf with 'usrsec'\n\n";
	print "Output Legend =============================================================\n";
	print "Statistics (lower case letters):\n";
	print "i#	Interfaces\n";
	print "j#	IF IP addresses\n";
	print "a#	ARP entries\n";
	print "f#	Forwarding entries\n";
	print "m#	Modules\n";
	print "v#	Vlans\n";
	print "c#	Config lines returned (-c, -b or -Bx options)\n";
	print "l#	Lines returned from CLI commands (-c option)\n";
	print "x#	Return status from executed command (-x option)\n";
	print "p|r|o#/#	Protocol, route or OUI queueing (# added/# done already)\n";
	print "b#	Border hits\n\n";
	print "Warnings (upper case letters) ---------------------------------------------\n";
	print "Jx	IF Addresses (a=IP m=Mask 6=ipv6 c=cisco-ip6 p=prefix v=vrf)\n";
	print "Ax	ARP (n=n2m p=n2p 6=ipv6 c=cisco-ip6 i=no IF index s=no SNMP)\n";
	print "Bx	Backup f=fetched n=new u=update w=write e=empty d=delete\n";
	print "Cx	CLI c=connect d=disabled u=user l=login e=enable i=impossible m=cmd\n";
	print "Dx	Discover p=poe a=IP l=LLDP c=CDP e=EDP f=FDP r=rte\n";
	print "	o=IP127/0 L=loop x=IFix d=dupnbr i=noinv m=non-med n=nbrname\n";
	print "Fx(#)	Forwarding table (i=IF p=Port #=vlan x=idx w=wlan)\n";
	print "Ix	Interface d=desc n=name t=type S/s=HC/speed m=mac a=admin p=oper\n";
	print "	c=dscrd b=bcst I/O=HCtrf i/o=trf e=err l=alias x=duplx v=vlan w=poe\n";
	print "Kx	SSH keyscan e=error, s=scanned, r=removed\n";
	print "Mx	Modules t=slot d=desc c=class h=hw f=fw s=sw #=SN m=model l=loc\n";
	print "Px	Policy w=write cmd  \n";
	print "Rx	RRD d=mkdir u=update s=make sys i=make IF t=make top n=IF name\n";
	print "Sx	System W=write n=name c=con l=Loc v=SRV #=SN b=BI g=grp o=mode\n";
	print "	v=vlan y=type f=cfg w=PoE RRD:u=CPU m=Mem t=Tmp s=cus\n";
	print "Wx	Wireless controller ap=Access Point ra=Radio if=interface cl=client\n";
	print "---------------------------------------------------------------------------\n";
	print "NeDi ${main::VERSION}p3 (C) 2001-2015 Remo Rickli & contributors\n\n";
	exit;
}
