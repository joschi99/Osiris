<?php
# Program: Monitoring-Health.php
# Programmer: Remo Rickli

$refresh   = 60;
$exportxls = 0;
$yesterday = time() - 86400;

ini_set('default_socket_timeout', 1);

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");

error_reporting( ~'E_Notice');										# Don't display notices with debug, due to optimized creation of (large) hashes

$_GET = sanitize($_GET);
$reg  = isset($_GET['reg']) ? $_GET['reg'] : '';
$cty  = isset($_GET['cty']) ? $_GET['cty'] : '';
$bld  = isset($_GET['bld']) ? $_GET['bld'] : '';
$flr  = isset($_GET['fl']) ? $_GET['fl'] : "";
$rom  = isset($_GET['rm']) ? $_GET['rm'] : "";

if($_SESSION['opt']) $map = $_SESSION['tmap'];

$loc   = TopoLoc($reg,$cty,$bld);
$evloc = ($loc)?"&co[]=AND&in[]=location&op[]=LIKE&st[]=".urlencode($loc):'';
$rploc = ($loc)?"&in[]=location&op[]=LIKE&st[]=".urlencode($loc):'';
$jdev  = ($_SESSION['view'] or $loc)?'LEFT JOIN devices USING (device)':'';				# Only join on devs if required makes it faster!

$shrrd = ($reg or !$_SESSION['gsiz'] or $_SESSION['view'])?0:$_SESSION['gsiz'];
$isiz  = ($shrrd == 2)?"16":"32";

?>
<h1>Monitoring Health</h1>

<form method="get" name="dynfrm" action="<?= $self ?>.php">
	<input type="hidden" name="reg" value="<?= $reg ?>">
	<input type="hidden" name="cty" value="<?= $cty ?>">
	<input type="hidden" name="bld" value="<?= $bld ?>">
</form>

<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr top">
	<h3>
		<a href="Reports-Monitoring.php?rep[]=mav<?= $rploc ?>"><img src="img/16/dbin.png" title="<?= $avalbl ?> <?= $stslbl ?>"></a>
		<a href="Monitoring-Timeline.php?det=level&bsz=si<?= $rploc ?>"><img src="img/16/news.png" title="<?= $msglbl ?> <?= $hislbl ?>"></a>
		<?= $stalbl ?>

	</h3>

<?php

$link  = DbConnect($dbhost,$dbuser,$dbpass,$dbname);

TopoTable($reg,$cty,$bld,$flr,$rom,2);

if(!$shrrd) StatusIncidents($loc,0,$isiz);

StatusMon($loc,$shrrd);

?>
</td>
<td class="ctr top">
	<h3>
		<a href="Reports-Interfaces.php?rep[]=trf<?= $rploc ?>"><img src="img/16/bbup.png" title="<?= $trflbl ?> <?= $stslbl ?>"></a>
		<a href="Reports-Combination.php?rep=poe<?= $rploc ?>"><img src="img/16/batt.png" title="PoE <?= $stslbl ?>"></a>
		<?= $lodlbl ?>

	</h3>

<?php
if($shrrd){
?>
	<a href="Devices-Graph.php?dv=Totals&if[]=ttr&sho=1"><img src="inc/drawrrd.php?t=ttr&s=<?= $shrrd ?>" title="<?= $totlbl ?> <?= $acslbl ?> <?= $trflbl ?>"></a>
	<a href="Devices-Graph.php?dv=Totals&if[]=tpw&sho=1"><img src="inc/drawrrd.php?t=tpw&s=<?= $shrrd ?>" title="<?= $totlbl ?> PoE <?= $lodlbl ?>"></a>
<?php
}

StatusIf($loc,'bbup',$shrrd);
StatusIf($loc,'bbdn',$shrrd);
StatusPoE($loc,$shrrd);

?>
</td>
<td class="ctr top">
	<h3>
		<a href="Reports-Interfaces.php?rep[]=err<?= $rploc ?>"><img src="img/16/brup.png" title="<?= $errlbl ?> <?= $stslbl ?>"></a>
		<a href="Reports-Interfaces.php?rep[]=dis<?= $rploc ?>"><img src="img/16/bdis.png" title="<?= $dsalbl ?> <?= $tim['t'] ?>"></a>
		<?= $errlbl ?>

	</h3>

<?php
if($shrrd){
?>
	<a href="Devices-Graph.php?dv=Totals&if[]=ter&sho=1"><img src="inc/drawrrd.php?t=ter&s=<?= $shrrd ?>" title="<?= $totlbl ?> non-Wlan <?= $errlbl ?>"></a>
	<a href="Devices-Graph.php?dv=Totals&if[]=ifs&sho=1"><img src="inc/drawrrd.php?t=ifs&s=<?= $shrrd ?>" title="<?= $stalbl ?> <?= $sumlbl ?>"></a>
<?php
}
StatusIf($loc,'brup',$shrrd);
StatusIf($loc,'brdn',$shrrd);
if(!$shrrd) StatusDsc($loc,$srrd,$isiz);
#too slow StatusIf($loc,'bdis',$shrrd);
?>
</td>
<td class="ctr top m">
	<h3 title="<?= $nonlbl ?> <?= $updlbl ?>" onClick="stop_countdown(interval);">
		<img src="img/16/exit.png">
		<span id="counter"><?= $refresh ?></span>
	</h3>

<?php
StatusCpu($loc,$shrrd,$isiz);
StatusMem($loc,$shrrd,$isiz);
StatusTmp($loc,$shrrd,$isiz);

if($shrrd){
	StatusIncidents($loc,$shrrd,$isiz);
	StatusDsc($loc,$shrrd,$isiz);
}
?>
</td>
</tr>
</table>
<p>

<?php

if($_SESSION['lim'] > 2){
?>

<h2><?= $msglbl ?> <?= $tim['t'] ?></h2>

<table class="full"><tr><td class="helper">

<h3><?= $levlbl ?></h3>

<?php
	$query	= GenQuery('events','g','level','level desc',$_SESSION['lim'],array('time','location'),array('>','like'),array($yesterday,$loc),array('AND'),$jdev);
	$res	= DbQuery($query,$link);
	if($res){
		$nlev = DbNumRows($res);
		if($nlev){
?>
<table class="content">
	<tr class="bgsub">
		<th class="xs">
			<img src="img/16/idea.png"><br><?= $levlbl ?>

		</th>
		<th>
			<img src="img/16/bell.png"><br><?= $msglbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			while( ($m = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$mbar = Bar($m[1],'lvl10','sbar');
				$mtit = ($m[0]>30)?$mlvl[$m[0]]:$mlvl[$m[0]*10].", $acklbl OK";
				echo "\t<tr class=\"$bg\">\n\t\t<td class=\"ctr ".$mbak[$m[0]]."\">\n\t\t\t<img src=\"img/16/".$mico[$m[0]].".png\" title=\"$mtit\">\n\t\t</td>\n";
				echo "\t\t<td class=\"nw\">\n\t\t\t$mbar <a href=\"Monitoring-Events.php?in[]=level&op[]==&st[]=$m[0]$evloc\">$m[1]</a>\n\t\t</td>\n\t</tr>\n";
			}
			echo "</table>\n";
		}else{
			echo "<p><h5>$nonlbl $msglbl</h5>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
?>

</td><td class="helper">

<?php if($_SESSION['lim'] > 5){ ?>

<h3><?= $clalbl ?></h3>
<?php
	$query	= GenQuery('events','g','class','cnt desc',$_SESSION['lim'],array('time','location'),array('>','like'),array($yesterday,$loc),array('AND'),$jdev);
	$res	= DbQuery($query,$link);
	if($res){
		$nlev = DbNumRows($res);
		if($nlev){
?>
<table class="content">
	<tr class="bgsub">
		<th class="xs">
			<img src="img/16/abc.png"><br><?= $clalbl ?>

		</th>
		<th>
			<img src="img/16/bell.png"><br><?= $msglbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			while( ($m = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				list($ei,$et)   = EvClass($m[0]);
				$mbar = Bar($m[1],'lvl10','sbar');
				echo "\t<tr class=\"$bg\">\t\t<td class=\"ctr $bi\">\n\t\t\t<img src=\"$ei\" title=\"$et\">\n\t\t</td>\n";
				echo "\t\t<td class=\"nw\">\n\t\t\t$mbar <a href=\"Monitoring-Events.php?in[]=class&op[]==&st[]=$m[0]$evloc\">$m[1]</a>\n\t\t</td>\n\t</tr>\n";
			}
			echo "</table>\n";
		}else{
			echo "<p><h5>$nonlbl $msglbl</h5>";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
?>

</td><td class="helper">

<h3><?= $srclbl ?></h3>
<?php
	$query	= GenQuery('events','g','source','cnt desc',$_SESSION['lim'],array('time','location'),array('>','like'),array($yesterday,$loc),array('AND'),$jdev);
	$res	= DbQuery($query,$link);
	if($res){
		$nlev = DbNumRows($res);
		if($nlev){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/say.png"><br><?= $srclbl ?>

		</th>
		<th>
			<img src="img/16/bell.png"><br><?= $msglbl ?>

		</th>
	</tr>
<?php
			$row = 0;
			while( ($r = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$s    = substr($r[0],0,$_SESSION['lsiz']);		# Shorten sources
				$mbar = Bar($r[1],'lvl10','sbar');
				echo "\t<tr class=\"$bg\">\n\t\t<td class=\"lft $bi b\" title=\"$r[0]\">\n\t\t\t$s\n\t\t</td>\n";
				echo "\t\t<td class=\"nw\">\n\t\t\t$mbar <a href=\"Monitoring-Events.php?in[]=source&op[]==&st[]=".urlencode($r[0])."$evloc\">$r[1]</a>\n\t\t</td>\n\t</tr>\n";
			}
			echo "</table>\n";
		}else{
			echo "<p><h5>$nonlbl $msglbl</h5>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
?>

</td><td class="helper">

<?php } ?>

<h3><?= $mlvl[200] ?> & <?= $mlvl[250] ?> <?= $lstlbl ?></h3>

<?php
	Events($_SESSION['lim'],array('level','time','location'),array('>=','>','like'),array(200,$yesterday,$loc),array('AND','AND'),($jdev)?1:0);

	echo "\n\n</td></tr></table>\n\n";
}

if($_SESSION['col']){

	if(!$reg) $leok = 1;
	if( count($dreg) == 1 ){
		$rarr = array_keys($dreg);
		$reg = $rarr[0];
		if( count($dcity[$reg]) == 1 ){
			$carr = array_keys($dcity[$reg]);
			$cty = $carr[0];
		}
	}

	if(!$reg){
		TopoRegs();
	}elseif(!$cty){
		TopoCities($reg);
	}elseif(!$bld){
		TopoBuilds($reg,$cty);
	}elseif (!$rom){
		TopoFloors($reg,$cty,$bld);
	}else{
		TopoRoom($reg,$cty,$bld,$flr,$rom);
	}
	if($leok) TopoLocErr();
}


include_once ("inc/footer.php");

?>
