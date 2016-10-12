<?php
# Program: Monitoring-Incidents.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$id = isset($_GET['id']) ? $_GET['id'] : "";
$dli = isset($_GET['dli']) ? $_GET['dli'] : "";
$ugr = isset($_GET['ugr']) ? $_GET['ugr'] : "";
$ucm = isset($_GET['ucm']) ? $_GET['ucm'] : "";
$cmt = isset($_GET['cmt']) ? $_GET['cmt'] : "";
$grp = isset($_GET['grp']) ? $_GET['grp'] : "";
$end = isset($_GET['end']) ? 'checked':'';
$ilm = isset($_GET['ilm']) ? preg_replace('/\D+/','',$_GET['ilm']) : 25;
$off = (isset($_GET['off']) and !isset($_GET['sho']))? $_GET['off'] : 0;
$nof = $off;

if( isset($_GET['p']) ){
	$nof = abs($off - $ilm);
}elseif( isset($_GET['n']) ){
	$nof = $off + $ilm;
}

echo "<h1>Monitoring Incidents</h1>\n";

$dlim = ($ilm)?"$ilm OFFSET $nof":'';
$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if($dli){
	$query	= GenQuery('incidents','d','','','',array('id'),array('='),array($dli) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>Incident $dli $dellbl OK</h5>";}
}elseif($ugr){
	$query	= GenQuery('incidents','u',"id = '$ugr'",'','',array('usrname','time','grp'),array(),array($_GET['usr'],$_GET['tme'],$grp) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5> Incident $ugr $updlbl OK</h5>";}
	$grp = "";
}elseif($ucm){
	$query	= GenQuery('incidents','u',"id = '$ucm'",'','',array('usrname','comment'),array(),array($_GET['usr'],$cmt) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5> Incident $ucm $updlbl OK</h5>";}
}
?>

<?php  if( !isset($_GET['print']) ) { ?>
<form method="get" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
	<select name="grp">
		<option value=""><?= $fltlbl ?> <?= $clalbl ?> >
<?php
foreach (array_keys($igrp) as $ig){
	echo "\t\t<option value=\"$ig\" ";
	if($ig == $grp){echo "selected ";}
	echo (strpos($ig,'0')?"style=\"color:blue\">$igrp[$ig]\n":">- $igrp[$ig]\n");
}
?>
	</select>
	<img src="img/16/bbrt.png" title="<?= $fltlbl ?> <?= $stco['100'] ?> (<?= $nonlbl ?> <?= $endlbl ?>)">
	<input type="checkbox" name="end" <?= $end ?> onchange="this.form.submit();">
</td>
<td>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select name="ilm">
<?php selectbox("limit",$ilm) ?>
	</select>
</td>
<td class="ctr s nw">
	<input type="submit" class="button" name="sho" value="<?= $sholbl ?>"><br>
	<input type="hidden" name="off" value="<?= $nof ?>">
	<input type="submit" class="button" name="p" value=" < ">
	<input type="submit" class="button" name="n" value=" > ">
</td>
</tr>
</table>
</form>
<p>

<?php } ?>

<h2><?= ($grp)?$igrp[$grp]:"" ?> <?= $inclbl ?> <?= $lstlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/eyes.png"><br>
			<?= $inclbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>

		</th>
		<th>
			<img src="img/16/bblf.png"><br>
			<?= $sttlbl ?>

		</th>
		<th>
			<img src="img/16/bbrt.png"><br>
			<?= $endlbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/user.png"><br>
			<?= $usrlbl ?>

		</th>
		<th colspan="2">
			<img src="img/16/find.png"><br>
			<?= $inflbl ?>

		</th>
	</tr>
<?php
$flte = ($end)?'AND':'';
if(strpos($grp,'0') ){
	$query	= GenQuery('incidents','s','*','id desc',$dlim,array('grp','endinc'),array('~','='),array("^".substr($grp,0,1).".",0),array($flte));
}elseif($grp){
	$query	= GenQuery('incidents','s','*','id desc',$dlim,array('grp','endinc'),array('=','='),array($grp,0),array($flte));
}elseif($id){
	$query	= GenQuery('incidents','s','*','','',array('id'),array('='),array($id));
}elseif($flte){
	$query	= GenQuery('incidents','s','*','id desc',$dlim,array('endinc'),array('='),array(0));
}else{
	$query	= GenQuery('incidents','s','*','id desc',$dlim);
}
$res	= DbQuery($query,$link);
if($res){
	$nin = 0;
	$row = 0;
	while( ($i = DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$fs = date("d.M H:i",$i[4]);
		if($i[5]){
			$dur = intval(($i[5] - $i[4]) / 3600);
			$ls  = date("d.M H:i",$i[5]); # . " ($dur h)";
		}else{
			$ls  = "-";
		}
		if($i[7]){$at = date("d.M H:i",$i[7]);}else{$at = "-";}
		$ud = urlencode($i[2]);
		list($fc,$lc) = Agecol($i[4],$i[5],$row % 2);
		TblRow($bg);
		echo "\t\t<td class=\"$bi ctr xs b\">\n\t\t\t<a href=\"?id=$i[0]\">$i[0]</a>\n\t\t</td>\n";
		echo "\t\t<td class=\"".$mbak[$i[1]]." ctr xs\">\n\t\t\t<img src=\"img/16/" . $mico[$i[1]] . ".png\" title=\"" . $mlvl[$i[1]] . "\">\n\t</td>\n";
		echo "\t\t<td>\n\t\t\t<a href=\"Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=$ud\">$i[2]</a>\n\t\t</td>\n";
		echo "\t\t<td>\n\t\t\t$i[3] deps\n\t\t</td>\n";
		echo "\t\t<td style=\"background-color:#$fc\">\n\t\t\t$fs\n\t\t</td>\n";
		echo "\t\t<td style=\"background-color:#$lc\">\n\t\t\t$ls\n\t\t</td>\n";
		echo "\t\t<td>\n\t\t\t$i[6]\n\t\t</td>\n\t\t<td>\n\t\t\t$at\n\t\t</td>\n\t\t<td>\n";

		if( isset($_GET['print']) ){
			echo "\t\t\t<img src=\"img/16/".IncImg($i[8]).".png\">".$igrp[$i[8]]."\n\t\t</td>\n\t\t<td>\n\t\t\t$i[9]";
		}else{
?>
		<form method="get" action="<?= $self ?>.php">
			<img src="img/16/<?=IncImg($i[8]) ?>.png">
			<input type="hidden" name="ugr" value="<?= $i[0] ?>">
			<input type="hidden" name="usr" value="<?= ($i[6])?$i[6]:$_SESSION['user'] ?>">
			<input type="hidden" name="tme" value="<?= ($i[7])?$i[7]:time() ?>">
			<input type="hidden" name="lim" value="<?= $ilm ?>">
			<input type="hidden" name="off" value="<?= $nof ?>">
			<select size="1" name="grp" onchange="this.form.submit();" title="<?= $sellbl ?> <?= $clalbl ?>">
<?php
		foreach (array_keys($igrp) as $ig){
			echo "\t\t\t\t<option value=\"$ig\" ".(strpos($ig,'0')?"style=\"color: blue\" ":"");
			if($ig == $i[8]){echo "selected ";}
			echo (strpos($ig,'0')?"style=\"color: blue\">$igrp[$ig]\n":">- $igrp[$ig]\n");
		}
?>
			</select>
		</form>
		</td>
		<td>
		<form method="get" action="<?= $self ?>.php">
			<input type="hidden" name="usr" value="<?= ($i[6])?$i[6]:$_SESSION['user'] ?>">
			<input type="hidden" name="ucm" value="<?= $i[0] ?>">
			<input type="hidden" name="lim" value="<?= $ilm ?>">
			<input type="hidden" name="off" value="<?= $nof ?>">
			<input type="text" name="cmt" size="30" value="<?= $i[9] ?>" onchange="this.form.submit();">
			<a href="<?= $self ?>.php?dli=<?= $i[0] ?>"><img src="img/16/bcnl.png" onclick="return confirm('<?= $dellbl ?> Incident <?= $i[0] ?>?');" title="<?= $dellbl ?> Incident"></a>
		</form>
		</td>
	</tr>
<?php
		}
		$nin++;
		if($nin == $lim){break;}
	}
	TblFoot("bgsub", 10, "$row $vallbl" );
	DbFreeResult($res);
}else{
	print DbError($link);
}

include_once ("inc/footer.php");
?>
