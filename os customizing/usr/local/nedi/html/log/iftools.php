<?php
# Quit if called directly
if( !isset($_SESSION['group']) ) exit;

# This file can be used to add links for each interface in Device-Status, with the variables from devtools.php plus those:
# $in		IF name
# $ui		URL encoded IF name
# $ifc[$in]	IF comment (usually a list of neighbors)
# $ifl[$in]	IF alias
# $ift[$in]	IF type
# $ifv[$in]	IF PVID
# $ify[$in]	Linktype

# Examples
#
# List all interfaces with same alias. Only show icon, if an actual alias exists.
echo ($ifl[$i])?"<a href=\"Devices-Interfaces.php?in[]=alias&op[]=%3D&st[]=".urlencode($ifl[$i])."\"><img src=\"img/16/abc.png\" title=\"Alias $lstlbl\"></a>":"";

?>
