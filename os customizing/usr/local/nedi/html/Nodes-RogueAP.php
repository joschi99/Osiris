<?php
# Program: Nodes-RogueAP.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libnod.php");

error_reporting( ~'E_Notice');										# Don't display notices with debug, due to optimized creation of (large) hashes

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$ord = isset($_GET['ord']) ? $_GET['ord'] : '';
$opt = isset($_GET['opt']) ? 'checked' : '';

$cols = array(	"device"=>"Device $namlbl",
		"devip"=>"IP $adrlbl",
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
		"name"=>"Node $namlbl",
		"nodip"=>"Node IP",
		"oui"=>$venlbl,
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
		"nodtype"=>"Node $typlbl",
		"nodos"=>"Node OS",
		"osupdate"=>"OS $updlbl"
		);
?>
<h1>RogueAP <?= $lstlbl ?></h1>

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
	<img src="img/16/abc.png" title="<?= $srtlbl ?>">
	<select name="ord" size="1">
		<option value="firstseen" <?= ($ord == "firstseen")?" selected":"" ?> ><?= $fislbl ?>

		<option value="lastseen" <?= ($ord == "lastseen")?" selected":"" ?> ><?= $laslbl ?>

		<option value="mac" <?= ($ord == "mac")?" selected":"" ?> >MAC address
		<option value="device" <?= ($ord == "device")?" selected":"" ?> >Device

	</select>
</td>
<td class="ctr">
	<img src="img/16/nods.png" title="<?= $poplbl ?> > 1"><input type="checkbox" name="opt" <?= $opt ?> >
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
$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('wlan');
$res	= DbQuery($query,$link);
if($res){
	$nwmac = 0;
	while( ($w = DbFetchRow($res)) ){
		$nwmac++;
		if( strlen($w[0]) == 8 ){
			$wlap[$w[0]] = 1;
		}else{
			$wlvn[$w[0]] = 1;
		}
	}
	DbFreeResult($res);
}else{
	print DbError($link);
	die;
}

?>

<h2>AP <?= $lstlbl ?></h2>

<?php Condition($in,$op,$st,$co); ?>

<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/node.png"><br>
			<?= $namlbl ?> - <?= $adrlbl ?>
		</th>
		<th colspan="3">
			<img src="img/16/dev.png"><br>
			Device - IF - <?= $poplbl ?>
		</th>
		<th colspan="2">
			<img src="img/16/clock.png"><br>
			<?= $fislbl ?> - <?= $laslbl ?>
		</th>
	</tr>
<?php

$query	= GenQuery('nodes','s','mac,oui,firstseen,lastseen,device,ifname,vlanid,nodip,aname',$ord,'',array($in[0]),array($op[0]),array($st[0]),array(),'LEFT JOIN devices USING (device) LEFT JOIN nodarp USING (mac) LEFT JOIN dns USING (nodip)');
$res	= DbQuery($query,$link);

$ap	= array();
while( ($n = DbFetchRow($res)) ){
	$nmc["$n[4];;$n[5]"]++;
	if(array_key_exists(substr($n[0],0,8), $wlap) or array_key_exists(substr($n[0],0,6), $wlvn) ){
		$ap[$n[0]]['oui'] = $n[1];
		$ap[$n[0]]['fs'] = $n[2];
		$ap[$n[0]]['ls'] = $n[3];
		$ap[$n[0]]['dv'] = $n[4];
		$ap[$n[0]]['if'] = $n[5];
		$ap[$n[0]]['vl'] = $n[6];
		$ap[$n[0]]['ip'] = long2ip($n[7]);
		$ap[$n[0]]['na'] = preg_replace("/^(.*?)\.(.*)/","$1", $n[8]);
	}
}

$row = 0;
foreach ( array_keys($ap) as $m ){
	if($nmc[$ap[$m]['dv'].';;'.$ap[$m]['if']] > 1 or !$opt){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ud	= urlencode($ap[$m]['dv']);
		$ui	= urlencode($ap[$m]['if']);
		list($fs,$ls)	= Agecol($ap[$m]['fs'],$ap[$m]['ls'],$row % 2);
		TblRow($bg);
		TblCell('','',"$bi ctr xs","+<a href=\"Nodes-Status.php?st=$m&vid=$ap[$m]['vl']\"><img src=\"img/oui/".VendorIcon($ap[$m].';'.$ap[$m]['oui']).".png\" title=\"$m (".$ap[$m]['oui'].")\"></a>");
		TblCell($ap[$m]['na']);
		TblCell($ap[$m]['ip']);
		TblCell($ap[$m]['dv'],"?in[]=device&op[]==&st[]=$ud&ord=ifname",'nw',"<a href=\"Devices-Status.php?dev=$ud&pop=on\"><img src=\"img/16/sys.png\"></a>");
		TblCell($ap[$m]['if']." Vl".$ap[$m]['vl']);
		TblCell($nmc[$ap[$m]['dv'].';;'.$ap[$m]['if']],"Nodes-List.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui",'','+'.Bar($nmc[$ap[$m]['dv'].';;'.$ap[$m]['if']],5) );
		TblCell( date($_SESSION['timf'],$ap[$m]['fs']),"?in[]=firstseen&op[]==&st[]=".$ap[$m]['fs'],'nw','',"background-color:#$fs" );
		TblCell( date($_SESSION['timf'],$ap[$m]['ls']),"?in[]=lastseen&op[]==&st[]=".$ap[$m]['ls'],'nw','',"background-color:#$ls" );
		echo "\t</tr>\n";
	}
}
TblFoot("bgsub", 8, "$row APs, $nwmac MACs".(($ord)?", $srtlbl: $ord":"") );

include_once ("inc/footer.php");
?>
