<?php
# Program: Monitoring-Setup.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();

$tst = isset($_GET['tst']) ? $_GET['tst'] : '';
$top = isset($_GET['top']) ? $_GET['top'] : '';
$trs = isset($_GET['trs']) ? $_GET['trs'] : '';
$adp = isset($_GET['adp']) ? $_GET['adp'] : '';
$rav = isset($_GET['rav']) ? $_GET['rav'] : '';
$uip = isset($_GET['uip']) ? $_GET['uip'] : '';
$efd = isset($_GET['efd']) ? $_GET['efd'] : '';
$elv = isset($_GET['elv']) ? $_GET['elv'] : '';
$inf = isset($_GET['inf']) ? $_GET['inf'] : '';
$al  = isset($_GET['al']) ? $_GET['al'] : '';

$tem = isset($_GET['tem']) ? 1 : 0;
$tet = isset($_GET['tet']) ? $_GET['tet'] : '';

$nrp = isset($_GET['nrp']) ? $_GET['nrp'] : '';
$law = isset($_GET['law']) ? $_GET['law'] : '';
$nfy = isset($_GET['nfy']) ? $_GET['nfy'] : '';

$cpa = isset($_GET['cpa']) ? $_GET['cpa'] : '';
$mea = isset($_GET['mea']) ? $_GET['mea'] : '';
$tea = isset($_GET['tea']) ? $_GET['tea'] : '';
$pow = isset($_GET['pow']) ? $_GET['pow'] : '';
$arp = isset($_GET['arp']) ? $_GET['arp'] : '';
$sua = isset($_GET['sua']) ? $_GET['sua'] : '';

$des = isset($_GET['des']) ? $_GET['des'] : '';
$dpt = isset($_GET['dpt']) ? $_GET['dpt'] : '';
$dps = isset($_GET['dps']) ? $_GET['dps'] : '';
$dpt2= isset($_GET['dpt2']) ? $_GET['dpt2'] : '';
$dps2= isset($_GET['dps2']) ? $_GET['dps2'] : '';

$cols = array(	"name"=>"Name",
		"monip"=>"IP $adrlbl",
		"class"=>$clalbl,
		"depend1"=>$deplbl,
		"depend2"=>"${deplbl}2",
		"test"=>"$tstlbl",
		"noreply"=>"$nonlbl $rpylbl",
		"alert"=>$mlvl['200'],
		"latwarn"=>"$latlbl $mlvl[150]",
		"testopt"=>"$tstlbl $sndlbl",
		"testres"=>"$tstlbl $rcvlbl",
		"lastok"=>"$laslbl OK",
		"status"=>$stalbl,
		"lost"=>$loslbl,
		"eventdel"=>"$msglbl $dcalbl",
		"latency"=>"$latlbl $laslbl",
		"latavg"=>"$latlbl $avglbl",
		"eventlvl"=>"$levlbl $limlbl",
		"eventfwd"=>"$msglbl $fwdlbl",
		"eventmax"=>"$maxlbl $levlbl",
		"notify"=>"notify",
		"cpualert"=>"CPU $mlvl[200]",
		"memalert"=>"Mem $mlvl[200]",
		"tmpalert"=>"$tmplbl $mlvl[200]",
		"poewarn"=>"PoE $mlvl[150]",
		"arppoison"=>"ARPpoison $mlvl[150]",
		"supplyalert"=>"Supply $mlvl[200]",
		"type"=>"Device $typlbl",
		"devos"=>"Device OS",
		"bootimage"=>"Bootimage",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"firstdis"=>"$fislbl $dsclbl",
		"lastdis"=>"$laslbl $dsclbl"
		);

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);

function MonUpdate($tgt,$c,$v,$t,$p){

	global $link,$updlbl;

	if($v === "-"){$v='';}
	$uquery	= GenQuery('monitoring','u',"name = '".DbEscapeString($tgt)."'",'','',array($c),array(),array($v) );
	if( !DbQuery($uquery,$link) ){
		return array ("<img src=\"img/16/bcnl.png\" title=\"" .DbError($link)."\">",$p);
	}else{
		return array ("<img src=\"img/16/bchk.png\" title=\"$t $updlbl OK\">",$v);
	}
}

?>
<h1>Monitoring Setup</h1>

<?php  if( !isset($_GET['print']) ) { ?>

<form method="get" action="<?= $self ?>.php" name="mons">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top">
	<h3><?= $fltlbl ?></h3>
	<a href="?in[]=status&op[]=>&st[]=0"><img src="img/16/flag.png" title="<?= $mlvl['200'] ?> <?= $stalbl ?>"></a>
	<a href="?in[]=test&op[]=%3D&st[]=uptime"><img src="img/16/clock.png" title="Uptime <?= $tstlbl ?>"></a>
	<a href="?in[]=depend1&op[]=%3D&st[]=&in[]=depend1&op[]=%3D&st[]="><img src="img/16/ncon.png" title="<?= $nonlbl ?> <?= $deplbl ?>"></a>
	<a href="?in[]=eventfwd&op[]=~&st[]=."><img src="img/16/mail.png" title="<?= $msglbl ?> <?= $fwdlbl ?>"></a>
	<a href="?in[]=eventdel&op[]=~&st[]=."><img src="img/16/bdis.png" title="<?= $msglbl ?> <?= $dcalbl ?>"></a>
	<a href="?in[]=eventlvl&op[]=!%3D&st[]=0"><img src="img/16/fogy.png" title="<?= $levlbl ?> <?= $limlbl ?>"></a>

<?php Filters(1); ?>
</td>
<td class="top nw">
	<h3>
		<a href="?tem=1"><img src="img/16/ford.png" title="<?= $tstlbl ?> <?= $mlvl['250'] ?>"></a>
		<?= $monlbl ?>
	</h3>
	<img src="img/16/say.png" title="<?= $tstlbl ?>">
	<select size="1" name="tst">
		<option value=""><?= $tstlbl ?> >
		<option value="none"><?= $nonlbl ?>
		<option value="cifs">cifs
		<option value="dns">dns
		<option value="mysql">mysql
		<option value="ntp">ntp
		<option value="http">http
		<option value="https">https
		<option value="ping">ping
		<option value="icmp">icmp
		<option value="ssh">ssh
		<option value="telnet">telnet
		<option value="uptime">uptime
	</select>
	<input type="number" min="1" max="9" name="nrp" class="xs" title="# <?= $nonlbl ?> <?= $rpylbl ?>">
	<select size="1" name="al">
		<option value=""><?= $mlvl['200'] ?> >
		<option value="1"><?= $nonlbl ?>
		<option value="2"><?= $msglbl ?>
		<option value="3">Mail
		<option value="131">Mail (<?= substr($rptlbl,0,3) ?>)
		<option value="7">Mail & SMS
		<option value="135">M&S (<?= substr($rptlbl,0,3) ?>)
	</select><br>
	<img src="img/16/bbrt.png" title="<?= $tstlbl ?> <?= $sndlbl ?>">
	<input type="text" name="top" class="l"><br>
	<img src="img/16/bblf.png" title="<?= $tstlbl ?> <?= $rcvlbl ?>">
	<input type="text" name="trs" class="m">
	<input type="number" min="0" step="50" name="law" class="s" title="<?= $latlbl ?> <?= $mlvl['150'] ?> [ms]">

</td>
<td class="top nw">
	<h3><?= $msglbl ?></h3>
	<img src="img/16/bell.png" title="Syslog, Trap, <?= $dsclbl ?>">
	<select size="1" name="efd">
		<option value="fwd"><?= $fwdlbl ?>
		<option value="del"><?= $dcalbl ?>
		<option value="max"><?= $maxlbl ?>
	</select>
	<select size="1" name="elv">
		<option value=""><?= $levlbl ?>
		<option value="1"><?= $nonlbl ?>
		<option value="11"  class="txtb"><?= $mlvl['30'] ?>
		<option value="51"  class="good"><?= $mlvl['50'] ?>
		<option value="101" class="noti"><?= $mlvl['100'] ?>
		<option value="151" class="warn"><?= $mlvl['150'] ?>
		<option value="201" class="alrm"><?= $mlvl['200'] ?>
		<option value="251" class="crit"><?= $mlvl['250'] ?>
	</select><br>
	<img src="img/16/abc.png" title="<?= $fltlbl ?>">
	<input type="text" name="inf" class="l"><br>
	<img src="img/16/radr.png" title="notify" onclick="document.mons.nfy.value='<?= $notify ?>';">
	<input type="text" name="nfy" class="l">
</td>
<td class="top nw">
	<h3><?= $trslbl ?></h3>

	<img src="img/16/cpu.png" title="CPU <?= $mlvl['200'] ?>"><input type="number" min="0" max="100" name="cpa" class="xs">
	<img src="img/16/mem.png" title="Mem <?= $mlvl['200'] ?>"><input type="number" min="0" name="mea" class="xs"><br>
	<img src="img/16/temp.png" title="<?= $tmplbl ?> <?= $mlvl['200'] ?>"><input type="number" min="0" max="250" name="tea" class="xs">
	<img src="img/16/batt.png" title="PoE <?= $mlvl['150'] ?>"><input type="number" min="0" max="100" name="pow" class="xs"><br>
	<img src="img/16/drop.png" title="ARPpoison <?= $mlvl['150'] ?>"><input type="number" min="0" max="999" name="arp" class="xs">
	<img src="img/16/file.png" title="Supply <?= $mlvl[200] ?>"><input type="number" min="0" max="100" name="sua" class="xs">
</td>
<td class="ctr top nw">
	<h3><?= $reslbl ?></h3>
	<img src="img/16/ncon.png" title="Auto <?= $deplbl ?>">
	<input type="checkbox" name="adp"><br>
	<img src="img/16/net.png" title="IP <?= $updlbl ?>">
	<input type="checkbox" name="uip"><br>
	<img src="img/16/walk.png" title="<?= $avalbl ?>">
	<input type="checkbox" name="rav">
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>"><br>
	<input type="submit" class="button" name="upd" value="<?= $updlbl ?>"><br>
	<input type="submit" class="button" name="del" value="<?= $dellbl ?>" onclick="return confirm('Monitor <?= $dellbl ?>, <?= $cfmmsg ?>')" >
</td>
</tr>
</table>
</form>
<p>
<?php
}

if( $tem == 1 ){
	echo "<div class=\"textpad code pre txta tqrt\">\n";
	echo "<h5>$nedipath/moni.pl -vc250</h5>\n";
	system("$nedipath/moni.pl -vc250");
	echo "</div>\n";
}elseif( $tet ){
	echo "<div class=\"textpad code pre txta tqrt\">\n";
	echo "<h5>$nedipath/moni.pl -vt $tet</h5>\n";
	system("$nedipath/moni.pl -vt$tet");
	echo "</div>\n";
}

if( count($in) ){
	Condition($in,$op,$st,$co);
?>

<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/trgt.png"><br>
			<?= $tgtlbl ?>

		</th>
		<th>
			<img src="img/16/chrt.png"><br>
			<?= $stslbl ?>

		</th>
		<th>
			<img src="img/16/say.png"><br>
			<?= $tstlbl ?>

		</th>
		<th>
			<img src="img/16/ncon.png"><br>
			<?= $deplbl ?>

		</th>
		<th>
			<img src="img/16/flag.png"><br>
			<?= $mlvl['200'] ?>

		</th>
		<th>
			<img src="img/16/bell.png"><br>
			<?= $msglbl ?> <?= $actlbl ?>

		</th>
		<th>
			<img src="img/16/radr.png"><br>
			<?= $dsclbl ?>

		</th>
	</tr>
<?php
	$query	= GenQuery('monitoring','s','monitoring.*,devip','monitoring.name','',$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$row  = 0;
		$nnod = 0;
		$ndev = 0;
		$srcip= 0;
		while( ($mon = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$una = urlencode($mon[0]);
			list($statbg,$stat) = StatusBg(1,($mon[3] != 'none')?1:0,$mon[7],$bi);

			TblRow($bg);
			$cmpip = 1;
			$neb   = array();
			if ($mon[2] == "dev"){
				$ndev++;
				$srcip = $mon[31];
				$query = GenQuery('links','s','neighbor,nbrifname','','',array('device'),array('='),array($mon[0]) );
				$dres  = DbQuery($query,$link);
				if($dres){
					if ( DbNumRows($dres) ) {
						while( ($l = DbFetchRow($dres)) ){
							$neb[$l[0]] = $l[1];
						}
						DbFreeResult($dres);
					}
				}else{
					print DbError($link);
				}
				echo "\t\t<td class=\"$statbg ctr\">\n\t\t\t<a href=\"Devices-Status.php?dev=$una\"><img src=\"img/16/dev.png\" title=\"$stat\"></a>\n";
			}elseif($mon[2] == "node"){
				$nnod++;
				$query = GenQuery('dns','s','nodip','','',array('aname'),array('='),array($mon[0]) );
				$dres  = DbQuery($query,$link);
				if($dres){
					$nnod = DbNumRows($dres);
					if($nnod == 1) {
						echo "\t\t<td class=\"$statbg ctr\">\n\t\t\t<a href=\"Nodes-List.php?in[]=aname&op[]=%3D&st[]=$una\"><img src=\"img/16/node.png\"  title=\"$stat\"></a>\n";
						$l = DbFetchRow($dres);
						$srcip = $l[0];
					}elseif($nnod > 1){
						$cmpip = 0;
						echo "\t\t<td class=\"warn part ctr\">\n\t\t\t<a href=\"Nodes-List.php?in[]=aname&op[]=%3D&st[]=$una\"><img src=\"img/16/nods.png\" title=\"$mullbl Nodes $namlbl!\"></a>\n";
					}else{
						$cmpip = 0;
						echo "\t\t<td class=\"warn part ctr\">\n\t\t\t<a href=\"Nodes-List.php?in[]=nodip&op[]=%3D&st[]=$mon[1]\"><img src=\"img/16/bcls.png\" title=\"$nonlbl Nodes! (IP $stat)\"></a>\n";
					}
					DbFreeResult($dres);
				}else{
					print DbError($link);
				}
			}else{
				echo "\t\t<td class=\"txtb\">\n\t\t\t<img src=\"img/16/qmrk.png\">\n";
			}
			if($mon[1] != $srcip and $cmpip){
				echo "\t\t\t<img src=\"img/16/bdis.png\" title=\"IP $chglbl ".long2ip($mon[1])." > ".long2ip($srcip).": $updlbl!\">\n";
			}

			$depst[0] = '';
			$depst[1] = '';
			$alst     = '';
			$elst     = '';
			ksort($neb);
			if( isset($_GET['upd']) ){
				if($adp){
					if(count(array_keys($neb) ) == 1){
						$dquery	= GenQuery('monitoring','u',"name = '".DbEscapeString($mon[0])."'",'','',array('depend1'),array(),array( key($neb) ) );
						if( !DbQuery($dquery,$link) ){
							$depst[0] = "<img src=\"img/16/bcnl.png\" title=\"" .DbError($link)."\">";
						}else{
							$depst[0] = "<img src=\"img/16/bchk.png\" title=\"Auto $deplbl OK\">";
							$mon[19]  = key($neb);
						}
					}elseif(count(array_keys($neb) ) == 2){
						$i = 0;
						foreach ( array_keys($neb) as $nb){
							$depcol = ($i)?'depend1':'depend2';
							$dquery	= GenQuery('monitoring','u',"name = '".DbEscapeString($mon[0])."'",'','',array($depcol),array(),array($nb) );
							if( !DbQuery($dquery,$link) ){
								$depst[$i] = "<img src=\"img/16/bcnl.png\" title=\"" .DbError($link)."\">";
							}else{
								$depst[$i] = "<img src=\"img/16/bchk.png\" title=\"Auto $deplbl OK\">";
								$mon[19+$i]= $nb;
							}
							$i++;
						}
					}else{
						$depst[0] = "<img src=\"img/16/bdis.png\" title=\"$mullbl $deplbl\">";
					}
				}

				if($rav){
					$uquery	= GenQuery('monitoring','u',"name = '".DbEscapeString($mon[0])."'",'','',array('lastok','status','lost','ok','latency','latmax','latavg'),array(),array(0,0,0,0,0,0,0) );
					if( !DbQuery($uquery,$link) ){
						$ravst = "<img src=\"img/16/bcnl.png\" title=\"" .DbError($link)."\">";
					}else{
						$ravst = "<img src=\"img/16/bchk.png\" title=\"$avalbl $reslbl OK\">";
						$mon[6]  = 0;
						$mon[7]  = 0;
						$mon[8]  = 0;
						$mon[9]  = 0;
						$mon[10] = 0;
						$mon[11] = 0;
						$mon[12] = 0;
					}
				}

				if($uip){
					list($uipst,$mon[1]) = MonUpdate($mon[0],'monip',$srcip,'IP',$mon[3]);
				}
				if($tst){
					list($testst,$mon[3]) = MonUpdate($mon[0],'test',$tst,$tstlbl,$mon[3]);
				}
				if($nrp != ''){
					list($nrpst,$mon[23]) = MonUpdate($mon[0],'noreply',$nrp,"$nonlbl $rpylbl",$mon[23]);
				}
				if($al){
					list($alst,$mon[14]) = MonUpdate($mon[0],'alert',$al-1,$mlvl['200'],$mon[14]);	# Adding 1 in the form, so it's still true with 0
				}
				if($law != ''){
					list($lawst,$mon[24]) = MonUpdate($mon[0],'latwarn',$law,"$latlbl $trslbl",$mon[24]);
				}
				if($top){
					list($topst,$mon[4]) = MonUpdate($mon[0],'testopt',$top,"$tstlbl $sndlbl",$mon[4]);
				}
				if($trs){
					list($trsst,$mon[5]) = MonUpdate($mon[0],'testres',$trs,"$tstlbl $rcvlbl",$mon[5]);
				}
				if($elv){
					$myelv = ($efd == "fwd" or $elv == 1)?$elv-1:$elv;		# Adding 1 in the form, so it's still true with 0 (remove eventlevel)
					list($elst,$mon[16]) = MonUpdate($mon[0],'eventlvl',$myelv,"$levlbl $limlbl",$mon[16]);
				}
				if($inf){
					if($efd == "fwd"){
						list($infst,$mon[15]) = MonUpdate($mon[0],'eventfwd',$inf,"$fwdlbl $fltlbl",$mon[15]);
					}elseif($efd == "max"){
						list($infst,$mon[18]) = MonUpdate($mon[0],'eventmax',$inf,"$maxlbl $levlbl",$mon[18]);
					}else{
						list($infst,$mon[17]) = MonUpdate($mon[0],'eventdel',$inf,"$dcalbl $fltlbl",$mon[17]);
					}
				}
				if($nfy != ''){
					list($nfyst,$mon[22]) = MonUpdate($mon[0],'notify',$nfy,"$notify",$mon[22]);
				}
				if($cpa != ''){
					list($cpast,$mon[25]) = MonUpdate($mon[0],'cpualert',$cpa,"CPU $mlvl[200]",$mon[25]);
				}
				if($mea != ''){
					list($meast,$mon[26]) = MonUpdate($mon[0],'memalert',$mea,"Mem $mlvl[200]",$mon[26]);
				}
				if($tea != ''){
					list($teast,$mon[27]) = MonUpdate($mon[0],'tempalert',$tea,"$tmplbl $mlvl[200]",$mon[27]);
				}
				if($pow != ''){
					list($powst,$mon[28]) = MonUpdate($mon[0],'poewarn',$pow,"PoE $mlvl[150]",$mon[28]);
				}
				if($arp != ''){
					list($arpst,$mon[29]) = MonUpdate($mon[0],'arppoison',$arp,"PoE $mlvl[150]",$mon[29]);
				}
				if($sua != ''){
					list($suast,$mon[30]) = MonUpdate($mon[0],'supplyalert',$sua,"Supply $mlvl[200]",$mon[30]);
				}
			}elseif($des and $des ==  $mon[0] and ($dps or $dpt) ){
				list($depst[0],$mon[19]) = MonUpdate($mon[0],'depend1',($dps)?$dps:$dpt,"$deplbl",$mon[19]);
			}elseif($des and $des ==  $mon[0] and ($dps2 or $dpt2) ){
				list($depst[1],$mon[20]) = MonUpdate($mon[0],'depend2',($dps2)?$dps2:$dpt2,"$deplbl",$mon[20]);
			}
?>
		</td>
		<td class="b">
			<a href="?in[]=name&op[]=%3D&st[]=<?= $una ?>"><?= substr($mon[0],0,$_SESSION['lsiz']) ?></a> <?= $uipst ?>
		</td>
		<td>
<?php
			if ($mon[6]){
				$lac = ($mon[10] > $mon[24])?'drd':'grn';
				$lmc = ($mon[11] > $mon[24])?'drd':'grn';
				$lvc = ($mon[12] > $mon[24])?'drd':'grn';
				$los = ($mon[8])?'drd':'grn';
				$las = ($mon[6] < (time() - $rrdstep) )?'drd':'grn';
				echo "\t\t\t$latlbl: <span class=\"$lac\" title=\"$laslbl\">$mon[10]ms </span>\n";
				echo "\t\t\t<span class=\"$lvc\" title=\"$avglbl\">$mon[12]ms</span>\n";
				echo "\t\t\t<span class=\"$lmc\" title=\"$maxlbl\">$mon[11]ms</span>\n";
				echo "\t\t\t<span class=\"gry\" title=\"$latlbl $mlvl[150]\">$mon[24]ms</span><br>\n";
				echo "\t\t\t$loslbl/OK: <span class=\"$los\">$mon[8]/$mon[9]</span><br>\n";
				echo "\t\t\t$laslbl: <span class=\"$las\">". date($_SESSION['timf'],$mon[6]) . "</span>\n";
			}
			echo "\t\t\t$ravst\n";
?>
		</td>
		<td class="<?= $bi ?> ctr">
			<a href="?tet=<?= $una ?>"><?=TestImg($mon[3],$mon[4],$mon[5]) ?></a><?= $testst ?><?= $lawst ?><?= $topst ?><?= $trsst ?> <span class="gry" title="<?= $nonlbl ?> <?= $rpylbl ?>"><?= $mon[23] ?><?= $nrpst ?></span><br>
		</td>
		<td>
<?php  if( isset($_GET['print']) ){ ?>
			<?= $mon[19] ?>
			<br>
			<?= $mon[20] ?>
<?php  }else{ ?>
			<form method="get">
				<input type="hidden" name="in[]" value="<?= $in[0] ?>">
				<input type="hidden" name="op[]" value="<?= $op[0] ?>">
				<input type="hidden" name="st[]" value="<?= $st[0] ?>">
				<input type="hidden" name="des" value="<?= $mon[0] ?>">
				<input type="text" name="dpt" class="l" value="<?= $mon[19] ?>" onfocus="select();" onchange="this.form.submit();" title="<?= $wrtlbl ?> <?= $namlbl ?>">
				<select size="1" name="dps" onchange="this.form.submit();" title="<?= $namlbl ?>">
					<option value=""><?= $sellbl ?>
					<option value="-">(<?= $nonlbl ?>)
<?php
			if($neb){
				foreach ($neb as $nen => $nif){
					echo "\t\t\t\t\t<option value=\"$nen\">".substr($nen,0,$_SESSION['lsiz'])."\n";
				}
			}
?>
				</select> <?= $depst[0] ?>

			</form>
<?php 	if( $mon[19] != '' or $mon[20] != '' ){ ?>
			<form method="get">
				<input type="hidden" name="in[]" value="<?= $in[0] ?>">
				<input type="hidden" name="op[]" value="<?= $op[0] ?>">
				<input type="hidden" name="st[]" value="<?= $st[0] ?>">
				<input type="hidden" name="des" value="<?= $mon[0] ?>">
				<input type="text" name="dpt2" class="l" value="<?= $mon[20] ?>" onfocus="select();" onchange="this.form.submit();" title="<?= $wrtlbl ?> <?= $namlbl ?>">
				<select size="1" name="dps2" onchange="this.form.submit();" title="<?= $namlbl ?>">
					<option value=""><?= $sellbl ?>
					<option value="-">(<?= $nonlbl ?>)
<?php
			if($neb){
				foreach ($neb as $nen => $nif){
					echo "\t\t\t\t\t<option value=\"$nen\">".substr($nen,0,$_SESSION['lsiz'])."\n";
				}
			}
?>
				</select> <?= $depst[1] ?>

			</form>
<?php 	} ?>
<?php } ?>
		</td>
		<td class="ctr">
			<a href="?in[]=alert&op[]==&st[]=<?= $mon[14] ?>">
<?php
if($mon[14] & 128){
	echo "\t\t\t\t<img src=\"img/16/brld.png\" title=\"Mail $rptlbl\">\n";
}elseif($mon[14] & 2){
	echo "\t\t\t\t<img src=\"img/16/mail.png\" title=\"Mail\">\n";
}elseif($mon[14] & 1){
	echo "\t\t\t\t<img src=\"img/16/bell.png\" title=\"$msglbl\">\n";
}else{
	echo "\t\t\t\t<img src=\"img/16/bcls.png\" title=\"$nonlbl Mail\">\n";
}
if($mon[14] & 4){
	echo "\t\t\t\t<img src=\"img/16/sms.png\" title=\"SMS\">\n";
}else{
	echo "\t\t\t\t<img src=\"img/16/bcls.png\" title=\"$nonlbl SMS\">\n";
}
?>
			</a>
			<?= $alst ?>

		</td>
		<td>
<?php
if($mon[15] or $mon[16] and !($mon[16]%2) ){
?>
			<img src="img/16/mail.png" title="<?= $fwdlbl ?>">
<?php
	if($mon[16] and !($mon[16]%2) ){
?>
			<a href="?in[]=eventlvl&op[]==&st[]=<?= $mon[16] ?>"><img src="img/16/<?= $mico[$mon[16]] ?>.png" title="<?= $mlvl[$mon[16]] ?>"></a>
<?php
	}
	if($mon[15]){
?>
			<a href="?in[]=eventfwd&op[]==&st[]=<?= urlencode($mon[15]) ?>"><?= $mon[15] ?></a>
<?php
	}
}

if($mon[16]%2 or $mon[17]){
?>
			<br><img src="img/16/bdis.png" title="<?= $dcalbl ?>">
<?php
	if($mon[16]%2){
?>
			<a href="?in[]=eventlvl&op[]==&st[]=<?= $mon[16] ?>"><img src="img/16/<?= $mico[$mon[16]-1] ?>.png" title="<?= $mlvl[$mon[16]-1] ?>"></a>
<?php
	}
	if($mon[17]){
?>
			<a href="?in[]=eventdel&op[]==&st[]=<?= urlencode($mon[17]) ?>"><?= $mon[17] ?></a>
<?php
	}
}

if($mon[18]){
?>
			<br><img src="img/16/ford.png" title="<?= $maxlbl ?> <?= $levlbl ?>">
			<a href="?in[]=eventmax&op[]==&st[]=<?= urlencode($mon[18]) ?>"><?= $mon[18] ?></a>
<?php
}
?>
			<?= $infst ?><?= $elst ?>
		</td>
		<td>
<?php
if( isset($_GET['del']) ){
	$query	= GenQuery('monitoring','d','','','',array('name'),array('='),array($mon[0]) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$dellbl OK</h5>";}
}else{
?>

			<span class="gry" title="CPU <?= $mlvl[200]?>"><?= $mon[25] ?>%</span><?= $cpast ?>

			<span class="gry" title="Mem <?= $mlvl[200]?>"><?= $mon[26] ?><?= ($mon[26] < 101)?'%':'KB' ?></span><?= $meast ?>

			<span class="gry" title="<?= $tmplbl ?> <?= $mlvl[200]?>"><?= $mon[27] ?>C</span><?= $teast ?>

			<span class="gry" title="PoE <?= $mlvl[150]?>"><?= $mon[28] ?>%</span><?= $powst ?>

			<span class="gry" title="ARP-Poison"><?= $mon[29] ?></span><?= $arpst ?>

			<span class="gry" title="Supply <?= $mlvl[200] ?>"><?= $mon[30] ?></span><?= $suast ?>

			<br>
			<span class="gry" title="notify"><?= $mon[22] ?></span> <?= $nfyst ?>

<?php
}
?>
		</td>
	</tr>

<?php
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}

	TblFoot("bgsub", 8, "$nnod Nodes, $ndev $tgtlbl $totlbl".(($ord)?", $srtlbl: $ord":"").(($lim)?", $limlbl: $lim":"") );
}elseif($_SESSION['opt']){
	include_once ("inc/librep.php");
	MonAvail($in[0],$op[0],$st[0],$_SESSION['lim'],'');
}
include_once ("inc/footer.php");
?>
