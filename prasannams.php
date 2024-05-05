<!DOCTYPE html>
<?php
include "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

if(isset($_SESSION['prasannam_place']) && isset($_POST['prasannam_place']) && strcmp($_SESSION['prasannam_place'],$_POST['prasannam_place'])==0){
	$place = $_SESSION['prasannam_place'];
}
elseif (isset($_POST['prasannam_place'])){
	$place = $_POST['prasannam_place'];
	$_SESSION['prasannam_place'] = $_POST['prasannam_place'];
}
elseif (isset($_SESSION['prasannam_place'])){
	$place = $_SESSION['prasannam_place'];
}
else{
	$place = "Chennai|Asia/Kolkata";
}


$lang = isset($_POST["lang"]) ? $_POST["lang"] : "Tamil";
debug("PLACE=".$place);
$city=strtok($place,"|");
$TZ=strtok("|");

$type = isset($_GET['type']) ? $_GET['type'] : -1;

//for rasi prasannam
$rasi = isset($_POST['hiddenRasi']) ? $_POST['hiddenRasi'] : -1;
$rasiNo = isset($_POST['rasiNo']) ? $_POST['rasiNo'] : -1;

//for paadha prasannam
$paadham = isset($_POST['hiddenPaadham']) ? $_POST['hiddenPaadham'] : -1;
$paadhamNo = isset($_POST['paadhamNo']) ? $_POST['paadhamNo'] : "";

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
$libPath = './sweph/';
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
	<center><div class="divTable blueTable" id="transit_chart" style="width:300px;height:300px;">
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableCell" id=12>$sw_planet[11]</div>
					<div class="divTableCell" id='1'>$sw_planet[0]</span></div>
					<div class="divTableCell" id=2>$sw_planet[1]</div>
					<div class="divTableCell" id=3>$sw_planet[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" id=11>$sw_planet[10]</div>
					<div class="divTableCenterCell" style='font-size:10px' id='chartName'></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell" id=4>$sw_planet[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" id=10>$sw_planet[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'></div>
					<div class="divTableCell" id=5>$sw_planet[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" id=9>$sw_planet[8]</div>
					<div class="divTableCell" id=8>$sw_planet[7]</div>
					<div class="divTableCell" id=7>$sw_planet[6]</div>
					<div class="divTableCell" id=6>$sw_planet[5]</div>
			</div>
		</div>	
	</div>
	</center>
<BR><BR><BR>
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

$next_day=date("Y-m-d",strtotime($chart_date. ' + 1 days'));
$previous_day=date("Y-m-d",strtotime($chart_date. ' - 1 days'));

$today_sunrise = $chart_date . " " . getSunrise(strtotime($chart_date),floatval($lat), floatval($lon));
$today_sunset = $chart_date . " " .  getSunset(strtotime($chart_date),floatval($lat), floatval($lon));
$tomo_sunrise = $next_day . " " .  getSunrise(strtotime($next_day),floatval($lat), floatval($lon));
$prev_sunset = $previous_day . " " .  getSunset(strtotime($previous_day),floatval($lat), floatval($lon));

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

dbDisconnect($conn);

function lagnaPrasannam(){
	global $place;
	echo <<<EOL
	<center>
	<div class="divTable blueTable" id="d2" style='height:auto'>
		<form name="prasannamForm" id="prasannamForm" action="#" method="post">
			<input type="hidden" value=1>
			<table style="width:100%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">லக்ன/கோட்சார பிரசன்னம்</P></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன தேதி</div></td>
					<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன நேரம்</div></td>
					<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*பிரசன்ன இடம்</div></td>
					<td><div class="search-box"><input type="text" name="prasannam_place" id="prasannam_place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur(this.form);" value='$place' /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td style="width:50%;text-align:right">
						<input type="submit" value="Submit" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
					</td>
					<td style="width:50%;text-align:left">
						<button value="New" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="window.reload();">New</button>
					</td>
				</tr>					
			</table>
		</form>	
	</div>
	</center>
EOL;
}

function clockPrasannam(){
	global $place;
	echo <<<EOL
	<center>
	<div class="divTable blueTable" id="clock" style='height:auto'>
				
		<form name="prasannamForm" id="prasannamForm" action="#" method="post">
			<table style="width:100%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">கடிகார பிரசன்னம்</P></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன தேதி</div></td>
					<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன நேரம்</div></td>
					<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*பிரசன்ன இடம்</div></td>
					<td><div class="search-box"><input type="text" name="prasannam_place" id="prasannam_place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur();" value='$place' /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td style="width:50%;text-align:right">
						<input type="submit" value="Submit" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
					</td>
					<td style="width:50%;text-align:left">
						<button value="New" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="window.reload();">New</button>
					</td>
				</tr>					
			</table>
		</form>	
	</div>
	</center>
EOL;
}

function rasiPrasannam(){
	global $rasi,$rasiNo,$place ;
	$selected = [null,null,null,null,null,null,null,null,null,null,null,null,null];
	
	if($rasi != 1){ //first time, so display random rasi
		mt_srand();
		$random_number = mt_rand(1,12);	
	
		$selected[$random_number] = 'selected';
	}
	else{
		$selected[$rasiNo] = 'selected';
	}
	
	echo <<<EOL
	<center>
	<div class="divTable blueTable" id="rasi" style='height:auto'>
				
		<form name="prasannamForm" id="prasannamForm" action="#" method="post">
			<input type='hidden' name='hiddenRasi' id='hiddenRasi' value=1 />
			<table style="width:100%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">ராசி	பிரசன்னம்</P></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*தேர்வு செய்த எண்</div></td>
					<td>
						<select name='rasiNo' id='rasiNo'>
							<option value='1' $selected[1]>1</option>
							<option value='2' $selected[2]>2</option>
							<option value='3' $selected[3]>3</option>
							<option value='4' $selected[4]>4</option>
							<option value='5' $selected[5]>5</option>
							<option value='6' $selected[6]>6</option>
							<option value='7' $selected[7]>7</option>
							<option value='8' $selected[8]>8</option>
							<option value='9' $selected[9]>9</option>
							<option value='10' $selected[10]>10</option>
							<option value='11' $selected[11]>11</option>
							<option value='12' $selected[12]>12</option>
						</select>
						<BR><div style='font-size:12px;color:blue'>12க்குள் ஒரு நம்பர் தேர்வு செய்யவும்</div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன தேதி</div></td>
					<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன நேரம்</div></td>
					<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*பிரசன்ன இடம்</div></td>
					<td><div class="search-box"><input type="text" name="prasannam_place" id="prasannam_place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur();" value='$place' /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td style="width:50%;text-align:right">
						<input type="submit" value="Submit" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
					</td>
					<td style="width:50%;text-align:left">
						<button value="New" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="newPrasannam(3);return false;">New</button>
					</td>
				</tr>				
			</table>
		</form>	
	</div>
	</center>
EOL;
}

function paadhaPrasannam(){
	global $paadham,$paadhamNo,$place ;
	
	if($paadham != 1){ //first time, so display random paadham
		mt_srand();
		$paadhamNo = mt_rand(1,108);
	}
	
	echo <<<EOL
	<center>
	<div class="divTable blueTable" id="paadham" style='height:auto'>
				
		<form name="prasannamForm" id="prasannamForm" action="#" method="post">
			<input type='hidden' name='hiddenPaadham' id='hiddenPaadham' value=1 />
			<table style="width:100%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">பாத	பிரசன்னம்</P></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*நக்ஷ். பாதம்:</div></td>
					<td><input type="number" name="paadhamNo" id="paadhamNo" autocomplete="off" style="width:60px;text-align:center;background:#ffffff;font-weight:bold" min="1" max="108" onfocusout='validateNumber();' value='$paadhamNo' />
					<BR><div style='font-size:12px;color:blue'>108க்குள் ஒரு நம்பர் தேர்வு செய்யவும்</div></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன தேதி</div></td>
					<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன நேரம்</div></td>
					<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*பிரசன்ன இடம்</div></td>
					<td><div class="search-box"><input type="text" name="prasannam_place" id="prasannam_place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur();" value='$place' /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td style="width:50%;text-align:right">
						<input type="submit" value="Submit" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
					</td>
					<td style="width:50%;text-align:left">
						<button value="New" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="newPrasannam(4);return false;">New</button>
					</td>
				</tr>				
			</table>
		</form>	
	</div>
	</center>
EOL;
}

function horaiPrasannam(){
	global $place;
	echo <<<EOL
	<center>
	<div class="divTable blueTable" id="horai" style='height:auto'>
				
		<form name="prasannamForm" id="prasannamForm" action="#" method="post">
			<input type='hidden' name='hiddenHorai' value=1 />
			<table style="width:100%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">ஹோரை	பிரசன்னம்</P></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன தேதி</div></td>
					<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">பிரசன்ன நேரம்</div></td>
					<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold;background-color:lightgray" readonly></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><div class="divBirthInputLable">*பிரசன்ன இடம்</div></td>
					<td><div class="search-box"><input type="text" name="prasannam_place" id="prasannam_place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur();" value='$place' /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td style="width:50%;text-align:right">
						<input type="submit" value="Submit" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
					</td>
					<td style="width:50%;text-align:left">
						<button value="New" class="actionBtn" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="window.reload();">New</button>
					</td>
				</tr>				
			</table>
		</form>	
	</div>
	</center>
EOL;
}

?>


<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro admin page</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

	<script>
	
	var g_horalord = "-";
	
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length>2){
            $.get("backend-search.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
               resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
// Set search input value on click of result item
	//$(document).on("click", ".result p", function(){
	$(document).on("click", ".result div", function(){
    //$(document).on("change", ".result select", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());		
		//$(this).parents(".search-box").find('input[type="text"]').val($('option:selected', this).val());
		
        $(this).parent(".result").empty();
		$(this).parent("#lplace").hide();
    });
});

		
		document.addEventListener("readystatechange", function(event){
			
			if(event.target.readyState == "interactive"){
				$('#hora').load('hora.php div#currentHoraTable');
			}
			
			if(event.target.readyState == "complete"){
				//setTimeout(updateUdhayaLagna(<?php echo $type; ?>),2000);
			}
				
		});
		
		function init(){
			//document.getElementById('prasannam_place').value = '<?php echo $place; ?>';
			updateNow();
			
		}

	function validateForm() {

	  let d = document.getElementById("in_date").value;
	  let t = document.getElementById("in_time").value;
	  
	  if (d == "" || t == "") {
			updateNow();
	  }
	  
	  p = document.getElementById("prasannam_place").value;
	  if (p == "") {
		alert('பிறந்த இடம் கொடுல்லவில்லை');
		document.getElementById("error").innerHTML = "பிறந்த இடம் கொடுல்லவில்லை";
		document.getElementById('prasannam_place').style.borderColor = "red";
		return false;
	  }
	  
	  if (p.includes("|")==false || p.includes("/")==false || p.length<10){
		  alert('நீங்கள் இடத்தை கீழ் வரும் பட்டியலில் தேர்ந்தெடுக்கவில்லை');
		  document.getElementById("error").innerHTML = "நீங்கள் இடத்தை கீழ் வரும் பட்டியலில் தேர்ந்தெடுக்கவில்லை";
		  document.getElementById('prasannam_place').style.borderColor = "red";
		  return false;
	  }
	}

	function placeOnBlur() {
		let place = document.getElementById("prasannam_place").value;
		
		if(place==""){
			document.getElementById('prasannam_place').value = '<?php echo $place; ?>';
		}
	}

	

	function updateNow() {
	  const date = new Date();
	  
	  var dd = date.getDate();
	  var mm = date.getMonth()+1; 
	  var yyyy = date.getFullYear();
	  if(dd<10) 
	  {
		  dd='0'+dd;
	  } 

	  if(mm<10) 
	  {
		  mm='0'+mm;
	  } 
	  var today = yyyy+'-'+mm+'-'+dd;

	  var H=date.getHours();
	  var M=date.getMinutes();
	  var S=date.getSeconds();

	  if(H<10) 
	  {
		  H='0'+H;
	  } 

	  if(M<10) 
	  {
		  M='0'+M;
	  } 

	  if(S<10) 
	  {
		  S='0'+S;
	  } 
	  var time = H+':'+M+':'+S;

	  document.getElementById('in_date').value= today ; 
	  document.getElementById('in_time').value= time ; 
	}
	
	function updateUdhayaLagna(){
		type = <?php echo $type; ?>;
		switch(type){
			case 1: //kotchar prasannam
				for (i = 1; i <= 12; i++) {
					var text = document.getElementById(i).innerHTML;
				  if(text.includes("லக்")){
					document.getElementById(i).style.backgroundColor = "Orange";
				  }
				}
				break;
			case 2: //clock prasannam
				var datetime = new Date();
				hour = datetime.getHours();
				min = datetime.getMinutes();
				
				target_house = Math.floor(min / 5)+1;
				
				for (i = 1; i <= 12; i++) {
					var text = document.getElementById(i).innerHTML;
					if(text.includes("லக்")){
						document.getElementById(i).style.backgroundColor = "Orange";
						//document.getElementById(i).innerHTML = document.getElementById(i).innerHTML.replace("லக்","உத");
						document.getElementById(i).innerHTML = document.getElementById(i).innerHTML + " <span style='color:black;background-color:white;'>உத</span>";
					}
				}
				
				document.getElementById(target_house).style.backgroundColor = "Orange";
				document.getElementById(target_house).innerHTML = document.getElementById(target_house).innerHTML + " <span style='color:black;background-color:white;'>ஆரு</span>";
				
				break;
			case 3: //rasi prasannam
				var rasi = document.getElementById('rasiNo').value;
				
				document.getElementById(rasi).style.backgroundColor = "Orange";
				document.getElementById(rasi).innerHTML = document.getElementById(rasi).innerHTML + " <span style='color:black;background-color:white;'>உத</span>";
				
				break;
			case 4: //paadha prasannam
				var paadham = parseInt(document.getElementById('paadhamNo').value);
				
				var rasi = Math.ceil(paadham/9);
				
				document.getElementById(rasi).style.backgroundColor = "Orange";
				document.getElementById(rasi).innerHTML = document.getElementById(rasi).innerHTML + " <span style='color:black;background-color:white;'>உத</span>";
				
				break;
			case 5: //horai prasannam
				
				horaLord = document.getElementById('horaLord').textContent;
				for (i = 1; i <= 12; i++) {
					var text = document.getElementById(i).innerHTML;
					if(text.includes(horaLord)){
						document.getElementById(i).style.backgroundColor = "Orange";
						document.getElementById(i).innerHTML = document.getElementById(i).innerHTML + " <span style='color:black;background-color:white;'>உத</span>";
					}
				}
				break;
			default:
				break;
		}
	}
	
	function subtractTimeFromDate(objDate, intHours) {
		var numberOfMlSeconds = objDate;
		var addMlSeconds = (intHours * 60) * 60 * 1000;
		var newDateObj = new Date(numberOfMlSeconds - addMlSeconds);
		return newDateObj;
	}
	
	function addHoursToDate(objDate, intHours) {
		var numberOfMlSeconds = objDate;
		var addMlSeconds = (intHours * 60) * 60 * 1000;
		var newDateObj = new Date(numberOfMlSeconds + addMlSeconds);
		return newDateObj;
	}
	
	function validateNumber(){
		var value = parseInt(document.getElementById('paadhamNo').value);
		if(value < 0 || value >108){
			alert('108க்குள் ஒரு நம்பர் தேர்வு செய்யவும்');
			document.getElementById('paadhamNo').style.borderColor = 'red';
			//document.getElementById('paadhamNo').focus();
			return false;
		}
	}
	
	function displayHora(){
		//this is to display hora details
		
		$.ajax({
		   url: "hora.php?flag=1",
		   success: function(data){
			 $(".hora").html(data);
		   }
		 });
	}
	
	function getSubHora(){
		/*var xhr = new XMLHttpRequest();
			xhr.open('GET', 'hora.php', true);
			xhr.responseType = 'document';
			xhr.send();
			xhr.onload = function(e) {  
				var doc = e.target.responseXML;
				//alert(doc.getElementById('currentHoraTable').innerHTML);
				document.getElementById('hora').innerHTML = doc.getElementById('currentHoraTable').innerHTML;
				g_horalord = doc.getElementById('horaLord').innerHTML;
			}*/
			$('#hora').load('hora.php div#currentHoraTable');
	}
	
	function newPrasannam(type){
		switch(type){
		case 1:
		case 2:
		case 5:
			window.reload();
			break;
		case 3: //raasi prasannam
			document.getElementById('hiddenRasi').value=-1;
			window.reload();
			break;
		case 4: //paadha prasannam
			document.getElementById('hiddenPaadham').value=-1;
			window.reload();
			break;
		default:
			break;
		}
	}

	</script>
	
<body onLoad='init();'>

<div class="title"></div>
	<div class='column-menu'>
		<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>	
			 
			<button class="menu" onClick="window.open('prasannam_home.php','_self')">Prasannam Home</button>
		</div>
		<div class='mobile'>
			<?php include "prasannamMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
	<div class="grid-container" style='height:auto'>
	  <div class="grid-item" onClick="window.open('prasannam_home.php','_self')" style="cursor:pointer;height:auto">மற்ற பிரசன்னங்கள்</div>
	</div>
	
<?php
switch($type){ 
	case 1:
		lagnaPrasannam();
		break;
	case 2:
		clockPrasannam();
		break;
	case 3:
		rasiPrasannam();
		break;
	case 4:
		paadhaPrasannam();
		break;
	case 5:
		horaiPrasannam();
		echo "<center><div class='hora' style='margin-bottom:20px' id='hora'>will be filled from hora.php</div></center>";
		//echo "<script>getSubHora();</script>";
		break;
	default:
		echo "Wrong option";
}

getTransitChart();

?>

	</div>
<script>setTimeout(updateUdhayaLagna,500);</script>


</body>
</html>