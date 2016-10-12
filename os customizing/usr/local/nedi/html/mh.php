<?php
# Program: mh.php (Mobile Health)
# Programmer: Remo Rickli

error_reporting(E_ALL ^ E_NOTICE);

$refresh   = 60;
$firstmsg  = time() - 86400;

$_SESSION['lim']  = 3;
$_SESSION['col']  = 4;
$_SESSION['vol']  = 100;
$_SESSION['gsiz'] = 6;
$_SESSION['lsiz'] = 12;
$_SESSION['view'] = "";
$_SESSION['timf'] = 'j.M y G:i';
$_SESSION['brght']= 200;
$_SESSION['tz'] = "GMT";

require_once ("inc/libmisc.php");
ReadConf('mon');
include_once ("./languages/english/gui.php");							# Don't require, GUI still works if missing
include_once ("inc/libdb-" . strtolower($backend) . ".php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$reg = isset($_GET['reg']) ? $_GET['reg'] : "";
$cty = isset($_GET['cty']) ? $_GET['cty'] : "";
$bld = isset($_GET['bld']) ? $_GET['bld'] : "";
$loc = TopoLoc($reg,$cty,$bld);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
	<title>NeDi Mobile Health</title>
	<meta http-equiv="refresh" content="60">
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-1">
	<link href="inc/print.css" type="text/css" rel="stylesheet">
	<link rel="shortcut icon" href="img/favicon.ico">
</head>

<body>
<table class="content"><tr class="bgmain">
<td class="ctr top">

<p>
<?php

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);

StatusIncidents($loc,$_SESSION['gsiz'],32);
StatusMon($loc,$_SESSION['gsiz']);

?>
</td>
<td class="ctr top">

<?php

StatusIf($loc,'bbup',$_SESSION['gsiz']);
StatusIf($loc,'bbdn',$_SESSION['gsiz']);
StatusPoE($loc,$_SESSION['gsiz'],32);
?>

</td>
<td class="ctr top">

<?php
StatusIf($loc,'brup',$_SESSION['gsiz']);
StatusIf($loc,'brdn',$_SESSION['gsiz']);
StatusDsc($loc,$_SESSION['gsiz'],32);
#StatusIf($loc,'bdis',$_SESSION['gsiz']);
?>

</td>
<td class="ctr top">

<?php
StatusCpu($loc,$_SESSION['gsiz'],32);
StatusMem($loc,$_SESSION['gsiz'],32);
StatusTmp($loc,$_SESSION['gsiz'],32);
?>

</td></tr>
</table>

<h2><?= $mlvl[200] ?> & <?= $mlvl[250] ?> <?= $tim['t'] ?></h2>

<table class="content">
<?php

Events($_SESSION['lim'],array('level','time','location'),array('>=','>','like'),array(200,$firstmsg,$loc),array('AND','AND'),4);

TopoTable($reg,$cty,$bld,'','',2);

if( count($dreg) == 1 ){
	$rarr = array_keys($dreg);
	$reg = $rarr[0];
	if( count($dcity[$reg]) == 1 ){
		$carr = array_keys($dcity[$reg]);
		$cty = $carr[0];
	}
}

if(!$reg){
	TopoRegs();
}elseif(!$cty){
	TopoCities($reg,1);
}elseif(!$bld){
	TopoBuilds($reg,$cty,1);
}else{
	TopoFloors($reg,$cty,$bld,1);
}

?>

</body>

</html>
