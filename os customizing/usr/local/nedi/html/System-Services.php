<?php
# Program: System-Services.php
# Programmer: Remo Rickli

$exportxls = 1;

include_once ("inc/header.php");

$mysrv['Moni']['cmd'] = "$nedipath/moni.pl -D";
$mysrv['Moni']['ico'] = "bino";

if( array_key_exists('Master', $mod['Monitoring']) ){
	$mysrv['Master']['cmd'] = "$nedipath/master.pl -D";
	$mysrv['Master']['ico'] = "trgt";
}

$mysrv['Syslog']['cmd'] = "$nedipath/syslog.pl -Dp 1514";
$mysrv['Syslog']['ico'] = "bell";

$mysrv['Trap']['cmd'] = "snmptrapd -c /etc/snmp/snmptrapd.conf 1162";
$mysrv['Trap']['ico'] = "warn";

if(PHP_OS == "OpenBSD"){
	$mysrv['Radius']['cmd'] = "/usr/local/sbin/radiusd";
}else{
	$mysrv['Radius']['cmd'] = "/usr/sbin/freeradius";
}
$mysrv['Radius']['ico'] = "key";

if(PHP_OS == "OpenBSD"){
	$mysrv['Iperf']['cmd'] = "/usr/local/bin/iperf -s -D";
}else{
	$mysrv['Iperf']['cmd'] = "/usr/bin/iperf -s -D";
}
$mysrv['Iperf']['ico'] = "tap";

#$mysrv['Dhcpd']['cmd'] = "/usr/sbin/dhcpd -p1067";
#$mysrv['Dhcpd']['ico'] = "glob";

$_GET = sanitize($_GET);
$stop = (isset($_GET['stop']) and in_array($_GET['stop'],array_keys($mysrv) ) ) ? $_GET['stop'] : "";
$start = (isset($_GET['start']) and in_array($_GET['start'],array_keys($mysrv) ) ) ? $_GET['start'] : "";
$clear = (isset($_GET['clear']))? $_GET['clear'] : "";

function GetPID($srv){

	global $procs;

	$pids = explode ("\n", $procs);
	foreach ($pids as $pid){
		if (strstr ($pid, $srv)){
			return strtok ($pid, " ");
		}
	}
	return 0;
}

if(preg_match("/OpenBSD|Linux/",PHP_OS) ){
	$pscmd = "ps -axo pid,command";
}
$procs  = shell_exec($pscmd);							# Get PIDs first
$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);

echo "<h1>NeDi $srvlbl</h1>\n";

if($start and $isadmin){
	if( $pid = GetPID($mysrv[$start]['cmd']) ){
		echo "<h4>$start Running with PID $pid!</h4>";
	}else{
		if( system($mysrv[$start]['cmd']." > /dev/null") !== FALSE){
			$procs = shell_exec($pscmd);				# Refresh PIDs after start
			echo "<h5>$start started</h5>";
		}else{
			echo "<h4>$start not started!</h4>";
		}
	}
}elseif($stop and $isadmin){
	if( $pid = GetPID($mysrv[$stop]['cmd']) ){
		if( posix_kill ($pid, 9) ){
			$procs = shell_exec($pscmd);				# Refresh PIDs after kill
			echo "<h5>$stop stopped</h5>";
		}else{
			echo "<h4>$stop not stopped!</h4>";
		}
	}else{
		echo "<h4>$stop not running!</h4>";
	}
}elseif($clear and $isadmin){
	$query	= GenQuery('system','u',"name = 'threads'",'','',array('value'),array(),array('0') );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dellbl threads OK</h5>";}

	if( $pid = GetPID("$nedipath/nedi.pl") ){
		posix_kill ($pid, 9);
		$err = posix_get_last_error();
		if( $err ){
			echo "<h4>$dellbl NeDi: ".posix_strerror($err)."</h4>\n";
		}else{
			echo "<h5>NeDi $dellbl OK</h5>\n";
		}
		$procs = shell_exec($pscmd);					# Refresh PIDs after kill
	}else{
		echo "<h4>NeDi not running</h4>\n";
	}
}

$query	= GenQuery('system','s');
$res	= DbQuery($query,$link);
while( $s = DbFetchRow($res) ){
	$sys[$s[0]] = $s[1];
}
DbFreeResult($res);

ob_end_flush();
?>
<form name="form" action="<?= $self ?>.php" method="post">
<table class="content fixed">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<?php  foreach (array_keys($mysrv) as $p ) { ?>
<td class="ctr">
	<img src="img/32/<?= $mysrv[$p]['ico'] ?>.png" title="<?= $p ?>">
	<p>
	<a href="<?= ( GetPID($mysrv[$p]['cmd']) )?"?stop=$p\"><img src=\"img/32/walk.png\" title=\"$endlbl\">":"?start=$p\"><img src=\"img/32/bcls.png\" title=\"$cmdlbl\">" ?></a>
</td>
<?php } ?>
<td class="ctr">
	<img src="img/32/radr.png" title="NeDi">
	<p>
<?= $sys['threads']?"\t<img src=\"img/32/walk.png\" ":"\t<img src=\"img/32/bcls.png\" " ?> title="<?= $sys['threads'] ?> threads, 1st:<?= (date($_SESSION['timf'],$sys['first'])) ?>">
<?php if ($sys['threads'] and $isadmin){ ?>
	<a href="?clear=1"><img src="img/16/bstp.png" class="frgt" onclick="return confirm('<?= $reslbl ?>, <?= $cfmmsg ?>?')" title="<?= $reslbl ?>!"></a>
<?php } ?>
</td>
</tr>
</table>
<p>

<h2>Processes</h2>

<div class="textpad code pre txta tqrt">
<?= $procs ?>
</div><br>
<p>

<h2><?= $lodlbl ?></h2>

<div class="textpad code pre txta tqrt">
<?php
	if(PHP_OS == "OpenBSD"){
		system("/usr/bin/top -n -1");
	}elseif(PHP_OS == "Linux"){
		system("/usr/bin/top -bn1");
	}elseif(PHP_OS == "FreeBSD"){
		system("/usr/bin/top -b");
	}
?>
</div><br>
<p>

<h2>Disks</h2>

<div class="textpad code pre txta tqrt">
<?php
	if(preg_match("/OpenBSD|FreeBSD|Linux/",PHP_OS) ){
		system("df -h");
	}elseif( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ){
		system("dir|find \"bytes free\"");
	}
?>
</div><br>
<p>

<h2><?= $netlbl ?></h2>

<div class="textpad code pre txta tqrt">
<?php
	if(PHP_OS == "OpenBSD"){
		system("/usr/bin/systat -b netstat");
	}elseif(PHP_OS == "Linux"){
		system("netstat -ln --inet");
	}elseif(PHP_OS == "FreeBSD"){
		system("sockstat -P tcp -c");
	}
?>
</div><br>
<p>

<h2>Sensors</h2>

<div class="textpad code pre txta tqrt">
<?php
	if(PHP_OS == "OpenBSD"){
		system("/usr/bin/systat -b sensors");
	}elseif(PHP_OS == "Linux"){
		system("/usr/bin/sensors");
	}elseif(PHP_OS == "FreeBSD"){
		system("sysctl hw.acpi.thermal");
	}
?>
</div><br>
<p>

<h2>SMS <?= $oublbl ?></h2>

<div class="textpad code pre txta tqrt">
<?php
	if( file_exists( '/var/spool/sms/outgoing/') ){
		if(PHP_OS == "OpenBSD"){
			system("cat /var/spool/sms/outgoing/*");
		}
	}
?>
</div>

<br><p>
<?php
include_once ("inc/footer.php");
?>
