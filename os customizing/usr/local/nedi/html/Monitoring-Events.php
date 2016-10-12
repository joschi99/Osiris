<?php
# Program: Monitoring-Events.php
# Programmer: Remo Rickli

$refresh   = 60;
$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$in = isset($_GET['in']) ? $_GET['in'] : array();
$op = isset($_GET['op']) ? $_GET['op'] : array();
$st = isset($_GET['st']) ? $_GET['st'] : array();
$co = isset($_GET['co']) ? $_GET['co'] : array();
$ak = isset($_GET['ak']) ? $_GET['ak'] : '';

$elm = isset($_GET['elm']) ? preg_replace('/\D+/','',$_GET['elm']) : 25;
$off = (isset($_GET['off']) and !isset($_GET['sho']))? preg_replace('/\D+/','',$_GET['off']) : 0;

$nof = $off;
if( isset($_GET['p']) ){
	$nof = abs($off - $elm);
}elseif( isset($_GET['n']) ){
	$nof = $off + $elm;
}
$dlim = ($elm)?"$elm OFFSET $nof":'';

echo "<h1>$msglbl $lstlbl</h1>\n";

$cols = array(	"info"=>"Info",
		"id"=>"ID",
		"level"=>"$levlbl",
		"time"=>$timlbl,
		"source"=>$srclbl,
		"class"=>$clalbl,
		"device"=>"Device $namlbl",
		"location"=>$loclbl,
		"contact"=>$conlbl
		);

if( !isset($_GET['print']) ) { ?>
<form method="get" name="dynfrm" action="<?= $self ?>.php">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
<?php Filters(); ?>
</td>
<?php
if($st[0]){
	echo "<td class=\"ctr top\">\n\t<h3>$cmdlbl</h3>\n";
	echo "\t<a href=\"Monitoring-Timeline.php?det=level&in[]=$in[0]&op[]=$op[0]&st[]=".urlencode($st[0])."\"><img src=\"img/16/news.png\" title=\"Monitoring-Timeline\"></a>\n";
	if($in[0] == 'source'){
		echo "\t<a href=\"Monitoring-Setup.php?in[]=name&op[]==&st[]=".urlencode($st[0])."\"><img src=\"img/16/bino.png\" title=\"Monitoring-Setup, $fltlbl $srclbl\"></a>\n";
		echo "\t<a href=\"Reports-Monitoring.php?rep[]=evt&in[]=name&op[]==&st[]=".urlencode($st[0])."\"><img src=\"img/16/dbin.png\" title=\"Reports-Monitoring, $fltlbl $srclbl\"></a>\n";
		echo "\t<a href=\"Other-Noodle.php?str=".urlencode($st[0])."\"><img src=\"img/16/find.png\" title=\"Other-Noodle, $fltlbl $srclbl\"></a>\n";
	}
	echo "</td>";
}
?>
<td class="ctr">
	<a href="?in[]=class&op[]=LIKE&st[]=ned%25&elm=<?= $elm ?>"><img src="img/16/radr.png" title="<?= $dsclbl ?>"></a>
	<a href="?in[]=class&op[]=LIKE&st[]=mon%25&elm=<?= $elm ?>"><img src="img/16/bino.png" title="Monitoring"></a>
	<a href="?in[]=class&op[]=LIKE&st[]=cfg%25&elm=<?= $elm ?>"><img src="img/16/conf.png" title="<?= $cfglbl ?>"></a>
	<a href="?in[]=class&op[]=LIKE&st[]=ln%25&elm=<?= $elm ?>"><img src="img/16/link.png" title="<?= $cnclbl ?>"></a>
	<br>
	<a href="?in[]=class&op[]=~&st[]=(if|ln)t[io]&elm=<?= $elm ?>"><img src="img/16/bbup.png" title="<?= $trflbl ?>"></a>
	<a href="?in[]=class&op[]=~&st[]=(if|ln)e[io]&elm=<?= $elm ?>"><img src="img/16/brup.png" title="<?= $errlbl ?>"></a>
	<a href="?in[]=class&op[]=~&st[]=(if|ln)d[io]&elm=<?= $elm ?>"><img src="img/16/bbu2.png" title="<?= $dcalbl ?>"></a>
	<a href="?in[]=class&op[]=~&st[]=(if|ln)bi&elm=<?= $elm ?>"><img src="img/16/brc.png" title="Broadcast"></a>
	<br>
	<a href="?in[]=class&op[]=LIKE&st[]=sec%25&elm=<?= $elm ?>"><img src="img/16/hat.png" title="Security <?= $msglbl ?>"></a>
	<a href="?in[]=class&op[]=LIKE&st[]=sp%25&elm=<?= $elm ?>"><img src="img/16/hat3.png" title="Policy <?= $msglbl ?>"></a>
	<a href="?in[]=class&op[]=LIKE&st[]=usr%25&elm=<?= $elm ?>"><img src="img/16/user.png" title="<?= $usrlbl ?> <?= $msglbl ?>"></a>
	<a href="?in[]=class&op[]==&st[]=trap&elm=<?= $elm ?>"><img src="img/16/warn.png" title="Traps"></a>
	<br>
	<a href="?in[]=level&op[]=>&st[]=140&elm=<?= $elm ?>"><img src="img/16/foye.png" title="<?= $mlvl[150] ?>, <?= $mlvl[200] ?>, <?= $mlvl[250] ?>"></a>
	<a href="?in[]=level&op[]=<&st[]=30&elm=<?= $elm ?>"><img src="img/16/eyes.png" title="<?= $msglbl ?> <?= $acklbl ?>"></a>
	<a href="?in[]=class&op[]==&st[]=dev&elm=<?= $elm ?>"><img src="img/16/dev.png" title="Device <?= $loglbl ?>"></a>
	<a href="?in[]=class&op[]==&st[]=node&elm=<?= $elm ?>"><img src="img/16/node.png" title="Node <?= $loglbl ?>"></a>
</td>
<td class="ctr top">
	<h3 title="<?= $nonlbl ?> <?= $updlbl ?>" onClick="stop_countdown(interval);">
		<img src="img/16/exit.png">
		<span id="counter"><?= $refresh ?></span>
	</h3>
	<img src="img/16/form.png" title="<?= $limlbl ?>">
	<select size="1" name="elm">
<?php selectbox("limit",$elm) ?>
	</select>
</td>
<td class="ctr s nw">
	<input type="submit" class="button" name="sho" value="<?= $sholbl ?>"><br>
	<input type="hidden" name="off" value="<?= $nof ?>">
	<input type="submit" class="button" name="p" value="<">
	<input type="submit" class="button" name="n" value=">"><br>
	<input type="submit" class="button" name="del" value="<?= $dellbl ?>" onclick="return confirm('<?= $dellbl ?>, <?= $cfmmsg ?>')">
</td>
</tr>
</table>
</form>
<p>

<?php
}

$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if( isset($_GET['del']) ){# TODO check/fix order and limit for Postgres and filter on loc, con
	if($isadmin){
		$query	= GenQuery('events','d','*','id desc',$elm,$in,$op,$st,$co );
		if(DbQuery($query,$link) ){
			echo "<h5> $msglbl $dellbl OK </h5>";
		}else{
			echo "<h4>".DbError($link)."</h4>";
		}
	}else{
		echo $nokmsg;
	}
}elseif( $ak and $ismgr ){
	$query = GenQuery('events','s','level','','',array('id'),array('='),array($ak),array(),'LEFT JOIN devices USING (device)');
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
		$r = DbFetchRow($res);
		DbFreeResult($res);
		if( $r[0] > 30){
			$query = GenQuery('events','u',"id = $ak",'','',array('level'),array(),array($r[0]/10) );
			if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$acklbl #$ak OK</h5>";}
		}
	}

}

Condition($in,$op,$st,$co);

Events($dlim,$in,$op,$st,$co,1);

include_once ("inc/footer.php");
?>
