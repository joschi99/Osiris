<?php
# Program: Monitoring-Timeline.php
# Programmer: Remo Rickli

$refresh   = 600;
$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$gra = isset($_GET['gra']) ? $_GET['gra'] : 3600;
$det = isset($_GET['det']) ? $_GET['det'] : '';
$fmt = isset($_GET['fmt']) ? $_GET['fmt'] : 'sbar';
$sho = isset($_GET['sho']) ? 1 : 0;

$strsta = isset($_GET['sta']) ? $_GET['sta'] : date("m/d/Y", time() - 86400).' 00:00';
$strend = isset($_GET['end']) ? $_GET['end'] : date("m/d/Y H:i");
if( $st and !$sho ){											# Let graph follow autoupdate
	$strsta = date("m/d/Y H:i",strtotime($strsta) + $refresh);
	$strend = date("m/d/Y H:i");
}
$sta = strtotime($strsta);
$end = strtotime($strend);
if($sta > $end){
	$sta    = $end - 100 * $rrdstep;
	$strsta = date("m/d/Y H:i",$sta);
}
$qstr = strpos($_SERVER['QUERY_STRING'], "sta")?$_SERVER['QUERY_STRING']:$_SERVER['QUERY_STRING']."&sta=".urlencode($strsta)."&end=".urlencode($strend);

$cols = array(	"info"=>"Info",
		"id"=>"ID",
		"level"=>"$levlbl",
		"time"=>$timlbl,
		"source"=>$srclbl,
		"class"=>$clalbl,
		"type"=>"Device $typlbl",
		"devos"=>"Device OS",
		"bootimage"=>"Bootimage",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl"
		);

?>
<h1>Monitoring Timeline</h1>

<?php  if( !isset($_GET['print']) ){ ?>

<form method="get" name="dynfrm" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
<?php Filters(1); ?>
</td>
<td>
	<img src="img/16/abc.png" title="<?= $grplbl ?>">
	<select size="1" name="det">
		<option value=""><?= $nonlbl ?>
		<option value="level" <?= ($det == "level")?" selected":"" ?>><?= $levlbl ?>
		<option value="source" <?= ($det == "source")?" selected":"" ?>><?= $srclbl ?>
		<option value="class" <?= ($det == "class")?" selected":"" ?>><?= $clalbl ?>
	</select>
	<br>
	<img src="img/16/clock.png" title="<?= $timlbl ?> <?= $sizlbl ?>">
	<select size="1" name="gra">
		<option value="3600"><?= $tim['h'] ?>
		<option value="86400" <?= ($gra == "86400")?" selected":"" ?>><?= $tim['d'] ?>
		<option value="604800" <?= ($gra == "604800")?" selected":"" ?>><?= $tim['w'] ?>
		<option value="2592000" <?= ($gra == "2592000")?" selected":"" ?>><?= $tim['m'] ?>
	</select>
	<br>
	<img src="img/16/form.png" title="<?= $frmlbl ?>">
	<select size="1" name="fmt">
		<option value='sbar' <?= ($fmt == 'sbar')?" selected":"" ?>><?= $siz[m] ?>
		<option value='lbar' <?= ($fmt == 'lbar')?" selected":"" ?>><?= $siz[l] ?>
		<option value="cg" <?= ($fmt == "cg")?" selected":"" ?>><?= $collbl ?> <?= $gralbl ?>
		<option value="ag" <?= ($fmt == "ag")?" selected":"" ?>><?= $arclbl ?> <?= $gralbl ?>
	</select>
</td>
<td class="ctr">

	<table style="border-spacing: 0px">
		<tr class="bgsub">
			<td>
				<a href="?<?=SkewTime($qstr,"sta", -7) ?>"><img src="img/16/bbl2.png" title="<?= $sttlbl ?> -<?= $tim['w'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"sta", -1) ?>"><img src="img/16/bblf.png" title="<?= $sttlbl ?> -<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<input  name="sta" id="start" type="text" value="<?= $strsta ?>" onfocus="select();" size="15" title="<?= $sttlbl ?>">
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"sta", 1) ?>"><img src="img/16/bbrt.png" title="<?= $sttlbl ?> +<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"sta", 7) ?>"><img src="img/16/bbr2.png" title="<?= $sttlbl ?> +<?= $tim['w'] ?>"></a>
			</td>
		</tr>
		<tr class="bgsub">
			<td>
				<a href="?<?=SkewTime($qstr,"all", -7) ?>"><img src="img/16/bbl2.png" title="<?= $gralbl ?> -<?= $tim['w'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"all", -1) ?>"><img src="img/16/bblf.png" title="<?= $gralbl ?> -<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<img src="img/16/date.png" title="<?= $sttlbl ?> & <?= $endlbl ?>">
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"all", 1) ?>"><img src="img/16/bbrt.png" title="<?= $gralbl ?> +<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"all", 7) ?>"><img src="img/16/bbr2.png" title="<?= $gralbl ?> +<?= $tim['w'] ?>"></a>
			</td>
		</tr>
		<tr class="bgsub">
			<td>
				<a href="?<?=SkewTime($qstr,"end", -7) ?>"><img src="img/16/bbl2.png" title="<?= $endlbl ?> -<?= $tim['w'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"end", -1) ?>"><img src="img/16/bblf.png" title="<?= $endlbl ?> -<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<input  name="end" id="end" type="text" value="<?= $strend ?>" onfocus="select();" size="15" title="<?= $endlbl ?>">
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"end", 1) ?>"><img src="img/16/bbrt.png" title="<?= $endlbl ?> +<?= $tim['d'] ?>"></a>
			</td>
			<td>
				<a href="?<?=SkewTime($qstr,"end", 7) ?>"><img src="img/16/bbr2.png" title="<?= $endlbl ?> +<?= $tim['w'] ?>"></a>
			</td>
		</tr>
	</table>

<script type="text/javascript" src="inc/datepickr.js"></script>
<link rel="stylesheet" type="text/css" href="inc/datepickr.css" />
<script>
	new datepickr('start', {'dateFormat': 'm/d/y'});
	new datepickr('end', {'dateFormat': 'm/d/y'});
</script>

</td>
<td class="ctr s">
	<span id="counter"><?= $refresh ?></span>
	<img src="img/16/exit.png" title="Stop" onClick="stop_countdown(interval);">
	<p>
	<input type="submit" class="button" name="sho" value="<?= $sholbl ?>">
</td>
</tr>
</table>
</form>
<p>
<?php
}
Condition($in,$op,$st,$co);

if( !strpos($fmt,'g') ){
?>
<table class="content">
	<tr class="bgsub">
		<th class="s">
			<img src="img/16/clock.png"><br><?= $timlbl ?>
		</th>
		<th>
			<img src="img/16/bell.png"><br><?= $msglbl ?>
		</th>
	</tr>
<?php
}
$istart	= $sta;
if( $st[0] ){
	$monev	= "Monitoring-Events.php?in[]=$in[0]&op[]=$op[0]&st[]=".urlencode($st[0])."&co[]=AND&";
}else{
	$monev	= "Monitoring-Events.php?";
}
$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);

$tmsg = 0;
$row = 0;
while($istart < $end){
	$iend = $istart + $gra;
	if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
	$fs = urlencode(date("m/d/Y H:i:s",$istart));
	$fe = urlencode(date("m/d/Y H:i:s",$iend));
	if( $gra == "3600" ){
		$chd['labels'][] = date("H",$istart).':00';
	}elseif( $gra == "86400" ){
		$chd['labels'][] = date("D",$istart);
	}elseif( $gra == "604800" ){
		$chd['labels'][] = 'W'.date("W",$istart);
	}elseif( $gra == "2592000" ){
		$chd['labels'][] = date("M",$istart);
	}
	if( !strpos($fmt,'g') ){
		TblRow($bg);
		echo "\t\t<td class=\"$bi ctr nw\">\n";
		echo "\t\t\t<a href=\"${monev}in[]=time&op[]=%3E=&st[]=$fs&co[]=AND&in[]=time&op[]=%3C&st[]=$fe&elm=$listlim\">".date("j.M G:i",$istart)."</a>\n\t\t</td>\n";
		echo "\t\t<td>\n";
	}
	if($det){
		# Postgres dictates that all columns must appear in the GROUP BY clause or be used in an aggregate function, so we "happily" givem that...%&/(ç(*"*&
		$query = GenQuery('events','g',($det == 'source')?'source,icon':$det,$det,'',array('time','time',$in[0]),array('>=','<',$op[0]),array($istart,$iend,$st[0]),array('AND','AND'),'LEFT JOIN devices USING (device)');
		$res   = DbQuery($query,$link);
		if($res){
			$nmsg = 0;
			while( $m = DbFetchArray($res) ){
				if($det == 'source'){
					$wid = (($fmt == 'lbar')?"24px":"16px");
					if($m['source'] == 'nedi'){
						$gico = "<img src=\"img/16/cog.png\" width=\"$wid\" title=\"$srclbl NeDi\">";
					}elseif($m['icon']){
						$gico = "<img src=\"img/dev/$m[icon].png\" width=\"$wid\" title=\"$srclbl $m[source]\">";
					}else{
						$gico = "<img src=\"img/16/say.png\" width=\"$wid\" title=\"$srclbl $m[source]\">\n";
					}
				}elseif($det == 'level'){
					$gico = "<img src=\"img/16/" . $mico[$m['level']] . ".png\" title=\"" . $mlvl[$m['level']] . "\">";
				}else{
					list($ei,$et) = EvClass($m['class']);
					$gico = "<img src=\"$ei\" title=\"$et ($m[class])\">";
				}
				if( !strpos($fmt,'g') ){
					echo "\t\t\t<a href=\"${monev}in[]=time&op[]=%3E=&st[]=$fs&co[]=AND&in[]=time&op[]=%3C&st[]=$fe&in[]=$det&op[]==&st[]=".urlencode($m[$det])."&co[]=AND&elm=$listlim\">\n";
					if( strpos($fmt,'bar') ) echo "\t\t\t\t$gico\n";
					echo "\t\t\t\t".Bar($m['cnt'],($det == 'level' and $m[level] > 49)?"lvl$m[level]":'lvl10',$fmt,$m['cnt'])."\n\t\t\t</a>\n";
				}
				$dsico[$m[$det]] = $gico;
				$dsval[$m[$det]][$row] = $m['cnt'];
				$nmsg += $m['cnt'];
			}
			if( !strpos($fmt,'g') ){
				if($nmsg) echo "\t\t\t &nbsp; $nmsg $totlbl\n";
				echo "\t\t</td>\n\t</tr>\n";
			}
			DbFreeResult($res);
		}else{
			print DbError($link);
		}
	}else{
		$query	= GenQuery('events','s','count(*)','','',array('time','time',$in[0]),array('>=','<',$op[0]),array($istart,$iend,$st[0]),array('AND','AND'),'LEFT JOIN devices USING (device)');
		$res	= DbQuery($query,$link);
		if($res){
			$m = DbFetchRow($res);
			if($m[0]){
				if( !strpos($fmt,'g') ) echo "\t\t\t".Bar($m[0],'lvl10',$fmt)." $m[0]\n";
			}
			$dsval[$alllbl][$row] = $m[0];
			if( !strpos($fmt,'g') ) echo "\t\t</td>\n\t</tr>\n";
			$tmsg += $m[0];
			DbFreeResult($res);
		}else{
			print DbError($link);
		}
	}
	$istart = $iend;
	$row++;
	flush();
}
if( strpos($fmt,'g') ){
	$row = 0;
	ksort($dsval);
	$ncol = count($chd['labels']);
	echo "<div class=\"genpad txta bctr tqrt\">\n";
	foreach ( array_keys($dsval) as $dsgrp ){
		$row++;
		$cds = array();
		if($dsgrp == '50'){
			$rgba = '140,240,140';
		}elseif($dsgrp == '100'){
			$rgba = '140,140,240';
		}elseif($dsgrp == '150'){
			$rgba = '240,240,140';
		}elseif($dsgrp == '200'){
			$rgba = '240,180,100';
		}elseif($dsgrp == '250'){
			$rgba = '240,140,140';
		}elseif($dsgrp == '10'){
			$rgba = '200,200,200';
		}else{
			$rgba = GetCol('dsc',$row,1,1);
		}
		for($d=0;$d < $ncol;$d++){
			if( !array_key_exists($d,$dsval[$dsgrp]) ){
				$dsval[$dsgrp][$d] = 0;
			}
		}
		echo "<span style=\"background-color:rgb($rgba);padding:3px;margin:2px\">\n";
		echo "<a href=\"${monev}in[]=time&op[]=%3E=&st[]=$strsta&co[]=AND&in[]=time&op[]=%3C&st[]=$strend&in[]=$det&op[]==&st[]=".urlencode($dsgrp)."&co[]=AND&elm=$listlim\">";
		echo "$dsico[$dsgrp]</a></span>\n";
		ksort( $dsval[$dsgrp] );
		$cds['fillColor']     = "rgba($rgba,0.5)";
		$cds['strokeColor']   = "rgba($rgba,1)";
		$cds['highlightFill'] = "rgba($rgba,1)";
		$cds['pointColor']    = "rgba($rgba,1)";
		$cds['data'] = array_values( $dsval[$dsgrp] );
		$chd['datasets'][] = $cds;
	}
?>
</div>
<p>
<script src="inc/Chart.min.js"></script>
<canvas id="evchart" class="genpad bctr" width="960" height="400"></canvas>
<script language="javascript">
var data = <?= json_encode($chd,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("evchart").getContext("2d");
var myNewChart = new Chart(ctx).<?= ($fmt == 'cg')?'Bar':'Line' ?>(data,{scaleGridLineWidth : 1<?= $anim ?>});
</script>

<?php
	if($debug){
		echo "<div class=\"textpad code pre txta\">\n";
		print_r($chd);
		echo "</div>\n";
	}
}else{
	TblFoot("bgsub", 2, "$row $vallbl, $tmsg $msglbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}

include_once ("inc/footer.php");
?>
