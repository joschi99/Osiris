<?php
# Program: Reports-Networks.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/librep.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$rep = isset($_GET['rep']) ? $_GET['rep'] : array();

$lim = isset($_GET['lir']) ? preg_replace('/\D+/','',$_GET['lir']) : 10;

$map = isset($_GET['map']) ? "checked" : "";
$ord = isset($_GET['ord']) ? "checked" : "";
$opt = isset($_GET['opt']) ? "checked" : "";

$cols = array(	"device"=>"Device",
		"devip"=>"IP $adrlbl",
		"vendor"=>$venlbl,
		"type"=>"Device $typlbl",
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl",
		"services"=>$srvlbl,
		"description"=>$deslbl,
		"devos"=>"Device OS",
		"bootimage"=>"Bootimage",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"devmode"=>$modlbl,
		"snmpversion"=>"SNMP $verlbl",
		"ifname"=>"IF $namlbl",
		"iftype"=>"IF $typlbl",
		"linktype"=>"Link $typlbl",
		"ifdesc"=>"IF $deslbl",
		"comment"=>"IF $cmtlbl",
		"vlid"=>"Vlan ID",
		"alias"=>"Alias",
		"lastchg"=>"$laslbl $chglbl"
		);
?>
<h1><?= $netlbl ?> Reports</h1>

<?php  if( !isset($_GET['print']) ) { ?>

<form method="get" name="report" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top">
<?php Filters(1); ?>
</td>
<td class="ctr">
	<a href="?in[]=lastdis&op[]=<&st[]=<?= time()-2*$rrdstep ?>&co[]=&in[]=lastdis&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&col[]=device&col[]=devip&col[]=location&col[]=contact&col[]=firstdis&col[]=lastdis&ord=lastdis+desc"><img src="img/16/date.png" title="<?= $undlbl ?> Devices"></a>
	<a href="?in[]=lastdis&op[]=>&st[]=<?= time()-86400 ?>&co[]=&in[]=lastdis&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&col[]=device&col[]=devip&col[]=location&col[]=contact&col[]=firstdis&col[]=lastdis&ord=lastdis+desc"><img src="img/16/clock.png" title="<?= $dsclbl ?> <?= $tim['t'] ?>"></a>
</td>
<td class="ctr">
	<select multiple name="rep[]" size="4">
		<option value="net" <?php if(in_array("net",$rep)){echo "selected";} ?> ><?= $netlbl ?> <?= $dislbl ?>

		<option value="pop" <?php if(in_array("pop",$rep)){echo "selected";} ?> ><?= $netlbl ?> <?= $poplbl ?>

	</select>
</td>
<td class="ctr">
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lir">
<?php selectbox("limit",$lim) ?>
	</select>
</td>
<td class="ctr">
	<img src="img/16/paint.png" title="<?= (($verb1)?"$sholbl $laslbl Map":"Map $laslbl $sholbl") ?>">
	<input type="checkbox" name="map" <?= $map ?>><br>
	<img src="img/16/abc.png" title="<?= $altlbl ?> <?= $srtlbl ?>">
	<input type="checkbox" name="ord" <?= $ord ?>><br>
	<img src="img/16/hat2.png" title="<?= $optlbl ?>">
	<input type="checkbox" name="opt" <?= $opt ?>>
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
echo "<div class=\"ctr\">\n";
if ($map and file_exists("map/map_$_SESSION[user].php")) {
		echo "<h2>$netlbl Map</h2>\n";
		echo "<img src=\"map/map_$_SESSION[user].php\" class=\"genpad\">\n";
}
echo "</div>\n<p>\n\n";

if($rep){
	Condition($in,$op,$st,$co);

	$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);

	if ( in_array("net",$rep) ){
		NetDist($in[0],$op[0],$st[0],$lim,$ord);
	}

	if ( in_array("pop",$rep) ){
		NetPop($in[0],$op[0],$st[0],$lim,$ord);
	}
}

include_once ("inc/footer.php");

?>
