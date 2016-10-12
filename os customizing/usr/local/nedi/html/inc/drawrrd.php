<?php
#============================================================================
# Program: drawrrd.php
# use GET option debug=on to debug output, if you encounter problems!
#============================================================================

session_start();
$nedipath = preg_replace( "/^(\/.+)\/html\/.+.php/","$1",$_SERVER['SCRIPT_FILENAME']);			# Guess NeDi path for nedi.conf

include_once ("libmisc.php");
ReadConf('nomenu');

if( !$rrdcmd or !isset($_SESSION['group']) ) die;

date_default_timezone_set($_SESSION['tz']);

include_once ("librrd.php");

$_GET = sanitize($_GET);
$typ = isset($_GET['t']) ? $_GET['t'] : "";
$gsz = isset($_GET['s']) ? $_GET['s'] : "";
$sta = isset($_GET['a']) ? $_GET['a'] : "";
$end = isset($_GET['e']) ? $_GET['e'] : "";
$opt = isset($_GET['o']) ? $_GET['o'] : "";

$debug = isset($_GET['debug']) ? $_GET['debug'] : "";

include_once ("../languages/$_SESSION[lang]/gui.php");

$draw	= "";
$outdir = "";
$outmod = "LINE2";
if( $_SESSION['gneg'] ){
	$outdir = "-";
	$outmod = "AREA";
}
if($typ == 'cpu'){
	$tit = "$lodlbl";
	$rrd = "$nedipath/rrd/" . rawurlencode($_GET['dv']) ."/system.rrd";
	$draw .= "DEF:cpu=$rrd:cpu:AVERAGE AREA:cpu#cc8844 ";
	$draw .= "CDEF:cpu2=cpu,1.3,/ AREA:cpu2#dd9955 ";
	$draw .= "CDEF:cpu3=cpu,2,/ AREA:cpu3#eeaa66 ";
	$draw .= "LINE2:cpu#884400:\"%\" ";
}elseif($typ == 'mem'){
	$tit = "$manlbl Memory";
	$rrd = "$nedipath/rrd/" . rawurlencode($_GET['dv']) ."/system.rrd";
	$draw .= "DEF:memcpu=$rrd:memcpu:AVERAGE AREA:memcpu#bbbb66 ";
	$draw .= "CDEF:memcpu2=memcpu,1.3,/ AREA:memcpu2#cccc77 ";
	$draw .= "CDEF:memcpu3=memcpu,2,/ AREA:memcpu3#dddd88 ";
	$draw .= "LINE2:memcpu#666600:\"Bytes/% $frelbl\" ";
}elseif($typ == 'tmp'){
	$tit = "$tmplbl";
	$rrd = "$nedipath/rrd/" . rawurlencode($_GET['dv']) ."/system.rrd";
	$draw .= "DEF:temp=$rrd:temp:AVERAGE AREA:temp#6688bb  ";
	$draw .= "CDEF:temp2=temp,1.3,/ AREA:temp2#7799cc ";
	$draw .= "CDEF:temp3=temp,2,/ AREA:temp3#88aadd ";
	$draw .= "LINE2:temp#004488:\"$grdlbl Celsius\" ";
	if ($_SESSION['far']){$draw .= "CDEF:far=temp,1.8,*,32,+ LINE2:far#006699:\"$grdlbl Fahrenheit\" ";}
}elseif($typ == 'cuv'){
	$tit = ($_GET['if'][0])?$_GET['if'][0]:"IO Memory";
	$u   = ($_GET['if'][1])?$_GET['if'][1]:"Bytes $frelbl";
	$cuds= preg_replace('/[^a-zA-Z0-9]/', '', strtolower($tit) );
	$rrd = "$nedipath/rrd/" . rawurlencode($_GET['dv']) ."/system.rrd";
	$draw .= "DEF:$cuds=$rrd:$cuds:AVERAGE AREA:$cuds#88bb66 ";
	$draw .= "CDEF:${cuds}2=$cuds,1.5,/ AREA:${cuds}2#99cc77 ";
	$draw .= "CDEF:${cuds}3=$cuds,2,/ AREA:${cuds}3#aadd88 ";
	$draw .= "LINE2:$cuds#448800:\"$u\" ";
}elseif($typ == 'ttr'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$totlbl $acslbl $trflbl";
	if ($_SESSION['gbit']){
		$unit = 'bit';
		$draw .= "DEF:tino=$rrd:tinoct:AVERAGE ";
		$draw .= "CDEF:tinoct=tino,8,* AREA:tinoct#0088cc:\"In Gbit/s \" ";
		$draw .= "DEF:toto=$rrd:totoct:AVERAGE ";
		$draw .= "CDEF:totoct=toto,${outdir}8,* $outmod:totoct#000088:\"Out Gbit/s\\n \" ";
	}else{
		$unit = 'byte';
		$draw .= "DEF:tinoct=$rrd:tinoct:AVERAGE AREA:tinoct#0088cc:\"In Gbyte/s            \" ";
		$draw .= "DEF:toto=$rrd:totoct:AVERAGE ";
		$draw .= "CDEF:totoct=toto,${outdir}1,* $outmod:totoct#000088:\"Out Gbyte/s\\n\" ";
	}
	if(!$_SESSION['gneg']){
		$draw .= "VDEF:tio95=tinoct,95,PERCENT LINE1:tio95#eeaa44:\"In 95%\" GPRINT:tio95:\"%4.1lf%S G$unit/s\" ";
		$draw .= "VDEF:too95=totoct,95,PERCENT LINE1:too95#ee4444:\"Out 95%\" GPRINT:too95:\"%4.1lf%S G$unit/s\l\"";
	}
}elseif($typ == 'ter'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$totlbl $errlbl";
	$draw .= "DEF:tinerr=$rrd:tinerr:AVERAGE AREA:tinerr#aa0000:\"In #/s\" ";
	$draw .= "DEF:outgr=$rrd:toterr:AVERAGE ";
	$draw .= "CDEF:toterr=outgr,${outdir}1,* $outmod:toterr#aa8800:\" Out #/s\l\" ";
}elseif($typ == 'tdi'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$totlbl Discards";
	$draw .= "DEF:tindis=$rrd:tindis:AVERAGE AREA:tindis#8844aa:\"In #/s\" ";
	$draw .= "DEF:outgr=$rrd:totdis:AVERAGE ";
	$draw .= "CDEF:totdis=outgr,${outdir}1,* $outmod:totdis#662288:\" Out #/s\l\" ";
}elseif($typ == 'nod'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$totlbl Nodes";
	$draw .= "DEF:nodls=$rrd:nodls:AVERAGE AREA:nodls#aaaaaa ";
	$draw .= "DEF:nodfs=$rrd:nodfs:AVERAGE AREA:nodfs#44aa00:\"$fislbl\" ";
	$draw .= "LINE2:nodls#666666:\"$laslbl\l\" ";
}elseif($typ == 'tpw'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$totlbl PoE";
	$draw .= "DEF:tpoe=$rrd:tpoe:AVERAGE AREA:tpoe#bbaa00 ";
	$draw .= "CDEF:tpoe2=tpoe,1.1,/ AREA:tpoe2#ccbb00 ";
	$draw .= "CDEF:tpoe3=tpoe,1.2,/ AREA:tpoe3#ddcc00 ";
	$draw .= "CDEF:tpoe4=tpoe,1.3,/ AREA:tpoe4#eedd00 ";
	$draw .= "LINE2:tpoe#886600:\"Watt\" ";
}elseif($typ == 'ifs'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "IF $sumlbl";
	$draw .= "DEF:disif=$rrd:disif:AVERAGE AREA:disif#cc8844:\"Admin Down\" ";
	$draw .= "DEF:downif=$rrd:downif:AVERAGE STACK:downif#cccc44:\"Link down\" ";
	$draw .= "DEF:upif=$rrd:upif:AVERAGE STACK:upif#44cc44:\"Link up\l\" ";
}elseif($typ == 'mon'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$tgtlbl $avalbl";
	$draw .= "DEF:monok=$rrd:monok:AVERAGE AREA:monok#008844:\"Ok\" ";
	$draw .= "DEF:monsl=$rrd:monsl:AVERAGE STACK:monsl#ccaa00:\"Slow\" ";
	$draw .= "DEF:monal=$rrd:monal:AVERAGE STACK:monal#884400:\"Down\l\" ";
}elseif($typ == 'msg'){
	$rrd = "$nedipath/rrd/top.rrd";
	$tit = "$msglbl / " .round($rrdstep/60)."m";
	$draw .= "DEF:msg50=$rrd:msg50:AVERAGE AREA:msg50#44cc44:\"".substr($mlvl[50],0,4)."\" ";
	$draw .= "DEF:msg100=$rrd:msg100:AVERAGE STACK:msg100#4444cc:\"".substr($mlvl[100],0,4)."\" ";
	$draw .= "DEF:msg150=$rrd:msg150:AVERAGE STACK:msg150#cccc44:\"".substr($mlvl[150],0,4)."\" ";
	$draw .= "DEF:msg200=$rrd:msg200:AVERAGE STACK:msg200#cc8844:\"".substr($mlvl[200],0,4)."\" ";
	$draw .= "DEF:msg250=$rrd:msg250:AVERAGE STACK:msg250#cc4444:\"".substr($mlvl[250],0,4)."\l\" ";
}elseif($typ == 'trf' or $typ == 'err' or $typ == 'brc' or $typ == 'dsc' or $typ == 'sta'){
	foreach ($_GET['if'] as $i){
		$rrd[$i] = "$nedipath/rrd/" . rawurlencode($_GET['dv']) . "/" . rawurlencode($i) . ".rrd";			# rawurlencode for valid filenames!
	}
	list($draw,$tit) = GraphTraffic($rrd,$typ);
}
$opts = GraphOpts($gsz,$sta,$end,$tit,$opt);

if($debug){
	echo "<pre>$rrdcmd graph - -a PNG $opts\n\t$draw</pre>";
}else{
	header("Content-type: image/png");
	passthru("$rrdcmd graph - -a PNG $opts $draw", $stat);
	if( $stat ){
		$image = Imagecreate(100, 40);
		$grey  = ImageColorAllocate($image, 50, 50, 50);
		$red   = ImageColorAllocate($image, 200, 150, 0);
		ImageString($image, 4, 2, 12, "RRD $errlbl", $red);
		Imagepng($image);
		Imagedestroy($image);
	}
}

?>
