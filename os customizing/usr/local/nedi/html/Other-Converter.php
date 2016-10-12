<?php
# Program: Other-Converter.php
# Simple dec-hex-ascii converter to assists OID analysis
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$txt  = isset($_GET['txt']) ? $_GET['txt'] : "";

if( !isset($_GET['print']) ) {
?>
<h1>Number Converter</h1>

<form method="get" action="<?= $self ?>.php">
<table class="content" >
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr">
	<img src="img/16/say.png" title="<?= $inflbl ?>"> <input type="text" name="txt" value="<?= $txt ?>" class="xl" onfocus="select();">
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $sholbl ?>">
</td>
</tr>
</table>
</form>
<p>

<?php
}

?>
<h2><?= $vallbl ?></h2>

<table class="content fixed" >
	<tr class="bgsub">
		<th>
			<?= $frmlbl ?>
		</th>
<?php
$hex = ( preg_match('/^(H|0x)|[a-f]+/i',$txt) )?1:0;
$ord = preg_split('/[^0-9a-f]/i', $txt);
for($i=0;$i<count($ord);$i++){
	echo "\t\t<th>\n\t\t\t$i\n\t\t</th>\n";
}

?>
	</tr>
	<tr class="txta code">
		<th>
			Decimal
		</th>
<?php

foreach ($ord as $o){
	echo "\t\t<td>\n\t\t\t".(($hex)?hexdec($o):$o)."\n\t\t</td>\n";
}

?>
	</tr>
	<tr class="txtb code">
		<th>
			ASCII
		</th>
<?php

foreach ($ord as $o){
	if($o > 31 and $o < 122){
		echo "\t\t<td>\n\t\t\t".(($hex)?chr(hexdec($o)):chr($o))."\n\t\t</td>\n";
	}else{
		echo "\t\t<td>\n\t\t\t\n\t\t</td>\n";
	}
}

?>
	</tr>
	<tr class="txta code">
		<th>
			HEX
		</th>
<?php

foreach ($ord as $o){
	echo "\t\t<td>\n\t\t\t".(($hex)?$o:dechex($o))."\n\t\t</td>\n";
}
?>
	</tr>
</table>

<?php
include_once ("inc/footer.php");
?>
