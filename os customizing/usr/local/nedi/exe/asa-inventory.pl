#!/usr/bin/perl
=pod

=head1 PROGRAM asa-inventory.pl

A callout example for NeDi.

=head1 SYNOPSIS

nedi.pl -c shinv -x exe/asa-inventory.pl

=head2 DESCRIPTION

This is an example to combine device scripting and callout.
Cisco ASA do not show powersupplies via SNMP. This is a workaround:

1) Create a textfile cli/shinv containing "show inventory"

2) Run NeDi as shown above to parse the resulting shinv.log file

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

use strict;
use warnings;
no warnings qw(once);

use vars qw(%opt $p $now $days $from);

$opt{'v'} = 1;
$opt{'d'} = 'ds';
use Data::Dumper;											

$now = time;

$p   = $0;
$p   =~ s/(.*)\/(.*)/$1/;
if($0 eq $p){$p = "."};

$misc::dbname = $misc::dbhost = $misc::dbuser = $misc::dbpass = '';

require "$p/inc/libmisc.pm";										# Use the miscellaneous nedi library
require "$p/inc/libdb.pm";										# Use the DB function library

&misc::ReadConf();

my $ddir = $ARGV[1];
$ddir =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;					# Translate devicename to valid filename

if(-e "$p/cli/$ddir/shinv.log"){
	open  ("INV", "$p/cli/$ddir/shinv.log");
}else{
	die "Can't find $p/cli/$ddir/shinv.log: $!\n";
}
my @inv = <INV>;
close("INV");

&db::Connect($misc::dbname,$misc::dbhost,$misc::dbuser,$misc::dbpass,1);
my $next = 0;
foreach my $l (@inv){
	$next++;
	if($l =~ /^NAME:/i){
		if($inv[$next] =~ /^PID:/){
			my ($slot,$desc) =  split(', DESCR: ', substr($l,7) );
			my $class = ($slot =~ /power supply/)?6:1;
			my ($model,$sn) =  split(', VID:[\w\s]+, SN:', substr($inv[$next],5) );
			$sn = misc::Strip($sn);
			my $dev = &db::Select('devices','','device',"serial = '$sn'");
			my $mod = &db::Select('modules','','device',"serial = '$sn'");
			&db::Insert(	'modules','device,slot,model,moddesc,serial,modidx,modclass',
					$db::dbh->quote($ARGV[1]).",'".misc::Strip($slot)."','".misc::Strip($model)."','".misc::Strip($desc)."','$sn',$next,$class") unless $dev or $mod;
		}
	}
}
&db::Disconnect();
