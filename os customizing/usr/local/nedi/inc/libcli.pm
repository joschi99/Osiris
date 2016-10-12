=pod

=head1 LIBRARY
libcli-iopty.pm

Net::Telnet/IO::Pty based Functions

Only needs libio-pty-perl and relies on the ssh binary for secured connections

Ubuntu install hint: apt-get install libio-pty-perl

PrepDev() tries to find credentials on new devices (or if cliport set to 0).
After that BridgeFwd() reads MAC table on supported switches if PrepDev()
confirmed support (only IOS at the time).

Config() fetches the configuration and stores the "interesting" part.
All functions use the universal EnableDev() to get into enable mode.
The $obj->get is used to avoid problems with ^M and Escape sequences...

-d option shows more details on errors and pre-match and actual matches and
also creates input.log and output.log (open extra terminals with tail -f
on them to see what's happening right away)

=head2 AUTHORS

Remo Rickli & NeDi Community

=cut

package cli;
use warnings;

use Net::Telnet;

eval 'use IO::Pty();';
if ($@){
	$misc::usessh = 'never';									# Keeps nedi working without io::pty
	&misc::Prt("CLI :IO-Pty not available\n");
}else{
	&misc::Prt("CLI :IO-Pty loaded\n");
}

use vars (%cmd);

=head2 Important Variables

$clipause: Increase if devices hang between connects


$cmd: Holds commands, expected prompts and other OS specific parameters needed to handle CLI access.

=over

=item *
ropr: Read only prompt, if no readonly prompt is used set to some string which won't occur otherwise.

=item *
enpr: Enable prompt

=item *
enab: Enable command

=item *
conf: Command to enter config mode

=item *
shcf: Command to display config

=item *
cfto: Config timeout, wait additional seconds for config (default 10)

=item *
cfst: Match start of config (use a . to match anything)

=item *
page: Command to disable paging

=item *
dfwd: Show dynamic bridge forwarding table (only IOS & CatOS)

=item *
sfwd: Show static bridge forwarding table (only IOS & CatOS)

=item *
arp: Show arp table (ASA, Nexus)

=item *
wsnr: Command for wireless SNR levels (only IOS-ap, but not finished yet)

=back

=head2 Tips & Tricks

=head3 OS Modes


 OS        Read    Enable        Config
 =============================================
 IOS       Name>   Name#         Name(config)#
 CatOS     Name>   Name>(enable) -
 Omnistack -       Name#
 ProCurve  Name>   Name#         Name(config)#
 Comware   <Name>  -             [Name]
 JunOS     -       Name>         Name#
 ESX       -       Name#         -
 etc.

=head3 Enable VMware ESX support

Enable CDP on vSwitch1 (ESX name needs to be in DNS, if you wish to discover it!):

esxcfg-vswitch -B both vSwitch1

Enable SNMP:

esxcli system snmp set -L "CH;Zurich;Bahnhofstr.1;UG;RZ;Rack1;5;1" -c public
esxcli system snmp set -e yes

or

vi /etc/vmware/snmp.xml

<enabled>true</enabled>

<communities>public</communities>

/sbin/services.sh restart

Edit $cmd{'ESX'}{'shcf'} to adjust path to *.vmx files for backup.

=cut

our $clipause = 1;

#TODO assign the 'cfst' strings Software|# What:| General System Information)|\*\*\* CORE|<config>|\sversion

# Accton
$cmd{'Edgecore'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Edgecore'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Edgecore'}{'enab'} = 'enable';
$cmd{'Edgecore'}{'page'} = 'terminal datadump';
#$cmd{'Edgecore'}{'page'} = 'terminal length 0'; v1.4?
$cmd{'Edgecore'}{'shcf'} = 'show run';
$cmd{'Edgecore'}{'cfst'} = '.';
#$cmd{'Edgecore'}{'cfst'} = '!'; v1.4?
$cmd{'Edgecore'}{'cfen'}  = '^end$';
$cmd{'Edgecore'}{'conf'} = 'configure';
$cmd{'Edgecore'}{'exit'} = 'end';

# Aerohive
$cmd{'Aerohive'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Aerohive'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Aerohive'}{'enab'} = 'enable';
$cmd{'Aerohive'}{'shcf'} = 'show config running';
$cmd{'Aerohive'}{'page'} = 'console page 0';
$cmd{'Aerohive'}{'arp'}  = 'show arp-cache';
$cmd{'Aerohive'}{'strt'} = '.';

# Alcatel-Lucent
$cmd{'ISAM'}{'ropr'} = '^([\w.:@-_]+>#)\s$';
$cmd{'ISAM'}{'enpr'} = '.*>.*#.*';
$cmd{'ISAM'}{'enab'} = 'environment inhibit-alarms print no-more';
#$cmd{'ISAM'}{'shcf'} = 'info configure'; #takes very long; currently disable
$cmd{'ISAM'}{'cfst'} = '.';
$cmd{'ISAM'}{'page'} = 'environment mode batch';

$cmd{'Omnistack'}{'ropr'} = 'GitsDoNid';
$cmd{'Omnistack'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Omnistack'}{'enab'} = 'enable';
$cmd{'Omnistack'}{'shcf'} = 'show configuration snapshot';
$cmd{'Omnistack'}{'cfst'} = '.';

#Aruba
$cmd{'ArubaOS'}{'ropr'} = '(.+?)>\s?$';
$cmd{'ArubaOS'}{'enpr'} = '(.+?)#\s?$';
#$cmd{'ArubaOS'}{'ropr'} = '[\w+().-]+>\s?$';
#$cmd{'ArubaOS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'ArubaOS'}{'enab'} = 'enable';
$cmd{'ArubaOS'}{'page'} = 'no paging';
$cmd{'ArubaOS'}{'shcf'} = 'show running-config';
$cmd{'ArubaOS'}{'cfst'} = '^version';
$cmd{'ArubaOS'}{'dfwd'} = 'show mac-address-table';

# AVAYA
$cmd{'Nortel'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Nortel'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Nortel'}{'enab'} = 'enable';
$cmd{'Nortel'}{'page'} = 'terminal length 0';
$cmd{'Nortel'}{'shcf'} = 'show run';
$cmd{'Nortel'}{'cfst'} = '.';

# BROCADE
$cmd{'Ironware'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Ironware'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Ironware'}{'enab'} = 'enable';
$cmd{'Ironware'}{'page'} = 'skip-page-display';
$cmd{'Ironware'}{'shcf'} = 'show run';
$cmd{'Ironware'}{'cfst'} = '.';

$cmd{'Vyatta'}{'ropr'} = 'GitsDoNid';
$cmd{'Vyatta'}{'enpr'} = '[\w+():.-~]+[#\$]\s?$';
$cmd{'Vyatta'}{'shcf'} = 'sh configuration | no-more';
$cmd{'Vyatta'}{'cfst'} = '.';

# CIENA
$cmd{'LEOS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'LEOS'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'LEOS'}{'page'} = 'system shell set more off';
$cmd{'LEOS'}{'shcf'} = 'configuration show';
$cmd{'LEOS'}{'cfst'} = '.';

$cmd{'SAOS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'SAOS'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'SAOS'}{'page'} = 'system shell set more off';
$cmd{'SAOS'}{'shcf'} = 'configuration show';
$cmd{'SAOS'}{'cfst'} = '.';

# CISCO
$cmd{'IOS'}{'ropr'} = '[\w+().-]+>\s?$';								# New approach to avoid problems with # in banners
$cmd{'IOS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS'}{'enab'} = 'enable';
$cmd{'IOS'}{'page'} = 'terminal length 0';
$cmd{'IOS'}{'more'} = ' --More-- ';									# Fallback, if page didn't work
$cmd{'IOS'}{'shcf'} = 'show run';
$cmd{'IOS'}{'cfto'} = 30;
$cmd{'IOS'}{'cfst'} = '^version';
$cmd{'IOS'}{'cfen'}  = '^end$';
$cmd{'IOS'}{'dfwd'} = 'show mac address-table | e CPU|Switch|Router|/.*,';				# tx colejv
$cmd{'IOS'}{'conf'} = 'conf t';
$cmd{'IOS'}{'end'}  = 'end';
$cmd{'IOS'}{'save'} = 'write mem';
$cmd{'IOS'}{'ifct'} = 'interface';
$cmd{'IOS'}{'ifds'} = 'shut';
$cmd{'IOS'}{'ifes'} = 'no shut';
$cmd{'IOS'}{'ifdp'} = 'power inline never';
$cmd{'IOS'}{'ifep'} = 'power inline auto';
$cmd{'IOS'}{'tech'} = "show tech-support | redirect tftp://%NeDi%/%FILE%";
$cmd{'IOS'}{'tecd'} = "test cable-diagnostics tdr interface";						# TODO Will be available in Nodes-Status some day?
$cmd{'IOS'}{'shcd'} = "show cable-diagnostics tdr interface";

$cmd{'IOS-old'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-old'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-old'}{'enab'} = 'enable';
$cmd{'IOS-old'}{'page'} = 'terminal length 0';
$cmd{'IOS-old'}{'shcf'} = 'show run';
$cmd{'IOS-old'}{'conf'} = 'conf t';
$cmd{'IOS-old'}{'end'}  = 'end';
$cmd{'IOS-old'}{'cfst'} = '^version';
$cmd{'IOS-old'}{'cfen'}  = '^end$';
$cmd{'IOS-old'}{'dfwd'} = 'show mac-address-table dyn';							# Older IOS, tx Rufer & Eviltrooper
$cmd{'IOS-old'}{'sfwd'} = 'show port-security addr';							# tx Duane

$cmd{'IOS-rtr'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-rtr'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-rtr'}{'enab'} = 'enable';
$cmd{'IOS-rtr'}{'page'} = 'terminal length 0';
$cmd{'IOS-rtr'}{'shcf'} = 'show run';
$cmd{'IOS-rtr'}{'conf'} = 'conf t';
$cmd{'IOS-rtr'}{'end'}  = 'end';
$cmd{'IOS-rtr'}{'cfst'} = '^version';
$cmd{'IOS-rtr'}{'cfen'}  = '^end$';
$cmd{'IOS-rtr'}{'dfwd'} = 'show mac-address-table dyn';							# Some router are like IOS-old
$cmd{'IOS-rtr'}{'sfwd'} = 'show port-security addr';							# tx Duane

$cmd{'IOS-ap'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-ap'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-ap'}{'enab'} = 'enable';
$cmd{'IOS-ap'}{'page'} = 'terminal length 0';
$cmd{'IOS-ap'}{'shcf'} = 'show run';
$cmd{'IOS-ap'}{'conf'} = 'conf t';
$cmd{'IOS-ap'}{'end'}  = 'end';
$cmd{'IOS-ap'}{'cfst'} = '^version';
$cmd{'IOS-ap'}{'cfen'} = '^end$';
$cmd{'IOS-ap'}{'dfwd'} = 'show bridge | exclude \*\*\*';
$cmd{'IOS-ap'}{'wsnr'} = 'show dot11 statistics client-traffic'; 					# Credits to HB9DDO

$cmd{'IOS-wlc'}{'ropr'} = '\(Cisco Controller\) >$';
$cmd{'IOS-wlc'}{'enpr'} = $cmd{'IOS-wlc'}{'ropr'};
$cmd{'IOS-wlc'}{'page'} = 'config paging disable';
$cmd{'IOS-wlc'}{'shcf'} = 'show run-config commands';
#new $cmd{'IOS-wlc'}{'shcf'} = 'show run-config startup-commands';
$cmd{'IOS-wlc'}{'cfst'} = '.';

$cmd{'IOS-pix'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-pix'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-pix'}{'enab'} = 'enable';
$cmd{'IOS-pix'}{'page'} = 'no pager';									# PIX 6.3
$cmd{'IOS-pix'}{'shcf'} = 'show run';
$cmd{'IOS-pix'}{'cfst'} = '^PIX Version';
$cmd{'IOS-pix'}{'arp'} = 'show arp';

$cmd{'IOS-asa'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-asa'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-asa'}{'enab'} = 'enable';
$cmd{'IOS-asa'}{'page'} = 'no terminal pager';								# PIX 8.0.3, ASA 8.2
#$cmd{'IOS-asa'}{'shcf'} = 'show run';
$cmd{'IOS-asa'}{'shcf'} = 'more system:running-config';							# displays plain-text keys, tx uestueno
$cmd{'IOS-asa'}{'cfst'} = '^PIX|ASA';
$cmd{'IOS-asa'}{'cfen'}  = '^: end$';
$cmd{'IOS-asa'}{'arp'} = 'show arp';

$cmd{'IOS-fv'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-fv'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-fv'}{'enab'} = 'enable';
$cmd{'IOS-fv'}{'page'} = 'terminal pager 0';
$cmd{'IOS-fv'}{'shcf'} = 'show run';
$cmd{'IOS-fv'}{'cfst'} = '^FWSM';

$cmd{'IOS-css'}{'ropr'} = 'GitsDoNid';
$cmd{'IOS-css'}{'enpr'} = '/# $/';									# Thanks to kai
$cmd{'IOS-css'}{'shcf'} = 'show run';
$cmd{'IOS-css'}{'cfst'} = '^!Generated' ;

$cmd{'IOS-xr'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'IOS-xr'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'IOS-xr'}{'enab'} = 'enable';
$cmd{'IOS-xr'}{'shcf'} = 'show run';
$cmd{'IOS-xr'}{'conf'} = 'conf t';
$cmd{'IOS-xr'}{'end'} = 'end';
$cmd{'IOS-xr'}{'cfst'} = '^!! IOS XR Configuration';
$cmd{'IOS-xr'}{'cfen'}  = '^end$';
$cmd{'IOS-xr'}{'page'} = 'terminal length 0';
$cmd{'IOS-xr'}{'dfwd'} = '';
$cmd{'IOS-xr'}{'sfwd'} = 'show port-security addr';

$cmd{'CatOS'}{'ropr'} = '(.+)>\s?$';
$cmd{'CatOS'}{'enpr'} = '(.+)>\s?\(enable\)\s?$';
$cmd{'CatOS'}{'enab'} = 'enable';
$cmd{'CatOS'}{'page'} = 'set length 0';
$cmd{'CatOS'}{'dfwd'} = 'show cam dyn';
$cmd{'CatOS'}{'shcf'} = 'show conf';
$cmd{'CatOS'}{'cfst'} = '^begin';

$cmd{'CSBS'}{'ropr'} = 'GitsDoNid';									# tx Ronny
$cmd{'CSBS'}{'enpr'} = '(.+?)#$';
$cmd{'CSBS'}{'page'} = 'terminal datadump';
$cmd{'CSBS'}{'shcf'} = 'show run';
$cmd{'CSBS'}{'cfst'} = '.';

$cmd{'NXOS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'NXOS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'NXOS'}{'enab'} = 'enable';
$cmd{'NXOS'}{'page'} = 'terminal length 0';
$cmd{'NXOS'}{'dfwd'} = 'sh mac address-table dyn';
$cmd{'NXOS'}{'shcf'} = 'show running-config';
$cmd{'NXOS'}{'cfst'} = '^begin|running-config';
$cmd{'NXOS'}{'arp'} = 'sh ip arp vrf all';

$cmd{'NXUCS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'NXUCS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'NXUCS'}{'enab'} = 'enable';
$cmd{'NXUCS'}{'page'} = 'terminal length 0';
$cmd{'NXUCS'}{'pfwd'} = 'connect nxos';
$cmd{'NXUCS'}{'dfwd'} = 'sh mac address-table dyn';
$cmd{'NXUCS'}{'shcf'} = 'show configuration';
$cmd{'NXUCS'}{'cfst'} = '.';

# Dell
$cmd{'DPC'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'DPC'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'DPC'}{'enab'} = 'enable';
$cmd{'DPC'}{'page'} = 'terminal datadump';
$cmd{'DPC'}{'shcf'} = 'show run';
$cmd{'DPC'}{'cfst'} = '.';

$cmd{'DPCN'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'DPCN'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'DPCN'}{'enab'} = 'enable';
$cmd{'DPCN'}{'page'} = 'terminal length 0';
$cmd{'DPCN'}{'shcf'} = 'show run';
$cmd{'DPCN'}{'cfst'} = '.';

$cmd{'SonicOS'}{'ropr'} = 'GitsDoNid';
$cmd{'SonicOS'}{'enpr'} = '[\w@+().-]+>\s?$';
$cmd{'SonicOS'}{'page'} = 'no cli pager session';
$cmd{'SonicOS'}{'shcf'} = 'show current-config';
$cmd{'SonicOS'}{'cfst'} = 'Zones Configuration|administration';
$cmd{'SonicOS'}{'arp'} 	= 'show arp cache';

# EXTREME
$cmd{'Xware'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Xware'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Xware'}{'enab'} = 'enable';
$cmd{'Xware'}{'page'} = 'disable clipaging session';
$cmd{'Xware'}{'shcf'} = 'show configuration';
$cmd{'Xware'}{'cfst'} = 'Software Version';

$cmd{'XOS'}{'ropr'} = '[\w.-]+\s>\s?$';
$cmd{'XOS'}{'enpr'} = '[\w.-]+\s#\s?$';
$cmd{'XOS'}{'enpr'} = '[\w\s+().-]+#\s?$';
$cmd{'XOS'}{'enab'} = 'enable';
$cmd{'XOS'}{'shcf'} = 'show configuration';
$cmd{'XOS'}{'cfst'} = '^#';
$cmd{'XOS'}{'page'} = 'disable clipaging';

$cmd{'EOS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'EOS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'EOS'}{'enab'} = 'enable';
$cmd{'EOS'}{'page'} = 'terminal length 0';
$cmd{'EOS'}{'shcf'} = 'show run';
$cmd{'EOS'}{'cfst'} = '!PLATFORM';

$cmd{'EOSB2'}{'ropr'} = 'GitsDoNid';
$cmd{'EOSB2'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'EOSB2'}{'shcf'} = 'show config';
$cmd{'EOSB2'}{'cfst'} = 'begin';

# Fortigate
$cmd{'FortiOS'}{'ropr'} = 'GitsDoNid';
$cmd{'FortiOS'}{'enpr'} = '[\w+().-]+\$\s?$';
$cmd{'FortiOS'}{'more'} = '--More-- ';
$cmd{'FortiOS'}{'shcf'} = 'show full-configuration';
$cmd{'FortiOS'}{'cfst'} = '^config';

# JUNIPER
$cmd{'JunOS'}{'ropr'} = 'GitsDoNid';
$cmd{'JunOS'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'JunOS'}{'enab'} = 'enable';
$cmd{'JunOS'}{'shcf'} = 'show configuration | no-more';
#$cmd{'JunOS'}{'dfwd'} = 'show ethernet-switching table | no-more';					# Sneuser: for switches only
$cmd{'JunOS'}{'cfst'} = '^## Last commit';

$cmd{'NetScreen'}{'ropr'} = 'GitsDoNid';
$cmd{'NetScreen'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'NetScreen'}{'enab'} = 'enable';
$cmd{'NetScreen'}{'page'} = 'set console page 0';
$cmd{'NetScreen'}{'shcf'} = 'get config';
$cmd{'NetScreen'}{'cfst'} = '^Total';

# Hirschmann
$cmd{'Hirschmann'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Hirschmann'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Hirschmann'}{'enab'} = 'enable';
$cmd{'Hirschmann'}{'shcf'} = 'show run';
$cmd{'Hirschmann'}{'cfst'} = '!Current';

# HP
$cmd{'Comwar3'}{'ropr'} = 'GitsDoNid';
$cmd{'Comwar3'}{'enpr'} = '[\w+()<\[.-]+[>\]]\s?(\n%.+)?$';						# Sends a % line after login prompt!
$cmd{'Comwar3'}{'enab'} = 'super';
$cmd{'Comwar3'}{'more'} = '  ---- More ----';								# Experimental, to get old 3Com switches backed up
$cmd{'Comwar3'}{'shcf'} = 'display current';
$cmd{'Comwar3'}{'cfst'} = '#';

$cmd{'Comware'}{'ropr'} = 'GitsDoNid';
$cmd{'Comware'}{'enpr'} = '[\w+()<\[.-]+[>\]]\s?$';
$cmd{'Comware'}{'enab'} = 'super';
$cmd{'Comware'}{'page'} = 'screen-length disable';
$cmd{'Comware'}{'shcf'} = 'display current';
$cmd{'Comware'}{'conf'} = 'sys';
$cmd{'Comware'}{'end'}  = 'quit';
$cmd{'Comware'}{'cfst'} = '.';

$cmd{'ProCurve'}{'ropr'} = '(\x1b\[[;\?0-9A-Za-z]+)+[\w\s()\'+.-]+>\s?(\x1b\[[;\?0-9A-Za-z]+)+$';		# Match nasty Escapes! ProCurve names contain spaces by default, thus \s
$cmd{'ProCurve'}{'enpr'} = '(\x1b\[[;\?0-9A-Za-z]+)+[\w\s()\'+.-]+#\s?(\x1b\[[;\?0-9A-Za-z]+)+$';
$cmd{'ProCurve'}{'enab'} = 'enable';
$cmd{'ProCurve'}{'page'} = 'no page';
$cmd{'ProCurve'}{'shcf'} = 'show run';
$cmd{'ProCurve'}{'cfst'} = 'Configuration Editor; ';
$cmd{'ProCurve'}{'conf'} = 'conf t';
$cmd{'ProCurve'}{'end'}  = 'end';
$cmd{'ProCurve'}{'ifct'} = 'interface';
$cmd{'ProCurve'}{'ifds'} = 'disable';
$cmd{'ProCurve'}{'ifes'} = 'enable';
$cmd{'ProCurve'}{'ifdp'} = 'no power-over-ethernet';
$cmd{'ProCurve'}{'ifep'} = 'power-over-ethernet';
$cmd{'ProCurve'}{'tech'} = "copy command-output \"sh tech all\" tftp %NeDi% %FILE%";

$cmd{'MSM'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'MSM'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'MSM'}{'enab'} = 'enable';
$cmd{'MSM'}{'shcf'} = 'show all conf';
$cmd{'MSM'}{'cfto'} = 120;
$cmd{'MSM'}{'cfst'} = 'enable';

$cmd{'SROS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'SROS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'SROS'}{'enab'} = 'enable';
$cmd{'SROS'}{'page'} = 'terminal length 0';
$cmd{'SROS'}{'shcf'} = 'show run';
$cmd{'SROS'}{'cfst'} = '.';

$cmd{'TMS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'TMS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'TMS'}{'enab'} = 'enable';
$cmd{'TMS'}{'page'} = 'no page';
$cmd{'TMS'}{'shcf'} = 'show run';
$cmd{'TMS'}{'cfst'} = '.';

$cmd{'VC'}{'ropr'} = 'GitsDoNid';
$cmd{'VC'}{'enpr'} = '->\s?$';
$cmd{'VC'}{'enab'} = 'super';
$cmd{'VC'}{'shcf'} = 'show config';
$cmd{'VC'}{'cfst'} = '#';
$cmd{'VC'}{'cfen'}  = 'SUCCESS';

#HuaweiVRP
$cmd{'HuaweiVRP'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'HuaweiVRP'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'HuaweiVRP'}{'enab'} = 'enable';
$cmd{'HuaweiVRP'}{'page'} = 'screen-length 0 temporary';
$cmd{'HuaweiVRP'}{'shcf'} = 'display current';
$cmd{'HuaweiVRP'}{'cfst'} = '#';

# LANCOM
$cmd{'LANCOM'}{'ropr'} = '>\s$';
$cmd{'LANCOM'}{'enpr'} = '>\s$';
$cmd{'LANCOM'}{'shcf'} = 'readconfig';                                                  		# Backs up main config
$cmd{'LANCOM'}{'cfst'} = '.';

# Maipu
$cmd{'Maipu'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Maipu'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Maipu'}{'enab'} = 'enable';
$cmd{'Maipu'}{'more'} = '---MORE---';
$cmd{'Maipu'}{'shcf'} = 'show run';
$cmd{'Maipu'}{'cfst'} = '.';

# MIKROTIK
$cmd{'ROS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'ROS'}{'enpr'} = '[\w+().-]+>\s?$';
$cmd{'ROS'}{'shcf'} = 'export';
$cmd{'ROS'}{'cfst'} = '.';

# Netgear
$cmd{'Netgear'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'Netgear'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'Netgear'}{'enab'} = 'enable';
$cmd{'Netgear'}{'page'} = 'terminal length 0';
$cmd{'Netgear'}{'shcf'} = 'show run';
$cmd{'Netgear'}{'cfst'} = '^Current';
$cmd{'Netgear'}{'dfwd'} = 'show mac-addr-table all';
$cmd{'Netgear'}{'sfwd'} = 'show port-security all ';

# Ruckus
$cmd{'ZDOS'}{'ropr'} = '[\w+().-]+>\s?$';
$cmd{'ZDOS'}{'enpr'} = '[\w+().-]+#\s?$';
$cmd{'ZDOS'}{'enab'} = 'enable';
$cmd{'ZDOS'}{'shcf'} = 'show config';
$cmd{'ZDOS'}{'cfst'} = '.';

# VMWARE
$cmd{'ESX'}{'ropr'} = 'GitsDoNid';
$cmd{'ESX'}{'enpr'} = '(.+?)#\s$';
#$cmd{'ESX'}{'shcf'} = 'cat /etc/vmware/esx.conf';							# Backs up main config
$cmd{'ESX'}{'shcf'} = 'for file in /vmfs/volumes/datastore1/*/*.vmx; do echo \#== $file ================================; cat $file; done';	# List each VM config
$cmd{'ESX'}{'cfst'} = '.';

# Zyxel
$cmd{'ZyNOS'}{'ropr'} = '[\w+().-]+>\s?(\x1b7)?$';
$cmd{'ZyNOS'}{'enpr'} = '[\w+().-]+#\s?(\x1b7)?$';
$cmd{'ZyNOS'}{'shcf'} = 'show run';
$cmd{'ZyNOS'}{'cfst'} = 'Current configuration:';

=head2 FUNCTION Spawn()

Spawns a pty.

B<Options> command

B<Globals> -

B<Returns> pty

=cut
sub Spawn{

	my $pty = new IO::Pty or die $!;

	&misc::Prt("PTY :Forking $_[0]\n");
	$SIG{CHLD} = 'IGNORE';
	unless (my $pid = fork){
		die $! unless defined $pid;

		use POSIX ();
		POSIX::setsid or die $!;

		my $tty = $pty->slave;
		$pty->make_slave_controlling_terminal();
		my $tty_fd = $tty->fileno;
		close $pty;

		open STDIN, "<&$tty_fd" or die $!;
		open STDOUT, ">&$tty_fd" or die $!;
		open STDERR, ">&STDOUT" or die $!;
		close $tty;
		exec $_[0] or die $!;
	}
	$pty;
}

=head2 FUNCTION Connect()

Connects to a device using telnet or SSH and sets user if enable is ok

Verbose output is divided into different stages:

CLI|SSH|TEL:check transport & policy conformance

CLI0:connecting

CLI1:detect anykey then login prompt

CLI2:login prompt without anykey

CLI3:1st level login

CLI4:Enable required?

CLI5:Enable prompts for username

CLI6:Enable password with username

CLI7:Enable password without username

CLI8:Status feedback

B<Options> ip, port, user, os

B<Globals> -

B<Returns> session, status

=cut
sub Connect{

	my ($ip, $po, $us, $os) = @_;

	my ($session, $err, $pty, $pre, $match);
	my $next = $err = "";
	my $errmod = 0?'die':'return';									# set to 1 for debugging, if necessary
	my @iolog = ($main::opt{'d'} =~ /c/)?( Input_log	=> 'input.log', Output_log	=> 'output.log' ):();

	my ($realus,$usidx) = split(/;/,$us);								# This allows for multiple pw for same user indexed by ;x
	if($po == 1){
		&misc::Prt("CLI :connection disabled due to previous error (TCP Port=1)\n","Cd");
		return (undef, "connection disabled");
	}elsif($po == 22){
		my $known = "-o 'StrictHostKeyChecking no'";
		if($misc::usessh =~ /never/){
			&misc::Prt("CLI :ssh connection prohibited by usessh policy\n","Cc");
			return (undef, "connection prohibited by usessh policy");
		}elsif($misc::usessh =~ /known/){
			$known = '';
		}
		&misc::Prt("SSH :$us\@$ip:$po Tout:${misc::timeout}s OS:$os EN:$cmd{$os}{'enpr'}\n");
		$pty = &Spawn("ssh $known -l $realus $ip");
		$session = new Net::Telnet(	fhopen		=> $pty,
						Timeout		=> $misc::timeout + 4,			# Add 4s to factor in auth server and ssh on slow devs
						Prompt		=> "/$cmd{$os}{'enpr'}/",
						Telnetmode	=> 0,
						Cmd_remove_mode => 1,
						Output_record_separator => "\r",
						Errmode		=> $errmod,
						@iolog);
	}else{
		if($misc::usessh =~ /always/){
			&misc::Prt("CLI :telnet connection prohibited by usessh policy\n","Ci");
			return (undef, "connection prohibited by usessh policy");
		}
		&misc::Prt("TEL :$us\@$ip:$po Tout:${misc::timeout}s OS:$os EN:$cmd{$os}{'enpr'}\n");
		$session = new Net::Telnet(	Host		=> $ip,
						Port		=> $po,
						Timeout		=> $misc::timeout + 2,			# Add 2s to factor in auth server timeout
						Prompt		=> "/$cmd{$os}{'enpr'}/",
						Errmode		=> $errmod,
						@iolog);
	}
	return (undef, "connection error on port $po") if !defined $session;				# To catch failed connections

	($pre, $match) = $session->waitfor("/are you sure|offending key|modulus too small|connection refused|ssh_exchange_identification|any key|Ctrl-Y|$misc::uselogin|password\\s?:|$cmd{$os}{ropr}|$cli::cmd{$os}{enpr}/i");
	$err = $session->errmsg;
	if($err){											# on OBSD $err=pattern match read eof
		$session->close if defined $session;
		&misc::Prt("ERR0:$err\n","Cc");
		return (undef, "connection $err");
	}elsif($match =~ /connection refused/i){
		$session->close if defined $session;
		&misc::Prt("CLI0:Connection refused\n","Cc");						# on Linux $match=Connection refused
		return (undef, "connection refused");
	}elsif($match =~ /Selected cipher type not supported/){
		$session->close if defined $session;
		&misc::Prt("CLI0:Selected cipher type not supported\n","Cc");				# Sneuser: Juniper with Export image, you need a domestic image!
		return (undef, "connection cipher type not supported");
	}elsif($match =~ /ssh_exchange_identification/i){
		$session->close if defined $session;
		&misc::Prt("CLI0:Connection ssh_exchange_identification\n","Cc");
		return (undef, "connection ssh_exchange_identification");
	}elsif($match =~ /are you sure/i){								# StrictHostKeyChecking
		$session->close if defined $session;
		&misc::Prt("CLI0:Turn StrictHostKeyChecking off or add key\n","Cc");
		return (undef, "connection hostkey not in known_hosts");
	}elsif($match =~ /offending key/i){								# Hostkey changed
		$session->close if defined $session;
		&misc::Prt("CLI0:Hostkey changed\n","Cc");
		return (undef, "connection hostkey changed");
	}elsif($match =~ /modulus too small/i){								# Size matters after all...
		$session->close if defined $session;
		&misc::Prt("CLI0:Hostkey too small\n","Cc");
		return (undef, "connection hostkey too small");
	}elsif($match =~ /any key|Ctrl-Y/i){
		&misc::Prt("CLI1:Matched '$match' sending ctrl-Y\n");
		$session->put("\cY");									# Since Nortel wants Ctrl-Y...
		($pre, $match) = $session->waitfor("/$misc::uselogin|password\\s?:|$cmd{$os}{ropr}|$cmd{$os}{enpr}/i");
		if($match =~ /$misc::uselogin/i){
			&misc::Prt("CLI1:Matched '$match' sending username\n");
			$next = "us";
		}elsif($match =~ /password\s?(:|for)/i){
			&misc::Prt("CLI1:Matched '$match' sending password\n");
			$next = "pw";
		}
	}elsif($match =~ /$misc::uselogin/i){
		&misc::Prt("CLI2:Matched '$match' sending username\n");
		$next = "us";
	}elsif($match =~ /password\s?(:|for)/i){
		&misc::Prt("CLI2:Matched '$match' sending password\n");
		$next = "pw";
	}

	if($next eq "us"){
		$session->print($realus);
		&misc::Prt("CLI3:Username $realus sent\n");
		($pre, $match) = $session->waitfor("/password\\s?:|invalid|incorrect|denied|authentication failed|$misc::uselogin|$cmd{$os}{ropr}|$cmd{$os}{enpr}/i");
		if($match =~ /password\s?(:|for)/i){
			&misc::Prt("CLI3:Matched '$match' sending password\n");
			$next = "pw";
		}else{
			&misc::Prt("CLI3:Login, no match ($pre)\n");
		}
	}
	if($next eq "pw"){
		$session->print($misc::login{$us}{pw});
		&misc::Prt("CLI3:Password sent\n");
		($pre, $match) = $session->waitfor("/any key|Ctrl-Y|password\\s?:|invalid|incorrect|denied|authentication failed|$misc::uselogin|$cmd{$os}{ropr}|$cmd{$os}{enpr}/i");
		#print "PRE :$pre\nMTCH:$match\n" if $main::opt{'d'}; #TODO find out why my enterasys disconnects here
	}
	$err = $session->errmsg;
	if($err){
		&misc::Prt("ERR3:$err\n",'Cl');
		$session->close;
		return (undef, "login error");
	}elsif($match =~ /password\s?(:|for)|invalid|incorrect|denied|Authentication failed|$misc::uselogin/i){
		&misc::Prt("CLI3:Matched '$match' login failed\n",'Cl');
		$session->close;
		return (undef, "invalid credentials");
	}elsif($match =~ /any key|Ctrl-Y/i){								# Some want this now (with SSH)...
		&misc::Prt("CLI3:Matched '$match' sending ctrl-Y\n");
		$session->put("\cY");									# Since Nortel wants Ctrl-Y...
		($pre, $match) = $session->waitfor("/$cmd{$os}{enpr}/i");
		$err = $session->errmsg;
	}else{
		if ($match =~ /$cmd{$os}{ropr}/ or $cmd{$os}{'ropr'} eq 'GitsDoNid' and $cmd{$os}{'enab'} and $misc::login{$us}{en}){	# Read-only prompt or general prompt and enable cmd and pw?
			if (!$misc::login{$us}{en}){											# No enable pw, resort to read only prompt
				&misc::Prt("CLI4:Matched $match (without enpass)\n");
				$session->prompt("/$cmd{$os}{'ropr'}/");
				return ($session, "OK-ropr");
			}
			&misc::Prt("CLI4:Matched $match (or gen. prompt with enpass & $cmd{$os}{'enab'} cmd), enabling\n");
			$session->print($cmd{$os}{'enab'});
			($pre, $match) = $session->waitfor("/$misc::uselogin|password\\s?:|$cmd{$os}{enpr}/i");
			$err = $session->errmsg;
			if($err){
				&misc::Prt("ERR4:$err\n",'Ce');
				$session->close;
				return (undef, "login error");
			}elsif($match =~ /$misc::uselogin/i){
				&misc::Prt("CLI5:Matched '$match' sending username\n");
				$session->print($realus);
				($pre, $match) = $session->waitfor("/password\\s?:/i");
				$err = $session->errmsg;
				if($err){
					&misc::Prt("ERR5:$err\n",'Cl');
					$session->close;
					return (undef, "login error");
				}elsif($match =~ /password\s?(:|for)/i){
					&misc::Prt("CLI6:Matched '$match' sending password\n");
					$session->print($misc::login{$us}{en});
					($pre, $match) = $session->waitfor("/password\\s?:|invalid|incorrect|denied|authentication failed|$cmd{$os}{enpr}/i");
					$err = $session->errmsg;
				}else{
					&misc::Prt("CLI6:Enabling with user, no match in -->$pre<--\n");
				}
			}elsif($match =~ /password\s?(:|for)/i){
				&misc::Prt("CLI7:Matched '$match' sending password\n");
				$session->print($misc::login{$us}{en});
				($pre, $match) = $session->waitfor("/password\\s?:|invalid|incorrect|denied|authentication failed|unable to verif |$cmd{$os}{enpr}/i");
				$err = $session->errmsg;
			}else{
				&misc::Prt("CLI7:Enabling, no match PRE:$pre\n",'Ce');
			}
		}else{
			$err = "no read-only prompt";
		}
	}
	if($match =~ /$cmd{$os}{enpr}/i){								# Are we enabled?
		&misc::Prt("CLI8:Matched enable prompt, OK\n");
		return ($session, "OK-enpr");
	}else{
		$err = $session->errmsg;
		&misc::Prt("CLI8:Matched '$match' enable failed \n",'Ce');
		$session->close;
		return (undef, "enable failed");
	}
}

=head2 FUNCTION PrepDev()

Find login, if device is compatible for mac-address-table or config retrieval

B<Options> device name, preparation mode (fwd table or config backup)

B<Globals> main::dev

B<Returns> status

=cut
sub PrepDev{

	my ($na, $mod) = @_;
	my ($session, $us);
	my $po    = 0;
	my $status= "init";
	my @users = @misc::users;

	&misc::Prt("\nPrepare (CLI)  ----------------------------------------------------------------\n");
	if(!exists $cmd{$main::dev{$na}{os}}){								# OS supported?
		return "unsupported OS $main::dev{$na}{os} for $mod";
	}elsif($mod eq "fwd" and !$cmd{$main::dev{$na}{os}}{dfwd}){					# Bridge forwarding supported?
		&misc::Prt("PREP:Bridge-Forward table unsupported\n");
		return "unsupported mode fwd";
	}elsif($mod eq "arp" and !$cmd{$main::dev{$na}{os}}{arp}){					# Arp supported?
		&misc::Prt("PREP:ARP/ND table unsupported\n");
		return "unsupported mode arp";
	}elsif($mod eq "cfg" and !$cmd{$main::dev{$na}{os}}{shcf}){					# Config backup supported?
		&misc::Prt("PREP:Config backup unsupported\n");
		return "unsupported mode cfg";
	}
	if($main::dev{$na}{cp}){									# port=0 -> set to be prepd
		if(!scalar keys %misc::login){								# Any users in nedi.conf?
			&misc::Prt("PREP:No users in nedi.conf\n",'Cu');
			return "no users in nedi.conf";
		}elsif(!$main::dev{$na}{us}){								# Do we have a  user?
			&misc::Prt("PREP:No working user\n",'Cu');
			return "no working user";
		}elsif(exists $misc::login{$main::dev{$na}{us}}){					# OK if in nedi.conf
			&misc::Prt("PREP:$mod supported and user $main::dev{$na}{us} exists\n");
			return "OK";									# Plain OK, if straight from DB (no pause before reconnect)
		}else{
			&misc::Prt("PREP:No user $main::dev{$na}{us} in nedi.conf\n",'Cu');		# User not in nedi.conf -> Prep
		}
	}

	$main::dev{$na}{us} = '';
	while ($status !~ /^OK-/){									# Find a way to log in
		$us = shift (@users) unless $status =~ /^connection /;					# Try next user if connection worked
		if(!$us){
			$status= "no valid users";
			last;										# Not possible, no more tries!
		}
		unless($po){										# Port was set before
			if(exists $misc::map{$main::dev{$na}{ip}}{cp}){					# Port mapped
				$po = $misc::map{$main::dev{$na}{ip}}{cp};
			}elsif($misc::usessh eq "never"){
				$po = 23;
			}else{
				$po = 22;
			}
		}
		($session, $status) = Connect($main::dev{$na}{ip}, $po, $us, $main::dev{$na}{os});
		if($status =~ /^OK-/){
			$main::dev{$na}{cp} = $po;
			$main::dev{$na}{us} = $us;
		}elsif($status =~ /^connection /){							# Connection problem
			if($po == 22 and $misc::usessh ne "always"){					# Telnet if ssh failed and ok with policy
				$po = 23;
			}else{
				$main::dev{$na}{cp} = 1;						# port=1, connect not possible
				last;									# Not possible, no more tries!
			}
		}else{
			$main::dev{$na}{cp} = $po;							# Connected, save port
			last if($#users == -1);
		}
		if(defined $session){									# OK, but we just found out
			$session->close;
			select(undef, undef, undef, $clipause);						# Wait to avoid hang in fwd or conf
		}
	}
	return $status;
}

=head2 FUNCTION BridgeFwd()

Get Ios mac address table

B<Options> device name

B<Globals> misc::portprop, misc::portnew

B<Returns> 0 on success, 1 on failure

=cut
sub BridgeFwd{

	my ($na) = @_;
	my ($line, @cam, %nod);
	my $nspo = 0;

	&misc::Prt("\nBridgeFwd (CLI)   -------------------------------------------------------------\n");
	($session, $status) = Connect($main::dev{$na}{ip}, $main::dev{$na}{cp}, $main::dev{$na}{us}, $main::dev{$na}{os});
	if($status !~ /^OK-/){
		return $status;
	}else{
		$session->max_buffer_length(8 * 1024 * 1024);						# Increase buffer to 8Mb
		if($cmd{$main::dev{$na}{os}}{page}){
			my @page = $session->cmd($cmd{$main::dev{$na}{os}}{page});
			&CmdRes($na,$cmd{$main::dev{$na}{os}}{page},\@page);
		}
		@cam = $session->cmd($cmd{$main::dev{$na}{os}}{pfwd}) if $cmd{$main::dev{$na}{os}}{pfwd};
		@cam = $session->cmd($cmd{$main::dev{$na}{os}}{dfwd});
		&CmdRes($na,$cmd{$main::dev{$na}{os}}{dfwd},\@cam);
		if($misc::getfwd eq 'sec' and exists $cmd{$main::dev{$na}{os}}{sfwd}){
			&misc::Prt("CMD :$cmd{$main::dev{$na}{os}}{sfwd}\n");
			my @scam = $session->cmd($cmd{$main::dev{$na}{os}}{sfwd});
			&CmdRes($na,$cmd{$main::dev{$na}{os}}{sfwd},\@scam);
			push @cam, @scam;
		}
		$session->close;
	}

	foreach my $l (@cam){
		my $mc = "";
		my $po = "";
		my $vl = "";
		my $ul = 0;
		my $rt = 0;
		if($main::dev{$na}{os} =~ /^(IOS|NXOS|NXUCS)/){
			if ($l =~ /\s+(dynamic|static|forward|secure(dynamic|sticky))\s+/i){		# (secure) tx Duane Walker 7/2007
				my @mactab = split (/\s+/,$l);
				foreach my $col (@mactab){
					if ($col =~ /^(Eth|Fa|Gi|Te|Do|Po|Vi|Veth)/){$po = &misc::Shif($col)}
					elsif ($col =~ /^[0-9|a-f]{4}\./){$mc = $col}
					elsif ($main::dev{$na}{os} ne "IOS-wl" and !$vl and $col =~ /^[0-9]{1,4}$/){$vl = $col} # Only use, if no vlan yet and it's not a Cisco AP
				}
				if($po =~ /[0-9]\.[0-9]/){						# Does it look like a subinterface?
					my @sub = split(/\./,$po);
					if(exists $misc::portprop{$na}{$sub[0]}){			# Parent IF exists, treat as sub
						$vl = $sub[1];
						$misc::portprop{$na}{$po}{lnk} = $misc::portprop{$na}{$sub[0]}{lnk};
					}
				}
			}
		}elsif($main::dev{$na}{os} eq "CatOS"){
			if ($l =~ /^[0-9]{1,4}\s/){
				my @mactab = split (/\s+/,$l);
				foreach my $col (@mactab){
					if ($col =~ /^[0-9]{1,4}$/){$vl = $col}
					elsif ($col =~ /^([0-9|a-f]{2}-){5}[0-9|a-f]{2}$/){$mc = $col}
					elsif ($col =~ /[0-9]{1,2}\/[0-9]{1,2}/){$po = $col}
				}
			}
		}

		$mc =~ s/[^0-9a-f]//g;									# Strip to pure hex
		my $mcst = &misc::ValidMAC($mc);
		if($po and $mcst ){
			if(exists($misc::portprop{$na}{$po}) ){						# IF exists?
				my $mcvl = ($vl =~ /$misc::useivl/)?$mc.$vl:$mc;			# Add vlid to mac if set in nedi.conf
				$nod{$na}{$mcvl}{if} = $po;
				$nod{$na}{$mcvl}{vl} = $vl;
				$nod{$na}{$mcvl}{me} =  &misc::NodeMetric( $misc::portprop{$na}{$po}{spd}, $misc::portprop{$na}{$po}{dpx} );
				misc::PrepLink($na,$po,$mc, $mcst) unless $misc::portprop{$na}{$po}{lnk} eq 'D';
				&misc::Prt("FWDC:$mc on $po\tVl$vl\t".&misc::DecFix($misc::portprop{$na}{$po}{spd})."-$misc::portprop{$na}{$po}{dpx}\t$misc::portprop{$na}{$po}{pop}\t$misc::portprop{$na}{$po}{lnk}\n");
			}else{
				&misc::Prt("FWDC:$mc vl$vl, no IF $po\n");
			}
			$nspo++;
		}
	}
	&misc::Prt("FWDC:$nspo bridge forwarding entries found\n"," f$nspo");
	&db::WriteNod(\%nod);

	return "OK-Bridge";
}

=head2 FUNCTION ArpND()

Get arp table off ASAs, since they don't share via SNMP

B<Options> device name

B<Globals> misc::portprop, misc::portnew

B<Returns> 0 on success, 1 on failure

=cut
sub ArpND{

	my ($na) = @_;
	my (@out, %arp);
	my $narp = 0;

	my $os = $main::dev{$na}{os};
	my $cr = '';											# Cut regex
	my $ix = 0;											# IFindex position
	my $mx = 2;											# MAC position
	my $px = 3;											# Port position
	my $pr = 'init';

	misc::Prt("\nArpND (CLI)   -----------------------------------------------------------------\n");
	($session, $status) = Connect($main::dev{$na}{ip}, $main::dev{$na}{cp}, $main::dev{$na}{us}, $os);
	if($status !~ /^OK-/){
		return $status;
	}else{
		$session->max_buffer_length(8 * 1024 * 1024);						# Increase buffer to 8Mb
		if( $main::dev{$na}{bi} =~ /^SonicOS Enhanced 5.[78]/ ){				# Older Sonicwalls use 'sh arp' an no paging
			$ix = 0;
			$mx = 1;
			$px = 2;
			$cr = '^<BR>\s*|<->|\(|\)';
			$cmd{$os}{'arp'} = 'sh arp';
		}elsif ($cmd{$os}{page}){
			if($main::dev{$na}{os} =~ /^IOS/){
				$px = 1;
				$ix = 2;
				$mx = 3;
			}
			my @page = $session->cmd($cmd{$os}{page});
			$pr = CmdRes($na,$cmd{$os}{page},\@page);
		}
		@out = SendCmd( $cmd{$os}{'arp'}, $os, $pr );
		CmdRes($na,$cmd{$os}{arp},\@out);
		$session->close;
	}

	foreach my $l (@out){

		my $mc = "";
		my $ip = "";
		my $po = "";
		my $vl = "";
		my $ul = 0;
		my $rt = 0;

		if ($l =~ /([0-9a-f]{4}\.){2}[0-9a-f]{4}|([0-9a-fA-F]{2}\:){5}[0-9a-fA-F]{2}\s/){		# based on sk95 NXOS contribution
			$l =~ s/$cr//g if $cr;
			my @atab = split (/\s+/,$l);
			$ip = $atab[$ix];
			$mc = lc $atab[$mx];
			$po = $atab[$px];
			$mc =~ s/[^0-9a-f]//ig;								# Strip to pure hex
			if( misc::ValidMAC($mc) and misc::ValidIP($ip) ){				# Only 
				$arp{''}{$mc}{$po}{$ip} = $main::now;
				misc::Prt("ARPC:$mc $ip on $po\n");
				$narp++;
			}else{
				misc::Prt("DBG :MAC $mc or IP $ip is unusable\n") if $main::opt{'d'};
			}
		}
	}
	misc::Prt("ARPC:$narp ARP entries found\n"," a$narp ");

	&db::WriteArpND($na,\%arp);

	return "OK-Arp";
}

=head2 FUNCTION Config()

Get Ios mac address table

B<Options> device name

B<Globals> misc::curcfg

B<Returns> 0 on success, error on failure

=cut
sub Config{

	my ($na) = @_;
	my ($go);
	my @cfg = ();

	my $os = $main::dev{$na}{os};

	&misc::Prt("\nConfig (CLI)   ----------------------------------------------------------------\n");
	($session, $status) = Connect($main::dev{$na}{ip}, $main::dev{$na}{cp}, $main::dev{$na}{us}, $os);
	if($status !~ /^OK-/){
		return $status;
	}else{
		my $pres = 'init';
		if( exists $cmd{$os}{'page'} ){
			my @page = $session->cmd($cmd{$os}{'page'});
			$pres = CmdRes($na,$cmd{$os}{'page'},\@page);
		}
		$session->max_buffer_length(8 * 1024 * 1024);						# Increase buffer to 8Mb
		$session->timeout( ($misc::timeout + (exists $cmd{$os}{'cfto'})?$cmd{$os}{'cfto'}:10) );# Increase for building config
		$cmd{$os}{'shcf'} = 'sh all' if $main::dev{$na}{bi} =~ /^SonicOS Enhanced 5.[78]/;
		@cfg = SendCmd( $cmd{$os}{'shcf'}, $os, $pres );
		$session->close;
		&CmdRes($na,$cmd{$os}{shcf},\@cfg);
	}
	foreach my $line (@cfg){
		if ($line =~ /$cmd{$os}{cfst}/){$go = 1}
		if ($go){
			&misc::Prt("CONF:$line\n");
			push @misc::curcfg,$line if $line !~ /$misc::ignoreconf/;
		}else{
			&misc::Prt("WAIT:$line\n");
		}
		if (exists $cmd{$os}{cfen} and $line =~ /$cmd{$os}{cfen}/){$go = 0}
	}
	if( scalar(@misc::curcfg) < 3 ){
		&misc::Prt("ERR :No config (".join(' ',@misc::curcfg).")\n","Be");
		return "config is less than 3 lines";
	}else{
		while($misc::curcfg[$#misc::curcfg] eq ''){						# Remove empty trailing lines
			pop @misc::curcfg;
		}
		my $nl = scalar(@misc::curcfg);
		&misc::Prt("CONF:$nl lines read\n"," c$nl");
		return "OK-${nl}lines";
	}
}


=head2 FUNCTION Commands()

Send commands to device (used by the GUI helper Devsend.pl)

B<Options> IP, port, user, pass, OS, command file

B<Globals> -

B<Returns> -

=cut
sub Commands{

	my ($na, $ip, $po, $us, $pw, $os, $cf) = @_;
	my $diff = '';
	my $ddir = $na;
	$ddir =~ s/([^a-zA-Z0-9_.-])/"%" . uc(sprintf("%2.2x",ord($1)))/eg;

	my $ok = 1;
	unless(-e "$misc::nedipath/cli/$ddir"){
		misc::Prt("CMD :Creating $misc::nedipath/cli/$ddir\n");
		$ok = mkdir( "$misc::nedipath/cli/$ddir", 0755 );
	}
	return "Error creating $misc::nedipath/cli/$ddir" unless $ok;

	if($misc::guiauth =~ /-pass$/){
		$misc::login{$us}{pw} = $pw;
	}
	if($cf =~ /^diff-/){
		$diff = 1;
		$cf =~ s/^diff-//;
	}
	if( -e  "$misc::nedipath/cli/$cf" and open( CMDF, "$misc::nedipath/cli/$cf" ) ){
		my @cmd = <CMDF>;
		close(CMDF);
		misc::Prt("CMD :$cf(". scalar @cmd ." lines) OS=$os USR=$us T=${misc::timeout}s\n");

		my @cmp = ();
		if( $diff and -e  "$misc::nedipath/cli/$ddir/$cf.log" ){
			&misc::Prt("CMD :Reading $misc::nedipath/cli/$ddir/$cf.log\n");
			open  (CMPF, "$misc::nedipath/cli/$ddir/$cf.log" );
			@cmp = <CMPF>;
			close(CMPF);
			chomp @cmp;
		}

		($session, $status) = Connect($ip, $po, $us, $os);
		if($status =~ /^OK-/){
			open (LOG, ">$misc::nedipath/cli/$ddir/$cf.log" ) or return "Error writing to $misc::nedipath/cli/$ddir/$cf.log";
			my $pres = 'init';
			if( $cmd{$os}{'page'} ){
				my @page = $session->cmd($cmd{$os}{'page'});
				$pres = CmdRes($na,$cmd{$os}{'page'},\@page);
			}
			my @out = ();
			foreach my $c (@cmd){
				$c =~ s/\r?\n$//;							# Browser adds ^M and chomp fails on this!
				if($c =~ /^sleep \d+$/){
					$c =~ s/^sleep //;
					&misc::Prt("CMD :sleeping $c seconds\n");
					sleep $c;
				}else{
					my @cmdout = SendCmd( $c, $os, $pres );
					foreach ( @cmdout ){
						print LOG "$_\n";
						push @out, $_;
					}

					last if CmdRes($na,$c,\@cmdout);
					my $nl = @cmdout;
					&misc::Prt("CMD :$nl lines returned\n","l$nl");
				}
			}
			$session->close;
			if( $diff and scalar @cmp ){
				my $chg = &misc::Diff(\@cmp, \@out);
				if( $chg ){
					$misc::mq += &mon::Event('C',150,'nede',$na,$na,"$misc::nedipath/cli/$ddir/$cf.log changed:\n$chg");
				}
			}
			close (LOG);
		}
	}else{
		$status = "Error opening command file $cf";
	}
	return $status;
}

=head2 FUNCTION SendCmd()

Executes command, resorting to manual paging if the more prompt is defined and page command failed or isn't available.
Returns otherwise to avoid long timeouts.

B<Options> command

B<Globals> -

B<Returns>

=cut
sub SendCmd{

	my ($c, $os, $pstat) = @_;

	my $pre = my $match = '';
	my $prompt = substr($session->prompt,1,-1);

	if( !$pstat or $pstat eq 'init' and !exists $cmd{$os}{'more'}){					# Paging ok or not necessary, use cmd method
		if( $c =~ /;[yn];\d+$/ ){
			my @opt = split( ';',$c );
			$session->timeout( $opt[2] ) if $opt[2];					# Set timeout if desired
			&misc::Prt("ACK :answer $opt[1] timout $opt[2]\n");
			$session->print( $opt[0] );
			$session->put( $opt[1] );
			if( $opt[2] ){
				($pre, $match) = $session->waitfor(Match => "/$prompt/",Binmode => 1);
			}else{
				sleep 1;								# Wait a sec for the command to complete
				($pre, $match) = $session->waitfor("/$/");				# Try to catch whatever we can
				$pre = '' unless defined $pre;
			}
		}else{
			$session->print( $c );
			($pre, $match) = $session->waitfor(Match => "/$prompt/",Binmode => 1);
		}
	}elsif( $cmd{$os}{'more'} and $pstat ){								# Got a more prompt and paging is not taken care of, try to page manually
		$session->print($c);
		my $chnk = '';
		my $mcol = 0;
		&misc::Prt("MORE:");
		do{
			($chnk, $match) = $session->waitfor(Match => "/$cmd{$os}{more}|$prompt/",Binmode => 1);
			$pre .= $chnk;
			$session->put(" ") if $match eq $cmd{$os}{more};
			&misc::Prt(".");
			&misc::Prt("\nMORE:") unless $mcol % 78;
			$mcol++;
		}while($match !~ /$prompt/i);								# press space until prompt is received
		&misc::Prt(" $mcol\n");
	}else{
		$pre = "Unknown paging status without 'more' prompt defined";
	}

	$pre =~ s/\r|\x08|\x1b[7E]|(\x1b\[(\d{1,2};\d{1,2}[Hr]|2[JK]|\?25h|\?\dl))//g;			# Rid carriage return, backspace, Zyxel & ProCurve Escapes
	@res = split(/\n/,$pre);

	return @res;
}

=head2 FUNCTION CmdRes()

Searches for errors in cmd output and creates event accordingly

 Device		Error string
 Cisco WLC	Incorrect usage
 IOS cmd auth	Command authorization failed

B<Options> command

B<Globals> -

B<Returns>

=cut
sub CmdRes{

	my ($na, $cmd, $res) = @_;

	my $err = '';
	foreach my $l (@{$res}){
		misc::Prt("DBG :$l\n") if $main::opt{'d'} =~ /c/;
		$err = $l if $l =~ /invalid input|Unknown|failed|Incorrect usage|Corrupted download file|Transport error|Peer unreachable/i;	# Catch errors, but ignore "% Warnings" (doesn't seem to work on ProCurve switches using SSH!)
	}
	if($err){
		chomp $err;
		chomp $err;
		$misc::mq += &mon::Event('C',150,'nede',$na,$na,"Command \"$cmd\" returned $err");
		return $err;
	}else{
		&misc::Prt("CMDR:$cmd result is OK\n");
		return '';
	}
}

1;
