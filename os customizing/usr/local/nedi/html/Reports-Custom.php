<?php
# Program: Reports-Custom.php
# Programmer: Remo Rickli (and contributors)

$exportxls = 1;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/librep.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$ta = isset($_GET['ta']) ? $_GET['ta'] : '';
$ca = isset($_GET['ca']) ? $_GET['ca'] : array();
#$tb = isset($_GET['tb']) ? $_GET['tb'] : '';

$lim = isset($_GET['lir']) ? preg_replace('/\D+/','',$_GET['lir']) : 10;
$lol = isset($_GET['lol']) ? $_GET['lol'] : '';
$cha = isset($_GET['cha']) ? $_GET['cha'] : '';

$map = isset($_GET['map']) ? "checked" : '';
$ord = isset($_GET['ord']) ? $_GET['ord'] : 'cnt desc';

$cols = array(	"devices.device"=>"Device $namlbl",
		"devip"=>"IP $adrlbl",
		"type"=>"Device $typlbl",
		"vendor"=>$venlbl,
		"firstdis"=>"Device $fislbl $dsclbl",
		"lastdis"=>"Device $laslbl $dsclbl",
		"services"=>$srvlbl,
		"description"=>$deslbl,
		"devos"=>"Device OS",
		"bootimage"=>"Bootimage",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"devmode"=>$modlbl,
		"icon"=>"Icon",
		"snmpversion"=>"SNMP $verlbl",
		"cnt"=>$numlbl
		);

$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if( $ta ){
	$res = DbQuery(GenQuery($ta, "c"), $link);
	while($c = DbFetchRow($res)) {
		$cols[$c[0]] = $c[0];
	}
}
?>
<script src="inc/Chart.min.js"></script>

<h1 onclick="document.report.style.display = (document.report.style.display == 'none')?'':'none';"><?= $cuslbl ?> Report</h1>

<?php  if( !isset($_GET['print']) ) { ?>

<form method="get" name="report" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top">
<?php Filters(); ?>
</td>
<td class="ctr">
	<a href="?ta=modules&ca%5B%5D=devgroup&ca%5B%5D=moddesc&lir=10&cha=CPie&do=Show&ord=cnt+desc"><img src="img/16/pcm.png" title="<?= $igrp['23'] ?> <?= $grplbl ?> <?= $dislbl ?>"></a>
	<a href="?in%5B%5D=snmpversion&op%5B%5D=>&st%5B%5D=0&ta=modules&ca%5B%5D=location&ca%5B%5D=moddesc&lir=10&lol=2&cha=RPie&ord=location"><img src="img/16/fort.png" title="<?= $igrp['23'] ?> <?= $place['c'] ?> <?= $dislbl ?>"></a><br>
	<a href="?ta=interfaces&ca%5B%5D=speed&ca%5B%5D=duplex&lir=10&cha=BPie&do=Show&ord=cnt+desc"><img src="img/16/port.png" title="<?= $toplbl ?> <?= $porlbl ?> <?= $spdlbl ?>, Duplex"></a>
	<a href="?ta=monitoring&ca%5B%5D=class&ca%5B%5D=test&lir=10&cha=RPie&do=Show&ord=class"><img src="img/16/bino.png" title="<?= $monlbl ?> <?= $clalbl ?> <?= $dislbl ?>"></a>
</td>
<td class="ctr">
	<?= $ta?"<img src=\"img/16/$tblicon[$ta].png\">":'' ?>
	<select name="ta" onchange="this.form.submit();">
		<option value="">Table >
<?php
	$res = DbQuery(GenQuery("", "h"), $link);
	while($t = DbFetchRow($res)) {
		echo "\t\t<option value=\"$t[0]\"".(($ta == $t[0])?" selected":"").">$t[0]\n";
	}
	DbFreeResult($res);
?>
	</select><br>
	
<?php if( $ta ){ ?>
	<select multiple size="4" name="ca[]">
		<option value=""><?= $grplbl ?> <?= $collbl ?> >
<?php
	foreach($cols as $dc => $n) {
		if( $dc != 'device' ){
			echo "\t\t<option value=\"$dc\"".((in_array($dc,$ca))?" selected":"").">$n\n";
		}
	}
	DbFreeResult($res);
?>
	</select>
<?php } ?>

</td>
<td>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lir">
<?php selectbox("limit",$lim) ?>
	</select><br>
	<img src="img/16/home.png" title="<?= $loclbl ?> <?= $levlbl ?>">
	<select size="1" name="lol">
		<option value=""><?= $alllbl ?>
		<option value="1"<?= ($lol == 1)?" selected":"" ?>><?= $place['r'] ?>
		<option value="2"<?= ($lol == 2)?" selected":"" ?>><?= $place['c'] ?>
		<option value="3"<?= ($lol == 3)?" selected":"" ?>><?= $place['b'] ?>
		<option value="4"<?= ($lol == 4)?" selected":"" ?>><?= $place['o'] ?>
	</select><br>
	<img src="img/16/chrt.png">
	<select size="1" name="cha">
		<option value=""><?= $gralbl ?> >
		<option value="RPie"<?= ($cha == "RPie")?" selected":"" ?>>Red Pie
		<option value="GPie"<?= ($cha == "GPie")?" selected":"" ?>>Green Pie
		<option value="BPie"<?= ($cha == "BPie")?" selected":"" ?>>Blue Pie
		<option value="CPie"<?= ($cha == "CPie")?" selected":"" ?>>Color Pie 1
		<option value="DPie"<?= ($cha == "DPie")?" selected":"" ?>>Color Pie 2
	</select>
</td>
<td>
	<img src="img/16/paint.png" title="<?= (($verb1)?"$sholbl $laslbl Map":"Map $laslbl $sholbl") ?>">
	<input type="checkbox" name="map" <?= $map ?>><br>
</td>
<td class="ctr s">
	<input type="submit" class="button" name="do" value="<?= $sholbl ?>">
</td>
</tr>
</table>
</form>
<p>

<?php
}
if ($map and file_exists("map/map_$_SESSION[user].php")) {
	echo "<div class=\"ctr\">\n";
	echo "<h2>$netlbl Map</h2>\n";
	echo "<img src=\"map/map_$_SESSION[user].php\" class=\"genpad\">\n";
	echo "</div>\n";
}

if( count($ca) ){

	$clrs = '111';
	if( $cha ){
		if( strstr($cha,'R') ){
			$clrs = '300';
		}elseif( strstr($cha,'G') ){
			$clrs = '030';
		}elseif( strstr($cha,'B') ){
			$clrs = '003';
		}elseif( strstr($cha,'C') ){
			$clrs = 'trf';
		}elseif( strstr($cha,'D') ){
			$clrs = 'brc';
		}
		$chopt = ', {segmentStrokeWidth : 1'.$anim.'}';
?>
	<canvas id="chart" style="display: block;margin: 0 auto;padding: 10px" width="800" height="400"></canvas>
<?php
}
	$col   = $ca;
	$cstr  = implode(',',$col).($lol?"#$lol":"");							# Append location level, if desired
	$query = GenQuery($ta,'g',$cstr,$ord,$lim,$in,$op,$st,$co,$joindv[$ta]?"LEFT JOIN devices ON ($ta.$joindv[$ta] = devices.device)":'');

	Condition($in,$op,$st,$co);
	
	$col[] = 'cnt';
	TblHead("bgsub",1);
	
	$res = DbQuery($query,$link);
	$row = 0;
	$chd = array();
	while($l = DbFetchArray($res)) {
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		TblRow($bg);
		foreach($l as $k => $v) {
			#if( $k == 'device' ) $k = 'devices.device';
			if( $k == 'cnt' ){
				$cclr = 'lvl100';
				if( $cha ){
					 $cclr  = GetCol($clrs,$row,2);
					 $chd[] = array('value' => $v,'color' => $cclr );
				 }
				TblCell( $v,'','',( !isset($_GET['xls']) )?'+'.Bar($v, $cclr,'sbar'):'' );				
			}elseif( $k == 'iftype' ){
				list($ifi,$ift)	= Iftype($v);
				TblCell( $ift,'','',( !isset($_GET['xls']) )?"+<img src=\"img/$ifi\">":'' );
			}elseif( preg_match("/^(dev|orig|nod|if|mon)ip$/",$k) ){
				TblCell( long2ip($v) );
			}elseif( preg_match("/^(first|last|start|end|time|(if|ip|os|as)?update)/",$k) ){
				TblCell( Ftime($v) );
			}else{
				TblCell( $v,array_key_exists($k,$collnk)?"$collnk[$k]".urlencode($v):'' );
			}
		}
		echo "\t</tr>\n";
	}

	TblFoot("bgsub", count($col), "$row $vallbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );

	if($cha){
?>

<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("chart").getContext("2d");
var myNewChart = new Chart(ctx).<?= substr($cha,1) ?>(data<?= $chopt ?>);
</script>
<?php
	}
}

include_once ("inc/footer.php");
?>
