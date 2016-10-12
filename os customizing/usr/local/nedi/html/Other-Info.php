<?php
# Program: Other-Info.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
?>

<h1>Information</h1>

<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
	<table class="full">
		<tr>
			<td>Language: <?= $_SESSION['lang'] ?></td>
			<td>Theme: <?= $_SESSION['theme'] ?></td>
			<td><?= $optlbl ?>: <?= $_SESSION['opt'] ?></td>
		</tr>
		<tr>
			<td>Volume: <?= $_SESSION['vol']  ?></td>
			<td><?= $collbl ?>: <?= $_SESSION['col']  ?></td>
			<td><?= $limlbl ?>: <?= $_SESSION['lim']  ?>/<?= $_SESSION['lsiz']  ?></td>
		</tr>
		<tr>
			<td><?= $gralbl ?> <?= $sizlbl ?>: <?= $_SESSION['gsiz'] ?></td>
			<td><?= $trflbl ?> Bit/s: <?= $_SESSION['gbit'] ?></td>
			<td>Fahrenheit: <?= $_SESSION['far'] ?></td>
		</tr>
	</table>
</td>
</tr>
</table>
<p>

<div class="textpad bgsub tqrt">
<?php

// Fixed CSS issues, with help from php.net:
ob_start () ;
phpinfo () ;
$pinfo = ob_get_contents () ;
ob_end_clean () ;

// the name attribute "module_Zend Optimizer" of an anker-tag is not xhtml valid, so replace it with "module_Zend_Optimizer"
echo ( str_replace ( "module_Zend Optimizer", "module_Zend_Optimizer", preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) ) ) ;

?>
</div>

<style type="text/css">
	td.e {	background-color: #eee;
		font-size: 100%;
		font-weight: bold;
		vertical-align: baseline;
	}
	td.v {	background-color: #ddd;
	}
</style>
