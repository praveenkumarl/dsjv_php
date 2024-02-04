<?php
if(!session_id()){
	session_start();
}
	
include_once "db.php";

$moon_longitude_data=array();

//constants

const MIN_STAR_MATCH_COUNT = 7; //minimum matching
const TOTAL_STAR_MATCH_COUNT = 10; //max matching
const TOTAL_MATCH_RULES = 12; //total matching rules

$DEBUG= isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

function getSunrise($date,$lat,$long)
{
	$sun_info = date_sun_info($date,$lat,$long);
	
	foreach ($sun_info as $key => $val) {
		if(strpos($key,"sunrise")===0){
			return date("H:i:s", $val);
		}	
	}
}

function getSunset($date,$lat,$long)
{
	$sun_info = date_sun_info($date,$lat,$long);
	
	foreach ($sun_info as $key => $val) {
		if(strpos($key,"sunset")===0){
			return date("H:i:s", $val);
		}	
	}
}

// requires a valid time in the sHH:MM format, returns minutes of day 
function timeToMinutes($time)
{
	debug("timeToMinutes-time=".$time);
    list($hours, $minutes) = explode(':',$time);
    return 60 * $hours + $minutes;
}

// requires an integer, representing minutes, returns time in the sHH:MM format
function minutesToTime($minutes)

{
    return sprintf('%02d', intdiv($minutes, 60)) . ':' . sprintf('%02d', $minutes % 60);
}

function addTime($time1, $time2) 
{
   $minutes = timeToMinutes($time1) + timeToMinutes($time2);
   return minutesToTime($minutes);
}

function subtractTime($time1, $time2) 
{
   $minutes = timeToMinutes($time1) - timeToMinutes($time2);
   return minutesToTime($minutes);
}

function countHouses($start_house,$end_house)
{	
	if($end_house > $start_house){		
		$housecount = $end_house-$start_house+1;
	}elseif($start_house > $end_house){
		$housecount = 12-$start_house+1+$end_house;
	}else{
		$housecount = 1;
	}
	
	return $housecount;
}

function DECtoDMS($dec)
{

// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.
    $vars = explode(".",$dec);
    $deg = $vars[0];
	
	if(isset($vars[1])) {
		$tempma = "0.". $vars[1];
	}
	else{
		$tempma = "0.". "0";
	}
    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = $tempma - ($min*60);

    //return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	return $deg . '°' . $min . '\'';
}  

function DECtoDMS_jamakol($dec) //seconds will be added only in case degree is 0
{
// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.
    $vars = explode(".",$dec);
    $deg = $vars[0];
	
	if(isset($vars[1])) {
		$tempma = "0.". $vars[1];
	}
	else{
		$tempma = "0.". "0";
	}
    $tempma = $tempma * 3600;
	
	if(intval($deg)==0){
		$min = round(floor($tempma / 60)/10,0);
		return $deg . '°' . $min . '\'';
	}
	else{
		return $deg . '°';
	}

    //return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	
}  

function DMStoDEC($deg,$min,$sec)
{

    // Converting DMS ( Degrees / minutes / seconds ) to decimal format
    return $deg+((($min*60)+($sec))/3600);
}    


function debug($o)
{
	global $DEBUG;
	
	if($DEBUG==1){
		print '<pre>';
		print_r($o);
		print '</pre>';
	}
}

function findAthipathi($house)
{
	$athipathi="";
	
	switch ($house) {
		case 11:
		case 8:
			$athipathi="குரு";
			break;
		case 9: 
		case 10:
			$athipathi="சனி";
			break;
		case 0:
		case 7:
			$athipathi="செ";
			break;
		case 1:
		case 6:
			$athipathi="சுக்";
			break;
		case 2:
		case 5:
			$athipathi="புத";
			break;
		case 3:
			$athipathi="சந்";
			break;
		case 4:
			$athipathi="சூரி";
			break;
		default:
			break;
	}
			
	return $athipathi;
}

function findUbhaya($house)
{
	$ubhaya="இல்லை";
	
	switch ($house) {
		case 11:
		case 8:
			$ubhaya_udhayam="ஆம்";
			break;
		case 2:
		case 5:
			$ubhaya_udhayam="ஆம்";
			break;
		default:
			break;
	}
			
	return $ubhaya;
}

function updateStrength(array $planets)
{
	global $DEBUG;
	
	if($DEBUG==1) {
		echo "<BR>updateStrength before update</BR>";
		var_dump($planets);
	}
	
	//Meenam
	if(strpos($planets[11],"சுக்")===0){
		//$planets = str_replace("சுக்","சுக்&#8593;",$planets);
		$planets = str_replace("சுக்","சுக்+",$planets);
	}
	if(strpos($planets[11],"புத")===0){
		//$planets = str_replace("புத","புத&#8595;",$planets);
		$planets = str_replace("புத","புத-",$planets);
	}
	
	//Makaram
	if(strpos($planets[9],"செ")===0){
		//$planets = str_replace("செ","செ&#8593;",$planets);
		$planets = str_replace("செ","செ+",$planets);
	}
	if(strpos($planets[9],"குரு")===0){
		//$planets = str_replace("குரு","குரு&#8595;",$planets);
		$planets = str_replace("குரு","குரு-",$planets);
	}
	
	//Thulam
	if(strpos($planets[6],"சனி")===0){
		//$planets = str_replace("சனி","சனி&#8593;",$planets);
		$planets = str_replace("சனி","சனி+",$planets);
	}
	if(strpos($planets[6],"சூரி")===0){
		//$planets = str_replace("சூரி","சூரி&#8595;",$planets);
		$planets = str_replace("சூரி","சூரி-",$planets);
	}
	
	//Kanni
	if(strpos($planets[5],"புத")===0){
		//$planets = str_replace("புத","புத&#8593;",$planets);
		$planets = str_replace("புத","புத+",$planets);
	}
	if(strpos($planets[5],"சுக்")===0){
		//$planets = str_replace("சுக்","சுக்&#8595;",$planets);
		$planets = str_replace("சுக்","சுக்-",$planets);
	}
	
	//Katakam
	if(strpos($planets[3],"குரு")===0){
		//$planets = str_replace("குரு","குரு&#8593;",$planets);
		$planets = str_replace("குரு","குரு+",$planets);
	}
	if(strpos($planets[3],"செ")===0){
		//$planets = str_replace("செ","செ&#8595;",$planets);
		$planets = str_replace("செ","செ-",$planets);
	}
	
	//Rishabam
	if(strpos($planets[1],"சந்")===0){
		//$planets = str_replace("சந்","சந்&#8593;",$planets);
		$planets = str_replace("சந்","சந்+",$planets);
	}
	
	//Mesham
	if(strpos($planets[0],"சூரி")===0){
		//$planets = str_replace("சூரி","சூரி&#8593;",$planets);
		$planets = str_replace("சூரி","சூரி+",$planets);
	}
	if(strpos($planets[0],"சனி")===0){
		//$planets = str_replace("சனி","சனி&#8595;",$planets);
		$planets = str_replace("சனி","சனி-",$planets);
	}
	
	if($DEBUG==1) {
		
		echo "<BR>updateStrength after update</BR>";
		var_dump($planets);
	}
	
	return $planets;
}

function updateSymbols(array $planets)
{
	global $DEBUG;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE SYMBOL before update</BR>";
		var_dump($planets);
	}
	
	$out = str_replace("சூரி","சூரி<br><span class='symbol'>&#9737;<span>",$planets);
	$out = str_replace("செ","செ<br><span class='symbol'>&#9794;<span>",$out);
	$out = str_replace("ரா","ரா<br><span class='symbol'>&#9738;<span>",$out);
	$out = str_replace("குரு","குரு<br><span class='symbol'>&#9795;<span>",$out);
	$out = str_replace("புத","புத<br><span class='symbol'>&#9791;<span>",$out);
	$out = str_replace("சுக்","சுக்<br><span class='symbol'>&#9792;<span>",$out);
	$out = str_replace("சனி","சனி<br><span class='symbol'>&#9796;<span>",$out);
	$out = str_replace("சந்","சந்<br><span class='symbol'>&#9790;<span>",$out);
	
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE SYMBOL after update</BR>";
		var_dump($out);
	}
	
	return $out;
}

function targetJamaHouse($start_house,$number_of_houses)
{
	$temp = 0;
	debug($start_house);
	debug($number_of_houses);
	
	if($start_house-$number_of_houses > 0){
		$temp = $start_house - $number_of_houses;
		
		if($temp == 1 || $temp == 4 || $temp == 7 || $temp == 10){
			$temp = $temp - 1;
		}
	}
	else {
		$temp = 12 - abs($start_house-$number_of_houses);
		
		if($temp == 1 || $temp == 4 || $temp == 7 || $temp == 10){
			$temp = $temp - 1;
		}
	}
	
	debug("targetJH-".$start_house."-".$number_of_houses."-".$temp."<BR>");
	
	return $temp;
}

function uchchaDetails()
{
	global $outside;
	$uchchaDetails="இல்லை";
	$uPlanet="";
	
	foreach ($outside as $value){
		if(strlen($value)>0 && strpos($value,"&#8593;")>0){
		$nPlanet=$value;
		}
	}
	
	if(strlen($uPlanet)>0){
		$uchchaDetails = $uPlanet;
	}
	
	return $uchchaDetails;
}

function nechchaDetails()
{
	global $outside;
	$nechchaDetails="இல்லை";
	$nPlanet="";
	
	foreach ($outside as $value){
		if(strlen($value)>0 && strpos($value,"&#8595;")>0){
		$nPlanet=$value;
		}
	}
	
	if(strlen($nPlanet)>0){
		$nechchaDetails = $nPlanet;
	}
	
	return $nechchaDetails;
}

function createAutoAnalysisSection()
{
	global $Analysis;
	
	echo <<<EOL
		<div class="section" id="section1">
			<div class="intro">
				<div class="container">
					<div style="height:60px"></div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableOuterCell" colspan=3 style="border:none;">
							<div class="header">Auto Analysis</div>
							</div>
						</div>
					</div>
			
					<div class="divTable blueTable">
						<div class="divTableBody">
EOL;

foreach ($Analysis as $key => $value){
//foreach ($out_planet_degree as $key => $value){
	echo "<div class='divTableRow'>";
	echo "<div class='divTableDegreeCell'>$key</div>";
	echo "<div class='divTableDegreeCell'>$value</div>";
	echo "</div>";	
}

echo <<<EOL
						</div>
					</div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableDummyCell" colspan=3>
							<div class="footer">Scroll up for chart details	&#9650;</div>
							</div>
					</div>
				</div>
			</div>
		</div>
	</div>
EOL;

}

// check if the request is from mobile device or system
function isMobileDevice() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo
|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i"
, $_SERVER["HTTP_USER_AGENT"]);
}

function isMobile(){
	return is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile")); //returns true if mobile
}

function isLogged(){
	return isset($_SESSION['logged']) ? $_SESSION['logged'] : false; //returns true if true
}

function getUserInfo_web($phone)
{
	global $conn, $usertype, $row;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT name, phone, user_type, imei, last_login, registered_date from user where phone=$phone";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				$row = $result->fetch_assoc();
				 /*while($row = $result->fetch_assoc()) {
					$usertype = $row["user_type"];
				  }*/
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
	
	return $row;
}

function getUserInfo_mobile($key)
{
	global $conn, $row;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT name, phone, user_type, imei, last_login, registered_date from user where imei='$key'";
		
		//echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				$row = $result->fetch_assoc();
				 /*while($row = $result->fetch_assoc()) {
					$usertype = $row["user_type"];
				  }*/
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
	
	return $row;
}

function checkAdminKey($key)
{
	$keyfile = fopen("adminkey", "r") or die("Unable to open file!");
	$adminkey = fgets($keyfile);
	fclose($keyfile);
	
	//check if admin key sent from mobile app matches the server admin key to allow full access
	if(strcmp($key,$adminkey)==0){ 
		return true;
	}
	else{
		return false;
	}
}

//To display user details
function displayUserInfo($userinfo)
{
	echo "<div class='envWeb' style='background-color:".envColor()."'>";
		echo "<div class='userinfo'>";
			echo $userinfo['name'];
		echo "</div>";
		echo "<div class='userinfo'>";
			echo $userinfo['phone'];
		echo "</div>";
		echo "<div class='userinfo'>";
			echo $userinfo['user_type'];
		echo "</div>";
	echo "</div>";
	
}

function displayUserInfoMobile($userinfo)
{
	echo "<div class='envMobile' style='background-color:".envColor()."'>";
		echo "<div class='userinfoMobile'>";
			echo $userinfo['user_type'];
		echo "</div>";
		echo "<div class='userinfoMobile'>";
			echo $userinfo['phone'];
		echo "</div>";
		echo "<div class='userinfoMobile'>";
			echo $userinfo['name'];
		echo "</div>";
	echo "</div>";
}

//To display user details
function displayDeviceInfo($devInfo)
{
	echo "<table>";
	
	foreach($devInfo as $heading => $val){
		echo "<tr>";
			echo "<th class='devInfo_heading'>$heading</th>";
			echo "<td class='devInfo_body'>$val</td>";
		echo "</tr>";
	}
	
	echo "</table>";
}

function isFreeUser($user_type)
{
	if(strcmp($_SESSION['user_type'],"FREE")==0){
		return true;
	}
	else{
		return false;
	}
}

function envColor()
{
	if(strcmp($_SERVER['HTTP_HOST'],"pkastro.com")==0)
	{
		return "#ffcccc";
	}
	elseif(strcmp($_SERVER['HTTP_HOST'],"preprod.pkastro.com")==0)
	{
		return "#ff8000";
	}
	elseif(strcmp($_SERVER['HTTP_HOST'],"test.pkastro.com")==0)
	{
		return "#4dff88";
	}
	elseif(strcmp($_SERVER['HTTP_HOST'],"192.168.29.78")==0)
	{
		return "#80e5ff";
	}
	elseif(strcmp($_SERVER['HTTP_HOST'],"192.168.29.14")==0)
	{
		return "Black";
	}
	else
	{
		return "Silver";
	}
}

function sortDegree($inArray) // new function
{
	global $planetDeg,$mandhi_degree, $g_rasi_girl;
	
	$RK_house = -1;
	
	$outArray=array(null,null,null,null,null,null,null,null,null,null,null,null);
	
	foreach ($planetDeg as $planet => $deg) {
		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg[$planet],$inArray[$house]);
			
			//arrange planets by degree from top to bottom within these houses (Mesham to Thulam)
			if($house<=5)
			{
				if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = $outArray[$house] . " " . "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
						if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
							$outArray[$house] = $outArray[$house] . " " . "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						}
						elseif(strpos($planet,'மா')>0 || strpos($planet,"மா")===0){
							$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						}
						elseif(strpos($planet,'சந்')>0 || strpos($planet,"சந்")===0){
							//$outArray[$house] = $outArray[$house] . " " . "<span>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							$outArray[$house] = $outArray[$house] . " " . "<span class='moon'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						}
						elseif(strpos($planet,'உத')>0 || strpos($planet,"உத")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='udh'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='udh'>$planet</span>";
							}
						}
						elseif(strpos($planet,'ஆரு')>0 || strpos($planet,"ஆரு")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='aar'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='aar'>$planet</span>";
							}
						}
						elseif(strpos($planet,'கவி')>0 || strpos($planet,"கவி")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='kav'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='kav'>$planet</span>";
							}
						}
						elseif(strpos($planet,'R.K')>0 || strpos($planet,"R.K")===0){
							$RK_house = $house;
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='rahu_kaalam'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='rahu_kaalam'>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						elseif(strpos($planet,'எ.க')>0 || strpos($planet,"எ.க")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='emma_kantam'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='emma_kantam'>$planet</span>";
							}
						}
						else{
							$outArray[$house] = $outArray[$house] . " " . $planet . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							
						}
					}
				}
			}
			//arrange planets by degree from bottom to top within these houses (Virichigam to Meenam)
			else{
				if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
						if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
							$outArray[$house] =  "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						}
						elseif(strpos($planet,'மா')>0 || strpos($planet,"மா")===0){
							$outArray[$house] = "<span class='mandhi'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						}
						elseif(strpos($planet,'சந்')>0 || strpos($planet,"சந்")===0){
							//$outArray[$house] = "<span>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							$outArray[$house] = "<span class='moon'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
						}
						elseif(strpos($planet,'உத')>0 || strpos($planet,"உத")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='udh'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
							}
							else{
								$outArray[$house] = "<span class='udh'>$planet</span>" . " " . $outArray[$house];
							}
						}
						elseif(strpos($planet,'ஆரு')>0 || strpos($planet,"ஆரு")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='aar'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='aar'>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						elseif(strpos($planet,'கவி')>0 || strpos($planet,"கவி")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='kav'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='kav'>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						elseif(strpos($planet,'R.K')>0 || strpos($planet,"R.K")===0){
							$RK_house = $house;
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='rahu_kaalam'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='rahu_kaalam'>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						elseif(strpos($planet,'எ.க')>0 || strpos($planet,"எ.க")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='emma_kantam'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='emma_kantam'>$planet</span>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						else{
							$outArray[$house] = $planet . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
						}
					}
				}
				
			}
		}
	}
	
	if(isset($outArray[$RK_house])){
		$outArray[$RK_house] = str_replace("R.K","ரா.கா",$outArray[$RK_house]);
	}
	
	return $outArray;
}

function sortDegree_transit($inArray) // new function
{
	global $planetDeg_transit,$mandhi_degree_transit,$g_rasi_boy;
	
	$outArray=array(null,null,null,null,null,null,null,null,null,null,null,null);
	
	foreach ($planetDeg_transit as $planet => $deg) {
		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg_transit[$planet],$inArray[$house]);
			
			if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
					//$outArray[$house] = $outArray[$house] . " " . "<span style='color:red'>$planet</span>" . "-". DECtoDMS($planetDeg_transit[$planet]);
					$outArray[$house] = $outArray[$house] . " " . "<span class='retro'>$planet</span>";
				}
				else{
					//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg_transit[$planet];
					
					if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
						//$outArray[$house] = $outArray[$house] . " " . "<span class='lagna'>$planet</span>" . "-". DECtoDMS($planetDeg_transit[$planet]);
						$outArray[$house] = $outArray[$house] . " " . "<span class='lagna'>$planet</span>";
					}
					elseif(strpos($planet,'மா')>0 || strpos($planet,"மா")===0){
						//$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>$planet</span>" . "-". DECtoDMS($planetDeg_transit[$planet]);
						$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>$planet</span>";
					}
					elseif(strpos($planet,'சந்')>0 || strpos($planet,"சந்")===0){
						//$outArray[$house] = $outArray[$house] . " " . "<span class='moon'>$planet</span>" . "-". DECtoDMS($planetDeg_transit[$planet]);
						$outArray[$house] = $outArray[$house] . " " . "<span class='moon'>$planet</span>";
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". DECtoDMS($planetDeg_transit[$planet]);
						$outArray[$house] = $outArray[$house] . " " . $planet;
					}
				}
			}
		}
	}
	
	return $outArray;
}

function getRasi($in_house)
{
	$rasi_arr = ["மேஷம்", "ரிஷபம்", "மிதுனம்", "கடகம்", "சிம்மம்", "கன்னி", "துலாம்", "விருச்சிகம்", "தனுசு", "மகரம்", "கும்பம்", "மீனம்"];
	
	return $rasi_arr[$in_house];
}

function updateRahuEmma(array $inside, $weekday, $sun_degree, $lang)
{
	global $DEBUG, $planetDeg; //$planetDeg is global array
	
	if($DEBUG==1) {
		echo "<BR>updateRahuEmma before update</BR>";
		var_dump($planets);
		echo "<BR>updateRahuEmma before update</BR>";
	}
	
	if(strcmp($lang,"Tamil")===0){
		
		switch($weekday)
		{
			case 0: //sun
				$inside[0] = $inside[0] . "R.K" . "-" . $sun_degree; //mesham
				$inside[5] = $inside[5] . "எ.க" . "-" . $sun_degree; //kanni
				break;
			case 1: //mon
				$inside[9] = $inside[9] . "R.K" . "-" . $sun_degree; //makaram
				$inside[6] = $inside[6] . "எ.க" . "-" . $sun_degree; //thulam
				break;
			case 2: //tue
				$inside[2] = $inside[2] . "R.K" . "-" . $sun_degree; //mithunam
				$inside[8] = $inside[8] . "எ.க" . "-" . $sun_degree; //thanusu
				break;
			case 3: //wed
				$inside[5] = $inside[5] . "R.K" . "-" . $sun_degree; //kanni
				$inside[9] = $inside[9] . "எ.க" . "-" . $sun_degree; //makaram
				break;
			case 4: //thu
				$inside[3] = $inside[3] . "R.K" . "-" . $sun_degree; //katakam
				$inside[11] = $inside[11] . "எ.க" . "-" . $sun_degree; //meenam
				break;
			case 5: //fri
				$inside[6] = $inside[6] . "R.K" . "-" . $sun_degree; //thulam
				$inside[2] = $inside[2] . "எ.க" . "-" . $sun_degree; //mithunam
				break;
			case 6: //sat
				$inside[8] = $inside[8] . "R.K" . "-" . $sun_degree; //thanusu
				$inside[3] = $inside[3] . "எ.க" . "-" . $sun_degree; //katakam
				break;
			default: //none
				break;
		}
		
		$planetDeg["R.K"]=$sun_degree;
		$planetDeg["எ.க"]=$sun_degree;
		
		asort($planetDeg);
	}
	elseif(strpos($lang,"English")===0){
		
	}
	
	if($DEBUG==1) {
		
		echo "<BR>updateRahuEmma after update</BR>";
		var_dump($inside);
	}
	
	return $inside;
}

//Used in matching chart, $inner=true inside planets style (girl), false outside/transit style(boy)
function calculateVimDasaBalance($in_moon_degree, $in_moon_house, $in_birth_date, $in_birth_time, $nak, $nak_pad_idx, $inner) 
{
	global $moon_house,$dasa_balance,$birth_dasa_athipathi,$g_dasaBalance,$birth_dasa_athipathi_boy,$dasa_balance_boy;
	
	$sun_dasa_period = 6;
	$ket_dasa_period = 7;
	$ven_dasa_period = 20;
	$moo_dasa_period = 10;
	$mar_dasa_period = 7;
	$rah_dasa_period = 18;
	$jup_dasa_period = 16;
	$sat_dasa_period = 19;
	$mer_dasa_period = 17;
	
	$one_pada_minutes = 800;
	$one_pada_degree = 3.33333333;
	
	$nak_padam="";
	
	$dasa_lord_eng="";
	$dasa_lord_tam="";
	
	$nak_padam = getDasaAthipathi($nak);
	
	if($inner == true){
		$birth_dasa_athipathi = $nak_padam[0];
	}
	else{
		$birth_dasa_athipathi_boy = $nak_padam[0];
	}
	
	
	switch($nak_padam[0]){
		case "ket":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $ket_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra	

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Kethu';
			$dasa_lord_tam = 'கேது';
			
			break;
		case "ven":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $ven_dasa_period/$one_pada_minutes; // calculation balance for venus by multiplying the remining with venus total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Venus';
			$dasa_lord_tam = 'சுக்ர';
			
			break;
		case "sun":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $sun_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Sun';
			$dasa_lord_tam = 'சூரிய';
			
			break;
		case "moo":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $moo_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Moon';
			$dasa_lord_tam = 'சந்திர';
			
			break;
		case "mar":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $mar_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Mars';
			$dasa_lord_tam = 'செவ்வாய்';
			
			break;
		case "rah":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $rah_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Rahu';
			$dasa_lord_tam = 'ராகு';
			
			break;
		case "jup":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $jup_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Junpiter';
			$dasa_lord_tam = 'குரு';
		
			break;
		case "sat":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $sat_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Saturn';
			$dasa_lord_tam = 'சனி';
			
			break;
		case "mer":
			$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $mer_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;

			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			break;
	}
	
	if($inner == true){
		$class_name = 'divTableCell';
		$g_dasaBalance[0] = $dasa_lord_tam . " தசை<BR>" . $years . " வருடம்<BR>". $months . " மாதம்<BR>" . $days . " நாள்";
	}
	else{
		$class_name = 'divTableOuterCell';
		$g_dasaBalance[1] = $dasa_lord_tam . " தசை<BR>" . $years . " வருடம்<BR>". $months . " மாதம்<BR>" . $days . " நாள் ";
	}
	
	/*
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='$class_name' colspan=3 style='height:30px;'>";
					//echo "<H6 style='font-weight:bold'>$dasa_lord_eng Dasa balance/$dasa_lord_tam தசை இருப்பு</H6>";
					echo "<H6 style='font-weight:bold'>$dasa_lord_tam தசை இருப்பு</H6>";
					echo "</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			//echo "<div class='divPanchang'>$years years $months months $days days</div>";
			echo "<div class='divPanchang'>$years வருடம் $months மாதம் $days நாள் </div>";
		echo "</div>";
	echo "</div></center>";
	*/
}

function calculateCurrentDasaBukthi($inner)
{
	global $current_dasa_bukthi, $current_dasa_bukthi_boy, $g_dasaCurrent;
	
	if($inner == true){
		$g_dasaCurrent[0] = $current_dasa_bukthi['dasa'] . "->" . $current_dasa_bukthi['bukthi'] . "<BR>" . $current_dasa_bukthi['start_date'] . " to <BR>" . $current_dasa_bukthi['end_date'];
		$class_name = 'divTableCell';
	}
	else{
		$g_dasaCurrent[1] = $current_dasa_bukthi_boy['dasa'] . "->" . $current_dasa_bukthi_boy['bukthi'] . "<BR>" . $current_dasa_bukthi_boy['start_date'] . " to <BR>" . $current_dasa_bukthi_boy['end_date'];
		$class_name = 'divTableOuterCell';
	}
	
/*
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
				echo "<div class='divTableRow'>";
					echo "<div class='$class_name' style='width:50%'>";
						//echo "<H6 style='font-weight:bold'>Current Dasa/Bukthi</H6>";
						echo "<H6 style='font-weight:bold'>நடப்பு தசா/புக்தி</H6>";
					echo "</div>";
					echo "<div class='$class_name' style='width:50%'>";
						echo "<H6 style='font-weight:bold'>" . $current_dasa_bukthi['dasa'] . "->" . $current_dasa_bukthi['bukthi'] . "</H6>";
						echo "<H6 style='font-weight:bold'>" . $current_dasa_bukthi['start_date'] . " - " . $current_dasa_bukthi['end_date'] . "</H6>";
						echo "</div>";
				echo "</div>";
			echo "</div></center>";
*/
}

function calculateVimBukthi($curr_dasa_lord, $bukthi_lord, $bukthi_start_date, $dasa_end_date)
{
	global $chart_date,$current_dasa_bukthi, $g_bukthi_arr, $g_current_dasa_lord;
	
	$bukthi_lord_arr = array('ket'=>7,'ven'=>20,'sun'=>6,'moo'=>10,'mar'=>7,'rah'=>18,'jup'=>16,'sat'=>19,'mer'=>17);
	$bukthi_lord_order = array('ket','ven','sun','moo','mar','rah','jup','sat','mer');
	$bukthi_lord_order_tamil = array('கே','சுக்','சூரி','சந்','செ','ரா','குரு','சனி','புத');
	
	$curr_bukthi_start_date = $bukthi_start_date;
	
	$current_date = date('d-m-Y');
	
	for($i=0; $i<9; $i++)
	{
		if($i<$curr_dasa_lord){
			continue;
		}
		else{
			$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
			
			$curr_bukthi_period = round($curr_bukthi_period * 365);
			
			$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' days'));
			
			if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
				
				$current_dasa_bukthi['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi['end_date'] = $curr_bukthi_end_date;
				
				$g_current_dasa_lord = $bukthi_lord_order_tamil[$curr_dasa_lord];
			}
			elseif(strtotime($chart_date) >= strtotime($curr_bukthi_start_date) and strtotime($chart_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			}
			else{
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			}
			
			$g_bukthi_arr[$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]] = array($bukthi_lord_order_tamil[$i],$curr_bukthi_start_date,$curr_bukthi_end_date);
			
			//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
			$curr_bukthi_start_date = $curr_bukthi_end_date;
		}
	}
	
	//to calculate bukthi for planets previous to birth dasa
	for($i=0; $i<$curr_dasa_lord; $i++)
	{
		$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
		$curr_bukthi_period = round($curr_bukthi_period * 365);
		$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' days'));
		
		if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					if($i==$curr_dasa_lord-1){
						echo "<div class='divCurrentBukthi'>$dasa_end_date</div>";
					}
					else{
						echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
					}
				echo "</div>";
				
				$current_dasa_bukthi['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi['end_date'] = $curr_bukthi_end_date;
		}
		elseif(strtotime($chart_date) >= strtotime($curr_bukthi_start_date) and strtotime($chart_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					if($i==$curr_dasa_lord-1){
						echo "<div class='divBirthBukthi'>$dasa_end_date</div>";
					}
					else{
						echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
					}
				echo "</div>";
		}
		else{
			echo "<div class='divTableRow'>";
				echo "<div class='divBukthiHeading'  onClick='displayAnthara(\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\",\"$curr_bukthi_start_date\", \"$curr_bukthi_end_date\");'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
				echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
				if($i==$curr_dasa_lord-1){
					echo "<div class='divBukthi'>$dasa_end_date</div>";
				}
				else{
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				}
			echo "</div>";
		}
		
		$g_bukthi_arr[$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]] = array($bukthi_lord_order_tamil[$i],$curr_bukthi_start_date,$curr_bukthi_end_date);
		
		//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
		$curr_bukthi_start_date = $curr_bukthi_end_date;
	}
}

function calculateVimBukthi_boy($curr_dasa_lord, $bukthi_lord, $bukthi_start_date, $dasa_end_date)
{
	global $transit_date,$current_dasa_bukthi_boy;
	
	$bukthi_lord_arr = array('ket'=>7,'ven'=>20,'sun'=>6,'moo'=>10,'mar'=>7,'rah'=>18,'jup'=>16,'sat'=>19,'mer'=>17);
	$bukthi_lord_order = array('ket','ven','sun','moo','mar','rah','jup','sat','mer');
	$bukthi_lord_order_tamil = array('கே','சுக்','சூரி','சந்','செ','ரா','குரு','சனி','புத');
	
	$curr_bukthi_start_date = $bukthi_start_date;
	
	$current_date = date('d-m-Y');
	
	for($i=0; $i<9; $i++)
	{
		if($i<$curr_dasa_lord){
			continue;
		}
		else{
			$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
			
			$curr_bukthi_period = round($curr_bukthi_period * 365);
			
			$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' days'));
			
			if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
				
				$current_dasa_bukthi_boy['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi_boy['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi_boy['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi_boy['end_date'] = $curr_bukthi_end_date;
			}
			elseif(strtotime($transit_date) >= strtotime($curr_bukthi_start_date) and strtotime($transit_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
		}
			else{
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			}
			
			//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
			$curr_bukthi_start_date = $curr_bukthi_end_date;
		}
	}
	
	//to calculate bukthi for planets previous to birth dasa
	for($i=0; $i<$curr_dasa_lord; $i++)
	{
		$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
		$curr_bukthi_period = round($curr_bukthi_period * 365);
		$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' days'));
		
		if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					if($i==$curr_dasa_lord-1){
						echo "<div class='divCurrentBukthi'>$dasa_end_date</div>";
					}
					else{
						echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
					}
				echo "</div>";
				
				$current_dasa_bukthi_boy['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi_boy['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi_boy['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi_boy['end_date'] = $curr_bukthi_end_date;
		}
		elseif(strtotime($transit_date) >= strtotime($curr_bukthi_start_date) and strtotime($transit_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow'>";
					echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					if($i==$curr_dasa_lord-1){
						echo "<div class='divBirthBukthi'>$dasa_end_date</div>";
					}
					else{
						echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
					}
				echo "</div>";
		}
		else{
			echo "<div class='divTableRow'>";
				echo "<div class='divBukthiHeading'>".$bukthi_lord_order_tamil[$curr_dasa_lord]."&#8594;".$bukthi_lord_order_tamil[$i]."</div>";
				echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
				if($i==$curr_dasa_lord-1){
					echo "<div class='divBukthi'>$dasa_end_date</div>";
				}
				else{
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				}
			echo "</div>";
		}
		
		//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
		$curr_bukthi_start_date = $curr_bukthi_end_date;
	}
}

function calculateVimDasa()
{
	global $dasa_balance, $birth_dasa_athipathi, $chart_date, $g_dasa_arr;
	
	$dasa_lord_arr = array('ket'=>7,'ven'=>20,'sun'=>6,'moo'=>10,'mar'=>7,'rah'=>18,'jup'=>16,'sat'=>19,'mer'=>17);
	$dasa_lord_order = array('ket','ven','sun','moo','mar','rah','jup','sat','mer');
	$dasa_lord_order_tamil = array('கே','சுக்','சூரி','சந்','செ','ரா','குரு','சனி','புத');
	
	//$curr_dasa_start_date = date('d-m-Y',strtotime($chart_date. '+ '.$dasa_balance[0].' years + '.$dasa_balance[1].' months '.$dasa_balance[2].' days'));
	$birth_dasa_start_date = date('d-m-Y',strtotime($chart_date. '- '.$dasa_lord_arr[$birth_dasa_athipathi].' years'));
	
	$birth_dasa_start_date = date('d-m-Y',strtotime($birth_dasa_start_date. '+ '.$dasa_balance[0].' years + '.$dasa_balance[1].' months '.$dasa_balance[2].' days'));
	
	$curr_dasa_start_date = $birth_dasa_start_date;
	$dasa_start_date = $curr_dasa_start_date;
	
	//$curr_dasa_lord = array_search($birth_dasa_athipathi,$dasa_lord_order)+1;
	$curr_dasa_lord = array_search($birth_dasa_athipathi,$dasa_lord_order);
	
	echo "<center><div class='divTable blueTable' style='height:32px;width:360px;display:none;' id='DasaMatable'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
				echo "<H6 style='font-weight:bold'>தசாபுக்தி</H6>";
				echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' style='height:100%;width:360px;display:none;' id='BukthiMatable'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading'>தசா/புக்தி அதிபதி</div>";
			echo "<div class='divDasaHeading'>Start</div>";
			echo "<div class='divDasaHeading'>End</div>";
		echo "</div>";
	
	for($i=0; $i<9; $i++)
	{
		if($i<$curr_dasa_lord){
			continue;
		}
		else{
			$dasa_lord = $dasa_lord_order[$i];
			$curr_dasa_end_date = date('d-m-Y',strtotime($curr_dasa_start_date. '+ '.$dasa_lord_arr[$dasa_lord].' years')); 
			
			$dasa_start_date = $curr_dasa_start_date;
			
			echo "<div class='divTableRow'>";
				echo "<div class='divDasaHeading'>".$dasa_lord_order_tamil[$i]."</div>";
				echo "<div class='divDasa'>$curr_dasa_start_date</div>";
				echo "<div class='divDasa'>$curr_dasa_end_date</div>";
			echo "</div>";
			
			$g_dasa_arr[$dasa_lord_order_tamil[$i]] = array($curr_dasa_start_date,$curr_dasa_end_date);
			
			$curr_dasa_start_date=date('d-m-Y',strtotime($curr_dasa_end_date. '+ 1 day'));
			
			calculateVimBukthi($i,$dasa_lord,$dasa_start_date,$curr_dasa_end_date);
		}
	}
	
	//to calculate for dasa for planets previous to birth dasa
	for($i=0; $i<$curr_dasa_lord-1; $i++)
	{
		$dasa_start_date = $curr_dasa_start_date;
		
		$dasa_lord = $dasa_lord_order[$i];
		$curr_dasa_end_date = date('d-m-Y',strtotime($curr_dasa_start_date. '+ '.$dasa_lord_arr[$dasa_lord].' years'));
		
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading'>".$dasa_lord_order_tamil[$i]."</div>";
			echo "<div class='divDasa'>$curr_dasa_start_date</div>";
			echo "<div class='divDasa'>$curr_dasa_end_date</div>";
		echo "</div>";
		
		$g_dasa_arr[$dasa_lord_order_tamil[$i]] = array($curr_dasa_start_date,$curr_dasa_end_date);
		
		$curr_dasa_start_date=date('d-m-Y',strtotime($curr_dasa_end_date. '+ 1 day'));
		
		calculateVimBukthi($i,$dasa_lord,$dasa_start_date,$curr_dasa_end_date);
	}
	
	echo "<div class='divTableRow'>";
		echo "<BR>";
	echo "</div>";
	
	echo "</div></center>";
}

function calculateVimDasa_boy()
{
	global $dasa_balance_boy, $birth_dasa_athipathi_boy, $transit_date;
	
	$dasa_lord_arr = array('ket'=>7,'ven'=>20,'sun'=>6,'moo'=>10,'mar'=>7,'rah'=>18,'jup'=>16,'sat'=>19,'mer'=>17);
	$dasa_lord_order = array('ket','ven','sun','moo','mar','rah','jup','sat','mer');
	$dasa_lord_order_tamil = array('கே','சுக்','சூரி','சந்','செ','ரா','குரு','சனி','புத');
	
	//$curr_dasa_start_date = date('d-m-Y',strtotime($chart_date. '+ '.$dasa_balance[0].' years + '.$dasa_balance[1].' months '.$dasa_balance[2].' days'));
	$birth_dasa_start_date = date('d-m-Y',strtotime($transit_date. '- '.$dasa_lord_arr[$birth_dasa_athipathi_boy].' years'));
	$birth_dasa_start_date = date('d-m-Y',strtotime($birth_dasa_start_date. '+ '.$dasa_balance_boy[0].' years + '.$dasa_balance_boy[1].' months '.$dasa_balance_boy[2].' days'));
	$curr_dasa_start_date = $birth_dasa_start_date;
	$dasa_start_date = $curr_dasa_start_date;
	
	//$curr_dasa_lord = array_search($birth_dasa_athipathi,$dasa_lord_order)+1;
	$curr_dasa_lord = array_search($birth_dasa_athipathi_boy,$dasa_lord_order);
	
	echo "<center><div class='divTable blueTable' style='height:32px;width:360px;display:none;' id='DasaMatable_boy'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableOuterCell' colspan=3>";
				echo "<H6 style='font-weight:bold'>Vim DasaBukthi/தசாபுக்தி</H6>";
				echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' style='height:100%;width:360px;display:none;' id='BukthiMatable_boy'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading'>Dasa/Bukthi Lord</div>";
			echo "<div class='divDasaHeading'>Start</div>";
			echo "<div class='divDasaHeading'>End</div>";
		echo "</div>";
	
	for($i=0; $i<9; $i++)
	{
		if($i<$curr_dasa_lord){
			continue;
		}
		else{
			$dasa_lord = $dasa_lord_order[$i];
			$curr_dasa_end_date = date('d-m-Y',strtotime($curr_dasa_start_date. '+ '.$dasa_lord_arr[$dasa_lord].' years')); 
			
			$dasa_start_date = $curr_dasa_start_date;
			
			echo "<div class='divTableRow'>";
				echo "<div class='divDasaHeading'>".$dasa_lord_order_tamil[$i]."</div>";
				echo "<div class='divDasa'>$curr_dasa_start_date</div>";
				echo "<div class='divDasa'>$curr_dasa_end_date</div>";
			echo "</div>";
			
			$curr_dasa_start_date=date('d-m-Y',strtotime($curr_dasa_end_date. '+ 1 day'));
			
			calculateVimBukthi_boy($i,$dasa_lord,$dasa_start_date,$curr_dasa_end_date);
		}
	}
	
	//to calculate for dasa for planets previous to birth dasa
	for($i=0; $i<$curr_dasa_lord-1; $i++)
	{
		$dasa_start_date = $curr_dasa_start_date;
		
		$dasa_lord = $dasa_lord_order[$i];
		$curr_dasa_end_date = date('d-m-Y',strtotime($curr_dasa_start_date. '+ '.$dasa_lord_arr[$dasa_lord].' years'));
		
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading'>".$dasa_lord_order_tamil[$i]."</div>";
			echo "<div class='divDasa'>$curr_dasa_start_date</div>";
			echo "<div class='divDasa'>$curr_dasa_end_date</div>";
		echo "</div>";
		
		$curr_dasa_start_date=date('d-m-Y',strtotime($curr_dasa_end_date. '+ 1 day'));
		
		calculateVimBukthi_boy($i,$dasa_lord,$dasa_start_date,$curr_dasa_end_date);
	}
	
	echo "<div class='divTableRow'>";
	echo "</div>";
	
	echo "</div></center>";
}

function displayStarLard()
{
	global $planetDeg,$sw_planet, $planet_starlord, $g_planetKaranaLord;
	
	$planet_starlord = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
	$nak_lord = array("dummy","கே","கே","கே","கே",
						"சுக்","சுக்","சுக்","சுக்",
						"சூரி","சூரி","சூரி","சூரி",
						"சந்","சந்","சந்","சந்",
						"செ","செ","செ","செ",
						"ரா","ரா","ரா","ரா",
						"குரு","குரு","குரு","குரு",
						"சனி","சனி","சனி","சனி",
						"புத","புத","புத","புத",
						"கே","கே","கே","கே",
						"சுக்","சுக்","சுக்","சுக்",
						"சூரி","சூரி","சூரி","சூரி",
						"சந்","சந்","சந்","சந்",
						"செ","செ","செ","செ",
						"ரா","ரா","ரா","ரா",
						"குரு","குரு","குரு","குரு",
						"சனி","சனி","சனி","சனி",
						"புத","புத","புத","புத",
						"கே","கே","கே","கே",
						"சுக்","சுக்","சுக்","சுக்",
						"சூரி","சூரி","சூரி","சூரி",
						"சந்","சந்","சந்","சந்",
						"செ","செ","செ","செ",
						"ரா","ரா","ரா","ரா",
						"குரு","குரு","குரு","குரு",
						"சனி","சனி","சனி","சனி",
						"புத","புத","புத","புத");
	
	foreach($planetDeg as $planet => $deg)
	{
		foreach($sw_planet as $house => $planets)
		{
			//starlord calculation
			$full_planet_deg = ($house * 30 + $deg);
			$full_planet_deg_ragu = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
			$starlord_idx = ceil($full_planet_deg/3.333333);
			$starlord_idx_ragu = ceil($full_planet_deg_ragu/3.333333);
			
			if(strpos($planets,$planet)===0 || strpos($planets,$planet)>0){
				
				//$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord[$planet][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
				
				/*if(!(strpos($planets,"ரா.கா")===0 || strpos($planets,"ரா.கா")>0) && (strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0)){
					//echo $house. "-" . $planets ."<br>";
					//echo $planet_starlord[$planet][1];
					
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
					$planet_starlord[$planet][3] = $deg;
					
					echo "|".$planet_starlord[$planet][2]."-".$planet."|";
					echo "|".$planet_starlord[$planet][3]."-".$planet."|";
				}*/
			}
			
			if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				//$planet_starlord["ரா"][0] = $nak_array[$starlord_idx_ragu];
				$planet_starlord["ரா"][0] = explode('/',$nak_array[$starlord_idx_ragu])[1]; //display tamil star name
				//$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
				$planet_starlord["கே"][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu]."(புஷ்)";
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
			}
		}
	}
	
	//calculate planaet karana's
	calculateKaranamAllPlanets();

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable' style='height:32px;width:360px;display:none'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>நட் சாரம்</H6>";
					echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable' style='height:100%;width:360px;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>Planet</div>";
				echo "<div class='divTableCell'>Star Padam</div>";
				echo "<div class='divTableCell'>Star lord</div>";
				echo "<div class='divTableCell'>Degree</div>";
				//echo "<div class='divPanchangHeading'>Rasi degree</div>";
				echo "<div class='divTableCell'>Karanam</div>";
		echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{
		echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>$planet</div>";
				echo "<div class='divPanchang'>$data[0]</div>";
				echo "<div class='divPanchang'>$data[1]</div>";
				echo "<div class='divPanchang'>" . DECtoDMS($data[2]) . "</div>";
				//echo "<div class='divPanchang'>" . DECtoDMS($data[3]) . "</div>";
				echo "<div class='divPanchang'>" . $g_planetKaranaLord[$planet] . "</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
	
}

function displayStarLard_boy()
{
	global $planetDeg_transit,$tr_sw_planet;
	
	$planet_starlord = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
	$nak_lord = array("dummy","கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத",
						"கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத",
						"கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத");
	
	
	foreach($planetDeg_transit as $planet => $deg)
	{
		foreach($tr_sw_planet as $house => $planets)
		{
			if(strpos($planets,$planet)===0 || strpos($planets,$planet)>0){
				
				//starlord calculation
				$full_planet_deg = ($house * 30 + $deg);
				$starlord_idx = ceil($full_planet_deg/3.333333);
				
				$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
			}
			
			if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				//starlord calculation
				$full_planet_deg = ($house * 30 + $deg);
				$starlord_idx = ceil($full_planet_deg/3.333333);
				
				$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
			}
		}
	}

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable_boy' style='height:32px;width:360px;display:none'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableOuterCell' colspan=3>";
					echo "<H6 style='font-weight:bold'>Starlord/நட் சாரம்</H6>";
					echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable_boy' style='height:100%;width:360px;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Planet</div>";
				echo "<div class='divPanchangHeading'>Star Padam</div>";
				echo "<div class='divPanchangHeading'>Star lord</div>";
				echo "<div class='divPanchangHeading'>Full degree</div>";
				echo "<div class='divPanchangHeading'>Rasi degree</div>";
		echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{
		echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>$planet</div>";
				echo "<div class='divPanchang'>$data[0]</div>";
				echo "<div class='divPanchang'>$data[1]</div>";
				echo "<div class='divPanchang'>" . DECtoDMS($data[2]) . "</div>";
				echo "<div class='divPanchang'>" . DECtoDMS($data[3]) . "</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
	
}

function getDasaAthipathi($nak)
{
	global $panchang;
	
	$nak_lord = array("அசுவினி" => "ket", 
						"பரணி" => "ven",
						"கிருத்திகை" => "sun",
						"ரோகிணி" => "moo",
						"மிருகசிரீஷம்" => "mar",
						"திருவாதிரை" => "rah",
						"புனர்பூசம்" => "jup",
						"பூசம்" => "sat",
						"ஆயில்யம்" => "mer",
						"மகம்" => "ket",
						"பூரம்" => "ven",
						"உத்திரம்" => "sun",
						"ஹஸ்தம்" => "moo",
						"சித்திரை" => "mar",
						"சுவாதி" => "rah",
						"விசாகம்" => "jup",
						"அனுஷம்" => "sat",
						"கேட்டை" => "mer",
						"மூலம்" => "ket",
						"பூராடம்" => "ven",
						"உத்திராடம்" => "sun",
						"திருவோணம்" => "moo",
						"அவிட்டம்" => "mar",
						"சதயம்" => "rah",
						"பூரட்டாதி" => "jup",
						"உத்திரட்டாதி" => "sat",
						"ரேவதி" => "mer");
						
	$naksh = explode(' ',explode("/",$nak)[1])[0];
	$padam = explode(' ',explode("/",$nak)[1])[1];
	
	return array($nak_lord[$naksh],$padam);
}

function getPanchaPakshi()
{
	global $panchang;
	
	$pancha_pakshi = array("அசுவினி" =>array("வல்லூரு", "மயில்"),
		"பரணி" => array("வல்லூரு","மயில்"),
		"கிருத்திகை" => array("வல்லூரு","மயில்"),
		"ரோகிணி" => array("வல்லூரு","மயில்"),
		"மிருகசிரீஷம்" => array("வல்லூரு","மயில்"),
		"திருவாதிரை" => array("ஆந்தை","கோழி"),
		"புனர்பூசம்" => array("ஆந்தை","கோழி"),
		"பூசம்" => array("ஆந்தை","கோழி"),
		"ஆயில்யம்" => array("ஆந்தை","கோழி"),
		"மகம்" => array("ஆந்தை","கோழி"),
		"பூரம்" => array("ஆந்தை","கோழி"),
		"உத்திரம்" => array("காகம்","காகம்"),
		"ஹஸ்தம்" => array("காகம்","காகம்"),
		"சித்திரை" => array("காகம்","காகம்"),
		"சுவாதி" => array("காகம்","காகம்"),
		"விசாகம்" => array("காகம்","காகம்"),
		"அனுஷம்" => array("கோழி","ஆந்தை"),
		"கேட்டை" => array("கோழி","ஆந்தை"),
		"மூலம்" => array("கோழி","ஆந்தை"),
		"பூராடம்" => array("கோழி","ஆந்தை"),
		"உத்திராடம்" => array("கோழி","ஆந்தை"),
		"திருவோணம்" => array("மயில்","வல்லூரு"),
		"அவிட்டம்" => array("மயில்","வல்லூரு"),
		"சதயம்" => array("மயில்","வல்லூரு"),
		"பூரட்டாதி" => array("மயில்","வல்லூரு"),
		"உத்திரட்டாதி" => array("மயில்","வல்லூரு"),
		"ரேவதி" => array("மயில்","வல்லூரு"));
		
		$nak = explode(' ',explode("/",$panchang['nak'])[1])[0];
		
		$paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
		
		if(strpos("வளர்",$paksham)===0){
			return $pancha_pakshi[$nak][0];
		}
		else{
			return $pancha_pakshi[$nak][1];
		}
		
}


function displayPanchang($date=null,$time=null,$place=null){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $lagna_house;
	
	calculatePanchang();
	
	//display panchang table
	echo "<div class='divTable blueTable' style='height:40px;width:320px;' id='panchangBody'>"; 
		if($date != null){
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Date</div>";
				echo "<div class='divPanchang'>$date</div>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Time</div>";
				echo "<div class='divPanchang'>$time</div>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Place</div>";
				echo "<div class='divPanchang'>$place</div>";
			echo "</div>";
		}
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['vaaram'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['nak'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>திதி</div>";
			$thithi_tamil = explode('[',explode('/',$panchang['thithi'])[1])[0] . '[' . explode('/',$panchang['thithi'])[2];
			echo "<div class='divPanchang'>".$thithi_tamil."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			echo "<div class='divPanchang' OnClick='showKaranamDiv()' style='color:blue;cursor:pointer'>".explode('/',$panchang['karan'])[1]." (Click)</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			echo "<div class='divPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>லக்கினம்</div>";
			echo "<div class='divPanchang'>".getRasi($lagna_house-1)."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>ராசி</div>";
			echo "<div class='divPanchang'>".getRasi($moon_house-1)."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய உதயம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய அஸ்தமனம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunset)[1]."</div>";
		echo "</div>";
	echo "</div>";
}

function displayPanchang_jamakol(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $lagna_house;
	
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;' id='panchangBody'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['vaaram'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['nak'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>திதி</div>";
			$thithi_tamil = explode('[',explode('/',$panchang['thithi'])[1])[0] . '[' . explode('/',$panchang['thithi'])[2];
			echo "<div class='divPanchang'>".$thithi_tamil."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['karan'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			echo "<div class='divPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>லக்கினம்</div>";
			echo "<div class='divPanchang'>".getRasi($lagna_house-1)."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>ராசி</div>";
			echo "<div class='divPanchang'>".getRasi($moon_house-1)."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய உதயம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய அஸ்தமனம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunset)[1]."</div>";
		echo "</div>";
	echo "</div></center>";
	
	//displayThithiSoonyam($panchang["thithi"]);
	
}

function getMoonLongitude($timestamp){
	global $lon,$lat,$h_sys,$libPath,$offset;

	$date = getUTCdate(strtotime($timestamp),$offset);
	$time = getUTCtime(strtotime($timestamp),$offset);
	
	exec ("swetest64 -edir$libPath -b$date -ut$time -p1 -n1 -sid1 -eswe -fl -head", $output); //get moon degree
	
	var_dump($output);
	
	echo ("swetest64 -edir$libPath -b$date -ut$time -p1 -n1 -sid1 -eswe -fl -head");
}

function calculatePanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $chart_time, $chart_date, $moon_degree_exact,$TZ,$full_moon_deg,$full_sun_deg;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	$karan_sukla = array(array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"));
	$karan_krishna = array(array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Sakuni/சகுனி"),
						array("Chatuspada/சதுஷ்பாத","Naga/நாகவ"));
	
	$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
					"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333333333333);
	
	debug ("nak_idx=$nak_idx");
	
	//Get nak end time
	$nak_end_time = findNakEndTime($nak_idx);
	
	debug("NAK_END_TIME=$nak_end_time");
	
	/*************Calculate nak end time*****************
	$d = fillInterPolArray();

	//$nak_idx is pada idx, so dividing it by 4 to get nak idx. Multiply by 360/27 to get ending degree of the nak
	$nak_end_degree = ceil($nak_idx/4)*360/27;
	$approx_nak_end = calc_invinter($d,5,$nak_end_degree);
	
	debug ("approx_nak_end=$approx_nak_end");
	
	$approx_nak_end_seconds = round($approx_nak_end * 60 * 60);
	
	//$nak_end = (strtotime($today_sunrise) - $jd + $approx_nak_end) * 24 + strtotime($TZ);
	$nak_end = strtotime("+$approx_nak_end_seconds seconds",strtotime($today_sunrise));

	debug ("approx_nak_end_seconds=$approx_nak_end_seconds");
	
	//$nak_end_time = date('H:i:s',strtotime('+330 minutes',$nak_end));
	//$nak_end_time = date('H:i:s',calc_invinter($d,5,$nak_idx));
	$nak_end_time = date('d-m-Y H:i:s',$nak_end);
	debug ("<BR>TEST interpolation".$nak_end_time);
********************************************************/
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$nak_pad_idx = $nak_idx;
	
	//thithi calculation
	$full_sun_deg = ($sun_house-1)*30+$sun_degree;
	
	debug("MOON DEGREE (full) =".$full_moon_deg);
	debug("SUN DEGREE =".$sun_degree);
	debug("SUN DEGREE (full) =".$full_sun_deg);
	debug("SUN HOUSE =".$sun_house);
	debug("MOON HOUSE =".$moon_house);
	debug("SUN-MOON house count =".countHouses($sun_house,$moon_house));
	
	debug("MOON DEGREE (after) =".$full_moon_deg);
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			debug("IDX=".(floor($idx)));
			
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx];
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx];
			}
			debug("MOON-SUN DEGREE=".($full_moon_deg - $full_sun_deg));//testing
			
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor(abs($idx));
			debug("IDX=".(floor(abs($idx))));
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx];
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx];
			}
			debug("Else MOON-SUN DEGREE=".($full_moon_deg - $full_sun_deg));//testing
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
			
		}
		
		debug(__LINE__ . ":". "MOON-SUN DEGREE=".($full_moon_deg - $full_sun_deg));//testing
	}
	
	if(countHouses($sun_house,$moon_house)<=7){
		$panchang["thithi"] = $panchang["thithi"]."[shukla/வளர்]";
	}
	else{
		$panchang["thithi"] = $panchang["thithi"]."[krishna/தேய்]";
	}
	
	//Get thithi end time
	$thithi_end_time = findThithiEndTime($thi_idx);
	debug("THITHI_END_TIME=$thithi_end_time");
	
	//karanam calculation
	$kar = $idx-floor($idx);
	//$kar = $thi_idx-floor($thi_idx);
	
	if($thi_idx < 15){
		
		if($kar < 0.5){
			$panchang["karan"] = $karan_sukla[$thi_idx][0];
		}
		else{
			$panchang["karan"] = $karan_sukla[$thi_idx][1];
		}
		
		debug("THI-IDX=".$thi_idx . "KAR=". $kar . "&&&&&&");
	}
	else{
		if($kar < 0.5){
			$panchang["karan"] = $karan_krishna[$thi_idx-15][0];
		}
		else{
			$panchang["karan"] = $karan_krishna[$thi_idx-15][1];
		}
		
		debug("THI-IDX-15=".($thi_idx-15) . "KAR=". $kar . "&&&&&&");
	}
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$g_yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	
	$panchang["yoga"] = $yoga_arr[$g_yoga_idx];
}

//This displays different buttons like starlord/Dasa/etc. to toggle display. Used in birth chart and matching chart (for girl)
function displayButtons($inner)
{	
	if($inner == true){
		$class_name = 'actionBtn';
		$starlord_function = 'showStarLordTable()';
		$dasa_function = 'showDasaTable()';
	}
	else{
		$class_name = 'actionBtnBoy';
		$starlord_function = 'showStarLordTable_boy()';
		$dasa_function = 'showDasaTable_boy()';
	}

	echo "<center><div class='divTable blueTable' style='height:auto;' id='buttons'>"; 
		echo "<div class='divTableRow'>";
			echo <<<EOL
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="starlord" name="starlord" onclick="$starlord_function">நட் சாரம்</button>
				</div>
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="dasa" name="dasa" onclick="$dasa_function">தசா புக்தி</button>
				</div>
EOL;
		echo "</div>";
	echo "</div></center>";


	if($inner == true){
		displayStarLard();
		calculateVimDasa();
	}
	else{
		displayStarLard_boy();
		calculateVimDasa_boy();
	}
	
}

function displayThithiSoonyam($thithi,$inner=true) //$inner - true for girl in matching chart  false for boy in matching chart
{
	global $g_thithi_soonyam_arr;
	
	$thithi = explode('[',explode("/",$thithi)[1])[0];
	
	$thithi_arr = array(
					"பிரதமை"		=>	 "துலாம், மகரம்",
					"துவிதியை"		=>	 "தனுசு, மீனம்",
					"திருதியை"		=>	 "சிம்மம், மகரம்",
					"சதுர்த்தி"		=>	 "ரிஷபம், கும்பம்",
					"பஞ்சமி"		=>	 "மிதுனம், கன்னி",
					"சஷ்டி"			=>	 "மேஷம், சிம்மம்",
					"சப்தமி"		=>	 "கடகம், தனுசு",
					"அஷ்டமி"		=>	 "மிதுனம், கன்னி",
					"நவமி"			=>	 "சிம்மம், விருச்சிகம்",
					"தசமி"			=>	 "சிம்மம், விருச்சிகம்",
					"ஏகாதசி"		=>	 "தனுசு, மீனம்",
					"துவாதசி"		=>	 "துலாம், மகரம்",
					"திரயோதசி"	=>	 "ரிஷபம், சிம்மம்",
					"சதுர்த்தசி"		=>	 "மிதுனம், கன்னி, தனுசு, மீனம்",
					"அமாவாசை"	=>	"இல்லை",
					"பௌர்ணமி" 	=>	"இல்லை"
					);
					
	if($inner == true){
		$class_name = 'divTableCell';
	}
	else{
		$class_name = 'divTableOuterCell';
	}
	
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='$class_name' colspan=3>";
					echo "<H6 style='font-weight:bold'>திதி சூன்யம் </H6>";
					echo "</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchang'>$thithi_arr[$thithi]</div>";
		echo "</div>";
	echo "</div></center>";
}

//Used in matching chart to fill the thithi soonyam
function calculateThithiSoonyam($thithi_girl,$thithi_boy) 
{
	global $g_thithi_soonyam_matching_arr;
	
	$thithi_girl = explode('[',explode("/",$thithi_girl)[1])[0];
	$thithi_boy = explode('[',explode("/",$thithi_boy)[1])[0];
	
	$thithi_arr = array(
					"பிரதமை"		=>	 "துலாம், மகரம்",
					"துவிதியை"		=>	 "தனுசு, மீனம்",
					"திருதியை"		=>	 "சிம்மம், மகரம்",
					"சதுர்த்தி"		=>	 "ரிஷபம், கும்பம்",
					"பஞ்சமி"		=>	 "மிதுனம், கன்னி",
					"சஷ்டி"			=>	 "மேஷம், சிம்மம்",
					"சப்தமி"		=>	 "கடகம், தனுசு",
					"அஷ்டமி"		=>	 "மிதுனம், கன்னி",
					"நவமி"			=>	 "சிம்மம், விருச்சிகம்",
					"தசமி"			=>	 "சிம்மம், விருச்சிகம்",
					"ஏகாதசி"		=>	 "தனுசு, மீனம்",
					"துவாதசி"		=>	 "துலாம், மகரம்",
					"திரயோதசி"	=>	 "ரிஷபம், சிம்மம்",
					"சதுர்த்தசி"		=>	 "மிதுனம், கன்னி, தனுசு, மீனம்",
					"அமாவாசை"	=>	"இல்லை",
					"பௌர்ணமி" 	=>	"இல்லை"
					);
					
	$g_thithi_soonyam_matching_arr[0] = $thithi_arr[$thithi_girl];
	$g_thithi_soonyam_matching_arr[1] = $thithi_arr[$thithi_boy];
}

//Used in birth chart to fill the thithi soonyam
function calculateBirthThithiSoonyam($thithi) 
{
	global $g_thithi_soonyam_matching_arr;
	
	$thithi = explode('[',explode("/",$thithi)[1])[0];
	
	$thithi_arr = array(
					"பிரதமை"		=>	 "துலாம், மகரம்",
					"துவிதியை"		=>	 "தனுசு, மீனம்",
					"திருதியை"		=>	 "சிம்மம், மகரம்",
					"சதுர்த்தி"		=>	 "ரிஷபம், கும்பம்",
					"பஞ்சமி"		=>	 "மிதுனம், கன்னி",
					"சஷ்டி"			=>	 "மேஷம், சிம்மம்",
					"சப்தமி"		=>	 "கடகம், தனுசு",
					"அஷ்டமி"		=>	 "மிதுனம், கன்னி",
					"நவமி"			=>	 "சிம்மம், விருச்சிகம்",
					"தசமி"			=>	 "சிம்மம், விருச்சிகம்",
					"ஏகாதசி"		=>	 "தனுசு, மீனம்",
					"துவாதசி"		=>	 "துலாம், மகரம்",
					"திரயோதசி"	=>	 "ரிஷபம், சிம்மம்",
					"சதுர்த்தசி"		=>	 "மிதுனம், கன்னி, தனுசு, மீனம்",
					"அமாவாசை"	=>	"இல்லை",
					"பௌர்ணமி" 	=>	"இல்லை"
					);
					
	$g_thithi_soonyam_matching_arr[0] = $thithi_arr[$thithi];
}

function updateThithiSoonyam($thithi,$in_inner=true, $in_jamakol=false) //$inner - true for girl in matching chart  false for boy in matching chart
{	
	global $g_thithi_soonyam_arr, $full_sun_deg, $full_moon_deg,$g_thithi_soonyam_matching_arr;
	$thithi = explode('[',explode("/",$thithi)[1])[0];
	
	$main_thithi_soonyam = 0;
	
	$thithi_arr = array(
					"பிரதமை"	=>	 "துலாம், மகரம்",
					"துவிதியை"	=>	 "தனுசு, மீனம்",
					"திருதியை"	=>	 "சிம்மம், மகரம்",
					"சதுர்த்தி"	=>	 "ரிஷபம், கும்பம்",
					"பஞ்சமி"	=>	 "மிதுனம், கன்னி",
					"சஷ்டி"	=>	 "மேஷம், சிம்மம்",
					"சப்தமி"	=>	 "கடகம், தனுசு",
					"அஷ்டமி"	=>	 "மிதுனம், கன்னி",
					"நவமி"	=>	 "சிம்மம், விருச்சிகம்",
					"தசமி"	=>	 "சிம்மம், விருச்சிகம்",
					"ஏகாதசி"	=>	 "தனுசு, மீனம்",
					"துவாதசி"	=>	 "துலாம், மகரம்",
					"திரயோதசி"	=>	 "ரிஷபம், சிம்மம்",
					"சதுர்த்தசி"	=>	 "மிதுனம், கன்னி, தனுசு, மீனம்",
					"அமாவாசை"	=>	"இல்லை",
					"பௌர்ணமி" 	=>	"இல்லை"
					);
	$thithi_house = array(
					"பிரதமை"	=>	 "7,10",
					"துவிதியை"	=>	 "9,12",
					"திருதியை"	=>	 "5,10",
					"சதுர்த்தி"	=>	 "2,11",
					"பஞ்சமி"	=>	 "3,6",
					"சஷ்டி"	=>	 "1,5",
					"சப்தமி"	=>	 "4,9",
					"அஷ்டமி"	=>	 "3,6",
					"நவமி"	=>	 "5,8",
					"தசமி"	=>	 "5,8",
					"ஏகாதசி"	=>	 "9,12",
					"துவாதசி"	=>	 "7,10",
					"திரயோதசி"	=>	 "2,5",
					"சதுர்த்தசி"	=>	 "3,6,9,12",
					"அமாவாசை"	=>	"-1",
					"பௌர்ணமி" 	=>	"-1"
					);
					
	if($in_inner == true){
		$class_name = 'divTableCell';
	}
	else{
		$class_name = 'divTableOuterCell';
	}
	
	$thithi_soonya_rasi_arr = explode(',',$thithi_house[$thithi]);
	
	//to find exact thithi within the thithi's
	$thithi_deg = ((($full_sun_deg-$full_moon_deg)/12 - floor(($full_sun_deg-$full_moon_deg)/12))) * 12;
	
	if(sizeof($thithi_soonya_rasi_arr)==2){
		if($thithi_deg <= 6){
			$main_thithi_soonyam = 1;
		}
		else{
			$main_thithi_soonyam = 2;
		}
	}
	elseif(sizeof($thithi_soonya_rasi_arr)==4){
		if($thithi_deg > 0 && $thithi_deg <= 3){
			$main_thithi_soonyam = 1;
		}
		elseif($thithi_deg > 3 && $thithi_deg <= 6){
			$main_thithi_soonyam = 2;
		}
		elseif($thithi_deg > 6 && $thithi_deg <= 9){
			$main_thithi_soonyam = 3;
		}
		elseif($thithi_deg > 9 && $thithi_deg <= 12){
			$main_thithi_soonyam = 4;
		}
	}
	
	
	$count=1;
	foreach($thithi_soonya_rasi_arr as $idx => $soonya_house){
		//$g_thithi_soonyam_arr[$soonya_house-1] = '&#x2718;';
		
		if(!$in_jamakol){
			if($count == $main_thithi_soonyam){
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam_main'>தி.சூ</span>";
			}
			else{
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam'>தி.சூ</span>";
			}
		}
		else{
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam'>தி.சூ</span>";
		}
		
		$count=$count+1;
	}
	
	if($main_thithi_soonyam==0){
		$main_thithi_soonyam=1;
	}
	
	
	//updating the background color for the thithi soonyam text in the table below the birth chart
	$temp_arr = explode(',',$thithi_arr[$thithi]);
	
	if($thithi_house[$thithi] > 0)
	{
		$temp_arr[$main_thithi_soonyam-1] = "<span class='thithi_soonyam_main_txt'>" . $temp_arr[$main_thithi_soonyam-1] . "</span>";
	}
	else
	{
		$temp_arr[$main_thithi_soonyam-1] = $temp_arr[$main_thithi_soonyam-1];
	}
	
	$g_thithi_soonyam_matching_arr[0] = join(',',$temp_arr);

	
}

function calculateKathirData($in_inside_arr, $in_outside_arr)
{
	$kathir_data = array();
	
	$rasi_kathir = [8,8,5,3,8,11,2,4,6,8,8,27];
	
	$kathir_data=$rasi_kathir;
	
	$planet_kathir = ["சூரி"=>5,
						"சந்"=>21,
						"செ"=>8,
						"புத"=>16,
						"குரு"=>10,
						"சுக்"=>20,
						"சனி"=>4,
						"ரா"=>4,
						"கே"=>4
						];
	
	foreach($in_outside_arr as $house => $planets){
		$kathir_data[$house] = $kathir_data[$house]+getPlanetsKathir($house,$planets);
	}
	
	foreach($in_inside_arr as $house => $planets){
		$kathir_data[$house] = $kathir_data[$house]+getPlanetsKathir($house,$planets);
	}
	
	return $kathir_data;
}

function getPlanetsKathir($in_house,$in_planets)
{
	$planet_kathir = ["சூரி"=>5,
						"சந்"=>21,
						"செ"=>8,
						"புத"=>16,
						"குரு"=>10,
						"சுக்"=>20,
						"சனி"=>4,
						"ரா"=>4,
						"கே"=>4
						];
	$kathir_value=0;
	
	$planet_karthir = $planet_kathir["சூரி"];
	
	if(strpos($in_planets,'சூரி')>0 || strpos($in_planets,"சூரி")===0){
		if($in_house==0){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==6){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==4){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["சந்"];
	
	if(strpos($in_planets,'சந்')>0 || strpos($in_planets,"சந்")===0){
		if($in_house==1){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==7){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==3){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["செ"];
	
	if(strpos($in_planets,'செ')>0 || strpos($in_planets,"செ")===0){
		if($in_house==9){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==3){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==0 or $in_house==7){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["புத"];
	
	if(strpos($in_planets,'புத')>0 || strpos($in_planets,"புத")===0){
		if($in_house==5){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==11){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==2){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["குரு"];
	
	if(strpos($in_planets,'குரு')>0 || strpos($in_planets,"குரு")===0){
		if($in_house==3){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==9){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==8 or $in_house==11){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["சுக்"];
	
	if(strpos($in_planets,'சுக்')>0 || strpos($in_planets,"சுக்")===0){
		if($in_house==11){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==5){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==1 or $in_house==6){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["சனி"];
	
	if(strpos($in_planets,'சனி')>0 || strpos($in_planets,"சனி")===0){
		if($in_house==6){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==0){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		elseif($in_house==9 or $in_house==10){		
			$kathir_value = $kathir_value + ($planet_karthir * 2);
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["ரா"];
	
	if(!(strpos($in_planets,'ரா.')>0 || strpos($in_planets,"ரா.")===0) && (strpos($in_planets,'ரா')>0 || strpos($in_planets,"ரா")===0)){
		if($in_house==1){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==7){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	$planet_karthir = $planet_kathir["கே"];
	
	if(strpos($in_planets,'கே')>0 || strpos($in_planets,"கே")===0){
		if($in_house==7){		
			$kathir_value = $kathir_value + ($planet_karthir * 3); //uchcham
		}
		elseif($in_house==1){		
			$kathir_value = $kathir_value + ($planet_karthir * 0.5); //neesam
		}
		else{		
			$kathir_value = $kathir_value + ($planet_karthir * 1);
		}
	}
	
	return $kathir_value;
}

function displayKathirChart($in_kathir_data)
{	
	echo <<<EOL
		<BR>
		<P style="color:#660000;font-weight:bold; font-size:16px;  background-color:white;width:360px;">கதிர்கள்</P>
		<div class="divTable blueTable" id="kathirchart">
			<div class="divTableBody">
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$in_kathir_data[11]</div>
						<div class="divTableCell">$in_kathir_data[0]</div>
						<div class="divTableCell">$in_kathir_data[1]</div>
						<div class="divTableCell">$in_kathir_data[2]</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$in_kathir_data[10]</div>
						<div class="divTableCenterCell1"></div>
						<div class="divTableCenterCell2"></div>
						<div class="divTableCell">$in_kathir_data[3]</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$in_kathir_data[9]</div>
						<div class="divTableCenterCell3"></div>
						<div class="divTableCenterCell4"></div>
						<div class="divTableCell">$in_kathir_data[4]</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$in_kathir_data[8]</div>
						<div class="divTableCell">$in_kathir_data[7]</div>
						<div class="divTableCell">$in_kathir_data[6]</div>
						<div class="divTableCell">$in_kathir_data[5]</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
				</div>
			</div>	
		</div>
EOL;
}

function getUTCtimestamp($timestamp,$offset){
	return gmdate($timestamp+$offset * (60 * 60));
}

function getUTCdate($timestamp,$offset){
	$utcTimestamp = gmdate($timestamp+$offset * (60 * 60));
	return date('d.m.Y', $utcTimestamp);
}

function getUTCtime($timestamp,$offset){
	$utcTimestamp = gmdate($timestamp+$offset * (60 * 60));
	return date('H:i:s', $utcTimestamp); 
}

function getDegree($in_deg)
{
// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.

    $vars = explode(".",$in_deg);
    $deg = $vars[0]+(isset($vars[1]) ? $vars[1] : 0)/100;
    
	return $deg;
} 

function array_to_xml( $data, &$xml_data ) {
    foreach( $data as $key => $value ) {
        if( is_array($value) ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild("$key",htmlspecialchars("$value"));
        }
     }
}

//calculating inverse interpolation
function calc_invinter($d, $n, $y){
   $x = 0.0;
   
   debug("<BR>Inside calc_invinter-Nak=".$y);

   for ($i = 0; $i < $n; $i++) {
      $xi = $d[$i][0];
      for ($j = 0; $j < $n; $j++) {
         if ($j != $i) {
			 $xi = $xi * ($y - $d[$j][1]) / ($d[$i][1] - $d[$j][1]);
         }
      }
      $x += $xi;
   }
   return $x;
}

/*************Calculate nak end time******************/
function findNakEndTime($nak_idx){
	global $today_sunrise, $moon_longitude_data, $sun_moon_data;
	
	$sun_moon_data = fillInterPolArray();
	
	$moon_longitude_data = $sun_moon_data[1];

	//$nak_idx is pada idx, so dividing it by 4 to get nak idx. Multiply by 360/27 to get ending degree of the nak
	$nak_end_degree = ceil($nak_idx/4)*360/27;
	$approx_nak_end = calc_invinter($moon_longitude_data,5,$nak_end_degree);
	
	debug ("approx_nak_end=$approx_nak_end");
	
	$approx_nak_end_seconds = round($approx_nak_end * 60 * 60);
	
	//$nak_end = (strtotime($today_sunrise) - $jd + $approx_nak_end) * 24 + strtotime($TZ);
	$nak_end = strtotime("+$approx_nak_end_seconds seconds",strtotime($today_sunrise));

	debug ("approx_nak_end_seconds=$approx_nak_end_seconds");
	
	//$nak_end_time = date('H:i:s',strtotime('+330 minutes',$nak_end));
	//$nak_end_time = date('H:i:s',calc_invinter($d,5,$nak_idx));
	$nak_end_time = date('d-m-Y h:i A',$nak_end);
	debug ("<BR>TEST interpolation".$nak_end_time);
	
	return $nak_end_time;
}

/*************Calculate thithi end time******************/
function findThithiEndTime($thithi_idx){
	global $today_sunrise,$full_sun_deg, $full_moon_deg, $sun_moon_data;
	
	$sun_moon_data = fillInterPolArray();

	$rel_motion_data = $sun_moon_data[1];

	//thithi index starts from 0, so add 1. One thithi is 12 degrees
	$thithi_end_degree = ($thithi_idx+1) * 12 + $full_sun_deg;
	
	if($thithi_end_degree > 360.0){
		$thithi_end_degree = $thithi_end_degree - 360.0;
	}

	debug ("thithi_end_degree=$thithi_end_degree");

	$approx_thithi_end = calc_invinter($rel_motion_data,5,$thithi_end_degree);
	
	debug ("approx_thithi_end=$approx_thithi_end");
	
	$approx_thithi_end_seconds = round($approx_thithi_end * 60 * 60);
	
	$thithi_end = strtotime("+$approx_thithi_end_seconds seconds",strtotime($today_sunrise));

	debug ("approx_thithi_end_seconds=$approx_thithi_end_seconds");
	
	$thithi_end_time = date('d-m-Y h:i A',$thithi_end);
	debug ("<BR>TEST interpolation".$thithi_end_time);
	
	return $thithi_end_time;
}

function fillInterPolArray()
{
	global $today_sunrise,$date, $time, $offset, $moon_longitude_data, $sun_moon_data;
	
	//if moon langitude is already filled, don't recalculate it
	if(!empty($sun_moon_data)){
		return $sun_moon_data;
	}
	
	//Calculating the moon degree at difference of 15 mins. 0 mins, 15 mins, 30 mins, 45 mins
	/*$d = array( array( 0, 30.5513331),
                 array( 0.25, 30.6886007),
                 array( 0.5, 30.8258323),
                 array( 0.75, 30.9630281),
				 array( 1.0, 31.1001880));*/
				 
	//Calculating the moon degree at differnet hours 1st hour, 2nd hour, 3rd hour, 4th hour, 5th hour. Above 15 mins intervel is not providing
	//correct end time for next day, so changed it by hours. dummy moon degrees 30.551, etc.
	$moon = array( array( 1, 30.5513331),
                 array( 2, 30.6886007),
                 array( 3, 30.8258323),
                 array( 4, 30.9630281),
				 array( 5, 31.1001880));
	$sun = array( array( 1, 30.5513331),
                 array( 2, 30.6886007),
                 array( 3, 30.8258323),
                 array( 4, 30.9630281),
				 array( 5, 31.1001880));
	$rel_motion = array( array( 1, 30.5513331),
                 array( 2, 30.6886007),
                 array( 3, 30.8258323),
                 array( 4, 30.9630281),
				 array( 5, 31.1001880));
				 
				 
	for($i=0;$i<5;$i++)
	{
		$new_time = strtotime("+".round($moon[$i][0]*60)." minutes",strtotime($today_sunrise));
		$new_time = gmdate('H:i:s',$new_time); //convert to UTC time
		
		$new_time = strtotime("+".round($sun[$i][0]*60)." minutes",strtotime($today_sunrise));
		$new_time = gmdate('H:i:s',$new_time); //convert to UTC time
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			debug('RUNNING ON WINDOWS');
			
			//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
			//64 version runs little faster
			//exec ("swetest64 -edir./sweph/ -b$date -ut$new_time -p1 -n1 -sid1 -eswe -fPls -head -g,",$output);  //get moon data
			exec ("swetest64 -edir./sweph/ -b$date -ut$new_time -p01 -n1 -sid1 -eswe -fPls -head -g,",$output); //get sun data as well
			//debug("<BR>swetest64 -edir./sweph/ -b$date -ut$new_time -p1 -n1 -sid1 -eswe -fPls -head -g,</BR>");
			debug("swetest64 -edir./sweph/ -b$date -ut$new_time -p01 -n1 -sid1 -eswe -fPls -head -g,");  //get sun data as well
		} else {
			debug('RUNNING ON LINUX');
			
			//on linux
			//exec ("swetest -edir./sweph/ -b$date -ut$new_time -p1 -n1 -sid1 -eswe -fPls -head -g,",$output);   //get moon data
			exec ("swetest -edir./sweph/ -b$date -ut$new_time -p01 -n1 -sid1 -eswe -fPls -head -g,",$output);  //get sun data as well
			//debug("<BR>swetest -edir./sweph/ -b22.04.2023 -ut01:32:19 -p1 -n1 -sid1 -eswe -fPls -head</BR>");
			debug("swetest -edir./sweph/ -b$date -ut$new_time -p01 -n1 -sid1 -eswe -fPls -head -g,");  //get sun data as well
		}
		
		$sun[$i][1] = trim(explode(',',$output[0])[1]);
		$moon[$i][1] = trim(explode(',',$output[1])[1]);
		$rel_motion[$i][1] = $moon[$i][1] - $sun[$i][1];
	
		$output="";
	}
	
	$out_arr = array($sun,$moon,$rel_motion);
	
	return $out_arr;
}


function getYogi($yogam)
{
	// யோகம் => யோகி நட், யோகி கிரகம், அவயோகி நட், அவயோகி கிரகம்
	$yoga_yogi_arr = [
						"விஷ்கம்பம்" => array("பூசம்","சனி","திருவோணம்","சந்திரன்"),
						"ப்ரீதி" => array("ஆயில்யம்","புதன்","அவிட்டம்","செவ்வாய்"),
						"ஆயுஷ்மான்" => array("மகம்","கேது","சதயம்","ராகு"),
						 "செளபாக்யம்" => array("பூரம்","சுக்ரன்","பூரட்டாதி","குரு"),
						 "சோபனம்" => array("உத்திரம்","சூரியன்","உத்திரட்டாதி","சனி"),
						 "அதி கண்டம்" => array("ஹஸ்தம்","சந்திரன்","ரேவதி","புதன்"),
						 "சுகர்மம்" => array("சித்திரை","செவ்வாய்","அசுவினி","கேது"),
						 "திருதி" => array("சுவாதி","ராகு","பரணி","சுக்ரன்"),
						 "சூலம்" => array("விசாகம்","குரு","கிருத்திகை","சூரியன்"),
						 "கண்டம்" => array("அனுஷம்","சனி","ரோகிணி","சந்திரன்"),
						 "விருத்தி" => array("கேட்டை","புதன்","மிருகசிரீஷம்","செவ்வாய்"),
						 "துருவம்" => array("மூலம்","கேது","திருவாதிரை","ராகு"),
						"வ்யாகதம்" => array("பூராடம்","சுக்ரன்","புனர்பூசம்","குரு"),
						"ஹர்ஷணம்" => array("உத்திராடம்","சூரியன்","பூசம்","சனி"),
						 "வஜ்ரம்" => array("திருவோணம்","சந்திரன்","ஆயில்யம்","புதன்"),
						"சித்தி" => array("அவிட்டம்","செவ்வாய்","மகம்","கேது"),
						"வியதீபாதம்" => array("சதயம்","ராகு","பூரம்","சுக்ரன்"),
						"வரீயான்" => array("பூரட்டாதி","குரு","உத்திரம்","சூரியன்"),
						 "பரிகம்" => array("உத்திரட்டாதி","சனி","ஹஸ்தம்","சந்திரன்"),
						"சிவம்" => array("ரேவதி","புதன்","சித்திரை","செவ்வாய்"),
						 "சித்தம்" => array("அசுவினி","கேது","சுவாதி","ராகு"),
						 "சாத்தியம்" => array("பரணி","சுக்ரன்","விசாகம்","குரு"),
						"சுபம்" => array("கிருத்திகை","சூரியன்","அனுஷம்","சனி"),
						"சுப்பிரம்" => array("ரோகிணி","சந்திரன்","கேட்டை","புதன்"),
						"பிராம்மியம்" => array("மிருகசிரீஷம்","செவ்வாய்","மூலம்","கேது"),
						"ஐந்திரம்" => array("திருவாதிரை","ராகு","பூராடம்","சுக்ரன்"),
						"வைதிருதி" => array("புனர்பூசம்","குரு","உத்திராடம்","சூரியன்")
					];
					
	return $yoga_yogi_arr[$yogam];
}

function getKaranaPlanet($karan)
{
	$karana_planet_array = [
								"பவ" => "செவ்வாய்",
								"பாலவ" => "ராகு",
								"கௌலவ" => "சனி",
								"தைதுலை" => "சுக்ரன்",
								"கரசை" => "சந்திரன்",
								"வணிசை"=> "சூரியன்",
								"பத்தரை"=> "கேது",
								"சகுனி" => "சனி",
								"சதுஷ்பாத" => "குரு",
								"நாகவ" => "ராகு",
								"கிம்ஸ்துக்கினம்" => "புதன்",
							];
							
	return $karana_planet_array[$karan];
}

function targetHouse($start_house,$number_of_houses)
{
	debug($start_house);
	debug($number_of_houses);
	
	if($start_house+$number_of_houses > 12){
		return $start_house - 1 + $number_of_houses-12;
	}
	else {
		return $number_of_houses + $start_house - 1;
	}
}


function updateIndhuLagna()
{
	global $lagna_house, $moon_house, $sw_planet;
	
	$lagna_ray = 0;
	$moon_ray = 0;
	
	/* $planet_rays = array("சூரி" =>30,
						"சந்" =>16,
						"புத" =>8,
						"சுக்" =>12,
						"செ" =>6,
						"குரு" =>10,
						"சனி" =>1); */
	
	switch(targetHouse($lagna_house,9)){
	//switch($lagna_house){
		case 1:
		case 8:
			$lagna_ray=6;
			break;
		case 2:
		case 7:
			$lagna_ray=12;
			break;
		case 3:
		case 6:
			$lagna_ray=8;
			break;
		case 9:
		case 12:
			$lagna_ray=10;
			break;
		case 10:
		case 11:
			$lagna_ray=1;
			break;
		case 4:
			$lagna_ray=16;
			break;
		case 5:
			$lagna_ray=30;
			break;
		default:
			break;
	}
	
	switch(targetHouse($moon_house,9)){
	//switch($moon_house){
		case 1:
		case 8:
			$moon_ray=6;
			break;
		case 2:
		case 7:
			$moon_ray=12;
			break;
		case 3:
		case 6:
			$moon_ray=8;
			break;
		case 9:
		case 12:
			$moon_ray=10;
			break;
		case 10:
		case 11:
			$moon_ray=1;
			break;
		case 4:
			$moon_ray=16;
			break;
		case 5:
			$moon_ray=30;
			break;
		default:
			break;
	}
	
	//$count = ($planet_rays[findAthipathi($lagna_house-1)]+$planet_rays[findAthipathi($moon_house-1)]) % 12;
	$count = ($lagna_ray + $moon_ray) % 12;
	
	$indhu_lagna_house = targetHouse($moon_house,$count);
	
	debug("COUNT=".$count." MOON_HOUSE=".$moon_house." LAGNA House=".$lagna_house." INDHU HOUSE=".$indhu_lagna_house);
	
	if($indhu_lagna_house == 0){
		$indhu_lagna_house=12;
	}
	
	debug($indhu_lagna_house);
	
	$sw_planet[$indhu_lagna_house-1] = $sw_planet[$indhu_lagna_house-1] . " " . "<span class='indhu'>இந்</span>" ;
}

function findKethuPos($pos)
{
	global $DEBUG;
	$kethuHouse=0;
	
	if($DEBUG==1) {
		echo "<BR>findKethuPos() pos=" . $pos . "</BR>";
	}
	
	switch ($pos) {
		case 0:
			$kethuHouse=6;
			break;
		case 1:
			$kethuHouse=7;
			break;
		case 2:
			$kethuHouse=8;
			break;
		case 3:
			$kethuHouse=9;
			break;
		case 4:
			$kethuHouse=10;
			break;
		case 5:
			$kethuHouse=11;
			break;
		case 6:
			$kethuHouse=0;
			break;
		case 7:
			$kethuHouse=1;
			break;
		case 8:
			$kethuHouse=2;
			break;
		case 9:
			$kethuHouse=3;
			break;
		case 10:
			$kethuHouse=4;
			break;
		case 11:
			$kethuHouse=5;
			break;
	  default:
		break;
	}
	
	return $kethuHouse;
}

function updatePlanet(array $planets,array $retro, $in_ragu_house)
{
	global $DEBUG,$planetDeg;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($planets);
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($retro);
	}
	
	
	$out = str_replace('Sun','சூரி',$planets);
	
	$out = str_replace('Moon','சந்',$out);
	
	if($retro[4]==1){
		$out = str_replace('Mars','செ',$out);
	}else{
		$out = str_replace('Mars','<span class="retro">செ</span>',$out);
	}
	
	if($retro[3]==1){
		$out = str_replace('Venus','சுக்',$out);
	}else{
		$out = str_replace('Venus','<span class="retro">சுக்</span>',$out);
	}
	
	if($retro[2]==1){
		$out = str_replace('Mercury','புத',$out);
	}else{
		$out = str_replace('Mercury','<span class="retro">புத</span>',$out);
	}
	
	if($retro[5]==1){
		$out = str_replace('Jupiter','குரு',$out);
	}else{
		$out = str_replace('Jupiter','<span class="retro">குரு</span>',$out);
	}
	
	if($retro[6]==1){
		$out = str_replace('Saturn','சனி',$out);
	}else{
		$out = str_replace('Saturn','<span class="retro">சனி</span>',$out);
	}
	
	//$out = str_replace('true Node','ரா',$out);
	$out = str_replace('mean Node','ரா',$out);
	$out = str_replace('Ascendant','<span style="background-color:blue;color:white">லக்</span>',$out);
	
	//trim spaces
	$out = array_map('trim', $out);
	
	$Kethu_pos = findKethuPos($in_ragu_house);
	
	$out[$Kethu_pos] = 'கே ' . $out[$Kethu_pos];
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE PLANET after update</BR>";
		echo "<BR>KETHU position: ".$Kethu_pos . "</BR>";
		var_dump($out);	
	}
	
	$planetDeg['கே']=$planetDeg['ரா'];
	asort($planetDeg);
	
	return $out;
}

function updateMandhi(array $out, array $in, $in_sun_degree, $in_day, $in_dayofweek,$in_sun_house)
{	
	global $datetime,$today_sunrise, $planetDeg,$mandhi_degree, $planetDeg_default;
	
	if(strtotime($datetime) < strtotime($today_sunrise)){
		$in_dayofweek = $in_dayofweek - 1;
		
		if($in_dayofweek < 0){
			$in_dayofweek = 6;
		}
	}
		
	$mandhi_house = targetHouse($in_sun_house,findMandhiHouse($in_dayofweek,$in_sun_degree, $in_day));
	
	debug("MANDHI_HOUSE=".$mandhi_house);
	
	foreach ($in as $key => $value) {
		//mandhi house update
		if($key == $mandhi_house){
			$out[$key] = $out[$key] . " மா";
			
			$planetDeg["மா"] = ($mandhi_degree % 30);
			$planetDeg_default["மா"] = ($mandhi_degree % 30);
		}
	}
	
	asort($planetDeg);
	
	return $out;
}

function findMandhiHouse($in_dayofweek,$in_sun_degree,$in_day)
{
	global $mandhi_degree;
	
	switch ($in_dayofweek) {
		case 0: //Sun
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+156;
			}
			else{
				$mandhi_degree=$in_sun_degree+240;
			}
			break;
		case 1: //Mon
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+132;
			}
			else{
				$mandhi_degree=$in_sun_degree+216;
			}
			break;
		case 2: //Tue
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+108;
			}
			else{
				$mandhi_degree=$in_sun_degree+192;
			}
			break;
		case 3: //Wed
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+84;
			}
			else{
				$mandhi_degree=$in_sun_degree+336;
			}
			
			debug("(((((((((".$mandhi_degree."))))))))");
			break;
		case 4: //Thur
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+60;
			}
			else{
				$mandhi_degree=$in_sun_degree+312;
			}
			break;
		case 5: //Fri
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+36;
			}
			else{
				$mandhi_degree=$in_sun_degree+288;
			}
			break;
		case 6:  //Sat
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+12;
			}
			else{
				$mandhi_degree=$in_sun_degree+264;
			}
			break;
	  default:
		break;
	}
	
	$mandhi_house = (int)(($mandhi_degree % 360)/30);
	
	debug("FINDMANDHI - WEEK DAY=" . $in_dayofweek);
	debug("FINDMANDHI - DAYTIME=" . $in_day);
	debug("FINDMANDHI - MANDHI DEGREE=" . ($mandhi_degree % 30));
	debug("FINDMANDHI - SUN DEGREE=" . $in_sun_degree);
	debug("FINDMANDHI - MANDHI DEGREE FROM SUN=" . $mandhi_degree);
	debug("FINDMANDHI - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$sw_degree["mandhi"] = DECtoDMS($mandhi_degree % 30);
	
	return $mandhi_house;
}

function displayDegree($in_sw_degree)
{
	global $out_planet_degree, $planetDeg_default, $planetDeg;
	
	$txt="";
	$planet="";
	
	foreach ($in_sw_degree as $key => $val) {
		if($val!=null) {
			
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
					$planet="ரா/கே";
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
			
			$txt = $txt . " " . $planet . "-" . $val;
			
			$planetDeg_default[$planet] = $val;
		}
	}
	
	$txt = $txt . " " . "மா-" . $planetDeg['மா']; 
	
	return $txt;
}

//generic function to insert array data into table having only one array data and last_updated field
function updatePanchangToDB($in_arr){
	global $conn;
	
	$conn = dbConnect($conn);
	
	$arr = serialize($in_arr);

	try {
		$sql = "UPDATE snapshot SET last_updated = LOCALTIMESTAMP(), panchang_data = '$arr'";
		
		if($result = mysqli_query($conn, $sql)){
			//echo "<script>alert('panchang data updated successfully!!!')</script>";
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	//dbDisconnect($conn);
}

//generic function to insert array data into table having only one array data and last_updated field
function updateHoraToDB($in_arr){
	global $conn;
	
	$conn = dbConnect($conn);
	
	$arr = serialize($in_arr);

	try {
		$sql = "UPDATE snapshot SET last_updated = LOCALTIMESTAMP(), hora_data = '$arr'";
		
		if($result = mysqli_query($conn, $sql)){
			//echo "<script>alert('panchang data updated successfully!!!')</script>";
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	//dbDisconnect($conn);
}

//generic function to insert array data into table having only one array data and last_updated field
function getSnapshotData(){
	global $conn;
	
	$row = "";
	
	$conn = dbConnect($conn);

	try {
		$sql = "SELECT panchang_data,hora_data FROM snapshot";
		
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				$row = $result->fetch_assoc();
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	return [unserialize($row["panchang_data"]),unserialize($row["hora_data"])];
	
	//dbDisconnect($conn);
}

function getStarLetters($nak)
{	
	$nak_letters = array("அசுவினி" => array(['சு,சே,சோ,ல'], ['CHU,CHE,CHO,LA']),
						"பரணி" => array(['லி,லு,லே,லோ'], ['LI,LU,LE,LO']),
						"கிருத்திகை" => array(['அ,இ,உ,எ'], ['AA,E,U,AI']),
						"ரோகிணி" => array(['ஒ,வ,வி,வு'], ['O,VA,VI,VU']),
						"மிருகசிரீஷம்" => array(['வே,வோ,கா,கி'], ['VE,VO,KA,KI']),
						"திருவாதிரை" => array(['கு,க,ச,ஞ'], ['KU,GHA,JNA,CHA']),
						"புனர்பூசம்" => array(['கே,கோ,ஹ,ஹி'], ['KE,KO,HA,HI']),
						"பூசம்" => array(['ஹூ,ஹே,ஹோ,ட'], ['HU,HE,HO,DA']),
						"ஆயில்யம்" => array(['டி,டு,டே,டோ'], ['DI,DU,DE,DO']),
						"மகம்" => array(['ம,மி,மு,மெ'], ['MA,MI,MU,ME']),
						"பூரம்" => array(['மோ,ட,டி,டு'], ['MO,TA,TI,TU']),
						"உத்திரம்" => array(['டே,டோ,ப,பி'], ['TE,TO,PA,PI']),
						"ஹஸ்தம்" => array(['பூ,ஷ,ந,ட'], ['PU,SHA,NA,TA']),
						"சித்திரை" => array(['பே,போ,ர,ரி'], ['PE,PO,RA,RI']),
						"சுவாதி" => array(['ரு,ரே,ரோ,த'], ['RU,RE,RO,THA']),
						"விசாகம்" => array(['தி,து,தே,தோ'], ['THI,THU,THE,THO']),
						"அனுஷம்" => array(['ந,நி,நு,நே'], ['NA,NI,NU,NE']),
						"கேட்டை" => array(['நோ,ய,இ,பூ'], ['NO,YA,YI,YU']),
						"மூலம்" => array(['யே,யோ,ப,பி'], ['YE,YO,BA,BI']),
						"பூராடம்" => array(['பூ,த,ப,டா'], ['BU,DHA,BHA,DA']),
						"உத்திராடம்" => array(['பே,போ,ஜ,ஜி'], ['BE,BO,JA,JE']),
						"திருவோணம்" => array(['ஜூ,ஜே,ஜோ,கா'], ['JU,JAY,JO,GHA']),
						"அவிட்டம்" => array(['க,கீ,கு,கூ'], ['GA,GI,GU,GE']),
						"சதயம்" => array(['கோ,ஸ,ஸீ,ஸூ'], ['GO,SA,SI,SU']),
						"பூரட்டாதி" => array(['ஸே,ஸோ,தா,தீ'], ['SE,SO,DHA,DHI']),
						"உத்திரட்டாதி" => array(['து,ஞ,ச,ஸ்ரீ'], ['DHU,GNA,SA,SRI']),
						"ரேவதி" => array(['தே,தோ,ச,சி'], ['DHE,DHO,CHA,CHI'])
						);
	
	return $nak_letters[$nak];
}

function getRasi_short($in_house)
{
	$rasi_arr = ["மே", "ரி", "மி", "கட", "சி", "கன்", "து", "வி", "த", "ம", "கு", "மீ"];
	
	return $rasi_arr[$in_house];
}

function calculateKaranamAllPlanets()
{
	global $full_sun_deg, $planet_starlord, $g_planetKaranaLord, $planetDeg;
	
	/* $karana_array = [
						"/",                            //this is to avoid adding 1 to the index
						"Kimstughna/கிம்ஸ்துக்கினம்",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Bava/பவ",
						"Balava/பாலவ",
						"Kaulava/கௌலவ",
						"Taitula/தைதுலை",
						"Garija/கரசை",
						"Vanija/வணிசை",
						"Visti/பத்தரை",
						"Sakuni/சகுனி",
						"Chatuspada/சதுஷ்பாத",
						"Naga/நாகவ",
					]; */
					
	$karana_array = [
						"/",                            //this is to avoid adding 1 to the index
						"Kim/கிம்ஸ்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Van/வணி",
						"Vis/பத்",
						"Bava/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bava/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Bav/பவ",
						"Bal/பால",
						"Kau/கௌ",
						"Tai/தைது",
						"Gar/கர",
						"Vani/வணி",
						"Vis/பத்",
						"Sak/சகு",
						"Cha/சது",
						"Nag/நாக",
					];
	
	foreach($planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){
			continue;
		}

		if($planet_starlord[$planet][2] > $full_sun_deg)
		{
			$karana_deg = $planet_starlord[$planet][2] - $full_sun_deg;
		}
		else{
			$karana_deg = 360 - ($full_sun_deg - $planet_starlord[$planet][2]); //subtract it from 360 to get degree from sun to the planet
		}
		
		if(strcmp($planet,"சூரி")==0){
			if($planetDeg['லக்'] > $full_sun_deg)
			{
				$karana_deg = $planetDeg['லக்'] - $full_sun_deg;
			}
			else{
				$karana_deg = 360 - ($full_sun_deg - $planetDeg['லக்']); //subtract it from 360 to get degree from sun to the planet
			}
		}
		
		$g_planetKaranaLord[$planet] = getValue($karana_array[ceil($karana_deg/6)]); //2nd argument is lang, which is defaulted to 1 (tamil)
		
		debug("$planet-------$karana_deg---".ceil($karana_deg/6)."-----".$karana_array[ceil($karana_deg/6)]."<BR>");
	}
}

//get value based on language passed. Defaulted to tamil
function getValue($in_string, $lang=1)
{
	$out = explode("/",$in_string);
	return $out[$lang];
}

//get thithi temple based on 
function getThithiParigaaram($thithi_idx)
{
	$temple_arr = [
					"பட்டீஸ்வரம் துர்க்கை", //1-15
					"திருவைக்காவூர்",
					"திங்களூர் சந்திரன்",
					"பிள்ளையார்பட்டி விநாயகர்",
					"பெண்ணாடம் தேவபுரீஸ்வரர்",
					"திருச்செந்தூர் சுப்பிரமணியர்",
					"சூரியனார் கோவில்",
					"தேவூர் மகாலட்சுமி",
					"கூத்தனூர் சரஸ்வதி அம்மன்",
					"வீரபத்ரசுவாமி, திருவெண்காடு",
					"மதுரை மீனாட்சி அம்மன்",
					"சாரங்கபானி பெருமாள்",
					"திருக்கண்டியூர் ப்ரம்மன்",
					"திருபுவனம் சரவெஸ்வரர்",
					"மகேந்திரப்பள்ளி",      //பௌர்ணமி
					"திருக்காட்டுகப்பள்ளி",  //16-30
					"காலஹஸ்தி சிவன்",
					"திருவண்ணாமலை",
					"கஞ்சனூர் சுக்கிரன்",
					"ஆலங்குடி குரு",
					"வைத்திஸ்வரன் முத்துகுமாரசுவாமி, அங்காரகன்",
					"திருக்கானூர் கரும்பீஸ்வரர்",
					"ஸ்ரீரங்கம் அரங்கநாதர், கருடாழ்வார்",
					"ஸ்ரீவாஞ்சியம் எமதர்மன், கைலாசநாதர்",
					"தென்குடித்திட்டை வசிஸ்டேஸ்வரர்‌",
					"திருநள்ளாறு சனிபகவான்‌",
					"திருவில்லியங்குடி பெருமாள்‌",
					"திருமலைப்பாடி சிவன், நந்தீஸ்வரர்",
					"கங்கைகொண்ட சோழபுரம்‌",
					"இராமேஸ்வரம்"    //அம்மாவாசை  30
					];
					
	$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>திதி பரிகாரம்</div>$temple_arr[$thithi_idx]</DIV>";
					
	return $details;
}

//get karana parigaaram based on karana idx
function getKaranaParigaaram($karan)
{					
	$kar = explode('/',$karan)[1];
	$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>கரண பரிகாரம்</div><img src='images/karanam_gods/$kar.jpg' /></DIV>";
					
	return $details;
}

function getYogiParigaaram($yogi_idx)
{
	$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>யோகி பரிகாரம்</div><img src='images/yogi_parigaaram/$yogi_idx.png' /></DIV>";
					
	return $details;
}

function getAvaYogiParigaaram($ayogi_idx)
{
	$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>அவயோகி பரிகாரம்</div><img src='images/avayogi_parigaaram/$ayogi_idx.png' /></DIV>";
					
	return $details;
}

function calculateBirthPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_nak, $g_karan, $panchang,$g_nak_pad_idx, $g_thithi_idx, $g_yoga_idx, $g_nak_idx;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	$karan_sukla = array(array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"));
	$karan_krishna = array(array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Sakuni/சகுனி"),
						array("Chatuspada/சதுஷ்பாத","Naga/நாகவ"));
	
	$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
					"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	
	$panchang = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Poorva bhadrapada 1/பூரட்டாதி 1","Poorva bhadrapada 2/பூரட்டாதி 2","Poorva bhadrapada 3/பூரட்டாதி 3","Poorva bhadrapada 4/பூரட்டாதி 4",
						"Uthra bhadrapada 1/உத்திரட்டாதி 1","Uthra bhadrapada 2/உத்திரட்டாதி 2","Uthra bhadrapada 3/உத்திரட்டாதி 3","Uthra bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	$g_nak_idx = $nak_idx;
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_nak_pad_idx = $nak_idx;
	$g_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
	//Get nak end time
	$nak_end_time = findNakEndTime($nak_idx);
	$panchang["nak-end-time"] = $nak_end_time;
	debug("NAK_END_TIME=$nak_end_time");
	
	//thithi calculation
	$full_sun_deg = ($sun_house-1)*30+$sun_degree;
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			
			if($thi_idx >=0 and $thi_idx <=14)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			elseif($thi_idx >14 and $thi_idx <=29)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			
			/*
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			elseif($idx > 14){
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			elseif($idx <= 14){
				
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			*/
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
	}
	
	debug("--THI IDX--".floor($idx)."----");
	$g_thithi_idx = $thi_idx;
	
	//karanam calculation
	$kar = $idx-floor($idx);
	//$kar = $thi_idx-floor($thi_idx);
	
	if($thi_idx < 15){
		
		if($kar < 0.5){
			$panchang["karan"] = $karan_sukla[$thi_idx][0];
		}
		else{
			$panchang["karan"] = $karan_sukla[$thi_idx][1];
		}
		
		debug("THI-IDX=".$thi_idx . "KAR=". $kar . "&&&&&&");
	}
	else{
		if($kar < 0.5){
			$panchang["karan"] = $karan_krishna[$thi_idx-15][0];
		}
		else{
			$panchang["karan"] = $karan_krishna[$thi_idx-15][1];
		}
		
		debug("THI-IDX-15=".($thi_idx-15) . "KAR=". $kar . "&&&&&&");
	}
	
	//Store karanam for future use
	$g_karan = $panchang["karan"];
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$g_yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	$panchang["yoga"] = $yoga_arr[$g_yoga_idx];
}

function updateThithi()
{
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_nak, $g_karan, $panchang,$g_nak_pad_idx, $g_thithi_idx;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	
	//thithi calculation
	$full_sun_deg = ($sun_house-1)*30+$sun_degree;
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			
			if($thi_idx >=0 and $thi_idx <=14)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			elseif($thi_idx >14 and $thi_idx <=29)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			
			/*
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			elseif($idx > 14){
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			elseif($idx <= 14){
				
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]-";
			}
			*/
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
	}
	
	debug("--THI IDX--".floor($idx)."----");
	$g_thithi_idx = $thi_idx;
	
	return $idx;
}

function displayDetailsDiv($in_dataType,$in_data){
	global $g_yoga_idx, $g_nak_idx, $g_thithi_idx, $g_karan;
	$details = "";
	
	
	switch($in_dataType){
		case "thi":
			$details = getThithiParigaaram($g_thithi_idx);
			echo "<DIV class='thithiDetailsDiv' id='thithiDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";
			break;
		case "nak":
			$details = getNakParigaaram($g_nak_idx);
			echo "<DIV class='nakDetailsDiv' id='nakDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";
			break;
		case "kar":
			$details = getKaranaParigaaram($g_karan);
			echo "<DIV class='karanaDetailsDiv' id='karanaDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";

			break;
		case "yogi":
			$details = getYogiParigaaram($g_yoga_idx);
			echo "<DIV class='yogiDetailsDiv' id='yogiDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";
			break;
		case "ayogi":
			$details = getAvaYogiParigaaram($g_yoga_idx);
			echo "<DIV class='ayogiDetailsDiv' id='ayogiDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";
			break;
		
	}
}

//disclaimer to avoid any issues
function displayDisclaimer()
{
echo <<<EOL
	
			<div class="divDisclaimer" colspan=3>
				<H6 style='font-weight;color:red'>*இந்த தளத்தில்/மொபைல் ஆப்பில் கொடுக்கப்பட்டுள்ள ஆய்வுகள் மற்றும் மதிப்பென்கள் இறுதியானது கிடையாது, இதனுடன் நீங்கள் தங்கள் விரிவான ஆய்வுகளையும் மேற்க்கொண்டு இருதி முடிவு எடுக்கவும்</H6>
			</div>
EOL;
}

function starCount($star1_idx, $star2_idx)
{
	if($star1_idx > $star2_idx)
	{
		return 27-abs($star2_idx - $star1_idx)+1;
	}
	else{
		return $star2_idx - $star1_idx+1;
	}	
}

function getSex($in_sex,$lang=1) //tamil
{
	switch((int)$in_sex)
	{
		case 1: 
			return "ஆண்";
		case 2:
			return "பெண்";
		case 3:
			return "மூன்றாம்";
	}
}

function getStr($str, $lang)
{
	$out = "";
	$temp = preg_split('/[\/\[\]]/', $str,-1, PREG_SPLIT_NO_EMPTY);

	if($lang==1){ //tamil
		switch(sizeof($temp)){
			case 1:   //eg அதி கண்டம்
				$out = $str;
				break;
			case 2:  //eg Uthra bhadrapada 3/உத்திரட்டாதி 3
				$out = $temp[1];
				break;
			case 4: //eg Shrashti/சஷ்டி[krishna/தேய்]
				$out = $temp[1]."(".$temp[3].")";
				break;
		}		
	}
	elseif($lang==2) {  // english
		switch(sizeof($temp)){
			case 1:   //eg அதி கண்டம்
				$out = $str;
				break;
			case 2:  //eg Uthra bhadrapada 3/உத்திரட்டாதி 3
				$out = $temp[0];
				break;
			case 4: //eg Shrashti/சஷ்டி[krishna/தேய்]
				$out = $temp[0]."(".$temp[2].")";
				break;
		}		
	}
	
	return $out;
}

?>
