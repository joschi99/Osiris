<?php
# Program: Other-Chat.php
# Programmer: Karel Stadler (adapted by Remo Rickli)

$refresh   = 60;
$exportxls = 0;

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$msg = isset( $_GET['msg']) ? strip_tags( $_GET['msg'] ) : '';

$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if($msg){
	$query	= GenQuery('chat','i','','','',array('time','usrname','message'),'',array(time(),$_SESSION['user'],$msg) );
	if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}
}
?>
<h1><?= $usrlbl ?> Chat</h1>

<?php  if( !isset($_GET['print']) ) { ?>
<form method="get" name="dynfrm" action="<?= $self ?>.php">
<table class="content" >
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr">
	<img src="img/16/say.png"><input type="text" name="msg" class="xl" OnFocus="select();" autofocus>

</td>
<td class="ctr s">
	<span id="counter"><?= $refresh ?></span>
	<img src="img/16/exit.png" title="Stop" onClick="stop_countdown(interval);">
</td>
<td class="ctr s">
	<input type="submit" class="button" name="create" value="<?= $sndlbl ?>">
</td>
</table>
</form>
<p>
<?php
}
$query = GenQuery('chat','s','*','time desc',100);
$res   = DbQuery($query,$link);
if($res){
?>

<h2><?= $msglbl ?></h2>

<table class="content">
	<tr class="bgsub">
		<th class="xs">
			<img src="img/16/user.png"><br>
			User
		</th>
		<th class="m">
			<img src="img/16/clock.png"><br>
			<?= $timlbl ?>

		</th>
		<th>
			<img src="img/16/say.png"><br>
			<?= $cmtlbl ?>

		</th>
	</tr>
<?php
	while( ($m = DbFetchRow($res)) ){
		if ($_SESSION['user'] == $m[1]){$bg = "txta"; $bi = "imga";$me=1;}else{$bg = "txtb"; $bi = "imgb";$me=0;}
		list($fc,$lc) = Agecol($m[0],$m[0],$me);
		$time = date($_SESSION['timf'],$m[0]);
		echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi ctr\">\n\t\t\t" . Smilie($m[1],$m[1],1)."\n\t\t</td>\n";
		echo "\t\t<td style=\"background-color:#$fc\">\n\t\t\t$time\n\t\t</td>\n";
		echo "\t\t<td ".($me?"class=\"rgt\"":"").">\n\t\t\t".preg_replace('/(http[s]?:\/\/[^\s]*)/',"<a href=\"$1\" target=\"window\">$1</a>",$m[2])."\n\t\t</td>\n\t</tr>\n";
	}
	echo "</table>\n";
}

include_once ("inc/footer.php");
?>
