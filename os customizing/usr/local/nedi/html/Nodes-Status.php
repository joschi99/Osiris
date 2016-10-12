<?php
# Program: Nodes-Status.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libnod.php");
include_once ("inc/libmon.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : 'mac';
$st  = isset($_GET['st']) ? $_GET['st'] : '';
$wol = isset($_GET['wol']) ? $_GET['wol'] : '';
$wip = isset($_GET['wip']) ? $_GET['wip'] : '';
$del = isset($_GET['del']) ? $_GET['del'] : '';
$trk = isset($_GET['trk']) ? $_GET['trk'] : '';
$dip = isset($_GET['dip']) ? $_GET['dip'] : '';
$mon = isset($_GET['mon']) ? $_GET['mon'] : '';
$shg = isset($_GET['shg']) ? 'checked' : '';
?>
<script src="inc/Chart.min.js"></script>

<h1>Node <?= $stalbl ?></h1>

<?php  if( !isset($_GET['print']) ) { ?>

<form method="get" action="<?= $self ?>.php">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr">
	<h3>
		<select name="in">
			<option value="mac">MAC <?= $adrlbl ?>

			<option value="ip" <?= ($in == 'ip')?" selected":"" ?>>IP <?= $adrlbl ?>

			<option value="dns" <?= ($in == 'dns')?" selected":"" ?>>DNS <?= $namlbl ?>

		</select>
		<input type="text" name="st" value="<?= $st ?>" class="m">
	</h3>
</td>
<td class="ctr">
<?php if($rrdcmd and $_SESSION['gsiz']){ ?>
	<img src="img/16/grph.png" title="<?= $porlbl ?> <?= $gralbl ?>">
	<input type="checkbox" name="shg" <?= $shg ?>>
<?php } ?>
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
if ($trk){
	$st = $trk;
	if($ismgr){
		$query	= GenQuery('nodes','u',"mac = '$trk'",'','',array('ifchanges'),array(),array('0') );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$trk ifchanges $updlbl OK</h5>";}
		$query	= GenQuery('iptrack','d','','','',array('mac'),array('='),array($trk) );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$trk iptrack $dellbl OK</h5>";}
		$query	= GenQuery('iftrack','d','','','',array('mac'),array('='),array($trk) );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$trk iftrack $dellbl OK</h5>";}
	}else{
		echo $nokmsg;
	}
}elseif ($dip){
	$st = $dip;
	if($ismgr){
		$query	= GenQuery('nodarp','d','','','',array('mac'),array('='),array($dip) );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dip ARP $dellbl OK</h5>";}
		$query	= GenQuery('nodnd','d','','','',array('mac'),array('='),array($dip) );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dip ND $dellbl OK</h5>";}
	}else{
		echo $nokmsg;
	}
}

if ($st){
	$cols = 'nodes.*,type,location,contact,snmpversion,icon,vendor,linktype,iftype,ifdesc,alias,ifstat,speed,duplex,pvid,lastchg,inoct,outoct,inerr,outerr,indis,outdis,inbrc,dinoct,doutoct,dinerr,douterr,dindis,doutdis,dinbrc';
	if( $in == 'mac' ){
		$query	= GenQuery('nodes','s',$cols,'','',array('mac'),array('='),array($st),array(),'LEFT JOIN devices USING (device) LEFT JOIN interfaces USING (device,ifname)');
	}elseif( $in == 'ip' ){
		$query	= GenQuery('nodes','s',$cols,'','',array('nodip'),array('='),array($st),array(),'LEFT JOIN devices USING (device) LEFT JOIN interfaces USING (device,ifname) LEFT JOIN nodarp USING (mac)');
	}elseif( $in == 'dns' ){
		$query	= GenQuery('nodes','s',$cols,'','',array('aname'),array('LIKE'),array("$st%"),array(),'LEFT JOIN devices USING (device) LEFT JOIN interfaces USING (device,ifname) LEFT JOIN nodarp USING (mac) LEFT JOIN dns USING (nodip)');
	}
	$nres	= DbQuery($query,$link);
	while( ($n = DbFetchRow($nres)) ){
		$dur	= intval(($n[3]-$n[2])/86400);
		$wasup	= ($n[3] > time() - $rrdstep*2)?1:0;
		$ud 	= urlencode($n[4]);
		$ui 	= urlencode($n[5]);
		$loc	= explode($locsep, $n[13]);
		$lit	= '';
		list($firstcol,$lastcol)  = Agecol($n[2],$n[3],0);
		list($ifchgcol,$ifchgcol) = Agecol($n[8],$n[8],1);
		list($ifimg,$iftyp)	  = Iftype($n[19]);
		list($ifbg,$ifst)	  = Ifdbstat($n[22]);
		list($lnkhgt,$lnkcol)	  = LinkStyle( $n[23],0 );

		$vl[2] = "-";
		if($n[6] and preg_match('/[A-L]/',$n[7]) ){
			$query	= GenQuery('vlans','s','*','','',array('device','vlanid'),array('=','='),array($n[4],$n[6]),array('AND') );
			$res	= DbQuery($query,$link);
			if (DbNumRows($res) == 1) {
				$vl = DbFetchRow($res);
			}
			DbFreeResult($res);
		}
?>

<table class="full"><tr>
<td class="helper xl">
	<table class="content">
		<tr class="bgsub">
			<td colspan="2">
				<h2>Node
				<div  class="frgt">
<?php
		if($ismgr){
			echo "\t\t\t\t<a href=\"?trk=$n[0]\"><img src=\"img/16/walk.png\" onclick=\"return confirm('$dellbl IF/IP $chglbl  $n[0]?')\" title=\"$dellbl IF/IP $chglbl\"></a>";
			echo "\t\t\t\t<a href=\"?dip=$n[0]\"><img src=\"img/16/glob.png\" onclick=\"return confirm('$dellbl IP $adrlbl  $n[0]?')\" title=\"$dellbl IP $adrlbl\"></a>";
			echo "\t\t\t\t<a href=\"?del=$n[0]\"><img src=\"img/16/bcnl.png\" onclick=\"return confirm('$dellbl $n[0] ?')\" title=\"$dellbl Node!\"></a>";
		}
?>
				</div>
				</h2>
			</td>
		</tr>
		<tr class="txta">
			<td class="imga s b">
				MAC <?= $adrlbl ?>
			</td>
			<td class="drd">
				<?= rtrim(chunk_split($n[0],2,"-"),"-") ?><br>
				<?= rtrim(chunk_split($n[0],2,":"),":") ?><br>
				<?= rtrim(chunk_split($n[0],4,"."),".") ?>&nbsp;
				<a href="System-Policy.php?cl=mac&tg=<?= $n[0] ?>&al=2&io=Marked+on+<?= $ud ?>,<?= $ui ?>" title="<?= $dsclbl ?> <?= $mlvl[200] ?>" class="frgt"><img src="img/16/hat3.png"></a>
				<a href="Monitoring-Events.php?in[]=info&op[]=~&st[]=<?= $n[0] ?>" title="MAC ~ Monitoring-Events" class="frgt"><img src="img/16/bell.png"></a>
				<a href="Nodes-List.php?in[]=mac&op[]==&st[]=<?= $n[0] ?>" title="MAC -> Nodes-List" class="frgt"><img src="img/16/nods.png"></a>
			</td>
		</tr>
		<tr class="txtb">
			<td class="imgb s b">
				<?= $venlbl ?>
			</td>
			<td>
				<a href="http://www.google.com/search?q=<?= urlencode($n[1]) ?>&btnI=1" target="window"><img src="img/oui/<?= VendorIcon($n[1]) ?>.png" title="Google <?= $venlbl ?>"></a>
				<a href="Nodes-List.php?in[]=oui&op[]==&st[]=<?= urlencode($n[1]) ?>"><?= $n[1] ?></a>
				<?= ($n[1] == 'VMware')?"<a href=\"Devices-Modules.php?in[]=hw&op[]==&st[]=$n[0]\"><img src=\"img/16/node.png\" title=\"VM, Devices-Modules\" class=\"frgt\"></a>":""; ?>
			</td>
		</tr>
		<tr class="txta">
			<td class="imga s b">
				<?= $dsclbl ?>
			</td>
			<td class="ctr">
				<span  class="genpad" style="background-color:#<?= $firstcol ?>" title="<?= $fislbl ?>"><a href="Nodes-List.php?in[]=firstseen&op[]==&st[]=<?= $n[2] ?>"><?= date($_SESSION['timf'],$n[2]) ?></a></span>
				<?= Bar($dur,'lvl10','sbar',"$dur $tim[d]") ?>
				<span  class="genpad" style="background-color:#<?= $lastcol ?>" title="<?= $laslbl ?>"><a href="Nodes-List.php?in[]=lastseen&op[]==&st[]=<?= $n[3] ?>"><?= date($_SESSION['timf'],$n[3]) ?></a></span>
				<?= ($wasup)?"<img src=\"img/16/flas.png\" title=\"$stco[100]\" class=\"frgt\">":"<img src=\"img/16/bcls.png\" title=\"$outlbl\" class=\"frgt\">"; ?>
			</td>
		</tr>
		<tr class="txtb">
			<td class="imgb s b">
				<?= $usrlbl ?>
			</td>
			<td>
				<?= $n[10] ?>
			</td>
		</tr>
	</table>
	<p>
	<table class="content">
		<tr>
			<th class="bgsub" colspan="4">
				IP <?= $adrlbl ?>
			</th>
		</tr>
<?php
		$irow   = 1;
		$query	= GenQuery('nodarp','s','nodarp.*,aname,dnsupdate,test,status','','',array('mac'),array('='),array($n[0]),array(),'LEFT JOIN dns USING (nodip) LEFT JOIN monitoring on (nodip = monip)' );
		$res	= DbQuery($query,$link);
		while( ($arp = DbFetchRow($res)) ){
			if ($irow % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$ip                 = long2ip($arp[1]);
			list($statbg,$stat) = StatusBg(1,($arp[13])?1:0,$arp[14]);
			list($iuc,$iuc)     = Agecol($arp[3],$arp[3],1);
			list($ntimg,$ntit)  = Nettype($ip);
			TblRow($bg);
?>
			<td class="<?= $statbg ?> ctr xs">
				<img src="img/<?= $ntimg ?>" title="<?= $irow ?>) <?= $ntit ?>" onclick="togvis('ip<?= $irow ?>');">
				<div id="ip<?= $irow ?>" class="genpad lft" style="position:absolute;display:none" onmouseout="togvis('ip<?= $irow ?>');">
				<a href="Monitoring-Events.php?in[]=info&op[]=~&st[]=<?= $ip ?>"><img src="img/16/bell.png" title="IP ~ Monitoring-Events"></a><?= $msglbl ?><br>
				<?= ($stat)?"<a href=\"Monitoring-Setup.php?in[]=monip&op[]=%3D&st[]=$ip\">".TestImg($arp[13])."</a>Monitoring-Setup<br>":"" ?>
				<a href="Nodes-Toolbox.php?Dest=<?= $ip ?>"><img src="img/16/tool.png"></a>Nodes-Toolbox<br>
				<a href="?wol=<?= $n[0] ?>&wip=<?= $arp[1] ?>"><img src="img/16/exit.png"></a>WoL <?= $srvlbl ?><br>
<?php if($ismgr) { ?>
				<form method="post" action="System-NeDi.php">
					<input type="hidden" name="mde" value="d">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="hidden" name="vrb" value="on">
					<input type="hidden" name="ins" value="on">
					<input type="hidden" name="skp" value="AFGgsjmvpadobewit">
					<input type="image" class="imgbtn" style="padding:0 2px" src="img/16/dril.png" value="Submit">
					Install
				</form>
<?php 	if($ismgr) { ?>
				<form method="post" action="System-NeDi.php">
					<input type="hidden" name="mde" value="d">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="hidden" name="vrb" value="on">
					<input type="hidden" name="skp" value="G">
					<input type="image" class="imgbtn" style="padding:0 2px" src="img/16/radr.png" value="Submit">
					<?= (($verb1)?"$dsclbl $tim[n]":"$tim[n] $dsclbl") ?>
				</form>

				<form method="post" action="System-NeDi.php">
					<input type="hidden" name="mde" value="s">
					<input type="hidden" name="sed" value="a">
					<input type="hidden" name="opt" value="<?=$ip?>">
					<input type="hidden" name="vrb" value="on">
					<input type="image" class="imgbtn" style="padding:0 2px" src="img/16/find.png" value="Submit">
					<?= (($verb1)?"$realbl $srvlbl":"$srvlbl $realbl") ?>
				</form>
<?php 	} ?>
<?php } ?>
				</div>
			</td>
			<td>
				<a href="Nodes-List.php?in[]=nodip&op[]==&st[]=<?= $ip ?>" title="<?= $updlbl ?> <?= date($_SESSION['timf'],$arp[3]) ?><?= ($arp[9])?", Device:$arp[9]":"" ?>"><?= $ip ?></a>
			</td>
			<td>
				<?= ($arp[7])?OSImg($arp[7]):'' ?><a href="Nodes-List.php?in[]=aname&op[]==&st[]=<?= $arp[11] ?>" title="<?= $updlbl ?> <?= date($_SESSION['timf'],$arp[12]) ?>"><?= $arp[11] ?></a>
			</td>
			<td>
<?php
			$tp = explode(',',$arp[4]);
			foreach ($tp as $i){
				if($i) echo SrvImg('tcp',$i);
			}
?>
			</td>
		</tr>
<?php
			$irow++;
		}
		DbFreeResult($res);

		$irow   = 1;
		$query	= GenQuery('nodnd','s','nodnd.*,aaaaname,dns6update','','',array('mac'),array('='),array($n[0]),array(),'LEFT JOIN dns6 USING (nodip6)' );
		$res	= DbQuery($query,$link);
		while( ($arp = DbFetchRow($res)) ){
			if ($irow % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$ip                 = DbIPv6($arp[1]);
			list($iuc,$iuc)     = Agecol($arp[3],$arp[3],1);
			list($ntimg,$ntit)  = Nettype('',$ip);
			TblRow($bg);
?>
			<td class="<?= $bi ?> ctr xs">
				<img src="img/<?= $ntimg ?>" title="<?= $irow ?>) <?= $ntit ?>" onclick="togvis('ip6<?= $irow ?>');">
				<div id="ip6<?= $irow ?>" class="genpad lft" style="position:absolute;display:none" onmouseout="togvis('ip6<?= $irow ?>');">
				<a href="Monitoring-Events.php?in[]=info&op[]=~&st[]=<?= $ip ?>"><img src="img/16/bell.png" title="IP ~ Monitoring-Events"></a><?= $msglbl ?><br>
				</div>
			</td>
			<td>
				<a href="Nodes-List.php?in[]=nodip6&op[]==&st[]=<?= $ip ?>" title="<?= $updlbl ?> <?= date($_SESSION['timf'],$arp[3]) ?><?= ($arp[9])?", Device:$arp[9]":"" ?>, Nodes-List"><?= $ip ?></a>
			</td>
			<td>
				<strong><a href="Nodes-List.php?in[]=aaaaname&op[]==&st[]=<?= $arp[11] ?>" title="<?= $updlbl ?> <?= date($_SESSION['timf'],$arp[12]) ?>, Nodes-List"><?= $arp[11] ?></a></strong>
			</td>
			<td>
<?php
			$tp = explode(',',$arp[4]);
			foreach ($tp as $i){
				if($i) echo SrvImg('tcp',$i);
			}
?>
			</td>
		</tr>
<?php
			$irow++;
		}
		DbFreeResult($res);
?>
	</table>
	<p>
	<table class="content">
		<tr>
			<th class="bgsub">
				<?= $metlbl ?> <?= $hislbl ?>
			</th>
		</tr>
		<tr>
			<td class="imga ctr b">
				<?php MetricChart("meth",4, $n[7]); ?><br>
				<span class="drd">0 = FD, 1 = HD</span><br>
				<span class="blu">1 = 10M, 2 = 100M, 3 = 1G, 4 = 10G</span>
			</td>
		</tr>
	</table>
</td><td class="ctr l">
<?php
		if( $n[18] ){
			$query	= GenQuery('links','s','neighbor,type,contact,icon','','',array('links.device','ifname'),array('=','='),array($n[4],$n[5]),array('AND'),'LEFT JOIN devices on (neighbor = devices.device)' );
			$res	= DbQuery($query,$link);
			while( ($neb = DbFetchRow($res)) ){
				if( !$neb[3] ){
					echo "\t<h2>".DecFix($n[23])." - $n[24] ($n[18])</h2>\n";					
				}else{
?>
	<div  class="genpad imga">
		<a href="Devices-Status.php?dev=<?= urlencode($neb[0]) ?>"><img src="img/dev/<?= $neb[3] ?>.png" title="<?= $neb[1] ?>, <?= $conlbl ?> <?= $neb[2] ?>, Devices-Status"></a>
		<a href="Devices-List.php?in[]=device&op[]==&st[]=<?= urlencode($neb[0]) ?>" title="Devices-List"><strong><?= $neb[0] ?></strong></a><br>
		<?= DecFix($n[23]) ?> - <?= $n[24] ?>
	</div>
<?php
				}
			}
		}else{
			echo "\t<h2>".DecFix($n[23])." - $n[24]</h2>\n";
		}
?>
	<p>
	<div  class="genpad ctr m" style="background-color:#<?= $ifchgcol ?>" title="<?= $cnclbl ?> <?= $updlbl ?>"><a href="Nodes-List.php?in[]=ifupdate&op[]==&st[]=<?= $n[8] ?>"><?= date($_SESSION['timf'],$n[8]) ?></a></div>
	<div style="background-color:<?= $lnkcol ?>;height:<?= ($lnkhgt*4) ?>px;"></div>
	<?php if($shg) IfGraphs($ud, $ui, $n[23],4); ?>
</td><td class="helper xl">
	<table class="content">
		<tr class="bgsub">
			<td colspan="2">
				<h2>Device</h2>
			</td>
		</tr>
		<tr class="txta">
			<td class="imga s b">
				<?= $namlbl ?>
			</td>
			<td>
				<a href="Devices-Status.php?dev=<?= $ud ?>&pop=on"><img src="img/dev/<?= $n[16] ?>.png" title="Devices-Status"></a>
				<a href="Devices-List.php?in[]=device&op[]==&st[]=<?= $ud ?>" title="Devices-List"><strong><?= $n[4] ?></strong></a>
			</td>
		</tr>
		<tr class="txtb">
			<td class="imgb s b">
				<?= $typlbl ?>
			</td>
			<td>
				<a href="http://www.google.com/search?q=<?= urlencode("$n[17] $n[12]") ?>&btnI=1" target="window"><img src="img/oui/<?= VendorIcon($n[17]) ?>.png" title="<?= $n[17] ?>"></a>
				<a href="Devices-List.php?in[]=type&op[]==&st[]=<?= urlencode($n[12]) ?>" title="Devices-List"><?= $n[12] ?></a>
			</td>
		</tr>
		<tr class="txta">
			<td class="imga s b">
				<?= $conlbl ?>
			</td>
			<td>
				<a href="Devices-List.php?in[]=contact&op[]==&st[]=<?= urlencode($n[14]) ?>"><?= $n[14] ?></a>
			</td>
		</tr>
		<tr class="txtb">
			<td class="imgb s b">
				<?= $loclbl ?>
			</td>
			<td>
				<?= $loc[1] ?>,<?= $loc[0] ?> <?= $loc[2] ?>, <?= $place['f'] ?> <?= $loc[3] ?>
			</td>
		</tr>
		<tr class="<?= ($ifbg)?$ifbg:"txtb" ?>">
			<td class="imga s b">
				<?= $porlbl ?>
			</td>
			<td>
				<img src="img/<?= $ifimg ?>" title="<?= $iftyp ?> - <?= $ifst ?>, <?= $laslbl ?> <?= $stalbl ?> <?= $chglbl ?> <?= date($_SESSION['timf'],$n[26]) ?>">
				<a href="Devices-Interfaces.php?in[]=device&op[]==&in[]=ifname&op[]==&st[]=<?= $ud ?>&co[]=AND&st[]=<?= $ui?>" class="b"><?= $n[5] ?></a> <?= $n[21] ?> <span class="gry"><?= $n[20] ?></span>
			</td>
		</tr>
		<tr class="txtb">
			<td class="imgb s b">
				Vlan
			</td>
			<td>
				<a href="Devices-Vlans.php?in[]=vlanid&op[]==&st[]=<?= $n[6] ?>"><?= $n[6] ?></a> - <?= $vl[2] ?>
			</td>
	</table>
	<p>
	<table class="content">
		</tr>
			<td class="bgsub ctr b" colspan="2">
				<?= $stslbl ?> <?= $totlbl ?> / <?= $laslbl ?>
			</td>
		</tr>
		<tr>
			<td class="imga ctr nw">
					<?php IfRadar('radtot',4,'28',$n[27],$n[28],$n[29],$n[30],$n[31],$n[32],$n[33],1); ?>
					<?php IfRadar('radlast',4,'82',$n[34],$n[35],$n[36],$n[37],$n[38],$n[39],$n[40],1); ?>
			</td>
		</tr>
	</table>
</td>
</tr></table>

<table class="full fixed">
<tr><td class="helper">

	<h2>IP <?= $chglbl ?></h2>

<?php
		$query	= GenQuery('iptrack','s','*','ipupdate desc','',array('mac'),array('='),array($n[0]) );
		$res	= DbQuery($query,$link);
		if( DbNumRows($res) ){
?>
	<table class="content">
		<tr class="bgsub">
			<th colspan="2"><img src="img/16/clock.png"><br><?= $updlbl ?></th>
			<th class="l"><img src="img/16/abc.png"><br><?= $namlbl ?></th>
			<th class="m"><img src="img/16/net.png"><br>IP <?= $adrlbl ?></th>
		</tr>
	</table>
	<div class="scroller">
	<table class="content" >
<?php
			$row = 0;
			while( $r = DbFetchRow($res) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$lip = long2ip($r[3]);
				echo "\t\t<tr class=\"$bg\">\n";
				echo "\t\t\t<td class=\"$bi ctr b\">\n\t\t\t\t$row\n\t\t\t</td>\n";
				echo "\t\t\t<td>\n\t\t\t\t".date($_SESSION['timf'],$r[1]) ."\n\t\t\t</td>\n";
				echo "\t\t\t<td class=\"l\">\n\t\t\t\t$r[2]\n\t\t\t</td>\n";
				echo "\t\t\t<td class=\"m\">\n\t\t\t\t<a href=\"Nodes-List.php?in[]=nodip&op[]==&st[]=$lip\" title=\"ARP Device $r[4], Nodes-List\">$lip</a>\n\t\t\t</td>\n";
				echo "\t\t</tr>\n";
			}
?>
	</table>
	</div>
	<table class="content">
		<tr class="bgsub"><td><?= $row ?> IP <?= $chglbl ?></td></tr>
	</table>
<?php
		}else{
?>
	<h5><?= $nonlbl ?></h5>
<?php
		}
		DbFreeResult($res);
?>

</td><td class="helper">

	<h2>IF <?= $chglbl ?></h2>

<?php
		$query	= GenQuery('iftrack','s','*','ifupdate desc','',array('mac'),array('='),array($n[0]) );
		$res	= DbQuery($query,$link);
		if( DbNumRows($res) ){
?>
	<table class="content">
		<tr class="bgsub">
			<th colspan="2"><img src="img/16/clock.png"><br><?= $updlbl ?></th>
			<th class="l"><img src="img/16/dev.png"><br>Device</th>
			<th class="m"><img src="img/16/port.png"><br>IF</th>
			<th class="s"><img src="img/16/vlan.png"><br>Vlan</th>
		</tr>
	</table>
	<div class="scroller">
	<table class="content" >
<?php
			$row = 0;
			while( $r = DbFetchRow($res) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$utd = rawurlencode($r[2]);
				$uti = rawurlencode($r[3]);
				echo "\t\t<tr class=\"$bg\">\n";
				echo "\t\t\t<td class=\"$bi ctr b\">\n\t\t\t\t$row\n\t\t\t</td>\n";
				echo "\t\t\t<td>\n\t\t\t\t". date($_SESSION['timf'],$r[1]) ."\n\t\t\t</td>\n";
				echo "\t\t\t<td class=\"l\">\n\t\t\t\t<a href=\"Devices-Status.php?dev=$utd&shp=on\">$r[2]</a>\n\t\t\t</td>\n";
				echo "\t\t\t<td class=\"m\">\n\t\t\t\t<a href=\"Nodes-List.php?in[]=device&op[]==&st[]=$utd&co[]=AND&in[]=ifname&op[]==&st[]=$uti\">$r[3]</a>\n\t\t\t</td>\n";
				echo "\t\t\t<td class=\"s\">\n\t\t\t\t$r[4]\n\t\t\t</td>\n";
				echo "\t\t</tr>\n";
			}
?>
	</table>
	</div>
	<table class="content">
		<tr class="bgsub"><td><?= $row ?> IF <?= $chglbl ?></td></tr>
	</table>
<?php
		}else{
?>
<h5><?= $nonlbl ?></h5>
<?php
		}
		DbFreeResult($res);
?>

</td></tr></table>
<?php
	}
	DbFreeResult($nres);
	if( !$ud ) echo "<h4>$nonlbl Node</h4>";
}elseif ($wol and $wip){
	if(preg_match("/dsk/",$_SESSION['group']) ){
		$query	= GenQuery('networks','s','inet_ntoa(ifip|power(2, 32 - prefix )-1)','','1',array('ifip','(ifip|power(2, 32 - prefix )-1)'),array('>','COL ='),array(0,"($wip|power(2, 32 - prefix )-1)"),array('AND'));
		$bres = DbQuery($query,$link);
		$bcst = DbFetchRow($bres);
		Wake($bcst[0],$wol, 9);
		Wake("255.255.255.255",$wol, 9);							# In case local broadcast addr is not allowed
	}else{
		echo $nokmsg;
	}
?>
<script language="JavaScript"><!--
setTimeout("history.go(-1)",3000);
//--></script>
<?php
}elseif ($del){
	if($ismgr){
		$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
		NodDelete($del);
?>
<script language="JavaScript"><!--
setTimeout("history.go(-2)",3000);
//--></script>
<?php
	}else{
		echo $nokmsg;
	}
}

include_once ("inc/footer.php");
?>
