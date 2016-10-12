<?php
# Program: Nodes-Create.php
# Programmer: Remo Rickli

$exportxls = 0;

include_once ("inc/header.php");
include_once ("inc/libdev.php");

$_GET = sanitize($_GET);
$dev = isset($_GET['dev']) ? $_GET['dev'] : "";
$svm = isset($_GET['svm']) ? $_GET['svm'] : "";
$chw = isset($_GET['chw']) ?"checked" : "";
$nvm = isset($_GET['nvm']) ? $_GET['nvm'] : "";

$cpu = isset($_GET['cpu']) ? $_GET['cpu'] : 1;
$mem = isset($_GET['mem']) ? $_GET['mem'] : 256;
$hdd = isset($_GET['hdd']) ? $_GET['hdd'] : 8;

$dly = isset($_GET['dly']) ? $_GET['dly'] : 0;

$vnc = isset($_GET['vnc']) ? $_GET['vnc'] : 0;
$vnp = isset($_GET['vnp']) ? $_GET['vnp'] : "";
$sxg = isset($_GET['sxg']) ?"checked" : "";

$iso = isset($_GET['iso']) ? $_GET['iso'] : "";

?>
<h1>Nodes Create</h1>

<form method="get" action="<?= $self ?>.php" name="mkvm">
<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="top">
	<h3><?= $dstlbl ?></h3>
	<img src="img/16/dev.png" title="Hypervisor">
	<select size="1" name="dev" onchange="this.form.submit();">
		<option value=""><?= $sellbl ?> ->
<?php
$link	= DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('devices','s','device,devip,cliport,login','','',array('devos','cliport'),array('=','>'),array('ESX','0'), array('AND') );
$res	= DbQuery($query,$link);
if($res){
	while( $d = DbFetchRow($res) ){
		if($dev == $d[0]){
			$sel = "selected";
			$dip = long2ip($d[1]);
			$dpo = $d[2];
			$dlg = $d[3];
		}else{
			$sel = "";
		}
		echo "\t\t\t\t<option value=\"$d[0]\" $sel>$d[0]\n";
	}
	DbFreeResult($res);
}else{
	print DbError($link);
	die ( mysql_error() );
}
?>
	</select>
	<br>
	<img src="img/16/node.png" title="<?= $srclbl ?> VM">
	<select size="1" name="svm" onchange="this.form.submit();">
		<option value=""><?= $coplbl ?> ->
<?php
if($dev){
	$query	= GenQuery('modules','s','*','','',array('device'),array('='),array($dev) );
	$res	= DbQuery($query,$link);
	if($res){
		while( $m = DbFetchRow($res) ){
			if($svm == $m[8]){
				$sel = "selected";
				$sna = $m[1];
				$svx = $m[3];
				$cpu = ($chw and $m[4])?$m[4]:$cpu;
				$mem = ($chw and $m[6])?$m[6]:$mem;
				$nvm = ($nvm)?$nvm:"$m[1]-new";
			}else{
				$sel = "";
			}
			echo "\t\t\t\t<option value=\"$m[8]\" $sel>$m[1]\n";
		}
		DbFreeResult($res);
	}else{
		print DbError($link);
		die ( mysql_error() );
	}
}
?>
	</select>
	<input type="checkbox" name="chw" <?= $chw ?> title="<?= $coplbl ?> #CPU & Mem" onchange="this.form.submit();">
	<br>
	<img src="img/16/trgt.png" title="<?= $tgtlbl ?> VM">
	<input type="text" name="nvm" class="m" value="<?= $nvm ?>" >
</td>
<td class="top">
	<h3>Node HW</h3>
	<img src="img/16/cpu.png" title="# CPUs">
	<input type="number" min="1" max="8" name="cpu" value="<?= $cpu ?>" class="s">
	<br>
	<img src="img/16/mem.png" title="Mem <?= $sizlbl ?>">
	<input type="number" min="256" max="65535" step="256" name="mem" value="<?= $mem ?>" class="s">
	<br>
	<img src="img/16/db.png" title="HDD <?= $sizlbl ?>">
	<select size="1" name="hdd">
		<option value="1">1Gb
		<option value="4"<?= ( ($hdd == "4")?" selected":"") ?>>4Gb
		<option value="40"<?= ( ($hdd == "40")?" selected":"") ?>>40Gb
		<option value="80"<?= ( ($hdd == "80")?" selected":"") ?>>80Gb
		<option value="160"<?= ( ($hdd == "160")?" selected":"") ?>>160Gb
		<option value="250"<?= ( ($hdd == "250")?" selected":"") ?>>250Gb
		<option value="500"<?= ( ($hdd == "500")?" selected":"") ?>>500Gb
	</select>
</td>
<td class="top">
	<h3><?= $srvlbl ?></h3>
	<img src="img/16/cog.png" title="Boot <?= $latlbl ?>">
	<input type="number" min="0" max="8" name="dly" value="<?= $dly ?>" class="s">
	<br>
	<img src="img/16/node.png" title="VNC Port/Password">
	<input type="number" min="0" max="99" name="vnc" value="<?= $vnc ?>" class="s">
	<input type="text" name="vnp" class="m" value="<?= $vnp ?>" >
	<input type="checkbox" name="sxg" <?= $sxg ?> title="SXGA Screen">
	<br>
	<img src="img/16/cbox.png" title="ISO <?= $fillbl ?>">
	<input type="text" name="iso" class="l" value="<?= $iso ?>" >
</td>
<td class="ctr s">
	<input type="submit" class="button" name="sho" value="<?= $sholbl ?>">
	<br>
	<input type="submit" class="button" name="add" value="<?= $addlbl ?>">
</td>
</tr>
</table>
</form>
<p>

<?php
if($dev and $sna){
	$parr = explode('/', $svx);
	array_pop($parr);
	array_pop($parr);
	$vmpath = implode('/',$parr)."/".$nvm;

	$cmds = "mkdir $vmpath\n";
	$cmds .= "vmkfstools -c ${hdd}g -d thin $vmpath/$nvm.vmdk\n";

	$cmds .= "echo config.version = \\\"8\\\" > $vmpath/$nvm.vmx\n";
	$cmds .= "echo virtualHW.version = \\\"7\\\" >> $vmpath/$nvm.vmx\n";

	$cmds .= "echo scsi0.present = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo scsi0.sharedBus = \\\"none\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo scsi0.virtualDev = \\\"lsilogic\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo scsi0:0.present = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo scsi0:0.fileName = \\\"$nvm.vmdk\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo scsi0:0.deviceType = \\\"scsi-hardDisk\\\" >> $vmpath/$nvm.vmx\n";

	$cmds .= "echo ethernet0.present = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo ethernet0.allowGuestConnectionControl = \\\"FALSE\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo ethernet0.networkName = \\\"VM Network\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo ethernet0.addressType = \\\"generated\\\" >> $vmpath/$nvm.vmx\n";

	$cmds .= "echo guestOS = \\\"other\\\" >> $vmpath/$nvm.vmx\n";

	$cmds .= "echo memsize = \\\"$mem\\\" >> $vmpath/$nvm.vmx\n";
	$cmds .= "echo numvcpus = \\\"$cpu\\\" >> $vmpath/$nvm.vmx\n";

	if($sxg){
		$cmds .= "echo svga.maxWidth =  \\\"1280\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo svga.maxHeight =  \\\"1024\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo svga.vramSize =  \\\"5242880\\\" >> $vmpath/$nvm.vmx\n";
	}

	if($iso){
		$cmds .= "echo ide1:0.present = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo ide1:0.fileName = \\\"$iso\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo ide1:0.deviceType = \\\"cdrom-image\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo ide1:0.startConnected = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
	}

	if($vnc){
		$cmds .= "echo remotedisplay.vnc.port = \\\"".($vnc+5900)."\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo remotedisplay.vnc.enabled = \\\"TRUE\\\" >> $vmpath/$nvm.vmx\n";
		$cmds .= "echo remotedisplay.vnc.password = \\\"$vnp\\\" >> $vmpath/$nvm.vmx\n";
	}

	if($dly){
		$cmds .= "echo bios.bootDelay = \\\"".($dly*1000)."\\\" >> $vmpath/$nvm.vmx\n";
	}

	$cmds .= "vim-cmd solo/registervm $vmpath/$nvm.vmx $nvm\n";

	echo "<h3>$dev ".DevCli($dip,$dpo,1)."</h3>\n";

	$cred = ( strstr($guiauth,'-pass') )?"$_SESSION[user] $pwd":"$dlg dummy";
	$cred = addcslashes($cred,';$!');
	if($_GET['add']){
		$fd =  @fopen("$nedipath/cli/cmd_$_SESSION[user]","w") or die ("$errlbl $wrtlbl log/cmd_$_SESSION[user]");
		fwrite($fd, $cmds);
		fclose($fd);
		echo "<div class=\"textpad txtb qrt\">\n";
		echo "\t<a href=\"Devices-Status.php?dev=".urlencode($dev)."\"><img src=\"img/16/sys.png\"></a>\n";
		echo DevCli($ip,$devpo[$dv],1)." $rpylbl:";
		$out  = system("perl $nedipath/inc/devwrite.pl $nedipath $dev $dip $dpo $cred ESX cmd_$_SESSION[user]", $err);
		echo "\n</div>\n";

		if($err){
			$lvl = 150;
			$cla = 'warn';
			$msg = "User $_SESSION[user] created VM $nvm causing errors";
		}else{
			$lvl = 100;
			$cla = 'good';
			$msg = "User $_SESSION[user] created VM $nvm successfully";
		}
		$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array($lvl,time(),$dev,$msg,'usrv',$dev) );
		if( !DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}
		$nout = array_sum( explode(' ',$out) );
		$outh = ($nout > 50)?500:$nout*20;
		echo "<div class=\"textpad code pre $cla bctr tqrt\" height=\"$outh\">\n";
		echo "<strong>$msg</strong>:\n";
		include("$nedipath/cli/".rawurlencode($dev)."/cmd_$_SESSION[user].log");
		echo "</div>\n";
	}elseif($_GET['sho']){
		echo "<div class=\"textpad txtb code txta tqrt pre\">$cmds</div>\n";
	}else{
		echo "<div class=\"textpad txtb tqrt\">$tim[n] $cmdlbl $sholbl||$addlbl</div>";
	}
}else{
	echo "<div class=\"textpad alrm drd half\">This is work in progress and intended for my ESXi! It may not work properly in other environments yet...</div>\n";
}

include_once ("inc/footer.php");

?>
