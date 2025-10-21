<?php
include_once "lib.php";

/*
echo $_POST['in_lat'];
echo $_POST['in_long'];
echo $_POST['in_tz'];

$cookie_name = "lat";
$cookie_value = $_POST['in_lat'];
setcookie($cookie_name, $cookie_value); //storing the lattitude display choice to default it in the jamakol input page

$cookie_name = "long";
$cookie_value = $_POST['in_long'];
setcookie($cookie_name, $cookie_value); //storing the longitude display choice to default it in the jamakol input page

$cookie_name = "tz";
$cookie_value = $_POST['in_tz'];
setcookie($cookie_name, $cookie_value); //storing the timezone display choice to default it in the jamakol input page
*/

if(isset($_SESSION['orig_time'])){
	$_SESSION['orig_time']=isset($_POST["in_time"]) ? $_POST["in_time"] : $_SESSION['orig_time'];
	$_SESSION['orig_date']=isset($_POST["in_date"]) ? $_POST["in_date"] : $_SESSION['orig_date'];
	$_SESSION['orig_place']=isset($_POST["in_place"]) ? $_POST["in_place"] : $_SESSION['orig_place'];
	$_SESSION['orig_analysis']=isset($_POST["analysis"]) ? $_POST["analysis"] : $_SESSION['orig_analysis'];

	$orig_date = $_SESSION['orig_date'];
	$orig_time = $_SESSION['orig_time'];
	$orig_place = $_SESSION['orig_place'];
	$orig_analysis = $_SESSION['orig_analysis'];
}
else{
	$_SESSION['orig_time']=isset($_POST["in_time"]) ? $_POST["in_time"] : "";
	$_SESSION['orig_date']=isset($_POST["in_date"]) ? $_POST["in_date"] : "";
	$_SESSION['orig_place']=isset($_POST["in_place"]) ? $_POST["in_place"] : "";
	$_SESSION['orig_analysis']=isset($_POST["analysis"]) ? $_POST["analysis"] : "";

	$orig_date = $_SESSION['orig_date'];
	$orig_time = $_SESSION['orig_time'];
	$orig_place = $_SESSION['orig_place'];
	$orig_analysis = $_SESSION['orig_analysis'];
}

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$chartNow = isset($_POST["chartNow"]) ? $_POST["chartNow"] : 0; 

$in_place = isset($_POST["place"]) ? $_POST["place"] : $_POST["nPlace"];

$in_degree = isset($_POST["in_degree"]) ? $_POST["in_degree"] : $_POST["nDegree"];

$cookie_name = "degree";
$cookie_value = $in_degree;
setcookie($cookie_name, $cookie_value, $in_degree); //storing the degree display choice to default it in the jamakol input page

$in_question = isset($_POST["question"]) ? $_POST["question"] : $_POST["nQuestion"];
$_SESSION['orig_question']=isset($_POST["question"]) ? $_POST["question"] : $_POST["nQuestion"];
$orig_question = $_SESSION['orig_question'];

$in_analysis = isset($_POST["analysis"]) ? $_POST["analysis"] : $_SESSION['orig_analysis'];

if(strstr(strtolower($_SERVER['HTTP_REFERER']),"jamakol_input.php") or (strstr(strtolower($_SERVER['HTTP_REFERER']),"jamakol_chart.php")) && (isset($_POST['qChart']) && $_POST['qChart'] !=1)){
	$in_analysis = "";
}

$place=strtok($in_place,"|");
$TZ=strtok("|");

$lang = isset($_POST["lang"]) ? $_POST["lang"] : "Tamil";
$rowid = isset($_POST["rowid"]) ? $_POST["rowid"] : -1; //rowid will be posted in case it's called from jamaChartDisplay

$_SESSION["place"]=$place;

$cookie_name = "place";
$cookie_value = $in_place;
setcookie($cookie_name, $cookie_value); 

$DEBUG= isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

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
$moon_degree=0;
$moon_degree_exact=0;
$moon_house=0;
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
$panchang = array();
$g_thithi_soonyam_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$g_bhava_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);

$saveMode=0;
$saveBtnTxt='Save';

$planetDeg = array();
$planetDeg_default = array();

$g_kathir_data=array();

if(isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'],'Jamakol_input.php')==false){
	$saveMode=1;
	$saveBtnTxt='Update';
}

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

if(!isset($_POST["qChart"]) || $_POST["qChart"]==0){
	$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time"];
	$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date"];
}
else{ //if questionChart requested
	$chart_time = isset($_POST["qTime"]) ? $_POST["qTime"] : $_POST["in_time"];
	$chart_date = isset($_POST["qDate"]) ? $_POST["qDate"] : $_POST["in_date"];
}

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    
	<script src="html2pdf.bundle.min.js"></script>
	
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

		function GeneratePdf(filename) {
            var element = document.getElementById('pdf');
			
			var opt = {
                
                margin: 0.5,                
                filename:     filename+'.pdf',
                
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { 
                    scale: 1,
                },
                jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
            };
			
            html2pdf(element,opt);
        }

		function init(){
			if(getCookie(focus)=='date'){
				document.getElementById('nDate').focus();
			}
			
			if(getCookie(focus)=='time'){
				document.getElementById('nTime').focus();
			}
			
			delete_cookie(focus);
		} 
		
		//get cookie value
		function getCookie(name) {
		  const value = `; ${document.cookie}`;
		  const parts = value.split(`; ${name}=`);
		  if (parts.length === 2) return parts.pop().split(';').shift();
		}
		
		function set_cookie(name, value) {
			document.cookie = name +'='+ value +'; Path=/;';
		}
		function delete_cookie(name) {
			document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}
		
		function updateNow(form) {
			  const date = new Date();
			  
			  document.getElementById('chart').style.opacity=0.2;
			  document.getElementById("loader").style.display = "block";
			  
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
			  document.getElementById('nQuestion').value= '' ; 
		      document.getElementById('nAnalysis').value= '' ; 
			  
			  form.submit();
			}

		
		$('nTime').on('change', function() {
			var dataString = $(this).serialize();
			alert("test");
			$.ajax({
			  type: "POST",
			  url: "Jamakol_Chart.php",
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
			  url: "Jamakol_Chart.php",
			  data: dataString,
			  success: function () {
				// Display message back to the user here
			  }
					});
			 
				e.preventDefault();
		});
		
		 $(window).bind("load", function () {
			$('#work-in-progress').fadeOut(100);
		});
	
		
		function freshChart(form){
			window.open('Jamakol_input.php','_self');
		}
		
		function questionChart(form){
			document.getElementById('qChart').value=1;
			document.getElementById('chart').style.opacity=0.2;
			document.getElementById("loader").style.display = "block";
			
			form.submit();
		}
		
		function saveChart(form)
		{
			document.getElementById('chart').style.opacity=0.2;
			document.getElementById("loader").style.display = "block";
			
			var question = document.getElementById("question").value;
			var place = document.getElementById("nPlace").value;
			var date = document.getElementById("nDate").value;
			var time = document.getElementById("nTime").value;
			
			if(document.getElementById("analysis").value.length > 500){
				alert("ஆய்வு பெரிதாக உள்ளது, சுருக்கவும்");
				document.getElementById('analysis').style.borderColor = "red";
				document.getElementById('analysis').focus();
				return false;
			}
			
			var url = 'saveChartMultiple.php?question=' + question + '&place=' + place + '&date=' + date + '&time=' + time + '&phone=' +'<?php echo $phone;?>' + '&charttype=j' + '&savemode=' +'<?php echo $saveMode;?>' + '&rowid=' +'<?php echo $rowid;?>';
			
			form.action=url;
			form.submit();
			//alert(url);
			//window.open(url);
			
			return url;
		}
		
		//Submit form on date change
		function dateChange(form) { 
			document.getElementById('chart').style.opacity=0.2;
			document.getElementById("loader").style.display = "block";
			set_cookie(focus,'date');
			form.submit();
		}
		
		//Submit form on time change
		function timeChange(form)  {
			document.getElementById('chart').style.opacity=0.2;
			document.getElementById("loader").style.display = "block";
			set_cookie(focus,'time');
			form.submit();
		}
		
		
		//for progress spinner animation
		$(document).ready(function(){
		//javascript
		if(document.getElementById('chart')){
			document.querySelector(
			  "#loader").style.display = "none";
			//document.querySelector(
			//  "chart").style.visibility = "visible";
		}
		else{
			//document.querySelector(
			 // "chart").style.visibility = "hidden";
			document.querySelector(
			  "#loader").style.visibility = "visible";
		}
		
		date='<?php echo $chart_date; ?>';
		time='<?php echo $chart_time; ?>';
		place='<?php echo $place; ?>';
		
		url = 'hora.php?date='+date+'&time='+time+'&place='+place;

		$('#hora').load(url+' div#horaLordTxt'); //getting the current hora lord running the hora.php and extract only the lord text
	});
			

	function showStarLordTable() {
		var z1 = document.getElementById("starlordHeadMutable");
		var z2 = document.getElementById("starlordBodyMutable");
		
		var y1 = document.getElementById("starlordHeadMutable_jama");
		var y2 = document.getElementById("starlordBodyMutable_jama");
		
		if (z1.style.display === "none" && z2.style.display === "none") {
			z1.style.display = "block";
			z2.style.display = "block";
			
			//hidding the jama starlord table if available
			y1.style.display = "none";
			y2.style.display = "none";
		  } else {
			z1.style.display = "none";
			z2.style.display = "none";
		  }
	}
	
	function showStarLordTable_jama() {
		var z1 = document.getElementById("starlordHeadMutable_jama");
		var z2 = document.getElementById("starlordBodyMutable_jama");
		
		var y1 = document.getElementById("starlordHeadMutable");
		var y2 = document.getElementById("starlordBodyMutable");
		
		if (z1.style.display === "none" && z2.style.display === "none") {
			z1.style.display = "block";
			z2.style.display = "block";
			
			//hidding the starlord table if available
			y1.style.display = "none";
			y2.style.display = "none";
		  } else {
			z1.style.display = "none";
			z2.style.display = "none";
		  }
	}
	</script>
</head>

<body onLoad="init();" id="body">
<div id="loader" class="center"></div>

<?php

function addRasiDegJamaPlanet(array $planets)
{
	foreach($planets as $idx => $jama_planet_deg){
		if(empty($jama_planet_deg)){
			$out_planets[$idx] = $jama_planet_deg;
			continue;
		}
		
		$jama_planet_deg = explode(' ',$jama_planet_deg);
		
		$jama_planet = $jama_planet_deg[0];
		mb_internal_encoding("UTF-8");
		$rasi = getRasi_short(ceil($jama_planet_deg[1]/30)-1);
		$rasi_deg = $jama_planet_deg[1] % 30 + $jama_planet_deg[1]-floor($jama_planet_deg[1]);
		
		$out_planets[$idx] = "$planets[$idx] <span class='jama_info'>($rasi-$rasi_deg)</span>";
		debug("$jama_planet ($rasi-$rasi_deg)<BR>");
	}
	
	return $out_planets;
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


function updateUAK_Mandhi(array $in, $sun_degree, $day)
{	
	global $sun_house,$udhayam_house,$aarudam_house,$kavippu_house,$lang, $planetDeg, $mandhi_degree, $sw_planet, $in_degree,$dayofweek ;
	
	$mandhi_house = targetHouse($sun_house,findMandhiHouse($dayofweek,$sun_degree, $day));
	
	debug($mandhi_house);
	
	if(strpos($lang,"Tamil")===0){
		foreach ($in as $key => $value) {
			//echo "<BR>----".$in[$key]."-----";
			//udhayam color update
			$pos = strpos($in[$key], 'உத');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span class="udh">உத</span>';
				//$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;font-size:11pt">'.$in[$key].'</span>';
				$udhayam_house = $key;
				
				if($in_degree==true){
					$planetDeg["உத"]=explode(' ',explode('-',strstr($in[$key],"உத"))[1])[0];
				}
				else{
					$planetDeg["உத"]="-1";
				}
			}
			
			//aarudam color update
			$pos = strpos($in[$key], 'ஆரு');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span class="aar">ஆரு</span>';
				//$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;font-size:11pt">'.$in[$key].'</span>';
				$aarudam_house = $key;
				
				if($in_degree==true){
					$planetDeg["ஆரு"]=explode(' ',explode('-',strstr($in[$key],"ஆரு"))[1])[0];
					//$planetDeg["ஆரு"]=explode(' ',explode('-',$in[$key])[1])[0];
				}
				else{
					$planetDeg["ஆரு"]="-1";
				}
			}
			
			//kavippu color update
			$pos = strpos($in[$key], 'கவி');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span class="kav">கவி</span>';
				//$out[$key] = $out[$key] . ' <span style="color:white;background-color:green;font-size:11pt">'.$in[$key].'</span>';
				$kavippu_house = $key;
				
				if($in_degree==true){
					$planetDeg["கவி"]=explode(' ',explode('-',strstr($in[$key],"கவி"))[1])[0];
					//$planetDeg["கவி"]=explode(' ',explode('-',$in[$key])[1])[0];
				}
				else{
					$planetDeg["கவி"]="-1";
				}
			}
		}
		
		//$sw_planet[$mandhi_house] = $sw_planet[$mandhi_house] . " <span class='mandhi'>மா</span>";
		//$planetDeg["மா"]=$mandhi_degree % 30;
	}
	elseif(strpos($lang,"English")===0){
		foreach ($in as $key => $value) {
			//udhayam color update
			$pos = strpos($in[$key], 'உத');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span style="color:white;background-color:green;">UDH</span>';
				$udhayam_house = $key;
				
				$planetDeg["UDH"]=explode('-',$in[$key])[1];
			}
			
			//aarudam color update
			$pos = strpos($in[$key], 'ஆரு');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span style="color:white;background-color:green;">ARU</span>';
				$aarudam_house = $key;
				
				$planetDeg["ARU"]=explode('-',$in[$key])[1];
			}
			
			//kavippu color update
			$pos = strpos($in[$key], 'கவி');
			if($pos===0 || $pos >0){
				$sw_planet[$key] = $sw_planet[$key] . ' <span style="color:white;background-color:red;">KAV</span>';
				$kavippu_house = $key;
				
				$planetDeg["KAV"]=explode('-',$in[$key])[1];
			}
			
			//mandhi house update
			//if($key == $mandhi_house){
			//	$sw_planet[$key] = $sw_planet[$key] . " <span>Md</span>";
			//}
		}
	}
	
	asort($planetDeg);
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
    $deg = $vars[0]+(isset($vars[1]) ? $vars[1] : 0)/100;
    
	$sun_degree = $deg;
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

/*function planetSort($inArray)
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
				//case "true Node":  //true node
				case "mean Node":  //mean node
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
*/

function displayStarLard_jama()
{
	global $outside;
	
	$tr_sw_planet = $outside; 
	
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
	
	
	foreach($tr_sw_planet as $house => $planets)
	{
		if(empty($planets)){
			continue;
		}
		
		$planet = explode(' ',$planets)[0];
		$deg = explode(' ',$planets)[1];
		
		//starlord calculation
		$starlord_idx = round($deg/3.333333);
		
		$planet_starlord[$planet][0] = $nak_array[$starlord_idx];
		
		$planet_starlord[$planet][1] = $nak_lord[$starlord_idx];
		
		$planet_starlord[$planet][2] = $deg;
		
		if(strpos($planet,"ரா")===0 && (strpos($planets,"கே")===0 || strpos($planets,"கே")>0))
		{
			//starlord calculation
			$starlord_idx = round($deg/3.333333);
			
			$planet_starlord["கே"][0] = $nak_array[$starlord_idx];
			
			$planet_starlord["கே"][1] = $nak_lord[$starlord_idx];
			
			$planet_starlord["கே"][2] = $deg;
		}
	}

	echo "<center><div class='divTable blueTable' id='starlordHeadMutable_jama' style='height:auto;display:none'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=3>";
					echo "<H6 style='font-weight:bold'>ஜாம கிரக நட் சாரம்</H6>";
					echo "</div>";
		echo "</div>";
	echo "</div></center>";

	echo "<center><div class='divTable blueTable' id='starlordBodyMutable_jama' style='height:auto;max-width: fit-content;display:none'>"; 
	echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeadingSmall'>கிரகம்</div>";
				echo "<div class='divPanchangHeadingSmall'>நக்ஷ் பா</div>";
				echo "<div class='divPanchangHeadingSmall'>நக்ஷ் அதி</div>";
				echo "<div class='divPanchangHeadingSmall'>பாகை</div>";
		echo "</div>";
	
	foreach($planet_starlord as $planet => $data)
	{
		echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeadingSmall'>$planet</div>";
				echo "<div class='divPanchangSmall'>".getValue($data[0],1)."</div>";
				echo "<div class='divPanchangSmall'>$data[1]</div>";
				echo "<div class='divPanchangSmall'>" . DECtoDMS($data[2]) . "</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow' id='hidePdf1'>";
	echo "</div>";
	
	echo "</div></center>";
	
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
		  `outside` varchar(250) DEFAULT NULL,
		  `inside` varchar(250) DEFAULT NULL ,
		  `start_time` datetime NOT NULL,
		  `end_time` datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ;";


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

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$place."'"." AND TZ="."'".$TZ."'";

debug($sql);

$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $lat = $row["Latitude"];
    $lon = $row["Longitude"];
    $gmt_offset = $row["GMT_offset"];
    $gmt_offset_dst=$row["GMT_offset_dsl"];
  }
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
//$utcTimestamp1 = getUTCtimestamp($birthTimestamp);

/***** UTC datetime *****/
$date = date('d.m.Y', $utcTimestamp);
$mysql_date = date('Y-m-d', $utcTimestamp);
$time = date('H:i:s', $utcTimestamp);
/***** UTC datetime *****/

/******gmdate automatically converts it to UTC time based on the current set Timezone ***
$date = gmdate('d.m.Y', $birthTimestamp);
$mysql_date = gmdate('Y-m-d', $birthTimestamp);
$time = gmdate('H:i:s', $birthTimestamp);
******/

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
	//exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); //true node
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); //mean node
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); //true node
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); //mean node
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

if($DEBUG==1){ 
var_dump($output);
}

planetSort($output);

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 

$count=0;
foreach ($output as $key => $line)
  {
	global $ragu_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {
		if($count > 8 and $count <= 20){
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = explode(' ',$house)[1];
			$g_bhava_arr[$house-1] = array(round((float)trim($row[1]),4),DECtoDMS(fmod($row[1],30.0)));
			array_push($g_bhava_arr[$house-1],"");
		}
		
		continue;
	}
	
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$planetDeg_default[$row[0]][0] = (float)($row[2]);
	
    if($count!=21){
		$sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($sun_house==0 and strpos($row[0],'Sun')===0){
		$sun_house = (int)($tot_degree[$key]/30)+1;
		updateSunDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	if($moon_house==0 and strpos($row[0],'Moon')===0){
		$moon_house = (int)($tot_degree[$key]/30)+1;
		$moon_degree = getDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
		$moon_degree_exact = $tot_degree[$key] - $sw_house[$key]*30;
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
  echo "<br>[".$sw_planet[0]."]</br>";
  var_dump($sw_planet);
  var_dump($sw_degree);
  var_dump($sw_dir);
}
  $sw_planet = updatePlanet($sw_planet,$sw_dir,$ragu_house);

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

// Update Mandhi
$sw_planet = updateMandhi($sw_planet,$sw_planet,$sun_degree,$day,$dayofweek,$sun_house);

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


if($DEBUG==1){  echo "<BR>". "%%%%prepareJamaChartData inputs - sun_house=" . $sun_house . " sun_degree=" .  $sun_degree . " in_degree=" .  $in_degree .  " in_debug=" .  $DEBUG . "<BR>"; }

startSqlTxn($conn);

try {
	//$sql = "CALL prepareChartData()";
	$sql = "CALL prepareJamaChartData($sun_house,$sun_degree,$in_degree,$DEBUG)"; //in_degree is boolean flag to enable degree display
	
	if($res=mysqli_query($conn, $sql)){
		if($DEBUG==1){ 
			echo "prepareJamaChartData() executed"; 
			//if DEBUG parameter passed to procedure, program will fail. You need to run the procedure in PHPAdmin with same parameters displayed to debug the procedure
			echo "<script>alert('Program failed due to debug mode, Please run prepareJamaChartData($sun_house,$sun_degree,$in_degree,$DEBUG) in PhpAdmmin');</script>";
		}
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
updateUAK_Mandhi($inside,$sun_degree,$day);

if($DEBUG==1){ //testing
	var_export($outside);
	var_export($inside);
}

$stmt->close();

//Update planet name accordingly to language selected in dropdown
$outside = updateOutsidePlanetLanguage($outside);

//Update planet symbol in Jama planets (outside)
//$outside = updateSymbols($outside);
//$outside = updateStrengthLan($outside);
//$sw_planet = updateStrengthLan($sw_planet);
$sw_planet = updateRahuEmma($sw_planet,$dayofweek,$sun_degree,$lang);

$chartdate=date("d/m/Y",strtotime($chart_date));
$Planet_degree=displayDegree($sw_degree); 

$sw_planet = sortDegree($sw_planet);



//to display place in center of 2 cells
$place_left_part = substr($place,0,strlen($place)/2);
$place_right_part = substr($place,strlen($place)/2);

$g_kathir_data = calculateKathirData($sw_planet, $outside); //calculates and returns the karthir data array

updateSookumaRasi($aarudam_house);

calculatePanchang();

$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"],true,true);

//Add additional rasi/deg data for jama planets (outside)
$outside = addRasiDegJamaPlanet($outside);

/******************** Current chart generation *****************************************/
//for returning the chart data alone for quick display (home page, widget display, etc., or sending the chart data to 3rd party)
if($chartNow==1 and isLogged()){
	$_SESSION['jamadata'] = array($sw_planet,$outside);
	$_SESSION['panchang'] = $panchang;

	dbDisconnect($conn);
	$calling_page = $_SERVER['HTTP_REFERER'];
	//using javascript to avoid php already header send error while using header() call
	echo <<<EOL
	<script type="text/javascript">
		window.location.href = '$calling_page';
	</script>
EOL;
	//header("Location: ".$_SERVER['HTTP_REFERER']);
	exit;
}

/*if($autoAnalysisMenu==1){
		echo "<li data-menuanchor='3rdPage'><a href='#3rdPage'>Chart details 2</a></li>";
}

if($cricketMenu==1){
		echo "<li data-menuanchor='4thPage'><a href='#4thPage'>CricketAnalysis</a></li>";
}*/

echo <<<EOL
	<div class="title"></div>
			<div class='column-menu'>
			<div class='desktop'>
EOL;
		displayUserInfo($_SESSION['userinfo']);
echo <<<EOL
				<button class="menu" onClick="window.open('index.php','_self')">Home</button>
				<button class='menu' onClick='window.open("Jamakol_input.php","_self")'>New Chart</button>
				<button class='menu' onClick='window.open("jamaChartDisplay.php","_self")'>Jama Chart history</button>
			</div>
			<div class='mobile'>
EOL;
			//displayUserInfoMobile($_SESSION['userinfo']);
			include "jamaChartMenu_mobile.php";
			$time = date('h:i A',strtotime($chart_time));
			$date = date('d-m-Y',strtotime($chart_date));

echo <<<EOL
			</div>
		</div>
		<div class='column-body' id="pdf">
					<center>
					<P style="color:#660000;font-weight:bold; font-size:16px; text-align:center; background-color:white;width:200px;">ஜாமக்கோள் (DSJV)</P>
					<center><div class="divTable blueTable" style="height:70px;">
								<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
									<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">
										<DIV class='body'>Question</div>
										<!-- <input type="text" name="question" id="question" autocomplete="off" style="background:#ffffff; font-size:12px" value="$in_question" /> -->
										<textarea rows = "2" cols = "60" name="question" id="question" autocomplete="off" style="background:#ffffff; font-size:10px;" placeholder="Enter analysis here..." />$in_question</textarea>
										<form name='updateChart' id='updateChart' action='Jamakol_Chart.php' method='post'>
											<input type='hidden' id='nPlace' name='nPlace' value='$in_place'>
											<input type='hidden' id='nQuestion' name='nQuestion' value='$orig_question'>
											<input type='hidden' id='nDegree' name='nDegree' value=$in_degree>
											<input type='hidden' id='lang' name='lang' value=$lang>
											<input type='hidden' id='nAnalysis' name='nAnalysis' value='$orig_analysis'>
											<input type='hidden' id='qDate' name='qDate' value=$orig_date>
											<input type='hidden' id='qTime' name='qTime' value=$orig_time>
											<input type='hidden' id='qChart' name='qChart' value=0>
											<input type='date' id='nDate' name='nDate' style="color:#660000;font-size:12px;font-weight:bold;width:110px;" value=$chart_date onChange='dateChange(this.form);'>
											<input type='time' id='nTime' name='nTime' style="color:#660000;font-size:12px;font-weight:bold;width:110px" step='1' value=$chart_time onChange='timeChange(this.form);'>
											<BR>
											<input type='button' class='actionBtn' value='கே.நேரம்' Onclick="return questionChart(this.form);">
											<input type='button' class='actionBtn' value='தற்போது' Onclick="return updateNow(this.form);">
	
											<BR><BR>
										</form>
										
									</div>
								</div>
							</div>
				<div class="container" id="container" style="horizontal-align:center">
					
					<div class="divTable blueTable" id="chart">
						
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
									<div class="divTableCell">$sw_planet[11]<span class='kathir'>$g_kathir_data[11]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[11]</span></div>
									<div class="divTableCell">$sw_planet[0]<span class='kathir'>$g_kathir_data[0]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[0]</span></div>
									<div class="divTableCell">$sw_planet[1]<span class='kathir'>$g_kathir_data[1]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[1]</span></div>
									<div class="divTableCell">$sw_planet[2]<span class='kathir'>$g_kathir_data[2]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[2]</span></div>
									<div class="divTableDummyCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableDummyCell"></div>
									<div class="divTableCell">$sw_planet[10]<span class='kathir'>$g_kathir_data[10]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[10]</span></div>
									<div class="divTableCenterCell1">$date</div>
									<div class="divTableCenterCell2">$time</div>
									<div class="divTableCell">$sw_planet[3]<span class='kathir'>$g_kathir_data[3]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[3]</span></div>
									<div class="divTableOuterCell">$outside[3]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$outside[9]</div>
									<div class="divTableCell">$sw_planet[9]<span class='kathir'>$g_kathir_data[9]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[9]</span></div>
									<div class="divTableCenterCell3">$place_left_part</div>
									<div class="divTableCenterCell4">$place_right_part</div>
									<div class="divTableCell">$sw_planet[4]<span class='kathir'>$g_kathir_data[4]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[4]</span></div>
									<div class="divTableDummyCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableDummyCell"></div>
									<div class="divTableCell">$sw_planet[8]<span class='kathir'>$g_kathir_data[8]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[8]</span></div>
									<div class="divTableCell">$sw_planet[7]<span class='kathir'>$g_kathir_data[7]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[7]</span></div>
									<div class="divTableCell">$sw_planet[6]<span class='kathir'>$g_kathir_data[6]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[6]</span></div>
									<div class="divTableCell">$sw_planet[5]<span class='kathir'>$g_kathir_data[5]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[5]</span></div>
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
					<BR>
					<input type='button' class='actionBtn' id='strdtl' value='சாரம் (உள்)' Onclick="showStarLordTable();">
					<input type='button' class='actionBtn' id='strdtl_jama' value='சாரம் (வெளி)'  Onclick="showStarLordTable_jama();">
EOL;
					displayStarLord_jamakol();
					displayStarLard_jama();
					displayPanchang_jamakol();
					
					$filename = 'jamakol_'.$date.'_'.$time;
echo <<<EOL
					<div class="divTable blueTable" style="height:40px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableDummyCell" colspan=3 style="border-radius:  0 0 20px 20px;">
							<form id='analysisData' method='post'>
							<textarea rows = 5 cols = "60" name="analysis" id="analysis" autocomplete="off" style="background:#ffffff; font-size:10px;" placeholder="Enter analysis here..." />$in_analysis</textarea>
							<input type='button' class='actionBtn' id='save' value='$saveBtnTxt' OnClick="return saveChart(this.form);" />
							<!-- <input type='button' class='actionBtn' id='saveToPdf' value='Download' OnClick="GeneratePdf('$filename');" /> -->
							</form>
EOL;
							/*if($autoAnalysisMenu==1){
								echo "<div class='footer'>Scroll down for Auto Analysis details &#9660;</div>";
							}*/
echo <<<EOL
							</div>
					</div>
EOL;
						
						//displayKathirChart($g_kathir_data); //added kathir detail inside the chart itself
echo <<<EOL
					</center>
					
				</div>
			</div>
		
EOL;

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'Jamakol|'.$place."|".$chart_date."|".$chart_time;
	date_default_timezone_set('Asia/Kolkata'); //log activity logs in IST times
	logActivity($conn,$_COOKIE["phone"],$activity,date('Y-m-d H:i:s'));
}

//get punchang, hora data from db (updated regularly by a back-end program)
//[$panchang,$hora] = getSnapshotData();

dbDisconnect($conn);

$_POST["mTime"]="";
$_SESSION["charttime"]="";
$_SESSION["inside"]=$sw_planet;
$_SESSION["outside"]=$outside;

?>

</body>
</html>