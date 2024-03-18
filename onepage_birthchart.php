<?php
include_once "lib.php";

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$in_place = $_GET["place"];
$in_name = $_GET["name"];
$in_sex = $_GET["sex"];

$place=strtok($in_place,"|");
$TZ=strtok("|");

$g_rowid = $_GET["rowid"];

$father_name=$_POST['father'];
$mother_name=$_POST['mother'];
$caste=$_POST['caste'];
$office_name=$_POST['office_name'];

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$lat="";
$lon="";
$gmt_offset="";
$tz_offset;
$country="";
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
$g_thithi_soonyam_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$g_thithi_soonyam_matching_arr=array();
$g_dasaBalance=array();
$g_dasaCurrent=array();
$g_nak = "";
$g_karan = "";
$current_dasa_bukthi=array();
$yogi_arr=array();
$g_pancha_pakshi="";
$g_thithi="";
$g_paksham="";
$planet_starlord_transit=array();
$planet_starlord=array();
$g_navamsam_arr=array('','','','','','','','','','','','','');
$g_bhava_arr = array();
$saveMode=0;
$saveBtnTxt='Save';
$g_planetKaranaLord=array();
$g_thithi_idx=-1;
$otherPanchangDetails = array();
$g_dasa_arr = array();
$g_bukthi_arr = array();
$g_current_dasa_lord = "";
$g_lagnam="";
$g_rasi="";
$g_nak_idx=-1;
$g_ucha_details="";
$g_neecha_details="";
$g_mudakku_detail="";
?>

<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Astrology">
	
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/onepage_birth_style.css">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
   
   <script>
			
		
		/*window.addEventListener('load', function () {
			setTimeOut(window.close(),5000);
		});*/
		
   </script>

</head>

<?php



// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_time = $_GET["nTime"];
$chart_date = $_GET["nDate"];

$dayofweek = date('w', strtotime($chart_date));

?>

<?php

function isFilled($str)
{
	if(isset($str)) {
		echo "already has value";
		return 1;
	}
	else {
		return 0;
	}
}

function isPushkara($patham)
{	
	echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold'>Thithi Soonyam/திதி சூன்யம்</H6>";
					echo "</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchang'>$thithi_arr[$thithi]</div>";
		echo "</div>";
	echo "</div></center>";
}


function updatePlanet_transit(array $planets,array $retro, $in_ragu_house)
{
	global $DEBUG,$planetDeg_transit;
	
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
	
	$planetDeg_transit['கே']=$planetDeg_transit['ரா'];
	asort($planetDeg_transit);
	
	return $out;
}

function updateMandhi_transit(array $out, array $in, $in_sun_degree, $in_day, $in_dayofweek,$in_sun_house)
{	
	global $transit_datetime,$transit_today_sunrise, $planetDeg_transit,$mandhi_degree_transit, $planetDeg_default;
	
	if(strtotime($transit_datetime) < strtotime($transit_today_sunrise)){
		$in_dayofweek = $in_dayofweek - 1;
		
		if($in_dayofweek < 0){
			$in_dayofweek = 6;
		}
	}
	
	$mandhi_house = targetHouse($in_sun_house,findMandhiHouse_transit($in_dayofweek,$in_sun_degree, $in_day));
	
	debug($mandhi_house);
	
	foreach ($in as $key => $value) {
		//mandhi house update
		if($key == $mandhi_house){
			$out[$key] = $out[$key] . " மா";
			
			$planetDeg_transit["மா"] = ($mandhi_degree_transit % 30);
			$planetDeg_default["மா"] = ($mandhi_degree_transit % 30);
		}
	}
	
	asort($planetDeg_transit);
	
	return $out;
}



function displayDegree_transit($in_sw_degree)
{
	global $out_planet_degree, $planetDeg_default, $planetDeg_transit;
	
	$txt="";
	$planet="";
	
	foreach ($planetDeg_transit as $key => $val) {
			$out_planet_degree[$key] = $val;
			
			$txt = $txt . " " . $key . "-" . $val;
			
			$planetDeg_default[$planet] = $val;
	}
	
	return $txt;
}


function findMandhiHouse_transit($in_dayofweek,$in_sun_degree,$in_day)
{
	global $mandhi_degree_transit;
	
	switch ($in_dayofweek) {
		case 0: //Sun
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+156;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+240;
			}
			break;
		case 1: //Mon
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+132;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+216;
			}
			break;
		case 2: //Tue
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+108;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+192;
			}
			break;
		case 3: //Wed
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+84;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+336;
			}
			
			debug ("(((((((((".$mandhi_degree_transit."))))))))");
			break;
		case 4: //Thur
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+60;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+312;
			}
			break;
		case 5: //Fri
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+36;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+288;
			}
			break;
		case 6:  //Sat
			if($in_day==1){
				$mandhi_degree_transit=$in_sun_degree+12;
			}
			else{
				$mandhi_degree_transit=$in_sun_degree+264;
			}
			break;
	  default:
		break;
	}
	
	$mandhi_house = (int)(($mandhi_degree_transit % 360)/30);
	
	debug("FINDMANDHI Transit - WEEK DAY=" . $in_dayofweek);
	debug("FINDMANDHI Transit - DAYTIME=" . $in_day);
	debug("FINDMANDHI Transit - MANDHI DEGREE=" . ($mandhi_degree_transit % 30));
	debug("FINDMANDHI Transit - SUN DEGREE=" . $in_sun_degree);
	debug("FINDMANDHI Transit - MANDHI DEGREE FROM SUN=" . $mandhi_degree_transit);
	debug("FINDMANDHI Transit - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$tr_sw_degree["mandhi"] = DECtoDMS($mandhi_degree_transit % 30);
	
	return $mandhi_house;
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
		$planetDeg_transit[trim($row[0])] = round(fmod(floatval($row[1]), 30.0),2);		
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


function old_sortDegree($inArray)
{
	global $planetDeg,$mandhi_degree;
	
	foreach ($planetDeg as $planet => $deg) {
		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg[$planet],$inArray[$house]);
			$inArray[$house] = str_replace($planet, $planet . "-". $planetDeg[$planet],$inArray[$house]);
				
			if(strpos($planet,"ரா")===0 and strpos($inPlanet,"கே")===0){
				//$inArray[$house] = str_replace($inPlanet, trim($inPlanet). "." . $planetDeg[$planet],$inArray[$house]);
				$inArray[$house] = substr_replace($inArray[$house], "-".$planetDeg[$planet],6,0);
				
			}
		}
	}
	
	$inArray = str_replace("மா", "மா". "-" . strval($mandhi_degree % 30.0),$inArray);
	
	return $inArray;
}


function getTargetNak($inStar, $in_count){		
	$star_arr = array("அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி");
		
	$start_pos = array_search($inStar,$star_arr);
	$target_pos = $start_pos+$in_count;
	
	/*
	if($start_pos > 1 && $start_pos>$target_pos){
		$target_star = $star_arr[27-$start_pos+$target_pos+1];
	}
	else{
		$target_star = $star_arr[$target_pos-$start_pos+1];
	}*/
	
	if($target_pos > 27){
		$final_pos = $target_pos - 27 - 1;
	}
	else{
		$final_pos = $target_pos - 1 ;
	}
	
	debug("start_pos=$start_pos   target_pos=$target_pos   final_pos=$final_pos");
	
	return $star_arr[$final_pos];
}

//This displays star lord button for transit planets
function displayTransitStartLordButton()
{	
	$class_name = 'actionBtnTransit';
	$starlord_function = 'showStarLordTable_transit()';

	echo "<center><div class='divTable blueTable' style='height:auto;' id='buttons'>"; 
		echo "<div class='divTableRow'>";
			echo <<<EOL
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="starlord" name="starlord" onclick="$starlord_function">கோட் நட் சாரம்</button>
				</div>
				<div class="divMenu" style="width:50%">
					<button type="button" class='$class_name' id="navamsam" name="navamsam" onclick="showNavamsam()">நவாம்சம்</button>
				</div>
EOL;
		echo "</div>";
	echo "</div>";
	
	displayStarLard_transit();
}

function displayStarLard_transit()
{
	global $planetDeg_transit,$tr_sw_planet, $planet_starlord_transit;
	
	$planet_starlord_transit = array();
	
	$nak_array = array("dummy/dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
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
					$planet_starlord_transit[$planet][1] = $nak_lord[$starlord_idx]."(புஷ்)";
				}
				else{
					$planet_starlord_transit[$planet][1] = $nak_lord[$starlord_idx];
				}
				
				$planet_starlord_transit[$planet][2] = $full_planet_deg;
				$planet_starlord_transit[$planet][3] = $deg;
			}
			
			if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
			{
				
				//$planet_starlord_transit["கே"][0] = $nak_array[$starlord_idx];
				$planet_starlord_transit["கே"][0] = explode('/',$nak_array[$starlord_idx])[1];  //display only tamil
				//$planet_starlord["ரா"][0] = $nak_array[$starlord_idx_ragu];
				$planet_starlord["ரா"][0] = explode('/',$nak_array[$starlord_idx_ragu])[1]; //display tamil star name
				
				if($starlord_idx==7 || $starlord_idx==9 || $starlord_idx==12 || $starlord_idx==14 || $starlord_idx==24 || $starlord_idx==26 || $starlord_idx==28 || 
				$starlord_idx==30 || $starlord_idx==43 || $starlord_idx==45 || $starlord_idx==48 || $starlord_idx==50 || $starlord_idx==60 || $starlord_idx==62 || 
				$starlord_idx==64 || $starlord_idx==66 || $starlord_idx==79 || $starlord_idx==81 || $starlord_idx==84 || $starlord_idx==86 || $starlord_idx==96 || 
				$starlord_idx==98 || $starlord_idx==100 || $starlord_idx==102){
					$planet_starlord_transit["கே"][1] = $nak_lord[$starlord_idx]."(புஷ்)";
					$planet_starlord_transit["ரா"][1] = $nak_lord[$starlord_idx_ragu]."(புஷ்)";
				}
				else{
					$planet_starlord_transit["கே"][1] = $nak_lord[$starlord_idx];
					$planet_starlord_transit["ரா"][1] = $nak_lord[$starlord_idx_ragu];
				}
				
				$planet_starlord["ரா"][2] = ($full_planet_deg > 180) ? $full_planet_deg-180 : $full_planet_deg;
				$planet_starlord["ரா"][3] = $deg;
				$planet_starlord_transit["கே"][2] = $full_planet_deg;
				$planet_starlord_transit["கே"][3] = $deg;
			}
		}
	}

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable_boy' style='height:32px;display:none'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold;color:black'>கோட் நட் சாரம்</H6>";
					echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable_boy' style='height:100%;width:360px;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divTableCell'>Planet</div>";
				echo "<div class='divTableCell'>Star Padam</div>";
				echo "<div class='divTableCell'>Star lord</div>";
				echo "<div class='divTableCell'>Full degree</div>";
				echo "<div class='divTableCell'>Rasi degree</div>";
		echo "</div>";
	
	foreach($planet_starlord_transit as $planet => $data)
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

function prepareBukthiTable(){
	global $dasa_lord, $g_dasaCurrent, $g_bukthi_arr;
	$bukthi_arr = array();
	
	$dasa_lord = explode('-',$g_dasaCurrent[0])[0];
	$dasa_lord = $dasa_lord . "&#8594;";

	foreach($g_bukthi_arr as $bukthi => $data){
	if(mb_strpos($bukthi,$dasa_lord)===0){
			$bukthi_arr[$bukthi] = $data;
		}
	}
	
	return $bukthi_arr;
}


function displayDetailsCol1()
{
	global $name, $birth_date, $birth_time, $sex, $g_lagnam, $g_rasi, $g_dasaBalance, $g_dasaCurrent, $place, $father_name,
			$mother_name,$caste,$g_age, $g_vayadhotta_lagna, $g_rowid;
	
	echo <<<EOL
	
			<div class="divTableRow">
				<div class='divTableTextHeading'>பெயர்</div><div class='divTableTextMedium'>$name ($g_rowid)</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>தந்தை பெயர்</div><div class='divTableTextMedium'>$father_name</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>தாய் பெயர்</div><div class='divTableTextMedium'>$mother_name</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிரிவு</div><div class='divTableTextMedium'>$caste</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பாலினம்</div><div class='divTableTextMedium'>$sex</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த தேதி</div><div class='divTableTextMedium'>$birth_date</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த நேரம்</div><div class='divTableTextMedium'>$birth_time</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த இடம்</div><div class='divTableTextMedium'>$place</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>வயது</div><div class='divTableTextMedium'>$g_age</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>லக்னம்</div><div class='divTableTextMedium'>$g_lagnam </div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>வயதோட்ட லக்னம்</div><div class='divTableTextMedium'>$g_vayadhotta_lagna</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>ராசி</div><div class='divTableTextMedium'>$g_rasi</div>
			</div>
EOL;
}

function displayDetailsCol3()
{
	global $panchang, $LANG, $karan, $yogi, $ayogi, $g_thithi_soonyam , $g_pancha_pakshi, $g_dasaBalance, $g_dasaCurrent, $g_sunrise, $g_sunset, $moon_house,
		$g_ucha_details, $g_neecha_details, $g_mudakku_detail,$lat, $lon, $gmt_offset, $TZ;
	
	$vaaram = getStr($panchang['vaaram'],$LANG);
	$nak = getStr($panchang['nak'],$LANG);
	$thithi = getStr($panchang['thithi'],$LANG);
	$yogam	= getStr($panchang['yoga'],$LANG);
	$karan = getStr($karan,$LANG);
	
	$chadrashtamam = getRasi(targetHouse($moon_house-1,8));
	
	echo <<<EOL
		<div class="divTableRow">
			<div class='divTableTextHeading'>உச்சம்</div><div class='divTableTextMedium'>$g_ucha_details</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>நீசம்</div><div class='divTableTextMedium'>$g_neecha_details</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>உங்கள் சந்திராஷ்டம ராசி</div><div class='divTableTextMedium'>$chadrashtamam</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>அவயோகி</div><div class='divTableTextMedium'>$ayogi</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>திதி சூன்யம்</div><div class='divTableTextMedium'>$g_thithi_soonyam</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>முடக்கு</div><div class='divTableTextMedium'>$g_mudakku_detail</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>சூரிய உதயம்</div><div class='divTableTextMedium'>$g_sunrise</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>சூரிய அஸ்தமனம்</div><div class='divTableTextMedium'>$g_sunset</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>அட்சரேகை/தீர்க்கரேகை</div><div class='divTableTextMedium'>$lat/$lon</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>நேரம் மண்டலம்</div><div class='divTableTextMedium'>$gmt_offset ($TZ)</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>அயனாம்சம்</div><div class='divTableTextMedium'>லஹிரி</div>
		</div>
EOL;
}

function displayDetailsCol2()
{
	global $panchang, $LANG, $karan, $yogi, $ayogi, $g_thithi_soonyam, $g_pancha_pakshi, $g_mudakku_detail,$g_dasaBalance,$g_dasaCurrent  ;
	
	$vaaram = getStr($panchang['vaaram'],$LANG);
	$nak = getStr($panchang['nak'],$LANG);
	$thithi = getStr($panchang['thithi'],$LANG);
	$yogam	= getStr($panchang['yoga'],$LANG);
	$karan = getStr($karan,$LANG);
	
	echo <<<EOL
	
		<div class="divTableRow">
			<div class='divTableTextHeading'>வாரம்</div><div class='divTableTextMedium'>$vaaram</div>
		</div>
		
		<div class="divTableRow">
			<div class='divTableTextHeading'>திதி</div><div class='divTableTextMedium'>$thithi</div>
		</div>
		
		<div class="divTableRow">
			<div class='divTableTextHeading'>நட்சத்திரம்</div><div class='divTableTextMedium'>$nak</div>
		</div>
		
		<div class="divTableRow">
			<div class='divTableTextHeading'>யோகம்</div><div class='divTableTextMedium'>$yogam</div>
		</div>
		
		<div class="divTableRow">
			<div class='divTableTextHeading'>கரணம்</div><div class='divTableTextMedium'>$karan</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>யோகி</div><div class='divTableTextMedium'>$yogi</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>பஞ்ச பக்ஷி</div><div class='divTableTextMedium'>$g_pancha_pakshi</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>பிறப்பு தசா இருப்பு</div><div class='divTableTextMedium'>$g_dasaBalance</div>
		</div>
		<div class="divTableRow">
			<div class='divTableTextHeading'>தற்போதைய தசா புக்தி</div><div class='divTableTextMedium'>$g_dasaCurrent</div>
		</div>
EOL;
}



function displayDasa()
{
	global $dasa;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>தசா அட்டவணை</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>கிரகம்</div>
			<div class='divTableTextHeading'>ஆரம்பம்</div>
			<div class='divTableTextHeading'>முடிவு</div>
		</div>
EOL;
	
	foreach($dasa as $planet => $data){
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$planet</div>
			<div class="divTableTextMedium">$data[0]</div>
			<div class="divTableTextMedium">$data[1]</div>
		</div>
EOL;
	}
	
	echo '</div>';
}

function displayBukthi()
{
	global $bukthi_table;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>தசா/புக்தி அட்டவணை</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>தசா/புக்தி</div>
			<div class='divTableTextHeading'>ஆரம்பம்</div>
			<div class='divTableTextHeading'>முடிவு</div>
		</div>
EOL;
	
	foreach($bukthi_table as $key => $data){
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$key</div>
			<div class="divTableTextMedium">$data[1]</div>
			<div class="divTableTextMedium">$data[2]</div>
		</div>
EOL;
	}
	
	echo '</div>';
}

function displayStarLord()
{
	global $starlord_arr, $planetKaranaLord;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>நட் சாரம்</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>கிரகம்</div>
			<div class='divTableTextHeading'>நட் பாதம்</div>
			<div class='divTableTextHeading'>நட் அதிபதி</div>
			<div class='divTableTextHeading'>பாகை</div>
			<div class='divTableTextHeading'>கரணம்</div>
		</div>
	
EOL;
	
	foreach($starlord_arr as $idx => $data){
		
	$deg = DECtoDMS($data[2]);
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$idx</div>
			<div class="divTableTextMedium">$data[0]</div>
			<div class="divTableTextMedium">$data[1]</div>
			<div class="divTableTextMedium">$deg</div>
			<div class="divTableTextMedium">$planetKaranaLord[$idx]</div>
		</div>
EOL;
	}
	echo '</div>';
}

function displayChart($in,$out)
{

echo <<<EOL
	<div class="divTable blueTable" id="chart">
		
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[11]</div>
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

function displayChart1()
{
	global $sw_planet, $g_thithi_soonyam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="chart">
						
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableCell" style='border-right:0px; border-bottom:0px'>$sw_planet[11] $g_thithi_soonyam_arr[11]</div>
					<div class="divTableCell"  style='border-right:0px;border-bottom:0px'>$sw_planet[0] $g_thithi_soonyam_arr[0]</div>
					<div class="divTableCell"  style='border-right:0px;border-bottom:0px'>$sw_planet[1] $g_thithi_soonyam_arr[1]</div>
					<div class="divTableCell" style='border-bottom:0px;'>$sw_planet[2] $g_thithi_soonyam_arr[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-bottom:0px;border-right:0px'>$sw_planet[10] $g_thithi_soonyam_arr[10]</div>
					<div class="divTableCenterCell" style='border-left:1px solid;border-top:1px solid'>ராசி</div>
					<div class="divTableCenterCell" style='border-top:1px solid;border-right:0px'></div>
					<div class="divTableCell" style='border-left:1px solid;border-bottom:0px'>$sw_planet[3] $g_thithi_soonyam_arr[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-bottom:0px;border-right:0px'>$sw_planet[9] $g_thithi_soonyam_arr[9]</div>
					<div class="divTableCenterCell" style='border-left:1px solid;border-bottom:0px'></div>
					<div class="divTableCenterCell" style='border-right:0px;border-bottom:0px'>D1</div>
					<div class="divTableCell" style='border-bottom:0px; border-left:1px solid'>$sw_planet[4] $g_thithi_soonyam_arr[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-right:0px'>$sw_planet[8] $g_thithi_soonyam_arr[8]</div>
					<div class="divTableCell" style='border-right:0px;border-top:1px solid'>$sw_planet[7] $g_thithi_soonyam_arr[7]</div>
					<div class="divTableCell" style='border-right:0px;border-top:1px solid'>$sw_planet[6] $g_thithi_soonyam_arr[6]</div>
					<div class="divTableCell" style='border-left:1px solid'>$sw_planet[5] $g_thithi_soonyam_arr[5]</div>
			</div>
		</div>	
	</div>
EOL;
}

function displayNavamsam()
{
	global $g_navamsam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="d9_chart">
		
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableCell" style='border-right:0px; border-bottom:0px'>$g_navamsam_arr[11]</div>
					<div class="divTableCell" style='border-right:0px;border-bottom:0px'>$g_navamsam_arr[0]</div>
					<div class="divTableCell" style='border-right:0px;border-bottom:0px'>$g_navamsam_arr[1]</div>
					<div class="divTableCell" style='border-bottom:0px;'>$g_navamsam_arr[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-bottom:0px;border-right:0px'>$g_navamsam_arr[10]</div>
					<div class="divTableCenterCell" style='border-left:1px solid;border-top:1px solid'>நவாம்சம்</div>
					<div class="divTableCenterCell" style='border-top:1px solid;border-right:0px'></div>
					<div class="divTableCell" style='border-left:1px solid;border-bottom:0px'>$g_navamsam_arr[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-bottom:0px;border-right:0px'>$g_navamsam_arr[9]</div>
					<div class="divTableCenterCell" style='border-left:1px solid;border-bottom:0px'></div>
					<div class="divTableCenterCell" style='border-right:0px;border-bottom:0px'>D9</div>
					<div class="divTableCell" style='border-bottom:0px; border-left:1px solid'>$g_navamsam_arr[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell" style='border-right:0px'>$g_navamsam_arr[8]</div>
					<div class="divTableCell" style='border-right:0px;border-top:1px solid'>$g_navamsam_arr[7]</div>
					<div class="divTableCell" style='border-right:0px;border-top:1px solid'>$g_navamsam_arr[6]</div>
					<div class="divTableCell" style='border-left:1px solid'>$g_navamsam_arr[5]</div>
			</div>
		</div>	
	</div>

EOL;
}

$conn = dbConnect($conn);

startSqlTxn($conn);

if($DEBUG==0){
	try {
		$sql="CREATE TEMPORARY TABLE `input_data` (
		  `in_sunrise` datetime NOT NULL,
		  `in_sunset` datetime NOT NULL,
		  `in_sunrise_tom` datetime NOT NULL,
		  `in_sunset_prev` datetime NOT NULL,
		  `in_chart_date` date NOT NULL,
		  `in_chart_time` datetime NOT NULL,
		  `in_place` varchar(20) NOT NULL,
		  `in_timezone` time NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

		if($res=mysqli_query($conn, $sql)){
			if($DEBUG==1){ echo "CREATE TEMPORARY TABLE executed"; }
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);	
	}catch (mysqli_sql_exception $exception) {
		mysqli_rollback($mysqli);

		throw $exception;
	}	

	try {
		$sql="CREATE TEMPORARY TABLE `temp_data` (
		  `house` tinyint(4) NOT NULL,
		  `outside` varchar(20) DEFAULT NULL,
		  `inside` varchar(250) DEFAULT NULL,
		  `start_time` datetime NOT NULL,
		  `end_time` datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


		if($res=mysqli_query($conn, $sql)){
			if($DEBUG==1){ echo "CREATE TEMPORARY TABLE executed"; }
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);	
	}catch (mysqli_sql_exception $exception) {
		mysqli_rollback($mysqli);

		throw $exception;
	}
}



$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl,country FROM world_cities WHERE Place="."'".$place."'"." AND TZ="."'".$TZ."'";
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
echo "Country:" . $country."<BR>";
echo "TZ: " . $TZ ."<BR>";
}

date_default_timezone_set($TZ);

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
	//exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);  // t - true node
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // m - mean node
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);  // t - true node
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // m - mean node
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

debug("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys");

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
    
	if($count>8 && $count!=21) {
		if($count > 8 and $count <= 20){
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = explode(' ',$house)[1];
			$g_bhava_arr[$house] = array($row[1],DECtoDMS(fmod($row[1],30.0)));
		}
		
		continue;
	}
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
  $sw_planet = updatePlanet($sw_planet,$sw_dir, $ragu_house);

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

// Update Mandhi
$sw_planet = updateMandhi($sw_planet,$sw_planet,$sun_degree,$day,$dayofweek,$sun_house);


if($DEBUG==1){
	var_export($sw_planet);
}

//Update planet symbol in Jama planets (tr_sw_planet)
//$tr_sw_planet = updateSymbols($tr_sw_planet);

//Update planet strengths
$sw_planet = updateStrength($sw_planet);

//Get transit data (outside)

$transit_date = isset($_POST['nTrDate']) ? $_POST['nTrDate'] : date('Y-m-d');
$transit_time = isset($_POST['nTrTime']) ? $_POST['nTrTime'] : date('H:i:s');

$date = date("d.m.Y",strtotime($transit_date));
$time = date("H:i:s",strtotime($transit_time));

$transit_datetime = $date . " " . $time;

$transitTimestamp = strtotime($transit_datetime);
$utcTimestamp = $transitTimestamp - $offset * (60 * 60);

$currUTC_date = date('d.m.Y', $utcTimestamp);
$currUTC_time = date('H:i:s', $utcTimestamp);

$transit_dayofweek = date('w', strtotime($transit_date));

$transit_next_day=date("Y-m-d",strtotime($transit_date. ' + 1 days'));
$transit_previous_day=date("Y-m-d",strtotime($transit_date. ' - 1 days'));

$transit_today_sunrise = $transit_date . " " . getSunrise(strtotime($transit_date),floatval($lat), floatval($lon));
$transit_today_sunset = $transit_date . " " .  getSunset(strtotime($transit_date),floatval($lat), floatval($lon));
$transit_tomo_sunrise = $transit_next_day . " " .  getSunrise(strtotime($transit_next_day),floatval($lat), floatval($lon));
$transit_prev_sunset = $transit_previous_day . " " .  getSunset(strtotime($transit_previous_day),floatval($lat), floatval($lon));

debug("TRANSIT DATE=".$transit_date);
debug("TRANSIT TIME=".$transit_time);
debug("TRANSIT TODAY SUNRISE=".date("H:i:s",strtotime($transit_today_sunrise)));
debug("TRANSIT TODAY SUNSET=".date("H:i:s",strtotime($transit_today_sunset)));

// find if it's day or night for mandhi calculation
if(strtotime($transit_datetime) >= strtotime($transit_today_sunrise) && strtotime($transit_datetime) <= strtotime($transit_today_sunset)) {
	$transit_day=1;
}else {
	$transit_day=0;
}

debug("TRANSIT DAY=" . $transit_day);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	debug('RUNNING ON WINDOWS');
	
	//exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);
	//64 version runs little faster
	//exec ("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit); // t - true node
	exec ("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);   // m - mean node
	debug("<BR>swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	//exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit); // t - true node
	exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit); // m - mean node
	debug("<BR>swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

debug("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys");

planetSort_transit($transit);

$count=0;
foreach ($transit as $key => $line)
  {
	global $tr_ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree_tr[$key] = $row[1];
	
	$tr_sw_house[$key] = (int)((float)$tot_degree_tr[$key]/30.0);
	
	//$tr_sw_degree[$key] = DECtoDMS(round($tot_degree_tr[$key] - $sw_house[$key]*30,2));
	
	//$tr_sw_degree[$row[0]] = DECtoDMS(round($tot_degree_tr[$key] - $tr_sw_house[$key]*30,2));
	$tr_sw_degree[$row[0]] = round($tot_degree_tr[$key] - $sw_house[$key]*30,2);
	
    if($count!=21){
		$tr_sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($transit_sun_house==0 and strpos($row[0],'Sun')===0){
		$transit_sun_house = (int)($tot_degree_tr[$key]/30)+1;
		$transit_sun_degree = getDegree(round($tot_degree_tr[$key] - $tr_sw_house[$key]*30,2)); 
	}
	
	if($transit_moon_house==0 and strpos($row[0],'Moon')===0){
		$transit_moon_house = (int)($tot_degree_tr[$key]/30)+1;
		$transit_moon_degree = getDegree(round($tot_degree_tr[$key] - $tr_sw_house[$key]*30,2));
	}
	
	if($transit_lagna_house==0 and strpos($row[0],'Ascendant')===0){
		$transit_lagna_house = (int)($tot_degree_tr[$key]/30)+1;
	}
	
	$tr_sw_planet[$tr_sw_house[$key]] = $tr_sw_planet[$tr_sw_house[$key]]." ".$row[0];
	
	$tot_degree_tr[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	//if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
	if(strpos($row[0],"mean Node")===0){ // using === because this strpos returns 0 when mean Node found and also false(0) when it's not found. So we need to use ===
		
		$tr_ragu_house = $tr_sw_house[$key];
		if($DEBUG==1){ echo "tr_ragu_house=" . $tr_sw_house[$key]."<br>";}
	}
		
	if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$tr_sw_planet[$key]."|".$tr_sw_planet[$key]."|".$tr_sw_degree[$key]."|".$tr_sw_dir[$key]."|"."<br>";}
  }
  
if($DEBUG==1){ 
  echo "<br>Transit[".$sw_planet[0]."]</br>";
  var_dump($tr_sw_planet);
  var_dump($tr_sw_degree);
  var_dump($tr_sw_dir);
}
  $tr_sw_planet = updatePlanet_transit($tr_sw_planet,$tr_sw_dir, $tr_ragu_house);

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

// Update Mandhi
$tr_sw_planet = updateMandhi_transit($tr_sw_planet,$tr_sw_planet,$transit_sun_degree,$transit_day,$transit_dayofweek,$transit_sun_house);

if($DEBUG==1){
	var_export($tr_sw_planet);
	var_export($sw_planet);
}

//Update planet symbol in Jama planets (tr_sw_planet)
//$tr_sw_planet = updateSymbols($tr_sw_planet);

//Update planet strengths
//$tr_sw_planet = updateStrength($tr_sw_planet);

$chartdate=date("d/m/Y",strtotime($chart_date));

$Planet_degree=displayDegree($sw_degree); 
$Transit_Planet_degree=displayDegree_transit($tr_sw_degree);

//$sw_degree = sortDegree($sw_degree);
$sw_planet = sortDegree($sw_planet);
$tr_sw_planet = sortDegree_transit($tr_sw_planet);

updateIndhuLagna();

calculatePanchang();
$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);

calculateBirthPanchang();
//displayThithiSoonyam($panchang["thithi"]);
//calculateBirthThithiSoonyam($panchang["thithi"]);
calculateVimDasaBalance($moon_degree,$moon_house,$chart_time,$chart_time,$panchang['nak'],$nak_pad_idx, true);
//displayButtons(true); //this will calculate star lord as well 
displayStarLard();
calculateVimDasa();
calculateCurrentDasaBukthi(true);
calculateNavamsam();


$LANG = 1; //1 tamil  2 english
//$sw_planet = $_SESSION["inside"];
$g_thithi_soonyam_arr = $g_thithi_soonyam_arr;
$starlord_arr = $planet_starlord;
$planetKaranaLord = $g_planetKaranaLord;
$dasa = $g_dasa_arr;
$birth_date = date('d/M/Y',strtotime($chart_date));
$birth_time = date('h:i:s a',strtotime($chart_time));
$name = $in_name;
$sex = $in_sex;
$karan = $g_karan;
$yogi_arr = getYogi($panchang['yoga']);
$yogi = $yogi_arr[1]."(".$yogi_arr[0].")";
$ayogi = $yogi_arr[3]."(".$yogi_arr[2].")";
$bukthi_table = prepareBukthiTable();
$g_thithi_soonyam = $g_thithi_soonyam_matching_arr[0];
$g_dasaBalance = $g_dasaBalance[0];
$g_dasaCurrent = $g_dasaCurrent[0];
$g_sunrise = explode(' ',$today_sunrise)[1];
$g_sunset = explode(' ',$today_sunset)[1];
$g_lagnam = getRasi($lagna_house-1);
$g_rasi = getRasi($moon_house-1);
$g_pancha_pakshi = getPanchaPakshi();
$g_age=-1;
$g_vayadhotta_lagna=-1;

$mudakku_star = getMudakku(1);
$mudakku_rasi = getMudakku(2);
$mudakku_baavam = getBavam(getMudakku(2));
$g_mudakku_detail = $mudakku_star." (".$mudakku_rasi.")<BR>".$mudakku_baavam."ஆம் வீடு";

$g_age = round((strtotime($transit_date)-strtotime($chart_date))/(365*60*60*24),1);
$vayadhotta_rasi = ceil($g_age) % 12;

if($vayadhotta_rasi == 0){
	$vayadhotta_rasi = 12;
}

if($g_age >= 0){
	$g_vayadhotta_lagna = getRasi(targetHouse($lagna_house,$vayadhotta_rasi-1));
}
else{
	$g_vayadhotta_lagna = "சாத்தியம் இல்லை";
}

?>



<div id='onepage' style="width: min-content">
	<div class="header-parent">
	  <div class="header-left"><img src='images/dsjv_title.png' height="25px"></div>
	  <div class="header-center"><div class='divTableTextOfficeName'>&#3059; <BR><?php echo $office_name; ?></div></div>
	  <div class="header-right"><img src='images/pkastro_title.png' height="25px"></div>
	</div>
<center><div class='divTableTextSloka' style='font-family: Verdana, Raavi, sans-serif;font-size:8px;width:100%;line-height:10px'>&#2384; ஜனனி ஜன்ம சௌக்யானாம் வர்தநீ குலஸம்பதாம்
 பதவி பூர்வ  புண்யாணாம் லிக்ய தே ஜன்ம பத்திரிகா !</div></center>
	<center>
		<!-- <div class="divTableBody"> -->
		<div class='divTable blueTable' style='height:100%;width:90%'> 
			<div class="divTableRow">
				<div class="divHoroDetailCell" style="border:none;"><?php displayDetailsCol1(); ?></div>
				<div class="divHoroDetailCell" style="border:none;"><?php displayDetailsCol2(); ?></div>
				<div class="divHoroDetailCell" style="border:none;"><?php displayDetailsCol3(); ?></div>
			</div>
		</div>	
			
		<div class="divTable blueTable" id="onepage">
				<div class="divTableRow">
					<div class="divHoroDetailCell" style="border:none; padding:5px"><?php displayChart1(); ?></div>
					<div class="divHoroDetailCell" style="border:none; padding:5px"><?php displayNavamsam(); ?></div>
				</div>
		</div>

			<div class="divTableRow">
				<div class="divStarDetailCell" style="border:none;"><?php displayStarLord(); ?></div>
				<div class="divStarDetailCell" style="border:none;"><?php displayDasa(); ?></div>
				<div class="divStarDetailCell" style="border:none;"><?php displayBukthi(); ?></div>
			</div>
			
			
	</center>
	
</div>
<BR>
<BR>
<BR>
<BR>
<BR>
<div class="PageFooter" style="width:-webkit-fill-available">&#169; Periyanayaki Astro Solutions</div>  

					
<?php

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'OnePageBirth|'.$place."|".$chart_date."|".$chart_time."|".$transit_date."|".$transit_time;
	logActivity($conn,$_COOKIE["phone"],$activity);
}

dbDisconnect($conn);


?>

</body>
</html>