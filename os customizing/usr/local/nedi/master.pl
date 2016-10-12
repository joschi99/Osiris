#!/usr/bin/perl

=pod

=head1 PROGRAM master.pl

Daemon for the Master NeDi console to collect information from NeDi agents via

https://nediagent/query.php?p=adminpass&q?=query

=head1 SYNOPSIS

master.pl [-D -v -d -u agentlist]

=head2 DESCRIPTION

=head2 LICENSE

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

=head2 AUTHORS

Remo Rickli & NeDi Community

Visit http://www.nedi.ch for more information.

=cut

use strict;
use warnings;
no warnings qw(once);

use Getopt::Std;
use LWP::UserAgent;
use Time::HiRes;

use vars qw($now $p %opt %dev %mon %usr %msgq);
$misc::pause = $misc::doip = $misc::todo = $misc::login = $misc::seedini = "";

getopts('Dd:tu:v',\%opt) || &HELP_MESSAGE;
if(!defined $opt{'d'}){$opt{'d'} = ''}									# Avoid warnings if unused
if(!defined $opt{'S'}){$opt{'S'} = ''}									# Avoid warnings if unused

select(STDOUT); $| = 1;											# Disable buffering

$p   = $0;
$p   =~ s/(.*)\/(.*)/$1/;
if($0 eq $p){$p = "."};
$now = time;

require "$p/inc/libmisc.pm";										# Use the miscellaneous nedi library
require "$p/inc/libmon.pm";										# Use the Monitoring lib for notifications
require "$p/inc/libdb.pm";										# Use the DB function library

misc::ReadConf();

if ($opt{'D'}) {											# Daemonize or...
	misc::Daemonize();
}
while(1){
	$now = time;
	misc::Prt("\nMaster " . localtime($now) . " --------------\n");
	db::Connect($misc::dbname,$misc::dbhost,$misc::dbuser,$misc::dbpass);
	my $ndev = db::ReadDev();
	my $nsat = misc::InitSeeds(1);
	my $ntgt = &mon::InitMon();
	foreach my $id (@misc::todo){
		my $ip = $misc::doip{$id};
		my $na = $misc::seedini{$ip}{rv};

		misc::Prt("\nQERY:$misc::seedini{$ip}{rc}\@$ip ");
		if(!$misc::seedini{$ip}{rc}){
			my $msg = "No user for $ip, add one in agentlist";
			misc::Prt("- $msg\n");
			db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','mas','',".$db::dbh->quote($msg));
		}elsif(!exists $misc::login{$misc::seedini{$ip}{rc}}{pw}){
			my $msg = "No password for $misc::seedini{$ip}{rc}, add one in nedi.conf";
			misc::Prt("- $msg\n");
			db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','mas','',".$db::dbh->quote($msg));
		}else{
			$dev{$na}{ip} = $ip;
			$dev{$na}{sn} = $id;
			$dev{$na}{ty} = "NeDi Agent";
			$dev{$na}{ven}= "Unkown";
			$dev{$na}{dg} = "Unmonitored";
			$dev{$na}{sv} = 1;
			$dev{$na}{rv} = 4;								# Make it a pseudo SNMP device
			$dev{$na}{lo} = $misc::seedini{$ip}{lo};
			$dev{$na}{co} = $misc::seedini{$ip}{co};
			$dev{$na}{ic} = '../32/qmrk';
			$dev{$na}{us} = $misc::seedini{$ip}{rc};
			$dev{$na}{fs} = $now unless $dev{$na}{fs};

			my $err  = '';
			if(exists $mon{$na} and $mon{$na}{te}){
				my $evq = "level > 150 ORDER BY id DESC LIMIT 1";
				if($mon{$na}{up}){
					$evq = "level > 150 and id > $mon{$na}{up} ORDER BY id DESC";
				}
				misc::Prt("-> $evq\n");
				my $start = Time::HiRes::time;
				my $ses = LWP::UserAgent->new( ssl_opts => { SSL_verify_mode => 'SSL_VERIFY_NONE'} );
				my $res = $ses->post( "$mon{$na}{te}://$ip/$mon{$na}{to}query.php",
					[
						'u' => $misc::seedini{$ip}{rc},
						'p' => $misc::login{$misc::seedini{$ip}{rc}}{pw},
						'm' => 'csv',
						't' => 'events',
						'q' => $evq,
					]
				);
				if( $res->is_success ){
					my $latency = int(1000 * (Time::HiRes::time - $start) );
					my @l = split(/\n/,$res->content);
					my @f = split(/;;/,shift @l);
					db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','masl',".$db::dbh->quote($na).",'Latency ${latency}ms exceeds threshold of ${misc::latw}ms'" ) if($latency > $misc::latw) and !$main::opt{'t'};

					if($f[0] =~ /^ERR :/){
						$err = $f[0];
						misc::Prt("$err\n");
						$err =~ s/^ERR ://;
						db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','mase',".$db::dbh->quote($na).",".$db::dbh->quote($err) );
					}else{
						$dev{$na}{ls} = $now;
						$dev{$na}{de} = "$f[1], $f[0], $f[3], $f[4], API Version:$f[5]";
						$dev{$na}{os} = $f[0];
						$dev{$na}{bi} = "$f[2]";
						$dev{$na}{dg} = "Active";
						$dev{$na}{sv} = 64;
						$dev{$na}{rc} = $mon{$na}{te};

						if($f[0] =~ /^win/i){
							$dev{$na}{ic} = '../32/nwin';
						}elsif($f[0] =~ /^freebsd/i){
							$dev{$na}{ic} = '../32/nfbs';
						}elsif($f[0] =~ /^openbsd/i){
							$dev{$na}{ic} = '../32/nobs';
						}elsif($f[3] =~ /centos/i){
							$dev{$na}{ic} = '../32/ncos';
						}elsif($f[3] =~ /debian/i){
							$dev{$na}{ic} = '../32/ndeb';
						}elsif($f[3] =~ /redhat/i){
							$dev{$na}{ic} = '../32/nred';
						}elsif($f[3] =~ /suse/i){
							$dev{$na}{ic} = '../32/nsus';
						}elsif($f[3] =~ /ubuntu/i){
							$dev{$na}{ic} = '../32/nubu';
						}elsif($f[0] =~ /^linux/i){
							$dev{$na}{ic} = '../32/nlin';
						}
						my $ok = ++$mon{$na}{ok};
						my $latmax = ($latency > $mon{$na}{lm})?$latency:$mon{$na}{lm};	# Update max if higher than previous
						my $latavg = sprintf("%.0f",( ($ok - 1) * $mon{$na}{la} + $latency)/$ok);# This is where school stuff comes in handy (sprintf to round)

						foreach my $ev (@l){
							my @f = split(/;;/, $ev);
							$mon{$na}{up} = $f[0] if $f[0] > $mon{$na}{up};
							db::Insert('events','level,time,source,class,device,info',"$f[1],$f[2],'$f[3]','$f[5]',".$db::dbh->quote($na).",".$db::dbh->quote($f[4]) );
						}
						misc::Prt("ANSR:$dev{$na}{rc}\tEvent#$mon{$na}{up}\t$dev{$na}{bi}, $dev{$na}{de}\t\n");
						db::Update('monitoring',"status=0,lastok=$now,uptime=$mon{$na}{up},ok=$ok,latency=$latency,latmax=$latmax,latavg=$latavg","name =".$db::dbh->quote($na) );
					}
				}else{
					$err = "Events - ".$res->status_line;
					misc::Prt("ERR :$err\n");
					db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','masc',".$db::dbh->quote($na).",".$db::dbh->quote($err) );
				}

				misc::Prt("QERY:$misc::seedini{$ip}{rc}\@$ip get incidents where time = 0\n");
				$res = $ses->post( "$mon{$na}{te}://$ip/$mon{$na}{to}query.php",
					[
						'u' => $misc::seedini{$ip}{rc},
						'p' => $misc::login{$misc::seedini{$ip}{rc}}{pw},
						'm' => 'csv',
						't' => 'incidents',
						'q' => 'time = 0',
					]
				);
				if( $res->is_success ){
					my @l = split(/\n/,$res->content);
					my @f = split(/;;/,shift @l);
					if($f[0] =~ /^ERR :/){
						$err = $f[0];
						misc::Prt("$err\n");
						$err =~ s/^ERR ://;
						db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','masi',".$db::dbh->quote($na).",".$db::dbh->quote($err) );
					}else{
						my %unack = ();
						my $ni = my $ui = my $di = 0;
						foreach my $inc (@l){
							my @f = split(/;;/, $inc);
							$unack{"$f[2];;$f[4];;$na"}++;
							my $dbinc = db::Select('incidents','','*',"name='$f[2]' AND startinc=$f[4] AND device='$na'");
							if( $dbinc ){					# Remote incidents already on master?
								if($f[5] != $dbinc->[0][5]){		# Acknowledged on agent, but not yet on master?
									db::Update('incidents',"endinc=$f[5]","id=$dbinc->[0][0]");
									$ui++;
								}
							}else{						# Create new incident on master
								db::Insert('incidents','level,name,deps,startinc,endinc,usrname,time,grp,comment,device',"$f[1],'$f[2]',$f[3],$f[4],$f[5],'$f[6]',0,1,'',".$db::dbh->quote($na) );
								$ni++;
							}
						}
						my $dbincs = db::Select('incidents','','*',"device='$na'");# Check master incidents against remotes...
						if( $dbincs ){						# $dbincs is empty string, if no result
							foreach my $dbi ( @$dbincs ) {
								if(!exists $unack{"$dbi->[2];;$dbi->[4];;$dbi->[10]"}){	# Incident not unacknowledged on agent anymore
									my $d = db::Delete('incidents',"name='$dbi->[2]' AND startinc=$dbi->[4] AND device='$dbi->[10]'");
									$di += $d;
								}
							}
						}
						misc::Prt("ANSR:$ni new incidents, $ui updated and $di deleted\n");# TODO refactor to commit at end for scalability?
					}
				}else{
					$err = "Incidents - ".$res->status_line;
					misc::Prt("ERR :$err\n");
					db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','masc',".$db::dbh->quote($na).",".$db::dbh->quote($err) );
					my $st = ++$mon{$na}{st};
					my $lo = ++$mon{$na}{lo};
					db::Update('monitoring',"status=$st,lost=$lo","name =".$db::dbh->quote($na) ) unless $main::opt{'t'};
				}

			}else{
				misc::Prt("Not monitored right now\n");
			}

			db::WriteDev($na) unless $err or $main::opt{'t'};
		}
	}
	db::Commit();
	misc::TopRRD();

	my $took = time - $now;
	if ($misc::pause > $took){
		my $sl = $misc::pause - $took;
		misc::Prt("\nTook ${took}s, sleeping ${sl}s\n\n");
		sleep($sl);
	}else{
		misc::Prt("\nTook ${took}s, no time to pause!\n\n");
		db::Insert('events','level,time,source,class,device,info',"150,$now,'Master','mas','','Master took ${took}s, increase pause!'");
	}

	db::Disconnect();
}

=head2 FUNCTION HELP_MESSAGE()

Display some help

B<Options> -

B<Globals> -

B<Returns> -

=cut
sub HELP_MESSAGE{
	print "\n";
	print "usage: master.pl <Option(s)>\n\n";
	print "---------------------------------------------------------------------------\n";
	print "Options:\n";
	print "-v	verbose output\n";
	print "-d opt	debug output\n";
	print "-u file	Use specified agentlist\n";
	print "-t	Test only, but don't write anything\n";
	print "-D	Run as daemon\n\n";
	print "(C) 2001-2015 Remo Rickli (and contributors)\n\n";
	exit;
}
