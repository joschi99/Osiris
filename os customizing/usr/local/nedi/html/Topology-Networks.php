<?php
# Program: Topology-Networks.php
# Programmer: Remo Rickli

$exportxls = 1;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$ord = isset($_GET['ord']) ? $_GET['ord'] : "";
if($_SESSION['opt'] and !$ord and $in[0]) $ord = $in[0];

$map = isset($_GET['map']) ? "checked" : "";
$lim = isset($_GET['lim']) ? preg_replace('/\D+/','',$_GET['lim']) : $listlim;

if( isset($_GET['col']) ){
	$col = $_GET['col'];
	if($_SESSION['opt']) $_SESSION['netcol'] = $col;
}elseif( isset($_SESSION['netcol']) ){
	$col = $_SESSION['netcol'];
}else{
	$col = array('imBL','ifip','device','ifname','vrfname');
}

$cols = array(	"imBL"=>$imglbl,
		"ifip"=>"IP $adrlbl",
		"ifip6"=>"IPv6 $adrlbl",
		"prefix"=>"Prefix",
		"device"=>"Device $namlbl",
		"type"=>"Device $typlbl",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl",
		"ifname"=>"IF $namlbl",
		"vrfname"=>"VRF $namlbl",
		"vrfrd"=>"VRF RD",
		"status"=>$stalbl
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
?>
<h1><?= $netlbl ?> <?= $lstlbl ?></h1>

<?php  if( !isset($_GET['print']) and !isset($_GET['xls']) ) { ?>

<form method="get" name="list" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
<?php Filters(); ?>
</td>
<td class="ctr">
	<select multiple name="col[]" size="6" title="<?= $collbl ?>">
<?php
foreach ($cols as $k => $v){
	echo "\t\t<option value=\"$k\"".((in_array($k,$col))?" selected":"").">$v\n";
}
?>
	</select>
</td>
<td>
	<img src="img/16/paint.png" title="<?= (($verb1)?"$sholbl $laslbl Map":"Map $laslbl $sholbl") ?>">
	<input type="checkbox" name="map" <?= $map ?>><br>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lim">
<?php selectbox("limit",$lim) ?>
	</select>
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>">
</td>
</tr>
</table>
</form>
<p>

<?php
}
if( count($in) ){
	if ($map and !isset($_GET['xls']) and file_exists("map/map_$_SESSION[user].php")) {
		echo "<div class=\"ctr\">\n\t<h2>$netlbl Map</h2>\n";
		echo "\t<img src=\"map/map_$_SESSION[user].php\" class=\"genpad\">\n</div>\n<p>\n\n";
	}

	Condition($in,$op,$st,$co);
	TblHead("bgsub",1);
	$query	= GenQuery('networks','s','networks.*,type,firstdis,lastdis,location,contact',$ord,$lim,$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($m = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ip6 = DbIPv6($m[3]);
			$ud  = urlencode($m[0]);
			$ip  = ($m[2])?long2ip($m[2]):"";
			list($ntimg,$ntit) = Nettype($ip,$ip6);
			list($ifb,$ifs)    = Ifdbstat($m[7]);
			list($fc,$lc)      = Agecol($m[8],$m[9],$row % 2);
			TblRow($bg);
			if(in_array("imBL",$col))	TblCell("<img src=\"img/$ntimg\" title=\"$ntit\">",'',"$ifb ctr xs");
			if(in_array("ifip",$col))	TblCell($ip,"?in[]=ifip&op[]==&st[]=$ip/$m[4]");
			if(in_array("ifip6",$col))	TblCell($ip6,'','prp' );
			if(in_array("prefix",$col))	TblCell($m[4]);
			if( in_array("device",$col) )	TblCell($m[0],"?in[]=device&op[]==&st[]=$ud&ord=ifname",'nw',"<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/16/sys.png\"></a>");
			if(in_array("type",$col))	TblCell( $m[8],"?in[]=type&op[]==&st[]=".urlencode($m[8]) );
			if(in_array("location",$col))	TblCell( $m[11],"?in[]=location&op[]==&st[]=".urlencode($m[11]) );
			if(in_array("contact",$col))	TblCell( $m[12],"?in[]=contact&op[]==&st[]=".urlencode($m[12]) );
			if( in_array("firstdis",$col) )	TblCell( date($_SESSION['timf'],$m[8]),"?in[]=firstdis&op[]==&st[]=$m[9]",'nw','',"background-color:#$fc" );
			if( in_array("lastdis",$col) )	TblCell( date($_SESSION['timf'],$m[9]),"?in[]=lastdis&op[]==&st[]=$m[10]",'nw','',"background-color:#$lc" );
			if(in_array("ifname",$col))	TblCell( $m[1],"?in[]=ifname&op[]==&st[]=".urlencode($m[1]) );
			if(in_array("vrfname",$col))	TblCell( $m[5],"?in[]=vrfname&op[]==&st[]=".urlencode($m[5]) );
			if(in_array("vrfrd",$col))	TblCell( $m[6],"?in[]=vrfrd&op[]==&st[]=".urlencode($m[6]) );
			if(in_array("status",$col))	TblCell( $m[7],"?in[]=status&op[]==&st[]=".urlencode($m[7]) );
			echo "	</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row $netlbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}elseif($_SESSION['opt']){
	include_once ("inc/librep.php");
	DevDupIP($in[0],$op[0],$st[0],$_SESSION['lim'],'');
}
include_once ("inc/footer.php");
?>
