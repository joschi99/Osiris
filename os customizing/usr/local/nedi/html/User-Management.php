<?php
# Program: User-Accounts.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libldap.php");
include_once ("inc/timezones.php");

$_GET = sanitize($_GET);
$ord = isset( $_GET['ord']) ? $_GET['ord'] : "";
$grp = isset( $_GET['grp']) ? $_GET['grp'] : "";
$del = isset( $_GET['del']) ? $_GET['del'] : "";

$usr = isset($_GET['usr']) ? $_GET['usr'] : "";
$usr  = preg_match('/[\'";$?]/',$usr) ? '':$usr;							# Stay in sync with index.php
$eml = isset($_GET['eml']) ? $_GET['eml'] : "";
$phn = isset($_GET['phn']) ? $_GET['phn'] : "";

$inv = isset($_GET['inv']) ? $_GET['inv'] : "";
$opv = isset($_GET['opv']) ? $_GET['opv'] : "";
$stv = isset($_GET['stv']) ? $_GET['stv'] : "";

$cols = array(	"usrname"=>$namlbl,
		"email"=>$adrlbl,
		"phone"=>"Phone",
		"comment"=>$cmtlbl,
		"time"=>"$usrlbl $addlbl",
		"lastlogin"=>$laslbl,
		"viewdev"=>"$fltlbl Devices",
		"grpNS"=>$grplbl,
		"guiNS"=>"GUI",
		"cmdNS"=>$cmdlbl
		);

$gnam = array(	"1" =>"Admins",
		"2" =>$netlbl,
		"4" =>"Helpdesk",
		"8" =>$monlbl,
		"16"=>$mgtlbl,
		"32"=>$mlvl['30']
		);

$dcol = array(	"device"=>"Device",
		"devip"=>"IP $adrlbl",
		"serial"=>"$serlbl",
		"type"=>"Device $typlbl",
		"services"=>$srvlbl,
		"description"=>$deslbl,
		"devos"=>"Device OS",
		"bootimage"=>"Bootimage",
		"location"=>$loclbl,
		"contact"=>$conlbl,
		"devgroup"=>$grplbl,
		"devmode"=>"Mode",
		"readcomm"=>"SNMP $realbl",
		"writecomm"=>"SNMP $wrtlbl",
		"login"=>"Login",
		"cpu"=>"% CPU",
		"memcpu"=>"$memlbl $frelbl",
		"temp"=>$tmplbl,
		"cusvalue"=>"$cuslbl $vallbl"
		);

?>
<h1><?= $usrlbl ?> <?= $mgtlbl ?></h1>

<?php  if( !isset($_GET['print']) ) { ?>
<form method="get" action="<?= $self ?>.php">
<table class="content" >
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
	<select size="1" name="grp" onchange="this.form.submit();">
		<option value=""><?= $fltlbl ?> <?= $grplbl ?> >
		<option value="1" <?= ($grp == "1")?" selected":"" ?> ><?= $gnam['1'] ?>
		<option value="2" <?= ($grp == "2")?" selected":"" ?> ><?= $gnam['2'] ?>
		<option value="4" <?= ($grp == "4")?" selected":"" ?> ><?= $gnam['4'] ?>
		<option value="8" <?= ($grp == "8")?" selected":"" ?> ><?= $gnam['8'] ?>
		<option value="16" <?= ($grp == "16")?" selected":"" ?> ><?= $gnam['16'] ?>
		<option value="32" <?= ($grp == "32")?" selected":"" ?> ><?= $gnam['32'] ?>
	</select>
	<input type="hidden" name="ord" value="<?= $ord ?>">
</td>
<td class="ctr">
	<img src="img/16/user.png" title="<?= $usrlbl ?> <?= $namlbl ?>">
	<input type="text" name="usr" class="m" placeholder="<?= $usrlbl ?>" >
	<img src="img/16/mail.png" title="Email">
	<input type="text" name="eml" class="l" placeholder="Email">
	<img src="img/16/sms.png" title="Phone#">
	<input type="text" name="phn" class="m" placeholder="Phone#">&nbsp;
</td>
<td class="ctr s">
	<input type="submit" class="button" name="add" value="<?= $addlbl ?>">
	<?php  if( strstr($guiauth,'ldap') ) { ?>
	<input type="submit" class="button" name="ldap" value="<?= $addlbl ?> LDAP">
	<?php } ?>
</td>
</table>
</form>
<p>
<?php
}
$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if (isset($_GET['add']) and $usr){
	$pass = hash('sha256','NeDi'.$usr.$usr);
	$query	= GenQuery('users','i','','','',array('usrname','password','email','phone','time','language','theme'),'',array($usr,$pass,$eml,$phn,time(),'english','default') );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>$usrlbl $_GET[usr]: $addlbl OK</h5>\n";}
}elseif(isset($_GET['ldap']) and $usr){
	$now = time();
	if ( user_from_ldap_servers($usr) ){
		$query	= GenQuery('users','i','','','',array('usrname','email','phone','password','time','language','theme'),'',array($fields['ldap_login'] ,$fields['ldap_field_email'],$fields['ldap_field_phone'],'',time(),'english','default') );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>$usrlbl $_GET[usr]: $addbtn OK</h5>\n";}
	}else{
		echo "<h4>No $usrlbl $_GET[usr] in LDAP!</h4>\n";
	}
}elseif(isset($_GET['psw']) ){
	$pass = hash("sha256","NeDi".$_GET['psw'].$_GET['psw']);
	$query	= GenQuery('users','u',"usrname = '$_GET[psw]'",'','',array('password'),array(),array($pass) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>$usrlbl $_GET[psw]: $reslbl password OK</h5>\n";}
}elseif(isset($_GET['gup']) ){
	$query	= GenQuery('users','u',"usrname = '$_GET[usr]'",'','',array('groups'),array(),array($_GET['gup']) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>$usrlbl $grplbl $updlbl OK</h5>\n";}
}elseif($del){
	$query	= GenQuery('users','d','','','',array('usrname'),array('='),array($_GET['del']) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>$usrlbl $_GET[del]: $dellbl OK</h5>\n";}
}elseif($stv){
	$viewdev = ($stv == '-')?'':"$inv $opv $stv";
	$query	= GenQuery('users','u',"usrname = '$_GET[usr]'",'','',array('viewdev'),array(),array($viewdev) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>\n";}else{echo "<h5>Device $acslbl $updlbl OK</h5>\n";}
}
?>

<h2><?= $usrlbl ?> <?= $lstlbl ?></h2>

<?php
TblHead("bgsub",2);

if ($grp){
	$query	= GenQuery('users','s','*',$ord,'',array('groups'),array('&'),array($grp) );
}else{
	$query	= GenQuery('users','s','*',$ord );
}
$res	= DbQuery($query,$link);
if($res){
	$row = 0;
	while( ($dbu = DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		list($cc,$lc) = Agecol($dbu[5],$dbu[6],$row % 2);
		TblRow($bg);
?>
		<td class="<?= $bi ?> ctr s">
			<?=Smilie($dbu[0]) ?><br>
			<?= $dbu[0] ?>

		</td>
		<td class="nw">
			<?= $dbu[3] ?>

		</td>
		<td class="nw">
			<?= $dbu[4] ?>

			</td>
		<td class="nw">
			<?= $dbu[7] ?>

		</td>
		<td style="background-color:#<?= $cc ?>">
			<?= (date($_SESSION['timf'],$dbu[5])) ?>

		</td>
		<td style="background-color:#<?= $lc ?>">
			<?= (date($_SESSION['timf'],$dbu[6])) ?>

		</td>
		<td>
<?php  if( !($dbu[2] & 1) ) { ?>
<form method="get">
	<input type="hidden" name="usr" value="<?= $dbu[0] ?>">
	<select size="1" name="inv">
<?php

$vid = explode(" ",$dbu[15]);
$inv = array_shift($vid);
$opv = array_shift($vid);										# Operator has no spaces (not regexp is !~ since 1.0.9)
$stv = implode(' ',preg_replace('/["\']/','',$vid));							# Now string can contain spaces, pre 1.0.9 had quotes
foreach ($dcol as $k => $v){
       echo "\t<option value=\"$k\"".( ($inv == $k)?" selected":"").">$v\n";
}
?>
	</select>
	<select size="1" name="opv">
<?php selectbox("oper",$opv) ?>
	</select><br>
	<input type="text" name="stv" size="16" value="<?= $stv ?>" onfocus="select();"  onchange="this.form.submit();" title="Device <?= $acslbl ?> <?= $limlbl ?>">
<?= (($stv)?"\t\t\t\t<a href=\"Devices-List.php?in[]=$inv&op[]=$opv&st[]=$stv\"><img src=\"img/16/eyes.png\" title=\"Device $lstlbl\"></a>":"") ?>
</form> 
<?php } ?>
		</td>
		<td class="ctr b">
<?php
GroupButton($dbu[0],$dbu[2],1,'ucfg');
GroupButton($dbu[0],$dbu[2],2,'net');
GroupButton($dbu[0],$dbu[2],4,'supp');
GroupButton($dbu[0],$dbu[2],8,'bino');
GroupButton($dbu[0],$dbu[2],16,'umgr');
GroupButton($dbu[0],$dbu[2],32,'ugrp');
?>
		</td>
		<td>
			<?= $dbu[8] ?> <?= $dbu[9] ?><br>
			<?= $tzone[substr($dbu[14],-3)] ?>

		</td>
		<td class="ctr b">
			<a href="Devices-List.php?in[]=contact&op[]=%3D&st[]=<?= $dbu[0] ?>"><img src="img/16/dev.png" title="Device <?= $lstlbl ?>"></a>
			<a href="Assets-List.php?in[]=usrname&op[]==&st[]=<?= $dbu[0] ?>"><img src="img/16/list.png" title="<?= $invlbl ?> <?= $lstlbl ?>"></a>
			<a href="Monitoring-Events.php?in%5B%5D=class&op%5B%5D=LIKE&st%5B%5D=usr%25&co%5B%5D=AND&in%5B%5D=info&op%5B%5D=~&st%5B%5D=<?= $dbu[0] ?>"><img src="img/16/bell.png" title="<?= $msglbl ?> <?= $lstlbl ?>"></a>
			<a href="?grp=<?= $grp ?>&ord=<?= $ord ?>&psw=<?= $dbu[0] ?>"><img src="img/16/key.png" title="Password <?= $reslbl ?>" onclick="return confirm('<?= $reslbl ?>, <?= $cfmmsg ?>')"></a>
			<a href="?grp=<?= $grp ?>&ord=<?= $ord ?>&del=<?= $dbu[0] ?>"><img src="img/16/bcnl.png" title="<?= $dellbl ?>" onclick="return confirm('<?= $dellbl ?>, <?= $cfmmsg ?>')"></a>
		</td>
	</tr>
<?php
	}
	DbFreeResult($res);
}else{
	print DbError($link);
}
TblFoot("bgsub", 10, "$row $usrlbl".(($ord)?", $srtlbl: $ord":"") );

include_once ("inc/footer.php");

//===================================================================
// Draw group button
function GroupButton($us,$st,$gp,$ic){
	
	global $gnam,$grp,$ord,$addlbl,$dellbl;

	if($st & $gp){
		echo "<a href=\"?grp=$grp&ord=$ord&usr=$us&gup=".($st-$gp)."\">\n";
		echo "<img src=\"img/16/$ic.png\" title=\"$gnam[$gp]: $dellbl\"></a>\n";
	}else{
		echo "<a href=\"?grp=$grp&ord=$ord&usr=$us&gup=".($st+$gp)."\">\n";
		echo "<img src=\"img/16/bcls.png\" title=\"$gnam[$gp]: $addlbl\"></a>\n";
	}
}
?>
