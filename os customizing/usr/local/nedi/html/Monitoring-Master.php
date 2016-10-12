<?php
# Program: Monitoring-Master.php
# Programmer: Remo Rickli

$refresh   = 60;
$firstmsg  = time() - 86400;

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");
include_once ("inc/librep.php");

$_GET = sanitize($_GET);
$reg = isset($_GET['reg']) ? $_GET['reg'] : "";
$cty = isset($_GET['cty']) ? $_GET['cty'] : "";
$bld = isset($_GET['bld']) ? $_GET['bld'] : "";

$isiz  = ($srrd == 2)?"16":"32";

?>
<h1>Monitoring Master</h1>

<form method="get" name="dynfrm" action="<?= $self ?>.php">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr top">
	<h3><?= $stalbl ?></h3>

<?php
$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
TopoTable($reg,$cty,$bld,$flr,$rom,2);

StatusMon( $loc,$_SESSION['gsiz'] );
?>

</td>
<td class="ctr top">
	<h3><?= $inclbl ?> <?= $acklbl ?></h3>
<?php
StatusIncidents($loc,1,32);
?>

</td>
<td class="ctr s">
	<span id="counter"><?= $refresh ?></span>
	<img src="img/16/exit.png" title="Stop" onClick="stop_countdown(interval);">
</td>
</tr>
</table>
</form>
<p>

<h2><?= $msglbl ?> <?= $tim['t'] ?></h2>

<table class="full"><tr><td class="helper qrt">

<h3>Agents</h3>
<?php
	$query	= GenQuery('events','g','device,devip,readcomm,icon,testopt','cnt desc',$_SESSION['lim'],array('time','location'),array('>','~'),array($firstmsg,$loc),array('AND'),'LEFT JOIN devices USING (device) LEFT JOIN monitoring USING (device)');
	$res	= DbQuery($query,$link);
	if($res){
		$nlev = DbNumRows($res);
		if($nlev){
?>
<table class="content">
	<tr class="bgsub">
		<th>
			<img src="img/16/dev.png"><br>
			Device
		</th>
		<th>
			<img src="img/16/bell.png"><br>
			<?= $msglbl ?>

		</th>
		<th>
			<img src="img/16/cog.png"><br>
			<?= $cmdlbl ?>

		</th>
<?php
			$row = 0;
			while( ($r = DbFetchRow($res)) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$s    = substr($r[0],0,$_SESSION['lsiz']);		# Shorten labels
				$mbar = Bar($r[5],'lvl10','sbar');
				$ud   = urlencode($r[0]);
				TblRow($bg);
				TblCell( $r[0],"Devices-Status.php?dev=$ud",'$bi b nw',"<img src=\"img/dev/$r[3].png\" width=\"18px\">" );
				TblCell( "$mbar $r[5]","Monitoring-Events.php?in[]=device&op[]==&st[]=$ud&co[]=AND&in[]=time&op[]=>&st[]=$firstmsg",'nw' );
				TblCell( "<a href=\"$r[2]://$r[1]/$r[4]Monitoring-Health.php\" target=\"window\"><img src=\"img/16/hlth.png\" title=\"$r[0] Health\"></a>\n".
					 "\t\t\t<a href=\"$r[2]://$r[1]/$r[4]Monitoring-Setup.php\" target=\"window\"><img src=\"img/16/bino.png\" title=\"$r[0] $monlbl $cfglbl\"></a>\n".
					 "\t\t\t<a href=\"$r[2]://$r[1]/$r[4]Reports-Combination.php?in[]=&op[]=~&st[]=&rep=mon\" target=\"window\"><img src=\"img/16/chrt.png\" title=\"$r[0] $inclbl $sumlbl\"></a>\n".
					 "\t\t\t<a href=\"$r[2]://$r[1]/$r[4]Reports-Monitoring.php?rep[]=lat&rep[]=evt\" target=\"window\"><img src=\"img/16/dbin.png\" title=\"$r[0] $monlbl $stslbl\"></a>\n".
					 "\t\t\t<a href=\"$r[2]://$r[1]/$r[4]System-Services.php\" target=\"window\"><img src=\"img/16/cog.png\" title=\"$r[0] $srvlbl\"></a>\n"
					,'','ctr nw' );
				echo "\t</tr>\n";
			}
			echo "</table>\n";
		}else{
			echo "<p>\n<h5>$nonlbl</h5>\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
	}
?>

</td><td class="helper tqrt">

<h3><?= $mlvl[200] ?> & <?= $mlvl[250] ?> <?= $lstlbl ?></h3>
<?php

Events($_SESSION['lim'],array('level','time','location'),array('>=','>','~'),array(200,$firstmsg,$loc),array('AND','AND'),2);

echo "\n</td></tr></table>\n\n";
if($_SESSION['opt']){
	MonAvail('','','',$_SESSION['lim'],'');
}

include_once ("inc/footer.php");

?>
