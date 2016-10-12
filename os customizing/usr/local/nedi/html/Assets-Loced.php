<?php
# Program: Assets-Loced.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$id   = isset($_GET['id']) ? $_GET['id'] : '';
$reg  = isset($_GET['reg']) ? $_GET['reg'] : '';
$cty  = isset($_GET['cty']) ? $_GET['cty'] : '';
$bld  = isset($_GET['bld']) ? $_GET['bld'] : '';
$x    = isset($_GET['x']) ? $_GET['x'] : 0;
$y    = isset($_GET['y']) ? $_GET['y'] : 0;
$ns   = isset($_GET['ns']) ? $_GET['ns'] : 0;
$ew   = isset($_GET['ew']) ? $_GET['ew'] : 0;
$com  = isset($_GET['com']) ? $_GET['com'] : '';
$map  = isset($_GET['map']) ? 'checked' : '';
$dem  = isset($_GET['dem']) ? 'checked' : '';

$bgm  = "background.jpg";

echo "<h1>$loclbl $edilbl</h1>\n";

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if (isset($_GET['add']) and $reg){
	$query	= GenQuery('locations','i','','','',array('region','city','building','x','y','ns','ew','locdesc'),array(),array($reg,$cty,$bld,$x,$y,round($ns*10000000),round($ew*10000000),$com) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$addlbl $reg $cty $bld OK</h5>";}
}elseif (isset($_GET['up']) and $id){
	$query	= GenQuery('locations','u',"id = '$id'",'','',array('region','city','building','x','y','ns','ew','locdesc'),array(),array($reg,$cty,$bld,$x,$y,round($ns*10000000),round($ew*10000000),$com) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$updlbl $reg $cty $bld OK</h5>";}
}elseif(isset($_GET['del']) and $id){
	$query	= GenQuery('locations','d','','','',array('id'),array('='),array($id) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dellbl $reg $cty $bld OK</h5>";}
	$id = $reg = $cty = $bld = $x = $y = $com = $ns = $ew = '';
}

$locex = 0;
$lopt  = array();
$query = GenQuery('devices','s','distinct location');
$res   = DbQuery($query,$link);
if($res){
	while( ($d = DbFetchRow($res)) ){
		$l = explode($locsep, $d[0]);
		$lopt[$l[0]][$l[1]][$l[2]]++;
	}
	DbFreeResult($res);
}else{
	echo DbError($link);
}

if($reg){
	$z     = 6;
	$nam   = $reg;
	$ico   = 'img/32/glob.png';
	$res   = DbQuery( GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($reg,'',''),array('AND','AND') ),$link);
	$locex = DbNumRows($res);
	if ( $locex ){
		list($id,$x,$y,$dbns,$dbew,$com) = DbFetchRow($res);
		$dbns /= 10000000;
		$dbew /= 10000000;
		$geost = ($dem)?$com:$nam;
	}else{
		$geost = $nam;
		$x = $y = 0;
		$dbns = $dbew = 0;
		$com   = "$place[r], ".count(array_keys($lopt[$reg]))." $place[c] ($now)";
	}
	DbFreeResult($res);
}else{
	$nam = "$sellbl $place[r], $place[c], $place[b]";
}
if($cty){
	$z   = "11";
	$nam = "$cty, $reg";
	if(!$map) $bgm = TopoMap($reg);
	$ico = 'img/32/fort.png';
	$res = DbQuery( GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($reg,$cty,''),array('AND','AND') ),$link);
	$locex = DbNumRows($res);
	if ( $locex ){
		list($id,$x,$y,$dbns,$dbew,$com) = DbFetchRow($res);
		$dbns /= 10000000;
		$dbew /= 10000000;
		$geost = ($dem)?"$com, $geost":$nam;
	}else{
		$geost = ($dem)?"$cty, $geost":$nam;
		$x = $y = 0;
		$dbns = $dbew = 0;
		$com = "$place[c], ".count(array_keys($lopt[$reg][$cty]))." $place[b] ($now)";
	}
	DbFreeResult($res);
}
if($bld){
	$z   = "17";
	$nam = "$bld $cty, $reg";
	if(!$map) $bgm = TopoMap($reg,$cty);
	$ico = 'img/32/home.png';
	$res = DbQuery( GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($reg,$cty,$bld),array('AND','AND') ),$link);
	$locex = DbNumRows($res);
	if ( $locex ){
		list($id,$x,$y,$dbns,$dbew,$com) = DbFetchRow($res);
		$dbns /= 10000000;
		$dbew /= 10000000;
		$geost = ($dem)?"$com $geost":$nam;
	}else{
		$geost = ($dem)?"$bld $geost":$nam;
		$x = $y = 0;
		$dbns = $dbew = 0;
		$com = "$place[b], ".$lopt[$reg][$cty][$bld] ." devices ($now)";
	}
	DbFreeResult($res);
}

?>
<form method="get" action="<?= $self ?>.php" name="lof">
<table class="content" ><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top ctr">
	<h3><?= $place['r'] ?></h3>
	<select size="4" name="reg" onchange="document.lof.cty.selectedIndex = -1; document.lof.bld.selectedIndex = -1;this.form.submit();">
<?php
ksort($lopt);
foreach(array_keys($lopt) as $r){
	echo "\t\t<option value=\"$r\"".(($reg == $r)?" selected":"").">$r\n";
}
?>
	</select>
</td>
<td class="top ctr">
	<h3><?= $place['c'] ?></h3>
	<select size="4" name="cty" onchange="document.lof.bld.selectedIndex = -1;this.form.submit();">
<?php
if($reg){
ksort($lopt[$reg]);
	foreach(array_keys($lopt[$reg]) as $c){
		echo "\t\t<option value=\"$c\"".(($cty == $c)?" selected":"").">$c\n";
	}
}
?>
	</select>
</td>
<td class="top ctr">
	<h3><?= $place['b'] ?></h3>
	<select size="4" name="bld" onchange="this.form.submit();">
<?php
if($cty){
ksort($lopt[$reg][$cty]);
	foreach(array_keys($lopt[$reg][$cty]) as $b){
		echo "\t\t<option value=\"$b\"".(($bld == $b)?" selected":"").">$b\n";
	}
}
?>
	</select>
</td>
<td>
	<h3><?= $nam ?></h3>
	<img src="img/16/img.png" title="<?= $imglbl ?> <?= $loclbl ?>">
	<input type="number" min="0" name="x" class="m" value="<?= $x ?>" title="X">
	<input type="number" min="0" name="y" class="m" value="<?= $y ?>" title="Y">
	<br>
	<img src="img/16/map.png" title="GIS <?= $loclbl ?>" onclick="document.lof.ns.value=0;document.lof.ew.value=0">
	<input type="text" name="ns" class="m" value="<?= $dbns ?>" title="NS">
	<input type="text" name="ew" class="m" value="<?= $dbew ?>" title="EW">
	<br>
	<img src="img/16/find.png" title="<?= $deslbl ?>">
	<input type="text" name="com" class="xl" value="<?= $com ?>" onfocus="select();">
</td>
<td class="m">
	<img src="img/16/map.png" title="Geocoding">
	<input type="checkbox" name="map" <?= $map ?> title="<?= $stco['100'] ?> ">
	<input type="checkbox" name="dem" <?= $dem ?> title="<?= $deslbl ?> <?= $modlbl ?>">
</td>
<td class="ctr s">
<?php if($locex > 1) { ?>
	<a href="Assets-Locations.php?in[]=region&op[]=%3D&st[]=<?= urlencode($reg) ?>&co[]=AND&in[]=city&op[]=%3D&st[]=<?= urlencode($cty) ?>&co[]=AND&in[]=building&op[]=%3D&st[]=<?= urlencode($bld) ?>">
		<img title="<?= $mullbl ?> <?= $loclbl ?> Assets-Locations.php" src="img/16/home.png">
	</a>
<?php }elseif($locex) { ?>
	<input type="hidden" name="id" value="<?= $id ?>">
	<input type="submit" class="button" name="up" value="<?= $updlbl ?>"><p>
	<input type="submit" class="button" name="del" value="<?= $dellbl ?>">
<?php } else{?>
	<input type="submit" class="button" name="add" value="<?= $addlbl ?>"><p>
<?php } ?>
</td>
</tr>
</table>
</form>
<p>
<?php if( $map and $reg ){ ?>

<h2><?= $geost ?></h2>

<script src="inc/leaflet.js"></script>
<link rel="stylesheet" href="inc/leaflet.css" />
<div id="map" class="bctr"  style="width:1024px; height:600px;border:1px solid black"></div>

<?php
	if( $dbns or $dbew ){
		$ns = $dbns;
		$ew = $dbew;
	}else{
		$url = "http://nominatim.openstreetmap.org/search?format=json&limit=2&q=".urlencode($geost);
		$geo = json_decode( file_get_contents($url), TRUE);
		if($debug){echo "<div class=\"textpad code pre tqrt".(($geo)?'good':'alrm')."\"><strong>$url</strong><p>";print_r($geo); echo '</div>';}
		if($geo and $geo[0][lat]){
			$ns = $geo[0][lat];
			$ew = $geo[0][lon];
			echo "<script language=\"JavaScript\">\n";
			echo "document.lof.ns.value = '$ns'\n";
			echo "document.lof.ew.value = '$ew'\n";
			echo "document.lof.ns.style.color = 'blue'\n";
			echo "document.lof.ew.style.color = 'blue'\n";
			echo "</script>\n";
		}
	}
?>

<script language="JavaScript">

var map = L.map('map').setView([<?= $ns ?>, <?= $ew ?>], <?= $z ?>);

var Icon = L.icon({
	iconUrl: '<?= $ico ?>',
	iconSize:    [32, 32],
	shadowSize:  [0,0],
	iconAnchor:  [16, 16],
	popupAnchor: [-8, 16]
});

L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
	attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

marker = new L.marker([<?= $ns ?>, <?= $ew ?>], {icon: Icon, draggable:'true'});
marker.on('dragend', function(event){

	var marker = event.target;
	var pos = marker.getLatLng();
	marker.setLatLng(new L.LatLng(pos.lat, pos.lng),{draggable:'true'});
	map.panTo(new L.LatLng(pos.lat, pos.lng))

	document.lof.ns.value = pos.lat;
	document.lof.ew.value = pos.lng;
	document.lof.ns.style.color = "green";
	document.lof.ew.style.color = "green";
});
map.addLayer(marker);

</script>

<?php } else{
	$bgsize = getimagesize("topo/$bgm");
?>

<h2><?= $bgm ?></h2>

<div align="center"><!-- Only seems to work this way :-( -->
	<div id="map" onclick="getcoord(event)" style="background-image:url('topo/<?= $bgm ?>');width:<?= $bgsize[0] ?>px;height:<?= $bgsize[1] ?>px;border:1px solid black">
		<img src="<?= $ico ?>" id="loc" style="position:relative;visibility:hidden;z-index:2;">
	</div>
</div>

<script language="JavaScript">

function getcoord(event){
	mapx = event.offsetX?(event.offsetX):event.pageX-document.getElementById("map").offsetLeft;
	mapy = event.offsetY?(event.offsetY):event.pageY-document.getElementById("map").offsetTop;
	document.lof.x.value = mapx;
	document.lof.y.value = mapy;
	document.getElementById("loc").style.visibility = "visible" ;
	document.getElementById("loc").style.left = (mapx-<?= $bgsize[0]/2?>)+'px';
	document.getElementById("loc").style.top = (mapy-15)+'px';
}

<?php if($x and $y) { ?>
document.getElementById("loc").style.left = "<?= ($x-$bgsize[0]/2) ?>px";
document.getElementById("loc").style.top = "<?= ($y-15) ?>px" ;
document.getElementById("loc").style.visibility = "visible" ;
<?php } ?>

</script>
<?php } ?>

<?php
include_once ("inc/footer.php");
?>
