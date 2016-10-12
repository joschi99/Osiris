<?php
//===============================
// Password Security utility
//===============================

session_start();
$nedipath = preg_replace( "/^(\/.+)\/html\/.+.php/","$1",$_SERVER['SCRIPT_FILENAME']);			# Guess NeDi path for nedi.conf

include_once ("libmisc.php");
ReadConf('nomenu');

require_once ("../languages/$_SESSION[lang]/gui.php");
if( !preg_match("/adm/",$_SESSION['group']) ){
	echo $nokmsg;
	die;
}

$_POST = sanitize($_POST);

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?= $charset ?>">
	<link href="../themes/<?= $_SESSION['theme'] ?>.css" type="text/css" rel="stylesheet">
</head>

<body>
	<h2><?= $paslbl ?> <?= $seclbl ?></h2>
	<div class="genpad bgmain">
		<p>
		<form method="post">
			<a href="secpw.php"><img src="../img/16/loko.png"> <?= $_GET['oid'] ?></a>
			<input type="password" name="pw" class="m" onkeypress="if(event.keyCode==13)this.form.submit()">
		</form>
		<img src="../img/16/lokc.png"> <?= $_GET['oid'] ?>
		<input type="text" name="pw" class="l" value="<?php if( $_POST[pw] ) system("$nedipath/nedi.pl -Z $_POST[pw]"); ?>" onclick="select();" >
		<p>
	</div>
</body>
</html>
