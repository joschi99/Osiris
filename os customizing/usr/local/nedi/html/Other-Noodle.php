<?php
# Program: Other-Noodle.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$str = isset($_GET['str']) ? $_GET['str'] : "";
$lim = isset($_GET['lim']) ? preg_replace('/\D+/','',$_GET['lim']) : $listlim;

$nodip6 = ( $backend == 'mysql' )?'HEX(nodip6)':'nodip6';

$tabs = array(		'devices'	=> array ('device','inet_ntoa(devip)','serial','type','description','devos','bootimage','location','contact','devgroup'),
			'configs'	=> array ('configs.device','config','changes'),
			'interfaces'	=> array ('interfaces.device','ifname','ifmac','alias','ifdesc','comment'),
			'incidents'	=> array ('name','comment'),
			'links'		=> array ('id','links.device','ifname','neighbor','nbrifname','linktype'),
			'locations'	=> array ('region','city','building','locdesc'),
			'modules'	=> array ('modules.device','model','moddesc','modules.serial','hw','fw','sw'),
			'monitoring'	=> array ('name','test','eventfwd','eventdel','depend1','depend2','monitoring.device'),
			'networks'	=> array ('device','ifname','inet_ntoa(ifip)','vrfname'),
			'inventory'	=> array ('serial','assettype','assetnumber','assetlocation','pursource','purnumber','maintpartner','comment','usrname'),
			'policies'	=> array ('device','info','usrname'),
			'vlans'		=> array ('device','vlanname'),
			'nodes'		=> array ('mac','oui','nodes.device','ifname','noduser','nodesc'),
			'nodarp'	=> array ('mac','inet_ntoa(nodip)','srvtype','srvos','arpdevice','arpifname'),
			'nodnd'		=> array ('mac',$nodip6,'srv6type','srv6os','nddevice','ndifname'),
			'dns'		=> array ('inet_ntoa(nodip)','aname'),
			'iftrack'	=> array ('mac','iftrack.device','ifname'),
			'iptrack'	=> array ('mac','inet_ntoa(nodip)','name','iptrack.device'),
			'events'	=> array ('source','info'),
			'users'		=> array ('usrname','email','comment'),
			'chat'		=> array ('usrname','message')
			);

?>
<h1>Noodle Search</h1>

<?php  if( !isset($_GET['print']) ) { ?>
<form method="get" name="find" action="<?= $self ?>.php">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>

</td>
<td class="ctr b">
	<input type="search" name="str" value="<?= $str ?>" class="l">

</td>
<td class="ctr b">
	<?= $limlbl ?>

	<select size="1" name="lim">
<?php selectbox("limit",$lim) ?>
	</select>

</td>
<td class="ctr s">
	<input type="submit" class="button" value="Find IT">
</td>
</tr>
</table>
</form>
<p>

<?php
}

if ($str){
	echo "<h3>$fltlbl \"$str\"</h3>\n";
	$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);

	foreach ($tabs as $table => $cols){
		if($debug){
			echo "<div class=\"textpad noti\">\n";
			print_r($cols);
			echo "</div>\n";
		}
		$incol  = "CONCAT(".implode(",", $cols).")";
		$outcol = implode(",", $cols);
		$query	= GenQuery($table,'s',$outcol,'','',array($incol),array('~'),array($str),array(),$joindv[$table]?"LEFT JOIN devices ON ($table.$joindv[$table] = devices.device)":'');
		$res	= DbQuery($query,$link);

		if($res and DbNumRows($res)){
			echo "\n<h2><img src=img/16/$tblicon[$table].png> $table</h2>\n\n";
			echo "<table class=\"content\">\n\t<tr class=\"bgsub\">\n";
			for ($i = 0; $i < DbNumFields($res); ++$i) {
				$id = DbFieldName($res, $i);
				echo  "\t\t<th>\n\t\t\t$id\n\t\t</th>\n";
			}
			echo  "\t</tr>\n";
			$row = 0;
			while($l = DbFetchArray($res)) {
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				TblRow($bg);
				foreach($l as $id => $field) {
					if( strlen($field) > 255 ){
						echo "\n\t\t<td>\n\t\t\t".substr(implode("\n",preg_grep("/$str/i",explode("\n",$field) ) ),0,100 ) . "...\n\t\t</td>\n";
					}else{
						if( array_key_exists($id,$collnk) ){
							echo "\n\t\t<td>\n\t\t\t<a href=\"$collnk[$id]".urlencode($field)."\">$field</a>\n\t\t</td>\n";
						}else{
							echo "\n\t\t<td>\n\t\t\t$field\n\t\t</td>\n";
						}
					}
				}
				echo  "\t</tr>\n";
				if($row == $lim){break;}
			}
			TblFoot("bgsub", count($cols), "$row $vallbl".(($lim)?", $limlbl: $lim":"") );
		}
	}
}
include_once ("inc/footer.php");
?>
