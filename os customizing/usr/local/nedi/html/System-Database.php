<?php
# Program: System-Database.php
# Programmer: Pascal Voegeli, Remo Rickli (resorting to system calls for performance, avoid NULL on empty chars aroun 466)
#
# NOTE: For security reasons only admins can use the export function now. Remove "$isadmin AND " on line 160, if you don't care!
#
$exportxls = 0;

// Header.php contains the navigation and general settings for the UI
include_once("inc/header.php");

$sqltbl = isset($_GET['sqltbl']) ? $_GET['sqltbl'] : array();
$act    = isset($_GET['act']) ? $_GET['act'] : "";
$exptbl = isset($_GET['exptbl']) ? $_GET['exptbl'] : "";
$query  = isset($_GET['query']) ? $_GET['query'] : "";
$sep    = isset($_GET['sep']) ? $_GET['sep'] : "";
$quotes = isset($_GET['quotes']) ? "checked" : "";
$colhdr = isset($_GET['colhdr']) ? "checked" : "";
$type   = isset($_GET['type']) ? $_GET['type'] : "htm";
$opt    = isset($_GET['opt']) ? "checked" : "";
$conv   = isset($_GET['conv']) ? "checked" : "";
$rad    = $_GET['rad'] ? "checked" : "";

$ipmatch = "/^(orig|dev|if|nod|mon)ip$/";
$timatch = "/^(first|last|start|end)|(time|update)$/";

// A connection to the database has to be made
if( $rad ){
	$dblink = DbConnect($rdbhost, $rdbuser, $rdbpass, $rdbname);
}else{
	$dblink = DbConnect($dbhost, $dbuser, $dbpass, $dbname);
}
?>

<!-- Begin of the HTML part -->

<h1>System Database</h1>

<?php if( !isset($_GET['print']) ) { ?>
<form method="get" name="export" action="<?= $self ?>.php">
<table class="content" >
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<?php if( $rdbhost ){ ?>
<td class="ctr">
	<img src="img/16/key.png" title="Radius DB">
	<input type="checkbox" name="rad" <?= $rad ?> onchange="this.form.submit();">
</td>
<?php } ?>
<td class="ctr top">
	<!-- If the module is loaded without any GET variables the selected action is "Export" -->
	<h3><input type="radio" name="act" value="c" <?= $act=="c"?"checked":"" ?>><?= $cmdlbl ?></h3>
	<!-- There are 3 different types of things that can be selected in this box: -->
	<!-- If a database table is selected, a "SELECT * FROM..." query is automatically written to the text box -->
	<!-- If the "Device Config Files" entry is selected, the separator and quotes fields are disabled and a specific -->
	<!-- query is written to the text box -->
	<!-- If one of the meaningless entiries is selected nothing's changed in the text box -->
	<select size="1" name="exptbl" onchange="
		if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='none') {
			document.forms['export'].sep.disabled=false;
			document.forms['export'].quotes.disabled=false;
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='cfgfiles') {
			document.forms['export'].query.value='SELECT device, config, time FROM configs';
			document.forms['export'].sep.disabled=true;
			document.forms['export'].quotes.disabled=true;
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='eventret') {
			document.forms['export'].query.value='DELETE FROM events where time < <?= (time() - $retire * 86400) ?>';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='iftrkret') {
			document.forms['export'].query.value='DELETE FROM iftrack where ifupdate < <?= (time() - $retire * 86400) ?>';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='iptrkret') {
			document.forms['export'].query.value='DELETE FROM iptrack where ipupdate < <?= (time() - $retire * 86400) ?>';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='lnkret') {
			document.forms['export'].query.value='DELETE FROM links WHERE linktype is null';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='uprcom') {
			document.forms['export'].query.value='UPDATE devices set readcomm=<new> where readcomm=<old>';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='dupdns') {
			document.forms['export'].query.value='SELECT aname,count(*) FROM dns group by aname having( count(*) > 1);';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='ipupres') {
			document.forms['export'].query.value='UPDATE nodes set ipupdate=0';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='flush') {
			document.forms['export'].query.value='FLUSH LOGS';
		}
		else if(document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value=='reset') {
			document.forms['export'].query.value='RESET MASTER';
		}
		else {
			document.forms['export'].query.value='SELECT * FROM '+document.forms['export'].exptbl.options[document.forms['export'].exptbl.selectedIndex].value;
			document.forms['export'].sep.disabled=false;
			document.forms['export'].quotes.disabled=false;
		}
	">
		<option value="none" class="warn">--- DB <?= $lstlbl ?> ---</option>
<?php	// Some PHP code
// All the names of the database tables are collected and put into the select box
			$res = DbQuery(GenQuery("", "h"), $dblink);
			while($n = DbFetchRow($res)){
				echo "		<option value=\"".$n[0]."\"".($n[0]==$exptbl?" selected":"").">$sholbl ".$n[0]."</option>\n";
			}
			echo "		<option value=\"none\" class=\"warn\">--- ".(($verb1)?"$cmdlbl $igrp[31]":"$igrp[31] $cmdlbl")." ---</option>";
			echo "		<option value=\"cfgfiles\"".($exptbl=="cfgfiles"?" selected":"").">$cfglbl $buplbl</option>\n";
			echo "		<option value=\"eventret\"".($exptbl=="eventret"?" selected":"").">$dellbl $msglbl $agelbl > $retire $tim[d]</option>\n";
			echo "		<option value=\"iftrkret\"".($exptbl=="iftrkret"?" selected":"").">$dellbl IFtrack $agelbl > $retire $tim[d]</option>\n";
			echo "		<option value=\"iptrkret\"".($exptbl=="iptrkret"?" selected":"").">$dellbl IPtrack $agelbl > $retire $tim[d]</option>\n";
			echo "		<option value=\"lnkret\"".($exptbl=="lnkret"?" selected":"").">$dellbl Links $typlbl = NULL</option>\n";
			echo "		<option value=\"uprcom\"".($exptbl=="uprcom"?" selected":"").">$updlbl SNMP $realbl Community</option>\n";
			echo "		<option value=\"dupdns\"".($exptbl=="dupdns"?" selected":"").">$duplbl DNS $vallbl</option>\n";
			echo "		<option value=\"ipupres\"".($exptbl=="ipupres"?" selected":"").">$reslbl IP $updlbl</option>\n";
			echo "		<option value=\"flush\"".($exptbl=="flush"?" selected":"").">$dellbl bin-logs</option>\n";
			echo "		<option value=\"reset\"".($exptbl=="reset"?" selected":"").">$reslbl DB</option>\n";
		?>
	</select>
	Separator:
	<select size="1" name="sep">
<?php  // Some PHP code
		$separators = array(";", ";;", ":", "::", ",", "/");
		foreach($separators as $s){
			echo "			<option value=\"$s\"".($s==$sep?" selected":"").">".$s."</option>\n";
		}
	?>
	</select>
	&nbsp;Quotes <input type="checkbox" name="quotes" <?= $quotes ?>>
	&nbsp;Header <input type="checkbox" name="colhdr" <?= $colhdr ?>>
	<p>
	<textarea rows="3" name="query" cols="80"><?= $query ?></textarea>
</td>
<td class="ctr top">
	<h3><input type="radio" name="act" value="e" <?= $act=="e"?"checked":"" ?>><?= $explbl ?></h3>
	<select multiple size="6" name="sqltbl[]">
	<?php  // Some PHP code
		$res = DbQuery(GenQuery("", "h"), $dblink);
		while($n = DbFetchRow($res)){
			echo "			<option value=\"".$n[0]."\"".(in_array($n[0], $sqltbl)?" selected":"").">".$n[0]."</option>\n";
		}
	?>
	</select>
</td>
<td class="ctr s">
	<h3><?= $dstlbl ?></h3>
	<select size="1" name="type">
		<option value="htm" <?= ($type=="htm")?" selected":"" ?>>html</option>
		<option value="plain" <?= ($type=="plain")?" selected":"" ?>>plain</option>
		<option value="gz" <?= ($type=="gz")?" selected":"" ?>>Gzip</option>
		<option value="bz2" <?= ($type=="bz2")?" selected":"" ?>>Bzip2</option>
	</select>
	<p>
	<img src="img/16/abc.png" title="<?= (($verb1)?"$addlbl $timlbl":"$timlbl $addlbl") ?>/<?= $frmlbl ?> IP">
	<input type="checkbox" name="opt" <?= $opt ?>>
<?php
if( 0 and $backend == 'mysql'){// doesn't work properly and ipv6-bin need converting...
?>
	<br><img src="img/16/db.png" title="<?= $frmlbl ?>: Postgres">
	<input type="checkbox" name="conv" <?= $conv ?>>
<?php
}
?>
	<p>
	<input type="submit" class="button" value="<?= $cmdlbl ?>">
</td>
</tr>
</table>
</form>

<?php
}
if($isadmin and $act == "c") {
	$start = microtime(1);
	// An empty query produces an error message
	if($query == "") {
		echo "<h4>Query $emplbl!</h4>";
	}
	// Execute and return status, if the query is not an SELECT query
	elseif(!preg_match ('/^(SELECT|EXPLAIN)/i',$query) ) {
		if( !$res = DbQuery($query, $dblink) ) {
			echo "<p><div class=\"textpad alrm tqrt\">".DbError($dblink)."</div>";
		}else{
			echo "<h5>Query OK</h5>";
		}
	}
	// And finally, if the query is invalid for any other reasons, an error message is printed
	elseif(!($res = DbQuery($query, $dblink))) {
		echo "<p><div class=\"textpad alrm tqrt\">".DbError($dblink)."</div>";
	}
	// If the query starts with "SELECT device, config, time FROM configs " a config export is made
	// instead of a CSV export
	elseif(strtoupper(substr($query, 0, 43)) == "SELECT DEVICE, CONFIG, TIME FROM CONFIGS") {

		$row = array();
		$configs = array();

		echo "<h3>DB $explbl $cfglbl</h3>\n<div class=\"textpad half txta\">\n";

		while($row = DbFetchArray($res)) {
			$filename = rawurlencode($row['device'])."_".date("Ymd_Hi", $row['time']).".conf";

			$cfgfile = fopen("log/$filename", "w");
			fwrite($cfgfile, $row['config']);
			fclose($cfgfile);
			$configs[] = $filename;

			echo "$wrtlbl log/$filename<br>\n";
			flush();
		}

		$dbf = "log/configs_$_SESSION[user]".(($opt)?'_'.date("Ymd_Hi"):'');
		$cfg = join(' ', $configs);
		if($type == "bz2"){
			$dbf .= '.tbz';
			system("tar jcf $dbf -C log $cfg");
		}else{
			$dbf .= '.tgz';
			system("tar zcf $dbf -C log $cfg");
		}
		echo "<p>$wrtlbl log/$dbf<p>\n";

		foreach($configs as $cfg) {
			unlink("log/$cfg");
			echo "$dellbl log/$cfg<br>\n";
		}
		echo "<b>$buplbl $fillbl <a href=\"$dbf\">$dbf</a> <a href=\"System-Files.php?del=".urlencode($dbf)."\"><img src=\"img/16/bcnl.png\" title=\"$dellbl\"></a></b>\n";
	}
	// HTML Override
	elseif($type == "htm") {
		echo "<h2>$query</h2>";
		echo "<table class=\"content\">\n\t<tr class=\"bgsub\">\n";
		for ($i = 0; $i < DbNumFields($res); ++$i) {
			$field = DbFieldName($res, $i);
			echo  "\t\t<th>$i $field</th>\n";
		}
		echo "\t</tr>\n";
		$row = 0;
		while($l = DbFetchArray($res)) {
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			TblRow($bg);
			foreach($l as $id => $field) {
				if( $field and preg_match("/^(if|nod|mon)ip6$/",$id) ){
					echo "\t\t<td>\n\t\t\t".(( $backend == 'Pg')?$field:inet_ntop($field))."\n\t\t</td>\n";
				}elseif($field and $opt and  preg_match($ipmatch,$id) ){
					echo "\t\t<td>\n\t\t\t".long2ip($field)."\n\t\t</td>\n";
				}elseif($opt and preg_match($timatch,$id) ){
					echo "\t\t<td>\n\t\t\t".date($_SESSION['timf'],$field)."\n\t\t</td>\n";
				}else{
					echo "\t\t<td>\n\t\t\t$field\n\t\t</td>\n";
				}
			}
			echo  "\t</tr>\n";
		}
?>
	<tr class="bgsub">
		<td colspan="<?= $i ?>">
			<?= $row ?> <?= $vallbl ?>, <?= round( microtime(1) - $start,2 ) ?> <?= $tim['s'] ?>

		</td>
	</tr>
</table>
		<?php
	}else {
		echo "<h3>$collbl/$vallbl $explbl</h3>\n<div class=\"textpad txta half\">\n";

		$tbl = join(' ',$sqltbl);
		$dbf = "log/nedi-$_SESSION[user]".(($opt)?'_'.date("Ymd_Hi"):'').".csv";
		$csv = DbCsv($res, $sep, ($quotes=="checked"?"on":""), $dbf, $colhdr);
		echo "Created file $dbf from table ".$exptbl.($quotes=="checked"?" with surrounding quotes":"");
		echo " using separator '".$sep."'<br>\n";
		flush();

		if($type == "gz"){
			system("gzip -f $dbf");
			$dbf .= '.gz';
		}elseif($type == "bz2"){
			system("bzip2 -f $dbf");
			$dbf .= '.bz2';
		}
		flush();
		echo "<p><b>$fillbl <a href=\"$dbf\">$dbf</a> <a href=\"System-Files.php?del=".urlencode($dbf)."\"><img src=\"img/16/bcnl.png\" title=\"$dellbl\"></a></b></div>\n";

	}
}elseif($isadmin and $act == "e") {

	echo "<h3>DB $explbl</h3>\n<div class=\"textpad txta\">\n";

	$dok = 2;
	if( $backend == 'mysql'){
		$tbl = join(' ',$sqltbl);
		$cnv = ($conv)?"--compatible=postgresql":"";
		$dbf = "log/$dbname-$_SESSION[user]".(($opt)?'_'.date("Ymd_Hi"):'').".msq";
		$dok = system("mysqldump $cnv -h $dbhost -u$dbuser -p$dbpass $dbname $tbl > $dbf");
	}elseif( $backend == 'Pg'){
		$tbl = '-t'.join(' -t',$sqltbl);
		$dbf = "log/$dbname-$_SESSION[user]".(($opt)?'_'.date("Ymd_Hi"):'').".psq";
		$dok = system("export PGPASSWORD=$dbpass;pg_dump -c -U$dbuser $tbl $dbname > $dbf");
	}

	if($dok){
		echo "<h4>$errlbl ($dok) dump $dbname > $dbf</h4>";
		flush();
	}else{
		echo "$wrtlbl $dbf<br>";
		if($type == "gz"){
			system("gzip -f $dbf");
			$dbf .= '.gz';
		}elseif($type == "bz2"){
			system("bzip2 -f $dbf");
			$dbf .= '.bz2';
		}
		flush();
		echo "<p><b>$fillbl <a href=\"$dbf\">$dbf</a> <a href=\"System-Files.php?del=".urlencode($dbf)."\"><img src=\"img/16/bcnl.png\" title=\"$dellbl\"></a></b>\n";
	};

	echo "</div>\n";
}elseif($isadmin and $act == "trunc") {
	$query = GenQuery($sqltbl[0],"t");
	if( !DbQuery($query,$dblink) ){echo "<h4>".DbError($dblink)."</h4>";}else{echo "<h5>".(($verb1)?"$sqltbl[0] $dellbl $vallbl":"$sqltbl[0] $vallbl $dellbl")." OK</h5>";}
}elseif($isadmin and $act == "opt") {
	$query = GenQuery($sqltbl[0],"o");
	if( !DbQuery($query,$dblink) ){echo "<h4>".DbError($dblink)."</h4>";}else{echo "<h5>".(($verb1)?"$optlbl $sqltbl[0]":"$sqltbl[0] $optlbl")." OK</h5>";}
}elseif($isadmin and $act == "rep") {
	$query = GenQuery($sqltbl[0],"r");
	if( !DbQuery($query,$dblink) ){echo "<h4>".DbError($dblink)."</h4>";}else{echo "<h5>".(($verb1)?"$replbl $sqltbl[0]":"$sqltbl[0] $replbl")." OK</h5>";}
}else {
	echo "<h2>DB $dbname $sumlbl</h2>\n";

	$res = DbQuery(GenQuery("", "v"), $dblink);
	while($l = DbFetchRow($res)) {
		echo "<h3>$l[0]</h3>\n";
	}
	DbFreeResult($res);

	$res = DbQuery(GenQuery("", "x"), $dblink);
	echo "<table class=\"content\">\n\t<tr class=\"bgsub\">\n";
	for ($i = 0; $i < DbNumFields($res); ++$i) {
		$field = DbFieldName($res, $i);
		echo  "\t\t<th>\n\t\t\t$field\n\t\t</th>\n";
	}
	echo "\t</tr>\n";
	$row = 0;
	while($l = DbFetchArray($res)) {
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		TblRow($bg);
		foreach($l as $id => $field) {
			echo "\t\t<td>\n\t\t\t$field\n\t\t</td>\n";
		}
		echo  "\t</tr>\n";
	}
	echo  "</table>\n<p>\n";
	DbFreeResult($res);

	$res = DbQuery(GenQuery("", "h"), $dblink);
	$col = 0;
	echo "<table class=\"full fixed\">\n<tr>\n";
	while($tab = DbFetchRow($res)){
		if($col == intval($_SESSION['col']/2) or (!$_SESSION['col'] and $col == 4) ){echo "</tr><tr>";$col=0;}
		echo "<td class=\"helper\">\n\n<table class=\"content\" >\n\t<tr class=\"bgsub\">\n";
		echo "\t\t<th colspan=\"3\">\n\t\t\t$tab[0]\n\t\t</th>\n\t\t<th>\n\t\t\tNULL\n\t\t</th>\n\t\t<th>\n\t\t\tKEY\n\t\t</th>\n\t\t<th>\n\t\t\tDEF\n\t\t</th>\n\t</tr>\n";
		$cres = DbQuery(GenQuery($tab[0], "c"), $dblink);
		$row = 0;
		while($c = DbFetchRow($cres)){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			echo "\t<tr class=\"$bg\">\n\t\t<td class=\"ctr b $bi\">\n\t\t\t$row\n\t\t</td>\n";
			echo "\t\t<td class=\"drd\">\n\t\t\t$c[0]\n\t\t</td>\n\t\t<td>\n\t\t\t$c[1]\n\t\t</td>\n";
			echo "\t\t<td class=\"prp\">\n\t\t\t$c[2]\n\t\t</td>\n\t\t<td class=\"blu\">\n\t\t\t$c[3]\n\t\t</td>\n\t\t<td class=\"grn\">\n\t\t\t$c[4]\n\t\t</td>\n\t</tr>\n";
			$row++;
		}
		$recs = DbFetchRow(DbQuery(GenQuery($tab[0], 's','count(*)'), $dblink));
		DbFreeResult($cres);
	?>
	<tr class="bgsub">
		<td colspan="6" class="caption">
			<div class="frgt">
<?php  if($recs[0]) { ?>
				<a href="?rad=<?= $rad ?>&act=c&exptbl=links&sep=%3B&query=SELECT+*+FROM+<?= $tab[0] ?> limit <?= ($listlim)?$listlim:250 ?>&opt=on"><img src="img/16/eyes.png" title="<?= $sholbl ?>"></a>
<?php }
if($isadmin) { ?>
				<a href="?rad=<?= $rad ?>&act=opt&sqltbl[]=<?= $tab[0] ?>"><img src="img/16/hat2.png" title="<?= $optlbl ?>"></a>
				<a href="?rad=<?= $rad ?>&act=rep&sqltbl[]=<?= $tab[0] ?>"><img src="img/16/dril.png" title="<?= $replbl ?>"></a>
				<a href="?rad=<?= $rad ?>&act=trunc&sqltbl[]=<?= $tab[0] ?>"><img src="img/16/bcnl.png" onclick="return confirm('<?= (($verb1)?"$dellbl $vallbl":"$vallbl $dellbl") ?>, <?= $cfmmsg ?>')" title="<?= (($verb1)?"$dellbl $vallbl":"$vallbl $dellbl") ?>"></a>
<?php } ?>
			</div>
			<?= $recs[0] ?> <?= $vallbl ?>

		</td>
	</tr>
</table>

</td>
<?php
		$col++;
	}
	DbFreeResult($res);
?>
</tr>
</table>
<?php
}
// Now the database connection can be closed
DbClose($dblink);

// This is the footer on the very bottom of the page
include_once("inc/footer.php");


//================================================================================
// Name: DbCsv()
//
// Description: Creates a CSV file of a given SQL query result.
//              When calling the function you can choose if you want
//              to have quotes around the elements of the CSV file.
//              The separator between the elements has to be provided when
//              calling DbCsv()
//
// Parameters:
//     $res		- A valid SQL result identifier
//     $sep		- The separator to put between the elements
//         		  This can also be longer than one character
//     $quotes	- "on" to have quotes around the elements
//     $outfile	- The name of the file that should be created
//
// Return value:
//     none
//

function DbCsv($res, $sep, $quotes, $outfile, $head) {

	// The CSV file is created and opened
	$csvfile = fopen($outfile, "w");

	// Add column header, if desired
	if($head){
		$csv = "";
		for ($i = 0; $i < DbNumFields($res); ++$i) {
			if($quotes == "on") $csv .= "\"";
			$csv .= DbFieldName($res, $i);
			echo "$csv ";
			if($quotes == "on") $csv .= "\"";
			$csv .= $sep;
		}
		// The last separator of a line is always cut off
		$csv = trim($csv, $sep);

		// For each row a single line of the file is used
		$csv .= "\n";

		// After having prepared the CSV row, it is written to the file
		fwrite($csvfile, $csv);
	}

	// The rows of the given result are processed one after the other
	while($row = DbFetchArray($res)) {
		$csv = "";
		// Each element is added to the string individually
		foreach($row as $id => $field) {
			if(preg_match($ipmatch,$id) ){$field = long2ip($field);}
			if(preg_match($timatch,$id) ){$field = date($_SESSION['timf'],$field);}
			// If quotes are wished, they are put around the element
			if($quotes == "on") $csv .= "\"";
			$csv .= $field;
			if($quotes == "on") $csv .= "\"";
			$csv .= $sep;
		}
		// The last separator of a line is always cut off
		$csv = trim($csv, $sep);

		// For each row a single line of the file is used
		$csv .= "\r\n";

		// After having prepared the CSV row, it is written to the file
		fwrite($csvfile, $csv);
	}

	// When finished, the CSV file is closed
	fclose($csvfile);
}

?>
