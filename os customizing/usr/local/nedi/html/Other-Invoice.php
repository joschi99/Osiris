<?php
# Program: Other-Invoice.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$to = isset($_GET['to']) ? $_GET['to'] : "Happy User\n5000 Supportive Rd\nNicetown, JO\nGreatland\n";
$no = isset($_GET['no']) ? $_GET['no'] : "";

if( isset($_GET['cu']) ){
	$cu = $_GET['cu'];
	$sdc = ($_GET['sdc']) ? 'checked' : '';
	$ndc = ($_GET['ndc']) ? 'checked' : '';
	$noc = ($_GET['noc']) ? 'checked' : '';
}else{
	$cu = 'u';
	$sdc = 'checked';
	$ndc = 'checked';
	$noc = 'checked';
}

$inr = substr(ip2long($_SERVER['SERVER_ADDR']),-6) + date("z") * date("j");

$lnk = DbConnect($dbhost,$dbuser,$dbpass,$dbname);							# Above print-header!
$qry = GenQuery('devices','s','count(*)','','',array('snmpversion'),array('>'),array('0'));
$res = DbQuery($qry,$lnk);
if ($res) {
	$sdv = DbFetchRow($res);
	DbFreeResult($res);
}else{
	print DbError($lnk);
	die;
}

$qry = GenQuery('devices','s','count(*)','','',array('snmpversion'),array('='),array('0'));
$res = DbQuery($qry,$lnk);
if ($res) {
	$ndv = DbFetchRow($res);
	DbFreeResult($res);
}else{
	print DbError($lnk);
	die;
}

$qry = GenQuery('nodes','s','count(*)');
$res = DbQuery($qry,$lnk);
if ($res) {
	$nod = DbFetchRow($res);
	DbFreeResult($res);
}else{
	print DbError($lnk);
	die;
}

if($cu == "u"){
	$cuf = 0.95;
	$cul = 'USD';
	$ibn = 'CH72 0070 0130 0072 8546 9';
}elseif($cu == "e"){
	$cuf = 1.05;
	$cul = 'EUR';
	$ibn = 'CH77 0070 0130 0079 5031 4';
}elseif($cu == "c"){
	$cuf = 1;
	$cul = 'CHF';
	$ibn = 'CH31 0070 0110 0041 9947 4';
}

$sdr = (($sdc)?1:0) * round( 15 / log($sdv[0]) / $cuf,3 );
$sda = intval($sdr * $sdv[0]);

$ndr = (($ndc)?1:0) * round( 5 / log($ndv[0]) / $cuf,3 );
$nda = intval($ndr * $ndv[0]);

$nor = (($noc)?1:0) * round( 0.25 / log($nod[0]) / $cuf,3 );
$noa = intval($nor * $nod[0]);

$tot = $sda + $nda + $noa;

$sup = ceil(($tot-500)/1000);
?>

<form method="get" name="bill" action="<?= $self ?>.php">
<?php  if( !isset($_GET['print']) ) { ?>

<h1>NeDi <?= $icelbl ?></h1>

<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td>
</td>
<td class="ctr s">
	<input type="submit" class="button" value="<?= $updlbl ?>">
</td>
</tr>
</table>
<p>

<div class="textpad txta half">
	<h5>Annual Subscription</h5>
	
	Use this module to generate an official invoice and have it paid by your company to support NeDi's development!
	<p>
	Have a look at the context help for more information and feel free to <a class="b" href="http://www.nedi.ch/about/impressum/">Contact me</a> for addional paid support, training or feature requests...
<?php if( $tot > 500 ){ ?>
	<p>
	Paying this invoice as shown entitles to:
	<ul>
		<li><a class="b" href="http://www.nedi.ch/customer-area/">access</a> to the latest version and additional content
<?php if($sup) echo "<li>$sup support request".(($sup>1)?'s':'')." in the year ".date("Y")."\n" ?>
	</ul>
<?php  } ?>
</div>
<?php  } ?>
<p>

<div class="imga" style="position: relative;margin:10px auto;height:780px;width:96%;border:1px solid #111111;font-size:110%">
<table class="full fixed">
	<tr>
		<td>
			<b>NeDi Consulting</b><p>
			Remo Rickli<br>
			Steinbruchstrasse 10b<br>
			8187 Weiach<br>
			Switzerland<br>
		</td>
		<td class="rgt">
			<?= $icelbl ?> #<?= $inr ?><br>
			<?= date($_SESSION['datf']) ?>

		</td>
	</tr>
	<tr class="txtb">
		<td class="top">
			<b><?= $igrp['33'] ?></b><br>
<?php
if( isset($_GET['print']) ){
	echo "<pre class=\"imga\">$to</pre>\n";

}else{
	echo "<textarea rows=\"5\" name=\"to\" cols=\"25\">$to</textarea>\n";
}
?>
		</td>
		<td class="top">
			<b><?= $cmtlbl ?></b><br>
<?php
if( isset($_GET['print']) ){
	echo "<pre>$no</pre>\n";

}else{
	echo "<textarea rows=\"5\" name=\"no\" cols=\"25\">$no</textarea>\n";
}
?>
		</td>
	</tr>
</table><p>

<h3>NeDi <?= $srvlbl ?> 1.Jan.<?= date("Y") ?> - 31.Dec.<?= date("Y") ?></h3>

<table class="full">
	<tr class="bgmain">

<?php
TblCell($deslbl,'','','','th');
TblCell($qtylbl,'','m','','th');
TblCell($metlbl,'','m','','th');
TblCell($totlbl,'','m','','th');
echo "	</tr>\n";
TblRow('txta');
TblCell( 'SNMP Devices' );
TblCell( $sdv[0],'','rgt' );
TblCell( $sdr,'','rgt',"<input type=\"checkbox\" name=\"sdc\" $sdc onchange=\"this.form.submit();\">" );
TblCell( $sda,'','rgt' );
echo "	</tr>\n";
TblRow('txtb');
TblCell( "$nonlbl-SNMP Devices" );
TblCell( $ndv[0],'','rgt' );
TblCell( $ndr,'','rgt',"<input type=\"checkbox\" name=\"ndc\" $ndc onchange=\"this.form.submit();\">" );
TblCell( $nda,'','rgt' );
echo "	</tr>\n";
TblRow('txta');
TblCell( 'Nodes' );
TblCell( $nod[0],'','rgt' );
TblCell( $nor,'','rgt',"<input type=\"checkbox\" name=\"noc\" $noc onchange=\"this.form.submit();\">" );
TblCell( $noa,'','rgt','','border-bottom:solid 1px #444' );
echo "	</tr>\n";
if( $sup ){
	TblRow('txtb');
	TblCell( "Support request".(($sup>1)?'s':'') );
	TblCell( $sup,'','rgt b' );
	TblCell( );
	TblCell( );
	echo "	</tr>\n";
}
TblRow('imga');
TblCell($totlbl,'','rgt b');
echo "\n\t\t<td>\n\t\t</td>\n\t\t<td>\n";
if( isset($_GET['print']) ){
	echo "\t\t\t$cul\n";
}else{
	echo "\t\t\tCurrency\n\t\t\t<select size=\"1\" name=\"cu\" onchange=\"this.form.submit();\">\n";
	echo "\t\t\t\t<option value=\"u\"".( ($cu == "u")?" selected":"").">USD\n";
	echo "\t\t\t\t<option value=\"e\"".( ($cu == "e")?" selected":"").">EUR\n";
	echo "\t\t\t\t<option value=\"c\"".( ($cu == "c")?" selected":"").">CHF\n";
	echo "\t\t\t</select>\n";
}
echo "\n\t\t</td>\n";
TblCell($tot,'','rgt b','','border-bottom:double 4px #444');
?>
	</tr>
</table>

<div style='position: absolute;bottom: 0px;width: 100%;font-size:90%'>
	<img src="img/16/mail.png">rickli@nedi.ch &nbsp;&nbsp;&nbsp;
	<img src="img/16/sms.png">+41 41 511 98 41 &nbsp;&nbsp;&nbsp;
	<img src="img/16/cash.png">Remo Rickli - SWIFT-BIC:ZKBKCHZZ80A &nbsp; IBAN:<?= $ibn ?>

</div>
</div>
</form>
<?php
include_once ("inc/footer.php");
?>
