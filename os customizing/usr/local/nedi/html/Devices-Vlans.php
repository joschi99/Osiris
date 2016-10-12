<?php
# Program: Devices-Vlans.php
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
	if($_SESSION['opt']) $_SESSION['vlcol'] = $col;
}elseif( isset($_SESSION['vlcol']) ){
	$col = $_SESSION['vlcol'];
}else{
	$col = array('device','vlanid','vlanname');
}

$cols = array(	"vlanid"=>$idxlbl,
		"vlanname"=>"Vlan $namlbl",
		"device"=>"Device $namlbl",
		"vendor"=>$venlbl,
		"type"=>"Device $typlbl",
		"devgroup"=>$grplbl,
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl",
		"poNS"=>$poplbl
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
?>
<h1 onclick="document.list.style.display = (document.list.style.display == 'none')?'':'none';">Vlan <?= $lstlbl ?></h1>

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
	<select multiple name="col[]" size="6" title="<?= $collbl ?>">
<?php
foreach ($cols as $k => $v){
       echo "		<option value=\"$k\"".((in_array($k,$col))?" selected":"").">$v\n";
}
?>
	</select>
</td>
<td class="ctr">
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
	$query	= GenQuery('vlans','s','vlanid,vlanname,device,type,firstdis,lastdis,devgroup,location,contact,vendor',$ord,$lim,$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($v = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = urlencode($v[2]);
			list($fc,$lc) = Agecol($v[4],$v[5],$row % 2);
			TblRow($bg);
			if(in_array("vlanid",$col))	TblCell($v[0],"?in[]=vlanid&op[]==&st[]=".urlencode($v[0]),"$bi rgt b s");
			if(in_array("vlanname",$col))	TblCell($v[1],"?in[]=vlanname&op[]==&st[]=".urlencode($v[1]),'b');
			if(in_array("device",$col))	TblCell($v[2],"?in[]=device&op[]==&st[]=$ud&ord=vlanid",'',"<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/16/sys.png\"></a>");
			if(in_array("vendor",$col))	TblCell($v[8],"?in[]=vendor&op[]==&st[]=$v[8]" );
			if(in_array("type",$col))	TblCell($v[3],"?in[]=type&op[]==&st[]=".urlencode($v[3]),'nw',"+<a href=\"http://www.google.com/search?q=".urlencode("$v[8] $v[3]")."&btnI=1\" target=\"window\"><img src=\"img/oui/".VendorIcon($v[8]).".png\" title=\"$v[8]\"></a> ");
			if( in_array("devgroup",$col) )	TblCell($v[6],"?in[]=devgroup&op[]==&st[]=".urlencode($v[6]) );
			if(in_array("location",$col))	TblCell($v[7],"?in[]=location&op[]==&st[]=".urlencode($v[7]));
			if(in_array("contact",$col))	TblCell($v[8],"?in[]=contact&op[]==&st[]=".urlencode($v[8]));
			if( in_array("firstdis",$col) )	TblCell( date($_SESSION['timf'],$v[4]),"?in[]=firstdis&op[]==&st[]=$v[4]",'nw','',"background-color:#$fc" );
			if( in_array("lastdis",$col) )	TblCell( date($_SESSION['timf'],$v[5]),"?in[]=lastdis&op[]==&st[]=$v[5]",'nw','',"background-color:#$lc" );
			if(in_array("poNS",$col)){
				$pop = NodPop( array('device','vlanid'),array('=','='),array($v[2],$v[0]),array('AND') );
				if($pop){
					TblCell(" $pop","Nodes-List.php?in[]=device&in[]=vlanid&op[]==&op[]==&st[]=$ud&st[]=$v[0]&co[]=AND",'','+'.Bar($pop,100,'sbar'),'td-img');
				}else{
					TblCell();
				}
			}
			echo "	</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row Vlans".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}elseif($_SESSION['opt']){
	include_once ("inc/librep.php");
	VlanEmpty($in[0],$op[0],$st[0],1000,'');
}
include_once ("inc/footer.php");
?>
