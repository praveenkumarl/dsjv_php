<?php
//ini_set("session.gc_maxlifetime", "86400");

if(!session_id()){
	session_start();
}

//include_once "/home/u835731528/public_html/dsjv_home/db.php";
include_once "db.php";
include_once "lang/lang.php";

/**
 * Translate Tamil text to English based on current language setting
 * Handles composite strings by replacing individual Tamil words
 */
function translate_tamil_text($tamil_text) {
	global $language;
	
	// If language is Tamil or text is empty, return as-is
	if (!isset($language) || $language === 'tamil' || empty($tamil_text)) {
		return $tamil_text;
	}
	
	// Get the translation arrays
	$ta_translations = include(__DIR__ . '/lang/ta.php');
	$en_translations = include(__DIR__ . '/lang/en.php');
	
	// Check if the text is already in English (exists in English array)
	if (in_array($tamil_text, $en_translations)) {
		return $tamil_text;
	}
	
	// Try exact match first
	$key = array_search($tamil_text, $ta_translations);
	if ($key !== false && isset($en_translations[$key])) {
		return $en_translations[$key];
	}
	
	// If no exact match, try to translate each word/part
	$result = $tamil_text;
	
	// Create a list of translation pairs sorted by Tamil text length (longest first)
	$translations_sorted = [];
	foreach ($ta_translations as $key => $tamil_value) {
		if (isset($en_translations[$key]) && !empty($tamil_value)) {
			$translations_sorted[] = [
				'tamil' => $tamil_value,
				'english' => $en_translations[$key],
				'length' => mb_strlen($tamil_value)
			];
		}
	}
	
	// Sort by length descending to replace longest matches first
	usort($translations_sorted, function($a, $b) {
		return $b['length'] - $a['length'];
	});
	
	// Track what has been replaced to avoid overlapping replacements
	$replaced_positions = [];
	
	foreach ($translations_sorted as $translation) {
		$tamil_word = $translation['tamil'];
		$english_word = $translation['english'];
		
		// Find all occurrences of this Tamil word
		$offset = 0;
		while (($pos = mb_strpos($result, $tamil_word, $offset)) !== false) {
			// Check if this position overlaps with already replaced text
			$overlaps = false;
			$word_length = mb_strlen($tamil_word);
			
			for ($i = $pos; $i < $pos + $word_length; $i++) {
				if (isset($replaced_positions[$i])) {
					$overlaps = true;
					break;
				}
			}
			
			if (!$overlaps) {
				// Mark these positions as replaced
				for ($i = $pos; $i < $pos + $word_length; $i++) {
					$replaced_positions[$i] = true;
				}
				
				// Replace the Tamil word with English
				$before = mb_substr($result, 0, $pos);
				$after = mb_substr($result, $pos + $word_length);
				$result = $before . $english_word . $after;
				
				// Adjust offset for the replacement
				$offset = $pos + mb_strlen($english_word);
			} else {
				$offset = $pos + 1;
			}
		}
	}
	
	return $result;
}

/**
 * Translate planet Tamil abbreviation to localized text
 * Maps Tamil planet abbreviations to their translation keys
 */
function translate_planet_abbr($tamil_planet) {
	// Mapping of Tamil planet abbreviations to translation keys
	$planet_key_map = array(
		'சூரி' => 'sun_abbr',
		'சந்' => 'moon_abbr',
		'செ' => 'mars_abbr',
		'புத' => 'mercury_abbr',
		'குரு' => 'jupiter_abbr',
		'சுக்' => 'venus_abbr',
		'சனி' => 'saturn_abbr',
		'ரா' => 'rahu_abbr',
		'கே' => 'ketu_abbr',
		'மா' => 'mandhi_abbr',
		'லக்' => 'lagna_abbr',
		'இந்' => 'indhu_abbr'
	);
	
	// If we have a translation key for this planet, use it
	if (isset($planet_key_map[$tamil_planet])) {
		return __($planet_key_map[$tamil_planet]);
	}
	
	// Otherwise fall back to translate_tamil_text
	return translate_tamil_text($tamil_planet);
}

$moon_longitude_data=array();
$g_agas=null;
$rearranged_arr;

/*
//check if logged in and route it to default registration message
if ((basename(($_SERVER['PHP_SELF']) !== 'freeuser.php' || $_SERVER['PHP_SELF'] !== 'new_loginPage.php' || $_SERVER['PHP_SELF'] !== 'new_logout.php' || $_SERVER['PHP_SELF']) !== 'athenticate.php' || $_SERVER['PHP_SELF'] !== 'athenticate_mobile.php') && !isLoggedIn()) {
    header("Location: freeuser.php");
    exit();
}
*/

//constants

const MIN_STAR_MATCH_COUNT = 7; //minimum matching
const TOTAL_STAR_MATCH_COUNT = 11; //max matching
const TOTAL_MATCH_RULES = 12; //total matching rules


$menu_style='background-color: gold;padding:20px;color:black;font-weight:bold';

//user types
const FREE = "FREE";
const PAID = "PAID";
const BIRTH = "BIRTH";
const MATCHING = "MATCH";
const BIRTHMATCH = "BIRTHMATCH";
const BIRTHPANCH = "BIRTHPANCH";
const BIRTHPANCHMATCH = "BIRTHPANCHMATCH";
const PANCHANG = "PANCHANG";
const JAMAKOL = "JAMAKOL";
const JAMA_MATCH = "JAMAMATCH";
const JAMA_BIRTH = "JAMABIRTH";
const SUPER = "SUPER";
const ADMIN = "ADMIN";
const PRASANNAM = "PRASANNAM";
const NUMEROLOGY = "NUMEROLOGY";

const VAINASIGAM_PAD_COUNT = 88;
const ABHIJIT_PAD_COUNT = 84;

$g_nak="";

//usertype array used in drop down
$usertype_arr = [PAID, MATCHING, BIRTH, BIRTHMATCH, BIRTHPANCH, BIRTHPANCHMATCH, JAMAKOL, JAMA_MATCH, JAMA_BIRTH, PANCHANG, PRASANNAM, NUMEROLOGY, SUPER, ADMIN, FREE];

$timestamp = date('ymdhis');

$DEBUG= isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

echo '<head><link rel="stylesheet" href="css/main.css"></head>';

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

function isLoggedIn() {
    return isset($_SESSION['userinfo']);
}

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
    return 60 * (int)$hours + (int)$minutes;
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
	global $DEBUG, $g_ucha_details, $g_neecha_details;
	
	if($DEBUG==1) {
		echo "<BR>updateStrength before update</BR>";
		var_dump($planets);
	}
	
	//Meenam
	if(strpos($planets[11],"சுக்")===0 or strpos($planets[11],"சுக்")>0){
		//$planets = str_replace("சுக்","சுக்&#8593;",$planets);
		$planets = str_replace("சுக்","சுக்+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."சுக்";
		}
		else{
			$g_ucha_details = $g_ucha_details."சுக்";
		}
	}
	if(strpos($planets[11],"புத")===0 or strpos($planets[11],"புத")>0){
		//$planets = str_replace("புத","புத&#8595;",$planets);
		$planets = str_replace("புத","புத-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."புத";
		}
		else{
			$g_neecha_details = $g_neecha_details."புத";
		}
	}
	
	//Makaram
	if(strpos($planets[9],"செ")===0 or strpos($planets[9],"செ")>0){
		//$planets = str_replace("செ","செ&#8593;",$planets);
		$planets = str_replace("செ","செ+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."செ";
		}
		else{
			$g_ucha_details = $g_ucha_details."செ";
		}
	}
	if(strpos($planets[9],"குரு")===0 or strpos($planets[9],"குரு")>0){
		//$planets = str_replace("குரு","குரு&#8595;",$planets);
		$planets = str_replace("குரு","குரு-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."குரு";
		}
		else{
			$g_neecha_details = $g_neecha_details."குரு";
		}
	}
	
	//Thulam
	if(strpos($planets[6],"சனி")===0 or strpos($planets[6],"சனி")>0){
		//$planets = str_replace("சனி","சனி&#8593;",$planets);
		$planets = str_replace("சனி","சனி+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."சனி";
		}
		else{
			$g_ucha_details = $g_ucha_details."சனி";
		}
	}
	if(strpos($planets[6],"சூரி")===0 or strpos($planets[6],"சூரி")>0){
		//$planets = str_replace("சூரி","சூரி&#8595;",$planets);
		$planets = str_replace("சூரி","சூரி-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."சூரி";
		}
		else{
			$g_neecha_details = $g_neecha_details."சூரி";
		}
	}
	
	//Kanni
	if(strpos($planets[5],"புத")===0 or strpos($planets[5],"புத")>0){
		//$planets = str_replace("புத","புத&#8593;",$planets);
		$planets = str_replace("புத","புத+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."புத";
		}
		else{
			$g_ucha_details = $g_ucha_details."புத";
		}
	}
	if(strpos($planets[5],"சுக்")===0 or strpos($planets[5],"சுக்")>0){
		//$planets = str_replace("சுக்","சுக்&#8595;",$planets);
		$planets = str_replace("சுக்","சுக்-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."சுக்";
		}
		else{
			$g_neecha_details = $g_neecha_details."சுக்";
		}
	}
	
	//Katakam
	if(strpos($planets[3],"குரு")===0 or strpos($planets[3],"குரு")>0){
		//$planets = str_replace("குரு","குரு&#8593;",$planets);
		$planets = str_replace("குரு","குரு+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."குரு";
		}
		else{
			$g_ucha_details = $g_ucha_details."குரு";
		}
	}
	if(strpos($planets[3],"செ")===0 or strpos($planets[3],"செ")>0){
		//$planets = str_replace("செ","செ&#8595;",$planets);
		$planets = str_replace("செ","செ-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."செ";
		}
		else{
			$g_neecha_details = $g_neecha_details."செ";
		}
	}
	
	//Rishabam
	if(strpos($planets[1],"சந்")===0 or strpos($planets[1],"சந்")>0){
		//$planets = str_replace("சந்","சந்&#8593;",$planets);
		$planets = str_replace("சந்","சந்+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."சந்";
		}
		else{
			$g_ucha_details = $g_ucha_details."சந்";
		}
	}
	
	//Mesham
	if(strpos($planets[0],"சூரி")===0 or strpos($planets[0],"சூரி")>0){
		//$planets = str_replace("சூரி","சூரி&#8593;",$planets);
		$planets = str_replace("சூரி","சூரி+",$planets);
		
		if(strlen($g_ucha_details)!=0){
			$g_ucha_details = $g_ucha_details.","."சூரி";
		}
		else{
			$g_ucha_details = $g_ucha_details."சூரி";
		}
	}
	if(strpos($planets[0],"சனி")===0 or strpos($planets[0],"சனி")>0){
		//$planets = str_replace("சனி","சனி&#8595;",$planets);
		$planets = str_replace("சனி","சனி-",$planets);
		
		if(strlen($g_neecha_details)!=0){
			$g_neecha_details = $g_neecha_details.","."சனி";
		}
		else{
			$g_neecha_details = $g_neecha_details."சனி";
		}
	}
	
	if($DEBUG==1) {
		
		echo "<BR>updateStrength after update</BR>";
		var_dump($planets);
	}
	
	if(strlen($g_ucha_details)==0){
		$g_ucha_details = "இல்லை";
	}
	
	if(strlen($g_neecha_details)==0){
		$g_neecha_details = "இல்லை";
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
					<div class="divTable blueTable" style="height:40px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableOuterCell" colspan=3 style="border:none;">
							<div class="header">Auto Analysis</div>
							</div>
						</div>
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
					<div class="divTable blueTable" style="height:40px;">
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
	if(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile") == true or strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android") == true){
		return true;
	}
	else{
		return false;
	}
	
	//return is_numeric(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "mobile")); //returns true if mobile
}

function isLogged(){
	return isset($_SESSION['logged']) ? $_SESSION['logged'] : false; //returns true if true
}

function getUserInfo_web($phone)
{
	global $conn, $usertype, $row;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT user.name, user.phone, user.user_type, user.imei, user.last_login, user.registered_date, user_trial.expiry_date,user.office_name,user.office_contact from user, user_trial 
		where user.phone=user_trial.phone and user.phone='$phone'";
	
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

		echo $conn->errorMessage();
		throw $exception;
	}
	
	dbDisconnect($conn);
	
	$_COOKIE['phone'] = $row['phone'];
	
	
	return $row;
}

function getUserInfo_mobile($key)
{
	global $conn, $row;
	
	debug("__FUNCTION__.$key");
	
	$conn = dbConnect($conn);

	try {
		
		//$sql = "SELECT name, phone, user_type, imei, last_login, registered_date from user where imei='$key'";
		$sql = "SELECT user.name, user.phone, user.user_type, user.imei, user.last_login, user.registered_date, user_trial.expiry_date,user.office_name,user.office_contact from user, user_trial 
		where user.phone=user_trial.phone and user.imei='$key'";
	
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
		
		echo $conn->errorMessage();
		throw $exception;
	}
	
	dbDisconnect($conn);
	
	$_COOKIE['key'] = $key;
	
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
		echo "<div class='userinfo'>";
			//echo $userinfo['expiry_date'];
			echo date("d-m-Y", strtotime($userinfo['expiry_date']));
		echo "</div>";
	echo "</div>";
	
}

function displayUserInfoMobile($userinfo)
{
	echo "<div class='envWeb' style='background-color:".envColor()."'>";
		echo "<div class='userinfoMobile' style='width:auto'>";
			echo $userinfo['name'];
		echo "</div>";
		echo "<div class='userinfoMobile' style='width:auto'>";
			echo $userinfo['phone'];
		echo "</div>";
		echo "<div class='userinfoMobile' style='width:auto'>";
			echo $userinfo['user_type'];
		echo "</div>";
		echo "<div class='userinfoMobile' style='width:auto'>";
			//echo $userinfo['expiry_date'];
			echo date("d-m-Y", strtotime($userinfo['expiry_date']));
		echo "</div>";
	echo "</div>";
}

//To display user details
function displayHelpInfo($devInfo)
{
	echo "<div class='divTable blueTable' style='width:350px; height:120px'>";
	
	foreach($devInfo as $heading => $val){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchang'>$heading</div>";
			echo "<div class='divPanchang' style='font-size:clamp(10px, 1vw, 14px)'>$val</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow'>";
		echo "<div class='divPanchang'>Screen(W,H)</div>";
		echo "<div class='divPanchang' style='font-size:clamp(10px, 1vw, 14px)' id='sw'></div>";
	echo "</div>";
		
	echo "</div>";
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
	if(strcmp($_SERVER['HTTP_HOST'],"pkastro.com/dsjv_home")==0)
	{
		return "#ffcccc";
	}
	elseif(strcmp($_SERVER['HTTP_HOST'],"pkastro.com")==0)
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
	elseif(strcmp($_SERVER['HTTP_HOST'],"192.168.29.133")==0)
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


// true only if $token appears as a standalone token (not inside another Tamil/word token)
function hasToken($haystack, $token) {
    // Do not match if a letter/mark is immediately before or after the token
    $pattern = '/(?<![\p{L}\p{M}])' . preg_quote($token, '/') . '(?![\p{L}\p{M}])/u';
    return preg_match($pattern, $haystack) === 1;
}

function sortDegree($inArray) // new function
{
	global $planetDeg, $planetDeg_default, $mandhi_degree, $g_rasi_girl,$g_bhava_arr;
	
	$RK_house = -1;
	
	$outArray=array(null,null,null,null,null,null,null,null,null,null,null,null);
	
	foreach ($planetDeg as $planet => $deg) {

		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg[$planet],$inArray[$house]);
			
			//arrange planets by degree from top to bottom within these houses (Mesham to Thulam)
			if($house<=5)
			{
				//if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if (hasToken($inArray[$house], $planet)) {
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = $outArray[$house] . " " . "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						
						//updateBhavaArray($planetDeg[$planet],"$planet(வ)" . "-". $planetDeg[$planet],$house);
						updateBhavaArray($planetDeg[$planet],"$planet(வ)",$house);
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
					
					if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
						$outArray[$house] = $outArray[$house] . " " . "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						
						//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
						updateBhavaArray($planetDeg[$planet],"$planet",$house);
					}
					elseif(strpos($planet,'மா')>0 || strpos($planet,"மா")===0){
						$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>" . $planet . "</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						
						//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
						updateBhavaArray($planetDeg[$planet],"$planet",$house);
					}
					elseif(strpos($planet,'சந்')>0 || strpos($planet,"சந்")===0){
						//$outArray[$house] = $outArray[$house] . " " . "<span>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						$outArray[$house] = $outArray[$house] . " " . "<span class='moon'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
						elseif(strpos($planet,'உத')>0 || strpos($planet,"உத")===0 || strpos($planet,'UDH')>0 || strpos($planet,"UDH")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='udh' style='background-color:#7cfc00;color:black;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='udh' style='background-color:#7cfc00;color:black;'>$planet</span>";
							}
						}
						elseif(strpos($planet,'ஆரு')>0 || strpos($planet,"ஆரு")===0 || strpos($planet,'ARU')>0 || strpos($planet,"ARU")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='aar' style='background-color:#7cfc00;color:black;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='aar' style='background-color:#7cfc00;color:black;'>$planet</span>";
							}
						}
						elseif(strpos($planet,'கவி')>0 || strpos($planet,"கவி")===0 || strpos($planet,'KAV')>0 || strpos($planet,"KAV")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = $outArray[$house] . " " . "<span class='kav' style='background-color:red;color:white;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							}
							else{
								$outArray[$house] = $outArray[$house] . " " . "<span class='kav' style='background-color:red;color:white;'>$planet</span>";
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
						elseif(strpos($planet, '#') === 0) {							
							$outArray[$house] .= " <span class='ubagraha'>{$planet}</span>";
						}
						else{
						//	echo "<BR>$planet".$planetDeg[$planet];
							$outArray[$house] = $outArray[$house] . " " . $planet . "-". DECtoDMS_jamakol($planetDeg[$planet]);
							
							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
					}
				}
			}
			//arrange planets by degree from bottom to top within these houses (Virichigam to Meenam)
			else{
				//if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if (hasToken($inArray[$house], $planet)) {
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						
						//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
						updateBhavaArray($planetDeg[$planet],"$planet",$house);
						
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
						if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
							$outArray[$house] =  "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
							
							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
						elseif(strpos($planet,'??')>0 || strpos($planet,'??')===0){
							$outArray[$house] = "<span class='mandhi'>" . translate_tamil_text($planet) . "</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
							
							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
						elseif(strpos($planet,'சந்')>0 || strpos($planet,"சந்")===0){
							//$outArray[$house] = "<span>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							$outArray[$house] = "<span class='moon'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							
							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
						elseif(strpos($planet,'உத')>0 || strpos($planet,"உத")===0 || strpos($planet,'UDH')>0 || strpos($planet,"UDH")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='udh' style='background-color:#7cfc00;color:black;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
							}
							else{
								$outArray[$house] = "<span class='udh' style='background-color:#7cfc00;color:black;'>$planet</span>" . " " . $outArray[$house];
							}
						}
						elseif(strpos($planet,'ஆரு')>0 || strpos($planet,"ஆரு")===0 || strpos($planet,'ARU')>0 || strpos($planet,"ARU")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='aar' style='background-color:#7cfc00;color:black;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='aar' style='background-color:#7cfc00;color:black;'>$planet</span>" . " " . $outArray[$house] ;
							}
						}
						elseif(strpos($planet,'கவி')>0 || strpos($planet,"கவி")===0 || strpos($planet,'KAV')>0 || strpos($planet,"KAV")===0){
							if($planetDeg[$planet]>=0.0){
								$outArray[$house] = "<span class='kav' style='background-color:red;color:white;'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							}
							else{
								$outArray[$house] = "<span class='kav' style='background-color:red;color:white;'>$planet</span>" . " " . $outArray[$house] ;
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
						elseif(strpos($planet, '#') === 0) {							
							$outArray[$house] .= " <span class='ubagraha'>{$planet}</span>";
						}
						else{
							$outArray[$house] = $planet . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house] ;
							
							//updateBhavaArray($planetDeg[$planet],"$planet" . "-". $planetDeg[$planet],$house);
							updateBhavaArray($planetDeg[$planet],"$planet",$house);
						}
					}
				}
				
			}
		}
	}
	
	if(isset($outArray[$RK_house]) && (!function_exists('is_english') || !is_english())){
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
			
			//if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
			if (hasToken($inArray[$house], $planet)) {
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
						//$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>" . translate_tamil_text($planet) . "</span>" . "-". DECtoDMS($planetDeg_transit[$planet]);
						$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>" . $planet . "</span>";
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

function sortDegree_boy($inArray) // new function
{
	global $planetDeg_transit,$mandhi_degree_transit, $g_rasi_boy;
	
	$RK_house = -1;
	
	$planetDeg = $planetDeg_transit;
	$mandhi_degree = $mandhi_degree_transit;
	
	$outArray=array(null,null,null,null,null,null,null,null,null,null,null,null);
	
	foreach ($planetDeg as $planet => $deg) {
		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg[$planet],$inArray[$house]);
			
			//arrange planets by degree from top to bottom within these houses (Mesham to Thulam)
			if($house<=5)
			{
				//if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if (hasToken($inArray[$house], $planet)) {
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = $outArray[$house] . " " . "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
						if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
							$outArray[$house] = $outArray[$house] . " " . "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
						}
						elseif(strpos($planet,'??')>0 || strpos($planet,'??')===0){
							$outArray[$house] = $outArray[$house] . " " . "<span class='mandhi'>" . translate_tamil_text($planet) . "</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]);
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
				//if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if (hasToken($inArray[$house], $planet)) {
					if(strpos($inArray[$house],'retro">'.$planet)>0 || strpos($inArray[$house],'retro">'.$planet)===0){
						$outArray[$house] = "<span class='retro'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						
					}
					else{
						//$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
						
						if(strpos($planet,'லக்')>0 || strpos($planet,"லக்")===0){
							$outArray[$house] =  "<span class='lagna'>$planet</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
						}
						elseif(strpos($planet,'??')>0 || strpos($planet,'??')===0){
							$outArray[$house] = "<span class='mandhi'>" . translate_tamil_text($planet) . "</span>" . "-". DECtoDMS_jamakol($planetDeg[$planet]) . " " . $outArray[$house];
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

function getRasi($in_house)
{
	$rasi_arr = ["மேஷம்", "ரிஷபம்", "மிதுனம்", "கடகம்", "சிம்மம்", "கன்னி", "துலாம்", "விருச்சிகம்", "தனுசு", "மகரம்", "கும்பம்", "மீனம்"];
	
	return $rasi_arr[$in_house];
}

function updateRahuEmma(array $inside, $weekday, $sun_degree, $lang)
{
	global $DEBUG, $planetDeg, $previous_day,$chart_date,$chart_time,$next_day; //$planetDeg is global array
	
	if($DEBUG==1) {
		echo "<BR>updateRahuEmma before update</BR>";
		var_dump($planets);
		echo "<BR>updateRahuEmma before update</BR>";
	}
	
	$datetime = new DateTime($chart_date . ' ' . $chart_time);
	$date = $datetime->format('Y-m-d'); // Extract date

	// Define the correct boundaries
	$today_day_start = new DateTime("$date 06:00:00"); // Today at 6 AM
	$today_night_start = new DateTime("$date 18:00:00"); // Today at 6 PM
	$tomorrow_morning_end = new DateTime("$next_day 05:59:59"); // Tomorrow at 6 AM
	$yesterday_morning_end = new DateTime("$previous_day 05:59:59"); // Tomorrow at 6 AM
	
	if($datetime < $today_day_start){
		if($weekday > 0){
			$weekday = $weekday - 1;
		}
		else{
			$weekday = 6; //move to Saturday if $weekday is Sunday (0)
		}
		
	}
	
	//updating Ema kantam
	if(strcmp($lang,"Tamil")===0 || strcmp($lang,"English")===0){
		switch($weekday)
		{
			case 0: //sun
				// Daytime condition (6 AM to 5:59 PM) 
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[5] = $inside[5] . " எ.க" . "-" . $sun_degree; //12:00-1:30
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[11] = $inside[11] . " எ.க" . "-" . $sun_degree; //6:00-7:30
				}
				
				//$inside[5] = $inside[5] . "எ.க" . "-" . $sun_degree; //kanni
				break;
			case 1: //mon
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[6] = $inside[6] . " எ.க" . "-" . $sun_degree; //10:30-12:00
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[2] = $inside[2] . " எ.க" . "-" . $sun_degree; //3:00-4:30
				}
				
				//$inside[6] = $inside[6] . "எ.க" . "-" . $sun_degree; //thulam
				break;
			case 2: //tue
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[8] = $inside[8] . " எ.க" . "-" . $sun_degree; //9:00-10:30
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[3] = $inside[3] . " எ.க" . "-" . $sun_degree; //1:30-3:00
				}
				
				//$inside[8] = $inside[8] . "எ.க" . "-" . $sun_degree; //thanusu
				break;
			case 3: //wed
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[9] = $inside[9] . " எ.க" . "-" . $sun_degree; //7:30-9:00
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[5] = $inside[5] . " எ.க" . "-" . $sun_degree; //12:00-1:30
				}
				
				//$inside[9] = $inside[9] . "எ.க" . "-" . $sun_degree; //makaram
				break;
			case 4: //thu
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[11] = $inside[11] . " எ.க" . "-" . $sun_degree; //6:00-7:30
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[6] = $inside[6] . " எ.க" . "-" . $sun_degree; //10:30-12:00
				}
				
				//$inside[11] = $inside[11] . " எ.க" . "-" . $sun_degree; //meenam
				break;
			case 5: //fri
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[2] = $inside[2] . " எ.க" . "-" . $sun_degree; //3:00-4:30
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[8] = $inside[8] . " எ.க" . "-" . $sun_degree; //9:00-10:30
				}
				
				//$inside[2] = $inside[2] . " எ.க" . "-" . $sun_degree; //mithunam
				break;
			case 6: //sat
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[3] = $inside[3] . " எ.க" . "-" . $sun_degree; //1:30-3:00
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[9] = $inside[9] . " எ.க" . "-" . $sun_degree; //7:30-9:00
				}
				
				//$inside[3] = $inside[3] . "எ.க" . "-" . $sun_degree; //katakam
				break;
			default: //none
				break;
		}
	}	
	
	debug($datetime->format('Y-m-d H:i:s') . $today_day_start->format('Y-m-d H:i:s') . $today_night_start->format('Y-m-d H:i:s'));
	
	//using R.K for Rahu Kaalam because using ரா.கா is causing issues when we replace raahu(ரா) later. 
	//So finnally I replace R.K to tamil
	if(strcmp($lang,"Tamil")===0){
		switch($weekday)
		{
			case 0: //sun
				// Daytime condition (6 AM to 5:59 PM)
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[0] = $inside[0] . " R.K" . "-" . $sun_degree;
				} else {
					// Nighttime (6 PM to 5:59 AM) 
					$inside[6] = $inside[6] . " R.K" . "-" . $sun_degree;
				}
				
				//$inside[0] = $inside[0] . "R.K" . "-" . $sun_degree; //mesham
				break;
			case 1: //mon
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[9] = $inside[9] . " R.K" . "-" . $sun_degree; //9:30-10:00
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[5] = $inside[5] . " R.K" . "-" . $sun_degree; //12:00-1:30
				}
				
				//$inside[9] = $inside[9] . "R.K" . "-" . $sun_degree; //makaram
				break;
			case 2: //tue
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[2] = $inside[2] . " R.K" . "-" . $sun_degree; //3:00-4:30
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[8] = $inside[8] . " R.K" . "-" . $sun_degree; //9:00-10:30
				}
				
				//$inside[2] = $inside[2] . "R.K" . "-" . $sun_degree; //mithunam
				break;
			case 3: //wed
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[5] = $inside[5] . " R.K" . "-" . $sun_degree; //12:00-1:30
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[0] = $inside[0] . " R.K" . "-" . $sun_degree; //4:30-6:00
				}
				
				//$inside[5] = $inside[5] . " R.K" . "-" . $sun_degree; //kanni
				break;
			case 4: //thu
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[3] = $inside[3] . " R.K" . "-" . $sun_degree; //1:30-3:00
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[9] = $inside[9] . " R.K" . "-" . $sun_degree; //7:30-9:00
				}
				
				//$inside[3] = $inside[3] . "R.K" . "-" . $sun_degree; //katakam
				break;
			case 5: //fri
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[6] = $inside[6] . " R.K" . "-" . $sun_degree; //10:30-12:00
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[2] = $inside[2] . " R.K" . "-" . $sun_degree; //3:00-4:30
				}
				
				//$inside[6] = $inside[6] . "R.K" . "-" . $sun_degree; //thulam
				break;
			case 6: //sat
				if ($datetime >= $today_day_start && $datetime < $today_night_start) { 
					$inside[8] = $inside[8] . " R.K" . "-" . $sun_degree; //9:00-10:30
				} else {
					// Nighttime (6 PM to 5:59 AM)
					$inside[3] = $inside[3] . " R.K" . "-" . $sun_degree; //1:30-3:00
				}
				
				//$inside[8] = $inside[8] . "R.K" . "-" . $sun_degree; //thanusu
				break;
			default: //none
				break;
		}
		
		$planetDeg["R.K"]=$sun_degree;
		$planetDeg["எ.க"]=$sun_degree;
		
		asort($planetDeg);
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
	global $moon_house,$dasa_balance,$birth_dasa_athipathi,$g_dasaBalance,$birth_dasa_athipathi_boy,$dasa_balance_boy, $full_moon_deg, $moon_house, $moon_degree,$planetDeg,$planetDeg_transit;
	
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
	//$one_pada_degree = 3.333333333333333;
	$one_pada_degree = number_format(800/60/4, 20);
	
	$nak_padam="";
	
	$dasa_lord_eng="";
	$dasa_lord_tam="";
	
	$nak_padam = getDasaAthipathi($nak);
	
	$already_covered=0;
	$remining=0;
	$balance=0;
	
	if($inner == true){ //birth inner or girl chart
		debug("moondeg=".$planetDeg['சந்']);
		$birth_dasa_athipathi = $nak_padam[0];
		//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
		$already_covered = $planetDeg['சந்'] - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0) - $nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
		$remining = $one_pada_minutes-$already_covered*60.0; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
	}
	else{
		debug("moondeg=".$planetDeg_transit['சந்']);
		$birth_dasa_athipathi_boy = $nak_padam[0];
		//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
		$already_covered = $planetDeg_transit['சந்'] - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0) - $nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
		$remining = $one_pada_minutes-$already_covered*60.0; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
	}	
	
	switch($nak_padam[0]){
		case "ket":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $ket_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra	
			
			debug("<BR>ket_dasa_period=$ket_dasa_period<BR>already_covered=$already_covered<BR>remining=$remining<BR>balance=$balance<BR>".$planetDeg['சந்']);
			debug("<BR>($nak_pad_idx)*$one_pada_degree====".(($nak_pad_idx)*$one_pada_degree));
			debug("<BR>($in_moon_house-1) * 30.0)====".(($in_moon_house-1) * 30.0));
			debug("<BR>$nak_padam[1]*$one_pada_degree====".($nak_padam[1]*$one_pada_degree));
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Kethu';
			$dasa_lord_tam = 'கேது';
			
			break;
		case "ven":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $ven_dasa_period/$one_pada_minutes; // calculation balance for venus by multiplying the remining with venus total dasa years / total minutesf or a nakshatra

			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Venus';
			$dasa_lord_tam = 'சுக்ர';
			
			break;
		case "sun":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $sun_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Sun';
			$dasa_lord_tam = 'சூரிய';
			
			break;
		case "moo":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $moo_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Moon';
			$dasa_lord_tam = 'சந்திர';
			
			break;
		case "mar":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $mar_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Mars';
			$dasa_lord_tam = 'செவ்வாய்';
			
			break;
		case "rah":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $rah_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Rahu';
			$dasa_lord_tam = 'ராகு';
			
			break;
		case "jup":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $jup_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Jupiter';
			$dasa_lord_tam = 'குரு';
		
			break;
		case "sat":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $sat_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
			
			debug("<BR>sat_dasa_period=$sat_dasa_period<BR>already_covered=$already_covered<BR>remining=$remining<BR>balance=$balance<BR>".$planetDeg['சந்']);
			debug("<BR>($nak_pad_idx)*$one_pada_degree====".(($nak_pad_idx)*$one_pada_degree));
			debug("<BR>($in_moon_house-1) * 30.0)====".(($in_moon_house-1) * 30.0));
			debug("<BR>$nak_padam[1]*$one_pada_degree====".($nak_padam[1]*$one_pada_degree));
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;
			
			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Saturn';
			$dasa_lord_tam = 'சனி';
			
			break;
		case "mer":
			//$already_covered = $in_moon_degree - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0)-$nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$already_covered = $planetDeg['சந்'] - (($nak_pad_idx)*$one_pada_degree-(($in_moon_house-1) * 30.0) - $nak_padam[1]*$one_pada_degree); // moon degree - (total degree till previous nak pada - total degree till moon's previous house - total degree of previous padas in current nak)
			//$remining = $one_pada_minutes-$already_covered*60.0; // multiplying by 60 to convert in minutes. Dedecting from 800 which is total minutes for one Nakshathra
			$balance =  $remining * $mer_dasa_period/$one_pada_minutes; // calculation balance for sun by multiplying the remining with sun total dasa years / total minutesf or a nakshatra
	
			debug("<BR>mer_dasa_period=$mer_dasa_period<BR>already_covered=$already_covered<BR>remining=$remining<BR>balance=$balance<BR>".$planetDeg['சந்']);
			debug("<BR>($nak_pad_idx)*$one_pada_degree====".(($nak_pad_idx)*$one_pada_degree));
			debug("<BR>($in_moon_house-1) * 30.0)====".(($in_moon_house-1) * 30.0));
			debug("<BR>$nak_padam[1]*$one_pada_degree====".($nak_padam[1]*$one_pada_degree));
			
			
			$whole = floor($balance);
			$decimal = $balance - floor($balance);
			
			$years = $whole;

			$decimal = $decimal*12;
			
			$months = floor($decimal);
			$decimal = $decimal - $months ;
			
			$decimal = $decimal*30;
			
			$days = floor($decimal);
			
			if($years == 0 && $months == 0 && $days == 0){
				$days = 1;
			}
			
			$decimal = $decimal - $days ;
			
			if($inner==true)
				$dasa_balance = array($years,$months,$days);
			else
				$dasa_balance_boy = array($years,$months,$days);
			
			$dasa_lord_eng = 'Mercury';
			$dasa_lord_tam = 'புதன்';
			
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
	echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
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
	echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
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
	global $chart_date,$current_dasa_bukthi,$g_bukthi_arr, $g_current_dasa_lord, $transit_date,$place_boy;
   
	$bukthi_lord_arr = array('ket'=>7,'ven'=>20,'sun'=>6,'moo'=>10,'mar'=>7,'rah'=>18,'jup'=>16,'sat'=>19,'mer'=>17);
	$bukthi_lord_order = array('ket','ven','sun','moo','mar','rah','jup','sat','mer');
	$bukthi_lord_order_tamil = array('கே','சுக்','சூரி','சந்','செ','ரா','குரு','சனி','புத');
   
	$curr_bukthi_start_date = $bukthi_start_date;
   
    $backtrace = debug_backtrace();
	if(strpos($backtrace[1]['file'],'panchang')>0){ //this is to check if this function is called from panchang or other page
		//$current_date = date('d-m-Y'); //for panchang we don't have transit data so we should use chart_date
		$current_date = date('d-m-Y',strtotime($chart_date));
	}
	elseif(isset($place_boy)){ //this is to check if this function is called from panchang or other page

		$current_date = date('d-m-Y');
	}
	else{
		$current_date = date('d-m-Y',strtotime($transit_date));  //for birth chart we have transit date
	}
   
	for($i=0; $i<9; $i++)
	{
		if($i<$curr_dasa_lord){
			continue;
		}
		else{
			$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
			$curr_bukthi_period = round($curr_bukthi_period * 365.2425*24*60*60);
			$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
		   
			$start = date('Y-m-d',strtotime($curr_bukthi_start_date));
			$end = date('Y-m-d',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
			
			debug("<br>i=$i, curr=$curr_dasa_lord");
			if($curr_dasa_lord==0 && $i==8){
				$curr_bukthi_end_date=$dasa_end_date;
				$end = date('Y-m-d',strtotime($curr_bukthi_end_date));
			}
			
			debug("$start    $end    $curr_bukthi_end_date");
	 
			debug("curr_bukthi_period=".($curr_bukthi_period/(365.2425*24*60*60))."   curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
			
			debug("curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
		   
			if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading' id='divCurrentBukthi'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			   
				$current_dasa_bukthi['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi['end_date'] = $curr_bukthi_end_date;
			}
			elseif(strtotime($chart_date) >= strtotime($curr_bukthi_start_date) and strtotime($chart_date) <= strtotime($curr_bukthi_end_date)){
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			}
			else{
				echo "<div class='divTableRow'   onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
					echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
			}
			
			$g_bukthi_arr[$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]] = array($bukthi_lord_order_tamil[$i],$curr_bukthi_start_date,$curr_bukthi_end_date);
		   
			//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
			$curr_bukthi_start_date = $curr_bukthi_end_date;
		}
	}
   
	//to calculate bukthi for planets previous to birth dasa
	for($i=0; $i<$curr_dasa_lord; $i++)
	{
		$curr_bukthi_period = ($bukthi_lord_arr[$bukthi_lord_order[$curr_dasa_lord]]/120) * $bukthi_lord_arr[$bukthi_lord_order[$i]];
		$curr_bukthi_period = round($curr_bukthi_period * 365.2425*24*60*60);
		$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
	   
		$start = date('Y-m-d',strtotime($curr_bukthi_start_date));
		$end = date('Y-m-d',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
		
		if($i==$curr_dasa_lord-1){
			$curr_bukthi_end_date=$dasa_end_date;
			$end = date('Y-m-d',strtotime($curr_bukthi_end_date));
		}
		
		debug("$start    $end    $curr_bukthi_end_date");
	 
		debug("curr_bukthi_period=".($curr_bukthi_period/(365.2425*24*60*60))."   curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
		
		if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
			echo "<div class='divTableRow'   onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
				echo "<div class='divBukthiHeading' id='divCurrentBukthi'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
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
			echo "<div class='divTableRow'   onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
				echo "<div class='divBukthiHeading'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
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
			echo "<div class='divTableRow'   onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
				echo "<div class='divBukthiHeading'>" . map_display_text($bukthi_lord_order_tamil[$curr_dasa_lord]) . " / " . map_display_text($bukthi_lord_order_tamil[$i]) . "</div>";
				echo "<div class='divBukthi'>$curr_bukthi_start_date</div>";
				if($i==$curr_dasa_lord-1){
					echo "<div class='divBukthi'>$dasa_end_date</div>";
				}
				else{
					echo "<div class='divBukthi'>$curr_bukthi_end_date</div>";
				}
			echo "</div>";
		}
	   
		$g_bukthi_arr[$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]] = array($bukthi_lord_order_tamil[$i],$curr_bukthi_start_date,$curr_bukthi_end_date);
		//$curr_bukthi_start_date=date('d-m-Y',strtotime($curr_bukthi_end_date. '+ 1 day'));
		$curr_bukthi_start_date = $curr_bukthi_end_date;
	}
   
   
	//var_dump($bukthi_arr);
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
			$curr_bukthi_period = round($curr_bukthi_period * 365.2425*24*60*60);
			$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
			
			$start = date('Y-m-d',strtotime($curr_bukthi_start_date));
			$end = date('Y-m-d',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
			
			debug("<br>i=$i, curr=$curr_dasa_lord");
			if($curr_dasa_lord==0 && $i==8){
				$curr_bukthi_end_date=$dasa_end_date;
				$end = date('Y-m-d',strtotime($curr_bukthi_end_date));
			}
			
			debug("$start    $end    $curr_bukthi_end_date");
	 
			debug("curr_bukthi_period=".($curr_bukthi_period/(365.2425*24*60*60))."   curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
			
			debug("curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
			
			if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				//echo "<div class='divTableRow'>";
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading_boy' id='divCurrentBukthi'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divCurrentBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
				
				$current_dasa_bukthi_boy['dasa'] = $bukthi_lord_order_tamil[$curr_dasa_lord];
				$current_dasa_bukthi_boy['bukthi'] = $bukthi_lord_order_tamil[$i];
				$current_dasa_bukthi_boy['start_date'] = $curr_bukthi_start_date;
				$current_dasa_bukthi_boy['end_date'] = $curr_bukthi_end_date;
			}
			elseif(strtotime($transit_date) >= strtotime($curr_bukthi_start_date) and strtotime($transit_date) <= strtotime($curr_bukthi_end_date)){
				//echo "<div class='divTableRow'>";
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading_boy'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_start_date</div>";
					echo "<div class='divBirthBukthi'>$curr_bukthi_end_date</div>";
				echo "</div>";
		}
			else{
				//echo "<div class='divTableRow'>";
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading_boy'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
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
		$curr_bukthi_period = round($curr_bukthi_period * 365.2425*24*60*60);
		$curr_bukthi_end_date = date('d-m-Y',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
		
		$start = date('Y-m-d',strtotime($curr_bukthi_start_date));
		$end = date('Y-m-d',strtotime($curr_bukthi_start_date. '+ '.$curr_bukthi_period.' seconds'));
		
		if($i==$curr_dasa_lord-1){
			$curr_bukthi_end_date=$dasa_end_date;
			$end = date('Y-m-d',strtotime($curr_bukthi_end_date));
		}
		
		debug("$start    $end    $curr_bukthi_end_date");
	 
		debug("curr_bukthi_period=".($curr_bukthi_period/(365.2425*24*60*60))."   curr_bukthi_start_date=$curr_bukthi_start_date      curr_bukthi_end_date=$curr_bukthi_end_date");
		
		if(strtotime($current_date) >= strtotime($curr_bukthi_start_date) and strtotime($current_date) <= strtotime($curr_bukthi_end_date)){
				//echo "<div class='divTableRow'>";
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading_boy'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
					echo "<div class='divCurrentBukthi'  id='divCurrentBukthi'>$curr_bukthi_start_date</div>";
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
				//echo "<div class='divTableRow'>";
				echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
					echo "<div class='divBukthiHeading_boy'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
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
			//echo "<div class='divTableRow'>";
			echo "<div class='divTableRow' onClick='generateAnthram(\"$start\", \"$end\",\"$bukthi_lord_order_tamil[$curr_dasa_lord]\",\"$bukthi_lord_order_tamil[$i]\");'>";
				echo "<div class='divBukthiHeading_boy'>".$bukthi_lord_order_tamil[$curr_dasa_lord]." / ".$bukthi_lord_order_tamil[$i]."</div>";
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
	

	echo "<center><div class='divTable blueTable' style='height:32px;display:none;margin-top:20px' id='DasaMatable'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
				echo "<H6 style='font-weight:bold'>" . __('menu_dasa') . "</H6>";
				echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' style='height:auto;display:none;' id='BukthiMatable'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading'>" . __('dasa_bukthi_adhipati') . "</div>";
			echo "<div class='divDasaHeading'>" . __('start') . "</div>";
			echo "<div class='divDasaHeading'>" . __('end') . "</div>";
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
				echo "<div class='divDasaHeading'>" . map_display_text($dasa_lord_order_tamil[$i]) . "</div>";
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
			echo "<div class='divDasaHeading'>" . map_display_text($dasa_lord_order_tamil[$i]) . "</div>";
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
	
	echo "<center><div class='divTable blueTable' style='height:32px;display:none;' id='DasaMatable_boy'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableOuterCell' colspan=3>";
				echo "<H6 style='font-weight:bold'>தசாபுக்தி</H6>";
				echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' style='height:auto;display:none;' id='BukthiMatable_boy'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divDasaHeading_boy'>தசா/புக்தி அதிபதி</div>";
			echo "<div class='divDasaHeading_boy'>Start</div>";
			echo "<div class='divDasaHeading_boy'>End</div>";
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
				echo "<div class='divDasaHeading_boy'>".$dasa_lord_order_tamil[$i]."</div>";
				echo "<div class='divDasa_boy'>$curr_dasa_start_date</div>";
				echo "<div class='divDasa_boy'>$curr_dasa_end_date</div>";
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
			echo "<div class='divDasaHeading_boy'>".$dasa_lord_order_tamil[$i]."</div>";
			echo "<div class='divDasa_boy'>$curr_dasa_start_date</div>";
			echo "<div class='divDasa_boy'>$curr_dasa_end_date</div>";
		echo "</div>";
		
		$curr_dasa_start_date=date('d-m-Y',strtotime($curr_dasa_end_date. '+ 1 day'));
		
		calculateVimBukthi_boy($i,$dasa_lord,$dasa_start_date,$curr_dasa_end_date);
	}
	
	echo "<div class='divTableRow'>";
	echo "</div>";
	
	echo "</div></center>";
}

function resortStarLordArray($inner=true){
	global $planet_starlord, $planet_starlord_transit;
	
	/******example
	$customer['address']    = '123 fake st';
	$customer['name']       = 'Tim';
	$customer['dob']        = '12/08/1986';
	$customer['dontSortMe'] = 'this value doesnt need to be sorted';

	$properOrderedArray = array_merge(array_flip(array('name', 'dob', 'address')), $customer);
	// or
	$properOrderedArray = array_replace(array_flip(array('name', 'dob', 'address')), $customer);

	// $properOrderedArray: array(
	//   'name'       => 'Tim',
	//   'dob'        => '12/08/1986',
	//   'address'    => '123 fake st',
	//   'dontSortMe' => 'this value doesnt need to be sorted')
	*/
	
	if($inner==true){  //inner chart/girl
		$temp_startlord = $planet_starlord;
	}
	else{    //outer transit/boy
		$temp_startlord = $planet_starlord_transit;
	}
	
	$sort_order = array('லக்', 'சூரி', 'சந்', 'செ', 'புத', 'குரு', 'சுக்', 'சனி', 'ரா', 'கே', 'மா');
	$temp_startlord = array_merge(array_flip($sort_order), $temp_startlord);
	
	if($inner==true){  //inner chart/girl
		$planet_starlord = $temp_startlord;
	}
	else{    //outer transit/boy
		$planet_starlord_transit = $temp_startlord;
	}
}

function displayStarLord()
{
	global $planetDeg,$sw_planet, $planet_starlord, $g_planetKaranaLord, $g_planetThithiLord, $g_planetYogamLord, 
	$g_planetVainasigamNak, $g_planetAbhijitNak,$planetDeg_default, $g_nak, $g_padham, $rearranged_arr, $nak_array ;
	
	//$planet_starlord = array();
	
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
			
			//if(strpos($planets,$planet)===0 || strpos($planets,$planet)>0){
			if (hasToken($planets, $planet)) {
				
				//$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord[$planet][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = "புஷ்";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] ='';
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
				$planet_starlord[$planet][4] = $starlord_idx;
				$planet_starlord[$planet][5] = ceil($full_planet_deg/30.0);
				
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
			

			//if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			//if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			if(strpos($planet,"ரா")===0 && hasToken($planets, "கே"))
			{
				//$planet_starlord["ரா"][0] = $nak_array[$starlord_idx_ragu];
				$planet_starlord["ரா"][0] = explode('/',$nak_array[$starlord_idx_ragu])[1]; //display tamil star name
				//$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
				$planet_starlord["கே"][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx_ragu==7 || $starlord_idx_ragu==9 || $starlord_idx_ragu==12 || $starlord_idx_ragu==14 || $starlord_idx_ragu==24 || $starlord_idx_ragu==26 || $starlord_idx_ragu==28 || 
				$starlord_idx_ragu==30 || $starlord_idx_ragu==43 || $starlord_idx_ragu==45 || $starlord_idx_ragu==48 || $starlord_idx_ragu==50 || $starlord_idx_ragu==60 || $starlord_idx_ragu==62 || 
				$starlord_idx_ragu==64 || $starlord_idx_ragu==66 || $starlord_idx_ragu==79 || $starlord_idx_ragu==81 || $starlord_idx_ragu==84 || $starlord_idx_ragu==86 || $starlord_idx_ragu==96 || 
				$starlord_idx_ragu==98 || $starlord_idx_ragu==100 || $starlord_idx_ragu==102){
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["ரா"][6] = "புஷ்";
				}
				else{
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
				}
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "புஷ்";
				}
				else{
					
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "";
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord["ரா"][4] = $starlord_idx_ragu;
				$planet_starlord["ரா"][5] = ceil($full_planet_deg/30.0);
				
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
				$planet_starlord["கே"][4] = $starlord_idx;
				$planet_starlord["கே"][5] = ceil($full_planet_deg/30.0);
			}
		}
	}
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		
		//calculate planet karana's
		calculateKaranamAllPlanets(true); //true for inner/girl
	
		//calculate planet thithi's
		calculateThithiAllPlanets(true); //true for inner/girl
	
		//calculate planet thithi's
		calculateYogamAllPlanets(true); //true for inner/girl
		
		//calculate planet thithi's
		calculateVainasigamAllPlanets(true); //true for inner/girl
		
		//calculate planet abhijit's
		calculateAbhijitAllPlanets(true); //true for inner/girl
	}		

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable' style='height:auto;display:none'>"; 
		echo "<br>";
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
				echo "<H6 style='font-weight:bold;color:black'>" . __('graha_saaram') . "</H6>";
			echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable' style='height:auto;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>" . __('graha') . "</div>";
				echo "<div class='divTableCell'>" . __('naksh_pad') . "</div>";
				echo "<div class='divTableCell'>" . __('tharai') . "</div>";
				echo "<div class='divTableCell'>" . __('sookshma_tharai') . "</div>";
				echo "<div class='divTableCell'>" . __('bhagai') . "</div>";
				//echo "<div class='divTableCell'>" . __('rasi_degree') . "</div>";
		echo "</div>";
	
	//Rearranging raasi/paadham array based on Moon paadham for Thaara palan
	$str = getShortStar($g_nak . ' ' . $g_padham);
	//findPaadham($str);
	$rearranged_arr = generateOrderedNakArray($str);
	
	foreach($planet_starlord as $planet => $data)
	{	
		echo "<div class='divTableRow'>";
			$planet_translated = __($planet);
			if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
				strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					//strcmp("புத",$planet)==0) and $planetDeg[$planet] < 0.0){
						
				echo "<div class='divPanchangHeadingSmall'>$planet_translated " . translate_tamil_text("(வ)") . "</div>";
			}
			else{
				echo "<div class='divPanchangHeadingSmall'>$planet_translated</div>";
			}
			
			$star_padham = getShortStar($data[0]);
			$star_padham_translated = translate_tamil_text($star_padham);
			
			if(strstr($data[6],"புஷ்") !== false){
				echo "<div class='divPanchangSmallGreen'>$star_padham_translated<BR>" . translate_tamil_text("(புஷ்)") . "</div>";
			}
			else{
				echo "<div class='divPanchangSmall'>" . $star_padham_translated ."</div>";
			}
			
			echo getThaaraPalan($data[0]);
			debug("planet-----".$data[0]."--------moon".$g_nak . ' ' . $g_padham);
			echo getThaaraHtml($data[0]);
			
			echo "<div class='divPanchangSmall'>" . DECtoDMS($data[2]) . " (" . DECtoDMS($data[3]) . ")" . "</div>";
			//echo "<div class='divPanchangSmall'>" . DECtoDMS($data[3]) . "</div>";
			
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		echo "<center><div class='divTable blueTable' id='thithiYogamKaranamHeadMutable' style='height:32px;display:none;margin-top:20px'>"; 
			echo "<div class='divTableRow'>";
				echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('planet_thithi_yogam_karanam') . "</H6>";
				echo "</div>";
			echo "</div>";
		echo "</div></center>";

		echo "<center><div class='divTable blueTable' id='thithiYogamKaranamBodyMutable' style='height:auto;display:none'>"; 
		echo "<div class='divTableRow'>";
			    echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_thithi') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_yogam') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_karanam') . "</div>";
		    echo "</div>";
		
		foreach($planet_starlord as $planet => $data)
		{
			echo "<div class='divTableRow'>";
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>".__($planet)." ".__("(வ)")."</div>";
				}
				else{
					echo "<div class='divPanchangHeadingSmall'>".__($planet)."</div>";
				}
					
				
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetThithiLord[$planet]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetYogamLord[$planet]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetKaranaLord[$planet]) . "</div>";
			echo "</div>";
		}

		echo "</div></center>";
		
		echo "<center><div class='divTable blueTable' id='abhijitHeadMutable' style='height:32px;display:none;margin-top:20px'>"; 
			echo "<div class='divTableRow'>";
				echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('planet_vainasika_abijith') . "</H6>";
				echo "</div>";
			echo "</div>";
		echo "</div></center>";

		echo "<center><div class='divTable blueTable' id='abhijitBodyMutable' style='height:auto;display:none'>"; 
			echo "<div class='divTableRow'>";
					echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
					echo "<div class='divTableCell'>" . __('col_vaina') . "</div>";
					echo "<div class='divTableCell'>" . __('col_vaina_nivarthi') . "</div>";
					echo "<div class='divTableCell'>" . __('col_abijit') . "</div>";
			echo "</div>";
		
		foreach($planet_starlord as $planet => $data)
		{
			echo "<div class='divTableRow'>";
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>" . __($planet) . " " . __("(வ)") . "</div>";
				}
				else{
					echo "<div class='divPanchangHeadingSmall'>" . __($planet) . "</div>";
				}
				
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetVainasigamNak[$planet][0]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetVainasigamNak[$planet][1]) . "</div>";
				// Map each Abhijit nak entry using map_display_text
				$abh_items = array_map(function($it){ return map_display_text($it); }, $g_planetAbhijitNak[$planet]);
				echo "<div class='divPanchangSmall'>" . join("<BR>", $abh_items) . "</div>";
			echo "</div>";
		}
		
		echo "</div></center>";
		
		echo "<center><div class='divTable blueTable' id='kpHeadMutable' style='height:32px;display:none;margin-top:20px'>"; 
			echo "<div class='divTableRow'>";
				echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('menu_kp_details') . "</H6>";
				echo "</div>";
			echo "</div>";
		echo "</div></center>";

		echo "<center><div class='divTable blueTable' id='kpBodyMutable' style='height:auto;display:none'>"; 
		echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
				echo "<div class='divTableCell'>" . __('col_pagai') . "</div>";
				echo "<div class='divTableCell'>" . __('col_naksha_pa') . "</div>";
				echo "<div class='divTableCell'>" . __('col_na') . "</div>";
				echo "<div class='divTableCell'>" . __('col_ua') . "</div>";
			echo "</div>";
		
		foreach($planet_starlord as $planet => $data)
		{	
			echo "<div class='divTableRow'>";
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
						//strcmp("புத",$planet)==0) and $planetDeg[$planet] < 0.0){
							
					echo "<div class='divPanchangHeadingSmall'>" . __($planet) . " " . __("(வ)") . "</div>";
				}
				else{
						echo "<div class='divPanchangHeadingSmall'>" . __($planet) . "</div>";
				}
				
					echo "<div class='divPanchangSmall'>" . map_display_text(DECtoDMS($data[2]) . ' (' . DECtoDMS($data[3]) . ')') . "</div>";
					
					$star_padham = getShortStar($data[0]);
					
					if(strstr($data[6],"புஷ்") !== false){
						echo "<div class='divPanchangSmallGreen'>" . map_display_text($star_padham) . "<BR>" . __("(புஷ்)") . "</div>";
					}
					else{
						echo "<div class='divPanchangSmall'>" . map_display_text($star_padham) ."</div>";
					}
					
					echo "<div class='divPanchangSmall'>" . map_display_text($data[1]) . "</div>";
					
					echo "<div class='divPanchangSmall'>" . map_display_text(findSubLord($data[1],$data[2])) . "</div>";
				
			echo "</div>";
		}

		echo "</div></center>";
	}
	
	//displayStarLord_transit();
}


//This function sperated for jamakol to exclude unnecessary functionality like vainasigam, karanam
function displayStarLord_jamakol()
{
	global $planetDeg,$sw_planet, $planet_starlord, $g_planetKaranaLord, $g_planetThithiLord, $g_planetYogamLord, $g_planetVainasigamNak, $g_planetAbhijitNak,$planetDeg_default, $nak_array;
	
	//$planet_starlord = array();
	
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
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = "புஷ்";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = '';
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
				$planet_starlord[$planet][4] = $starlord_idx;
				$planet_starlord[$planet][5] = ceil($full_planet_deg/30.0);
				
				/*if(!(strpos($planets,"ரா.கா")===0 || strpos($planets,"ரா.கா")>0) && (strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0)){
					//echo $house. "-" . $planets ."<br>";
					//echo $planet_starlord[$planet][1];
					
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
					$planet_starlord[$planet][3] = $deg;
					
					echo "|".$planet_starlord[$planet][2]."-".$planet."|";
					echo "|".$planet_starlord[$planet][3]."-".$planet."|";
				}*/
				
				debug("<BR>planet=$planet,fulldegree=".$full_planet_deg."house=".$house."deg=$deg");
			}
			
			//if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
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
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["ரா"][6] = "புஷ்";
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "புஷ்";
				}
				else{
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "";
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord["ரா"][4] = $starlord_idx_ragu;
				$planet_starlord["ரா"][5] = ceil($full_planet_deg/30.0);
				
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
				$planet_starlord["கே"][4] = $starlord_idx;
				$planet_starlord["கே"][5] = ceil($full_planet_deg/30.0);
			}
		}
	}
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		
		//calculate planet karana's
		calculateKaranamAllPlanets(true); //true for inner/girl
	
		//calculate planet thithi's
		calculateThithiAllPlanets(true); //true for inner/girl
	
		//calculate planet thithi's
		calculateYogamAllPlanets(true); //true for inner/girl
		
		//calculate planet thithi's
		calculateVainasigamAllPlanets(true); //true for inner/girl
		
		//calculate planet abhijit's
		calculateAbhijitAllPlanets(true); //true for inner/girl
	}

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable' style='height:auto;display:none'>"; 
		echo "<br>";
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
				echo "<H6 style='font-weight:bold;color:black'>" . __('graha_saaram') . "</H6>";
			echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable' style='height:auto;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>" . __('graha') . "</div>";
				echo "<div class='divTableCell'>" . __('naksh_pad') . "</div>";
				echo "<div class='divTableCell'>" . __('col_nakshatra_athi') . "</div>";
				echo "<div class='divTableCell'>" . __('bhagai') . "</div>";
				echo "<div class='divTableCell'>" . __('rasi_degree') . "</div>";
		echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{	
		echo "<div class='divTableRow'>";
		$planet_translated = translate_planet_abbr($planet);
		if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
			strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
				//strcmp("புத",$planet)==0) and $planetDeg[$planet] < 0.0){
					
				echo "<div class='divPanchangHeadingSmall'>$planet_translated " . translate_tamil_text("(வ)") . "</div>";
		}
		else{
			echo "<div class='divPanchangHeadingSmall'>$planet_translated</div>";
		}
		
		echo "<div class='divPanchangSmall'>" . translate_tamil_text($data[0]) . "</div>";
		
		if(strstr($data[1],"புஷ்") !== false){
			echo "<div class='divPanchangSmallGreen'>" . translate_tamil_text($data[1]) . "</div>";
		}
		else{
			echo "<div class='divPanchangSmall'>" . translate_tamil_text($data[1]) . "</div>";
		}
		
		echo "<div class='divPanchangSmall'>" . DECtoDMS($data[2]) . "</div>";
		echo "<div class='divPanchangSmall'>" . DECtoDMS($data[3]) . "</div>";
	echo "</div>";
}	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		echo "<center><div class='divTable blueTable' id='thithiYogamKaranamHeadMutable' style='height:32px;display:none;margin-top:20px'>"; 
			echo "<div class='divTableRow'>";
				echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('planet_thithi_yogam_karanam') . "</H6>";
				echo "</div>";
			echo "</div>";
		echo "</div></center>";

		echo "<center><div class='divTable blueTable' id='thithiYogamKaranamBodyMutable' style='height:auto;display:none'>"; 
		echo "<div class='divTableRow'>";
			    echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_thithi') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_yogam') . "</div>";
			    echo "<div class='divTableCell'>" . __('col_karanam') . "</div>";
		    echo "</div>";
		
		foreach($planet_starlord as $planet => $data)
		{
			echo "<div class='divTableRow'>";
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>".__($planet)." ".__("(வ)")."</div>";
				}
				else{
					echo "<div class='divPanchangHeadingSmall'>".__($planet)."</div>";
				}
					
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetThithiLord[$planet]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetYogamLord[$planet]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($g_planetKaranaLord[$planet]) . "</div>";
			echo "</div>";
		}

		echo "</div></center>";
		
		echo "<center><div class='divTable blueTable' id='abhijitHeadMutable' style='height:32px;display:none;margin-top:20px'>"; 
			echo "<div class='divTableRow'>";
				echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('planet_vainasika_abijith') . "</H6>";
				echo "</div>";
			echo "</div>";
		echo "</div></center>";

		echo "<center><div class='divTable blueTable' id='abhijitBodyMutable' style='height:auto;display:none'>"; 
			echo "<div class='divTableRow'>";
					echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
					echo "<div class='divTableCell'>" . __('col_vaina') . "</div>";
					echo "<div class='divTableCell'>" . __('col_vaina_nivarthi') . "</div>";
					echo "<div class='divTableCell'>" . __('col_abijit') . "</div>";
			echo "</div>";
		
		foreach($planet_starlord as $planet => $data)
		{
			echo "<div class='divTableRow'>";
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>$planet (வ)</div>";
				}
				else{
					echo "<div class='divPanchangHeadingSmall'>$planet</div>";
				}
				
				echo "<div class='divPanchangSmall'>" . $g_planetVainasigamNak[$planet][0] . "</div>";
				echo "<div class='divPanchangSmall'>" . $g_planetVainasigamNak[$planet][1] . "</div>";
				echo "<div class='divPanchangSmall'>" . join("<BR>",$g_planetAbhijitNak[$planet]) . "</div>";
			echo "</div>";
		}
		
		echo "</div></center>";
	}
	
	//displayStarLord_transit();
}


function displayStarLord_transit()
{
	global $planetDeg_transit,$tr_sw_planet, $planet_starlord_transit,$g_planetKaranaLord_boy, $g_planetThithiLord_boy, $g_planetYogamLord_boy, $planetDeg_default_transit, $nak_array;
	
	$planet_starlord_transit = array();
	
	$nak_lord = array("dummy","கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத",
						"கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத",
						"கே","கே","கே","கே","சுக்","சுக்","சுக்","சுக்","சூரி","சூரி","சூரி","சூரி","சந்","சந்","சந்","சந்","செ","செ","செ","செ","ரா","ரா","ரா","ரா","குரு","குரு","குரு","குரு","சனி","சனி","சனி","சனி","புத","புத","புத","புத");
	
	
	foreach($planetDeg_transit as $planet => $deg)
	{
		foreach($tr_sw_planet as $house => $planets)
		{
			//starlord calculation
			$full_planet_deg = ($house * 30 + $deg);
			$full_planet_deg_ragu = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
			$starlord_idx = ceil($full_planet_deg/3.333333);
			$starlord_idx_ragu = ceil($full_planet_deg_ragu/3.333333);
			
			if(strpos($planets,$planet)===0 || strpos($planets,$planet)>0){
				
				//$planet_starlord_transit[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord_transit[$planet][0] = explode('/',$nak_array[$starlord_idx])[1];  //display only tamil
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord_transit[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord_transit[$planet][6] = "புஷ்";
				}
				else{
					$planet_starlord_transit[$planet][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord_transit[$planet][2] = $full_planet_deg;
				$planet_starlord_transit[$planet][3] = $deg;
				$planet_starlord_transit[$planet][4] = $starlord_idx;
			}
			
			if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				
				//$planet_starlord_transit["கே"][0] = $nak_array[$starlord_idx];
				$planet_starlord_transit["கே"][0] = explode('/',$nak_array[$starlord_idx])[1];  //display only tamil
				//$planet_starlord["ரா"][0] = $nak_array[$starlord_idx_ragu];
				$planet_starlord_transit["ரா"][0] = explode('/',$nak_array[$starlord_idx_ragu])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord_transit["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord_transit["கே"][6] = "புஷ்";
					$planet_starlord_transit["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord_transit["ரா"][6] = "புஷ்";
				}
				else{
					$planet_starlord_transit["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord_transit["ரா"][1] = $nak_lord[$starlord_idx_ragu];
				}
				
				$planet_starlord_transit["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord_transit["ரா"][3] = $deg;
				$planet_starlord_transit["ரா"][4] = $starlord_idx_ragu;
				
				$planet_starlord_transit["கே"][2] = $full_planet_deg;
				$planet_starlord_transit["கே"][3] = $deg;
				$planet_starlord_transit["கே"][4] = $starlord_idx;
				
			}
		}
	}
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		//calculate planet karana's
		calculateKaranamAllPlanets(false); //false for outer/transit
		
		//calculate planet thithi's
		calculateThithiAllPlanets(false); //false for outer/transit
		
		//calculate planet thithi's
		calculateYogamAllPlanets(false); //false for outer/transit
		
	}
	
	resortStarLordArray(false);  //this is to sort the planets in correct order
	
	echo "<center><div class='divTable blueTable' id='starlordHeadMutable_transit' style='height:32px;display:none;margin-top:20px'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('kot_planet_essence') . "</H6>";
					echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable_transit' style='height:auto;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>" . __('col_graha') . "</div>";
				echo "<div class='divTableCell'>" . __('col_nakshatra') . "</div>";
				echo "<div class='divTableCell'>" . __('col_nakshatra_athi') . "</div>";
				echo "<div class='divTableCell'>" . __('col_pa') . "</div>";
				echo "<div class='divTableCell'>" . __('col_rasi_pa') . "</div>";
		echo "</div>";
	
	foreach($planet_starlord_transit as $planet => $data)
	{
		echo "<div class='divTableRow'>";
			if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
				strcmp("புத",$planet)==0) and $planetDeg_default_transit[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>" . __($planet) . " " . __("(வ)") . "</div>";
			}
			else{
					echo "<div class='divPanchangHeadingSmall'>" . __($planet) . "</div>";
			}
				echo "<div class='divPanchangSmall'>" . map_display_text($data[0]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text($data[1]) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text(DECtoDMS($data[2])) . "</div>";
				echo "<div class='divPanchangSmall'>" . map_display_text(DECtoDMS($data[3])) . "</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";	
}

function getVainasigam($inner=true){
	global $planetDeg_transit,$tr_sw_planet, $planet_starlord, $planet_starlord_boy, $nak_array;
	
	$nak_arr = $nak_array;
	
	if($inner==true){ //girl	
		$vaisaisam_idx = $planet_starlord['சந்'][4] + VAINASIGAM_PAD_COUNT - 1;
			
		if($vaisaisam_idx > 108){
			$vaisaisam_idx = $vaisaisam_idx - 108;
		}
		
		$nivarthi_star_idx = $vaisaisam_idx + 28 - 1; //7th star 28th paatham
		
		if($nivarthi_star_idx > 108){
			$nivarthi_star_idx = $nivarthi_star_idx - 108;
		}
		
		$vainasigam_array = array(getValue($nak_arr[$vaisaisam_idx]), getValue($nak_arr[$nivarthi_star_idx])); //getValue 2nd argument is lang, which is defaulted to 1 (tamil)
	}
	else{
		$vaisaisam_idx = $planet_starlord_boy['சந்'][4] + VAINASIGAM_PAD_COUNT - 1;
			
		if($vaisaisam_idx > 108){
			$vaisaisam_idx = $vaisaisam_idx - 108;
		}
		
		$nivarthi_star_idx = $vaisaisam_idx + 28 - 1; //7th star 28th paatham
		
		if($nivarthi_star_idx > 108){
			$nivarthi_star_idx = $nivarthi_star_idx - 108;
		}
		
		$vainasigam_array = array(getValue($nak_arr[$vaisaisam_idx]), getValue($nak_arr[$nivarthi_star_idx])); //getValue 2nd argument is lang, which is defaulted to 1 (tamil)
	}
	
	return $vainasigam_array;
}

function calculateStarLord()
{
	global $planetDeg,$sw_planet, $planet_starlord, $g_planetKaranaLord, $g_planetThithiLord, $g_planetYogamLord, $nak_array;
	
	//$planet_starlord = array();
	
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
			
			//if(strpos($planets,$planet)===0 || strpos($planets,$planet)>0){
			if(hasToken($planets,$planet)){
				//$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord[$planet][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = "புஷ்";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = '';
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
				$planet_starlord[$planet][4] = $starlord_idx;
				
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
			
			//if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && hasToken($planets,"கே"))
			{
				//$planet_starlord["ரா"][0] = $nak_array[$starlord_idx_ragu];
				$planet_starlord["ரா"][0] = explode('/',$nak_array[$starlord_idx_ragu])[1]; //display tamil star name
				//$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
				$planet_starlord["கே"][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["ரா"][6] = "புஷ்";
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "புஷ்";
				}
				else{
					
					$planet_starlord["ரா"][1] = $nak_lord[$starlord_idx_ragu];
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "";
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord["ரா"][4] = $starlord_idx_ragu;
				
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
				$planet_starlord["கே"][4] = $starlord_idx;
			}
		}
	}
	
	if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		//calculate planet karana's
		calculateKaranamAllPlanets(true); //true for inner/girl
		
		//calculate planet thithi's
		calculateThithiAllPlanets(true); //true for inner/girl
		
		//calculate planet thithi's
		calculateYogamAllPlanets(true); //true for inner/girl
		
		//calculate planet thithi's
		calculateVainasigamAllPlanets(true); //true for inner/girl
		
		//calculate planet abhijit's
		calculateAbhijitAllPlanets(true); //true for inner/girl
		
	}
}

function displayStarLord_boy()
{
	global $planetDeg_transit,$tr_sw_planet, $g_planetKaranaLord_boy, $planet_starlord_boy, $planetDeg_default, $nak_array ;
	
	$planet_starlord = array();
	
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
				
				//$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord[$planet][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name

				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = "புஷ்";
				}
				else{
					$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = '';
				}
				
				$planet_starlord[$planet][2] = $full_planet_deg;
				$planet_starlord[$planet][3] = $deg;
				$planet_starlord[$planet][4] = $starlord_idx;
			}
			
			if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				//starlord calculation
				$full_planet_deg = ($house * 30 + $deg);
				$starlord_idx = ceil($full_planet_deg/3.333333);
				
				$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord["கே"][6] = "புஷ்";
				}
				else{
					$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord[$planet][6] = '';
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord["ரா"][4] = $starlord_idx_ragu;
				
				$planet_starlord["கே"][2] = $full_planet_deg;
				$planet_starlord["கே"][3] = $deg;
				$planet_starlord["கே"][4] = $starlord_idx;
			}
		}
	}
	
	$planet_starlord_boy = $planet_starlord;

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable_boy' style='height:32px;display:none'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableOuterCell' colspan=3 style='height:32px !important'>";
				echo "<H6 style='font-weight:bold;color:black'>" . __('graha_saaram') . "</H6>";
			echo "</div>";
		echo "</div>";
	echo "</div></center>";
	
	//calculate planaet karana's
	//calculateKaranamAllPlanets(false); //false for boy, outer

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable_boy' style='height:auto;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableOuterCell'>" . __('graha') . "</div>";
				echo "<div class='divTableOuterCell'>" . __('naksh_pad') . "</div>";
				echo "<div class='divTableOuterCell'>" . __('col_nakshatra_athi') . "</div>";
				echo "<div class='divTableOuterCell'>" . __('bhagai') . "</div>";
				echo "<div class='divTableOuterCell'>" . __('rasi_degree') . "</div>";
				//echo "<div class='divTableOuterCell'>Karanam</div>";
		echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{
		echo "<div class='divTableRow'>";
				$planet_translated = translate_planet_abbr($planet);
				if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
					strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					echo "<div class='divPanchangHeadingSmall'>$planet_translated " . translate_tamil_text("(வ)") . "</div>";
				}
				else{
					echo "<div class='divPanchangHeadingSmall'>$planet_translated</div>";
				}
				echo "<div class='divPanchang'>" . translate_tamil_text($data[0]) . "</div>";
				echo "<div class='divPanchang'>" . translate_tamil_text($data[1]) . "</div>";
				echo "<div class='divPanchang'>" . DECtoDMS($data[2]) . "</div>";
				echo "<div class='divPanchang'>" . DECtoDMS($data[3]) . "</div>";
				//echo "<div class='divPanchang'>" . $g_planetKaranaLord_boy[$planet] . "</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
}

function calculateStarLord_boy()
{
	global $planetDeg_transit,$tr_sw_planet, $g_planetKaranaLord_boy, $planet_starlord_boy, $nak_array;
	
	//$planet_starlord = array();
	
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
				
				//$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
				$planet_starlord_boy[$planet][0] = explode('/',$nak_array[$starlord_idx])[1]; //display tamil star name

				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord_boy[$planet][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					$planet_starlord_boy[$planet][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord_boy[$planet][2] = $full_planet_deg;
				$planet_starlord_boy[$planet][3] = $deg;
				$planet_starlord_boy[$planet][4] = $starlord_idx;
			}
			
			if((strpos($planets,"ரா")===0 || strpos($planets,"ரா")>0) && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				//starlord calculation
				$full_planet_deg = ($house * 30 + $deg);
				$starlord_idx = ceil($full_planet_deg/3.333333);
				
				$planet_starlord_boy["கே"][0] = $nak_array[$starlord_idx];
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord_boy["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord_boy["கே"][6] = "புஷ்";
				}
				else{
					$planet_starlord_boy["கே"][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord_boy["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord_boy["ரா"][3] = $deg;
				$planet_starlord_boy["ரா"][4] = $starlord_idx_ragu;
				
				$planet_starlord_boy["கே"][2] = $full_planet_deg;
				$planet_starlord_boy["கே"][3] = $deg;
				$planet_starlord_boy["கே"][4] = $starlord_idx;
			}
		}
	}
	
	//$planet_starlord_boy = $planet_starlord;
	
	/*if(strpos(debug_backtrace()[0]['file'],"new_panchang")===false){
		//calculate planet karana's
		calculateKaranamAllPlanets(false); //false for outer/boy
		
		//calculate planet thithi's
		calculateThithiAllPlanets(false); //false for outer/boy
		
		//calculate planet thithi's
		calculateYogamAllPlanets(false); //false for outer/boy
		
		//calculate planet thithi's
		calculateVainasigamAllPlanets(false); //false for outer/boy
		
		//calculate planet abhijit's
		calculateAbhijitAllPlanets(false); //false for outer/boy
		
	}*/
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
	global $panchang,$g_nak;
	
	//Array format Nak => sukla paksha Pakshi, krishna paksha pakshi
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
		
		$g_nak = explode(' ',explode("/",$panchang['nak'])[1])[0];
		
		//Manikandan sir said in his method, pakshi is same for both paksham.
		/*$paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
		
		if(strpos("வளர்",$paksham)===0){
			return $pancha_pakshi[$g_nak][0];
		}
		else{
			return $pancha_pakshi[$g_nak][1];
		}*/
		
		return $pancha_pakshi[$g_nak][0];
}


function displayPanchang($date=null,$time=null,$place=null){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $lagna_house;
	
	calculatePanchang();
	$thithi_parts = explode('/', $panchang['thithi']);
	if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
		$thithi_display = panchang_display_part($panchang['thithi']);
	}
	elseif(function_exists('is_english') && is_english()){
		$thithi_display = ($thithi_parts[0] ?? $panchang['thithi']) . (isset($thithi_parts[2]) ? '[' . map_display_text($thithi_parts[2]) : '');
	}
	else{
		$thithi_display = explode('[',$thithi_parts[1] ?? $panchang['thithi'])[0] . (isset($thithi_parts[2]) ? '[' . $thithi_parts[2] : '');
	}
	
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
			echo "<div class='divPanchangHeading'>" . __('weekday') . "</div>";
			echo "<div class='divPanchang'>".panchang_display_part($panchang['vaaram'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('nakshatra') . "</div>";
			echo "<div class='divPanchang'>".panchang_display_part($panchang['nak'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('thithi') . "</div>";
			echo "<div class='divPanchang'>".$thithi_display."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('karanam') . "</div>";
			echo "<div class='divPanchang' OnClick='showKaranamDiv()' style='color:blue;cursor:pointer'>".panchang_display_part($panchang['karan'])." (" . __('click_here') . ")</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('yogam') . "</div>";
			echo "<div class='divPanchang'>".map_display_text($panchang['yoga'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('lagna') . "</div>";
			echo "<div class='divPanchang'>".map_display_text(getRasi($lagna_house-1))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('rasi') . "</div>";
			echo "<div class='divPanchang'>".map_display_text(getRasi($moon_house-1))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunrise') . "</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunset') . "</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunset)[1]."</div>";
		echo "</div>";
	echo "</div>";
}

function panchang_display_part($value, $tamil_index = 1) {
	$parts = explode('/', $value);
	if (function_exists('is_english') && is_english()) {
		return map_display_text($parts[0] ?? $value);
	}
	return $parts[$tamil_index] ?? end($parts);
}

function displayPanchang_jamakol(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $lagna_house, $g_paksham;
	
	$thithi_parts = explode('/', $panchang['thithi']);
	if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
		$thithi_display = panchang_display_part($panchang['thithi']);
	}
	elseif(function_exists('is_english') && is_english()){
		$thithi_display = ($thithi_parts[0] ?? $panchang['thithi']) . (isset($thithi_parts[2]) ? '[' . map_display_text($thithi_parts[2]) : '');
	}
	else{
		$thithi_display = explode('[',$thithi_parts[1] ?? $panchang['thithi'])[0] . (isset($thithi_parts[2]) ? '[' . $thithi_parts[2] : '');
	}
	
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;' id='panchangBody'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('weekday') . "</div>";
			echo "<div class='divPanchang'>".panchang_display_part($panchang['vaaram'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('nakshatra') . "</div>";
			echo "<div class='divPanchang'>".panchang_display_part($panchang['nak'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('thithi') . "</div>";
			echo "<div class='divPanchang'>".$thithi_display."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('karanam') . "</div>";
			echo "<div class='divPanchang'>".panchang_display_part($panchang['karan'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('yogam') . "</div>";
			echo "<div class='divPanchang'>".map_display_text($panchang['yoga'])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('lagna') . "</div>";
			echo "<div class='divPanchang'>".map_display_text(getRasi($lagna_house-1))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('rasi') . "</div>";
			echo "<div class='divPanchang'>".map_display_text(getRasi($moon_house-1))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('current_horai') . "</div>";
			echo "<div class='divPanchang' id='hora'></div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunrise') . "</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunset') . "</div>";
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
	
	//echo ("swetest64 -edir$libPath -b$date -ut$time -p1 -n1 -sid1 -eswe -fl -head");
}

function calculatePanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek,$panchang,$nak_pad_idx,$today_sunrise, $today_sunset, $chart_time, $chart_date, $moon_degree_exact,$TZ,$full_moon_deg,$full_sun_deg, 
		$g_padham,$nak_array;
	
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
	
	//$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
	//				"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	$weekday_arr = array("Sunday/ஞாயிறு","Monday/திங்கள்","Tuesday/செவ்வாய்","Wednesday/புதன்",
					"Thursday/வியாழன்","Friday/வெள்ளி","Saturday/சனி");
	
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி","விஷ்கம்பம்");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333333333333);
	
	if($nak_idx > 108){ //it can go beyond 108 due to approxmiate division above
		$nak_idx = 108;
	}
	
	debug ("nak_idx=$nak_idx");
	
	//Get nak end time
	$nak_end_time = findNakEndTime($nak_idx);
	
	debug("NAK_END_TIME=$nak_end_time");
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$nak_pad_idx = $nak_idx;
	$g_padham = explode(' ',explode('/',$panchang['nak'])[1])[1];
	
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
			
			if($thi_idx >=0 and $thi_idx <=14)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			elseif($thi_idx >14 and $thi_idx <=29)
			{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			debug("MOON-SUN DEGREE=".($full_moon_deg - $full_sun_deg));//testing
			
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor(abs($idx));
			debug("IDX=".(floor(abs($idx))));
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
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
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
		
		debug(__LINE__ . ":". "MOON-SUN DEGREE=".($full_moon_deg - $full_sun_deg));//testing
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
		
		if(strpos(debug_backtrace()[0]['file'],'Matching')!==false){
			$class_name = "actionBtnGirl";
		}
		
		$button_id = "buttons_inner";
	}
	else{
		$class_name = 'actionBtnBoy';
		$starlord_function = 'showStarLordTable_boy()';
		$dasa_function = 'showDasaTable_boy()';
		
		$button_id = "buttons_outer";
	}

	// prepare translated labels for buttons
	$btn_star_label = __('btn_star_lord');
	$btn_dasa_label = __('btn_dasa_bukthi');

	echo "<center><div class='divTable blueTable' style='height:auto;display:none' id='$button_id'>"; 
		echo "<div class='divTableRow'>";
			echo <<<EOL
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="starlord" name="starlord" onclick="$starlord_function">$btn_star_label</button>
				</div>
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="dasa" name="dasa" onclick="$dasa_function">$btn_dasa_label</button>
				</div>
EOL;
		echo "</div>";
	echo "</div></center>";


	if($inner == true){
		displayStarLord();
		calculateVimDasa();
	}
	else{
		displayStarLord_boy();
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
	
	echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='$class_name' colspan=3>";
					echo "<H6 style='font-weight:bold'>" . __('thithi_soonyam') . "</H6>";
					echo "</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchang'>" . map_display_text($thithi_arr[$thithi]) . "</div>";
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
	if($full_moon_deg-$full_sun_deg < 12 && $full_moon_deg > $full_sun_deg){
		$thithi_deg = $full_moon_deg-$full_sun_deg;
	}
	elseif($full_moon_deg < $full_sun_deg){
		$thithi_deg = ((($full_moon_deg-$full_sun_deg+360)/12 - floor(($full_moon_deg-$full_sun_deg+360)/12))) * 12;
		
	}
	else{
		$thithi_deg = ((($full_moon_deg-$full_sun_deg)/12 - floor(($full_moon_deg-$full_sun_deg)/12))) * 12;
	}
	
	debug(__FUNCTION__."-THITHI_DEG=$thithi_deg");
		
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
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam_main'>" . __('thrisoolam_abbr') . "</span>";
			}
			else{
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam'>" . __('thrisoolam_abbr') . "</span>";
			}
		}
		else{
				$g_thithi_soonyam_arr[$soonya_house-1] = " <span class='thithi_soonyam'>" . __('thrisoolam_abbr') . "</span>";
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
		<P style="color:#660000;font-weight:bold; font-size:16px;  background-color:white;">கதிர்கள்</P>
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

	$in_deg = sprintf("%.2f", $in_deg);
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

//returns the rasi house from the start house
function targetHouse($start_house,$number_of_houses)
{
	debug("TARGETHOUSE:$start_house");
	debug("TARGETHOUSE:$number_of_houses");
	
	if($start_house+$number_of_houses > 12){
		$temp = $start_house - 1 + $number_of_houses-12;
		
		return $start_house - 1 + $number_of_houses-12;
		/*if($temp==0){
			return 12;
		}	
		else{
			return $temp;
		}*/
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
	
	debug("LAGNA 9TH HOUSE=".targetHouse($lagna_house,9));
	
	$lagna_9th_house = targetHouse($lagna_house,9);
	
	if($lagna_9th_house==0){
		$lagna_9th_house = 12;
	}
	
	switch($lagna_9th_house){
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
	
	debug("MOON 9TH HOUSE=".targetHouse($moon_house,9));
	
	$moon_9th_house = targetHouse($moon_house,9);
	
	if($moon_9th_house==0){
		$moon_9th_house = 12;
	}
	
	switch($moon_9th_house){
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
	
	debug("LAGNA_RAY=$lagna_ray MOON_RAY=$moon_ray"."COUNT=".$count." MOON_HOUSE=".$moon_house." LAGNA House=".$lagna_house." INDHU HOUSE=".$indhu_lagna_house);
	
	if($indhu_lagna_house == 0){
		$indhu_lagna_house=12;
	}
	
	debug($indhu_lagna_house);
	
	$sw_planet[$indhu_lagna_house-1] = $sw_planet[$indhu_lagna_house-1] . " " . "<span class='indhu'>" . __('indhu_abbr') . "</span>" ;
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
	$out = str_replace('Ascendant','<span class="lagna">லக்</span>',$out);
	
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
			
			$planetDeg["மா"] = $mandhi_degree;
			$planetDeg_default["மா"] = $mandhi_degree;
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
				$mandhi_degree=$in_sun_degree+156.0;
			}
			else{
				$mandhi_degree=$in_sun_degree+240.0;
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
	
	$mandhi_degree = ($mandhi_degree % 30.0)+($mandhi_degree - floor($mandhi_degree));
	$mandhi_degree = round($mandhi_degree,4);
	
	debug("FINDMANDHI - WEEK DAY=" . $in_dayofweek);
	debug("FINDMANDHI - DAYTIME=" . $in_day);
	debug("FINDMANDHI - MANDHI DEGREE=" . $mandhi_degree);
	debug("FINDMANDHI - MANDHI DEGREE (Rasi)=" . $mandhi_degree);
	debug("FINDMANDHI - SUN DEGREE=" . $in_sun_degree);
	debug("FINDMANDHI - MANDHI DEGREE FROM SUN=" . $mandhi_degree);
	debug("FINDMANDHI - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$sw_degree["mandhi"] = DECtoDMS($mandhi_degree);
	
	return $mandhi_house;
}

function displayDegree($in_sw_degree, $inner=true)
{
	global $out_planet_degree, $planetDeg_default, $planetDeg_default_transit, $planetDeg, $planetDeg_transit;
	
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
		
		$out_planet_degree[$planet] = $val;			$txt = $txt . " " . $planet . "-" . $val;
			
			if($inner==true)
			{
				//$planetDeg_default[$planet] = $val;
				$planetDeg_default[$planet] = $planetDeg_default[$key][0];
				unset($planetDeg_default[$key]);
			}
			else{
				$planetDeg_default_transit[$planet] = $planetDeg_default_transit[$key][0];
				unset($planetDeg_default_transit[$key]);
			}
		}
	}
		
	if($inner==true)
	{
		$txt = $txt . " " . "மா-" . $planetDeg['மா'];
	}
	else{
		$txt = $txt . " " . "மா-" . $planetDeg_transit['மா'];
	}
	
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

function calculateAbhijitAllPlanets($inner=true)
{
	global $planet_starlord, $planet_starlord_boy, $g_planetAbhijitNak, $g_planetAbhijitNak_boy, $planetDeg, $planetDeg_transit, $g_nak_pad_idx;
	
	$planetAbhijitNak = array();
	
	if($inner==true){
		$temp_planetDeg = $planetDeg;  //girl list
		$temp_planet_starlord = $planet_starlord;
		$temp_nak_pad_idx = $g_nak_pad_idx;
	}
	else{
		$temp_planetDeg = $planetDeg_transit;  //boy list
		//$temp_planet_starlord = $planet_starlord_transit;
		$temp_planet_starlord = $planet_starlord_boy;
		$temp_nak_pad_idx = $g_nak_pad_idx;
	}
					
	$nak_arr = array("dummy","Aswini 1/அசுவி 1","Aswini 2/அசுவி 2","Aswini 3/அசுவி 3","Aswini 4/அசுவி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத் 1","Krithika 2/கிருத் 2","Krithika 3/கிருத் 3","Krithika 4/கிருத் 4",
						"Rohini 1/ரோகி 1","Rohini 2/ரோகி 2","Rohini 3/ரோகி 3","Rohini 4/ரோகி 4",
						"Mrigashirsha 1/மிருக 1","Mrigashirsha 2/மிருக 2","Mrigashirsha 3/மிருக 3","Mrigashirsha 4/மிருக 4",
						"Arudra 1/திருவா 1","Arudra 2/திருவா 2","Arudra 3/திருவா 3","Arudra 4/திருவா 4",
						"Punarvasu 1/புனர் 1","Punarvasu 2/புனர் 2","Punarvasu 3/புனர் 3","Punarvasu 4/புனர் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில் 1","Ashlesha 2/ஆயில் 2","Ashlesha 3/ஆயில் 3","Ashlesha 4/ஆயில் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்ர 1","Uthra phalguni 2/உத்ர 2","Uthra phalguni 3/உத்ர 3","Uthra phalguni 4/உத்ர 4",
						"Hastha 1/ஹஸ்த 1","Hastha 2/ஹஸ்த 2","Hastha 3/ஹஸ்த 3","Hastha 4/ஹஸ்த 4",
						"Chitra 1/சித்தி 1","Chitra 2/சித்தி 2","Chitra 3/சித்தி 3","Chitra 4/சித்தி 4",
						"Swaathi 1/சுவா 1","Swaathi 2/சுவா 2","Swaathi 3/சுவா 3","Swaathi 4/சுவா 4",
						"Vishaakha 1/விசா 1","Vishaakha 2/விசா 2","Vishaakha 3/விசா 3","Vishaakha 4/விசா 4",
						"Anuraadha 1/அனுஷ 1","Anuraadha 2/அனுஷ 2","Anuraadha 3/அனுஷ 3","Anuraadha 4/அனுஷ 4",
						"Jyeshta 1/கேட் 1","Jyeshta 2/கேட் 2","Jyeshta 3/கேட் 3","Jyeshta 4/கேட் 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூரா 1","Poorva Ashaada 2/பூரா 2","Poorva Ashaada 3/பூரா 3","Poorva Ashaada 4/பூரா 4",
						"Uthra Ashaada 1/உத்ராட 1","Uthra Ashaada 2/உத்ராட 2","Uthra Ashaada 3/உத்ராட 3","Uthra Ashaada 4/உத்ராட 4",
						"Shraavan 1/திருவோ 1","Shraavan 2/திருவோ 2","Shraavan 3/திருவோ 3","Shraavan 4/திருவோ 4",
						"Dhanishta 1/அவிட் 1","Dhanishta 2/அவிட் 2","Dhanishta 3/அவிட் 3","Dhanishta 4/அவிட் 4",
						"Shathabhisha 1/சதய 1","Shathabhisha 2/சதய 2","Shathabhisha 3/சதய 3","Shathabhisha 4/சதய 4",
						"Poorva bhadrapada 1/பூரட் 1","Poorva bhadrapada 2/பூரட் 2","Poorva bhadrapada 3/பூரட் 3","Poorva bhadrapada 4/பூரட் 4",
						"Uthra bhadrapada 1/உத்ரட் 1","Uthra bhadrapada 2/உத்ரட் 2","Uthra bhadrapada 3/உத்ரட் 3","Uthra bhadrapada 4/உத்ரட் 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
		
		/*$nak_arr = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
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
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");*/
	
	foreach($temp_planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){ //rahu kaalam used in jamakol
			continue;
		}
		
		$abhijit_idx_1 = $temp_planet_starlord[$planet][4] + ABHIJIT_PAD_COUNT - 1;
		$abhijit_idx_2 = $temp_planet_starlord[$planet][4] + ABHIJIT_PAD_COUNT;
		
		if($abhijit_idx_1 > 108){
			$abhijit_idx_1 = $abhijit_idx_1 - 108;
		}
		
		if($abhijit_idx_2 > 108){
			$abhijit_idx_2 = $abhijit_idx_2 - 108;
		}
		
		$planetAbhijitNak[$planet] = array(getValue($nak_arr[$abhijit_idx_1]), getValue($nak_arr[$abhijit_idx_2])); //getValue 2nd argument is lang, which is defaulted to 1 (tamil)
		
		debug ("$planet-------$abhijit_idx_1/$abhijit_idx_2-----".$planetAbhijitNak[$planet][0].'/'.$planetAbhijitNak[$planet][1]."<BR>");
	}
	
	if($inner==true){
		$g_planetAbhijitNak = $planetAbhijitNak;
	}
	else{
		$g_planetAbhijitNak_boy = $planetAbhijitNak;
	}
}

function calculateVainasigamAllPlanets($inner=true)
{
	global $planet_starlord, $planet_starlord_boy, $g_planetVainasigamNak, $g_planetVainasigamNak_boy, $planetDeg, $planetDeg_transit, $g_nak_pad_idx;
	
	$planetVainasigamNak = array();
	
	if($inner==true){
		$temp_planetDeg = $planetDeg;  //girl list
		$temp_planet_starlord = $planet_starlord;
		$temp_nak_pad_idx = $g_nak_pad_idx;
	}
	else{
		$temp_planetDeg = $planetDeg_transit;  //boy list
		//$temp_planet_starlord = $planet_starlord_transit;
		$temp_planet_starlord = $planet_starlord_boy;
		$temp_nak_pad_idx = $g_nak_pad_idx;
	}
					
	$nak_arr = array("dummy","Aswini 1/அசுவி 1","Aswini 2/அசுவி 2","Aswini 3/அசுவி 3","Aswini 4/அசுவி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத் 1","Krithika 2/கிருத் 2","Krithika 3/கிருத் 3","Krithika 4/கிருத் 4",
						"Rohini 1/ரோகி 1","Rohini 2/ரோகி 2","Rohini 3/ரோகி 3","Rohini 4/ரோகி 4",
						"Mrigashirsha 1/மிருக 1","Mrigashirsha 2/மிருக 2","Mrigashirsha 3/மிருக 3","Mrigashirsha 4/மிருக 4",
						"Arudra 1/திருவா 1","Arudra 2/திருவா 2","Arudra 3/திருவா 3","Arudra 4/திருவா 4",
						"Punarvasu 1/புனர் 1","Punarvasu 2/புனர் 2","Punarvasu 3/புனர் 3","Punarvasu 4/புனர் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில் 1","Ashlesha 2/ஆயில் 2","Ashlesha 3/ஆயில் 3","Ashlesha 4/ஆயில் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Poorva Phalguni 1/பூரம் 1","Poorva Phalguni 2/பூரம் 2","Poorva Phalguni 3/பூரம் 3","Poorva Phalguni 4/பூரம் 4",
						"Uthra phalguni 1/உத்ர 1","Uthra phalguni 2/உத்ர 2","Uthra phalguni 3/உத்ர 3","Uthra phalguni 4/உத்ர 4",
						"Hastha 1/ஹஸ்த 1","Hastha 2/ஹஸ்த 2","Hastha 3/ஹஸ்த 3","Hastha 4/ஹஸ்த 4",
						"Chitra 1/சித்தி 1","Chitra 2/சித்தி 2","Chitra 3/சித்தி 3","Chitra 4/சித்தி 4",
						"Swaathi 1/சுவா 1","Swaathi 2/சுவா 2","Swaathi 3/சுவா 3","Swaathi 4/சுவா 4",
						"Vishaakha 1/விசா 1","Vishaakha 2/விசா 2","Vishaakha 3/விசா 3","Vishaakha 4/விசா 4",
						"Anuraadha 1/அனுஷ 1","Anuraadha 2/அனுஷ 2","Anuraadha 3/அனுஷ 3","Anuraadha 4/அனுஷ 4",
						"Jyeshta 1/கேட் 1","Jyeshta 2/கேட் 2","Jyeshta 3/கேட் 3","Jyeshta 4/கேட் 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூரா 1","Poorva Ashaada 2/பூரா 2","Poorva Ashaada 3/பூரா 3","Poorva Ashaada 4/பூரா 4",
						"Uthra Ashaada 1/உத்ரா 1","Uthra Ashaada 2/உத்ரா 2","Uthra Ashaada 3/உத்ரா 3","Uthra Ashaada 4/உத்ரா 4",
						"Shraavan 1/திருவோ 1","Shraavan 2/திருவோ 2","Shraavan 3/திருவோ 3","Shraavan 4/திருவோ 4",
						"Dhanishta 1/அவிட் 1","Dhanishta 2/அவிட் 2","Dhanishta 3/அவிட் 3","Dhanishta 4/அவிட் 4",
						"Shathabhisha 1/சதய 1","Shathabhisha 2/சதய 2","Shathabhisha 3/சதய 3","Shathabhisha 4/சதய 4",
						"Poorva bhadrapada 1/பூரட் 1","Poorva bhadrapada 2/பூரட் 2","Poorva bhadrapada 3/பூரட் 3","Poorva bhadrapada 4/பூரட் 4",
						"Uthra bhadrapada 1/உத்ரட் 1","Uthra bhadrapada 2/உத்ரட் 2","Uthra bhadrapada 3/உத்ரட் 3","Uthra bhadrapada 4/உத்ரட் 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
		
		/*$nak_arr = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
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
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");*/
	
	foreach($temp_planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){
			continue;
		}
		
		$vaisaisam_idx = $temp_planet_starlord[$planet][4] + VAINASIGAM_PAD_COUNT - 1;
		
		if($vaisaisam_idx > 108){
			$vaisaisam_idx = $vaisaisam_idx - 108;
		}
		
		$nivarthi_star_idx = $vaisaisam_idx + 28 - 1; //7th star 28th paatham
		
		if($nivarthi_star_idx > 108){
			$nivarthi_star_idx = $nivarthi_star_idx - 108;
		}
		
		//$planetVainasigamNak[$planet] = array(getValue($nak_arr[$vaisaisam_idx]), explode(' ',getValue($nak_arr[$nivarthi_star_idx]))[0]); //getValue 2nd argument is lang, which is defaulted to 1 (tamil)
		$planetVainasigamNak[$planet] = array(getValue($nak_arr[$vaisaisam_idx]), getValue($nak_arr[$nivarthi_star_idx])); //getValue 2nd argument is lang, which is defaulted to 1 (tamil)
		
		debug ("$temp_nak_pad_idx-------$vaisaisam_idx-----".$planetVainasigamNak[$planet][0]."------".$planetVainasigamNak[$planet][1]."<BR>");
	}
	
	if($inner==true){
		$g_planetVainasigamNak = $planetVainasigamNak;
	}
	else{
		$g_planetVainasigamNak_boy = $planetVainasigamNak;
	}
}

function calculateYogamAllPlanets($inner=true)
{
	global $full_sun_deg, $full_sun_deg_boy,$planet_starlord, $planet_starlord_transit , $g_planetYogamLord, $g_planetYogamLord_boy, $planetDeg, $planetDeg_transit,
			$lagna_house,$transit_lagna_house;
	
	$planetYogamLord = array();
	
	if($inner==true){
		$temp_planetDeg = $planetDeg;  //girl list
		$temp_full_sun_deg = $full_sun_deg;
		$temp_planet_starlord = $planet_starlord;
		$temp_lagna_full_degree = $planetDeg['லக்'] + ($lagna_house - 1) * 30;
	}
	else{
		$temp_planetDeg = $planetDeg_transit;  //boy list
		$temp_full_sun_deg = $full_sun_deg_boy;
		$temp_planet_starlord = $planet_starlord_transit;
		//$temp_planet_starlord = $planet_starlord_boy;
		$temp_lagna_full_degree = $planetDeg_transit['லக்'] + ($transit_lagna_house - 1) * 30;
	}
					
	$yoga_arr = array("dummy","VISHKAMBHA/விஷ்","PRITI/ப்ரீ","AYUSHMAN/ஆயுஷ்", "SAUBHAGYA/செள", "SOBHANA/சோப", "ATIGANDA/அதிக", "SUKARMA/சுகர்", "DHRITI/திருதி", "SOOLA/சூலம்", "GANDA/கண்ட", "VRIDDHA/விருத்", "DHRUVA/துருவ","VYAGATHA/வ்யாக",
					"HARSHANA/ஹர்ஷ", "VAJRA/வஜ்","SIDDHI/சித்தி","VYATAPATA/வியதீ","VARIYAN/வரீ", "PARIGHA/பரிக","SIVA/சிவ", "SIDDHA/சித்த", "SADHYA/சாத்தி","SUBHA/சுப","SUKLA/சுப்பி","BRAHMA/பிராம்","INDRA/ஐந்தி","VAIDHRITI/வைதி");
	
	foreach($temp_planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){
			continue;
		}

		$yogam_deg = $temp_planet_starlord[$planet][2] + $temp_full_sun_deg;
		
		if($yogam_deg > 360)
		{
			$yogam_deg = $yogam_deg - 360;
		}

		
		if(strcmp($planet,"சூரி")==0){
			if($temp_lagna_full_degree < $temp_full_sun_deg)
			{
				$yogam_deg = $temp_full_sun_deg - $temp_lagna_full_degree;
			}
			else{
				$yogam_deg = 360 - $temp_lagna_full_degree + $temp_full_sun_deg; //subtract it from 360 to get degree from sun to the planet
			}
		}
		
		$yogam_idx = ceil($yogam_deg / 13.333333);
		
		$planetYogamLord[$planet] = getValue($yoga_arr[ceil($yogam_idx)]); //2nd argument is lang, which is defaulted to 1 (tamil)
		
		debug("$planet-------$yogam_deg---yogam_idx=$yogam_idx-----".$planetYogamLord[$planet]."<BR>");
	}
	
	if($inner==true){
		$g_planetYogamLord = $planetYogamLord;
	}
	else{
		$g_planetYogamLord_boy = $planetYogamLord;
	}
}

function calculateThithiAllPlanets($inner=true)
{
	global $full_sun_deg, $full_sun_deg_boy,$planet_starlord, $planet_starlord_transit, $g_planetThithiLord, $g_planetThithiLord_boy, $planetDeg, $planetDeg_transit,
			$lagna_house,$transit_lagna_house;
	
	$planetThithiLord = array();
	
	if($inner==true){
		$temp_planetDeg = $planetDeg;  //girl list
		$temp_full_sun_deg = $full_sun_deg;
		$temp_planet_starlord = $planet_starlord;
		$temp_lagna_full_degree = $planetDeg['லக்'] + ($lagna_house - 1) * 30;
	}
	else{
		$temp_planetDeg = $planetDeg_transit;  //boy list
		$temp_full_sun_deg = $full_sun_deg_boy;
		$temp_planet_starlord = $planet_starlord_transit;
		$temp_lagna_full_degree = $planetDeg_transit['லக்'] + ($transit_lagna_house - 1) * 30;
	}
					
	/*$thithi_arr = array(
					"dummy",    //to avoid index 0 
					"Prathame/பிரதமை",
					"Dwithiya/துவிதியை",
					"Thrithiya/திருதியை",
					"Chathurthi/சதுர்த்தி",
					"Panchami/பஞ்சமி",
					"Shrashti/சஷ்டி",
					"Saptami/சப்தமி",
					"Ashtami/அஷ்டமி",
					"Navami/நவமி",
					"Dashami/தசமி",
					"Ekadashi/ஏகாதசி",
					"Dwadashi/துவாதசி",
					"Thrayodashi/திரயோதசி",
					"Chaturdashi/சதுர்த்தசி",
					"Poornima/பௌர்ணமி",
					"Prathame/பிரதமை",
					"Dwithiya/துவிதியை",
					"Thrithiya/திருதியை",
					"Chathurthi/சதுர்த்தி",
					"Panchami/பஞ்சமி",
					"Shrashti/சஷ்டி",
					"Saptami/சப்தமி",
					"Ashtami/அஷ்டமி",
					"Navami/நவமி",
					"Dashami/தசமி",
					"Ekadashi/ஏகாதசி",
					"Dwadashi/துவாதசி",
					"Thrayodashi/திரயோதசி",
					"Chaturdashi/சதுர்த்தசி",
					"Amavasya/அமாவாசை"
					);*/
					
	$thithi_arr = array(
					"dummy",    //to avoid index 0 
					"Prathame/பிரத",
					"Dwithiya/துவிதி",
					"Thrithiya/திருதி",
					"Chathurthi/சதுர்",
					"Panchami/பஞ்ச",
					"Shrashti/சஷ்டி",
					"Saptami/சப்த",
					"Ashtami/அஷ்ட",
					"Navami/நவமி",
					"Dashami/தசமி",
					"Ekadashi/ஏகா",
					"Dwadashi/துவா",
					"Thrayodashi/திரயோ",
					"Chaturdashi/சதுதசி",
					"Poornima/பௌ",
					"Prathame/பிரத",
					"Dwithiya/துவிதி",
					"Thrithiya/திருதி",
					"Chathurthi/சதுர்",
					"Panchami/பஞ்ச",
					"Shrashti/சஷ்டி",
					"Saptami/சப்த",
					"Ashtami/அஷ்ட",
					"Navami/நவமி",
					"Dashami/தசமி",
					"Ekadashi/ஏகா",
					"Dwadashi/துவா",
					"Thrayodashi/திரயோ",
					"Chaturdashi/சதுதசி",
					"Amavasya/அமா"
					);
	
	foreach($temp_planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){
			continue;
		}

		if($temp_planet_starlord[$planet][2] > $temp_full_sun_deg)
		{
			$thithi_deg = $temp_planet_starlord[$planet][2] - $temp_full_sun_deg;
		}
		else{
			$thithi_deg = 360 - ($temp_full_sun_deg - $temp_planet_starlord[$planet][2]); //subtract it from 360 to get degree from sun to the planet
		}
		
		if(strcmp($planet,"சூரி")==0){
			if($temp_lagna_full_degree < $temp_full_sun_deg)
			{
				$thithi_deg = $temp_full_sun_deg - $temp_lagna_full_degree;
			}
			else{
				$thithi_deg = 360 - $temp_lagna_full_degree + $temp_full_sun_deg; //subtract it from 360 to get degree from sun to the planet
			}
		}
		
		$temp_thithi_idx = ceil($thithi_deg/12);
		
		if($temp_thithi_idx >=0 and $temp_thithi_idx <=14)
		{
			$temp_paksham = "வளர்";
		}
		elseif($temp_thithi_idx >14 and $temp_thithi_idx <=29)
		{
			$temp_paksham = "தேய்";
		}
		
		$thithi_label = getValue($thithi_arr[$temp_thithi_idx]);
		if(isset($temp_paksham)){
			// Do not append paksham for Ama/Po (अமா/பௌ) thithis
			if (mb_strpos($thithi_label, 'அமா') !== false || mb_strpos($thithi_label, 'பௌ') !== false) {
				$planetThithiLord[$planet] = $thithi_label;
			} else {
				$planetThithiLord[$planet] = $thithi_label . " ($temp_paksham)"; //2nd argument is lang, which is defaulted to 1 (tamil)
			}
		} else {
			$temp_paksham = "";
			$planetThithiLord[$planet] = $thithi_label; //2nd argument is lang, which is defaulted to 1 (tamil)
		}
		
		debug("planet=$planet---thithi-deg=$thithi_deg---temp_paksham----$temp_paksham---".ceil($thithi_deg/12)."-----".$planetThithiLord[$planet]."<BR>");
	}
	
	if($inner==true){
		$g_planetThithiLord = $planetThithiLord;
	}
	else{
		$g_planetThithiLord_boy = $planetThithiLord;
	}
}

function calculateKaranamAllPlanets($inner=true)
{
	global $full_sun_deg, $full_sun_deg_boy,$planet_starlord, $planet_starlord_boy, $g_planetKaranaLord, $g_planetKaranaLord_boy, $planetDeg, $planetDeg_transit, 
			$lagna_house,$transit_lagna_house, $planet_starlord_transit;
	
	$planetKaranaLord = array();
	
	if($inner==true){
		$temp_planetDeg = $planetDeg;  //girl list
		$temp_full_sun_deg = $full_sun_deg;
		$temp_planet_starlord = $planet_starlord;
		$temp_lagna_full_degree = $planetDeg['லக்'] + ($lagna_house - 1) * 30;
	}
	else{
		$temp_planetDeg = $planetDeg_transit;  //boy list
		$temp_full_sun_deg = $full_sun_deg_boy;
		$temp_planet_starlord = $planet_starlord_transit;
		//$temp_planet_starlord = $planet_starlord_boy;
		$temp_lagna_full_degree = $planetDeg_transit['லக்'] + ($transit_lagna_house - 1) * 30;
	}
					
	$karana_array  = [
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
	
	foreach($temp_planetDeg as $planet => $deg){
		if(strcmp($planet,"R.K")==0){
			continue;
		}

		if($temp_planet_starlord[$planet][2] > $temp_full_sun_deg)
		{
			$karana_deg = $temp_planet_starlord[$planet][2] - $temp_full_sun_deg;
		}
		else{
			$karana_deg = 360 - ($temp_full_sun_deg - $temp_planet_starlord[$planet][2]); //subtract it from 360 to get degree from sun to the planet
		}
		
		if(strcmp($planet,"சூரி")==0){
			if($temp_lagna_full_degree < $temp_full_sun_deg)
			{
				$karana_deg = $temp_full_sun_deg - $temp_lagna_full_degree;
			}
			else{
				$karana_deg = 360 - $temp_lagna_full_degree + $temp_full_sun_deg; //subtract it from 360 to get degree from sun to the planet
			}
		}
		
		$planetKaranaLord[$planet] = getValue($karana_array[ceil($karana_deg/6)]); //2nd argument is lang, which is defaulted to 1 (tamil)
		
		debug("++++$planet-------$karana_deg---".ceil($karana_deg/6)."-----".$planetKaranaLord[$planet]."<BR>");
	}
	
	if($inner==true){
		$g_planetKaranaLord = $planetKaranaLord;
	}
	else{
		$g_planetKaranaLord_boy = $planetKaranaLord;
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
	global $conn, $g_paksham, $timestamp, $g_all_parigaaram;
	
	$data = getDataFromThithiById($conn, $thithi_idx+1);
	
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>திதி பரிகாரம்</div>$temple_arr[$thithi_idx]</DIV>";
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>".translate_text('திதி விவரங்கள்')."</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('திதி')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['thithi_name'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('திதி கோவில்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['thithi_temple'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('திதி கோவில் இருப்பிடம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['thithi_temple_link']."' target='_blank'>".translate_text('google Map')."</A></DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('திதி தெய்வம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right' id='photo'><image style='width:150px' src='images\\thithi_god\\" . $data['thithi_name'] . "_" . $g_paksham . ".jpg?".$timestamp."'></DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";

	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

//get karana parigaaram based on karana idx
function getKaranaParigaaram($karan)
{					
	global $timestamp, $g_all_parigaaram;
	$kar = explode('/',$karan)[1];
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>கரண பரிகாரம்</div><img src='images/karanam_gods/$kar.jpg' /></DIV>";
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>".translate_text('கரண விவரங்கள்')."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('கரணம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".map_display_text($kar)."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('கரண பரிகாரம் கோவில்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><img style='width:150px' src='images/karanam_gods/$kar.jpg?$timestamp' /></DIV>";
        
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
            
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>".__('close')."</button></center></DIV>";
		
	$details = $details . "</DIV>";
					
	return $details;
}

function getYogiParigaaram($yogi_idx)
{
	global $conn, $g_all_parigaaram;
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>யோகி பரிகாரம்</div><img src='images/yogi_parigaaram/$yogi_idx.png' /></DIV>";
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
					
    $yogam = $yoga_arr[$yogi_idx];
	
	$data = getDataFromYogiById($conn,$yogi_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>".translate_text('யோகி விவரங்கள்')."</DIV>";
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('யோகம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['yogam'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('நக்ஷ் (கிரகம்)')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['star_planet'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('யோகி கோவில்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['temple'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('பரிகாரம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['remedy'])."</DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
		
		/*$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>யோகம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$yogam."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>யோகி பரிகாரம் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><img style='width:150px' src='images/yogi_parigaaram/$yogi_idx.png' /></DIV>";
			
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";*/
	$details = $details . "</DIV>";
	
	return $details;
}

function getAvaYogiParigaaram($ayogi_idx)
{
	global $timestamp, $g_all_parigaaram;
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>அவயோகி பரிகாரம்</div><img src='images/avayogi_parigaaram/$ayogi_idx.png' /></DIV>";
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>அவயோகி விவரங்கள்</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>அவயோகி பரிகாரம் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><img style='width:150px' src='images/avayogi_parigaaram/$ayogi_idx.png?$timestamp' /></DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
			
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getNakParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>நட் பரிகாரம்</div><img src='images/nak_parigaaram/$nak_idx.png' /></DIV>";
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>".translate_text('நக்ஷ் விவரங்கள்')."</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('நக்ஷ்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['star_name'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('நக்ஷ் கோவில்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['star_temple'])."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('நக்ஷ் கோவில் இருப்பிடம்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>".translate_text('google Map')."</A></DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('நக்ஷ் அதிபதி')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".translate_text($data['star_lord'])."</DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getMudakkuParigaaram($mudakku_idx)
{
	global $conn, $timestamp, $g_all_parigaaram;
	
	//$details = "<DIV class='parigaraDiv'><div id='labelParigaraDiv'>நட் பரிகாரம்</div><img src='images/nak_parigaaram/$nak_idx.png' /></DIV>";
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>முடக்கு விவரங்கள்</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>முடக்கு கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><img style='width:150px' src='images/mudakku_parigaaram/$mudakku_idx.png?$timestamp' /></DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
			
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getKuberaParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>குபேர நக்ஷ் விவரங்கள்</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>குபேர நக்ஷ்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_name']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>குபேர நக்ஷ் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_temple']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>குபேர நக்ஷ் கோவில் இருப்பிடம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>google Map</A></DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>குபேர நக்ஷ் அதிபதி</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_lord']."</DIV>";		
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getDestinyPointParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>தலைவிதி  நக்ஷ் விவரங்கள்</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>தலைவிதி  நக்ஷ்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_name']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>தலைவிதி  நக்ஷ் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_temple']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>தலைவிதி  நக்ஷ் கோவில் இருப்பிடம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>google Map</A></DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>தலைவிதி  நக்ஷ் அதிபதி</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_lord']."</DIV>";		
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getAthrishtaPointParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>அதிர்ஷ்ட நக்ஷ் விவரங்கள்</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>அதிர்ஷ்ட நக்ஷ்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_name']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>அதிர்ஷ்ட நக்ஷ் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_temple']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>அதிர்ஷ்ட நக்ஷ் கோவில் இருப்பிடம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>google Map</A></DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>அதிர்ஷ்ட நக்ஷ் அதிபதி</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_lord']."</DIV>";		
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getDhurathrishtaPointParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>துரதிர்ஷ்ட பரிகார நக்ஷ் விவரங்கள்</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>துரதிர்ஷ்ட பரிகார நக்ஷ்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_name']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>துரதிர்ஷ்ட பரிகார நக்ஷ் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_temple']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>துரதிர்ஷ்ட பரிகார நக்ஷ் கோவில் இருப்பிடம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>google Map</A></DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>துரதிர்ஷ்ட பரிகார நக்ஷ் அதிபதி</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_lord']."</DIV>";		
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getSugaPointParigaaram($nak_idx)
{
	global $conn, $g_all_parigaaram;
	
	$data = getDataFromStarById($conn, $nak_idx);
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>சுக நக்ஷ் விவரங்கள்</DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>சுக நக்ஷ்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_name']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>சுக நக்ஷ் கோவில்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_temple']."</DIV>";
		
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>சுக நக்ஷ் கோவில் இருப்பிடம்</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'><A style='color:blue;cursor:pointer' href='".$data['star_temple_link']."' target='_blank'>google Map</A></DIV>";

		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>சுக நக்ஷ் அதிபதி</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".$data['star_lord']."</DIV>";		
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
	
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function getRasiParigaaram()
{
	global $g_rasi, $g_all_parigaaram;
	
	$rasi_temples = [
				"மேஷம்" => "ராமநாதஸ்வாமி கோவில் (ராமேஸ்வரம்)",
				"ரிஷபம்" => "சிவபெருந்துறைத்திருக்கோவில் (திருவையாற்று)",
				"மிதுனம்" => "தண்டாயுதபாணி சுவாமி திருக்கோவில் (பழனி)",
				"கடகம்" => "குற்றாலநாதர் (குற்றாலநாதர்)",
				"சிம்மம்" => "பெரியகளப்பர் கோவில் (திருவாடமை)",
				"கன்னி" => "வேதவல்லீஸ்வரர் கோவில் (கூத்தநல்லூர்)",
				"துலாம்" => "திருத்தணி முருகன் கோவில்",
				"விருச்சிகம்" => "எகாம்பரநாதர் கோவில் (காஞ்சிபுரம்)",
				"தனுசு" => "மத்யார்ஜுனஸ்வாமி கோவில் (மிட்கொத்தூர்)",
				"மகரம்" => "திருநல்லார் சனீஸ்வர கோவில் (திருநல்லாறு)",
				"கும்பம்" => "ஓம்காரேஸ்வரர் கோவில் (ஓம்காரேஸ்வரம்)",
				"மீனம்" => "வைதீஸ்வரன் கோவில் (மயிலாடுதுறை)"
			];
	
	$details = "<DIV class='parigaaram-grid-container'>";
	$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'>".translate_text('ராசி பரிகாரம்')."</DIV>";
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('ராசி')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>".map_display_text($g_rasi)."</DIV>";
        
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-left'>".translate_text('ராசி கோவில்')."</DIV>";
		$details = $details . "<DIV class='parigaaram-grid-item parigaaram-grid-item-right'>" . translate_text($rasi_temples[$g_rasi]) . "</DIV>";
		
		$g_all_parigaaram = $g_all_parigaaram . $details . "</DIV>";
			
		$details = $details . "<div class='parigaaram-grid-item parigaaram-grid-item-head'><center><button class='actionBtn'>Close</button></center></DIV>";
	$details = $details . "</DIV>";
					
	return $details;
}

function calculateBirthPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_nak, $g_karan, $panchang,$g_nak_pad_idx, $g_thithi_idx, $g_yoga_idx, $g_nak_idx, $full_sun_deg, 
		$nak_pad_idx, $nak_array;
	
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
	
	$weekday_arr = array("Sunday/ஞாயிறு","Monday/திங்கள்","Tuesday/செவ்வாய்","Wednesday/புதன்",
					"Thursday/வியாழன்","Friday/வெள்ளி","Saturday/சனி");
	
	$panchang = array();
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333333333333);
	
	if($nak_idx>108){ //due to approximate division by 3.333333333333333 and ceil, nak_idx can go beyond 108 when in Revathi nak.
		$nak_idx = 108; 
	}
	
	$g_nak_idx = $nak_idx;
	
	$panchang["nak"] = $nak_array[$nak_idx];
	//$g_nak_pad_idx = $nak_idx;
	$nak_pad_idx = $nak_idx;
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
	debug("g_yoga_idx=$g_yoga_idx, yogam_deg".($deg / 13.333333));
	
	if($g_yoga_idx > 27){
		$g_yoga_idx = 27;
	}
	
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
			$details = getNakParigaaram(ceil($g_nak_idx/4));
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
		case "mudakku":
			$details = getMudakkuParigaaram($in_data);
			echo "<DIV class='mudakkuDetailsDiv' id='mudakkuDetailsDiv' onclick='hideDetailsDiv()'>$details</DIV>";
			break;
		case "kubera":
			$details = getKuberaParigaaram($in_data);
			echo "<DIV class='kuberaDetailsDiv' id='kuberaDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;
		case "rasi":
			$details = getRasiParigaaram();
			echo "<DIV class='rasiDetailsDiv' id='rasiDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;
		case "destinyPoint":
			$details = getDestinyPointParigaaram($in_data);
			echo "<DIV class='destinyPointDetailsDiv' id='destinyPointDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;
		case "AthrishtaPoint":
			$details = getAthrishtaPointParigaaram($in_data);
			echo "<DIV class='athrishtaPointDetailsDiv' id='athrishtaPointDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;
		case "DhurathrishtaPoint":
			$details = getDhurathrishtaPointParigaaram($in_data);
			echo "<DIV class='dhurathrishtaPointDetailsDiv' id='dhurathrishtaPointDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;	
		case "SugaPoint":
			$details = getSugaPointParigaaram($in_data);
			echo "<DIV class='sugaPointDetailsDiv' id='sugaPointDetailsDiv' onclick='this.style.display=`none`'>$details</DIV>";
			break;	
	}
}

function displayAllParigaaramDiv(){
	global $g_all_parigaaram;
	
	//$g_all_parigaaram = '<a href="javascript:hideAllParigaaramDiv()">&nbsp;CLOSE (X)&nbsp;</a>' . $g_all_parigaaram;
	$g_all_parigaaram = "<button class='close-btn' onclick='hideAllParigaaramDiv()'>X</button>" . $g_all_parigaaram . "<BR><BR><BR>";

	echo "<DIV class='allParigaaramDiv' id='allParigaaramDiv'>$g_all_parigaaram</DIV>";

}

//disclaimer to avoid any issues
function displayDisclaimer()
{
	echo "<BR>";
/*	echo <<<EOL
		<div class="divDisclaimer" colspan=3>
			<H6 style='font-weight;color:red'>*இந்த தளத்தில்/மொபைல் ஆப்பில் கொடுக்கப்பட்டுள்ள ஆய்வுகள் மற்றும் மதிப்பென்கள் இறுதியானது கிடையாது, இதனுடன் நீங்கள் தங்கள் விரிவான ஆய்வுகளையும் மேற்க்கொண்டு இருதி முடிவு எடுக்கவும்</H6>
		</div>
EOL;*/
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

function getMudakku($flag)  //flag=1 star, flag=2 rasi
{
	global $planet_starlord;
	
	$star = explode(' ',$planet_starlord['சூரி'][0])[0];
	
	$rasi_arr = ["மேஷம்", "ரிஷபம்", "மிதுனம்", "கடகம்", "சிம்மம்", "கன்னி", "துலாம்", "விருச்சிகம்", "தனுசு", "மகரம்", "கும்பம்", "மீனம்"];
	$mutakku_arr = array("அசுவினி" => array("பூரம்", "சிம்மம்",11),
						"பரணி" => array("மகம்", "சிம்மம்",10),
						"கிருத்திகை" => array("ஆயில்யம்", "கடகம்",9),
						"ரோகிணி" => array("பூசம்", "கடகம்",8),
						"மிருகசிரீஷம்" => array("புனர்பூசம்", "மிதுனம்",7),
						"திருவாதிரை" => array("திருவாதிரை", "மிதுனம்",6),
						"புனர்பூசம்" => array("மிருகசிரீஷம்", "ரிஷபம்",5),
						"பூசம்" => array("ரோகிணி", "ரிஷபம்",4),
						"ஆயில்யம்" => array("கிருத்திகை", "மேஷம்",3),
						"மகம்" => array("பரணி", "மேஷம்",2),
						"பூரம்" => array("அசுவினி", "மேஷம்",1),
						"உத்திரம்" => array("ரேவதி", "மீனம்",27),
						"ஹஸ்தம்" => array("உத்திரட்டாதி", "மீனம்",26),
						"சித்திரை" => array("பூரட்டாதி", "கும்பம்",25),
						"சுவாதி" => array("சதயம்", "கும்பம்",24),
						"விசாகம்" => array("அவிட்டம்", "மகரம்",23),
						"அனுஷம்" => array("திருவோணம்", "மகரம்",22),
						"கேட்டை" => array("உத்திராடம்", "தனுசு",21),
						"மூலம்" => array("பூராடம்", "தனுசு",20),
						"பூராடம்" => array("மூலம்" , "தனுசு",19),
						"உத்திராடம்" => array("கேட்டை", "விருச்சிகம்",18),
						"திருவோணம்" => array("அனுஷம்", "விருச்சிகம்",17),
						"அவிட்டம்" => array("விசாகம்", "துலாம்",16),
						"சதயம்" => array("சுவாதி", "துலாம்",15),
						"பூரட்டாதி" => array("சித்திரை", "கன்னி",14),
						"உத்திரட்டாதி" => array("ஹஸ்தம்", "கன்னி",13),
						"ரேவதி" => array("உத்திரம்", "சிம்மம்",12)
						);

	debug($mutakku_arr[$star][0]."----".$mutakku_arr[$mutakku_arr[$star][0]][1]);
	
	if($flag==1){ //star
		return $mutakku_arr[$star][0];
	}
	elseif($flag==2){  //rasi
		$padham = (int)explode(' ',$planet_starlord['சூரி'][0])[1];
		if(strcmp($mutakku_arr[$star][0],"கிருத்திகை")==0 or strcmp($mutakku_arr[$star][0],"உத்திரம்")==0 or strcmp($mutakku_arr[$star][0],"உத்திராடம்")==0){
			if($padham==1){
				return $mutakku_arr[$star][1];
			}
			else{
				return getRasi(array_search($mutakku_arr[$star][1],$rasi_arr)+1);
			}
		}
		elseif(strcmp($mutakku_arr[$star][0],"புனர்பூசம்")==0 or strcmp($mutakku_arr[$star][0],"விசாகம்")==0 or strcmp($mutakku_arr[$star][0],"பூரட்டாதி")==0){		
			if($padham!=4){
				return $mutakku_arr[$star][1];
			}
			else{
				return getRasi(array_search($mutakku_arr[$star][1],$rasi_arr));
			}
		}
		elseif(strcmp($mutakku_arr[$star][0],"மிருகசிரீஷம்")==0 or strcmp($mutakku_arr[$star][0],"சித்திரை")==0 or strcmp($mutakku_arr[$star][0],"அவிட்டம்")==0){
			if($padham==1 or $padham==2){
				return $mutakku_arr[$star][1];
			}
			else{
				return getRasi(array_search($mutakku_arr[$star][1],$rasi_arr));
			}
		}
		else{
			return $mutakku_arr[$star][1];
		}
	}
	else{
		return $mutakku_arr[$star][2];
	}
}

function getBavam($rasi)
{
	global $lagna_house;
	
	$rasi_arr = ["dummy","மேஷம்", "ரிஷபம்", "மிதுனம்", "கடகம்", "சிம்மம்", "கன்னி", "துலாம்", "விருச்சிகம்", "தனுசு", "மகரம்", "கும்பம்", "மீனம்"];
	
	return countHouses($lagna_house,array_search($rasi,$rasi_arr));
}

//this is used as a unique code when registered from browser. This will be set as cookie in the customer mobile
function get19DigitRandomNumber()
{
	return date("YmdHis").rand(11111,99999);
}

function getSessionId()
{
	return session_id();
}

function getTamilMonthDay($sun_house, $sun_degree) {
    global $today_sunrise, $today_sunset;

    $tamil_month_rasi_arr = [
        array("dummy", "dummy", 0, 0),
        array("மேஷம்", "சித்திரை", 1.030851852, 30),
        array("ரிஷபம்", "வைகாசி", 1.013010753, 30),
        array("மிதுனம்", "ஆனி", 1.019695341, 30),
        array("கடகம்", "ஆடி", 1.01516129, 30),
        array("சிம்மம்", "ஆவணி", 1.001164875, 30),
        array("கன்னி", "புரட்டாசி", 1.015203704, 30),
        array("துலாம்", "ஐப்பசி", 1.031101533, 30),
        array("விருச்சிகம்", "கார்த்திகை", 1.017471264, 30),
        array("தனுசு", "மார்கழி", 1.012001916, 20),
        array("மகரம்", "தை", 1.015670498, 30),
        array("கும்பம்", "மாசி", 1.027816092, 30),
        array("மீனம்", "பங்குனி", 1.011305556, 30)
    ];

    $month = $tamil_month_rasi_arr[$sun_house][1];

    if ($sun_degree == 0) {
        $sun_degree = 0.001;
    }

    // Calculate the maximum days in the month based on the sun's rise and set times
    $maxdays = ceil((strtotime($today_sunset) - strtotime($today_sunrise)) / 60 / 12 / 60 * 30);

    // Calculate the day based on sun's degree in the house
    $day = ceil($sun_degree * $maxdays / 30);

    if ($day > $maxdays) {
        $day = 1;
        $month = $tamil_month_rasi_arr[$sun_house + 1][1];  // Move to next month
    }

    return array($month, $day);
}

//currently works only for current year
function getTamilDate() {
    global $chart_time, $chart_date, $sun_house, $sun_degree, $today_sunrise;
    include_once "eng_tamil_year_map.php";
    
   // Parse the time from chart_time
    $chartHour = (int) date('H', strtotime($chart_time));
    $chartMinute = (int) date('i', strtotime($chart_time));
    
     // Parse the sunrise time
    list($sunriseDate, $sunriseTime) = explode(' ', $today_sunrise);
    $sunriseHour = (int) date('H', strtotime($sunriseTime));
    $sunriseMinute = (int) date('i', strtotime($sunriseTime));
    
    // Check if the time is before today's sunrise
    if ($chartHour < $sunriseHour || ($chartHour == $sunriseHour && $chartMinute < $sunriseMinute)) {
        // Subtract one day from the chart_date
        $chart_date = date('Y-m-d', strtotime($chart_date . ' -1 day'));
    }
    
    $year = date('Y', strtotime($chart_date));
    $month = (int) date('m', strtotime($chart_date));
    $day = date('d', strtotime($chart_date));
    
    if (isset($eng_tam_map[$year])) { // if array exists
        if ($day < $eng_tam_map[$year][$month][1]) {
            $tamil_month = $eng_tam_map[$year][$month-1][0];
        } else {
            $tamil_month = $eng_tam_map[$year][$month][0];
        }
        if ($day - $eng_tam_map[$year][$month][1] >= 0) {
            $tamil_day = $day - $eng_tam_map[$year][$month][1] + 1;
        } else {
            if ($month == 1) {
                $tamil_day = cal_days_in_month(CAL_GREGORIAN, 12, $year) - $eng_tam_map[$year][$month-1][1] + $day + 1;
            } else {
                $tamil_day = cal_days_in_month(CAL_GREGORIAN, $month-1, $year) - $eng_tam_map[$year][$month-1][1] + $day + 1;
            }
        }
    } else {
        $month_day_arr = getTamilMonthDay($sun_house, $sun_degree);
        $tamil_month = $month_day_arr[0];
        $tamil_day = $month_day_arr[1];
    }
    
    $tamil_year = getTamilYear($year, $tamil_month, $month);
    setKariNaal($tamil_month, $tamil_day);
    
    return $tamil_year . ',<BR>' . $tamil_month . ' ' . $tamil_day;
}

/**
 * Translate Tamil date (year and month) without conflicting with other translations
 * This function specifically handles whole year/month names to avoid partial replacements
 */
function translate_tamil_date($tamil_date_str) {
    global $language;
    
    if (!isset($language) || $language === 'tamil' || empty($tamil_date_str)) {
        return $tamil_date_str;
    }
    
    // Tamil year name mappings (exact matches only)
    $year_map = [
        'பிரபவ' => 'Prabhava', 'விபவ' => 'Vibhava', 'சுக்ல' => 'Sukla',
        'பிரமோதூத' => 'Pramodoota', 'பிரசோற்பத்தி' => 'Prajotpatti', 'ஆங்கீரச' => 'Aangirasa',
        'ஸ்ரீமுக' => 'Srimukha', 'பவ' => 'Bhava', 'யுவ' => 'Yuva',
        'தாது' => 'Dhaatu', 'ஈஸ்வர' => 'Eeshwara', 'வெகுதானிய' => 'Vedhukruth',
        'பிரமாதி' => 'Pramadhi', 'விக்கிரம' => 'Vikrama', 'விஷு' => 'Vishnu',
        'சித்திரபானு' => 'Chitrabhanu', 'சுபானு' => 'Subhanu', 'தாரண' => 'Tharana',
        'பார்த்திப' => 'Parthiva', 'விய' => 'Vyaya', 'சர்வசித்து' => 'Sarvajith',
        'சர்வதாரி' => 'Sarvadharin', 'விரோதி' => 'Virodhi', 'விக்ருதி' => 'Vikruti',
        'கர' => 'Khara', 'நந்தன' => 'Nandana', 'விஜய' => 'Vijaya',
        'ஜய' => 'Jaya', 'மன்மத' => 'Manmatha', 'துன்முகி' => 'Durmukhi',
        'ஹேவிளம்பி' => 'Hevilambi', 'விளம்பி' => 'Vilambi', 'விகாரி' => 'Vikari',
        'சார்வரி' => 'Sarvari', 'பிலவ' => 'Plava', 'சுபகிருது' => 'Subhakruth',
        'சோபகிருது' => 'Shobhakruth', 'குரோதி' => 'Krodhi', 'விசுவாவசு' => 'Viswavasu',
        'பரபாவ' => 'Parabhava', 'பிலவங்க' => 'Plavanga', 'கீலக' => 'Keelaka',
        'சௌமிய' => 'Sowmya', 'சாதாரண' => 'Sadharana', 'விரோதகிருது' => 'Virodhikruth',
        'பரிதாபி' => 'Paridhavi', 'பிரமாதீச' => 'Pramadhicha', 'ஆனந்த' => 'Ananda',
        'ராட்சச' => 'Rakshasa', 'நள' => 'Nala', 'பிங்கள' => 'Pingala',
        'காளயுக்தி' => 'Kalayukti', 'சித்தார்த்தி' => 'Siddharthi', 'ரௌத்திரி' => 'Raudri',
        'துன்மதி' => 'Durmathi', 'துந்துபி' => 'Dundubhi', 'ருத்ரோத்காரி' => 'Rudhirodgari',
        'ரக்தாட்சி' => 'Raktakshi', 'குரோதன' => 'Krodhana', 'அட்சய' => 'Akshaya'
    ];
    
    // Tamil month name mappings
    $month_map = [
        'சித்திரை' => 'Chithirai', 'வைகாசி' => 'Vaikasi', 'ஆனி' => 'Aani',
        'ஆடி' => 'Aadi', 'ஆவணி' => 'Aavani', 'புரட்டாசி' => 'Purattasi',
        'ஐப்பசி' => 'Aippasi', 'கார்த்திகை' => 'Karthigai', 'மார்கழி' => 'Margazhi',
        'தை' => 'Thai', 'மாசி' => 'Masi', 'பங்குனி' => 'Panguni'
    ];
    
    $result = $tamil_date_str;
    
    // Replace year names first (sorted by length descending to avoid partial matches)
    $years_sorted = $year_map;
    uksort($years_sorted, function($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });
    
    foreach ($years_sorted as $tamil => $english) {
        $result = str_replace($tamil, $english, $result);
    }
    
    // Then replace month names
    $months_sorted = $month_map;
    uksort($months_sorted, function($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });
    
    foreach ($months_sorted as $tamil => $english) {
        $result = str_replace($tamil, $english, $result);
    }
    
    return $result;
}

function getTamilDate_old() {
    global $chart_time, $chart_date, $sun_house, $sun_degree;
    include_once "eng_tamil_year_map.php";

    // Get the current year, month, and day
    $year = date('Y', strtotime($chart_date));
    $month = (int) date('m', strtotime($chart_date));
    $day = date('d', strtotime($chart_date));

    if (isset($eng_tam_map[$year])) { // If the map exists for the year
        if ($day < $eng_tam_map[$year][$month][1]) {
            $tamil_month = $eng_tam_map[$year][$month - 1][0];
        } else {
            $tamil_month = $eng_tam_map[$year][$month][0];
        }

        if ($day - $eng_tam_map[$year][$month][1] >= 0) {
            $tamil_day = $day - $eng_tam_map[$year][$month][1] + 1;
        } else {
            // Calculate the Tamil day considering month-end transitions
            if ($month == 1) {
                $tamil_day = cal_days_in_month(CAL_GREGORIAN, 12, $year) - $eng_tam_map[$year][$month - 1][1] + $day + 1;
            } else {
                $tamil_day = cal_days_in_month(CAL_GREGORIAN, $month - 1, $year) - $eng_tam_map[$year][$month - 1][1] + $day + 1;
            }
        }
    } else {
        // Fallback method when the map is not available
        $month_day_arr = getTamilMonthDay($sun_house, $sun_degree);
        $tamil_month = $month_day_arr[0];
        $tamil_day = $month_day_arr[1];
    }

    // Get the Tamil year based on the English year
    $tamil_year = getTamilYear($year, $tamil_month);

    // Set the Tamil "Kari Naal" (Auspicious Day)
    setKariNaal($tamil_month, $tamil_day);

    return $tamil_year . ', ' . $tamil_month . ' ' . $tamil_day;
}

// Pass the English month number (1 to 12) as the 3rd argument
// Usage Example for your specific date (Jan 9, 2026):
// echo getTamilYear(2026, "மார்கழி", 1); 
// Result: விசுவாவசு (Correct)
function getTamilYear($eng_year, $tamil_month, $eng_month_num){
    $year_names = ['பிரபவ','விபவ','சுக்ல','பிரமோதூத','பிரசோற்பத்தி','ஆங்கீரச','ஸ்ரீமுக','பவ','யுவ','தாது','ஈஸ்வர','வெகுதானிய','பிரமாதி','விக்கிரம','விஷு','சித்திரபானு','சுபானு','தாரண','பார்த்திப','விய','சர்வசித்து','சர்வதாரி','விரோதி','விக்ருதி','கர','நந்தன','விஜய','ஜய','மன்மத','துன்முகி','ஹேவிளம்பி','விளம்பி','விகாரி','சார்வரி','பிலவ','சுபகிருது','சோபகிருது','குரோதி','விசுவாவசு','பரபாவ','பிலவங்க','கீலக','சௌமிய','சாதாரண','விரோதகிருது','பரிதாபி','பிரமாதீச','ஆனந்த','ராட்சச','நள','பிங்கள','காளயுக்தி','சித்தார்த்தி','ரௌத்திரி','துன்மதி','துந்துபி','ருத்ரோத்காரி','ரக்தாட்சி','குரோதன','அட்சய'];
    
    // Months that ALWAYS belong to the previous cycle (Jan-Apr)
    $always_prev_cycle = ["தை", "மாசி", "பங்குனி"];

    $subtract_one_year = false;

    // Check 1: Is it Thai, Maasi, or Panguni? (Always -1)
    if(in_array($tamil_month, $always_prev_cycle)){
        $subtract_one_year = true;
    }
    // Check 2: Is it Margazhi AND is it January? (If yes, -1)
    elseif($tamil_month == "மார்கழி" && $eng_month_num == 1){
        $subtract_one_year = true;
    }

    if($subtract_one_year){
        $calc_year = $eng_year - 1;
    } else {
        $calc_year = $eng_year;
    }

    $index = ($calc_year - 907) % 60;
    return $year_names[$index];
}



function getTamilYear_old($eng_year, $tamil_month) {
    // Tamil year names array
    $year_names = [
        'பிரபவ', 'விபவ', 'சுக்ல', 'பிரமோதூத', 'பிரசோற்பத்தி', 'ஆங்கீரச', 'ஸ்ரீமுக', 'பவ', 'யுவ', 'தாது',
        'ஈஸ்வர', 'வெகுதானிய', 'பிரமாதி', 'விக்கிரம', 'விஷு', 'சித்திரபானு', 'சுபானு', 'தாரண', 'பார்த்திப',
        'விய', 'சர்வசித்து', 'சர்வதாரி', 'விரோதி', 'விக்ருதி', 'கர', 'நந்தன', 'விஜய', 'ஜய', 'மன்மத', 'துன்முகி',
        'ஹேவிளம்பி', 'விளம்பி', 'விகாரி', 'சார்வரி', 'பிலவ', 'சுபகிருது', 'சோபகிருது', 'குரோதி', 'விசுவாவசு',
        'பரபாவ', 'பிலவங்க', 'கீலக', 'சௌமிய', 'சாதாரண', 'விரோதகிருது', 'பரிதாபி', 'பிரமாதீச', 'ஆனந்த', 'ராட்சச',
        'நள', 'பிங்கள', 'காளயுக்தி', 'சித்தார்த்தி', 'ரௌத்திரி', 'துன்மதி', 'துந்துபி', 'ருத்ரோத்காரி', 'ரக்தாட்சி',
        'குரோதன', 'அட்சய'
    ];

    // Tamil months starting from Chithirai
    $tamil_months = ["சித்திரை", "வைகாசி", "ஆனி", "ஆடி", "ஆவணி", "புரட்டாசி", "ஐப்பசி", "கார்த்திகை", "மார்கழி", "தை", "மாசி", "பங்குனி"];

    // Find the index of the given Tamil month
    $month_index = array_search($tamil_month, $tamil_months);

    if ($month_index === false) {
        return "Invalid Tamil month.";
    }

    // Tamil year transitions in mid-April (Chithirai starts the new year)
    // Months before Chithirai (Thai, Maasi, Panguni) belong to the current Tamil year
    if ($month_index >= 0 && $month_index <= 8) { // Chithirai to Panguni
        return $year_names[($eng_year - 907) % 60];
    } else { // Thai and Masi
        return $year_names[($eng_year - 907) % 60];
    }
}

function _getTamilYear($eng_year, $tamil_month){
    global $sun_house; 
    
    // ---------------------------------------------------------
    // WORKAROUND: We need the English Month number (1-12) 
    // to distinguish Jan-Margazhi from Dec-Margazhi.
    // ---------------------------------------------------------
    
    // Option A: If you have a month variable in your main loop (e.g., $month, $m, $eng_month_num)
    // Add that variable name to this global line:
    global $month, $m, $eng_month; 
    
    // Option B: Check if the English month is passed in the URL (e.g. ?date=2026-01-09)
    if(isset($_REQUEST['date'])) {
        $detected_month = date('n', strtotime($_REQUEST['date']));
    } 
    // Option C: Use the variable found in Option A
    elseif(isset($month)) { $detected_month = $month; }
    elseif(isset($m)) { $detected_month = $m; }
    // Option D: Fallback to "Today's Month" (Use this if you are only displaying current day)
    else {
        $detected_month = date('n'); 
    }
    // ---------------------------------------------------------

    $year_names = ['பிரபவ','விபவ','சுக்ல','பிரமோதூத','பிரசோற்பத்தி','ஆங்கீரச','ஸ்ரீமுக','பவ','யுவ','தாது','ஈஸ்வர','வெகுதானிய','பிரமாதி','விக்கிரம','விஷு','சித்திரபானு','சுபானு','தாரண','பார்த்திப','விய','சர்வசித்து','சர்வதாரி','விரோதி','விக்ருதி','கர','நந்தன','விஜய','ஜய','மன்மத','துன்முகி','ஹேவிளம்பி','விளம்பி','விகாரி','சார்வரி','பிலவ','சுபகிருது','சோபகிருது','குரோதி','விசுவாவசு','பரபாவ','பிலவங்க','கீலக','சௌமிய','சாதாரண','விரோதகிருது','பரிதாபி','பிரமாதீச','ஆனந்த','ராட்சச','நள','பிங்கள','காளயுக்தி','சித்தார்த்தி','ரௌத்திரி','துன்மதி','துந்துபி','ருத்ரோத்காரி','ரக்தாட்சி','குரோதன','அட்சய'];
    
    // Months that ALWAYS belong to previous cycle (Jan, Feb, Mar)
    // Note: Margazhi is removed from here and handled specially below
    $always_prev_cycle = ["தை", "மாசி", "பங்குனி"];

    $subtract_one_year = false;

    // 1. Is it Thai, Maasi, Panguni? (Always -1)
    if(in_array($tamil_month, $always_prev_cycle)){
        $subtract_one_year = true;
    }
    // 2. Is it Margazhi AND is the English Month January (1)?
    elseif($tamil_month == "மார்கழி" && $detected_month == 1){
        $subtract_one_year = true;
    }

    // Apply the Year Logic
    if($subtract_one_year){
        $calc_year = $eng_year - 1; // For Jan 2026, uses 2025
    } else {
        $calc_year = $eng_year;     // For Dec 2026, uses 2026
    }

    $index = ($calc_year - 907) % 60;
    return $year_names[$index];
}


function displayHomePageButtons($in_usertype){
	global $timestamp;
	
	echo "<center><IMG SRC='images/dsjv_title.png?$timestamp' width='70%' style='position:relative;top:0px;padding:20px'></center>";
	echo "<BR><BR>";
	switch($in_usertype){
		case MATCHING:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Matching_input.php\',\'_self\')">புதிய திருமண பொருத்தம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'matchingChartDisplay.php\',\'_self\')">சேமித்த திருமண பொருத்தங்கள்</button>';
			break;
		case BIRTH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாதகம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாதகங்கள்</button>';
			break;
		case BIRTHMATCH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Matching_input.php\',\'_self\')">புதிய திருமண பொருத்தம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'matchingChartDisplay.php\',\'_self\')">சேமித்த திருமண பொருத்தங்கள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாதகம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாதகங்கள்</button>';
			break;
		case BIRTHPANCH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாதகம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாதகங்கள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'new_panchangam.php\',\'_self\')">பஞ்சாங்கம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'hora.php\',\'_self\')">ஹோரை</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'star_thaara_palan.php\',\'_self\')">நட் தாரா பலன் </button>';
		break;
		case BIRTHPANCHMATCH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Matching_input.php\',\'_self\')">புதிய திருமண பொருத்தம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'matchingChartDisplay.php\',\'_self\')">சேமித்த திருமண பொருத்தங்கள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாதகம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாதகங்கள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'new_panchangam.php\',\'_self\')">பஞ்சாங்கம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'hora.php\',\'_self\')">ஹோரை</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'star_thaara_palan.php\',\'_self\')">நட் தாரா பலன் </button>';
		break;
		case PANCHANG:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'new_panchangam.php\',\'_self\')">பஞ்சாங்கம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'hora.php\',\'_self\')">ஹோரை</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'star_thaara_palan.php\',\'_self\')">நட் தாரா பலன் </button>';
			break;
		case NUMEROLOGY:
			break;
		case JAMAKOL:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'index.php\',\'_self\')">முகப்பு</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாமக்கோள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாமக்கோள்</button>';
			break;
		case JAMA_MATCH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'index.php\',\'_self\')">முகப்பு</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாமக்கோள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாமக்கோள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Matching_input.php\',\'_self\')">புதிய திருமண பொருத்தம்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'matchingChartDisplay.php\',\'_self\')">சேமித்த திருமண பொருத்தங்கள்</button>';
			break;
		case JAMA_BIRTH:
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'index.php\',\'_self\')">முகப்பு</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'Birth_input.php\',\'_self\')">புதிய ஜாமக்கோள்</button>';
			echo '<button class="btn btn-primary btn-lg btn-block" type="button" onClick="window.open(\'birthChartDisplay.php\',\'_self\')">சேமித்த ஜாமக்கோள்</button>';
			break;
		default:  //PAID
			//program will not reach this case at all
			echo "Some server error occured..";
			break;
	}
}

function checkUserType($in_usertype){
	switch($in_usertype){
		case MATCHING:
		case BIRTH:
		case BIRTHMATCH:
		case BIRTHPANCH:
		case BIRTHPANCHMATCH:
		case PANCHANG:
		case JAMAKOL:
		case JAMA_MATCH:
		case JAMA_BIRTH:
			header('Location: homepage.php');
		default:  //PAID
			//it will just open the index.php
			break;
	}
}

function updateOfficeDetails($conn,$in_phone,$in_office_name,$in_office_contact)
{
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user SET office_name='$in_office_name',office_contact='$in_office_contact' WHERE phone='$in_phone'";
		
		if(!mysqli_query($conn, $sql)){
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
}

function displayRegistrationMsg(){
	global $timestamp;
	echo <<<EOL
	<div class='regMessage'>
		<img src='images/dsjv_logo.jpg?$timestamp' style='width:200px' />
		<br>
		<img src='images/dsjv_title.png?$timestamp' style='width:350px' />
		<br>
		<br>
		<H2>Thanks for choosing DSJV App. Please contact DSJV Admin with your HELP menu screenshot, NAME and PHONE# for registration.</H2>
		<H2>DSJV app சேவையில் இணைந்ததற்கு நன்றி. தங்கள் பெயர், கைபேசி எண் மற்றும் help மெனு screenshot உடன் DSJV அட்மினை தொடர்பு கொள்ளவும்.</H2>
		<BR>
		<br>
		<BR>
		<br>
		<img src='images/periyanayaki_astro_solutions.png?$timestamp' style='width:250px;left:40%;bottom:20px;position:fixed' />
	</div>
EOL;
}

//this function adds sunrise to rahu kaalam, ema kantam, kulika which is passed in the format 9:00-10:30
function adjustSunrise($timestr){
	global $today_sunrise, $today_sunset;
	
	$AMPM = explode(' ',$timestr);
	$start_end_arr = explode('-',$AMPM[1]);
	
	$sunrisetime = explode(' ',$today_sunrise)[1];
	$sunsettime = explode(' ',$today_sunset)[1];
	
	/*if(strtotime($sunrisetime) > strtotime("06:00")){
		$minutes = $minutes = timeToMinutes($sunrisetime) - timeToMinutes("06:00");
	}
	else{
		$minutes = $minutes = timeToMinutes("06:00") - timeToMinutes($sunrisetime);
	}*/
	
	$agas = timeToMinutes($sunrisetime) - timeToMinutes($sunsettime);
	
	$minutes = timeToMinutes($sunrisetime) - timeToMinutes("06:00");
	
	if($minutes>0){
		$newstart = date('H:i',strtotime($start_end_arr[0].' +'.abs($minutes).' minute'));
		$newend = date('H:i',strtotime($start_end_arr[1].' +'.abs($minutes).' minute'));
	}
	else{
		$newstart = date('H:i',strtotime($start_end_arr[0].' -'.abs($minutes).' minute'));
		$newend = date('H:i',strtotime($start_end_arr[1].' -'.abs($minutes).' minute'));
	}
	
	if(strcmp($AMPM[0],'காலை')==0){
		return $newstart.'-'.$newend.' AM';
	}
	else{
		return $newstart.'-'.$newend.' PM';
	}
}

function getRahuKaalam(){
	global $today_sunrise, $today_sunset, $dayofweek, $g_agas;
	
	//$rahu_kaalam = array("மாலை 4:30-6:00","காலை 7:30-9:00","மதியம் 3:00-4:30","மதியம் 12:00-1:30","மதியம் 1:30-3:00","காலை 10:30-12:00","காலை 09:00-10:30");
	$rahu_kaalam = array(7,1,6,4,5,3,2); //mulipier, 8th jamam will not come
	
	$sunrisetime = explode(' ',$today_sunrise)[1];
	$sunsettime = explode(' ',$today_sunset)[1];
		
	if($g_agas==null){
		$g_agas = timeToMinutes($sunsettime) - timeToMinutes($sunrisetime);
	}
	
	$jama_time = ceil($g_agas/8*60); //single jama time
	
	$rk_startime = date('H:i',strtotime($sunrisetime.' +'.($jama_time * $rahu_kaalam[$dayofweek]).' seconds'));
	$rk_endime = date('H:i',strtotime($rk_startime.' +'.$jama_time.' seconds'));
	
	debug (__FUNCTION__.':'.$rk_startime.' - '.$rk_endime);
	
	return convertToMeridian($rk_startime).' - '.convertToMeridian($rk_endime);
}

function getEmaKantam(){
	global $today_sunrise, $today_sunset, $dayofweek, $g_agas;
	
	//$yama_kantam = array("மதியம் 12:00-1:30","காலை 10:30-12:00","காலை 09:00-10:30","காலை 7:30-9:00","காலை 6:00-7:30","மதியம் 3:00-4:30","மதியம் 1:30-3:00");
	$ek_kaalam = array(4,3,2,1,0,6,5); //multiplier 1st jamam will not come, so reducing it by 1
	
	$sunrisetime = explode(' ',$today_sunrise)[1];
	$sunsettime = explode(' ',$today_sunset)[1];
	
	if($g_agas==null){
		$g_agas = timeToMinutes($sunsettime) - timeToMinutes($sunrisetime);
	}

	$jama_time = ceil($g_agas/8*60); //single jama time
	
	$ek_startime = date('H:i',strtotime($sunrisetime.' +'.($jama_time * $ek_kaalam[$dayofweek]).' seconds'));
	$ek_endime = date('H:i',strtotime($ek_startime.' +'.$jama_time.' seconds'));
	
	debug (__FUNCTION__.':'.$ek_startime.' - '.$ek_endime);
	
	return convertToMeridian($ek_startime).' - '.convertToMeridian($ek_endime);
}

function getKulikai(){
	global $today_sunrise, $today_sunset, $dayofweek, $g_agas;
	
	//$kuligai_neram = array("மதியம் 3:00-4:30","மதியம் 1:30-3:00","மதியம் 12:00-1:30","காலை 10:30-12:00","காலை 09:00-10:30","காலை 7:30-9:00","காலை 6:00-7:30");
	$kuligai_kaalam = array(6,5,4,3,2,1,0); //multiplier
	
	$sunrisetime = explode(' ',$today_sunrise)[1];
	$sunsettime = explode(' ',$today_sunset)[1];
	
	if($g_agas==null){
		$g_agas = timeToMinutes($sunsettime) - timeToMinutes($sunrisetime);
	}

	$jama_time = ceil($g_agas/8*60); //single jama time
	
	$kuli_startime = date('H:i',strtotime($sunrisetime.' +'.($jama_time * $kuligai_kaalam[$dayofweek]).' seconds'));
	$kuli_endime = date('H:i',strtotime($kuli_startime.' +'.$jama_time.' seconds'));
	
	debug (__FUNCTION__.':'.$kuli_startime.' - '.$kuli_endime);
	
	return convertToMeridian($kuli_startime).' - '.convertToMeridian($kuli_endime);
}

function getVaaraSoolai(){
	global $dayofweek;
	$vaara_soolai = array("மேற்கு","கிழக்கு","வடக்கு","வடக்கு","தெற்கு","மேற்கு","கிழக்கு");
	
	return $vaara_soolai[$dayofweek];
}

function getSoolaiParigaaram(){
	global $dayofweek;
	$parikaaram = array("வெல்லம் ","தயிர்","பால்","பால்","தைலம்","வெல்லம்","தயிர்");
	
	return $parikaaram[$dayofweek];
}

function getAbhijit(){
	global $today_sunrise, $today_sunset, $dayofweek, $g_agas;
	
	$sunrisetime = explode(' ',$today_sunrise)[1];
	$sunsettime = explode(' ',$today_sunset)[1];
	
	if($g_agas==null){
		$g_agas = timeToMinutes($sunsettime) - timeToMinutes($sunrisetime);
	}

	$halfday = ceil($g_agas/2*60); //half agas
	
	$noon_time = date('H:i',strtotime($sunrisetime.' +'.$halfday.' seconds'));
	$abijit_startime = date('H:i',strtotime($noon_time.' -'.(24*60).' seconds'));
	$abijit_endime = date('H:i',strtotime($noon_time.' +'.(24*60).' seconds'));
	
	debug (__FUNCTION__.':'.$abijit_startime.' - '.$abijit_endime);
	
	return convertToMeridian($abijit_startime).' - '.convertToMeridian($abijit_endime);
}

function convertToMeridian($time){
	return date('h:i A',strtotime($time));
}

function getAmrithathiYogam(){ 
	global $g_nak, $dayofweek;
	//inner array is for each day of the week starts from Sunday till Sat
	$yoga_arr = ["அசுவினி" =>Array("சி","சி","சி","ம","அ","அ","சி"),
				"பரணி"=>Array("பி","சி","சி","சி","சி","சி","சி"),
				"கிருத்திகை"=>Array("சி","ம","சி","அ","ம","சி","சி"),
				"ரோகிணி"=>Array("சி","அ","அ","சி","ம","ம","அ"),
				"மிருகசிரீஷம்" =>Array("சி","சி","சி","சி","ம","சி","சி"),
				"திருவாதிரை"=>Array("சி","சி","ம","சி","ம","சி","சி"),
				"புனர்பூசம்"=>Array("சி","அ","சி","சி","அ","சி","சி"),
				"பூசம்"=>Array("சி","சி","சி","சி","சி","ம","சி"),
				"ஆயில்யம்"=>Array("சி","சி","சி","சி","சி","ம","ம"),
				"மகம்"=>Array("ம","ம","சி","சி","அ","ம","அ"),
				"பூரம்"=>Array("சி","சி","சி","அ","சி","சி","சி"),
				"உத்திரம்"=>Array("அ","சி","அ","அ","ம","சி","ம"),
				"ஹஸ்தம்"=>Array("சி","சி","சி","ம","சி","அ","ம"),
				"சித்திரை"=>Array("சி","பி","சி","சி","சி","சி","ம"),
				"சுவாதி"=>Array("சி","அ","சி","சி","அ","சி","சி"),
				"விசாகம்"=>Array("ம","ம","ம","சி","சி","சி","சி"),
				"அனுஷம்" =>Array("ம","சி","சி","சி","சி","சி","சி"),
				"கேட்டை"=>Array("ம","சி","ம","சி","பி","ம","சி"),
				"மூலம்"=>Array("அ","சி","அ","ம","சி","அ","சி"),
				"பூராடம்"=>Array("சி","ம","சி","அ","சி","பி","சி"),
				"உத்திராடம்"=>Array("அ","ம","பி","அ","சி","சி","சி"),
				"திருவோணம்"=>Array("அ","அ","சி","சி","சி","ம","சி"),
				"அவிட்டம்"=>Array("ம","சி","சி","பி","சி","சி","சி"),
				"சதயம்"=>Array("சி","சி","ம","சி","ம","சி","அ"),
				"பூரட்டாதி"=>Array("சி","ம","ம","அ","சி","சி","ம"),
				"உத்திரட்டாதி"=>Array("அ","சி","அ","சி","சி","சி","சி"),
				"ரேவதி"=>Array("அ","சி","சி","ம","சி","சி","பி")
				];
				
	$yoga_full_name = ["அ" => "அமிர்த",
					   "சி" => "சித்த",
					   "ம" => "மரண",
					   "பி" => "பிரபலாரிஷ்ட"
					  ];
						
	return $yoga_full_name[$yoga_arr[$g_nak][$dayofweek]];

}

/*
குருடு நாட்கள் (அ) பார்வை இல்லாத நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 1, 2, 3, 4, 25, 26, 27ம் நட்சத்திரங்கள்
நேத்திரம் 1 (அ) ஒரு பார்வை உள்ள நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 5, 6, 7, 8, 21, 22, 23, 24 ம் நட்சத்திரங்கள்
நேத்திரம் 2 (அ) இருகண் உள்ள நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20ம் நட்சத்திரங்கள்
*/

function getNethram()
{
	global $sun_degree,$sun_house, $g_nak_idx;
	$result="";
	
	$sunstar = ceil((($sun_house-1)*30+$sun_degree)/13.33);
	$todaystar = ceil($g_nak_idx/4);
	
	$diff = $todaystar - $sunstar+1; //add one to include sun star
	
	if($diff <= 0){
		$diff = 27+$diff;
	}
	
	debug("$todaystar - $sunstar=$diff");
	
	if(($diff >=1 and $diff <= 4) or ($diff >=25 and $diff <=27)){
		$result = "குருடு (0)";
	}
	elseif(($diff >=5 and $diff <=8) or ($diff >=21 and $diff <=24)){
		$result = "அரை (1)";
	}
	elseif($diff >=9 and $diff <=20){
		$result = "முழு (2)";
	}
	
	return $result;
}

/*
உயிரற்ற நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 1, 2, 10, 19, 27 ம் நட்சத்திரங்கள்
அரை உயிர் உள்ள நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 3, 4, 5, 6, 7, 8, 9, 20, 21, 22, 23, 24, 25, 26 ம் நட்சத்திரங்கள்
முழு உயிர் உள்ள நாட்கள் சூரியன் நிற்கும் நட்சத்திரம் முதல் அன்றைய நட்சத்திரம் வரை எண்ணினால் 11, 12, 13, 14, 15, 16, 17, 18 ம் நட்சத்திரங்கள்
*/
function getJeevan()
{
	global $sun_degree,$sun_house, $g_nak_idx;
	$result="";
	
	$sunstar = ceil((($sun_house-1)*30+$sun_degree)/13.33);
	$todaystar = ceil($g_nak_idx/4);
	
	$diff = $todaystar - $sunstar+1; //add one to include sun star
	
	if($diff < 0){
		$diff = 27-abs($diff);
	}
	
	debug("$todaystar - $sunstar=$diff");
	
	if($diff ==0 or $diff ==1 or $diff == 2 or $diff == 10 or $diff == 19 or $diff == 27){
		$result = "உயிரற்ற (0)";
	}
	elseif(($diff >=3 and $diff <=9) or $diff == 9 or ($diff >=20 and $diff <=26)){
		$result = "அரை உயிர் (&frac12;)";
	}
	elseif($diff >=11 and $diff <=18){
		$result = "முழு உயிர் (1)";
	}
	
	return $result;
}

//மேல்நோக்கு நாள் - ரோகிணி, திருவாதிரை, பூசம், உத்திரம், உத்திராடம், திருவோணம், அவிட்டம், சதயம், உத்திரட்டாதி
//கீழ்நோக்கு நாட்கள் - பரணி, கிருத்திகை, ஆயில்யம், மகம், பூரம், விசாகம், மூலம், பூராடம், பூரட்டாதி
//சமநோக்கு நாட்கள் - அஸ்வினி, மிருகசீரிஷம், புனர்பூசம், ஹஸ்தம், சித்திரை, சுவாதி, அனுஷம், கேட்டை, ரேவதி

function getDayType(){
	global $g_nak;
	
	$good_day_arr = ['ரோகிணி', 'திருவாதிரை', 'பூசம்', 'உத்திரம்', 'உத்திராடம்', 'திருவோணம்', 'அவிட்டம்', 'சதயம்', 'உத்திரட்டாதி'];
	$bad_day_arr = ['பரணி', 'கிருத்திகை', 'ஆயில்யம்', 'மகம்', 'பூரம்', 'விசாகம்', 'மூலம்', 'பூராடம்', 'பூரட்டாதி'];
	$equal_day_arr = ['அசுவினி', 'மிருகசிரீஷம்', 'புனர்பூசம்', 'ஹஸ்தம்', 'சித்திரை', 'சுவாதி', 'அனுஷம்', 'கேட்டை', 'ரேவதி'];
	
	if(array_search($g_nak,$good_day_arr)!==false){
		return 'மேல் நோக்கு';
	}
	elseif(array_search($g_nak, $bad_day_arr)!==false){
		return 'கீழ் நோக்கு';
	}
	elseif(array_search($g_nak, $equal_day_arr)!==false){
		return 'சம நோக்கு';
	}
}

function setKariNaal($in_tamil_month,$in_tamil_day){
	global $g_kariNaal;
	
	$kariNaal_arr = ['சித்திரை' => array(6, 15),
					 'வைகாசி' => array(7, 16, 17),
					 'ஆனி' => array(1, 6),
					 'ஆடி' => array(2, 10, 20),
					 'ஆவணி' => array(2, 9, 28),
					 'புரட்டாசி' => array(16, 29),
					 'ஐப்பசி' => array(6, 20),
					 'கார்த்திகை' => array(1, 10, 17),
					 'மார்கழி' => array(6, 9, 11),
					 'தை' => array(1, 2, 3, 11, 17),
					 'மாசி' => array(15, 16, 17),
					 'பங்குனி' => array(6, 15, 19)
					];
					
	if(array_search($in_tamil_day,$kariNaal_arr[$in_tamil_month])!==false){
		$g_kariNaal = "கரி நாள்";
	}
}

function sqlDateToDefault($date){
	$date_arr = explode('-',$date);
	
	return $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
}

function displayBhavaChart()
{
	global $g_bhava_arr, $tr_sw_planet;
	
	echo '<center><div class="divTable blueTable" id="bhava_chart" style="margin-top:20px">';
		
		echo '<div class="divTableBody">';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCellLeftTop">'.map_display_text($tr_sw_planet[11]).'</div>'; 
					echo '<div class="divTableOuterCell" style="border-left:none"></div>';
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[0]).'</div>'; 
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[1]).'</div>'; 
					echo '<div class="divTableOuterCell" style="border-right:none"></div>';
					echo '<div class="divTableOuterCellRightTop">'.map_display_text($tr_sw_planet[2]).'</div>'; 
			echo '</div>';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCell" style="border-top:none"></div>';
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[11],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[0],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[1],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[2],2))).'</div>'; 
					echo '<div class="divTableOuterCell"  style="border-top:none"></div>';
			echo '</div>';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[10]).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[10],2))).'</div>'; 
					echo '<div class="divTableCenterCell1" style="font-size:10px">'.__('menu_bhava_chart').'</div>';
					echo '<div class="divTableCenterCell1"></div>';
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[3],2))).'</div>'; 
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[3]).'</div>'; 
			echo '</div>';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[9]).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[9],2))).'</div>'; 
					echo '<div class="divTableCenterCell1"></div>';
					echo '<div class="divTableCenterCell1" style="font-size:10px"></div>';
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[4],2))).'</div>'; 
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[4]).'</div>'; 
			echo '</div>';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCell" style="border-bottom:none"></div>';
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[8],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[7],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[6],2))).'</div>'; 
					echo '<div class="divTableCell">'.map_display_text(join(' ',array_slice($g_bhava_arr[5],2))).'</div>'; 
					echo '<div class="divTableOuterCell"  style="border-bottom:none"></div>';
			echo '</div>';
			echo '<div class="divTableRow">';
					echo '<div class="divTableOuterCellLeftBottom">'.map_display_text($tr_sw_planet[8]).'</div>'; 
					echo '<div class="divTableOuterCell" style="border-left:none"></div>';
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[7]).'</div>'; 
					echo '<div class="divTableOuterCell">'.map_display_text($tr_sw_planet[6]).'</div>'; 
					echo '<div class="divTableOuterCell" style="border-right:none"></div>';
					echo '<div class="divTableOuterCellRightBottom">'.map_display_text($tr_sw_planet[5]).'</div>'; 
			echo '</div>';
		echo '</div>';
	echo '</div></center>';
	echo '<center>'.__('bhava_change').'</center>';
}

function updateBhavaArray($deg, $planet, $house, $reverse = false) {
    global $g_bhava_arr, $lagna_house;
    
    $targetHouse = countHouses($lagna_house, $house);
    
    if ($targetHouse == 12) {
        $targetHouse = 0;
    }
    
//   $tempdeg = DECtoDMS_jamakol($deg);
//   [$pl, $de] = explode('-', $planet);
	 $pl = $planet;

    $house_start = $g_bhava_arr[$targetHouse][0];
    $house_end = $g_bhava_arr[$targetHouse][0] + 30;

    if ($house_end > 360) {
        $house_end -= 360;
    }

    $pl_degree = $deg + $house * 30;

    debug("<br><br>$planet " . $pl_degree . ' Bhava_housestart=' . $house_start . ' Bhava_houseend=' . $house_end . ' bava_house =' . $targetHouse . ' Kalapurush_house=' . $house);

    if ($house_start < $house_end) {
        if ($pl_degree >= $house_start && $pl_degree < $house_end) {
            array_push($g_bhava_arr[$house], $planet);
        } elseif ($pl_degree > $house_end) {
           // array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl . '-' . ($pl_degree));
		    array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl);
        } elseif ($pl_degree < $house_start) {
            //array_push($g_bhava_arr[($house - 1 < 0 ? 11 : $house - 1)], '*' . $pl . '-' . ($pl_degree));
			array_push($g_bhava_arr[($house - 1 < 0 ? 11 : $house - 1)], '*' . $pl);
        }
    } else {
        if ($pl_degree >= $house_start || $pl_degree < $house_end) {
            array_push($g_bhava_arr[$house], $planet);
        } elseif ($pl_degree > $house_end && $pl_degree < $house_start && $pl_degree < 30) {
            //array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl . '-' . ($pl_degree));
			array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl);
        }
		elseif ($pl_degree < $house_start) {
            //array_push($g_bhava_arr[($house - 1 < 0 ? 11 : $house - 1)], '*' . $pl . '-' . ($pl_degree));
			array_push($g_bhava_arr[($house - 1 < 0 ? 11 : $house - 1)], '*' . $pl);
        }
    }

	/*if ($house_start < $house_end) {
        if ($pl_degree > $house_start && $pl_degree < $house_end) {
            array_push($g_bhava_arr[$house], $planet);
        } elseif ($pl_degree > $house_end) {
            array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl . '-' . ($pl_degree));
        } elseif ($pl_degree < $house_start) {
            array_push($g_bhava_arr[($house - 1 < 0 ? 11 : $house - 1)], '*' . $pl . '-' . ($pl_degree));
        }
    } else {
        if ($pl_degree > $house_start || $pl_degree < $house_end) {
            array_push($g_bhava_arr[$house], $planet);
        } elseif ($pl_degree > $house_end && $pl_degree < $house_start) {
            array_push($g_bhava_arr[($house + 1 > 11 ? 0 : $house + 1)], '*' . $pl . '-' . ($pl_degree));
        }
    }*/
}

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
		$planetDeg[trim($row[0])] = round(fmod(floatval($row[1]), 30.0),4);
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

function planetSort_transit($inArray)
{
	global $planetDeg_transit, $DEBUG;
	$count = 0;
	
	foreach ($inArray as $key => $line)
	{
		$row = explode(',',$line);
		$count = $count + 1;
		
		if($count>8 && $count!=21) {continue;}

		debug($count."[".$row[0]."|".$row[1]."|".$row[2]."]<Br>");
		$planetDeg_transit[trim($row[0])] = round(fmod(floatval($row[1]), 30.0),4);
	}
	
	debug("PLANET+DEGREE");
	if($DEBUG==1){var_dump($planetDeg_transit);}
	
	debug("PLANET+DEGREE+SORTED");
	asort($planetDeg_transit);
	
	if($DEBUG==1){var_dump($planetDeg_transit);}
	
	foreach ($planetDeg_transit as $key => $val) {
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
	
	$planetDeg_transit = $out_planet_degree;
}

//get thithi details from DB
function getDataFromThithiById($conn, $id) {
    $query = "SELECT * FROM thithi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = array();
    if ($row = mysqli_fetch_assoc($result)) {
        $data = array(
            'id' => $row['id'],
            'thithi_name' => $row['thithi_name'],
            'thithi_temple' => $row['thithi_temple'],
			'thithi_temple_link' => $row['thithi_temple_link'],
            'thithi_god' => $row['thithi_god']
        );
    }
    
    return $data;
}

//get star details from DB
function getDataFromStarById($conn, $id) {
    $query = "SELECT * FROM star WHERE id = ?";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = array();
    if ($row = mysqli_fetch_assoc($result)) {
        $data = array(
            'id' => $row['id'],
            'star_name' => $row['star_name'],
            'star_temple' => $row['star_temple'],
			'star_temple_link' => $row['star_temple_link'],
            'star_lord' => $row['star_lord']
        );
    }
    
    return $data;
}

//get star details from DB
function getDataFromYogiById($conn, $id) {
    $query = "SELECT id,yogam,star_planet,temple,remedy,google_map FROM yogi WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $data = array();
    if ($row = mysqli_fetch_assoc($result)) {
        $data = array(
            'id' => $row['id'],
            'yogam' => $row['yogam'],
            'star_planet' => $row['star_planet'],
			'temple' => $row['temple'],
            'remedy' => $row['remedy'],
			'google_map' => $row['google_map']
        );
    }
    
    return $data;
}

//this function is to get Gubera point for birth chart
/*
குபேர புள்ளி (Kubera Point) கணக்கிடும் விதம்:

குபேரம்=(சந்திரன்பாதம்×2வதுஅதிபதிபாதம்)(mod108)

இதனால் கிடைக்கும் மதிப்பு 1–108 வரையில் இருக்கும்.

0 வந்தால், அதை 108 என்று எடுத்துக் கொள்ள வேண்டும்.

அந்த எண் எந்த நட்சத்திரப் பாதத்தில், எந்த ராசியில், எந்த வீட்டில் இருக்கிறது என்பதை கண்டுபிடிக்கிறோம்.

👉 அதாவது, சந்திரனின் பாதம் மற்றும் 2வது வீட்டின் அதிபதியின் பாதம் இரண்டையும் பெருக்கி, 108-ஆல் வகுத்த பின் மீதி எண் தான் குபேரப் பாதம்.
*/

function getKubera(){
	global $planet_starlord, $lagna_house;
	
	//find moon paatham count per kalapurusha raasi
	$moon_paatham = $planet_starlord['சந்'][4];
	
	//$second_athipathi = findAthipathi(targetHouse($lagna_house,2)-1);
	
	$second_athipathi_house = targetHouse($lagna_house,2)-1;
	
	if($second_athipathi_house < 0)
			$second_athipathi_house = 11;
	
	$second_athipathi = findAthipathi($second_athipathi_house);
	
	debug(findAthipathi(targetHouse($lagna_house,2)-1) . '|' . $second_athipathi_house);
	
	$second_athipathi_paatham = $planet_starlord[$second_athipathi][4];
	
	//Forumla to find the gubera paadham, (Moon paatham * $second_lord_star_paatham) / 108, reminder is the required point/paatham
	$gubera_paatham = ($moon_paatham * $second_athipathi_paatham) % 108;
	
	if($gubera_paatham == 0)
		$gubera_paatham = 108;
	
	debug("moon-paatham=$moon_paatham--second_athipathi-paatham=$second_athipathi_paatham--kubera_paatham=$gubera_paatham");
	debug(getValue(getStarPaatham($gubera_paatham),1));
	
	$guberaArr[0] = getValue(getStarPaatham($gubera_paatham),1);  //nak paatham
	$guberaArr[1] = getRasiNameFromPaatham($gubera_paatham); // Rasi name
	$guberaArr[2] = getRasiHouseFromPaatham($gubera_paatham); // Rasi house
	$guberaArr[3] = ceil($gubera_paatham/4); //star index
	
	return $guberaArr;
}

//function get star paatham for a given 108 paaatham number
function getStarPaatham($paatham_idx){
	global $nak_array;
						
	return $nak_array[$paatham_idx];
}

function getRasiNameFromPaatham($paatham){
	$rasi_arr = ["மேஷம்", "ரிஷபம்", "மிதுனம்", "கடகம்", "சிம்மம்", "கன்னி", "துலாம்", "விருச்சிகம்", "தனுசு", "மகரம்", "கும்பம்", "மீனம்"];

	$idx = ceil($paatham / 9)-1;

	return $rasi_arr[$idx];
}

function getRasiHouseFromPaatham($paatham){
	return (int)($paatham / 9);
}

function getDasaInfluencers($planet){
	/*
	"planet" => "bodhagan","vedhagan","paasagan","kaaragan";
	
	போதகன்:
		தனது தசைகளில் வரும் புத்திகளில் தனது பலனைக் கொடுக்கும்
	
	வேதகன்:
		பாவத்தின் பலனை மாறுபடச் செய்யும்.
	
	பாசகன்:
		பாவத்தின் பலன் கிடைக்க உதவி செய்யும்.
	
	காரகன்:
		சம்பாதித்த பொருளை கிடைக்க உதவி செய்யும்.
	*/
	
	/*$influencers = [
					  "சூரி" => ["செ","சுக்","சனி","குரு"],
					  "சந்" => ["செ","சூரி","சுக்","சனி"],
					  "செ" => ["சந்","புத","சூரி","சனி"],
					  "குரு" => ["செ","சூரி","சனி","சந்"],
					  "சுக்" => ["குரு","சனி","புத","சூரி"],
					  "சனி" => ["சந்","செ","சுக்","குரு"],
					  "புத" => ["குரு","செ","சந்","சுக்"],
					  "ரா" => ["குரு","சூரி","புத","செ"],
					  "கே" => ["புத","சந்","சுக்","சனி"],
					];
	*/
	
	$influencers = [
					  "சூரி" => "போதகன் - செ<BR>வேதகன் - சுக்<BR>பாசகன் - சனி<BR>காரகன் - குரு",
					  "சந்" => "போதகன் - செ<BR>வேதகன் - சூரி<BR>பாசகன் - சுக்<BR>காரகன் - சனி",
					  "செ" => "போதகன் - சந்<BR>வேதகன் - புத<BR>பாசகன் - சூரி<BR>காரகன் - சனி",
					  "குரு" => "போதகன் - செ<BR>வேதகன் - சூரி<BR>பாசகன் - சனி<BR>காரகன் - சந்",
					  "சுக்" => "போதகன் - குரு<BR>வேதகன் - சனி<BR>பாசகன் - புத<BR>காரகன் - சூரி",
					  "சனி" => "போதகன் - சந்<BR>வேதகன் - செ<BR>பாசகன் - சுக்<BR>காரகன் - குரு",
					  "புத" => "போதகன் - குரு<BR>வேதகன் - செ<BR>பாசகன் - சந்<BR>காரகன் - சுக்",
					  "ரா" => "போதகன் - குரு<BR>வேதகன் - சூரி<BR>பாசகன் - புத<BR>காரகன் - செ",
					  "கே" => "போதகன் - புத<BR>வேதகன் - சந்<BR>பாசகன் - சுக்<BR>காரகன் - சனி",
					];
					
	return $influencers[$planet];
}

function displayChart($in,$out)
{

echo <<<EOL
	<div class="divTable blueTable" id="chart">
		
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableOuterCell"><?php echo translate_text($out[11]); ?></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[0]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[11]<span class='symbol'>&#9795;</span></div>
					<div class="divTableCell">$in[0]<span class='symbol'>&#9794;</span></div>
					<div class="divTableCell">$in[1]<span class='symbol'>&#9792;</span></div>
					<div class="divTableCell">$in[2]<span class='symbol'>&#9791;</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[10]<span class='symbol'>&#9796;</span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;</span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;</span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[4]<span class='symbol'>&#9737;</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[8]<span class='symbol'>&#9795;</span></div>
					<div class="divTableCell">$in[7]<span class='symbol'>&#9794;</span></div>
					<div class="divTableCell">$in[6]<span class='symbol'>&#9792;</span></div>
					<div class="divTableCell">$in[5]<span class='symbol'>&#9791;</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[8]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[6]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[5]</div>
			</div>
		</div>	
	</div>

EOL;
}

function updateMatchingMark($conn,$rowid,$mark)
{
	startSqlTxn($conn);

	try {
		$sql = "UPDATE matching_chart SET mark='$mark' WHERE rowid='$rowid'";
		
		if(!mysqli_query($conn, $sql)){
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
}

function displayNumerologyInfo(){
	global $timestamp;
	echo<<<EOL
	    <center><img src='images/num_planets_letters.png?$timestamp' style='width:-webkit-fill-available;height:auto'></center>
		<center><img src='images/num_friend_enemy.png?$timestamp' style='width:-webkit-fill-available;height:auto'></center>
		<!-- Disabled as requested by Manikantan sir -->
		<!-- <center><img src='images/num_rasi_chart.png?$timestamp' style='width:-webkit-fill-available;height:auto'></center> -->
EOL;
}

function calculateVivahaChakra($sun_nakshatra, $moon_nakshatra) {
    // Nakshatra sequence
    $nakshatras = [
        'அசுவினி', 'பரணி', 'கிருத்திகை', 'ரோகிணி', 'மிருகசிரீஷம்', 'திருவாதிரை', 'புனர்பூசம்', 'பூசம்',
        'ஆயில்யம்', 'மகம்', 'பூரம்', 'உத்திரம்', 'ஹஸ்தம்', 'சித்திரை', 'சுவாதி', 'விசாகம்', 'அனுஷம்',
        'கேட்டை', 'மூலம்', 'பூராடம்', 'உத்திராடம்', 'திருவோணம்', 'அவிட்டம்', 'சதயம்', 'பூரட்டாதி', 'உத்திரட்டாதி', 'ரேவதி'
    ];

    // Adjust the start index to be one before the sun's nakshatra
    $sun_index = array_search($sun_nakshatra, $nakshatras);
    $start_index = ($sun_index - 1 + count($nakshatras)) % count($nakshatras);

    // Create houses and place Nakshatras
    $houses = [];
    for ($i = 0; $i < 9; $i++) {
        $houses[$i + 1] = [
            $nakshatras[($start_index + 3 * $i) % 27],
            $nakshatras[($start_index + 3 * $i + 1) % 27],
            $nakshatras[($start_index + 3 * $i + 2) % 27]
        ];
    }

    // Check in which house the Moon's Nakshatra falls
    $moon_house = 0;
    foreach ($houses as $index => $house) {
        if (in_array($moon_nakshatra, $house)) {
            $moon_house = $index;
            break;
        }
    }

   // Results based on the house where Moon's Nakshatra is found
    /*$results = [
        1 => 'Collateral Damage to the Family',
        2 => 'Wealthy and Blessed',
        3 => 'Collateral Damage to the Girl\'s Family',
        4 => 'Poverty and cursed',
        5 => 'Gainful and Beneficial',
        6 => 'Loss of Reputation and honour',
        7 => 'Devastating results for the Girl',
        8 => 'Completely Successful',
        9 => 'Wonderful Pair and Blessed'
    ];*/
	
	$results = [
        1 => '<div class="vivagaResultRed">வேதனை<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக இல்லை</div>',
        2 => '<div class="vivagaResultGreen">லட்சமி கடாட்சம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக உள்ளது</div>',
        3 => '<div class="vivagaResultRed">வறுமை, பீடை<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக இல்லை</div>',
        4 => '<div class="vivagaResultRed">இழப்பு, விரயம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக இல்லை</div>',
        5 => '<div class="vivagaResultGreen">சர்வ சௌபாக்கியம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக உள்ளது</div>',
        6 => '<div class="vivagaResultRed">அசிங்கம், அவமானம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக இல்லை</div>',
        7 => '<div class="vivagaResultRed">வளர்ச்சி இல்லை<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக இல்லை</div>',
        8 => '<div class="vivagaResultGreen">சகல சௌபாக்கியம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக உள்ளது</div>',
        9 => '<div class="vivagaResultGreen">சகல சௌபாக்கியம்<BR>இந்த நாள் விவாஹ சக்ரப்படி நன்றாக உள்ளது</div>'
    ];

    // Define the houses to be displayed in red
    $red_houses = [1, 3, 4, 6, 7];

    // Define the order of display
    $display_order = [7, 8, 9, 6, 1, 2, 5, 4, 3];

    // Generate HTML for the Vivaha Chakra table
    $html = '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size:10px; text-align:center;">';
    foreach ($display_order as $house_number) {
        $background_color = in_array($house_number, $red_houses) ? '#FADBD8' : '#D5F5E3';
        $html .= "<div style='border: 1px solid #000; padding: 10px; background-color: $background_color;'>
                    <strong>House $house_number</strong><br>" .
                    implode('<br>', $houses[$house_number]) .
                "</div>";
    }
    $html .= '</div>';
	
	

    $result = [
        'result' => $results[$moon_house] ?? 'Invalid Nakshatra',
        'chart' => $html
    ];
	
	displayVivahaChakram($result,$sun_nakshatra,$moon_nakshatra);
}

function displayVivahaChakra_Manikantan($sun_nakshatra, $moon_nakshatra) {
    // Nakshatra sequence
    $nakshatras = [
        'அசுவினி', 'பரணி', 'கிருத்திகை', 'ரோகிணி', 'மிருகசிரீஷம்', 'திருவாதிரை', 'புனர்பூசம்', 'பூசம்',
        'ஆயில்யம்', 'மகம்', 'பூரம்', 'உத்திரம்', 'ஹஸ்தம்', 'சித்திரை', 'சுவாதி', 'விசாகம்', 'அனுஷம்',
        'கேட்டை', 'மூலம்', 'பூராடம்', 'உத்திராடம்', 'திருவோணம்', 'அவிட்டம்', 'சதயம்', 'பூரட்டாதி', 'உத்திரட்டாதி', 'ரேவதி'
    ];
	
	$result = "";
	
	// Get the indexes of Sun and Moon Nakshatras
	$sun_index = array_search($sun_nakshatra, $nakshatras)+1;
	$moon_index = array_search($moon_nakshatra, $nakshatras)+1;


   // Ensure valid indexes before calculation
	if ($sun_index === false || $moon_index === false) {
		die("Invalid Nakshatra name provided.");
	}

	// Circular counting from sun_nakshatra to moon_nakshatra
	$count = ($moon_index - $sun_index + count($nakshatras)) % count($nakshatras) + 1;

	
	debug("sun_idx=$sun_index, moon_idx=$moon_index, count=$count");
	
	if(in_array($count, [3, 4, 5, 12, 13, 14, 21, 22, 23, 24, 25, 26])){
		$result = '<div class="vivagaResultGreen" id="vivahaRes">' . __('this_day_good_vivah') . '</div>';
	}
	else{
		$result = '<div class="vivagaResultRed"  id="vivahaRes">' . __('this_day_not_good_vivah') . '</div>';
	}
	
	echo "<center>";
	echo "<div class='divPanchangHeading' id='vivahaChakram' style='width: var(--chart-width);padding-top:15px; padding-bottom:15px'>" . __('today_marriage_chakra') . "<BR><BR>" . $result;
	echo "<BR>";
	echo "<div style='font-size: 12px;text-align:left;' id='vivSunNak'>" . __('sun_star') . ": <U>" . translate_tamil_text($sun_nakshatra) . "</U></div>";
	echo "<div style='font-size: 12px;text-align:left' id='vivMooNak'>" . __('moon_star') . ": <U>" . translate_tamil_text($moon_nakshatra) . "</U></div>";
	echo "</center>";
	echo "</div>";
	
}

function displayVivahaChakram($result, $sunstar, $moonstar){
	echo "<center>";
	echo "<div class='divPanchangHeading' id='vivahaChakram' style='width: var(--chart-width)'>இன்றய விவாஹ சக்ர பலன்<BR><BR>" . $result['result'];
	echo "சூரியன் நின்ற நட்சத்திரம்: <U>$sunstar</U><BR>";
	echo "சந்திரன் நின்ற நட்சத்திரம்: <U>$moonstar</U><BR><BR>";
	echo $result['chart'];
	echo "</center>";
}


function getProkeralaAuthToken()
{
	$curl = curl_init();

	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://api.prokerala.com/token',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => 'client_secret=SI8Oj6gB6ElVSIOolaQqKM4h5l9nptFrmfLmS1zf&grant_type=client_credentials&client_id=fc7711e7-e6a3-4728-97df-b83a7d53ab47',
	  CURLOPT_HTTPHEADER => array(
		'Content-Type: application/x-www-form-urlencoded'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	$auth_arr = json_decode($response,true);
	
	return $auth_arr['access_token'];
}

function getProkeralaPunchangDetail($in_lat,$in_long,$in_datetime,$in_lang)
{
	$curl = curl_init();
	
	$token = getProkeralaAuthToken();

	curl_setopt_array($curl, array(
	  //CURLOPT_URL => 'https://api.prokerala.com/v2/astrology/panchang?ayanamsa=1&coordinates=13.214747,80.097626&datetime=2023-02-01T20:58:21%2B05:30&la=ta',
	  CURLOPT_URL => 'https://api.prokerala.com/v2/astrology/panchang?ayanamsa=1&coordinates='.$in_lat.','.$in_long.'&datetime='.$in_datetime.'&la='.$in_lang,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_HTTPHEADER => array(
		'Accept: application/xml',
		'Authorization: Bearer '.$token
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	
	$panchang_arr = json_decode($response, true);
	
	var_dump($panchang_arr);
	
	displayProkeralaDetailPanchang($panchang_arr);
}

function displayProkeralaDetailPanchang($in_arr)
{
	echo "<center><div class='divTable blueTable' style='height:40px;' id='panchangBody'>"; 
	
	foreach($in_arr['data'] as $heading => $value)
	{
		if(strpos($heading,"nakshatra")===0){
			foreach($value[0] as $subhead => $subvalue){
				echo "<div class='divTableRow'>";
					echo "<div class='divPanchangHeading'>$subhead</div>";
					echo "<div class='divPanchang'>$subvalue</div>";
				echo "</div>";
			}
		}
		else{
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>$heading</div>";
				echo "<div class='divPanchang'>$value</div>";
			echo "</div>";
		}
	}
	
	echo "</div>";
}

function getProkeralaTamilCalenderDetail($date,$calendar,$in_lang)
{
	$curl = curl_init();
	
	$token = getProkeralaAuthToken();

	curl_setopt_array($curl, array(
	  //CURLOPT_URL => 'https://api.prokerala.com/v2/astrology/panchang?ayanamsa=1&coordinates=13.214747,80.097626&datetime=2023-02-01T20:58:21%2B05:30&la=ta',
	  CURLOPT_URL => 'https://api.prokerala.com/v2/calendar?date='.$date.'&calendar='.$calendar.'&la='.$in_lang,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'GET',
	  CURLOPT_HTTPHEADER => array(
		'Accept: application/xml',
		'Authorization: Bearer '.$token
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	
	$panchang_arr = json_decode($response, true);
	
	return $panchang_arr;
	
	//displayProkeralaDetailPanchang($panchang_arr);
}

function runSwetest($args) {
	// ------- Swiss ephemeris // ------- 
	$libPath = './sweph/';
	// ------- Swiss ephemeris // ------- 
	
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		debug('RUNNING ON WINDOWS');
		$swetestPath = 'swetest64.exe -edir'.$libPath;
	} else {
		debug('RUNNING ON LINUX');
		$swetestPath = 'swetest';
	}
    // Path to swetest.exe
    //$swetestPath = 'swetest.exe';

    // Command execution
    $command = $swetestPath . ' ' . $args;
	
    $output = shell_exec($command);

    return $output;
}
/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/shodasamsa-chart
/********************************/

function calculateD2Chart() {
    global $planet_starlord;
	
	$d2_array = [
					[0.0,15.0,4],  //male male
					[15.0,30.0,3], //male female
					[0.0,15.0,3],  //female male
					[15.0,30.0,4], //female female
				];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
	
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 2 == 1){ //male rasi
			if($signDegree >$d2_array[0][0] and $signDegree <= $d2_array[0][1]){
				$offset = $d2_array[0][2];
			}
			else if($signDegree >$d2_array[1][0] and $signDegree <= $d2_array[1][1]){
				$offset = $d2_array[1][2];
			}
		}
		else{  //female rasi
			if($signDegree >$d2_array[0][0] and $signDegree <= $d2_array[0][1]){
				$offset = $d2_array[2][2];
			}
			else if($signDegree >$d2_array[1][0] and $signDegree <= $d2_array[1][1]){
				$offset = $d2_array[3][2];
			}
		}

        // Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD3Chart() {
    global $planet_starlord;
	
	$d3_array = [
					[0.0,10.0,1],
					[10.0,20.0,5],
					[20.0,30.0,9]
				];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($signDegree >$d3_array[0][0] and $signDegree <= $d3_array[0][1]){
			$offset = targetHouse($sign,$d3_array[0][2]) - 1;
		}
		else if($signDegree >$d3_array[1][0] and $signDegree <= $d3_array[1][1]){
			$offset = targetHouse($sign,$d3_array[1][2]) - 1;
		}
		else if($signDegree >$d3_array[2][0] and $signDegree <= $d3_array[2][1]){
			$offset = targetHouse($sign,$d3_array[2][2]) - 1;
		}
		
		if($offset < 0)
			$offset = 11;

        // Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD4Chart() {
    global $planet_starlord;
	
	$d4_array = [
					[0.0,7.5,1],
					[7.5,15.0,4],
					[15.0,22.5,7],
					[22.5,30,10]
				];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($signDegree >$d4_array[0][0] and $signDegree <= $d4_array[0][1]){
			$offset = targetHouse($sign,$d4_array[0][2]) - 1;
		}
		else if($signDegree >$d4_array[1][0] and $signDegree <= $d4_array[1][1]){
			$offset = targetHouse($sign,$d4_array[1][2]) - 1;
		}
		else if($signDegree >$d4_array[2][0] and $signDegree <= $d4_array[2][1]){
			$offset = targetHouse($sign,$d4_array[2][2]) - 1;
		}
		else if($signDegree >$d4_array[3][0] and $signDegree <= $d4_array[3][1]){
			$offset = targetHouse($sign,$d4_array[3][2]) - 1;
		}
		
		if($offset < 0)
			$offset = 11;

        // Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD5Chart() {
    global $planet_starlord;
	
	$d2_array = [
					[0.0,6.0,0],  //male
					[6.0,12.0,10], //male
					[12.0,18.0,8],  //male
					[18.0,24.0,2], //male
					[24.0,30.0,6], //male
					[0.0,6.0,1],  //female
					[6.0,12.0,5], //female
					[12.0,18.0,11],  //female
					[18.0,24.0,9], //female
					[24.0,30.0,7] //female
				];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 2 == 1){ //male rasi
			if($signDegree >$d2_array[0][0] and $signDegree <= $d2_array[0][1]){
				$offset = $d2_array[0][2];
			}
			else if($signDegree >$d2_array[1][0] and $signDegree <= $d2_array[1][1]){
				$offset = $d2_array[1][2];
			}
			else if($signDegree >$d2_array[2][0] and $signDegree <= $d2_array[2][1]){
				$offset = $d2_array[2][2];
			}
			else if($signDegree >$d2_array[3][0] and $signDegree <= $d2_array[3][1]){
				$offset = $d2_array[3][2];
			}
			else if($signDegree >$d2_array[4][0] and $signDegree <= $d2_array[4][1]){
				$offset = $d2_array[4][2];
			}
		}
		else{  //female rasi
			if($signDegree >$d2_array[5][0] and $signDegree <= $d2_array[5][1]){
				$offset = $d2_array[5][2];
			}
			else if($signDegree >$d2_array[6][0] and $signDegree <= $d2_array[6][1]){
				$offset = $d2_array[6][2];
			}
			else if($signDegree >$d2_array[7][0] and $signDegree <= $d2_array[7][1]){
				$offset = $d2_array[7][2];
			}
			else if($signDegree >$d2_array[8][0] and $signDegree <= $d2_array[8][1]){
				$offset = $d2_array[8][2];
			}
			else if($signDegree >$d2_array[9][0] and $signDegree <= $d2_array[9][1]){
				$offset = $d2_array[9][2];
			}
		}
		
		debug("<br>$planet---$sign($signDegree)----offset=$offset");

		// Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD6Chart() {
    global $planet_starlord;
	
	$d6_array = [
					[0.0,5.0,0],  //male
					[5.0,10.0,1], //male
					[10.0,15.0,2],  //male
					[15.0,20.0,3], //male
					[20.0,25.0,4], //male
					[25.0,30.0,5], //male
					
					[0.0,5.0,6],  //female
					[5.0,10.0,7], //female
					[10.0,15.0,8],  //female
					[15.0,20.0,9], //female
					[20.0,25.0,10], //female
					[25.0,30.0,11], //female
				];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 2 == 1){ //male rasi
			if($signDegree >$d6_array[0][0] and $signDegree <= $d6_array[0][1]){
				$offset = $d6_array[0][2];
			}
			else if($signDegree >$d6_array[1][0] and $signDegree <= $d6_array[1][1]){
				$offset = $d6_array[1][2];
			}
			else if($signDegree >$d6_array[2][0] and $signDegree <= $d6_array[2][1]){
				$offset = $d6_array[2][2];
			}
			else if($signDegree >$d6_array[3][0] and $signDegree <= $d6_array[3][1]){
				$offset = $d6_array[3][2];
			}
			else if($signDegree >$d6_array[4][0] and $signDegree <= $d6_array[4][1]){
				$offset = $d6_array[4][2];
			}
			else if($signDegree >$d6_array[5][0] and $signDegree <= $d6_array[5][1]){
				$offset = $d6_array[5][2];
			}
		}
		else{  //female rasi
			if($signDegree >$d6_array[6][0] and $signDegree <= $d6_array[6][1]){
				$offset = $d6_array[6][2];
			}
			else if($signDegree >$d6_array[7][0] and $signDegree <= $d6_array[7][1]){
				$offset = $d6_array[7][2];
			}
			else if($signDegree >$d6_array[8][0] and $signDegree <= $d6_array[8][1]){
				$offset = $d6_array[8][2];
			}
			else if($signDegree >$d6_array[9][0] and $signDegree <= $d6_array[9][1]){
				$offset = $d6_array[9][2];
			}
			else if($signDegree >$d6_array[10][0] and $signDegree <= $d6_array[10][1]){
				$offset = $d6_array[10][2];
			}
			else if($signDegree >$d6_array[11][0] and $signDegree <= $d6_array[11][1]){
				$offset = $d6_array[11][2];
			}
		}
		
		debug("<br>$planet---$sign($signDegree)----offset=$offset");

		// Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD7Chart() {
    global $planet_starlord;
	
	$d7_array = [
					[0.0, 4.286, 0],   //male
					[4.286, 8.572, 1], //male
					[8.572, 12.857, 2], //male
					[12.857, 17.143, 3], //male
					[17.143, 21.429, 4], //male
					[21.429, 25.715, 5], //male
					[25.715, 30.0, 6], //male

					[0.0, 4.286, 6],   //female
					[4.286, 8.572, 7], //female
					[8.572, 12.857, 8], //female
					[12.857, 17.143, 9], //female
					[17.143, 21.429, 10], //female
					[21.429, 25.715, 11], //female
					[25.715, 30.0, 0]  //female
				];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 2 == 1){ //male rasi
			if($signDegree >$d7_array[0][0] and $signDegree <= $d7_array[0][1]){
				$offset = $sign - 1 + $d7_array[0][2];
			}
			else if($signDegree >$d7_array[1][0] and $signDegree <= $d7_array[1][1]){
				$offset = $sign - 1 + $d7_array[1][2];
			}
			else if($signDegree >$d7_array[2][0] and $signDegree <= $d7_array[2][1]){
				$offset = $sign - 1 + $d7_array[2][2];
			}
			else if($signDegree >$d7_array[3][0] and $signDegree <= $d7_array[3][1]){
				$offset = $sign - 1 + $d7_array[3][2];
			}
			else if($signDegree >$d7_array[4][0] and $signDegree <= $d7_array[4][1]){
				$offset = $sign - 1 + $d7_array[4][2];
			}
			else if($signDegree >$d7_array[5][0] and $signDegree <= $d7_array[5][1]){
				$offset = $sign - 1 + $d7_array[5][2];
			}
			else if($signDegree >$d7_array[6][0] and $signDegree <= $d7_array[6][1]){
				$offset = $sign - 1 + $d7_array[6][2];
			}
		}
		else{  //female rasi
			if($signDegree >$d7_array[7][0] and $signDegree <= $d7_array[7][1]){
				$offset = $sign - 1 + $d7_array[7][2];
			}
			else if($signDegree >$d7_array[8][0] and $signDegree <= $d7_array[8][1]){
				$offset = $sign - 1 + $d7_array[8][2];
			}
			else if($signDegree >$d7_array[9][0] and $signDegree <= $d7_array[9][1]){
				$offset = $sign - 1 + $d7_array[9][2];
			}
			else if($signDegree >$d7_array[10][0] and $signDegree <= $d7_array[10][1]){
				$offset = $sign - 1 + $d7_array[10][2];
			}
			else if($signDegree >$d7_array[11][0] and $signDegree <= $d7_array[11][1]){
				$offset = $sign - 1 + $d7_array[11][2];
			}
			else if($signDegree >$d7_array[12][0] and $signDegree <= $d7_array[12][1]){
				$offset = $sign - 1 + $d7_array[12][2];
			}
			else if($signDegree >$d7_array[13][0] and $signDegree <= $d7_array[13][1]){
				$offset = $sign - 1 + $d7_array[13][2];
			}
		}
		
		$offset = $offset % 12 ;
		
		debug("<br>$planet---$sign($signDegree)----offset=$offset");

		// Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateD8Chart() {
    global $planet_starlord;
	
	$d8_array = [
					[0.0, 3.75, 0],
					[3.75, 7.5, 1],
					[7.5, 11.25, 2],
					[11.25, 15.0, 3],
					[15.0, 18.75, 4],
					[18.75, 22.5, 5],
					[22.5, 26.25, 6],
					[26.25, 30.0, 7],

					[0.0, 3.75, 8],
					[3.75, 7.5, 9],
					[7.5, 11.25, 10],
					[11.25, 15.0, 11],
					[15.0, 18.75, 0],
					[18.75, 22.5, 1],
					[22.5, 26.25, 2],
					[26.25, 30.0, 3],
					
					[0.0, 3.75, 4],
					[3.75, 7.5, 5],
					[7.5, 11.25, 6],
					[11.25, 15.0, 7],
					[15.0, 18.75, 8],
					[18.75, 22.5, 9],
					[22.5, 26.25, 10],
					[26.25, 30.0, 11]
				];



    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 3 == 1){ //sara rasi
			if($signDegree >$d8_array[0][0] and $signDegree <= $d8_array[0][1]){
				$offset = $d8_array[0][2];
			}
			else if($signDegree >$d8_array[1][0] and $signDegree <= $d8_array[1][1]){
				$offset = $d8_array[1][2];
			}
			else if($signDegree >$d8_array[2][0] and $signDegree <= $d8_array[2][1]){
				$offset = $d8_array[2][2];
			}
			else if($signDegree >$d8_array[3][0] and $signDegree <= $d8_array[3][1]){
				$offset = $d8_array[3][2];
			}
			else if($signDegree >$d8_array[4][0] and $signDegree <= $d8_array[4][1]){
				$offset = $d8_array[4][2];
			}
			else if($signDegree >$d8_array[5][0] and $signDegree <= $d8_array[5][1]){
				$offset = $d8_array[5][2];
			}
			else if($signDegree >$d8_array[6][0] and $signDegree <= $d8_array[6][1]){
				$offset = $d8_array[6][2];
			}
			else if($signDegree >$d8_array[7][0] and $signDegree <= $d8_array[7][1]){
				$offset = $d8_array[7][2];
			}
		}
		else if($sign % 3 == 2){  //sthira rasi
			if($signDegree >$d8_array[8][0] and $signDegree <= $d8_array[8][1]){
				$offset = $d8_array[8][2];
			}
			else if($signDegree >$d8_array[9][0] and $signDegree <= $d8_array[9][1]){
				$offset = $d8_array[9][2];
			}
			else if($signDegree >$d8_array[10][0] and $signDegree <= $d8_array[10][1]){
				$offset = $d8_array[10][2];
			}
			else if($signDegree >$d8_array[11][0] and $signDegree <= $d8_array[11][1]){
				$offset = $d8_array[11][2];
			}
			else if($signDegree >$d8_array[12][0] and $signDegree <= $d8_array[12][1]){
				$offset = $d8_array[12][2];
			}
			else if($signDegree >$d8_array[13][0] and $signDegree <= $d8_array[13][1]){
				$offset = $d8_array[13][2];
			}
			else if($signDegree >$d8_array[14][0] and $signDegree <= $d8_array[14][1]){
				$offset = $d8_array[14][2];
			}
			else if($signDegree >$d8_array[15][0] and $signDegree <= $d8_array[15][1]){
				$offset = $d8_array[15][2];
			}
		}
		else if($sign % 3 == 0){  //ubaya rasi
			if($signDegree >$d8_array[16][0] and $signDegree <= $d8_array[16][1]){
				$offset = $d8_array[16][2];
			}
			else if($signDegree >$d8_array[17][0] and $signDegree <= $d8_array[17][1]){
				$offset = $d8_array[17][2];
			}
			else if($signDegree >$d8_array[18][0] and $signDegree <= $d8_array[18][1]){
				$offset = $d8_array[18][2];
			}
			else if($signDegree >$d8_array[19][0] and $signDegree <= $d8_array[19][1]){
				$offset = $d8_array[19][2];
			}
			else if($signDegree >$d8_array[20][0] and $signDegree <= $d8_array[20][1]){
				$offset = $d8_array[20][2];
			}
			else if($signDegree >$d8_array[21][0] and $signDegree <= $d8_array[21][1]){
				$offset = $d8_array[21][2];
			}
			else if($signDegree >$d8_array[22][0] and $signDegree <= $d8_array[22][1]){
				$offset = $d8_array[22][2];
			}
			else if($signDegree >$d8_array[23][0] and $signDegree <= $d8_array[23][1]){
				$offset = $d8_array[23][2];
			}
		}
		
		$offset = $offset % 12 ;
		
		debug("<br>$planet---$sign($signDegree)----offset=$offset");

		// Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

function calculateNavamsam()
{
	global $planet_starlord, $g_navamsam_arr;
	
	//Arrnagement of rasi planets in navamsa chart arrangmenet notes
	//Mesham to Katakam - சந் குரு கே - Mesham - padam1, Rishabam - padam2, Mithunam - padam3, Katakam - padham4
	//Simham to Virichigam - செ சனி சுக் - Simham - padam1, Kanni - padam2, Thulaam - padam3, Virichigam - padham4
	//Danusu to Meenam - சூரி ரா புத  - Danusu - padam1, Makaram - padam2, Kumbam - padam3, Meenam - padham4
	
	$rasi_idx1 = [	
					array('சந்1','குரு1','கே1'), //mesham
					array('சந்2','குரு2','கே2'), //rishabam
					array('சந்3','குரு3','கே3'),  //mithunam
					array('சந்4','குரு4','கே4'),  //katakam
					array('செ1','சனி1','சுக்1'),  //simham
					array('செ2','சனி2','சுக்2'),  //kanni
					array('செ3','சனி3','சுக்3'),  //thulaam
					array('செ4','சனி4','சுக்4'),  //virichigam
					array('சூரி1','ரா1','புத1'),  //danusu
					array('சூரி2','ரா2','புத2'),  //makaram
					array('சூரி3','ரா3','புத3'),  //kumbam
					array('சூரி4','ரா4','புத4')  //meenam
				];
				
	$rasi_idx = [	
					'சந்1' => 0,  // mesham
					'குரு1' => 0 , // mesham
					'கே1' => 0 , // mesham
					'சந்2' => 1 , // rishabam
					'குரு2' => 1 , // rishabam
					'கே2' => 1 , // rishabam
					'சந்3'  => 2 , // mithunam
					'குரு3'  => 2 , // mithunam
					'கே3'  => 2 , // mithunam
					'சந்4'  => 3 , // katakam
					'குரு4'  => 3 , // katakam
					'கே4'  => 3 , // katakam
					'செ1'  => 4 , // simham
					'சனி1'  => 4 , // simham
					'சுக்1'  => 4 , // simham
					'செ2'  => 5 , // kanni
					'சனி2'  => 5 , // kanni
					'சுக்2'  => 5 , // kanni
					'செ3'  => 6 , // thulaam
					'சனி3'  => 6 , // thulaam
					'சுக்3'  => 6 , // thulaam
					'செ4'  => 7 , // virichigam
					'சனி4'  => 7 , // virichigam
					'சுக்4'  => 7 , // virichigam
					'சூரி1'  => 8 , // danusu
					'ரா1'  => 8 , // danusu
					'புத1'  => 8 , // danusu
					'சூரி2'  => 9 , // makaram
					'ரா2'  => 9 , // makaram
					'புத2'  => 9 , // makaram
					'சூரி3'  => 10 , // kumbam
					'ரா3'  => 10 , // kumbam
					'புத3'  => 10 , // kumbam
					'சூரி4'  => 11 , // meenam
					'ரா4'  => 11 , // meenam
					'புத4'  => 11  // meenam
				];
	
	$planet_padam = explode('(',$planet_starlord["லக்"][1])[0] . explode(' ',$planet_starlord["லக்"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " <span class='lagna'>லக்</span>";
	
	$planet_padam = explode('(',$planet_starlord["சூரி"][1])[0] . explode(' ',$planet_starlord["சூரி"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " சூரி";
	
	$planet_padam = explode('(',$planet_starlord["சந்"][1])[0] . explode(' ',$planet_starlord["சந்"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " <span class='moon'>சந்</span>";
	
	$planet_padam = explode('(',$planet_starlord["புத"][1])[0] . explode(' ',$planet_starlord["புத"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " புத";
	
	$planet_padam = explode('(',$planet_starlord["சுக்"][1])[0] . explode(' ',$planet_starlord["சுக்"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " சுக்";
	
	$planet_padam = explode('(',$planet_starlord["செ"][1])[0] . explode(' ',$planet_starlord["செ"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " செ";
	
	$planet_padam = explode('(',$planet_starlord["குரு"][1])[0] . explode(' ',$planet_starlord["குரு"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " குரு";
	
	$planet_padam = explode('(',$planet_starlord["சனி"][1])[0] . explode(' ',$planet_starlord["சனி"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " சனி";
	
	$planet_padam = explode('(',$planet_starlord["ரா"][1])[0] . explode(' ',$planet_starlord["ரா"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " ரா";
	
	$planet_padam = explode('(',$planet_starlord["கே"][1])[0] . explode(' ',$planet_starlord["கே"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " கே";
	
	$planet_padam = explode('(',$planet_starlord["மா"][1])[0] . explode(' ',$planet_starlord["மா"][0])[1];
	$g_navamsam_arr[$rasi_idx[$planet_padam]] = $g_navamsam_arr[$rasi_idx[$planet_padam]] . " மா";
}

/*** D10 logic **************************************************************************/
/* Every sign is divided into 
10 parts of 3 deg. 
For odd signs the dasamsa are calcuated from the same sign, counted zodiacally. 
For even signs the dasamsa are calculated from the sign in the 9th house, counted 
zodiacally. 
*****************************************************************************************/
function calculateD10Chart() {
    global $planet_starlord;
	
	/*$d10_array = [
		[0.0, 3.0, 0],
		[3.0, 6.0, 1],
		[6.0, 9.0, 2],
		[9.0, 12.0, 3],
		[12.0, 15.0, 4],
		[15.0, 18.0, 5],
		[18.0, 21.0, 6],
		[21.0, 24.0, 7],
		[24.0, 27.0, 8],
		[27.0, 30.0, 9],

		[0.0, 3.0, 8],
		[3.0, 6.0, 9],
		[6.0, 9.0, 10],
		[9.0, 12.0, 11],
		[12.0, 15.0, 1],
		[15.0, 18.0, 2],
		[18.0, 21.0, 3],
		[21.0, 24.0, 4],
		[24.0, 27.0, 5],
		[27.0, 30.0, 6]
	];*/
	
	$d10_array = [
		[0.0, 3.0, 1],
		[3.0, 6.0, 2],
		[6.0, 9.0, 3],
		[9.0, 12.0, 4],
		[12.0, 15.0, 5],
		[15.0, 18.0, 6],
		[18.0, 21.0, 7],
		[21.0, 24.0, 8],
		[24.0, 27.0, 9],
		[27.0, 30.0, 10]
		];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		/*if($sign % 2 == 1){ //male rasi
			if($signDegree >$d10_array[0][0] and $signDegree <= $d10_array[0][1]){
				$offset = $sign - 1 + $d10_array[0][2];
			}
			else if($signDegree >$d10_array[1][0] and $signDegree <= $d10_array[1][1]){
				$offset = $sign - 1 + $d10_array[1][2];
			}
			else if($signDegree >$d10_array[2][0] and $signDegree <= $d10_array[2][1]){
				$offset = $sign - 1 + $d10_array[2][2];
			}
			else if($signDegree >$d10_array[3][0] and $signDegree <= $d10_array[3][1]){
				$offset = $sign - 1 + $d10_array[3][2];
			}
			else if($signDegree >$d10_array[4][0] and $signDegree <= $d10_array[4][1]){
				$offset = $sign - 1 + $d10_array[4][2];
			}
			else if($signDegree >$d10_array[5][0] and $signDegree <= $d10_array[5][1]){
				$offset = $sign - 1 + $d10_array[5][2];
			}
			else if($signDegree >$d10_array[6][0] and $signDegree <= $d10_array[6][1]){
				$offset = $sign - 1 + $d10_array[6][2];
			}
			else if($signDegree >$d10_array[7][0] and $signDegree <= $d10_array[7][1]){
				$offset = $sign - 1 + $d10_array[7][2];
			}
			else if($signDegree >$d10_array[8][0] and $signDegree <= $d10_array[8][1]){
				$offset = $sign - 1 + $d10_array[8][2];
			}
			else if($signDegree >$d10_array[9][0] and $signDegree <= $d10_array[9][1]){
				$offset = $sign - 1 + $d10_array[9][2];
			}
		}
		else if($sign % 2 == 0){  //female rasi
			if($signDegree >$d10_array[10][0] and $signDegree <= $d10_array[10][1]){
				$offset = $sign - 1 + $d10_array[10][2];
			}
			else if($signDegree >$d10_array[11][0] and $signDegree <= $d10_array[11][1]){
				$offset = $sign - 1 + $d10_array[11][2];
			}
			else if($signDegree >$d10_array[12][0] and $signDegree <= $d10_array[12][1]){
				$offset = $sign - 1 + $d10_array[12][2];
			}
			else if($signDegree >$d10_array[13][0] and $signDegree <= $d10_array[13][1]){
				$offset = $sign - 1 + $d10_array[13][2];
			}
			else if($signDegree >$d10_array[14][0] and $signDegree <= $d10_array[14][1]){
				$offset = $sign - 1 + $d10_array[14][2];
			}
			else if($signDegree >$d10_array[15][0] and $signDegree <= $d10_array[15][1]){
				$offset = $sign - 1 + $d10_array[15][2];
			}
			else if($signDegree >$d10_array[16][0] and $signDegree <= $d10_array[16][1]){
				$offset = $sign - 1 + $d10_array[16][2];
			}
			else if($signDegree >$d10_array[17][0] and $signDegree <= $d10_array[17][1]){
				$offset = $sign - 1 + $d10_array[17][2];
			}
			else if($signDegree >$d10_array[18][0] and $signDegree <= $d10_array[18][1]){
				$offset = $sign - 1 + $d10_array[18][2];
			}
			else if($signDegree >$d10_array[19][0] and $signDegree <= $d10_array[19][1]){
				$offset = $sign - 1 + $d10_array[19][2];
			}
			*/
			
			foreach ($d10_array as $range) {
				if ($signDegree > $range[0] && $signDegree <= $range[1]) {
					if($sign % 2 == 1){ //male rasi
						$offset = (0 + $sign -2 + $range[2]) % 12;
						debug("<br>"."start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
					}
					else{ //female rasi
						$offset = (8 + $sign -2 + $range[2]) % 12;
						debug("<br>"."start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
					}
					break;
				}
			}
		

		// Store the calculated divisional position with degree
		if(mb_strpos($planet,'லக்')!==false)
		{
			$g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
		}
		else{
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
		}
    }

    return $g_divisional_arr;
}

/*
ருத்ராம்சம் - D11 விதிகள்
படி 1: மேஷத்திலிருந்து ஒரு கிரகம் எத்தனையாது இராசியில் உள்ளது என்ற எண்ணிக்கையை குறித்துக் கொள்க.
படி 2: பின்பு மேஷத்திலிருந்து எதிர்ப்புறமாக (ANTI CLOCK WISE) அதே எண்ணிக்கையில் எண்ண வேண்டும். எண்ணிக்கை முடியும் இராசியில் நிற்க.
படி 3: இப்போது அந்த கிரகத்தின் பாகை ருத்ராம்சத்தின் 11 பிரிவில் எத்தனையாவது பங்கு என்று பார்க்கணும்.
படி 4: இப்போது நீங்கள் எதிர்ப்புறமாக எண்ணி நின்ற இராசியில் இருந்து எத்தனையாவது பங்கில் கிரகம் உள்ளதோ. அத்தனை பங்கு எண்ணிக்கை)  
முறை வலப்புறமாக  எண்ணி (CLOCK WISE) அந்த எண்ணிக்கை எந்த இராசியில் முடிகிறதோ, அந்த இராசியே ருத்ராம்சத்தில் கிரகம் அமையும் இராசி
*/

function calculateD11Chart() {
    global $planet_starlord;

    $d11_array = [
        [0.0, 2.72777777778, 0],
        [2.72777777778, 5.45444444444, 1],
        [5.45444444444, 8.18111111111, 2],
        [8.18111111111, 10.9088888889, 3],
        [10.9088888889, 13.5855555556, 4],
        [13.5855555556, 16.3625, 5],
        [16.3625, 19.0902777778, 6],
        [19.0902777778, 21.8180555556, 7],
        [21.8180555556, 24.5458333333, 8],
        [24.5458333333, 27.2736111111, 9],
        [27.2736111111, 30.0, 10],
    ];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        $sign = floor($degree / 30.0) + 1; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 1: Count the number of signs from Aries
        //$count = countHouses(0,$sign);
		$count = $sign;

        // Step 2: Calculate the anti-clockwise offset
        $offset = 12 - $count;
		

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($d11_array as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                $offset = ($offset + $range[2] + 1) % 12;
				debug("<br>"."start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }

        debug("<br>$planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
            if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }
    }

    return $g_divisional_arr;
}

/*
துவாதசாம்சம் - D12 விதிகள்

/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/shodasamsa-chart
Dwadasamsa chart Calculation
In D12 chart, each sign is divided into 12 parts of 2°30´. Each unit is called Dwadasamsa; Dwadasa -12, amsa- unit. There are about 144units for all 12 signs.

1 Dwadasamsa= 30° /12- 2°30´.

The planet’s longitude is noted accurately and the Dwadasamsa unit is mapped. D12 chart is created by positioning the planets based on the amsa. The chart counting will start from the same sign where the planet is placed in D1chart. 

Example:  Lagna is in Scorpio at 7°,  Jupiter in Taurus at 28°47’ and Sun is in Pisces at 21°14’. In D12 chart,

Lagna is in the 3rd amsa, so lagna is placed in Capricorn (3rd sign from Scorpio)

Jupiter is in 12th amsa, it is placed in Aries(12th sign from Taurus)

Sun is in 9th amsa, so it is placed in Scorpio (9th sign from Pisces)
*/

function calculateD12Chart() {
    global $planet_starlord;

    /*$divisional_chart_table = [
		1 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
		2 => [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1],
		3 => [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2],
		4 => [4, 5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3],
		5 => [5, 6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4],
		6 => [6, 7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5],
		7 => [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6],
		8 => [8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7],
		9 => [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8],
		10 => [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9],
		11 => [11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
		12 => [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
	];*/

	$degree_spans = [
		[00.00,02.30,1],
		[02.30,05.00,2],
		[05.00,07.30,3],
		[07.30,10.00,4],
		[10.00,12.30,5],
		[12.30,15.00,6],
		[15.00,17.00,7],
		[17.00,20.00,8],
		[20.00,22.30,9],
		[22.30,25.00,10],
		[25.00,27.30,11],
		[27.30,30.00,12]
	];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
        //$sign = floor($degree / 30.0) + 1; // Determine the sign
		$sign = floor($degree / 30.0) ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($degree_spans as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                $offset = (($sign + $range[2]) % 12) - 1;
				debug("<br>"."sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }
		
		if($offset==-1) $offset=11;

        debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
            if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }
    }

    return $g_divisional_arr;
}

/*
ஷோடசாம்ச - D16 விதிகள்

/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/shodasamsa-chart

Shodasamsa Chart Calculation
Each sign is divided into 16 pada of 1°52’30” each. This pada or amsa is called Shodasamsa. Lord Brahma, Lord Vishnu, Lord Shiva, Surya Deva are the deities of the Shodasamsas. This is the order followed for odd signs and it gets reversed in case of even signs; recurs 4 times in each sign for 16 amsas.

The calculation for the chart is based on the nature of the sign a planet is positioned in D1 chart.

If the planet is in a movable sign (Aries, Cancer, Libra and Capricorn) counting starts from Aries
If the planet is in a fixed sign (Taurus, Leo, Scorpio, Aquarius) counting begins from Leo 
and if the planet is in dual sign (Gemini, Virgo, Sagittarius, Pisces) counting starts from Sagittarius.

*/

function calculateD16Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 1.875, 1],
		[1.875, 3.750, 2],
		[3.750, 5.625, 3],
		[5.625, 7.500, 4],
		[7.500, 9.375, 5],
		[9.375, 11.250, 6],
		[11.250, 13.125, 7],
		[13.125, 15.000, 8],
		[15.000, 16.875, 9],
		[16.875, 18.750, 10],
		[18.750, 20.625, 11],
		[20.625, 22.500, 12],
		[22.500, 24.375, 13],
		[24.375, 26.250, 14],
		[26.250, 28.125, 15],
		[28.125, 30.000, 16]
	];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($degree_spans as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                if($sign == 0){
					$offset = ((0 +  $range[2]) % 12) - 1;
				}
				else if($sign == 1){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 2){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 3){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 4){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 5){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 6){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 7){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 8){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 9){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 10){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 11){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				
				debug( "<br>"."sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }
		
		if($offset==-1) $offset=11;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/*
விம்சம்சா - D20 விதிகள்

/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/vimsamsa-chart

Vimsamsa chart Calculation
Every sign of 30º is divided into 20 pada of 1º30΄ each. Each pada is called Vimsamsa. There are about 240 Vimsamsa for all the whole of zodiac. 

D20 chart is calculated based on the nature of the sign a planet is located in the Natal chart,   

For a planet placed in a movable sign, counting starts from Aries, 
If a planet is in fixed sign, the counting starts from Sagittarius and
If it is in dual sign, the counting starts from Leo.

*/

function calculateD20Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 1.50, 1],
		[1.50, 3.00, 2],
		[3.00, 4.50, 3],
		[4.50, 6.00, 4],
		[6.00, 7.50, 5],
		[7.50, 9.00, 6],
		[9.00, 10.50, 7],
		[10.50, 12.00, 8],
		[12.00, 13.50, 9],
		[13.50, 15.00, 10],
		[15.00, 16.50, 11],
		[16.50, 18.00, 12],
		[18.00, 19.50, 13],
		[19.50, 21.00, 14],
		[21.00, 22.50, 15],
		[22.50, 24.00, 16],
		[24.00, 25.50, 17],
		[25.50, 27.00, 18],
		[27.00, 28.50, 19],
		[28.50, 30.00, 20]
	];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($degree_spans as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                if($sign == 0){
					$offset = ((0 +  $range[2]) % 12) - 1;
				}
				else if($sign == 1){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 2){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 3){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 4){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 5){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 6){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 7){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 8){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				else if($sign == 9){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 10){
					$offset = ((8 +  $range[2]) % 12)- 1;
				}
				else if($sign == 11){
					$offset = ((4 +  $range[2]) % 12)- 1;
				}
				
				debug( "<br>planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }
		
		if($offset==-1) $offset=11;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/*
சதுர்விம்ஷம்சா  - D24 விதிகள்

/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/chaturvimsamsa-chart

Calculation
This divisional chart divides each sign into 24 amsas called Siddhamsa or Chaturvimshamsa. 
One siddhamsa is 1 º 15’. The whole zodiac has a total of about 288 Siddhamsa. 
To create a D24 chart, the longitude of the planets in the natal chart are accurately mapped with 
the corresponding Siddhamsa division and placed accordingly. Based on the nature of the sign, 
the counting begins at Leo or Cancer and goes through the complete zodiac order in 2 cycles 
to cover 24 amsas.

*/

function calculateD24Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 1.25, 1],
		[1.25, 2.50, 2],
		[2.50, 3.75, 3],
		[3.75, 5.00, 4],
		[5.00, 6.25, 5],
		[6.25, 7.50, 6],
		[7.50, 8.75, 7],
		[8.75, 10.00, 8],
		[10.00, 11.25, 9],
		[11.25, 12.50, 10],
		[12.50, 13.75, 11],
		[13.75, 15.00, 12],
		[15.00, 16.25, 13],
		[16.25, 17.50, 14],
		[17.50, 18.75, 15],
		[18.75, 20.00, 16],
		[20.00, 21.25, 17],
		[21.25, 22.50, 18],
		[22.50, 23.75, 19],
		[23.75, 25.00, 20],
		[25.00, 26.25, 21],
		[26.25, 27.50, 22],
		[27.50, 28.75, 23],
		[28.75, 30.00, 24]
	];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($degree_spans as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                if(($sign + 1) % 2 == 1){
					$offset = ((4 +  $range[2]) % 12) - 1;
				}
				else{
					$offset = ((3 +  $range[2]) % 12)- 1;
				}
				
				debug( "<br>planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }
		
		if($offset==-1) $offset=11;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/*
சப்தவிம்சாம்சம்  - D27 விதிகள்

/****** Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/saptavimsamsa-chart

Calculation
This divisional chart divides each sign into 22 amsas called Nakshatramsa or Bhamsa. The meaning of the Sanskrit word Saptavimsha is twenty-seven. 
One Bhamsa is 1 º 6’ 40”. The whole of the zodiac has a total of about 324 Bhamsas. 
1Bhamsa = 30 º /27 = 1 º 6’40”
To create a D27 chart, the longitude of the planets in the natal chart are accurately mapped with the corresponding Bhamsa division and placed accordingly.
Any change in the time seconds might also result in a completely different chart. This is one of the reasons this chart is not much utilized. 
Based on the nature of the sign, the counting begins. 

Fiery signs, Aries, Leo, and Sagittarius: Counting starts from Aries.
Earthly sign, Taurus, Virgo, and Capricorn - Counting starts from Cancer.
Airy sign, Gemini, Libra, and Aquarius -Counting starts from Libra.
Watery signs, Cancer, Scorpio, and Pisces - Counting starts from Capricorn.

*/

function calculateD27Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 1.11, 1],
		[1.11, 2.22, 2],
		[2.22, 3.33, 3],
		[3.33, 4.44, 4],
		[4.44, 5.55, 5],
		[5.55, 6.66, 6],
		[6.66, 7.77, 7],
		[7.77, 8.88, 8],
		[8.88, 10.00, 9],
		[10.00, 11.11, 10],
		[11.11, 12.22, 11],
		[12.22, 13.33, 12],
		[13.33, 14.44, 13],
		[14.44, 15.55, 14],
		[15.55, 16.66, 15],
		[16.66, 17.77, 16],
		[17.77, 18.88, 17],
		[18.88, 20.00, 18],
		[20.00, 21.11, 19],
		[21.11, 22.22, 20],
		[22.22, 23.33, 21],
		[23.33, 24.44, 22],
		[24.44, 25.55, 23],
		[25.55, 26.66, 24],
		[26.66, 27.77, 25],
		[27.77, 28.88, 26],
		[28.88, 30.00, 27]
	];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

        // Step 3: Determine the Rudramsa (D11) divisional chart position
        foreach ($degree_spans as $range) {
            if ($signDegree > $range[0] && $signDegree <= $range[1]) {
                if($sign == 0){
					$offset = ((0 +  $range[2]) % 12) - 1;
				}
				else if($sign == 1){
					$offset = ((3 +  $range[2]) % 12)- 1;
				}
				else if($sign == 2){
					$offset = ((6 +  $range[2]) % 12)- 1;
				}
				else if($sign == 3){
					$offset = ((9 +  $range[2]) % 12)- 1;
				}
				else if($sign == 4){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 5){
					$offset = ((3 +  $range[2]) % 12)- 1;
				}
				else if($sign == 6){
					$offset = ((6 +  $range[2]) % 12)- 1;
				}
				else if($sign == 7){
					$offset = ((9 +  $range[2]) % 12)- 1;
				}
				else if($sign == 8){
					$offset = ((0 +  $range[2]) % 12)- 1;
				}
				else if($sign == 9){
					$offset = ((3 +  $range[2]) % 12)- 1;
				}
				else if($sign == 10){
					$offset = ((6 +  $range[2]) % 12)- 1;
				}
				else if($sign == 11){
					$offset = ((9 +  $range[2]) % 12)- 1;
				}
				
				debug( "<br>planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
                break;
            }
        }
		
		if($offset==-1) $offset=11;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/****** D30 Trimsamsa Divisional chart *******/
/* Reference https://www.youtube.com/watch?v=Lcm9WaPl5o4

The D30 (Trimshamsha) calculation logic is as follows:

Divide the zodiac sign degrees (0°–30°) into Visham (odd) and Sam (even) categories.
If the sign is odd (1, 3, 5, 7, 9, 11), use Visham spans to determine the house offset.
If the sign is even (2, 4, 6, 8, 10, 12), use Sam spans to determine the house offset.
Combine the sign number and the span-based offset to position the planet in the D30 chart.

*/

function calculateD30Chart() {
    global $planet_starlord;

	$degree_spans_odd = [
		[0.00, 5.00, 1],   // Visham 0°–5° or Sam 0°–5°
		[5.00, 10.00, 11], // Visham 6°–10°
		[10.00, 18.00, 9], // Visham 11°–18°
		[18.00, 25.00, 3], // Visham 19°–25°
		[25.00, 30.00, 7], // Visham 26°–30°
	];
	
	$degree_spans_even = [
		[0.00, 5.00, 2],   // Sam 0°–5°
		[5.00, 12.00, 6],  // Sam 6°–12°
		[12.00, 20.00, 12],// Sam 13°–20°
		[20.00, 25.00, 10],// Sam 21°–25°
		[25.00, 30.00, 8], // Sam 26°–30°
	];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) + 1 ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		if($sign % 2 == 1) //odd
		{
			foreach ($degree_spans_odd as $range) {
				if ($signDegree > $range[0] && $signDegree <= $range[1]) {
					$offset = $range[2];
					
					debug( "<br>ODD - planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
					break;
				}
			}
		}
		else{
			foreach ($degree_spans_even as $range) {
				if ($signDegree > $range[0] && $signDegree <= $range[1]) {
					$offset = $range[2];
					
					debug( "<br>EVEN - planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
					break;
				}
			}
		}
		
		$offset = $offset - 1;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/****** D40 Khavedamsa Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/khavedamsa-chart

Calculation
Each sign of 30º is divided into 40 parts of 0º45΄ each called Khavedamsa or Chatvarimshamsha.   

D40 is created based on the nature of the sign a planet is placed in a natal chart. 

For odd signs, Aries, Gemini, Leo, Libra, Sagittarius & Aquarius- counting begins from Aries  
even signs, Taurus, Cancer, Virgo, Scorpio & Capricorn & Pisces- counting begins from Libra. 
To locate the correct house to place the planet.

Check which Khavedamsa the planet falls in. If it is between 13-24, subtract 12 from the Khavedamsa, and it will denote the sign where the planet will go. 
21st Khavedamsa-(21-12=9) count 9houses from the sign. 

If it’s between 25-36, subtract 24 from the Khavedamsa, and it will denote the sign where the planet will go. 
30th Khavedamsa= (30-24= 6) count 6 houses from the sign.

*/

function calculateD40Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 0.75, 1],
		[0.75, 1.50, 2],
		[1.50, 2.25, 3],
		[2.25, 3.00, 4],
		[3.00, 3.75, 5],
		[3.75, 4.50, 6],
		[4.50, 5.25, 7],
		[5.25, 6.00, 8],
		[6.00, 6.75, 9],
		[6.75, 7.50, 10],
		[7.50, 8.25, 11],
		[8.25, 9.00, 12],
		[9.00, 9.75, 13],
		[9.75, 10.50, 14],
		[10.50, 11.25, 15],
		[11.25, 12.00, 16],
		[12.00, 12.75, 17],
		[12.75, 13.50, 18],
		[13.50, 14.25, 19],
		[14.25, 15.00, 20],
		[15.00, 15.75, 21],
		[15.75, 16.50, 22],
		[16.50, 17.25, 23],
		[17.25, 18.00, 24],
		[18.00, 18.75, 25],
		[18.75, 19.50, 26],
		[19.50, 20.25, 27],
		[20.25, 21.00, 28],
		[21.00, 21.75, 29],
		[21.75, 22.50, 30],
		[22.50, 23.25, 31],
		[23.25, 24.00, 32],
		[24.00, 24.75, 33],
		[24.75, 25.50, 34],
		[25.50, 26.25, 35],
		[26.25, 27.00, 36],
		[27.00, 27.75, 37],
		[27.75, 28.50, 38],
		[28.50, 29.25, 39],
		[29.25, 30.00, 40]
	];


    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) + 1 ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		foreach ($degree_spans as $range) {
			if ($signDegree > $range[0] && $signDegree <= $range[1]) {
				
				if($sign % 2 == 1){ //odd
					$offset = 0 + $range[2];
				}
				else{ //even
					$offset = 6 + $range[2];
				}
				
				debug("<br>planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
				break;
			}
		}
		
		$offset = ($offset - 1) % 12;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/****** D45 Akshavedamsa Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/akshavedamsa-chart

Calculation
Each sign is divided into 45 parts of 0 degrees 40’ each called Akshavedamsa. The whole zodiac system has about 540 Akshavedamsas.   

D45 chart is created based on the nature of the sign a planet is placed in the natal chart,   

For movable signs, Aries, Cancer, Libra, Capricorn- count from Aries  
for fixed signs, Taurus, Leo, Scorpio, and Aquarius- count from Leo 
for dual signs, Gemini, Virgo, Sagittarius, and Pisces- count from Sagittarius. 
To locate the correct house to place the planet,

Check which Akshavedamsa the planet falls in. If it is between 13-24, subtract 12 from the number, and it will denote the sign where the planet will go. 21st Akshavedamsa -(21-12=9) count 9 houses from the sign. 

If it’s between 25-36, subtract 24 and 37-45, subtract 36, and it will denote the sign where the planet should go. 
30th Akshavedamsa = (30-24= 6) count 6 houses from the sign.

*/

function calculateD45Chart() {
    global $planet_starlord;

	$degree_spans = [
		[0.00, 0.67, 1],    [0.67, 1.33, 2],   [1.33, 2.00, 3],   [2.00, 2.67, 4],
		[2.67, 3.33, 5],    [3.33, 4.00, 6],   [4.00, 4.67, 7],   [4.67, 5.33, 8],
		[5.33, 6.00, 9],    [6.00, 6.67, 10],  [6.67, 7.33, 11],  [7.33, 8.00, 12],
		[8.00, 8.67, 13],   [8.67, 9.33, 14],  [9.33, 10.00, 15], [10.00, 10.67, 16],
		[10.67, 11.33, 17], [11.33, 12.00, 18],[12.00, 12.67, 19],[12.67, 13.33, 20],
		[13.33, 14.00, 21], [14.00, 14.67, 22],[14.67, 15.33, 23],[15.33, 16.00, 24],
		[16.00, 16.67, 25], [16.67, 17.33, 26],[17.33, 18.00, 27],[18.00, 18.67, 28],
		[18.67, 19.33, 29], [19.33, 20.00, 30],[20.00, 20.67, 31],[20.67, 21.33, 32],
		[21.33, 22.00, 33], [22.00, 22.67, 34],[22.67, 23.33, 35],[23.33, 24.00, 36],
		[24.00, 24.67, 37], [24.67, 25.33, 38],[25.33, 26.00, 39],[26.00, 26.67, 40],
		[26.67, 27.33, 41], [27.33, 28.00, 42],[28.00, 28.67, 43],[28.67, 29.33, 44],
		[29.33, 30.00, 45]
	];

    // Reset the divisional array
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate divisional positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Get the degree from the third element
		$sign = floor($degree / 30.0) + 1 ; // Determine the sign
        $signDegree = fmod($degree, 30.0); // Degree within the sign, ensuring decimals are included

		foreach ($degree_spans as $range) {
			if ($signDegree > $range[0] && $signDegree <= $range[1]) {
				
				if($sign == 1 or $sign == 4 or $sign == 7 or $sign == 10){ //movable sign
					$offset = 1 + $range[2] - 1;
				}
				else if($sign == 2 or $sign == 5 or $sign == 8 or $sign == 11){ //fixed sign
					$offset = 5 + $range[2] - 1;
				}
				else{ //dual sign
					$offset = 9 + $range[2] - 1;
				}
				
				debug("<br>planet=$planet" . " planetDegree=$signDegree" ." sign=$sign  start=".$range[0]." end=".$range[1]." offset=$offset   rasi=".$range[2]);
				break;
			}
		}
		
		$offset = ($offset - 1) % 12;
		
		debug("<br>range=".$range[2]." sign=".$sign." $planet---$sign($signDegree)----offset=$offset");

        // Store the calculated divisional position with degree
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
			if(isRetro($planet)[1] == -1)
				$g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>" ;
			else
				$g_divisional_arr[$offset] .= " $planet" ;
        }	
    }
	
    return $g_divisional_arr;
}

/****** D60 Sashtiamsa Divisional chart *******/
/* Reference https://www.cosmicsquares.com/free-horoscope/shastiamsa-chart

Calculation
Sashtiamsa is 1/60th division of a sign; each Sashtiamsa is of 0degrees, 30’ minutes. Each Sashtiamsa is assigned to a deity. 
Based on the nature of the deity/demigod assigned, there are Shuba Sashtiamsa and Kroora Sashtiamsas. 
Planets in Shuba Sashtiamsa give good results, and planets in kroora Sashtiamsa give malefic results. 
Madhya Sashtiamsas provide moderate results. The nomenclature of the deities is devised by Rishi Parasara.
To create a D60 chart, see the longitude of a planet in a sign, consider only the degree, multiply it by 2. 
Divide the resultant number by 12 and add 1 to the remainder. It will denote the sign where the planet will be placed in the D60 chart. 
The Lord of the resultant sign is the planet’s Sashtiamsa dispositor. Here is a table on Sashtiamsa, deities, and degrees,

*/

function calculateD60Chart() {
    global $planet_starlord;

    // Reset the divisional array for 12 signs
    $g_divisional_arr = array_fill(0, 12, '');

    // Loop through each planet to calculate D60 positions
    foreach ($planet_starlord as $planet => $details) {
		// Skip upagrahas that start with #
		if (strpos($planet, '#') === 0) {
			continue;
		}
		
        $degree = $details[2]; // Planet's degree in full
        $sign = floor($degree / 30.0) + 1; // Determine the sign (1 to 12)
        $signDegree = fmod($degree, 30.0); // Degree within the sign

        // Step 1: Multiply degree by 2, divide by 12, and add 1
        $offset = floor(fmod(($signDegree * 2), 12)) + 1;
		
		$offset = targetHouse($sign,$offset)- 1;
		
		if($offset < 0)
		{
			$offset = 11;
		}

        debug("<br>Planet=$planet Degree=$signDegree Sign=$sign Offset=$offset");

        // Store calculated position in divisional array
        if (mb_strpos($planet, 'லக்') !== false) {
            $g_divisional_arr[$offset] .= "<span class='lagna'>$planet</span>";
        } else {
            if (isRetro($planet)[1] == -1) {
                $g_divisional_arr[$offset] .= " <span class='retro'>$planet</span>";
            } else {
                $g_divisional_arr[$offset] .= " $planet";
            }
        }
    }
    return $g_divisional_arr;
}


//returns if the plant is retro or not
//returns array ["(வ)",-1] for retro and ["",1] for non retro planet
function isRetro($planet){
	global $sw_dir;
	
	$direction = 0;
	
	switch($planet)
	{
		case "செ":
			$direction = $sw_dir[4];
			break;
		case "சுக்":
			$direction = $sw_dir[3];
			break;
		case "புத":
			$direction = $sw_dir[2];
			break;
		case "குரு":
			$direction = $sw_dir[5];
			break;
		case "சனி":
			$direction = $sw_dir[6];
			break;
		default:
			break;
	}
	
	if($direction < 0){
		return ["(வ)",-1];
	}
	else{
		return ["",1];
	}
}

function displayVarga($data,$division)
{	
	global $divisional_chartname_arr;
	
	$chartname = $divisional_chartname_arr[$division][0];
	$chartdetail = $divisional_chartname_arr[$division][1];
	
	
	echo "<center>";
	echo "<P><B>".translate_text($chartdetail)."</B></p>";

	// prepare translated cell values so they can be interpolated inside the heredoc
	$cell11 = map_display_text($data[11]);
	$cell0  = map_display_text($data[0]);
	$cell1  = map_display_text($data[1]);
	$cell2  = map_display_text($data[2]);
	$cell10 = map_display_text($data[10]);
	$cell3  = map_display_text($data[3]);
	$cell9  = map_display_text($data[9]);
	$cell4  = map_display_text($data[4]);
	$cell8  = map_display_text($data[8]);
	$cell7  = map_display_text($data[7]);
	$cell6  = map_display_text($data[6]);
	$cell5  = map_display_text($data[5]);
	$chartname_trans = map_display_text($chartname);

	echo <<<EOL
		<div class="divTable blueTable" id="D$division" style='display:table'>
				
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
						<div class="divTableCell">$cell11</div>
						<div class="divTableCell">$cell0</div>
						<div class="divTableCell">$cell1</div>
						<div class="divTableCell">$cell2</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$cell10</div>
						<div class="divTableCenterCell" style='font-size:10px'>D$division</div>
						<div class="divTableCenterCell"></div>
						<div class="divTableCell">$cell3</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$cell9</div>
						<div class="divTableCenterCell"></div>
						<div class="divTableCenterCell" style='font-size:1.7vw'>$chartname_trans</div>
						<div class="divTableCell">$cell4</div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$cell8</div>
						<div class="divTableCell">$cell7</div>
						<div class="divTableCell">$cell6</div>
						<div class="divTableCell">$cell5</div>
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

	echo "<center>";
}

function getAsthangam(){
	global $g_asthangam_arr, $planet_starlord;
	
	$deg = -1.0;
	
	$sun_full_degree = $planet_starlord["சூரி"][2];
	
	// Loop through each planet to calculate D60 positions
    foreach ($planet_starlord as $planet => $details) {
		if(strpos("சூரி",$planet)!==false) continue; // not required to check on sun
		
        $degree = $details[2]; // Planet's degree in full

        $diff = abs($sun_full_degree - $degree);
		
		switch($planet){
			case "செ":
				$deg = 17.0;
				if($diff <= $deg){
					array_push($g_asthangam_arr,$planet);
				}
				break;
			case "குரு":
				$deg = 11.0;
				if($diff <= $deg){
					array_push($g_asthangam_arr,$planet);
				}
				break;
			case "சனி":
				$deg = 15.0;
				if($diff <= $deg){
					array_push($g_asthangam_arr,$planet);
				}
				break;
			case "புத":
				$deg = 14.0;
				if($diff <= $deg){
					array_push($g_asthangam_arr,$planet);
				}
				break;
			case "சுக்":
				$deg = 14.0;
				if($diff <= $deg){
					array_push($g_asthangam_arr,$planet);
				}
				break;
			default:
				break;
		}
    }
	
	if(empty($g_asthangam_arr)){
		$g_asthangam_arr = ['இல்லை'];
	}
	
    return $g_asthangam_arr;
}

/********Astavarga calculation /
// https://www.vedicastrologer.org/articles/vedic_astro_textbook.pdf
/********/
function calculateAshtavarga($planet) {
    global $planet_starlord, $lagna_house;

    // Define favorable house mappings for all planets
    $favorable_houses_map = array(
		"சூரி" => array(
			"சூரி" => array(1, 2, 4, 7, 8, 9, 10, 11),
			"சந்" => array(3, 6, 10, 11),
			"செ" => array(1, 2, 4, 7, 8, 9, 10, 11),
			"புத" => array(3, 5, 6, 9, 10, 11, 12),
			"குரு" => array(5, 6, 9, 11),
			"சுக்" => array(6, 7, 12),
			"சனி" => array(1, 2, 4, 7, 8, 9, 10, 11),
			"லக்" => array(3, 4, 6, 10, 11, 12)
		),
		"சந்" => array(
			"சூரி" => array(3, 6, 7, 8, 10, 11),
			"சந்" => array(1, 3, 6, 7, 9, 10, 11),  //1st version
			//"சந்" => array(1, 3, 6, 7, 10, 11),   //2nd version
			"செ" => array(2, 3, 5, 6, 10, 11),       //1st version
			//"செ" => array(2, 3, 5, 6, 9, 10, 11),  //2nd version
			"புத" => array(1, 3, 4, 5, 7, 8, 10, 11),
			"குரு" => array(1, 2, 4, 7, 8, 10, 11),    //1st version
			//"குரு" => array(1, 4, 7, 8, 10, 11, 12), //2nd version
			"சுக்" => array(3, 4, 5, 7, 9, 10, 11),
			"சனி" => array(3, 5, 6, 11),
			"லக்" => array(3, 6, 10, 11)
		),
		"செ" => array(
			"சூரி" => array(3, 5, 6, 10, 11),
			"சந்" => array(3, 6, 11),
			"செ" => array(1, 2, 4, 7, 8, 10, 11),
			"புத" => array(3, 5, 6, 11),
			"குரு" => array(6, 10, 11, 12),
			"சுக்" => array(6, 8, 11, 12),
			"சனி" => array(1, 4, 7, 8, 9, 10, 11),
			"லக்" => array(1, 3, 6, 10, 11)
		),
		"புத" => array(
			"சூரி" => array(5, 6, 9, 11, 12),
			"சந்" => array(2, 4, 6, 8, 10, 11),
			"செ" => array(1, 2, 4, 7, 8, 9, 10, 11),
			"புத" => array(1, 3, 5, 6, 9, 10, 11, 12),
			"குரு" => array(6, 8, 11, 12),
			"சுக்" => array(1, 2, 3, 4, 5, 8, 9, 11),
			"சனி" => array(1, 2, 4, 7, 8, 9, 10, 11),
			"லக்" => array(1, 2, 4, 6, 8, 10, 11)
		),
		"குரு" => array(
			"சூரி" => array(1, 2, 3, 4, 7, 8, 9, 10, 11),
			"சந்" => array(2, 5, 7, 9, 11),
			"செ" => array(1, 2, 4, 7, 8, 10, 11),
			"புத" => array(1, 2, 4, 5, 6, 9, 10, 11),
			"குரு" => array(1, 2, 3, 4, 7, 8, 10, 11),
			"சுக்" => array(2, 5, 6, 9, 10, 11),
			"சனி" => array(3, 5, 6, 12),
			"லக்" => array(1, 2, 4, 5, 6, 7, 9, 10, 11)
		),
		"சுக்" => array(
			"சூரி" => array(8, 11, 12),
			"சந்" => array(1, 2, 3, 4, 5, 8, 9, 11, 12),
			"செ" => array(3, 4, 6, 9, 11, 12),    //1st version
			//"செ" => array(3, 5, 6, 9, 11, 12),  //2nd version
			"புத" => array(3, 5, 6, 9, 11),
			"குரு" => array(5, 8, 9, 10, 11),
			"சுக்" => array(1, 2, 3, 4, 5, 8, 9, 10, 11),
			"சனி" => array(3, 4, 5, 8, 9, 10, 11),
			"லக்" => array(1, 2, 3, 4, 5, 8, 9, 11)
		),
		"சனி" => array(
			"சூரி" => array(1, 2, 4, 7, 8, 10, 11),
			"சந்" => array(3, 6, 11),
			"செ" => array(3, 5, 6, 10, 11, 12),
			"புத" => array(6, 8, 9, 10, 11, 12),
			"குரு" => array(5, 6, 11, 12),
			"சுக்" => array(6, 11, 12),
			"சனி" => array(3, 5, 6, 11),
			"லக்" => array(1, 3, 4, 6, 10, 11)
		),
		"லக்" => array(
			"சூரி" => array(3, 4, 6, 10, 11, 12),
			"சந்" => array(3, 6, 10, 11, 12),
			"செ" => array(1, 3, 6, 10, 11),
			"புத" => array(1, 2, 4, 6, 8, 10, 11),
			"குரு" => array(1, 2, 4, 5, 6, 7, 9, 10, 11),
			"சுக்" => array(1, 2, 3, 4, 5, 8, 9),
			"சனி" => array(1, 3, 4, 6, 10, 11),
			"லக்" => array(3, 6, 10, 11)
		)
	);


    // Ensure the planet exists in the mapping
    if (!isset($favorable_houses_map[$planet])) {
        return array_fill(0, 12, null); // Return an empty array if the planet is not found
    }

    // Get the favorable houses for the given planet
    $favorable_houses = $favorable_houses_map[$planet];

    // Initialize Ashtavarga_arr with 12 positions
    //$Ashtavarga_arr = array_fill(0, 12, null);
	$Ashtavarga_arr = array_fill(0, 12, array());

    // Populate Ashtavarga_arr dynamically for all sub-keys in $favorable_houses
    foreach ($favorable_houses as $key => $houses) {
        foreach ($houses as $favorable_house) {
            // Calculate the index based on $lagna_house
            //$index = targetHouse($planet_starlord[$key][5], $favorable_house) -1 ;
			$index = targetHouse($favorable_house-1, $planet_starlord[$key][5]) ;
            if ($index < 0){
				debug("<br>index is $index, making it 11");
				$index = 11;
			}

            // If the position is empty, assign the key (planet name); otherwise, append
            /*if (is_null($Ashtavarga_arr[$index])) {
                $Ashtavarga_arr[$index] = $key;
            } else {
                $Ashtavarga_arr[$index] .= "," . $key;
            }*/
			
			debug("<br>index=$index----planet=$key------house=".$planet_starlord[$key][5]);
			
			array_push($Ashtavarga_arr[$index],$key);
        }
    }

    // Return the filled array
    return $Ashtavarga_arr;
}

function displayAshtavargam($id,$planet,$data,$type=1)
{	
	global $g_astavargam_total_arr, $lagna_house;
	
	$total = 0;
	
	echo "<center>";

	
	if($type==1){
		$chartname = $planet;
		$chartdetail = "பின்னாஷ்டக வர்கம் (" . $chartname . ")";
	
		$h0 = count($data[0]);
		$h1 = count($data[1]);
		$h2 = count($data[2]);
		$h3 = count($data[3]);
		$h4 = count($data[4]);
		$h5 = count($data[5]);
		$h6 = count($data[6]);
		$h7 = count($data[7]);
		$h8 = count($data[8]);
		$h9 = count($data[9]);
		$h10 = count($data[10]);
		$h11 = count($data[11]);
		
		$total = $h0+$h1+$h2+$h3+$h4+$h5+$h6+$h7+$h8+$h9+$h10+$h11;
		
		if(strpos($planet,"லக்")===false){
			$g_astavargam_total_arr[0] += $h0;
			$g_astavargam_total_arr[1] += $h1;
			$g_astavargam_total_arr[2] += $h2;
			$g_astavargam_total_arr[3] += $h3;
			$g_astavargam_total_arr[4] += $h4;
			$g_astavargam_total_arr[5] += $h5;
			$g_astavargam_total_arr[6] += $h6;
			$g_astavargam_total_arr[7] += $h7;
			$g_astavargam_total_arr[8] += $h8;
			$g_astavargam_total_arr[9] += $h9;
			$g_astavargam_total_arr[10] += $h10;
			$g_astavargam_total_arr[11] += $h11;
		}
	}
	else if($type==2) { //$type==2
	
		$chartname = $planet;
		$chartdetail = "பின்னாஷ்டக வர்கம் (" . $chartname . ")";
	
		$h0 = join(', ',$data[0]);
		$h1 = join(', ',$data[1]);
		$h2 = join(', ',$data[2]);
		$h3 = join(', ',$data[3]);
		$h4 = join(', ',$data[4]);
		$h5 = join(', ',$data[5]);
		$h6 = join(', ',$data[6]);
		$h7 = join(', ',$data[7]);
		$h8 = join(', ',$data[8]);
		$h9 = join(', ',$data[9]);
		$h10 = join(', ',$data[10]);
		$h11 = join(', ',$data[11]);

		$total = "";
	}
	else if($type == 3){
		$chartname = "";
		$chartdetail = "சர்வாஷ்டக வர்கம்";
		
		$h0 = $data[0];
		$h1 = $data[1];
		$h2 = $data[2];
		$h3 = $data[3];
		$h4 = $data[4];
		$h5 = $data[5];
		$h6 = $data[6];
		$h7 = $data[7];
		$h8 = $data[8];
		$h9 = $data[9];
		$h10 = $data[10];
		$h11 = $data[11];

		$total = "";
	}
	
	// Add "Asc" to the appropriate house based on $lagna_house
	${'h' . ($lagna_house - 1)} .= ' <span class="lagna">லக்</span>';

	// prepare translated display values for heredoc interpolation
	$th11 = map_display_text($h11);
	$th0  = map_display_text($h0);
	$th1  = map_display_text($h1);
	$th2  = map_display_text($h2);
	$th10 = map_display_text($h10);
	$th3  = map_display_text($h3);
	$th9  = map_display_text($h9);
	$th4  = map_display_text($h4);
	$th8  = map_display_text($h8);
	$th7  = map_display_text($h7);
	$th6  = map_display_text($h6);
	$th5  = map_display_text($h5);
	$chartname_trans = translate_text($chartname);
	$chartdetail_trans = translate_text($chartdetail);

	echo "<P><B>".$chartdetail_trans."</B></p>";
	
	echo <<<EOL
		<div class="divTable blueTable" id="$planet" style='display:table'>
				
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
					<div class="divTableCell">$th11</div>
					<div class="divTableCell">$th0</div>
					<div class="divTableCell">$th1</div>
					<div class="divTableCell">$th2</div>
					<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$th10</div>
					<div class="divTableCenterCell" style='font-size:10px'>$chartname_trans</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$th3</div>
					<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$th9</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'>$total</div>
					<div class="divTableCell">$th4</div>
					<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$th8</div>
					<div class="divTableCell">$th7</div>
					<div class="divTableCell">$th6</div>
					<div class="divTableCell">$th5</div>
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

	echo "<center>";
}

function displayPlanetTharaPalan()
{
	global $planetDeg,$sw_planet, $planet_starlord, $planetDeg_default, $g_nak;
	
	//$planet_starlord = array();

	echo "<center><div class='divTable blueTable' id='starlordTharaiHead' style='height:auto;display:none'>"; 
		echo "<br>";
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
				echo "<H6 style='font-weight:bold;color:black'>கிரக நக்ஷ் சார தாரா பலன்</H6>";
			echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordTharaiBody' style='height:auto;display:none'>"; 
	echo "<div class='divTableRow'>";
		echo "<div class='divTableCell'>" . __('graha') . "</div>";
		echo "<div class='divTableCell'>" . __('naksh_pad') . "</div>";
		echo "<div class='divTableCell'>" . __('naksh_tharai') . "</div>";
	echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{	
		echo "<div class='divTableRow'>";
			$planet_translated = translate_planet_abbr($planet);
			if((strcmp("சனி",$planet)==0 or strcmp("குரு",$planet)==0 or strcmp("செ",$planet)==0 or strcmp("சுக்",$planet)==0 or
				strcmp("புத",$planet)==0) and $planetDeg_default[$planet] < 0.0){
					//strcmp("புத",$planet)==0) and $planetDeg[$planet] < 0.0){
						
				echo "<div class='divPanchangHeadingSmall'>$planet_translated " . translate_tamil_text("(வ)") . "</div>";
			}
			else{
				echo "<div class='divPanchangHeadingSmall'>$planet_translated</div>";
		}
		
		echo "<div class='divPanchangSmall'>" . translate_tamil_text($data[0]) . "</div>";
		//echo "<div class='divPanchangSmall'>" . getThaaraPalan(explode(' ',$data[0])[0]) . "</div>";
		echo getThaaraPalan($data[0]);
	echo "</div>";
}	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
}

function calculateThaaraPalan($inStar){
	$thaarapalan = array();
	
	$star_arr = array("dummy","Aswini","Bharani","Krithika","Rohini","Mrigashirsha","Arudra", "Punarvasu","Pushyami","Ashlesha","Magha","Poorva Phalguni","Uthra phalguni","Hastha","Chitra","Swaathi","Vishaakha","Anuraadha","Jyeshta","Moola","Poorva Ashaada","Uthra Ashaada","Shraavan","Dhanishta","Shathabhisha","Poorva bhadrapada","Uthra bhadrapada","Revathi");
	//$star_arr_result = array("dummy","Aswini/அசுவினி","Bharani/பரணி","Krithika/கிருத்திகை","Rohini/ரோகிணி","Mrigashirsha/மிருகசிரீஷம்","Arudra/திருவாதிரை","Punarvasu/புனர்பூசம்","Pushyami/பூசம்","Ashlesha/ஆயில்யம்","Magha/மகம்","Poorva Phalguni/பூரம்","Uthra phalguni/உத்திரம்","Hastha/ஹஸ்தம்","Chitra/சித்திரை","Swaathi/சுவாதி","Vishaakha/விசாகம்","Anuraadha/அனுஷம்","Jyeshta/கேட்டை","Moola/மூலம்","Poorva Ashaada/பூராடம்","Uthra Ashaada/உத்திராடம்","Shraavan/திருவோணம்","Dhanishta/அவிட்டம்","Shathabhisha/சதயம்","Poorva bhadrapada/பூரட்டாதி","Uthra bhadrapada/உத்திரட்டாதி","Revathi/ரேவதி");
	$star_arr_result = array("dummy","அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி");
	foreach ($star_arr as $key => $value){
		if($key==0) {continue;}
		
		$start_pos = array_search($inStar,$star_arr);
		$end_pos = array_search($value,$star_arr);
		
		if($start_pos > 1 && $start_pos>$end_pos){
			//echo $value . (27-$start_pos+$end_pos+1) . "<br>";
			debug("[" . $inStar . "=" . $start_pos . "]" . "[" . $value . "=" . (27-$start_pos+$end_pos+1) . "]" . "<br>");
			$thaarapalan[$star_arr_result[$key]] = 27-$start_pos+$end_pos+1;
			
			if((27-$start_pos+$end_pos+1)==22) {
				debug("<span style='color:white;background-color:red;font-size:11pt'>VAINASIKA STAR - ". $value . "</span><br>");
				$thaarapalan['VAINASIKA'] = $star_arr_result[$key];
			}
			continue;
		}
		else{
			debug("[" . $inStar . "=" . $start_pos . "]" . "[" . $value . "=" . ($end_pos-$start_pos+1) . "]" . "<br>");
			$thaarapalan[$star_arr_result[$key]] = $end_pos-$start_pos+1;
		}
		
		if(($end_pos-$start_pos+1)==22) {
			debug("<span style='color:white;background-color:red;font-size:11pt'>VAINASIKA STAR - ". $value . "</span><br>");
			$thaarapalan['VAINASIKA'] = $star_arr_result[$key];
		}
	}
	
	return $thaarapalan;
}

// Declare this function once outside the main function
function getThaaraHtml($instar_padham) {
	global $rearranged_arr;
	
	$nak_array = array("அசுவினி 1","அசுவினி 2","அசுவினி 3","அசுவினி 4",
                   "பரணி 1","பரணி 2","பரணி 3","பரணி 4",
                   "கிருத்திகை 1","கிருத்திகை 2","கிருத்திகை 3","கிருத்திகை 4",
                   "ரோகிணி 1","ரோகிணி 2","ரோகிணி 3","ரோகிணி 4",
                   "மிருகசிரீஷம் 1","மிருகசிரீஷம் 2","மிருகசிரீஷம் 3","மிருகசிரீஷம் 4",
                   "திருவாதிரை 1","திருவாதிரை 2","திருவாதிரை 3","திருவாதிரை 4",
                   "புனர்பூசம் 1","புனர்பூசம் 2","புனர்பூசம் 3","புனர்பூசம் 4",
                   "பூசம் 1","பூசம் 2","பூசம் 3","பூசம் 4",
                   "ஆயில்யம் 1","ஆயில்யம் 2","ஆயில்யம் 3","ஆயில்யம் 4",
                   "மகம் 1","மகம் 2","மகம் 3","மகம் 4",
                   "பூரம் 1","பூரம் 2","பூரம் 3","பூரம் 4",
                   "உத்திரம் 1","உத்திரம் 2","உத்திரம் 3","உத்திரம் 4",
                   "ஹஸ்தம் 1","ஹஸ்தம் 2","ஹஸ்தம் 3","ஹஸ்தம் 4",
                   "சித்திரை 1","சித்திரை 2","சித்திரை 3","சித்திரை 4",
                   "சுவாதி 1","சுவாதி 2","சுவாதி 3","சுவாதி 4",
                   "விசாகம் 1","விசாகம் 2","விசாகம் 3","விசாகம் 4",
                   "அனுஷம் 1","அனுஷம் 2","அனுஷம் 3","அனுஷம் 4",
                   "கேட்டை 1","கேட்டை 2","கேட்டை 3","கேட்டை 4",
                   "மூலம் 1","மூலம் 2","மூலம் 3","மூலம் 4",
                   "பூராடம் 1","பூராடம் 2","பூராடம் 3","பூராடம் 4",
                   "உத்திராடம் 1","உத்திராடம் 2","உத்திராடம் 3","உத்திராடம் 4",
                   "திருவோணம் 1","திருவோணம் 2","திருவோணம் 3","திருவோணம் 4",
                   "அவிட்டம் 1","அவிட்டம் 2","அவிட்டம் 3","அவிட்டம் 4",
                   "சதயம் 1","சதயம் 2","சதயம் 3","சதயம் 4",
                   "பூரட்டாதி 1","பூரட்டாதி 2","பூரட்டாதி 3","பூரட்டாதி 4",
                   "உத்திரட்டாதி 1","உத்திரட்டாதி 2","உத்திரட்டாதி 3","உத்திரட்டாதி 4",
                   "ரேவதி 1","ரேவதி 2","ரேவதி 3","ரேவதி 4");

						
	$thara_palan_arr = $rearranged_arr[13];
	
	$nak_idx = array_search($instar_padham,$nak_array);
	
	$rasi_padham_idx = $nak_idx % 9;
	
	$nak_idx = ceil(($nak_idx+1) / 4);  // divide by 4 to get rasi idx
	
	$suk_thara = $rearranged_arr[13][$rasi_padham_idx];
	
	debug("instar_padham=$instar_padham,rasi_padham_idx=$rasi_padham_idx,suk_thara=$suk_thara,nak_idx=$nak_idx");
	
    /*switch ($nak_idx) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 10:
        case 12:
        case 14:
        case 16:
        case 19:
        case 21:
        case 23:
        case 25:
            //return "<div class='divThaaraPalanRed'>" . "$thara ($sookshama_thaara)" . $thaarapalan_arr[$thara] . "</div>";
			return "<div class='divThaaraPalanRed'>$suk_thara</div>";
        case 11:
			return "<div class='divThaaraPalanRedBright'>சமு.பகை</div>";
        case 18:
			return "<div class='divThaaraPalanRedBright'>பணப்பகை</div>";
        case 22:
            //return "<div class='divThaaraPalanRedBright'>" . "$thara ($sookshama_thaara)" . $thaarapalan_arr[$thara] . "</div>";
			return "<div class='divThaaraPalanRedBright'>வைநாசிகம்</div>";
        case 2:
        case 4:
        case 6:
        case 8:
        case 13:
        case 15:
        case 17:
        case 20:
        case 24:
        case 26:
            //return "<div class='divThaaraPalanGreen'>" . "$thara ($sookshama_thaara)" . $thaarapalan_arr[$thara] . "</div>";
			return "<div class='divThaaraPalanGreen'>$suk_thara</div>";
        case 9:
        case 27:
            //return "<div class='divThaaraPalanGreenBright'>" . "$thara ($sookshama_thaara)" . $thaarapalan_arr[$thara] . "</div>";
			return "<div class='divThaaraPalanGreenBright'>$suk_thara</div>";
        default:
            return ""; // Optional default case for when no match occurs
    }
	*/
	
	return "<div class='divThaaraPalanWhite'>" . translate_tamil_text($suk_thara) . "</div>";
}

// Main function
function getThaaraPalan($inStar_padham) {
    global $g_nak, $g_padham, $panchang;
	
	[$inStar, $padham] = explode(' ',$inStar_padham);
    
    $star_arr_result = array("dummy", "அசுவினி", "பரணி", "கிருத்திகை", "ரோகிணி", "மிருகசிரீஷம்", "திருவாதிரை", "புனர்பூசம்", "பூசம்", "ஆயில்யம்", "மகம்", "பூரம்", "உத்திரம்", "ஹஸ்தம்", "சித்திரை", "சுவாதி", "விசாகம்", "அனுஷம்", "கேட்டை", "மூலம்", "பூராடம்", "உத்திராடம்", "திருவோணம்", "அவிட்டம்", "சதயம்", "பூரட்டாதி", "உத்திரட்டாதி", "ரேவதி");
    
    $thaarapalan_arr = [
        "dummy", "ஜென்ம", "சம்பத்", "விபத்து", "ஷேம", "பிரத்யக்", "சாதக", "வதை", "மைத்ர", "ப.மைத்ர", "ஜென்ம", "சமு.பகை", "விபத்து", "ஷேம", "பிரத்யக்", "சாதக", "வதை", "மைத்ர", "ப.பகை", "ஜென்ம", "சம்பத்", "விபத்து", "வைநாசி", "பிரத்யக்", "சாதக", "வதை", "மைத்ர", "ப.மைத்ர"
    ];
	
	$str = getShortStar($g_nak . ' ' . $g_padham);
	
	//echo "----".array_search($str, $nak_array);


    // Find the positions in the star array
    $start_pos = array_search($g_nak, $star_arr_result);
    $end_pos = array_search($inStar, $star_arr_result);

    // Calculate the thara value
    if ($start_pos > 1 && $start_pos > $end_pos) {
        $thara = 27 - $start_pos + $end_pos + 1;
    } else {
        $thara = $end_pos - $start_pos + 1;
    }
	
	debug("START=$start_pos,END=$end_pos,THAARA=$thara");
	
	switch ($thara) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 10:
        case 12:
        case 14:
        case 16:
        case 19:
        case 21:
        case 23:
        case 25:
            return "<div class='divThaaraPalanRed'>" . translate_tamil_text($thaarapalan_arr[$thara]) . "</div>";
        case 11:
		case 18:
		case 22:
			return "<div class='divThaaraPalanRedBright'>" . translate_tamil_text($thaarapalan_arr[$thara]) . "</div>";
        case 2:
        case 4:
        case 6:
        case 8:
        case 13:
        case 15:
        case 17:
        case 20:
        case 24:
        case 26:
            return "<div class='divThaaraPalanGreen'>" . translate_tamil_text($thaarapalan_arr[$thara]) . "</div>";
        case 9:
        case 27:
            return "<div class='divThaaraPalanGreenBright'>" . translate_tamil_text($thaarapalan_arr[$thara]) . "</div>";
        default:
            return ""; // Optional default case for when no match occurs
    }
}

//This orders the paadham inside each rasi as well as thaara palan array (13) based on the moon's paadham
//this is used to calculate sukshuma paadham for each planets in star lord table
function generateOrderedNakArray($search) {
    $nak_array = array(
        1 => array(
            "அசுவி 1", "அசுவி 2", "அசுவி 3", "அசுவி 4", 
            "பரணி 1", "பரணி 2", "பரணி 3", "பரணி 4",
            "கிருத் 1"
        ),
        2 => array(
            "கிருத் 2", "கிருத் 3", "கிருத் 4", 
            "ரோகி 1", "ரோகி 2", "ரோகி 3", "ரோகி 4", 
            "மிருக 1", "மிருக 2"
        ),
        3 => array(
            "மிருக 3", "மிருக 4", "திருவா 1", "திருவா 2", 
            "திருவா 3", "திருவா 4", "புனர் 1", "புனர் 2", 
            "புனர் 3"
        ),
        4 => array(
            "புனர் 4", "பூசம் 1", "பூசம் 2", "பூசம் 3", 
            "பூசம் 4", "ஆயில் 1", "ஆயில் 2", "ஆயில் 3", 
            "ஆயில் 4"
        ),
        5 => array(
            "மகம் 1", "மகம் 2", "மகம் 3", "மகம் 4", 
            "பூரம் 1", "பூரம் 2", "பூரம் 3", "பூரம் 4", 
            "உத்ர 1"
        ),
        6 => array(
            "உத்ர 2", "உத்ர 3", "உத்ர 4", "ஹஸ்த 1", 
            "ஹஸ்த 2", "ஹஸ்த 3", "ஹஸ்த 4", "சித்தி 1", 
            "சித்தி 2"
        ),
        7 => array(
            "சித்தி 3", "சித்தி 4", "சுவா 1", "சுவா 2", 
            "சுவா 3", "சுவா 4", "விசா 1", "விசா 2", 
            "விசா 3"
        ),
        8 => array(
            "விசா 4", "அனுஷ 1", "அனுஷ 2", "அனுஷ 3", 
            "அனுஷ 4", "கேட் 1", "கேட் 2", "கேட் 3", 
            "கேட் 4"
        ),
        9 => array(
            "மூலம் 1", "மூலம் 2", "மூலம் 3", "மூலம் 4", 
            "பூரா 1", "பூரா 2", "பூரா 3", "பூரா 4", 
            "உத்ராட 1"
        ),
        10 => array(
            "உத்ராட 2", "உத்ராட 3", "உத்ராட 4", "திருவோ 1", 
            "திருவோ 2", "திருவோ 3", "திருவோ 4", "அவிட் 1", 
            "அவிட் 2"
        ),
        11 => array(
            "அவிட் 3", "அவிட் 4", "சதய 1", "சதய 2", 
            "சதய 3", "சதய 4", "பூரட் 1", "பூரட் 2", 
            "பூரட் 3"
        ),
        12 => array(
            "பூரட் 4", "உத்ரட் 1", "உத்ரட் 2", "உத்ரட் 3", 
            "உத்ரட் 4", "ரேவதி 1", "ரேவதி 2", "ரேவதி 3", 
            "ரேவதி 4"
        ),
		13 => array("ஜென்ம", "சம்பத்", "விபத்து", "ஷேமம்", 
				"பிரத்யகு", "சாதக", "வதை", "மைத்ரம்", 
				"ப.மைத்ர"
		)
    );

    $nak_array_out = array();

    // Step 1: Find the moon paadham
    $foundInGroup = null;
    $indexOfSearch = null;

    foreach ($nak_array as $groupNumber => $group) {
        if (in_array($search, $group)) {
            $foundInGroup = $groupNumber;
            $indexOfSearch = array_search($search, $group);
            break;
        }
    }

    // Step 2: Generate the reordered array based on moon paadham count
    foreach ($nak_array as $groupNumber => $group) {
        // Rotate the group array starting from the found moon paadham index
        $group = array_merge(
            array_slice($group, 9-$indexOfSearch), 
            array_slice($group, 0, 9-$indexOfSearch)
        );
        
        $nak_array_out[$groupNumber] = $group;
    }

    return $nak_array_out;
}

function displayErrorPage(){
	echo <<< EOL
		<center>
<H2><span style='color:red'>***UPDATE***<BR>There is an issue in the server which is causing this error. Working with service provider. Server பிரச்சினை இருப்பதால் app வேலை செய்யவில்லை. சர்வர் கம்பெனியுடன் வேலை செய்துகொண்டுள்ளேன். விரைவில் சரி செய்யப்படும்.</SPAN></h2>
</center>

EOL;

	return;
}

function getShortStar($star_padham){
	// Mapping of nakshatra names to their abbreviation keys
	$nak_to_abbr_key = array(
		// Tamil names
		"அசுவினி" => "aswini_abbr",
		"பரணி" => "bharani_abbr",
		"கிருத்திகை" => "krittikai_abbr",
		"ரோகிணி" => "rohini_abbr",
		"மிருகசிரீஷம்" => "mrigashirsha_abbr",
		"திருவாதிரை" => "ardra_abbr",
		"புனர்பூசம்" => "punarvasu_abbr",
		"பூசம்" => "pushya_abbr",
		"ஆயில்யம்" => "ashlesha_abbr",
		"மகம்" => "magha_abbr",
		"பூரம்" => "purva_phalguni_abbr",
		"உத்திரம்" => "uttara_phalguni_abbr",
		"ஹஸ்தம்" => "hasta_abbr",
		"சித்திரை" => "chitra_abbr",
		"சுவாதி" => "swati_abbr",
		"விசாகம்" => "vishakha_abbr",
		"அனுஷம்" => "anuradha_abbr",
		"கேட்டை" => "jyeshtha_abbr",
		"மூலம்" => "mula_abbr",
		"பூராடம்" => "purva_ashadha_abbr",
		"உத்திராடம்" => "uttara_ashadha_abbr",
		"திருவோணம்" => "shravana_abbr",
		"அவிட்டம்" => "dhanishta_abbr",
		"சதயம்" => "shatabhisha_abbr",
		"பூரட்டாதி" => "purva_bhadrapada_abbr",
		"உத்திரட்டாதி" => "uttara_bhadrapada_abbr",
		"ரேவதி" => "revati_abbr",
		// English names
		"Ashwini" => "aswini_abbr",
		"Bharani" => "bharani_abbr",
		"Krittika" => "krittikai_abbr",
		"Rohini" => "rohini_abbr",
		"Mrigashira" => "mrigashirsha_abbr",
		"Ardra" => "ardra_abbr",
		"Punarvasu" => "punarvasu_abbr",
		"Pushya" => "pushya_abbr",
		"Ashlesha" => "ashlesha_abbr",
		"Magha" => "magha_abbr",
		"Purva Phalguni" => "purva_phalguni_abbr",
		"Uttara Phalguni" => "uttara_phalguni_abbr",
		"Hasta" => "hasta_abbr",
		"Chitra" => "chitra_abbr",
		"Swati" => "swati_abbr",
		"Vishakha" => "vishakha_abbr",
		"Anuradha" => "anuradha_abbr",
		"Jyeshtha" => "jyeshtha_abbr",
		"Mula" => "mula_abbr",
		"Purva Ashadha" => "purva_ashadha_abbr",
		"Uttara Ashadha" => "uttara_ashadha_abbr",
		"Shravana" => "shravana_abbr",
		"Dhanishta" => "dhanishta_abbr",
		"Shatabhisha" => "shatabhisha_abbr",
		"Purva Bhadrapada" => "purva_bhadrapada_abbr",
		"Uttara Bhadrapada" => "uttara_bhadrapada_abbr",
		"Revathi" => "revati_abbr"
	);
	
	[$star,$padham] = explode(" ",$star_padham);
	
	// Look up the abbreviation key for this nakshatra
	if (isset($nak_to_abbr_key[$star])) {
		$abbr_key = $nak_to_abbr_key[$star];
		return __($abbr_key) . ' ' . $padham;
	}
	
	// Fallback to original star name if no abbreviation found
	return $star . ' ' . $padham;
}

//Used in Jamakol to find sookuma rasi
//Calculation, Count Udhayam to Aaruda athipathi, uthayam_to_aarudaAthipathi_count. 
//Find the rasi from Aaruda athipathi for this uthayam_to_aarudaAthipathi_count.
function updateSookumaRasi($aar_house){
	global $sw_planet, $outside, $udhayam_house;
	
	$aaruda_athipathi = findAthipathi($aar_house);
	$aaruda_athipathi_search = $aaruda_athipathi;
	if (function_exists('is_english') && is_english() && function_exists('translate_planet_abbr')) {
		$aaruda_athipathi_search = translate_planet_abbr($aaruda_athipathi);
	}
		
	$found_keys = array_keys(array_filter($outside, function ($value) use ($aaruda_athipathi_search, $aaruda_athipathi) {
		return strpos($value, $aaruda_athipathi_search) !== false || strpos($value, $aaruda_athipathi) !== false;
	}));

	if (empty($found_keys)) {
		debug("updateSookumaRasi: aaruda athipathi not found in outside planets: $aaruda_athipathi_search");
		return;
	}
	
	$aaruda_athipathi_house = $found_keys[0];
	
	$uthayam_to_aarudaAthipathi_count = countHouses($udhayam_house,$aaruda_athipathi_house);
	
	$sookuma_house = targetHouse($aaruda_athipathi_house,$uthayam_to_aarudaAthipathi_count);
	
	debug("aar_house=$aar_house<BR>aaruda_athipathi=$aaruda_athipathi<BR>aaruda_athipathi_house=$aaruda_athipathi_house<BR>uthayam_to_aarudaAthipathi_count=$uthayam_to_aarudaAthipathi_count<BR>sookuma_house=$sookuma_house");
	
	$sw_planet[$sookuma_house] = $sw_planet[$sookuma_house] . "<span class='sookuma'> சூக்</span>";
}

//தலைவிதி புள்ளி காணும் முறை 
//ராகு உள்ளதூரம் முதல் சந்திரன் உள்ள தூரம் வரை எண்ணி வரும் விடையை 2 ஆல் வகுக்க கிடைக்க விடை தலைவிதி புள்ளி ஆகும்...
function calculateThalaivithiPulli($moonDeg, $rahuDeg) {
    // Step 1: Subtract Rahu from Moon
    $diff = abs($moonDeg - $rahuDeg);

    // Step 3: Divide by 2 to get Destiny Point
    $destinyPoint = ceil($diff / 2 / 3.333333);
	
	$starIdx = ceil($destinyPoint/4);

    return [getValue(getStarPaatham($destinyPoint)),$starIdx];
}

//அதிர்ஷ்ட புள்ளி
//Add full degree of moon, 5th lord, 9th lord. Subtract 360 if the total is more than 360.
//The star on which the final degree falls is your luck point. We should see if there are any
//planets in this star. If yes that planet is your lucky planet. if not there, consider the star lord.
//Check in which bava the lucky star lord placed. The life karaka of that bava is your luck.
//if 4 mother, 5 child, 6 uncle, 7 wife, 9 father. Make sure to activate that relation

function calculateAthrishtaPulli(){
	global $planet_starlord, $lagna_house;
	
	$fifth_house = $lagna_house-1+5-1;
	if($fifth_house >= 12) $fifth_house=$fifth_house-12;
	
	$ninth_house = $lagna_house-1+9-1;
	if($ninth_house >= 12) $ninth_house=$ninth_house-12;
	
	$fifthLord = findAthipathi($fifth_house);
	$ninthLord = findAthipathi($ninth_house);
	$total_deg = $planet_starlord['சந்'][2]+$planet_starlord[$fifthLord][2]+$planet_starlord[$ninthLord][2];
	
	debug("<BR>****MoonDeg=". $planet_starlord['சந்'][2]. "<BR>fifthLord=$fifthLord". $planet_starlord[$fifthLord][2] . "<BR>NinthLord=$ninthLord" . $planet_starlord[$ninthLord][2]);
	
	$final_deg = fmod($total_deg,360);
	
	debug("<BR>****adjustedDeg=$final_deg");
	
	$athirishtaIdx = ceil($final_deg/3.333333);
	
	$starIdx = ceil($athirishtaIdx/4);
	
	debug("<BR>****star#=$starIdx<BR>star=".getValue(getStarPaatham($athirishtaIdx)));
	
	return [getValue(getStarPaatham($athirishtaIdx)),$starIdx];
}

//துரதிர்ஷ்ட புள்ளி
//Add full degree of moon, 6th lord, 8th lord. Subtract 360 if the total is more than 360.
//The star on which the final degree falls is your unlucky point. We should see if there are any
//planets in this star. If yes that planet is your unlucky planet. if not there, consider the star lord.
//Check in which bava the unlucky star lord placed. The life karaka of that bava is your unlucky.

function calculateDhurathrishtaPulli(){
	global $planet_starlord, $lagna_house;
	
	$sixth_house = $lagna_house-1+6-1;
	if($sixth_house >= 12) $sixth_house=$sixth_house-12;
	
	$eight_house = $lagna_house-1+8-1;
	if($eight_house >= 12) $eight_house=$eight_house-12;
	
	$sixthLord = findAthipathi($sixth_house);
	$eightLord = findAthipathi($eight_house);
	$total_deg = $planet_starlord['சந்'][2]+$planet_starlord[$sixthLord][2]+$planet_starlord[$eightLord][2];
	
	debug("<BR>****MoonDeg=". $planet_starlord['சந்'][2]. "<BR>sixthLord=$sixthLord" . $planet_starlord[$sixthLord][2]. "<BR>eightLord=$eightLord" . $planet_starlord[$eightLord][2]);
	
	$bad_point_idx = ceil(fmod($total_deg,360)/3.333333);
	
	debug("<BR>****adjustedDeg=".ceil(fmod($total_deg,360)));
	
	$bad_point_remedy_idx = ($bad_point_idx + 54) % 108;
	
	if($bad_point_remedy_idx  == 0){
		$bad_point_remedy_idx = 108;
	}
	
	//$bad_point_remedy_idx = $bad_point_remedy_idx + 1; //increase by 1  because nak_arr starts from idx 1
	
	$star_idx =ceil($bad_point_remedy_idx/4);

	debug("<BR>****badstar#=$bad_point_idx<BR>star=".getValue(getStarPaatham($bad_point_idx)));
	debug("<BR>****remedystar#=$star_idx<BR>badstar=".getValue(getStarPaatham($bad_point_remedy_idx)));
	
	return [getValue(getStarPaatham($bad_point_idx)),getValue(getStarPaatham($bad_point_remedy_idx)), $star_idx];
}

function findSubLord($planet, $full_deg) {
	
	//find nakshatra degree
	$deg = fmod($full_deg,13.333333);
	
    // Convert degrees to minutes
    $deg_in_minutes = $deg * 60;

    // Vimshottari Dasha years
    $vimshottari = [
        'கே' => 7,
        'சுக்' => 20,
        'சூரி' => 6,
        'சந்' => 10,
        'செ' => 7,
        'ரா' => 18,
        'குரு' => 16,
        'சனி' => 19,
        'புத' => 17
    ];

    // Planet order
    $planet_order = array_keys($vimshottari);

    // Total span for 1 Nakshatra (800 minutes = 13°20′)
    $total_span = 800;

    // Reorder the planet list to start from the given planet
    $start_index = array_search($planet, $planet_order);
    if ($start_index === false) {
        return "Invalid planet name.";
    }

    // Reorder the array to begin with the starting planet and wrap around
    $rotated_planets = array_merge(
        array_slice($planet_order, $start_index),
        array_slice($planet_order, 0, $start_index)
    );

    // Calculate and find the sub lord
    $current_pos = 0;
    foreach ($rotated_planets as $sub_lord) {
	    $portion = ($vimshottari[$sub_lord] / 120) * $total_span;
        if ($deg_in_minutes >= $current_pos && $deg_in_minutes < ($current_pos + $portion)) {
            return $sub_lord;
        }
        $current_pos += $portion;
    }

    return "Degree out of range.";
}

/*
இதைத் தேடி அலைந்த போது தான் ஒரு குத்திரம் கிடைத்தது. ஒரு சிறு கணிதம் தான்.
உங்கள் சாதகத்தை திறக்கவும், அஸ்வினியிலிருந்து

1. சூரியன் நின்ற பாதம் எண்
2. சந்திரன் நின்ற நட்சத்திரம் பாதம் எண்
3. லக்னப் புள்ளி நின்ற பாதம் எண்

இவை மூன்றையும் கூட்டவும்.
உதாரணம்:
சூரியன் நின்ற பாதம் :                         60
சந்திரன் நின்ற பாதம் :                         92
லக்னப் புள்ளி நின்ற பாதம் :                    63
கூட்டுத் தொகை :                              215
கழிக்க :                                       108
மீதம் வருவது சுகம் அளிக்கும் புண்ணிய பாதம் : 107

107 முடிந்து விட்டது. 108வது ஆகும் பாதம் ரேவதி உங்கள் சுகம் தரும் புண்ணிய பாதம்.

இந்த பாதத்தில் உச்சமாகும் கிரகமே உங்கள் சுகம் தரும் புண்ணிய பாதம். சுக்கிரனே
உங்களுக்கு வண்டி வாகனம் லக்ஸூரியஸ் வாழ்வை தரும் அதன் தசா/புக்தியில்.

இந்த கிரகமே நிலைத்த இன்பத்தை வாரி வழங்கும். இந்த பாதத்தில் நீச கிரகம் இருந்த்தால் 
கவனம் தேவை.

உச்ச நீசம் இல்லையெனில் அமர்ந்த கிரகம் அல்லது அந்த நட்சத்திர அதிபதி துணை புரிவார்.
*/

/**
 * Calculate the “புண்ணிய பாதம்” (final paadham).
 *
 * @param int|float $sunPada      சூரியன் தாய்தின்ற பாதம் எண்
 * @param int|float $moonPada     சந்திரன் தாய்நின்ற நட்சத்திரம் பாதம் எண்
 * @param int|float $lagnaPada    லக்னப் புள்ளி நின்ற பாதம் எண்
 *
 * @return int 1‑to‑108 — the final paadham (“சகம் தரும் புண்ணிய பாதம்”)
 */
function getSugaPaadham($sunPada, $moonPada, $lagnaPada)
{
    // Step 1: add the three paadham numbers
    $total = $sunPada + $moonPada + $lagnaPada;

    // Step 2: take the remainder when divided by 108
    $remainder = $total % 108 + 1;
	
	if($remainder > 108){
		$remainder = 1;
	}
	
	$padham_idx = $remainder === 0 ? 108 : $remainder;

	$star_idx = ceil($padham_idx/4);


    // Step 3: if perfectly divisible, treat the result as 108 (not 0)
    return [$star_idx, getValue(getStarPaatham($padham_idx))];
}


/**
 * Build the 1‑5‑9 / 2‑6‑10 / 3‑7‑11 / 4‑8‑12 rāśi‑triad clubs,
 * sort each club by (full‑degree % 30), wrap retrograde planets
 * in <span class="retro">…</span>, and **hide any club that ends
 * up with just a single planet**.
 *
 * ─────────────────────────────────────────────────────────────
 * • A planet is *retrograde* when
 *       – $planetDeg_default[$tag] < 0,  **or**
 *       – the tag is Rahu (ரா) or Ketu (கே).  
 *   Retro tags are wrapped in <span class="retro">…</span>.
 * • Tags in $exclude are ignored entirely (default: Lagna லக் & Maandi மா).
 * • Returned structure example:
 *     [
 *       '1‑5‑9'  => [ ['tag'=>'செ',                'deg'=> 4.0 ],
 *                     ['tag'=>'<span …>ரா</span>','deg'=> 7.1 ],
 *                     ['tag'=>'சந்',              'deg'=>21.0 ] ],
 *       '3‑7‑11' => [ … ],  // only clubs with ≥2 planets appear
 *     ]
 *
 * @param array $planet_starlord   ['Tag' => [?, ?, <fullDeg>], …]
 * @param array $planetDeg_default ['Tag' => <speed‑or‑Δλ>, …]
 * @param array $exclude           Tags to omit (default ['லக்','மா'])
 * @return array                   Clubs with ≥2 planets, sorted inside
 */
function buildTriadClubsWithRetro(
    array $planet_starlord,
    array $planetDeg_default,
    array  $exclude = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"]
): array {
    /* -------- setup -------- */
    $clubs = [
        '1‑5‑9'  => [],
        '2‑6‑10' => [],
        '3‑7‑11' => [],
        '4‑8‑12' => [],
    ];
    $alwaysRetro = ['ரா', 'கே'];                     // Rahu, Ketu

    /* -------- scan every planet -------- */
    foreach ($planet_starlord as $tag => $row) {

        /* skip excluded tags or bad rows */
        if (in_array($tag, $exclude, true) ||
            !isset($row[2]) ||
            !is_numeric($row[2])) {
            continue;
        }

        /* absolute & relative positions */
        $full = fmod((float)$row[2] + 360, 360);     // 0‑359.999…
        $rel  = $full % 30;                          // 0‑29.999  (inside rāśi)
        $rasi = intdiv((int)$full, 30) + 1;          // 1 … 12

        /* retro? */
        $isRetro = in_array($tag, $alwaysRetro, true) ||
                   (isset($planetDeg_default[$tag]) &&
                    $planetDeg_default[$tag] < 0);

        $dispTag = $isRetro
                 ? '<span class="retro">'.$tag.'</span>'
				 #? $tag.'(வ)'
                 : $tag;

        /* bucket: 1‑5‑9, 2‑6‑10, 3‑7‑11, 4‑8‑12 */
        $bucket = ['1‑5‑9','2‑6‑10','3‑7‑11','4‑8‑12'][($rasi - 1) % 4];

        $clubs[$bucket][] = ['tag' => $dispTag, 'deg' => $rel];
    }

    /* -------- sort inside every club -------- */
    foreach ($clubs as &$club) {
        usort($club, fn($a, $b) => $a['deg'] <=> $b['deg']);
    }
    unset($club);

    /* -------- drop clubs with ≤1 planet -------- */
    $clubs = array_filter($clubs, fn($c) => count($c) > 1);

    return $clubs;
}



/**
 * Build every “base + partner” pair for the 2‑nd‑rāśi rule.
 *
 * Rule set
 * ─────────────────────────────────────────────────────────────
 * • A “base” planet is any planet in rāśi‑1 (0° – < 30°), except
 *   the tags you choose to exclude (default: Lagna லக் & Maandi மா).
 * • If the base planet is **non‑retro**            → partners are
 *   all planets in rāśi‑2  (30° – < 60°).
 * • If the base planet is **retrograde**           → partners are
 *   all planets in rāśi‑12 (330° – < 360°).
 * • Rahu (ரா) and Ketu (கே) are always treated as retrograde.
 * • Retrograde planets are wrapped in
 *   `<span class="retro">…</span>` so you can style them in red.
 *
 * @param array $planet_starlord   ['Tag' => [?, ?, <fullDegree>], …]
 * @param array $planetDeg_default ['Tag' => <speed‑or‑Δλ>, …]  (< 0 = retro)
 * @param array $exclude           Tags to skip as bases or partners
 * @return array                   ["Base + Partner", …]  ← already formatted
 */
function buildSecondRasiPairs(
    array $planet_starlord,
    array $planetDeg_default,
    array  $exclude = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"]
): array {
    $alwaysRetro = ['ரா', 'கே'];                   // Rahu & Ketu (default retro)
    $rasiBuckets = array_fill(1, 12, []);          // 1 … 12 → []

    /* ---- 1) classify every planet by absolute rāśi ---- */
    foreach ($planet_starlord as $tag => $row) {
        if (!isset($row[2]) || !is_numeric($row[2])) {
            continue;                              // bad or missing degree
        }
        $full   = fmod((float)$row[2] + 360, 360); // 0‑359.999…
        $rasi   = intdiv((int)$full, 30) + 1;      // 1 … 12
        $isRetro = in_array($tag, $alwaysRetro, true)
                   || (isset($planetDeg_default[$tag]) && $planetDeg_default[$tag] < 0);

        $rasiBuckets[$rasi][] = [
            'tag'   => $tag,
            'retro' => $isRetro,
            #'disp'  => $isRetro ? '<span class="retro">'.$tag.'</span>' : $tag
			'disp'  => $isRetro ? $tag.'(வ)' : $tag
        ];
    }

    /* ---- 2) build the “base + partner” pairs ---- */
    $pairs = [];

    foreach ($rasiBuckets[1] as $base) {           // only planets in rāśi‑1
        if (in_array($base['tag'], $exclude, true)) {
            continue;                              // skip Lagna, Maandi, …
        }

        $targetRasi = $base['retro'] ? 12 : 2;     // retro → 12th, else 2nd

        foreach ($rasiBuckets[$targetRasi] as $partner) {
            if (in_array($partner['tag'], $exclude, true)) {
                continue;                          // never pair with excluded
            }
            $pairs[] = $base['disp'].' + '.$partner['disp'];
        }
    }

    return $pairs;                                 // already “Base + Partner”
}


/**
 * Build every 2‑planet AND “full‑set” combination for each rāśi.
 *
 * – Two‑planet pairs are always produced when a sign has ≥ 2 planets.  
 * – If a sign has ≥ 3 planets, an extra entry that concatenates them all
 *   (e.g.  A + B + C) is added.
 * – Retro criteria, exclusions, and HTML wrapping are identical to the
 *   previous version.
 *
 * @param array $planet_starlord   ['Tag' => [?, ?, <fullDeg>], …]
 * @param array $planetDeg_default ['Tag' => <speed‑or‑Δλ>, …]  (<0 = retro)
 * @param array $exclude           Tags to skip entirely (default ['லக்','மா'])
 * @return array                   rāśi‑number → [ "A + B", "A + B + C", … ]
 */
function buildSameRasiCombinations(
    array $planet_starlord,
    array $planetDeg_default,
    array  $exclude = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"]
): array {
    $alwaysRetro = ['ரா', 'கே'];                  // Rahu & Ketu
    $rasiBuckets = array_fill(1, 12, []);         // 1 … 12 → []

    /* ---- 1) bucket every planet by rāśi ---- */
    foreach ($planet_starlord as $tag => $row) {
        if (in_array($tag, $exclude, true) ||
            !isset($row[2]) ||
            !is_numeric($row[2])) {
            continue;                             // skip excluded / invalid
        }

        $full = fmod((float)$row[2] + 360, 360);  // 0‑359.999…
        $rasi = intdiv((int)$full, 30) + 1;       // 1 … 12

        $isRetro = in_array($tag, $alwaysRetro, true) ||
                   (isset($planetDeg_default[$tag]) &&
                    $planetDeg_default[$tag] < 0);

        $dispTag = $isRetro
                 #? '<span class="retro">'.$tag.'</span>'
				 ? $tag.'(வ)'
                 : $tag;

        $rasiBuckets[$rasi][] = $dispTag;
    }

    /* ---- 2) build combinations for each rāśi ---- */
    $combos = [];

    foreach ($rasiBuckets as $rasiNum => $planets) {
        $n = count($planets);
        if ($n < 2) continue;                    // need at least two

        /* two‑planet pairs */
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $combos[$rasiNum][] = $planets[$i] . ' + ' . $planets[$j];
            }
        }

        /* full combination when ≥3 planets */
        if ($n > 2) {
            $combos[$rasiNum][] = implode(' + ', $planets);
        }
    }

    return $combos;                              // rāśi → [ "A + B", … ]
}


/**
 * Produce four “focus + partner” buckets around a given planet:
 *
 *   triad  → partners in the 5th & 9th houses from focus
 *   2/12   → • if focus is **direct**  → partners in the 2 nd house
 *             • if focus is **retro**  → partners in the 12 th house
 *   7      → partner(s) in the 7 th house
 *   3/11   → partners in the 3 rd & 11 th houses
 *
 * ─ Rahu (ரா) & Ketu (கே) are always retrograde.  
 *   Any planet whose speed in $planetDeg_default is < 0 is retrograde.
 * ─ Retro tags receive “(வ)” and are wrapped in
 *     <span class="retro"> … </span>.
 * ─ Every planet prints its **relative house number**:
 *     focus is always (1), partners show (2), (5), (9), etc.
 * ─ Tags in $exclude (default Lagna லக், Maandi மா) are ignored.
 *
 * @param string $focusTag          Planet to centre on (e.g. 'புத')
 * @param array  $planet_starlord   ['Tag' => [?, ?, <fullDeg>], …]
 * @param array  $planetDeg_default ['Tag' => <speed>, …]   (<0 = retro)
 * @param array  $exclude           Tags to skip entirely (default ['லக்','மா'])
 * @return array                    [
 *     'triad' => ['புத(வ)(1) + சனி(5)', …],
 *     '2/12'  => ['புத(வ)(1) + <span …>ரா(வ)(12)</span>', …],
 *     '7'     => [ … ],
 *     '3/11'  => [ … ],
 * ]
 * Buckets with no combinations are omitted.
 */
function buildPlanetCentricCombinations(
    string $focusTag,
    array  $planet_starlord,
    array  $planetDeg_default,
    array  $exclude = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"]
): array {
    $alwaysRetro = ['ரா', 'கே'];

    // Build planet info: [fullDeg, retro?, rasi]
    $info = [];
    foreach ($planet_starlord as $tag => $row) {
        if (!isset($row[2]) || !is_numeric($row[2])) continue;
        $full = fmod((float)$row[2] + 360, 360);
        $rasi = intdiv((int)$full, 30) + 1;
        $retro = in_array($tag, $alwaysRetro, true) ||
                 (isset($planetDeg_default[$tag]) && $planetDeg_default[$tag] < 0);
        $info[$tag] = [$full, $retro, $rasi];
    }

    if (!isset($info[$focusTag]) || in_array($focusTag, $exclude, true)) {
        return [];
    }

    [, $focusRetro, $focusRasi] = $info[$focusTag];

    // Display formatter
    $fmt = function(string $tag, bool $isRetro, int $rel): string {
        $txt = $tag . ($isRetro ? '(வ)' : '') . "($rel)";
        return $isRetro ? '<span class="retro">'.$txt.'</span>' : $txt;
    };

    $focusDisp = $fmt($focusTag, $focusRetro, 1);

    // Buckets
    $buckets = [
        'triad' => [],
        '2/12'  => [],
        '7'     => [],
        '3/11'  => [],
    ];

    // Loop and find partner planets
    foreach ($info as $tag => [$full, $retro, $rasi]) {
        if ($tag === $focusTag || in_array($tag, $exclude, true)) continue;

        $diff = ($rasi - $focusRasi + 12) % 12;
        $rel  = $diff + 1;
        $partner = $fmt($tag, $retro, $rel);

        // Triad (1, 5, 9)
        if (in_array($diff, [0, 4, 8], true) && $diff !== 0) {
            $buckets['triad'][] = "$focusDisp + $partner";
        }

        // 2nd rasi — only if focus is direct
        if ($diff === 1 && !$focusRetro) {
            $buckets['2/12'][] = "$focusDisp + $partner";
        }

        // 12th rasi — only if focus is retro
        if ($diff === 11 && $focusRetro) {
            $buckets['2/12'][] = "$focusDisp + $partner";
        }

        // 7th
        if ($diff === 6) {
            $buckets['7'][] = "$focusDisp + $partner";
        }

        // 3rd or 11th
        if (in_array($diff, [2, 10], true)) {
            $buckets['3/11'][] = "$focusDisp + $partner";
        }
    }

    return array_filter($buckets);
}


/*--------------------------------------------------------------------
  CONFIG
  ------------------------------------------------------------------*/
$allPlanets  = ['சூரி','சந்','செ','புத','குரு','சுக்','சனி','ரா','கே'];
$excludeTags = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"];                // ignore Lagna & Maandi
$alwaysRetro = ['ரா','கே'];                // Rahu & Ketu are retro
/*------------------------------------------------------------------*/

/* ------------ helpers ---------------- */
/* → now shows ( absolute‑rāśi no ) */
$fmt = function(string $tag, bool $retro, int $rasiNo): string {
    return $tag . ($retro ? '(வ)' : '') . "($rasiNo)";
};

/* ---------------------------------------------------------------
   CONFIG  (same as before)
-----------------------------------------------------------------*/
$allPlanets  = ['சூரி','சந்','செ','புத','குரு','சுக்','சனி','ரா','கே'];
$excludeTags = ['லக்', 'மா', "#கால", "#மிரு", "#உகே", "#வியா", "#இந்ர", "#அர்த", "#தூம", "#பரி"];
$alwaysRetro = ['ரா','கே'];

/* ---------------------------------------------------------------
   1) tiny formatter ⟶ now ONLY tag + '(வ)' when retro
-----------------------------------------------------------------*/
$fmt = fn(string $tag, bool $retro) => $retro ? "$tag(வ)" : $tag;

/* ---------------------------------------------------------------
   2) buildNaadi helper (unchanged)
-----------------------------------------------------------------*/
function buildNaadi(array $planet_starlord,
                   array $planetDeg_default,
                   array $alwaysRetro): array {
    $info = [];
    foreach ($planet_starlord as $tag => $row) {
        if (!isset($row[2]) || !is_numeric($row[2])) continue;
        $full  = fmod((float)$row[2] + 360, 360);
        $rasi  = intdiv((int)$full, 30) + 1;        // 1–12
        $retro = in_array($tag, $alwaysRetro, true) ||
                 (isset($planetDeg_default[$tag]) &&
                  $planetDeg_default[$tag] < 0);
        $info[$tag] = [$full, $retro, $rasi];
    }
    return $info;
}

/* ---------------------------------------------------------------
   3) combosForFocus  ➜ version WITHOUT rāśi numbers
      (exactly the one from my previous reply)
-----------------------------------------------------------------*/
/* -------------------------------------------------------------------
   ENEMY MAP  (feel free to adjust if you use English tags internally)
--------------------------------------------------------------------*/
$enemyMap = [
    'சூரி'=>['சுக்','ரா','கே','சனி'],
    'சந்' =>['சுக்','சனி','ரா','கே'],
    'செ'  =>['சனி','புத','ரா','கே'],
    'புத' =>['செ','ரா','கே'],
    'குரு'=>['ரா','கே'],
    'சுக்'=>['சூரி','சந்','ரா','கே'],
    'சனி'=>['செ','சந்','சூரி','ரா','கே'],
    'ரா' =>['சூரி','சந்','செ','புத','குரு','சுக்','சனி'],
    'கே' =>['சூரி','சந்','செ','புத','குரு','சுக்','சனி'],
];

/* -------------------------------------------------------------------
   HELPER – join tags inserting red “+” when adjacent tags are enemies
--------------------------------------------------------------------*/
function joinWithEnemyPlus(array $tags, array $enemyMap): string
{
	if (count($tags) < 2) return $tags[0] ?? '';

	/* strip off everything after '(' to get the plain tag */
	$plain = fn($s) => preg_replace('/\(.*$/u', '', strip_tags($s));

	$out = $tags[0];
	for ($i = 1; $i < count($tags); $i++) {
		$prev = $plain($tags[$i - 1]);
		$curr = $plain($tags[$i]);

		// $isEnemy = in_array($curr, $enemyMap[$prev] ?? [], true) ||
		//            in_array($prev, $enemyMap[$curr] ?? [], true);

		// $plus = $isEnemy ? '<span class="enemy">+</span>' : '+';
		$plus = '+';
		$out .= " $plus " . $tags[$i];
	}
	return $out;
}

/* -------------------------------------------------------------------
 * MAIN — combosForFocus() (REVERTED)
--------------------------------------------------------------------*/
function combosForFocus(
    string   $focus, // This parameter is now primarily for filtering, not the main focus of the logic
    array    $info,         // tag → [full°, retro?, absolute rāśi]
    array    $exclude,
    callable $fmtTag,       // ($tag,$isRetro) → display string
    array    $enemyMap
): array {

    $out = [
        '2nd'   => [],
        '12th'  => [],
        '6/10'  => [],
        '4/8'   => [],
        'trine_1_5_9' => [],
        'trine_2_6_10' => [],
        'trine_3_7_11' => [],
        'trine_4_8_12' => [],
    ];
    
    // Create a new, filtered array of planets and their details
    $planets = [];
    foreach ($info as $tag => $details) {
        if (!in_array($tag, $exclude, true)) {
            $planets[] = [
                'tag' => $tag,
                'full_deg' => $details[0],
                'retro' => $details[1],
                'rasi' => $details[2],
                'disp' => $fmtTag($tag, $details[1]) . " (" . round(fmod($details[0], 30), 2) . "°)",
            ];
        }
    }

    // Process all combinations for all planets at once
    foreach ($planets as $i => $focusPlanet) {
        
        $rasiPlanets = [];
        foreach ($planets as $partner) {
            if ($focusPlanet['tag'] === $partner['tag']) continue;
            $diff = ($partner['rasi'] - $focusPlanet['rasi'] + 12) % 12;
            $rasiPlanets[$diff][] = $partner;
        }

        // --- 2nd (NON-RETRO) / 12th (RETRO) ---
        if (!$focusPlanet['retro']) {
            if (isset($rasiPlanets[1])) {
                usort($rasiPlanets[1], fn($a, $b) => $a['full_deg'] <=> $b['full_deg']);
                $out['2nd'][] = joinWithEnemyPlus([$focusPlanet['disp'], $rasiPlanets[1][0]['disp']], $enemyMap);
            }
        } else {
            if (isset($rasiPlanets[11])) {
                usort($rasiPlanets[11], fn($a, $b) => $b['full_deg'] <=> $a['full_deg']);
                $out['12th'][] = joinWithEnemyPlus([$focusPlanet['disp'], $rasiPlanets[11][0]['disp']], $enemyMap);
            }
        }
        
        // --- 6/10 (NON-RETRO) ---
        if (!$focusPlanet['retro']) {
            $sixthAndTenth = [];
            if (isset($rasiPlanets[5])) $sixthAndTenth = array_merge($sixthAndTenth, $rasiPlanets[5]);
            if (isset($rasiPlanets[9])) $sixthAndTenth = array_merge($sixthAndTenth, $rasiPlanets[9]);
            
            if (!empty($sixthAndTenth)) {
                usort($sixthAndTenth, fn($a, $b) => $a['full_deg'] <=> $b['full_deg']);
                $out['6/10'][] = joinWithEnemyPlus([$focusPlanet['disp'], $sixthAndTenth[0]['disp']], $enemyMap);
            }
        }

        // --- 4/8 (RETRO) ---
        if ($focusPlanet['retro']) {
            $fourthAndEighth = [];
            if (isset($rasiPlanets[3])) $fourthAndEighth = array_merge($fourthAndEighth, $rasiPlanets[3]);
            if (isset($rasiPlanets[7])) $fourthAndEighth = array_merge($fourthAndEighth, $rasiPlanets[7]);
            
            if (!empty($fourthAndEighth)) {
                usort($fourthAndEighth, fn($a, $b) => $b['full_deg'] <=> $a['full_deg']);
                $out['4/8'][] = joinWithEnemyPlus([$focusPlanet['disp'], $fourthAndEighth[0]['disp']], $enemyMap);
            }
        }
    }

    // --- ALL TRINES (UNCHANGED) ---
    $trineGroups = [
        'trine_1_5_9' => [1, 5, 9],
        'trine_2_6_10' => [2, 6, 10],
        'trine_3_7_11' => [3, 7, 11],
        'trine_4_8_12' => [4, 8, 12],
    ];
    $rasiToTrineGroup = [];
    foreach ($trineGroups as $key => $signs) {
        foreach ($signs as $sign) {
            $rasiToTrineGroup[$sign] = $key;
        }
    }
    $trineCollections = [
        'trine_1_5_9' => [], 'trine_2_6_10' => [],
        'trine_3_7_11' => [], 'trine_4_8_12' => [],
    ];
    foreach ($planets as $p) {
        if (isset($rasiToTrineGroup[$p['rasi']])) {
            $trineKey = $rasiToTrineGroup[$p['rasi']];
            $trineCollections[$trineKey][] = ['tag' => $p['disp'], 'deg' => fmod($p['full_deg'], 30)];
        }
    }
    foreach ($trineCollections as $key => $planets) {
        if (!empty($planets)) {
            usort($planets, fn($a, $b) => $a['deg'] <=> $b['deg']);
            $tags = array_column($planets, 'tag');
            $out[$key] = [joinWithEnemyPlus($tags, $enemyMap)];
        }
    }
    
    // Remove duplicates from the final output lists
    foreach ($out as $key => $values) {
        if (is_array($values)) {
            $out[$key] = array_unique($values);
        }
    }

    return array_filter($out);
}


function updateUpaGraha($sun_degree)
{
	global $planetDeg, $sw_planet, $planetDeg_default;
	global $datetime, $today_sunrise, $today_sunset;

	// Normalize degrees to 0–360
	$norm = fn($deg) => fmod(($deg + 360.0), 360.0);

	// Step 1: Calculate all Aprakasha Upagraha positions
	$dhuma       = $norm($sun_degree + 120 + (3 + 20/60));       // Sun + 120° + 3°20'
	$vyatipata   = $norm(360 - $dhuma);
	$parivesha   = $norm($dhuma + 180);
	$indrachapa  = $norm(360 - $parivesha);
	$upaketu     = $norm($sun_degree + (16 + 40/60));            // Sun + 16°40'

	// Step 2: Map with Tamil names
	$upa_list = [
		"#தூம" => $dhuma,
		"#வியா" => $vyatipata,
		"#பரி" => $parivesha,
		"#இந்ர" => $indrachapa,
		//"#உk" => $upaketu
		"#உகே" => $upaketu
	];

	foreach ($upa_list as $upa_name => $deg) {
		$sign_index = floor($deg / 30); // 0 to 11
		$sw_planet[$sign_index] .= ($sw_planet[$sign_index] ? " + " : "") . $upa_name;

		// Store degree in 0–30 format within sign
		$planetDeg[$upa_name] = round(fmod($deg, 30.0),4);
		$planetDeg_default[$upa_name] = fmod($deg, 30.0);
	}

	// ------------------ Ardhaprahara, Kala, Mrityu ------------------

	$birth_time = strtotime($datetime);
	$sunrise    = strtotime($today_sunrise);
	$sunset     = strtotime($today_sunset);

	$is_daytime = ($birth_time >= $sunrise && $birth_time < $sunset);
	$start_time = $is_daytime ? $sunrise : $sunset;
	$end_time   = $is_daytime ? $sunset : strtotime('+1 day', $sunrise);

	$total_secs = $end_time - $start_time;
	$half_secs  = $total_secs / 2;

	$ardha_time   = $start_time + $half_secs;
	$kala_time    = $start_time + ($half_secs / 2);
	$mrityu_time  = $start_time + ($half_secs * 1.5);

	$timeToDeg = function($time, $sunrise, $sunset, $sun_start_deg) use ($norm) {
		$total = strtotime($sunset) - strtotime($sunrise);
		$elapsed = $time - strtotime($sunrise);
		$frac = $elapsed / $total;
		$deg = $norm($sun_start_deg + ($frac * 180));
		return $deg;
	};

	$ardha_deg   = $timeToDeg($ardha_time, $today_sunrise, $today_sunset, $sun_degree);
	$kala_deg    = $timeToDeg($kala_time, $today_sunrise, $today_sunset, $sun_degree);
	$mrityu_deg  = $timeToDeg($mrityu_time, $today_sunrise, $today_sunset, $sun_degree);

	$kala_list = [
		"#அர்த" => $ardha_deg,
		"#கால"   => $kala_deg,
		"#மிரு"   => $mrityu_deg
	];

	foreach ($kala_list as $upa_name => $deg) {
		$sign_index = floor($deg / 30);
		$sw_planet[$sign_index] .= ($sw_planet[$sign_index] ? " + " : "") . $upa_name;

		$planetDeg[$upa_name] = round(fmod($deg, 30.0),4);
		$planetDeg_default[$upa_name] = fmod($deg, 30.0);
	}

	asort($planetDeg);

	return $sw_planet;
}

function calculateBriguCombination($planet_starlord, $planetDeg_default, $alwaysRetro, $allPlanets, $excludeTags, $fmt, $enemyMap) {
	$planetInfo = buildNaadi($planet_starlord, $planetDeg_default, $alwaysRetro);

	// Usage:
	$allCombos = calculateBriguCombination($planet_starlord, $planetDeg_default, $alwaysRetro, $allPlanets, $excludeTags, $fmt, $enemyMap);

	// Prepare an array to hold the single set of combinations
	$allCombos = [
		'trine_1_5_9' => [],
		'trine_2_6_10' => [],
		'trine_3_7_11' => [],
		'trine_4_8_12' => [],
		'2nd' => [],
		'12th' => [],
		'6/10' => [],
		'4/8' => [],
	];

	// Iterate through each planet to find its combinations
	foreach ($allPlanets as $pl) {
		$combo = combosForFocus($pl, $planetInfo, $excludeTags, $fmt, $enemyMap);

		// Merge the individual planet's combinations into the main array
		foreach ($allCombos as $k => $v) {
			if (isset($combo[$k])) {
				$allCombos[$k] = array_merge($allCombos[$k], $combo[$k]);
			}
		}
	}

	// Remove duplicates from the combined lists
	foreach ($allCombos as $k => $v) {
		$allCombos[$k] = array_unique($v);
	}

	createBriguDiv($allCombos);

	return $allCombos;
}

function createBriguDiv($allCombos) {
	/* ========= OVERLAY ============================================ */
	echo <<<HTML
<div id="astroModal" class="astro-modal">
	<div class="astro-modal-content">
		<span id="closeNadi" class="astro-close">&times;</span>
		<div class="bucket-container">
HTML;

	/* Loop through the final, combined combinations and display them */
	foreach ([
		'trine_1_5_9' => 'திரிகோண குழு 1,5,9',
		'trine_2_6_10' => 'திரிகோண குழு 2,6,10',
		'trine_3_7_11' => 'திரிகோண குழு 3,7,11',
		'trine_4_8_12' => 'திரிகோண குழு 4,8,12',
		'2nd' => '2ஆம் ராசி',
		'12th' => '12ஆம் ராசி',
		'6/10' => '6/10ஆம் ராசி',
		'4/8' => '4/8ஆம் ராசி',
	] as $key => $label) {
		echo "<div class='bucket'><div class='bucket-label'>$label:</div>";
		if (!empty($allCombos[$key])) {
			foreach ($allCombos[$key] as $combo) {
				echo "<div class='combo-line'>".trim($combo)."</div>";
			}
		} else {
			echo "<div class='combo-line'>none</div>";
		}
		echo "</div>";
	}
	echo <<<HTML
		</div>
	</div>
</div>
HTML;
}

function displayBriguCombination() {
	global $planet_starlord, $planetDeg_default, $alwaysRetro, $allPlanets, $excludeTags, $fmt, $enemyMap;
	$output = '';

	$allCombos = buildNaadiCombinations($planet_starlord, $planetDeg_default, $alwaysRetro, $allPlanets, $excludeTags, $fmt, $enemyMap);

	$firstElement = '';
	$firstSet = true;

	foreach (['trine_1_5_9', 'trine_2_6_10', 'trine_3_7_11', 'trine_4_8_12'] as $key) {
		$items = array_map(function($v) {
			return str_replace('+', ' ', $v);
		}, $allCombos[$key]);
		foreach ($items as $item) {
			$parts = explode(' ', $item);
			foreach ($parts as $part) {
				if (empty($part)) continue;
				if (strpos($part, '°') !== false) continue;
				$has_retro = (mb_substr(trim($part), -3) === '(வ)');
				if ($firstSet && empty($firstElement)) {
					$part = __($part);
					$firstElement = "<div style='display:inline-block;text-align:center;'>" .
						($has_retro
							? "<span style='font-size:10px;display:block;color:red;'>&#8592;</span><span style='color:red;'>$part</span>"
							: "<span style='font-size:10px;display:block;'>&#8594;</span><span style='font-size:10px;'>$part</span>"
						) .
						"</div>";
				}
				if ($has_retro) {
					$arrow = "<span style='color:red;'>&#8592;</span>";
					// Remove (வ) from the part before displaying
					$clean_part = str_replace('(வ)', '', $part);
					// translate/map tokens when UI is English
					$display_clean = $clean_part;
					if (is_english()) {
						$display_clean = __($display_clean);
					}
					$output .= "<div style='display:inline-block;text-align:center;'>
						<span style='font-size:10px;display:block;color:red;'>$arrow</span>
						<span style='color:red;font-size:10px'>" . $display_clean . "</span>
					  </div>";
				} else {
					$arrow = '&#8594;';
					$display_part = $part;
					if (is_english()) {
						$display_part = __($display_part);
					}
					$output .= "<div style='display:inline-block;text-align:center;'>
						<span style='font-size:10px;display:block;'>$arrow</span>
						<span style='font-size:10px'>" . $display_part . "</span>
					  </div>";
				}
			}
			$firstSet = false;
		}
	}

	// Add the first element once after the loop
	$output .= str_replace('(வ)', '', $firstElement);

	echo "<div class='divBNN'>";
	echo "<div class='bnn-title'>" . __('bnn_title') . "</div>";
	echo "<div style='display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;'>";
	echo $output;
	echo "</div>";
	echo "</div>";
}

function buildNaadiCombinations($planet_starlord, $planetDeg_default, $alwaysRetro, $allPlanets, $excludeTags, $fmt, $enemyMap) {
	// ============= MAIN BUILD ========================================
	$planetInfo = buildNaadi($planet_starlord, $planetDeg_default, $alwaysRetro);

	// Prepare an array to hold the single set of combinations
	$allCombos = [
		'trine_1_5_9' => [],
		'trine_2_6_10' => [],
		'trine_3_7_11' => [],
		'trine_4_8_12' => [],
		'2nd' => [],
		'12th' => [],
		'6/10' => [],
		'4/8' => [],
	];

	// Iterate through each planet to find its combinations
	foreach ($allPlanets as $pl) {
		$combo = combosForFocus($pl, $planetInfo, $excludeTags, $fmt, $enemyMap);

		// Merge the individual planet's combinations into the main array
		foreach ($allCombos as $k => $v) {
			if (isset($combo[$k])) {
				$allCombos[$k] = array_merge($allCombos[$k], $combo[$k]);
			}
		}
	}

	// Remove duplicates from the combined lists
	foreach ($allCombos as $k => $v) {
		$allCombos[$k] = array_unique($v);
	}

	/* This section is to display all combinations in a div 
	// ========= OVERLAY ============================================
	echo <<<HTML
	<div id="astroModal" class="astro-modal">
		<div class="astro-modal-content">
			<span id="closeNadi" class="astro-close">&times;</span>
			<div class="bucket-container">
	HTML;

	// Loop through the final, combined combinations and display them
	foreach ([
		'trine_1_5_9' => 'திரிகோண குழு 1,5,9',
		'trine_2_6_10' => 'திரிகோண குழு 2,6,10',
		'trine_3_7_11' => 'திரிகோண குழு 3,7,11',
		'trine_4_8_12' => 'திரிகோண குழு 4,8,12',
		'2nd' => '2ஆம் ராசி',
		'12th' => '12ஆம் ராசி',
		'6/10' => '6/10ஆம் ராசி',
		'4/8' => '4/8ஆம் ராசி',
	] as $key => $label) {
		echo "<div class='bucket'><div class='bucket-label'>$label:</div>";
		if (!empty($allCombos[$key])) {
			foreach ($allCombos[$key] as $combo) {
				echo "<div class='combo-line'>" . trim($combo) . "</div>";
			}
		} else {
			echo "<div class='combo-line'>none</div>";
		}
		echo "</div>";
	}
	echo <<<HTML
			</div>
		</div>
	</div>
	HTML;
*/
	// Usage: return $allCombos if needed
	return $allCombos;
}

?>

