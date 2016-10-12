<?php
# Program: User-Profile.php
# Programmer: Remo Rickli

$exportxls = 0;
$msgfile   = "log/msg.txt";

include_once ("inc/header.php");
include_once ("inc/timezones.php");

$name = $_SESSION['user'];

$_GET = sanitize($_GET);
$_POST= sanitize($_POST);

$msg = isset($_POST['msg']) ? $_POST['msg'] : "";
$eam = isset($_GET['eam'])  ? $_GET['eam']  : "";

echo "<h1>$usrlbl Profile</h1>\n";

$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
if(isset($_POST['up']) ){
	if($_POST['curp'] AND $_POST['newp'] AND $_POST['ackp']){
		if($_POST['newp'] == $_POST['ackp']){
			$pass = hash("sha256","NeDi".$name.$_POST['newp']);
			$query	= GenQuery('users','s','*','','',array('usrname'),array('='),array($name) );
			$res	= DbQuery($query,$link);
			$uok	= DbNumRows($res);
			if ($uok == 1) {
				$usr = DbFetchRow($res);
			}else{
				echo "<h4>$nonlbl $usrlbl $name</h4>";
				die;
			}
			if($usr[1] == hash("sha256","NeDi".$name.$_POST['curp']) ){
				$query	= GenQuery('users','u',"usrname = '$name'",'','',array('password'),array(),array($pass) );
				if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h5>$paslbl $updlbl OK</h5>";}
			}else{
				echo "<h4>$paslbl: $stco[100] $errlbl!</h4>";
			}
		}else{
			echo "<h4>$paslbl: $acklbl $errlbl!</h4>";
		}
	}
	$mopts = $_POST['gsiz'] + ($_POST['gbit']?8:0) + ($_POST['far']?16:0) + ($_POST['opt']?32:0) + ($_POST['map']?64:0) + ($_POST['gneg']?128:0) + ($_POST['nip']?256:0);
	$lsiz  = (($_POST['lsiz'] > 31)?31:$_POST['lsiz']);
	$query = GenQuery('users','u',"usrname = '$name'",'','',array('email','phone','comment','language','theme','volume','columns','msglimit','miscopts','dateformat'),array(),array($_POST['email'],$_POST['phone'],$_POST['cmt'],$_POST['lang'],$_POST['theme'],$_POST['vol']+$lsiz*4,$_POST['col'],$_POST['lim'],$mopts,$_POST['timf'].$_POST['tz']) );
	if( !DbQuery($query,$link) ){
		echo "<h4>".DbError($link)."</h4>";
	}else{
		echo "<h5>$name $updlbl OK</h5>";
		$_SESSION['lang'] = $_POST['lang'];
		$_SESSION['theme']= $_POST['theme'];
		$_SESSION['brght'] = strpos($_SESSION['theme'],'-Dk')?120:200;
		$_SESSION['vol']  = $_POST['vol']*33;
		$_SESSION['col']  = $_POST['col'];
		$_SESSION['opt']  = $_POST['opt'];
		$_SESSION['nip']  = $_POST['nip'];
		$_SESSION['lim']  = $_POST['lim'];
		$_SESSION['gsiz'] = $_POST['gsiz'];
		$_SESSION['lsiz'] = $lsiz;
		$_SESSION['gbit'] = $_POST['gbit'];
		$_SESSION['far'] = $_POST['far'];
		$_SESSION['map'] = $_POST['map'];
		$_SESSION['gneg'] = $_POST['gneg'];
		$_SESSION['timf'] = $_POST['timf'];
		$_SESSION['datf'] = substr($_SESSION['timf'],0,strrpos($_SESSION['timf'],' '));
		$_SESSION['tz']   = $tzone[$_POST['tz']];
	}
}

$query	= GenQuery('users','s','*','','',array('usrname'),array('='),array($name) );
$res	= DbQuery($query,$link);
$uok	= DbNumRows($res);
if ($uok == 1) {
	$usr = DbFetchRow($res);
}else{
	echo "<h4>$nonlbl $usrlbl $name</h4>";
}
?>
<form method="post" action="<?= $self ?>.php" name="pro">
<table class="content"><tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><?=Smilie($name,$self,0) ?></a><br>
	<?= $name ?>

</td>
<td class="top">
	<h3><?= $paslbl ?></h3>
	<img src="img/16/add.png" title="<?= $usrlbl ?> <?= $addlbl ?>"> <?= date($_SESSION['timf'],$usr[5]) ?>
	<br>
	<img src="img/16/loko.png" title="<?= $paslbl ?> <?= $stco['100'] ?>">
	<input type="password" name="curp" class="s">
	<br>
	<img src="img/16/lokc.png" title="<?= $paslbl ?> <?= $stco['10'] ?>">
	<input type="password" name="newp" class="s">
	<br>
	<img src="img/16/lokc.png" title="<?= $paslbl ?> <?= $acklbl ?>">
	<input type="password" name="ackp" class="s">
</td>
<td class="top">
	<h3><?= $grplbl ?> / <?= $inflbl ?></h3>
	<div class="ctr">
		<img src="img/16/<?= ($usr[2] &  1)?"ucfg":"bcls" ?>.png" title="Admin">
		<img src="img/16/<?= ($usr[2] &  2)?"net":"bcls" ?>.png" title="<?= $netlbl ?>">
		<img src="img/16/<?= ($usr[2] &  4)?"supp":"bcls" ?>.png" title="Helpdesk">
		<img src="img/16/<?= ($usr[2] &  8)?"bino":"bcls" ?>.png" title="<?= $monlbl ?>">
		<img src="img/16/<?= ($usr[2] & 16)?"umgr":"bcls" ?>.png" title="<?= $mgtlbl ?>">
		<img src="img/16/<?= ($usr[2] & 32)?"ugrp":"bcls" ?>.png" title="<?= $mlvl['30'] ?>">
	</div>
	<img src="img/16/sms.png" title="Phone#">
	<input type="text" name="phone" class="m" value="<?= $usr[4] ?>" >
	<br>
	<img src="img/16/mail.png" title="Email <?= $adrlbl ?>">
	<input type="email" name="email" class="m" value="<?= $usr[3] ?>" >
	<br>
	<img src="img/16/say.png" title="<?= $cmtlbl ?>">
	<input type="text" name="cmt" class="m" value="<?= $usr[7] ?>" >
</td>
<td class="top">
	<h3><?= $frmlbl ?></h3>
	<img src="img/16/hat2.png" title="GUI">
	<input type="checkbox" name="opt" <?= ($_SESSION['opt'])?"checked":"" ?> title="<?= $lstlbl ?> <?= $optlbl ?> / <?= $hislbl ?>">
	<input type="checkbox" name="nip" <?= ($_SESSION['nip'])?"checked":"" ?> title="<?= $nonlbl ?> IP Link">
	<input type="checkbox" name="map" <?= ($_SESSION['map'])?"checked":"" ?> title="Googlemaps">
	<br>
	<img src="img/16/form.png" title="# <?= $toplbl ?> <?= $msglbl ?>">
	<input type="number" min="0" max="31" name="lim" class="xs" value="<?= $_SESSION['lim'] ?>">
	<br>
	<img src="img/16/icon.png" title="# <?= $collbl ?> (0-31)">
	<input type="number" min="0" max="31" name="col" class="xs" value="<?= $_SESSION['col'] ?>">
	<br>
	<img src="img/16/abc.png" title="<?= $namlbl ?> <?= $sizlbl ?> (3-31)">
	<input type="number" min="3" max="31" name="lsiz" class="xs" value="<?= $_SESSION['lsiz'] ?>">
</td>
<td class="top">
	<h3><?= $place['r'] ?></h3>
	<img src="img/16/temp.png"  title="<?= $tmplbl ?> Fahrenheit">
	<input type="checkbox" name="far" <?= ($_SESSION['far'])?"checked":"" ?>>
	<br>
	<a href="http://php.net/manual/en/function.date.php" target="window"><img src="img/16/date.png" title="<?= $timlbl ?> <?= $frmlbl ?>"></a>
	<input type="text" name="timf" class="s" value="<?= $_SESSION['timf'] ?>" >

	<select name="tz">
<?php
foreach ($tzone as $k => $v){
	echo "\t\t<option value=\"$k\"".( ($_SESSION['tz'] == $v)?" selected":"").">".substr($v,0,$_SESSION['lsiz'])."\n";
}
?>
	</select>
	<br>
	<img src="img/16/say.png" title="Language">
	<select name="lang">
<?php
if ($dh = opendir("languages")) {
	while (($f = readdir($dh)) !== false) {
		if($f != "." and $f != ".." and !strpos($f,'.png') ){
			echo "\t\t<option value=\"$f\" ".(($_SESSION['lang'] == $f)?" selected":"").">$f\n";
		}
	}
	closedir($dh);
}
?>
	</select>
	<br>
	<img src="img/16/paint.png" title="Theme">
	<select name="theme">
<?php
foreach (glob("themes/*.css") as $f) {
	$t = substr($f, 7, strpos($f, ".css") -7);
	echo "\t\t<option value=\"$t\" ".(($_SESSION['theme'] == $t)?" selected":"").">$t\n";
}
?>
	</select>
</td>
<td class="top">
	<h3><?= $monlbl ?></h3>
	<img src="img/16/bbup.png" title="<?= $trflbl ?> <?= $sholbl ?> Bit/s">
	<input type="checkbox" name="gbit" <?= ($_SESSION['gbit'])?"checked":"" ?>>
	<br>
	<audio id="aud" src="inc/minor.mp3"></audio>
	<img src="img/16/spkr.png" title="Volume" onclick="document.getElementById('aud').play()">
	<select size="1" name="vol" onchange="document.getElementById('aud').volume=document.pro.vol.options[document.pro.vol.selectedIndex].value * .33">
		<option value="0"><?= $nonlbl ?>

		<option value="1"<?= ( ($_SESSION['vol'] == "33")?" selected":"") ?>><?= $qutlbl ?>

		<option value="2"<?= ( ($_SESSION['vol'] == "66")?" selected":"") ?>><?= $siz['m'] ?>

		<option value="3"<?= ( ($_SESSION['vol'] == "99")?" selected":"") ?>><?= $maxlbl ?>

	</select>
	<br>
	<img src="img/16/grph.png"  title="<?= $gralbl ?>">
	<select size="1" name="gsiz">
		<option value="0"><?= $nonlbl ?> <?= $gralbl ?>

		<option value="2"<?= ( ($_SESSION['gsiz'] == "2")?" selected":"") ?>><?= $siz['s'] ?>

		<option value="3"<?= ( ($_SESSION['gsiz'] == "3")?" selected":"") ?>><?= $siz['m'] ?>

		<option value="4"<?= ( ($_SESSION['gsiz'] == "4")?" selected":"") ?>><?= $siz['l'] ?>

	</select>
	<input type="checkbox" name="gneg" <?= ($_SESSION['gneg'])?"checked":"" ?>  title="<?= $sholbl ?> -Y <?= $vallbl ?>">
</td>
<td class="ctr s">
	<input type="submit" class="button" name="up" value="<?= $updlbl ?>">
</td>
</tr>
</table>
</form>
<p>
<?php
$editam = '';
if($isadmin){
	if(isset($_POST['cam']) ){
		unlink($msgfile);
	}elseif(isset($_POST['sam']) ){
		$fh = fopen($msgfile, 'w') or die("Cannot write $msgfile!");
		fwrite($fh, "$msg");
		fclose($fh);
	}
	if(isset($_GET['eam']) ){
?>

<form method="post" action="<?= $self ?>.php" name="ano">
	<table class="content">
		<tr class="bgsub">
			<td class="ctr s">
				<input type="button" value="Bold" OnClick='document.ano.msg.value = document.ano.msg.value + "<b></b>"';>
				<p>
				<input type="button" value="Italic" OnClick='document.ano.msg.value = document.ano.msg.value + "<i></i>"';>
				<p>
				<input type="button" value="Pre" OnClick='document.ano.msg.value = document.ano.msg.value + "<pre></pre>"';>
				<p>
				<input type="button" value="Break" OnClick='document.ano.msg.value = document.ano.msg.value + "<br>\n"';>
				<p>
				<input type="button" value="Title" OnClick='document.ano.msg.value = document.ano.msg.value + "<h2></h2>\n"';>
				<p>
				<input type="button" value="List" OnClick='document.ano.msg.value = document.ano.msg.value + "<ul>\n<li>\n<li>\n</ul>\n"';>
			</td>
			<td class="ctr">
<textarea rows="16" name="msg" cols="100">
<?php
		if ( file_exists($msgfile) ){
			readfile($msgfile);
		}
		if($eam != 1) echo "<a href=\"$eam\">EDIT</a>\n<p>\n";
?>
</textarea>
			</td>
			<td class="ctr s">
				<input type="submit" class="button" name="cam" value="<?= $dellbl ?>">
				<p>
				<input type="submit" class="button" name="sam" value="<?= $wrtlbl ?>">
			</td>
		</tr>
	</table>
</form>

<?php
	}else{
		$editam = "<a href=\"?eam=1\"><img src=\"img/16/note.png\" title=\"$chglbl\"></a>";
	}
}
echo "<h2>$editam Admin $mlvl[100]</h2>\n";

if( file_exists($msgfile) ){
	echo "<div class=\"textpad txtb tqrt\">\n";
	include_once ($msgfile);
	echo "</div><br>\n";
}

$query = GenQuery('chat','s','*','time desc',$_SESSION['lim']);
$res   = DbQuery($query,$link);
$nchat= DbNumRows($res);
if($nchat){
?>
<p>

<h2>
	<a href="User-Chat.php"><img src="img/16/say.png" title="Chat"></a>
	<?= (($verb1)?"$laslbl Chat":"Chat $laslbl") ?>

</h2>

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
		echo "\t<tr class=\"$bg\">\n\t\t<td class=\"$bi ctr xs\">\n\t\t\t" . Smilie($m[1],$m[1],1)."\n\t\t</td>\n";
		echo "\t\t<td bgcolor=#$fc>\n\t\t\t$time\n\t\t</td>\n";
		echo "\t\t<td ".($me?"class=\"rgt\"":"").">\n\t\t\t".preg_replace('/(http[s]?:\/\/[^\s]*)/',"<a href=\"$1\" target=\"window\">$1</a>",$m[2])."\n\t\t</td>\n\t</tr>\n";
	}
	echo "</table>\n";
}

include_once ("inc/footer.php");
?>
