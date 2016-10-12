<?php
# Program: Other-Defgen.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$co = isset($_GET['co']) ? $_GET['co'] : "public";
$so = isset($_GET['so']) ? $_GET['so'] : "";
$sc = isset($_GET['sc']) ? $_GET['sc'] : "";
$ip = isset($_GET['ip']) ? $_GET['ip'] : "";
$wr = isset($_POST['wr']) ? $_POST['wr'] : "";

$def = "";
$dis = "";																	# 0 is not working with javascript!

$typ = "";
$des = "";
$to = "";

$df  = array();
$ver = array();

$ico = "w2an";
$hgt = "1";

$bi = "";
$sn = "";
$vln = "";
$vnx = "";
$arp = "";
$grp = "";
$dmo = "";
$cfc = "";
$cfw = "";

$stx = "";
$enx = "";
$vrf = "";
$ina = "";
$ifa = "";
$ial = "";
$iax = "";
$idu = "";
$idx = "";
$hdv = "";
$fdv = "";
$brc = "";
$idi = "";
$odi = "";
$ivl = "";
$ivx = "";
$ipw = "";
$pem = "";
$ipx = "";

$msl = "";
$mst = "";
$msv = "";
$mcl = "";
$mcv = "";
$mde = "";
$mhw = "";
$msw = "";
$mfw = "";
$msn = "";
$mmo = "";
$mlo = "";

$cpu = "";
$cmu = "";
$tmp = "";
$tmu = "";
$mcp = "";
$mmu = "";
$mio = "";

$cul = "";
$cuv = "";

$os  = "";
$bfd = "";
$dpr = "";

$wrw = "";


$defpath = "$nedipath/sysobj";

if($isadmin and $so and $sc){
?>
<script language="JavaScript"><!--
setTimeout("history.go(-1)",2000);
//--></script>
<?php
	if( copy("$defpath/$sc.def", "$defpath/$so.def") ){
		echo "<h5>$coplbl $defpath/$so.def OK</h5>\n";
	}else{
		echo "<h4>$errlbl $coplbl $defpath/$so.def!</h4>\n";
	}
	include_once ("inc/footer.php");
	exit(0);
}

if($so){
	if( file_exists("$defpath/$so.def") ){
		$deffile = file("$defpath/$so.def");
		$wrw = "onclick=\"return confirm('".(($verb1)?"$rpllbl $so.def":"$so.def $rpllbl").", $cfmmsg')\"";
	}elseif( file_exists("log/$so.def") ){
		$deffile = file("log/$so.def");
		$defpath = "log";
	}else{
		$deffile = "";
	}
	if ($deffile){
		$mailbody = rawurlencode( str_replace('	','____',implode('',$deffile)) );			# Replacing tabs with something I can easily substitute again, since mail clients convert tabs to spaces...
		$def = "$realbl $defpath/$so.def OK\n\n";
		do {
			$defhead = array_shift($deffile);
			$def .= $defhead;
		}while(preg_match('/^[#;]/',$defhead) );

		foreach ($deffile as $l) {
			if( !preg_match('/^[#;]/', $l) ){
				$d = preg_split('/\t+/',rtrim($l) );
				if($d[0] == 'SNMPv' AND $d[1] == '2HC'){$ver['2HC'] = ' selected';}
				elseif($d[0] == 'SNMPv' AND $d[1] == '2MC'){$ver['2MC'] = ' selected';}
				elseif($d[0] == 'SNMPv' AND $d[1] == '2'){$ver['2'] = ' selected';}
				elseif($d[0] == 'Type'){$typ = $d[1];}
				elseif($d[0] == 'Sysdes'){$des = $d[1];}
				elseif($d[0] == 'Icon'){$ico = $d[1];}
				elseif($d[0] == 'Size'){$hgt = $d[1];}
				elseif($d[0] == 'Typoid'){$to = $d[1];}
				elseif($d[0] == 'OS' AND $d[1]){$os = $d[1];}
				elseif($d[0] == 'Bridge' AND $d[1]){$bfd = $d[1];}
				elseif($d[0] == 'ArpND' AND $d[1]){$arp = $d[1];}
				elseif($d[0] == 'Dispro'){$dpr = $d[1];}
				elseif($d[0] == 'Serial'){$sn  = $d[1];}
				elseif($d[0] == 'Bimage'){$bi  = $d[1];}
				elseif($d[0] == 'VLnams'){$vln = $d[1];}
				elseif($d[0] == 'VLnamx'){$vnx = $d[1];}
				elseif($d[0] == 'Group'){$grp = $d[1];}
				elseif($d[0] == 'Mode'){$dmo = $d[1];}
				elseif($d[0] == 'CfgChg'){$cfc = $d[1];}
				elseif($d[0] == 'CfgWrt'){$cfw = $d[1];}
				elseif($d[0] == 'StartX'){$stx = $d[1];}
				elseif($d[0] == 'EndX'){$enx = $d[1];}
				elseif($d[0] == 'IFname'){$ina = $d[1];}
				elseif($d[0] == 'IFaddr'){$ifa = $d[1];$vrf = $d[2];}
				elseif($d[0] == 'IFalia'){$ial = $d[1];}
				elseif($d[0] == 'IFalix'){$iax = $d[1];}
				elseif($d[0] == 'IFdupl'){$idu = $d[1];}
				elseif($d[0] == 'IFduix'){$idx = $d[1];}
				elseif($d[0] == 'Halfdp'){$hdv = $d[1];}
				elseif($d[0] == 'Fulldp'){$fdv = $d[1];}
				elseif($d[0] == 'InBcast'){$brc = $d[1];}
				elseif($d[0] == 'InDisc'){$idi = $d[1];}
				elseif($d[0] == 'OutDisc'){$odi = $d[1];}
				elseif($d[0] == 'IFvlan'){$ivl = $d[1];}
				elseif($d[0] == 'IFvlix'){$ivx = $d[1];}
				elseif($d[0] == 'IFpowr'){$ipw = $d[1];$pem = $d[2];}
				elseif($d[0] == 'IFpwix'){$ipx = $d[1];}
				elseif($d[0] == 'Modesc'){$mde = $d[1];}
				elseif($d[0] == 'Moclas'){$mcl = $d[1];}
				elseif($d[0] == 'Movalu'){$mcv = $d[1];}
				elseif($d[0] == 'Moslot'){$msl = $d[1];}
				elseif($d[0] == 'Mostat'){$mst = $d[1];}
				elseif($d[0] == 'Mostok'){$msv = $d[1];}
				elseif($d[0] == 'Modhw'){$mhw  = $d[1];}
				elseif($d[0] == 'Modsw'){$msw  = $d[1];}
				elseif($d[0] == 'Modfw'){$mfw  = $d[1];}
				elseif($d[0] == 'Modser'){$msn = $d[1];}
				elseif($d[0] == 'Momodl'){$mmo = $d[1];}
				elseif($d[0] == 'Modloc'){$mlo = $d[1];}
				elseif($d[0] == 'CPUutl'){$cpu = $d[1];$cmu = $d[2];}
				elseif($d[0] == 'Temp'){$tmp   = $d[1];$tmu = $d[2];}
				elseif($d[0] == 'MemCPU'){$mcp = $d[1];$mmu = $d[2];}
				elseif($d[0] == 'MemIO'){$cuv = $d[1];$cul = "MemIO;G;Bytes";}	# Support legacy .defs
				elseif($d[0] == 'Custom'){$cul = $d[1];$cuv = $d[2];}
			}
		}
	}
}

echo "<h1>Definition Generator</h1>\n";

if($isadmin and $wr){
?>
<script language="JavaScript"><!--
setTimeout("history.go(-1)",2000);
//--></script>
<?php
	$def = str_replace ('____', '	', preg_replace("/\r|\/|\\|\.\.|/", "", $_POST['def']) );
	$hdr = substr($def, 0, strpos($def, "\n") );
	if( $so == '' and preg_match('/# Definition for ([\d\.]+) created.*/', $hdr ) ){
		$so   = preg_replace('/# Definition for ([\d\.]+) created.*/', '$1', $hdr );
	}else{
		$so   = preg_replace("/\/|\\|\.\./", "", $_POST['so'] );
	}
	if( $so == '' ){
			echo "<h4>".(($verb1)?"$addlbl $namlbl":"$namlbl $addlbl")."</h4>\n";
	}else{
		$hdle = fopen("$defpath/$so.def", "w");
		if( fwrite($hdle, $def) ){
			echo "<h5>$wrtlbl $defpath/$so.def OK</h5>\n";
		}else{
			echo "<h4>$errlbl $wrtlbl $defpath/$so.def!</h4>\n";
		}
		fclose($hdle);
	}
	echo "\n<div class=\"txta tqrt textpad code pre\">$def</div>\n";
	include_once ("inc/footer.php");
	exit(0);
}
?>

<script language="JavaScript">
<!--
dis = '<?= $dis ?>';

function update() {

	if (dis){
		alert('Controls disabled!');
	}else{
		document.gen.so.value = document.bld.so.value;
		document.gen.def.value = "# Definition for " + document.bld.so.value + " created by Defgen 2.0 on <?= date('j.M Y') ?> (<?= $_SESSION['user'] ?>)\n" +
		" \n# Main\n" +
		"SNMPv\t" + document.bld.ver.options[document.bld.ver.selectedIndex].value + "\n" +
		"Type\t" + document.bld.typ.value + "\n" +
		"Typoid\t" + document.bld.to.value + "\n" +
		"Sysdes\t" + document.bld.des.value + "\n" +
		"OS\t" + document.bld.os.options[document.bld.os.selectedIndex].value + "\n" +
		"Icon\t" + document.bld.ico.value + "\n" +
		"Size\t" + document.bld.hgt.value + "\n" +
		"Bridge\t" + document.bld.brg.options[document.bld.brg.selectedIndex].value + "\n" +
		"ArpND\t" + document.bld.arp.options[document.bld.arp.selectedIndex].value + "\n" +
		"Dispro\t" + document.bld.dpr.value + "\n" +
		"Serial\t" + document.bld.sn.value + "\n" +
		"Bimage\t" + document.bld.bi.value + "\n" +
		"CfgChg\t" + document.bld.cfc.value + "\n" +
		"CfgWrt\t" + document.bld.cfw.value + "\n" +
		"VLnams\t" + document.bld.vln.value + "\n" +
		"VLnamx\t" + document.bld.vnx.value + "\n" +
		"Group\t" + document.bld.grp.value + "\n" +
		"Mode\t" + document.bld.dmo.value + "\n" +
		" \n# Interfaces\n" +
		"StartX\t" + document.bld.stx.value + "\n" +
		"EndX\t" + document.bld.enx.value + "\n" +
		"IFname\t" + document.bld.ina.value + "\n" +
		"IFaddr\t" + document.bld.ifa.options[document.bld.ifa.selectedIndex].value + "\t" + document.bld.vrf.value + "\n" +
		"IFalia\t" + document.bld.ial.value + "\n" +
		"IFalix\t" + document.bld.iax.value + "\n" +
		"InBcast\t" + document.bld.brc.value + "\n" +
		"InDisc\t" + document.bld.idi.value + "\n" +
		"OutDisc\t" + document.bld.odi.value + "\n" +
		"IFvlan\t" + document.bld.ivl.value + "\n" +
		"IFvlix\t" + document.bld.ivx.value + "\n" +
		"IFpowr\t" + document.bld.ipw.value + "\t" + document.bld.pem.value + "\n" +
		"IFpwix\t" + document.bld.ipx.value + "\n" +
		"IFdupl\t" + document.bld.idu.value + "\n" +
		"IFduix\t" + document.bld.idx.value + "\n" +
		"Halfdp\t" + document.bld.hdv.value + "\n" +
		"Fulldp\t" + document.bld.fdv.value + "\n" +
		" \n# Modules\n" +
		"Modesc\t" + document.bld.mde.value + "\n" +
		"Moclas\t" + document.bld.mcl.value + "\n" +
		"Movalu\t" + document.bld.mcv.value + "\n" +
		"Moslot\t" + document.bld.msl.value + "\n" +
		"Mostat\t" + document.bld.mst.value + "\n" +
		"Mostok\t" + document.bld.msv.value + "\n" +
		"Modhw\t" + document.bld.mhw.value + "\n" +
		"Modsw\t" + document.bld.msw.value + "\n" +
		"Modfw\t" + document.bld.mfw.value + "\n" +
		"Modser\t" + document.bld.msn.value + "\n" +
		"Momodl\t" + document.bld.mmo.value + "\n" +
		"Modloc\t" + document.bld.mlo.value + "\n" +
		" \n# RRD Graphing\n" +
		"CPUutl\t" + document.bld.cpu.value + "\t" + document.bld.cmu.value + "\n" +
		"Temp\t" + document.bld.tmp.value + "\t" + document.bld.tmu.value + "\n" +
		"MemCPU\t" + document.bld.mcp.value + "\t" + document.bld.mmu.value + "\n" +
		"Custom\t" + document.bld.cul.value + "\t" + document.bld.cuv.value;

		document.gen.wr.disabled=false;
	}
}

function setgen(gen) {
	if('1' == gen){
		document.bld.sn.value = "1.3.6.1.2.1.47.1.1.1.1.11.1";
		document.bld.bi.value = "";
		document.bld.ico.value = "w2gn";
		document.bld.hgt.value = "1";
		document.bld.to.value = "1.3.6.1.2.1.47.1.1.1.1.13.1";
		document.bld.ver.selectedIndex  = 3;
		document.bld.os.selectedIndex   = 0;
		document.bld.brg.selectedIndex  = 3;
		document.bld.arp.selectedIndex  = 1;
		document.bld.dpr.value = "LLDP";
		document.bld.vln.value = "1.3.6.1.2.1.17.7.1.4.3.1.1";
		document.bld.grp.value = "";
		document.bld.dmo.value = "";
		document.bld.cfc.value = "";
		document.bld.cfw.value = "";
	}else{
		document.bld.sn.value = "";
		document.bld.bi.value = "";
		document.bld.ico.value = "w2an";
		document.bld.hgt.value = "1";
		document.bld.ver.selectedIndex  = 0;
		document.bld.os.selectedIndex  = 0;
		document.bld.brg.selectedIndex  = 0;
		document.bld.arp.selectedIndex  = 0;
		document.bld.dpr.value = "";
		document.bld.vln.value = "";
		document.bld.grp.value = "";
		document.bld.dmo.value = "";
		document.bld.cfc.value = "";
		document.bld.cfw.value = "";
	}
	update();
}

function setint(typ) {
	if ('1' == typ){
		document.bld.stx.value = "";
		document.bld.enx.value = "";
		document.bld.ina.value = "1.3.6.1.2.1.31.1.1.1.1";
		document.bld.ifa.selectedIndex = 2;
		document.bld.ial.value = "1.3.6.1.2.1.31.1.1.1.18";
		document.bld.iax.value = "";
		document.bld.idu.value = "1.3.6.1.2.1.10.7.2.1.19";
		document.bld.idx.value = "1.3.6.1.2.1.10.7.2.1.1";
		document.bld.hdv.value = "2";
		document.bld.fdv.value = "3";
		document.bld.brc.value = "1.3.6.1.2.1.31.1.1.1.9";
		document.bld.idi.value = "1.3.6.1.2.1.2.2.1.13";
		document.bld.odi.value = "1.3.6.1.2.1.2.2.1.19";
		document.bld.ivl.value = "1.3.6.1.2.1.17.7.1.4.5.1.1";
		document.bld.ivx.value = "";
		document.bld.ipw.value = "";
		document.bld.pem.selectedIndex = 0;
		document.bld.ipx.value = "";
	}else if ('2' == typ){
		document.bld.stx.value = "";
		document.bld.enx.value = "";
		document.bld.ina.value = "1.3.6.1.2.1.31.1.1.1.1";
		document.bld.ifa.selectedIndex = 2;
		document.bld.ial.value = "1.3.6.1.2.1.31.1.1.1.18";
		document.bld.iax.value = "";
		document.bld.idu.value = "1.3.6.1.2.1.26.2.1.1.11";
		document.bld.idx.value = "";
		document.bld.hdv.value = "";
		document.bld.fdv.value = "";
		document.bld.brc.value = "1.3.6.1.2.1.31.1.1.1.3";
		document.bld.idi.value = "1.3.6.1.2.1.2.2.1.13";
		document.bld.odi.value = "1.3.6.1.2.1.2.2.1.19";
		document.bld.ivl.value = "1.3.6.1.2.1.17.7.1.4.5.1.1";
		document.bld.ivx.value = "";
		document.bld.ipw.value = "";
		document.bld.pem.selectedIndex = 0;
		document.bld.ipx.value = "";
	}else{
		document.bld.stx.value = "";
		document.bld.enx.value = "";
		document.bld.ina.value = "";
		document.bld.ifa.selectedIndex = 1;
		document.bld.ial.value = "";
		document.bld.iax.value = "";
		document.bld.idu.value = "";
		document.bld.idx.value = "";
		document.bld.hdv.value = "";
		document.bld.fdv.value = "";
		document.bld.brc.value = "";
		document.bld.idi.value = "";
		document.bld.odi.value = "";
		document.bld.ivl.value = "";
		document.bld.ivx.value = "";
		document.bld.ipw.value = "";
		document.bld.pem.selectedIndex = 0;
		document.bld.ipx.value = "";
	}
	update();
}

function setmod(typ) {
	if ('1' == typ){
		document.bld.mde.value = "1.3.6.1.2.1.47.1.1.1.1.2";
		document.bld.mcl.value = "1.3.6.1.2.1.47.1.1.1.1.5";
		document.bld.mcv.value = "9";
		document.bld.msl.value = "1.3.6.1.2.1.47.1.1.1.1.7";
		document.bld.mhw.value = "1.3.6.1.2.1.47.1.1.1.1.8";
		document.bld.msw.value = "1.3.6.1.2.1.47.1.1.1.1.9";
		document.bld.mfw.value = "1.3.6.1.2.1.47.1.1.1.1.10";
		document.bld.msn.value = "1.3.6.1.2.1.47.1.1.1.1.11";
		document.bld.mmo.value = "1.3.6.1.2.1.47.1.1.1.1.13";
	}else if ('2' == typ){
		document.bld.mde.value = "1.3.6.1.2.1.43.11.1.1.6.1";
		document.bld.mcl.value = "";
		document.bld.mcv.value = "30";
		document.bld.msl.value = "1.3.6.1.2.1.43.11.1.1.5.1";
		document.bld.mhw.value = "1.3.6.1.2.1.43.11.1.1.9.1";
		document.bld.msw.value = "";
		document.bld.mfw.value = "1.3.6.1.2.1.43.11.1.1.8.1";
		document.bld.msn.value = "";
		document.bld.mmo.value = "";
	}else{
		document.bld.mde.value = "";
		document.bld.mcl.value = "";
		document.bld.mcv.value = "";
		document.bld.msl.value = "";
		document.bld.mhw.value = "";
		document.bld.msw.value = "";
		document.bld.mfw.value = "";
		document.bld.msn.value = "";
		document.bld.mmo.value = "";
	}
	update();
}

function setrrd(typ) {
	if ('1' == typ){
		document.bld.cpu.value = "1.3.6.1.4.1.2021.10.1.3.3";
		document.bld.tmp.value = "1.3.6.1.2.1.99.1.1.1.4.11";
		document.bld.mcp.value = "1.3.6.1.4.1.2021.4.11.0";
		document.bld.cul.value = "";
		document.bld.cuv.value = "";
	}else{
		document.bld.cpu.value = "";
		document.bld.cmu.value = "";
		document.bld.tmp.value = "";
		document.bld.tmu.value = "";
		document.bld.mcp.value = "";
		document.bld.mmu.value = "";
		document.bld.cuv.value = "";
	}
	update();
}

function get(oid) {
	window.open('inc/snmpget.php?d=<?= $debug ?>&ip=' + document.bld.ip.value + '&v=' + document.bld.ver.value.substr(0,1) + '&c=' + encodeURIComponent(document.bld.co.value) + '&oid=' + oid,'SNMP','scrollbars=1,menubar=0,resizable=1,width=<?= ($debug)?800:400 ?>,height=300');
}

function walk(oid) {
	window.open('inc/snmpwalk.php?d=<?= $debug ?>&ip=' + document.bld.ip.value + '&v=' + document.bld.ver.value.substr(0,1) + '&c=' + encodeURIComponent(document.bld.co.value) + '&oid=' + oid,'SNMP','scrollbars=1,menubar=0,resizable=1,width=<?= ($debug)?800:400 ?>,height=600');
}

//-->
</script>
<form name="bld">

<table class="content">
<tr class="bgmain">
<td class="ctr">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr">
	IP
	<input type="text" name="ip" value="<?= $ip ?>" class="m" onclick="select();" title="<?= $tgtlbl ?> IP <?= $adrlbl ?>">
<?= (Devcli($ip,22,1)) ?>

<?= (Devcli($ip,23,1)) ?>

</td>
<td class="ctr">
	Community
	<input type="text" name="co" value="<?= $co ?>" class="m" onclick="select();" title="target's SNMP community">
</td>
</tr>
</table>
<p>

<?= file_exists("img/panel/$typ.jpg")?"<img src=\"img/panel/$typ.jpg\" id=\"panel\" class=\"genpad m\" style=\"position: absolute;left: 480px;top: 260px;\" onclick=\"document.getElementById('panel').style.display='none';\";>":"" ?>
<table class="bgmain content">
	<tr class="bgsub">
		<td class="ctr b" colspan="4">
			<img src="img/16/dev.png">
			<?= $manlbl ?>

			<img src="img/16/bcnl.png" class="frgt" onClick="setgen();" title="<?= $reslbl ?>">
			<img src="img/16/idea.png" class="frgt" onClick="setgen('1');" title="Standard System OIDs">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			SysObjId
		</td>
		<td>
			<input type="text" name="so" value="<?= $so ?>" class="l" title="SysObj ID -> <?= $fillbl ?> <?= $namlbl ?>" onclick="select();" onchange="update();">
			<a href="http://www.google.com/search?q=<?= $so ?>" target="window"><img src="img/16/find.png" target="window" title="Google Sysobjid"></a><br>
<?php
$mybase = substr($so, 0, -strlen(strrchr($so, ".")));
$mytyp  = substr(strrchr($so, "."), 1);

foreach (glob("$defpath/$mybase*") as $f){
	$defoid  = substr($f, strlen($defpath)+1,-4);
	$defbase = substr($defoid, 0, -strlen(strrchr($defoid, ".")));
	$deftyp  = substr(strrchr($defoid, "."), 1);
	if( $mybase == $defbase and $deftyp > ($mytyp-10) and $deftyp < ($mytyp+10) ){
		$df[] = "$defbase.$deftyp";
	}
}
sort($df);
foreach ($df as $f){
	if($f == $so){
?>
			<img src="img/16/brld.png" title="<?= ($verb1)?"$reslbl $alllbl":"$alllbl $reslbl" ?>" onClick="document.location.href='?ip='+document.bld.ip.value+'&co='+document.bld.co.value+'&so='+document.bld.so.value;">
<?php
	}elseif($isadmin){
?>
			<a href="<?= $self ?>.php?ip=<?= $ip ?>&co=<?= $co ?>&so=<?= $so ?>&sc=<?= $f ?>"><img src="img/16/copy.png" title="<?= $coplbl ?> <?= $srclbl ?> <?= $f ?>" <?= $wrw ?>></a>
<?php
	}
}

?>
		</td>
		<td class="rgt">
			Typeoid
		</td>
		<td>
			<input type="text" name="to" value="<?= $to ?>" class="l" title="<?= $altlbl ?> device <?= $typlbl ?>" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.to.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $typlbl ?>
		</td>
		<td>
			<input type="text" name="typ" value="<?= $typ ?>" class="l" title="SNMP Sysobj <?= $typlbl ?>" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get('1.3.6.1.2.1.1.1.0');">
		</td>
		<td class="rgt">
			OS
		</td>
		<td>
			<select size="1" name="os" title="Operating System" onchange="update();">
				<option value="other"><?= $mlvl['30'] ?>
				<option value="Printer"<?= ($os == "Printer")?" selected":"" ?>>Printer
				<option value="UPS"<?= ($os == "UPS")?" selected":"" ?>>UPS
				<option value="">--------
				<option value="ArubaOS"<?=($os == "ArubaOS")?" selected":""?>>Aruba OS
				<option value="">--------
				<option value="Aerohive"<?= ($os == "Aerohive")?" selected":"" ?>>Aerohive
				<option value="">--------
				<option value="XOS"<?= ($os == "XOS")?" selected":"" ?>>Extreme OS
				<option value="Xware"<?= ($os == "Xware")?" selected":"" ?>>ExtremeWare
				<option value="EOS"<?= ($os == "EOS")?" selected":"" ?>>Enterasys OS B2
				<option value="EOSB2"<?= ($os == "EOSB2")?" selected":"" ?>>Enterasys OS
				<option value="">--------
				<option value="Edgecore"<?= ($os == "Edgecore")?" selected":"" ?>>Edgecore
				<option value="">--------
				<option value="IOS"<?= ($os == "IOS")?" selected":"" ?>>Cisco IOS
				<option value="IOS-old"<?= ($os == "IOS-old")?" selected":"" ?>>IOS < 12.1
				<option value="IOS-css"<?= ($os == "IOS-css")?" selected":"" ?>>IOS CSS
				<option value="IOS-pix"<?= ($os == "IOS-pix")?" selected":"" ?>>IOS PIX
				<option value="IOS-asa"<?= ($os == "IOS-asa")?" selected":"" ?>>IOS ASA
				<option value="IOS-fv"<?= ($os == "IOS-fv")?" selected":"" ?>>IOS FWSM
				<option value="IOS-rtr"<?= ($os == "IOS-rtr")?" selected":"" ?>>IOS Router
				<option value="IOS-xr"<?= ($os == "IOS-xr")?" selected":"" ?>>Cisco IOS XR
				<option value="IOS-wlc"<?= ($os == "IOS-wlc")?" selected":"" ?>>IOS WLC
				<option value="IOS-ap"<?= ($os == "IOS-ap")?" selected":"" ?>>IOS Wlan-AP
				<option value="NXOS"<?= ($os == "NXOS")?" selected":"" ?>>Nexus OS
				<option value="NXUCS"<?= ($os == "NXUCS")?" selected":"" ?>>Nexus UCS
				<option value="CSBS"<?= ($os == "CSBS")?" selected":"" ?>>Cisco SMB
				<option value="CatOS"<?= ($os == "CatOS")?" selected":"" ?>>Cisco CatOS
				<option value="Cvpn"<?= ($os == "Cvpn")?" selected":"" ?>>Cisco vpn
				<option value="">--------
				<option value="DPC"<?=($os == "DPC")?" selected":""?>>Dell Powerconnect
				<option value="DPCN"<?=($os == "DPCN")?" selected":""?>>Dell Powerconnect N
				<option value="SonicOS"<?=($os == "SonicOS")?" selected":""?>>Dell Sonic OS
				<option value="">--------
				<option value="FortiOS"<?=($os == "FortiOS")?" selected":""?>>FortiOS
				<option value="">--------
				<option value="Hirschmann"<?=($os == "Hirschmann")?" selected":""?>>Hirschmann
				<option value="">--------
				<option value="Comwar3"<?= ($os == "Comwar3")?" selected":"" ?>>HP Comware 3
				<option value="Comware"<?= ($os == "Comware")?" selected":"" ?>>HP Comware
				<option value="MSM"<?= ($os == "MSM")?" selected":"" ?>>HP MSM
				<option value="ProCurve"<?= ($os == "ProCurve")?" selected":"" ?>>HP ProCurve
				<option value="SROS"<?= ($os == "SROS")?" selected":"" ?>>HP SROS
				<option value="TMS"<?= ($os == "TMS")?" selected":"" ?>>HP TMS
				<option value="VC"<?= ($os == "VC")?" selected":"" ?>>HP VC
				<option value="">--------
				<option value="HuaweiVRP"<?= ($os == "HuaweiVRP")?" selected":"" ?>>Huawei VRP
				<option value="">--------
				<option value="Ironware"<?= ($os == "Ironware")?" selected":"" ?>>Ironware
				<option value="Vyatta"<?= ($os == "Vyatta")?" selected":"" ?>>Vyatta
				<option value="">--------
				<option value="JunOS"<?= ($os == "JunOS")?" selected":"" ?>>Juniper OS
				<option value="NetScreen"<?= ($os == "NetScreen")?" selected":"" ?>>NetScreen OS
				<option value="">--------
				<option value="LANCOM"<?= ($os == "LANCOM")?" selected":"" ?>>LANCOM
				<option value="">--------
				<option value="Maipu"<?= ($os == "Maipu")?" selected":"" ?>>Maipu
				<option value="">--------
				<option value="ROS"<?= ($os == "ROS")?" selected":"" ?>>Mikrotik-ROS
				<option value="">--------
				<option value="Netgear"<?= ($os == "Netgear")?" selected":"" ?>>Netgear
				<option value="">--------
				<option value="Baystack"<?= ($os == "Baystack")?" selected":"" ?>>Nortel Legacy
				<option value="Nortel"<?= ($os == "Nortel")?" selected":"" ?>>Nortel (CLI)
				<option value="">--------
				<option value="Omnistack"<?= ($os == "Omnistack")?" selected":"" ?>>ALU Omnistack
				<option value="ISAM"<?= ($os == "ISAM")?" selected":"" ?>>ALU ISAM
				<option value="">--------
				<option value="ZDOS"<?= ($os == "ZDOS")?" selected":"" ?>>Ruckus ZDOS
				<option value="">--------
				<option value="ESX"<?= ($os == "ESX")?" selected":"" ?>>VMware ESX
				<option value="">--------
				<option value="ZyNOS"<?= ($os == "ZyNOS")?" selected":"" ?>>Zyxel ZyNOS
			</select>
			SNMP
			<select size="1" name="ver" title="HC=64bit, MC=64bit & 32bit" onchange="update();">
				<option value="1">v1
				<option value="2"<?= $ver['2'] ?>>v2
				<option value="2MC"<?= $ver['2MC'] ?>>v2MC
				<option value="2HC"<?= $ver['2HC'] ?>>v2HC
			</select>
			<img src="img/16/walk.png" title="Test 32bit Counters" onClick="walk('1.3.6.1.2.1.2.2.1.10');">
			<img src="img/16/walk.png" title="Test 64bit Counters" onClick="walk('1.3.6.1.2.1.31.1.1.1.6');">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Icon
		</td>
		<td>
			<input type="text" name="ico" value="<?= $ico ?>" class="m" onclick="select();" onchange="update();">
			<img src="img/16/icon.png" onClick="window.open('inc/browse-img.php','Icons','scrollbars=1,menubar=0,resizable=1,width=600,height=800');" title="<?= $sellbl ?> Icon">
		</td>
		<td class="rgt">
			Bridge
		</td>
		<td>
		<select size="1" name="brg" title="<?= $fwdlbl ?> <?= $lstlbl ?>" onchange="update();" >
			<option value=""> <?= $nonlbl ?>

			<option value="normal"<?= ($bfd == "normal")?" selected":"" ?>>Normal
			<option value="normalX"<?= ($bfd == "normalX")?" selected":"" ?>>Normal, IF indexed
			<option value="qbri"<?= ($bfd == "qbri")?" selected":"" ?>>Q-bridge
			<option value="qbriX"<?= ($bfd == "qbriX")?" selected":"" ?>>Q-bridge, IF indexed
			<option value="VLX"<?= ($bfd == "VLX")?" selected":"" ?>>Vlan indexed
			<option value="VXP"<?= ($bfd == "VXP")?" selected":"" ?>>VLX, w/o Port (N5K)
			<option value="Aruba"<?= ($bfd == "Aruba")?" selected":"" ?>>Aruba Controller
			<option value="CAP"<?= ($bfd == "CAP")?" selected":"" ?>>Cisco fat AP
			<option value="WLC"<?= ($bfd == "WLC")?" selected":"" ?>>Cisco Wlan Controller
			<option value="CW"<?= ($bfd == "CW")?" selected":"" ?>>HP Comware Controller
			<option value="MSM"<?= ($bfd == "MSM")?" selected":"" ?>>HP MSM Controller
			<option value="ZD"<?= ($bfd == "ZD")?" selected":"" ?>>Ruckus Zone Director
			<option value="DDWRT"<?= ($bfd == "DDWRT")?" selected":"" ?>>DD-WRT AP
		</select>
		<img src="img/16/walk.png" title="normal bridge-fwd" onClick="walk('1.3.6.1.2.1.17.4.3.1.2');">
		<img src="img/16/walk.png" title="Q-bridge-fwd, 1st #=vlid (use normal if empty)" onClick="walk('1.3.6.1.2.1.17.7.1.2.2.1.2');">
		<img src="img/16/walk.png" title="IF indexed, if numbers are different" onClick="walk('1.3.6.1.2.1.17.1.4.1.2');">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $sizlbl ?>

		</td>
		<td>
			<input type="text" name="hgt" value="<?= $hgt ?>" class="s" onclick="select();" onchange="update();"> RU
			<select size="1" name="pem" title="POWER-ETHERNET-MIB <?= $opolbl ?>" onchange="update();" >
				<option value=""> PEM->
				<option value="P"<?= ($pem == "P")?" selected":"" ?>><?= $strlbl ?>

				<option value="N"<?= ($pem == "N")?" selected":"" ?>>IF-<?= $namlbl ?>2<?= $idxlbl ?>

				<option value="S"<?= ($pem == "S")?" selected":"" ?>>Cisco-StackMib
			</select>
			<img src="img/16/walk.png" onClick="walk('1.3.6.1.2.1.105.1.3.1.1.2');" title="<?= $totlbl ?> PoE">
			<img src="img/16/walk.png" onClick="walk('1.3.6.1.2.1.105.1.1.1.3');" title="IF PoE Admin <?= $stalbl ?>">
		</td>
		<td class="rgt">
			ARP/ND
		</td>
		<td>
			<select size="1" name="arp" title="IPv4 & IPv6 <?= $adrlbl ?>" onchange="update();" >
				<option value=""> <?= $nonlbl ?>
				<option value="old"<?= ($arp == "old")?" selected":"" ?>>Old
				<option value="phy"<?= ($arp == "phy")?" selected":"" ?>>IPv4/6
				<option value="oldphy"<?= ($arp == "oldphy")?" selected":"" ?>>Old & IPv4/6
				<option value="oldip6"<?= ($arp == "oldip6")?" selected":"" ?>>Old & IPv6
				<option value="oldcie"<?= ($arp == "oldcie")?" selected":"" ?>>Old & Cisco
				<option value="cli"<?= ($arp == "cli")?" selected":"" ?>>CLI
			</select>
			<img src="img/16/walk.png" title="IPv4 (ipNetToMedia)" onClick="walk('1.3.6.1.2.1.4.22.1.2');">
			<img src="img/16/walk.png" title="IPv4/6 (ipNetToPhysical)" onClick="walk('1.3.6.1.2.1.4.35.1.4');">
			<img src="img/16/walk.png" title="IPv6 (ipv6Addr)" onClick="walk('1.3.6.1.2.1.55.1.12.1.2');">
			<img src="img/16/walk.png" title="Cisco (cInetNetToMedia)" onClick="walk('1.3.6.1.4.1.9.10.86.1.1.3.1.3');">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $serlbl ?>

		</td>
		<td>
			<input type="text" name="sn" value="<?= $sn ?>" class="l" title="OID for SN#" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.sn.value);">
		</td>
		<td class="rgt">
			<?= $dsclbl ?> <?= $prolbl ?>
		</td>
		<td>
			<input type="text" name="dpr" value="<?= $dpr ?>" class="s" title="e.g. CDP|LLDPXN" onclick="select();" onchange="update();">
			<span class="bgmain"><img src="img/16/walk.png" title="LLDP" onClick="document.bld.dpr.value = 'LLDP';walk('1.0.8802.1.1.2.1.4.1.1');update();">
				<img src="img/16/walk.png" title="+X <?= $idxlbl ?> = ifdesc, +XA <?= $idxlbl ?> = ifalias" onClick="document.bld.dpr.value = 'LLDPX';walk('1.0.8802.1.1.2.1.3.7.1.4');update();">
				<img src="img/16/walk.png" title="+XN <?= $idxlbl ?> = ifname" onClick="document.bld.dpr.value = 'LLDPXN';walk('1.0.8802.1.1.2.1.3.7.1.3');update();">
				<img src="img/16/walk.png" title="LLDP <?= $neblbl ?> IP <?= $adrlbl ?>" onClick="walk('1.0.8802.1.1.2.1.4.2.1.3');">
				<img src="img/16/walk.png" title="LLDP-MED <?= $invlbl ?>" onClick="walk('1.0.8802.1.1.2.1.5.4795.1.3.3.1');">
			</span>
			<img src="img/16/walk.png" title="Cisco discovery protocol" onClick="document.bld.dpr.value = 'CDP';walk('1.3.6.1.4.1.9.9.23.1.2.1.1');update();">
			<?php #not supported <img src="img/16/walk.png" title="Extreme discovery protocol" onClick="document.bld.dpr.value = 'EDP';walk('1.3.6.1.4.1.1916.1.13');update();"> ?>
			<img src="img/16/walk.png" title="Foundry discovery protocol" onClick="document.bld.dpr.value = 'FDP';walk('1.3.6.1.4.1.1991.1.1.3.20.1.2.1.1');update();">
			<img src="img/16/walk.png" title="Nortel discovery protocol" onClick="document.bld.dpr.value = 'NDP';walk('1.3.6.1.4.1.45.1.6.13.2.1.1');update();">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Bootimage
		</td>
		<td>
			<input type="text" name="bi" value="<?= $bi ?>" class="l" title="OID (end can be .1-5 e.g. for Zyxel) for bootimage" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.bi.value);">
		</td>
		<td class="rgt">
			<?= $deslbl ?>
		</td>
		<td>
			<input type="text" name="des" value="<?= $des ?>" class="l" title="Override standard sysdes for exotic devices/printers" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.des.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Vlan <?= $namlbl ?>
		</td>
		<td>
			<input type="text" name="vln" value="<?= $vln ?>" class="l" title="OID for Vlan names, if available" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.vln.value);">
		</td>
		<td class="rgt">
			Vlan <?= $idxlbl ?>
		</td>
		<td>
			<input type="text" name="vnx" value="<?= $vnx ?>" class="l" title="Vlname to Vlid index, if not indexed with OID" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.vnx.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $grplbl ?>

		</td>
		<td>
			<input type="text" name="grp" value="<?= $grp ?>" class="l" title="Group can be VTP domain on Cisco devices" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.grp.value);">
		</td>
		<td class="rgt">
			<?= $modlbl ?>

		</td>
		<td>
			<input type="text" name="dmo" value="<?= $dmo ?>" class="l" title="Mode (e.g. client, server or transparent for VTP devices)" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.dmo.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= substr($cfglbl,0,4) ?> <?= $chglbl ?>

		</td>
		<td>
			<input type="text" name="cfc" value="<?= $cfc ?>" class="l" title="<?= ($verb1)?"$laslbl $cfglbl $chglbl":"$cfglbl $chglbl $laslbl" ?>" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.cfc.value);">
		</td>
		<td class="rgt">
			<?= substr($cfglbl,0,4) ?> <?= $wrtlbl ?>

		</td>
		<td>
			<input type="text" name="cfw" value="<?= $cfw ?>" class="l" title="<?= ($verb1)?"$laslbl $cfglbl $wrtlbl":"$cfglbl $wrtlbl $laslbl" ?>" onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.cfw.value);">
		</td>
	</tr>
</table>
<p>

<table class="content bgmain">
	<tr class="bgsub">
		<td class="ctr b" colspan="4">
			<img src="img/16/port.png">
			<?= $porlbl ?>
			<img src="img/16/bcnl.png" class="frgt" onClick="setint('0');" title="<?= $reslbl ?>">
			<img src="img/16/idea.png" class="frgt" onClick="setint('2');" title="Standard dot3-MIBs 32bit & Mau Duplex">
			<img src="img/16/idea.png" class="frgt" onClick="setint('1');" title="Standard dot3-MIBs 64bit">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $idxlbl ?>

		</td>
		<td>
			<input type="text" name="stx" value="<?= $stx ?>" class="s" onclick="select();" onchange="update();"> <?= $sttlbl ?> &nbsp;
			<input type="text" name="enx" value="<?= $enx ?>" class="s" onclick="select();" onchange="update();"> <?= $endlbl ?>

		</td>
		<td class="rgt">
			VRF <?= $inflbl ?>

		</td>
		<td>
			<select size="1" name="vrf" title="MPLS & VRF-Lite" onchange="update();" >
				<option value=""> <?= $nonlbl ?>

				<option value="S"<?= ($vrf == "S")?" selected":"" ?>>MPLS-L3VPN-STD-MIB
				<option value="V"<?= ($vrf == "V")?" selected":"" ?>>MPLS-VPN-MIB
			</select>
			<img src="img/16/walk.png" title="MPLS-L3VPN-STD-MIB" onClick="walk('1.3.6.1.2.1.10.166.11.1.2.2.1');">
			<img src="img/16/walk.png" title="MPLS-VPN-MIB" onClick="walk('1.3.6.1.3.118.1.2.1.1.6');">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $namlbl ?>

		</td>
		<td>
			<input type="text" name="ina" value="<?= $ina ?>" class="l" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ina.value);">
		</td>
		<td class="rgt">
			<?= $adrlbl ?>

		</td>
		<td>
			<select size="1" name="ifa" title="IPv4 & IPv6 <?= $adrlbl ?>" onchange="update();" >
				<option value=""> <?= $nonlbl ?>

				<option value="old"<?= ($ifa == "old")?" selected":"" ?>>Old
				<option value="adr"<?= ($ifa == "adr")?" selected":"" ?>>IPv4/6
				<option value="oldadr"<?= ($ifa == "oldadr")?" selected":"" ?>>Old & IPv4/6
				<option value="oldip6"<?= ($ifa == "oldip6")?" selected":"" ?>>Old & IPv6
				<option value="oldcie"<?= ($ifa == "oldcie")?" selected":"" ?>>Old & Cisco
			</select>
			<img src="img/16/walk.png" title="Old (ifAddr)" onClick="walk('1.3.6.1.2.1.4.20.1.2');">
			<img src="img/16/walk.png" title="IPv4/6 (ifAddress)" onClick="walk('1.3.6.1.2.1.4.34.1.5');">
			<img src="img/16/walk.png" title="IPv6 (ipv6Addr)" onClick="walk('1.3.6.1.2.1.55.1.8.1.2');">
			<img src="img/16/walk.png" title="Cisco (IetfIpMIB)" onClick="walk('1.3.6.1.4.1.9.10.86.1.1.2.1.3');">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Alias
		</td>
		<td>
			<input type="text" name="ial" value="<?= $ial ?>" class="l" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ial.value);">
		</td>
		<td class="rgt">
			Alias <?= $idxlbl ?>
		</td>
		<td>
			<input type="text" name="iax" value="<?= $iax ?>" class="l" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.iax.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Duplex
		</td>
		<td>
			<input type="text" name="idu" value="<?= $idu ?>" class="l" title="dot3StatsDuplexStatus or MAU(1.3.6.1.2.1.26.2.1.1.11) or enterprise" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.idu.value);">
		</td>
		<td class="rgt">
			Duplex <?= $idxlbl ?>

		</td>
		<td>
			<input type="text" name="idx" value="<?= $idx ?>" class="l" title="Only set, if index is not the same as MIB2 IFindex" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.idx.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Duplex <?= substr($vallbl,0,3) ?>

		</td>
		<td>
			<input type="text" name="hdv" value="<?= $hdv ?>" class="s" title="half-duplex <?= $vallbl ?>" onclick="select();" onchange="update();"> Half &nbsp;
			<input type="text" name="fdv" value="<?= $fdv ?>" class="s" title="full-duplex <?= $vallbl ?>" onclick="select();" onchange="update();"> Full
		</td>
		<td class="rgt">
			Broadcast <?= substr($inblbl,0,3) ?>

		</td>
		<td>
			<input type="text" name="brc" value="<?= $brc ?>" class="l" title="Broadcasts entering device" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.brc.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $dcalbl ?> <?= substr($inblbl,0,3) ?>

		</td>
		<td>
			<input type="text" name="idi" value="<?= $idi ?>" class="l" title="In discard is usually in the standard IF-mib..." onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.idi.value);">
		</td>
		<td class="rgt">
			<?= $dcalbl ?> <?= substr($oublbl,0,3) ?>

		</td>
		<td>
			<input type="text" name="odi" value="<?= $odi ?>" class="l" title="...out as well, but this supports the exotic implementations" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.odi.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			PVID
		</td>
		<td>
			<input type="text" name="ivl" value="<?= $ivl ?>" class="l" title="dot1qPvid or enterprise" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ivl.value);">
		</td>
		<td class="rgt">
			PVID <?= $idxlbl ?>

		</td>
		<td>
			<input type="text" name="ivx" value="<?= $ivx ?>" class="l" title="Only set, if index is not the same as MIB2 IFindex" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ivx.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			PoE
		</td>
		<td>
			<input type="text" name="ipw" value="<?= $ipw ?>" class="l" title="PoE switch should reveal actual power delivery here" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ipw.value);">
		</td>
		<td class="rgt">
			PoE <?= $idxlbl ?>

		</td>
		<td>
			<input type="text" name="ipx" value="<?= $ipx ?>" class="l" title="Use ifnx to find index via IF name (like 1.7 for Gi1/7" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.ipx.value);">
		</td>
	</tr>
</table>
<p>

<table class="bgmain content">
	<tr class="bgsub">
		<td class="ctr b" colspan="4">
			<img src="img/16/cubs.png">
			<?= $igrp['23'] ?>
			<img src="img/16/bcnl.png" class="frgt" onClick="setmod('0');" title="<?= $reslbl ?>">
			<img src="img/16/idea.png" class="frgt" onClick="setmod('1');" title="Standard Entity-MIB">
			<img src="img/16/print.png" class="frgt" onClick="setmod('2');" title="Printsupplies MIB">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Slot
		</td>
		<td>
			<input type="text" name="msl" value="<?= $msl ?>" class="l" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.msl.value);">
		</td>
		<td class="rgt">
			<?= $stalbl ?>
		</td>
		<td>
			<input type="text" name="mst" value="<?= $mst ?>" class="l" title="Module <?= $stalbl ?>" onclick="select();" onchange="update();">
			<input type="text" name="msv" value="<?= $msv ?>" class="xs" title="OK <?= $vallbl ?>" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mst.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $clalbl ?> OID
		</td>
		<td>
			<input type="text" name="mcl" value="<?= $mcl ?>" class="l" title="Classes identify, what an actual module is" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mcl.value);">
		</td>
		<td class="rgt">
			<?= $clalbl ?> <?= $vallbl ?>

		</td>
		<td>
			<input type="text" name="mcv" value="<?= $mcv ?>" class="m" title="The actual value (e.g. Entity-MIB modules use 9" onclick="select();" onchange="update();">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $deslbl ?>

		</td>
		<td>
			<input type="text" name="mde" value="<?= $mde ?>" class="l" title="Module description" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mde.value);">
		</td>
		<td class="rgt">
			Hardware
		</td>
		<td>
			<input type="text" name="mhw" value="<?= $mhw ?>" class="l" title="Module hardware version" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mhw.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			Model
		</td>
		<td>
			<input type="text" name="mmo" value="<?= $mmo ?>" class="l" title="Sometimes an additional model# can be fetched" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mmo.value);">
		</td>
		<td class="rgt">
			Firmware
		</td>
		<td>
			<input type="text" name="mfw" value="<?= $mfw ?>" class="l" title="Module firmware version" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mfw.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $serlbl ?>
		</td>
		<td>
			<input type="text" name="msn" value="<?= $msn ?>" class="l" title="Module serial numbers" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.msn.value);">
		</td>
		<td class="rgt">
			<?= $igrp['24'] ?>
		</td>
		<td>
			<input type="text" name="msw" value="<?= $msw ?>" class="l" title="SW <?= $verlbl ?>" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.msw.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $loclbl ?>
		</td>
		<td>
			<input type="text" name="mlo" value="<?= $mlo ?>" class="l" title="<?= $cuslbl ?> <?= $deslbl ?>" onclick="select();" onchange="update();">
			<img src="img/16/walk.png" onClick="walk(document.bld.mlo.value);">
		</td>
		<td class="rgt">
		</td>
		<td>
		</td>
	</tr>
</table>
<p>

<table class="bgmain content">
	<tr class="bgsub">
		<td class="ctr b" colspan="4">
			<img src="img/16/grph.png">
			<?= $gralbl ?>
			<img src="img/16/bcnl.png" class="frgt" onClick="setrrd('0');" title="<?= $reslbl ?>">
			<img src="img/16/idea.png" class="frgt" onClick="setrrd('1');" title="Possible Standard OIDs">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			% CPU
		</td>
		<td>
			<input type="text" name="cpu" value="<?= $cpu ?>" class="l" title="Try to use a long average (e.g. 5min)" onclick="select();" onchange="update();">
			* <input type="text" name="cmu" value="<?= $cmu ?>" class="s" title="x10, x0.1..." onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.cpu.value);">
		</td>
		<td class="rgt">
			Mem <?= $frelbl ?>
		</td>
		<td>
			<input type="text" name="mcp" value="<?= $mcp ?>" class="l" title="Available memory" onclick="select();" onchange="update();">
			 * <input type="text" name="mmu" value="<?= $mmu ?>" class="s" title="x10, x0.1..." onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.mcp.value);">
		</td>
	</tr>
	<tr>
		<td class="rgt">
			<?= $tmplbl ?>
		</td>
		<td>
			<input type="text" name="tmp" value="<?= $tmp ?>" class="l" title="Could be used for other values, if temperature is not supported" onclick="select();" onchange="update();">
			 * <input type="text" name="tmu" value="<?= $tmu ?>" class="s" title="x10, x0.1..." onclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.tmp.value);">
		</td>
		<td class="rgt">
			<input type="text" name="cul" value="<?= $cul ?>" class="m" title="Custom Label;C(ounter)|G(auge);Unit" onclick="select();" onchange="update();"></th><td>
			<input type="text" name="cuv" value="<?= $cuv ?>" class="l" title="Custom Gauge OID" sonclick="select();" onchange="update();">
			<img src="img/16/brgt.png" onClick="get(document.bld.cuv.value);">
		</td>
	</tr>
</table>
</form>
<p>

<table class="bgmain content">
	<tr class="bgsub">
		<td class="ctr">
<?php  if( $isadmin and $ip) { ?>
			<form method="post" class="frgt" name="nedi" action="System-NeDi.php">
				<input type="hidden" name="mde" value="d">
				<input type="hidden" name="sed" value="a">
				<input type="hidden" name="opt" value="<?= $ip ?>">
				<input type="image" class="imgbtn" src="img/16/radr.png" value="Submit" title="<?= $dsclbl ?>">
			</form>
<?php } ?>

			<a href="mailto:def@nedi.ch?subject=<?= $so ?>.def&body=<?= $mailbody ?>" class="frgt"><img src="img/16/mail.png"  title="<?= ($verb1)?"$sndlbl NeDi":"NeDi  $sndlbl" ?>"></a>
			<form method="post" name="gen" action="<?= $self ?>.php">
				<input type="button" class="button" value="<?= $updlbl ?>" name="up" onClick="update();" title="<?= $updlbl ?> .def <?= $tim[n] ?>">
				- <input type="text" name="so" value="<?= $so ?>" class="l" title="Filename">.def
<?php  if( $isadmin) { ?>
				<input type="submit" class="button" value="<?= $wrtlbl ?>" name="wr" title="<?= $wrtlbl ?> .def <?= $fillbl ?>" disabled="true" <?= $wrw ?>>
<?php } ?>
				<p>
				<textarea rows="24" name="def" cols="100" onChange="dis='1';document.gen.wr.disabled=false;alert('Controls disabled!');">
<?= $def ?>
</textarea>
			</form>
		</td>
	</tr>
</table>
<?php
include_once ("inc/footer.php");
?>
