<?php
# Program: Devices-Modules.php
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
	if($_SESSION['opt']) $_SESSION['modcol'] = $_GET['col'];
}elseif( isset($_SESSION['modcol']) ){
	$col = $_SESSION['modcol'];
}else{
	$col = array('imBL','device','slot','model','moddesc','modules.serial');
}

$cols = array(	"imBL"=>$imglbl,
		"modclass"=>$clalbl,
		"device"=>"Device $namlbl",
		"vendor"=>$venlbl,
		"type"=>"Device $typlbl",
		"devgroup"=>$grplbl,
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl",
		"slot"=>"Slot",
		"model"=>$mdllbl,
		"moddesc"=>"Module $deslbl",
		"modules.serial"=>$serlbl,
		"hw"=>"Hardware",
		"fw"=>"Firmware",
		"sw"=>"Software",
		"modidx"=>$idxlbl,
		"status"=>$stalbl,
		"modloc"=>"Module $loclbl",
		"postNS"=>"$porlbl $stalbl"
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
?>
<h1 onclick="document.list.style.display = (document.list.style.display == 'none')?'':'none';">Module <?= $lstlbl ?></h1>

<?php  if( !isset($_GET['print']) and !isset($_GET['xls']) ) { ?>
<form method="get" name="list" action="<?= $self ?>.php">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
<?php Filters(); ?>
</td>
<td class="ctr">
	<a href="?in[]=status&op[]==&st[]=1&co[]=AND&in[]=modclass&op[]=!=&st[]=30"><img src="img/16/bdis.png" title="<?= $stalbl ?>: <?= $notlbl ?> <?= $stco[100] ?>"></a>
	<a href="?in[]=slot&op[]=LIKE&st[]=FEX-%25&in[]=moddesc&co[]=OR&op[]=LIKE&st[]=qfc%25&in[]=modclass&op[]=%3D&co[]=AND&st[]=3&col[]=imBL&col[]=device&col[]=slot&col[]=model&col[]=modloc&col[]=postNS"><img src="img/16/port.png" title="FEX <?= $porlbl ?> <?= $stalbl ?>"></a><br>
	<a href="?in[]=modclass&op[]==&st[]=30"><img src="img/16/print.png" title="Printsupplies"></a>
	<a href="?in[]=modclass&op[]==&st[]=40"><img src="img/16/node.png" title="Virtual Machines"></a>
</td>
<td class="ctr">
	<select multiple name="col[]" size="6" title="<?= $collbl ?>">
<?php
foreach ($cols as $k => $v){
       echo "		<option value=\"$k\"".((in_array($k,$col) )?" selected":"").">$v\n";
}
?>
	</select>
</td>
<td>
	<img src="img/16/paint.png" title="<?= (($verb1)?"$sholbl $laslbl Map":"Map $laslbl $sholbl") ?>">
	<input type="checkbox" name="map" <?= $map ?>>
	<br>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lim">
<?php selectbox("limit",$lim) ?>
	</select>
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>">
</td>
</tr></table>
</form>
<p>
<?php
}
if( count($in) ){
	if ($map and !isset($_GET['xls']) and file_exists("map/map_$_SESSION[user].php")) {
		echo "<div class=\"ctr\">\n	<h2>$netlbl Map</h2>\n";
		echo "	<img src=\"map/map_$_SESSION[user].php\" style=\"border:1px solid black\">\n</div>\n<p>\n";
	}
	Condition($in,$op,$st,$co);
	TblHead("bgsub",1);

	$query	= GenQuery('modules','s','modules.*,type,firstdis,lastdis,devgroup,location,contact,vendor',$ord,$lim,$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($m = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($m[0]);
			list($mcl,$img)    = ModClass($m[9]);
			list($mstat,$mtit) = ModStat($m[10],$bi,$m[9]);
			list($fc,$lc)      = Agecol($m[13],$m[14],$row % 2);

			TblRow($bg);
			if( in_array("imBL",$col) )	TblCell('','',"$mstat ctr xs","+<a href=\"?in[]=modclass&op[]==&st[]=$m[9]\"><img src=\"img/16/$img.png\" title=\"$mcl$mtit\"></a>");
			if( in_array("modclass",$col) )	TblCell( $mcl,"?in[]=modclass&op[]==&st[]=$m[9]");
			if( in_array("device",$col) )	TblCell($m[0],"?in[]=device&op[]==&st[]=$ud",'nw',"<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/16/sys.png\"></a>");
			if( in_array("vendor",$col) )	TblCell($m[18],"?in[]=vendor&op[]==&st[]=$m[18]" );
			if( in_array("type",$col) )	TblCell($m[12],"?in[]=type&op[]==&st[]=".urlencode($m[12]),'nw',"+<a href=\"http://www.google.com/search?q=".urlencode("$m[18] $m[12]")."&btnI=1\" target=\"window\"><img src=\"img/oui/".VendorIcon($m[18]).".png\" title=\"$m[18]\"></a> ");
			if( in_array("devgroup",$col) )TblCell( $m[15],"?in[]=devgroup&op[]==&st[]=".urlencode($m[15]) );
			if( in_array("location",$col) )	TblCell( $m[16],"?in[]=location&op[]==&st[]=".urlencode($m[16]) );
			if( in_array("contact",$col) )	TblCell( $m[17],"?in[]=contact&op[]==&st[]=".urlencode($m[17]) );
			if( in_array("firstdis",$col) )	TblCell( date($_SESSION['timf'],$m[13]),"?in[]=firstdis&op[]==&st[]=$m[13]",'nw','',"background-color:#$fc" );
			if( in_array("lastdis",$col) )	TblCell( date($_SESSION['timf'],$m[14]),"?in[]=lastdis&op[]==&st[]=$m[14]",'nw','',"background-color:#$lc" );
			if( in_array("slot",$col) )	TblCell( $m[1],"?in[]=slot&op[]==&st[]=".urlencode($m[1]));
			if( in_array("model",$col) )	TblCell( $m[2],"?in[]=model&op[]==&st[]=".urlencode($m[2]) );
			if( in_array("moddesc",$col) )	TblCell( $m[3] );
			if( in_array("modules.serial",$col) )TblCell( ($m[9]==40)?"($m[4] CPU)":InvCheck( $m[4],$m[2],$m[9],$m[16],$m[17] ),'','nw' );
			if( in_array("hw",$col) )	TblCell( $m[5],"Nodes-Status.php?st=$m[5]" );
			if( in_array("fw",$col) )	TblCell( ($m[9]==40)?"$m[6] MB":$m[6],"?in[]=fw&op[]==&st[]=".urlencode($m[6]) );
			if( in_array("sw",$col) )	TblCell( $m[7],"?in[]=sw&op[]==&st[]=".urlencode($m[7]),'nw',($m[9]==40)?'+'.OSImg($m[7]):'' );
			if( in_array("modidx",$col) )	TblCell($m[8],"?in[]=modidx&op[]==&st[]=$m[8]");
			if( in_array("status",$col) )	TblCell($m[10],"?in[]=status&op[]==&st[]=$m[10]");
			if( in_array("modloc",$col) )	TblCell( $m[11],"?in[]=modloc&op[]==&st[]=".urlencode($m[11]) );
			if( in_array("postNS",$col) ){
				echo "\t\t<td>\n";
				$ifp = '';
				if( $m[9] == 3 and preg_match('/^qfx/',$m[12]) ){
					#$ifp = '^[xg]e-'.preg_replace('/^FPC: QFX5100-\w+-\w+ @ (\d+)\/.*/','$1',$m[3] ).'/[0-9]+/[0-9]+$';
					$ifp = '^[xg]e-'.$m[8].'/[0-9]+/[0-9]+$';
					$opr = '~';
				}elseif( $m[9] == 3 and preg_match('/^N[56]K-/',$m[12]) ){
					$ifp = 'Et'.preg_replace('/^Fex-(\d+) .*/','$1',$m[1] ).'/%';
					$opr = 'LIKE';
				}
				if( $ifp ){
#echo "$opr $ifp";
					echo "\t\t\t<span style=\"border:1px solid #aaa;border-radius: 3px;float: left\">\n";
					$iqry = GenQuery('interfaces','g','ifstat','ifstat desc','',array('device','ifname'),array('=',$opr),array($m[0],$ifp),array('AND') );
					$ires = DbQuery( $iqry,$link);
					while( ($i = DbFetchRow($ires)) ){
						echo "\t\t\t<a href=\"Devices-Interfaces.php?in%5B%5D=device&op%5B%5D=%3D&st%5B%5D=$ud&co%5B%5D=AND&in%5B%5D=ifname&op%5B%5D=$opr&st%5B%5D=".urlencode($ifp)."&co%5B%5D=AND&in%5B%5D=ifstat&op%5B%5D=%3D&st%5B%5D=";
						if( $i[0] == 3 ){
							echo "$i[0]\" class=\"good flft\" style=\"padding:1px;width:".($i[1]*3+2)."px\" title=\"$stco[100]\">$i[1]</a>\n";
						}elseif( $i[0] == 1 ){
						echo "$i[0]\" class=\"warn flft\" style=\"padding:1px;width:".($i[1]*3+2)."px\" title=\"$stco[120]\">$i[1]</a>\n";
						}elseif( $i[0] == 0 ){
						echo "$i[0]\" class=\"alrm flft\" style=\"padding:1px;width:".($i[1]*3+2)."px\" title=\"$dsalbl\">$i[1]</a>\n";
						}
					}
					DbFreeResult($ires);
					echo "\t\t\t</span>\n";
				}
				echo "\t\t</td>\n";
			}
			echo "	</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row Modules".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}elseif($_SESSION['opt']){
	include_once ("inc/librep.php");
	ModDist($in[0],$op[0],$st[0],$_SESSION['lim'],'');
}
include_once ("inc/footer.php");
?>
