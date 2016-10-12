<?PHP

//===============================
// Monitoring related functions
//===============================

//===================================================================
// Return icon and title for an event class
function EvClass($c){

	global $cfglbl,$chglbl,$conlbl,$memlbl,$msglbl,$notlbl,$dsclbl,$acslbl,$dcalbl,$usrlbl,$cnclbl,$dsalbl;
	global $mlvl,$lodlbl,$trflbl,$tmplbl,$errlbl,$inblbl,$oublbl,$stco,$porlbl,$stalbl,$frelbl;

	$if = (strpos($c,'ln') === 0)?$cnclbl:$porlbl;
	if($c == 'dev'){
		return array('img/16/dev.png','Device Syslog');
	}elseif($c == 'node'){
		return array('img/16/node.png','Node Syslog');
	}elseif($c == 'trap'){
		return array('img/16/warn.png','SNMP Trap');
	}elseif($c == 'neda'){
		return array('img/16/bchk.png',"SNMP $acslbl OK");
	}elseif($c == 'nedc'){
		return array('img/16/cpu.png',"CPU $lodlbl");
	}elseif($c == 'nedd'){
		return array('img/16/radr.png',"$dsclbl");
	}elseif($c == 'nede'){
		return array('img/16/kons.png',"CLI $errlbl");
	}elseif($c == 'nedl'){
		return array('img/16/link.png',"$cnclbl");
	}elseif($c == 'nedj'){
		return array('img/16/ncfg.png',"IP $msglbl");
	}elseif($c == 'nedm'){
		return array('img/16/mem.png',$memlbl);
	}elseif($c == 'nedn'){
		return array('img/16/bcls.png',"$notlbl $dsclbl");
	}elseif($c == 'nedo'){
		return array('img/16/pcm.png','Module');
	}elseif($c == 'nedp'){
		return array('img/16/batt.png','PoE');
	}elseif($c == 'neds'){
		return array('img/16/sys.png',"System $chglbl");
	}elseif($c == 'nedt'){
		return array('img/16/temp.png',$tmplbl);
	}elseif($c == 'supp'){
		return array('img/16/mark.png','Supplies');
	}elseif($c == 'supe'){
		return array('img/p45.png',"Ethernet $porlbl $frelbl");
	}elseif($c == 'supd'){
		return array('img/plug.png',"xDSL $porlbl $frelbl");
	}elseif($c == 'secf'){
		return array('img/16/nods.png',"MAC Flood");
	}elseif($c == 'secj'){
		return array('img/16/net.png',"IP $chglbl");
	}elseif($c == 'secn'){
		return array('img/16/add.png',"$stco[10] Node");
	}elseif($c == 'secp'){
		return array('img/16/drop.png','ARP Poison');
	}elseif($c == 'secr'){
		return array('img/16/eyes.png','Rogue IP');
	}elseif($c == 'lnc'){
		return array('img/16/link.png',"$cnclbl $chglbl");
	}elseif( strpos($c,'ti') ){
		return array('img/16/bbup.png',"$if $inblbl $trflbl");
	}elseif( strpos($c,'to') ){
		return array('img/16/bbdn.png',"$if $oublbl $trflbl");
	}elseif( strpos($c,'ei') ){
		return array('img/16/brup.png',"$if $inblbl $errlbl");
	}elseif( strpos($c,'eo') ){
		return array('img/16/brdn.png',"$if $oublbl $errlbl");
	}elseif( strpos($c,'di') ){
		return array('img/16/bbu2.png',"$if $inblbl $dcalbl");
	}elseif( strpos($c,'do') ){
		return array('img/16/bbd2.png',"$if $oublbl $dcalbl");
	}elseif( strpos($c,'bi') ){
		return array('img/16/brc.png',"$if $inblbl Broadcast");
	}elseif( strpos($c,'op') ){
		return array('img/16/swit.png',"$if $stalbl $chglbl");
	}elseif( strpos($c,'ad') ){
		return array('img/16/bdis.png',"$if $dsalbl");
	}elseif($c == 'bugn'){
		return array('img/16/bug.png','Debug');
	}elseif($c == 'bugx'){
		return array('img/16/bug.png','Extended Debug');
	}elseif(strpos($c,'sp') === 0){
		return array('img/16/hat3.png','System Policy');
	}elseif(strpos($c,'cfg') === 0){
		return array('img/16/conf.png',$cfglbl);
	}elseif(strpos($c,'mon') === 0){
		return array('img/16/bino.png','Monitoring');
	}elseif(strpos($c,'usr') === 0){
		return array('img/16/user.png',$usrlbl);
	}elseif(strpos($c,'mas') === 0){
		return array('img/16/trgt.png',"Master $svclbl");
	}else{
		return array('img/16/say.png',$mlvl['30']);
	}
}

//===================================================================
// Return icon for an incident group
function IncImg($cat){

	if($cat == 1)		{return "add";}
	elseif($cat == 11)	{return "flas";}
	elseif($cat == 12)	{return "tool";}
	elseif($cat == 13)	{return "star";}
	elseif($cat == 14)	{return "ncon";}
	elseif($cat == 15)	{return "ele";}
	elseif($cat == 16)	{return "wthr";}
	elseif($cat == 17)	{return "dril";}
	elseif($cat < 20)	{return "home";}
	elseif($cat == 21)	{return "psup";}
	elseif($cat == 22)	{return "dev";}
	elseif($cat == 23)	{return "cubs";}
	elseif($cat == 24)	{return "cbox";}
	elseif($cat == 25)	{return "grph";}
	elseif($cat < 30)	{return "cinf";}
	elseif($cat == 31)	{return "ncfg";}
	elseif($cat == 32)	{return "conf";}
	elseif($cat == 33)	{return "eyes";}
	elseif($cat == 34)	{return "hat";}
	elseif($cat < 40)	{return "user";}
	else			{return "qmrk";}
}

//===================================================================
// Return bg color based on monitoring status
// $mn = -1 expects $al to be # of missing replies
function StatusBg($nd,$mn,$al,$bg='imga'){

	global $pause,$tim,$stco;

	if ($mn == -1){
		$out = $al * $pause;
		if( $out > 86400){
			return array("crit",(intval($out/8640)/10)." $tim[d]");
		}elseif( $out > 3600){
			return array("crit",(intval($out/360)/10)." $tim[h]");
		}elseif( $out > 600){
			return array("alrm",(intval($out/6)/10)." $tim[i]");
		}elseif( $out ){
			return array("warn","$out $tim[s]");
		}else{
			return array("good","OK");
		}
	}elseif ( $mn ){
		$partial = ($nd == $mn)?"":" part";
		if($al > 1){
			return array("crit$partial","$al $stco[200]");
		}elseif($al){
			return array("alrm$partial","1 $stco[200]");
		}else{
			return array("good$partial");
		}
	}else{
		return array ($bg,'');
	}
}

//===================================================================
// Generate Target status table
function StatusMon($loc,$srrd=0){

	global $link,$locsep,$bldsep,$dreg,$dcity,$dbuild,$dev,$yesterday,$pause,$debug,$nl;
	global $mlvl,$stco,$tgtlbl,$nonlbl,$monlbl,$srvlbl,$avglbl,$msglbl,$sumlbl,$latlbl;

	$query = GenQuery('monitoring','s',"count(*),sum(case when status = 0 then 0 else 1 end),max(lastok),avg(latency)",'','',array('location'),array('LIKE'),array($loc),array(),'LEFT JOIN devices USING (device)' );
	$res   = DbQuery($query,$link);
	$msta  = DbFetchRow($res);
	DbFreeResult($res);

	$mtit = "$monlbl, $msta[1]/$msta[0] $stco[200]";

	if(!$srrd or $srrd == 6){
		echo "<img src=\"img/32/bino.png\" title=\"$mtit\">\n";
	}else{
?>
	<a href="Devices-Graph.php?dv=Totals&if[]=mon&sho=1"><img src="inc/drawrrd.php?t=mon&s=<?= $srrd ?>" title="<?= $avalbl ?> <?= $gralbl ?> (<?= $mtit ?>)"></a>
	<a href="Devices-Graph.php?dv=Totals&if[]=msg&sho=1"><img src="inc/drawrrd.php?t=msg&s=<?= $srrd ?>" title="<?= $msglbl ?> <?= $sumlbl ?>"></a>
<?php
	}

	if( $msta[1] ){
		if( $msta[1] == 1 ){
			if(!$srrd or $srrd == 6){echo "<img src=\"img/32/foye.png\" title=\"1 $mlvl[200]\">";}
			if($_SESSION['vol']){echo "<audio src=\"inc/minor.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
		}elseif( $msta[1] < 10 ){
			if(!$srrd or $srrd == 6){echo "<img src=\"img/32/foor.png\" title=\"$msta[1] $mlvl[200]\">";}
			if($_SESSION['vol']){echo "<audio src=\"inc/major.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
		}else{
			if(!$srrd or $srrd == 6){echo "<img src=\"img/32/ford.png\" title=\"$msta[1] $mlvl[200]!\">";}
			if($_SESSION['vol']){echo "<audio src=\"inc/critical.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
		}
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/trgt.png"><br><?= $tgtlbl ?>

		</th>
		<th class="m">
			<img src="img/16/flag.png"><br><?= $mlvl['200'] ?>

		</th>
	</tr>
<?php
		$query = GenQuery('monitoring','s','name,class,test,status,device,location,inet_ntoa(monip),type,devgroup,icon','name','',array('status','location'),array('!=','LIKE'),array(0,$loc),array('AND'),'LEFT JOIN devices USING (device)' );
		$res   = DbQuery($query,$link);
		$row   = 0;
		while( ($t = DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			if( $t[2] != 'uptime' ){							# Add non uptime targets in topo table (adds dead snmp devices not tested with uptime again :-/ )
				if( strpos($t[5],$locsep.$locsep) === FALSE and substr_count($t[5],$locsep) > 1 ){
					$l  = explode($locsep, $t[5]);
					$dreg[$l[0]]['nl']++;
					$dcity[$l[0]][$l[1]]['nl']++;
					$b  = explode($bldsep, $l[2]);
					$dbuild[$l[0]][$l[1]][$b[0]]['nl']++;
					if( $t[1] == 'dev' ){
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['rk'] = $l[5];
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['ru'] = $l[6];
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['ip'] = $t[6];
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['ty'] = $t[7];
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['ic'] = $t[9];
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['mn'] = 1;
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['al'] = $t[3];
					}else{
						$dev[$b[0]][$l[3]][$l[4]][$t[4]]['nl']++;
					}
 				}
			}
			$ldst = ($t[1] == 'dev')?"Devices-Status.php?dev=".urlencode($t[0]):"Nodes-Status.php?in=ip&st=$t[6]";
			echo "\t<tr class=\"$bg\">\n";
			if( $srrd == 6 ){
				echo "\t\t<td class=\"$bi ctr xs\">\n\t\t\t".TestImg($t[2])."\n\t\t</td>\n";
			}else{
				echo "\t\t<td class=\"$bi ctr xs\">\n\t\t\t<a href=\"$ldst\">".TestImg($t[2])."</a>\n\t\t</td>\n";
			}
			echo "\t\t<td class=\"lft nw b\">\n\t\t\t".substr($t[0],0,$_SESSION['lsiz'])."\n\t\t</td>\n";
			echo "\t\t<td class=\"lft\">\n\t\t\t".substr($t[8],0,$_SESSION['lsiz'])."\n\t\t</td>\n";
			list($statbg,$stat) = StatusBg(1,-1,$t[3],$bi);
			echo "\t\t<td class=\"$statbg rgt b\">\n\t\t\t$stat\n\t\t</td>\n\t</tr>\n";
		}
		DbFreeResult($res);
?>
</table>

<?php
	}else{
		if($msta[2] > time() - 2*$pause){
			if(!$srrd or $srrd == 6){echo "<img src=\"img/16/bchk.png\" title=\"$monlbl $srvlbl $stco[100]\">\n";}
		}else{
			if(!$srrd or $srrd == 6){echo "<img src=\"img/32/bcls.png\" title=\"$nonlbl $monlbl $srvlbl\">";}
			if($_SESSION['vol']){echo "<audio src=\"inc/noserv.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
		}
		if($msta[3] and (!$srrd or $srrd == 6) ) echo "<p>\n<h3><img src=\"img/32/clock.png\" title=\"$avglbl $latlbl\">".intval($msta[3])."ms</h3>\n";
	}
}

//===================================================================
// Generate If status tables
function StatusIf($loc,$mode,$srrd){

	global $link,$rrdstep,$trfa,$trflbl,$errlbl,$inblbl,$oublbl,$tim,$yesterday;

	if($mode   == "brup"){
		$label = "$inblbl $errlbl";
		$query = GenQuery('interfaces','s','device,ifname,speed,iftype,dinerr','dinerr desc',$_SESSION['lim'],array('dinerr','iftype','location'),array('>','!=','LIKE'),array("$rrdstep",71,$loc),array('AND','AND'),'JOIN devices USING (device)');
	}elseif($mode  == "brdn"){
		$label = "$oublbl $errlbl";
		$query = GenQuery('interfaces','s','device,ifname,speed,iftype,douterr','douterr desc',$_SESSION['lim'],array('douterr','iftype','location'),array('>','!=','LIKE'),array("$rrdstep",71,$loc),array('AND','AND'),'JOIN devices USING (device)');
	}elseif($mode  == "bbup"){
		$label = "$inblbl $trflbl";
		$query = GenQuery('interfaces','s',"device,ifname,speed,iftype,dinoct/speed/$rrdstep*800",'dinoct/speed desc',$_SESSION['lim'],array('speed',"dinoct/speed/$rrdstep*800",'trafalert','location'),array('>','>','<','LIKE'),array(0,$trfa,100,$loc),array('AND','AND','AND'),'JOIN devices USING (device)');
	}elseif($mode  == "bbdn"){
		$label = "$oublbl $trflbl";
		$query = GenQuery('interfaces','s',"device,ifname,speed,iftype,doutoct/speed/$rrdstep*800",'doutoct/speed desc',$_SESSION['lim'],array('speed',"doutoct/speed/$rrdstep*800",'trafalert','location'),array('>','>','<','LIKE'),array(0,$trfa,100,$loc),array('AND','AND','AND'),'JOIN devices USING (device)');
	}elseif($mode  == "bdis"){
		$label = "Disabled $tim[t]";
		$query = GenQuery('interfaces','s','device,ifname,speed,iftype,ifstat,lastchg','lastchg desc',$_SESSION['lim'],array('ifstat','iftype','lastchg','location'),array('=','!=','>','LIKE'),array('0','53',$yesterday,$loc),array('AND','AND','AND'),'JOIN devices USING (device)');
	}
	$res	= DbQuery($query,$link);
	if($res){
		$nr = DbNumRows($res);
		if($nr){
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th colspan="3">
			<img src="img/16/port.png" title="Top <?= $_SESSION['lim'] ?>"><br>
			Interface
		</th>
		<th>
			<img src="img/16/<?= $mode ?>.png" title="<?= $label ?>"><br>
			<?= (substr($label,0,3)) ?>
		</th>
	</tr>
<?php
			$row = 0;
			$off = $_SESSION['brght'];
			while( ($r = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";$off-=15;}
				$row++;
				$bg3= sprintf("%02x",$off);
				$tb = ($mode == 'bbup' or $mode == 'bbdn')?($r[4]-$trfa)*2:$r[4]*5;
				if ($tb > 55){$tb = 55;}
				$rb = sprintf("%02x",$tb + $off);
				$t  = substr($r[0],0,strpos($r[0],'.') );
				$t  = (strlen($t) < 4)?$r[0]:$t;
				$ud = urlencode($r[0]);
				$ui = urlencode($r[1]);
				if($mode == "bdis"){
					$rb = $bg3;
					$stat = date($_SESSION['timf'],$r[5]);
				}elseif($mode == "brup" or $mode == "brdn"){
					$stat = DecFix($r[4]);
				}else{
					$stat = sprintf("%1.1f",$r[4])." %";
				}
				list($ifimg,$iftit) = Iftype($r[3]);
				echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi ctr xs\">\n\t\t\t<img src=img/$ifimg title=\"$iftit\">\n\t\t</td>\n\t\t<td class=\"lft\">\n";
				if($srrd == 6){
					echo "\t\t\t$t\n\t\t</td>\n\t\t<td class=\"lft\">\n\t\t\t$r[1]\n\t\t</td>\n";
				}else{
					echo "\t\t\t<a href=\"Devices-Status.php?dev=$ud&pop=on\">$t</a>\n\t\t</td>\n";
					echo "\t\t<td class=\"lft\">\n\t\t\t<a href=\"Devices-Interfaces.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=ifname&op[]==&st[]=$ui&col[]=imBL&col[]=ifname&col[]=alias&col[]=comment&col[]=poNS&col[]=gfNS&col[]=rdrNS\">$r[1]</a> ".DecFix($r[2])."\n\t\t</td>\n";
				}
				echo "\t\t<td class=\"b nw\" style=\"background-color:#$rb$rb$bg3\">\n\t\t\t$stat\n\t\t</td>\n\t</tr>\n";
			}
			echo "</table>\n\n";
		}elseif(!$srrd or $srrd == 6){
?>
<p>
<img src="img/32/<?= $mode ?>.png" title="<?= $label ?>"><img src="img/16/bchk.png" title="OK">
<?php
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
}

//===================================================================
// Generate PoE status
function StatusPoE($loc,$srrd){

	global $link,$totlbl,$lodlbl,$limlbl;

	if( !$srrd or $srrd == 6 ){
		$query	= GenQuery('devices','s','count(*),sum(totpoe)','','',array('totpoe','location'),array('>','LIKE'),array('0',$loc),array('AND') );
		$res	= DbQuery($query,$link);
		if($res){
			$m = DbFetchRow($res);
			if($m[0]){echo "<p>\n<h3><img src=\"img/32/batt.png\" title=\"$totlbl PoE $lodlbl, $m[0] Devices\">".DecFix($m[1])."W</h3>\n";}
			DbFreeResult($res);
		}else{
			print DbError($link);
		}
	}
}

//===================================================================
// Generate discovery status
function StatusDsc($loc,$srrd,$isiz){

	global $link,$rrdstep,$outlbl,$dsclbl;

	$query = GenQuery('devices','s',"count(*),sum(case when lastdis > ".(time() - 2*$rrdstep)." then 0 else 1 end)",'','',array('location'),array('LIKE'),array($loc) );
	$res   = DbQuery($query,$link);
	$dsta  = DbFetchRow($res);
	DbFreeResult($res);

	echo "<p>\n<h3><img src=\"img/$isiz/radr.png\" title=\"$dsclbl $outlbl\">$dsta[1]/$dsta[0]</h3>\n";
}

//===================================================================
// Generate cpu status table
function StatusCpu($loc,$srrd,$isiz){

	global $link,$tgtlbl,$lodlbl,$limlbl;

	$query = GenQuery('monitoring','s','name,cpu,cpualert','cpu desc',$_SESSION['lim'],array('cpu','location'),array('COL >','LIKE'),array('cpualert',$loc),array('AND'),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$nr = DbNumRows($res);
		if($nr){
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th colspan="2" class="nw">
			<img src="img/16/trgt.png" title="Top <?= $_SESSION['lim'] ?> <?= $lodlbl ?>"><br>
			<?= $tgtlbl ?>

		</th>
		<th class="nw">
			<img src="img/16/cpu.png"><br>
			<?= $lodlbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			$off = $_SESSION['brght'];
			while( ($t = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";$off-=15;}
				$row++;
				$lv  = $t[1]-$t[2];
				$bs = sprintf("%02x",$off);
				$hi  = sprintf("%02x",(($lv > 55)?55:$lv) + $off);
				$na  = substr($t[0],0,$_SESSION['lsiz']);
				$ud  = urlencode($t[0]);
				if($srrd == 6){
					echo "\t<tr bgcolor=\"#$hi$bs$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\">$na</td>\n\t\t<td>$t[1]%</td>\n\t</tr>\n";
				}else{
					echo "\t<tr bgcolor=\"#$hi$bs$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\"><a href=\"Devices-Status.php?dev=$ud\">$na</a></td>\n";
					echo "\t\t<td class=\"rgt b\" title=\"$limlbl $t[2]%\">$t[1]%</td>\n\t</tr>\n";
				}
			}
			echo "</table>\n";
		}else{
?>
<p>
<img src="img/<?= $isiz ?>/cpu.png" title="CPU <?= $lodlbl ?>">
<img src="img/16/bchk.png" title="OK">

<?php
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
}

//===================================================================
// Generate mem availabilty table
function StatusMem($loc,$srrd,$isiz){#TODO like cpu

	global $link,$tgtlbl,$limlbl,$frelbl,$memlbl;

	$aquery = GenQuery('monitoring','s','name,memcpu,memalert','memcpu desc',$_SESSION['lim'],array('memcpu/1024','memcpu','location'),array('COL <','>','LIKE'),array('memalert',100,$loc),array('AND','AND'),'LEFT JOIN devices USING (device)');
	$ares	= DbQuery($aquery,$link);
	$nar    = DbNumRows($ares);

	$pquery = GenQuery('monitoring','s','name,memcpu,memalert','memcpu desc',$_SESSION['lim'],array('memcpu','memcpu','memcpu','location'),array('COL <','>','<','LIKE'),array('memalert',0,100,$loc),array('AND','AND','AND'),'LEFT JOIN devices USING (device)');
	$pres	= DbQuery($pquery,$link);
	$npr    = DbNumRows($pres);

	if($nar or $npr){
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/trgt.png" title="Top <?= $_SESSION['lim'] ?> <?= $memlbl ?> <?= $frelbl ?>"><br>
			<?= $tgtlbl ?>

		</th>
		<th class="nw">
			<img src="img/16/mem.png"><br>
			<?= $frelbl ?>

		</th>
	</tr>
<?php
		$row = 0;
		$off = $_SESSION['brght'];
		while( ($t = DbFetchRow($ares)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";$off-=15;}
			$row++;
			$lv  = pow($ma[0]*1024/$t[1],8);
			$bs  = sprintf("%02x",$off);
			$hi  = sprintf("%02x",(($lv > 55)?55:$lv) + $off);
			$na  = substr($t[0],0,$_SESSION['lsiz']);
			$ud  = urlencode($t[0]);
			if($srrd == 6){
				echo "\t<tr bgcolor=\"#$hi$hi$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\">$na</td>\n\t\t<td>".DecFix($t[1])."B</td>\n\t</tr>\n";
			}else{
				echo "\t<tr bgcolor=\"#$hi$hi$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\"><a href=Devices-Status.php?dev=$ud>$na</a></td>\n";
				echo "\t\t<td class=\"rgt b\" title=\"$limlbl ".DecFix($t[2]*1024)."B\">".DecFix($t[1])."B</td>\n\t</tr>\n";
			}
		}
		$off = $_SESSION['brght'];
		while( ($t = DbFetchRow($pres)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";$off-=15;}
			$row++;
			$lv  = $t[1]-$m[1];
			$bs  = sprintf("%02x",$off);
			$hi  = sprintf("%02x",(($lv > 55)?55:$lv) + $_SESSION['brght']);
			$na  = substr($t[0],0,$_SESSION['lsiz']);
			$ud  = urlencode($t[0]);
			if($srrd == 6){
				echo "\t<tr bgcolor=\"#$hi$hi$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\">$na</td>\n\t\t<td>$t[1]%</td>\n\t</tr>\n";
			}else{
				echo "\t<tr bgcolor=\"#$hi$hi$bs\">\n\t\t<td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\"><a href=Devices-Status.php?dev=$ud>$na</a></td>\n";
				echo "\t\t<td class=\"rgt b\" title=\"$limlbl $t[2]%\">$t[1]%</td>\n\t</tr>\n";
			}
		}
		echo "</table>\n";
	}else{
?>
<p>
<img src="img/<?= $isiz ?>/mem.png" title="<?= $memlbl ?> <?= $frelbl ?>">
<img src="img/16/bchk.png" title="OK">

<?php
	}
	DbFreeResult($ares);
	DbFreeResult($pres);
}

//===================================================================
// Generate temperature status table
function StatusTmp($loc,$srrd,$isiz){

	global $link,$tmpa,$tgtlbl,$tmplbl,$limlbl;

	$query = GenQuery('monitoring','s','name,temp,tempalert','temp desc',$_SESSION['lim'],array('temp','location'),array('COL >','LIKE'),array('tempalert',$loc),array('AND'),'LEFT JOIN devices USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$nr = DbNumRows($res);
		if($nr){
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th colspan="2">
			<img src="img/16/trgt.png" title="Top <?= $_SESSION['lim'] ?> <?= $tmplbl ?>"><br>
			<?= $tgtlbl ?>
		</th>
		<th>
			<img src="img/16/temp.png"><br>
			<?= $tmplbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			$off = $_SESSION['brght'];
			while( ($t = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";$off-=15;}
				$row++;
				$lv  = $t[1]-$t[2];
				$bs  = sprintf("%02x",$off);
				$hi  = sprintf("%02x",(($lv > 55)?55:$lv) + $_SESSION['brght']);
				$na  = substr($t[0],0,$_SESSION['lsiz']);
				$ud  = urlencode($t[0]);
				if($srrd == 6){
					echo "\t<tr bgcolor=\"#$hi$bs$hi\"><td class=\"$bi ctr b\">$row</td>\n\t\t<td class=\"lft nw\">$na</td>\n\t\t<td>$t[1]C</td></tr>\n";
				}else{
					echo "\t<tr bgcolor=\"#$hi$bs$hi\">\n\t\t<td class=\"$bi b\">$row</td>\n\t\t<td class=\"lft nw\"><a href=\"Devices-Status.php?dev=$ud\">$na</a></td>\n";
					echo "\t\t<td class=\"rgt b\" title=\"$limlbl $t[2]C\">$t[1]C</td></tr>\n";
				}
			}
			echo "</table>\n";
		}else{
?>
<p>
<img src="img/<?= $isiz ?>/temp.png" title="<?= $tmplbl ?>">
<img src="img/16/bchk.png" title="OK">

<?php
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
}

//===================================================================
// Show unacknowledged incidents
function StatusIncidents($loc,$srrd,$opt=16){

	global $link,$levlbl,$inclbl,$sttlbl,$endlbl,$tgtlbl,$loclbl,$conlbl,$acklbl,$nonlbl,$mbak,$mico,$locsep;

	$ilnk = ($srrd == 6)?'mh.php':'Monitoring-Incidents.php?grp=1';

	if($opt == 1){											# Monitoring Master
		$query	= GenQuery('incidents','s','level,incidents.name,startinc,endinc,device,devip,location,contact,type,readcomm,testopt','id desc',$_SESSION['lim'],array('time','location'),array('=','LIKE'),array(0,$loc),array('AND'),'LEFT JOIN devices USING (device) LEFT JOIN monitoring USING (device)');
		$res	= DbQuery($query,$link);
		if($res){
			$nr = DbNumRows($res);
			if($nr){
?>
<p>
<table class="content">
	<tr class="bgsub">
		<th class="xs">
			<img src="img/16/idea.png"><br>
			<?= $levlbl ?>

		</th>
		<th>
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
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/home.png"><br>
			<?= $loclbl ?>

		</th>
		<th>
			<img src="img/16/umgr.png"><br>
			<?= $conlbl ?>

		</th>
	</tr>
<?php
				$row = 0;
				while( ($i = DbFetchRow($res)) ){
					if ($row % 2){$bg = "txta"; $bi = "imga";$off="b8";}else{$bg = "txtb"; $bi = "imgb";$off="c8";}
					$row++;
					$ut  = urlencode($i[1]);
					$ud  = urlencode($i[4]);
					TblRow($bg);
					echo "\t\t<td class=\"".$mbak[$i[0]]." ctr\"><img src=\"img/16/" . $mico[$i[0]] . ".png\" title=\"" . $mlvl[$i[0]] . "\"></td>\n";
					echo "\t\t<td class=\"nw\"><a href=\"$i[9]://$i[5]/$i[10]Monitoring-Setup.php?in[]=name&op[]=%3D&st[]=$ut\" target=\"window\">".substr($i[1],0,$_SESSION['lsiz'])."</a></td>\n";
					echo "\t\t<td class=\"nw\">".date($_SESSION['timf'],$i[2])."</td><td ".(($i[3])?">".date($_SESSION['timf'],$i[3]):"class=\"warn\">-")."</td>\n";
					echo "\t\t<td><a href=\"$i[9]://$i[5]/$i[10]Monitoring-Incidents.php?grp=1\" target=\"window\">".substr($i[4],0,$_SESSION['lsiz'])."</a></td>\n";
					$l = explode($locsep, $i[6]);
					echo "\t\t<td>".substr("$l[1], $l[0]",0,$_SESSION['lsiz'])."</td>\n\t\t<td>$i[7]</td>\n\t</tr>\n";
				}
				echo "</table>\n";
				if($nr == 1){
					if($_SESSION['vol']){echo "<audio src=\"inc/minor.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
				}elseif($nr < 10){
					if($_SESSION['vol']){echo "<audio src=\"inc/major.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
				}else{
					if($_SESSION['vol']){echo "<audio src=\"inc/critical.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}
				}
			}else{
?>
<p>
<img src="img/<?= $opt ?>/bomb.png" title="<?= $inclbl ?>">
<img src="img/16/bchk.png" title="<?= $nonlbl ?>">

<?php
			}
		}else{
			print DbError($link);
		}
	}else{
		$ico = "16/bchk";
		$inct = $nonlbl;
		$query	= GenQuery('incidents','s','count(*)','','',array('time','location'),array('=','LIKE'),array(0,$loc),array('AND'),'LEFT JOIN devices USING (device)');
		$res	= DbQuery($query,$link);
		if($res){
			$ni = DbFetchRow($res);
			$inct = $ni[0];
			if($ni[0] == 1){
				$ico = "$opt/foye";
			}elseif($ni[0] > 10){
				$ico = "$opt/ford";
			}elseif($ni[0]){
				$ico = "$opt/foor";
			}
		}else{
			print DbError($link);
		}
?>
<p>
<a href="<?= $ilnk ?>">
<img src="img/<?= $opt ?>/bomb.png" title="<?= $inclbl ?>">
<img src="img/<?= $ico ?>.png" title="<?= $acklbl ?>: <?= $inct ?>">
</a>
<p>

<?php
	}
	DbFreeResult($res);
}

//===================================================================
// Displays Events based on query (mod 0=full, 1=full-joindev 2=full-master 3=small, 4=mobile)
function Events($lim,$in,$op,$st,$co,$mod=0){

	global $link,$bg,$bi,$mico,$mbak,$mlvl,$noiplink;
	global $gralbl,$lstlbl,$levlbl,$timlbl,$tgtlbl,$srclbl,$monlbl,$msglbl,$stalbl,$cfglbl,$srvlbl;
	global $inflbl,$cmdlbl,$nonlbl,$clalbl,$acklbl,$limlbl;

	if($mod){											# Need to join dev due to 'filter' or 'view'
		$query = GenQuery('events','s','id,level,time,source,info,class,device,type,readcomm','id desc',$lim,$in,$op,$st,$co,'LEFT JOIN devices USING (device)');
	}else{												# No join is faster
		$query = GenQuery('events','s','id,level,time,source,info,class,device','id desc',$lim,$in,$op,$st,$co);
	}
	$res	= DbQuery($query,$link);
	if($res){
		$nmsg = DbNumRows($res);
		if($nmsg){
			$row  = 0;
			if($mod > 2){
				if($mod == 3){
?>
<table class="content">
	<tr>
		<th class="s">
			<img src="img/16/idea.png"><br>
			<?= $levlbl ?>

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $timlbl ?>

		</th>
		<th>
			<img src="img/16/say.png"><br>
			<?= $srclbl ?>

		</th>
		<th>
			<img src="img/16/find.png"><br>
			<?= $inflbl ?>

		</th>
	</tr>
<?php
				}
				while( ($m = DbFetchRow($res)) ){
					if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
					$row++;
					$time = date($_SESSION['timf'],$m[2]);
					$fd   = urlencode(date("m/d/Y H:i:s",$m[2]));
					$usrc = urlencode($m[3]);
					$ssrc = substr($m[3],0,$_SESSION['lsiz']);
					$sinf = (strlen($m[4]) > 60)?substr($m[4],0,60)."...":$m[4];
					$mtit = ($m[1]>29)?$mlvl[$m[1]]:$mlvl[$m[1]*10].", $acklbl OK";
					if($mod == 3){
						TblRow($bg);
						echo "\t\t<td class=\"".$mbak[$m[1]]." ctr\">\n\t\t\t<a href=\"Monitoring-Events.php?lvl=$m[1]\"><img src=\"img/16/". $mico[$m[1]] .".png\" title=\"$mtit\"></a>\n\t\t</td>\n";
						echo "\t\t<td>\n\t\t\t<a href=\"Monitoring-Events.php?in[]=time&op[]==&st[]=$fd\">$time</a>\n\t\t</td>\n";
						echo "\t\t<td class=\"nw\">\n\t\t\t<a href=\"Monitoring-Events.php?in[]=source&op[]==&st[]=$usrc\">$ssrc</a></td>\n\t\t<td>$sinf\n\t\t</td>\n\t</tr>\n";
					}else{								# Mobile mode, mh.php
						echo "\t<tr class=\"".$mbak[$m[1]]."\">\n\t\t<td class=\"nw\">\n\t\t\t$ssrc\n\t\t</td>\n\t\t<td>$time</td>\n\t\t<td>$sinf\n\t\t</td>\n\t</tr>\n";
					}
				}
				echo "</table>\n";
			}else{
?>
<table class="content">
	<tr class="bgsub">
		<th class="xs">
			<img src="img/16/key.png"><br>
			Id
		</th>
		<th class="xs">
			<img src="img/16/idea.png" title="30=<?= $mlvl['30'] ?>,50=<?= $mlvl['50'] ?>, 100=<?= $mlvl['100'] ?>, 150=<?= $mlvl['150'] ?>, 200=<?= $mlvl['200'] ?>, 250=<?= $mlvl['250'] ?>"><br>
			<?= $levlbl ?>

		</th>
		<th>
			<img src="img/16/clock.png"><br>
			<?= $timlbl ?>

		</th>
		<th>
			<img src="img/16/say.png" title="<?= $monlbl ?> <?= $tgtlbl ?> || IP (<?= $msglbl ?> <?= $levlbl ?> < 50)"><br>
			<?= $srclbl ?>

		</th>
		<th class="xs">
			<img src="img/16/abc.png" title="<?= $msglbl ?> <?= $clalbl ?>:<?= $cmdlbl ?>"><br>
			<?= $clalbl ?>

		</th>
		<th>
			<img src="img/16/find.png"><br>
			<?= $inflbl ?>

		</th>
	</tr>
<?php
				while( ($m = DbFetchRow($res)) ){
					if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
					$row++;
					$time = date($_SESSION['timf'],$m[2]);
					$fd   = urlencode(date("m/d/Y H:i:s",$m[2]));
					$usrc = urlencode($m[3]);
					$utgt = urlencode($m[6]);
					$mtit = ($m[1]>29)?$mlvl[$m[1]]:$mlvl[$m[1]*10].", $acklbl OK";
					list($ei,$et) = EvClass($m[5]);
					TblRow($bg);
					echo "\t\t<td>\n\t\t\t<a href=\"Monitoring-Events.php?".(($m[1]>29)?"ak=$m[0]\" title=\"$acklbl":"in[]=id&op[]==&st[]=$m[0]")."\">$m[0]</a>\n\t\t</td>\n";
					echo "\t\t<td class=\"".$mbak[$m[1]]." ctr\">\n\t\t\t<a href=\"Monitoring-Events.php?in[]=level&op[]==&st[]=$m[1]&co[]=AND&in[]=$in[0]&op[]=$op[0]&st[]=".urlencode($st[0])."\"><img src=\"img/16/". $mico[$m[1]] .".png\" title=\"$mtit\"></a>\n\t\t</td>\n";
					echo "\t\t<td class=\"nw\">\n\t\t\t<a href=\"Monitoring-Events.php?in[]=time&op[]==&st[]=$fd\">$time</a>\n\t\t</td>\n";
					if($mod == 1 and $m[7] == 'NeDi Agent'){
						$agnt = "$m[8]://$utgt/";
						$alnk = "on <a href=\"Devices-Status.php?dev=$utgt\">$utgt</a> ";
					}else{
						$agnt  = '';
						$alnk  = '';
					}
					echo "\t\t<td>\n\t\t\t<a href=\"Monitoring-Events.php?in[]=source&op[]==&st[]=$usrc\"><strong>$m[3]</strong></a> $alnk\n\t\t</td>\n";

					$action = "<a href=\"${agnt}Devices-Status.php?dev=$utgt\"><img src=\"$ei\" title=\"$et ($m[5]), Device $stalbl\"></a>";
					if($m[5] == "node"){						# Syslog from a node
						$action = "<a href=\"${agnt}Nodes-Status.php?in=dns&st=$m[3]\"><img src=\"$ei\" title=\"$et ($m[5]), Node $lstlbl\"></a>";
					}elseif($m[3] == "NeDi"){					# Not related to a dev or node!
						$action = "<a href=\"${agnt}System-Services.php\"><img src=\"$ei\" title=\"$et ($m[5]), NeDi $srvlbl\"></a>";
					}elseif($m[5] == "cfgn" or $m[5] == "cfgc"){			# New config or changes
						$action =  "<a href=\"Devices-Config.php?shc=$usrc\"><img src=\"$ei\" title=\"$et ($m[5]), Device $cfglbl\"></a>";
					}elseif($m[1] == 30){						# Node Syslog
						$action = "<a href=\"${agnt}Nodes-List.php?in[]=nodip&op[]==&st[]=$m[3]\"><img src=\"$ei\" title=\"$et ($m[5]), Node $lstlbl\"></a>";
					}
					echo "\t\t<td class=\"$bi ctr\">\n\t\t\t$action\n\t\t</td>\n\t\t<td>\n\t\t\t";
					if($noiplink or isset($_GET['print'])){
						$info = preg_replace('/[\s:]([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(\s|:|,|$)/', " <span class=\"blu\">$1</span> ", $m[4]);
						echo preg_replace('/[\s:]([0-9a-f]{4}[\.-]?[0-9a-f]{4}[\.-]?[0-9a-f]{4}|[0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2})(\s|$)/', "\n\t\t\t<span class=\"mrn\">$1</span> ", $info);
					}else{
						$info = preg_replace('/[\s:]([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(\s|:|,|$)/', " <span class=\"blu\">$1</span>
			<a href=\"Nodes-List.php?in[]=nodip&op[]=%3D&st[]=$1\"><img src=\"img/16/nods.png\" title=\"Nodes $lstlbl\"></a>
			<a href=\"Nodes-Toolbox.php?Dest=$1\"><img src=\"img/16/tool.png\" title=\"Lookup\"></a>
			<a href=\"?in[]=info&op[]=~&st[]=$1\"><img src=\"img/16/bell.png\" title=\"Monitoring-Events\"></a> ", $m[4]);
						echo preg_replace('/[\s:]([0-9a-f]{4}[\.-]?[0-9a-f]{4}[\.-]?[0-9a-f]{4}|[0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2}[-:][0-9a-f]{2})(\s|$)/', "\n\t\t\t<span class=\"mrn\">$1</span><a href=\"Nodes-Status.php?st=$1\"><img src=\"img/16/node.png\" title=\"Node $stalbl\"></a>\n", $info);
					}
					echo "\n\t\t</td>\n\t</tr>\n";
				}
				TblFoot("bgsub", 6, "$row $msglbl".(($lim)?", $limlbl: $lim":"") );
			}
		}else{
			echo "<p>\n<h5>$nonlbl $msglbl</h5>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
}

//===================================================================
// Generate device metainfo for topology based device tables
function TopoTable($reg="",$cty="",$bld="",$flr="",$rom="",$opt=0){

	global $link,$dev,$noloc,$yesterday,$dreg,$dcity,$dbuild,$locsep,$bldsep,$now,$retire;

	$dreg = array();
	$loc  = TopoLoc($reg,$cty,$bld,$flr,$rom);

	if($opt == 1){
		$query	= GenQuery('devices','s','device,inet_ntoa(devip),type,location,contact,cliport,icon,size,stack','','',array('location'),array('LIKE'),array($loc) );
	}elseif($opt == 2){
		$query	= GenQuery('events','g','source','','',array('level','time','location'),array('=','>','LIKE'),array(250,$yesterday,$loc),array('AND','AND'),'LEFT JOIN devices USING (device)' );
		$res	= DbQuery($query,$link);
		while( ($r = DbFetchRow($res)) ){
			$evmax[$r[0]] = $r[1];
		}
		DbFreeResult($res);
		if($_SESSION['vol'] and isset($evmax) ){echo "<audio src=\"inc/minor.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio for eventmax</audio>\n";}
		$query	= GenQuery('devices','s','devices.device,inet_ntoa(devip),type,location,contact,cliport,icon,size,stack,test,status,latency,latwarn','','',array('snmpversion','location'),array('>','LIKE'),array('0',$loc),array('AND'),'LEFT JOIN monitoring on (devices.device = monitoring.name)' );
	}else{
		$query	= GenQuery('devices','s','device,inet_ntoa(devip),type,location,contact,cliport,icon,size,stack','','',array('snmpversion','location'),array('>','LIKE'),array('0',$loc),array('AND') );
	}
	$res	= DbQuery($query,$link);
	if($res){
		while( ($d = DbFetchRow($res)) ){
			$mn = ($opt == 2 and isset($d[9]) and $d[9] != 'none')?1:0;
			$al = ($opt == 2 and $d[10])?1:0;
			$em = ( isset($evmax) and array_key_exists($d[0],$evmax) )?$evmax[$d[0]]:0;
			if( strpos($d[3],$locsep.$locsep) === FALSE and substr_count($d[3],$locsep) > 1 ){		# At least 2 non-adjacent locseps are considered valid
				$l  = explode($locsep, $d[3]);
				$b  = explode($bldsep, $l[2]);
				if( $mn ){
					$dreg[$l[0]]['mn']++;
					$dcity[$l[0]][$l[1]]['mn']++;
					$dbuild[$l[0]][$l[1]][$b[0]]['mn']++;
				}
				if( $al ){
					$dreg[$l[0]]['al'] += $al;
					$dcity[$l[0]][$l[1]]['al'] += $al;
					$dbuild[$l[0]][$l[1]][$b[0]]['al'] += $al;
				}
				if( $em ){
					$dreg[$l[0]]['em'] += $em;
					$dcity[$l[0]][$l[1]]['em'] += $em;
					$dbuild[$l[0]][$l[1]][$b[0]]['em'] += $em;
				}
				$dreg[$l[0]]['nd']++;
				$dcity[$l[0]][$l[1]]['nd']++;
				$dbuild[$l[0]][$l[1]][$b[0]]['nd']++;
				if($b[1]) $dbuild[$l[0]][$l[1]][$b[0]]['sb'][$b[1]]++;
				if( $bld ){
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['rk'] = $l[5];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['ru'] = $l[6];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['ip'] = $d[1];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['ty'] = $d[2];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['co'] = $d[4];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['po'] = $d[5];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['ic'] = $d[6];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['sz'] = $d[7];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['sk'] = $d[8];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['mn'] = $mn;
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['al'] = $d[10];
					$dev[$l[2]][$l[3]][$l[4]][$d[0]]['em'] = $em;
				}
			}else{
				$noloc[$d[0]]['ip'] = $d[1];
				$noloc[$d[0]]['ty'] = $d[2];
				$noloc[$d[0]]['lo'] = $d[3];
				$noloc[$d[0]]['co'] = $d[4];
				$noloc[$d[0]]['po'] = $d[5];
				$noloc[$d[0]]['ic'] = $d[6];
				$noloc[$d[0]]['mn'] = $mn;
				$noloc[$d[0]]['al'] = $al;
				$noloc[$d[0]]['em'] = $em;
			}
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
}

//===================================================================
// Generate world table
function TopoRegs($siz=0){

	global $link,$debug,$map,$pop,$manlbl,$dreg,$locsep,$bg2,$notlbl,$uptlbl,$netlbl,$addlbl,$loclbl,$porlbl,$frelbl,$stco,$poplbl,$errlbl;

	echo "<h2>$manlbl $netlbl</h2>\n\n";
	echo "<table class=\"content fixed\">\n\t<tr>\n";

	$col = 0;
	ksort($dreg);
	foreach (array_keys($dreg) as $r){
		$ur = urlencode($r);
		$ul = urlencode("$r$locsep%");
		$nd = $dreg[$r]['nd'];
		$mn = isset( $dreg[$r]['mn'] ) ? $dreg[$r]['mn'] : 0;
		$al = isset( $dreg[$r]['al'] ) ? $dreg[$r]['al'] : 0;
		$nl = isset( $dreg[$r]['nl'] ) ? $dreg[$r]['nl'] : 0;
		list($statbg,$stat) = StatusBg($nd,$mn,$al,'imga');
		if( $col == $_SESSION['col'] ){
			$col = 0;
			echo "\t</tr>\n	<tr>\n";
		}
	        echo "\t\t<td class=\"".(($map == 1)?'':'ctr')." btm $statbg\">\n";
	        if($al) echo "\t\t\t<div class=\"frgt b\"><img src=\"img/16/dev.png\" title=\"Devices $stco[200]\">$al</div>\n";
	        if($nl) echo "\t\t\t<div class=\"genpad alrm frgt b\"><img src=\"img/16/wlar.png\" title=\"$notlbl $uptlbl\">$nl</div>\n";
	        $mstat = ($mn)?($mn-$al)."/$nd $stco[100], $stat":"";
		if($siz){
			EmEvents( $dreg[$r]['em'] );
			echo "\t\t\t<a href=\"?reg=$ur\"><img src=\"img/32/glob.png\" title=\"$mstat\"></a><br>".substr($r,0,$_SESSION['lsiz'])."\n";
		}else{
			$qmap = $ur;
			list($w,$h) = MapSize( $_SESSION['gsiz'] );
			$rp   = preg_replace('/\W/','', $r);
			EmEvents( $dreg[$r]['em'],"in[]=location&op[]=LIKE&st[]=$ul" );
			if($rp and $map > 1){
				if( !file_exists("topo/$rp")  and !isset($_SESSION['snap']) ) mkdir("topo/$rp");
				if($map > 2){
					$loced = '';
					$ns = $ew = 0;
					$query	= GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,'',''),array('AND','AND'));
					$res	= DbQuery($query,$link);
					if (DbNumRows($res)){
						list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
						echo "\t\t\t$des<br>\n";
					}else{
						$loced = "\t\t\t<a href=\"Assets-Loced.php?reg=$ur\"><img src=\"img/16/ncfg.png\" title=\"$addlbl\"></a>\n";
					}
					if($ns and $ew){
						$ns /= 10000000;
						$ew /= 10000000;
						$qmap= "$ns,$ew";
					}
					if( isset($_SESSION['snap']) ){
						$cache = "img/32/glob.png";
					}else{
						$cache = "topo/$rp/osm.png";
						if( !file_exists($cache) and ini_get('allow_url_fopen') ){
							if(!$ns and !$ew){
								$url = "http://nominatim.openstreetmap.org/search?format=json&limit=2&q=$qmap";
								$geo = json_decode( file_get_contents($url), TRUE);
								if($debug){echo "<div class=\"textpad code pre ".(($geo)?'good':'alrm')."\"><strong>$url</strong><p>";print_r($geo); echo '</div>';}
								if($geo and $geo[0][lat]){
									$qmap= $geo[0][lat].",".$geo[0][lon];
								}
							}
							$cfurl = "http://staticmap.openstreetmap.de/staticmap.php?center=$qmap&zoom=5&size=240x160&maptype=osmarenderer&markers=$qmap";
							$cdata = file_get_contents($cfurl);
							$csize = ($cdata)?DecFix(file_put_contents($cache, $cdata)):$errlbl;
							if($debug){echo "<div class=\"textpad code pre ".(($csize)?'good':'alrm')."\">$cfurl\n$cache: $csize</div>";}
						}
					}
					echo "\t\t\t<a href=\"?reg=$ur&map=$map&pop=$pop\"><img src=\"$cache\" width=\"$w\" title=\"$mstat\" style=\"border:1px solid black\"></a><br>\n";
					if( !isset($_GET['print']) and !$mn ) echo "$loced\t\t\t<a href=\"http://nominatim.openstreetmap.org/search.php?q=$qmap\" target=\"window\"><img src=\"img/16/osm.png\" title=\"Openstreetmap\"></a>\n";
				}else{
					echo "\t\t\t<a href=\"?reg=$ur&map=$map&pop=$pop\"><img src=\"inc/drawmap.php?st[]=$ul&dim=${w}x$h&lev=2&pos=".(($mn)?'a':'d')."\" title=\"$mstat\" class=\"genpad\"></a><br>\n";
					if( !isset($_GET['print']) and !$mn ) echo "\t\t\t<a href=\"Topology-Map.php?st[]=$ul&tit=$ur+Map&lev=2&fmt=png\"><img src=\"img/16/paint.png\" title=\"Topology-Map\"></a>\n";
				}
			}elseif($map){
				echo "\t\t\t<a href=\"?reg=$ur&pop=$pop\"><img src=\"img/16/glob.png\" title=\"$mstat\"></a>\n";				
			}else{
				echo "\t\t\t<a href=\"?reg=$ur&pop=$pop\"><img src=\"img/32/glob.png\" title=\"$mstat\"></a><br>\n";
			}
			if( isset($_GET['print']) ){
				echo "<strong>".substr($r,0,$_SESSION['lsiz'])."</strong>";
			}else{
				echo "\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><strong>".substr($r,0,$_SESSION['lsiz'])."</strong></a>\n";
			}
			if( $pop ){
				echo "\t\t\t<br><div class=\"genpad flft lft\">\n";
				echo "\t\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul&co[]=AND&in[]=snmpversion&op[]=>&st[]=0\"><img src=\"img/16/dev.png\" title=\"Devices\">$nd</a>\n";
				if( $pop > 1 ){
					$myp = NodPop( array('location'),array('LIKE'),array("$r$locsep%"),array() );
					if($myp) echo "\t\t\t\t<a href=\"Nodes-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><img src=\"img/16/nods.png\" title=\"$loclbl $poplbl\">$myp</a><br>\n";
					if( $pop == 3){
						IfFree(5,'eth','location','LIKE',"$r$locsep%");
						IfFree(5,'dsl','location','LIKE',"$r$locsep%");
					}
				}
				echo "\t\t\t</div>\n";
			}
		}
		echo "\t\t</td>\n";
	        $col++;
	}
	echo "	</tr>\n</table>\n";
}

//===================================================================
// Generate region table
function TopoCities($r,$siz=0){

	global $link,$map,$pop,$debug,$dcity,$locsep,$bg2,$notlbl,$uptlbl,$netlbl,$errlbl,$tmplbl,$loclbl,$porlbl,$frelbl,$addlbl,$stco,$poplbl,$igrp,$nonlbl,$inflbl,$errlbl;

	$ur  = urlencode($r);

	$query	= GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,'',''),array('AND','AND'));
	$res	= DbQuery($query,$link);
	if (DbNumRows($res)){
		list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
		echo "<h2>$r - $des</h2>\n\n";
	}else{
		echo "<h2>$r $netlbl</h2>\n\n";
	}
	echo "<table class=\"content fixed\"><tr>\n";

	$col = 0;
	ksort($dcity[$r]);
	foreach (array_keys($dcity[$r]) as $c){
		$uc = urlencode($c);
		$ul = urlencode("$r$locsep$c$locsep%");
		$nd = $dcity[$r][$c]['nd'];
		$ci = CtyImg($dcity[$r][$c]['nd']);
		$mn = isset( $dcity[$r][$c]['mn']) ? $dcity[$r][$c]['mn'] : 0;
		$al = isset( $dcity[$r][$c]['al']) ? $dcity[$r][$c]['al'] : 0;
		$nl = isset( $dcity[$r][$c]['nl']) ? $dcity[$r][$c]['nl'] : 0;
		list($statbg,$stat) = StatusBg($nd,$mn,$al,'imga');
		if ($col == $_SESSION['col']){
			$col = 0;
			echo "\t</tr>\n\t<tr>\n";
		}
		echo "\t\t<td class=\"".(($map == 1)?'btm':'ctr btm')." $statbg\">\n";
	        if($al) echo "\t\t\t<div class=\"frgt b\"><img src=\"img/16/dev.png\" title=\"Devices $stco[200]\">$al</div>\n";
	        if($nl) echo "\t\t\t<div class=\"genpad alrm frgt b\"><img src=\"img/16/wlar.png\" title=\"$notlbl $uptlbl\">$nl</div>\n";
		$mstat = ($mn)?($mn-$al)."/$nd $stco[100] $stat":"";
		if($siz){
			echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&pop=$pop\"><img src=\"img/$ci.png\" title=\"$mstat\"></a><br>".substr($c,0,$_SESSION['lsiz'])."\n";
		}else{
			$qmap = "$uc,$ur";
			list($w,$h) = MapSize( $_SESSION['gsiz'] );
			$cp   = preg_replace('/\W/','', $r).'/'.preg_replace('/\W/','', $c);
			EmEvents( $dcity[$r][$c]['em'],"in[]=location&op[]=LIKE&st[]=$ul" );
			if($cp and $map > 1){
				if($map > 2){
					if( !file_exists("topo/$cp") and !isset($_SESSION['snap']) ) mkdir("topo/$cp", 0755, true);
					$ns = $ew = 0;
					$query	= GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,$c,''),array('AND','AND'));
					$res	= DbQuery($query,$link);

					if (DbNumRows($res)){
						list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
						echo "$des<br>";
						$loced = '';
					}else{
						$loced = "\t\t\t<a href=\"Assets-Loced.php?reg=$ur&cty=$uc\"><img src=\"img/16/ncfg.png\" title=\"$addlbl\"></a>\n";
					}
					$cache = "topo/$cp/osm.png";
					$cachd = file_exists($cache);					# OSM is cached
					if($ns and $ew){
						$ns /= 10000000;
						$ew /= 10000000;
						$qmap= "$ns,$ew";
					}elseif( ($map == 4 or !$cachd) and ini_get('allow_url_fopen') ){	# Weather and OSM only works with coordinates...
						$url = "http://nominatim.openstreetmap.org/search?format=json&limit=2&q=$qmap";
						$geo = json_decode( file_get_contents($url), TRUE);
						if($debug){echo "<div class=\"textpad code pre ".(($geo)?'good':'alrm')."\"><strong>$url</strong><p>";print_r($geo); echo '</div>';}
						if($geo and $geo[0][lat]){
							$ns = $geo[0][lat];
							$ew = $geo[0][lon];
							$qmap= "$ns,$ew";
						}
					}

					if($map == 4 and ini_get('allow_url_fopen') ){
						if($_SESSION['far']){
							$mod = 'imperial';
							$teu = 'F';
							$wiu = 'mph';
						}else{
							$mod = 'metric';
							$teu = 'C';
							$wiu = 'm/s';
						}
						$url = "http://api.openweathermap.org/data/2.5/weather?lat=$ns&lon=$ew&units=$mod";
						$wtr = json_decode( file_get_contents($url), TRUE);
						if($debug){echo "<div class=\"textpad code pre ".(($wtr)?'good':'alrm')."\"><strong>$url</strong><p>";print_r($wtr); echo '</div>';}
						if( is_array($wtr) ){
							echo "\t\t\t<a href=\"http://openweathermap.org/city/".$wtr[id]."\" target=\"window\"><img src=\"http://openweathermap.org/img/w/".$wtr[weather][0][icon].".png\" title=\"".$wtr[weather][0][description]."\"></a>\n";
							echo "<img src=\"img/16/temp.png\" title=\"$tmplbl\">".round($wtr[main][temp])."$teu <img src=\"img/16/drop.png\" title=\"Humidity\">".$wtr[main][humidity]."% <img src=\"img/16/fan.png\" title=\"Wind\">".round($wtr[wind][speed])."$wiu<br>";
						}else{
							echo "$nonlbl $igrp[16] $inflbl<br>\n";
						}
					}else{
						if($debug){echo "<div class=\"textpad code pre warn\">Skip $igrp[16]: map=$map, allow_url_fopen ".ini_get('allow_url_fopen').'</div>';}
					}
					if( isset($_SESSION['snap']) ){
						$cache = "img/$ci.png";
					}elseif( !$cachd and ini_get('allow_url_fopen') ){
						$cfurl = "http://staticmap.openstreetmap.de/staticmap.php?center=$qmap&zoom=12&size=240x160&maptype=osmarenderer&markers=$qmap";
						$cdata = file_get_contents($cfurl);
						$csize = ($cdata)?DecFix(file_put_contents($cache, $cdata)):$errlbl;
						if($debug){echo "<div class=\"textpad code pre ".(($csize)?'good':'alrm')."\">$cfurl\n$cache: $csize</div>\n";}
					}
					echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&map=$map&pop=$pop\"><img src=\"$cache\" width=\"$w\" title=\"$mstat\" style=\"border:1px solid black\"></a><br>\n";
					if( !isset($_GET['print'])) echo "\t\t\t$loced <a href=\"http://nominatim.openstreetmap.org/search.php?q=$qmap\" target=\"window\"><img src=\"img/16/osm.png\" title=\"Openstreetmap\"></a>\n";
				}else{
					echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&map=$map&pop=$pop\"><img src=\"inc/drawmap.php?st[]=$ul&dim=${w}x$h&lev=3&pos=".(($mn)?'a':'d')."\" title=\"$mstat\" class=\"genpad\"></a><br>\n";
					if( !isset($_GET['print']) and !$mn ) echo "\t\t\t<a href=\"Topology-Map.php?st[]=$ul&tit=$uc+Map&lev=3&fmt=png\"><img src=\"img/16/paint.png\" title=\"Topology-Map\"></a>\n";
				}
			}elseif($map){
				echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&pop=$pop\"><img src=\"img/16/fort.png\" title=\"$mstat\"></a>\n";				
			}else{
				echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&pop=$pop\"><img src=\"img/$ci.png\" title=\"$mstat\"></a><br>\n";
			}
			if( isset($_GET['print']) ){
				echo "<strong>".substr($c,0,$_SESSION['lsiz'])."</strong>";
			}else{
				echo "\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><strong>".substr($c,0,$_SESSION['lsiz'])."</strong></a>\n";
			}
			if( $pop ){
				echo "\t\t\t<p><div class=\"genpad flft lft\">\n";
				echo "\t\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul&co[]=AND&in[]=snmpversion&op[]=>&st[]=0\"><img src=\"img/16/dev.png\" title=\"Devices\">$nd</a>\n";
				if( $pop > 1 ){
					$myp = NodPop( array('location'),array('LIKE'),array("$r$locsep$c$locsep%"),array() );
					if($myp) echo "\t\t\t\t<a href=\"Nodes-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><img src=\"img/16/nods.png\" title=\"$loclbl $poplbl\">$myp</a><br>\n";
					if( $pop == 3){
						IfFree(5,'eth','location','LIKE',"$r$locsep$c$locsep%");
						IfFree(5,'dsl','location','LIKE',"$r$locsep$c$locsep%");
					}
				}
				echo "\t\t\t</div>\n";
			}
		}
		echo "\t\t</td>\n";
		$col++;
	}
	echo "\t</tr>\n</table>\n";
}

//===================================================================
// Generate city table
function TopoBuilds($r,$c,$siz=0){

	global $link,$map,$pop,$debug,$dbuild,$locsep,$bg2,$notlbl,$uptlbl,$netlbl,$loclbl,$porlbl,$frelbl,$addlbl,$stco,$poplbl,$errlbl;

	$ur = urlencode($r);
	$uc = urlencode($c);

	$query	= GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,$c,''),array('AND','AND'));
	$res	= DbQuery($query,$link);
	if (DbNumRows($res)){
		list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
		echo "<h2>$c - $des</h2>\n";
	}else{
		echo "<h2>$c, $r $netlbl</h2>\n";
	}
	echo "<table class=\"content fixed\"><tr>\n";

	$col = 0;
	ksort($dbuild[$r][$c]);
	foreach (array_keys($dbuild[$r][$c]) as $b){
		$sb =  count($dbuild[$r][$c][$b]['sb']);
		$nd =  $dbuild[$r][$c][$b]['nd'];
		$mn = isset( $dbuild[$r][$c][$b]['mn']) ? $dbuild[$r][$c][$b]['mn'] : 0;
		$al = isset( $dbuild[$r][$c][$b]['al']) ? $dbuild[$r][$c][$b]['al'] : 0;
		$nl = isset( $dbuild[$r][$c][$b]['nl']) ? $dbuild[$r][$c][$b]['nl'] : 0;
		$bi = BldImg($nd,$b);
		list($statbg,$stat) = StatusBg($nd,$mn,$al,"imga");
		$ub = urlencode($b);
		$ul = urlencode("$r$locsep$c$locsep$b%");
		if ($col == $_SESSION['col']){
			$col = 0;
			echo "\t</tr>\n\t<tr>\n";
		}
	        echo "\t\t<td class=\"".(($map == 1)?'btm':'ctr btm')." $statbg\">\n";
	        if($al) echo "\t\t\t<div class=\"frgt b\"><img src=\"img/16/dev.png\" title=\"Devices $stco[200]\">$al</div>\n";
	        if($nl) echo "\t\t\t<div class=\"genpad alrm frgt b\"><img src=\"img/16/wlar.png\" title=\"$notlbl $uptlbl\">$nl</div>\n";
	        $mstat = ($mn)?($mn-$al)."/$nd $stco[100] $stat":"";
		if($siz){
			echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&bld=$ub&pop=$pop\"><img src=\"img/$bi.png\" title=\"$mstat\"></a><br>".substr($b,0,$_SESSION['lsiz'])."\n";
		}else{
			$qmap = "$ub,$uc,$ur";
			list($w,$h) = MapSize( $_SESSION['gsiz'] );
			$cp   = preg_replace('/\W/','', $r).'/'.preg_replace('/\W/','', $c);
			EmEvents( $dbuild[$r][$c][$b]['em'],"in[]=location&op[]=LIKE&st[]=$ul" );
			if($cp and $map > 1){
				if($map > 2){
					if( !file_exists("topo/$cp") and !isset($_SESSION['snap']) ) mkdir("topo/$cp", 0755, true);
					$ns = $ew = 0;
					$query	= GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,$c,$b),array('AND','AND'));
					$res	= DbQuery($query,$link);
					if (DbNumRows($res)){
						list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
						echo "\t\t\t$des<br>";
						$loced = '';
					}else{
						$loced = "\t\t\t<a href=\"Assets-Loced.php?reg=$ur&cty=$uc&bld=$ub&pop=$pop\"><img src=\"img/16/ncfg.png\" title=\"$addlbl\"><a/>";
					}
					if($ns and $ew){
						$ns /= 10000000;
						$ew /= 10000000;
						$qmap= "$ns,$ew";
					}
					if( isset($_SESSION['snap']) ){
						$cache = "img/$bi.png";
					}else{
						$cache = "topo/$cp/osm-".preg_replace('/\W/','',$b).".png";
						if( !file_exists($cache) and ini_get('allow_url_fopen') ){
							if(!$ns and !$ew){
								$url = "http://nominatim.openstreetmap.org/search?format=json&limit=2&q=$qmap";
								$geo = json_decode( file_get_contents($url), TRUE);
								if($debug){echo "<div class=\"textpad tqrt code pre ".(($geo)?'good':'alrm')."\"><strong>$url</strong><p>";print_r($geo); echo '</div>';}
								if($geo and $geo[0][lat]){
									$qmap= $geo[0][lat].",".$geo[0][lon];
								}
							}
							$cfurl = "http://staticmap.openstreetmap.de/staticmap.php?center=$qmap&zoom=16&size=240x160&maptype=osmarenderer&markers=$qmap";
							$cdata = file_get_contents($cfurl);
							$csize = ($cdata)?DecFix(file_put_contents($cache, $cdata)):'';
							if($debug){echo "<div class=\"textpad code tqrt pre ".(($csize)?'good':'alrm')."\">$cfurl\n$cache $csize</div>";}
						}
					}
					echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&bld=$ub&map=$map&pop=$pop\"><img src=\"$cache\" width=\"$w\" title=\"$mstat\" style=\"border:1px solid black\"></a><br>\n";
					if( !isset($_GET['print'])) echo "$loced\t\t\t<a href=\"http://nominatim.openstreetmap.org/search.php?q=$qmap\" target=\"window\"><img src=\"img/16/osm.png\" title=\"Openstreetmap\"></a>\n";
				}else{
					echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&bld=$ub&map=$map&pop=$pop\"><img src=\"inc/drawmap.php?st[]=$ul&dim=${w}x$h&lev=4&xo=-20&pos=".(($mn)?"a":"d")."\" title=\"$mstat\" class=\"genpad\"></a><br>\n";
					if( !isset($_GET['print']) and !$mn ) echo "\t\t\t<a href=\"Topology-Map.php?st[]=$ul&tit=$ub+Map&lev=4&fmt=png\"><img src=\"img/16/paint.png\" title=\"Topology-Map\"></a>\n";
				}
			}elseif($map){
				echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&bld=$ub&pop=$pop\"><img src=\"img/16/home.png\" title=\"$mstat\">".Digit($sb)."</a>\n";				
			}else{
				echo "\t\t\t<a href=\"?reg=$ur&cty=$uc&bld=$ub&pop=$pop\"><img src=\"img/$bi.png\" title=\"$mstat\">".Digit($sb)."</a><br>\n";
			}
			if( isset($_GET['print']) ){
				echo "<strong>".substr($b,0,$_SESSION['lsiz'])."</strong>";
			}else{
				echo "\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><strong>".substr($b,0,$_SESSION['lsiz'])."</strong></a>\n";
			}
			if($pop){
				echo "\t\t\t<p><div class=\"genpad flft lft\">\n";
				echo "\t\t\t\t<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$ul&co[]=AND&in[]=snmpversion&op[]=>&st[]=0\"><img src=\"img/16/dev.png\" title=\"Devices\">$nd</a>\n";
				if( $pop > 1 ){
					$myp = NodPop( array('location'),array('LIKE'),array("$r$locsep$c$locsep$b%"),array() );
					if($myp) echo "\t\t\t\t<a href=\"Nodes-List.php?in[]=location&op[]=LIKE&st[]=$ul\"><img src=\"img/16/nods.png\" title=\"$loclbl $poplbl\">$myp</a><br>\n";
					if( $pop == 3){
						IfFree(5,'eth','location','LIKE',"$r$locsep$c$locsep$b%");
						IfFree(5,'dsl','location','LIKE',"$r$locsep$c$locsep$b%");
					}
				}
				echo "\t\t\t</div>\n";
			}
		}
		echo "\t\t</td>\n";
		$col++;
	}
	echo "\t</tr>\n</table>\n";
}

//===================================================================
// Generate building table
function TopoFloors($r,$c,$b,$siz=0){

	global $link,$dev,$img,$map,$pop,$v,$locsep,$nl,$place,$notlbl,$uptlbl,$porlbl,$frelbl,$loclbl,$poplbl;

	foreach (array_keys($dev) as $sb){
		$query = GenQuery('locations','s','id,x,y,ns,ew,locdesc','','',array('region','city','building'),array('=','=','='),array($r,$c,$sb),array('AND','AND'));
		$res   = DbQuery($query,$link);
		$cp    = preg_replace('/\W/','', $r).'/'.preg_replace('/\W/','', $c);
		$bfile = preg_replace('/\W/','', $b); 
		$osmi = '';
		if (DbNumRows($res)){
			list($id,$x,$y,$ns,$ew,$des) = DbFetchRow($res);
			echo "<h2>$sb - $des</h2>\n";
		}else{
			echo "<h2>$sb</h2>\n";
		}
		if( $map > 2 and file_exists("topo/$cp/osm-$bfile.png") ){
			echo "<img src=\"topo/$cp/osm-$bfile.png\" class=\"genpad txtb bctr\"><p>\n";
		}
		echo "<table class=\"content fixed\">\n";
		uksort($dev[$sb], "floorsort");
		foreach (array_keys($dev[$sb]) as $fl){
			echo "\t<tr>\n\t\t<td class=\"bgsub s\">\n";
			if($siz){
				echo "\t\t\t<h3>$fl</h3>\n";
			}else{
				echo "\t\t\t<h3><img src=\"img/stair.png\"><br><a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=".urlencode($r.$locsep.$c.$locsep.$sb.$locsep.$fl.'%')."\">$fl</a></h3>\n";
				foreach( glob("$bp-".preg_replace('/\W/','', $fl).'*') as $f ){
					list($ico,$ed) = FileImg($f);
					echo "$ico ";
				}
			}
			echo "\t\t</td>\n";
			$col = 0;
			$prm = "";
			ksort( $dev[$sb][$fl] );
			foreach(array_keys($dev[$sb][$fl]) as $rm){
				if($prm != $rm){
					$bi = ($bi == "imga")?"imgb":"imga";
				}
				$prm = $rm;
				foreach (array_keys($dev[$sb][$fl][$rm]) as $d){
					$ip = $dev[$sb][$fl][$rm][$d]['ip'];
					$po = $dev[$sb][$fl][$rm][$d]['po'];
					$ty = $dev[$sb][$fl][$rm][$d]['ty'];
					$di = $dev[$sb][$fl][$rm][$d]['ic'];
					$co = $dev[$sb][$fl][$rm][$d]['co'];
					$rk = $dev[$sb][$fl][$rm][$d]['rk'];
					$mn = $dev[$sb][$fl][$rm][$d]['mn'];
					$al = $dev[$sb][$fl][$rm][$d]['al'];
					$sz = $dev[$sb][$fl][$rm][$d]['sz'];
					$nl = $dev[$sb][$fl][$rm][$d]['nl'];
					$sk = Digit($dev[$sb][$fl][$rm][$d]['sk']);
					list($statbg,$stat) = StatusBg(1,$mn*-1,$al,$bi);
					$tit = ($stat)?$stat:$ty;
					$ud = urlencode($d);
					$ur = urlencode($r);
					$uc = urlencode($c);
					$ub = urlencode($sb);
					$uf = urlencode($fl);
					$um = urlencode($rm);
					if ($col and $col == $_SESSION['col']){
						$col = 0;
						echo "\t</tr>\n\t<tr>\n\t\t<td>\n\t\t\t&nbsp;\n\t\t</td>\n";
					}
					if($siz){
						echo "\t\t<td class=\"top ctr $statbg\">\n";
						if($nl) echo "\t\t\t<div class=\"genpad alrm frgt b\"><img src=\"img/16/wlar.png\" title=\"$notlbl $uptlbl\">$nl</div>\n";
						echo "\t\t\t<img src=\"img/dev/$di.png\" title=\"$ip\"><br>$d\n\t\t</td>\n";
					}else{
						echo "\t\t<td class=\"top $statbg\">\n";
						if($nl) echo "\t\t\t<div class=\"genpad alrm frgt b\"><img src=\"img/16/wlar.png\" title=\"$notlbl $uptlbl\">$nl</div>\n";
						EmEvents( $dev[$sb][$fl][$rm][$d]['em'],"in[]=device&op[]==&st[]=$ud" );
						$rkv = ($dev[$sb][$fl][$rm][$d]['ru'])?"<a href=\"?reg=$ur&cty=$uc&bld=$ub&fl=$uf&rm=$um\">$rm</a>":$rm;
						echo "\t\t\t<strong>$rkv</strong> $rk<p>\n";
						echo "\t\t\t<div class=\"ctr\"><a href=\"Devices-Status.php?dev=$ud\">";
						echo "<img src=\"".(($img)?DevPanel($ty,$di,$sz)."\" width=\"".(($sz)?'100':'50')."\"":"img/dev/$di.png\"")." title=\"$tit\"></a>$sk<br>\n";
						echo "\t\t\t<strong>$d</strong><br>\n";
						echo "\t\t\t".Devcli($ip,$po);
						echo"<p>".substr($dev[$sb][$fl][$rm][$d]['co'],0,$_SESSION['lsiz'])."</div>\n";
						if( $pop > 1 ){
							echo "\t\t\t<p><div class=\"genpad flft lft\">\n";
							$myp = NodPop( array('device'),array('='),array("$d"),array() );
							if( $myp ) echo "\t\t\t\t<a href=\"Nodes-List.php?in[]=device&op[]==&st[]=$ud\"><img src=\"img/16/nods.png\" title=\"$loclbl $poplbl\">$myp</a><br>\n";
							if( $pop == 3){
								IfFree(5,'eth','device','=',$d);
								IfFree(5,'dsl','device','=',$d);
							}
							echo "\t\t\t</div>\n";
						}
						echo"\t\t</td>\n";
					}
					$col++;
				}
			}
		}
		echo "\t</tr>\n</table>\n<br>\n";
	}
}

//===================================================================
// Generate room with a rackview
function TopoRoom($r,$c,$b,$f,$m){

	global $link,$dev,$locsep,$bg2,$addlbl,$invlbl,$stalbl,$lstlbl,$debug;

	echo "<h2>$b $f-$m</h2>\n";
	echo "<table class=\"fixed\"><tr>\n";

	$query	= GenQuery('inventory','s','serial,assetclass,assettype,assetnumber,assetlocation','','',array('assettype','assetlocation'),array('LIKE','LIKE'),array('gen-%',TopoLoc($r,$c,$b,$f,$m) ),array('AND') );
	$res	= DbQuery($query,$link);
	if($res){
		while( ($i = DbFetchRow($res)) ){
			$l = explode($locsep, $i[4]);
			$d = "$l[6]-$i[3]";
			$dev[$b][$f][$m][$d]['wdh'] = 250;
			$dev[$b][$f][$m][$d]['cl'] = $i[1];
			$dev[$b][$f][$m][$d]['ty'] = $i[2];
			$dev[$b][$f][$m][$d]['sn'] = $i[0];
			$dev[$b][$f][$m][$d]['sz'] = substr($i[2],-1);
			$dev[$b][$f][$m][$d]['sk'] = 1;
			$dev[$b][$f][$m][$d]['rk']  = $l[5];
			$dev[$b][$f][$m][$d]['ru']  = $l[6];
			$dev[$b][$f][$m][$d]['lwd'] = 24;
			$dev[$b][$f][$m][$d]['ip'] = 0;
		}
	}

	$col = 0;
	if($debug){echo '<div class="textpad code pre noti half">';}
	$rsiz[$dev[$b][$f][$m][$d]['rk']] = 0;
	foreach( array_keys($dev[$b][$f][$m]) as $d ){
		if( $dev[$b][$f][$m][$d]['ru'] ){
			if($dev[$b][$f][$m][$d]['sz'] < 1){
				$dev[$b][$f][$m][$d]['wdh'] = 125;
				$dev[$b][$f][$m][$d]['hgt'] = $dev[$b][$f][$m][$d]['sk'];
				$dev[$b][$f][$m][$d]['lwd'] = 8;
			}else{
				$dev[$b][$f][$m][$d]['wdh'] = 250;
				if( preg_match('/^N[56]K-/',$dev[$b][$f][$m][$d]['ty']) ) $dev[$b][$f][$m][$d]['sk'] = 1;
				$dev[$b][$f][$m][$d]['hgt'] = $dev[$b][$f][$m][$d]['sk'] * $dev[$b][$f][$m][$d]['sz'];
				$dev[$b][$f][$m][$d]['lwd'] = 24;
			}
			if( is_array($rack[$dev[$b][$f][$m][$d]['rk']]) and array_key_exists($dev[$b][$f][$m][$d]['ru'].";-4",$rack[$dev[$b][$f][$m][$d]['rk']]) ){
				$xpos = 121;
			}else{
				$xpos = -4;
			}
			$rack[$dev[$b][$f][$m][$d]['rk']][$dev[$b][$f][$m][$d]['ru'].";".$xpos] = $d;
			$top = $dev[$b][$f][$m][$d]['ru'] + $dev[$b][$f][$m][$d]['hgt'];
			if( $top > $rsiz[$dev[$b][$f][$m][$d]['rk']] ) $rsiz[$dev[$b][$f][$m][$d]['rk']] = $top;
			if($debug){echo "$d Rack:".$dev[$b][$f][$m][$d]['rk']." Top:$top RU:".$dev[$b][$f][$m][$d]['ru']." H:".$dev[$b][$f][$m][$d]['hgt']."<br>\n";}
		}else{
			if($debug){echo "$d Rack:".$dev[$b][$f][$m][$d]['rk']." no RU<br>\n";}
		}
	}
	if($debug){echo '</div>';}


	ksort( $rack );
	foreach( array_keys($rack) as $rk ){
		if( $col == $_SESSION['col'] ){
			$col = 0;
			echo "</tr>\n<tr>";
		}
		$rupx = 23;
		$urk = urlencode($r.$locsep.$c.$locsep.$b.$locsep.$f.$locsep.$m.$locsep.$rk.$locsep);
		echo "<td class=\"txta btm\"><h3>";
		if( isset($_GET['print'])){
			echo "<h3>$rk</h3>\n";
		}else{
			echo "<a href=\"Assets-Management.php?st=0&sn=rk".time()."&lo=$urk&st=100&ty=gen-patch&cl=20\"><img src=\"img/16/link.png\" title=\"$addlbl $invlbl\"></a>";
			echo "<a href=\"Assets-Management.php?st=0&sn=sv".time()."&lo=$urk&st=100&ty=gen-srv&cl=60\"><img src=\"img/16/nhdd.png\" title=\"$addlbl $invlbl\"></a>";
			echo "<a href=\"Devices-List.php?in[]=location&op[]=LIKE&st[]=$urk%25\">$rk</a></h3>\n";
		}
		echo "<div style=\"height:".($rsiz[$rk]*$rupx-$rupx)."px;width:240px;border:12px solid #444;background-color:#aaa\">\n";
		echo "<div style=\"position:relative;bottom:0px;left:-6px;height:".($rsiz[$rk]*$rupx-$rupx)."px;width:244px;border-width:1px 4px;border-style:solid dotted;border-color:#888\">\n";
		$rus = array_keys($rack[$rk]);
		sort($rus);
		foreach ($rus as $rup){
			$p = explode(';', $rup);
			if($debug){echo "$rup ".$rack[$rk][$rup]."<br>\n";}
			$ud = urlencode($rack[$rk][$rup]);
			list($statbg,$stat) = StatusBg(1,$dev[$b][$f][$m][$rack[$rk][$rup]]['mn']*-1,$dev[$b][$f][$m][$rack[$rk][$rup]]['al'],'txta');
			$bgpanel = DevPanel($dev[$b][$f][$m][$rack[$rk][$rup]]['ty'],$dev[$b][$f][$m][$rack[$rk][$rup]]['ic'],$dev[$b][$f][$m][$rack[$rk][$rup]]['sz']);
			$lbl     = "<span class=\"$statbg\" style=\"border: 1px solid black;font-size:80%\" title=\"RU:$p[0]\">".substr($rack[$rk][$rup],0,$dev[$b][$f][$m][$rack[$rk][$rup]]['lwd'])."&nbsp;</span>\n";
			$lbl    .= (($dev[$b][$f][$m][$rack[$rk][$rup]]['sk'] > 1)?"<img src=\"img/".$dev[$b][$f][$m][$rack[$rk][$rup]]['sk'].".png\" style=\"background-color:#999\" title=\"Stack\">":"");
			if( !isset($_GET['print'])){
				$lbl .= "<div class=\"frgt $statbg\" style=\"border: 1px solid black\">";
				if($dev[$b][$f][$m][$rack[$rk][$rup]]['sn']){
					list($mcl,$img) = ModClass($dev[$b][$f][$m][$rack[$rk][$rup]]['cl']);
					$lbl .= "<a href=\"Assets-Management.php?chg=".$dev[$b][$f][$m][$rack[$rk][$rup]]['sn']."\"><img src=\"img/16/$img.png\" title=\"$mcl, $invlbl\"></a></div>";
				}else{
					$lbl .= Devcli($dev[$b][$f][$m][$rack[$rk][$rup]]['ip'],$dev[$b][$f][$m][$rack[$rk][$rup]]['po'],2);
					$lbl .= "<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/16/sys.png\" title=\"Device $stalbl\"></a>";
					$lbl .= "<a href=\"Nodes-List.php?in[]=device&op[]==&st[]=$ud&ord=ifname\"><img src=\"img/16/nods.png\" title=\"Nodes $lstlbl\"></a></div>";
				}
			}
			$rpos = $rsiz[$rk] * $rupx - ($p[0] + $dev[$b][$f][$m][$rack[$rk][$rup]]['hgt']) * $rupx;
			echo "<div style=\"position:absolute;top:${rpos}px;left:$p[1]px;height:".($dev[$b][$f][$m][$rack[$rk][$rup]]['hgt']*23)."px;width:".$dev[$b][$f][$m][$rack[$rk][$rup]]['wdh']."px;border:1px solid black;background-image: URL($bgpanel);\">\n$lbl</div>\n";
		}
		echo "</div></div></td>\n";
		$col++;
	}
	echo "</tr></table>\n";
}

//===================================================================
// Show the misfits
function TopoLocErr($siz=0){

	global $noloc,$img,$debug,$manlbl,$bg2,$loclbl,$errlbl;

	if( !count($noloc) ) return;

	echo "<p>\n\n<h2>$loclbl $errlbl</h2>\n\n";
	echo "<table class=\"content fixed\">\n	<tr>\n";

	$col = 0;
	foreach (array_keys($noloc) as $d){
		$ip = $noloc[$d]['ip'];
		$ty = $noloc[$d]['ty'];
		$di = $noloc[$d]['ic'];
		$lo = $noloc[$d]['lo'];
		$co = $noloc[$d]['co'];
		$po = $noloc[$d]['po'];
		$mn = $noloc[$d]['mn'];
		$al = $noloc[$d]['al'];
		list($statbg,$stat) = StatusBg(1,$mn,$al,'imga');
		$tit = ($stat)?$stat:$ty;
		$ud = urlencode($d);
		if ($col == $_SESSION['col']){
			$col = 0;
			echo "\n	</tr>\n	<tr>\n";
		}
		if($siz){
			echo "		<td class=\"$statbg ctr\">\n";
			echo "			<img src=\"img/dev/$di.png\" title=\"$lo, $co\"><br>$d\n";
			echo "		</td>\n";
		}else{
			echo "		<td class=\"$statbg ctr\">\n";
			echo "			<a href=\"Devices-Status.php?dev=$ud\"><img src=\"img/dev/$di.png\" title=\"$tit\"></a>\n";
			echo "			$sk<br><strong>$d</strong><br>".Devcli($ip,$po)."<br>$lo<br><span class=\"gry\">".substr($noloc[$d]['co'],0,$_SESSION['lsiz'])."</span>\n";
			echo "		</td>\n";
		}
		$col++;
	}
	echo "	</tr>\n</table>\n";
}

//===================================================================
// Return image for test
function TestImg($srv,$topt="",$tres=""){

	global $nonlbl,$tstlbl,$sndlbl,$rcvlbl;

	if($srv == "uptime"){$img =  "clock";}
	elseif($srv == "ping")	{$img =  "relo";}
	elseif($srv == "icmp")	{$img =  "relo";}
	elseif($srv == "dns")	{$img =  "abc";}
	elseif($srv == "ntp")	{$img =  "date";}
	elseif($srv == "http")	{$img =  "glob";}
	elseif($srv == "https")	{$img =  "glok";}
	elseif($srv == "telnet"){$img =  "loko";}
	elseif($srv == "ssh")	{$img =  "lokc";}
	elseif($srv == "mysql")	{$img =  "db";}
	elseif($srv == "cifs")	{$img =  "nwin";}
	elseif($srv == "none")	{$img =  "bcls";}
	else{$img =  "bdis";$srv = "$nonlbl Monitor";}

	return "<img src=\"img/16/$img.png\" title=\"$tstlbl: $srv".(($topt or $tres)?" $sndlbl $topt, $rcvlbl $tres":"")."\">";
}

//===================================================================
// Emergency Event Indicator
function EmEvents($n,$l){

	global $msglbl,$mlvl;

        if( $n ){
		if( $l ) echo "\t\t\t<a href=\"Monitoring-Events.php?$l&co[]=AND&in[]=level&op[]==&st[]=250\">";
	        echo "\t\t\t<div class=\"genpad crit frgt b\"><img src=\"img/16/ford.png\" title=\"$mlvl[250] $msglbl\">$n</div>\n";
		if( $l ) echo "\t\t\t</a>";
		echo "\n";
	 }
}
?>
