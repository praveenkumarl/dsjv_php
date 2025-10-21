<?php
//pmr - Manikandan raju new jamakol
include "db.php";
include "lib.php";

$in_place = isset($_POST["place"]) ? $_POST["place"] : $_POST["nPlace"];
$place=strtok($in_place,"|");
$TZ=strtok("|");
$lang = isset($_POST["lang"]) ? $_POST["lang"] : "English";

$_SESSION["place"]=$place;

$cookie_name = "place";
$cookie_value = $in_place;
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

	
    <script src="script.js"></script>
    <script type="text/javascript">
/******** this code is to take screenshot of the chart **************	
		var dataURL = {};  
		 $(document).ready(function(){
		$('a').click(function(){
			html2canvas(document.querySelector("#chart")).then(canvas => {  
				document.body.appendChild(canvas);  
				dataURL = canvas.toDataURL(); 
				post_data(dataURL); 
		  });
		});
		});
  
		function post_data(imageURL) {  
            //console.log(imageURL);  
			alert(imageURL);
            $.ajax({  
                url: "save_data.php",  
                type: "POST",  
                data: {  
                    image: imageURL  
                },  
                dataType: "html",  
                success: function() {  
                    alert('Success!!');  
                    location.reload();  
                }  
            });  
        }   
****** this code is to take screenshot of the chart ***************/

		function init(){
			
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

			  document.getElementById('nDate').value= today ; 
			  document.getElementById('nTime').value= time ; 
			  
			  return true;
			}

		
		$('nTime').on('change', function() {
			var dataString = $(this).serialize();
			alert("test");
			$.ajax({
			  type: "POST",
			  url: "pmr_Jamakol_Chart.php",
			  data: dataString,
			  success: function () {
				// Display message back to the user here
			  }
					});
			 
				e.preventDefault();
		});
		
		$('nDate').on('change', function() {
			var dataString = $(this).serialize();
			alert("test");
			$.ajax({
			  type: "POST",
			  url: "pmr_Jamakol_Chart.php",
			  data: dataString,
			  success: function () {
				// Display message back to the user here
			  }
					});
			 
				e.preventDefault();
		});
				
	</script>
</head>
<?php

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$lat="";
$lon="";
$gmt_offset="";
$tz_offset;
$today_sunrise="";
$today_sunset="";
$tomo_sunrise="";
$sun_degree=0;
$sun_house=0;
$ragu_house=-1;
$sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$sw_degree = array(null,null,null,null,null,null,null,null,null,null,null,null);
$out_planet_degree = array();
$sw_dir = array(null,null,null,null,null,null,null,null,null,null,null,null);
$dayofweek=-1;
$day=-1;
$udhayam_house=null;
$aarudam_house=null;
$kavippu_house=null;
$output=array();
$lagna_house=null;
$lagna_degree=null;
$db_ephemeris=false;
$udhaya_athipathi="";
$ubhaya_udhayam="";
$udhaya_vedhai="";
$udhaya_athipathi_house=null;
$udhaya_athipathi_rasi_house=null;
$cricketTable="";
$cricketMenu=0;
$autoAnalysisMenu=0;

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time"];
$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date"];

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));


?>

<body onLoad="init();">


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



function updatePlanet(array $planets,array $retro)
{
	global $DEBUG,$ragu_house,$lang;
	
	debug("inside updateplanet-".$lang);
	
	if($DEBUG==1) {
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($planets);
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($retro);
	}
	
	if(strpos($lang,"Tamil")===0){
		
		$out = str_replace('Sun','சூரி',$planets);
		$out = str_replace('Moon','சந்',$out);
		
		if($retro[4]==1){
			$out = str_replace('Mars','செ',$out);
		}else{
			$out = str_replace('Mars','செ(வ)',$out);
		}
		
		if($retro[3]==1){
			$out = str_replace('Venus','சுக்',$out);
		}else{
			$out = str_replace('Venus','சுக்(வ)',$out);
		}
		
		if($retro[2]==1){
			$out = str_replace('Mercury','புத',$out);
		}else{
			$out = str_replace('Mercury','புத(வ)',$out);
		}
		
		if($retro[5]==1){
			$out = str_replace('Jupiter','குரு',$out);
		}else{
			$out = str_replace('Jupiter','குரு(வ)',$out);
		}
		
		if($retro[6]==1){
			$out = str_replace('Saturn','சனி',$out);
		}else{
			$out = str_replace('Saturn','சனி(வ)',$out);
		}
		
		$out = str_replace('true Node','ரா',$out);
		$out = str_replace('Ascendant','லக்',$out);
		
		//trim spaces
		$out = array_map('trim', $out);
		
		$Kethu_pos = findKethuPos($ragu_house);
		
		$out[$Kethu_pos] = 'கே ' . $out[$Kethu_pos];
	}
	elseif(strpos($lang,"English")===0){
		$out = str_replace('Sun','Su',$planets);
		$out = str_replace('Moon','Mo',$out);
		
		if($retro[4]==1){
			$out = str_replace('Mars','Ma',$out);
		}else{
			$out = str_replace('Mars','Mar(R)',$out);
		}
		
		if($retro[3]==1){
			$out = str_replace('Venus','Ve',$out);
		}else{
			$out = str_replace('Venus','Ve(R)',$out);
		}
		
		if($retro[2]==1){
			$out = str_replace('Mercury','Me',$out);
		}else{
			$out = str_replace('Mercury','Me(R)',$out);
		}
		
		if($retro[5]==1){
			$out = str_replace('Jupiter','Ju',$out);
		}else{
			$out = str_replace('Jupiter','Ju(R)',$out);
		}
		
		if($retro[6]==1){
			$out = str_replace('Saturn','Sa',$out);
		}else{
			$out = str_replace('Saturn','Sa(R)',$out);
		}
		
		$out = str_replace('true Node','Ra',$out);
		$out = str_replace('Ascendant','Asc',$out);
		
		//trim spaces
		$out = array_map('trim', $out);
		
		$Kethu_pos = findKethuPos($ragu_house);
		
		$out[$Kethu_pos] = 'Ke ' . $out[$Kethu_pos];
	}
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE PLANET after update</BR>";
		echo "<BR>KETHU position: ".$Kethu_pos . "</BR>";
		var_dump($out);
	}
	
	return $out;
}

function updateOutsidePlanetLanguage(array $planets)
{
	global $DEBUG,$lang;
	
	$out = $planets;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE LANGUAGE before update</BR>";
		var_dump($planets);
	}
	
	if(strpos($lang,"English")===0){
		$out = str_replace("சூரி","Su",$planets);
		$out = str_replace("செ","Ma",$out);
		$out = str_replace("ரா","Ra",$out);
		$out = str_replace("குரு","Ju",$out);
		$out = str_replace("புத","Me",$out);
		$out = str_replace("சுக்","Ve",$out);
		$out = str_replace("சனி","Sa",$out);
		$out = str_replace("சந்","Mo",$out);
	}
	
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE LANGUAGE after update</BR>";
		var_dump($out);
	}
	
	return $out;
}

function updateStrengthLan(array $planets)
{
	global $DEBUG,$lang;
	
	if($DEBUG==1) {
		echo "<BR>updateStrength before update</BR>";
		var_dump($planets);
	}
	
	if(strpos($lang,"Tamil")===0){ //tamil language display
		//Meenam
		if(strpos($planets[11],"சுக்")===0 || strpos($planets[11],"சுக்")>0){
			$planets = str_replace("சுக்","சுக்&#8593;",$planets);
		}
		if(strpos($planets[11],"புத")===0 || strpos($planets[11],"புத")>0){
			$planets = str_replace("புத","புத&#8595;",$planets);
		}
		
		//Makaram
		if(strpos($planets[9],"செ")===0 || strpos($planets[9],"செ")>0){
			$planets = str_replace("செ","செ&#8593;",$planets);
		}
		if(strpos($planets[9],"குரு")===0 || strpos($planets[9],"குரு")>0){
			$planets = str_replace("குரு","குரு&#8595;",$planets);
		}
		
		//Thulam
		if(strpos($planets[6],"சனி")===0 || strpos($planets[6],"சனி")>0){
			$planets = str_replace("சனி","சனி&#8593;",$planets);
		}
		if(strpos($planets[6],"சூரி")===0 || strpos($planets[6],"சூரி")>0){
			$planets = str_replace("சூரி","சூரி&#8595;",$planets);
		}
		
		//Kanni
		if(strpos($planets[5],"புத")===0 || strpos($planets[5],"புத")>0){
			$planets = str_replace("புத","புத&#8593;",$planets);
		}
		if(strpos($planets[5],"சுக்")===0 || strpos($planets[5],"சுக்")>0){
			$planets = str_replace("சுக்","சுக்&#8595;",$planets);
		}
		
		//Katakam
		if(strpos($planets[3],"குரு")===0 || strpos($planets[3],"குரு")>0){
			$planets = str_replace("குரு","குரு&#8593;",$planets);
		}
		if(strpos($planets[3],"செ")===0 || strpos($planets[3],"செ")>0){
			$planets = str_replace("செ","செ&#8595;",$planets);
		}
		
		//Rishabam
		if(strpos($planets[1],"சந்")===0 || strpos($planets[1],"சந்")>0){
			$planets = str_replace("சந்","சந்&#8593;",$planets);
		}
		
		//Mesham
		if(strpos($planets[0],"சூரி")===0 || strpos($planets[0],"சூரி")>0){
			$planets = str_replace("சூரி","சூரி&#8593;",$planets);
		}
		if(strpos($planets[0],"சனி")===0|| strpos($planets[0],"சனி")>0){
			$planets = str_replace("சனி","சனி&#8595;",$planets);
		}
	}
	elseif (strpos($lang,"English")===0){ //english language display
		//Meenam
		if(strpos($planets[11],"Ve")===0 || strpos($planets[11],"Ve")>0){
			$planets = str_replace("Ve","Ve&#8593;",$planets);
		}
		if(strpos($planets[11],"Me")===0 || strpos($planets[11],"Me")>0){
			$planets = str_replace("Me","Me&#8595;",$planets);
		}
		
		//Makaram
		if(strpos($planets[9],"Ma")===0 || strpos($planets[9],"Ma")>0){
			$planets = str_replace("Ma","Ma&#8593;",$planets);
		}
		if(strpos($planets[9],"Ju")===0 || strpos($planets[9],"Ju")>0){
			$planets = str_replace("Ju","Ju&#8595;",$planets);
		}
		
		//Thulam
		if(strpos($planets[6],"Sa")===0 || strpos($planets[6],"Sa")>0){
			$planets = str_replace("Sa","Sa&#8593;",$planets);
		}
		if(strpos($planets[6],"Su")===0 || strpos($planets[6],"Su")>0){
			$planets = str_replace("Su","Su&#8595;",$planets);
		}
		
		//Kanni
		if(strpos($planets[5],"Me")===0 || strpos($planets[5],"Me")>0){
			$planets = str_replace("Me","Me&#8593;",$planets);
		}
		if(strpos($planets[5],"Ve")===0 || strpos($planets[5],"Ve")>0){
			$planets = str_replace("Ve","Ve&#8595;",$planets);
		}
		
		//Katakam
		if(strpos($planets[3],"Ju")===0 || strpos($planets[3],"Ju")>0){
			$planets = str_replace("Ju","Ju&#8593;",$planets);
		}
		if(strpos($planets[3],"Ma")===0 || strpos($planets[3],"Ma")>0){
			$planets = str_replace("Ma","Ma&#8595;",$planets);
		}
		
		//Rishabam
		if(strpos($planets[1],"Mo")===0 || strpos($planets[1],"Mo")>0){
			$planets = str_replace("Mo","Mo&#8593;",$planets);
		}
		
		//Mesham
		if(strpos($planets[0],"Su")===0){
			$planets = str_replace("Su","Su&#8593;",$planets);
		}
		if(strpos($planets[0],"Sa")===0){
			$planets = str_replace("Sa","Sa&#8595;",$planets);
		}
	}
	
	if($DEBUG==1) {
		
		echo "<BR>updateStrength after update</BR>";
		var_dump($planets);
	}
	
	return $planets;
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


function updateUAK_Mandhi(array $out,array $in, $sun_degree, $day)
{	
	global $sun_house,$udhayam_house,$aarudam_house,$kavippu_house,$lang;
	
	$mandhi_house = targetHouse($sun_house,findMandhiHouse($sun_degree, $day));
	
	debug($mandhi_house);
	
	if(strpos($lang,"Tamil")===0){
		foreach ($in as $key => $value) {
			//udhayam color update
			$pos = strpos($in[$key], 'உத');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;font-size:11pt">உத</span>';
				$udhayam_house = $key;
			}
			
			//aarudam color update
			$pos = strpos($in[$key], 'ஆரு');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;font-size:11pt">ஆரு</span>';
				$aarudam_house = $key;
			}
			
			//kavippu color update
			$pos = strpos($in[$key], 'கவி');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:red;font-size:11pt">கவி</span>';
				$kavippu_house = $key;
			}
			
			//mandhi house update
			if($key == $mandhi_house){
				$out[$key] = $out[$key] . " <span>மா</span>";
			}
		}
	}
	elseif(strpos($lang,"English")===0){
		foreach ($in as $key => $value) {
			//udhayam color update
			$pos = strpos($in[$key], 'உத');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;">UDH</span>';
				$udhayam_house = $key;
			}
			
			//aarudam color update
			$pos = strpos($in[$key], 'ஆரு');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;">ARU</span>';
				$aarudam_house = $key;
			}
			
			//kavippu color update
			$pos = strpos($in[$key], 'கவி');
			if($pos===0 || $pos >0){
				$out[$key] = $out[$key] . ' <span style="color:white;background-color:red;">KAV</span>';
				$kavippu_house = $key;
			}
			
			//mandhi house update
			if($key == $mandhi_house){
				$out[$key] = $out[$key] . " <span>Md</span>";
			}
		}
	}
	return $out;
}

function updateSunDegree($in_deg)
{
	global $sun_degree;
// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.

    $vars = explode(".",$in_deg);
    $deg = $vars[0]+$vars[1]/100;
    
	$sun_degree = $deg;
} 



function displayDegree()
{
	global $sw_degree,$out_planet_degree,$lang;
	
	$txt="";
	$planet="";
	
	foreach ($sw_degree as $key => $val) {
		if($val!=null) {
			
			if(strpos($lang,"Tamil")===0){
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
			}
			elseif(strpos($lang,"English")===0){
				switch (trim($key)) {
					case "Sun":
						$planet="Su";
						break;
					case "Moon":
						$planet="Mo";
						break;
					case "Mercury":
						$planet="Me";
						break;
					case "Venus":
						$planet="Ve";
						break;
					case "Mars":
						$planet="Ma";
						break;
					case "Jupiter":
						$planet="Ju";
						break;
					case "Saturn":
						$planet="Sa";
						break;
					case "true Node":
						$planet="Ra/Ke";
						break;
					case "Ascendant":
						$planet="Asc";
						break;
					case "mandhi":
						$planet="Man";
						break;
					default:
						break;
				}
			}
			
			$out_planet_degree[$planet] = $val;
			
			$txt = $txt . " " . $planet . "-" . $val;
		}
	}
	
	return $txt;
}

function aarudaVedhai()
{
	global $udhayam_house,$aarudam_house;
	
	$status="இல்லை";
	
	debug("UDHAYAM HOUSE=".$udhayam_house);
	debug("AARUDAM HOUSE=".$aarudam_house);
	
	if(targetHouse($udhayam_house, 6)==$aarudam_house){
		$status = "<span style='color:red;'>ஆம்</span>";
	}
	elseif(targetHouse($udhayam_house, 8)==$aarudam_house){
		$status = "<span style='color:white;background-color:red;'>ஆம்(கடுமை)</span>";
	}
	elseif(targetHouse($udhayam_house, 12)==$aarudam_house){
		$status = "<span style='color:red;'>ஆம்</span>";
	}
	
	return $status;
}

function udhayaVedhai()
{
	global $udhayam_house,$outside,$udhaya_athipathi,$ubhaya_udhayam,$udhaya_vedhai,$udhaya_athipathi_house;
	
	$udhaya_vedhai="இல்லை";
	
	$udhaya_athipathi = findAthipathi($udhayam_house);
	$ubhaya_udhayam = findUbhaya($udhayam_house);
		
	$udhaya_athipathi_rasi_house = (int)array_search ($udhaya_athipathi, $outside)+(int)array_search ($udhaya_athipathi.'&#8593;', $outside)+(int)array_search ($udhaya_athipathi.'&#8595;', $outside);
	$udhaya_athipathi_house = countHouses($udhayam_house,$udhaya_athipathi_rasi_house);
	
	debug("UDHAYA VEDHAI|" . $udhayam_house . "|" . $udhaya_athipathi . "|" . $udhaya_athipathi_rasi_house . "|" . $udhaya_athipathi_house);
	
	if(targetHouse($udhayam_house, 6)==$udhaya_athipathi_house){
	$udhaya_vedhai = "<span style='color:red;'>ஆம்</span>";
	}
	elseif(targetHouse($udhayam_house, 8)==$udhaya_athipathi_house){
		$udhaya_vedhai = "<span style='color:white;background-color:red;'>ஆம்(கடுமை)</span>";
	}
	elseif(targetHouse($udhayam_house, 12)==$udhaya_athipathi_house){
		$udhaya_vedhai = "<span style='color:red;'>ஆம்</span>";
	}
	
	$udhaya_athipathi = $udhaya_athipathi."(".$udhaya_athipathi_house.")";
	
	return $udhaya_vedhai;
}

/*function kavippuDetails()
{
	global $kavippu_house,$outside,$udhayam_house;
	
	if(strlen($outside[$kavippu_house])>0){
		$kavippu_detail[0] = $outside[$kavippu_house];
	}else{
		$kavippu_detail[0] = $outside[$kavippu_house-1];
	}
	
	$kavippu_detail[1] = countHouses($udhayam_house,$kavippu_house);
	
	return $kavippu_detail;
}*/


/*
function displayAnalysis()
{
	global $outside, $inside, $udhayam_house, $aarudam_house,$udhaya_athipathi,$ubhaya_udhayam;
	
	$analysis= array ('ஆருட வேதை'=>null,'உதயம் வேதை'=>null,'உதயம் முதல் ஆருடம்'=>null,'உதயாதிபதி'=>null,'உபய உதயம்'=>null,'10ஆம் இடம்'=>null,'11ஆம் இடம்'=>null,'உச்சம்'=>null,'நீசம்'=>null);
	
	$analysis['ஆருட வேதை'] = aarudaVedhai();
	$analysis['உதயம் வேதை'] = udhayaVedhai();
	$analysis['உதயம் முதல் ஆருடம்'] = countHouses($udhayam_house,$aarudam_house);
	$analysis['உதயாதிபதி']=$udhaya_athipathi;
	$analysis['உபய உதயம்']=$ubhaya_udhayam;
	
	if($outside[targetHouse($udhayam_house,10)]==null){
		$analysis['10ஆம் இடம்']=$outside[targetHouse($udhayam_house,11)]."(நோக்கி)";
	}
	else{
		$analysis['10ஆம் இடம்']=$outside[targetHouse($udhayam_house,10)];
	}
	
	if($outside[targetHouse($udhayam_house,11)]==null){
		$analysis['11ஆம் இடம்']=$outside[targetHouse($udhayam_house,12)]."(நோக்கி)";
	}
	else{
		$analysis['11ஆம் இடம்']=$outside[targetHouse($udhayam_house,11)];
	}
	
	$analysis["கவிப்பு"." (".kavippuDetails()[1].")"]=kavippuDetails()[0];
	$analysis['உச்சம்']=uchchaDetails();
	$analysis['நீசம்']=nechchaDetails();
	
	return $analysis;
}
*/

function cricketAnalysis()
{
	global $outside, $udhayam_house, $aarudam_house,$udhaya_athipathi,$ubhaya_udhayam,$udhaya_athipathi_house,$udhaya_athipathi_rasi_house;
	
	//analysis for 1st team
	$analysis = array ('உதயாதிபதி(1st team)'=>null,'முதல் ஜாமம்(1st team)'=>null,'இரண்டாம் ஜாமம்(1st team)'=>null,'மூன்றாம் ஜாமம்(1st team)'=>null, '7ஆம் அதிபதி(2nd team)'=>null,'முதல் ஜாமம்(2nd team)'=>null,'இரண்டாம் ஜாமம்(2nd team)'=>null,'மூன்றாம் ஜாமம்(2nd team)'=>null);
	$secondTeam = array ('7ஆம் அதிபதி'=>null,'முதல் ஜாமம்'=>null,'இரண்டாம் ஜாமம்'=>null,'மூன்றாம் ஜாமம்'=>null);
	
	$udhaya_athipathi_rasi_house = targetHouse($udhayam_house,$udhaya_athipathi_house);
	
	$analysis['உதயாதிபதி(1st team)'] = $udhaya_athipathi;
	$analysis['முதல் ஜாமம்(1st team)'] = $udhaya_athipathi_house;
	
	if($udhayam_house==0)
	{
		$analysis['இரண்டாம் ஜாமம்(1st team)'] = countHouses($udhayam_house+1,targetJamaHouse($udhaya_athipathi_rasi_house,1));
	}
	else{
		$analysis['இரண்டாம் ஜாமம்(1st team)'] = countHouses($udhayam_house,targetJamaHouse($udhaya_athipathi_rasi_house,1));
	}
	$analysis['மூன்றாம் ஜாமம்(1st team)'] = countHouses($udhayam_house,targetJamaHouse($udhaya_athipathi_rasi_house,2));
	
	//analysis for 2nd team
	$seventh_house = targetHouse($udhayam_house,7);
	$seventh_athipathi = findAthipathi($seventh_house);
	$seventh_athipathi_rasi_house = (int)array_search ($seventh_athipathi, $outside)+(int)array_search ($seventh_athipathi.'&#8593;', $outside)+(int)array_search ($seventh_athipathi.'&#8595;', $outside);
	$seventh_athipathi_house = countHouses($seventh_house,$seventh_athipathi_rasi_house);
	
	$analysis['7ஆம் அதிபதி(2nd team)'] = $seventh_athipathi."(".$seventh_athipathi_house.")";
	$analysis['முதல் ஜாமம்(2nd team)'] = $seventh_athipathi_house;
	$analysis['இரண்டாம் ஜாமம்(2nd team)'] = countHouses($seventh_house,targetJamaHouse($seventh_athipathi_rasi_house,1));
	$analysis['மூன்றாம் ஜாமம்(2nd team)'] = countHouses($seventh_house,targetJamaHouse($seventh_athipathi_rasi_house-1,2));
	
	return $analysis;
}

function findMandhiHouse($sun_degree,$day)
{
	global $dayofweek,$sw_degree;
	
	switch ($dayofweek) {
		case 0: //Sun
			if($day==1){
				$mandhi_degree=$sun_degree+156;
			}
			else{
				$mandhi_degree=$sun_degree+240;
			}
			break;
		case 1: //Mon
			if($day==1){
				$mandhi_degree=$sun_degree+132;
			}
			else{
				$mandhi_degree=$sun_degree+216;
			}
			break;
		case 2: //Tue
			if($day==1){
				$mandhi_degree=$sun_degree+108;
			}
			else{
				$mandhi_degree=$sun_degree+192;
			}
			break;
		case 3: //Wed
			if($day==1){
				$mandhi_degree=$sun_degree+84;
			}
			else{
				$mandhi_degree=$sun_degree+336;
			}
			break;
		case 4: //Thur
			if($day==1){
				$mandhi_degree=$sun_degree+60;
			}
			else{
				$mandhi_degree=$sun_degree+312;
			}
			break;
		case 5: //Fri
			if($day==1){
				$mandhi_degree=$sun_degree+36;
			}
			else{
				$mandhi_degree=$sun_degree+288;
			}
			break;
		case 6:  //Sat
			if($day==1){
				$mandhi_degree=$sun_degree+12;
			}
			else{
				$mandhi_degree=$sun_degree+264;
			}
			break;
	  default:
		break;
	}
	
	$mandhi_house = (int)(($mandhi_degree % 360)/30);
	
	debug("FINDMANDHI - WEEK DAY=" . $dayofweek);
	debug("FINDMANDHI - DAYTIME=" . $day);
	debug("FINDMANDHI - MANDHI DEGREE=" . ($mandhi_degree % 30));
	debug("FINDMANDHI - SUN DEGREE=" . $sun_degree);
	debug("FINDMANDHI - MANDHI DEGREE FROM SUN=" . $mandhi_degree);
	debug("FINDMANDHI - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$sw_degree["mandhi"] = DECtoDMS($mandhi_degree % 30);
	
	return $mandhi_house;
}

function updateLagna($sunrise, $tom_sunrise)
{
	global $sun_degree, $sun_house, $chart_time,$lagna_house,$sw_planet;
	$day_length=date_diff(date_create($tom_sunrise),date_create($sunrise));
	
	$rasi_length_min = ($day_length->h*60+$day_length->m)/12;
	debug( "RASI LENGTH=".$rasi_length_min);
	
	$lagna_time=date_diff(date_create($sunrise),date_create($chart_time));
	$reminder = (($lagna_time->h*60.0+$lagna_time->i)/4.0-(30-$sun_degree)) % 30.0;
	$lagna_degree = DECtoDMS($reminder);
	debug("LAGNA DEGREE=" . $lagna_degree);
	
	$sw_degree['லக்']=$lagna_degree;
	
	$datetimeObj = date_diff(date_create($chart_time),date_create($sunrise));
	
	$elasped_mins = $datetimeObj->h*60 + $datetimeObj->i;
	
	$temp=(30-$sun_degree)*4;
	
	//From sun house
	$house1_end = strtotime($sunrise."+".(int)$temp." minutes");
	$house2_end = strtotime(date("H:i",$house1_end) . "+" . $rasi_length_min . "  minutes");
	$house3_end = strtotime(date("H:i",$house2_end) . "+" . $rasi_length_min . "  minutes");
	$house4_end = strtotime(date("H:i",$house3_end) . "+" . $rasi_length_min . "  minutes");
	$house5_end = strtotime(date("H:i",$house4_end) . "+" . $rasi_length_min . "  minutes");
	$house6_end = strtotime(date("H:i",$house5_end) . "+" . $rasi_length_min . "  minutes");
	$house7_end = strtotime(date("H:i",$house6_end) . "+" . $rasi_length_min . "  minutes");
	$house8_end = strtotime(date("H:i",$house7_end) . "+" . $rasi_length_min . "  minutes");
	$house9_end = strtotime(date("H:i",$house8_end) . "+" . $rasi_length_min . "  minutes");
	$house10_end = strtotime(date("H:i",$house9_end) . "+" . $rasi_length_min . "  minutes");
	$house11_end = strtotime(date("H:i",$house10_end) . "+" . $rasi_length_min . "  minutes");
	$house12_end = strtotime(date("H:i",$house11_end) . "+" . $rasi_length_min . "  minutes");
	
	debug ( "HOUSE1_END=".date("H:i",$house1_end)."<BR>");
	debug ( "HOUSE2_END=".date("H:i",$house2_end)."<BR>");
	debug ( "HOUSE3_END=".date("H:i",$house3_end)."<BR>");
	debug ( "HOUSE4_END=".date("H:i",$house4_end)."<BR>");
	debug ( "HOUSE5_END=".date("H:i",$house5_end)."<BR>");
	debug ( "HOUSE6_END=".date("H:i",$house6_end)."<BR>");
	debug ( "HOUSE7_END=".date("H:i",$house7_end)."<BR>");
	debug ( "HOUSE8_END=".date("H:i",$house8_end)."<BR>");
	debug ( "HOUSE9_END=".date("H:i",$house9_end)."<BR>");
	debug ( "HOUSE10_END=".date("H:i",$house10_end)."<BR>");
	debug ( "HOUSE11_END=".date("H:i",$house11_end)."<BR>");
	debug ( "HOUSE12_END=".date("H:i",$house12_end)."<BR>");
	
	if(strtotime($chart_time)>=strtotime($sunrise) and strtotime($chart_time) <$house1_end){
		$lagna_house = targetHouse($sun_house,0);
	}
	elseif(strtotime($chart_time)>=$house1_end and strtotime($chart_time) <$house2_end){
		$lagna_house = targetHouse($sun_house,1);
	}
	elseif(strtotime($chart_time)>=$house2_end and strtotime($chart_time) <$house3_end){
		$lagna_house = targetHouse($sun_house,2);
	}
	elseif(strtotime($chart_time)>=$house3_end and strtotime($chart_time) <$house4_end){
		$lagna_house = targetHouse($sun_house,3);
	}
	elseif(strtotime($chart_time)>=$house4_end and strtotime($chart_time) <$house5_end){
		$lagna_house = targetHouse($sun_house,4);
	}
	elseif(strtotime($chart_time)>=$house5_end and strtotime($chart_time) <$house6_end){
		$lagna_house = targetHouse($sun_house,5);
	}
	elseif(strtotime($chart_time)>=$house6_end and strtotime($chart_time) <$house7_end){
		$lagna_house = targetHouse($sun_house,6);
	}
	elseif(strtotime($chart_time)>=$house7_end and strtotime($chart_time) <$house8_end){
		$lagna_house = targetHouse($sun_house,7);
	}
	elseif(strtotime($chart_time)>=$house8_end and strtotime($chart_time) <$house9_end){
		$lagna_house = targetHouse($sun_house,8);
	}
	elseif(strtotime($chart_time)>=$house9_end and strtotime($chart_time) <$house10_end){
		$lagna_house = targetHouse($sun_house,9);
	}
	elseif(strtotime($chart_time)>=$house10_end and strtotime($chart_time) <$house11_end){
		$lagna_house = targetHouse($sun_house,10);
	}
	elseif(strtotime($chart_time)>=$house11_end and strtotime($chart_time) <$house12_end){
		$lagna_house = targetHouse($sun_house,11);
	}
	else{
		$lagna_house = targetHouse($sun_house,12);
	}
	
	$sw_planet = str_replace("லக்","",$sw_planet);
	$sw_planet[$lagna_house] = $sw_planet[$lagna_house] . " " . "லக்";
}

function createCricketSection()
{
	global $cricAnalysis,$cricketTable;
	
	$cricketTable = <<<EOL
		<div class="section fp-auto-height-responsive" id="section2">
			<div class="intro">
				<div class="container">
					<div style="height:60px"></div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableOuterCell" colspan=3 style="border-radius: 20px 20px 0 0;border:none;">
							<div class="header">Cricket Analysis</div>
							</div>
						</div>
					</div>
			
					<div class="divTable blueTable">
						<div class="divTableBody">
EOL;

	foreach ($cricAnalysis as $key => $value){
	//foreach ($out_planet_degree as $key => $value){
		$cricketTable = $cricketTable. "<div class='divTableRow'>";
		$cricketTable = $cricketTable. "<div class='divTableDegreeCell'>$key</div>";
		$cricketTable = $cricketTable. "<div class='divTableDegreeCell'>$value</div>";
		$cricketTable = $cricketTable. "</div>";	
	}

	$cricketTable = $cricketTable. <<<EOL
							</div>
						</div>
						<div class="divTable blueTable" style="height:40px;width:360px;">
							<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
								<div class="divTableDummyCell" colspan=3 style="border-radius:  0 0 20px 20px;">
								<div class="footer">Scroll up for Auto Analysis	&#9650;</div>
								</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
EOL;

echo $cricketTable;

}

function displayChart($in,$out)
{

echo <<<EOL
	<div class="divTable blueTable" id="chart">
		<div class="overlay1"><br>பெரியநாயகி துணை</br></div>
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
					<div class="divTableCell">$in[11]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[0]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[1]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[2]<span class='symbol'>&#9791;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[10]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;<span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[4]<span class='symbol'>&#9737;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[8]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[7]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[6]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[5]<span class='symbol'>&#9791;<span></div>
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
		  `inside` varchar(250) DEFAULT NULL ,
		  `start_time` datetime NOT NULL,
		  `end_time` datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;";


		if($res=mysqli_query($conn, $sql)){
			if($DEBUG==1){ echo "prepareChartData() executed"; }
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

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$place."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
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
echo "DATE: " . $_POST["in_date"]."<BR>";
echo "TIME: " . $_POST["in_time"]."<BR>";
echo "PLACE: " . $_POST["place"]."<BR>";
echo "LAT: " . $lat."<BR>";
echo "LONG: " . $lon."<BR>";
echo "GMT offset: " . $gmt_offset."<BR>";
echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
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
$mysql_date = date('Y-m-d', $utcTimestamp);
$time = date('H:i:s', $utcTimestamp);

if($DEBUG==1){ 
	echo 'DATE =' . $date ;
	echo 'TIME =' . $time;
	echo 'OFFSET =' . $offset; echo '<br>';
	echo 'birthTimestamp =' . $birthTimestamp; echo '<br>';
	echo 'utcTimestamp =' . $utcTimestamp; echo '<br>';
	echo 'UTC time=' . date('Y-m-d H:i:s', $utcTimestamp); echo '<br>';
}

// ------- Swiss ephemeris // SIDEREAL ZODIAC using Lahiri Ayanamsa | Planetary position for single day
//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head", $output);

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

if($DEBUG==1){ 
var_dump($output);
}

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 

$count=0;
foreach ($output as $key => $line)
  {
	global $ragu_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
    if($count!=21){
		$sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($sun_house==0 and strpos($row[0],'Sun')===0){
		$sun_house = (int)($tot_degree[$key]/30)+1;
		updateSunDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
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
  echo "<br>[".$sw_planet[0]."]</br>";
  var_dump($sw_planet);
  var_dump($sw_degree);
  var_dump($sw_dir);
}
  $sw_planet = updatePlanet($sw_planet,$sw_dir);

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

$next_day=date("Y-m-d",strtotime($chart_date. ' + 1 days'));
$previous_day=date("Y-m-d",strtotime($chart_date. ' - 1 days'));

$today_sunrise = $chart_date . " " . getSunrise(strtotime($chart_date),floatval($lat), floatval($lon));
$today_sunset = $chart_date . " " .  getSunset(strtotime($chart_date),floatval($lat), floatval($lon));
$tomo_sunrise = $next_day . " " .  getSunrise(strtotime($next_day),floatval($lat), floatval($lon));
$prev_sunset = $previous_day . " " .  getSunset(strtotime($previous_day),floatval($lat), floatval($lon));


debug("CHART TIME=".$chart_time);
debug("TODAY SUNRISE=".date("H:i:s",strtotime($today_sunrise)));
debug("TODAY SUNRISE=".date("H:i:s",strtotime($today_sunrise)));
debug("TODAY SUNSET=".date("H:i:s",strtotime($today_sunset)));

// find if it's day or night for mandhi calculation
if(strtotime($datetime) >= strtotime($today_sunrise) && strtotime($datetime) <= strtotime($today_sunset)) {
	$day=1;
}else {
	$day=0;
}

debug("DAY=" . $day);

if($db_ephemeris==true){
	updateLagna($today_sunrise,$tomo_sunrise);
}

$chart_datetime = $chart_date . " " . $chart_time;

if($DEBUG==1){ 
	echo "<br>".$next_day."</br>";
	echo "<br>".$previous_day."</br>";
	echo "<br>".$today_sunrise."</br>";
	echo "<br>".$today_sunset."</br>";
	echo "<br>".$tomo_sunrise."</br>";
	echo "<br>".$prev_sunset."</br>";
}

$sql = "TRUNCATE input_data";
$result = $conn->query($sql);
if(mysqli_query($conn, $sql)){
  	if($DEBUG==1){ echo "<BR>TRUNCATE SUCCESS"; }
  }
 else {
  	if($DEBUG==1){ echo "<BR>TRUNCATE FAILED"; }
}

startSqlTxn($conn);

try {
	$sql = "INSERT INTO input_data VALUES ('$today_sunrise','$today_sunset','$tomo_sunrise','$prev_sunset','$chart_date','$chart_datetime','$place','$tz_offset')";
	if(mysqli_query($conn, $sql)){
		if($DEBUG==1){ echo "Records inserted successfully."; }
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
	}
	
	/* If code reaches this point without errors then commit the data in the database */
    commitSqlTxn($conn);
	
} catch (mysqli_sql_exception $exception) {
    mysqli_rollback($mysqli);

    throw $exception;
}


if($DEBUG==1){  echo "<BR>". "%%%%prepareJamaChartData inputs - sun_house=" . $sun_house . "sun_degree=" .  $sun_degree ."<BR>"; }

startSqlTxn($conn);

try {
	//$sql = "CALL pmr_prepareJamaChartData()"; pmr - Manikandan raju new jamakol
	$sql = "CALL pmr_prepareJamaChartData($sun_house,$sun_degree)";
	if($res=mysqli_query($conn, $sql)){
		if($DEBUG==1){ echo "prepareJamaChartData() executed"; }
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
	}
	
	/* If code reaches this point without errors then commit the data in the database */
    commitSqlTxn($conn);
	
} catch (mysqli_sql_exception $exception) {
    mysqli_rollback($conn);

    throw $exception;
}

// sleep(1);


$stmt = $conn->prepare("SELECT inside,outside FROM temp_data ORDER BY house");

if(!$stmt){
       echo "Prepare failed: (". $conn->errno.") ".$conn->error."<br>";
    }

$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) exit('No rows');
while($row = $result->fetch_assoc()) {
  $outside[] = $row['outside'];
  $inside[] = $row['inside'];
}

// Update udhayam, aarudam, Kavippu- & Mandhi
$sw_planet = updateUAK_Mandhi($sw_planet,$inside,$sun_degree,$day);

if($DEBUG==1){
	var_export($outside);
	var_export($inside);
}

$stmt->close();

//Update planet name accordingly to language selected in dropdown
$outside = updateOutsidePlanetLanguage($outside);

//Update planet symbol in Jama planets (outside)
//$outside = updateSymbols($outside);
$outside = updateStrengthLan($outside);
$sw_planet = updateStrengthLan($sw_planet);


$chartdate=date("d/m/Y",strtotime($chart_date));
$Planet_degree=displayDegree();

//$Analysis = displayAnalysis();
$cricAnalysis = cricketAnalysis();

/*if($autoAnalysisMenu==1){
		echo "<li data-menuanchor='3rdPage'><a href='#3rdPage'>Chart details 2</a></li>";
}

if($cricketMenu==1){
		echo "<li data-menuanchor='4thPage'><a href='#4thPage'>CricketAnalysis</a></li>";
}*/

echo <<<EOL
	<div class="title"></div>
	<div id="fullpage">
		<div class="section fp-auto-height-responsive" id="section0">
			<div class='column-menu'>
			<div class='desktop'>
				<button class="menu" onClick="window.open('index.php','_self')">Home</button>
				<button class="menu" onClick="window.open('pmr_Jamakol_input.php','_self')">New PMR Jama Chart</button>
			</div>
			<div class='mobile'>
				<div class="menu-container">
					<div class='menuBtn' onClick="window.open('index.php','_self')">Home</div>
					<div class='menuBtn' onClick="window.open('pmr_Jamakol_input.php','_self')">New Chart</div>
				</div> 
			</div> 
			</div>
			<div class='column-body'>
					<center><P style="color:#660000;font-weight:bold; font-size:16px; text-align:center;background-color:white;width:250px">PMR ஜாமக்கோள் Chart</P>
					<BR>
					<div class="divTable blueTable" style="height:70px;width:360px;">
								<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
									<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">
										
										<H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">$place</H4>
										<form name='updateChart' action='pmr_Jamakol_Chart.php' method='post'>
											<input type='hidden' id='nPlace' name='nPlace' value=$in_place>
											<input type='date' id='nDate' name='nDate' style="color:#660000;font-size:12px;font-weight:bold;width:110px;" value=$chart_date>
											<input type='time' id='nTime' name='nTime' style="color:#660000;font-size:12px;font-weight:bold;width:110px" step='1' value=$chart_time>
											<input type='hidden' id='lang' name='lang' value=$lang>
											<BR>
											<input type='submit' id='submit' value='Submit' style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;">
											<input type='submit' value='Now' style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;" Onclick="return updateNow();">
										</form>
									</div>
								</div>
							</div>
					<div class="container" id="container" style="horizontal-align:center">
						
						<div class="divTable blueTable" id="chart">
							<div class="overlay1"><br>பெரியநாயகி துணை<br><br><br><br><br><br><br><br><br><br><br><br><br><br><P>Jamakol<P></div>
							<div class="divTableBody">
								<div class="divTableRow">
										<div class="divTableOuterCell">$outside[11]</div>
										<div class="divTableDummyCell"></div>
										<div class="divTableOuterCell">$outside[0]</div>
										<div class="divTableDummyCell"></div>
										<div class="divTableDummyCell"></div>
										<div class="divTableOuterCell">$outside[2]</div>
								</div>
								<div class="divTableRow">
										<div class="divTableDummyCell"></div>
										<div class="divTableCell">$sw_planet[11]<span class='symbol'>&#9795;<span></div>
										<div class="divTableCell">$sw_planet[0]<span class='symbol'>&#9794;<span></div>
										<div class="divTableCell">$sw_planet[1]<span class='symbol'>&#9792;<span></div>
										<div class="divTableCell">$sw_planet[2]<span class='symbol'>&#9791;<span></div>
										<div class="divTableDummyCell"></div>
								</div>
								<div class="divTableRow">
										<div class="divTableDummyCell"></div>
										<div class="divTableCell">$sw_planet[10]<span class='symbol'>&#9796;<span></div>
										<div class="divTableCenterCell"></div>
										<div class="divTableCenterCell"></div>
										<div class="divTableCell">$sw_planet[3]<span class='symbol'>&#9790;<span></div>
										<div class="divTableOuterCell">$outside[3]</div>
								</div>
								<div class="divTableRow">
										<div class="divTableOuterCell">$outside[9]</div>
										<div class="divTableCell">$sw_planet[9]<span class='symbol'>&#9796;<span></div>
										<div class="divTableCenterCell"></div>
										<div class="divTableCenterCell"></div>
										<div class="divTableCell">$sw_planet[4]<span class='symbol'>&#9737;<span></div>
										<div class="divTableDummyCell"></div>
								</div>
								<div class="divTableRow">
										<div class="divTableDummyCell"></div>
										<div class="divTableCell">$sw_planet[8]<span class='symbol'>&#9795;<span></div>
										<div class="divTableCell">$sw_planet[7]<span class='symbol'>&#9794;<span></div>
										<div class="divTableCell">$sw_planet[6]<span class='symbol'>&#9792;<span></div>
										<div class="divTableCell">$sw_planet[5]<span class='symbol'>&#9791;<span></div>
										<div class="divTableDummyCell"></div>
								</div>
								<div class="divTableRow">
										<div class="divTableOuterCell">$outside[8]</div>
										<div class="divTableDummyCell"></div>
										<div class="divTableDummyCell"></div>
										<div class="divTableOuterCell">$outside[6]</div>
										<div class="divTableDummyCell"></div>
										<div class="divTableOuterCell">$outside[5]</div>
								</div>
							</div>	
						</div>
						<div class="divTable blueTable" style="height:40px;width:360px;">
							<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
								<div class="divTableDummyCell" colspan=3 style="border-radius:  0 0 20px 20px;">
								<H6>$Planet_degree</H6>
	EOL;
								/*if($autoAnalysisMenu==1){
									echo "<div class='footer'>Scroll down for Auto Analysis details &#9660;</div>";
								}*/
	echo <<<EOL
									<div><a href="saveChart.php" id="save" style="color:blue;">Click to save chart, question & analysis</a></div>
								</div>
					</div>
					</div></center>
				</div>
			</div>
		</div>
		
		
EOL;

if($autoAnalysisMenu==1){
	createAutoAnalysisSection();
}

if($cricketMenu==1){
	createCricketSection();
}

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'PMRJama|'.$place."|".$chart_date."|".$chart_time;
	date_default_timezone_set('Asia/Kolkata');
	logActivity($conn,$_COOKIE["phone"],$activity,date('Y-m-d H:i:s'));
}

dbDisconnect($conn);

$_POST["mTime"]="";
$_SESSION["charttime"]="";
$_SESSION["inside"]=$sw_planet;
$_SESSION["outside"]=$outside;
?>

</body>
</html>