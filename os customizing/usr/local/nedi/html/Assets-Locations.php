<?php
# Program: Assets-Locations.php
# Programmer: Remo Rickli (based on ideas of Steffen Scholz)

$exportxls = 1;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$ord = isset($_GET['ord']) ? $_GET['ord'] : "";
if($_SESSION['opt'] and !$ord and count($in)) $ord = $in[0];

$map = isset($_GET['map']) ? $_GET['map'] : "";
$lim = isset($_GET['lim']) ? preg_replace('/\D+/','',$_GET['lim']) : $listlim;

if( isset($_GET['col']) ){
	$col = $_GET['col'];
	if($_SESSION['opt']){$_SESSION['loccol'] = $_GET['col'];}
}elseif( isset($_SESSION['loccol']) ){
	$col = $_SESSION['loccol'];
}else{
	$col = array('locBL','region','city','building','locdesc');
}

$cols = array(	"locBL"=>$loclbl,
		"id"=>"ID",
		"region"=>$place['r'],
		"city"=>$place['c'],
		"building"=>$place['b'],
		"x"=>"X",
		"y"=>"Y",
		"ns"=>"Latitude (NS)",
		"ew"=>"Longitude (EW)",
		"locdesc"=>$deslbl,
		"dvNS"=>"Devices",
		"poNS"=>$poplbl,
		"filNS"=>$fillbl,
		"cmdNS"=>$cmdlbl
		);

$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if( isset($_GET['del']) ){
	if($isadmin){
		$query	= GenQuery('locations','d','*','','',$in,$op,$st,$co);
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$loclbl $dellbl OK</h5>";}
	}else{
		echo $nokmsg;
	}
}

?>
<h1><?= $loclbl ?> <?= $lstlbl ?></h1>

<?php  if( !isset($_GET['print']) ) { ?>

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
	<select multiple name="col[]" size="6">
<?php
foreach ($cols as $k => $v){
	echo "\t\t<option value=\"$k\"".((in_array($k,$col))?" selected":"").">$v\n";
}
?>
	</select>
</td>
<td>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lim">
<?php selectbox("limit",$lim) ?>
	</select>
	<br>
	<img src="img/16/paint.png" title="<?= (($verb1)?"$addlbl Map":"Map $addlbl") ?>">
	<select size="1" name="map">
		<option value=""><?= $nonlbl ?> <?= $maplbl ?>
		<option value="1"<?= ($map == 1)?" selected":"" ?>><?= $laslbl ?> NeDimap
		<option value="2"<?= ($map == 2)?" selected":"" ?>><?= $altlbl ?> <?= $maplbl ?>
	</select>
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>"><br>
	<input type="submit" class="button" name="del" value="<?= $dellbl ?>" onclick="return confirm('<?= $dellbl ?>, <?= $cfmmsg ?>')" >
</td>
</tr>
</table>
</form>
<p>
<?php
}

if( count($in) ){

	if ( $map and !isset($_GET['xls'])){
		if( $map > 1 ){
?>

	<img name="map" class="genpad bctr" src="img/32/paint.png">

<?php
		}elseif( file_exists("map/map_$_SESSION[user].png") ){
			echo "<p><img src=\"map/map_$_SESSION[user].png\" class=\"bctr genpad\">\n";
		}
	}

	Condition($in,$op,$st,$co);

	TblHead("bgsub",1);
	$query	= GenQuery('locations','s','*',$ord,$lim,$in,$op,$st,$co );
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		$omk = array();
		$minew = 180;
		$maxew = -180;
		$minns = 90;
		$maxns = -90;
		while( ($l = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ns = $l[6]/10000000;
			$ew = $l[7]/10000000;
			if($ns > $maxns){$maxns = $ns;}
			if($ns < $minns){$minns = $ns;}
			if($ew > $maxew){$maxew = $ew;}
			if($ew < $minew){$minew = $ew;}

			if($l[3]){
				$ico = 'home';
				$tit = "$place[b]-$l[0]";
			}elseif($l[2]){
				$tit = "$place[c]-$l[0]";
				$ico = 'fort';
			}else{
				$tit = "$place[r]-$l[0]";
				$ico = 'glob';
			}
			if($ns and $ew) $omk[] = "$ns,$ew,lightblue$row";
			TblRow($bg);
			if(in_array("locBL",$col))	TblCell( ($map>1)?$row:'','',"$bi ctr xs","+<img src=\"img/16/$ico.png\" title=\"$tit\">" );
			if(in_array("id",$col))		TblCell($l[0],"?in[]=id&op[]==&st[]=$l[0]&map=$map",'rgt s');
			if(in_array("region",$col)) 	TblCell($l[1],"?in[]=region&op[]==&st[]=".urlencode($l[1])."&map=$map");
			if(in_array("city",$col))	TblCell($l[2],"?in[]=city&op[]==&st[]=".urlencode($l[2])."&map=$map");
			if(in_array("building",$col))	TblCell($l[3],"?in[]=building&op[]==&st[]=".urlencode($l[3])."&map=$map");
			if(in_array("x",$col))		TblCell($l[4],'','rgt');
			if(in_array("y",$col))		TblCell($l[5],'','rgt');
			if(in_array("ns",$col))		TblCell($ns);
			if(in_array("ew",$col))		TblCell($ew);
			if(in_array("locdesc",$col))	TblCell($l[8]);
			if(in_array("dvNS",$col)){
				$lor = TopoLoc($l[1],$l[2],$l[3]);
				$pop = DevPop(array('location'),array('LIKE'),array($lor));
				if($pop){
					TblCell(' '.$pop,"Devices-List.php?in[]=location&op[]=LIKE&st[]=".urlencode($lor),'','+'.Bar($pop,'lvl100','sbar'));
				}else{
					TblCell();
				}
			}
			if(in_array("poNS",$col)){
				$lor = TopoLoc($l[1],$l[2],$l[3]);
				$pop = NodPop( array('location'),array('LIKE'),array($lor),array() );
				if($pop){
					TblCell(' '.$pop,"Nodes-List.php?in[]=location&op[]=LIKE&st[]=".urlencode($lor),'','+'.Bar($pop,'lvl100','sbar'));
				}else{
					TblCell();
				}
			}
			if(in_array("filNS",$col) and !isset($_GET['xls']) ){
				echo "\t\t<td>\n";
				$pfx = 'topo/';
				if($l[1]) $pfx .= preg_replace('/\W/','', $l[1]).'/';
				if($l[2]) $pfx .= preg_replace('/\W/','', $l[2]).'/';
				if($l[3]) $pfx .= preg_replace('/\W/','', $l[3]).'-';
				foreach (glob("$pfx*") as $fil){
					if( !is_dir($fil) ){
						$lbl = basename($fil);
						list($ico,$ed) = FileImg($fil);
						echo "\t\t\t$ico\n";
					}
				}
				echo "\t\t</td>\n";
			}
			if(in_array("cmdNS",$col) and !isset($_GET['xls']) ){
				$ur = urlencode($l[1]);
				$uc = urlencode($l[2]);
				$ub = urlencode($l[3]);
				$ul = urlencode(TopoLoc($l[1],$l[2],$l[3]));
				$lv = 2;
				$gl = "$ur";
				if($ub){
					$lv = 4;
					$gl = "$ub $uc, $ur";
				}elseif($uc){
					$lv = 3;
					$gl = "$uc, $ur";
				}
				echo "\t\t<td align=\"right\">\n";
				if($ns or $ew){
					echo "\t\t\t<a href=\"http://nominatim.openstreetmap.org/search.php?q=$ns,$ew\" target=\"window\"><img src=\"img/16/map.png\" title=\"Openstreetmap coords\"></a>\n";
				}
				echo "\t\t\t<a href=\"http://nominatim.openstreetmap.org/search.php?q=$gl\" target=\"window\"><img src=\"img/16/osm.png\" title=\"Openstreetmap $namlbl\"></a>\n";
				echo "\t\t\t<a href=\"Topology-Map.php?st[]=$ul&fmt=png&lev=$lv\"><img src=\"img/16/paint.png\" title=\"Topology Map\"></a>\n";
				echo "\t\t\t<a href=\"Topology-Table.php?reg=$ur&cty=$uc&bld=$ub\"><img src=\"img/16/icon.png\" title=\"Topology Table\"></a>\n";
				echo "\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=~&st[]=$ul\"><img src=\"img/16/dev.png\" title=\"Device $lstlbl\"></a>\n";
				echo "\t\t\t<a href=\"Nodes-List.php?in[]=location&op[]=~&st[]=$ul\"><img src=\"img/16/nods.png\" title=\"Nodes $lstlbl\"></a>\n";
				if($isadmin){
					echo "\t\t\t<a href=\"Assets-Loced.php?id=$l[0]&del=1\"><img src=\"img/16/bcnl.png\" title=\"$dellbl\"></a>\n";
				}
				echo "\t\t</td>\n";
			}
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row $loclbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );

	if( $map > 1 and !isset($_GET['xls']) ){
		$cns = ($maxns + $minns)/2;								# Center
		$cew = ($maxew + $minew)/2;
		$van = $maxns - $minns;
		$han = $maxew - $minew;
		$man = $van>$han?$van:$han;								# Max. angle
		$z   = 1 + abs( intval( log(360/$man,2) ) );						# Angle = 360/2^z
		if( $z > 18 ) $z = 18;
		if( $z < 5  ) $z++;
?>
	<script language="javascript">
	document.map.src ="http://staticmap.openstreetmap.de/staticmap.php?center=<?= $cns ?>,<?= $cew ?>&zoom=<?= $z ?>&size=1000x700&maptype=mapnik&markers=<?= implode('|',$omk) ?>"
	</script>
<?php
	}
}

include_once ("inc/footer.php");
?>
