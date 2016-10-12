<?php
//===============================
// Node related functions.
//===============================

//===================================================================
// Emulate good old nbtstat on port 137
function NbtStat($ip) {

	global $timeout, $nonlbl, $rpylbl;

	if ($ip == "0.0.0.0") {
		return "<img src=\"img/16/bcls.png\"> No IP!";
	}else{
		$nbts	= pack('C50',129,98,00,00,00,01,00,00,00,00,00,00,32,67,75,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,00,00,33,00,01);
		$fp		= @fsockopen("udp://$ip", 137, $errno, $errstr);
		if (!$fp) {
			return "ERROR! $errno $errstr";
		}else {
			fwrite($fp, "$nbts");
			stream_set_timeout($fp,$timeout);
			$data =  fread($fp, 400);
			fclose($fp);

			if (preg_match("/AAAAAAAAAA/",$data) ){
				$nna = unpack('cnam',substr($data,56,1));  							# Get number of names
				$out = substr($data,57);                							# get rid of WINS header

				for ($i = 0; $i < $nna['nam'];$i++){
					$nam = preg_replace("/ +/","",substr($out,18*$i,15));
					$id = unpack('cid',substr($out,18*$i+15,1));
					$fl = unpack('cfl',substr($out,18*$i+16,1));
					$na = "";
					$gr = "";
					$co = "";
					if ($fl['fl'] > 0){
						if ($id['id'] == "3"){
							if ($na == ""){
								$na = $nam;
							}else{
								$co = $nam;
							}
						}
					}else{
						if ($na == ""){
							$gr = $nam;
						}
					}
				}
				return "<img src=\"img/16/bchk.png\"> $na $gr $co";
			}else{
				return "<img src=\"img/16/bstp.png\"> $nonlbl $rpylbl";
			}
		}
	}
}

//===================================================================
// Check for open port and return server information, if possible.
function CheckTCP ($ip, $p,$d){

	global $debug,$sndlbl,$timeout;

	if ($ip == "0.0.0.0") {
		return "<img src=\"img/16/bcls.png\"> No IP!";
	}else{
		if($debug){echo "<div class=\"textpad noti \">$sndlbl $ip:$p \"$d\"</div>\n";}

		$fp = @fsockopen($ip, $p, $errno, $errstr, 1 );

		flush();
		if (!$fp) {
			return "<img src=\"img/16/bstp.png\"> $errstr";
		} else {
			fwrite($fp,$d);
			stream_set_timeout($fp,$timeout);
			$ans = fread($fp, 255);
			$ans .= fread($fp, 255);
			$ans .= fread($fp, 255);
			fclose($fp);

			if( preg_match("/Server:(.*)/i",$ans,$mstr) ){
				$srv = "<i>$mstr[1]</i>";
			}else{
				$srv = "";
			}
			if( preg_match("/<address>(.*)<\/address>/i",$ans,$mstr) ){
				return "<img src=\"img/16/bchk.png\"> $mstr[1] $srv";
			}elseif( preg_match("/<title>(.*)<\/title>/i",$ans,$mstr) ){
				return "<img src=\"img/16/bchk.png\"> $mstr[1] $srv";
			}elseif( preg_match("/content=\"(.*)\">/i",$ans,$mstr) ){
				return "<img src=\"img/16/bchk.png\"> $mstr[1] $srv";
			}else{
				$mstr = substr(preg_replace("/[^\x20-\x7e]|<!|!>|(<script.*)/i",'',$ans),0,50);
				return "<img src=\"img/16/bchk.png\"> $mstr $srv";
			}
		}
	}
}

//===================================================================
// Create and send magic packet (copied from the PHP webiste)
function Wake($ip, $mac, $port){

	global $errlbl, $sndlbl;

	$nic = fsockopen("udp://" . $ip, $port);
	if($nic){
		$packet = "";
		for($i = 0; $i < 6; $i++)
			$packet .= chr(0xFF);
		for($j = 0; $j < 16; $j++){
			for($k = 0; $k < 6; $k++){
				$str = substr($mac, $k * 2, 2);
				$dec = hexdec($str);
				$packet .= chr($dec);
			}
		}
		$ret = fwrite($nic, $packet);
		fclose($nic);
		if($ret){
			echo "<h5>WoL $sndlbl $ip OK</h5>\n";
			return true;
		}
	}
	echo "<h4>WoL $sndlbl $ip $errlbl</h4>\n";
	return false;
}

//===================================================================
// Draw Node's Metric Chart
function MetricChart($id, $sz, $str){

	global $spdlbl,$debug,$anim;

	if($sz == 4){
		$w = 320;
		$h = 200;
		$f = 9;
	}elseif($sz == 3){
		$w = 200;
		$h = 100;
		$f = 8;
	}else{
		$w = 110;
		$h = 70;
		$f = 8;
	}

	$i = 0;
	if( preg_match('/[M-Z]/',$str) ){
		foreach( str_split($str) as $ch ){
			$met['labels'][] = $i++;
			$snr['data'][] = 3*(90-ord($ch));
		}
		$snr['label']           = "SNR";
		$snr['fillColor']       = "rgba(100,200,100,0.5)";
		$snr['strokeColor']     = "rgba(50,150,50,1)";
		$snr['highlightFill']   = "rgba(75,175,75,0.5)";
		$snr['highlightStroke'] = "rgba(25,150,25,1)";
		$met['datasets'][]      = $snr;
		$typ = 'Bar';
	}else{
		foreach( str_split($str) as $ch ){
			$met['labels'][] = $i++;
			$v = 76 - ord($ch);
			if( $v > 5 ){
				$spd['data'][] = $v-6;
				$dup['data'][] = 0;
			}else{
				$spd['data'][] = $v;
				$dup['data'][] = 1;
			}
		}
		$spd['label']       = $spdlbl;
		$spd['fillColor']   = "rgba(100,150,200,0.5)";
		$spd['strokeColor'] = "rgba(100,150,200,1)";
		$spd['pointColor']  = "rgba(100,150,200,1)";
		$spd['pointHighlightFill']  = "rgba(20,30,20,1)";
		$dup['label']       = 'Duplex';
		$dup['fillColor']   = "rgba(200,100,100,0.5)";
		$dup['strokeColor'] = "rgba(200,100,100,1)";
		$dup['pointColor']  = "rgba(200,100,100,1)";
		$dup['pointHighlightFill']  = "rgba(40,20,20,1)";
		$met['datasets'][]  = $spd;
		$met['datasets'][]  = $dup;
		$typ = 'Line';
	}

?>
<canvas id="<?= $id ?>" class="genpad" width="<?= $w ?>" height="<?= $h ?>"></canvas>

<script language="javascript">
var data = <?= json_encode($met,JSON_NUMERIC_CHECK) ?>

var ctx = document.getElementById("<?= $id ?>").getContext("2d");
var myNewChart = new Chart(ctx).<?= $typ ?>(data, {pointDotRadius : 2, scaleFontSize: <?= $f ?><?= $anim ?>});
</script>

<?php
	if($debug){
		echo "<div class=\"textpad code pre txta\">\n";
		print_r($met);
		echo "</div>\n";
	}
}

//===================================================================
// Delete node and related tables
function NodDelete($dln){

	global $link,$delbl,$errlbl,$updlbl,$nedipath;

	$query	= GenQuery('nodes','d','','','',array('mac'),array('='),array($dln) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Node</span> ";}
	$query	= GenQuery('nodarp','d','','','',array('mac'),array('='),array($dln) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">".DbError($link)."</span> ";}else{echo "<span class=\"olv\">IP ARP</span> ";}
	$query	= GenQuery('nodnd','d','','','',array('mac'),array('='),array($dln) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">".DbError($link)."</span> ";}else{echo "<span class=\"olv\">IPv6 ND</span> ";}
	$query	= GenQuery('iptrack','d','','','',array('mac'),array('='),array($dln) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">".DbError($link)."</span> ";}else{echo "<span class=\"olv\">IPtrack</span> ";}
	$query	= GenQuery('iftrack','d','','','',array('mac'),array('='),array($dln) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">".DbError($link)."</span> ";}else{echo "<span class=\"olv\">IFtrack</span> ";}
}

?>
