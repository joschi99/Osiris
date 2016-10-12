<?php
# Program: User-Accounts.php
# Programmer: Remo Rickli
#
# Create DB:
# CREATE DATABASE radius;
# GRANT ALL ON radius.* TO radius@localhost IDENTIFIED BY "radpass";
#
# cd /etc/freeradius/sql/mysql
# mysql -u radius -pradpass radius < schema.sql

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libldap.php");
include_once ("inc/timezones.php");

$_GET = sanitize($_GET);
$mde = isset( $_GET['mde']) ? $_GET['mde'] : "n";

$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();

$gr  = isset($_GET['gr']) ? $_GET['gr'] : '';
$vl  = isset($_GET['vl']) ? $_GET['vl'] : '';
$ti  = isset($_GET['ti']) ? $_GET['ti'] : 24;

$un = isset($_GET['un']) ? $_GET['un'] : '';
$un = preg_match('/[\'";$?]/',$un) ? '':$un;								# Stay in sync with index.php
$pw  = isset($_GET['pw']) ? $_GET['pw'] : '';
$ugr = isset($_GET['ugr']) ? $_GET['ugr'] : '';

$dus = isset( $_GET['dus']) ? $_GET['dus'] : "";
$dgr = isset( $_GET['dgr']) ? $_GET['dgr'] : "";

$cols = array(	"id"=>'Id',
		"username"=>$usrlbl,
		"attribute"=>$opolbl,
		"op"=>$cndlbl,
		"value"=>$vallbl,
		"groupname"=>$grplbl
		);

$link	= DbConnect($rdbhost,$rdbuser,$rdbpass,$rdbname);
$res	= DbQuery( GenQuery('radgroupreply'),$link);
while( $g = DbFetchRow($res) ){
	$grply[$g[1]][$g[2]] = $g[4]; 
}
DbFreeResult($res);
?>

<h1><?= $usrlbl ?> Radius</h1>

<?php  if( !isset($_GET['print']) ) { ?>
<table class="content" >
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="nw">
<form method="get" action="<?= $self ?>.php">
<div class="flft">
	<?php Filters(1); ?>
</div>
	<input type="submit" class="button" value="<?= $sholbl ?>">
</form>
</td>
<td>
<form method="get" action="<?= $self ?>.php">
	<img src="img/16/ugrp.png" title="<?= $grplbl ?>">
	<input type="text" name="gr" class="m" value="<?= $gr ?>" placeholder="<?= $grplbl ?>" onclick="select();">
	<img src="img/16/vlan.png" title="Vlan">
	<input type="text" name="vl" class="m" value="<?= $vl ?>" placeholder="Vlan" onclick="select();" >
	<img src="img/16/clock.png" title="Timeout [<?= $tim[h] ?>]">
	<input type="text" name="ti" class="s" value="<?= $ti ?>">
	<input type="submit" class="button" name="adg" value="<?= $addlbl ?>">
</form>

<form method="get" action="<?= $self ?>.php">
	<img src="img/16/user.png" title="<?= $usrlbl ?> <?= $namlbl ?>">
	<input type="text" name="un" class="m" placeholder="<?= $usrlbl ?>" >
	<img src="img/16/lokc.png" title="Password">
	<input type="password" name="pw" class="m">
	<img src="img/16/ugrp.png" title="<?= $grplbl ?>">
	<select size="1" name="ugr">
		<option value=""><?= $grplbl ?> >
<?php
foreach ( array_keys($grply) as $g){
	echo "\t\t<option value=\"$g\">$g\n";
}
?>
	</select>
	<input type="submit" class="button" name="adu" value="<?= $addlbl ?>">
</form>
</td>
</tr>
</table>
<p>
<?php
}
if( $ismgr ){
	if(isset($_GET['adg']) and $gr ){
		$stat = 0;
		$stat = DbQuery( GenQuery('radgroupreply','i','','','',array('groupname','attribute','op','value'),'',array($gr,'Session-Timeout','=',$ti) ),$link);
		if( $stat and $vl) $stat = DbQuery( GenQuery('radgroupreply','i','','','',array('groupname','attribute','op','value'),'',array($gr,'Tunnel-Type','=',13) ),$link);
		if( $stat and $vl) $stat = DbQuery( GenQuery('radgroupreply','i','','','',array('groupname','attribute','op','value'),'',array($gr,'Tunnel-Medium-Type','=',6) ),$link);
		if( $stat and $vl) $stat = DbQuery( GenQuery('radgroupreply','i','','','',array('groupname','attribute','op','value'),'',array($gr,'Tunnel-Private-Group-ID','=',$vl) ),$link);
		if( $stat ){echo "<h5>$grplbl $gr, $addlbl OK</h5>\n";}else{echo "<h4>".DbError($link)."</h4>\n";}
	}elseif(isset($_GET['adu']) and $un){
		$stat = 0;
		$stat = DbQuery( GenQuery('radcheck','i','','','',array('username','attribute','op','value'),'',array($un,'Cleartext-Password',':=',$pw) ),$link);
		if( $stat ) $stat = DbQuery( GenQuery('radusergroup','i','','','',array('username','groupname'),'',array($un,$ugr) ),$link);
		if( $stat ){echo "<h5>$usrlbl $un, $addlbl OK</h5>\n";}else{echo "<h4>".DbError($link)."</h4>\n";}
	}elseif($dus){
		$stat = 0;
		$stat = DbQuery( GenQuery('radcheck','d','','','',array('username'),array('='),array($dus) ),$link);
		if( $stat ) $stat =  DbQuery( GenQuery('radusergroup','d','','','',array('username'),array('='),array($dus) ),$link);
		if( $stat ){echo "<h5>$usrlbl $dus, $dellbl OK</h5>\n";}else{echo "<h4>".DbError($link)."</h4>\n";}
	}elseif($dgr){
		$stat = 0;
		$stat = DbQuery( GenQuery('radgroupreply','d','','','',array('groupname'),array('='),array($dgr) ),$link);
		if( $stat ){echo "<h5>$grplbl $dgr, $dellbl OK</h5>\n";unset($grply[$g]);}else{echo "<h4>".DbError($link)."</h4>\n";}
	}
}

if( count($in) ){
	Condition($in,$op,$st);

	$row = 0;
	TblHead("bgsub",2);
	$flt    = "?in[]=$in[0]&op[]=$op[0]&st[]=".urlencode($st[0]);
	$query	= GenQuery('radcheck','s','radcheck.*,groupname',$ord,250,$in,$op,$st,array(),'LEFT JOIN radusergroup USING (username)');
	$res	= DbQuery($query,$link);
	while( ($ru = DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		TblRow($bg);
		TblCell( $ru[0] );
		TblCell( $ru[1],'','b' );
		TblCell( $ru[2] );
		TblCell( $ru[3] );
		TblCell( $ru[4] );
?>
		<td>
			<?= $ru[5] ?>
			<a href="<?= $flt ?>&ord=<?= $ord ?>&mde=r&dus=<?= $ru[1] ?>"><img src="img/16/bcnl.png" class="frgt" title="<?= $dellbl ?>"></a>
		</td>
<?php
		echo "\t</tr>\n";
	}
	DbFreeResult($res);

	TblFoot("bgsub", 10, "$row $usrlbl".(($ord)?", $srtlbl: $ord":"") );
}elseif($_SESSION['opt']){
?>

<table class="full fixed"><tr><td class="helper">

<h2><?= $grplbl ?> <?= $rpylbl ?></h2>

<?php
$res = DbQuery( GenQuery('radusergroup','g','groupname'),$link);
if( count($grply) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/say.png"><br>
			<?= $grplbl ?> <?= $namlbl ?>

		</th>
		<th>
			<img src="img/16/vlan.png"><br>
			Vlan
		</th>
		<th colspan=2>
			<img src="img/16/clock.png"><br>
			Timeout
		</th>
	</tr>
<?php
$row = 0;
ksort( $grply);
foreach ( array_keys($grply) as $g){
	if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
	$row++;
	TblRow($bg);
	TblCell( $g,'','b' );
	TblCell( $grply[$g]['Tunnel-Private-Group-ID'],'','rgt' );
	TblCell( $grply[$g]['Session-Timeout'].' '.$tim[h],'','rgt' );
?>
		<td>
			<?= $ru[5] ?>
			<a href="?dgr=<?= $g ?>"><img src="img/16/bcnl.png" class="frgt" title="<?= $dellbl ?>"  onclick="return confirm('<?= $dellbl ?> <?= $grplbl ?> <?= $rpylbl ?>, <?= $cfmmsg ?>')"></a>
		</td>
<?php
	echo "\t</tr>\n";
}
?>
</table>
<?php
}else{
	echo "<h5>$nonlbl $vallbl</h5>";
}
DbFreeResult($res);
?>

</td><td class="helper">

<h2><?= $usrlbl ?> <?= $grplbl ?></h2>

<?php
$res = DbQuery( GenQuery('radusergroup','g','groupname'),$link);
if( DbNumRows($res) ){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/say.png"><br>
			<?= $grplbl ?> <?= $namlbl ?>

		</th>
		<th>
			<img src="img/16/ugrp.png"><br>
			<?= $poplbl ?>

		</th>
	</tr>
<?php
$row = 0;
	while( $g = DbFetchRow($res) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		TblRow($bg);
		TblCell( $g[0],'','b' );
		TblCell( $g[1],"?in[]=groupname&op[]==&st[]=".urlencode($g[0]),'','+'.Bar($g[1],'lvl100','sbar') );
		echo "\t</tr>\n";
	}
?>
</table>
<?php
}else{
	echo "<h5>$nonlbl $vallbl</h5>";
}
DbFreeResult($res);
?>

</td></tr></table>

<?php
}

include_once ("inc/footer.php");
?>
