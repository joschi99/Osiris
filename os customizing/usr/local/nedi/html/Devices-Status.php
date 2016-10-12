<?php
# Program: Devices-Status.php
# Programmer: Remo Rickli

snmp_set_quick_print(1);
snmp_set_oid_numeric_print(1);
snmp_set_valueretrieval(SNMP_VALUE_LIBRARY);

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libsnmp.php");

$_GET = sanitize($_GET);
$dld = isset($_GET['del']) ? $_GET['del'] : '';
$dtx = isset($_GET['dtx']) ? $_GET['dtx'] : '';
$dv  = isset($_GET['dev']) ? $_GET['dev'] : '';
$add = isset($_GET['add']) ? $_GET['add'] : '';
$adi = isset($_GET['adi']) ? $_GET['adi'] : 0;

$loc = isset($_GET['loc']) ? $_GET['loc'] : '';
$con = isset($_GET['con']) ? $_GET['con'] : $_SESSION['user'];
$typ = isset($_GET['ty']) ? $_GET['ty'] : 'gen-cloud';
$srv = isset($_GET['srv']) ? $_GET['srv'] : 1;
$des = isset($_GET['des']) ? $_GET['des'] : 'User added';
$grp = isset($_GET['grp']) ? $_GET['grp'] : '';
$siz = isset($_GET['siz']) ? $_GET['siz'] : 1;
$ina = isset($_GET['ina']) ? $_GET['ina'] : '-';
$mac = isset($_GET['mac']) ? $_GET['mac'] : '';
$ali = isset($_GET['ali']) ? $_GET['ali'] : '';
$spd = isset($_GET['spd']) ? $_GET['spd'] : 100000000;
$ico = isset($_GET['ico']) ? $_GET['ico'] : 'clan';
$ip  = isset($_GET['ip'])  ? $_GET['ip'] : '0.0.0.0';
$sn  = isset($_GET['sn'])  ? $_GET['sn']  : '';

$clo = isset($_GET['clo']) ? $_GET['clo'] : '';
$cco = isset($_GET['cco']) ? $_GET['cco'] : '';
$cif = isset($_GET['cif']) ? $_GET['cif'] : '';
$pif = isset($_GET['pif']) ? $_GET['pif'] : '';
$ifx = isset($_GET['ifx']) ? $_GET['ifx'] : '';
$ali = isset($_GET['ali']) ? $_GET['ali'] : '';

$mon = isset($_GET['mon']) ? 1 : 0;
$shg = isset($_GET['shg']) ? "checked" : '';
$pop = isset($_GET['pop']) ? "checked" : '';
$fpv = isset($_GET['fpv']) ? $_GET['fpv'] : '';
$fst = isset($_GET['fst']) ? $_GET['fst'] : 0;

$rta = isset($_POST['rta']) ? $_POST['rta']:0;									# Use POST to avoid accidental reset!

$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
?>
<h1>Device <?= $stalbl ?></h1>

<?php  if( !isset($_GET['print']) ) { ?>
<form method="get">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
	<select size="1" name="dev">
		<option value="">Device ->
<?php
$query	= GenQuery('devices','s','device,snmpversion','device');
$res	= DbQuery($query,$link);
$rver = 0;
while( $d = DbFetchRow($res) ){
	$s = '';
	if($dv == $d[0]){
		$rver = $d[1] & 3;
		$s    = ' selected';
	}
	echo "		<option value=\"$d[0]\"$s>$d[0]\n";
}
DbFreeResult($res);
?>
	</select>
<?php

if ($rver){
?>
	<select size="1" name="fst">
		<option value=""><?= $fltlbl ?> <?= $porlbl ?> <?= $stalbl ?> ->
		<option value="1"<?= ($fst == 1)?" selected":"" ?>>Up
		<option value="2"<?= ($fst == 2)?" selected":"" ?>>Down
		<option value="7"<?= ($fst == 7)?" selected":"" ?>>Down > 7 <?= $tim['d'] ?>

		<option value="30"<?= ($fst == 30)?" selected":"" ?>>Down > 30 <?= $tim['d'] ?>
		<option value="90"<?= ($fst == 90)?" selected":"" ?>>Down > 90 <?= $tim['d'] ?>

		<option value="5"<?= ($fst == 5)?" selected":"" ?>><?= $chglbl ?> < 5 <?= $tim['h'] ?>

	</select>
<?php
	$query = GenQuery('vlans','s','*','vlanid','',array('device'),array('='),array($dv) );
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
	<select name="fpv" size="1">
		<option value=""><?= $fltlbl ?> PVID ->
<?php
		while( $v = DbFetchRow($res) ){
			echo "\t\t<option value=\"$v[1]\"".(($fpv == $v[1])?" selected":"").">$v[1] $v[2]\n";
 			$vlans[$v[1]] = $v[2];
		}
	}
	echo "\t</select>\n";
}
?>
<?php if($rrdcmd and $_SESSION['gsiz']){ ?>
	<img src="img/16/grph.png" title="<?= ($verb1)?"$sholbl $gralbl":"$poplbl $gralbl" ?>">
	<input type="checkbox" name="shg" <?= $shg ?>>
<?php } ?>
	<img src="img/16/nods.png" title="<?= ($verb1)?"$sholbl $poplbl":"$poplbl $sholbl" ?>">
	<input type="checkbox" name="pop" <?= $pop ?>>

	<a href="?dev=new"><img src="img/16/add.png" title="<?= $addlbl ?>"></a>
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>"><br>
</td>
</tr>
</table>
</form>
<p>

<?php
}
if( $add ){
	if( $isadmin ){
		$dvst = AddRecord($link,'devices',"device='".DbEscapeString($add)."'",'device,devip,serial,type,firstdis,lastdis,services,description,location,contact,devgroup,devmode,icon,sysobjid,devopts,size,vendor',"'".DbEscapeString($add)."','".ip2long($ip)."','$sn','$typ','".time()."','".time()."',0,'$des','$loc','$con','$grp',9,'$ico','NoSNMP-User','NNNI',$siz,'Other'");
		$ifst = AddRecord($link,'interfaces',"device='".DbEscapeString($add)."' AND ifidx='$adi'",'device,ifname,ifidx,ifmac,ifdesc,alias,iftype,ifstat,speed',"'".DbEscapeString($add)."','$ina','$adi','$mac','User added','$ali',53,3,$spd");
		echo "<h3><a href=\"?dev=".urlencode($add)."\">$add</a> $addlbl Device $dvst Interface $ifst</h3>\n";
	}else{
		echo $nokmsg;
	}
}elseif( $dld ){
	if( $isadmin or $ismgr ){
		DevDelete($dld,$dtx);
?>
<script language="JavaScript"><!--
setTimeout("history.go(-2)",5000);
//--></script>
<?php
	}else{
		echo $nokmsg;
	}
}elseif ($dv){

$ud	= urlencode($dv);										# Need raw for RRD filenames
$query	= GenQuery('devices','s','*','','',array('device'),array('='),array($dv) );
$res	= DbQuery($query,$link);
$ndev	= DbNumRows($res);
if( $ndev != 1 or $adi ){
	if( $ndev != 1 ){
		echo "<h4>$nonlbl Device '$dv' <a href=\"?del=$ud\"><img src=\"img/16/bcnl.png\" title=\"".(($verb1)?"$dellbl $mlvl[30] $inflbl":"$mlvl[30] $inflbl $dellbl")."\"></a></h4>";
		echo "<h2>$stco[10] Device</h2>\n<p>\n\n";
	}else{
		echo "<h2>$stco[10] $intlbl</h2>\n<p>\n\n";
	}
	DbFreeResult($res);
?>
<form method="get" name="bld" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="top">
<?php
	if( $ndev != 1 ){
?>
	<h3><?= $manlbl ?></h3>
	<img src="img/16/dev.png" title="<?= $namlbl ?>">
	<input type="text" name="add" value="<?= $dv ?>" placeholder="<?= $namlbl ?>" class="m"><br>
	<img src="img/16/key.png" title="<?= $serlbl ?>">
	<input type="text" name="sn" value="<?= $sn ?>" placeholder="<?= $serlbl ?>" class="m"><br>
	<img src="img/16/icon.png" onClick="window.open('inc/browse-img.php','Icons','scrollbars=1,menubar=0,resizable=1,width=600,height=800');" title="<?= $sellbl ?> <?= $imglbl ?>">
	<input type="text" name="ico" value="<?= $ico ?>" class="m" placeholder="Icon"><br>
	<img src="img/16/abc.png" title="<?= $typlbl ?>" onClick="window.open('inc/browse-img.php?t=p','Panels','scrollbars=1,menubar=0,resizable=1,width=600,height=800');" title="<?= $sellbl ?> <?= $imglbl ?>">
	<input type="text" name="ty" value="<?= $typ ?>" placeholder="<?= $typlbl ?>" class="m"><br>
</td>
<td class="top">
	<h3><?= $inflbl ?></h3>
	<img src="img/16/form.png" title="<?= $sizlbl ?>">
	<input type="text" name="siz" value="<?= $siz ?>" placeholder="<?= $sizlbl ?>" class="s"><br>
	<img src="img/16/home.png" title="<?= $loclbl ?>">
	<input type="text" name="loc" value="<?= $loc ?>" placeholder="<?= $loclbl ?>" class="l"><br>
	<img src="img/16/user.png" title="<?= $conlbl ?>">
	<input type="text" name="con" value="<?= $con ?>" placeholder="<?= $conlbl ?>" class="l"><br>
	<img src="img/16/ugrp.png" title="<?= $grplbl ?>">
	<input type="text" name="grp" value="<?= $grp ?>" placeholder="<?= $grplbl ?>" class="l"><br>
	<img src="img/16/find.png" title="<?= $deslbl ?>">
	<input type="text" name="des" value="<?= $des ?>" placeholder="<?= $deslbl ?>" class="l"><br>
<?php
	}else{
		echo "	<h3>$idxlbl $adi</h3>\n";
		echo "	<input type=\"hidden\" name=\"add\" value=\"$dv\">\n";
		echo "	<input type=\"hidden\" name=\"adi\" value=\"$adi\">\n";
	}
?>
</td>
<td class="top">
	<h3><?= $intlbl ?></h3>
	<img src="img/16/port.png" title="IF <?= $namlbl ?>">
	<input type="text" name="ina" value="<?= $ina ?>" placeholder="IF <?= $namlbl ?>" class="m"><br>
	<img src="img/16/glob.png" title="IP <?= $adrlbl ?>">
	<input type="text" name="ip" value="<?= $ip ?>" placeholder="IP <?= $adrlbl ?>" class="m"><br>
	<img src="img/16/card.png" title="MAC <?= $adrlbl ?>">
	<input type="text" name="mac" value="<?= $mac ?>" placeholder="MAC <?= $adrlbl ?>" class="m"><br>
	<img src="img/16/find.png" title="Alias">
	<input type="text" name="ali" value="<?= $ali ?>" placeholder="Alias" class="l"><br>
	<img src="img/16/tap.png" title="<?= $spdlbl ?>">
	<select size="1" name="sps" onchange="document.bld.spd.value=document.bld.sps.options[document.bld.sps.selectedIndex].value">
<?php selectbox("bw",$dbs) ?>
	</select>
	<input type="text" name="spd" value="<?= $spd ?>" placeholder="<?= $spdlbl ?>" class="m">
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $addlbl ?>">
</td>
</tr>
</table>
</form>
<?php
	include_once ("inc/footer.php");
	exit(0);
}
$dev = DbFetchRow($res);
DbFreeResult($res);

$ip		= ($dev[1]) ? long2ip($dev[1]) : 0;
list($fc,$lc)	= Agecol($dev[4],$dev[5],0);
$fs		= date($_SESSION['timf'],$dev[4]);
$ls		= date($_SESSION['timf'],$dev[5]);
$wasup		= ($dev[5] > time() - $rrdstep*2)?1:0;
$os		= $dev[8];
$boot		= $dev[9];
$ug		= urlencode($dev[12]);
$rver		= $dev[14] & 3;
$wver		= ($dev[14] & 12) >> 2;
$rcomm		= (($guiauth != 'none')?$dev[15]:"***");
$cliport	= $dev[16];
$login		= $dev[17];
$ico		= $dev[18];
$oi		= ($dev[19]) ? long2ip($dev[19]) : 0;
$sysobj		= $dev[25];
$wcomm		= (($isadmin and $guiauth != 'none')?$dev[26]:"***");
$devopts	= $dev[27];
$stk		= ($dev[29] > 1)?"<img src=\"img/$dev[29].png\" title=\"Stack\">":"";
$lifespan	= intval(($dev[5]-$dev[4])/86400);

if($dev[13] < 10){
	$query	= GenQuery('interfaces','s','*','ifidx','',array('device'),array('='),array($dv) );
	$res	= DbQuery($query,$link);
	while( $i = DbFetchRow($res) ){
		$ifn[$i[2]] = $i[1];
		if( strpos($devopts,'N') and preg_match('/[A-Z][a-z](\d+)\/(\d+)$/',$i[1]) ){
			$nx = preg_replace('/[A-Z][a-z](\d+)\/(\d+)$/',"$1.$2",$i[1]);
			$ifnx[$nx] = $i[2];
		}
		$ify[$i[2]] = $i[3];
		$ift[$i[2]] = $i[4];
		$ifa[$i[2]] = $i[8];
		$ifs[$i[2]] = $i[9];
		$ifd[$i[2]] = $i[10];
		$ifl[$i[2]] = $i[7];
		$ifi[$i[2]] = $i[6];
		$ifc[$i[2]] = $i[28];
		$ifv[$i[2]] = $i[11];
		$ifm[$i[2]] = $i[5];
		$ino[$i[2]] = $i[12];
		$oto[$i[2]] = $i[14];
		$dio[$i[2]] = $i[16];
		$die[$i[2]] = $i[17];
		$doo[$i[2]] = $i[18];
		$doe[$i[2]] = $i[19];
		$did[$i[2]] = $i[22];
		$dod[$i[2]] = $i[23];
		$dib[$i[2]] = $i[25];
		$ifp[$i[2]] = $i[27];
	}

	$net6   = array();
	$net    = array();
	$query	= GenQuery('networks','s','*','ifip','',array('device'),array('='),array($dv) );
	$res	= DbQuery($query,$link);
	while( $n = DbFetchRow($res) ){
		if($n[2]){
			$ifip = long2ip($n[2]);
			$net[$n[1]][$ifip]['ifip'] = $n[4];
			if($n[5]) $net[$n[1]][$ifip]['vrf'] = $n[5];
		}else{
			$ifip = DbIPv6($n[3]);
			$net[$n[1]][$ifip]['ifip6'] = $n[4];
			if($n[5]) $net6[$n[1]][$ifip]['vrf'] = $n[5];
		}
	}
	DbFreeResult($res);
}

$query	= GenQuery('monitoring','s','*','','',array('name'),array('='),array($dev[0]) );
$res	= DbQuery($query,$link);
$statit  = '';
$statbg = 'imga';
if (DbNumRows($res) == 1){
	include_once ("inc/libmon.php");
	$mon = DbFetchRow($res);
	list($statbg,$stat) = StatusBg(1,($mon[3] != 'none')?-1:0,$mon[7]);
	$statit  = $stat;
	if(!$wasup){
		$statbg .= " part";
		$statit  = "$stat, $laslbl $dsclbl < $rrdstep $tim[s]?";
	}
}
DbFreeResult($res);

if($isadmin and $guiauth != 'none'){
	if ($rta == 1){
		$query	= GenQuery('devices','u',"device = '".DbEscapeString($dv)."'",'','',array('readcomm'),array(),array('') );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>SNMP $realbl $reslbl OK</h5>";}
	}elseif ($rta == 2){
		$cliport = 0;
		$query	= GenQuery('devices','u',"device = '".DbEscapeString($dv)."'",'','',array('cliport'),array(),array('0') );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>CLI $reslbl OK</h5>";}
	}elseif ($clo){
		if($sysobj == 'NoSNMP-User'){
			$ok = 1;
		}else{
			if( Set($ip, $wver, $dev[26], "1.3.6.1.2.1.1.6.0", 's', $clo ) ){
				$ok = 1;
				echo "<h5>SNMP $loclbl $chglbl OK</h5>";
			}else{
				echo "<h4>SNMP $loclbl $chglbl $errlbl</h4>";
			}
		}
		if( $ok ){
			$dev[10] = $clo;
			$query	= GenQuery('devices','u',"device = '".DbEscapeString($dv)."'",'','',array('location'),array(),array($clo) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>DB $loclbl $chglbl OK</h5>";}
			$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dv,"User $_SESSION[user] changed location to $clo",'usrs',$dv) );
			DbQuery($query,$link);
		}
	}elseif ($cco){
		if($sysobj == 'NoSNMP-User'){
			$ok = 1;
		}else{
			if( Set($ip, $wver, $dev[26], "1.3.6.1.2.1.1.4.0", 's', $cco ) ){
				$ok = 1;
				echo "<h5>SNMP $conlbl $chglbl OK</h5>";
			}else{
				echo "<h4>SNMP $conlbl $chglbl $errlbl</h4>";
			}
		}
		if( $ok ){
			$dev[11] = $cco;
			$query	= GenQuery('devices','u',"device = '".DbEscapeString($dv)."'",'','',array('contact'),array(),array($cco) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>DB $conlbl $chglbl OK</h5>";}
			$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dv,"User $_SESSION[user] changed contact to $cco",'usrs',$dv) );
			DbQuery($query,$link);
		}
	}elseif ($cif){
		$s = substr($cif,0,1);
		$act = ($s == 1)?"enabled":"disabled";
		$i = substr($cif,1);
		if( Set($ip, $wver, $dev[26], "1.3.6.1.2.1.2.2.1.7.$i", 'i', ($s)?1:2 ) ){
			echo "<h5>SNMP IF $chglbl OK</h5>";
			$query	= GenQuery('interfaces','u',"device = '".DbEscapeString($dv)."' AND ifname = '$ifn[$i]'",'','',array('ifstat'),array(),array($s) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>DB $ifn[$i] $act OK</h5>";$ifa[$i] = $s;}
			$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dv,"User $_SESSION[user] $act interface $ifn[$i]",'usrs',$dv) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$msglbl $updlbl OK</h5>";}
		}else{
			echo "<h4>IF $chglbl $errlbl</h4>";
		}
	}elseif ($pif){
		$s = substr($pif,0,1);
		$act = ($s == 1)?"enabled PoE on":"disabled PoE on";
		list($o,$i) = explode('.', substr($pif,1));
		if( Set($ip, $wver, $dev[26], "1.3.6.1.2.1.105.1.1.1.3.$o.$i", 'i', ($s)?1:2 ) ){
			echo "<h5>SNMP IF $chglbl PoE OK</h5>";
			$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dv,"User $_SESSION[user] $act interface $ifn[$i]",'usrs',$dv) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$msglbl $updlbl OK</h5>";}
		}else{
			echo "<h4>IF $chglbl $errlbl</h4>";
		}
	}elseif ($ali and $ifn[$ifx]){
		if($ali == "-"){$ali = "";}
		if( Set($ip, $wver, $dev[26], "1.3.6.1.2.1.31.1.1.1.18.$ifx", 's', $ali) ){
			echo "<h5>SNMP IF $ifx Alias = $ali OK</h5>";
			$query	= GenQuery('interfaces','u',"device = '".DbEscapeString($dv)."' AND ifname = '$ifn[$ifx]'",'','',array('alias'),array(),array($ali) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>DB IF $ifn[$ifx] $updlbl OK</h5>";$ifl[$ifx] = $ali;}
			$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dv,"User $_SESSION[user] set interface $ifn[$ifx] alias to $ali",'usrs',$dv) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$msglbl $updlbl OK</h5>";}
		}else{
			echo "<h4>IF $chglbl $errlbl</h4>";
		}
	}
}
?>
<table class="full"><tr><td class="helper half">

<h2><?= $sumlbl ?></h2><p>

<table class="content">
	<tr>
		<td class="<?= $statbg ?> ctr s b">
			<a href="?dev=<?= $ud ?>"><img src="img/dev/<?= $ico ?>.png" title="<?= $statit ?>"></a> <?= Digit($dev[29]) ?><br>
			<?= $dev[0] ?>

		</td>
		<td class="bgsub">
			<div class="flft">
<?php  if ($rver) { ?>
<?php
	if ($cactiuser and $cactihost and $cactidb){
		$clink  = DbConnect($cactihost,$cactiuser,$cactipass,$cactidb);
		$cquery = GenQuery('host','s','id','','',array('description','hostname'),array('=','='),array($dv,$dv),array('OR') );
		$cres   = DbQuery($cquery,$clink);
		if ( DbNumRows($cres) == 1) {
			$caho = DbFetchRow($cres);
		}
		DbFreeResult($cres);
	}

	if ( isset($caho[0]) ){
?>
				<a target="window" href="<?= $cactiurl ?>/graph_view.php?action=preview&host_id=<?= $caho[0] ?>"><img src="img/cacti.png"></a>
<?php
	}
	if (file_exists("log/devtools.php")) {						# Based on Steffen's idea
		include_once ("log/devtools.php");
	}

}
?>
			</div>
			<div class="frgt">
<?php  if($rver and $guiauth != 'none'){ ?>
				<a href="Other-Defgen.php?so=<?= $sysobj ?>&ip=<?= $ip ?>&co=<?= urlencode($dev[15]) ?>"><img src="img/16/geom.png" title="<?= (($verb1)?"$edilbl Def $fillbl":"Def $fillbl $edilbl") ?>"></a>
<?php
}
if($isadmin or $ismgr){
	if( is_array($mon) ){
		$most = TestImg($mon[3]);
	}else{
		if ($mon == 1 and $dev[1]){
			if($dev[14] & 3){
				$mma  = explode('/', $mema);
				$myma = ($dev[21] > 100)?$mma[0]:$mma[1];
				$most = AddRecord($link,'monitoring',"name='".DbEscapeString($dev[0])."'",'name,monip,test,alert,device,memalert,latwarn,arppoison',"'".DbEscapeString($dev[0])."',$dev[1],'uptime',130,'".DbEscapeString($dev[0])."',$myma,$latw,$arppoison");
			}else{
				$most =  AddRecord($link,'monitoring',"name='".DbEscapeString($dev[0])."'",'name,monip,test,device',"'".DbEscapeString($dev[0])."',$dev[1],'ping','".DbEscapeString($dev[0])."'");
			}
		}else{
			echo "\t\t\t\t<a href=\"?dev=$ud&mon=1\"><img src=\"img/16/bino.png\" title=\"Monitor $addlbl\"></a>\n";
		}
	}
	echo "\t\t\t\t<a href=\"Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=$ud\">$most</a>\n";
?>
				<a href="?del=<?= $ud ?>&dtx=+with+IP+<?= $ip ?>+SN+<?= urlencode($dev[2]) ?>"><img src="img/16/bcnl.png" title="<?= $dellbl ?>!" onclick="return confirm('<?= $dellbl ?> <?= $dev[0] ?>, <?= $cfmmsg ?>')"></a>
<?php } ?>
			</div>
		</td>
	</tr>
	<tr class="txta">
		<td class="imgb s b">
			<?= $manlbl ?> IP
		</td>
		<td>
			<div style="float:right">
				<a href="telnet://<?= $ip ?>"><img src="img/16/loko.png" title="Telnet"></a>
				<a href="ssh://<?= $ip ?>"><img src="img/16/lokc.png" title="SSH"></a>
				<a href="http://<?= $ip ?>" target="window"><img src="img/16/glob.png" title="HTTP"></a>
				<a href="https://<?= $ip ?>" target="window"><img src="img/16/glok.png" title="HTTPS"></a>
				<a href="Nodes-Toolbox.php?Dest=<?= $ip ?>"><img src="img/16/tool.png" title="Toolbox"></a>
			</div>
			<?= (Devcli($ip,$cliport)) ?>

		</td>
	</tr>
<?php  if($ip != $oi and $oi){ ?>
	<tr class="txtb">
		<td class="imgb s b"><?= $orilbl ?> IP</th><td>
			<div class="frgt">
				<a href="telnet://<?= $oi ?>"><img src="img/16/loko.png" title="Telnet"></a>
				<a href="ssh://<?= $oi ?>"><img src="img/16/lokc.png" title="SSH"></a>
				<a href="http://<?= $oi ?>" target="window"><img src="img/16/glob.png" title="HTTP"></a>
				<a href="https://<?= $oi ?>" target="window"><img src="img/16/glok.png" title="HTTPS"></a>
				<a href="Nodes-Toolbox.php?Dest=<?= $oi ?>"><img src="img/16/tool.png" title="Toolbox"></a>
			</div>
<?= (Devcli($oi,$cliport)) ?>
		</td>
	</tr>
<?php } ?>
	<tr class="txta">
		<td class="imga s b">
			<?= $srvlbl ?>

		</td>
		<td>
			<div class="frgt">
<?php  if($dev[6] > 3) { ?>
				<a href="Topology-Routes.php?rtr=<?= $ud ?>"><img src="img/16/rout.png" title="Topology-Routes"></a>
				<a href="Topology-Multicast.php?dev=<?= $ud ?>"><img src="img/16/cam.png" title="Topology-Multicast <?= $lstlbl ?>"></a>
<?php }
if($dev[6] & 2) { ?>
				<a href="Topology-Spanningtree.php?dev=<?= $ud ?>"><img src="img/16/traf.png" title="Topology-Spanningtree"></a>
<?php } ?>
			</div>
			<?= Syssrv($dev[6]) ?>

		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb s b">
			<?= $dsclbl ?>

		</td>
		<td class="ctr">
			<a href="Devices-List.php?in[]=firstdis&op[]==&st[]=<?= $dev[4] ?>" class="genpad" style="background-color: #<?= $fc ?>" title="<?= $fislbl ?>"><?= $fs ?></a>
			<?= Bar( $lifespan,'lvl10','sbar',"$lifespan $tim[d]") ?>

			<a href="Devices-List.php?in[]=lastdis&op[]==&st[]=<?= $dev[5] ?>" class="genpad" style="background-color:#<?= $lc ?>" title="<?= $laslbl ?>"><?= $ls ?></a>
		</td>
	</tr>
	<tr class="txta">
		<td class="imga s b">
			Bootimage
		</td>
		<td>
			<a href="Reports-Devices.php?in[]=bootimage&op[]=%3D&st[]=<?= urlencode($dev[9]) ?>&rep[]=sft"><?= $dev[9] ?></a> (<a href="Reports-Devices.php?in[]=devos&op[]=%3D&st[]=<?= urlencode($os) ?>&rep[]=sft"><?= $os ?></a>)
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb s b">
			<?= $serlbl ?>

		</td>
		<td>
<?php
	echo InvCheck( $dev[2],$dev[3],3,$dev[10],$dev[11] );

	$dbloc = explode($locsep, $dev[10]);
	if($dbloc[2]){
		$dvloc = 'reg='.urlencode($dbloc[0]).'&cty='.urlencode($dbloc[1]).'&bld='.urlencode($dbloc[2]);
		$lolbl = "$place[b]";
	}elseif($dbloc[1]){
		$dvloc = 'reg='.urlencode($dbloc[0]).'&cty='.urlencode($dbloc[1]);
		$lolbl = "$place[c]";
	}else{
		$dvloc = 'reg='.urlencode($dbloc[0]);
		$lolbl = "$place[r]";
	}
?>

		</td>
	</tr>
	<tr class="txta">
		<td class="imga s b">
			<?= $deslbl ?>

		</td>
		<td>
			<a href="http://www.google.com/search?q=<?= urlencode("$dev[34] $dev[3]") ?>&btnI=1" target="window"><img src="img/oui/<?= VendorIcon($dev[34]) ?>.png" title="<?= $dev[34] ?>"></a>
			<a href="Devices-List.php?in[]=type&op[]==&st[]=<?= urlencode($dev[3]) ?>"><?= $dev[3] ?></a>
			<?= $dev[7] ?>

		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb s b">
			<?= $loclbl ?>

		</td>
		<td>
			<div class="frgt">
				<a href="Topology-Table.php?<?= $dvloc ?>"><img src="img/16/icon.png" title="<?= (($verb1)?"$sholbl $lolbl":"$lolbl $sholbl") ?>"></a>
			</div>
<?php
	if($isadmin and $guiauth != 'none' and ($wasup and $dev[26] or $sysobj == 'NoSNMP-User') and !isset($_GET['print']) ){# Admin, write access or user-created
?>
			<form method="get" name="locfrm">
				<input type="hidden" name="dev" value="<?= $dev[0] ?>">
				<input type="text" name="clo" class="<?= ($_SESSION['gsiz'] == 4)?'xl':'l' ?>" value="<?= $dev[10] ?>" onkeypress="if(event.keyCode==13)this.form.submit()">
			</form>
<?php
	}else{
		echo "\t\t\t$dev[10]\n";
	}
?>
		</td>
	</tr>
	<tr class="txta">
		<td class="imga s b">
			<?= $conlbl ?>

		</td>
		<td>
<?php
	if($isadmin and $guiauth != 'none' and ($wasup and $dev[26] or $sysobj == 'NoSNMP-User')  and !isset($_GET['print']) ){# Admin, write access or user-created
?>
			<div style="float:right">
				<a href="Devices-List.php?in[]=contact&op[]==&st[]=<?= urlencode($dev[11]) ?>"><img src="img/16/dev.png" title="<?= $dev[11] ?> Device <?= $lstlbl ?>"></a>
			</div>
			<form method="get">
				<input type="hidden" name="dev" value="<?= $dev[0] ?>">
				<input type="text" name="cco" class="<?= ($_SESSION['gsiz'] == 4)?'xl':'l' ?>" value="<?= $dev[11] ?>" onkeypress="if(event.keyCode==13)this.form.submit()">
			</form>
<?php
	}else{
		echo "\t\t\t$dev[11]\n";
	}
?>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb s b">
			<?= $grplbl ?>

		<td>
			<a href="Devices-List.php?in[]=devgroup&op[]==&st[]=<?= $ug ?>"><?= $dev[12] ?></a>
			<?= $modlbl ?>: <a href="Devices-List.php?in[]=devmode&op[]==&st[]=<?= $dev[13] ?>"><?= DevMode($dev[13]) ?></a>
		</td>
	</tr>
<?php if( $dev[14] ){ ?>
	<tr class="txta">
		<td class="imga s b">
			SNMP
		</td>
		<td>
<?php if($isadmin) { ?>
			<form method="post" name="nedi" action="System-NeDi.php" class="frgt">
				<input type="hidden" name="mde" value="d">
				<input type="hidden" name="sed" value="a">
				<input type="hidden" name="opt" value="<?=$ip?>">
				<input type="hidden" name="vrb" value="on">
				<input type="hidden" name="skp" value="Gg">
				<?= (strpos($devopts,'i') )?'<input type="hidden" name="uip" value="1">':'' ?>
				<input type="image" class="imgbtn" src="img/16/radr.png" value="Submit" title="<?= (($verb1)?"$dsclbl $tim[n]":"$tim[n] $dsclbl") ?>">
			</form>
			<form method="post" action="Devices-Status.php?dev=<?= $ud ?>" class="frgt">
				<input type="hidden" name="rta" value="1">
				<input type="image" class="imgbtn" src="img/16/exit.png" value="Submit" title="<?= $reslbl ?> SNMP <?= $realbl ?>">
			</form>
<?php } ?>
			<?= ($rver and $dev[15])?"<img src=\"img/bulbg.png\">":"<img src=\"img/bulba.png\">" ?>

			<?= $realbl ?> <?= $rcomm ?> v<?= ($rver  . (($dev[14] & 128)?(($dev[14] & 64)?"-MC":"-HC"):"")) ?>&nbsp;
			<?= ($wver and $dev[26])?"<img src=\"img/bulbg.png\"> $wrtlbl $wcomm v$wver":"<img src=\"img/bulba.png\"> $wrtlbl" ?>

		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb s b">
			CLI
		</td>
		<td>
<?php
	if( $isadmin ){
		if($cliport){
			if($login){
				$cliimg = '<img src="img/bulbg.png">';
				if( preg_match("/^(IOS|EOS|Ironware|ProCurve|Nortel)/",$os) ){
					$shlog = 'show log';
				}elseif($os == 'CatOS'){
					$shlog = 'show logging buf';
				}elseif($os == 'ESX'){
					$shlog = 'tail -100 /var/log/syslog.log';
				}elseif($os == 'Comware'){
					$shlog = 'dis log';
				}elseif($os == 'SAOS'){
					$shlog = 'log flash view';
				}elseif($os == 'LEOS'){
					$shlog = 'log flash view tail 50';
				}elseif($os == 'ROS'){
					$shlog = 'log print';
				}else{
					$shlog = '';
				}
				if($shlog){
?>
			<div class="frgt">
				<form method="post" name="nedi" action="System-NeDi.php">
					<input type="hidden" name="mde" value="d">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="hidden" name="tst" value="i">
					<input type="hidden" name="skp" value="AFGgsjmvpadobewitu">
					<input type="hidden" name="vrb" value="on">
					<?= ( strpos($devopts,'i') )?'<input type="hidden" name="uip" value="1">':'' ?>


					<select size="1" name="cli" onchange="if (confirm(' <?= $sndlbl ?>, <?= $cfmmsg ?>'))this.form.submit();else this.selectedIndex=0">
						<option value="">CLI <?= $sndlbl ?> ->
<?php
foreach (glob("$nedipath/cli/*") as $f){
	if( !is_dir($f) ){
		$l = substr($f,strlen("$nedipath/cli/") );
		echo "\t\t\t\t<option value=\"$l\" ".( ($cli == $f)?" selected":"").">$l\n";
	}
}
?>
					</select>
				</form>
			</div>
			<div class="frgt">
				<form method="post" action="Devices-Write.php">
					<input type="hidden" name="in[]" value="device">
					<input type="hidden" name="op[]" value="=">
					<input type="hidden" name="st[]" value="<?= $dev[0] ?>">
					<input type="hidden" name="cmd" value="<?= $shlog ?>">
					<input type="hidden" name="scm" value="1">
					<input type="image" class="imgbtn" src="img/16/log.png" value="Submit" title="<?= (($verb1)?"$sholbl $loglbl":"$loglbl $sholbl") ?>">
				</form>
			</div>
<?php				}
			}else{
				$cliimg = '<img src="img/bulbr.png">';
			}
?>
			<div class="frgt">
				<form method="post" action="Devices-Status.php?dev=<?= $ud ?>">
					<input type="hidden" name="rta" value="2">
					<input type="image" class="imgbtn" src="img/16/exit.png" value="Submit" title="<?= $reslbl ?> CLI <?= $acslbl ?>">
				</form>
			</div>
<?php
			if( $cliport == 22 ){
?>
			<div class="frgt">
				<form method="post" name="nedi" action="System-NeDi.php">
					<input type="hidden" name="mde" value="d">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="skp" value="AFGgsjmvpadobewitu -kK">
					<?= ( strpos($devopts,'i') )?'<input type="hidden" name="uip" value="1">':'' ?>

					<input type="hidden" name="tst" value="i">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="image" class="imgbtn" src="img/16/key.png" value="Submit" title="<?= "SSH key $updlbl" ?>">
				</form>
			</div>
<?php
			}
		}else{
			$cliimg = '<img src="img/bulba.png">';
		}
	}
	echo "\t\t\t$cliimg\n";
	echo "\t\t\t<a href=\"Devices-List.php?in[]=login&op[]==&st[]=".urlencode($login)."\">$login</a>\n";
	echo "\t\t\t$porlbl <a href=\"Devices-List.php?in[]=cliport&op[]==&st[]=$cliport\">$cliport</a>\n";
	echo "\t\t</td>\n\t</tr>\n";
	echo "\t<tr class=\"txta\">\n\t\t<td class=\"imga s b\">\n\t\t\t$cfglbl\n\t\t</td>\n\t\t<td>\n";
	echo "\t\t\t".DevCfg($dev[33],$dev[32])."\n";

if($isadmin){
?>
			<div class="frgt">
				<form method="post" name="nedi" action="System-NeDi.php">
					<input type="hidden" name="mde" value="d">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="bup" value="B0">
					<input type="hidden" name="skp" value="AFGgsjmvpadobewitu">
					<?= ( strpos($devopts,'i') )?'<input type="hidden" name="uip" value="1">':'' ?>

					<input type="hidden" name="vrb" value="on">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="image" class="imgbtn" src="img/16/radr.png" value="Submit" title="<?= "$cfglbl $buplbl" ?>">
				</form>
			</div>
<?php
}
if($isadmin and $cliport and $login){
?>
			<div class="frgt">
				<form method="post" action="Devices-Write.php">
					<input type="hidden" name="in[]" value="device">
					<input type="hidden" name="op[]" value="=">
					<input type="hidden" name="st[]" value="<?= $dev[0] ?>">
					<input type="hidden" name="con" value="1">
					<input type="image" class="imgbtn" src="img/16/wrte.png" value="Submit" title="<?= (($verb1)?"$wrtlbl $cfglbl":"$cfglbl $wrtlbl") ?>">
				</form>
			</div>
<?php
}
	$query	= GenQuery('configs','s','length(config),time','','',array('device'),array('='),array($dv) );
	$res	= DbQuery($query,$link);
	if (DbNumRows($res) == 1) {
		$dbcfg = DbFetchRow($res);
?>
			<div style="float:right">
				<a href="Devices-Config.php?shc=<?= $ud ?>"><img src="img/16/conf.png" title="Devices-Config"></a>
				<a href="Devices-Doctor.php?dev=<?= $ud ?>"><img src="img/16/info.png" title="<?= $cfglbl ?> <?= $sumlbl ?>"></a>
			</div>
			<?= $buplbl ?> <?= date($_SESSION['timf'],$dbcfg[1]) ?> <?= $sizlbl ?> <?= DecFix($dbcfg[0]) ?>B
<?php
	}
?>
		</td>
	</tr>
<?php
}elseif($dev[13] == 8){
?>
	<tr class="txtb">
		<td class="imgb s b">
			Controller
		</td>
		<td>
			<a href="Devices-Status.php?dev=<?= urlencode($login) ?>"><img src="img/16/sys.png" title="Devices-Status"></a>
			<a href="Devices-List.php?in[]=login&op[]==&st[]=<?= urlencode($login) ?>"><?= $login ?></a>
		</td>
	</tr>
<?php
}

if( substr($devopts,1,1) == "W" ){
	$loadimg = 'flas';
	$loadtyp = 'UPS';
	$avaltyp = "Battery $avalbl";
}elseif( substr($devopts,1,1) == "C" ){
	$loadimg = 'cpu';
	$loadtyp = 'CPU';
	$avaltyp = "$memlbl $frelbl";
}else{
	$loadimg = '';
	$loadtyp = '';
}
$tmp = ($dev[22])?(($_SESSION['far'])?($dev[22]*1.8+32)."F":"$dev[22]C"):"";
if($rver and $rrdcmd and $_SESSION['gsiz']){
	$gsiz    = ($_SESSION['gsiz'] == 4)?2:1;
	$avaunit = ($dev[21] <= 100)?"%":"Bytes";
	echo "\t<tr class=\"txtb\">\n\t\t<td class=\"imgb s b\">\n\t\t\t$gralbl\n\t\t</td>\n\t\t<td class=\"ctr\">\n";
	if( $loadtyp ){
		echo "\t\t\t<a href=\"Devices-Graph.php?dv=$ud&if[]=cpu\"><img src=\"inc/drawrrd.php?dv=$ud&t=cpu&s=$gsiz\" title=\"$loadtyp $lodlbl: $dev[20]%\"></a>\n";
	}
	if($dev[21]){
		echo "\t\t\t<a href=\"Devices-Graph.php?dv=$ud&if[]=mem\"><img src=\"inc/drawrrd.php?dv=$ud&t=mem&s=$gsiz\" title=\"$avaltyp: ".DecFix($dev[21])."$avaunit\"></a>\n";
	}
	if($tmp){
		echo "\t\t\t<a href=\"Devices-Graph.php?dv=$ud&if[]=tmp\"><img src=\"inc/drawrrd.php?dv=$ud&t=tmp&s=$gsiz\" title=\"$tmplbl: $tmp\"></a>\n";
	}
	if($dev[24]){
		list($ct,$cy,$cu) = explode(";", $dev[24]);
		echo "\t\t\t<a href=\"Devices-Graph.php?dv=$ud&if[]=cuv\"><img src=\"inc/drawrrd.php?dv=$ud&if[]=".urlencode($ct)."&if[]=".urlencode($cu)."&s=$gsiz&t=cuv\" title=\"$ct: ".DecFix($dev[23])."$cu\"></a>\n";
	}
	echo "\t\t</td>\n\t</tr>\n";
}

flush();

echo "\t<tr class=\"txta\">\n\t\t<td class=\"imga b\">\n\t\t\t$stalbl\n\t\t</td>\n\t\t<td>\n";
if( $loadtyp ){
	echo "\t\t\t<img src=\"img/16/$loadimg.png\" title=\"$loadtyp $lodlbl\">".Bar($dev[20],$cpua/2,'sbar')." $dev[20]% &nbsp;&nbsp;\n";
}
if( $dev[30] > 1 and preg_match('/^..[PNS]/',$devopts) ){
	$putil = round($dev[31] / $dev[30] * 100,1);
	echo "\t\t\t<img src=\"img/16/batt.png\" title=\"PoE $lodlbl $dev[31]/$dev[30] W\">".Bar($putil,$poew/2,'sbar')." $putil% &nbsp; \n";
}
if($tmp){
	echo "\t\t\t<img src=\"img/16/temp.png\" title=\"$tmplbl\">".Bar($dev[22],$tmpa/2,'sbar')." $tmp &nbsp; \n";
}

$pcnt = NodPop( array('device'),array('='),array($dev[0]),array() );
if( $pcnt )echo "\t\t\t<a href=\"Nodes-List.php?in[]=device&op[]==&st[]=$ud&ord=ifname\"><img src=\"img/16/nods.png\" title=\"$poplbl\">".Bar($pcnt,100,'sbar')." $pcnt</a> &nbsp; \n";

if ($rver){
	IfFree(7,'eth','device','=',$dev[0]);
	IfFree(7,'dsl','device','=',$dev[0]);

	$upsec = 0;
	if( $wasup ){
		$uptime = Get($ip, $rver, $dev[15], "1.3.6.1.2.1.1.3.0");
		if($uptime){
			$upsec = Tic2Sec($uptime);
			$sttit = date($_SESSION['timf'],time() - $upsec);
			$uptit = sprintf("%d %s %d:%02d",floor($upsec/86400),$tim['d'],floor(($upsec%86400)/3600),floor(($upsec%3600)/60));
			if ($upsec  < 86400){
				echo "\t\t\t<img src=\"img/16/warn.png\" title=\"$uptlbl < 24 $tim[h], $reslbl $sttit\"> $uptit\n";
			}else{
				echo "\t\t\t<img src=\"img/16/bchk.png\" title=\"$uptlbl > 24 $tim[h], $reslbl $sttit\"> $uptit\n";
			}

			if( strpos($devopts,'i') ){
				echo "\t\t\t<img src=\"img/16/net.png\" title=\"IP = $namlbl\">\n";
			}else{
				$sysnam = Get($ip, $rver, $dev[15], "1.3.6.1.2.1.1.5.0");
				$sysnar = preg_replace('/^"|"$/','',explode(".", $sysnam) );
				$devnar = explode(".", $dev[0]);
				if($sysnar[0] and $sysnar[0] != $devnar[0]){
					echo "<h4>$namlbl $chglbl: <a href=\"?dev=".urlencode($sysnar[0])."\">$sysnar[0]</a>!</h4>";
				}
			}
		}else{
			echo "\t\t\t$toumsg\n";
			if($_SESSION['vol']){echo "\t\t\t<audio src=\"inc/noserv.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\"><h5>No audio support</h5></audio>\n";}
		}
	}else{
		echo "\t\t\t<h4>$dsclbl $outlbl</h4>\n";
	}
}
echo "\t\t</td>\n\t</tr>\n";

flush();
?>
</table>

</td><td class="helper half">

<h2>
	<?= ($isadmin)?"<a href=\"Topology-Linked.php?dv=$ud\"><img src=\"img/16/ncon.png\" title=\"".(($verb1)?"$edilbl $cnclbl":"$cnclbl $edilbl")."\"></a>":"" ?>

	<a href="Topology-Links.php?in[]=device&op[]==&st[]=<?= $ud ?>">
	<img src="img/16/link.png" title="<?= $cnclbl ?> <?= $lstlbl ?>"></a>
	<?= $cnclbl ?>

</h2>

<?php
$lnk   = array();
$query = GenQuery('links','s','*','ifname','',array('device'),array('='),array($dv) );
$res   = DbQuery($query,$link);
if( DbNumRows($res) ){
?>
<table class="content" >
	<tr class="bgsub">
		<th class="m"><img src="img/16/port.png"><br>Interface</th>
		<th class="l"><img src="img/16/dev.png"><br><?= $neblbl ?></th>
		<th class="s"><img src="img/16/tap.png"><br><?= $bwdlbl ?></th>
		<th class="s"><img src="img/16/abc.png"><br><?= $typlbl ?></th>
		<th class="m"><img src="img/16/clock.png"><br><?= $timlbl ?></th>
	</tr>
</table>

<div class="scroller">
<table class="content" >
<?php
	$row = 0;
	while( $l = DbFetchRow($res) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ul = urlencode($l[3]);
		$lnk[$l[2]][$l[3]] = $l[7];
		list($tc,$tc) = Agecol($l[10],$l[10],$row % 2);
		echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi b m\">\n\t\t\t$l[2]\n\t\t</td>\n";
		echo "\t\t<td class=\"l\">\n\t\t\t<a href=\"?dev=$ul\" title=\"$l[7]\">$l[3]</a>, $l[4]\n\t\t</td>\n";
		echo "\t\t<td class=\"s\">\n\t\t\t" . DecFix($l[5]) . " $l[8]\n\t\t</td>\n";
		echo "\t\t<td class=\"s\">\n\t\t\t$l[6]\n\t\t</td>\n";
		echo "\t\t<td class=\"m nw\" style=\"background-color:#$tc\">\n\t\t\t".date($_SESSION['timf'],$l[10])."\n\t\t</td>\n\t</tr>\n";
	}
	DbFreeResult($res);
?>
</table>
</div>
<table class="content" >
	<tr class="bgsub">
		<td class="caption">
			<?= $row ?> Links <?= $totlbl ?>

		</td>
	</tr>
</table>

<?php
}else{
?>
<h5><?= $nonlbl ?></h5>

<?php
}

if ($rver){
	$query = GenQuery('vlans','s','*','vlanid','',array('device'),array('='),array($dv) );
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<h2>
	<a href="Devices-Vlans.php?in[]=device&op[]==&st[]=<?= $ud ?>"><img src="img/16/vlan.png" title="Vlan <?= $lstlbl ?>"></a>
	Vlans
</h2>

<table class="content" >
	<tr class="bgsub">
		<th class="s">
			<img src="img/16/vlan.png" title="SSIDs Wlan Controllers"><br>
			Vlan
		</th>
		<th colspan="2">
			<img src="img/16/say.png"><br>
			<?= $namlbl ?>

		</th>
	</tr>
</table>
<div class="scroller">
<table class="content" >
<?php
		$row  = 0;
		foreach ($vlans as $k => $v){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow($bg);
			TblCell( $k,"Devices-Vlans.php?in[]=vlanid&op[]==&st[]=$k\" title=\"Vlan $k $lstlbl","$bi rgt b s" );
			TblCell( $v,"Devices-Vlans.php?in[]=vlanname&op[]==&st[]=$v\" title=\"Vlan $namlbl $v $lstlbl" );
			echo "\t\t<td class=\"m\">\n";
			echo "\t\t\t\t<a href=\"Topology-Map.php?tit=Vlan+$k+Map&in[]=vlanid&op[]==&st[]=$k&fmt=png&mde=f&lev=4\"><img title=\"Vlan Map\" src=\"img/16/paint.png\"><a/>\n";
			echo "\t\t\t\t<a href=\"Devices-Interfaces.php?in[]=pvid&op[]==&st[]=$k\"><img title=\"Vlan $idxlbl\" src=\"img/16/port.png\"><a/>\n";
			echo "\t\t\t\t<a href=\"Nodes-List.php?in[]=vlanid&op[]==&st[]=$k\"><img title=\"IF Vlan $poplbl\" src=\"img/16/nods.png\"><a/>\n";
			if( $ismgr and $rdbhost )echo "\t\t\t\t<a href=\"User-Radius.php?gr=$v&vl=$k\"><img title=\"".($verb1?"$addlbl Radius $grplbl":"Radius $grplbl $addlbl")."\" src=\"img/16/key.png\"><a/>\n";
			echo "\t\t</td>\n\t</tr>\n";
		}
		DbFreeResult($res);
?>
</table>
</div>
<table class="content">
	<tr class="bgsub">
		<td class="caption">
			<?= $row ?> Vlans <?= $totlbl ?>

		</td>
	</tr>
</table>
<?php
	}
}

if ($rver or $dev[13] == 11 or $dev[13] == 40 ){							# Phones can have extensions like keypads and cameras
	$query	= GenQuery('modules','s','count(slot)','','',array('device'),array('='),array($dv) );
	$res	= DbQuery($query,$link);
	if($res){
		$tmod = DbFetchRow($res);
		DbFreeResult($res);
		if($tmod[0]){
			$query	= GenQuery('modules','s','*',($os == 'Printer')?'status':'slot','',array('device'),array('='),array($dv) );
			$res	= DbQuery($query,$link);
?>

<h2>
	<a href="Devices-Modules.php?in[]=device&op[]==&st[]=<?= $ud ?>&ord=slot"><img src="img/16/cubs.png" title="<?= $sholbl ?> <?= $lstlbl ?>"></a>
<?php
		if($os == "Printer"){
?>
	Supplies
</h2>

<table class="content" >
	<tr class="bgsub">
		<th>
			<img src="img/16/file.png" title="<?= $typlbl ?>,<?= $deslbl ?>"><br>
			<?= $typlbl ?>

		</th>
		<th class="m">
			<img src="img/16/form.png"><br>
			<?= $levlbl ?>

		</th>
	</tr>
</table>
<div class="scroller">
<table class="content" >
<?php
			$row  = 0;
			while( $m = DbFetchRow($res) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi ctr xs\">\n\t\t\t".PrintSupply($m[1])."\n\t\t</td>\n";
				echo "\t\t<td>\t\t\t$m[3]\n\t\t</td>\n\t\t<td class=\"m \">\n\t\t\t".Bar($m[10],-33)." $m[10]%\n\t\t</td>\n\t</tr>\n";
			}
			DbFreeResult($res);
?>
</table>
</div>
<table class="content" >
	<tr class="bgsub">
		<td class="caption">
			<?= $tmod[0] ?> Supplies <?= $totlbl ?>
		</td>
	</tr>
</table>
<?php
		}elseif($os == "ESX"){#TODO check XEN (xe vm-start name-label=)
?>
	Virtual Machines
</h2>

<table class="content" >
	<tr class="bgsub">
		<th class="m">
			<img src="img/16/node.png" title="<?= $stalbl ?>, <?= $namlbl ?>"><br>
			VM
		</th>
		<th title="# CPUs, <?= $memlbl ?>" class="l">
			<img src="img/16/info.png"><br>HW
		</th>
	</tr>
</table>
<div class="scroller">
<table class="content" >
<?php
			if($upsec){
				foreach( Walk($ip, $rver, $dev[15],"1.3.6.1.4.1.6876.2.1.1.6") as $ix => $val){
					$vmpwr[substr(strrchr($ix, "."), 1 )] = $val;
				}
			}
			$row  = 0;
			$tmem = 0;
			$tact = 0;
			while( $m = DbFetchRow($res) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$pwr = ($vmpwr[$m[8]] == '"poweredOn"' or $vmpwr[$m[8]] == '"powered on"')?1:0;
				echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi ctr xs\">\n";
				if( strpos($m[7],'tools not ') ){
					$shut    = "power.off";
					$vmtools = '';
				}else{
					$shut    = "power.shutdown";
					$vmtools = "\t\t\t<div class=\"frgt\">".OSImg($m[7])."</div>\n";
				}
				if($pwr){
					$tmem += $m[6];
					$tact++;
				}

				if($isadmin and $login and $cliport){
					$vmx = substr($m[3], 0, strrpos($m[3],'/'));
					$vmp = substr($vmx, 0, strrpos($vmx,'/'));
					if($pwr){
?>
			<form method="post" action="Devices-Write.php">
				<input type="hidden" name="in[]" value="device">
				<input type="hidden" name="op[]" value="=">
				<input type="hidden" name="st[]" value="<?= $dev[0] ?>">
				<input type="hidden" name="cmd" value="vim-cmd vmsvc/<?= $shut ?> <?= $m[8] ?>">
				<input type="hidden" name="scm" value="1">
				<input type="image" class="imgbtn" src="img/16/exit.png" value="Submit" title="On, click to shutdown <?= $m[1] ?> ID:<?= $m[8] ?>" onclick="return confirm('<?= $shut ?> <?= $m[1] ?>?')">
			</form>
<?php					}else{?>
			<form method="post" action="Devices-Write.php">
				<input type="hidden" name="in[]" value="device">
				<input type="hidden" name="op[]" value="=">
				<input type="hidden" name="st[]" value="<?= $dev[0] ?>">
				<input type="hidden" name="cmd" value="vim-cmd vmsvc/power.on <?= $m[8] ?>">
				<input type="hidden" name="scm" value="1">
				<input type="image" class="imgbtn" src="img/16/bcls.png" value="Submit" title="Off, click to turn on <?= $m[1] ?> ID:<?= $m[8] ?>">
			</form>
<?php					}?>
		</td>
		<td>
<?php  if($vmpwr[$m[8]] != '"poweredOn"') { ?>
			<div class="frgt">
				<form method="post" action="Devices-Write.php">
					<input type="hidden" name="in[]" value="device">
					<input type="hidden" name="op[]" value="=">
					<input type="hidden" name="st[]" value="<?= $dev[0] ?>">
					<input type="hidden" name="cmd" value="vim-cmd vmsvc/destroy <?= $m[8] ?>">
					<input type="hidden" name="scm" value="1">
					<input type="image" class="imgbtn" src="img/16/bcnl.png" value="Submit" onclick="return confirm('<?= $dellbl ?>, <?= $cfmmsg ?>')"  title="<?= $dellbl ?> <?= $m[1] ?>">
				</form>
			</div>
<?php } ?>
			<strong><?= $m[1] ?></strong>
		</td>
<?php
				}else{
					echo "\t\t\t<img src=\"img/16/".(($pwr)?"exit":"bcls").".png\" title=\"$m[2] (ID$m[8])\">\n\t\t</td>\n";
					echo "\t\t<td class=\"b\">\n\t\t\t".substr($m[1],0,$_SESSION['lsiz'])."\n\t\t</td>\n";
				}
				echo "\t\t<td class=\"$bi ctr xs\">\n\t\t\t<a href=\"Nodes-Status.php?st=$m[5]\" title=\"Nodes-Status $m[5]\"><img src=\"img/16/node.png\"></a>\n\t\t</td>\n";
				echo "\t\t<td>\n";
				for ($i = 1; $i <= $m[4]; $i++) {
					echo "\t\t\t<img src=\"img/16/cpu.png\" title=\"CPU $i\">\n";
				}
				echo "\t\t</td>\n\t\t<td class=\"l\">\n\t\t\t$vmtools".Bar($m[6],0,'sbar')." $m[6] Mb\n\t\t</td>\n\t</tr>\n";
			}
			DbFreeResult($res);
?>
</table>
</div>
<table class="content" >
	<tr class="bgsub">
		<td class="caption">
			<?= $row ?> VMs <?= $totlbl ?>, <?= $tact ?>VMs & <?= round($tmem/1000,2) ?>Gb Ram <?= $stco['100'] ?>

		</td>
	</tr>
</table>
<?php
		}else{
?>
	<?= $igrp['23'] ?>
</h2>

<table class="content" >
	<tr class="bgsub">
		<th>
			<img src="img/16/find.png"><br>
			Slot / <?= $mdllbl ?>

		</th>
		<th class="l">
			<img src="img/16/key.png"><br>
			<?= $serlbl ?>

		</th>
	</tr>
</table>
<div class="scroller">
<table class="content" >
<?php
			$row  = 0;
			while( $m = DbFetchRow($res) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				list($mcl,$img)    = ModClass($m[9]);
				list($mstat,$mtit) = ModStat($m[10],$bi,$m[9]);

				TblRow($bg);
				echo "\t\t<td class=\"$mstat ctr xs\">\n";
				echo "\t\t\t<img src=\"img/16/$img.png\" title=\"$mcl$mtit\">\n\t\t</td>\n\t\t<td>\n\t\t\t$m[1]\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t$m[2]\n\t\t</td>\n";
				echo "\t\t<td class=\"nw\">\n\t\t\t".InvCheck( $m[4],$m[2],$m[9],$dev[10],$dev[11] )."\n\t\t</td>\n";
				echo "	</tr>\n";
			}
			DbFreeResult($res);
?>
</table>
</div>
<table class="content" >
	<tr class="bgsub">
		<td class="caption">
			<?= $tmod[0] ?> <?= $igrp['23'] ?> <?= $totlbl ?>

		</td>
	</tr>
</table>
<?php
		}
		}
	}else{
		print DbError($link);
	}
}

echo "\n</td></tr></table>\n";
flush();

list($mw,$mh) = MapSize( $_SESSION['gsiz'] );
?>

<h2>Maps</h2>

<table class="content">
	<tr class="bgsub">
<?php
	$query	= GenQuery('events','g','level','level desc','',array('time','source'),array('>','='),array( (time() - $rrdstep),$dv),array('AND'));
	$res	= DbQuery($query,$link);
	if($res){
		while( ($m = DbFetchRow($res)) ){
			$evlvl[$m[0]] = $m[1];
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}

	if( isset($evlvl) ){
?>
		<th>
			<script src="inc/Chart.min.js"></script>
			<a href="Monitoring-Events.php?in[]=source&op[]==&st[]=<?= $ud ?>">
				<canvas id="evlev"class="genpad" width="<?= $mw ?>" height="<?= $mh ?>"></canvas>
			</a>
			<script language="javascript">
var data = [
	{
		value : <?= ($evlvl['10'])?$evlvl['10']:0 ?>,
		color: "#aaaaaa"
	},
	{
		value : <?= ($evlvl['50'])?$evlvl['50']:0 ?>,
		color: "#44aa44"
	},
	{
		value : <?= ($evlvl['100'])?$evlvl['100']:0 ?>,
		color: "#4444aa"
	},
	{
		value : <?= ($evlvl['150'])?$evlvl['150']:0 ?>,
		color: "#aaaa44"
	},
	{
		value : <?= ($evlvl['200'])?$evlvl['200']:0 ?>,
		color: "#aa6644"
	},
	{
		value : <?= ($evlvl['250'])?$evlvl['250']:0 ?>,
		color: "#aa4444"
	},
]
var ctx = document.getElementById("evlev").getContext("2d");
var myNewChart = new Chart(ctx).PolarArea(data,{segmentStrokeColor : "#aaa"<?= $anim ?>});
			</script><br>
<?= $msglbl ?>
		</th>
<?php } ?>
<?php if($pcnt > 0 and $pcnt < 100){ ?>
		<th>
			<a href="Topology-Map.php?tit=<?= $ud ?>+Nodes+Map&in[]=device&op[]==&st[]=<?= $ud ?>&fmt=png&mde=f&lev=6&lal=70&ifi=on"><img class="genpad" src="inc/drawmap.php?dim=<?= $mw ?>x<?= $mh ?>&in[]=device&op[]==&st[]=<?= $ud ?>&lev=6&mde=f&pos=s"></a><br>
			Nodes
		</th>
<?php } ?>
		<th>
			<a href="Topology-Map.php?tit=<?= $ud ?>+<?= $neblbl ?>+Map&in[]=device&op[]==&st[]=<?= $ud ?>&co[]=OR&in[]=neighbor&op[]==&st[]=<?= $ud ?>&fmt=png&mde=f&lev=4&pwt=-1"><img class="genpad" src="inc/drawmap.php?dim=<?= $mw ?>x<?= $mh ?>&in[]=device&op[]==&st[]=<?= $ud ?>&co[]=OR&in[]=neighbor&op[]==&st[]=<?= $ud ?>&mde=f&lev=4&pos=s&pwt=-1"></a><br>
			<?= $neblbl ?>

		</th>
<?php if($dev[12] != '-'){ ?>
		<th>
			<a href="Topology-Map.php?tit=<?= $ug ?>+<?= $grplbl ?>+Map&in[]=devgroup&op[]==&st[]=<?= $ug ?>&fmt=png&mde=f&dmo=1&lev=4"><img class="genpad" src="inc/drawmap.php?dim=<?= $mw ?>x<?= $mh ?>&in[]=devgroup&op[]==&st[]=<?= $ug ?>&tit=%23&mde=f&dmo=1&lev=4&pos=d"></a><br>
			<?= $dev[12] ?> <?= $grplbl ?>

		</th>
<?php } ?>
	</tr>
</table>

<?php

if( count($ifn) ){
?>
<h2>
	<a href="Devices-Interfaces.php?in[]=device&op[]==&st[]=<?= $ud ?>&ord=ifname"><img src="img/16/port.png" title="Interface <?= $lstlbl ?>"></a>
	<a href="Topology-Networks.php?in[]=device&op[]==&st[]=<?= $ud ?>&ord=ifname"><img src="img/16/net.png" title="<?= $netlbl ?> <?= $lstlbl ?>"></a>
	<a href="Reports-Interfaces.php?in[]=device&op[]==&st[]=<?= $ud ?>&ord=ifname&rep[]=trf&rep[]=err&rep[]=dsc&rep[]=brc&rep[]=net&rep[]=pop&lir=5"><img src="img/16/dif.png" title="Interface <?= $sumlbl ?>"></a>
	Interfaces

</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2" class="m">
			<img src="img/16/port.png" title="IF <?= $stalbl ?> (<?= $rltlbl ?>)"><br>
			<?= $namlbl ?>

		</th>
		<th>
			<img src="img/16/abc.png"><br>
			Alias
		</th>
		<th>
			<img src="img/16/find.png"><br>
			<?= $deslbl ?>

		</th>
		<th>
			<img src="img/16/vlan.png" title="pvid"><br>
			Vlan
		</th>
		<th>
			<img src="img/spd.png" title="<?= $spdlbl ?>"><br>
			<?= substr($spdlbl,0,5) ?>

		</th>
		<th>
			<img src="img/dpx.png"><br>
			Duplex
		</th>
		<th>
			<img src="img/16/swit.png" title="<?= $stalbl ?> <?= $chglbl ?> (<?= $rltlbl ?>)"><br>
			<?= $laslbl ?>

		</th>
<?php
	if($pop){
?>
		<th>
			<img src="img/16/nods.png"><br>
			<?= (substr($poplbl,0,3)) ?>

		</th>
<?php
		$query	= GenQuery('nodes','g','ifname','','',array('device'),array('='),array($dv) );
		$res	= DbQuery($query,$link);
		if($res){
			while( ($nc = DbFetchRow($res)) ){
				$ncount[$nc[0]] = $nc[1];
			}
		}
		$query	= GenQuery('iftrack','g','mac,ifupdate,ifname','','',array('device'),array('='),array($dv) );
		$res	= DbQuery($query,$link);
		if($res){
			while( ($nl = DbFetchRow($res)) ){
				$niflog[$nl[2]] = "$nl[0] $laslbl ". date($_SESSION['timf'],$nl[1]);
			}
		}
	}
	if($shg and $_SESSION['gsiz']){
?>
		<th>
			<img src="img/16/grph.png"><br>
			IF <?= $gralbl ?>

		</th>
<?php
	}else{
		$rrdt = ($rrdstep/60)." $tim[i]";
?>
		<th>
			<img src="img/16/bbup.png" title="Octets/<?= $rrdt ?>"><br>
			<?= (substr($inblbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/bbdn.png" title="Blue: Abs <?= $trflbl ?>"><br>
			<?= (substr($oublbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/brup.png" title=" <?= $errlbl ?>/<?= $rrdt ?>"><br>
			<?= (substr($inblbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/brdn.png" title="Red: <?= $mullbl ?> <?= $errlbl ?>"><br>
			<?= (substr($oublbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/bbu2.png" title="Discards/<?= $rrdt ?>"><br>
			<?= (substr($inblbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/bbd2.png"><br>
			<?= (substr($oublbl,0,3)) ?>

		</th>
		<th>
			<img src="img/16/brc.png" title="Broadcasts/<?= $rrdt ?>"><br>
			<?= (substr($inblbl,0,3)) ?>

		</th>
<?php
	}
?>
		<th>
			<img src="img/16/batt.png" title="PoE [mW]"><br>
			PoE
		</th>
		<th>
			<img src="img/netg.png" title="MAC IP VRF"><br>
			<?= $adrlbl ?>
		</th>
	</tr>

<?php
	if($upsec){
		foreach( Walk($ip, $rver, $dev[15],"1.3.6.1.2.1.2.2.1.8") as $ix => $val){
			$ifost[substr(strrchr($ix, "."), 1 )] = $val;
		}
		foreach( Walk($ip, $rver, $dev[15],"1.3.6.1.2.1.2.2.1.9") as $ix => $val){
			$iflac[substr(strrchr($ix, "."), 1 )] = $val;
		}
		if( strpos($devopts,'S') ){
			foreach( Walk($ip, $rver, $dev[15],"1.3.6.1.4.1.9.5.1.4.1.1.11") as $ix => $val){
				$stkx[substr($ix,28)] = $val;
			}
		}
		if( preg_match('/^..[PNS]/',$devopts) ){
			foreach( Walk($ip, $rver, $dev[15],"1.3.6.1.2.1.105.1.1.1.3") as $ix => $val){
				$x = explode('.', $ix);
#TODO find out which devs do what! echo "-$x[0]--$x[1]<br>";
				if( strpos($devopts,'S') ){
					$ifpst[$stkx["$x[12].$x[13]"]] = $val;
					$ifpsx[$stkx["$x[12].$x[13]"]] = "$x[12].$x[13]";
				}elseif( strpos($devopts,'N') ){
					$ifpst[$ifnx["$x[12].$x[13]"]] = $val;
					$ifpsx[$ifnx["$x[12].$x[13]"]] = "$x[12].$x[13]";
				}else{
					$ifpst[$x[12]] = $val;
					$ifpsx[$x[12]] = "$x[11].$x[12]";
				}
			}
		}
	}
	$tpow= 0;
	$row = 0;
	foreach ( $ifn as $i => $in){
		$shoif = 1;
		if ($row % 2){$bg = "txta"; $bi = "imga";$off=$_SESSION['brght'];}else{$bg = "txtb"; $bi = "imgb";$off=$_SESSION['brght']-15;}
		$bg3   = sprintf("%02x",$off);
		list($ifimg,$iftit) = Iftype($ift[$i]);
		$blc = $bio = $bie = $boo = $boe = $bic = $bid = $bod = '';
		if($upsec){
			if($fst == 1) $shoif = 0;
			if($ifost[$i] == "1" or $ifost[$i] == "up"){
				$ifstat = "good";
				$iftit .= " (Up)";
				$shoif = ($fst < 2)?1:0;
			}elseif($ifost[$i] == "2" or $ifost[$i] == "down"){
				$ifstat = "warn";
				$iftit .= " (Down)";
			}elseif($ifost[$i] == "5" or $ifost[$i] == "dormant"){
				$ifstat = "noti";
				$iftit .= " (Dormant)";
			}elseif($ifost[$i] == "6" or $ifost[$i] == "notPresent"){
				$ifstat = "txta";
				$iftit .= " ($nonlbl)";
			}elseif($ifost[$i] == "7" or $ifost[$i] == "lowerLayerDown"){
				$ifstat = "warn";
				$iftit .= " (Lower Layer Down)";
			}else{
				$ifstat = "imga";
			}

			if ($ifa[$i] & 1){
				$cif = "0$i";
				$actmsg = "Disable $ifn[$i], $cfmmsg";
			}else{
				$cif = "1$i";
				$actmsg = "Enable $ifn[$i], $cfmmsg";
				if($ifost[$i] == "1" or $ifost[$i] == "up"){
					$ifstat = "noti";						# admin status down, but oper up?
				}elseif($ifost[$i] == "2" or $ifost[$i] == "down"){
					$ifstat = "alrm";
				}else{
					$ifstat = "imgb";
				}
			}
			$lcs = $upsec - Tic2Sec($iflac[$i]);
		}else{
			list($ifstat,$ifdb) = Ifdbstat($ifa[$i]);
			$iftit .= " DB:$ifdb";
			$lcs = 0;
		}

		if($lcs > 0){
			$iflch = sprintf("%d-%d:%02d",floor($lcs/86400),floor(($lcs%86400)/3600),floor(($lcs%3600)/60));
			$redlc = 360000/($lcs + 1);
			$blc   = "title=\"$chglbl ".date($_SESSION['timf'],time() - $lcs)."\" ";
			if($redlc > 5){
				if($redlc > 55){$redlc = 55;}
				$blc .= "style=\"background-color:#".sprintf("%02x",$redlc + $off)."$bg3$bg3\"";
			}
		}else{
			$iflch = '-';									# offline or potential uptime overflow TODO use 64-bit, if confirmed working on all devs
		}
		if( $fst == 5){
			$shoif = ($lcs/3600 < $fst)?1:0;
		}elseif($shoif and $fst > 2){
			$shoif = ($lcs/86400 > $fst)?1:0;
		}

		if( (!$fpv or $fpv == $ifv[$i]) and (!$fst or $shoif) ){
			$ui    = urlencode($in);
			$row++;
			TblRow($bg);
			echo "\t\t<td class=\"$ifstat ctr xs\">\n";
			if($isadmin and $dev[26] and $guiauth != 'none' and $wasup and $cif){
				echo "\t\t\t<a href=\"?dev=$ud&cif=$cif\"><img src=\"img/$ifimg\" onclick=\"return confirm('$actmsg')\" title=\"$i - $iftit\"></a>\n\t\t</td>\n";
			}else{
				echo "\t\t\t<img src=\"img/$ifimg\" title=\"$i - $iftit\">\n\t\t</td>\n";
			}
			echo "\t\t<td class=\"$bi b nw\">\n";
			if($ifstat == "good" and $guiauth != 'none' and $wasup and !isset($_GET['print'])){
				echo "\t\t\t<span class=\"blu\" title=\"$rltlbl $gralbl\" onclick=\"window.open('inc/rt-popup.php?d=$ud&ip=$ip&v=$dev[14]&c=$dev[15]&i=$i&in=$ui','$dev[1]_$i','scrollbars=0,menubar=0,resizable=1,width=600,height=400')\">$in</span>\n\t\t</td>\n";
			}else{
				echo "\t\t\t$in\n\t\t</td>\n";
			}
			if(substr($devopts,0,1) == "A" and $isadmin and $dev[26] and $guiauth != 'none' and $wasup and !isset($_GET['print'])){
?>
		<td>
			<form method="get">
				<input type="hidden" name="dev" value="<?= $dev[0] ?>">
				<input type="hidden" name="ifx" value="<?= $i ?>">
				<input type="text" name="ali" class="<?= ($_SESSION['gsiz'] == 4)?'l':'m' ?>" value="<?= $ifl[$i] ?>" onkeypress="if(event.keyCode==13)this.form.submit()">
			</form>
		</td>
<?php
			}else{
				echo "\t\t<td>\n\t\t\t$ifl[$i]\n\t\t</td>\n";
			}
			$iclass = ($lcs < $rrdstep)?'gry':'';
			echo "\t\t<td>\n\t\t\t$ifi[$i]\n";
			if( array_key_exists($in,$lnk) ){
				foreach( array_keys($lnk[$in]) as $nb ){
					if( $dv == $nb ){
						echo "\t\t\t<img src=\"img/16/brld.png\" title=\"Loop\"></a>\n";
					}else{
						echo "\t\t\t<a href=\"?dev=".urlencode($nb)."\"><img src=\"".LtypIcon($ify[$i])."\" title=\"$nb ".$lnk[$in][$nb]."\"></a>\n";
					}
				}
			}
			echo "\t\t\t$ifc[$i]\n";
			echo "\t\t</td>\n\t\t<td class=\"$iclass rgt\">\n\t\t\t$ifv[$i]\n\t\t</td>\n";
			echo "\t\t<td class=\"$iclass rgt\">\n\t\t\t".DecFix($ifs[$i])."\n\t\t</td>\n";
			echo "\t\t<td class=\"$iclass ctr\">\n\t\t\t$ifd[$i]\n\t\t</td>\n";
			echo "\t\t<td class=\"$iclass rgt nw\" $blc>\n\t\t\t$iflch\n\t\t</td>\n";

			if( $ino[$i] > 70 ){								# Ignore the first 70  bytes...
				$rio[$i] = ($ifs[$i])?round($dio[$i] / $ifs[$i] / $rrdstep * 800,1):0;
				$bio = "title=\"$rio[$i]%\" style=\"background-color:#".(($rio[$i] > $trfa)?sprintf("%02x","40" + $off):$bg3)."$bg3".sprintf("%02x","40" + $off)."\"";
				if( $die[$i]/$rrdstep > 1 ){
					$ier = $die[$i] * $die[$i] * 8/(($dio[$i])?$dio[$i]:1);
					if( $ier > 55 )$ier = 55;
					$bie = "style=\"background-color:#".sprintf("%02x", $ier+$off)."$bg3$bg3\"";
				}
				if( $dib[$i] > $rrdstep ) $bic = 'warn';
				if( $did[$i] > $rrdstep ) $bid = 'warn';
			}
			if( $oto[$i] > 70 ){								# ...cauz some devs don't default to 0!
				$roo[$i] = ($ifs[$i])?round($doo[$i] / $ifs[$i] / $rrdstep * 800,1):0;
				$boo = "title=\"$roo[$i]%\" style=\"background-color:#".(($roo[$i] > $trfa)?sprintf("%02x","40" + $off):$bg3)."$bg3".sprintf("%02x","40" + $off)."\"";
				if( $doe[$i]/$rrdstep > 1 ){
					$oer = $doe[$i] * $doe[$i] * 8/(($doo[$i])?$doo[$i]:1);		# Relative err^2 with fix for / by 0
					if( $oer > 55 ) $oer = 55;
					$boe = "style=\"background-color:#".sprintf("%02x", $oer+ $off)."$bg3$bg3\"";
				}
				if( $dod[$i] > $rrdstep ) $bod = 'warn';
			}

			if($pop){
				if($niflog[$in]){
					$bnl = sprintf("%02x","40" + $off);
					echo "\t\t<td class=\"nw\" style=\"background-color:#$bg3$bg3$bnl\" title=\"$niflog[$in]\">\n";
				}else{
					echo "\t\t<td class=\"nw\">\n";
				}

				if($ncount[$in]){
					echo "\t\t\t".Bar($ncount[$in],8,'sbar') . " <a href=Nodes-List.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui title=\"Nodes-List\">$ncount[$in]</a>\n";
				}
				echo "\t\t</td>\n";
			}

			if($shg and $_SESSION['gsiz']){
				echo "\t\t<td class=\"ctr nw\">\n";
				IfGraphs($ud, $ui, $ifs[$i], $gsiz);
				echo "\t\t</td>\n";
			}else{
				echo "\t\t<td $bio class=\"rgt\">\t\t\t\t".DecFix($dio[$i])."\n\t\t</td>\n";
				echo "\t\t<td $boo class=\"rgt\">\t\t\t\t".DecFix($doo[$i])."\n\t\t</td>\n";
				echo "\t\t<td $bie class=\"rgt\">\t\t\t\t".DecFix($die[$i])."\n\t\t</td>\n";
				echo "\t\t<td $boe class=\"rgt\">\t\t\t\t".DecFix($doe[$i])."\n\t\t</td>\n";
				echo "\t\t<td class=\"rgt $bid\">\t\t\t\t".DecFix($did[$i])."\n\t\t</td>\n";
				echo "\t\t<td class=\"rgt $bod\">\t\t\t\t".DecFix($dod[$i])."\n\t\t</td>\n";
				echo "\t\t<td class=\"rgt $bic\">\t\t\t\t".DecFix($dib[$i])."\n\t\t</td>\n";
			}

			if($ifp[$i]){
				$tpow += $ifp[$i]/1000;
				$bp1 = sprintf("%02x",$ifp[$i]/650 + $off);				# 650 won't exceed 55 up to PoE+
				echo "\t\t<td class=\"rgt nw\" style=\"background-color:#$bp1$bp1$bg3\">\n\t\t\t$ifp[$i]\n";
			}else{
				echo "\t\t<td class=\"rgt nw\">\n";
			}
			if( preg_match('/^..[PNS]/',$devopts) and isset($ifpst[$i]) ){
				if($ismgr and $wver){
					echo (($ifpst[$i] == 1)?"\t\t\t<a href=\"?dev=$ud&pif=0$ifpsx[$i]\"><img src=\"img/16/bchk.png\" title=\"PoE $stco[100]\"></a>\n":"\t\t\t<a href=\"?dev=$ud&pif=1$ifpsx[$i]\"><img src=\"img/16/bdis.png\" title=\"PoE $dsalbl\"></a>\n");
				}else{
					echo (($ifpst[$i] == 1)?"\t\t\t<img src=\"img/16/bchk.png\" title=\"PoE $stco[100]\">":"<img src=\"img/16/bdis.png\" title=\"PoE $dsalbl\">\n");
				}
			}
			echo "\t\t</td>\n\t\t<td class=\"code\">\n";
			echo "\t\t\t<div class=\"frgt\">\n";
			include ("log/iftools.php");
			echo "\t\t\t</div>\n";
			if($ifm[$i]){echo "\t\t\t<span class=\"drd\">$ifm[$i]</span>\n\t\t\t<br>\n";}
			if( array_key_exists($in, $net) ){
				foreach( array_keys($net[$in]) as $addr ){
					if( array_key_exists('ifip',$net[$in][$addr]) ){
						echo "\t\t\t<a href=\"Reports-Networks.php?in[]=devip&op[]=%3D&st[]=$addr%2F&opt=on".$net[$in][$addr]['ifip']."&rep[]=net\">$addr/".$net[$in][$addr]['ifip']."</a>\n";
					}elseif( array_key_exists('ifip6',$net[$in][$addr]) ){
						echo "\t\t\t<span class=\"prp\">$addr/".$net[$in][$addr]['ifip6']."</span><br>\n";
					}
					if( array_key_exists('vrf',$net[$in][$addr]) ) echo "\t\t\t<a href=\"Topology-Networks.php?in[]=vrfname&op[]==&st[]=".urlencode($net[$in][$addr]['vrf'])."\">".$net[$in][$addr]['vrf']."</a>\n";
				}
			}
			echo "\t\t</td>\n\t</tr>\n";
			}
	}
	TblFoot("bgsub", ($shg?11:17)+($pop?1:0), (($fst or $fpv)?"$fltlbl $row/":"").count( array_keys($ifn) )." Interfaces".($tpow?", ${tpow}W $totlbl PoE":"") );
}

if($statit and strstr($_SESSION['group'],'mon')){
	include_once ("inc/librep.php");
?>

<h2>
	<?= ($verb1)?"$laslbl $mlvl[150], $mlvl[200], $mlvl[250]":"$mlvl[150], $mlvl[200], $mlvl[250] $laslbl" ?>

</h2>

<?php
	Events($_SESSION['lim'],array('level','source'),array('>=','='),array(150,$dev[0]),array('AND') );

	MonLatency("device","=",$dev[0],1,0);
	IncGroup("device","=",$dev[0],$_SESSION['lim'],0);
	MonAvail("device","=",$dev[0],1,0);
}
}else{
	include_once ("inc/librep.php");
	DevPoE($in[0],$op[0],$st[0],$_SESSION['lim'],'');
}

include_once ("inc/footer.php");
?>
