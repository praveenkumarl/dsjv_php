<!DOCTYPE html>
<?php
require "lib.php";

$in_place_girl = isset($_POST["place_girl"]) ? $_POST["place_girl"] : $_POST["nPlace_girl"];
$place_girl=strtok($in_place_girl,"|");
$TZ_girl=strtok("|");
$in_mark = isset($_POST["mark"]) ? $_POST["mark"] : -1;
$in_rowid = isset($_POST["rowid"]) ? $_POST["rowid"] : -1;

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$_SESSION["place_girl"]=$place_girl;

$cookie_name = "place_girl";
$cookie_value = $in_place_girl;
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day

$in_place_boy = isset($_POST["place_boy"]) ? $_POST["place_boy"] : $_POST["nPlace_boy"];
$place_boy=strtok($in_place_boy,"|");
$TZ_boy=strtok("|");

$_SESSION["place_boy"]=$place_boy;

$cookie_name = "place_boy";
$cookie_value = $in_place_boy;
setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); // 86400 = 1 day

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
$moon_degree=0;
$transit_moon_degree=0;
$sun_house=0;
$moon_house=0;
$transit_moon_house=0;
$transit_sun_degree=0;
$transit_sun_house=0;
$ragu_house=-1;
$tr_ragu_house=-1;
$sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$sw_degree = array();
$tr_sw_degree = array(null,null,null,null,null,null,null,null,null,null,null,null);
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
$planetDeg_default_transit = array();
$mandhi_degree=0;
$mandhi_degree_transit=0;
$g_boy_nak = "";
$g_girl_nak = "";
$g_girl_karan = "";
$g_boy_karan = "";
$g_panchang_boy = array();
$g_panchang_girl = array();
$g_girl_nak_pad_idx = 0;
$g_boy_nak_pad_idx = 0;
$current_dasa_bukthi=array();
$current_dasa_bukthi_boy=array();
$g_rasi_girl = "";
$g_rasi_boy = "";
$g_girl_thithi_soonyam_arr=array();
$g_boy_thithi_soonyam_arr=array();
$g_thithi_soonyam_matching_arr=array();
$g_dasaBalance=array();
$g_dasaCurrent=array();
$dasa_balance = array(null,null,null); // years, months, days for girl
$birth_dasa_athipathi=""; //for girl
$dasa_balance_boy = array(null,null,null); // years, months, days for boy
$birth_dasa_athipathi_boy=""; //for boy
$g_planet_asp_girl = array();
$g_planet_asp_boy = array();

$g_matching_mark = 0;
$full_sun_deg=0;
$full_sun_deg_boy=0;
$g_planetKaranaLord=array();
$g_planetKaranaLord_boy=array();
$planet_starlord = array();
$planet_starlord_boy = array();

$g_girlage=0;
$g_boyage=0;
$g_mudakku_detail="";
$vayadhotta_rasi_girl=0;
$vayadhotta_rasi_boy=0;
$g_planetVainasigamNak=array();
$g_planetVainasigamNak_boy=array();

$planet_starlord_transit=array();
$planet_starlord=array();
$matching_percent=0;
$g_bhava_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);

$g_seventh_house_disclaimer="";

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time_girl"];
$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date_girl"];
$in_boyname = isset($_POST["boyname"]) ? $_POST["boyname"] : $_POST["nBoyName"];
$in_girlname = isset($_POST["girlname"]) ? $_POST["girlname"] : $_POST["nGirlName"];

$_SESSION["girldate"] = $chart_date;
$_SESSION["girltime"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));
?>

<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/matching_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    
	
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
		
		function saveChart()
		{
			var girlname = document.getElementById("nGirlName").value;
			var boyname = document.getElementById("nBoyName").value;
			var place_girl = document.getElementById("nPlace_girl").value;
			var place_boy = document.getElementById("nPlace_boy").value;
			var girldate = document.getElementById("nGirlDate").value;
			var girltime = document.getElementById("nGirlTime").value;
			var boydate = document.getElementById("nBoyDate").value;
			var boytime = document.getElementById("nBoyTime").value;
			var mark = document.getElementById("matching_mark").innerHTML;
			
			var url = 'saveChartMultiple.php?girlname=' + girlname + '&place_girl=' + place_girl + '&in_date_girl=' + girldate + '&in_time_girl=' + girltime + '&boyname=' + boyname + '&place_boy=' + place_boy + '&in_date_boy=' + boydate + '&in_time_boy=' + boytime + '&mark=' +mark + '&phone=' +'<?php echo $phone; ?>' + '&charttype=m';
			//alert(url);
			window.open(url,'_self');
		}
		
		function showStarLordTable() {
			var x = document.getElementById("DasaMatable");
			var y = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			
			if (z1.style.display === "none" && z2.style.display === "none") {
				z1.style.display = "table";
				z2.style.display = "table";
				x.style.display = "none";
				y.style.display = "none";
			  } else {
				z1.style.display = "none";
				z2.style.display = "none";
				x.style.display = "none";
				y.style.display = "none";
			  }
		}
		
		function showStarLordTable_boy() {
			var x = document.getElementById("DasaMatable_boy");
			var y = document.getElementById("BukthiMatable_boy");
			var z1 = document.getElementById("starlordHeadMutable_boy");
			var z2 = document.getElementById("starlordBodyMutable_boy");
			
			if (z1.style.display === "none" && z2.style.display === "none") {
				z1.style.display = "table";
				z2.style.display = "table";
				x.style.display = "none";
				y.style.display = "none";
			  } else {
				z1.style.display = "none";
				z2.style.display = "none";
				x.style.display = "none";
				y.style.display = "none";
			  }
		}
		
		function showDasaTable() {
			var x = document.getElementById("DasaMatable");
			var y = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			
			if (x.style.display === "none" && y.style.display === "none") {
				x.style.display = "block";
				y.style.display = "block";
				z1.style.display = "none";
				z2.style.display = "none";
			  } else {
				x.style.display = "none";
				y.style.display = "none";
				z1.style.display = "none";
				z2.style.display = "none";
			  }
		}
		
		function showDasaTable_boy() {
			var x = document.getElementById("DasaMatable_boy");
			var y = document.getElementById("BukthiMatable_boy");
			var z1 = document.getElementById("starlordHeadMutable_boy");
			var z2 = document.getElementById("starlordBodyMutable_boy");
			
			if (x.style.display === "none" && y.style.display === "none") {
				x.style.display = "block";
				y.style.display = "block";
				z1.style.display = "none";
				z2.style.display = "none";
			  } else {
				x.style.display = "none";
				y.style.display = "none";
				z1.style.display = "none";
				z2.style.display = "none";
			  }
		}
		
		function displayButtons()
		{
			var btns1 = document.getElementById("buttons_inner");
			var btns2 = document.getElementById("buttons_outer");
						
			btns1.style.display='table';
			btns2.style.display='table';
			
		}
		
		function displayAll()
		{
			var x = document.getElementById("DasaMatable");
			var y = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			var btns = document.getElementById("buttons");
			
			btns.style.display='none';
			x.style.display='table';
			y.style.display='table';
			z1.style.display='table';
			z2.style.display='table';
		}
		
		function old_generatePDF() {
			// hide unncessary elements
			var ele = document.getElementById('menu');
			ele.style.display = 'none';
			
			displayAll();
			
			// Choose the element that our invoice is rendered in.
			const element = document.getElementById('section0');
			
			var opt = {
			  margin:       1,
			  filename:     'pkastro.pdf',
			  image:        { type: 'png', quality: 2.0 },
			  html2canvas:  { scale: 2 },
			  jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
			};
			
			// Choose the element and save the PDF for our user.
			html2pdf().from(element).set(opt).save();
			
			//ele.style.display = 'block';
		}
		
		function generatePdf(){
			var frm = document.getElementById('matchingChart');
			
			$('#loader').show();
			frm.action = 'matching_pdf.php';
			frm.submit();
		}
		
		$(document).ready(function() 
		{
			displayButtons();
			$('#loader').hide();
			
		});
		
		function generateAnthram(in_start, in_end, dasa, bukthi) {
		   var anthram_arr = {};
		   var count = 0;
	 
		   //var start = new Date(in_start).toLocaleDateString();
		   var start = new Date(in_start);
			  
		   var dasa_period = {'கே': 7, 'சுக்': 20, 'சூரி': 6, 'சந்': 10, 'செ': 7, 'ரா': 18, 'குரு': 16, 'சனி': 19, 'புத': 17};
		   var index = Object.keys(dasa_period).indexOf(bukthi);
		   var last_planet = '';
		   var html_str='';
		  
		   //var link = document.createElement('link');
		   //    link.rel = 'stylesheet';
			//   link.href = 'css/main.css';
			//   document.head.appendChild(link);
		  
		   html_str='<center><div class="divTable blueTable" style="width:80%;height:auto">';
		   html_str=html_str+'<div class="divTableRow"><div class="divAnthraHeading" colspan="3">அந்தரம்</div></div></div>';
		   html_str=html_str+'<center><div class="divTable blueTable" style="width:80%;height:auto">';
		   
		   curr_date = new Date();
		   
		   console.log("Anthram");
		   for (var i = index; i <= index + 7; i++) {
			   if (i > 8) {
				 break;
			   }
			   //console.log(Object.keys(dasa_period)[i]);
			   var no_of_days = Math.round((dasa_period[dasa] * dasa_period[bukthi] * Object.values(dasa_period)[i]) / 14400 * 365.2425 * 24 * 60 * 60);
			   var end = new Date(new Date(start).getTime() + no_of_days * 1000);
			  
			   anthram_arr[Object.keys(dasa_period)[i]] = [start, end];
			   //console.log(start + " - " + end);
			  
			   temp1=new Date(start);
			   s_date = String(temp1.getDate()).padStart(2, '0')+'-'+String(temp1.getMonth()+1).padStart(2, '0')+'-'+temp1.getFullYear();
			   temp2=new Date(end);
			   e_date = String(temp2.getDate()).padStart(2, '0')+'-'+String(temp2.getMonth()+1).padStart(2, '0')+'-'+temp2.getFullYear();
			   
			   if(curr_date.getTime() > temp1.getTime() && curr_date.getTime() < temp2.getTime()){
				   html_str = html_str + '<div class="divTableRow"><div class="divAnthraHeading">'+dasa+' / '+bukthi+' / '+Object.keys(dasa_period)[i]+'</div><div class="divCurrAnthra">'+s_date+'</div><div class="divCurrAnthra">'+e_date+'</div></div>';
			   }
			   else{
				   html_str = html_str + '<div class="divTableRow"><div class="divAnthraHeading">'+dasa+' / '+bukthi+' / '+Object.keys(dasa_period)[i]+'</div><div class="divAnthra">'+s_date+'</div><div class="divAnthra">'+e_date+'</div></div>';
			   }
			  
			   //start = new Date(new Date(end).getTime() + 24 * 60 * 60 * 1000);
			   start = end;
			  
			   count++;
			   last_planet = Object.keys(dasa_period)[i];
		   }
		  
		   
		   for (var i = 0; i < 9 - count; i++) {
			   //console.log(Object.keys(dasa_period)[i]);
			   var no_of_days = Math.round((dasa_period[dasa] * dasa_period[bukthi] * Object.values(dasa_period)[i]) / 14400 * 365.2425 * 24 * 60 * 60);
			   var end = new Date(new Date(start).getTime() + no_of_days * 1000);
			   
			   if(i==9-count-1) 
			   { 
					end = new Date(in_end);
				}
			   
			   anthram_arr[Object.keys(dasa_period)[i]] = [start, end];
			  
			   temp1=new Date(start);
			   s_date = String(temp1.getDate()).padStart(2, '0')+'-'+String(temp1.getMonth()+1).padStart(2, '0')+'-'+temp1.getFullYear();
			   temp2=new Date(end);
			   e_date = String(temp2.getDate()).padStart(2, '0')+'-'+String(temp2.getMonth()+1).padStart(2, '0')+'-'+temp2.getFullYear();
			  
			   if(curr_date.getTime() > temp1.getTime() && curr_date.getTime() < temp2.getTime()){
				   html_str = html_str + '<div class="divTableRow"><div class="divAnthraHeading">'+dasa+' / '+bukthi+' / '+Object.keys(dasa_period)[i]+'</div><div class="divCurrAnthra">'+s_date+'</div><div class="divCurrAnthra">'+e_date+'</div></div>';
			   }
			   else{
				   html_str = html_str + '<div class="divTableRow"><div class="divAnthraHeading">'+dasa+' / '+bukthi+' / '+Object.keys(dasa_period)[i]+'</div><div class="divAnthra">'+s_date+'</div><div class="divAnthra">'+e_date+'</div></div>';
			   }
			  
			   //start = new Date(new Date(end).getTime() + 24 * 60 * 60 * 1000);
			   start = end;
			  
			   last_planet = Object.keys(dasa_period)[i];
		   }
		   //console.log(anthram_arr);
		   
		   html_str=html_str+"</div></center>";
		  
		   document.getElementById('anthramDiv').innerHTML = html_str;
		   document.getElementById('anthramDiv').style.display = 'block';
		  
		   return anthram_arr;
     }
				
	</script>
</head>

<body onLoad="init();">
<div class="anthramDiv" id="anthramDiv" onclick="this.style.display='none'"></div>
<div id="loader" class="center"></div>

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
	
	$planetDeg_transit['கே']=$planetDeg_transit['ரா'];
	asort($planetDeg_transit);
	
	return $out;
}

function updateMandhi_transit(array $out, array $in, $in_sun_degree, $in_day, $in_dayofweek,$in_sun_house)
{	
	global $transit_datetime,$transit_today_sunrise,$planetDeg_transit,$mandhi_degree_transit, $planetDeg_default_transit;
	
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
			$planetDeg_default_transit["மா"] = ($mandhi_degree_transit % 30);
		}
	}
	
	asort($planetDeg_transit);
	
	return $out;
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

/*function displayChart($in,$out)
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
					<div class="divTableCell">$in[11]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[0]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[1]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[2]<span class='symbol'>&#9791;<Cspan></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[10]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell1"></div>
					<div class="divTableCenterCell2"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;<span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell3"></div>
					<div class="divTableCenterCell4"></div>
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
}*/


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
*/

function old_sortDegree($inArray) // new function
{
	global $planetDeg,$mandhi_degree;
	
	$kethuUpdated = false;
	$mandhiUpdated = false;
	$outArray=array(null,null,null,null,null,null,null,null,null,null,null,null);
	
	foreach ($planetDeg as $planet => $deg) {
		foreach ($inArray as $house => $inPlanet)
		{	
			//$inArray[$house] = str_replace($planet, $planet. " " . $planetDeg[$planet],$inArray[$house]);
			
			if(strpos($inArray[$house],$planet)===0 || strpos($inArray[$house],$planet)>0){	
				if(strpos($inArray[$house],'red">'.$planet)>0 || strpos($inArray[$house],'red">'.$planet)===0){
					$outArray[$house] = $outArray[$house] . " " . "<span style='color:red'>$planet</span>" . "-". $planetDeg[$planet];
				}
				else{
					$outArray[$house] = $outArray[$house] . " " . $planet . "-". $planetDeg[$planet];
				}
			}
			
			if($kethuUpdated==false && (strpos($inArray[$house],"கே")===0 || strpos($inArray[$house],"கே")>0)){	
					$outArray[$house] = $outArray[$house] . " " . "கே" . "-". $planetDeg["ரா"];
					$kethuUpdated = true;
			}
			
			if($mandhiUpdated==false && (strpos($inArray[$house],"மா")===0 || strpos($inArray[$house],"மா")>0)){	
				$outArray[$house] = $outArray[$house] . " " . "மா" . "-". strval(DECtoDMS($mandhi_degree % 30.0));
				$mandhiUpdated = true;
			}
			
			/*//$inArray[$house] = str_replace($planet, $planet . "-". $planetDeg[$planet],$inArray[$house]);
				
			if(strpos($planet,"ரா")===0 and strpos($inPlanet,"கே")===0){
				//$inArray[$house] = str_replace($inPlanet, trim($inPlanet). "." . $planetDeg[$planet],$inArray[$house]);
				$inArray[$house] = substr_replace($inArray[$house], "-".$planetDeg[$planet],6,0);
				
			}*/
		}
	}
	
	//$inArray = str_replace("மா", "மா". "-" . strval($mandhi_degree % 30.0),$inArray);
	
	//return $inArray;
	return $outArray;
}

//find house
function findHouse($inArray,$inStr)
{
	foreach($inArray as $house => $planets){
		if(strpos($planets,$inStr)===0 || strpos($planets,$inStr)>0){
			return $house;
		}
	}
}

// Lagna matching by position/raasi
function matchingRule1()
{
	global $sw_planet, $tr_sw_planet, $g_matching_mark;
	
	$result = false;
	
	$reason = "";
	
	$girlChart_lagna = findHouse($sw_planet,"லக்")+1;
	$boyChart_lagna = findHouse($tr_sw_planet,"லக்")+1;
	
	$girlLagna_to_boyLagna = countHouses($girlChart_lagna,$boyChart_lagna);
	$boyLagna_to_girlLagna = countHouses($boyChart_lagna,$girlChart_lagna);

	if(($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 1) ||     
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 1 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 3 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 5 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 7 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 1) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 3) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 5) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 7) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 9) ||    
		($girlLagna_to_boyLagna == 9 && $boyLagna_to_girlLagna == 11) ||   
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 1) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 3) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 5) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 7) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 9) ||     
		($girlLagna_to_boyLagna == 11 && $boyLagna_to_girlLagna == 11)){     
		$result = true;
		$reason = $girlLagna_to_boyLagna."/".$boyLagna_to_girlLagna;
	}
	else{
		$result = false;
		$reason = $girlLagna_to_boyLagna."/".$boyLagna_to_girlLagna;	
	}
	
	if($result == true){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>1. லக்கினம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>1. லக்கினம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	
}

//traditional star matching (11 points)
function matchingRule12()
{
	global $g_matching_mark, $g_girl_nak_pad_idx, $g_boy_nak_pad_idx;
	$result = false;
	$result_txt = "";
	
	$star_match_count = 0;
	
	$girl_to_boy_start_count = starCount(ceil($g_girl_nak_pad_idx/4), ceil($g_boy_nak_pad_idx/4));  //divide by 4 to get nak idx
	//1 தினம்
	$thinam = [2, 4, 6, 8, 9, 11, 13, 15, 17, 18, 20, 24, 26]; //removed 22 as it's vainasigam
	
	if(array_search($girl_to_boy_start_count,$thinam)){
		$result_txt = $result_txt . "தினம் பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "தினம் பொருத்தம் &#x2716;";
	}
	
	//2 கணம்
	if(ganaMatching())
	{
		$result_txt = $result_txt . "<BR>கண பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>கண பொருத்தம் &#x2716;";
	}
	
	//மகேந்திர பொருத்தம்
	$mahendra = [4,7,10,13,16,19,22,25];
	
	if(array_search($girl_to_boy_start_count,$mahendra)){
		$result_txt = $result_txt . "<BR>மகேந்திர பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>மகேந்திர பொருத்தம் &#x2716;";
	}
	
	//ஸ்திரீ தீர்க்கம்
	if($girl_to_boy_start_count>13){
		$result_txt = $result_txt . "<BR>ஸ்திரீ தீர்க்கம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ஸ்திரீ தீர்க்கம் &#x2716;";
	}
	
	//யோனி பொருத்தம்
	if(yoniMatch()){
		$result_txt = $result_txt . "<BR>யோனி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>யோனி பொருத்தம் &#x2716;";
	}
	
	//ராசி பொருத்தம்
	if(matchingRule2(false)){
		$result_txt = $result_txt . "<BR>ராசி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ராசி பொருத்தம் &#x2716;";
	}
	
	//ராசி அதிபதி பொருத்தம் 
	if(checkRaasiAthipathiMatching()){
		$result_txt = $result_txt . "<BR>ராசி அதிபதி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ராசி அதிபதி பொருத்தம் &#x2716;";
	}
	
	//வசிய பொருத்தம் 
	if(vasiyaMatching()){
		$result_txt = $result_txt . "<BR>வசிய பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>வசிய பொருத்தம் &#x2716;";
	}
	
	//ரஜ்ஜு பொருத்தம் 
	if(matchingRule8(false)){
		$result_txt = $result_txt . "<BR>ரஜ்ஜு பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>ரஜ்ஜு பொருத்தம் &#x2716;";
	}
	
	//வேதை பொருத்தம் 
	if(matchingRule8(false)){
		$result_txt = $result_txt . "<BR>வேதை பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>வேதை பொருத்தம் &#x2716;";
	}
	
	//நாடி பொருத்தம் 
	if(naadiMatching()){
		$result_txt = $result_txt . "<BR>நாடி பொருத்தம் &#x2714;";
		$star_match_count++;
	}
	else{
		$result_txt = $result_txt . "<BR>நாடி பொருத்தம் &#x2716;";
	}	
	
	if($star_match_count >= MIN_STAR_MATCH_COUNT){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>12. நட்சத்திர பொருத்தம்</div>";
			$final_result = "&#x2705;(".$star_match_count . "/" . TOTAL_STAR_MATCH_COUNT . ")" . "<BR>";
			echo "<div class='divPanchang'>$final_result<div class='divStarMatch'>$result_txt</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>12. நட்சத்திர பொருத்தம்</div>";
			$final_result = "&#x274C;(".$star_match_count . "/" . TOTAL_STAR_MATCH_COUNT . ")";
			echo "<div class='divPanchang'>$final_result<div class='divStarMatch'>$result_txt</div></div>";
		echo "</div>";
	}
}

//naadi poruththam
function naadiMatching(){
	global $g_boy_nak_pad_idx, $g_girl_nak_pad_idx;
	
	$result = true;
	
	$vaatha = [1,6,7,12,13,18,19,24,25]; //அசுவினி, திருவாதிரை, புனர்பூசம், உத்தரம், அஸ்தம், கேட்டை, மூலம், சதயம், பூரட்டாதி  
	$pithaa = [2,5,8,11,14,17,20,23,26]; //பரணி, மிருகசீரிடம், பூசம், பூரம், சித்திரை, அனுசம், பூராடம், அவிட்டம், உத்ரட்டாதி      
	$neer = [3,4,9,10,15,16,21,22,27]; //கார்த்திகை, ரோகிணி, ஆயில்யம், மகம், சுவாதி, விசாகம், உத்ராடம், திருவோணம், ரேவதி
	
	$girl_star = ceil($g_girl_nak_pad_idx/4);
	$boy_star = ceil($g_boy_nak_pad_idx/4);
	
	if(array_search($girl_star,$vaatha) && array_search($boy_star,$vaatha)){
		$result = false;
	}
	
	if(array_search($girl_star,$pithaa) && array_search($boy_star,$pithaa)){
		$result = false;
	}
	
	if(array_search($neer,$pithaa) && array_search($neer,$pithaa)){
		$result = false;
	}
	
	return $result;
	
}

//Girl/boy Sun/Sat/Rah/Kethu/Mars in 7th house, warning
function matchingRule13(){
	global $sw_planet, $tr_sw_planet,$g_matching_mark,$g_seventh_house_disclaimer;
	
	$result=true;
	
	$girl_sun = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சூரி"));
	$girl_sat = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சனி"));
	$girl_rah = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"ரா"));
	$girl_ket = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"கே"));
	$girl_mar = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"செ"));
	
	$boy_sun = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சூரி"));
	$boy_sat = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சனி"));
	$boy_rah = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"ரா"));
	$boy_ket = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"கே"));
	$boy_mar = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"செ"));
	
	if($girl_sun == 7 || $girl_sat == 7 || $girl_rah == 7 || $girl_ket == 7 || $girl_mar == 7
		|| $boy_sun == 7 || $boy_sat == 7 || $boy_rah == 7 || $boy_ket == 7 || $boy_mar == 7){
		$result = false;
		//$g_matching_mark = $g_matching_mark-2;
		$g_matching_mark = $g_matching_mark/2; //make it half to make sure final result is red due to this critical rule
		$g_seventh_house_disclaimer = "7ஆம் இடத்தில் சுரி/சனி/ரா/கே/செ, மிக கவனமாக பொருத்தம் பார்த்து முடிவு எடுக்கவும்";
	}
}

function display_7thHouse_disclaimer(){
	global $g_seventh_house_disclaimer;
	
	echo <<<EOL
	<div class="divDisclaimer" colspan=3>
		<H6 style='font-weight;color:red'>*$g_seventh_house_disclaimer</H6>
	</div>
EOL;
}


function vasiyaMatching()
{
	global $g_rasi_girl, $g_rasi_boy;
	
	$result = false;
	
	if($g_rasi_girl==1 and ($g_rasi_boy==5 or $g_rasi_boy==8)){
		$result = true;
	}
	
	if($g_rasi_girl==2 and ($g_rasi_boy==4 or $g_rasi_boy==7)){
		$result = true;
	}
	
	if($g_rasi_girl==3 and $g_rasi_boy==6){
		$result = true;
	}
	
	if($g_rasi_girl==4 and ($g_rasi_boy==8 or $g_rasi_boy==9)){
		$result = true;
	}
	
	if($g_rasi_girl==5 and $g_rasi_boy==10){
		$result = true;
	}
	
	if($g_rasi_girl==6 and ($g_rasi_boy==2 or $g_rasi_boy==12)){
		$result = true;
	}
	
	if($g_rasi_girl==7 and $g_rasi_boy==10){
		$result = true;
	}
	
	if($g_rasi_girl==8 and ($g_rasi_boy==4 or $g_rasi_boy==6)){
		$result = true;
	}
	
	if($g_rasi_girl==9 and $g_rasi_boy==12){
		$result = true;
	}
	
	if($g_rasi_girl==10 and $g_rasi_boy==11){
		$result = true;
	}
	
	if($g_rasi_girl==11 and $g_rasi_boy==12){
		$result = true;
	}
	
	if($g_rasi_girl==12 and $g_rasi_boy==10){
		$result = true;
	}
	
	return $result;
	
}

function checkRaasiAthipathiMatching()
{
	global $g_rasi_girl, $g_rasi_boy,$g_matching_mark;
	
	$result = true;
	$reason = "";
	
	$girlRasiLord = findAthipathi($g_rasi_girl-1);
	$boyRasiLord = findAthipathi($g_rasi_boy-1);
	
	if(strcmp($girlRasiLord,$boyRasiLord)==0){    //same rasi return true;
		return $result; //true
	}
	
	if((strcmp($girlRasiLord,"சூரி")==0 and strcmp($boyRasiLord,"சுக்")==0) //sun enemy to ven
		or (strcmp($boyRasiLord,"சுக்")==0 and strcmp($girlRasiLord,"சூரி")==0) //sun enemy to ven
		or (strcmp($girlRasiLord,"சூரி")==0 and strcmp($boyRasiLord,"சனி")==0) 		//sun enemy to sat
		or (strcmp($boyRasiLord,"சூரி")==0 and strcmp($girlRasiLord,"சனி")==0)){   //sun enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"செ")==0 and strcmp($boyRasiLord,"புத")==0) 		//mars enemy to mer
		or (strcmp($boyRasiLord,"செ")==0 and strcmp($girlRasiLord,"புத")==0) 	//mars enemy to mer
		or (strcmp($girlRasiLord,"செ")==0 and strcmp($boyRasiLord,"சனி")==0) 		//mar enemy to sat
		or (strcmp($boyRasiLord,"செ")==0 and strcmp($girlRasiLord,"சனி")==0)){   //mar enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"புத")==0 and strcmp($boyRasiLord,"சந்")==0) 		//mer enemy to moon
		or (strcmp($boyRasiLord,"புத")==0 and strcmp($girlRasiLord,"சந்")==0) 	//mer enemy to moon
		or (strcmp($girlRasiLord,"சந்")==0 and strcmp($boyRasiLord,"சனி")==0) 		//moo enemy to sat
		or (strcmp($boyRasiLord,"சந்")==0 and strcmp($girlRasiLord,"சனி")==0)){   //moo enemy to sat
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"குரு")==0 and strcmp($boyRasiLord,"சுக்")==0) 		//jup enemy to ven
		or (strcmp($boyRasiLord,"குரு")==0 and strcmp($girlRasiLord,"சுக்")==0) 		//jup enemy to ven
		or (strcmp($girlRasiLord,"குரு")==0 and strcmp($boyRasiLord,"புத")==0) 		//Jup enemy to mer
		or (strcmp($boyRasiLord,"குரு")==0 and strcmp($girlRasiLord,"புத")==0)){   //Jup enemy to mer
		$result = false;
	}
	
	if((strcmp($girlRasiLord,"சுக்")==0 and strcmp($boyRasiLord,"சந்")==0) 		//ven enemy to moon
		or (strcmp($boyRasiLord,"சுக்")==0 and strcmp($girlRasiLord,"சந்")==0)){   //ven enemy to moon
		$result = false;
	}
	
	return $result;
}

function displayMatchingResult()
{
	global $g_matching_mark,$matching_percent;
	
	$matching_percent = ceil($g_matching_mark/TOTAL_MATCH_RULES * 100);
	
	//if($matching_percent >=70) //possible good match
	if($matching_percent >=60) //possible good match
	{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultGreen'>$matching_percent%</div>";
		echo "</div>";
	}
	//elseif($matching_percent >50 && $matching_percent <70) //average match
	elseif($matching_percent >=40 && $matching_percent <60) //average match
	{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultOrange'>$matching_percent%</div>";
		echo "</div>";
	}
	else{   //bad match
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>பொருத்த மதிப்பு</div>";
			echo "<div class='divMatchingResultRed'>$matching_percent%</div>";
		echo "</div>";
	}
	
	echo "<div class='divTableRow'>";
		echo "<div class='divPanchangHeading' style='text-align:left'>மதிப்பு விவரம்</div>";
		echo "<div class='divPanchang' style='text-align:left;line-height:15px'>
		<!-- <span id='boot-icon' class='bi bi-x-square-fill' style='color: #ff8080;'></span> தவிர்க்க (0-50)<BR>
		<span id='boot-icon' class='bi bi-dash-square-fill' style='color: #FFBF00;'></span> சராசரி (50-69)<BR>
		<span id='boot-icon' class='bi bi-check-square-fill' style='color: #00FF00;'></span> உத்தமம் (70-100) -->
		<span id='boot-icon' class='bi bi-x-square-fill' style='color: #ff8080;'></span> தவிர்க்க (0-40)<BR>
		<span id='boot-icon' class='bi bi-dash-square-fill' style='color: #FFBF00;'></span> சராசரி (40-60)<BR>
		<span id='boot-icon' class='bi bi-check-square-fill' style='color: #00FF00;'></span> உத்தமம் (60-100)
		</div>";
	echo "</div>";
	
	echo "<div style='display:none' id='matching_mark'>$matching_percent%</div>";
}

// The house where Mars placed should not have Rahu, Sani in matching chart and viz versa
function matchingRule122()
{
	global $sw_planet, $tr_sw_planet;
	
	$boyChart_mars = findHouse($sw_planet,"செ");
	$boyChart_sat = findHouse($sw_planet,"சனி");
	$boyChart_rah = findHouse($sw_planet,"ரா");
	
	$girlChart_mars = findHouse($tr_sw_planet,"செ");
	$girlChart_sat = findHouse($tr_sw_planet,"சனி");
	$girlChart_rah = findHouse($tr_sw_planet,"ரா");
	
	if($boyChart_mars == $girlChart_sat || $boyChart_mars == $girlChart_rah){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangRed'>&#x274C;</div>";
		echo "</div>";
	}
	else if ($girlChart_mars == $boyChart_sat || $girlChart_mars == $boyChart_rah){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Rahu/Sani over Mars)</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Rahu/Sani over Mars</div>";
			echo "<div class='divPanchangGreen'>&#x2705; (Rahu/Sani not over Mars)</div>";
		echo "</div>";
	}
}

// male/female raasi matching (Moon)
function matchingRule2($display)
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark, $g_rasi_girl, $g_rasi_boy;
	
	$g_rasi_girl = findHouse($sw_planet,"சந்")+1; 
	$g_rasi_boy = findHouse($tr_sw_planet,"சந்")+1; 
	
	if($g_rasi_boy % 2 == 1 && ($g_rasi_girl % 2 == 1 || $g_rasi_girl % 2 == 0 )){
		$result = true;
		$reason = "<BR>" . "பெண்-" . ($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	elseif(($g_rasi_girl % 2 == 0 && ($g_rasi_boy % 2 == 1 || $g_rasi_boy % 2 == 0 ))){
		$result = true;
		$reason = "<BR>". "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	elseif($g_rasi_girl % 2 == 1 && $g_rasi_boy % 2 == 1){
		$result = true;
		$reason = "<BR>" . "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	else{
		$result = false;
		$reason = "<BR>" . "பெண்-" .($g_rasi_girl % 2 == 0 ? "பெண்" : "ஆண்") . "/" . "ஆண்-" . ($g_rasi_boy % 2 == 0 ? "பெண்" : "ஆண்");
	}
	
	if(!$display){
		return $result;
	}
	
	if($result == false){
		//echo "---Rasi rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>2. ராசி/சந்திரன்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Rasi rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>2. ராசி/சந்திரன்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
	return $result;
}

// One Neesa graka should not be neesa in another chart viz versa
function matchingRule22()
{
	global $sw_planet, $tr_sw_planet;
	
	if(findHouse($sw_planet,"செ")==3 && findHouse($tr_sw_planet,"செ")==3){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Mar/செ)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சூரி")==6 && findHouse($tr_sw_planet,"சூரி")==6){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Sun/சூரி)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சந்")==7 && findHouse($tr_sw_planet,"சந்")==7){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Moo/சந்)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"புத")==11 && findHouse($tr_sw_planet,"புத")==11){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Mer/புத)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சுக்")==5 && findHouse($tr_sw_planet,"சுக்")==5){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Ven/சுக்)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"குரு")==9 && findHouse($tr_sw_planet,"குரு")==9){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Jup/குரு)</div>";
		echo "</div>";
	}
	elseif(findHouse($sw_planet,"சனி")==0 && findHouse($tr_sw_planet,"சனி")==0){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangRed'>&#x274C; (Sat/சனி)</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Neesam match</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
}

// Karanam matching rule
function matchingRule3()
{
	global $sw_planet, $tr_sw_planet, $g_girl_karan, $g_boy_karan,$g_matching_mark;
	
	$karan = array("Bava/பவ","Balava/பாலவ","Kaulava/கௌலவ","Taitula/தைதுலை","Garija/கரசை","Vanija/வணிசை","Visti/பத்தரை","Sakuni/சகுனி","Chatuspada/சதுஷ்பாத","Naga/நாகவ","Kimstughna/கிம்ஸ்துக்கினம்");
	$karan_planet = array("செ","ரா","சனி","சுக்","சந்","சூரி","கே","சனி","குரு","ரா","புத");
	
	$result = false;
	$reason = "";
	
	$girl_karan_planet = array_search($g_girl_karan,$karan);
	$boy_karan_planet = array_search($g_boy_karan,$karan);
	
	debug('GIRL KARAN IDX='.$girl_karan_planet);
	debug('BOY KARAN IDX='.$boy_karan_planet);
	
	if(($girl_karan_planet==5 and $boy_karan_planet==3) //sun enemy to ven
		or ($girl_karan_planet==3 and $boy_karan_planet==5) //sun enemy to ven
		or ($girl_karan_planet==5 and $boy_karan_planet==2 or $boy_karan_planet==7) 		//sun enemy to sat
		or ($boy_karan_planet==5 and ($girl_karan_planet==2 or $girl_karan_planet==7))){   //sun enemy to sat
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==0 and $boy_karan_planet==10) //mars enemy to mer
		or ($girl_karan_planet==10 and $boy_karan_planet==0)){   
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==10 and $boy_karan_planet==4) //mer enemy to moon
			or ($girl_karan_planet==4 and $boy_karan_planet==10) //moon enemy to mer
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==8 and $boy_karan_planet==10) 	//Jup enemy to mer
			or ($girl_karan_planet==10 and $boy_karan_planet==8) //mer enemy to jup
			or ($girl_karan_planet==10 and $boy_karan_planet==3) //jup enemy to ven
			or ($girl_karan_planet==3 and $boy_karan_planet==10) //ven enemy to jup
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==7 and $boy_karan_planet==5) 	//sat enemy to sun
			or ($girl_karan_planet==5 and $boy_karan_planet==7) //sun enemy to sat
			or ($girl_karan_planet==7 and $boy_karan_planet==4) //sat enemy to moo
			or ($girl_karan_planet==4 and $boy_karan_planet==7) //moo enemy to sat
			or ($girl_karan_planet==7 and $boy_karan_planet==0) //sat enemy to mar
			or ($girl_karan_planet==0 and $boy_karan_planet==7) //mar enemy to sat
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif(($girl_karan_planet==3 and $boy_karan_planet==5) 	//ven enemy to sun
			or ($girl_karan_planet==5 and $boy_karan_planet==3) //sun enemy to ven
			or ($girl_karan_planet==3 and $boy_karan_planet==4) //ven enemy to moo
			or ($girl_karan_planet==4 and $boy_karan_planet==3) //moo enemy to ven
			){  
		$result = false;
		$reason = $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] . " பகை";
	}
	elseif($girl_karan_planet == $boy_karan_planet){
		$result = true;
		$reason = "<BR>" . $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] ;
	}
	else{
		$result = true;
		$reason = "<BR>" . $karan_planet[$girl_karan_planet] . "/" . $karan_planet[$boy_karan_planet] ;
	}
	
	if($result == false){
		//echo "---Karana rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>3. கரணம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Karana rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>3. கரணம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

// Lagna lords should not be 6 or 8. Also lagna should not be in the 6th or 8th to Venues and sukran
function matchingRule4()
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark;
	
	$result = array(true,true,true);
	$reason = "";
	
	$girlLagnaHouse = findHouse($sw_planet, "லக்");
	$boyLagnaHouse = findHouse($tr_sw_planet, "லக்");
	
	$girlLagnaLord = findAthipathi($girlLagnaHouse);
	$boyLagnaLord = findAthipathi($boyLagnaHouse);
	
	$girlVenHouse = findHouse($sw_planet, "சுக்");
	$boyVenHouse = findHouse($tr_sw_planet, "சுக்");
	
	$girlMarHouse = findHouse($sw_planet, "செ");
	$boyMarHouse = findHouse($tr_sw_planet, "செ");
	
	$girlLagnaLordHouse = findHouse($sw_planet, $girlLagnaLord);
	$boyLagnaLordHouse = findHouse($tr_sw_planet, $boyLagnaLord);
	
	//$girlBoyLagnaCount = countHouses($girlLagnaHouse, $boyLagnaHouse);
	$girlChart_lagna = findHouse($sw_planet,"லக்")+1;
	$boyChart_lagna = findHouse($tr_sw_planet,"லக்")+1;
	
	$girlLagna_to_boyLagna = countHouses($girlChart_lagna,$boyChart_lagna);
	$boyLagna_to_girlLagna = countHouses($boyChart_lagna,$girlChart_lagna);
	
	$girlLagnaLordBoyVenCount = countHouses($girlLagnaLordHouse, $boyVenHouse);
	$boyLagnaLordGirlVenCount = countHouses($boyLagnaLordHouse, $girlVenHouse);
	
	$girlLagnaLordBoyMarCount = countHouses($girlLagnaLordHouse, $boyMarHouse);
	$boyLagnaLordGirlMarCount = countHouses($boyLagnaLordHouse, $girlMarHouse);
	
	//if($girlBoyLagnaCount == 6 || $girlBoyLagnaCount == 8){
	if($girlLagna_to_boyLagna == 6 || $boyLagna_to_girlLagna == 8){
		//echo "<br>Lagna 6/8";
		$reason = "<BR>&#x2022; லக்கினம் 6/8";
		$result[0] = false;
	}
	else{
		$reason = "<BR>லக்கினம் ".$girlLagna_to_boyLagna.'/'.$boyLagna_to_girlLagna;
		$result[0] = true;
	}
	
	if($girlLagnaLordBoyVenCount == 6 || $girlLagnaLordBoyVenCount == 8){
		$result[1] = false;
		//echo "<br>Girl Venus-Boy lagna lord 6/8";
		$reason = $reason . "<BR>&#x2022; பெண் லக்கினாதிபதி & ஆண் சுக் 6/8";
	}
	else{
		$reason = $reason  . "<BR>&#x2022; பெண் லக்கினாதிபதி & ஆண் சுக் ". "(" . $girlLagnaLordBoyVenCount . ")";
		$result[1] = true;
	}
	
	if($boyLagnaLordGirlMarCount == 6 || $boyLagnaLordGirlMarCount == 8){
		$result[2] = false;
		//echo "<br>Boy Mars-Girl lagna lord 6/8";
		$reason = $reason . "<BR>&#x2022; ஆண் லக்கினாதிபதி & பெண் செ 6/8";
	}
	else{
		$reason = $reason  . "<BR>&#x2022; ஆண் லக்கினாதிபதி & பெண் செ ". "(" . $boyLagnaLordGirlMarCount . ")";
		$result[2] = true;
	}
	
	if($result[0] == true && $result[1] == true && $result[2] == true){
		//echo "<br>Lagna lord rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>4. ஆண்/பெண்<BR>(லக்/செவ்/சுக்/2/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	else{
		//echo "---Lagna lord rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>4. ஆண்/பெண்<BR>(லக்/செவ்/சுக்/2/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
}

// Lagna matching, panjcha bootha, paathaga
function matchingRule41()
{
	global $sw_planet, $tr_sw_planet;
	
	$result = false;
	
	$reason = "";
	
	$boyChart_lagna = findHouse($sw_planet,"லக்");
	$girlChart_lagna = findHouse($tr_sw_planet,"லக்");

	if($boyChart_lagna == $girlChart_lagna){
		$result = true;
	}
	elseif(($boyChart_lagna == 0 && $girlChart_lagna == 4) ||      //based on pancha bootha fire x fire
			($boyChart_lagna == 0 && $girlChart_lagna == 8) ||     // fire x fire
			($boyChart_lagna == 4 && $girlChart_lagna == 8) ||     // fire x fire
			($boyChart_lagna == 1 && $girlChart_lagna == 5) ||     // land x land
			($boyChart_lagna == 1 && $girlChart_lagna == 9) ||     // land x land
			($boyChart_lagna == 5 && $girlChart_lagna == 9) ||     // land x land
			($boyChart_lagna == 2 && $girlChart_lagna == 6) ||     // air x air
			($boyChart_lagna == 2 && $girlChart_lagna == 10) ||    // air x air
			($boyChart_lagna == 6 && $girlChart_lagna == 10) ||    // air x air
			($boyChart_lagna == 3 && $girlChart_lagna == 7) ||     // water x water
			($boyChart_lagna == 3 && $girlChart_lagna == 11) ||    // water x water
			($boyChart_lagna == 7 && $girlChart_lagna == 11) ||    // water x water
			($boyChart_lagna == 0 && $girlChart_lagna == 2) ||     // fire x air
			($boyChart_lagna == 0 && $girlChart_lagna == 5) ||     // fire x air
			($boyChart_lagna == 0 && $girlChart_lagna == 10) ||    // fire x air
			($boyChart_lagna == 2 && $girlChart_lagna == 0) ||     // air x fire
			($boyChart_lagna == 5 && $girlChart_lagna == 0) ||     // air x fire
			($boyChart_lagna == 10 && $girlChart_lagna == 0) ||    // air x fire
			($boyChart_lagna == 1 && $girlChart_lagna == 3) ||     // land x water
			($boyChart_lagna == 1 && $girlChart_lagna == 7) ||     // land x water
			($boyChart_lagna == 1 && $girlChart_lagna == 11) ||    // land x water
			($boyChart_lagna == 3 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 7 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 11 && $girlChart_lagna == 1) ||     // water x land
			($boyChart_lagna == 4 && $girlChart_lagna == 10) ||     // fire x air
			($boyChart_lagna == 4 && $girlChart_lagna == 6) ||      // fire x air
			($boyChart_lagna == 4 && $girlChart_lagna == 2)){       // fire x air
		$result = true;
	}
	else{
		$result = false;
		$reason = "Pancha Bootha";
	}
	
	if(($boyChart_lagna == 0 && $girlChart_lagna == 10) || //paathaga sthana
		($boyChart_lagna == 1 && $girlChart_lagna == 8) || 
		($boyChart_lagna == 2 && $girlChart_lagna == 8) ||
		($boyChart_lagna == 3 && $girlChart_lagna == 1) ||
		($boyChart_lagna == 4 && $girlChart_lagna == 0) ||
		($boyChart_lagna == 5 && $girlChart_lagna == 11) ||
		($boyChart_lagna == 6 && $girlChart_lagna == 4) ||
		($boyChart_lagna == 7 && $girlChart_lagna == 3) ||
		($boyChart_lagna == 8 && $girlChart_lagna == 2) ||
		($boyChart_lagna == 9 && $girlChart_lagna == 7) ||
		($boyChart_lagna == 10 && $girlChart_lagna == 6) ||
		($boyChart_lagna == 11 && $girlChart_lagna == 5)){
		//echo "<BR>Lagna rule failed (paathaga)";
		$result = false;
		$reason = "Pathaga Sthaana";
	}
	
	if($result == true){
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Lagna match (Pancha Pootha/Pathaga)</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
	else{
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Lagna match (Pancha Pootha/Pathaga)</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	
}

//this function fills all planet aspects in girl chart
function fillPlanetAspectsGirl(){
	global $sw_planet, $lagna_house;
	global $g_planet_asp_girl;
	$sun=array();
	$moo=array();
	$mer=array();
	$ven=array();
	$mars=array();
	$jup=array();
	$sat=array();
	$rah=array();
	$ket=array();
			
	foreach($sw_planet as $idx => $value){
		if(strpos($value,"சூரி")===0 or strpos($value,"சூரி")>0){
			array_push($sun,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சந்")===0 or strpos($value,"சந்")>0){
			array_push($moo,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"புத")===0 or strpos($value,"புத")>0){
			array_push($mer,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சுக்")===0 or strpos($value,"சுக்")>0){
			array_push($ven,countHouses($lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"செ")===0 or strpos($value,"செ")>0){
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,4)));
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($mars,countHouses($lagna_house,targetHouse($idx+1,8)));
		}
		
		if(strpos($value,"குரு")===0 or strpos($value,"குரு")>0){
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,5)));
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($jup,countHouses($lagna_house,targetHouse($idx+1,9)));
		}
		
		if(strpos($value,"சனி")===0 or strpos($value,"சனி")>0){
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,7)));
			array_push($sat,countHouses($lagna_house,targetHouse($idx+1,10)));
		}
		
		if(strpos($value,"ரா")===0 or strpos($value,"ரா")>0){
			array_push($rah,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($rah,countHouses($lagna_house,targetHouse($idx+1,11)));
		}
		
		if(strpos($value,"கே")===0 or strpos($value,"கே")>0){
			array_push($ket,countHouses($lagna_house,targetHouse($idx+1,3)));
			array_push($ket,countHouses($lagna_house,targetHouse($idx+1,11)));
		}
	}
	
	$g_planet_asp_girl["சூரி"] = $sun;
	$g_planet_asp_girl["சந்"] = $moo;
	$g_planet_asp_girl["புத"] = $mer;
	$g_planet_asp_girl["சுக்"] = $ven;
	$g_planet_asp_girl["செ"] = $mars;
	$g_planet_asp_girl["குரு"] = $jup;
	$g_planet_asp_girl["சனி"] = $sat;
	$g_planet_asp_girl["ரா"] = $rah;
	$g_planet_asp_girl["கே"] = $ket;
}

//this function fills all planet aspects in boy chart
function fillPlanetAspectsBoy(){
	global $tr_sw_planet, $transit_lagna_house;
	global $g_planet_asp_boy;
	$sun=array();
	$moo=array();
	$mer=array();
	$ven=array();
	$mars=array();
	$jup=array();
	$sat=array();
	$rah=array();
	$ket=array();
			
	foreach($tr_sw_planet as $idx => $value){
		if(strpos($value,"சூரி")===0 or strpos($value,"சூரி")>0){
			array_push($sun,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சந்")===0 or strpos($value,"சந்")>0){
			array_push($moo,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"புத")===0 or strpos($value,"புத")>0){
			array_push($mer,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"சுக்")===0 or strpos($value,"சுக்")>0){
			array_push($ven,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
		}
		
		if(strpos($value,"செ")===0 or strpos($value,"செ")>0){
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,4)));
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($mars,countHouses($transit_lagna_house,targetHouse($idx+1,8)));
		}
		
		if(strpos($value,"குரு")===0 or strpos($value,"குரு")>0){
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,5)));
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($jup,countHouses($transit_lagna_house,targetHouse($idx+1,9)));
		}
		
		if(strpos($value,"சனி")===0 or strpos($value,"சனி")>0){
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,7)));
			array_push($sat,countHouses($transit_lagna_house,targetHouse($idx+1,10)));
		}
		
		if(strpos($value,"ரா")===0 or strpos($value,"ரா")>0){
			array_push($rah,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($rah,countHouses($transit_lagna_house,targetHouse($idx+1,11)));
		}
		
		if(strpos($value,"கே")===0 or strpos($value,"கே")>0){
			array_push($ket,countHouses($transit_lagna_house,targetHouse($idx+1,3)));
			array_push($ket,countHouses($transit_lagna_house,targetHouse($idx+1,11)));
		}
	}
	
	$g_planet_asp_boy["சூரி"] = $sun;
	$g_planet_asp_boy["சந்"] = $moo;
	$g_planet_asp_boy["புத"] = $mer;
	$g_planet_asp_boy["சுக்"] = $ven;
	$g_planet_asp_boy["செ"] = $mars;
	$g_planet_asp_boy["குரு"] = $jup;
	$g_planet_asp_boy["சனி"] = $sat;
	$g_planet_asp_boy["ரா"] = $rah;
	$g_planet_asp_boy["கே"] = $ket;
}

//mars & saturn aspect
function matchingRule5()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_matching_mark;
	global $g_planet_asp_girl,$g_planet_asp_boy;
	
	$common_mars_sat_house = array();
	
	$result = true;
	$reason = "இல்லை";
			
	fillPlanetAspectsGirl();
	fillPlanetAspectsBoy();
	
	$mar_sat_asp_girl = array_intersect($g_planet_asp_girl["செ"],$g_planet_asp_girl["சனி"]);
	$mar_sat_asp_boy = array_intersect($g_planet_asp_boy["செ"],$g_planet_asp_boy["சனி"]);
	
	if(!empty(array_search(7,$mar_sat_asp_girl)) 
		and !empty(array_search(7,$mar_sat_asp_boy)))
	{
		$result=false;
		array_push($common_mars_sat_house,"7");
	}
	
	if(!empty(array_search(5,$mar_sat_asp_girl)) 
		and !empty(array_search(5,$mar_sat_asp_boy)))
	{
		$result=false;
		array_push($common_mars_sat_house,"5");
	}
	
	if(empty($mar_sat_asp_girl)){
		array_push($mar_sat_asp_girl,"இல்லை");
	}
	
	if(empty($mar_sat_asp_boy)){
		array_push($mar_sat_asp_boy,"இல்லை");
	}
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>5. செ/சனி பார்வை (5/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>".
				"பெண்-".join('-',$mar_sat_asp_girl).
				"<BR>".
				"ஆண்-".join('-',$mar_sat_asp_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>5. செ/சனி பார்வை (5/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>".
			"பெண்-".join('-',$mar_sat_asp_girl).
				"<BR>".
				"ஆண்-".join('-',$mar_sat_asp_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//இருண்ட வீடு  - எந்த கிரகமும் இல்லாத, எந்த கிரகத்தின் பார்வையும் இல்லாத வீடு இருண்ட வீடு 
function matchingRule6()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_planet_asp_girl,$g_planet_asp_boy,$g_matching_mark;
	
	$result = false;
	
	$dark_house_girl = array();
	$dark_house_boy = array();
	
	foreach($sw_planet as $idx => $value){
		if(empty($value)){
			array_push($dark_house_girl,countHouses($lagna_house,$idx+1));
		}
		elseif(!(strpos($value,"சுக்")===0 or 
				strpos($value,"புத")===0 or
				strpos($value,"குரு")===0 or
				strpos($value,"சனி")===0 or
				strpos($value,"ரா")===0 or
				strpos($value,"கே")===0 or
				strpos($value,"செ")===0 or
				strpos($value,"சூரி")===0 or
				strpos($value,"சந்")===0 or
				
				strpos($value,"சுக்")>0 or
				strpos($value,"புத")>0 or
				strpos($value,"குரு")>0 or
				strpos($value,"சனி")>0 or
				strpos($value,"ரா")>0 or
				strpos($value,"கே")>0 or
				strpos($value,"செ")>0 or
				strpos($value,"சூரி")>0 or
				strpos($value,"சந்")>0 )){
					array_push($dark_house_girl,countHouses($lagna_house,$idx+1));
				}		
	}
	
	foreach($tr_sw_planet as $idx => $value){
		if(empty($value)){
			array_push($dark_house_boy,countHouses($transit_lagna_house,$idx+1));
		}
		elseif(!(strpos($value,"சுக்")===0 or 
				strpos($value,"புத")===0 or
				strpos($value,"குரு")===0 or
				strpos($value,"சனி")===0 or
				strpos($value,"ரா")===0 or
				strpos($value,"கே")===0 or
				strpos($value,"செ")===0 or
				strpos($value,"சூரி")===0 or
				strpos($value,"சந்")===0 or
				
				strpos($value,"சுக்")>0 or
				strpos($value,"புத")>0 or
				strpos($value,"குரு")>0 or
				strpos($value,"சனி")>0 or
				strpos($value,"ரா")>0 or
				strpos($value,"கே")>0 or
				strpos($value,"செ")>0 or
				strpos($value,"சூரி")>0 or
				strpos($value,"சந்")>0 )){
					array_push($dark_house_boy,countHouses($transit_lagna_house,$idx+1));
				}
	}
	
	sort($dark_house_girl);
	sort($dark_house_boy);
	
	//find aspecting house and remove it from dark house array for girl
	$temp=array_diff($dark_house_girl,$g_planet_asp_girl["சூரி"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சந்"]);
	$temp=array_diff($temp,$g_planet_asp_girl["புத"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சுக்"]);
	$temp=array_diff($temp,$g_planet_asp_girl["செ"]);
	$temp=array_diff($temp,$g_planet_asp_girl["குரு"]);
	$temp=array_diff($temp,$g_planet_asp_girl["சனி"]);
	$temp=array_diff($temp,$g_planet_asp_girl["ரா"]);
	$temp=array_diff($temp,$g_planet_asp_girl["கே"]);
	
	$dark_house_girl = $temp;
	
	$temp = array(); //empty the temp variable
	//find aspecting house and remove it from dark house array for boy
	$temp=array_diff($dark_house_boy,$g_planet_asp_boy["சூரி"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சந்"]);
	$temp=array_diff($temp,$g_planet_asp_boy["புத"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சுக்"]);
	$temp=array_diff($temp,$g_planet_asp_boy["செ"]);
	$temp=array_diff($temp,$g_planet_asp_boy["குரு"]);
	$temp=array_diff($temp,$g_planet_asp_boy["சனி"]);
	$temp=array_diff($temp,$g_planet_asp_boy["ரா"]);
	$temp=array_diff($temp,$g_planet_asp_boy["கே"]);
	
	$dark_house_boy = $temp;
	
	if(empty($dark_house_boy)){
		$result = true;
		$dark_house_boy=array("இல்லை");
	}
	
	if(empty($dark_house_girl)){
		$result = true;
		$dark_house_girl=array("இல்லை");
	}
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>6. இருண்ட வீடுகள்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>".
				"பெண் ".join('-',$dark_house_girl).
				"<BR>".
				"ஆண் ".join('-',$dark_house_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>6. இருண்ட வீடுகள்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>".
			"பெண் ".join('-',$dark_house_girl).
				"<BR>".
				"ஆண் ".join('-',$dark_house_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//ஆண்/பெண் பாதிக்கப்பட்ட  பாவம் மற்றும் கிரகம் 
function matchingRule7(){
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_planet_asp_girl,$g_planet_asp_boy,$g_matching_mark;
	
	$result=true;
	$reason="இல்லை";
	
	$damaged_house_girl = array();
	$damaged_house_boy = array();
	$common_damaged_house = array();
	
	$girl_mars_aspect = $g_planet_asp_girl["செ"];
	$girl_sat_aspect = $g_planet_asp_girl["சனி"];
	$girl_rahu_aspect = $g_planet_asp_girl["ரா"];
	$girl_kethu_aspect = $g_planet_asp_girl["கே"];
	
	$boy_mars_aspect = $g_planet_asp_boy["செ"];
	$boy_sat_aspect = $g_planet_asp_boy["சனி"];
	$boy_rahu_aspect = $g_planet_asp_boy["ரா"];
	$boy_kethu_aspect = $g_planet_asp_boy["கே"];
		
	$damaged_house_girl[0] = "செ"."(".join('-',$girl_mars_aspect).")";
	$damaged_house_girl[1] = "சனி"."(".join('-',$girl_sat_aspect).")";
	$damaged_house_girl[2] = "ரா"."(".join('-',$girl_rahu_aspect).")";
	$damaged_house_girl[3] = "கே"."(".join('-',$girl_kethu_aspect).")";

	$damaged_house_boy[0] = "செ"."(".join('-',$boy_mars_aspect).")";
	$damaged_house_boy[1] = "சனி"."(".join('-',$boy_sat_aspect).")";
	$damaged_house_boy[2] = "ரா"."(".join('-',$boy_rahu_aspect).")";
	$damaged_house_boy[3] = "கே"."(".join('-',$boy_kethu_aspect).")";
	
	$all_aspects_girl = array_merge($girl_mars_aspect,$girl_sat_aspect,$girl_rahu_aspect,$girl_kethu_aspect);
	$all_aspects_boy = array_merge($boy_mars_aspect,$boy_sat_aspect,$boy_rahu_aspect,$boy_kethu_aspect);
	
	if(is_numeric(array_search(7,$all_aspects_girl))
		and is_numeric(array_search(7,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"7");
	}
	
	if(is_numeric(array_search(5,$all_aspects_girl)) 
		and is_numeric(array_search(5,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"5");
	}
		
	if(is_numeric(array_search(2,$all_aspects_girl)) 
		and is_numeric(array_search(2,$all_aspects_boy)))
	{
		$result=false;
		array_push($common_damaged_house,"2");
	}
	
	if(!empty($common_damaged_house)){
		$result=false;
		$reason = join('-',$common_damaged_house);
	}
	
	
	if($result == false){
		//echo "---damaged house match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>7. பாதிக்கப்பட்ட வீடுகள் (2/5/7)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>பாதிப்பு - $reason<BR>".
				"<BR>பெண்<BR>".join('<BR>',$damaged_house_girl).
				"<BR>".
				"<BR>ஆண்<BR>".join('<BR>',$damaged_house_boy).
				"</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>damaged house match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>7. பாதிக்கப்பட்ட வீடுகள் (2/5/7)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>பாதிப்பு - $reason<BR>".
			"<BR>பெண்<BR>".join('<BR>',$damaged_house_girl).
				"<BR>".
				"<BR>ஆண்<BR>".join('<BR>',$damaged_house_boy).
				"</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

// Guna or Gana star poruththam
function ganaMatching()
{
	global $sw_planet, $tr_sw_planet, $g_girl_nak, $g_boy_nak;
	
	$sath_ganam = array("அசுவினி","மிருகசிரீஷம்", "புனர்பூசம்", "பூசம்", "ஹஸ்தம்", "சுவாதி", "அனுஷம்", "திருவோணம்", "ரேவதி"); //sathveega
	$raja_ganam = array("பரணி", "ரோகிணி", "திருவாதிரை", "பூரம்", "உத்திரம்", "பூராடம்", "உத்திராடம்", "பூரட்டாதி","உத்திரட்டாதி"); //thamo
	$tham_ganam = array("கிருத்திகை", "ஆயில்யம்", "மகம்", "சித்திரை", "விசாகம்", "கேட்டை", "மூலம்", "அவிட்டம்","சதயம்"); //Rakshasha
	
	$result = false;
	$reason = "";
	
	if(in_array($g_girl_nak,$sath_ganam)){
		$girl_guna=1;
	}
	elseif(in_array($g_girl_nak,$raja_ganam)){
		$girl_guna=2;
	}
	elseif(in_array($g_girl_nak,$tham_ganam)){
		$girl_guna=3;
	}
	
	if(in_array($g_boy_nak,$sath_ganam)){
		$boy_guna=1;
	}
	elseif(in_array($g_boy_nak,$raja_ganam)){
		$boy_guna=2;
	}
	elseif(in_array($g_boy_nak,$tham_ganam)){
		$boy_guna=3;
	}
	
	if($girl_guna==$boy_guna){  //sathveegam+sathveegam or rajasam+rajasam or thamasom+thamasom
		debug("matched");
		$reason="ஒரே குணம்";
		$result=true;
	}
	elseif($girl_guna==1 && $boy_guna==2){   //sathveegam+rajasam
		debug("matched");
		$reason="சாத்வீக+ராஜஸ";
		$result=true;
	}
	elseif($girl_guna==1 && $boy_guna==2){   //sathveegam+thamasom
		debug("50% matched");
		$reason="சாத்வீக+தமோ-50%";
		$result=true;
	}
	elseif($girl_guna==2 && $boy_guna==1){  //rajasam+sathveegam
		debug("matched");
		$reason="ராஜஸ+சாத்வீக";
		$result=true;
	}
	elseif($girl_guna==2 && $boy_guna==3){   //rajasam+thamasom
		debug("not matched");
		$reason = "ராஜஸ+தமோ";
		$result=false;
	}
	elseif($girl_guna==3 && $boy_guna==2){   //thamasom+rajasam
		debug("not matched");
		$reason = "தமோ+ராஜஸ";
		$result=false;
	}
	elseif($girl_guna==3 && $boy_guna==1){   //thamasom+sathveegam
		debug("not matched");
		$reason = "தமோ+சாத்வீக";
		$result=false;
	}

/*	
	if($result == false){
		//echo "---Guna rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Guna match</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Guna rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Guna match</div>";
			echo "<div class='divPanchangGreen'>&#x2705;</div>";
		echo "</div>";
	}
	*/
	
	return $result;
}

// Boy & Girl bava matching
function matchingRule71()
{
}

//dasa lords matching based on friend/enemy
//dasa matching rule
function matchingRule10()
{
	global $current_dasa_bukthi, $current_dasa_bukthi_boy,$g_matching_mark;
	
	$result = false;
	$reason = "";
	
	debug('GIRL Dasa='.$current_dasa_bukthi['dasa']);
	debug('BOY Dasa='.$current_dasa_bukthi_boy['dasa']);
	
	if((strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //sun enemy to ven
		or (strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi['dasa'],"சூரி")==0) //sun enemy to ven
		or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) 		//sun enemy to sat
		or (strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi['dasa'],"சனி")==0)){   //sun enemy to sat
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"செ")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) //mars enemy to mer
		or (strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"செ")==0)){   
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //mer enemy to moon
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) //moon enemy to mer
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"குரு")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"புத")==0) 	//Jup enemy to mer
			or (strcmp($current_dasa_bukthi['dasa'],"புத")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"குரு")==0) //mer enemy to jup
			or (strcmp($current_dasa_bukthi['dasa'],"குரு")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //jup enemy to ven
			or (strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"குரு")==0) //ven enemy to jup
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0) 	//sat enemy to sun
			or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //sun enemy to sat
			or (strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //sat enemy to moo
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //moo enemy to sat
			or (strcmp($current_dasa_bukthi['dasa'],"சனி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"செ")==0) //sat enemy to mar
			or (strcmp($current_dasa_bukthi['dasa'],"செ")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சனி")==0) //mar enemy to sat
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif((strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சூரி")==0) 	//ven enemy to sun
			or (strcmp($current_dasa_bukthi['dasa'],"சூரி")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //sun enemy to ven
			or (strcmp($current_dasa_bukthi['dasa'],"சுக்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சந்")==0) //ven enemy to moo
			or (strcmp($current_dasa_bukthi['dasa'],"சந்")==0 and strcmp($current_dasa_bukthi_boy['dasa'],"சுக்")==0) //moo enemy to ven
			){  
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] . " பகை";
	}
	elseif(strcmp($current_dasa_bukthi['dasa'],$current_dasa_bukthi_boy['dasa'])==0){   //Same dasa
		$result = false;
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa']." - ஒரே தசா";
	}
	else{
		$reason = $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'];
		$result = true;
	}
	
	if($result == false){
		//echo "---Dasa match rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா பொருத்தம்</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dasa match rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா பொருத்தம்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}


// Sun/Moon position
function matchingRule9()
{
	global $sw_planet, $tr_sw_planet,$g_matching_mark;
	
	$girl_sun = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சூரி"));
	$girl_moon = countHouses(findHouse($sw_planet,"லக்"),findHouse($sw_planet,"சந்"));
	$boy_sun = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சூரி"));
	$boy_moon = countHouses(findHouse($tr_sw_planet,"லக்"),findHouse($tr_sw_planet,"சந்"));
	
	if($girl_sun == 6 || $girl_sun == 8 || $girl_sun == 12 ){
		$result = false;
		$reason = "பெண்-சூரி-".$girl_sun;
	}
	elseif($girl_moon == 6 || $girl_moon == 8 || $girl_moon == 12 ){
		$result = false;
		$reason = "பெண்-சந்-".$girl_moon;
	}
	elseif($boy_sun == 6 || $boy_sun == 8 || $boy_sun == 12 ){
		$result = false;
		$reason = "ஆண்-சூரி-".$boy_sun;
	}
	elseif($boy_moon == 6 || $boy_moon == 8 || $boy_moon == 12 ){
		$result = false;
		$reason = "ஆண்-சந்-".$boy_moon;
	}
	else{
		$result = true;
		$reason = "பெண்-சூரி-".$girl_sun." பெண்-சந்-".$girl_moon."<BR>ஆண்-சூரி-".$boy_sun." ஆண்-சந்-".$boy_moon;
	}
	
	if($result == false){
		//echo "---Rasi rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>9. சூரி/சந் நிலை</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Rasi rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>9. சூரி/சந் நிலை</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
}

//Dasa match
function matchingRule10__()
{
	global $current_dasa_bukthi, $current_dasa_bukthi_boy,$sw_planet, $tr_sw_planet;
	
	$result = false;
	$reason = "";
	
	debug('GIRL Dasa='.$current_dasa_bukthi['dasa']);
	debug('BOY Dasa='.$current_dasa_bukthi_boy['dasa']);
	
	$girl_dasa_lord_house = findHouse($sw_planet,$current_dasa_bukthi['dasa']);
	$boy_dasa_lord_house = findHouse($tr_sw_planet,$current_dasa_bukthi_boy['dasa']);
	
	$dasa_lord_housecount = countHouses($girl_dasa_lord_house,$boy_dasa_lord_house);
	
	debug('Girl-dasa-lord-house='.$girl_dasa_lord_house);
	debug('<BR>Boy-dasa-lord-house='.$boy_dasa_lord_house);
	debug('<BR>Dasa lord house count ='.$dasa_lord_housecount);
	
	if($dasa_lord_housecount==6 or $dasa_lord_housecount==8){
		$result = false;
		$reason = "Dasa lord 6/8 (". $current_dasa_bukthi['dasa'] . "/" . $current_dasa_bukthi_boy['dasa'] .")";
	}
	else{
		$result = true;
		$reason = "Dasa lord ". $dasa_lord_housecount . "/" . countHouses($boy_dasa_lord_house,$girl_dasa_lord_house);
	}
	
	if($result == false){
		//echo "---Dasa lord rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா/புக்தி</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dasa lord rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>10. தசா/புக்தி</div>";
			echo "<div class='divPanchangGreen'>&#x2705; ($reason)</div>";
		echo "</div>";
	}
	
}

//chevvai, surpa, kalathira, puthra thosam check
function matchingRule11()
{
	global $sw_planet, $tr_sw_planet, $lagna_house, $transit_lagna_house,$g_matching_mark;
	
	$mars_dhosham_girl = false; //mars in 2,4,7,8,12
	$mars_dhosham_boy = false;
	$sarpa_dhosham_girl = false; // rahu/kethu in 1/7 or 2/8
	$sarpa_dhosham_boy = false;
	$kalathira_dhosham_girl = false; //The placement of malefic planets Mars, Saturn, Sun, Rahu, and Ketu in the 1st, 2nd, 4th, 7th, 8th, or 12th house from the Ascendant (Lagna) signifies Kalathra Dosha
	$kalathira_dhosham_boy = false;
	$puthra_dhosham_girl = false; // rahu/kethu/sun/sat in 5th house, 5th lord Neesam, 5th lord with rahu/kethu/sat, 5th lord in 6/8 house
	$puthra_dhosham_boy = false;
	
	$result = true;
	
	$girl_dhoshas = "பெண்-";
	$boy_dhoshas = "ஆண்-";
	
	$girl_mars_house = countHouses($lagna_house,findHouse($sw_planet,"செ")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_mars_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"செ")+1); //findHouse returns 0 as first house, hence adding 1
	
	$girl_sat_house = countHouses($lagna_house,findHouse($sw_planet,"சனி ")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_sat_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"சனி ")+1); //findHouse returns 0 as first house, hence adding 1
	
	$girl_rahu_house = countHouses($lagna_house,findHouse($sw_planet,"ரா")+1); //findHouse returns 0 as first house, hence adding 1
	$boy_rahu_house = countHouses($transit_lagna_house,findHouse($tr_sw_planet,"ரா")+1); //findHouse returns 0 as first house, hence adding 1

	$girl_kethu_house = targetHouse($girl_rahu_house,7); 
	$boy_kethu_house = targetHouse($boy_rahu_house,7);   
	
	//chevvai dhosham
	//mars in 2,4,7,8,12
	if($girl_mars_house==2 or $girl_mars_house==4 or $girl_mars_house==7 or $girl_mars_house==8 or $girl_mars_house==12)
	{
		$mars_dhosham_girl = true;
	}
	
	if($boy_mars_house==2 or $boy_mars_house==4 or $boy_mars_house==7 or $boy_mars_house==8 or $boy_mars_house==12)
	{
		$mars_dhosham_boy = true;
	}
	
	//rahu/kethu dhosham
	// rahu/kethu in 1/7 or 2/8
	if($girl_rahu_house==1 or $girl_rahu_house==2 or $girl_rahu_house==7 or $girl_rahu_house==8)
	{
		$sarpa_dhosham_girl = true;
	}
	
	if($boy_rahu_house==1 or $boy_rahu_house==2 or $boy_rahu_house==7 or $boy_rahu_house==8)
	{
		$sarpa_dhosham_boy = true;
	}
	
	//Kalathra dhosam
	//The placement of malefic planets Mars, Saturn, Sun, Rahu, and Ketu in the 1st, 2nd, 4th, 7th, 8th, or 12th house from the Ascendant (Lagna) signifies Kalathra Dosha
	if($girl_rahu_house==1 or $girl_rahu_house==2 or $girl_rahu_house==7 or $girl_rahu_house==8 or
	   $girl_sat_house==7 or $girl_mars_house==7)
	{
		$kalathira_dhosham_girl = true;
	}
	
	if($boy_rahu_house==1 or $boy_rahu_house==2 or $boy_rahu_house==7 or $boy_rahu_house==8 or
	   $boy_sat_house==7 or $boy_mars_house==7)
	{
		$kalathira_dhosham_boy = true;
	}
	
	// puthira dhosham
	// rahu/kethu/sun/sat in 5th house, 5th lord Neesam, 5th lord with rahu/kethu/sat, 5th lord in 6/8 house
	if($girl_rahu_house==5 or $girl_kethu_house==5 or $girl_sat_house==5)
	{
		$puthra_dhosham_girl = true;
	}
	
	if($boy_rahu_house==5 or $boy_kethu_house==5 or $boy_sat_house==5)
	{
		$puthra_dhosham_boy = true;
	}
	
	//add dhosha details for girl
	if($mars_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "செ";
	}
	
	if($sarpa_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "ராகு கேது";
	}
	
	if($kalathira_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "களத்திர";
	}
	
	if($puthra_dhosham_girl)
	{
		$girl_dhoshas = $girl_dhoshas . "-" . "புத்திர";
	}
	
	//add dhosha details for boy
	if($mars_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "செ";
	}
	
	if($sarpa_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "ராகு கேது";
	}
	
	if($kalathira_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "களத்திர";
	}
	
	if($puthra_dhosham_boy)
	{
		$boy_dhoshas = $boy_dhoshas . "-" . "புத்திர";
	}
	
	// if any one dhosha is there, mark it as red
	if($mars_dhosham_girl or $mars_dhosham_boy or $sarpa_dhosham_girl or $sarpa_dhosham_boy or 
	   $kalathira_dhosham_girl or $kalathira_dhosham_boy or $puthra_dhosham_girl or $puthra_dhosham_boy)
	{
		$result = false;
	}
	
	if(!($mars_dhosham_girl or $sarpa_dhosham_girl or $kalathira_dhosham_girl or $puthra_dhosham_girl))
	{
		$girl_dhoshas = "பெண்-இல்லை";
	}
	
	if(!($mars_dhosham_boy or $sarpa_dhosham_boy or $kalathira_dhosham_boy or $puthra_dhosham_boy))
	{
		$boy_dhoshas = "ஆண்-இல்லை";
	}
	
	if($result == false){
		//echo "---Dhosha rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. தோஷங்கள்</div>";
			echo "<div class='divPanchang'>&#x274C;<div class='divStarMatch'>$girl_dhoshas<BR>$boy_dhoshas</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Dhosha  rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. தோஷங்கள்</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>(தோஷங்கள் இல்லை)</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
}

//yoni matching rule
function yoniMatch()
{
	global $g_girl_nak, $g_boy_nak;
	
	$yoni_arr = ["அசுவினி"=>"குதிரை",
				"பரணி"=>"யானை",
				"கிருத்திகை"=>"ஆடு",
				"ரோகிணி"=>"நாகம்",
				"மிருகசிரீஷம்"=>"சாரை",
				"திருவாதிரை"=>"நாய்",
				"புனர்பூசம்"=>"பூனை",
				"பூசம்"=>"ஆடு",
				"ஆயில்யம்"=>"பூனை",
				"மகம்"=>"எலி",
				"பூரம்"=>"எலி",
				"உத்திரம்"=>"எருது",
				"ஹஸ்தம்"=>"எருமை",
				"சித்திரை"=>"புலி",
				"சுவாதி"=>"எருமை",
				"விசாகம்"=>"புலி",
				"அனுஷம்"=>"மான்",
				"கேட்டை"=>"மான்",
				"மூலம்"=>"நாய்",
				"பூராடம்"=>"குரங்கு",
				"உத்திராடம்"=>"பசு",
				"திருவோணம்"=>"குரங்கு",
				"அவிட்டம்"=>"சிங்கம்",
				"சதயம்"=>"குதிரை",
				"பூரட்டாதி"=>"சிங்கம்",
				"உத்திரட்டாதி"=>"பசு",
				"ரேவதி"=>"யானை"];
	
	/**** யோனிப் பொருத்தம் **********
	
		பாம்பு x கீரி
		யானை x சிங்கம்
		குரங்கு x ஆடு
		மான் x நாய்
		எலி x பூனை
		குதிரை x எருமை
		பசு x புலி
		நாகம் x எலி
	
	**** யோனிப் பொருத்தம் **********/
	
	$result = false;
	$reason = "";
	
	debug('GIRL Yoni='.$g_girl_nak);
	debug('BOY Yoni='.$g_boy_nak);
	
	$girl_yoni = $yoni_arr[$g_girl_nak];
	$boy_yoni = $yoni_arr[$g_boy_nak];
	
	if((strcmp($girl_yoni,"பாம்பு")==0 and strcmp($boy_yoni,"கீரி")==0)
		or (strcmp($boy_yoni,"பாம்பு")==0 and strcmp($girl_yoni,"கீரி")==0)){   //பாம்பு x கீரி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"யானை")==0 and strcmp($boy_yoni,"சிங்கம்")==0)
		or (strcmp($boy_yoni,"யானை")==0 and strcmp($girl_yoni,"சிங்கம்")==0)){   //யானை x சிங்கம்
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"குரங்கு")==0 and strcmp($boy_yoni,"ஆடு")==0)
		or (strcmp($boy_yoni,"குரங்கு")==0 and strcmp($girl_yoni,"ஆடு")==0)){   //குரங்கு x ஆடு
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"மான்")==0 and strcmp($boy_yoni,"நாய்")==0)
		or (strcmp($boy_yoni,"மான்")==0 and strcmp($girl_yoni,"நாய்")==0)){   //மான் x நாய்
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"எலி")==0 and strcmp($boy_yoni,"பூனை")==0)
		or (strcmp($boy_yoni,"எலி")==0 and strcmp($girl_yoni,"பூனை")==0)){   //எலி x பூனை
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"குதிரை")==0 and strcmp($boy_yoni,"எருமை")==0)
		or (strcmp($boy_yoni,"குதிரை")==0 and strcmp($girl_yoni,"எருமை")==0)){   //குதிரை x எருமை
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"பசு")==0 and strcmp($boy_yoni,"புலி")==0)
		or (strcmp($boy_yoni,"பசு")==0 and strcmp($girl_yoni,"புலி")==0)){   //பசு x புலி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	elseif((strcmp($girl_yoni,"நாகம்")==0 and strcmp($boy_yoni,"எலி")==0)
		or (strcmp($boy_yoni,"நாகம்")==0 and strcmp($girl_yoni,"எலி")==0)){   //நாகம் x எலி
		$result = false;
		$reason = $girl_yoni . " x " . $boy_yoni;
	}
	else{
		$reason = $girl_yoni . " + " . $boy_yoni;
		$result = true;
	}
	
	/*
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Yoni match</div>";
			echo "<div class='divPanchangRed'>&#x274C; ($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>Yoni match</div>";
			echo "<div class='divPanchangGreen'>&#x2705; ($reason)</div>";
		echo "</div>";
	}
	*/
	
	return $result;
}

//rajju matching rule
function matchingRule8($display)
{
	global $g_girl_nak, $g_boy_nak,$g_matching_mark;
	
	$sirasu = ["மிருகசிரீஷம்", "சித்திரை", "அவிட்டம்"];
	$kanta = ["ரோகிணி", "திருவாதிரை", "ஹஸ்தம்", "சுவாதி", "திருவோணம்", "சதயம்"];
	$uthara = ["கிருத்திகை", "புனர்பூசம்", "உத்திரம்", "விசாகம்", "உத்திராடம்", "பூரட்டாதி"];
	$ooru = ["பரணி", "பூசம்", "பூரம்", "அனுஷம்", "பூராடம்", "உத்திரட்டாதி"];
	$paatha = ["அசுவினி", "ஆயில்யம்", "மகம்", "கேட்டை", "மூலம்", "ரேவதி"];
	
	$rajju_type = ["dummy","தலை","கழுத்து","வயிறு", "தொடை", "கால்"];
	
	/**** rajju பொருத்தம் **********
	
		same rajju = bad
	
	**** rajju பொருத்தம் **********/
	
	$result = false;
	$reason = "";
	$details ="";
	
	if(array_search($g_girl_nak,$sirasu)!==false){
		$girl_rajju = 1; //sirasu
	}
	elseif(array_search($g_girl_nak,$kanta)!==false){
		$girl_rajju = 2; //kanta
	}
	elseif(array_search($g_girl_nak,$uthara)!==false){
		$girl_rajju = 3; //uthara
	}
	elseif(array_search($g_girl_nak,$ooru)!==false){
		$girl_rajju = 4; //ooru
	}
	elseif(array_search($g_girl_nak,$paatha)!==false){
		$girl_rajju = 5; //paatha
	}
	
	if(array_search($g_boy_nak,$sirasu)!==false){
		$boy_rajju = 1; //sirasu
	}
	elseif(array_search($g_boy_nak,$kanta)!==false){
		$boy_rajju = 2; //kanta
	}
	elseif(array_search($g_boy_nak,$uthara)!==false){
		$boy_rajju = 3; //uthara
	}
	elseif(array_search($g_boy_nak,$ooru)!==false){
		$boy_rajju = 4; //ooru
	}
	elseif(array_search($g_boy_nak,$paatha)!==false){
		$boy_rajju = 5; //paatha
	}
	
	debug('GIRL rajju='.$girl_rajju);
	debug('BOY rajju='.$boy_rajju);
	
	if($girl_rajju == $boy_rajju){ 
		$result = false;
		$reason = "ஒரே ரஜ்ஜு";
	
		$details = "<BR>பெண் - $rajju_type[$girl_rajju] <BR> ஆண் - $rajju_type[$boy_rajju]";
	}
	else{
		$reason = "வேறு ரஜ்ஜு";
		$result = true;
		
		$details = "<BR>பெண் - $rajju_type[$girl_rajju] <BR> ஆண் - $rajju_type[$boy_rajju]";
	}
	
	if(!$display){   // Just the result returned to the called plage without displaying
		return $result;
	}
	
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>8. ரஜ்ஜு பொருத்தம் (வேதை நட்)</div>";
			echo "<div class='divPanchang'>&#x274C; <div class='divStarMatch'>$reason $details</div></div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>8. ரஜ்ஜு பொருத்தம் (வேதை நட்)</div>";
			echo "<div class='divPanchang'>&#x2705; <div class='divStarMatch'>$reason  $details</div></div>";
		echo "</div>";
		
		$g_matching_mark++;
	}
	
	return $result;
}

//thithi soonyam check
function matchingRule111($in_thithi_girl, $in_thithi_boy)
{
	global $tr_sw_planet, $sw_planet;
	
	$thithi_girl = explode('[',explode("/",$in_thithi_girl)[1])[0];
	$thithi_boy = explode('[',explode("/",$in_thithi_boy)[1])[0];
	
	$result=false;
	$reason="";
	
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
					
	$girl_thithi_soonyam = explode(',',$thithi_arr[$thithi_girl]);
	$boy_thithi_soonyam = explode(',',$thithi_arr[$thithi_boy]);
	
	$arr_size = sizeof($girl_thithi_soonyam);
	
	if(sizeof(array_intersect($girl_thithi_soonyam,$boy_thithi_soonyam))>0){ // if any one or more rasi being soonyam across girl and boy soonya rasi
		$result = false;
		$reason = "ஒரே திதி சூன்ய ராசி (". join('-',$boy_thithi_soonyam) . ' / ' . join('-',$girl_thithi_soonyam) . ')';
	}
	else{
		$result=true;
		$reason = "ஒரே திதி சூன்ய ராசி இல்லை";
	}
	
	if($result == false){
		//echo "---Yoni rule failed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. திதி சூனியம்</div>";
			echo "<div class='divPanchangRed'>&#x274C; <BR>($reason)</div>";
		echo "</div>";
	}
	else{
		//echo "<br>Yoni rule passed";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading' style='text-align:left'>11. திதி சூனியம்</div>";
			echo "<div class='divPanchangGreen'>PASS <BR>($reason)</div>";
		echo "</div>";
	}
}

function calculateGirlPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_girl_nak, $g_girl_karan, $g_panchang_girl,$g_girl_nak_pad_idx, $full_sun_deg ;
	
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
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_girl_nak_pad_idx = $nak_idx;
	$g_girl_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
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

	
/*	if($moon_house > $sun_house){
		$diff = ($full_moon_deg - $full_sun_deg)/12;
	}
	elseif($moon_house < $sun_house){
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	else{
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	
	debug("<BR>diff=".$diff);
	
	if(floor($diff) > 0){	
		$thi_idx = floor($diff);
		$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
	elseif(floor($diff) < 0){	
		$thi_idx = 30 - floor($diff) ;
		$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}else{
		if($moon_house==$sun_house){
			$thi_idx = 29; 
		}
		else{
			$thi_idx = 14; 
		}
		$panchang["thithi"] = $tithi[$thi_idx];
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
*/	
	
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
	$g_girl_karan = $panchang["karan"];
	
	/*if($full_moon_deg % 180 > $full_sun_deg){
		$kar = ($full_moon_deg - $full_sun_deg)/12;
		debug("krishna".(abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 . "|" . $thi_idx) ;
		
		if((abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][0];debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_krishna[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][1];debug("%%%%");
			}
			else{
				echo $thi_idx; 
				$panchang["karan"] = $karan_krishna[$thi_idx][1]; debug("$$$$$");
			}
		}
	}
	else{
		$kar = ($full_sun_deg - $full_moon_deg)/12;
		debug("shukla".((abs(($full_sun_deg - $full_moon_deg) - 180))) . "|")  ; 
		
		if((abs(($full_sun_deg - $full_moon_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][0]; debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][1]; debug("%%%%");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][1]; debug("$$$$$");
			}
		}
	}*/
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	debug("yoga_idx=$yoga_idx, yogam_deg".($deg / 13.333333));
	
	if($yoga_idx > 27){
		$yoga_idx = 27;
	}
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	/*
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Vaaram/வாரம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['vaaram']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Nakshatra/நட்சத்திரம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['nak']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Thithi/திதி</div>";
			echo "<div class='divGirlPanchang'>".$panchang['thithi']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Karanam/கரணம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['karan']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divGirlPanchangHeading'>Yogam/யோகம்</div>";
			echo "<div class='divGirlPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
	echo "</div></center>";
	*/
	
	$g_panchang_girl = $panchang;
}

function calculateBoyPanchang(){
	global $transit_moon_degree, $transit_moon_house, $transit_sun_house, $transit_sun_degree, $transit_dayofweek, $g_boy_nak, $g_boy_karan, $g_panchang_boy, $g_boy_nak_pad_idx, $full_sun_deg_boy;
	
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
	$panchang["vaaram"] = $weekday_arr[$transit_dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($transit_moon_house-1) * 30 + $transit_moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_boy_nak_pad_idx = $nak_idx;
	$g_boy_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
	//thithi calculation
	$full_sun_deg_boy = ($transit_sun_house-1)*30+$transit_sun_degree;
	
	$full_sun_deg = $full_sun_deg_boy; //just to avoid renaming in the below codes. Should be fixed in future.
	
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

	
/*	if($moon_house > $sun_house){
		$diff = ($full_moon_deg - $full_sun_deg)/12;
	}
	elseif($moon_house < $sun_house){
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	else{
		$diff = ($full_sun_deg - $full_moon_deg)/12;
	}
	
	debug("<BR>diff=".$diff);
	
	if(floor($diff) > 0){	
		$thi_idx = floor($diff);
		$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
	elseif(floor($diff) < 0){	
		$thi_idx = 30 - floor($diff) ;
		$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
		
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}else{
		if($moon_house==$sun_house){
			$thi_idx = 29; 
		}
		else{
			$thi_idx = 14; 
		}
		$panchang["thithi"] = $tithi[$thi_idx];
		debug(__LINE__.":" . "THITHI IDX=".$thi_idx);
	}
*/	
	
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
	$g_boy_karan = $panchang["karan"];
	
	/*if($full_moon_deg % 180 > $full_sun_deg){
		$kar = ($full_moon_deg - $full_sun_deg)/12;
		debug("krishna".(abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 . "|" . $thi_idx) ;
		
		if((abs(($full_moon_deg - $full_sun_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][0];debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_krishna[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_krishna[$thi_idx-15][1];debug("%%%%");
			}
			else{
				echo $thi_idx; 
				$panchang["karan"] = $karan_krishna[$thi_idx][1]; debug("$$$$$");
			}
		}
	}
	else{
		$kar = ($full_sun_deg - $full_moon_deg)/12;
		debug("shukla".((abs(($full_sun_deg - $full_moon_deg) - 180))) . "|")  ; 
		
		if((abs(($full_sun_deg - $full_moon_deg) - 180.0)) % 12 <= 6){
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][0]; debug("^^^^^");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][0]; debug("#####");
			}
		}
		else{
			if($thi_idx > 14){
				$panchang["karan"] = $karan_sukla[$thi_idx-15][1]; debug("%%%%");
			}
			else{
				$panchang["karan"] = $karan_sukla[$thi_idx][1]; debug("$$$$$");
			}
		}
	}*/
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	/*
	//display panchang table
	echo "<center><div class='divTable blueTable' style='height:40px;width:360px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Vaaram/வாரம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['vaaram']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Nakshatra/நட்சத்திரம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['nak']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Thithi/திதி</div>";
			echo "<div class='divBoyPanchang'>".$panchang['thithi']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Karanam/கரணம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['karan']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divBoyPanchangHeading'>Yogam/யோகம்</div>";
			echo "<div class='divBoyPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
	echo "</div></center>";
	*/
	
	$g_panchang_boy = $panchang;
	$full_sun_deg_boy = $full_sun_deg; //reassign the value to global variable
}

function displayGirlBoyDetails()
{
	global $g_panchang_boy, $g_panchang_girl, $in_girlname, $in_boyname, $chart_date, $chart_time, $g_dasaBalance, 
		$transit_date, $transit_time, $in_place_girl, $in_place_boy, $g_thithi_soonyam_matching_arr,$g_dasaCurrent, 
		$lagna_house, $transit_lagna_house, $today_sunrise, $g_girlage, $g_boyage,
		$g_vayadhotta_rasi_girl,$g_vayadhotta_rasi_boy, $moon_house, $transit_moon_house ;
	
	echo "<BR>";
	echo "<div class='divTable blueTable' style='height:40px'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)'>";
				echo "<div class='divPanchangHeading' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>விவரங்கள்*</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
	//display panchang table 
	echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'></div>";
			echo "<div class='divGirlPanchangHeading'>&#128105;பெண்</div>";
			echo "<div class='divBoyPanchangHeading'>&#128104;ஆண்</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பெயர்</div>";
			echo "<div class='divGirlPanchang'>".$in_girlname."</div>";
			echo "<div class='divBoyPanchang'>".$in_boyname."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த தேதி</div>";
			$g_girlage = round((strtotime(date('d-M-Y'))-strtotime($chart_date))/(365*60*60*24),1);
			$g_vayadhotta_rasi_girl = ceil($g_girlage) % 12;
			
			if($g_vayadhotta_rasi_girl == 0){
				$g_vayadhotta_rasi_girl = 12;
			}
			echo "<div class='divGirlPanchang'>".date('d-M-Y',strtotime($chart_date))."<BR>($g_girlage வயது)</div>";
			$g_boyage = round((strtotime(date('d-M-Y'))-strtotime($transit_date))/(365*60*60*24),1);
			$g_vayadhotta_rasi_boy = ceil($g_boyage) % 12;
			
			if($g_vayadhotta_rasi_boy == 0){
				$g_vayadhotta_rasi_boy = 12;
			}
			echo "<div class='divBoyPanchang'>".date('d-M-Y',strtotime($transit_date))."<BR>($g_boyage வயது)</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த நேரம்</div>";
			echo "<div class='divGirlPanchang'>".date('h:i A',strtotime($chart_time))."</div>";
			echo "<div class='divBoyPanchang'>".date('h:i A',strtotime($transit_time))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த இடம்</div>";
		echo "<div class='divGirlPanchang'>".explode('|',$in_place_girl)[0]."</div>";
			echo "<div class='divBoyPanchang'>".explode('|',$in_place_boy)[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divGirlPanchang'>".explode('/',$g_panchang_girl['vaaram'])[1]."</div>";
			echo "<div class='divBoyPanchang'>".explode('/',$g_panchang_boy['vaaram'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>திதி</div>";
			$thithi_arr = explode('/',$g_panchang_girl['thithi']);
			$thithi_txt = explode("[",$thithi_arr[1])[0]."(".explode("]",$thithi_arr[2])[0].")";
			//echo "<div class='divGirlPanchang'>".$g_panchang_girl['thithi']."</div>";
			echo "<div class='divGirlPanchang'>".$thithi_txt."</div>";
			$thithi_arr = explode('/',$g_panchang_boy['thithi']);
			$thithi_txt = explode("[",$thithi_arr[1])[0]."(".explode("]",$thithi_arr[2])[0].")";
			echo "<div class='divBoyPanchang'>".$thithi_txt."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			echo "<div class='divGirlPanchang'>".explode('/',$g_panchang_girl['nak'])[1]."</div>";
			echo "<div class='divBoyPanchang'>".explode('/',$g_panchang_boy['nak'])[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			echo "<div class='divGirlPanchang'>".$g_panchang_girl['yoga']."</div>";
			echo "<div class='divBoyPanchang'>".$g_panchang_boy['yoga']."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			$karan = explode('/',$g_panchang_girl['karan'])[1];
			echo "<div class='divGirlPanchang'>".$karan."(".getKaranaPlanet($karan).")</div>";
			$karan = explode('/',$g_panchang_boy['karan'])[1];
			echo "<div class='divBoyPanchang'>".$karan."(".getKaranaPlanet($karan).")</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>லக்னம்</div>";
			$g_lagnam = getRasi($lagna_house-1);
			echo "<div class='divGirlPanchang'>".$g_lagnam."</div>";
			$g_lagnam = getRasi($transit_lagna_house-1);
			echo "<div class='divBoyPanchang'>".$g_lagnam."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வயதோட்ட லக்னம்</div>";
			if($g_girlage >= 0){
				$g_vayadhotta_lagna = getRasi(targetHouse($lagna_house,$g_vayadhotta_rasi_girl-1));
			}
			else{
				$g_vayadhotta_lagna = "சாத்தியம் இல்லை";
			}
			echo "<div class='divGirlPanchang'>".$g_vayadhotta_lagna."</div>";
			if($g_boyage >= 0){
				$g_vayadhotta_lagna = getRasi(targetHouse($transit_lagna_house,$g_vayadhotta_rasi_boy-1));
			}
			else{
				$g_vayadhotta_lagna = "சாத்தியம் இல்லை";
			}
			echo "<div class='divBoyPanchang'>".$g_vayadhotta_lagna."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			$g_rasi = getRasi($moon_house-1);
			echo "<div class='divPanchangHeading'>ராசி</div>";
			echo "<div class='divGirlPanchang'>".$g_rasi."</div>";
			$g_rasi = getRasi($transit_moon_house-1);
			echo "<div class='divBoyPanchang'>".$g_rasi."</div>";
		echo "</div>";
	
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>திதி சூன்யம்</div>";
			echo "<div class='divGirlPanchang'>".$g_thithi_soonyam_matching_arr[0]."</div>";
			echo "<div class='divBoyPanchang'>".$g_thithi_soonyam_matching_arr[1]."</div>";
		echo "</div>";		
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறப்பு தசா இருப்பு</div>";
			echo "<div class='divGirlPanchang'>".$g_dasaBalance[0]."</div>";
			echo "<div class='divBoyPanchang'>".$g_dasaBalance[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய தசா புக்தி</div>";
			echo "<div class='divGirlPanchang'>".$g_dasaCurrent[0]."</div>";
			echo "<div class='divBoyPanchang'>".$g_dasaCurrent[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகி</div>";
			echo "<div class='divGirlPanchang'>".getYogi($g_panchang_girl['yoga'])[0]."<BR>(".getYogi($g_panchang_girl['yoga'])[1].")</div>";
			echo "<div class='divBoyPanchang'>".getYogi($g_panchang_boy['yoga'])[0]."<BR>(".getYogi($g_panchang_boy['yoga'])[1].")</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>அவயோகி</div>";
			echo "<div class='divGirlPanchang'>".getYogi($g_panchang_girl['yoga'])[2]."<BR>(".getYogi($g_panchang_girl['yoga'])[3].")</div>";
			echo "<div class='divBoyPanchang'>".getYogi($g_panchang_boy['yoga'])[2]."<BR>(".getYogi($g_panchang_boy['yoga'])[3].")</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வைநாசிகம்</div>";
			echo "<div class='divGirlPanchang'>".getVainasigam(true)[0]."</div>";
			echo "<div class='divBoyPanchang'>".getVainasigam(false)[0]."</div>";
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

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl,country FROM world_cities WHERE Place="."'".$place_girl."'"." AND TZ="."'".$TZ_girl."'";

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
echo "DATE: " . $chart_date."<BR>";
echo "TIME: " . $chart_time."<BR>";
echo "PLACE: " . $place_girl."<BR>";
echo "LAT: " . $lat."<BR>";
echo "LONG: " . $lon."<BR>";
echo "GMT offset: " . $gmt_offset."<BR>";
echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
echo "TZ: " . $TZ_girl ."<BR>";
}

date_default_timezone_set($TZ_girl);

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

// ------- Swiss ephemeris for girl // ------- 

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
	//exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);  // t - true node
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // m - mean node
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
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
	global $ragu_house,$sun_degree, $sun_house;
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
	
	//$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	$sw_degree[$row[0]] = round($tot_degree[$key] - $sw_house[$key]*30,2);
	
	$planetDeg_default[$row[0]][0] = (float)($row[2]);
	
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
	echo "PLACE:" . $place_girl."<BR>";
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

//Get boy data (outside), mentioned as transit in the code because birth chart code is reused here. Transit is nothing but boy data

$transit_date = isset($_POST['in_date_boy']) ? $_POST['in_date_boy'] : date('Y-m-d');
$transit_time = isset($_POST['in_time_boy']) ? $_POST['in_time_boy'] : date('H:i:s');

$_SESSION['boydate']=$transit_date;
$_SESSION['boytime']=$transit_time;

//get boys lat/long
$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl,country FROM world_cities WHERE Place="."'".$place_boy."'"." AND TZ="."'".$TZ_boy."'";

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
echo "DATE: " . $transit_date."<BR>";
echo "TIME: " . $transit_time."<BR>";
echo "PLACE: " . $place_boy."<BR>";
echo "LAT: " . $lat."<BR>";
echo "LONG: " . $lon."<BR>";
echo "GMT offset: " . $gmt_offset."<BR>";
echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
echo "TZ: " . $TZ_boy ."<BR>";
}

date_default_timezone_set($TZ_boy);

if(date('I',strtotime($transit_date))==1) { // DST
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
	//exec ("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit); // t - true node
	exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);   // m - mean node
	debug("<BR>swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

planetSort_transit($transit);

// Transit calculation
$count=0;
foreach ($transit as $key => $line)
  {
	global $tr_ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$tr_sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$tr_sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $tr_sw_house[$key]*30,2));
	
	$planetDeg_default_transit[$row[0]][0] = (float)($row[2]);
	
    if($count!=21){
		$tr_sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($transit_sun_house==0 and strpos($row[0],'Sun')===0){
		$transit_sun_house = (int)($tot_degree[$key]/30)+1;
		$transit_sun_degree = getDegree(round($tot_degree[$key] - $tr_sw_house[$key]*30,2)); 
	}
	
	if($transit_moon_house==0 and strpos($row[0],'Moon')===0){
		$transit_moon_house = (int)($tot_degree[$key]/30)+1;
		$transit_moon_degree = getDegree(round($tot_degree[$key] - $tr_sw_house[$key]*30,2));
	}
	
	if($transit_lagna_house==0 and strpos($row[0],'Ascendant')===0){
		$transit_lagna_house = (int)($tot_degree[$key]/30)+1;
	}
	
	$tr_sw_planet[$tr_sw_house[$key]] = $tr_sw_planet[$tr_sw_house[$key]]." ".$row[0];
	
	$tot_degree[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	//if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
	if(strpos($row[0],"mean Node")===0){ // using === because this strpos returns 0 when mean Node found and also false(0) when it's not found. So we need to use ===
		
		$tr_ragu_house = $tr_sw_house[$key];
		if($DEBUG==1){ echo "tr_ragu_house=" . $tr_sw_house[$key]."<br>";}
	}
		
	if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$tr_sw_planet[$key]."|".$tr_sw_planet[$key]."|".$tr_sw_degree[$key]."|".$tr_sw_dir[$key]."|"."<br>";}
  };
  
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
	echo "PLACE:" . $place_boy."<BR>";
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
$tr_sw_planet = updateStrength($tr_sw_planet);


$chartdate=date("d/m/Y",strtotime($chart_date));
$Planet_degree=displayDegree($sw_degree);
$Transit_Planet_degree=displayDegree($tr_sw_degree,false); //false for outer/transit/boy chart data

//$sw_degree = sortDegree($sw_degree);
$sw_planet = sortDegree($sw_planet);

updateIndhuLagna();

/* panchang data creation 

$p = new Panchang();
$dd = date("d",strtotime($chart_date));
$mm = date("m",strtotime($chart_date));
$yy = date("Y",strtotime($chart_date));
$hh = date("H",strtotime($chart_time));
$mn = date("i",strtotime($chart_time));

//echo "$dd-$mm-$yy-$hh-$mn";

$temp = explode(":",$tz_offset,-1);

$zhh = $temp[0];
$zmn = $temp[1];

$hr = $hh + $mn/60.0;
$zhr = $zhh + $zmn/60.0;

$data = $p->calcPanchanga($dd, $mm, $yy, $hr, $zhr);

foreach($data as $key => $value){
	$panchang[$key] = $value;
}*/

echo <<<EOL
	<div class="title"></div>
			<div class='column-menu'>
				<div class='desktop'>
EOL;
				displayUserInfo($_SESSION['userinfo']);
echo <<<EOL
				<button class="menu" onClick="window.open('index.php','_self')">Home</button>
				<button class='menu' onClick='window.open("Matching_input.php","_self")'>New Matching Chart</button>
				<button class='menu' onClick='window.open("matchingChartDisplay.php","_self")'>Matching Chart history</button>
			</div>
			<div class='mobile'>
EOL;
				//displayUserInfoMobile($_SESSION['userinfo']);
				include "matchingChartMenu_mobile.php";
echo <<<EOL
			</div>
			</div>
			<div class='column-body'>

					<center>
					<div class="divTable blueTable" style="height:70px;">
					<P style="color:#660000;font-weight:bold; font-size:12px; text-align:center; background-color:white;line-height: 20px;">திருமண பொருத்தம்</P>
					<BR>
					
								<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
									<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">
										
										<form id='matchingChart' action='Matching_Chart.php' method='post'>
											<input type='hidden' id='nPlace_girl' name='nPlace_girl' value='$in_place_girl'>
											<input type='hidden' id='nPlace_boy' name='nPlace_boy' value='$in_place_boy'>
											<input type='hidden' id='nGirlName' name='nGirlName' value='$in_girlname'>
											<input type='hidden' id='nBoyName' name='nBoyName' value='$in_boyname'>
											<input type='hidden' id='nGirlDate' name='nGirlDate' value='$chart_date'>
											<input type='hidden' id='nGirlTime' name='nGirlTime' value='$chart_time'>
											<input type='hidden' id='nBoyDate' name='nGirlDate' value='$transit_date'>
											<input type='hidden' id='nBoyTime' name='nBoyTime' value='$transit_time'>
											<input type='hidden' id='nMark' name='nMark' value='$in_mark'>
											<input type='hidden' id='nRowid' name='nMark' value='$in_rowid'>
											<div><H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px; text-align:center">&#128105;Girl BD: <input type='date' id='nDate' name='nDate' style="background-color:var(--girl-chart-background);color:#660000;font-size:12px;font-weight:bold;width:90px;" value=$chart_date ></div>
											<div>&#128105;Girl BT: <input type='time' id='nTime' name='nTime' style="background-color:var(--girl-chart-background);color:#660000;font-size:12px;font-weight:bold;width:100px" step='1' value=$chart_time></H4></div>
											<div><H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px; text-align:center"></div>
											<div>&#128104;Boy BD: <input type='date' id='in_date_boy' name='in_date_boy' style="background-color:var(--boy-chart-background);color:#660000;font-size:12px;font-weight:bold;width:90px;" value=$transit_date></div>
											<div>&#128104;Boy BT: <input type='time' id='in_time_boy' name='in_time_boy' style="background-color:var(--boy-chart-background);color:#660000;font-size:12px;font-weight:bold;width:100px" step='1' value=$transit_time></H4></div>
											<BR>
											<input type='submit' class='actionBtnDefault' id='btnSubmit' value='Submit'>
											<input type='button' class='actionBtnDefault' id='save' value='Save' onClick='saveChart();'>
											<input type='button' class='actionBtnDefault' value='Download' onClick='generatePdf();'>

										</form>
									</div>
								</div>
							</div>
					
					<div class="divTable blueTable" id="chart">
						
						<div class="divTableBody">
							<div class="divTableRow">
									<div class="divTableOuterCellLeftTop">$tr_sw_planet[11]</div>
									<div class="divTableOuterCell" style='border-left:none'></div>
									<div class="divTableOuterCell">$tr_sw_planet[0]</div>
									<div class="divTableOuterCell">$tr_sw_planet[1]</div>
									<div class="divTableOuterCell" style='border-right:none'></div>
									<div class="divTableOuterCellRightTop">$tr_sw_planet[2]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell" style='border-top:none'></div>
									<div class="divTableCell">$sw_planet[11]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[0]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[1]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[2]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell" style='border-top:none'></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[10]</div>
									<div class="divTableCell">$sw_planet[10]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell1"></div>
									<div class="divTableCenterCell2"></div>
									<div class="divTableCell">$sw_planet[3]<span class='symbol'>&#9790;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[3]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[9]</div>
									<div class="divTableCell">$sw_planet[9]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell3"></div>
									<div class="divTableCenterCell4"></div>
									<div class="divTableCell">$sw_planet[4]<span class='symbol'>&#9737;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[4]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell" style='border-bottom:none'></div>
									<div class="divTableCell">$sw_planet[8]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[7]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[6]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[5]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell" style='border-bottom:none'></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCellLeftBottom">$tr_sw_planet[8]</div>
									<div class="divTableOuterCell" style='border-left:none'></div>
									<div class="divTableOuterCell">$tr_sw_planet[7]</div>
									<div class="divTableOuterCell">$tr_sw_planet[6]</div>
									<div class="divTableOuterCell" style='border-right:none'></div>
									<div class="divTableOuterCellRightBottom">$tr_sw_planet[5]</div>
							</div>
						</div>	
					</div>
					<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
EOL;
	
						/* Panchang display
						echo "<table>";
							echo "<TR>";
								echo "<TD>Thithi</TD><td>".$panchang['tithi']."</td>";
							echo "</TR>";
							echo "<TR>";
								echo "<TD>Paksha</TD><td>".$panchang['Paksha']."</td>";
							echo "</TR>";
							echo "<TR>";
								echo "<TD>Nakshatra</TD><td>".$panchang['nakshatra']."</td>";
							echo "</TR>";
							echo "<TR>";
								echo "<TD>Yoga</TD><td>".$panchang['yoga']."</td>";
							echo "</TR>";
							echo "<TR>";
								echo "<TD>Karana</TD><td>".$panchang['karana']."</td>";
							echo "</TR>";
							echo "<TR>";
								echo "<TD>Rashi</TD><td>".$panchang['rashi']."</td>";
							echo "</TR>";
						echo "</TABLE>"; */
echo <<<EOL

							
					</div>
						
EOL;
								/*if($autoAnalysisMenu==1){
									echo "<div class='footer'>Scroll down for Auto Analysis details &#9660;</div>";
								}*/
					calculateStarLord();
					calculateStarLord_boy();
					
					calculateGirlPanchang();
					calculateBoyPanchang();
					calculateThithiSoonyam($g_panchang_girl["thithi"],$g_panchang_boy["thithi"]);
					calculateVimDasaBalance($moon_degree,$moon_house,$chart_date,$chart_time,$g_panchang_girl['nak'],$g_girl_nak_pad_idx,true);		
					calculateVimDasaBalance($transit_moon_degree,$transit_moon_house,$transit_date,$transit_time,$g_panchang_boy['nak'],$g_boy_nak_pad_idx,false);
					displayButtons(true); //starlord dasa buttons for girl
					displayButtons(false); //starlord dasa buttons for boy
					calculateCurrentDasaBukthi(true); //for girl
					calculateCurrentDasaBukthi(false); //for boy
					//displayThithiSoonyam($g_panchang_boy["thithi"],false);
					
					displayGirlBoyDetails();

					echo "<BR>";
					echo <<<EOL
					<div class="divTable blueTable" style="height:40px;">
						<div class="divTableRow">
							<div class="divPanchangHeading" colspan=3>
								<H6 style='font-weight:bold;color:black'>திருமணம் பொருத்தம்*</H6>
							</div>
						</div>
					</div>
EOL;
					
					echo "<center><div class='divTable blueTable' style='height:40px;'>"; 
					
					matchingRule1();
					matchingRule2(true);
					matchingRule3();
					matchingRule4();
					matchingRule5();
					matchingRule6();
					matchingRule7();
					matchingRule8(true);
					//matchingRule8();
					matchingRule9();
					matchingRule10();
					matchingRule11($g_panchang_girl['thithi'], $g_panchang_boy['thithi']);
					matchingRule12();
					matchingRule13();
					displayMatchingResult();
					
					echo "</div></center>";
					if(strlen($g_seventh_house_disclaimer) > 0)
						display_7thHouse_disclaimer();
					displayDisclaimer();
					
					
					

echo <<<EOL
			
					</div></center>
				</div>
			</div>
		
		
EOL;

/*
if($autoAnalysisMenu==1){
	createAutoAnalysisSection();
}

if($cricketMenu==1){
	createCricketSection();
}
*/

//Log user acitivity
if(isset($_COOKIE["phone"])) {
$activity = 'Matching|'.$in_girlname."|".$place_girl."|".$chart_date."|".$chart_time. "|" . $in_boyname. "|". $place_boy."|".$transit_date."|".$transit_time;
	logActivity($conn,$_COOKIE["phone"],$activity);
}

debug("in_mark=$in_mark,matching_percent=$matching_percent");

if((int)$in_mark != (int)$matching_percent){
	updateMatchingMark($conn,$in_rowid,$matching_percent);
}

dbDisconnect($conn);

$_POST["mTime"]="";
$_SESSION["charttime"]="";
$_SESSION["inside"]=$sw_planet;
$_SESSION["outside"]=$tr_sw_planet;
$_SESSION["planet_degree"]=$Planet_degree;
$_SESSION["transit_planet_degree"]=$Transit_Planet_degree;
$_SESSION["transit_date"]=$transit_date;
$_SESSION["transit_time"]=$transit_time;
$_SESSION["girlname"]=$in_girlname;
$_SESSION["boyname"]=$in_boyname;
?>

</body>
</html>