<?php
# Program: Topology-Links.php
# Programmer: Remo Rickli (based on suggestion of richard.lajaunie)

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
	if($_SESSION['opt']) $_SESSION['lnkcol'] = $col;
}elseif( isset($_SESSION['lnkcol']) ){
	$col = $_SESSION['lnkcol'];
}else{
	$col = array('device','ifname','neighbor','nbrifname','linktype','linkdesc','time');
}

$cols = array(	"id"=>"ID",
		"device"=>"Device $namlbl",
		"ifname"=>"IF $namlbl",
		"type"=>"Device $typlbl",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl",
		"neighbor"=>"$neblbl",
		"nbrifname"=>"$neblbl IF",
		"bandwidth"=>"$bwdlbl",
		"linktype"=>"$typlbl",
		"linkdesc"=>"$deslbl",
		"nbrduplex"=>"$neblbl Duplex",
		"nbrvlanid"=>"$neblbl Vlan",
		"time"=>$timlbl
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
?>
<h1>Link <?= $lstlbl ?></h1>

<?php  if( !isset($_GET['print']) and !isset($_GET['xls']) ) { ?>

<form method="get" name="list" action="<?= $self ?>.php">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>

<td>
<?php Filters(); ?>
</td>
<td class="ctr s">
	<a href="?in[]=device&op[]=~&st[]=&co[]=%3D&in[]=neighbor"><img src="img/16/brld.png" title="Loops"></a>
	<a href="?in[]=time&op[]=<&st[]=<?= strtotime("-1 day") ?>&ord=time"><img src="img/16/date.png" title="<?= $outlbl ?> <?= $cnclbl ?>"></a>
</td>
<td class="ctr">
	<select multiple name="col[]" size="6">
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
	$query	= GenQuery('links','s','links.*,type,firstdis,lastdis,location,contact,devgroup',$ord,$lim,$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($l = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($l[1]);
			$un = urlencode($l[3]);
			list($fc,$lc) = Agecol($l[12],$l[13],$row % 2);
			list($tc,$tc) = Agecol($l[10],$l[10],$row % 2);

			TblRow($bg);
			if(in_array("id",$col))		TblCell($l[0]);
			if( in_array("device",$col) )	TblCell($l[1],"?in[]=device&op[]==&st[]=$ud&ord=ifname",'nw',"<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/16/sys.png\"></a><a href=\"Topology-Linked.php?dv=$ud\"><img src=\"img/16/ncon.png\"></a>");
			if(in_array("ifname",$col))	TblCell($l[2]);
			if(in_array("type",$col))	TblCell( $l[11],"?in[]=type&op[]==&st[]=".urlencode($l[11]) );
			if(in_array("location",$col))	TblCell( $l[14],"?in[]=location&op[]==&st[]=".urlencode($l[14]) );
			if(in_array("contact",$col))	TblCell( $l[15],"?in[]=contact&op[]==&st[]=".urlencode($l[15]) );
			if(in_array("devgroup",$col))	TblCell( $l[16],"?in[]=contact&op[]==&st[]=".urlencode($l[16]) );
			if( in_array("firstdis",$col) )	TblCell( date($_SESSION['timf'],$l[12]),"?in[]=firstdis&op[]==&st[]=$l[12]",'','',"background-color:#$fc" );
			if( in_array("lastdis",$col) )	TblCell( date($_SESSION['timf'],$l[13]),"?in[]=lastdis&op[]==&st[]=$l[13]",'','',"background-color:#$lc" );
			if( in_array("neighbor",$col) )	TblCell($l[3],"?in[]=device&op[]==&st[]=$un&ord=ifname",'nw',"<a href=\"Devices-Status.php?dev=$un\"><img src=\"img/16/sys.png\"></a><a href=\"Topology-Linked.php?dv=$un\"><img src=\"img/16/ncon.png\"></a>");
			if(in_array("nbrifname",$col))	TblCell($l[4]);
			if(in_array("bandwidth",$col))	TblCell( DecFix($l[5]),'','rgt' );
			if(in_array("linktype",$col))	TblCell( $l[6],"?in[]=linktype&op[]==&st[]=$l[6]");
			if(in_array("linkdesc",$col))	TblCell($l[7]);
			if(in_array("nbrduplex",$col))	TblCell($l[8],'','ctr');
			if(in_array("nbrvlanid",$col))	TblCell($l[9],'','rgt');
			if(in_array("time",$col))	TblCell( date($_SESSION['timf'],$l[10]),"?in[]=time&op[]==&st[]=$l[10]",'nw','',"background-color:#$tc" );
			echo "	</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row Links".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}elseif($_SESSION['opt']){
	include_once ("inc/librep.php");
	include_once ("inc/libdev.php");
	DevLink($in[0],$op[0],$st[0],$_SESSION['lim'],'');
}
include_once ("inc/footer.php");
?>
