<?php
# Program: Assets-List.php
# Programmer: Remo Rickli

$exportxls = 1;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

if( is_numeric($_GET['as']) ) $invup['state'] = $_GET['as'];
if( is_numeric($_GET['ms']) ) $invup['maintstatus'] = $_GET['ms'];

$ord = isset($_GET['ord']) ? $_GET['ord'] : 'assetupdate desc';
if($_SESSION['opt'] and !$ord and $in[0]) $ord = $in[0];

$lim = isset($_GET['lim']) ? preg_replace('/\D+/','',$_GET['lim']) : $listlim;

if( isset($_GET['col']) ){
	$col = $_GET['col'];
	if($_SESSION['opt']) $_SESSION['ascol'] = $col;
}elseif( isset($_SESSION['ascol']) ){
	$col = $_SESSION['ascol'];
}else{
	$col = array('state','serial','assettype','assetnumber','assetupdate','maintstatus','endmaint','endsupport');
}

$cols = array(
		'state'=>$stalbl,
		'serial'=>$serlbl,
		'assetclass'=>$clalbl,
		'assettype'=>$typlbl,
		'assetnumber'=>"$invlbl $numlbl",
		'assetlocation'=>$loclbl,
		'assetcontact'=>$conlbl,
		'assetupdate'=>$updlbl,
		'pursource'=>$srclbl,
		'purcost'=>"$purlbl $coslbl",
		'purnumber'=>"$purlbl $numlbl",
		'purtime'=>"$purlbl $timlbl",
		'maintpartner'=>$igrp['17'],
		'maintsla'=>"$igrp[31] $levlbl",
		'maintdesc'=>"$igrp[31] $detlbl",
		'maintcost'=>"$igrp[31] $coslbl",
		'maintstatus'=>"$igrp[31] $stalbl",
		'startmaint'=>"$igrp[31] $sttlbl",
		'endmaint'=>"$igrp[31] $endlbl",
		'endwarranty'=>"$wtylbl $endlbl",
		'endsupport'=>"$srvlbl $endlbl",
		'endlife'=>'EoL',
		'comment'=>$cmtlbl,
		'usrname'=>$usrlbl
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
?>
<h1 onclick="document.list.style.display = (document.list.style.display == 'none')?'':'none';">Assets <?= $lstlbl ?></h1>

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
	<a href="?in[]=state&op[]==&st[]=10"><img src="img/16/star.png" title="<?= $stco['10'] ?>"></a>
	<a href="?in[]=maintstatus&op[]==&st[]=10"><img src="img/16/brld.png" title="<?= $igrp['31'] ?> <?= $stalbl ?> = <?= $mast['10'] ?>"></a><br>
	<a href="?in[]=state&op[]=~&st[]=&lim=25&ord=assetupdate+desc"><img src="img/16/user.png" title="<?= $updlbl ?> <?= $laslbl ?>"></a>
	<a href="?in[]=state&op[]=~&st[]=&col[]=state&col[]=serial&col[]=assettype&col[]=assetnumber&col[]=assetupdate&col[]=endwarranty&col[]=endsupport&col[]=endlife&col[]=comment&lim=25&as=&ms=&ord=endlife+desc"><img src="img/16/bdis.png" title="EoL"></a>
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
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="lim">
<?php selectbox("limit",$lim) ?>
	</select><br>
	<img src="img/16/idea.png" title="<?= $stalbl ?>">
	<select size="1" name="as">
		<option value=""><?= $nonlbl ?> <?= $chglbl ?>
<?php
foreach (array_keys($stco) as $c){
	echo "		<option value=\"$c\">$stco[$c]\n";
}
?>
	</select><br>
	<img src="img/16/brld.png" title="<?= $igrp['31'] ?> <?= $stalbl ?>">
	<select size="1" name="ms">
		<option value=""><?= $nonlbl ?> <?= $chglbl ?>
		<option value="0">-
		<option value="10"><?= $mast[10] ?>
		<option value="20"><?= $mast[20] ?>
	</select><br>

</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>"><br>
<?php if($ismgr){ ?>
	<input type="submit" class="button" name="upd" value="<?= $updlbl ?>"><br>
	<input type="submit" class="button" name="del" value="<?= $dellbl ?>" onclick="return confirm('<?= $invlbl ?> <?= $dellbl ?>, <?= $cfmmsg ?>')" >
<?php } ?>
</td>
</tr></table>
</form>
<p>

<?php
}
if( $ismgr and isset($_GET['del']) ){
	$query	= GenQuery('inventory','d','','','',$in,$op,$st,$co);
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dellbl OK</h5>";}
}

if( count($in) ){
	$query	= GenQuery('inventory','s','*',$ord,$lim,$in,$op,$st,$co);

	Condition($in,$op,$st,$co);

	TblHead("bgsub",1);

	$res	= DbQuery($query,$link);
	if($res){
		$row   = 0;
		$most = '';
		while( ($as = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			list($mcl,$img) = ModClass($as[2]);
			list($auc,$auc)	= Agecol($as[7],$as[7],$row % 2);
			if( $ismgr and count($invup) and isset($_GET['upd']) ){
				$invup['usrname'] = $_SESSION['user'];
				$invup['assetupdate'] = time();
				$query = GenQuery('inventory','u',"serial = '".DbEscapeString($as[1])."'",'','',array_keys($invup),array(),array_values($invup) );
				$updst = DbQuery($query,$link)?" <img src=\"img/16/bchk.png\" title=\"$igrp[31] $stalbl $updlbl OK\">":"<img src=\"img/16/bcnl.png\" title=\"".DbError($link)."\">";
			}
			TblRow($bg);
			if( in_array('state',$col) )	TblCell( $updst,'',"ctr $bi xs","+<a href=\"?in[]=state&op[]==&st[]=$as[0]\">".Staimg($as[0])."</a>" );
			if(in_array('serial',$col))	TblCell( $as[1],"Assets-Management.php?chg=".urlencode($as[1])."&lst=$lst&val=$uv" );
			if(in_array('assetclass',$col))	TblCell( $mcl,"?in[]=assetclass&op[]==&st[]=$as[2]",'nw' );
			if(in_array('assettype',$col))	TblCell( $as[3],"?in[]=assettype&op[]==&st[]=".urlencode($as[3]),'nw',"+<a href=\"".(($as[2] == 3)?'Devices-List.php?in[]=serial':'Devices-Modules.php?in[]=modules.serial')."&op[]==&st[]=".urlencode($as[1])."\"><img src=\"img/16/$img.png\" title=\"$mcl, $as[22]\"></a>" );
			if(in_array('assetnumber',$col))TblCell( $as[4] );
			if(in_array('assetlocation',$col))TblCell( $as[5],"?in[]=assetlocation&op[]==&st[]=".urlencode($as[5]) );
			if(in_array('assetcontact',$col))TblCell( $as[6],"?in[]=assetcontact&op[]==&st[]=".urlencode($as[6]) );
			if(in_array('assetupdate',$col))TblCell( Ftime($as[7]),"?in[]=assetupdate&op[]==&st[]=$as[7]",'nw','',"background-color:#$auc" );
			if(in_array('pursource',$col))	TblCell( $as[8],"?in[]=pursource&op[]==&st[]=".urlencode($as[8]) );
			if(in_array('purcost',$col))	TblCell( $as[9] );
			if(in_array('purnumber',$col))	TblCell( $as[10] );
			if(in_array('purtime',$col))	TblCell( Ftime($as[11],'datf') );
			if(in_array('maintpartner',$col))TblCell( $as[12],"?in[]=maintpartner&op[]==&st[]=".urlencode($as[12]) );
			if(in_array('maintsla',$col))	TblCell( $as[13],"?in[]=maintsla&op[]==&st[]=".urlencode($as[13]) );
			if(in_array('maintdesc',$col))	TblCell( $as[14] );
			if(in_array('maintcost',$col))	TblCell( $as[15],"?in[]=maintcost&op[]==&st[]=".urlencode($as[15]) );
			if(in_array('maintstatus',$col))TblCell( $mast[$as[16]],"?in[]=maintstatus&op[]==&st[]=$as[16]" );
			if(in_array('startmaint',$col))	TblCell( Ftime($as[17],'datf'),"?in[]=startmaint&op[]==&st[]=$as[17]",'nw');
			if(in_array('endmaint',$col))	TblCell( Ftime($as[18],'datf'),"?in[]=endmaint&op[]==&st[]=$as[18]",SupportBg($as[18]).' nw');
			if(in_array('endwarranty',$col))TblCell( Ftime($as[19],'datf'),"?in[]=endwarranty&op[]==&st[]=$as[19]",SupportBg($as[19]).' nw' );
			if(in_array('endsupport',$col))	TblCell( Ftime($as[20],'datf'),"?in[]=endsupport&op[]==&st[]=$as[20]",SupportBg($as[20]).' nw' );
			if(in_array('endlife',$col))	TblCell( Ftime($as[21],'datf'),"?in[]=endlife&op[]==&st[]=$as[21]",SupportBg($as[21]).' nw' );
			if(in_array('comment',$col))	TblCell( $as[22] );
			if(in_array('usrname',$col))	TblCell( $as[23],"?in[]=usrname&op[]==&st[]=".urlencode($as[23]) );
			echo "\t</tr>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
	TblFoot("bgsub", count($col), "$row $vallbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}
include_once ("inc/footer.php");
?>
