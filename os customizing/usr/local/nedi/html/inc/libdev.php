<?PHP

//===================================================================
// Device related functions
//===================================================================

//===================================================================
// Return Sys Services
function Syssrv($sv){

	$srv = "";

	if ($sv &  1) {$srv = " Repeater"; }
	if ($sv &  2) {$srv = "$srv Bridge"; }
	if ($sv &  4) {$srv = "$srv Router"; }
	if ($sv &  8) {$srv = "$srv Gateway"; }
	if ($sv & 16) {$srv = "$srv Session"; }
	if ($sv & 32) {$srv = "$srv Terminal"; }				# VoIP phones are kind of a terminal too...
	if ($sv & 64) {$srv = "$srv Application"; }
	if (!$sv)     {$srv = "-"; }

	return $srv;
}

//===================================================================
// Return Module/Physical Class
function ModClass($cl){

	global $mlvl, $nonlbl, $stco;

	if 	($cl == 1) {return array($mlvl['30'],"ugrp");}
	elseif	($cl == 2) {return array($stco['250'],"qmrk");}
	elseif	($cl == 3) {return array("Chassis","dev");}
	elseif	($cl == 4) {return array("Backplane","card");}
	elseif	($cl == 5) {return array("Container","pkg");}
	elseif	($cl == 6) {return array("Power Supply","psup");}
	elseif	($cl == 7) {return array("Fan","fan");}
	elseif	($cl == 8) {return array("Sensor","radr");}
	elseif	($cl == 9) {return array("Module","pcm");}
	elseif	($cl == 10){return array("Port","port");}
	elseif	($cl == 11){return array("Stack","db");}
	elseif	($cl == 18){return array("Keypad","calc");}
	elseif	($cl == 19){return array("Camera","cam");}

	elseif	($cl == 20){return array("Patchpanel","link");}
	elseif	($cl == 21){return array("Cover","kons");}

	elseif	($cl == 30){return array("Printsupply","file");}
	elseif	($cl == 40){return array("Virtual Machine","node");}
	elseif	($cl == 50){return array("Controlled AP","wlan");}

	elseif	($cl == 60){return array("Server","nhdd");}
	elseif	($cl == 61){return array("CPU","cpu");}
	elseif	($cl == 62){return array("Mem","mem");}
	elseif	($cl == 63){return array("HDD","hdd");}
	elseif	($cl == 64){return array("Card","card");}
	elseif	($cl == 69){return array("Display","disp");}

	elseif	($cl == 80){return array("OS","cog");}
	elseif	($cl == 81){return array("Software","cbox");}
	elseif	($cl == 82){return array("License","glok");}

	else	{return array("?","find");}
}

//===================================================================
// Return Device category based on icon
function DevCat($i){

	global $mlvl,$siz;

	if( preg_match('/^rs/',$i) ){
		return "Router $siz[s]";
	}elseif( preg_match('/^rm/',$i) ){
		return "Router $siz[m]";
	}elseif( preg_match('/^rl/',$i) ){
		return "Router $siz[l]";
	}elseif( preg_match('/^w2/',$i) ){
		return "Workgroup L2 Switch";
	}elseif( preg_match('/^w3/',$i) ){
		return "Workgroup L3 Switch";
	}elseif( preg_match('/^c2/',$i) ){
		return "Chassis L2 Switch";
	}elseif( preg_match('/^c3/',$i) ){
		return "Chassis L3 Switch";
	}elseif( preg_match('/^fv/',$i) ){
		return "Virtual FW";
	}elseif( preg_match('/^fw/',$i) ){
		return "Firewall";
	}elseif( preg_match('/^vp/',$i) ){
		return "VPN FW";
	}elseif( preg_match('/^ap/',$i) ){
		return "Appliance";
	}elseif( preg_match('/^cs/',$i) ){
		return "Contentswitch";
	}elseif( preg_match('/^lb/',$i) ){
		return "Loadbalancer";
	}elseif( preg_match('/^ic/',$i) ){
		return "IP Camera";
	}elseif( preg_match('/^iv/',$i) ){
		return "Video Conferencing";
	}elseif( preg_match('/^bs/',$i) ){
		return "Bladeserver Chassis";
	}elseif( preg_match('/^sv/',$i) ){
		return "Server";
	}elseif( preg_match('/^ph/',$i) ){
		return "IP Phone";
	}elseif( preg_match('/^at/',$i) ){
		return "Voice Adapter";
	}elseif( preg_match('/^up/',$i) ){
		return "UPS";
	}elseif( preg_match('/^pg/',$i) ){
		return "B&W Printer";
	}elseif( preg_match('/^pc/',$i) ){
		return "Color Printer";
	}elseif( preg_match('/^hv/',$i) ){
		return "Hypervisor";
	}elseif( preg_match('/^vs/',$i) ){
		return "Virtual Switch";
	}elseif( preg_match('/^hv/',$i) ){
		return "Hypervisor";
	}elseif( preg_match('/^fc/',$i) ){
		return "Fibrechannel Switch";
	}elseif( preg_match('/^st/',$i) ){
		return "Storage";
	}elseif( preg_match('/^wc/',$i) ){
		return "Wireless Controller";
	}elseif( preg_match('/^wa/',$i) ){
		return "Wireless AP";
	}elseif( preg_match('/^wb/',$i) ){
		return "Wireless Bridge";
	}else{
		return $mlvl['30'];
	}
}

//===================================================================
// Return Device mode (VTP mode for Cisco switches)
function DevMode($m){

	global $errlbl,$notlbl,$usrlbl,$addlbl;

	if 	($m == 0)	{ return "-"; }
	elseif	($m == 1)	{ return "VTP Client"; }
	elseif	($m == 2)	{ return "VTP Server"; }
	elseif	($m == 3)	{ return "Transparent"; }
	elseif	($m == 4)	{ return "Off"; }
	elseif	($m == 5)	{ return "SNMP $errlbl"; }
	elseif	($m == 6)	{ return "Bridge"; }							# bridge-fwd in .def
	elseif	($m == 8)	{ return "Controlled AP"; }
	elseif	($m == 9)	{ return "$usrlbl $addlbl"; }
	elseif 	($m == 10)	{ return "$notlbl SNMP"; }
	elseif	($m == 11)	{ return "VoIP Phone"; }
	elseif	($m == 12)	{ return "VoIP Box"; }
	elseif	($m == 15)	{ return "Wlan AP"; }
	elseif	($m == 17)	{ return "Wlan Bridge"; }
	elseif	($m == 20)	{ return "Video Camera"; }
	elseif	($m == 21)	{ return "Video Conference"; }
	elseif	($m == 30)	{ return "Virtual Bridge"; }
	elseif	($m == 40)	{ return "WMI Host"; }
	else			{ return $m; }
}

//===================================================================
// Return linktype icon
function LtypIcon($lt,$s=16){

	if( strpos($lt,'F',3) ){
		return "img/$s/sms.png";
	}elseif( strpos($lt,'A',3) ){
		return "img/$s/wlan.png";
	}elseif( strpos($lt,'C',3) ){
		return "img/$s/wlab.png";
	}elseif( strpos($lt,'V',3) ){
		return "img/$s/cam.png";
	}elseif( strpos($lt,'T',3) ){
		return "img/$s/disp.png";
	}elseif( strpos($lt,'H',3) ){
		return "img/$s/nhdd.png";
	}elseif( strpos($lt,'P',3) ){
		return "img/$s/print.png";
	}elseif( strpos($lt,'U',3) ){
		return "img/$s/batt.png";
	}elseif( strpos($lt,'N',3) ){
		return "img/$s/node.png";
	}elseif( strpos($lt,'W',3) ){
		return "img/$s/nwin.png";
	}else{
		return "img/$s/dev.png";
	}
}

//===================================================================
// Set map dimensions based on user's graph-size setting
function MapSize( $gs ){

	if( $gs == 4 ){
		return array(240,160);
	}elseif( $gs == 3 ){
		return array(180,120);
	}else{
		return array(120,80);
	}
}

//===================================================================
// Return city image
function CtyImg($nd){

	if($nd > 499){
		return "cityx";
	}elseif($nd > 99){
		return "cityl";
	}elseif($nd > 9){
		return "citym";
	}else{
		return "citys";
	}
}

//===================================================================
// Returns link for CLI access based on IP and port
function DevCli($ip,$p,$t=0){

	global $nipl;

	if(!$ip or $ip == "0.0.0.0" or !$p or $nipl){
		if($t != 2){
			return "$ip";
		}
	}else{
		if($p == 22){
			return "<a href=\"ssh://$ip\">".(($t)?"<img src=\"img/16/lokc.png\"  title=\"SSH $ip\">":$ip)."</a>";
		}elseif($p == 23){
			return "<a href=\"telnet://$ip\">".(($t)?"<img src=\"img/16/loko.png\" title=\"Telnet  $ip\">":$ip)."</a>";
		}else{
			return "<a href=\"telnet://$ip:$p\">".(($t)?"<img src=\"img/16/loko.png\" title=\"Telnet $ip Port $p\">":$ip)."</a>";
		}
	}
}

//===================================================================
// Return building image
function BldImg($nd,$na){

	global $redbuild;

	if( preg_match("/$redbuild/",$na) ){
		$bc = "r";
	}else{
		$bc = "";
	}
	if($nd > 19){
		return "bldh$bc";
	}elseif($nd > 9){
		return "bldb$bc";
	}elseif($nd > 2){
		return "bldm$bc";
	}else{
		return "blds$bc";
	}
}

//===================================================================
// Return IANAifType
function Iftype($it){

	if ($it == "5"){$img = "tel";$tit="rfc877x25";
	}elseif ($it == "6"){$img = "p45";$tit="Ethernet";
	}elseif ($it == "7"){$img = "p45";$tit="iso88023Csmacd";
	}elseif ($it == "18"){$img = "tel";$tit="ds1";
	}elseif ($it == "19"){$img = "tel";$tit="E1";
	}elseif ($it == "20"){$img = "tel";$tit="basicISDN";
	}elseif ($it == "22"){$img = "ppp";$tit="Point to Point Serial";
	}elseif ($it == "23"){$img = "ppp";$tit="PPP";
	}elseif ($it == "24"){$img = "tape";$tit="Software Loopback";
	}elseif ($it == "28"){$img = "ppp";$tit="slip";
	}elseif ($it == "32"){$img = "ppp";$tit="Frame Relay DTE only";
	}elseif ($it == "33"){$img = "plug";$tit="rs232";
	}elseif ($it == "37"){$img = "ppp";$tit="atm";
	}elseif ($it == "39"){$img = "fibr";$tit="sonet";
	}elseif ($it == "44"){$img = "plug";$tit="Frame Relay Service";
	}elseif ($it == "49"){$img = "netr";$tit="AAL5 over ATM";
	}elseif ($it == "50"){$img = "fibr";$tit="sonetPath";
	}elseif ($it == "51"){$img = "fibr";$tit="sonetVT";
	}elseif ($it == "53"){$img = "chip";$tit="Virtual Interface";
	}elseif ($it == "54"){$img = "mux";$tit="propMultiplexor";
	}elseif ($it == "56"){$img = "fibr";$tit="fibreChannel";
	}elseif ($it == "58"){$img = "cell";$tit="frameRelayInterconnect";
	}elseif ($it == "63"){$img = "tel";$tit="isdn";
	}elseif ($it == "71"){$img = "ant";$tit="radio spread spectrum";
	}elseif ($it == "75"){$img = "tel";$tit="ISDN S/T interface";
	}elseif ($it == "76"){$img = "tel";$tit="ISDN U interface";
	}elseif ($it == "77"){$img = "plug";$tit="lapd";
	}elseif ($it == "81"){$img = "tel";$tit="Digital Signal Level 0";
	}elseif ($it == "94"){$img = "plug";$tit="Asymmetric Digital Subscriber Loop";
	}elseif ($it == "96"){$img = "plug";$tit="Symmetric Digital Subscriber Loop";
	}elseif ($it == "97"){$img = "plug";$tit="Very H-Speed Digital Subscrib. Loop";
	}elseif ($it == "101"){$img = "tel";$tit="voice Foreign Exchange Office";
	}elseif ($it == "102"){$img = "tel";$tit="voice Foreign Exchange Station";
	}elseif ($it == "103"){$img = "tel";$tit="voice encapsulation";
	}elseif ($it == "104"){$img = "tel";$tit="voice over IP encapsulation";
	}elseif ($it == "117"){$img = "p45";$tit="Gigabit Ethernet";
	}elseif ($it == "131"){$img = "tun";$tit="Encapsulation Interface";
	}elseif ($it == "134"){$img = "cell";$tit="ATM Sub Interface";
	}elseif ($it == "135"){$img = "chip";$tit="Layer 2 Virtual LAN";
	}elseif ($it == "136"){$img = "chip";$tit="Layer 3 IP Virtual LAN";
	}elseif ($it == "150"){$img = "tun";$tit="MPLS Tunnel Virtual Interface";
	}elseif ($it == "161"){$img = "lag";$tit="IEEE 802.3ad Link Aggregate";
	}elseif ($it == "166"){$img = "mpls";$tit="MPLS";
	}elseif ($it == "169"){$img = "plug";$tit="Multirate HDSL2";
	}elseif ($it == "171"){$img = "cell";$tit="Packet over SONET/SDH Interface";
	}elseif ($it == "196"){$img = "bulba";$tit="Optical Transport";
	}elseif ($it == "209"){$img = "bri";$tit="Transparent bridge Interface";
	}elseif ($it == "230"){$img = "bri";$tit="Asymmetric Digital Subscriber Loop Version 2";
	}elseif ($it == "231"){$img = "bri";$tit="MACSecControlled";
	}elseif ($it == "232"){$img = "bri";$tit="MACSecUncontrolled";
	}elseif ($it == "238"){$img = "bri";$tit="Asymmetric Digital Subscriber Loop Version 2 Plus";
	}elseif ($it == "244"){$img = "ppp";$tit="3GPP2 WWAN";
	}elseif ($it == "251"){$img = "plug";$tit="Very high speed digital subscriber line Version 2";
	}elseif ($it == "258"){$img = "chip";$tit="VMware Virtual Network Interface";
	}else{$img = "qg";$tit="Other-$it";}

	return array("$img.png",$tit);
}

//===================================================================
// Return IF status for DB value:
// bit2=oper	bit1=admin
// bit4=PoE	bit8=disabled by NeDi
function Ifdbstat($s){

	global $stco,$dsalbl;

	$poe = ($s & 32) ?", PoE $dsalbl":'';
	$dis = ($s & 64) ?", $dsalbl by NeDi":'';
	$opt = ($poe or $dis)?' part':'';
	if( $s == 128 ){										# 128 is not polled/unknown
		return array("imga",$stco['250'].$poe.$dis);
	}elseif( ($s & 3) == 3 ){
		return array("good$opt","Link up/Admin up$poe$dis");
	}elseif( $s & 1 ){
		return array("warn$opt","Link down/Admin up$poe$dis");
	}elseif( $s & 2 ){
		return array("noti$opt","Link up/Admin down?$poe$dis");
	}else{
		return array("alrm$opt","Link down/Admin down$poe$dis");
	}
}

//===================================================================
// Return Module status 

function ModStat($s,$b,$m=0){

	global $stco,$notlbl,$supa;

	if( $m == 30 ){
		if( $s >= $supa){									# class=30 printsupply
			return array('good',", $s%");
		}elseif( $s < $supa){
			return array('alrm',", $s%");
		}
	}elseif( !$s or $s == 128 ){									# status of 128 means unknown
		return array($b,'');
	}elseif( $s == 3 ){
		return array('good',", $stco[100]");
	}else{
		return array('warn',", $notlbl $stco[100]");
	}
}

//===================================================================
// Generate location string for DB query. Now supporting sub-buildings
// like Ricklicollege_Campus1
function TopoLoc($reg="",$cty="",$bld="",$flr="",$rom=""){

	global $locsep;

	if($rom){
		return "$reg$locsep$cty$locsep$bld$locsep$flr$locsep$rom$locsep%";
	}elseif($bld){
		$b = explode('_', $bld);
		return "$reg$locsep$cty$locsep$b[0]%";
	}elseif($cty){
		return "$reg$locsep$cty$locsep%";
	}elseif($reg){
		return "$reg$locsep%";
	}
}

//===================================================================
// Find best map using a nice recursive function
function TopoMap($reg="",$cty=""){

	global $sub,$debug;

	$cp = '';
	$rp = '';
	$p = ($sub)?"../topo":"topo";
	if($reg){
		if($cty){
			$cp = preg_replace('/\W/','', $reg).'/'.preg_replace('/\W/','', $cty);
			if (file_exists("$p/$cp/background.jpg")) {
				$mapbg = "$cp/background.jpg";
			}else{
				$mapbg = TopoMap($reg);
			}
		}else{
			$rp = preg_replace('/\W/','', $reg);
			if (file_exists("$p/$rp/background.jpg")) {
				$mapbg = "$rp/background.jpg";
			}
		}
	}
	if(!$mapbg) $mapbg = "background.jpg";
	if($debug){echo "<div class=\"textpad imga\">Mapbg:Sub=$sub Path=$p $rp $cp BG=$mapbg</div>\n";}
	return $mapbg;
}

//===================================================================
// Returns a device panel according to type or icon and size
function DevPanel($t,$i,$s=1){

	global $sub,$debug;

	$p = ($sub)?"../img/panel":"img/panel";

	if($debug){echo "<div class=\"textpad xl imga\">Panel:$sub Path=$p Type=$t Icon=$i</div>\n";}

	if( $t and file_exists("$p/$t.jpg") ){
		return "img/panel/$t.jpg";
	}elseif( preg_match('/^wa/',$i) ){
		return "img/panel/gen-ap.jpg";
	}elseif( strpos($i,'ph') === 0 ){
		return "img/panel/gen-phone.jpg";
	}elseif( strpos($i,'st') === 0 ){
		return "img/panel/gen-srv2.jpg";
	}elseif( strpos($i,'cl') === 0 ){
		return "img/panel/gen-cloud.jpg";
	}elseif( strpos($i,'ic') === 0 ){
		return "img/panel/gen-camera.jpg";
	}elseif( preg_match('/^(fw|wc|cs|vp|up)/',$i) ){
		return "img/panel/gen-ctrl.jpg";
	}elseif( preg_match('/^(hv|sv)/',$i) ){
		$s = ($s > 4)?4:$s;
		return "img/panel/gen-srv$s.jpg";
	}elseif( !$s ){
		return "img/panel/gen-switch1.jpg";
	}elseif( strpos($i,'n') === 3 ){
		return "img/panel/gen-switch2.jpg";
	}else{
		return "img/panel/gen-switch3.jpg";
	}
}

//===================================================================
// Show a configuration
function Shoconf($l,$smo,$lnr){

	if($smo)
		$l = preg_replace("/(\^)([\w])$/","$1",$l);
	if( preg_match("/^\s*([!#;])|description/",$l) )
		$l = "<span class='gry'>$l</span>";
	elseif( preg_match("/^\s*((host|sys)?name|fault-finder|object-group|group-object)/i",$l) )
		$l = "<span class='dgy'>$l</span>";
	elseif( preg_match("/^\s*(no|undo)\s*|shutdown|disable|access-list|access-class|permit|rules/i",$l) )
		$l = "<span class='red'>$l</span>";
	elseif( preg_match("/user|login|password|inspect|network-object|port-object/i",$l) )
		$l = "<span class='prp'>$l</span>";
	elseif( preg_match("/^\s*(service|snmp|telnet|ssh|logging|boot|ntp|clock|http)| log /i",$l) )
		$l = "<span class='mrn'>$l</span>";
	elseif( preg_match("/root|cost|spanning-tree|stp|failover/i",$l) )
		$l = "<span class='grn'>$l</span>";
	elseif( preg_match("/passive-interface|default-gateway|redistribute|bgp/i",$l) )
		$l = "<span class='olv'>$l</span>";
	elseif( preg_match("/network|ip cef|neighbor|route|lldp/i",$l) )
		$l = "<span class='blu'>$l</span>";
	elseif( preg_match("/interface|vlan|line|\Wport/i",$l) )
		$l = "<span class='sbu'>$l</span>";
	elseif( preg_match("/address|broadcast|netmask|area/i",$l) )
		$l = "<span class='org'>$l</span>";
	elseif( preg_match("/^ standby.*|trunk|channel|access/i",$l) )
		$l = "<span class='sna'>$l</span>";
	elseif( preg_match("/^\s?aaa|radius|authentication|policy|crypto/i",$l) )
		$l = "<span class='drd'>$l</span>";
	elseif( preg_match("/ (mld|igmp|pim) /i",$l) )
		$l = "<span class='olv'>$l</span>";
	elseif( preg_match("/capabilities|vrf|mpls|vpn/i",$l) )
		$l = "<span class='sbu'>$l</span>";
	if($lnr)
		return sprintf("<span class='txtb'>%3d</span>",$lnr) . " $l\n";
	else
		return "$l\n";
}

//===================================================================
// Return Printer Supply Type
function PrintSupply($t){

	if 	($t == 1)	{return "<img src=\"img/16/ugrp.png\" title=\"other\">";}
	elseif	($t == 2)	{return "<img src=\"img/16/qmrk.png\" title=\"unknown\">";}
	elseif	($t == 3)	{return "<img src=\"img/16/pcm.png\" title=\"toner\">";}
	elseif	($t == 4)	{return "<img src=\"img/16/bdis.png\" title=\"wasteToner\">";}
	elseif	($t == 5)	{return "<img src=\"img/16/mark.png\" title=\"ink\">";}
	elseif	($t == 6)	{return "<img src=\"img/16/mark.png\" title=\"inkCartridge\">";}
	elseif	($t == 7)	{return "<img src=\"img/16/mark.png\" title=\"inkRibbon\">";}
	elseif	($t == 8)	{return "<img src=\"img/16/bdis.png\" title=\"wasteInk\">";}
	elseif	($t == 9)	{return "<img src=\"img/16/qmrk.png\" title=\"opc\">";}
	elseif	($t == 10)	{return "<img src=\"img/16/foto.png\" title=\"developer\">";}
	elseif	($t == 11)	{return "<img src=\"img/16/bomb.png\" title=\"fuserOil\">";}
	elseif	($t == 12)	{return "<img src=\"img/16/flask.png\" title=\"solidWax\">";}
	elseif	($t == 13)	{return "<img src=\"img/16/flask.png\" title=\"ribbonWax\">";}
	elseif	($t == 14)	{return "<img src=\"img/16/bdis.png\" title=\"wasteWax\">";}
	elseif	($t == 15)	{return "<img src=\"img/16/bomb.png\" title=\"fuser\">";}
	elseif	($t == 16)	{return "<img src=\"img/16/clip.png\" title=\"coronaWire\">";}
	elseif	($t == 17)	{return "<img src=\"img/16/bomb.png\" title=\"fuserOilWick\">";}
	elseif	($t == 18)	{return "<img src=\"img/16/tap.png\" title=\"cleanerUnit\">";}
	elseif	($t == 19)	{return "<img src=\"img/16/bbr2.png\" title=\"transferUnit\">";}
	elseif	($t == 20)	{return "<img src=\"img/16/pcm.png\" title=\"tonerCartridge\">";}
	elseif	($t == 21)	{return "<img src=\"img/16/pcm.png\" title=\"tonerCartridge\">";}
	elseif	($t == 22)	{return "<img src=\"img/16/bomb.png\" title=\"fuserOiler\">";}
	elseif	($t == 23)	{return "<img src=\"img/16/drop.png\" title=\"water\">";}
	elseif	($t == 24)	{return "<img src=\"img/16/bdis.png\" title=\"wasteWater\">";}
	elseif	($t == 25)	{return "<img src=\"img/16/tap.png\" title=\"glueWaterAdditive\">";}
	elseif	($t == 26)	{return "<img src=\"img/16/bdis.png\" title=\"wastePaper\">";}
	elseif	($t == 27)	{return "<img src=\"img/16/clip.png\" title=\"bindingSupply\">";}
	elseif	($t == 28)	{return "<img src=\"img/16/clip.png\" title=\"bandingSupply\">";}
	elseif	($t == 29)	{return "<img src=\"img/16/clip.png\" title=\"stitchingWire\">";}
	elseif	($t == 30)	{return "<img src=\"img/16/pkg.png\" title=\"shrinkWrap\">";}
	elseif	($t == 31)	{return "<img src=\"img/16/pkg.png\" title=\"paperWrap\">";}
	elseif	($t == 32)	{return "<img src=\"img/16/clip.png\" title=\"staples\">";}
	elseif	($t == 33)	{return "<img src=\"img/16/icon.png\" title=\"inserts\">";}
	elseif	($t == 34)	{return "<img src=\"img/16/fobl.png\" title=\"covers\">";}
	else			{return "-";}
}

//===================================================================
// Print IF RDD graphs and provide appropriate links
// Tiny graphs don't show y-axis, thus scale traffic to bw and bcast to 100
// Err and discards are bad enough to show any of them...
// If graphs are disabled in User-Profile, they're not drawn at all
function IfGraphs($ud,$ui,$opt,$sz){

	global $anim,$trflbl,$errlbl,$stalbl, $inblbl, $maxlbl;

	if($sz){
#		$sz -= 1;
?>
<a href="Devices-Graph.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&it%5B%5D=t">
<img src="inc/drawrrd.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&s=<?= $sz ?>&t=trf&o=<?= $opt ?>" title="<?= $trflbl ?> <?= ($sz == 1)?" $maxlbl ".DecFix($opt):"" ?>">
</a>

<a href="Devices-Graph.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&it%5B%5D=e">
<img src="inc/drawrrd.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&s=<?= $sz ?>&t=err&o=1" title="<?= $errlbl ?>">
</a>

<a href="Devices-Graph.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&it%5B%5D=d">
<img src="inc/drawrrd.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&s=<?= $sz ?>&t=dsc" title="Discards">
</a>

<a href="Devices-Graph.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&it%5B%5D=b">
<img src="inc/drawrrd.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&s=<?= $sz ?>&t=brc&o=100" title="Broadcast <?=$inblbl?> <?= ($sz == 1)?" $maxlbl 100":"" ?>">
</a>

<a href="Devices-Graph.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&it%5B%5D=s">
<img src="inc/drawrrd.php?dv=<?= $ud ?>&if%5B%5D=<?= $ui ?>&s=<?= $sz ?>&t=sta" title="<?= $stalbl ?>">
</a>
<?PHP
	}else{
		echo "---";
	}
}

//===================================================================
// Creates a Radargraph using interface information
function IfRadar($id,$sz,$c,$ti,$to,$ei,$eo,$di,$do,$bi){

	global $trflbl,$errlbl,$dcalbl,$inblbl,$oublbl,$debug,$anim;

	if( !$ti and !$to) return;									# No traffic, no graph!
	
	if($sz == 4){
		$w = 220;
		$h = 200;
		$f = 9;
	}elseif($sz == 3){
		$w = 110;
		$h = 100;
		$f = 7;
	}else{
		$w = 80;
		$h = 70;
		$f = 0;
	}
	$in  = substr($inblbl,0,1);
	$out = substr($oublbl,0,1);
	$trf = substr($trflbl,0,3);
	$err = substr($errlbl,0,3);
	$dca = substr($dcalbl,0,3);
	$red = intval( 10000 * ($ei + $eo + $di + $do + $bi)/( $ti + $to ) );
	$red = ($red > 8)?8:$red;
?>
<canvas id="<?= $id ?>" class="genpad" width="<?= $w ?>" height="<?= $h ?>"></canvas>

<script language="javascript">
var data = {

	labels : ["<?= $trf ?> <?= $in ?>","<?= $trf ?> <?= $out ?>","<?= $err ?> <?= $in ?>","<?= $err ?> <?= $out ?>","<?= $dca ?>  <?= $in ?>","<?= $dca ?> <?= $out ?>","Bcast"],
	datasets : [
		{
			fillColor : "rgba(<?= $red*30 ?>,<?= substr($c,0,1)*30 ?>,<?= substr($c,1,1)*30 ?>,0.4)",
			strokeColor : "rgba(<?= $red*25 ?>,<?= substr($c,0,1)*25 ?>,<?= substr($c,1,1)*25 ?>,1)",
			pointColor : "rgba(<?= $red*20 ?>,<?= substr($c,0,1)*20 ?>,<?= substr($c,1,1)*20 ?>,1)",
			pointStrokeColor : "#fff",
			data : [<?= intval($ti/1000) ?>,<?= intval($to/1000) ?>,<?= $ei ?>,<?= $eo ?>,<?= $di ?>,<?= $do ?>,<?= $bi ?>]
		}
	]
}
var ctx = document.getElementById("<?= $id ?>").getContext("2d");
var myNewChart = new Chart(ctx).Radar(data,{pointLabelFontSize : <?= $f ?><?= $anim ?>});
</script>

<?php
	if($debug){
		echo "<div class=\"textpad code pre txta\">\n";
		echo "$id,$sz,$c,$ti,$to,$ei,$eo,$di,$do,$bi,$anim";
		echo "</div>\n";
	}
}

//===================================================================
// Return link style based on forward bandwidth or utilisation
function LinkStyle($bw=0,$utl=0){

	global $lit;

	if($lit == 'l'){
		$w = 4;
		if($utl == 0){										# No traffic
			return array($w,'gainsboro');
		}elseif($utl < 2){
			return array($w,'cornflowerblue');
		}elseif($utl < 5){
			return array($w,'blue');
		}elseif($utl < 10){
			return array($w,'green');
		}elseif($utl < 25){
			return array($w,'limegreen');
		}elseif($utl < 50){
			return array($w,'yellow');
		}elseif($utl < 75){
			return array($w,'orange');
		}else{
			return array($w,'red');
		}
	}else{
		if($bw == 0){										# No bandwidth
			return array('1','lightgray');
		}elseif($bw == 11000000 or $bw == 54000000 or $bw == 300000000 or $bw == 450000000){	# Most likely Wlan
			return array('5','gainsboro');
		}elseif($bw < 10000000){								# Most likely serial links
			return array(intval($bw/1000000),'limegreen');
		}elseif($bw < 100000000){								# 10 Mbit Ethernet
			return array(intval($bw/10000000),'blue');
		}elseif($bw < 1000000000){								# 100 Mbit Ethernet
			return array(intval($bw/100000000),'orange');
		}elseif($bw < 10000000000){								# 1 Gbit Ethernet
			return array(intval($bw/1000000000),'red');
		}else{											# 10 Gbit Ethernet
			return array(intval($bw/10000000000),'purple');
		}
	}
}

//===================================================================
// Count interfaces which are down and haven't changed status for retire days
function IfFree($m,$t,$in,$op,$st){

	global $link,$retire,$porlbl,$frelbl;

	if( $t == 'eth'){
		$qt = '^(6|7|117)$';
		$ti = 'p45';
		$tl = 'Ethernet';
	}else{
		$qt = '^(94|96|97|169|230|238|251)$';
		$ti = 'plug';
		$tl = 'xDSL';
	}

	$query = GenQuery('interfaces','s','count(ifname)','','',array($in,'iftype','ifstat','lastchg'),array($op,'~','<','<'),array($st,$qt,'3',time()-$retire*86400),array('AND','AND','AND'),'JOIN devices USING (device)' );
	$res   = DbQuery($query,$link);
	if( DbNumRows($res) ){
		$r = DbFetchRow($res);
		$ao  = ( $m & 1 )?"<a href=\"Devices-Interfaces.php?in[]=$in&op[]=$op&st[]=".urlencode($st)."&co[]=AND&in[]=ifstat&op[]=<&st[]=3&co[]=AND&in[]=iftype&op[]=~&st[]=$qt&col[]=imBL&col[]=ifname&col[]=device&col[]=linktype&col[]=ifdesc&col[]=alias&col[]=lastchg&col[]=inoct&col[]=outoct&ord=lastchg\">":'';
		$ac  = ( $m & 1 )?'</a>':'';
		$bar = ( $m & 2 )?Bar($r[0],-5,'sbar'):'';
		$img = ( $m & 4 )?"<img src=\"img/$ti.png\" title=\"$tl $porlbl $frelbl\">":'';
		if( $r[0] )echo "\t\t\t\t$ao$img$bar$r[0]$ac";
	}
	DbFreeResult($res);
}

//===================================================================
// Count devices
function DevPop($in,$op,$st,$co=array() ){

	global $link,$retire;

	$query = GenQuery('devices','s','count(device)','','',$in,$op,$st,$co );
	$res   = DbQuery($query,$link);
	$lpop  = DbFetchRow($res);
	DbFreeResult($res);

	return $lpop[0];
}

//===================================================================
// Count nodes
function NodPop($in,$op,$st,$co){

	global $link,$retire;

	$query = GenQuery('nodes','s','count(mac)','','',$in,$op,$st,$co,'JOIN devices USING (device)' );
	$res   = DbQuery($query,$link);
	$lpop  = DbFetchRow($res);
	DbFreeResult($res);

	return $lpop[0];
}

//===================================================================
// Returns support status background
function SupportBg($d){
	if($d){
		if( time() > $d ){
			return "alrm";
		}elseif( time() + 30 * 86400 > $d ){
			return "warn";
		}else{
			return "good";
		}
	}else{
		return '';
	}
}

//===================================================================
// Returns asset status icon
function Staimg($s='',$t=''){

	global $nonlbl,$invlbl,$stco;

	if($s === 0){
		return "<img src=\"img/16/fogy.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 10){
		return "<img src=\"img/16/star.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 100){
		return "<img src=\"img/16/flas.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 120){
		return "<img src=\"img/16/bcls.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 150){
		return "<img src=\"img/16/warn.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 160){
		return "<img src=\"img/16/ncfg.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 170){
		return "<img src=\"img/16/trsh.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 180){
		return "<img src=\"img/16/cash.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 200){
		return "<img src=\"img/16/bstp.png\" title=\"$stco[$s] $t\">";
	}elseif($s == 250){
		return "<img src=\"img/16/qmrk.png\" title=\"$stco[$s] $t\">";
	}else{
		return "<img src=\"img/16/find.png\" title=\"$nonlbl $invlbl $t\">";
	}
}

//===================================================================
// Crosscheck Assets
function InvCheck($sn,$ty,$cl,$lo,$co){

	global $link,$stco,$igrp,$mast,$stalbl,$invlbl,$addlbl,$srvlbl,$endlbl;

	$usn    = urlencode($sn);
	$uty    = urlencode($ty);
	$ulo    = urlencode($lo);
	$uco    = urlencode($co);

	$query	= GenQuery('inventory','s','state,endmaint,endsupport,maintstatus,comment','','',array('serial'),array('='),array($sn));
	$dires	= DbQuery($query,$link);
	$dinv = DbFetchRow($dires);
	DbFreeResult($dires);
	if( $dinv[0] ){
		$class = '';
		$renst = '';
		$stl   = "$srvlbl $endlbl ".date($_SESSION['timf'],$dinv[2]).", $igrp[31] $endlbl ".date($_SESSION['timf'],$dinv[1]);
		$mst   = SupportBg($dinv[1]);
		$wst   = SupportBg($dinv[2]);
		if($mst == 'alrm' or $wst == 'alrm'){
			$class = 'class="genpad alrm nw"';
		}elseif($mst == 'warn' or $wst == 'warn'){
			$class = 'class="genpad warn nw"';
		}elseif($mst == 'good' and $wst == 'good'){
			$class = 'class="genpad good nw"';
		}elseif($mst == 'good' or $wst == 'good'){
			$class = 'class="genpad good part nw"';
		}

		if( $dinv[3] == 10 ){
			$renst = " <span class=\"grn\" title=\"$igrp[31] $stalbl\">".$mast[$dinv[3]].'</span>';
		}elseif( $dinv[3] == 20 ){
			$renst = " <span class=\"drd\" title=\"$igrp[31] $stalbl\">".$mast[$dinv[3]].'</span>';
		}
		return ( isset($_GET['xls']) )?$sn.$renst:"<a href=\"Assets-Management.php?chg=$usn\" $class title=\"$stl\">".Staimg($dinv[0], $dinv[4])." $sn</a>$renst";
	}elseif($sn and $sn != '-'){
		return ( isset($_GET['xls']) )?$sn:"<a href=\"Assets-Management.php?st=150&sn=$usn&ty=$uty&cl=$cl&lo=$ulo&co=$uco\" title=\"$invlbl $addlbl\">".Staimg()."$sn</a>";
	}
}

//===================================================================
// Return config status icon
function DevCfg($bucs){

	global $cfglbl,$chglbl,$stalbl,$wrtlbl,$buplbl,$errlbl,$nonlbl,$outlbl,$stco,$tim;

	$bup = substr($bucs,0,1);
	$sts = substr($bucs,1,1);

	if( $bup == 'A' ){
		$bst = "<img src=\"img/bulbg.png\" title=\"$buplbl: $stco[100]\">";
	}elseif( $bup == 'O' ){
		$bst = "<img src=\"img/bulby.png\" title=\"$buplbl: $outlbl\">";
	}elseif( $bup == 'E' ){
		$bst = "<img src=\"img/bulbo.png\" title=\"$buplbl: $errlbl\">";
	}elseif( $bup == 'U' ){
		$bst = "<img src=\"img/bulbb.png\" title=\"$buplbl: OK, $stalbl $stco[250]\">";
	}else{
		$bst = "<img src=\"img/bulba.png\" title=\"$buplbl: $nonlbl\">";
	}

	if( $sts == 'W' ){
		$cst = "<img src=\"img/bulbg.png\" title=\"$cfglbl: $wrtlbl OK\">";
	}elseif( $sts == 'C' ){
		$cst = "<img src=\"img/bulbo.png\" title=\"$cfglbl: $chglbl $tim[a] $wrtlbl\">";
	}else{
		$cst = "<img src=\"img/bulba.png\" title=\"$cfglbl: $wrtlbl $stco[250]\">";
	}
	return $bst.$cst;
}

//===================================================================
// Return seconds from timeticks
function Tic2Sec($ticks){

	sscanf($ticks, "%d:%d:%d:%d.%d",$upd,$uph,$upm,$ups,$ticks);
	return $ups + $upm*60 + $uph*3600 + $upd*86400;
}

//===================================================================
// Delete device, related tables and files
function DevDelete($dld,$dtxt){#TODO change inventory state of dev and modules to used?

	global $link,$delbl,$errlbl,$updlbl,$nedipath;

	$query	= GenQuery('devices','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Device ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Device</span> ";}
	$query	= GenQuery('interfaces','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Interfaces ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Interfaces</span> ";}
	$query	= GenQuery('modules','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Modules ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Modules</span> ";}
	$query	= GenQuery('links','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Links ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Links</span> ";}
	$query	= GenQuery('links','d','','','',array('neighbor'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Links ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Links</span> ";}
	$query	= GenQuery('configs','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Config ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Config</span> ";}
	$query	= GenQuery('monitoring','d','','','',array('name'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Monitoring ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Monitoring</span> ";}
	$query	= GenQuery('incidents','d','','','',array('name'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Incidents ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Incidents</span> ";}
	$query	= GenQuery('vlans','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Vlans ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Vlans</span> ";}
	$query	= GenQuery('networks','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Networks ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Networks</span> ";}
	$query	= GenQuery('events','d','','','',array('source'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Events ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Events</span> ";}
	$query	= GenQuery('iftrack','d','','','',array('device'),array('='),array($dld) );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Iftrack ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Iftrack</span> ";}
	$query	= GenQuery('nbrtrack','d','','','',array('device','neighbor'),array('=','='),array($dld,$dld),array('OR') );
	if( !DbQuery($query,$link) ){echo "<span class=\"drd\">Nbrtrack ".DbError($link)."</span> ";}else{echo "<span class=\"olv\">Nbrtrack</span> ";}

	$devdir = rawurlencode($dld);
	if( file_exists ( "$nedipath/rrd/$devdir/*.rrd" ) ){
		foreach (glob("$nedipath/rrd/$devdir/*.rrd") as $rrd){
			echo (unlink($rrd))?"<h5>$rrd $dellbl OK</h5>":"<h4>$rrd $dellbl $errlbl</h4>";
		}
		echo (rmdir("$nedipath/rrd/$devdir"))?"<h5>$nedipath/rrd/$devdir $dellbl OK</h5>":"<h4>$nedipath/rrd/$devdir $dellbl $errlbl</h4>";
	}
	if( file_exists ( "$nedipath/conf/$devdir/*.rrd" ) ){
		foreach (glob("$nedipath/conf/$devdir/*.cfg") as $cfg){
			echo (unlink($cfg))?"<h5>$cfg $dellbl OK</h5>":"<h4>$cfg $dellbl $errlbl</h4>";
		}
		echo (rmdir("$nedipath/conf/$devdir"))?"<h5>$nedipath/conf/$devdir $dellbl OK</h5>":"<h4>$nedipath/conf/$devdir $dellbl $errlbl</h4>";
	}

	$query = GenQuery('events','i','','','',array('level','time','source','info','class','device'),array(),array('100',time(),$dld,"device$dtxt deleted by $_SESSION[user]",'usrd',$dld) );
}

?>
