<?php
# Program: Topology-Multicast.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libsnmp.php");

$_GET = sanitize($_GET);
$mdv = isset($_GET['dev']) ? $_GET['dev'] : "";

$devtyp = array();
$link   = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query  = GenQuery('devices','s','*','device','',array('services','snmpversion'),array('>','!='),array('3','0'),array('AND') );
$res    = DbQuery($query,$link);
if($res){
	while( ($d = DbFetchRow($res)) ){
		$devtyp[$d[0]] = $d[3];
	}
	DbFreeResult($res);
}else{
	print DbError($link);
}

?>
<h1><?= $rltlbl ?> Multicast</h1>

<?php  if( !isset($_GET['print']) ) { ?>

<form method="get" action="<?= $self ?>.php" name="mrout">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
	<select size="1" name="dev" onchange="this.form.submit();">
		<option value="">Device ->
<?php
foreach (array_keys($devtyp) as $r ){
	echo "\t\t<option value=\"$r\" ";
	if($mdv == $r){echo "selected";}
	echo " >$r\n";
}
?>
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
if ($mdv) {
	$query	= GenQuery('devices','s','*','','',array('device'),array('='),array($mdv) );
	$res	= DbQuery($query,$link);
	$ndev	= DbNumRows($res);
	if ($ndev != 1) {
		echo "<h4>$mdv $mullbl $vallbl</h4>";
		DbFreeResult($res);
		die;
	}else{
		$dev	= DbFetchRow($res);
		$ip	= long2ip($dev[1]);
		$sv	= Syssrv($dev[6]);
		$ud = rawurlencode($dev[0]);
		DbFreeResult($res);

		$query	= GenQuery('interfaces','s','ifidx,ifname,iftype,alias,comment','','',array('device'),array('='),array($mdv) );
		$res	= DbQuery($query,$link);
		while( ($i = DbFetchRow($res)) ){
			$ifn[$i[0]] = $i[1];
			$ift[$i[0]] = $i[2];
			$ifi[$i[0]] = "$i[3] $i[4]";
		}
		DbFreeResult($res);

?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $sumlbl ?></h2>

<table class="content">
	<tr>
		<td class="imga ctr b s">
			<a href="Devices-Status.php?dev=<?= $ud ?>"><img src="img/dev/<?= $dev[18] ?>.png" title="<?= $stalbl ?>"></a><br>
			<?= $dev[0] ?>

		</td>
		<td class="bgsub">
		</td>
	</tr>
	<tr>
		<td class="imgb b">
			IP <?= $adrlbl ?>

		</td>
		<td class="txtb">
			<?= $ip ?>

			<div style="float:right">
				<a href="telnet://<?= $ip ?>"><img src="img/16/loko.png" title="Telnet"></a>
				<a href="ssh://<?= $ip ?>"><img src="img/16/lokc.png" title="SSH"></a>
				<a href="http://<?= $ip ?>" target="window"><img src="img/16/glob.png" title="HTTP"></a>
				<a href="https://<?= $ip ?>" target="window"><img src="img/16/glok.png" title="HTTPS"></a>
			</div>
		</td>
	</tr>
	<tr>
		<td class="imga b">
			<?= $srvlbl ?>

		</td>
		<td class="txtb">
			<?= ($sv)?$sv:"&nbsp;" ?>

		</td>
	</tr>
	<tr>
		<td class="imgb b">
			<?= $loclbl ?>

		</td>
		<td class="txta">
			<?= $dev[10] ?>

		</td>
	</tr>
	<tr>
		<td class="imga b">
			<?= $conlbl ?>

		</td>
		<td class="txtb">
			<?= $dev[11] ?>

		</td>
	</tr>
</table>

</td><td class="helper ctr">

<h2><?= $neblbl ?> <?= $maplbl ?></h2>

<a href="Topology-Map.php?tit=<?= $ud ?>+<?= $neblbl ?>+Map&in[]=device&op[]==&st[]=<?= $ud ?>&co[]=OR&in[]=neighbor&op[]==&st[]=<?= $ud ?>&fmt=png&mde=f&lev=4&ifi=on"><img class="genpad" src="inc/drawmap.php?dim=320x200&in[]=device&op[]==&st[]=<?= $ud ?>&co[]=OR&in[]=neighbor&op[]==&st[]=<?= $ud ?>&mde=f&lev=4&pos=s&ifi=on&lal=30"></a>

</td></tr></table>

<h2>IGMP  <?= $grplbl ?> <?= $lstlbl ?></h2>

<table class="content">
	<tr class="bgsub">
<?php
		if ($dev[8] == "ProCurve"){
?>
		<th>
			<img src="img/16/home.png"><br>
			<?= $dstlbl ?>

		</th>
		<th>
			<img src="img/16/note.png"><br>
			# Reports
		</th>
		<th>
			<img src="img/16/node.png"><br>
			Queries
		</th>
		<th>
			<img src="img/16/vlan.png"><br>
			Vlan
		</th>
	</tr>
<?php
			snmp_set_quick_print(1);

			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.11.2.14.11.5.1.9.10.1.1.1') as $ix => $val){
				$vlan[substr(strstr($ix,'14.11.5.1.9.10'),23)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.11.2.14.11.5.1.9.10.1.1.3') as $ix => $val){
				$rep[substr(strstr($ix,'14.11.5.1.9.10'),23)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.11.2.14.11.5.1.9.10.1.1.4') as $ix => $val){
				$qer[substr(strstr($ix,'14.11.5.1.9.10'),23)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			ksort($vlan);
			$row = 0;
			foreach($vlan as $grp => $vl){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				list($ntimg,$ntit) = Nettype($grp);
				echo "\t<tr class=\"$bg\">\n";
				echo "\t\t<td>\n\t\t\t<img src=\"img/$ntimg\" title=\"$ntit\"> $grp\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t$rep[$grp]\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t$qer[$grp]\n\t\t</td>\n\t\t<td>\n\t\t\t$vl\n\t\t</td>\n\t</tr>\n";
			}
			TblFoot("bgsub", 4, "$row $vallbl" );

?>

<h2>IGMP Querier <?= $lstlbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/port.png"><br>
			Interface
		</th>
		<th>
			<img src="img/16/home.png"><br>
			<?= $dstlbl ?>

		</th>
		<th>
			<img src="img/16/date.png"><br>
			Age <?= $timlbl ?>

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			Leave <?= $timlbl ?>

		</th>
	</tr>
<?php
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.11.2.14.11.5.1.9.10.3.1.4') as $ix => $val){
				$age[substr(strstr($ix,'14.11.5.1.9.10'),23)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.11.2.14.11.5.1.9.10.3.1.4') as $ix => $val){
				$lve[substr(strstr($ix,'14.11.5.1.9.10'),23)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			ksort($age);
			$row = 0;
			foreach($age as $grp => $a){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$ix = explode(".", $grp);
				list($ifimg,$iftit) = Iftype($ift[$ix[4]]);
				list($ntimg,$ntit)  = Nettype($grp);
				echo "\t<tr class=\"$bg\">\n";
				echo "\t\t<td class=\"$bi ctr xs\">\n\t\t\t<img src=\"img/$ifimg\" title=\"$iftit\">\n\t\t</td>\n";
				echo "\t\t<td class=\"b\">\n\t\t\t".$ifn[$ix[4]]."\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t".$ifi[$ix[4]]."\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t<img src=\"img/$ntimg\" title=\"$ntit\"> $ix[0].$ix[1].$ix[2].$ix[3]\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t$a\n\t\t</td>\n\t\t<td>\n\t\t\t$lve[$grp]\n\t\t</td>\n\t</tr>\n";
			}
			TblFoot("bgsub", 6, "$row $vallbl" );
		}else{
?>
		<th>
			<img src="img/16/cam.png"><br>
			<?= $srclbl ?>

		</th>
		<th>
			<img src="img/16/node.png"><br>
			<?= $dstlbl ?>

		</th>
		<th>
			<img src="img/16/tap.png"><br>
			<?= $bwdlbl ?>

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $laslbl ?>

		</th>
	</tr>
<?php
			snmp_set_quick_print(1);
			snmp_set_oid_numeric_print(1);

			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.9.10.2.1.1.2.1.12') as $ix => $val){
				$prun[substr(strstr($ix,'9.10.2.1.1.2.1.'),18)] = $val;					// cut string at beginning with strstr first, because it depends on snmpwalk & Co being installed!
			}
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.9.10.2.1.1.2.1.19') as $ix => $val){
				$bps[substr(strstr($ix,'9.10.2.1.1.2.1.'),18)] = $val;
			}
			foreach (Walk($ip, $dev[14] & 3, $dev[15],'1.3.6.1.4.1.9.10.2.1.1.2.1.23') as $ix => $val){
				$last[substr(strstr($ix,'9.10.2.1.1.2.1.'),18)] = $val;
			}
			ksort($prun);
			$row = 0;
			foreach($prun as $mr => $pr){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$i  = explode(".", $mr);
				$ip = "$i[4].$i[5].$i[6].$i[7]";
				if($pr == 1){
					$ntimg = "16/bstp.png";
				}else{
					list($ntimg,$ntit)  = Nettype($ip);
				}
				sscanf($last[$mr], "%d:%d:%0d:%0d.%d",$lud,$luh,$lum,$lus,$ticks);
				$bpsbar = Bar( intval($bps[$mr]/1000),0);
				echo "\t<tr class=\"$bg\">\n";
				echo "\t\t<td>\n\t\t\t<a href=Nodes-List.php?in[]=nodip&op[]==&st[]=$ip>$ip</a>\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t<img src=\"img/$ntimg\" title=\"$ntit\">$i[0].$i[1].$i[2].$i[3]\n\t\t</td>\n";
				echo "\t\t<td>\n\t\t\t$bpsbar".$bps[$mr]."\n\t\t</td>\n";
				printf("\t\t<td>\n\t\t\t%d D %d:%02d:%02d\n\t\t</td>\n\t</tr>\n",$lud,$luh,$lum,$lus);
			}
			TblFoot("bgsub", 4, "$row $vallbl" );
		}
	}
}

include_once ("inc/footer.php");
?>
