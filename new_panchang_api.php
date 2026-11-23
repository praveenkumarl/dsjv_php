<?php

$jsonResponse = isset($_GET['format']) && $_GET['format'] === 'json';
$jsonBufferLevel = ob_get_level();
if ($jsonResponse) {
	ob_start();
	register_shutdown_function(function() use ($jsonBufferLevel) {
		$error = error_get_last();
		$fatalErrorTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
		if (!$error || !in_array($error['type'], $fatalErrorTypes, true)) {
			return;
		}

		while (ob_get_level() > $jsonBufferLevel) {
			ob_end_clean();
		}
		error_log('new_panchang_api.php: fatal JSON request error: ' . $error['message']);
		http_response_code(500);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('error' => 'Panchang calculation failed.'), JSON_UNESCAPED_UNICODE);
	});
}

function sendPanchangJsonResponse($payload, $statusCode) {
	global $jsonBufferLevel;

	while (ob_get_level() > $jsonBufferLevel) {
		ob_end_clean();
	}

	http_response_code($statusCode);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

include_once "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;
$conn="";
$lat="";
$lon="";
$gmt_offset="";
$tz_offset;
$today_sunrise="";
$today_sunset="";
$tomo_sunrise="";
$transit_today_sunrise="";
$transit_today_sunset="";
$transit_tomo_sunrise="";
$transit_prev_sunset="";
$sun_degree=0;
$moon_degree=0;
$sun_house=0;
$moon_house=0;
$transit_sun_degree=0;
$transit_sun_house=0;
$transit_moon_degree=0;
$transit_moon_house=0;
$ragu_house=-1;
$tr_ragu_house=-1;
$sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$sw_degree = array();
$tr_sw_degree = array();
$out_planet_degree = array();
$sw_dir = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_dir = array(null,null,null,null,null,null,null,null,null,null,null,null);
$dayofweek=-1;
$transit_dayofweek=-1;
$day=-1;
$transit_day=-1;
$output=array();
$lagna_house=null;
$lagna_degree=null;
$transit_lagna_house=null;
$transit_lagna_degree=null;
$cricketMenu=0;
$autoAnalysisMenu=0;
$planetDeg = array();
$planetDeg_transit = array();
$planetDeg_default = array();
$mandhi_degree=0;
$mandhi_degree_transit=0;
$panchang = array();
$nak_pad_idx = 0;
$dasa_balance = array(null,null,null); // years, months, days
$birth_dasa_athipathi="";


//usage new_panchang_api.php?place='Chennai|Asia/Kolkata'&date='YYYY-MM-DD'&time=HH:MM:SS?debug=0
$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$place = isset($_GET['place']) ? $_GET['place'] : "Chennai|Asia/Kolkata";

$chart_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$chart_time = isset($_GET['time']) ? $_GET['time'] : date("H:i:s");

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));

$city=strtok($place,"|");
$TZ=strtok("|");

date_default_timezone_set($TZ);

$cookie_name = "place";
$cookie_value = $place;
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day

?>
<?php



// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 


?>

<body onLoad="init();">


<?php


function planetSortPanchangApi($inArray)
{
	global $planetDeg, $planetDeg_default, $DEBUG;
	$count = 0;
	
	foreach ($inArray as $key => $line)
	{
		$row = explode(',',$line);
		$count = $count + 1;
		
		if($count>8 && $count!=21) {continue;}

		debug($count."[".$row[0]."|".$row[1]."|".$row[2]."]<Br>");
		$planetDeg[trim($row[0])] = round(fmod(floatval($row[1]), 30.0),2);
	}
	
	debug("PLANET+DEGREE");
	if($DEBUG==1){var_dump($planetDeg);}
	
	debug("PLANET+DEGREE+SORTED");
	asort($planetDeg);
	
	if($DEBUG==1){var_dump($planetDeg);}
	
	foreach ($planetDeg as $key => $val) {
		if($val!=null or $val>=0) {
			
			switch (trim($key)) {
				case "Sun":
					$planet="சூரி";
					break;
				case "Moon":
					$planet="சந்";
					break;
				case "Mercury":
					$planet="புத";
					break;
				case "Venus":
					$planet="சுக்";
					break;
				case "Mars":
					$planet="செ";
					break;
				case "Jupiter":
					$planet="குரு";
					break;
				case "Saturn":
					$planet="சனி";
					break;
				case "true Node":
					$planet="ரா";
					break;
				case "Ascendant":
					$planet="லக்";
					break;
				case "mandhi":
					$planet="மா";
					break;
				default:
					break;
			}
			
			$out_planet_degree[$planet] = $val;
		}
	}
	
	$planetDeg = $out_planet_degree;
}

$conn = dbConnect($conn);
if ($jsonResponse && !($conn instanceof mysqli)) {
	sendPanchangJsonResponse(array('error' => 'Panchang data is temporarily unavailable.'), 503);
}

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$city."'"." AND TZ="."'".$TZ."'";

debug($sql);

try {
	$result = $conn->query($sql);
} catch (mysqli_sql_exception $exception) {
	if (!$jsonResponse) {
		throw $exception;
	}

	dbDisconnect($conn);
	error_log('new_panchang_api.php: panchang lookup failed: ' . $exception->getMessage());
	sendPanchangJsonResponse(array('error' => 'Panchang data is temporarily unavailable.'), 500);
}

if ($jsonResponse && (!$result || $result->num_rows === 0)) {
	dbDisconnect($conn);
	sendPanchangJsonResponse(array('error' => 'No Panchang data is available for this place.'), 404);
}

if ($result && $result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $lat = $row["Latitude"];
    $lon = $row["Longitude"];
    $gmt_offset = $row["GMT_offset"];
    $gmt_offset_dst=$row["GMT_offset_dsl"];
  }
} else {
  echo "0 results";
}

if($DEBUG==1){
echo "DATE: " . $chart_date."<BR>";
echo "TIME: " . $chart_time."<BR>";
echo "PLACE: " . $place."<BR>";
echo "LAT: " . $lat."<BR>";
echo "LONG: " . $lon."<BR>";
echo "GMT offset: " . $gmt_offset."<BR>";
echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
echo "TZ: " . $TZ ."<BR>";
}



if(date('I')==1) { // DST
   $hour=(int)strtok($gmt_offset_dst,":");
   $min=(int)strtok(":");
   $offset=$hour+$min/60;
   $tz_offset=$gmt_offset_dst;
}
else {
   $hour=(int)strtok($gmt_offset,":");
   $min=(int)strtok(":");
   $offset=$hour+$min/60;
   $tz_offset=$gmt_offset;
}

// ------- Swiss ephemeris // ------- 

$date = date("d.m.Y",strtotime($chart_date));
$time = date("H:i:s",strtotime($chart_time));
$datetime = $date . " " . $time;
$h_sys='P';

if($DEBUG==1){ 
	echo 'DATETIME =' . $datetime; echo '<br>';
	echo 'HOUSE SYSTEM =' . $h_sys; echo '<br>';
}

/**
 * Converting birth date/time to UTC
 */
//$birthTimestamp = date('Y-m-d H:i:s',strtotime($datetime));
$birthTimestamp = strtotime($datetime);
$utcTimestamp = $birthTimestamp - $offset * (60 * 60);

$date = date('d.m.Y', $utcTimestamp);
$time = date('H:i:s', $utcTimestamp);

if($DEBUG==1){ 
	echo 'DATE =' . $date ;
	echo 'TIME =' . $time;
	echo 'OFFSET =' . $offset; echo '<br>';
	echo 'birthTimestamp =' . $birthTimestamp; echo '<br>';
	echo 'utcTimestamp =' . $utcTimestamp; echo '<br>';
	echo 'UTC time=' . date('Y-m-d H:i:s', $utcTimestamp); echo '<br>';
}

$today_sunrise = $chart_date . " " . getSunrise(strtotime($chart_date),floatval($lat), floatval($lon));
$today_sunset = $chart_date . " " .  getSunset(strtotime($chart_date),floatval($lat), floatval($lon));

debug("CHART date=".$chart_date);
debug("CHART TIME=".$chart_time);
debug("CHART SUNRISE=".date("H:i:s",strtotime($today_sunrise)));
debug("CHART SUNSET=".date("H:i:s",strtotime($today_sunset)));

debug("DAY=" . $day);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	debug('RUNNING ON WINDOWS');
	
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	//64 version runs little faster
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

debug("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys");

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 
if($DEBUG==1){ 
	var_dump($output);
}

planetSortPanchangApi($output);

$count=0;
foreach ($output as $key => $line)
  {
	global $ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	//$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	$sw_degree[$row[0]] = round($tot_degree[$key] - $sw_house[$key]*30,2);
	
    if($count!=21){
		$sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($sun_house==0 and strpos($row[0],'Sun')===0){
		$sun_house = (int)($tot_degree[$key]/30)+1;
		$sun_degree = getDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	if($moon_house==0 and strpos($row[0],'Moon')===0){
		$moon_house = (int)($tot_degree[$key]/30)+1;
		$moon_degree = getDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	if($lagna_house==0 and strpos($row[0],'Ascendant')===0){
		$lagna_house = (int)($tot_degree[$key]/30)+1;
	}
	
	$sw_planet[$sw_house[$key]] = $sw_planet[$sw_house[$key]]." ".$row[0];
	
	$tot_degree[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
		
		$ragu_house = $sw_house[$key];
		if($DEBUG==1){ echo "ragu_house=" . $sw_house[$key]."<br>";}
	}
		
	if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$sw_house[$key]."|".$sw_planet[$key]."|".$sw_degree[$key]."|".$sw_dir[$key]."|"."<br>";}
  };
  
if($DEBUG==1){ 
  echo "<br>Birth[".$sw_planet[0]."]</br>";
  var_dump($sw_planet);
  var_dump($sw_degree);
  var_dump($sw_dir);
}

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

				
//displayPanchang(date('d-m-Y',strtotime($chart_date)),$chart_time,$place);
calculatePanchang();

if ($jsonResponse) {
	require_once __DIR__ . '/lang/lang.php';
	$currentLanguage = isset($_COOKIE['language']) && strtolower($_COOKIE['language']) === 'english'
		? 'english'
		: 'tamil';
	$language = $currentLanguage;
	init_language($currentLanguage);
	$thithiParts = explode('/', $panchang['thithi']);
	if (is_english()) {
		$thithiName = explode('/', $panchang['thithi'], 2)[0];
	} else {
		$thithiName = $thithiParts[1] ?? $panchang['thithi'];
	}
	$thithiDisplay = trim(explode('[', $thithiName, 2)[0]);
	if (preg_match('/\[([^\]]*)\]/u', $panchang['thithi'], $pakshaMatch)) {
		$waxingLabel = is_english() ? 'Wax' : 'வளர்';
		$waningLabel = is_english() ? 'Wan' : 'தேய்';
		$pakshaLabels = array(
			'shukla' => $waxingLabel,
			'sukla' => $waxingLabel,
			'waxing' => $waxingLabel,
			'வளர்' => $waxingLabel,
			'வளர்பிறை' => $waxingLabel,
			'krishna' => $waningLabel,
			'waning' => $waningLabel,
			'தேய்' => $waningLabel,
			'தேய்பிறை' => $waningLabel
		);
		foreach (explode('/', $pakshaMatch[1]) as $pakshaPart) {
			$paksha = strtolower(trim($pakshaPart));
			if (isset($pakshaLabels[$paksha])) {
				$thithiDisplay .= ' (' . $pakshaLabels[$paksha] . ')';
				break;
			}
		}
	}

	$requestedChartDate = $chart_date;
	$tamilDate = preg_replace('/<br\s*\/?>/i', ' ', getTamilDate());
	$tamilDate = trim(preg_replace('/\s+/', ' ', strip_tags($tamilDate)));
	$chart_date = $requestedChartDate;
	if (is_english()) {
		$tamilDate = translate_tamil_date($tamilDate);
	}

	$sunriseTimestamp = strtotime($today_sunrise);
	$sunsetTimestamp = strtotime($today_sunset);
	$currentTimestamp = strtotime($requestedChartDate . ' ' . $chart_time);
	if ($sunriseTimestamp === false || $sunsetTimestamp === false || $currentTimestamp === false) {
		sendPanchangJsonResponse(array('error' => 'Unable to calculate local sunrise and sunset.'), 500);
	}

	$g_agas = null;
	$periods = array(
		array('key' => 'ema_kantam', 'label' => __('ema_kantam'), 'value' => getEmaKantam()),
		array('key' => 'kuligai_time', 'label' => __('kuligai_time'), 'value' => getKulikai()),
		array('key' => 'rahu_kaalam', 'label' => __('rahu_kaalam'), 'value' => getRahuKaalam())
	);
	$periodStatus = __('periods_finished');
	$periodStatusType = 'clear';
	$highlightedPeriod = '';
	$activePeriod = null;
	$nextPeriod = null;
	foreach ($periods as $periodIndex => $period) {
		$range = preg_split('/\s*-\s*/', $period['value'], 2);
		if (count($range) !== 2) {
			sendPanchangJsonResponse(array('error' => 'Unable to calculate inauspicious time ranges.'), 500);
		}
		$periodStart = strtotime($requestedChartDate . ' ' . trim($range[0]));
		$periodEnd = strtotime($requestedChartDate . ' ' . trim($range[1]));
		if ($periodStart === false || $periodEnd === false) {
			sendPanchangJsonResponse(array('error' => 'Unable to calculate inauspicious time ranges.'), 500);
		}
		$periods[$periodIndex]['start'] = trim($range[0]);
		$periods[$periodIndex]['end'] = trim($range[1]);
		if ($currentTimestamp >= $periodStart && $currentTimestamp <= $periodEnd) {
			$activePeriod = $periods[$periodIndex];
		} elseif ($periodStart > $currentTimestamp && ($nextPeriod === null || $periodStart < $nextPeriod['start_timestamp'])) {
			$periods[$periodIndex]['start_timestamp'] = $periodStart;
			$nextPeriod = $periods[$periodIndex];
		}
	}
	if ($activePeriod !== null) {
		$periodStatus = $activePeriod['label'] . ' · ' . __('active_until') . ' ' . $activePeriod['end'];
		$periodStatusType = 'active';
		$highlightedPeriod = $activePeriod['key'];
	} elseif ($nextPeriod !== null) {
		$periodStatus = $nextPeriod['label'] . ' · ' . __('next_period_at') . ' ' . $nextPeriod['start'];
		$periodStatusType = 'upcoming';
		$highlightedPeriod = $nextPeriod['key'];
	}

	$tomorrowDate = date('Y-m-d', strtotime($requestedChartDate . ' +1 day'));
	$yesterdayDate = date('Y-m-d', strtotime($requestedChartDate . ' -1 day'));
	$tomorrowSunrise = strtotime($tomorrowDate . ' ' . getSunrise(strtotime($tomorrowDate), floatval($lat), floatval($lon)));
	$yesterdaySunset = strtotime($yesterdayDate . ' ' . getSunset(strtotime($yesterdayDate), floatval($lat), floatval($lon)));
	if ($tomorrowSunrise === false || $yesterdaySunset === false) {
		sendPanchangJsonResponse(array('error' => 'Unable to calculate the current hora.'), 500);
	}

	$horaCycle = array('சூரி', 'சுக்', 'புத', 'சந்', 'சனி', 'குரு', 'செ');
	$horaDayLords = array(0, 3, 6, 2, 5, 4, 1);
	$horaWeekday = (int)date('w', $currentTimestamp);
	$horaStart = $sunriseTimestamp;
	$horaEnd = $sunsetTimestamp;
	$horaIndex = 0;
	if ($currentTimestamp < $sunriseTimestamp) {
		$horaWeekday = ($horaWeekday + 6) % 7;
		$horaStart = $yesterdaySunset;
		$horaEnd = $sunriseTimestamp;
		$horaIndex = 12;
	} elseif ($currentTimestamp >= $sunsetTimestamp) {
		$horaStart = $sunsetTimestamp;
		$horaEnd = $tomorrowSunrise;
		$horaIndex = 12;
	}
	$horaDuration = ($horaEnd - $horaStart) / 12;
	if ($horaDuration <= 0) {
		sendPanchangJsonResponse(array('error' => 'Unable to calculate the current hora.'), 500);
	}
	$horaIndex += (int)floor(($currentTimestamp - $horaStart) / $horaDuration);
	$horaIndex = max(0, min(23, $horaIndex));
	$horaLord = $horaCycle[($horaDayLords[$horaWeekday] + $horaIndex) % count($horaCycle)];
	$gregorianTimestamp = strtotime($requestedChartDate);
	if ($gregorianTimestamp === false) {
		sendPanchangJsonResponse(array('error' => 'Unable to format the selected date.'), 500);
	}
	$tamilGregorianMonths = array(
		1 => 'ஜன', 2 => 'பிப்', 3 => 'மார்', 4 => 'ஏப்',
		5 => 'மே', 6 => 'ஜூன்', 7 => 'ஜூலை', 8 => 'ஆக',
		9 => 'செப்', 10 => 'அக்', 11 => 'நவ', 12 => 'டிச'
	);

	$response = array(
		'weekday' => panchang_display_part($panchang['vaaram']),
		'thithi' => $thithiDisplay,
		'nakshatra' => panchang_display_part($panchang['nak']),
		'yoga' => map_display_text($panchang['yoga']),
		'karanam' => panchang_display_part($panchang['karan']),
		'place' => $city,
		'gregorian_day' => date('j', $gregorianTimestamp),
		'gregorian_month_year' => (is_english() ? date('M', $gregorianTimestamp) : $tamilGregorianMonths[(int)date('n', $gregorianTimestamp)]) . ' ' . date('Y', $gregorianTimestamp),
		'tamil_date' => $tamilDate,
		'sunrise' => date('g:i A', $sunriseTimestamp),
		'sunset' => date('g:i A', $sunsetTimestamp),
		'ema_kantam' => $periods[0]['value'],
		'kuligai_time' => $periods[1]['value'],
		'rahu_kaalam' => $periods[2]['value'],
		'period_status' => $periodStatus,
		'period_status_type' => $periodStatusType,
		'highlighted_period' => $highlightedPeriod,
		'hora_name' => translate_planet_abbr($horaLord),
		'hora_time' => date('g:i A', $currentTimestamp)
	);
	dbDisconnect($conn);
	sendPanchangJsonResponse($response, 200);
}

// creating object of SimpleXMLElement
$xml_data = new SimpleXMLElement('<?xml version="1.0"?><panchang></panchang>');
array_to_xml($panchang,$xml_data);

//saving generated xml file; 
//$result = $xml_data->asXML('name.xml');

//$xml_data->asXML('out.xml');
print $xml_data->asXML();

dbDisconnect($conn);

?>

</body>
</html>