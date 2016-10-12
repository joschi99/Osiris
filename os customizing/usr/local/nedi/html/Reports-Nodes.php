<?php
# Program: Reports-Nodes.php
# Programmer: Remo Rickli (and contributors)

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libnod.php");
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

$cols = array(	"device"=>"Device $namlbl",
		"devip"=>"IP $adrlbl",
		"vendor"=>"Device $venlbl",
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
		"aname"=>"Node $namlbl",
		"nodip"=>"Node IP",
		"oui"=>"OUI $venlbl",
		"firstseen"=>$fislbl,
		"lastseen"=>$laslbl,
		"vlanid"=>"Vlan ID",
		"ifmetric"=>"IF $metlbl",
		"ifupdate"=>"IF $updlbl",
		"ifchanges"=>"IF $chglbl",
		"ipupdate"=>"IP $updlbl",
		"ipchanges"=>"IP $chglbl",
		"tcpports"=>"TCP $porlbl",
		"udpports"=>"UDP $porlbl",
		"srvtype"=>"Node $typlbl",
		"srvos"=>"Node OS",
		"osupdate"=>"OS $updlbl"
		);
?>
<h1>Node Reports</h1>

<script src="inc/Chart.min.js"></script>

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
	<a href="?in[]=snmpversion&op[]=>&st[]=0"><img src="img/16/dev.png" title="SNMP Devices"></a>
	<a href="?in[]=devmode&op[]==&st[]=8"><img src="img/16/wlan.png" title="Controlled APs"></a>
	<a href="?in[]=lastdis&op[]=<&st[]=<?= time()-2*$rrdstep ?>&co[]=&in[]=lastdis&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&col[]=device&col[]=devip&col[]=location&col[]=contact&col[]=firstdis&col[]=lastdis&ord=lastdis+desc"><img src="img/16/date.png" title="<?= $undlbl ?> Devices"></a>
	<a href="?in[]=lastdis&op[]=>&st[]=<?= time()-86400 ?>&co[]=&in[]=lastdis&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&co[]=&in[]=device&op[]=~&st[]=&col[]=device&col[]=devip&col[]=location&col[]=contact&col[]=firstdis&col[]=lastdis&ord=lastdis+desc"><img src="img/16/clock.png" title="<?= $dsclbl ?> <?= $tim['t'] ?>"></a>
</td>
<td class="ctr">
	<select multiple name="rep[]" size="4">
		<option value="sum" <?php if(in_array("sum",$rep)){echo "selected";} ?> ><?= $sumlbl ?>
		<option value="dis" <?php if(in_array("dis",$rep)){echo "selected";} ?> >Node <?= $dislbl ?>
		<option value="dup" <?php if(in_array("dup",$rep)){echo "selected";} ?> ><?= $duplbl ?> Nodes
		<option value="nad" <?php if(in_array("nad",$rep)){echo "selected";} ?> >Nodes <?= $adrlbl ?>
		<option value="srv" <?php if(in_array("srv",$rep)){echo "selected";} ?> >OS & <?= $srvlbl ?>
		<option value="nom" <?php if(in_array("nom",$rep)){echo "selected";} ?> ><?= $nomlbl ?>
		<option value="vem" <?php if(in_array("vem",$rep)){echo "selected";} ?> ><?= (($verb1)?"$emplbl Vlans":"Vlans $emplbl") ?>
		<option value="nhs" <?php if(in_array("nhs",$rep)){echo "selected";} ?> ><?= $dsclbl ?> <?= $hislbl ?>
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
</td>
<td class="ctr">
	<input type="submit" class="button" name="gen" value="<?= $sholbl ?>">
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

	if ( in_array("sum",$rep) ){
		NodSum($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("dup",$rep) ){
		NodDup($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("nad",$rep) ){
		NodIP($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("dis",$rep) ){
		NodDist($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("srv",$rep) ){
		NodSrv($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("nom",$rep) ){
		NodNomad($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("nhs",$rep) ){
		NodHistory($in[0],$op[0],$st[0],$lim,$ord);
	}
	if ( in_array("vem",$rep) ){
		VlanEmpty($in[0],$op[0],$st[0],$lim,$ord);
	}
}

include_once ("inc/footer.php");
?>
