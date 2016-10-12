<?php
//===============================
// SNMPwalk utility.
//===============================

session_start();
$nedipath = preg_replace( "/^(\/.+)\/html\/.+.php/","$1",$_SERVER['SCRIPT_FILENAME']);			# Guess NeDi path for nedi.conf

include_once ("libmisc.php");
ReadConf('nomenu');
require_once ("libsnmp.php");
require_once ("../languages/$_SESSION[lang]/gui.php");
if( !preg_match("/net/",$_SESSION['group']) ){
	echo $nokmsg;
	die;
}

$_GET  = sanitize($_GET);
$debug = isset($_GET['d']) ? $_GET['d'] : "";
$ver   = ($_GET['v'] > 1 and $comms[$_GET['c']]['pprot'])?'3':$_GET['v'];
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?= $charset ?>">
	<link href="../themes/<?= $_SESSION['theme'] ?>.css" type="text/css" rel="stylesheet">
</head>

<body>
	<h1><?= $_GET['ip'] ?> <?= $_GET['c'] ?> v<?= $ver ?></h1>
	<div class="bgmain">
		<h2><img src="../img/32/bdwn.png"> <?= $_GET['oid'] ?></h2>
	</div>

	<div class="bgsub code pre">
<?php
if($_GET['ip'] and $ver and $_GET['c'] and $_GET['oid']){
	#$cutoid = strlen($_GET['oid'])+2;
	#snmp_set_oid_numeric_print(1); Seems to be ignored in OBSD 5.6!
	snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);
	foreach( Walk($_GET['ip'], $ver, $_GET['c'], $_GET['oid'], $timeout*300000) as $ix => $val){
			#echo substr($ix, $cutoid ).": <strong>$val</strong>\n";
			echo "$ix: <strong>$val</strong>\n";
	}
}else{
	echo "\t\t<h4>$nonlbl IP, version, community, OID?</h4>";
}

?>
	</div>
</body>
</html>
