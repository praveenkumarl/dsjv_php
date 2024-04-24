<?php
include_once "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;
$place = isset($_GET['place']) ? $_GET['place'] : "Chennai|Asia/Kolkata";

$lang = isset($_POST["lang"]) ? $_POST["lang"] : "Tamil";
debug("PLACE=".$place);
$city=strtok($place,"|");
$TZ=strtok("|");

date_default_timezone_set($TZ);

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
$g_nak_pad_idx = 0;
$dasa_balance = array(null,null,null); // years, months, days
$birth_dasa_athipathi="";
$g_dasaCurrent=array();
$current_dasa_bukthi=array();
$yogi_arr=array();
$country="";
$otherPanchangDetails = array();
$g_thithi_soonyam_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$full_sun_deg=0.00000;
$sun_moon_data=array();
$full_moon_deg=0.00000;
$g_nak="";
$g_thithi_idx=-1;
$g_yoga_idx=-1;
$g_nak_idx=-1;
$g_dasaBalance=array();
$g_dasaCurrent=array();

// ------- Swiss ephemeris // ------- 
$libPath = '../sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$chart_time = isset($_GET['time']) ? $_GET['time'] : date("H:i:s");
$dayofweek = date('w', strtotime($chart_date));

function planetSort($inArray)
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
				//case "true Node":
				case "mean Node":
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


function getTransitChart()
{
	global $sw_planet;
	
echo <<<EOL
	<div class="divTable blueTable" id="transit_chart">
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$sw_planet[11]</div>
					<div class="divTableCell">$sw_planet[0]</span></div>
					<div class="divTableCell">$sw_planet[1]</div>
					<div class="divTableCell">$sw_planet[2]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[10]</div>
					<div class="divTableCenterCell" style='font-size:10px'>ராசி</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$sw_planet[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'>D1</div>
					<div class="divTableCell">$sw_planet[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[8]</div>
					<div class="divTableCell">$sw_planet[7]</div>
					<div class="divTableCell">$sw_planet[6]</div>
					<div class="divTableCell">$sw_planet[5]</div>
			</div>
		</div>	
	</div>

EOL;
}

$conn = dbConnect($conn);

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl,country FROM world_cities WHERE Place="."'".$city."'"." AND TZ="."'".$TZ."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $lat = $row["Latitude"];
    $lon = $row["Longitude"];
    $gmt_offset = $row["GMT_offset"];
    $gmt_offset_dst=$row["GMT_offset_dsl"];
	$country=$row["country"];
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

date_default_timezone_set($TZ);

if(date('I',strtotime($chart_date))==1) { // DST
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
	echo "----------".getUTCtimestamp($birthTimestamp,$offset);
}

$today_sunrise = $chart_date . " " . getSunrise(strtotime($chart_date),floatval($lat), floatval($lon));
$today_sunset = $chart_date . " " .  getSunset(strtotime($chart_date),floatval($lat), floatval($lon));

debug("CHART date=".$chart_date);
debug("CHART TIME=".$chart_time);
debug("CHART SUNRISE=".date("H:i:s",strtotime($today_sunrise)));
debug("CHART SUNSET=".date("H:i:s",strtotime($today_sunset)));

// find if it's day or night for mandhi calculation
if(strtotime($datetime) >= strtotime($today_sunrise) && strtotime($datetime) <= strtotime($today_sunset)) {
	$day=1;
}else {
	$day=0;
}

debug("DAY=" . $day);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	debug('RUNNING ON WINDOWS');
	
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	//64 version runs little faster
	//exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // t - mean node
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // m - mean node
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);  // t - mean node
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>"); // m - mean node
}

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 
if($DEBUG==1){ 
	var_dump($output);
}

planetSort($output);

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
	//if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
	if(strpos($row[0],"mean Node")===0){ // using === because this strpos returns 0 when mean Node found and also false(0) when it's not found. So we need to use ===
		
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

$sw_planet = updatePlanet($sw_planet,$sw_dir, $ragu_house);

// Update Mandhi
$sw_planet = updateMandhi($sw_planet,$sw_planet,$sun_degree,$day,$dayofweek,$sun_house);

//sort by degree
$sw_planet = sortDegree($sw_planet);

getTransitChart();

dbDisconnect($conn);

?>