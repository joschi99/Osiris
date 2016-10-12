<?php
# Program: Monitoring-Health.php
# Programmer: Remo Rickli
# Clustermap by Conny Ohlsson (disabled on line 198 due to Google licensing)
# Uses coordinates from locations table or 9 and 10th field in snmp location

$refresh   = 60;
$exportxls = 0;
$yesterday = time() - 86400;

ini_set('default_socket_timeout', 1);

include_once ("inc/header.php");
include_once ("inc/libdev.php");
include_once ("inc/libmon.php");

$_GET = sanitize($_GET);
$reg  = isset($_GET['reg']) ? $_GET['reg'] : '';
$cty  = isset($_GET['cty']) ? $_GET['cty'] : '';
$bld  = isset($_GET['bld']) ? $_GET['bld'] : '';
$flr  = isset($_GET['fl']) ? $_GET['fl'] : "";
$rom  = isset($_GET['rm']) ? $_GET['rm'] : "";

if($_SESSION['opt']) $map = $_SESSION['tmap'];

$loc   = TopoLoc($reg,$cty,$bld);
$evloc = ($loc)?"&co[]=AND&in[]=location&op[]=LIKE&st[]=".urlencode($loc):'';
$rploc = ($loc)?"&in[]=location&op[]=LIKE&st[]=".urlencode($loc):'';

list($mw,$mh) = MapSize( $_SESSION['gsiz'] );

$link  = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query = GenQuery('devices','s',"count(*),
	sum(case when lastdis < $yesterday then 1 else 0 end),
	sum(case when snmpversion > 0 then 1 else 0 end),
	sum(case when test is not NULL then 1 else 0 end),
	sum(case when status > 0 then 1 else 0 end)"
	,'','',array('location'),array('LIKE'),array($loc),array(),'LEFT JOIN monitoring USING (device)');
$res   = DbQuery($query,$link);
$data  = DbFetchRow($res);
DbFreeResult($res);

?>
<h1 onclick="document.dynfrm.style.display = (document.dynfrm.style.display == 'none')?'':'none';">Monitoring Map</h1>

<form method="get" name="dynfrm" action="<?= $self ?>.php">
	<input type="hidden" name="reg" value="<?= $reg ?>">
	<input type="hidden" name="cty" value="<?= $cty ?>">
	<input type="hidden" name="bld" value="<?= $bld ?>">

<script src="inc/Chart.min.js"></script>

<table class="content">
<tr class="bgmain">
<td class="ctr s">
	<h3>
		<img src="img/16/exit.png" title="Stop" onClick="stop_countdown(interval);">
		<span id="counter"><?= $refresh ?></span>
	</h3>
	<a href="<?= $self ?>.php"><img src="img/32/<?= $selfi ?>.png" title="<?= $self ?>"></a>
</td>
<td class="ctr top">
	<h3><?= $monlbl ?></h3>
	<a href="Monitoring-Setup.php?in[]=status&op[]=>&st[]=0">
		<canvas id="mon" style="display: block;margin: 0 auto;padding: 10px;" width="<?= $mw ?>" height="<?= $mw ?>"></canvas>
	</a>
	<div style="margin-top: -<?= intval($mw/1.4) ?>px;font-size: <?= intval($mw/8) ?>px;"><?= $data[4] ?><br><?= $data[3] ?></div>
</td>
<td class="ctr top">
	<h3><?= $dsclbl ?></h3>
	<a href="Devices-List.php?in[]=lastdis&op[]=%3C&st[]=<?= $yesterday ?>">
		<canvas id="dev" style="display: block;margin: 0 auto;padding: 10px;" width="<?= $mw ?>" height="<?= $mw ?>"></canvas>
	</a>
	<div style="margin-top: -<?= intval($mw/1.4) ?>px;font-size: <?= intval($mw/8) ?>px;"><?= $data[1] ?><br><?= $data[0] ?></div>
</td>
<td class="ctr top">
	<h3><?= $msglbl ?></h3>
	<a href="Monitoring-Events.php?in[]=time&op[]=>&st[]=<?= time() - $rrdstep ?>">
		<canvas id="event" style="display: block;margin: 0 auto;padding: 10px;" width="<?= $mw ?>" height="<?= $mw ?>"></canvas>
	</a>
<?php

$mon[] = array('value' => $data[3]-$data[4],'color' => '#4f4','label' => $stco['100']);
$mon[] = array('value' => $data[4],'color' => '#f44','label' => $stco['200']);

$dev[] = array('value' => $data[0]-$data[1],'color' => '#4f4','label' => $stco['100']);
$dev[] = array('value' => $data[1],'color' => '#fa4','label' => $outlbl);

$query	= GenQuery('events','g','level','level desc','',array('time','location'),array('>','LIKE'),array( (time() - $rrdstep),$loc),array('AND'),'LEFT JOIN devices USING (device)');
$res	= DbQuery($query,$link);
if($res){
	while( ($m = DbFetchRow($res)) ){
		$evlvl[$m[0]] = $m[1];
	}
	DbFreeResult($res);
}else{
	print DbError($link);
}

?>

<script language="javascript">
var data = <?= json_encode($dev,JSON_NUMERIC_CHECK) ?>

var ctd = document.getElementById("dev").getContext("2d");

var myNewChart = new Chart(ctd).Doughnut(data, { segmentStrokeWidth : 1, percentageInnerCutout : 75, animationEasing : "easeOutElastic"});

var data = <?= json_encode($mon,JSON_NUMERIC_CHECK) ?>

var ctm = document.getElementById("mon").getContext("2d");
var myNewChart = new Chart(ctm).Doughnut(data, {segmentStrokeWidth : 1, percentageInnerCutout : 75, animationEasing : "easeOutElastic"});

var data = [
	{
		value : <?= ($evlvl['30'])?$evlvl['30']:0 ?>,
		color: "#aaaaaa",
		label: "<?= $mlvl['30'] ?>"
	},
	{
		value : <?= ($evlvl['50'])?$evlvl['50']:0 ?>,
		color: "#44aa44",
		label: "<?= $mlvl['50'] ?>"
	},
	{
		value : <?= ($evlvl['100'])?$evlvl['100']:0 ?>,
		color: "#4444aa",
		label: "<?= $mlvl['100'] ?>"
	},
	{
		value : <?= ($evlvl['150'])?$evlvl['150']:0 ?>,
		color: "#aaaa44",
		label: "<?= $mlvl['150'] ?>"
	},
	{
		value : <?= ($evlvl['200'])?$evlvl['200']:0 ?>,
		color: "#aa6644",
		label: "<?= $mlvl['200'] ?>"
	},
	{
		value : <?= ($evlvl['250'])?$evlvl['250']:0 ?>,
		color: "#aa4444",
		label: "<?= $mlvl['250'] ?>"
	},
]
var cte = document.getElementById("event").getContext("2d");
var myNewChart = new Chart(cte).PolarArea(data,{segmentStrokeWidth : 1, animationEasing : "easeOutElastic"});

</script>

</td>
</tr>
</table>
</form>
<p>

<?php
	$statsRackCount=0; $statsAddressCount=0; $statsNoCoords=0;
	$link = DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('locations','s','region,city,building,ns,ew');
	$res	= DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($l = DbFetchRow($res)) ){
			if($l[2]){
			$nscoord[$l[0]][$l[1]][$l[2]] = $l[3]/10000000;
			$ewcoord[$l[0]][$l[1]][$l[2]] = $l[4]/10000000;
			}
		}
	}

	$query	= GenQuery('devices','s','devices.device,inet_ntoa(devip),type,location,contact,test,status,latency,latwarn','location','',array('snmpversion','location'),array('>','LIKE'),array('0',$loc),array('AND'),'LEFT JOIN monitoring on (devices.device = monitoring.name)' );
	$res	= DbQuery($query,$link);
	if($res){
		$okl = 0;
		$nol = 0;
		while( ($dv = DbFetchRow($res)) ){
			if ( $dv[3] ) {
				list($country, $town, $address, $floor, $room, $rack, $rackHeight, $coordinates) = explode(";", $dv[3]);
				if ($coordinates != "") {
					list ($lat_raw, $lng_raw) = explode(",", $coordinates);
					$lat = trim($lat_raw);
					$lng = trim($lng_raw);
				} else {
					$lat = $nscoord[$country][$town][$address];
					$lng = $ewcoord[$country][$town][$address];
				}
				if( $lat and $lng ){
					$loc["$lat;;$lng"]['dv']++;
					$loc["$lat;;$lng"]['al']  += ($dv[6])?1:0;
					$loc["$lat;;$lng"]['adr'] = $address;
					$okl++;
				}else{
					$nol++;
				}
			}
		}
		
		if( 1 or !$okl ){
			if( $data[4] ){
				include_once ("inc/librep.php");
				MonAvail('status','>',0,'');
			}
			include_once ("inc/footer.php");
			exit;
		}

?>
<script type="text/javascript"
	src="inc/markerclusterer.js">
</script> 
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
<script>
var locations = [
<?php

		foreach (array_keys($loc) as $c){
			list ($lat, $lng) = explode(";;", $c);
			$addressURLenc = urlencode($loc[$c]['adr']);
			$msg_hover = $loc[$c]['adr'].', '.$loc[$c]['al'].' Alerts';
			$msg_click ="<div class=\"s lft\">".$loc[$c]['adr']."<p>";
			$msg_click .="<a href=\"Devices-List.php?in%5B%5D=location&op%5B%5D=~&co%5B%5D=&st%5B%5D=$addressURLenc\">Device $lstlbl</a><br>";
			$msg_click .="<a href=\"Nodes-List.php?in%5B%5D=location&op%5B%5D=~&st%5B%5D=$addressURLenc\">Node $lstlbl</a><br>";
			$msg_click .="<a href=\"Monitoring-Events.php?in%5B%5D=location&op%5B%5D=~&st%5B%5D=$addressURLenc\">$msglbl $lstlbl</a><br>";
			$msg_click .="<a href=\"Monitoring-Setup.php?in%5B%5D=location&op%5B%5D=~&st%5B%5D=$addressURLenc\">Setup</a><br>";
			$msg_click .="</div>";
			printf("['%s', %s, %s, '%s', '%s',%s],\n",$address,$lat,$lng,$msg_click,$msg_hover,$loc[$c]['al']);
		}
	}
?>
	];
var map;
var bounds = {};

function initialize() {
var stylarr = [
  {
    featureType: '',
    elementType: '',
	stylers: [
	  { saturation: -75 },
	  { gamma: 1.9 }
	]
  },
  {
    featureType: '',
    // etc...
  }
]

	var mapOptions = {
		zoom: 8,
		center: new google.maps.LatLng(0,0),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById('map-canvas'),
		mapOptions);
	map.setOptions({styles: stylarr});
	setMarkers(map,locations)
}

function setMarkers(map,locations){
	var marker, i
	var markers = [];
	bounds = new google.maps.LatLngBounds();
	for (i = 0; i < locations.length; i++)
	{  

		var address = locations[i][0]
		var lat = locations[i][1]
		var lng = locations[i][2]
		var msg_click =  locations[i][3]
		var msg_hover = locations[i][4]

		latlngset = new google.maps.LatLng(lat, lng);

		if( locations[i][5] ){
			var marker = new google.maps.Marker({
				map: map,
				icon: ( locations[i][5] == 1 )?'img/32/foye.png':'img/32/ford.png',
				animation: google.maps.Animation.BOUNCE,
				title: msg_hover,
				position: latlngset
			});
		}else{
			var marker = new google.maps.Marker({
				map: map,
				icon: 'img/32/fogr.png',
				title: msg_hover,
				position: latlngset
			});
		}

		map.setCenter(marker.getPosition())
		var content = msg_click
		var infowindow = new google.maps.InfoWindow({ maxWidth: 320 })
		google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){
			return function() { infowindow.setContent(content);	infowindow.open(map,marker); };	})(marker,content,infowindow));
		bounds.extend(latlngset);
		map.fitBounds(bounds);
		markers.push(marker); // Comment this out if you dont want clustering of markers
	}
	var markerCluster = new MarkerClusterer(map, markers, {
		maxZoom: 16,
		averageCenter: 1,
	});
	bounds.extend(latlngset);
	map.fitBounds(bounds);
}

function myclick(i) {
	google.maps.event.trigger(markers[i], "click");
}

google.maps.event.addDomListener(window, 'load', initialize);
</script>

<center><div id="map-canvas" style="width:1000px; height:600px;border:1px solid black"></div></center>

<div class="textpad txtb half">
	<a  href="javascript: map.fitBounds(bounds);">Zoom out</a>
	Devices <?= $nonlbl ?> <?= $loclbl ?>: <?= $nol ?>/<?= $okl+$nol ?><br>
</div>
</center>
<?php

if($status and $_SESSION['vol']){echo "<audio src=\"inc/major.mp3\" autoplay onplay=\"this.volume=.$_SESSION[vol]\">no audio</audio>\n";}

include_once ("inc/footer.php");

?>
