<?PHP
//===================================================================
// Miscellaneous functions
//===================================================================

// Event icons & colors based on level
$mico['30']  = "fogy";
$mico['50']  = "fogr";
$mico['100'] = "fobl";
$mico['150'] = "foye";
$mico['200'] = "foor";
$mico['250'] = "ford";

$mbak['30']  = "txtb";
$mbak['50']  = "good";
$mbak['100'] = "noti";
$mbak['150'] = "warn";
$mbak['200'] = "alrm";
$mbak['250'] = "crit";

// Acknowledged events
$mico['1']  = "fogy";
$mico['5']  = "fogr";
$mico['10'] = "fobl";
$mico['15'] = "foye";
$mico['20'] = "foor";
$mico['25'] = "ford";

$mbak['1']  = "txtd";
$mbak['5']  = "txtd";
$mbak['10'] = "txtd";
$mbak['15'] = "txtd";
$mbak['20'] = "txtd";
$mbak['25'] = "txtd";

# Some general relations (used in Other-Noodle and Reports-Custom)
$tblicon = array('devices'	=> 'dev',
		'modules'	=> 'cubs',
		'interfaces'	=> 'port',
		'vlans'		=> 'vlan',
		'configs'	=> 'conf',
		'networks'	=> 'net',
		'links'		=> 'ncon',
		'locations'	=> 'home',
		'inventory'	=> 'pkg',
		'monitoring'	=> 'bino',
		'incidents'	=> 'bomb',
		'nodes'		=> 'nods',
		'nodarp'	=> 'card',
		'nodnd'		=> 'ipv6',
		'dns'		=> 'abc',
		'nodetrack'	=> 'note',
		'iftrack'	=> 'walk',
		'install'	=> 'dril',
		'iptrack'	=> 'glob',
		'policies'	=> 'hat3',
		'users'		=> 'ugrp',
		'chat'		=> 'say',
		'events'	=> 'bell',
	);

$joindv = array('devices'	=> '',
		'modules'	=> 'device',
		'interfaces'	=> 'device',
		'vlans'		=> 'device',
		'configs'	=> 'device',
		'networks'	=> 'device',
		'links'		=> 'device',
		'locations'	=> '',
		'inventory'	=> '',
		'monitoring'	=> 'device',
		'incidents'	=> 'device',
		'nodes'		=> 'device',
		'nodarp'	=> 'arpdevice',
		'nodnd'		=> 'nddevice',
		'ipnames'	=> '',
		'nodetrack'	=> 'device',
		'iftrack'	=> 'device',
		'iptrack'	=> 'arpdevice',
		'policies'	=> '',
		'users'		=> '',
		'chat'		=> '',
		'events'	=> 'device'
	);

$collnk = array('device'	=> 'Devices-Status.php?dev=',
		'source'	=> 'Monitoring-Events.php?in[]=source&op[]==&st[]=',
		'depend1'	=> 'Devices-Status.php?dev=',
		'depend2'	=> 'Devices-Status.php?dev=',
		'ifname'	=> 'Devices-Interfaces.php?in[]=ifname&op[]==&st[]=',
		'mac'		=> 'Nodes-Status.php?st=',
		'neighbor'	=> 'Devices-Status.php?dev=',
		'nbrifname'	=> 'Devices-Interfaces.php?in[]=ifname&op[]==&st[]=',
		'type'		=> 'Devices-List.php?in[]=type&op[]==&st[]=',
		'vlanname'	=> 'Devices-Vlans.php?in[]=vlanname&op[]==&st[]='
	);


//===================================================================
// sort based on floor (can be adjusted directly)
function Floorsort($a, $b){

	if (is_numeric($a) and is_numeric($b) ){
		if ($a == $b) return 0;
		return ($a > $b) ? -1 : 1;
	}elseif( preg_match('/[a-t]/i',$a) ){								# Some use letters for floors
		return strnatcmp ( $b,$a );
	}else{
		return strnatcmp ( $a,$b );
	}
}

//===================================================================
// Read configuration
function ReadConf($group=''){

	global $modgroup,$locsep,$bldsep,$lang,$redbuild,$disc,$fahrtmp;
	global $comms,$mod,$backend,$dbhost,$dbname,$dbuser,$dbpass,$retire;
	global $timeout,$ignoredvlans,$useivl,$cpua,$mema,$tmpa,$trfa,$supa;
	global $poew,$pause,$latw,$rrdcmd,$rrdopt,$nedipath,$rrdstep, $arppoison;
	global $cacticli,$cactiuser,$cactipass,$cactidb,$cactihost,$cactiurl;
	global $notify,$guiauth,$radsrv, $ldapsrv, $ldapmap, $nfdpath;
	global $rdbhost,$rdbuser,$rdbpass,$rdbname;

	if (file_exists("$nedipath/nedi.conf")) {
		$conf = file("$nedipath/nedi.conf");
	}elseif (file_exists("/etc/nedi.conf")) {
		$conf = file("/etc/nedi.conf");
	}elseif (file_exists("../nedi.conf")) {
		$conf = file("../nedi.conf");
	}else{
		echo "Can't find nedi.conf!";
		die;
	}

	$rrdopt	= '';
	$locsep	= ' ';
	$bldsep	= ' ';
	foreach ($conf as $cl) {
		if ( !preg_match("/^#|^$/",$cl) ){
			$v =  preg_split('/[\t\s]+/', rtrim($cl,"\n\r\0") );

			if ($v[0] == "module"){
				$v[4] = isset($v[4]) ? $v[4] : "usr";
				$modgroup["$v[1]-$v[2]"] = $v[4];
				if( strpos($group,$v[4]) !== false){
					$mod[$v[1]][$v[2]] = $v[3];
				}
			}
			if ($v[0] == "comm"){
				$comms[$v[1]]['aprot'] = (isset($v[3]))?$v[2]:"";
				$comms[$v[1]]['apass'] = (isset($v[3]))?$v[3]:"";
				$comms[$v[1]]['pprot'] = (isset($v[5]))?$v[4]:"";
				$comms[$v[1]]['ppass'] = (isset($v[5]))?$v[5]:"";
			}
			elseif ($v[0] == "backend")	{$backend = $v[1];}
			elseif ($v[0] == "dbhost")	{$dbhost  = $v[1];}
			elseif ($v[0] == "dbname")	{$dbname  = isset($_SESSION['snap'])?$_SESSION['snap']:$v[1];}
			elseif ($v[0] == "dbuser")	{$dbuser  = $v[1];}
			elseif ($v[0] == "dbpass")	{$dbpass  = $v[1];}

			elseif ($v[0] == "rdbhost")	{$rdbhost = $v[1];}
			elseif ($v[0] == "rdbname")	{$rdbname = $v[1];}
			elseif ($v[0] == "rdbuser")	{$rdbuser = $v[1];}
			elseif ($v[0] == "rdbpass")	{$rdbpass = $v[1];}

			elseif ($v[0] == "cpu-alert")	{$cpua = $v[1];}
			elseif ($v[0] == "mem-alert")	{$mema = $v[1];}
			elseif ($v[0] == "temp-alert")	{$tmpa = $v[1];}
			elseif ($v[0] == "poe-warn")	{$poew = $v[1];}
			elseif ($v[0] == "traf-alert")	{$trfa = $v[1];}
			elseif ($v[0] == "supply-alert"){$supa = $v[1];}
			elseif ($v[0] == "notify")	{$notify = $v[1];}

			elseif ($v[0] == "latency-warn"){$latw         = $v[1];}
			elseif ($v[0] == "pause")	{$pause        = $v[1];}
			elseif ($v[0] == "ignoredvlans"){$ignoredvlans = $v[1];}
			elseif ($v[0] == "useivl")	{$useivl       = $v[1];}
			elseif ($v[0] == "retire")	{$retire       = $v[1];}
			elseif ($v[0] == "arppoison")	{$arppoison    = $v[1];}
			elseif ($v[0] == "timeout")	{$timeout      = $v[1];}

			elseif ($v[0] == "rrdcmd")	{$rrdcmd   = $v[1];if($v[2])$rrdopt = $v[2];}
			elseif ($v[0] == "nedipath")	{$nedipath = $v[1];}
			elseif ($v[0] == "rrdstep")	{$rrdstep  = $v[1];}

			elseif ($v[0] == "locsep")	{$locsep   = $v[1];if($v[2])$bldsep   = $v[2];}
			elseif ($v[0] == "guiauth")	{$guiauth  = $v[1];}
			elseif ($v[0] == "radserver")	{$radsrv[] = array($v[1],$v[2],$v[3],$v[4],$v[5]);}
			elseif ($v[0] == "ldapsrv")	{$ldapsrv  = array($v[1],$v[2],$v[3],$v[4],$v[5],$v[6]);}
			elseif ($v[0] == "ldapmap")	{$ldapmap  = array($v[1],$v[2],$v[3],$v[4],$v[5],$v[6],$v[7],$v[8]);}
			elseif ($v[0] == "redbuild")	{array_shift($v);$redbuild = implode(" ",$v);}
			elseif ($v[0] == "disclaimer")	{array_shift($v);$disc = implode(" ",$v);}

			elseif ($v[0] == "cacticli")	{array_shift($v);$cacticli = implode(" ",$v);}
			elseif ($v[0] == "cactihost")	{$cactihost = $v[1];}
			elseif ($v[0] == "cactidb")	{$cactidb   = $v[1];}
			elseif ($v[0] == "cactiuser")	{$cactiuser = $v[1];}
			elseif ($v[0] == "cactipass")	{$cactipass = $v[1];}
			elseif ($v[0] == "cactiurl")	{$cactiurl  = $v[1];}

			elseif ($v[0] == "nfdpath")	{$nfdpath  = $v[1];}
		}
	}
}

//===================================================================
// Avoid directory traversal attacks (../ or ..\) TODO consider sanitize switch for DB fields and system commands?
// Remove <script> tags
// Avoid condition exclusion (e.g. attacking viewdev) with mysql comment --
// Avoid Javascript injection
// Recursive because array elements can be array as well
function sanitize( $arr ){
	if ( is_array($arr) ){
		return array_map( 'sanitize', $arr );
	}
	return preg_replace( "/\.\.\/|--|<\/?(java)?script>/i","", $arr );
}

//===================================================================
// Return IP address from hex value
function hex2ip($hip){
	return  hexdec(substr($hip, 0, 2)).".".hexdec(substr($hip, 2, 2)).".".hexdec(substr($hip, 4, 2)).".".hexdec(substr($hip, 6, 2));
}

//===================================================================
// Return IP address as hex
function ip2hex($ip){
	$i =  explode('.', str_replace( "*", "", $ip ) );
	return  sprintf("%02x%02x%02x%02x",$i[0],$i[1],$i[2],$i[3]);
}

//===================================================================
// Return IP address as bin
function ip2bin($ip){
	$i	=  explode('.',$ip);
	return sprintf(".%08b.%08b.%08b.%08b",$i[0],$i[1],$i[2],$i[3]);
}

//===================================================================
// Invert IP address
function ipinv($ip){
	$i	=  explode('.',$ip);
	return (255-$i[0]).".".(255-$i[1]).".".(255-$i[2]).".".(255-$i[3]);
}

//===================================================================
// convert netmask to various formats and check whether it's valid.
function Masker($in){

	if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$in) ){
		$mask = $in;
		list($n1,$n2,$n3,$n4) = explode('.', $in);
		$bits = str_pad(decbin($n1),8,0,STR_PAD_LEFT) .
			str_pad(decbin($n2),8,0,STR_PAD_LEFT) .
			str_pad(decbin($n3),8,0,STR_PAD_LEFT) .
			str_pad(decbin($n4),8,0,STR_PAD_LEFT);
		#$bits = str_pad(decbin($n1) . decbin($n2) . decbin($n3) . decbin($n4),32,0);
		$nbit = count_chars($bits);
		$pfix = $nbit[49];										// the 49th char is "1"...
		$dec  = ip2long($in);
	}elseif(preg_match("/^[-]|\d{3,10}$/",$in ) ){
		if( is_int($in) ){
			$in = sprintf("%u",$in);
		}
		$mask = long2ip($in);
		$bits = base_convert($in, 10, 2);
		$nbit = count_chars($bits);
		$pfix = $nbit[49];
		$dec  = $in;
	}elseif(preg_match("/^\d{1,2}$/",$in) ){
		#shift left of 255.255.255.255 will be 255.255.255.255.0! Trim after SHL (Vasily)
		#$bits = base_convert(sprintf("%u",0xffffffff << (32 - $in) ),10,2);
		$bits = base_convert(sprintf("%u",0xffffffff & (0xffffffff << (32 - $in)) ),10,2);
		$mask = bindec(substr($bits, 0,8)).".".bindec(substr($bits, 8,8)).".".bindec(substr($bits, 16,8)).".".bindec(substr($bits, 24,8));
		$pfix = $in;
		$dec  = 0xffffffff << (32 - $in);
	}
	$bin	= preg_replace( "/(\d{8})/", ".\$1", $bits );
	if(strstr($bits,'01') ){
		return array($pfix,'Illegal Mask',$bin,$dec);
	}else{
		return array($pfix,$mask,$bin,$dec);
	}
}

//===================================================================
// Replace ridiculously big numbers with readable ones
function DecFix($n,$d=0){

	if($n >= 1000000000){
		return round($n/1000000000,$d)."G";
	}elseif($n >= 1000000){
		return round($n/1000000,$d)."M";
	}elseif($n >= 1000){
		return round($n/1000,$d)."k";
	}elseif($n){											# keeps 0 empty
		return $n;
	}

}

//===================================================================
// Colorize html bg according to timestamps
function Agecol($fs, $ls,$row){

	global $retire;

        $o = ( strpos($_SESSION['theme'],'-Dk')?40:120) + 20 * $row;
	if(!$ls){$ls = $fs;}

        $tmpf = round(100 - 100 * (time() - $fs) / ($retire * 86400));
        if ($tmpf < 0){$tmpf = 0;}

        $tmpl = round(100 * (time() - $ls) / ($retire * 86400));
        if ($tmpl > 100){$tmpl = 100;}

        $tmpd = round(100 * ($ls  - $fs) / ($retire * 86400));
        if ($tmpd > 100){$tmpd = 100;}

        $f = sprintf("%02x",$tmpf + $o);
        $l = sprintf("%02x",$tmpl + $o);
        $d = sprintf("%02x",$tmpd + $o);
        $g = sprintf("%02x",$o);

        return array("$g$f$d","$l$g$d");
}

#===================================================================
# Returns color based on type, order and offset for RRDs and Charts
# Type can traffic, error etc. or a numeric rgb pattern like 125,
# whereas any digit + offset must not be higher than 5!
# Parameters:	type, count, offset(0-3)
# Global:	-
# Return:	color
function GetCol($typ,$cnt,$off=0,$dec=0){

	if($typ == 'trf'){
		$r = $cnt%3*5+$off;
		$g = $cnt%4*2+6+$off;
		$b = $cnt%5*3+$off;
	}elseif($typ == 'err'){
		$r = $cnt%4*2+6+$off;
		$g = $cnt%5*3+$off;
		$b = $cnt%3*5+$off;
	}elseif($typ == 'dsc'){
		$r = $cnt%4*2+6+$off;
		$g = $cnt%3*5+$off;
		$b = $cnt%5*3+$off;
	}elseif($typ == 'brc'){
		$r = $cnt%5*3+$off;
		$g = $cnt%9+$off;
		$b = 13-$cnt%13+$off;
	}else{
		$r = substr($typ,0,1)+$cnt%11+$off;
		$g = substr($typ,1,1)+$cnt%11+$off;
		$b = substr($typ,2,1)+$cnt%11+$off;
	}
	return ($dec)?sprintf("%d,%d,%d",$r*16,$g*16,$b*16):sprintf("#%x%x%x",$r,$g,$b);
}

//===================================================================
// Generate html select box
function selectbox($type,$sel){

	global $bwdlbl;

	if($type == "oper"){
		$options = array("~"=>"~","!~"=>"!~","LIKE"=>"like","NOT LIKE"=>"!like",">"=>">","="=>"=","!="=>"!=",">="=>">=","<"=>"<","<="=>"<=","&"=>"and","|"=>"or");
	}elseif($type == "comop"){
		$options = array(""=>"-","AND"=>"and","OR"=>"or",">"=>"Col > Col","="=>"Col = Col","!="=>"Col != Col","<"=>"Col < Col");
	}elseif($type == "limit"){
		$options = array("3"=>"3","5"=>"5","10"=>"10","25"=>"25","50"=>"50","100"=>"100","250"=>"250","500"=>"500","1000"=>"1000","2500"=>"2500","0"=>"none!");
	}elseif($type == "bw"){
		$options = array(""=>"$bwdlbl ->","1544000"=>"T1","2048000"=>"E1","10000000"=>"10M","100000000"=>"100M","1000000000"=>"1G","10000000000"=>"10G","100000000000"=>"100G");

	}
	foreach ($options as $key => $txt){
	       $selopt = ($sel == "$key")?' selected':'';
	       echo "	<option value=\"$key\"$selopt>$txt\n";
	}
	#TODO add this and opening tag to function? echo "</select>\n" or just return array, which can be used for sanity checks?
}

//===================================================================
// Generate html filter elements
function Filters($num=4){

	global $cols,$in,$op,$st,$co;
	global $collbl,$cndlbl,$vallbl;
?>
<script type="text/javascript" src="inc/datepickr.js"></script>
<link rel="stylesheet" type="text/css" href="inc/datepickr.css" />

<div>
<select name="in[]" title="<?= $collbl ?> 1">
<?php foreach ($cols as $k => $v){
	if( !preg_match('/(BL|IG|NS|NF)$/',$k) ){
		echo "	<option value=\"$k\"".( ($in[0] == $k)?' selected':'').">$v\n";
	}
}?>
</select>
<select name="op[]" id="oa1">
<?php selectbox("oper",$op[0]) ?>
</select>
<?php	#if( $num == 1 ) echo '<br>'; ?>
<input  name="st[]" id="sa1" type="text" value="<?= $st[0] ?>" placeholder="<?= $cndlbl ?> 1" onfocus="select();" class="m" autofocus>
<script>new datepickr('sa1', {'dateFormat': 'm/d/y'});</script>
<?php	if( $num == 1 ){ echo '</div>';return;} ?>
<select name="co[]" onchange="convis('1',this.value);">
	<option value="">
	<option value="AND"<?= ($co[0] == 'AND')?' selected':'' ?>>and
	<option value="OR"<?=  ($co[0] == 'OR' )?' selected':'' ?>>or
	<option value=">"<?=   ($co[0] == '>'  )?' selected':'' ?>>1 > 2
	<option value="="<?=   ($co[0] == '='  )?' selected':'' ?>>1 = 2
	<option value="!="<?=  ($co[0] == '!=' )?' selected':'' ?>>1 != 2
	<option value="<"<?=   ($co[0] == '<'  )?' selected':'' ?>>1 < 2
</select>
<br>
<select name="in[]" id="ib1" title="<?= $collbl ?> 2">
<?php foreach ($cols as $k => $v){
	if( !preg_match('/(BL|IG|NS|NF)$/',$k) ){
		echo "	<option value=\"$k\"".( ($in[1] == $k)?' selected':'').">$v\n";
	}
}?>
</select>
<select name="op[]" id="ob1">
<?php selectbox("oper",$op[1]) ?>
</select>
<input  name="st[]" id="sb1" type="text" value="<?= $st[1] ?>" placeholder="<?= $cndlbl ?> 2" onfocus="select();" class="m">
<select name="co[]" id="cb1" onchange="fltvis(this.value);">
	<option value="">
	<option value="AND"<?= ($co[1] == 'AND')?' selected':'' ?>>and
	<option value="OR"<?= ($co[1] == 'OR')?' selected':'' ?>>or
</select>
</div>
<div id="flt2" style="padding: 4px 0px;visibility: hidden">
<select name="in[]" id="ia2" title="<?= $collbl ?> 3">
<?php foreach ($cols as $k => $v){
	if( !preg_match('/(BL|IG|NS|NF)$/',$k) ){
		echo "	<option value=\"$k\"".( ($in[2] == $k)?' selected':'').">$v\n";
	}
}?>
</select>
<select name="op[]" id="oa2" >
<?php selectbox("oper",$op[2]) ?>
</select>
<input  name="st[]" id="sa2"  type="text" value="<?= $st[2] ?>" placeholder="<?= $cndlbl ?> 3" onfocus="select();" class="m">
<select name="co[]" id="ca2"  onchange="convis('2',this.value);">
	<option value="">
	<option value="AND"<?= ($co[2] == 'AND')?' selected':'' ?>>and
	<option value="OR"<?=  ($co[2] == 'OR' )?' selected':'' ?>>or
	<option value=">"<?=   ($co[2] == '>'  )?' selected':'' ?>>3 > 4
	<option value="="<?=   ($co[2] == '='  )?' selected':'' ?>>3 = 4
	<option value="!="<?=  ($co[2] == '!=' )?' selected':'' ?>>3 != 4
	<option value="<"<?=   ($co[2] == '<'  )?' selected':'' ?>>3 < 4
</select>
<br>
<select name="in[]" id="ib2" title="<?= $collbl ?> 4">
<?php foreach ($cols as $k => $v){
	if( !preg_match('/(BL|IG|NS|NF)$/',$k) ){
		echo "	<option value=\"$k\"".( ($in[3] == $k)?' selected':'').">$v\n";
	}
}?>
</select>
<select name="op[]" id="ob2" style="visibility: hidden">
<?php selectbox("oper",$op[3]) ?>
</select>
<input  name="st[]" id="sb2" type="text" value="<?= $st[3] ?>" placeholder="<?= $cndlbl ?> 4" onfocus="select();" class="m">
</div>

<script>
function fltvis(val){

	if(val){
		document.getElementById('flt2').style.visibility="inherit";
	}else{
		document.getElementById('ca2').selectedIndex=0;
		window.onload = convis('2','');
		document.getElementById('flt2').style.visibility="hidden";
	}
}

function convis(sq,op){

	if( op.match(/[<>=]/) ){
		document.getElementById('oa'+sq).style.visibility="hidden";
		document.getElementById('sa'+sq).style.visibility="hidden";
		document.getElementById('ib'+sq).style.visibility="inherit";
		document.getElementById('ob'+sq).style.visibility="hidden";
		document.getElementById('sb'+sq).style.visibility="hidden";
		if( sq == '1' ){
			document.getElementById('cb'+sq).style.visibility="inherit";
		}
	}else if(op == 'AND' || op == 'OR'){
		document.getElementById('oa'+sq).style.visibility="inherit";
		document.getElementById('sa'+sq).style.visibility="inherit";
		document.getElementById('ib'+sq).style.visibility="inherit";
		document.getElementById('ob'+sq).style.visibility="inherit";
		document.getElementById('sb'+sq).style.visibility="inherit";
		if( sq == '1' ){
			document.getElementById('cb'+sq).style.visibility  = "inherit";
		}
	}else{
		document.getElementById('oa'+sq).style.visibility="inherit";
		document.getElementById('sa'+sq).style.visibility="inherit";
		document.getElementById('ib'+sq).style.visibility="hidden";
		document.getElementById('ob'+sq).style.visibility="hidden";
		document.getElementById('sb'+sq).style.visibility="hidden";
		if( sq == '1' ){
			document.getElementById('cb'+sq).style.visibility="hidden";
			document.getElementById('cb'+sq).selectedIndex=0;
			document.getElementById('flt2').style.visibility = "hidden";
		}
	}
}

window.onload = convis('1','<?= $co[0] ?>');
window.onload = convis('2','<?= $co[2] ?>');
window.onload = fltvis('<?= $co[1] ?>');

new datepickr('sb1', {'dateFormat': 'm/d/y'});
new datepickr('sa2', {'dateFormat': 'm/d/y'});
new datepickr('sb2', {'dateFormat': 'm/d/y'});
</script>
<?PHP
}

//===================================================================
// Generate condition header or SQL if mod=2
function Condition($in,$op,$st,$co='',$mod=0){

	global $cols;

	$h = '';
	$w = '';

	$argok = ($co)?array_key_exists(0,$co):0;
	$comok = 0;
	if( !count($in) ) return '';

	if($argok and preg_match('/[<>=]/',$co[0]) ){							# subconditions 1 and 2 compare columns
		$w .= $in[0]." $co[0] ".$in[1];
		if($mod != 2) $h .= $cols[$in[0]]." $co[0] ".$cols[$in[1]];
		$comok = 1;
	}elseif( $op[0] and !( preg_match('/~|LIKE$/i',$op[0]) and $st[0] === '') ){			# process normally unless empty regexp/like in 1
		$w .= AdOpVal($in[0],$op[0],$st[0]);
		if($mod != 2) $h .= $cols[$in[0]]." $op[0] '".$st[0]."'";
		if($argok and $co[0] and $op[1] and !( preg_match('/~|LIKE$/i',$op[1]) and $st[1] === '') ){# subcondition 2 unless empty regexp/like
			$w .= " $co[0] ".AdOpVal($in[1],$op[1],$st[1]);
			if($mod != 2) $h .= " $co[0] ".$cols[$in[1]]." $op[1] '".$st[1]."'";
			$comok = 1;
		}
	}
	if($comok and array_key_exists(1,$co) and $co[1] ){						# Combining subconditions TODO turn into loop for unlimited combinations?
		$w2 = $h2 = '';
		if( array_key_exists(2,$co) and preg_match('/[<>=]/',$co[2]) ){				# subconditions 3 and 4 compares columns
			$w .= " $co[1] ".$in[2]." $co[2] ".$in[3];
			if($mod != 2) $h .= " $co[1] ".$cols[$in[2]]." $co[2] ".$cols[$in[3]];
		}elseif($op[2] and !( preg_match('/~|LIKE$/i',$op[2]) and $st[2] === '') ){		# process normally unless empty regexp/like in 3
			$w2 = AdOpVal($in[2],$op[2],$st[2]);
			if($mod != 2) $h2 = $cols[$in[2]]." $op[2] '".$st[2]."'";
			if($co[2] and $op[2] and !( preg_match('/~|LIKE$/i',$op[3]) and $st[3] === '') ){# subcondition 4 unless empty regexp/like
				$w2 .= " $co[2] ".AdOpVal($in[3],$op[3],$st[3]);
				$h2 .= " $co[2] ".$cols[$in[3]]." $op[3] '".$st[3]."'";
			}
			$w = "($w) $co[1] ($w2)";
			$h = "($h) $co[1] ($h2)";
		}
	}

	if($mod == 2){
		 return $w;
	}elseif($mod){
		 return $h;
	}else{
		if($h) echo "\n<h3>$h</h3>\n\n";
	}
}

//===================================================================
// Generate table header
// Opt	class, column mode: 2 or 3=use all, 0 or 3=no sorting (1 shows selected columns with sorting arrow)
// Keys BL=blank, IG=ignored, NS=no-sort, NF=no-filter
function TblHead($class,$mode = 0){

	global $ord,$cols,$col,$altlbl,$srtlbl;

	if( isset($_GET['xls']) ){
		echo "<table>\n\t<tr>\n";
	}else{
		echo "<table class=\"content\">\n\t<tr>\n";
	}

	if($mode == 2 or $mode == 3){
		$mycol = array_keys($cols);
	}else{
		$mycol = $col;
	}
	foreach( $mycol as $n ){
		if( !preg_match('/IG$/',$n) ){
			if( preg_match('/BL$/',$n) ){
				echo "\t\t<th class=\"$class\">\n\t\t\t&nbsp;\n\t\t</th>\n";
			}elseif( isset($_GET['xls']) or preg_match('/NS$/',$n) or $mode == 3 or !$mode ){
				echo "\t\t<th class=\"$class\">\n\t\t\t$cols[$n]\n\t\t</th>\n";
			}elseif( !array_key_exists($n,$cols) ){
				echo "\t\t<th class=\"$class\">\n\t\t\t$n\n\t\t</th>\n";
			}else{
				$nclr = preg_replace('/NF$/','',$n);
				if($ord == $nclr){
					echo "\t\t<th class=\"$class highlight nw\">\n\t\t\t$cols[$n] <a href=\"?";
					echo preg_replace('/&ord=[\w+]+/',"",$_SERVER['QUERY_STRING']);
					echo "&ord=$nclr+desc\"><img src=\"img/up.png\" title=\"$srtlbl\"></a>\n\t\t</th>\n";
				}elseif($ord == "$nclr desc"){
					echo "\t\t<th class=\"$class highlight nw\">\n\t\t\t$cols[$n] <a href=\"?";
					echo preg_replace('/&ord=[\w+]+/',"",$_SERVER['QUERY_STRING']);
					echo "&ord=$nclr\"><img src=\"img/dwn.png\" title=\"$altlbl $srtlbl\"></a>\n\t\t</th>\n";
				}else{
					echo "\t\t<th class=\"$class nw\">\n\t\t\t$cols[$n] <a href=\"?";
					echo preg_replace('/&ord=[\w+]+/',"",$_SERVER['QUERY_STRING']);
					echo "&ord=$nclr+desc\"><img src=\"img/both.png\" title=\"$srtlbl $nclr\"></a>\n\t\t</th>\n";
				}
			}
		}
	}
	echo "	</tr>\n";
}

//===================================================================
// Generate table row
function TblRow($bg,$static=0){


	if( isset($_GET['xls']) ){
		echo "\t<tr>\n";
	}elseif($static){
		echo "\t<tr class=\"$bg\">\n";
	}elseif(isset($_GET['print']) ){
		echo "\t<tr class=\"$bg\" onclick=\"this.className = (this.className=='warn part')?'$bg':'warn part';\">\n";
	}else{
		echo "\t<tr class=\"$bg\" onmouseover=\"this.className='imga'\" onmouseout=\"this.className='$bg'\">\n";
	}
}

//===================================================================
// Generate table cell (Argmuents sorted by relevance)
// Value, Class and Style are always shown, href not in print and XLS
// Image is not shown in XLS
// If image is preceeded by a +, it's shown in print and href will include
// it in normal lists (e.g. device icon in Devices-List)
function TblCell($val="",$href="",$cla="",$img="",$sty=""){

	$pimg = '';
	if( strpos($img,'+') === 0 ){
		$pimg = substr($img,1).' ';
		$img = $pimg;
	}
	if( isset($_GET['xls']) ){
		$cval = $val;
	}else{
		if( isset($_GET['print']) ){
			$cval = "$pimg$val";
		}else{
			$cval = ( $href )?"$img<a href=\"$href\">$val</a>":"$img$val";
		}
	}
	$ccla = ($cla)?" class=\"$cla\"":'';
	if($sty == 'th'){
		$sty = '';
		$ct = 'th';
	}else{
		$ct = 'td';
	}
	$csty = ($sty)?" style=\"$sty\"":'';

	echo "\t\t<$ct$ccla$csty>\n\t\t\t$cval\n\t\t</$ct>\n";
}

//===================================================================
// Generate table footer
// Opt	class, #colulmns, text to display
function TblFoot($class,$cols,$txt){
?>
	<tr class="<?= $class ?>">
		<td colspan="<?= $cols ?>" class="caption">
			<?= $txt ?>

		</td>
	</tr>
</table>
<?php
}

//===================================================================
// Generate coloured bar graph element

function Bar($val,$mode='',$bgclass='lbar',$tit=''){

	global $mbak;

	$stit = $ltit = $bg = '';
	if($bgclass=='sbar'){
		$x    = 4;
		$stit = " title=\"$tit\"";
	}else{
		$x = 16;
		$ltit = $tit;
	}
	if( strpos($mode,'lvl') !== FALSE ){
		$lvl = substr($mode,3);
		$bgclass .= " $mbak[$lvl]";
		$style = '';
	}elseif( preg_match('/^-?[0-9\.]+$/',$mode) ){
		$thr = $mode + 0;
		if($thr > 0){
			if($val < $thr){
				$bgclass .= ' good';
			}elseif($val < 2 * $thr){
				$bgclass .= ' warn';
			}else{
				$bgclass .= ' alrm';
			}
		}else{
			if($val < abs($thr)/2){
				$bgclass .= ' alrm';
			}elseif($val < abs($thr)){
				$bgclass .= ' warn';
			}else{
				$bgclass .= ' good';
			}
		}
	}else{
		$bg = ";background-color: $mode";
	}

	return "<div class=\"$bgclass\" style=\"width:".round(log(round($val)+1)*$x)."px$bg\"$stit>$ltit</div>";
}

//===================================================================
// Return network type
function Nettype($ip,$ip6=""){

	#if ($ip == "0.0.0.0"){$img = "netr";$tit="Default";
	if (preg_match("/^127\.0\.0/",$ip) or preg_match("/^::1/",$ip6) ){$img = "netr";$tit="LocalLoopback";
	}elseif (preg_match("/^192\.168/",$ip)){$img = "nety";$tit="Private 192.168/16";
	}elseif (preg_match("/^10\./",$ip)){$img = "netp";$tit="Private 10/8";
	}elseif (preg_match("/^172\.[1][6-9]/",$ip)){$img = "neto";$tit="Private 172.16/12";
	}elseif (preg_match("/^172\.[2][0-9]/",$ip)){$img = "neto";$tit="Private 172.16/12";
	}elseif (preg_match("/^172\.[3][0-1]/",$ip)){$img = "neto";$tit="Private 172.16/12";

	}elseif (preg_match("/^224\.0\.0/",$ip)){$img = "netb";$tit="Local Multicast-224.0.0/24";
	}elseif (preg_match("/^224\.0\.1/",$ip)){$img = "netb";$tit="Internetwork  Multicast-224.0.1/24";
	}elseif (preg_match("/^(224|233)/",$ip)){$img = "netb";$tit="AD-HOC Multicast-224~233";
	}elseif (preg_match("/^232\./",$ip)){$img = "netb";$tit="Source-specific Multicast-232/8";
	}elseif (preg_match("/^233\./",$ip)){$img = "netb";$tit="GLOP Multicast-233/8";
	}elseif (preg_match("/^234\./",$ip)){$img = "netb";$tit="Unicast-Prefix Multicast-234/8";
	}elseif (preg_match("/^239\./",$ip)){$img = "netb";$tit="Public Multicast-239/8";

	}elseif (preg_match("/^fe80/",$ip6)){$img = "nety";$tit="IPv6 Link Local";
	}elseif (preg_match("/^fc00/",$ip6)){$img = "neto";$tit="IPv6 Unique Local";
	}elseif (preg_match("/^ff01/",$ip6)){$img = "netb";$tit="IPv6 Interface Local Multicast";
	}elseif (preg_match("/^ff02/",$ip6)){$img = "netb";$tit="IPv6 Link Local Multicast";
	}elseif (preg_match("/^2001:0000/",$ip6)){$img = "netp";$tit="IPv6 Teredo";

	}else{$img = "netg";$tit="Public";}

	return array("$img.png",$tit);
}

//===================================================================
// Return Smilie based on name
function Smilie($usr,$t='',$s=0){

	global $stslbl, $cfglbl, $dsclbl, $msglbl;

	$n = strtolower($usr);
	if($n == "statc"){
		return "<img src=\"img/32/conf.png\"".($s?" width=\"20\"":"")." title=\"$cfglbl $stslbl\">";
	}elseif($n == "statd"){
		return "<img src=\"img/32/radr.png\"".($s?" width=\"20\"":"")." title=\"$dsclbl $stslbl\">";
	}elseif($n == "state"){
		return "<img src=\"img/32/bell.png\"".($s?" width=\"20\"":"")." title=\"$msglbl $stslbl\">";
	}elseif($n == "stati"){
		return "<img src=\"img/32/port.png\"".($s?" width=\"20\"":"")." title=\"Interface $stslbl\">";
	}else{
		$si = ( ord($n) + ord(substr($n,1)) + ord(substr($n,-1)) + ord(substr($n,-2)) ) % 99;
		return "<img src=\"img/usr/$si.png\"".($s?" width=\"20\"":"")." title=\"$t\">";
	}
}

//===================================================================
// Return digital numbers (for stacks)
function Digit($n){
	$i = '';
	if($n > 1){
		foreach (str_split($n) as $d){
			$i .= "<img src=\"img/$d.png\" style=\"margin : 0px\">";
		}
	}
	return $i;
}

//===================================================================
// Replace time of a variable in query string
function SkewTime($istr,$var,$days){

	global $sta, $end;

	$s = $days * 86400;
	if($var == "all"){
		$repl = "sta=".urlencode(date("m/d/Y H:i", ($sta + $s)))."&";
		$ostr = preg_replace("/sta=[0-9a-z%\+]+&/i",$repl,$istr);
		$repl = "end=".urlencode(date("m/d/Y H:i",($end + $s)))."&";
		$ostr = preg_replace("/end=[0-9a-z%\+]+(&|$)/i",$repl,$ostr);
	}else{
		$repl = "$var=".urlencode(date("m/d/Y H:i",(${$var} + $s)))."&";
		$ostr = preg_replace("/$var=[0-9a-z%\+]+(&|$)/i",$repl,$istr);
	}

	return $ostr.(strpos($ostr,'sho')?'':'&sho=1');
}

//===================================================================
// Return Hex Address
// echo IP6('fe80::3ee5:a6ff:feca:ea41');
function IP6($addr) {
	return bin2hex( inet_pton($addr) );
}

//===================================================================
// Return formatted timestamp, except if 0
function Ftime($tstamp,$f='timf') {
	if( $tstamp ){
		return date($_SESSION[$f],$tstamp);
	}else {
		return '-';
	}
}

//===================================================================
// Return fileicon
function FileImg($f) {

	global $hislbl,$fillbl,$imglbl,$cfglbl,$cmdlbl,$mlvl;

	$l  = '';
	$ed = 0;
	if(preg_match("/\.(zip|tgz|tbz|tar|gz|7z|bz2|rar)$/i",$f))	{$i = "pkg"; $t = "Archive";}
	elseif(preg_match("/\.(csv)$/i",$f))			{$i = "list";$t = "CSV $fillbl";$l = $f;}
	elseif(preg_match("/\.(def)$/i",$f))			{$i = "geom";$t = "Device Definition";$l = "Other-Defgen.php?so=".urlencode(basename($f,".def"));}
	elseif(preg_match("/\.(log)$/i",$f))			{$i = "log";$t = "$hislbl";}
	elseif(preg_match("/\.(js)$/i",$f))			{$i = "dbmb";$t = "Javascript";$l = $f;}
	elseif(preg_match("/\.(json)$/i",$f))			{$i = "form";$t = "Json";$l = $f;}
	elseif(preg_match("/\.(pdf)$/i",$f))			{$i = "pdf"; $t = "PDF $fillbl";$l = $f;}
	elseif(preg_match("/\.(php)$/i",$f))			{$i = "php"; $t = "PHP Script";}
	elseif(preg_match("/\.(patch)$/i",$f))			{$i = "hlth";$t = "System Patch";}
	elseif(preg_match("/\.(reg)$/i",$f))			{$i = "nwin";$t = "Registry $fillbl";}
	elseif(preg_match("/\.(xml)$/i",$f))			{$i = "dcub";$t = "XML $fillbl";$l = $f;$ed = 1;}
	elseif(preg_match("/\.(bmp|gif|jpg|png|svg)$/i",$f))	{$i = "img";$t = "$imglbl";$l = "javascript:pop('$f','$imglbl')";}
	elseif(preg_match("/\.(txt|text)$/i",$f))		{$i = "abc"; $t = "TXT $fillbl";$l = $f;$ed = 1;}
	elseif(preg_match("/[.-](cfg|conf|config)$/i",$f))	{$i = "conf";$t = "$cfglbl";$ed = 1;}
	elseif(preg_match("/\.(exe)$/i",$f))			{$i = "cog";$t = "$cmdlbl";}
	elseif(preg_match("/\.(htm|html)$/i",$f))		{$i = "dif";$t = "HTML $fillbl";$l = $f;}
	elseif(preg_match("/\.(pcm|raw)$/i",$f))		{$i = "bell";$t = "Ringtone";}
	elseif(preg_match("/\.(msq|psq|sql)$/i",$f))		{$i = "db";$t = "DB Dump";}
	elseif(preg_match("/\.(btm|loads)$/i",$f))		{$i = "nhdd"; $t = "Boot Image";}
	elseif(preg_match("/\.(app|bin|img|sbn|swi|ipe|xos)$/i",$f)){$i = "cbox"; $t = "Binary Image";}
	elseif(preg_match("/\.(cer|crt|crl|spc|stl)$/i",$f)){$i = "lock"; $t = "Cert & Co";}
	else							{$i = "qmrk";$t = $mlvl['30'];}

	if($l){
		return array("<a href=\"$l\"><img src=\"img/16/$i.png\" title=\"$f - $t\"></a>",$ed);
	}else{
		return array("<img src=\"img/16/$i.png\" title=\"$f - $t\">",$ed);
	}
}

//===================================================================
// Return OS icon
function OSImg($s) {

	global $stco;

	if( stripos($s,'Microsoft') !== FALSE or stripos($s,'Windows') !== FALSE ){	return "<img src=\"img/16/nwin.png\" title=\"$s\">";}
	elseif( stripos($s,'debian') !== FALSE ){	return "<img src=\"img/16/ndeb.png\" title=\"$s\">";}
	elseif( stripos($s,'redhat') !== FALSE ){	return "<img src=\"img/16/nred.png\" title=\"$s\">";}
	elseif( stripos($s,'rhel') !== FALSE ){		return "<img src=\"img/16/nred.png\" title=\"$s\">";}
	elseif( stripos($s,'centos') !== FALSE ){	return "<img src=\"img/16/ncos.png\" title=\"$s\">";}
	elseif( stripos($s,'suse') !== FALSE ){		return "<img src=\"img/16/nsus.png\" title=\"$s\">";}
	elseif( stripos($s,'sles') !== FALSE ){		return "<img src=\"img/16/nsus.png\" title=\"$s\">";}
	elseif( stripos($s,'ubuntu') !== FALSE ){	return "<img src=\"img/16/nubu.png\" title=\"$s\">";}
	elseif( stripos($s,'linux') !== FALSE ){	return "<img src=\"img/16/nlin.png\" title=\"$s\">";}
	elseif( stripos($s,'freebsd') !== FALSE ){	return "<img src=\"img/16/nfbs.png\" title=\"$s\">";}
	elseif( stripos($s,'openbsd') !== FALSE ){	return "<img src=\"img/16/nobs.png\" title=\"$s\">";}

	return "<img src=\"img/16/qmrk.png\" title=\"$stco[250] $l\">";
}

//===================================================================
// Return service icon
function SrvImg($p,$s) {

	global $stco;

	if( $p == 'tcp' ){
		if( $s == 22 ){		return "<img src=\"img/16/lokc.png\" title=\"ssh ($s)\">";}
		elseif( $s == 23 ){	return "<img src=\"img/16/loko.png\" title=\"telnet ($s)\">";}
		elseif( $s == 25 ){	return "<img src=\"img/16/mail.png\" title=\"smtp ($s)\">";}
		elseif( $s == 80 ){	return "<img src=\"img/16/glob.png\" title=\"http ($s)\">";}
		elseif( $s == 443 ){	return "<img src=\"img/16/glok.png\" title=\"https ($s)\">";}
		elseif( $s == 445 ){	return "<img src=\"img/16/nwin.png\" title=\"Microsoft-DS ($s)\">";}
		elseif( $s == 3306 ){	return "<img src=\"img/16/db.png\" title=\"mysql ($s)\">";}
	}elseif( $p == 'udp' ){
		if( $s == 137 ){	return "<img src=\"img/16/nwin.png\" title=\"NetBIOS ($s)\">";}
	}

	return "<img src=\"img/16/qmrk.png\" title=\"$stco[250] ($s)\">";
}

//===================================================================
// Return vendor icon
function VendorIcon($m) {

	if (stristr($m,"AASTRA"))				{return  "aas";}
	elseif (stristr($m,"ABIT"))				{return  "abit";}
	elseif (stristr($m,"ACCTON"))				{return  "acc";}
	elseif (stristr($m,"ACER"))				{return  "acr";}
	elseif (stristr($m,"Acme"))             		{return  "acm";}
	elseif (stristr($m,"Acrosser"))				{return  "acs";}
	elseif (stristr($m,"ACTIONTEC"))			{return  "atec";}
	elseif (stristr($m,"ADAPTEC"))				{return  "adt";}
	elseif (stristr($m,"Adder"))				{return  "addr";}
	elseif (stristr($m,"ADVANCED DIGITAL INFORMATION"))	{return  "adi";}
	elseif (stristr($m,"ADVANCED TECHNOLOGY &"))		{return  "adtx";}
	elseif (stristr($m,"ADVANTECH"))			{return  "adv";}
	elseif (stristr($m,"AGILENT"))				{return  "agi";}
	elseif (stristr($m,"Alcatel"))				{return  "alu";}
	elseif (stristr($m,"ALLEN BRAD"))			{return  "ab";}
	elseif (stristr($m,"ALPHA"))				{return  "alp";}
	elseif (stristr($m,"AMBIT"))				{return  "amb";}
	elseif (stristr($m,"Aopen"))				{return  "aop";}
	elseif (stristr($m,"APPLE"))				{return  "apl";}
	elseif (stristr($m,"Arcadyan Technology"))		{return  "arc";}
	elseif (stristr($m,"Aruba Networks"))			{return  "aru";}
	elseif (stristr($m,"ASTARO"))				{return  "ast";}
	elseif (stristr($m,"ASUS"))				{return  "asu";}
	elseif (stristr($m,"AUDIO CODES"))			{return  "aud";}
	elseif (stristr($m,"AVM GmbH"))				{return  "avm";}
	elseif (stristr($m,"AXIS"))				{return  "axis";}
	elseif (stristr($m,"Axotec"))				{return  "axo";}
	elseif (stristr($m,"Azurewave"))			{return  "azu";}
	elseif (stristr($m,"BARRACUDA"))			{return  "bar";}
	elseif (stristr($m,"Belkin International"))		{return  "bel";}
	elseif (stristr($m,"BECKHOFF"))				{return  "bek";}
	elseif (stristr($m,"Billion"))				{return  "bil";}
	elseif (stristr($m,"Bio-logic"))			{return	 "bio";}
	elseif (stristr($m,"B-Link"))				{return  "bli";}
	elseif (stristr($m,"Blue Coat"))			{return  "blu";}
	elseif (stristr($m,"B. R. Electronics"))		{return  "bre";}
	elseif (stristr($m,"BROADCOM"))				{return  "bcm";}
	elseif (stristr($m,"BROCADE"))				{return  "brc";}
	elseif (stristr($m,"BROTHER INDUSTRIES"))		{return  "bro";}
	elseif (stristr($m,"Buffalo"))				{return  "buf";}
	elseif (stristr($m,"CAB GmbH"))				{return  "cab";}
	elseif (stristr($m,"CADMUS"))				{return  "cad";}
	elseif (stristr($m,"CANON"))				{return  "can";}
	elseif (stristr($m,"Cellvision Systems"))		{return  "cev";}
	elseif (stristr($m,"CLEVO CO"))				{return  "clv";}
	elseif (stristr($m,"Cognex"))				{return  "cgx";}
	elseif (stristr($m,"COMPAL"))				{return  "cpl";}
	elseif (stristr($m,"COMPAQ"))				{return  "q";}
	elseif (stristr($m,"Comtech"))				{return  "cth";}
	elseif (stristr($m,"CRAY"))				{return  "cra";}
	elseif (stristr($m,"Data Robotics"))			{return  "dro";}
	elseif (stristr($m,"D-LINK"))				{return  "dli";}
	elseif (stristr($m,"DELL"))				{return  "dell";}
	elseif (stristr($m,"devolo AG"))			{return  "dev";}
	elseif (stristr($m,"DIGITAL EQUIPMENT"))		{return  "dec";}
	elseif (stristr($m,"DOT HILL"))				{return  "dhi";}
	elseif (stristr($m,"Dragonwave"))			{return  "dra";}
	elseif (stristr($m,"DrayTek"))				{return  "dt";}
	elseif (stristr($m,"DZG"))				{return  "dzg";}
	elseif (stristr($m,"EDIMAX"))				{return  "edi";}
	elseif (stristr($m,"EdgeCore Networks"))		{return  "edn";}
	elseif (stristr($m,"EFR Europ"))			{return  "efr";}
	elseif (stristr($m,"EGENERA"))				{return  "egn";}
	elseif (stristr($m,"ELECTRONICS FOR IMAGING"))		{return  "efi";}
	elseif (stristr($m,"Elitegroup"))			{return  "ecs";}
	elseif (stristr($m,"Elgato"))				{return	 "elg";}
	elseif (stristr($m,"EMULEX"))				{return  "emx";}
	elseif (stristr($m,"ENTRADA"))				{return  "ent";}
	elseif (stristr($m,"EPSON"))				{return  "eps";}
	elseif (stristr($m,"EqualLogic"))			{return  "Dell";}
	elseif (stristr($m,"EXIDE"))				{return  "exi";}
	elseif (stristr($m,"F5 Networks"))			{return  "f5";}
	elseif (stristr($m,"FIRST INTERNAT"))			{return  "fic";}
	elseif (stristr($m,"Flextronics"))			{return  "flx";}
	elseif (stristr($m,"Fortinet"))				{return  "for";}
	elseif (stristr($m,"FOUNDRY"))				{return  "fdry";}
	elseif (stristr($m,"FOXCONN"))				{return  "fox";}
	elseif (stristr($m,"Francotyp-Postalia GmbH"))		{return  "fpg";}
	elseif (stristr($m,"Fujian"))				{return  "ste";}
	elseif (stristr($m,"FUJITSU"))				{return  "fs";}
	elseif (stristr($m,"Gamatronic"))			{return	 "gmc";}
	elseif (stristr($m,"GemTek Technology"))		{return  "gmt";}
	elseif (stristr($m,"Genexis BV"))			{return  "gnx";}
	elseif (stristr($m,"GIGA-BYTE"))			{return  "gig";}
	elseif (stristr($m,"GK COMPUTER"))			{return	 "chi";}
	elseif (stristr($m,"GKB"))				{return	 "gkb";}
	elseif (stristr($m,"Go Networks"))			{return	 "gns";}
	elseif (stristr($m,"GOLDSTAR"))				{return	 "gs";}
	elseif (stristr($m,"GOOD WAY"))				{return	 "gway";}
	elseif (stristr($m,"G-PRO COMPUTER"))			{return	 "gpro";}
	elseif (stristr($m,"Hi-flying"))			{return  "hif";}
	elseif (stristr($m,"High Tech Computer"))		{return  "htc";}
	elseif (stristr($m,"Hon Hai Precision"))		{return  "amb";}
	elseif (stristr($m,"HTC"))				{return	 "hcc";}
	elseif (stristr($m,"Huawei"))				{return	 "hwi";}
	elseif (stristr($m,"HUGHES"))				{return  "wsw";}
	elseif (stristr($m,"IBM"))				{return  "ibm";}
	elseif (stristr($m,"Impro"))				{return	 "imp";}
        elseif (stristr($m,"INDIGO"))                           {return  "iv";}
	elseif (stristr($m,"Ingenico"))				{return	 "ing";}
	elseif (stristr($m,"INTEL"))				{return  "int";}
	elseif (stristr($m,"INTERFLEX"))			{return  "intr";}
	elseif (stristr($m,"INTERGRAPH"))			{return  "igr";}
	elseif (stristr($m,"INVENTEC CORPORATION"))		{return  "inv";}
	elseif (stristr($m,"IWILL"))				{return  "iwi";}
	elseif (stristr($m,"KABA"))				{return  "kaba";}
	elseif (stristr($m,"KINGSTON"))				{return  "ktc";}
	elseif (stristr($m,"KYOCERA"))				{return  "kyo";}
	elseif (stristr($m,"LANBit"))				{return	 "lbt";}
	elseif (stristr($m,"LANCOM"))				{return  "lac";}
	elseif (stristr($m,"Landis+Gyr"))			{return  "lgy";}
	elseif (stristr($m,"LANTRONIX"))			{return  "ltx";}
	elseif (stristr($m,"LEXMARK"))				{return  "lex";}
	elseif (stristr($m,"LINKSYS"))				{return  "lsy";}
	elseif (stristr($m,"LG Electronics"))			{return	 "lg";}
	elseif (stristr($m,"LN Srithai"))			{return	 "lg";}
	elseif (stristr($m,"March"))				{return	 "mar";}
	elseif (stristr($m,"MARCONI SPA"))			{return	 "mni";}
	elseif (stristr($m,"Matsushita"))			{return  "mat";}
	elseif (stristr($m,"McAffee"))				{return  "mca";}
	elseif (stristr($m,"MICRO-STAR"))			{return  "msi";}
	elseif (stristr($m,"MICROSENS"))			{return  "mse";}
	elseif (stristr($m,"Microsoft"))			{return  "ms";}
	elseif (stristr($m,"MINOLTA"))				{return  "min";}
	elseif (stristr($m,"MITAC INTERNATIONAL"))		{return  "mit";}
	elseif (stristr($m,"MITEL"))				{return	 "mtl";}
	elseif (stristr($m,"Mobotix"))				{return	 "mob";}
	elseif (stristr($m,"Morpho"))				{return	 "mor";}
	elseif (stristr($m,"MOTOROLA"))				{return  "mot";}
	elseif (stristr($m,"MOXA"))				{return	 "mox";}
	elseif (stristr($m,"MSI"))				{return  "msi";}
	elseif (stristr($m,"Murata Manufact"))			{return  "mur";}
	elseif (stristr($m,"NATIONAL INSTRUMENTS"))		{return  "ni";}
	elseif (stristr($m,"NComputing"))			{return	 "nc";}
	elseif (stristr($m,"NEC"))				{return	 "nec";}
	elseif (stristr($m,"Netapp"))				{return	 "nap";}
	elseif (stristr($m,"NETGEAR"))				{return  "ngr";}
	elseif (stristr($m,"NETWORK COMP"))			{return  "ncd";}
	elseif (stristr($m,"Network Equipment"))		{return	 "net";}
	elseif (stristr($m,"Newisys"))				{return	 "nws";}
	elseif (stristr($m,"Nexans"))				{return	 "nxa";}
	elseif (stristr($m,"Nexcom"))				{return	 "nxm";}
	elseif (stristr($m,"NEXT"))				{return  "nxt";}
	elseif (stristr($m,"Nintendo"))				{return  "nin";}
	elseif (stristr($m,"NOKIA"))				{return  "nok";}
	elseif (stristr($m,"NUCLEAR"))				{return  "atom";}
	elseif (stristr($m,"Other"))				{return  "oth";}
	elseif (stristr($m,"Oracle"))				{return	 "ora";}
	elseif (stristr($m,"OVERLAND"))				{return  "ovl";}
	elseif (stristr($m,"PATTON"))				{return	 "pat";}
	elseif (stristr($m,"PAUL SCHERRER"))			{return  "psi";}
	elseif (stristr($m,"PayTec AG"))			{return  "pay";}
	elseif (stristr($m,"PC Partner"))			{return	 "pcpa";}
	elseif (stristr($m,"PC-PoS"))				{return	 "pcp";}
	elseif (stristr($m,"PEGATRON"))				{return	 "peg";}
	elseif (stristr($m,"Peplink"))				{return	 "pep";}
	elseif (stristr($m,"PHILIPS"))				{return  "plp";}
	elseif (stristr($m,"PLANET"))				{return  "pla";}
	elseif (stristr($m,"POLYCOM"))				{return	 "ply";}
	elseif (stristr($m,"PRIMION"))				{return  "prim";}
	elseif (stristr($m,"PRONET GMBH"))			{return  "eze";}
	elseif (stristr($m,"PROXIM"))				{return  "prx";}
	elseif (stristr($m,"QLogic"))				{return  "qlo";}
	elseif (stristr($m,"QNAP"))				{return  "Qnap";}
	elseif (stristr($m,"QUANTA"))				{return  "qnt";}
	elseif (stristr($m,"RAD DATA"))				{return  "rad";}
	elseif (stristr($m,"RADWIN"))				{return	 "rwn";}
	elseif (stristr($m,"RARITAN"))				{return  "rar";}
	elseif (stristr($m,"Raspberry Pi"))			{return  "rpi";}
	elseif (stristr($m,"REALTEK"))				{return  "rtk";}
	elseif (stristr($m,"RICOH"))				{return  "rco";}
	elseif (stristr($m,"Riverbed"))				{return	 "riv";}
	elseif (stristr($m,"Rockwell"))				{return	 "ra";}
	elseif (stristr($m,"Routerboard.com"))			{return	 "rbd";}
	elseif (stristr($m,"RUBY TECH"))			{return  "rub";}
	elseif (stristr($m,"Ruckus"))				{return  "ruk";}
	elseif (stristr($m,"SAMSUNG"))				{return	 "sam";}
	elseif (stristr($m,"SHARP"))				{return	 "sharp";}
	elseif (stristr($m,"Sena"))				{return	 "sna";}
	elseif (stristr($m,"SERCOM"))				{return  "ser";}
	elseif (stristr($m,"SHIVA"))				{return  "sva";}
	elseif (stristr($m,"SHUTTLE"))				{return  "shu";}
	elseif (stristr($m,"Slim"))				{return  "slim";}
	elseif (stristr($m,"SIAE"))				{return	 "sia";}
	elseif (stristr($m,"SIEMENS"))				{return  "si";}
	elseif (stristr($m,"SILICON GRAPHICS"))			{return  "sgi";}
	elseif (stristr($m,"SMARTBRIDGES"))			{return	 "sbr";}
	elseif (stristr($m,"SNOM"))				{return  "Snom";}
	elseif (stristr($m,"SonicWALL"))			{return	 "swl";}
	elseif (stristr($m,"Sonos, "))				{return	 "sns";}
	elseif (stristr($m,"Sony Computer Entertainment"))	{return  "sps";}
	elseif (stristr($m,"Sony Ericsson"))			{return  "se";}
	elseif (stristr($m,"SONY"))				{return  "sony";}
	elseif (stristr($m,"STRATUS"))				{return  "sts";}
	elseif (stristr($m,"SUN MICROSYSTEMS"))			{return  "sun";}
	elseif (stristr($m,"SYMBOL"))				{return	 "sym";}
	elseif (stristr($m,"Synology"))				{return	 "syn";}
	elseif (stristr($m,"TECO INFORMATION "))		{return  "tec";}
	elseif (stristr($m,"TEKTRONIX"))			{return  "tek";}
	elseif (stristr($m,"Tilgin"))				{return	 "til";}
	elseif (stristr($m,"TiVo"))				{return  "tiv";}
        elseif (stristr($m,"TOKYO"))                            {return  "tok";}
	elseif (stristr($m,"TOSHIBA"))				{return  "tsa";}
	elseif (stristr($m,"TP-LINK"))				{return	 "tpl";}
	elseif (stristr($m,"TRENDnet"))				{return	 "tn";}
	elseif (stristr($m,"TTi LTD"))				{return	 "tti";}
	elseif (stristr($m,"TYAN"))				{return  "tya";}
	elseif (stristr($m,"U.S. Robotics"))			{return  "usr";}
	elseif (stristr($m,"Ubiquiti"))				{return	 "ubi";}
	elseif (stristr($m,"Universal Devices Inc."))		{return  "udi";}
	elseif (stristr($m,"Universal Global Scientific"))	{return	 "ugs";}
	elseif (stristr($m,"Unify"))				{return  "ufy";}
	elseif (stristr($m,"USC CORPORATION"))			{return  "usc";}
	elseif (stristr($m,"USI"))				{return  "usi";}
	elseif (stristr($m,"UTSTARCOM"))			{return	 "uts";}
	elseif (stristr($m,"Vanguard"))				{return	 "vgd";}
	elseif (stristr($m,"Vivotek"))				{return	 "viv";}
	elseif (stristr($m,"VMWARE"))				{return  "vmw";}
	elseif (stristr($m,"VXL"))				{return  "vxl";}
	elseif (stristr($m,"WESTERN"))				{return  "wdc";}
	elseif (stristr($m,"WIESEMANN & THEIS"))		{return  "wt";}
	elseif (stristr($m,"WISTRON"))				{return  "wis";}
	elseif (stristr($m,"WW PCBA"))				{return  "de";}
	elseif (stristr($m,"WYSE"))				{return  "wys";}
	elseif (stristr($m,"XAVi"))				{return	 "xvi";}
	elseif (stristr($m,"Xensource"))			{return	 "cit";}
	elseif (stristr($m,"XEROX"))				{return  "xrx";}
        elseif (stristr($m,"XIAMEN"))                           {return  "yea";}
	elseif (stristr($m,"XYLAN"))				{return  "xylan";}
	elseif (stristr($m,"Zebra Technologies"))		{return	 "zeb";}
	elseif (stristr($m,"Zenitel Norway"))			{return	 "zen";}
	elseif (stristr($m,"ZTE"))				{return	 "zte";}

	elseif (preg_match("/ASRock|Asiarock/i",$m))		{return  "asr";}
	elseif (preg_match("/AIRONET|CISCO/i",$m))		{return  "cis";}
	elseif (preg_match("/APC|AMERICAN POWER/i",$m))		{return  "apc";}
	elseif (preg_match("/AVAYA|LANNET|BAY|NORTEL|NETICS|XYLOGICS/i",$m)){return  "ava";}
	elseif (preg_match("/BAY|NORTEL|NETICS|XYLOGICS/i",$m))	{return  "nort";}
	elseif (preg_match("/EMC|CLARIION/i",$m))		{return  "emc";}
	elseif (preg_match("/Enterasys|EXTREME/i",$m))		{return  "ext";}
	elseif (stristr($m,"Emerson Network|ROSEMOUNT CONTROLS"))	{return  "eme";}
	elseif (preg_match("/Floware|ALVARION|Breeze/i",$m))	{return	 "aln";}
	elseif (preg_match("/^(Funkwerk|Bintec|Artem|Teldat)/i",$m))	{return	 "aln";}
	elseif (preg_match("/HEWLETT|ProCurve|Colubris|Hangzhou|Palm,|3 par|3\s*COM|MEGAHERTZ|H3C|HPN Supply Chain/i",$m)){return  "hp";}
	elseif (preg_match("/JUNIPER|PERIBIT|Netscreen/i",$m))  {return  "jun";}
	elseif (preg_match("/LITE-ON|Liteon/i",$m))		{return	 "lio";}
	elseif (preg_match("/MGE UPS|Schneider/i",$m))		{return	 "sce";}
	elseif (preg_match("/Mellanox|Voltaire/i",$m))		{return	 "mel";}
	elseif (preg_match("/^RIM$|Research In Motion/",$m))	{return  "rim";}
	elseif (preg_match("/SMC Net|STANDARD MICROSYS/i",$m))	{return  "smc";}
	elseif (preg_match("/SUPER(\s)?MICRO/i",$m))		{return  "sum";}
	elseif (preg_match("/VIA( NETWORKING)? TECHNOLOGIES/i",$m))	{return  "via";}
	elseif (preg_match("/ZYXEL|ZyGate/i",$m))		{return  "zyx";}
	else							{return  "Unkown";}
}

?>
