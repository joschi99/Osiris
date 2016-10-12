<?php
# Quit if called directly
if( !isset($_SESSION['group']) ) exit;

# This file can be used to add links in Device-Status, with the following variables available:
# $ip		Device IP address
# $dv		Device name
# $ud		URL encoded device name
# $os		Operating system
# $rcomm	SNMP read community
# $wcomm	SNMP write community
# $rver		SNMP read version
# $wver		SNMP write version
# $cliport	TCP Port for CLI access
# $login	Username
# $ico		Device icon
# $sysobj	SNMP Sysobjid
# $wasup	device was seen in last discovery
# $devopts	Device options (flags set by discovery)

# $isadmin	current user is in admin group
# $ismgr	current user is in manager group
# $debug 	debugging active
# $mobile	Access from mobile device

# Usage example for Cisco-WLC and HP-MSMs to list all controlled APs (using custom value as reference to controller's IP):
if( preg_match('/^wc.n/',$ico) ){
	echo "<a href=\"Devices-List.php?in[]=login&op[]=%3D&st[]=$ud\"><img src=\"img/16/wlan.png\" title=\"AP $lstlbl\"></a>";
}

# This lets Nodes-Toolbox ping the ip with a single click
echo "<a href=\"Nodes-Toolbox.php?Dest=$ip&Do=Ping\"><img src=\"img/16/tool.png\" title=\"Ping\"></a>";

# List all interfaces where this device appears and count total neighbors
?>
<a href="System-Export.php?act=c&exptbl=none&sep=%3B&query=SELECT+n.device%2Cn.ifname%2Ctmp1.amount+FROM+nbrtrack+n+JOIN+%28+SELECT+count%28*%29+amount%2Ca.device%2Ca.ifname+FROM+nbrtrack+a+GROUP+BY+a.device%2C+a.ifname%29+tmp1%0D%0AON+n.device+%3D+tmp1.device+AND+n.ifname+%3D+tmp1.ifname+WHERE+n.neighbor='<?= $ud ?>'+order+by+tmp1.amount+limit+25%3B&type=htm"><img src="img/16/link.png" title="MAClist"></a>
