<?php
//===============================
// Reports related functions.
//===============================

//===================================================================
// Add current filter to query string
function AddFilter($in,$op,$st){
	return ($st)?"&co[]=AND&in[]=$in&op[]=$op&st[]=".urlencode($st):'';
}

//===================================================================
// Device Config Stats
function DevConfigs($ina,$opa,$sta,$lim,$ord){

	global $link,$verb1,$cfglbl,$srtlbl,$mico,$loclbl,$locsep,$conlbl,$chglbl,$updlbl,$woulbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2>CLI Devices <?= $woulbl ?> <?= $cfglbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/glob.png"><br>
			IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/cog.png"><br>
			OS
		</th>
	</tr>
<?php

	if($ord){
		$ocol = "devip";
		$srt = "$srtlbl: IP";
	}else{
		$ocol = "device";
		$srt = "$srtlbl: Device";
	}
	$query	= GenQuery('devices','s','device,devip,cliport,devos,contact,location,icon',$ocol,$lim,array('config','cliport',$ina),array('COL IS','>',$opa),array('NULL','1',$sta),array('AND','AND'),'LEFT JOIN configs USING (device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $r[5]);
			TblRow( $bg );
			TblCell("<img src=\"img/dev/$r[6].png\" title=\"$conlbl: $r[4], $loclbl: $l[0] $l[1] $l[2]\">","Devices-Status.php?dev=".urlencode($r[0]),"$bi ctr s");
			TblCell(substr($r[0],0,$_SESSION['lsiz']),'','b');
			TblCell( Devcli(long2ip($r[1]),$r[2]) );
			TblCell($r[3]);
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", 4, "$row Devices $srt");
?>

</td><td class="helper">

<h2><?= $cfglbl ?> <?= $woulbl ?> <?= $chglbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/glob.png"><br>
			IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/date.png"><br>
			<?= $updlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = "devip";
		$srt = "$srtlbl: IP";
	}else{
		$ocol = "device";
		$srt = "$srtlbl: Device";
	}
	$query	= GenQuery('configs','s','device,devip,cliport,devos,time,contact,location,icon',$ocol,$lim,array('changes',$ina),array('~',$opa),array('^$',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $r[6]);
			list($u1c,$u2c) = Agecol($r[4],$r[4],$row % 2);
			TblRow( $bg );
			TblCell("<img src=\"img/dev/$r[7].png\" title=\"$conlbl: $r[5], $loclbl: $l[0] $l[1] $l[2]\">","Devices-Status.php?dev=".urlencode($r[0]),"$bi ctr s");
			TblCell(substr($r[0],0,$_SESSION['lsiz']),'','b');
			TblCell( Devcli(long2ip($r[1]),$r[2]) );
			TblCell(date($_SESSION['timf'],$r[4]),'','nw','',"background-color:#$u1c");
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", 4, "$row Devices, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// Device Discovery History
function DevHistory($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$timlbl,$dsclbl,$fislbl,$laslbl,$hislbl,$lstlbl,$updlbl,$msglbl;
?>

<h2>Device <?= $hislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/clock.png"><br>
			<?= $timlbl ?>

		</th>
		<th>
			<img src="img/16/blft.png"><br>
			<?= $fislbl ?> <?= $dsclbl ?>

		</th>
		<th>
			<img src="img/16/brgt.png"><br>
			<?= $laslbl ?> <?= $dsclbl ?>

		</th>
	</tr>
<?php
	$query	= GenQuery('devices','g','firstdis',($ord)?'firstdis':'firstdis desc',$lim,array($ina),array($opa),array($sta));
	$res	= DbQuery($query,$link);
	$fisr   = DbNumRows($res);
	if($res){
		while( $r = DbFetchRow($res) ){
			$devup[$r[0]]['fs'] = $r[1];
		}
		DbFreeResult($res);
	}
	$query	= GenQuery('devices','g','lastdis',($ord)?'lastdis':'lastdis desc',$lim,array($ina),array($opa),array($sta));
	$res	= DbQuery($query,$link);
	$lasr   = DbNumRows($res);
	if($res){
		while( $r = DbFetchRow($res) ){
			$devup[$r[0]]['ls'] = $r[1];
		}
		DbFreeResult($res);
	}

	if($ord){
		ksort ($devup);
		$srt = "$srtlbl: $laslbl - $fislbl";
	}else{
		krsort ($devup);
		$srt = "$srtlbl: $fislbl - $laslbl";
	}
	$row = 0;
	foreach ( array_keys($devup) as $d ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$fd   = urlencode(date("m/d/Y H:i:s",$d));
		TblRow( $bg );
		TblCell( date($_SESSION['timf'],$d),'','b' );
		TblCell( array_key_exists('fs',$devup[$d])?Bar($devup[$d]['fs'],'lvl50','sbar').$devup[$d]['fs']:'',"Devices-List.php?in[]=firstdis&op[]==&st[]=$fd" );
		TblCell( array_key_exists('ls',$devup[$d])?Bar($devup[$d]['ls'],'lvl200','sbar').$devup[$d]['ls']:'',"Devices-List.php?in[]=lastdis&op[]==&st[]=$fd" );
		echo "\t</tr>\n";
	}
	TblFoot("bgsub", 3, "$row $msglbl ($fisr $fislbl, $lasr $laslbl), $srt");
}

//===================================================================
// Device Link Stats (idea by Steffen1)
function DevLink($ina,$opa,$sta,$lim,$ord){

	global $link,$verb1,$srtlbl,$loclbl,$locsep,$adrlbl,$conlbl,$cnclbl,$isolbl,$undlbl,$namlbl,$neblbl,$typlbl,$deslbl;
?>
<table class="full fixed"><tr><td class="helper">

<h2><?= (($verb1)?"$isolbl Devices":"Devices $isolbl") ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/glob.png"><br>
			IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>

		</th>
	</tr>
<?php

	if($ord){
		$ocol = 'devip';
		$srt = "$srtlbl: IP";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}
	$query	= GenQuery('devices','s','distinct device,devip,cliport,type,contact,location,icon',$ocol,$lim,array('links.device',$ina),array('COL IS',$opa),array('NULL',$sta),array('AND'),'LEFT JOIN links USING (device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $r[5]);
			TblRow( $bg );
			TblCell( "<img src=\"img/dev/$r[6].png\" title=\"$conlbl: $r[4], $loclbl: $l[0] $l[1] $l[2]\">","Devices-Status.php?dev=".urlencode($r[0]),"ctr $bi s" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( Devcli(long2ip($r[1]),$r[2]) );
			TblCell( $r[3] );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", 4, "$row Devices $srt");
?>

</td><td class="helper">

<h2><?= $neblbl ?> <?= $undlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			IF <?= $namlbl ?>

		</th>
		<th>
			<img src="img/16/abc.png"><br>
			<?= $cnclbl ?> <?= $typlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			<?= $neblbl ?>

		</th>
		<th>
			<img src="img/16/find.png"><br>
			<?= $deslbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'neighbor';
		$srt = "$srtlbl: $neblbl";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}
	if( $ina == 'device' ) $ina = 'links.device';
	$query	= GenQuery('links','s','distinct links.device,links.ifname,linktype,neighbor,linkdesc',$ocol,$lim,array('devices.device',$ina),array('COL IS',$opa),array('NULL',$sta),array('AND'),'LEFT JOIN devices ON devices.device = neighbor');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			$nbip = '';
			if( preg_match('/^IP:([\d\.]+) .*/', $r[4],$m) ) $nbip = $m[1];
			$dneb = ($nbip)?"<a href=\"System-NeDi.php?add=$nbip\"><img src=\"img/16/radr.png\"></a>":"";
			$row++;
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			TblRow( $bg );
			TblCell( $r[0],"Devices-Status.php?dev=".urlencode($r[0]),"$bi b" );
			TblCell( $r[1] );
			TblCell( $r[2] );
			TblCell( $r[3],"Monitoring-Events.php?in[]=info&op[]=~&st[]=".urlencode($r[3]),"$bi b",$dneb );
			TblCell( $r[4] );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", 5, "$row $neblbl $undlbl, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// List device PoE stats
function DevPoE($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$totlbl,$lodlbl,$maxlbl;
?>
<table class="full fixed"><tr><td class="helper">

<h2>PoE <?= $lodlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/batt.png"><br>
			<?= $lodlbl ?>

		</th>
</tr>
<?php
	if($ord){
		$ocol = 'totpoe desc';
		$srt = "$srtlbl: $totlbl PoE $lodlbl";
	}else{
		$ocol = 'rtpoe desc';
		$srt = "$srtlbl: % PoE $lodlbl";
	}
	$query	= GenQuery('devices','s','device,type,icon,totpoe*1000/maxpoe as rtpoe',$ocol,$lim,array('maxpoe',$ina),array('>',$opa),array('1',$sta),array('AND'));
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( "<img src=\"img/dev/$r[2].png\" title=\"$r[1]\">","Devices-Status.php?dev=".urlencode($r[0]),"ctr $bi s" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( round($r[3]/10,1).'%','','',Bar($r[3]/10,66) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row Devices, $srt");
?>

</td><td class="helper">

<h2><?= $maxlbl ?> PoE</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/flas.png"><br>
			<?= $maxlbl ?> PoE
		</th>
	</tr>
<?php
	$query	= GenQuery('devices','s','device,type,icon,maxpoe','maxpoe desc',$lim,array('maxpoe',$ina),array('!=',$opa),array('0',$sta),array('AND'));
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( "<img src=\"img/dev/$r[2].png\" title=\"$r[1]\">","Devices-Status.php?dev=".urlencode($r[0]),"ctr $bi s" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( "$r[3]W",'','',Bar($r[3],'lvl150') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row Devices, $srtlbl: $maxlbl PoE");
?>

</td></tr></table>

<?php
}

//===================================================================
// List device software
function DevSW($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$qtylbl,$anim;
?>
<table class="full fixed"><tr><td class="helper">

<h2>Operating Systems</h2>

<canvas id="osdnt" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/cbox.png"><br>
			OS
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'devos';
		$srt = "$srtlbl: OS";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','devos',$ocol,$lim,array($ina),array($opa),array($sta));
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('err',$row,1) );
			if($r[0]){
				$uo = urlencode($r[0]);
				$op = "=";
			}else{
				$uo = "^$";
				$op = "~";
			}
			TblRow( $bg );
			TblCell( $r[0],"Reports-Devices.php?in[]=devos&op[]==&st[]=$uo&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp" );
			TblCell( $r[1],"Devices-List.php?in[]=devos&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'',Bar($r[1],GetCol('err',$row,1),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 2, "$row OS, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("osdnt").getContext("2d");
var myNewChart = new Chart(ctx).Doughnut(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>

</td><td class="helper">

<h2>Bootimages</h2>

<canvas id="bootdnt" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/cbox.png"><br>
			Bootimage
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'bootimage';
		$srt = "$srtlbl: Bootimage";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','bootimage',$ocol,$lim,array($ina),array($opa),array($sta),array(),$join);
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('412',$row,1) );
			if($r[0]){
				$uo = urlencode($r[0]);
				$op = "=";
			}else{
				$uo = "^$";
				$op = "~";
			}
			TblRow( $bg );
			TblCell( $r[0] );
			TblCell( $r[1],"Devices-List.php?in[]=bootimage&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'',Bar($r[1],GetCol('412',$row,1),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 2, "$row Bootimages, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("bootdnt").getContext("2d");
var myNewChart = new Chart(ctx).Doughnut(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>

</td></tr></table>

<?php
}

//===================================================================
// List duplicate device and module serials
function DevDupSer($ina,$opa,$sta,$lim,$ord){

	global $link,$serlbl,$srtlbl,$qtylbl,$duplbl,$typlbl,$totlbl,$nonlbl;
?>
<table class="full fixed"><tr><td class="helper">

<h2><?= $duplbl ?> <?= $serlbl ?></h2>

<?php
	if($ord){
		$ocol = 'serial';
		$srt = "$srtlbl: $serlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','serial,type,icon;-;count(*)>1',$ocol,$lim,array('CHAR_LENGTH(serial)',$ina),array('>',$opa),array('2',$sta),array('AND'));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>

		</th>
		<th>
			<img src="img/16/key.png"><br>
			<?= $serlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( "<img src=\"img/dev/$r[2].png\" title=\"$r[1]\">",'',"ctr $bi s" );
			TblCell( $r[1] );
			TblCell( $r[0] );
			TblCell( $r[3],"Devices-List.php?in[]=serial&op[]==&st[]=".urlencode($r[0]),'',Bar($r[3],0) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 4, "$row $duplbl $serlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>\n";
	}
?>

</td><td class="helper">

<h2><?= $duplbl ?> Module <?= $serlbl ?></h2>

<?php
	if($ord){
		$ocol = 'modules.serial';
		$srt = "$srtlbl: $serlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('modules','g','modules.serial,model,modclass;-;count(*)>1',$ocol,$lim,array('CHAR_LENGTH(modules.serial)',$ina),array('>',$opa),array('2',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>

		</th>
		<th>
			<img src="img/16/key.png"><br>
			<?= $serlbl ?>

		</th>
		<th>
			<img src="img/16/cubs.png"><br>
			Modules
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			list($mcl,$img) = ModClass($r[2]);
			$row++;
			TblRow( $bg );
			TblCell( "<img src=\"img/16/$img.png\" title=\"$mcl\">",'',"ctr $bi s" );
			TblCell( $r[1] );
			TblCell( $r[0] );
			TblCell( $r[3],"Devices-Modules.php?in[]=modules.serial&op[]==&st[]=".urlencode($r[0]),'',Bar($r[3],0) );
			echo "\t</tr>\n";

		}
		DbFreeResult($res);
		TblFoot("bgsub", 4, "$row $duplbl $serlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td></tr></table>

<?php
}

//===================================================================
// List duplicate device IPs
function DevDupIP($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$manlbl,$porlbl,$qtylbl,$duplbl,$totlbl,$nonlbl;

?>
<table class="full fixed"><tr><td class="helper">

<h2><?= $duplbl ?> <?= $manlbl ?> IPs</h2>

<?php
	if($ord){
		$ocol = 'devip';
		$srt = "$srtlbl: $serlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','devip;-;count(*)>1',$ocol,$lim,array('devip',$ina),array('>',$opa),array('0',$sta),array('AND'));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/net.png"><br>
			IP
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( long2ip($r[0]) );
			TblCell( $r[1],"Devices-List.php?in[]=devip&op[]==&st[]=$r[0]",'',Bar($r[1],0) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 2, "$row $duplbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td><td class="helper">

<h2><?= $duplbl ?> <?= $porlbl ?> IPs</h2>

<?php
	if($ord){
		$ocol = 'ifip';
		$srt = "$srtlbl: IP";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('networks','g','ifip;-;count(*)>1',$ocol,$lim,array('ifip',$ina),array('>',$opa),array('0',$sta),array('AND'));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/net.png"><br>
			IP
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>
		</th>
</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( long2ip($r[0]) );
			TblCell( $r[1],"Topology-Networks.php?in[]=ifip&op[]==&st[]=$r[0]",'',Bar($r[1],0) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 2, "$row $duplbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td></tr></table>

<?php
}

//===================================================================
// List device class & services
function DevClass($ina,$opa,$sta,$lim,$ord,$chart=1){

	global $link,$clalbl,$srvlbl,$srtlbl,$lstlbl,$qtylbl,$totlbl,$opt,$anim;
?>
<table class="full fixed"><tr><td class="helper">

<h2>Device <?= $clalbl ?></h2>

<?php if($chart){ ?>
<canvas id="clapie" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>
<?php } ?>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $clalbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'icon';
		$srt = "$srtlbl: $clalbl";
	}else{
		$ocol = (($opt)?'sum':'cnt')." desc";
		$srt = "$srtlbl: $qtylbl";
	}
	if($opt){
		$rcol   = 2;
		$ftlbl  = "$clalbl (Stacked)";
		$query	= GenQuery('devices','g','SUBSTR(icon,1,2);sum(stack) AS sum',$ocol,$lim,array($ina),array($opa),array($sta));
	}else{
		$rcol   = 1;
		$ftlbl  = $clalbl;
		$query	= GenQuery('devices','g','SUBSTR(icon,1,2)',$ocol,$lim,array($ina),array($opa),array($sta));
	}
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			if($chart) $chd[] = array('value' => $r[$rcol],'color' => GetCol('trf',$row) );
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<img src=\"img/dev/$r[0]an.png\" title=\"$r[0]\">" );
			TblCell( DevCat($r[0]),"Reports-Devices.php?in[]=icon&op[]=LIKE&st[]=$r[0]%25&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp" );
			TblCell( $r[$rcol],"Devices-List.php?in[]=icon&op[]=LIKE&st[]=$r[0]%25".AddFilter($ina,$opa,$sta),'',Bar($r[$rcol],GetCol('trf',$row),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row $ftlbl, $srt");

if($chart){
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("clapie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>
<?php } ?>

</td><td class="helper">

<h2>Device <?= $srvlbl ?></h2>

<?php if($chart){ ?>
<canvas id="srvpie" style="display: block;margin: 0 auto;padding: 10px" width="400" height="300"></canvas>
<?php } ?>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/cog.png"><br>
			<?= $srvlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'services';
		$srt = "$srtlbl: $srvlbl";
	}else{
		$ocol = (($opt)?'sum':'cnt')." desc";
		$srt = "$srtlbl: $qtylbl";
	}
	if($opt){
		$rcol   = 2;
		$ftlbl   = "$srvlbl (Stacked)";
		$query	= GenQuery('devices','g','services;sum(stack) AS sum',$ocol,$lim,array($ina),array($opa),array($sta));
	}else{
		$rcol   = 1;
		$ftlbl   = $srvlbl;
		$query	= GenQuery('devices','g','services',$ocol,$lim,array($ina),array($opa),array($sta));
	}
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			if($chart) $chd[] = array('value' => $r[$rcol],'color' => GetCol('142',$row,1) );
			if($r[0]){
				$uo = urlencode($r[0]);
				$op = "=";
			}else{
				$uo = "^$";
				$op = "~";
			}
			TblRow( $bg );
			TblCell( Syssrv($r[0])." ($r[0])" );
			TblCell( $r[$rcol],"Devices-List.php?in[]=services&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'',Bar($r[$rcol],GetCol('142',$row,1),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row $ftlbl, $srt");
?>

<?php if($chart){ ?>
<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("srvpie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>
<?php } ?>

</td></tr></table>

<?php
}

//===================================================================
// List device vendors & types
function DevType($ina,$opa,$sta,$lim,$ord,$chart=1){

	global $link,$typlbl,$srtlbl,$lstlbl,$venlbl,$qtylbl,$invlbl,$totlbl,$opt,$anim;
?>
<table class="full fixed"><tr><td class="helper">


<h2>Device <?= $venlbl ?></h2>

<?php if($chart){ ?>
<canvas id="venpie" style="display: block;margin: 0 auto;padding: 10px" width="400" height="300"></canvas>
<?php } ?>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/cbox.png"><br>
			<?= $venlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'icon';
		$srt = "$srtlbl: $venlbl";
	}else{
		$ocol = (($opt)?'sum':'cnt')." desc";
		$srt = "$srtlbl: $qtylbl";
	}
	if($opt){
		$rcol   = 2;
		$ftlbl  = "$venlbl (Stacked)";
		$query	= GenQuery('devices','g','vendor;sum(stack) AS sum',$ocol,$lim,array('icon',$ina),array('NOT LIKE',$opa),array('cl%',$sta),array('AND'));
	}else{
		$rcol   = 1;
		$ftlbl  = $venlbl;
		$query	= GenQuery('devices','g','vendor',$ocol,$lim,array('icon',$ina),array('NOT LIKE',$opa),array('cl%',$sta),array('AND'));
	}
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$uv = urlencode($r[0]);
			if($chart) $chd[] = array('value' => $r[$rcol],'color' => GetCol('345',$row) );
			TblRow( $bg );
			TblCell( '','',"imgw ctr s","+<a href=\"http://www.google.com/search?q=$uv&btnI=1\" target=\"window\"><img src=\"img/oui/".VendorIcon($r[0]).".png\"></a>" );
			TblCell( $r[0],"Reports-Devices.php?in[]=vendor&op[]==&st[]=$uv&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp" );
			TblCell( $r[$rcol],"Devices-List.php?in[]=vendor&op[]==&st[]=$uv".AddFilter($ina,$opa,$sta),'',Bar($r[$rcol],GetCol('345',$row),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row $ftlbl, $srt");

if($chart){
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("venpie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>
<?php } ?>

</td><td class="helper">

<h2>Device <?= $typlbl ?></h2>

<?php if($chart){ ?>
<canvas id="typpie" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>
<?php } ?>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'type';
		$srt = "$srtlbl: $typlbl";
	}else{
		$ocol = (($opt)?'sum':'cnt')." desc";
		$srt = "$srtlbl: $qtylbl";
	}
	if($opt){
		$rcol   = 4;
		$ftlbl   = "$typlbl (Stacked)";
		$query	= GenQuery('devices','g','type,icon,vendor;sum(stack) AS sum',$ocol,$lim,array($ina),array($opa),array($sta));
	}else{
		$rcol   = 3;
		$ftlbl   = $typlbl;
		$query	= GenQuery('devices','g','type,icon,vendor',$ocol,$lim,array($ina),array($opa),array($sta));
	}
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$utyp  = urlencode($r[0]);
			if($chart) $chd[] = array('value' => $r[$rcol],'color' => GetCol('trf',$row) );
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<img src=\"img/dev/$r[1].png\" title=\"$r[0]\">" );
			TblCell( $r[0],"Reports-Devices.php?in[]=type&op[]==&st[]=$utyp&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp",'',"+<a href=\"http://www.google.com/search?q=".urlencode("$r[2] $r[0]")."&btnI=1\" target=\"window\"><img src=\"img/oui/".VendorIcon($r[2]).".png\" title=\"$r[2]\"></a>");
			TblCell( $r[$rcol],"Devices-List.php?in[]=type&op[]==&st[]=$utyp".AddFilter($ina,$opa,$sta),'',Bar($r[$rcol],GetCol('trf',$row),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 3, "$row $ftlbl, $srt");
?>

<?php if($chart){ ?>
<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("typpie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>
<?php } ?>

</td></tr></table>

<?php
}

//===================================================================
// List Group info
function DevGroup($ina,$opa,$sta,$lim,$ord){

	global $link,$grplbl,$srtlbl,$qtylbl,$lstlbl,$vallbl,$anim;

?>
<table class="full fixed"><tr><td class="helper">

<h2>Device <?= $grplbl ?></h2>

<canvas id="grppie" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/ugrp.png"><br>
			<?= $grplbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'devgroup';
		$srt = "$srtlbl: $grplbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','devgroup',$ocol,$lim,array($ina),array($opa),array($sta));
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('brc',$row,1) );
			if($r[0]){
				$uo = urlencode($r[0]);
				$op = "=";
			}else{
				$uo = "^$";
				$op = "~";
			}
			TblRow( $bg );
			TblCell( $r[0],"Reports-Devices.php?in[]=devgroup&op[]==&st[]=$uo&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp",'',"<a href=\"Topology-Map.php?in%5B%5D=devgroup&op%5B%5D=%3D&st%5B%5D=$uo&tit=$uo+Map&mde=f&len=".(($r[1]>100)?100:120)."&pos=".(($r[1]>100)?'d':'s')."&fmt=png\"><img src=\"img/16/paint.png\"></a>" );
			TblCell( $r[1],"Devices-List.php?in[]=devgroup&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'','+'.Bar($r[1],GetCol('brc',$row,1),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 2, "$row $vallbl, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("grppie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>

</td><td class="helper">

<h2>Device Mode</h2>

<canvas id="modpie" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/abc.png"><br>
			Mode
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'devmode';
		$srt = "$srtlbl: Mode";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('devices','g','devmode',$ocol,$lim,array($ina),array($opa),array($sta));
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('214',$row,1) );
			TblRow( $bg );
			TblCell( DevMode($r[0]),"Reports-Devices.php?in[]=devmode&op[]==&st[]=$r[0]&rep[]=typ&rep[]=cla&rep[]=sft&rep[]=grp" );
			TblCell( $r[1],"Devices-List.php?in[]=devmode&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'',Bar($r[1],GetCol('214',$row,1),'lbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 2, "$row $vallbl, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("modpie").getContext("2d");
var myNewChart = new Chart(ctx).Pie(data, {segmentStrokeWidth : 1<?= $anim ?>});
</script>

</td></tr></table>

<?php
}

//===================================================================
// Device Install Statistics
function DevInstall($ina,$opa,$sta,$lim,$ord){

	global $link,$verb1,$stco,$srtlbl,$tgtlbl,$avalbl,$nonlbl,$dislbl,$qtylbl,$stalbl,$vallbl;

?>
<table class="full fixed"><tr><td class="helper">

<h2><?= $stalbl ?> <?= $dislbl ?></h2>

<?php
	if($ord){
		$ocol = 'status';
		$srt = "$srtlbl: $stalbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('install','g','status',$ocol,$lim,array($ina),array($opa),array($sta));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/bchk.png"><br>
			<?= $stalbl ?>

		</th>
		<th>
			<img src="img/16/form.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( Staimg($r[0]),'',"$bi ctr xs" );
			TblCell( $stco[$r[0]],"?in[]=status&op[]==&st[]=$r[0]",'b' );
			TblCell( $r[1],"System-Install.php?in[]=status&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'','+'.Bar($r[1],'lvl100') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 3, "$row $stalbl, $srt");
	}else{
		echo "<h5>$nonlbl $vallbl</h5>";
	}
?>

</td><td class="helper">

<h2><?= $tgtlbl ?> <?= $avalbl ?></h2>

<?php
	if($ord){
		$ocol = 'target';
		$srt = "$srtlbl: $avalbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $nonlbl $avalbl";
	}
	$query	= GenQuery('install','g','target',$ocol,$lim,array('status',$ina),array('=',$opa),array('10',$sta),array('AND'));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>

		</th>
		<th>
			<img src="img/16/form.png"><br>
			<?= $qtylbl ?>

		</th>
</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( $r[0],"?in[]=target&op[]==&st[]=".urlencode($r[0]),'b' );
			TblCell( $r[1],"System-Install.php?in[]=target&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'','+'.Bar($r[1],'lvl100') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 2, "$row $duplbl, $srt");
	}else{
		echo "<h5>$nonlbl $vallbl</h5>";
	}
?>

</td></tr></table>

<?php
}

//===================================================================
// Show Incident Acknowledge Stats
function IncAck($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$usrlbl,$acklbl,$qtylbl,$timlbl,$tim,$avglbl;
?>

<h2>Incident <?= $acklbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/ucfg.png"><br>
			<?= $usrlbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $avglbl ?> <?= $acklbl ?> <?= $timlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'usrname';
		$srt = "$srtlbl: $usrlbl";
	}else{
		$ocol = 'avg desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('incidents','g','usrname;avg((time - startinc)/3600) AS avg',$ocol,$lim,array('time',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( Smilie($r[0]),'',"$bi ctr s" );
			TblCell( $r[0] );
			TblCell( $r[1],'','',Bar($r[1],0) );
			TblCell( intval($r[2]%24)." $tim[h]",'','',Bar($r[2],24) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 4, "$row $grplbl, $srt");
}

//===================================================================
// Show Incident History
function IncHist($ina,$opa,$sta,$lim,$ord,$opt){

	global $link,$igrp,$hislbl,$tim,$durlbl;

	$dat  = getdate();
	$year = $dat['year'];
	if($lim == 20){$year -= 1;}
	elseif($lim == 50){$year -= 2;}
	elseif($lim == 100){$year -= 3;}
?>
<h2>Incident <?= $hislbl ?></h2><p>

<table class="content">
	<tr class="bgsub">
		<th>
		</th>
<?php
	$query	= GenQuery('incidents','s','incidents.*','','',array($ina),array($opa),array($sta),'', 'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$tinc = 0;
		$insta	= array();
		$inusr  = array();
		while( $r = DbFetchRow($res) ){
			$indev[$r[0]] = $r[2];
			$insta[$r[0]] = $r[4];
			$ingrp[$r[0]] = $r[8];
			if($r[5]){
				$inend[$r[0]] = $r[5];
			}else{
				$inend[$r[0]] = $dat[0];
			}
			$tinc++;
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}

	for($d=1;$d < 32;$d++){
		echo "\t\t<th>\n\t\t\t$d\n\t\t</th>\n";
	}
	$row = 0;
	$prevm = "";
	for($t = strtotime("1/1/$year");$t < $dat[0];$t += 86400){
		$then = getdate($t);
		if($prevm != $then['month']){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			echo "\t</tr>\n\t<tr class=\"$bg\">\n\t\t<th class=\"bgsub s\">\n\t\t\t". substr($then['month'],0,3)." $then[year]\n\t\t</th>\n";
		}
		foreach($insta as $id => $st){
			if($st < ($t + 86400) ){
				if($inend[$id] < $t){
					unset($insta[$id]);				# Speeds up this nasty loop towards the end!
					unset($inend[$id]);
				}else{
					$curi[$t][] = $id;
				}
			}
		}
		if($then['wday'] == 0 or $then['wday'] == 6){
			$cl = "olv";
		}else{
			$cl = "gry";
		}
		echo "\t\t<th class=\"$cl\">\n";
		if( isset($curi[$t]) ){
			sort($curi[$t]);
			if($opt){
				$ni = 0;
				foreach($curi[$t] as $id){
					$ni++;
					$tit  = $indev[$id] . ": " .$igrp[$ingrp[$id]] . ", $durlbl: ".date($_SESSION['timf'],$insta[$id])." - ".date($_SESSION['timf'],$inend[$id]);
					echo "\t\t\t<a href=Monitoring-Incidents.php?id=$id>";
					echo "<img src=\"img/16/".IncImg($ingrp[$id]).".png\" title=\"$tit\">";
					if ($ni == 4){echo "<br>\n\t\t\t";$ni = 0;}
					echo "</a>\n";
				}
			}else{
				$ninc = count($curi[$t]);
				if($ninc == 1){
					$ico = "fobl";
				}elseif($ninc < 3){
					$ico = "fovi";
				}elseif($ninc < 5){
					$ico = "foye";
				}elseif($ninc < 10){
					$ico = "foor";
				}else{
					$ico = "ford";
				}
				echo "\t\t\t<img src=\"img/16/$ico.png\" title=\"$then[weekday]: $ninc Incidents $totlbl\"></a>\n";
			}
		}else{
			echo "\t\t\t".substr($then['weekday'],0,2)."\n";
		}
		echo "\t\t</th>\n";
		$prevm = $then['month'];
	}
	echo "\t</tr>\n</table>\n";
}

//===================================================================
// Show Incident Groups
function IncGroup($ina,$opa,$sta,$lim,$ord){

	global $link,$grplbl,$srtlbl,$dislbl,$qtylbl,$igrp,$tim,$totlbl,$avglbl,$durlbl,$endlbl;
?>
<h2>Incident <?= $grplbl ?></h2>

<table class="full fixed"><tr><td class="helper">

<h2><?= $grplbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/ugrp.png"><br>
			<?= $grplbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'grp';
		$srt = "$srtlbl: $grplbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	if($ina == "class"){$ina = "grp";}
	$query	= GenQuery('incidents','g','grp',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<img src=\"img/16/".IncImg($r[0]).".png\">" );
			TblCell( $igrp[$r[0]],"Monitoring-Incidents.php?grp=$r[0]" );
			TblCell( $r[1],'','',Bar($r[1],'lvl100','sbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 4, "$row $grplbl, $srt");
?>

</td><td class="helper">

<h2><?= $avglbl ?> <?= $durlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/ugrp.png"><br>
			<?= $grplbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?> (<?= $endlbl ?>)

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $avglbl ?> <?= $durlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'grp';
	}else{
		$ocol = 'avg desc';
	}
	$query	= GenQuery('incidents','g','grp;avg((endinc - startinc)/60) AS avg',$ocol,$lim,array('endinc',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<img src=\"img/16/".IncImg($r[0]).".png\">" );
			TblCell( $igrp[$r[0]],"Monitoring-Incidents.php?grp=$r[0]" );
			TblCell( $r[1],'','',Bar($r[1],'lvl100','sbar') );
			TblCell( intval($r[2]/60)." $tim[h] ".($r[2]%60)." $tim[i]",'','',Bar($r[2],'lvl100','sbar') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 4, "$row $grplbl, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// Show Incident Distribution
function IncDist($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$conlbl,$srclbl,$mbak,$mico,$place,$locsep,$loclbl,$dislbl,$qtylbl;
?>
<h2>Incident <?= $dislbl ?></h2>

<table class="full fixed"><tr><td class="helper">

<h2><?= $srclbl ?></h2>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			<?= $srclbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'name';
		$srt = "$srtlbl: $srclbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$areg	= array();
	$acty	= array();
	$abld	= array();
	$ireg	= array();
	$icty	= array();
	$ibld	= array();
	$query	= GenQuery('incidents','g','name,location,contact,level',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $r[1]);
			$ireg["$l[0]"] += $r[4];
			$icty["$l[0]$locsep$l[1]"] += $r[4];
			$ibld["$l[0]$locsep$l[1]$locsep$l[2]"] += $r[4];
			TblRow( $bg );
			TblCell( '','',$mbak[$r[3]]." ctr xs","+<img src=\"img/16/".$mico[$r[3]].".png\" title=\"$conlbl: $r[3], $loclbl: $l[0] $l[1] $l[2]\">" );
			TblCell( $r[0],"Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=".urlencode($r[0]) );
			TblCell( $r[4],'','',Bar($r[4],10) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}
	TblFoot("bgsub", 4, "$row Incidents, $srt");
?>

</td><td class="helper">

<h2><?= $place['r'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
	if($ord){
		ksort($ireg);
		ksort($icty);
		ksort($ibld);
	}else{
		arsort($ireg);
		arsort($icty);
		arsort($ibld);
	}
	$row = 0;
	foreach ($ireg as $r => $ni){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		TblRow( $bg );
		TblCell( '','',"$bi ctr xs","+<img src=\"img/regg.png\" title=\"$place[r]\">" );
		TblCell( substr($r,0,$_SESSION['lsiz']),"Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$r$locsep%") );
		TblCell( $ni,'','',Bar($ni,10) );
		echo "\t</tr>\n";
	}
?>
</table>
<p>

<h2><?= $place['c'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
	foreach ($icty as $c => $ni){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$l = explode($locsep, $c);
		TblRow( $bg );
		TblCell( '','',"$bi ctr xs","+<img src=\"img/cityg.png\" title=\"$place[c]\">" );
		TblCell( substr($l[1],0,$_SESSION['lsiz']).", ".substr($l[0],0,$_SESSION['lsiz']),"Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$c$locsep%") );
		TblCell( $ni,'','',Bar($ni,10) );
		echo "\t</tr>\n";
	}
?>
</table>
<p>

<h2><?= $place['b'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/bomb.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
	foreach ($ibld as $b => $ni){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$l = explode($locsep, $b);
		TblRow( $bg );
		TblCell( '','',"$bi ctr xs","+<img src=\"img/blds.png\" title=\"$place[b]\">" );
		TblCell( substr($l[2],0,$_SESSION['lsiz']).", ".substr($l[1],0,$_SESSION['lsiz']),"Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$b$locsep%") );
		TblCell( $ni,'','',Bar($ni,10) );
		echo "\t</tr>\n";
	}
?>
</table>

</td></tr></table>

<?php
}

//===================================================================
// Show PoE "Charts"
function IntPoE($ina,$opa,$sta,$lim,$ord){

	global $link,$loclbl,$locsep,$conlbl,$srtlbl,$totlbl,$qtylbl,$avglbl,$porlbl;
?>
<table class="full fixed"><tr><td class="helper">

<h2><?= $totlbl ?> IF PoE / Device</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $qtylbl ?>

		</th>
		<th>
			<img src="img/16/batt.png" title="Red threshold 1kW"><br>
			PoE <?= $totlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}else{
		$ocol = 'sum desc';
		$srt = "$srtlbl: $totlbl PoE";
	}
	$query	= GenQuery('interfaces','g','device,contact,location,icon;sum(poe) AS sum',$ocol,$lim,array('poe',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$l  = explode($locsep, $r[2]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[3].png\" title=\"$conlbl: $r[1], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[4],"Devices-Interfaces.php?in[]=device&op[]=%3D&st[]=$ud&co[]=AND&in[]=poe&op[]=%3E&st[]=0",'ctr' );
			TblCell( round($r[5]/1000,2)." W",'','',Bar($r[5]/1000,500) );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row PoE Devices, $srt");
?>

</td><td class="helper">

<h2>PoE <?= $avglbl ?> / <?= $porlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $qtylbl ?>

		</th>
		<th>
			<img src="img/16/batt.png" title="Red threshold 1kW"><br>
			PoE <?= $avglbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}else{
		$ocol = 'avg desc';
		$srt = "$srtlbl: $avglbl PoE";
	}
	$query	= GenQuery('interfaces','g','device,contact,location,icon;avg(poe) AS avg',$ocol,$lim,array('poe',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$l  = explode($locsep, $r[2]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[3].png\" title=\"$conlbl: $r[1], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[4],"Devices-Interfaces.php?in[]=device&op[]=%3D&st[]=$ud&co[]=AND&in[]=poe&op[]=%3E&st[]=0",'ctr' );
			TblCell( round($r[5]/1000,2)." W",'','',Bar($r[5]/100,70) );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row PoE Devices, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// Interface Summary
function IntSum($ina,$opa,$sta,$lim,$ord){

	global $link,$opt,$srtlbl,$porlbl,$typlbl,$dislbl,$qtylbl,$conlbl,$totlbl,$stalbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $porlbl ?> <?= $typlbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $qtylbl ?>
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'iftype';
		$srt = "$srtlbl: $typlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
#	if($opt){
		$query	= GenQuery('interfaces','g','iftype',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
#	}else{
#	}
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			list($ifimg,$iftyp) = Iftype($r[0]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<img src=\"img/$ifimg\">" );
			TblCell( $iftyp,"Reports-Interfaces.php?in[]=iftype&op[]=%3D&st[]=$r[0]&rep[]=sum",'b' );
			TblCell( $r[1],"Devices-Interfaces.php?in[]=iftype&op[]=%3D&st[]=$r[0]".AddFilter($ina,$opa,$sta),'',Bar($r[1],'lvl100','sbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row $porlbl $typlbl, $srt");
?>

</td><td class="helper">

<h2><?= $porlbl ?> <?= $stalbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/idea.png"><br>
			<?= $stalbl ?>
		</th>
		<th class="l">
			<img src="img/16/port.png"><br>
			<?= $qtylbl ?>
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'ifstat';
		$srt = "$srtlbl: $stalbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
#	if($opt){
		$query	= GenQuery('interfaces','g','ifstat',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
#	}else{
#	}
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			list($ifb,$ifs)	= Ifdbstat($r[0]);
			TblRow( $bg );
			TblCell( "$ifs ($r[0])",'',"$ifb b" );
			TblCell( $r[1],"Devices-Interfaces.php?in[]=ifstat&op[]=%3D&st[]=$r[0]".AddFilter($ina,$opa,$sta),'',Bar($r[1],'lvl100','sbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row $porlbl $stalbl, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// Active Interfaces based on inoctets
function IntActiv($ina,$opa,$sta,$lim,$ord){

	global $link,$opt,$optlbl,$porlbl,$typlbl,$trflbl,$inblbl,$alllbl,$conlbl,$fullbl,$emplbl,$totlbl,$stco;
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= (($verb1)?"$fullbl Devices":"Devices $fullbl") ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $totlbl ?> <?= $porlbl ?>
		</th>
		<th>
			<img src="img/16/bbup.png" title="<?= $inblbl ?> <?= $trflbl ?>"><br>
			<?= $stco['100'] ?>

		</th>
	</tr>
<?php
	if($opt){
		$query	= GenQuery('interfaces','g','device,icon,contact;sum(case when dinoct>71 then 1 else 0 end) AS actif,sum(case when dinoct>71 then 1 else 0 end)*1000/count(*) AS usedif','usedif desc',$lim,array('iftype','services',$ina),array('~','COL &2=',$opa),array('^(6|7|117)$','2',$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	}else{
		$query	= GenQuery('interfaces','g','device,icon,contact;sum(case when dinoct>71 then 1 else 0 end) AS actif,sum(case when dinoct>71 then 1 else 0 end)*1000/count(*) AS usedif','usedif desc',$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	}
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			$ud = urlencode($r[0]);
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[1].png\" title=\"$conlbl: $r[1]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[3],'','ctr' );
			TblCell( round($r[5]/10,1)."% ($r[4])","Devices-Interfaces.php?in[]=device&op[]=%3D&st[]=$ud&co[]=AND&in[]=dinoct&op[]=>&st[]=71",'',Bar($r[5]/10,48) );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row ".(($opt)?"Bridge & $typlbl = Ethernet":"Devices, $typlbl = $alllbl").", $srt");
?>

</td><td class="helper">

<h2><?= (($verb1)?"$emplbl Devices":"Devices $emplbl") ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $totlbl ?> <?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/bbup.png" title="<?= $inblbl ?> <?= $trflbl ?>"><br>
			<?= $stco['100'] ?>

		</th>
	</tr>
<?php
	if($opt){
		$query	= GenQuery('interfaces','g','device,icon,contact;sum(case when inoct>71 then 1 else 0 end) AS actif,sum(case when inoct>71 then 1 else 0 end)*1000/count(*) AS usedif','usedif',$lim,array('iftype','services',$ina),array('~','COL &2=',$opa),array('^(6|7|117)$','2',$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	}else{
		$query	= GenQuery('interfaces','g','device,icon,contact;sum(case when inoct>71 then 1 else 0 end) AS actif,sum(case when inoct>71 then 1 else 0 end)*1000/count(*) AS usedif','usedif',$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	}
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[1].png\" title=\"$conlbl: $r[1]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[3],'','ctr' );
			TblCell( round($r[5]/10,1)."% ($r[4])","Devices-Interfaces.php?in[]=device&op[]=%3D&st[]=$ud&co[]=AND&in[]=dinoct&op[]=>&st[]=71",'',Bar($r[5]/10,48) );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row ".(($opt)?"Bridge & $typlbl = Ethernet":"Devices, $typlbl = $alllbl").", $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// Disabled Interfaces
function IntDis($ina,$opa,$sta,$lim,$ord){

	global $link,$dsalbl,$srtlbl,$loclbl,$locsep,$porlbl,$conlbl,$notlbl,$totlbl;
?>

<h2><?= $porlbl ?> <?= $dsalbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device

		</th>
		<th>
			<img src="img/16/glob.png"><br>
			IP
		</th>
		<th>
			<img src="img/16/bdis.png" title="<?= $notlbl ?> Loopback"><br>
			<?= $porlbl ?> <?= $dsalbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'devip';
		$srt = "$srtlbl: IP";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}
	$query	= GenQuery('interfaces','s','device,ifname,iftype,alias,devip,cliport,contact,location,icon',$ocol,$lim,array('ifstat','iftype',$ina),array('=','!=',$opa),array('0','24',$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	$nif = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			list($ifimg,$iftyp) = Iftype($r[2]);
			$curi = "<img src=\"img/$ifimg\" title=\"$iftyp $r[3]\">$r[1] &nbsp;";
			if($r[0] == $prev){
				echo $curi;
			}else{
				$prev = $r[0];
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$ud = urlencode($r[0]);
				$l  = explode($locsep, $r[7]);
				TblRow( $bg );
				TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[8].png\" title=\"$conlbl: $r[6], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
				TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
				TblCell( Devcli(long2ip($r[4]),$r[5]) );
				echo "\t\t<td>\n\t\t\t$curi ";
			}
			$nif++;
		}
		echo "\t\t</td>\n\t</tr>\n";
	}
	TblFoot("bgsub", 4, "$row Devices, $nif $porlbl $dsalbl, $srt");
}

//===================================================================
// Interface Charts
// *800 at the end to avoid potential overflow due to very big numbers (might help?)
//function IntChart($query,$mode,$title,$icon,$sort){
function IntChart($mode,$dir,$ina,$opa,$sta,$lim,$ord,$opt){

	global $link,$rrdstep;
	global $porlbl,$laslbl,$totlbl,$loclbl,$locsep,$conlbl,$srtlbl,$trflbl,$errlbl,$inblbl,$oublbl,$idxlbl,$spdlbl,$dcalbl,$nonlbl;

	$unt = '';
	$grf = intval($_SESSION['gsiz'] / 2);
	$dti = ($dir == "in")?$inblbl:$oublbl;

	if($opt){
		$pes = 0;
		$d   = "";
		$abs = $totlbl;
	}else{
		$pes = 1;
		$d  = "d";
		$abs  = $laslbl;
	}

	if($ord){
		$ocol = "aval desc";
		$sopt = "";
	}else{
		$ocol = "rval desc";
		$sopt = ($mode == "trf")?"/ $spdlbl":"/ $trflbl";
	}

	if($mode == "trf"){
		$pes = 0;
		$col = "oct";
		$tit = $trflbl;
		$ico = ($dir == "in")?"bbup":"bbdn";
		if($opt){
			$rel = "${d}${dir}oct/speed";
		}else{
			$rel = "${d}${dir}oct/$rrdstep*8000/speed";
		}
		$qry = GenQuery('interfaces','s',"device,contact,location,icon,ifname,speed,iftype,ifidx,comment,alias,${d}${dir}oct as aval,$rel as rval",$ocol,$lim,array('speed','trafalert',$ina),array('>','<',$opa),array('0',100,$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	}elseif($mode == "err"){
		$col = "err";
		$tit = $errlbl;
		$ico = ($dir == "in")?"brup":"brdn";
		$qry = GenQuery('interfaces','s',"device,contact,location,icon,ifname,speed,iftype,ifidx,comment,alias,$d$dir$col as aval,$d$dir$col/${d}${dir}oct as rval",$ocol,$lim,array('iftype',"$d$dir$col",$ina),array('!=','>',$opa),array('71',0,$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	}elseif($mode == "dsc"){
		$col = "dis";
		$tit = $dcalbl;
		$ico = ($dir == "in")?"bbu2":"bbd2";
		$qry = GenQuery('interfaces','s',"device,contact,location,icon,ifname,speed,iftype,ifidx,comment,alias,$d$dir$col as aval,$d$dir$col/${d}${dir}oct as rval",$ocol,$lim,array('iftype',"$d$dir$col",$ina),array('!=','>',$opa),array('71',0,$sta),array('AND','AND'),'LEFT JOIN devices USING (device)');
	}elseif($mode == "brc"){
		$tit = "Broadcasts";
		$ico = "brc";
		$qry = GenQuery('interfaces','s',"device,contact,location,icon,ifname,speed,iftype,ifidx,comment,alias,${d}inbrc as aval,${d}inbrc/${d}inoct as rval",$ocol,$lim,array("${d}inoct",$ina),array('>',$opa),array(0,$sta),array('AND'),'LEFT JOIN devices USING (device)');
	}

	echo "<h2>$abs $tit $dti</h2>\n";

	$res = DbQuery($qry,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/<?= $ico ?>.png"><br>
			<?= $tit ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$ui = urlencode($r[4]);
			$l  = explode($locsep, $r[2]);
			list($ifimg,$iftyp) = Iftype($r[6]);

			if($mode == "trf" and !$opt){
				$bar = Bar($r[11]/10,49).round($r[11]/10,1).'%';
			}elseif($mode == "brc"){
				$bar = Bar($r[10],"lvl100",'sbar')." ".DecFix($r[10]);
			}else{
				$bar = Bar($r[10],10,'sbar')." ".DecFix($r[10]);
			}

			if($grf){
				if($mode == "trf"){
					$gop = $r[5];
				}elseif($mode == "err"){
					$gop = 1;
				}else{
					$gop = 0;
				}
				$gr = "<img src=\"inc/drawrrd.php?dv=$ud&if[]=$ui&s=$grf&t=$mode&o=$gop\">";
			}else{
				$gr = DecFix($r[10]);
			}
			$pesbar = ($pes)?Bar($r[10]/$rrdstep,50,'sbar').round($r[10]/$rrdstep,1)."/s<br>\n":'';
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[3].png\" title=\"$conlbl: $r[1], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( " <a class=\"b\" href=\"Devices-Interfaces.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui&col[]=imBL&col[]=ifname&col[]=alias&col[]=comment&col[]=poNS&col[]=gfNS&col[]=rdrNS\">$r[4]</a> ".DecFix($r[5])." $r[8] $r[9]",'','',"+<img src=\"img/$ifimg\" title=\"$iftyp $idxlbl $r[7]\">" );
			TblCell( $gr,"Devices-Graph.php?dv=$ud&if[]=$ui&it[]=".substr($mode,0,1),'ctr' );
			TblCell( $pesbar.$bar,'','nw' );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 5, "$row $porlbl, $srtlbl: $tit $sopt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
}

//===================================================================
// Interface Broadcasts
function IntBrc($ina,$opa,$sta,$lim,$ord,$opt){

	global $srtlbl,$errlbl,$inblbl,$oublbl,$spdlbl,$rrdstep;

?>

<table class="full fixed"><tr><td class="helper">

<?php
	IntChart("brc","in",$ina,$opa,$sta,$lim,$ord,0);
?>

</td><td class="helper">

<?php
	IntChart("brc","in",$ina,$opa,$sta,$lim,$ord,1);
?>

</td></tr></table>

<?php
}

//===================================================================
// Interface Discards
function IntDsc($ina,$opa,$sta,$lim,$ord,$opt){

	global $srtlbl,$errlbl,$inblbl,$oublbl,$spdlbl,$rrdstep;

?>

<table class="full fixed"><tr><td class="helper">

<?php
	IntChart("dsc","in",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td><td class="helper">

<?php
	IntChart("dsc","out",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td></tr></table>

<?php
}

//===================================================================
// Interface Errors
function IntErr($ina,$opa,$sta,$lim,$ord,$opt){

	global $srtlbl,$errlbl,$inblbl,$oublbl,$spdlbl,$rrdstep;

?>

<table class="full fixed"><tr><td class="helper">

<?php
	IntChart("err","in",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td><td class="helper">

<?php
	IntChart("err","out",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td></tr></table>

<?php
}

//===================================================================
// Interface Traffic
function IntTrf($ina,$opa,$sta,$lim,$ord,$opt){

?>

<table class="full fixed"><tr><td class="helper">

<?php
	IntChart("trf","in",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td><td class="helper">

<?php
	IntChart("trf","out",$ina,$opa,$sta,$lim,$ord,$opt);
?>

</td></tr></table>

<?php
}

//===================================================================
// Link Status Errors
function LnkErr($ina,$opa,$sta,$lim,$ord){

	global $link,$stalbl,$srtlbl,$errlbl,$neblbl,$porlbl,$spdlbl,$typlbl,$sndlbl,$nonlbl;

	if($ord){
		$ocol = 'neighbor';
		$srt = "$srtlbl: $neblbl";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}

	echo "<h2>Link $spdlbl $errlbl</h2>\n";

	$query = GenQuery('links as l1 ','s','l1.device,l1.ifname,l1.neighbor,l1.nbrifname,l1.bandwidth,l2.bandwidth as l2bw',$ocol,$lim,array('l1.bandwidth',$ina),array('COL !=',$opa),array('l2.bandwidth',$sta),array('AND'),'JOIN links as l2 on (l1.device = l2.neighbor and l1.ifname = l2.nbrifname) LEFT JOIN devices on (l1.device = devices.device)');
	$res  = @DbQuery($query,$link);
	if( DbNumRows($res) ){
?>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/spd.png"><br>
			<?= $spdlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			<?= $neblbl ?>

		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/spd.png"><br>
			<?= $spdlbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = @DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[1] );
			TblCell( DecFix($r[4]),'',"$bi ctr b" );
			TblCell( substr($r[2],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[3] );
			TblCell( DecFix($r[5]),'',"$bi ctr b" );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 6, "$row $spdlbl $errlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}

	if($ord){
		$ocol = 'neighbor';
		$srt = "$srtlbl: $neblbl";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}

	echo "<h2>Link Duplex $errlbl</h2>\n";

	$query = GenQuery('links as l1 ','s','l1.device,l1.ifname,l1.neighbor,l1.nbrifname,l1.nbrduplex,l2.nbrduplex as l2dup',$ocol,$lim,array('l1.nbrduplex',$ina),array('COL !=',$opa),array('l2.nbrduplex',$sta),array('AND'),'JOIN links as l2 on (l1.device = l2.neighbor and l1.ifname = l2.nbrifname) LEFT JOIN devices on (l1.device = devices.device)');
	$res = @DbQuery($query,$link);
	if( DbNumRows($res) ){
?>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/dpx.png"><br>
			Duplex
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			<?= $neblbl ?>

		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/dpx.png"><br>
			Duplex
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = @DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[1] );
			TblCell( $r[4],'',"$bi ctr b" );
			TblCell( substr($r[2],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[3] );
			TblCell( $r[5],'',"$bi ctr b" );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 6, "$row Duplex $errlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}

	if($ord){
		$ocol = 'neighbor';
		$srt = "$srtlbl: $neblbl";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}

	echo "<h2>Link Vlan $errlbl</h2>\n";

	$query	= GenQuery('links as l1 ','s','l1.device,l1.ifname,l1.neighbor,l1.nbrifname,l1.nbrvlanid,l2.nbrvlanid as l2dup',$ocol,$lim,array('l1.nbrvlanid',$ina),array('COL !=',$opa),array('l2.nbrvlanid',$sta),array('AND'),'JOIN links as l2 on (l1.device = l2.neighbor and l1.ifname = l2.nbrifname) LEFT JOIN devices on (l1.device = devices.device)');
	$res	= @DbQuery($query,$link);
	if( DbNumRows($res) ){
?>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/vlan.png"><br>
			Vlan
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			<?= $neblbl ?>

		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/vlan.png"><br>
			Vlan
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = @DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[1] );
			TblCell( $r[4],'',"$bi ctr b" );
			TblCell( substr($r[2],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[3] );
			TblCell( $r[5],'',"$bi ctr b" );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 6, "$row Vlan $errlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}

	if($ord){
		$ocol = 'neighbor';
		$srt = "$srtlbl: $neblbl";
	}else{
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}

	echo "<h2>Link $typlbl $errlbl</h2>\n";

	$query	= GenQuery('links as l1 ','s','l1.device,l1.ifname,l1.neighbor,l1.nbrifname,l1.linktype,l2.linktype as l2dup',$ocol,$lim,array('l1.linktype',$ina),array('COL !=',$opa),array('l2.linktype',$sta),array('AND'),'JOIN links as l2 on (l1.device = l2.neighbor and l1.ifname = l2.nbrifname) LEFT JOIN devices on (l1.device = devices.device)');
	$res	= @DbQuery($query,$link);
	if( DbNumRows($res) ){
?>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/abc.png"><br>
			<?= $typblbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			<?= $neblbl ?>

		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/abc.png"><br>
			<?= $typblbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = @DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[1] );
			TblCell( $r[4],'',"$bi ctr b" );
			TblCell( substr($r[2],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b' );
			TblCell( $r[3] );
			TblCell( $r[5],'',"$bi ctr b" );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 6, "$row $typlbl $errlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
}

//===================================================================
// Module Distribution
function ModDist($ina,$opa,$sta,$lim,$ord){

	global $link,$mdllbl,$dislbl,$deslbl,$typlbl,$totlbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $mdllbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/abc.png"><br>
			<?= $mdllbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
		<th>
			<img src="img/16//cubs.png"><br>
			<?= $totlbl ?>

		</th>
	</tr>
<?php
	$query = GenQuery('modules','g','model,modclass,modules.device','','',array(DbCast('modclass','character'),$ina),array('!~',$opa),array('^[345]0$',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	$nmod  = 0;
	if($res){
		$nummo	= array();
		while( $r = DbFetchRow($res) ){
			$nummo[$r[0]] += $r[3];
			$mocla[$r[0]] = $r[1];
			$modev[$r[0]][$r[2]] = $r[3];
			$nmod += $r[3];
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	if($ord){
		ksort($nummo);
	}else{
		arsort($nummo);
	}
	$row = 0;
	foreach ($nummo as $mdl => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$um = urlencode($mdl);
		list($mcl,$img) = ModClass($mocla[$mdl]);
		TblRow( $bg );
		TblCell( '','',"$bi ctr xs","+<img src=\"img/16/$img.png\">" );
		TblCell( $mdl,"Reports-Modules.php?in[]=model&op[]==&st[]=$um&rep[]=sum&rep[]=inv",'b' );
		echo "\t\t<td>\n";
		foreach ($modev[$mdl] as $dv => $ndv){
			echo "\t\t\t<a href=\"Devices-Status.php?dev=".urlencode($dv)."\">".substr($dv,0,$_SESSION['lsiz'])."</a>: <strong>$ndv</strong> &nbsp;";
		}
		echo "\t\t</td>\n";
		TblCell( $n,"Devices-Modules.php?in[]=model&op[]==&st[]=$um".AddFilter($ina,$opa,$sta),'nw','+'.Bar($n,'lvl100','sbar') );
		echo "\t</tr>\n";
		if($row == $lim){break;}
	}
	TblFoot("bgsub", 4, "$nmod Modules, $row $mdllbl");
?>

</td><td class="helper">

<h2><?= $deslbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/find.png"><br>
			<?= $deslbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
		<th>
			<img src="img/16//cubs.png"><br>
			<?= $totlbl ?>

		</th>
	</tr>
<?php
	$query = GenQuery('modules','g','moddesc,modclass,modules.device','','',array(DbCast('modclass','character'),$ina),array('!~',$opa),array('^[345]0$',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	$nmod  = 0;
	if($res){
		$nummo	= array();
		while( $r = DbFetchRow($res) ){
			$nummo[$r[0]] += $r[3];
			$mocla[$r[0]] = $r[1];
			$modev[$r[0]][$r[2]] = $r[3];
			$nmod += $r[3];
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	if($ord){
		ksort($nummo);
	}else{
		arsort($nummo);
	}
	$row = 0;
	foreach ($nummo as $des => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ud = urlencode($des);
		list($mcl,$img) = ModClass($mocla[$des]);
		TblRow( $bg );
		TblCell( '','',"$bi ctr xs","+<img src=\"img/16/$img.png\">" );
		TblCell( $des,"Reports-Modules.php?in[]=moddesc&op[]==&st[]=$ud&rep[]=sum&rep[]=inv",'b' );
		echo "\t\t<td>\n";
		foreach ($modev[$des] as $dv => $ndv){
			echo "\t\t\t<a href=\"Devices-Status.php?dev=".urlencode($dv)."\">".substr($dv,0,$_SESSION['lsiz'])."</a>: <strong>$ndv</strong> &nbsp;";
		}
		echo "\t\t</td>\n";
		TblCell( $n,"Devices-Modules.php?in[]=moddesc&op[]==&st[]=$ud".AddFilter($ina,$opa,$sta),'nw','+'.Bar($n,'lvl100','sbar') );
		echo "\t</tr>\n";
		if($row == $lim){break;}
	}
	TblFoot("bgsub", 4, "$nmod Modules, $row $deslbl");
?>

</td></tr></table>

<?php
}

//===================================================================
// Modules per Devices
function ModInventory($ina,$opa,$sta,$lim,$ord){

	global $link,$inflbl,$srtlbl,$typlbl,$invlbl,$serlbl;

?>
<h2>Module <?= $invlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device / Slot
		</th>
		<th>
			<img src="img/16/find.png"><br>
			<?= $inflbl ?>

		</th>
		<th>
			<img src="img/16/key.png"><br>
			<?= $serlbl ?>

		</th>
		<th>
			<img src="img/16/card.png"><br>
			HW
		</th>
		<th>
			<img src="img/16/cog.png"><br>
			FW
		</th>
		<th>
			<img src="img/16/cbox.png"><br>
			SW
		</th>
	</tr>
<?php
	if($ord){
		$ocol = "type";
		$srt = "$srtlbl: $typlbl";
	}else{
		$ocol = "device";
		$srt = "$srtlbl: Device";
	}
	$query = GenQuery('devices','s','distinct device,type,devices.serial,bootimage,location,contact',$ocol,'',array('devos',$ina),array('!~',$opa),array('^(Printer|ESX)$',$sta),array('AND'),'LEFT JOIN modules USING (device)');
	$res   = DbQuery($query,$link);
	$dev   = 0;
	$modu  = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			$dev++;
			$utyp = urlencode($r[1]);
			$usw  = urlencode($r[3]);

			TblRow('imgb');
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Status.php?dev=".urlencode($r[0]),'b');
			TblCell( $r[1],"?in[]=type&op[]==&st[]=$utyp&rep[]=inv",'',"+<a href=\"Devices-List.php?in[]=type&op[]==&st[]=$utyp\"><img src=\"img/16/dev.png\" title=\"Devices-List\"></a>");
			TblCell( InvCheck($r[2],$r[3],3,$r[4],$r[5]) );
			TblCell('-');
			TblCell($r[3],"Devices-List.php?in[]=bootimage&op[]==&st[]=$usw");
			TblCell('-');
			echo "\t</tr>\n";
			$query	= GenQuery('modules','s','*','modidx','',array('device'),array('='),array($r[0]));
			$mres	= DbQuery($query,$link);
			if($mres){
				while( ($m = DbFetchRow($mres)) ){
					if ($modu % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
					$modu++;
					list($mcl,$img) = ModClass($m[9]);
					$umdl = urlencode($m[2]);
					TblRow( $bg );
					TblCell($m[1],'','rgt');
					TblCell("<strong> $m[2]</strong> $m[3]",'','',"+<a href=\"Devices-Modules.php?in[]=model&op[]==&st[]=$umdl\"><img src=\"img/16/$img.png\" title=\"$mcl, Modules-List\"></a>");
					TblCell( InvCheck($m[4],$m[2],$m[9],$r[4],$r[5]) );
					TblCell($m[5]);
					TblCell($m[6]);
					TblCell($m[7]);
					echo "\t</tr>\n";
				}
				DbFreeResult($mres);
			}else{
				echo DbError($link);
			}
		}
		DbFreeResult($res);
	}else{
		echo DbError($link);
	}
	TblFoot("bgsub", 6, "$dev Devices, $modu Modules, $srt");
}

//===================================================================
// Printsupplies Inventory & Levels
function ModPrint($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$stalbl,$typlbl,$loclbl,$locsep;
?>

<h2>Printsupplies</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/print.png"><br>
			Printer
		</th>
		<th>
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/user.png"><br>
			<?= $conlbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/file.png"><br>
			Supplies
		</th>
	</tr>
<?php
	$nprt = 0;
	if($ord){
		$ocol = "location";
		$srt = "$srtlbl: $loclbl";
	}else{
		$ocol = "status";
		$srt = "$srtlbl: $stalbl";
	}
	$query = GenQuery('modules','s','modules.*,location,contact,icon',$ocol,$lim,array('devos',$ina),array('=',$opa),array('Printer',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	$row   = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$l  = explode($locsep, $r[12]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[14].png\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( "$l[2] $l[3] $l[4]" );
			TblCell( $r[13] );
			TblCell( $r[3],'','','+'.PrintSupply($r[1]) );
			TblCell( "$r[10]%",'','','+'.Bar($r[10],-33) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		echo DbError($link);
	}
	TblFoot("bgsub", 6, "$row Printers, $srt");
}

//===================================================================
// Virtualmachine Inventory
function ModVM($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$memlbl,$poplbl,$dislbl,$loclbl,$conlbl,$locsep;
?>
<h2>VM <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/cog.png"><br>
			Hypervisor
		</th>
		<th>
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/user.png"><br>
			<?= $conlbl ?>
		</th>
		<th>
			<img src="img/16/node.png"><br>
			VM <?= $poplbl ?>

		</th>
		<th>
			<img src="img/16/cpu.png"><br>
			CPUs
		</th>
		<th>
			<img src="img/16/mem.png"><br>
			<?= $memlbl ?>

		</th>
	</tr>
<?php
	$nprt = 0;
	if($ord){
		$ocol = "location";
		$srt = "$srtlbl: $loclbl";
	}else{
		$ocol = "cnt desc";
		$srt = "$srtlbl: $poplbl";
	}
	$query = GenQuery('modules','g','device,icon,location,contact;sum('.DbCast('modules.serial','integer').') as cpu,sum('.DbCast('fw','integer').')/1024 as mem',$ocol,$lim,array('devos',$ina),array('=',$opa),array('ESX',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	$row   = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$l = explode($locsep, $r[2]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[1].png\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( "$l[2], $l[4] $l[5]" );
			TblCell( $r[3] );
			TblCell( $r[4],"Devices-Modules.php?in[]=device&op[]==&st[]=$ud",'','+'.Bar($r[4],'lvl100') );
			TblCell( $r[5],'','','+'.Bar($r[5],'lvl150') );
			TblCell( round($r[6],1)."GB",'','','+'.Bar($r[6],'lvl50') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		echo DbError($link);
	}
	TblFoot("bgsub", 7, "$row Hypervisors, $srt");
}

//===================================================================
// Monitoring Availability
function MonAvail($ina,$opa,$sta,$lim,$ord){

	global $link,$dislbl,$tgtlbl,$place,$locsep,$loclbl,$srtlbl,$conlbl,$avalbl,$totlbl;
?>

<h2><?= $avalbl ?> <?= $dislbl ?></h2>

<table class="full fixed"><tr><td class="helper">

<h2><?= $tgtlbl ?> <?= $avalbl ?> < 100%</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/walk.png"><br>
			<?= $avalbl ?>

		</th>
<?php
	if($ord){
		$ocol = "name";
		$srt = "$srtlbl: $tgtlbl";
	}else{
		$ocol = "relav";
		$srt = "$srtlbl: $avalbl";
	}
	$areg	= array();
	$acty	= array();
	$abld	= array();
	$query	= GenQuery('monitoring','s','name,test,1000*ok/(lost+ok) as relav,location,contact,class,icon',$ocol,$lim,array('ok','lost',$ina),array('COL >','COL >',$opa),array('0','0',$sta),array('OR','AND'),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	$row	= 0;
	if($res){
		while( ($r = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ut  = urlencode($r[0]);
			$l   = explode($locsep, $r[3]);
			$rea = $r[2]/10;
			if($l[0]){
				$nreg["$l[0]"]++;
				$areg["$l[0]"] = (($nreg["$l[0]"] - 1) * $areg["$l[0]"] + $rea)/$nreg["$l[0]"];
			}
			if($l[1]){
				$ncty["$l[0]$locsep$l[1]"]++;
				$acty["$l[0]$locsep$l[1]"] = (($ncty["$l[0]$locsep$l[1]"] - 1) * $acty["$l[0]$locsep$l[1]"] + $rea)/$ncty["$l[0]$locsep$l[1]"];
			}
			if($l[2]){
				$nbld["$l[0]$locsep$l[1]$locsep$l[2]"]++;
				$abld["$l[0]$locsep$l[1]$locsep$l[2]"] = (($nbld["$l[0]$locsep$l[1]$locsep$l[2]"] - 1) * $abld["$l[0]$locsep$l[1]$locsep$l[2]"] + $rea)/$nbld["$l[0]$locsep$l[1]$locsep$l[2]"];
			}
			if( $rea < 100 ){
				TblRow( $bg );
				TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ut\"><img src=\"img/".(($r[5] == "dev")?"dev/$r[6]":"32/node").".png\" title=\"$conlbl: $r[4], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
				TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
				TblCell( '','',"$bi ctr xs",'+'.TestImg($r[1]) );
				TblCell( Bar($rea,-99).round($rea,3)."%","Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=$ut" );
				echo "\t</tr>\n";
			}
		}
		DbFreeResult($res);
	}else{
		echo DbError($link);
	}
	TblFoot("bgsub", 4, "$row $totlbl, $srt");
?>

</td><td class="helper">

<?php if($row > 1){?>

<h2><?= $place['r'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th class="l">
			<img src="img/16/walk.png"><br>
			<?= $avalbl ?>

		</th>
	</tr>
<?php
	if($ord){
		ksort($areg);
		ksort($acty);
		ksort($abld);
	}else{
		asort($areg);
		asort($acty);
		asort($abld);
	}
	$row = 0;
	foreach ($areg as $r => $ra){
		if( $ra < 100 ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<img src=\"img/regg.png\" title=\"$place[r] $r\">" );
			TblCell( substr($r,0,$_SESSION['lsiz']),'','b' );
			TblCell( Bar($ra,-99).round($ra,2)."%","Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$r$locsep%") );
			echo "\t</tr>\n";
		}
	}
?>
</table>

<h2><?= $place['c'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th class="l">
			<img src="img/16/walk.png"><br>
			<?= $avalbl ?>

		</th>
	</tr>
<?php
	foreach ($acty as $c => $ca){
		if( $ca < 100 ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $c);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<img src=\"img/cityg.png\" title=\"$place[c] $c\">" );
			TblCell( substr("$l[1], $l[0]",0,$_SESSION['lsiz']),'','b' );
			TblCell( Bar($ca,-99).round($ca,2)."%","Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$c$locsep%") );
			echo "\t</tr>\n";
		}
	}
?>
</table>

<h2><?= $place['b'] ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th class="l">
			<img src="img/16/walk.png"><br>
			<?= $avalbl ?>

		</th>
	</tr>
<?php
	foreach ($abld as $b => $ba){
		if( $ba < 100 ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l = explode($locsep, $b);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<img src=\"img/blds.png\" title=\"$place[b] $b\">" );
			TblCell( substr("$l[2], $l[1]",0,$_SESSION['lsiz']),'','b' );
			TblCell( Bar($ba,-99).round($ba,2)."%","Monitoring-Setup.php?in[]=location&op[]=LIKE&st[]=".urlencode("$b$locsep%") );
		}
	}
?>
</table>

<?php } ?>

</td></tr></table>

<?php
}

//===================================================================
// Monitoring Events
function MonEvent($ina,$opa,$sta,$lim,$ord,$opt){

	global $link,$opt,$srtlbl,$optlbl,$levlbl,$clalbl,$dislbl,$srclbl,$loclbl,$locsep,$totlbl,$msglbl,$mico,$mlvl;
?>

<h2><?= $msglbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2" class="m">
			<img src="img/16/say.png"><br>
			<?= $srclbl ?>

		</th>
		<th class="s">
			<img src="img/16/form.png"><br>
			<?= $totlbl ?>

		</th>
		<th>
			<img src="img/16/<?= $mico['10'] ?>.png"><br>
			<?= $mlvl['30'] ?>
		</th>
		<th>
			<img src="img/16/<?= $mico['50'] ?>.png"><br>
			<?= $mlvl['50'] ?>
		</th>
		<th>
			<img src="img/16/<?= $mico['100'] ?>.png"><br>
			<?= $mlvl['100'] ?>
		</th>
		<th>
			<img src="img/16/<?= $mico['150'] ?>.png"><br>
			<?= $mlvl['150'] ?>
		</th>
		<th>
			<img src="img/16/<?= $mico['200'] ?>.png"><br>
			<?= $mlvl['200'] ?>
		</th>
		<th>
			<img src="img/16/<?= $mico['250'] ?>.png"><br>
			<?= $mlvl['250'] ?>
		</th>
	</tr>
<?php
	$ina = ($ina == 'name')?'source':$ina;
	if($ord){
		$ocol = "source";
		$srt  = "$srtlbl: $srclbl";
	}else{
		$ocol = "cnt desc";
		$srt  = "$srtlbl: $msglbl";
	}
	$cols = 'source,class,icon;sum(case when level=10 then 1 else 0 end),sum(case when level=50 then 1 else 0 end),sum(case when level=100 then 1 else 0 end),sum(case when level=150 then 1 else 0 end),sum(case when level=200 then 1 else 0 end),sum(case when level=250 then 1 else 0 end)';
	if($opt){
		$query = GenQuery('events','g',$cols,$ocol,$lim,array('class','class',$ina),array('=','=',$opa),array('dev','node',$sta),array('OR','AND'),'LEFT JOIN devices USING (device)');
	}else{
		$query = GenQuery('events','g',$cols,$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	}
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( ($r = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$us = urlencode($r[0]);
			list($ei,$et)   = EvClass($r[1]);
			$sysl = ($opt)?"&co[]=AND&in[]=class&op[]==&st[]=$r[1]":'';
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<img src=\"$ei\" title=\"$et\">" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Monitoring-Events.php?in[]=source&op[]=%3D&st[]=".urlencode($r[0]),'b' );
			TblCell( DecFix($r[3]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us" );
			TblCell( DecFix($r[4]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=10$sysl",'',($r[4])?Bar($r[4],"lvl10",'sbar'):'' );
			TblCell( DecFix($r[5]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=50$sysl",'',($r[5])?Bar($r[5],"lvl50",'sbar'):'' );
			TblCell( DecFix($r[6]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=100$sysl",'',($r[6])?Bar($r[6],"lvl100",'sbar'):'' );
			TblCell( DecFix($r[7]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=150$sysl",'',($r[7])?Bar($r[7],"lvl150",'sbar'):'' );
			TblCell( DecFix($r[8]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=200$sysl",'',($r[8])?Bar($r[8],"lvl200",'sbar'):'' );
			TblCell( DecFix($r[9]),"Monitoring-Events.php?in[]=source&op[]==&st[]=$us&co[]=AND&in[]=level&op[]==&st[]=250$sysl",'',($r[9])?Bar($r[9],"lvl250",'sbar'):'' );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", 9, "$row ".(($opt)?"Syslog ":"")."$srclbl, $srt" );
}

//===================================================================
// Monitoring Latency
function MonLatency($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$tgtlbl,$latlbl,$latw,$loclbl,$locsep,$conlbl,$stslbl,$laslbl,$avglbl,$maxlbl;
?>

<h2><?= $latlbl ?> <?= $stslbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>

		</th>
		<th>
			<img src="img/16/bbrt.png"><br>
			<?= $laslbl ?>

		</th>
		<th>
			<img src="img/16/form.png"><br>
			<?= $avglbl ?>

		</th>
		<th>
			<img src="img/16/brup.png"><br>
			<?= $maxlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = "name";
		$srt = "$srtlbl: $tgtlbl";
	}else{
		$ocol = "latavg desc";
		$srt = "$srtlbl: $avglbl $latlbl";
	}
	$query	= GenQuery('monitoring','s','name,test,latency,latmax,latavg,location,contact,class,icon',$ocol,$lim,array('test',$ina),array('!=',$opa),array('none',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ut = urlencode($r[0]);
			$l = explode($locsep, $r[5]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ut\"><img src=\"img/".(($r[7] == "dev")?"dev/$r[8]":"32/node").".png\" title=\"$conlbl: $r[4], $loclbl: $l[0] $l[1] $l[2]\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=$ut",'b' );
			TblCell( '','',"$bi ctr xs",'+'.TestImg($r[1]) );
			TblCell( "$r[2]ms",'','','+'.Bar($r[2],$latw,'sbar') );
			TblCell( "$r[4]ms",'','','+'.Bar($r[4],$latw,'sbar') );
			TblCell( "$r[3]ms",'','','+'.Bar($r[3],$latw,'sbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 6, "$row $tgtlbl, $srt" );
}

//===================================================================
// Monitoring Uptime
function MonUptime($ina,$opa,$sta,$lim,$ord){

	global $link,$inflbl,$uptlbl,$tgtlbl,$stslbl,$tim,$place,$locsep,$loclbl,$srtlbl,$conlbl;
?>

<h2><?= $uptlbl ?> <?= $stslbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>
		</th>
		<th>
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/user.png"><br>
			<?= $conlbl ?>
		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $uptlbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'name';
		$srt = "$srtlbl: $tgtlbl";
	}else{
		$ocol = 'uptime desc';
		$srt = "$srtlbl: Uptime";
	}
	$query	= GenQuery('monitoring','s','name,uptime/360000,devip,cliport,location,contact,icon',$ocol,$lim,array('test',$ina),array('=',$opa),array('uptime',$sta),array('AND'),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$l  = explode($locsep, $r[4]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$r[6].png\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( Devcli(long2ip($r[2]),$r[3]) );
			TblCell( "$l[2] $l[3] $l[4]" );
			TblCell( $r[5] );
			TblCell( Bar(intval($r[1]/24),-2).intval($r[1]/24)." $tim[d] ".intval($r[1]%24)." $tim[h]" );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		echo DbError($link);
	}
	TblFoot("bgsub", 6, "$row $tgtlbl, $srt");
}

//===================================================================
// Network Distribution
function NetDist($ina,$opa,$sta,$lim,$ord){

	global $link,$opt,$verb1,$netlbl,$dislbl,$adrlbl,$poplbl,$maxlbl,$chglbl,$tim,$totlbl,$srtlbl;

	$nets = array();

	if($ina == "devip"){$ina = "ifip";}
	if($ord){
		$ocol = "device";
		$srt = "$srtlbl: Device";
	}else{
		$ocol = "ifip";
		$srt = "$srtlbl: IP $adrlbl";
	}
	$query	= GenQuery('networks','s','networks.*',$ocol,'',array('ifip',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)' );
	$res	= DbQuery($query,$link);
	if ($res) {
		while( ($n = DbFetchRow($res)) ){
			$n[2] = ip2long(long2ip($n[2]));						# Hack to fix signing issue for 32bit vars in PHP!
			$dmsk = 0xffffffff << (32 - $n[4]);
			$dnet = sprintf("%u",$n[2] & $dmsk);
			$vrf  = ($n[4])?"<a href=\"Topology-Networks.php?in[]=vrfname&op[]==&st[]=".urlencode($n[4])."\">$n[4]</a> ":"";

			if( array_key_exists($dnet,$nets) ){
				if($nets[$dnet] != $n[4]){
					$devs[$dnet][$n[0]]	= "$n[1] $vrf<span class=\"red\">" .long2ip($dmsk) . "</span>";
				}else{
					if($devs[$dnet][$n[0]]){
						$devs[$dnet][$n[0]]	= "$n[1]  $vrf<span class=\"grn\">multiple ok</span>";
					}else{
						$devs[$dnet][$n[0]]	= "$n[1]  $vrf<span class=\"grn\">ok</span>";
					}
				}
			}elseif($n[4]){									# Ignore /0 networks...
				$nets[$dnet] = $n[4];
				$pop[$dnet] = 0;
				$age[$dnet] = 0;
				if($n[4] == 32){
					$devs[$dnet][$n[0]] = "$n[1]  $vrf<span class=\"prp\">hostroute</span>";
				}else{
					$devs[$dnet][$n[0]] = "$n[1]  $vrf<span class=\"blu\">mask base</span>";
				}
			}
		}
		DbFreeResult($res);

		if( count($nets) ){
?>

<h2><?= $netlbl ?> <?= $dislbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/net.png"><br>
			IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Devices
		</th>
		<th colspan="2">
			<img src="img/16/nods.png"><br>
			<?= $poplbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			foreach(array_keys($nets) as $dnet ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$dvs = '';
				$net = long2ip($dnet);
				list($ntimg,$ntit) = Nettype($net);
				if( $opt ){
					foreach( array_keys($devs[$dnet]) as $dv ){
						$dvs .= "\t\t\t<a href=\"Devices-Status.php?dev=".urlencode($dv)."\">".substr($dv,0,$_SESSION['lsiz'])."</a> ".$devs[$dnet][$dv]."<br>\n";
					}
				}else{
					$ndv = count( array_keys($devs[$dnet]) );
					$dvs = Bar( $ndv,'lvl100','sbar')." <a href=\"Devices-List.php?in[]=devip&op[]==&st[]=$net%2F$nets[$dnet]\">$ndv</a>";
				}
				$dmsk = 0xffffffff << (32 - $nets[$dnet]);
				$nquery	= GenQuery('nodarp','s','count(*)','','',array("nodip & $dmsk"),array('='),array($dnet) );
				$nodres	= DbQuery($nquery,$link);
				$no     = DbFetchRow($nodres);
				if( $no[0] ){
					$rnod = round(100 / (pow(2,32-$nets[$dnet]) - 2) * $no[0],1);
					$nds  = Bar($no[0],'lvl10','sbar')."<a href=\"Nodes-List.php?in[]=nodip&op[]==&st[]=$net/$nets[$dnet]&ord=nodip\"> $no[0]</a>";
					$ndr  = Bar($rnod,50,'sbar')."<a href=\"Nodes-List.php?in[]=nodip&op[]==&st[]=$net/$nets[$dnet]&ord=nodip\"> $rnod%</a>";
				}else{
					$nds   = '';
					$ndr   = '';
				}
				DbFreeResult($nodres);
				TblRow( $bg );
				TblCell( '','',"$bi ctr xs","+<img src=\"img/$ntimg\" title=\"$ntit\">" );
				echo "\t\t<td>\n";
				if( !isset($_GET['print']) ){
					echo "\t\t\t<div class=\"frgt\">\n";
					echo "\t\t\t<a href=\"Topology-Networks.php?in[]=ifip&op[]==&st[]=$net%2F$nets[$dnet]\"><img src=\"img/16/glob.png\" title=\"Topology-Networks\"></a>\n";
					echo "\t\t\t<a href=\"Topology-Map.php?in[]=ifip&op[]==&st[]=$net%2F$nets[$dnet]&mde=f&fmt=png\"><img src=\"img/16/paint.png\" title=\"Topology-Maps\"></a>\n";
					echo "\t\t\t<a href=\"Other-Calculator.php?ip=$net&nmsk=$nets[$dnet]\"><img src=\"img/16/calc.png\" title=\"Other-Calculator\"></a>\n\t\t\t</div>\n";
				}
				echo "\t\t\t<a href=\"?in[]=devip&op[]==&st[]=$net%2F$nets[$dnet]&rep[]=net\">$net/$nets[$dnet]</a>\n";
				echo "\t\t</td>\n";
				TblCell( $dvs );
				TblCell( $nds );
				TblCell( $ndr );
				echo "\t</tr>\n";
				if($row == $lim){break;}
			}
			TblFoot("bgsub", 5, "$row $netlbl, $srt");
		}
	}
}

//===================================================================
// Network Population
// Using IP-strings as hash indexes to avoid signed int problems.
// Don't assume it works the same way on all 32-bit systems or PHP versions!
function NetPop($ina,$opa,$sta,$lim,$ord){

	global $link,$opt,$verb1,$netlbl,$dislbl,$adrlbl,$poplbl,$nonlbl,$emplbl,$tim,$totlbl,$srtlbl;

	if($ord){
		$ocol = "device";
		$srt = "$srtlbl: Device";
	}else{
		$ocol = "ifip";
		$srt = "$srtlbl: IP $adrlbl";
	}
	$devip = array();
	$query	= GenQuery('devices','s','device,inet_ntoa(devip)','','',array($ina),array($opa),array($sta));
	$res = DbQuery($query,$link);
	while( $r = DbFetchRow($res) ){
		$devip[$r[1]] = "$r[0] $r[1]";
	}
	DbFreeResult($res);

	if($ina == "devip"){$ina = "ifip";}
	$query	= GenQuery('networks','s','networks.device,inet_ntoa(ifip),prefix',$ocol,'',array('ifip',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices USING (device)' );
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$netok = array();
		while( ($n = DbFetchRow($res)) ){
			$abip = ip2long($n[1]);
			$dmsk = 0xffffffff << (32 - $n[2]);
			$dnet = long2ip($abip & $dmsk);
			if($n[2] > 16 and $n[2] < 32){					# Only > /16 but not /32 networks
				if( !array_key_exists($dnet,$netok) ){			# Only if subnet hasn't been processed
					$netok[$dnet] = 1;
					$nod[$dnet] = array();
					$nquery	= GenQuery('nodarp','s','mac,inet_ntoa(nodip),srvos','','',array("nodip & $dmsk"),array('='),array(sprintf("%u",$abip & $dmsk)) );
					$nres	= DbQuery($nquery,$link);
					if ($nres) {
						while( ($no = DbFetchRow($nres)) ){
							$mac[$dnet][$no[1]]['mc'] = $no[0];
							$mac[$dnet][$no[1]]['os'] = $no[2];
						}
					}
					DbFreeResult($nres);
					$nquery	= GenQuery('dns','s','aname,inet_ntoa(nodip)','','',array("nodip & $dmsk"),array('='),array(sprintf("%u",$abip & $dmsk)) );
					$nres	= DbQuery($nquery,$link);
					if ($nres) {
						while( ($no = DbFetchRow($nres)) ){
							$dns[$dnet][$no[1]] = $no[0];
						}
					}
					DbFreeResult($nres);
				}
				$dev[$dnet][$n[1]] = $n[0];
				$nets[$dnet] = $n[2];
				if(count(array_keys($nets)) == $lim){break;}
			}
		}
		DbFreeResult($res);
		if($nets){
?>

<h2><?= $netlbl ?> <?= $poplbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/net.png"><br>
			IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			<?= $poplbl ?>

		</th>
	</tr>
<?php
			$row  = 0;
			$mcol = ( $_SESSION['col'] < 6 )?32:64;
			foreach(array_keys($nets) as $net){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				list($ntimg,$ntit) = Nettype($net);
				TblRow( $bg );
				TblCell( '','',"$bi ctr xs","+<img src=\"img/$ntimg\" title=\"$ntit\">" );

				echo "\t\t<td>\n";
				echo "\t\t\t<a href=\"?in[]=devip&op[]==&st[]=$net%2F$nets[$net]&rep[]=pop\">$net/$nets[$net]</a><p>\n";
				echo "\t\t\t<a href=\"Topology-Networks.php?in[]=ifip&op[]==&st[]=$net%2F$nets[$net]\"><img src=\"img/16/glob.png\" title=\"IF IPs\"> ".count(array_keys($dev[$net]))."</a><p>\n";
				echo "\t\t\t<a href=\"Nodes-List.php?in[]=nodip&op[]==&st[]=$net%2F$nets[$net]\"><img src=\"img/16/nods.png\" title=\"Node IPs\"> ".count(array_keys($mac[$net]))."</a>\n";
				echo "\t\t</td>\n";
				echo "\t\t<td>\n\t\t<table class=\"code\"><tr>\n";
				$col  = 0;
				$dnet = ip2long($net);
				$max  = $dnet + pow(2,(32-$nets[$net]));
				for($a = $dnet; $a < $max; $a++){
					$ip  = long2ip($a);
					$nam = ( array_key_exists($ip, $dns[$net]) )?$dns[$net][$ip]:'';
					$lbl = ($nam)?'n':'&nbsp;';
					if( $opt ){
						$lbl = ($mac[$net][$ip]['os'])?OSImg($mac[$net][$ip]['os']):$lbl;
					}
					if($col == $mcol){$col = 0;echo "\t\t</tr>\n\t\t<tr>\n";}
					if( array_key_exists($ip, $dev[$net]) and array_key_exists($ip, $mac[$net]) ){
						echo "\t\t\t<td title=\"$nam DEV:".$dev[$net][$ip]." $ip MAC:".$mac[$net][$ip]['mc']."\" class=\"warn\"><a href=\"Topology-Networks.php?in[]=ifip&op[]==&st[]=$ip\">$lbl</a></td>\n";
					}elseif( array_key_exists($ip, $mac[$net]) ){
							echo "\t\t\t<td title=\"$nam $ip MAC:".$mac[$net][$ip]['mc']."\" class=\"good\"><a href=\"Nodes-List.php?in[]=nodip&op[]==&st[]=$ip\">$lbl</a></td>\n";
					}elseif( array_key_exists($ip, $dev[$net]) ){
						echo "\t\t\t<td title=\"$nam DEV:".$dev[$net][$ip]." $ip\" class=\"noti\"><a href=\"Topology-Networks.php?in[]=ifip&op[]==&st[]=$ip\">$lbl</a></td>\n";
					}elseif( array_key_exists($ip, $devip) ){
						echo "\t\t\t<td title=\"$nam DEV:$devip[$ip]\" class=\"noti part\"><a href=\"Devices-List.php?in[]=devip&op[]==&st[]=$ip\">$lbl</a></td>\n";
					}elseif( $nam ){
						echo "\t\t\t<td title=\"$nam $ip = $emplbl\" class=\"alrm\"><a href=\"Other-Noodle.php?str=$ip\">n</a></td>\n";
					}elseif($a == $dnet or $a == $max -1){
						$netxt = ($a == $dnet)?$netlbl:"Broadcast";
						echo "\t\t\t<td title=\"$netxt:$ip\" class=\"$bg part\">&nbsp;</td>\n";
					}else{
						echo "\t\t\t<td title=\"$ip, $emplbl\" class=\"$bi\">$lbl</td>\n";
					}
					$col++;
				}
				echo "\t\t</tr></table>\n\t\t</td>\n\t</tr>\n";
			}
			TblFoot("bgsub", 3, "$row $netlbl, $srt");
		}
	}
}

//===================================================================
// Node Discovery History
function NodHistory($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$timlbl,$dsclbl,$fislbl,$laslbl,$hislbl,$lstlbl,$updlbl,$msglbl;
?>

<h2><?= $dsclbl ?> <?= $hislbl ?></h2>

	<table class="content">
		<tr class="bgsub">
			<th>
				<img src="img/16/clock.png"><br>
				<?= $timlbl ?>

			</th>
			<th>
				<img src="img/16/bblf.png"><br>
				<?= $fislbl ?> <?= $dsclbl ?>

			</th>
			<th>
				<img src="img/16/bbrt.png"><br>
				<?= $laslbl ?> <?= $dsclbl ?>

			</th>
			<th>
				<img src="img/16/glob.png"><br>
				IP <?= $updlbl ?>

			</th>
			<th>
				<img src="img/16/port.png"><br>
				IF <?= $updlbl ?>

			</th>
		</tr>
<?php
	$query	= GenQuery('nodes','g','firstseen',($ord)?'firstseen':'firstseen desc',$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if( DbNumRows($res) ){
		while( $r = DbFetchRow($res) ){
			$nodup[$r[0]]['fs'] = $r[1];
		}
	}
	$query	= GenQuery('nodes','g','lastseen',($ord)?'lastseen':'lastseen desc',$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if( DbNumRows($res) ){
		while( $r = DbFetchRow($res) ){
			$nodup[$r[0]]['ls'] = $r[1];
		}
	}
	$query	= GenQuery('nodarp','g','ipupdate',($ord)?'ipupdate desc':'ipupdate',$lim,array('ipupdate',$ina),array('>',$opa),array('0',$sta),array('AND'),'LEFT JOIN devices on (nodarp.arpdevice = devices.device)');
	$res	= DbQuery($query,$link);
	if( DbNumRows($res) ){
		while( $r = DbFetchRow($res) ){
			$nodup[$r[0]]['au'] = $r[1];
		}
	}
	$query	= GenQuery('nodes','g','ifupdate',($ord)?'ifupdate desc':'ifupdate',$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if( DbNumRows($res) ){
		while( $r = DbFetchRow($res) ){
			$nodup[$r[0]]['iu'] = $r[1];
		}
	}

	if($ord){
		ksort ($nodup);
		$srt = "$srtlbl: $laslbl - $fislbl";
	}else{
		krsort ($nodup);
		$srt = "$srtlbl: $fislbl - $laslbl";
	}
	$row = 0;
	foreach ( array_keys($nodup) as $d ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$fd   = urlencode(date("m/d/Y H:i:s",$d));
		TblRow( $bg );
		TblCell( date($_SESSION['timf'],$d),'','b' );
		TblCell( array_key_exists('fs',$nodup[$d])?Bar($nodup[$d]['fs'],'lvl50','sbar').$nodup[$d]['fs']:'',"Nodes-List.php?in[]=firstseen&op[]==&st[]=$fd".AddFilter($ina,$opa,$sta) );
		TblCell( array_key_exists('ls',$nodup[$d])?Bar($nodup[$d]['ls'],'lvl200','sbar').$nodup[$d]['ls']:'',"Nodes-List.php?in[]=lastseen&op[]==&st[]=$fd".AddFilter($ina,$opa,$sta) );
		TblCell( array_key_exists('au',$nodup[$d])?Bar($nodup[$d]['au'],'lvl100','sbar').$nodup[$d]['au']:'',"Nodes-List.php?in[]=ipupdate&op[]==&st[]=$fd".AddFilter($ina,$opa,$sta) );
		TblCell( array_key_exists('iu',$nodup[$d])?Bar($nodup[$d]['iu'],'lvl150','sbar').$nodup[$d]['iu']:'',"Nodes-List.php?in[]=ifupdate&op[]==&st[]=$fd".AddFilter($ina,$opa,$sta) );
		echo "\t</tr>\n";
	}
	TblFoot("bgsub", 6, "$row $msglbl ($fisr $fislbl, $lasr $laslbl, $iupr IF $updlbl, $aupr IP $updlbl, $srt");
}

//===================================================================
// Node Distribution
function NodDist($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$porlbl,$poplbl,$locsep,$conlbl,$neblbl,$vallbl,$duplbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2>Nodes / <?= $porlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/port.png"><br>
			<?= $porlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			<?= $poplbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $poplbl";
	}
	$query	= GenQuery('nodes','g','device,icon,ifname',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			$ui = urlencode($r[2]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/".(($r[1])?"dev/$r[1]":"32/qmrk").".png\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[2],"Devices-Interfaces.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui",'b' );
			TblCell( $r[3],"Nodes-List.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui",'','+'.Bar($r[3],8) );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 4, "$row Devices, $srt");
?>

</td><td class="helper">

<h2>Nodes / Device</h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/nods.png"><br>
			<?= $poplbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'device';
		$srt = "$srtlbl: Device";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $poplbl";
	}
	$query	= GenQuery('nodes','g','device,icon',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[0]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr s","+<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/".(($r[1])?"dev/$r[1]":"32/qmrk").".png\"></a>" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),'','b' );
			TblCell( $r[2],"Nodes-List.php?in[]=device&op[]==&st[]=$ud",'','+'.Bar($r[2],'lvl50') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 3, "$row Devices, $srt");
?>

</td></tr></table>

<?php
}

//===================================================================
// List duplicate nodes
function NodDup($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$manlbl,$namlbl,$adrlbl,$qtylbl,$duplbl,$typlbl,$totlbl,$nonlbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $duplbl ?> Node <?= $namlbl ?></h2>

<?php
	if($ord){
		$ocol = 'aname';
		$srt = "$srtlbl: $namlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query = GenQuery('nodes','g','aname;-;count(*)>1',$ocol,$lim,array('CHAR_LENGTH(aname)',$ina),array('>',$opa),array('1',$sta),array('AND'),'LEFT JOIN devices USING (device) LEFT JOIN nodarp USING (mac) LEFT JOIN dns USING (nodip)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/abc.png"><br>
			<?= $namlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			Nodes
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( $r[0],'','b' );
			TblCell( $r[1],"Nodes-List.php?in[]=aname&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'',Bar($r[1],0) );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 2, "$row $duplbl $namlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td><td class="helper">

<h2><?= $duplbl ?> Node MAC <?= $adrlbl ?></h2>

<?php
	if($ord){
		$ocol = 'mac,vlanid';
		$srt = "$srtlbl: MAC, Vlan";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: Nodes";
	}
	$query = GenQuery('nodes','g','mac,oui;-;count(*)>1',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/card.png"><br>
			MAC <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			Nodes
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<img src=\"img/oui/".VendorIcon($r[1]).".png\">" );
			TblCell( $r[0],'','drd' );
			TblCell( $r[1],"Nodes-List.php?in[]=mac&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'',Bar($r[2],0) );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 2, "$row $duplbl MAC $addrlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td></tr></table>

<?php
}

//===================================================================
// List duplicate node IPs
function NodIP($ina,$opa,$sta,$lim,$ord){

	global $link,$srtlbl,$manlbl,$mullbl,$adrlbl,$qtylbl,$duplbl,$typlbl,$totlbl,$nonlbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $duplbl ?> IP <?= $adrlbl ?></h2>

<?php
	if($ord){
		$ocol = 'nodip';
		$srt = "$srtlbl: $adrlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query = GenQuery('nodarp','g','nodip,arpdevice;-;count(*)>1',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices on (nodarp.arpdevice = devices.device)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/glob.png"><br>IP <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>ARP Device
		</th>
		<th>
			<img src="img/16/nods.png"><br>Nodes
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( long2ip($r[0]) );
			TblCell( $r[1],'','b' );
			TblCell( $r[2],"Nodes-List.php?in[]=nodip&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'','+'.Bar($r[2],0) );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 3, "$row $duplbl IP $adrlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td><td class="helper">

<h2><?= $duplbl ?> IPv6 <?= $adrlbl ?></h2>

<?php
	if($ord){
		$ocol = 'nodip';
		$srt = "$srtlbl: $adrlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query = GenQuery('nodnd','g','nodip6,arpdevice;-;count(*)>1',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices on (nodnd.nddevice = devices.device)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/glob.png"><br>
			IPv6 <?= $adrlbl ?>

		</th>
		<th>
			<img src="img/16/dev.png"><br>
			ARP Device
		</th>
		<th>
			<img src="img/16/nods.png"><br>
			Nodes
		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( DbIPv6($r[0]) );
			TblCell( $r[1],'','b' );
			TblCell( $r[2],"Nodes-List.php?in[]=nodip6&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'','+'.Bar($r[2],0) );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 3, "$row $duplbl IPv6 $adrlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td></tr><tr><td class="helper">

<h2><?= $mullbl ?> IP <?= $adrlbl ?></h2>

<?php
	if($ord){
		$ocol = 'mac';
		$srt = "$srtlbl: $adrlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query = GenQuery('nodarp','g','mac,arpdevice;-;count(*)>1',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices on (nodarp.arpdevice = devices.device)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/node.png"><br>Node
		</th>
		<th>
			<img src="img/16/dev.png"><br>ARP Device
		</th>
		<th>
			<img src="img/16/nods.png"><br>#IP <?= $adrlbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( $r[0] );
			TblCell( $r[1],'','b' );
			TblCell( $r[2],"Nodes-List.php?in[]=mac&op[]==&st[]=$r[0]".AddFilter($ina,$opa,$sta),'nw',Bar($r[2],0) );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 3, "$row $mullbl IP $adrlbl, $srt");
	}else{
		echo "<h5>$nonlbl</h5>";
	}
?>

</td></tr></table>

<?php
}

//===================================================================
// Node Services
function NodSrv($ina,$opa,$sta,$lim,$ord){

	global $link,$dislbl,$srtlbl,$typlbl,$qtylbl;
?>

<table class="full fixed"><tr><td class="helper">

<h2>OS <?= $dislbl ?></h2>

<canvas id="nosdnt" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/cbox.png"><br>
			OS
		</th>
		<th>
			<img src="img/16/nods.png"><br>
			Nodes
		</th>
	</tr>
<?php

	if($ord){
		$ocol = "srvos";
		$srt = "$srtlbl: OS";
	}else{
		$ocol = "cnt desc";
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('nodarp','g','srvos',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices on (nodarp.arpdevice = devices.device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( ($r = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('231',$row,2) );
			if($r[0]){
				$os = $r[0];
				$uo = urlencode($r[0]);
				$op = '=';
			}else{
				$os = '-';
				$uo = '^$';
				$op = '~';
			}
			TblRow( $bg );
			TblCell( $os,"?in[]=srvos&op[]==&st[]=$uo&rep[]=sum&rep[]=srv",'nw', OSImg($r[0]) );
			TblCell( $r[1], "Nodes-List.php?in[]=srvos&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'nw','+'.Bar($r[1],GetCol('231',$row,2),'lbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 2, "$row OS, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("nosdnt").getContext("2d");
var myNewChart = new Chart(ctx).Doughnut(data);
</script>

</td><td class="helper">

<h2><?= $typlbl ?> <?= $dislbl ?></h2>

<canvas id="ntydnt" style="display: block;margin: 0 auto;padding: 10px;" width="400" height="300"></canvas>

<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/abc.png"><br>
			<?= $typlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			Nodes
		</th>
	</tr>
<?php
	if($ord){
		$ocol = "srvtype";
		$srt = "$srtlbl: $typlbl";
	}else{
		$ocol = "cnt desc";
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('nodarp','g','srvtype',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices on (nodarp.arpdevice = devices.device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		$chd = array();
		while( ($r = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$chd[] = array('value' => $r[1],'color' => GetCol('123',$row,2) );
			echo "\t<tr class=\"$bg\">\n";
			if($r[0]){
				$uo = urlencode($r[0]);
				$op = "=";
			}else{
				$uo = "^$";
				$op = "~";
			}
			TblRow( $bg );
			TblCell( $r[0] );
			TblCell( $r[1], "Nodes-List.php?in[]=srvtype&op[]=$op&st[]=$uo".AddFilter($ina,$opa,$sta),'nw','+'.Bar($r[1],GetCol('123',$row,2),'lbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 2, "$row $typlbl, $srt");
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("ntydnt").getContext("2d");
var myNewChart = new Chart(ctx).Doughnut(data);
</script>

</td></tr></table>

<?php
}

//===================================================================
// Nomad Nodes
function NodNomad($ina,$opa,$sta,$lim,$ord){

	global $link,$nomlbl,$srtlbl,$chglbl,$namlbl,$vallbl,$lstlbl;
?>

<h2><?= $nomlbl ?> <?= $lstlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/node.png"><br>
			Node
		</th>
		<th>
			<img src="img/16/dev.png"><br>
			Device

		</th>
		<th>
			<img src="img/16/glob.png"><br>
			IP <?= $chglbl ?>

		</th>
		<th>
			<img src="img/16/port.png"><br>
			IF <?= $chglbl ?>

		</th>
		<th>
			<img src="img/16/walk.png" title="<?= $nomlbl ?> <?= $vallbl ?> = IP <?= $chglbl ?> * IF <?= $chglbl ?>"><br>
			<?= $nomlbl ?> <?= $vallbl ?>

		</th>
	</tr>
<?php
	if($ord){
		$ocol = "name";
		$srt = "$srtlbl: $namlbl";
	}else{
		$ocol = "nom desc";
		$srt = "$srtlbl: $nomlbl $vallbl";
	}
	$query	= GenQuery('nodes','s','aname,mac,oui,nodip,device,ifname,ifchanges,ipchanges,(ifchanges * ipchanges) as nom',$ocol,$lim,array('ifchanges','ipchanges',$ina),array('>','>',$opa),array('0','0',$sta),array('AND','AND'),'LEFT JOIN devices USING (device) LEFT JOIN nodarp USING (mac) LEFT JOIN dns USING (nodip)');
	$res = DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($r[4]);
			$ui = urlencode($r[5]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<a href=\"Nodes-Status.php?st=$r[1]\"><img src=\"img/oui/".VendorIcon($r[2]).".png\"></a>" );
			TblCell( $r[0],'','b' );
			TblCell( long2ip($r[3]),"Nodes-List.php?in[]=nodip&op[]==&st[]=$r[3]".AddFilter($ina,$opa,$sta) );
			TblCell( substr($r[4],0,$_SESSION['lsiz'])." $r[5]",'','',"+<a href=\"Devices-Status.php?dev=$ud&pop=on\"><img src=\"img/16/sys.png\"></a>" );
			TblCell( $r[6],"Nodes-List.php?in[]=ifchanges&op[]==&st[]=$r[6]".AddFilter($ina,$opa,$sta),'rgt' );
			TblCell( $r[7],"Nodes-List.php?in[]=ipchanges&op[]==&st[]=$r[7]".AddFilter($ina,$opa,$sta),'rgt' );
			TblCell( $r[8],'','','+'.Bar($r[8],100,'sbar') );
			echo "\t</tr>\n";
		}
	}
	TblFoot("bgsub", 7, "$row $nomlbl, $srt");
}

//===================================================================
// Node Summary
function NodSum($ina,$opa,$sta,$lim,$ord){

	global $link,$rrdstep,$verb1,$stco,$sumlbl,$stslbl,$srtlbl,$venlbl,$qtylbl,$alllbl,$chglbl,$totlbl,$deslbl,$fislbl,$laslbl,$namlbl,$metlbl,$nonlbl,$loslbl,$qutlbl,$dsclbl,$onclbl,$vallbl,$mullbl,$dbregexp;

	$lasdis = time() - $rrdstep * 2;#TODO split in independent queries for nodes, nodarp and dns!
	$query	= GenQuery('nodes','s',"count(*),
					sum(case when nodip is NULL then 1 else 0 end),
					sum(case when aname is NULL then 1 else 0 end),
					sum(case when firstseen = lastseen then 1 else 0 end),
					sum(case when metric $dbregexp '[M-Z]' then 1 else 0 end),
					sum(case when firstseen > $lasdis then 1 else 0 end),
					sum(case when lastseen > $lasdis then 1 else 0 end),
					sum(case when ipchanges > 0 then 1 else 0 end),
					sum(case when ifchanges > 0 then 1 else 0 end)",'','',array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device) LEFT JOIN nodarp USING (mac) LEFT JOIN dns USING (nodip)');
	$res	= DbQuery($query,$link);
	if ($res) {
		$r = DbFetchRow($res);
	}else{
		print DbError($link);
	}
?>

<table class="full fixed"><tr><td class="helper">

<h2>Node <?= $sumlbl ?> </h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/find.png" title="Nodes <?= $stslbl ?>"><br>
			<?= $deslbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			<?= $qtylbl ?>
		</th>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/add.png" title="<?= $fislbl ?> > <?= date($_SESSION['timf'],$lasdis) ?>">
		</td>
		<td>
			<strong><?= $stco['10'] ?></strong></td><td><?=Bar($r[5],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=firstseen&op[]=>&st[]=<?= $lasdis ?>&ord=nodip<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[5] ?></a>
		</td>
	</tr>
	<tr class="txta">
		<td class="imga ctr xs">
			<img src="img/16/exit.png" title="<?= $laslbl ?> > <?= date($_SESSION['timf'],$lasdis) ?>">
		</td>
		<td>
			<strong><?= $stco['100'] ?></strong>
		</td>
		<td>
			<?=Bar($r[6],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=lastseen&op[]=>&st[]=<?= $lasdis ?>&ord=nodip<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[6] ?></a>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/wlan.png" title="IF <?= $metlbl ?> < 256">
		</td>
		<td>
			<strong>Wlan</td></strong>
		<td>
			<?=Bar($r[4],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=metric&op[]=~&st[]=[M-Z]<?= AddFilter($ina,$opa,$sta) ?>"> <?= $r[4] ?></a>
		</td>
	</tr>
	<tr class="txta">
		<td class="imga ctr xs">
			<img src="img/16/calc.png" title="IP <?= $chglbl ?> > 0">
		</td>
		<td>
			<strong>IP <?= $chglbl ?></strong>
		</td>
		<td>
			<?=Bar($r[7],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=ipchanges&op[]=>&st[]=0&ord=ipchanges+desc<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[7] ?></a>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/walk.png" title="IF <?= $chglbl ?> > 0">
		</td>
		<td>
			<strong>IF <?= $chglbl ?></strong></td><td><?= Bar($r[8],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=ifchanges&op[]=>&st[]=0&ord=ifchanges+desc<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[8] ?></a>
		</td>
	</tr>
	<tr class="txta">
		<td class="imga ctr xs">
			<img src="img/16/abc.png"  title=" <?= $namlbl ?> = ''">
		</td>
		<td>
			<strong><?= $nonlbl ?> <?= $namlbl ?></strong>
		</td>
		<td>
			<?=Bar($r[2],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=aname&op[]==&st[]=NULL&ord=nodip<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[2] ?></a>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/glob.png" title="IP = ''">
		</td>
		<td>
			<strong><?= $nonlbl ?> IP</strong>
		</td>
		<td>
			<?=Bar($r[1],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=nodip&op[]==&st[]=NULL<?= AddFilter($ina,$opa,$sta) ?>"> <?= $r[1] ?></a>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/eyes.png" title="<?= $fislbl ?> = <?= $laslbl ?>">
		</td>
		<td>
			<strong><?= (($verb1)?"$dsclbl $onclbl":"$onclbl $dsclbl") ?></strong></td><td><?=Bar($r[3],'lvl100','sbar') ?> <a href="Nodes-List.php?in[]=firstseen&co[]==&in[]=lastseen&ord=firstseen&op[]=&st[]=&op[]=&st[]=<?= AddFilter($ina,$opa,$sta) ?>"><?= $r[3] ?></a>
		</td>
	</tr>
	<tr class="txtb">
		<td class="imgb ctr xs">
			<img src="img/16/nods.png" title="<?= $alllbl ?> Nodes">
		</td>
		<td>
			<strong><?= $totlbl ?></strong>
		</td>
		<td>
			<?=Bar($r[0],'lvl100','sbar') ?> <?= $r[0] ?>

		</td>
	</tr>
</table>

</td><td class="helper">

<h2>OUI <?= $venlbl ?> </h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/card.png"><br>
			<?= $venlbl ?>

		</th>
		<th>
			<img src="img/16/nods.png"><br>
			<?= $qtylbl ?>
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'oui';
		$srt = "$srtlbl: $venlbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('nodes','g','oui',$ocol,$lim,array($ina),array($opa),array($sta),array(),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	$row = 0;
	if($res){
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$uo = urlencode($r[0]);
			TblRow( $bg );
			TblCell( '','',"$bi ctr xs","+<a href=\"http://www.google.com/search?q=$uo&btnI=1\"><img src=\"img/oui/".VendorIcon($r[0]).".png\"></a>" );
			TblCell( $r[0],"?in[]=oui&op[]==&st[]=$uo&rep[]=sum&rep[]=srv",'' );
			TblCell( $r[1],"Nodes-List.php?in[]=oui&op[]==&st[]=$uo".AddFilter($ina,$opa,$sta),'',Bar($r[1],'lvl100','sbar') );
			echo "\t</tr>\n";
		}
		TblFoot("bgsub", 3, "$row $venlbl, $srt");
	}
	?>

</td></tr></table>

<?php
}

//===================================================================
// Empty Vlans
function VlanEmpty($ina,$opa,$sta,$lim,$ord){

	global $link,$verb1,$srtlbl,$lstlbl,$loclbl,$locsep,$conlbl,$emplbl,$limlbl;

?>

<h2><?= (($verb1)?"$emplbl Vlans":"Vlans $emplbl") ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/vlan.png"><br>
			Vlan <?= $lstlbl ?>
		</th>
	</tr>
<?php
	if($ord){
		$ocol = 'vlanid';
		$srt = "$srtlbl: Vlan";
	}else{
		$ocol = 'vlans.device,vlanid';
		$srt = "$srtlbl: Device";
	}
	if($ina == "device"){$ina = "vlans.device";}
	if($ina == "vlanid"){$ina = "vlans.vlanid";}
	$query	= GenQuery('vlans','s','vlans.device,vlans.vlanid,vlans.vlanname,contact,location,icon',$ocol,$lim,array('mac',$ina),array('COL IS',$opa),array('NULL',$sta),array('AND'),'LEFT JOIN nodes on (vlans.device = nodes.device and vlans.vlanid = nodes.vlanid) LEFT JOIN devices on (vlans.device = devices.device)');
	$res = DbQuery($query,$link);
	$row = 0;
	$nvl = 0;
	$prev = '';
	while( $r = DbFetchRow($res) ){
		$curi = "\t\t\t<a href=\"Devices-Vlans.php?in[]=vlanid&op[]==&st[]=$r[1]\"><img src=\"img/chip.png\" title=\"$r[2]\">$r[1]</a> &nbsp;\n";
		if( $r[0] == $prev ){
			echo $curi;
		}else{
			$prev = $r[0];
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$l  = explode($locsep, $r[3]);
			$ud = urlencode($r[0]);
			if( $prev ) echo "\t\t</td>\n\t</tr>\n";
			TblRow( $bg );
			TblCell( "<img src=\"img/".(($r[5])?"dev/$r[5]":"32/qmrk").".png\" title=\"$conlbl: $r[4], $loclbl: $l[0] $l[1] $l[2]\">","Devices-Status.php?dev=$ud","$bi ctr s" );
			TblCell( substr($r[0],0,$_SESSION['lsiz']),"Devices-Vlans.php?in[]=device&op[]==&st[]=$ud",'b' );
			echo "\t\t<td>\n$curi";
		}
		$nvl++;
	}
	echo "\t\t</td>\n\t</tr>\n";
	TblFoot("bgsub", 3, "$nvl Vlans ($limlbl $lim), $row Devices, $srt");
}

//===================================================================
// System Policy Statistics
function SysPolicy($ina,$opa,$sta,$lim,$ord){

	global $link,$verb1,$stco,$srtlbl,$tgtlbl,$pocl,$clalbl,$dislbl,$qtylbl,$stalbl,$nonlbl,$vallbl;

?>
<table class="full fixed"><tr><td class="helper">

<h2><?= $stalbl ?> <?= $dislbl ?></h2>

<?php
	if($ord){
		$ocol = 'status';
		$srt = "$srtlbl: $stalbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $qtylbl";
	}
	$query	= GenQuery('policies','g','status',$ocol,$lim,array($ina),array($opa),array($sta));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/bchk.png"><br>
			<?= $stalbl ?>

		</th>
		<th>
			<img src="img/16/form.png"><br>
			<?= $qtylbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( Staimg($r[0]),'',"$bi ctr xs" );
			TblCell( $stco[$r[0]],'','b' );
			TblCell( $r[1],"?in[]=status&op[]==&st[]=$r[0]",'','+'.Bar($r[1],'lvl100') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 3, "$row $stalbl, $srt");
	}else{
		echo "<h5>$nonlbl $vallbl</h5>";
	}
?>

</td><td class="helper">

<h2><?= $clalbl ?> <?= $dislbl ?></h2>

<?php
	if($ord){
		$ocol = 'class';
		$srt = "$srtlbl: $avalbl";
	}else{
		$ocol = 'cnt desc';
		$srt = "$srtlbl: $nonlbl $avalbl";
	}
	$query	= GenQuery('policies','g','class',$ocol,$lim,array($ina),array($opa),array($sta));
	$res = DbQuery($query,$link);
	if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/abc.png"><br>
			<?= $clalbl ?>

		</th>
		<th>
			<img src="img/16/form.png"><br>
			<?= $qtylbl ?>

		</th>
</tr>
<?php
		$row = 0;
		while( $r = DbFetchRow($res) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow( $bg );
			TblCell( $pocl[$r[0]],'','b' );
			TblCell( $r[1],"?in[]=class&op[]==&st[]=$r[0]",'','+'.Bar($r[1],'lvl100') );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
		TblFoot("bgsub", 2, "$row $duplbl, $srt");
	}else{
		echo "<h5>$nonlbl $vallbl</h5>";
	}
?>

</td></tr></table>

<?php
}

?>
