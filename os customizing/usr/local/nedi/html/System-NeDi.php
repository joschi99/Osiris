<?php
# Program: System-NeDi.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");

$_POST = sanitize($_POST);

$mde = isset($_POST['mde']) ? $_POST['mde'] : "h";

$vrb = isset($_POST['vrb']) ? 'checked' : '';
$ndn = isset($_POST['ndn']) ? 'checked' : '';
$ins = isset($_POST['ins']) ? 'checked' : '';

$dip = isset($_POST['dip']) ? 'checked' : '';
$rte = isset($_POST['rte']) ? 'checked' : '';
$oui = isset($_POST['oui']) ? 'checked' : '';

$wco = isset($_POST['wco']) ? 'checked' : '';
$uip = isset($_POST['uip']) ? 'checked' : '';
$fqd = isset($_POST['fqd']) ? 'checked' : '';

$sed = isset($_POST['sed']) ? $_POST['sed'] : '';
$ver = isset($_POST['ver']) ? $_POST['ver'] : '';
$opt = isset($_POST['opt']) ? $_POST['opt'] : '';
$bup = isset($_POST['bup']) ? $_POST['bup'] : '';
$tst = isset($_POST['tst']) ? $_POST['tst'] : '';
$cli = isset($_POST['cli']) ? $_POST['cli'] : '';
$skp = isset($_POST['skp']) ? $_POST['skp'] : '';
$pin = isset($_POST['pin']) ? $_POST['pin'] : '';

$usr = isset($_POST['usr']) ? $_POST['usr'] : '';
$psw = isset($_POST['psw']) ? $_POST['psw'] : '';

$_GET = sanitize($_GET);
$add = isset($_GET['add']) ? preg_replace('/[^\w\.]+/','',$_GET['add']) : '';
$vrb = isset($_GET['vrb']) ? 'checked' : $vrb;

$cmd = "$nedipath/nedi.pl";

if($mde == "i"){
	$cmd .= " -i $usr $psw";
}elseif($mde == "y"){
	$cmd .= " -y";
}elseif($opt and $mde == "n"){
	$cmd .= (($vrb)?" -vdb":"")." -N $opt";
}elseif($opt and $mde == "s"){
	$cmd .= (($vrb)?" -v":"")." -sid ".(($sed=='a')?" -N $opt":"-O\"$opt\"");
	if($pin){$cmd .= " -P$pin ";}

}elseif($mde == "d"){
	$arg = '';

	if($vrb){$arg .= "v";}
	if($ndn){$arg .= "n";}
	if($ins){$arg .= "T";}
	if($dip){$arg .= "p";}
	if($oui){$arg .= "o";}
	if($rte){$arg .= "r";}
	if($wco){$arg .= "W";}
	if($uip){$arg .= "f";}
	if($fqd){$arg .= "F";}
	if($arg){$arg = "-" . $arg;}
	if($ver){$arg .= " -V$ver ";}

	if($bup){$arg .= " -".$bup;}
	if($tst){$arg .= " -t$tst ";}
	if($pin){$arg .= " -P$pin ";}
	if($cli){$arg .= " -c$cli ";}
	if($skp){$arg .= " -S$skp ";}

	$cmd .= " $arg";
	if($sed == 'A' and $opt != 'all'){
		$cmd .= " -A \"$opt\"";
	}elseif($sed){
		$cmd .= " -$sed $opt";
	}
}elseif($mde == "h"){
	$cmd .= " --help";
}

?>
<script language="JavaScript">
<!--
var interval = "";

function RockBottom(){

	if( interval ){
		window.clearInterval(interval);
		interval = 0;
		document.getElementById('nedi').setAttribute('class', 'textpad code pre txta tqrt');
	}else{
		interval = window.setInterval("Down()",500);
		document.getElementById('nedi').setAttribute('class', 'textpad code pre warn tqrt');
	}
}

function Down(){
	window.scrollTo(0, document.body.scrollHeight);
}


function ConfirmSubmit(){

	if (document.nedi.mde[5].checked == true){
		if( confirm('NeDi <?= $reslbl ?>, <?= $cfmmsg ?>') ){
			document.nedi.submit();
		}else{
			return;
		}
	}
	document.nedi.submit();
}

// rufers idea
function UpCmd(){

	var arg = "";
	if(document.nedi.mde[0].checked){
		if(document.nedi.vrb.checked){arg += "v"}
		if(document.nedi.ndn.checked){arg += "n"}
		if(document.nedi.ins.checked){arg += "T"}
		if(document.nedi.dip.checked){arg += "p"}
		if(document.nedi.oui.checked){arg += "o"}
		if(document.nedi.rte.checked){arg += "r"}
		if(document.nedi.wco.checked){arg += "W"}
		if(document.nedi.fqd.checked){arg += "F"}
		if(document.nedi.uip.checked){arg += "f"}
		if(arg != ""){arg = "-" + arg}

		if(document.nedi.bup.selectedIndex){arg += " -" + document.nedi.bup.options[document.nedi.bup.selectedIndex].value}
		if(document.nedi.tst.selectedIndex){arg += " -t" + document.nedi.tst.options[document.nedi.tst.selectedIndex].value}
		if(document.nedi.pin.selectedIndex){arg += " -P" + document.nedi.pin.options[document.nedi.pin.selectedIndex].value}
		if(document.nedi.cli.selectedIndex){arg += " -c" + document.nedi.cli.options[document.nedi.cli.selectedIndex].value}
		if(document.nedi.skp.value){arg += " -S" + document.nedi.skp.value}
		if(document.nedi.sed.selectedIndex){arg += " -" + document.nedi.sed.options[document.nedi.sed.selectedIndex].value + document.nedi.opt.value}
		if(document.nedi.ver.selectedIndex){arg += " -V" + document.nedi.ver.options[document.nedi.ver.selectedIndex].value}
	}else if(document.nedi.mde[1].checked){
		if(document.nedi.vrb.checked){arg = "-vdb"}
		arg += " -N " + document.nedi.opt.value;
	}else if(document.nedi.mde[2].checked){
		if(document.nedi.vrb.checked){arg = "-v"}
		arg += " -sid " + ((document.nedi.sed.selectedIndex == 1)?'-N':'-O') + document.nedi.opt.value;
		if(document.nedi.pin.selectedIndex){arg += " -P" + document.nedi.pin.options[document.nedi.pin.selectedIndex].value}
	}else if(document.nedi.mde[3].checked){
		arg = "-y";
	}else if(document.nedi.mde[4].checked){
		arg = "--help";
	}

		cmd = document.getElementById('cmd');
		cmd.innerHTML = "<?= $nedipath ?>/nedi.pl " + arg;
		cmd.style.opacity = 0.6;
}
//--></script>

<h1><?= (($verb1)?"$cmdlbl NeDi":"NeDi $cmdlbl") ?></h1>

<?php  if( $isadmin ){ ?>
<form name="nedi" action="<?= $self ?>.php" method="post">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top">
	<h3>
		<input type="radio" name="mde" value="d" <?= ($mde == "d")?"checked":"" ?> onchange="UpCmd();"> <?= $dsclbl ?>
		<input type="radio" name="mde" value="n" <?= ($mde == "n")?"checked":"" ?> onchange="UpCmd();"> DNS <?= $namlbl ?>
		<input type="radio" name="mde" value="s" <?= ($mde == "s")?"checked":"" ?> onchange="UpCmd();"> <?= $srvlbl ?>
		<input type="radio" name="mde" value="y" <?= ($mde == "y")?"checked":"" ?> onchange="UpCmd();"> Definitions
		<input type="radio" name="mde" value="h" <?= ($mde == "h")?"checked":"" ?> onchange="UpCmd();"> Help
	</h3>

	<table>
		<tr>
			<td>
				<select size="1" name="sed" onchange="UpCmd();">
					<option value="">Seed ->
					<option value="a" <?= ($sed == "a")?" selected":"" ?> ><?= $adrlbl ?>
					<option value="A" <?= ($sed == "A")?" selected":"" ?> >Devices
					<option value="O" <?= ($sed == "O")?" selected":"" ?> >Nodes
				</select>
				<input type="text" name="opt" value="<?= htmlspecialchars($opt) ?>" class="m" placeholder="<?= $opolbl ?>" onfocus="select();" onchange="UpCmd();">
				<select size="1" name="ver" onchange="UpCmd();">
					<option value=""><?= $verlbl ?> ->
					<option value="1" <?= ($ver == 1)?" selected":"" ?> >v1
					<option value="2" <?= ($ver == 2)?" selected":"" ?> >v2c
					<option value="3" <?= ($ver == 3)?" selected":"" ?> >v3
				</select>
				<img src="img/16/db.png" align="right" onClick="document.nedi.opt.value='all';document.nedi.sed.selectedIndex=2;UpCmd();" title="DB <?= $addlbl ?>">
			</td>
			<td>
				<input type="checkbox" name="vrb" <?= $vrb ?> title="<?= (($verb1)?"$sholbl $deslbl":"$deslbl $sholbl") ?>" onchange="UpCmd();"> Verbose
			</td>
			<td>
				<input type="checkbox" name="ndn" <?= $ndn ?> title="<?= $nonlbl ?> Node <?= $namlbl ?>" onchange="UpCmd();"> No DNS
			</td>
			<td>
				<input type="checkbox" name="ins" <?= $ins ?> title="Device Provisioning" onchange="UpCmd();"> Install
			</td>
		</tr>
		<tr>
			<td>
				<select size="1" name="bup" onchange="UpCmd();">
					<option value=""><?= $cfglbl ?> ->
					<option value="b" <?= ($bup == "b")?" selected":"" ?> >DB <?= $buplbl ?>
					<option value="B0" <?= ($bup == "B0")?" selected":"" ?> >DB & <?= $fillbl ?>
					<option value="B5" <?= ($bup == "B5")?" selected":"" ?> >DB & <?= $fillbl ?> (<?= $maxlbl ?> 5)
					<option value="B10" <?= ($bup == "B10")?" selected":"" ?> >DB & <?= $fillbl ?> (<?= $maxlbl ?> 10)
				</select>
				<input type="text" name="skp" value="<?= $skp ?>" class="m" placeholder="<?= $skplbl ?>" onfocus="select();" onchange="UpCmd();">
				<img src="img/16/port.png" align="right" onClick="document.nedi.skp.value='adobewituv';UpCmd();" title="Skip IF">
				<img src="img/16/nods.png" align="right" onClick="document.nedi.skp.value='AF';UpCmd();" title="Skip Nodes">
				<img src="img/16/grph.png" align="right" onClick="document.nedi.skp.value='Gg';UpCmd();" title="Skip <?= $gralbl ?>">
			</td>
			<td>
				<input type="checkbox" name="dip" <?= $dip ?> title="LLDP, CDP, FDP, NDP..." onchange="UpCmd();"> <?= $prolbl ?>
			</td>
			<td>
				<input type="checkbox" name="oui" <?= $oui ?> title="<?= (($verb1)?"$dsclbl OUI $venlbl":"OUI $venlbl $dsclbl") ?>" onchange="UpCmd();"> OUI
			</td>
			<td>
				<input type="checkbox" name="rte" <?= $rte ?> title="<?= (($verb1)?"$dsclbl Routes":"Routes $dsclbl") ?>" onchange="UpCmd();"> 	Route
			</td>
		</tr>
		<tr>
			<td>
				<select size="1" name="pin" onchange="UpCmd();">
					<option value="">Ping ->
					<option value="1" <?= ($pin == "1")?" selected":"" ?> >1 <?= $tim[s] ?>
					<option value="2" <?= ($pin == "2")?" selected":"" ?> >2 <?= $tim[s] ?>
					<option value="3" <?= ($pin == "3")?" selected":"" ?> >3 <?= $tim[s] ?>
				</select>
				<select size="1" name="tst" onchange="UpCmd();">
					<option value=""><?= $tstlbl ?> ->
					<option value="a" <?= ($tst == "a")?" selected":"" ?> ><?= $acslbl ?>
					<option value="i" <?= ($tst == "i")?" selected":"" ?> ><?= $inflbl ?>
					<option value="s" <?= ($tst == "s")?" selected":"" ?> ><?= $srclbl ?>
				</select>
				<select size="1" name="cli" onchange="UpCmd();">
					<option value="">CLI <?= $sndlbl ?> ->
<?php
foreach (glob("$nedipath/cli/*") as $f){
	if( !is_dir($f) ){
		$l = substr($f,strlen("$nedipath/cli/") );
		echo "\t\t\t<option value=\"$l\" ".( ($cli == $l)?" selected":"").">$l\n";
	}
}
?>
				</select>
			</td>
			<td>
				<input type="checkbox" name="wco" <?= $wco ?> title="<?= $dsclbl ?> Writecommunity" onchange="UpCmd();"> <?= $wrtlbl ?>
			</td>
			<td>
				<input type="checkbox" name="fqd" <?= $fqd ?> title="Device <?= $namlbl ?> & Domain" onchange="UpCmd();"> FQDN
			</td>
			<td>
				<input type="checkbox" name="uip" <?= $uip ?> title="<?= "IP $adrlbl = $namlbl" ?>" onchange="UpCmd();"> DevIP
			</td>
		</tr>
	</table>
</td>
<td class="top">
	<h3>
		<input type="radio" name="mde" value="i" <?= ($mde == "i")?"checked":"" ?>> Init
	</h3>

	<img src="img/16/ucfg.png" title="DB Admin"> <input type="text" name="usr" class="m" value="updatedb"><p>
	<img src="img/16/loko.png" title="Password"> <input type="password" name="psw" class="m">
</td>
<td class="ctr s">
	<input type="button" class="button" name="go" value="<?= $cmdlbl ?>" onClick="ConfirmSubmit();">
</td>
</tr>
</table>
</form>
<p>

<?php }else{ ?>
<form name="nedi" action="<?= $self ?>.php" method="get">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr">
	<img src="img/16/dev.png" title="Device">
	<input type="text" name="add" value="<?= $add ?>" class="m" placeholder="IP <?= $adrlbl ?>" onfocus="select();" onchange="UpCmd();">
	<img src="img/16/say.png" title="Verbose">
	<input type="checkbox" name="vrb" <?= $vrb ?> onchange="UpCmd();">
</td>
<td class="ctr s">
	<input type="submit" class="button" name="go" value="<?= $dsclbl ?>">
</td>
</tr>
</table>
</form>
<p>
<?php }


if($add){
?>

<div class="textpad code pre txta tqrt" id="nedi" onClick="RockBottom();">
<?php
	system("$nedipath/nedi.pl -a $add ".(($vrb)?" -v":"")." 2>&1");
}elseif($isadmin){
?>

<h2 id="cmd"><?= $cmd ?></h2>

<div class="textpad code pre txta tqrt" id="nedi" onClick="RockBottom();">
<?php
	session_write_close();
	ob_end_flush();

	if($mde == 'y'){
		$out =shell_exec("$cmd 2>&1");
		echo preg_replace('/([\d\.]+)\.def/','<a href="Other-Defgen.php?so=$1">$1</a>',$out);
	}else{
		system("$cmd 2>&1");
	}
?>
</div>
<?php } ?>
</div>
<?php

include_once ("inc/footer.php");
?>
