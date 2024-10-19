<!DOCTYPE html>
<?php
include_once "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;
$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$home = isset($_GET['home'])? "none" : "";
$flag = (int)isset($_GET['flag']) ? $_GET['flag'] : 0;

if(isset($_POST['place'])){
	$cookie_name = "panchang_place";
	$cookie_value = $_POST['place'];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
}

$place = isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : "Chennai|Asia/Kolkata";
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
$nak_pad_idx = 0;
$dasa_balance = array(null,null,null); // years, months, days
$birth_dasa_athipathi="";
$g_dasaCurrent=array();
$current_dasa_bukthi=array();
$yogi_arr=array();
$country="";
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
$g_kariNaal ="";
$next_day="";



// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

if(isset($_POST['nDate']) && $_POST['nDate']==""){
	$chart_date = date("Y-m-d");
	$chart_time = date("H:i:s");
}
else{
	$chart_date = isset($_POST['nDate']) ? $_POST['nDate'] : date("Y-m-d");
	$chart_time = isset($_POST['nTime']) ? $_POST['nTime'] : date("H:i:s");
}

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));

$next_day=date("Y-m-d",strtotime($chart_date. ' + 1 days'));
$previous_day=date("Y-m-d",strtotime($chart_date. ' - 1 days'));

?>

<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>PK Astro</title>
    <meta name="description" content="One stop website for astrology">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
	<script src="panchang.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="my_panchang.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="common.js?<?php echo date('ymdhis'); ?>"></script>
    <script type="text/javascript">
	var timezone = '<?php echo $TZ; ?>';
	
	function init(){
			if(getCookie(focus)=='date'){
				document.getElementById('nDate').focus();
			}
			
			if(getCookie(focus)=='time'){
				document.getElementById('nTime').focus();
			}
			
			delete_cookie(focus);
			
			document.getElementById('place').value = '<?php echo $place; ?>';
			
			document.getElementById("starlordHeadMutable").style.display = 'table';
			document.getElementById("starlordBodyMutable").style.display = 'table';
		} 
	
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
		
		//Submit form on date change
		function dateChange(form) { 
			set_cookie(focus,'date');
			form.submit();
		}
		
		//Submit form on time change
		function timeChange(form)  {
			set_cookie(focus,'time');
			form.submit();
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
			$(document).on("click", ".result div", function(){
				$(this).parents(".search-box").find('input[type="text"]').val($(this).text());
				$(this).parent(".result").empty();
				$(this).parent("#lplace").hide();
			});
		});
		
		function placeOnBlur() {
			let place = document.getElementById("place").value;
			
			if(place==""){
				/*document.getElementById('place').value = '<?php echo $place; ?>';*/
				document.getElementById('place').value = '<?php echo $place; ?>';
			}
		}
		
		function dropdownFormSubmit() {
			document.getElementById('nDate').value = "";
			document.getElementById('nTime').value = "";
			document.forms["updatePanchang"].submit();
		}
		
		function movePanchang(days){
			datetime = (new Date(document.getElementById('nDate').value+' '+document.getElementById('nTime').value));
			switch(days){
				case -1:
					new_datetime = new Date(datetime.getTime()-60*60*24*1000);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				case -30:
					new_datetime = new Date(datetime.getTime()-60*60*24*1000*30);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				case -365:
					new_datetime = new Date(datetime.getTime()-60*60*24*1000*365);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				case 1:
					new_datetime = new Date(datetime.getTime()+60*60*24*1000);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				case 30:
					new_datetime = new Date(datetime.getTime()+60*60*24*1000*30);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				case 365:
					new_datetime = new Date(datetime.getTime()+60*60*24*1000*365);
					document.getElementById('nDate').value = formatDateYYYYMMDD(new_datetime);
					break;
				default:
					break;
			}
			document.querySelector("#loader").style.visibility = "visible";
			
			document.forms["updatePanchang"].submit();
		}
		
		function showKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "block";
		}

		function hideKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "none";
		}
		
		function showDetailsDiv(e,type) {
			switch(type){
				case 1:
					divName = "thithiDetailsDiv";
					break;
				case 2:
					divName = "karanaDetailsDiv";
					break;
				case 3:
					divName = "yogiDetailsDiv";
					break;
				case 4:
					divName = "ayogiDetailsDiv";
					break;
				case 5:
					divName = "nakDetailsDiv";
					break;
			}
			
			$(divName).delay(3000).fadeOut('slow');
			//document.getElementById(divName).style.display = "inline";
			document.getElementById(divName).style.display = "block";
			
			//var left  = e.clientX - 100 + "px";
			//var top  = e.clientY  + "px";
			//var left  = e.pageX - 50 + "px";
			var left = e.ScreenX/2 + "px";
			var top  = e.pageY  + "px";
		
			var div = document.getElementById(divName);

			//div.style.left = left;
			//div.style.top = top;
			div.style.left = 0;
			div.style.top = 0;
			//div.style.position = absolute;
			//div.style.left = (e.screenX + window.scrolLeft);
			//div.style.top = (e.screenY + window.scrolTop);
		  
		  //document.getElementById("detailsDiv").style.top = 0;
		  //document.getElementById("detailsDiv").style.left = 0;
		  //document.getElementById("detailsDiv").style. = 0;
		}

		function hideDetailsDiv() {
		  document.getElementById("thithiDetailsDiv").style.display = "none";
		  document.getElementById("karanaDetailsDiv").style.display = "none";
		  document.getElementById("yogiDetailsDiv").style.display = "none";
		  document.getElementById("ayogiDetailsDiv").style.display = "none";
		  document.getElementById("nakDetailsDiv").style.display = "none";
		}
		
		function formSubmitButton(){
			if(validateForm()==true){
				document.forms["updatePanchang"].submit();
			}
		}
		
		function onDateChange(){
			document.forms["updatePanchang"].submit();
		}
		
		function onTimeChange(){
			document.forms["updatePanchang"].submit();
		}
		
		/*function onPlaceChange(){
			document.updatePanchang.place_flag.value = 1;
			
			document.forms["updatePanchang"].submit();
		}*/
		
		document.onreadystatechange = function() {
		
		if(document.getElementById('dummy') != null){	//chart not loaded
			if(document.getElementById('loader'))
				//document.querySelector("#loader").style.display = "none";
				document.querySelector("#loader").style.visibility = "hidden";
		}
		else{
			//document.querySelector("body").style.visibility = "hidden";
			document.querySelector("#loader").style.visibility = "visible";
		}
		
		//handleLongPress();
		handleSwipe();
	};
	
	function handleSwipe(){
		const bodyElement = document.body;
		const hammer = new Hammer(bodyElement);

		// Listen for swipe events
		hammer.on('swipe', function (event) {
			switch (event.direction) {
				case Hammer.DIRECTION_LEFT:
					console.log('Left swipe detected');
					window.location.href = 'index.php';
					break;
			}
		});
	}
	
	function handleLongPress(){
		// Assuming you have an element with the ID 'my-element'
		var myElement = document.getElementById('mybody');

		// Initialize Hammer.js
		var mc = new Hammer.Manager(myElement);

		// Add the long press gesture
		mc.add(new Hammer.Press({ event: 'longpress', time: 500 }));

		// Handle the long press event
		mc.on('longpress', function (ev) {
			console.log('Long press detected!');
			window.location.href = 'index.php'
			// Perform your custom action here
			// For example, show a modal or trigger some other behavior
		});
	}
	
	function genPanchang(place){
			var url = "gen_panchang_form.php"+"?place="+place;
			//window.open("gen_panchang_form.php","_self");
			window.open(url,"_self");
			return false;
		}
		
	function validateForm() {
		  p = document.getElementById("place").value;

		  if (p == "") {
			alert('பிறந்த இடம் கொடுல்லவில்லை');
			//document.getElementById("error").innerHTML = "பிறந்த இடம் கொடுல்லவில்லை";
			document.getElementById('place').style.borderColor = "red";
			return false;
		  }
		  
		  if (p.includes("|")==false || p.includes("/")==false || p.length<10){
			  alert('நீங்கள் இடத்தை கீழ் வரும் பட்டியலில் தேர்ந்தெடுக்கவில்லை');
			  document.getElementById('place').style.borderColor = "red";
			  return false;
		  }
		  
		  return true;
	}
	
	function updatePanchangData(){
		const t = new Date('<?php echo $chart_date." ".$chart_time; ?>');

		newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate(),t.getHours(),t.getMinutes()); 
		
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			//document.writeln('<div class="divTableRow">');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:80px">'+formatDate(newdate)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:50px">'+panchang.Day.name+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Nakshatra.name+" "+formatDateTime(panchang.Nakshatra.end)+' வரை'+displayNextNak(newdate,panchang.Nakshatra)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Tithi.name+' '+formatDateTime(panchang.Tithi.end)+' வரை'+displayNextThithi(newdate,panchang.Tithi)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.start)+" முதல் "+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.start)+" முதல் "+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
			//document.writeln('</div>');
			
			document.getElementById('thithi').innerHTML = panchang.Tithi.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Tithi.start)+' முதல்<BR>'+formatDateTime(panchang.Tithi.end)+' வரை</div>';
			document.getElementById('nak').innerHTML = panchang.Nakshatra.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Nakshatra.start)+' முதல்<BR>'+formatDateTime(panchang.Nakshatra.end)+' வரை</div>';
			document.getElementById('yogam').innerHTML = panchang.Yoga.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Yoga.start)+' முதல்<BR>'+formatDateTime(panchang.Yoga.end)+' வரை</div>';
			document.getElementById('karanam').innerHTML = panchang.Karna.name+'('+getKaranaPlanet(panchang.Karna.name)+')'+'<BR><div class="divSmall">'+formatDateTime(panchang.Karna.start)+' முதல்<BR>'+formatDateTime(panchang.Karna.end)+' வரை</div>';
		});

		//storing initial end times for nak, thithi, yogam, karanam as new generation will result in changes to panchang object
		nak_end = panchang.Nakshatra.end;
		thithi_end = panchang.Tithi.end;
		yogam_end = panchang.Yoga.end;
		karanam_end = panchang.Karna.end;

		//To add next nak
		//newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate(),t.getHours()+36); //adding 26 hours for thithi, nak, yogam
		//newdate = new Date(panchang.Nakshatra.end.getFullYear(),panchang.Nakshatra.end.getMonth(),panchang.Nakshatra.end.getDate(),panchang.Nakshatra.end.getHours(),panchang.Nakshatra.end.getMinutes()+1); //adding 26 hours for thithi, nak, yogam
		newdate = new Date(nak_end.getFullYear(),nak_end.getMonth(),nak_end.getDate(),nak_end.getHours(),nak_end.getMinutes()+1); //adding 1 minute from current nak to get next
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('nak').innerHTML = document.getElementById('nak').innerHTML + panchang.Nakshatra.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Nakshatra.start)+' முதல்<BR>'+formatDateTime(panchang.Nakshatra.end)+' வரை</div>';
		});

		//To add next karanam
		newdate = new Date(karanam_end.getFullYear(),karanam_end.getMonth(),karanam_end.getDate(),karanam_end.getHours(), karanam_end.getMinutes()+1); //adding 1 mins to get next karanam
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('karanam').innerHTML = document.getElementById('karanam').innerHTML + panchang.Karna.name+'('+getKaranaPlanet(panchang.Karna.name)+')'+'<BR><div class="divSmall">'+formatDateTime(panchang.Karna.start)+' முதல்<BR>'+formatDateTime(panchang.Karna.end)+' வரை</div>';
		});

		//To add next thithi
		newdate = new Date(thithi_end.getFullYear(),thithi_end.getMonth(),thithi_end.getDate(),thithi_end.getHours(),thithi_end.getMinutes()+1); //adding 1 min to get next thithi
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('thithi').innerHTML = document.getElementById('thithi').innerHTML + panchang.Tithi.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Tithi.start)+' முதல்<BR>'+formatDateTime(panchang.Tithi.end)+' வரை</div>';
		});

		//To add next yogam
		newdate = new Date(yogam_end.getFullYear(),yogam_end.getMonth(),yogam_end.getDate(),yogam_end.getHours(),yogam_end.getMinutes()+10); //adding 10 min to get next yogam
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('yogam').innerHTML = document.getElementById('yogam').innerHTML + panchang.Yoga.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Yoga.start)+' முதல்<BR>'+formatDateTime(panchang.Yoga.end)+' வரை</div>';
		});
	}
	
	function getKaranaPlanet(karan) {
		const karanaPlanetArray = {
				"பவ": "செவ்வாய்",
				"பாலவ": "ராகு",
				"கௌலவ": "சனி",
				"தைதுலை": "சுக்ரன்",
				"கரசை": "சந்திரன்",
				"வணிசை": "சூரியன்",
				"பத்தரை": "கேது",
				"சகுனி": "சனி",
				"சதுஷ்பாத": "குரு",
				"நாகவ": "ராகு",
				"கிம்ஸ்துக்கினம்": "புதன்"
		};

		return karanaPlanetArray[karan];
	}
	
	$(document).ready(function(){
		updatePanchangData();
		date='<?php echo $chart_date; ?>';
		time='<?php echo $chart_time; ?>';
		place='<?php echo $place; ?>';

		url = 'hora.php?date='+date+'&time='+time+'&place='+place;

		$('#hora').load(url+' div#horaLordTxt'); //getting the current hora lord running the hora.php and extract only the lord text
		
		//$('#hora').load(url); //getting the current hora lord running the hora.php and extract only the lord text
		
		var param = '<?php echo $flag; ?>';
		
		if(param==1){ //Panchang details alone
			document.getElementById('appMenu').style.display = 'none';
			document.getElementById('panchangForm').style.display = 'none';
			document.getElementById('panchangDetailsHeading').style.display = 'reset';
			document.getElementById('panchangDetails').style.display = 'table';
			document.getElementById('transitChartHeading').style.display = 'none';
			document.getElementById('transitChart').style.display = 'none';
		}
		else if(param==2){ //transit chart alone
			document.getElementById('appMenu').style.display = 'none';
			document.getElementById('panchangForm').style.display = 'none';
			document.getElementById('panchangDetailsHeading').style.display = 'none';
			document.getElementById('panchangDetails').style.display = 'none';
			document.getElementById('transitChartHeading').style.display = 'reset';
			document.getElementById('transitChart').style.display = 'reset';
		}
	}); 
	
	function generateMuhurtam(today_sunrise,tomm_sunrise) {
       var count = 0;
          
       mukurt_arr = [
						["ருத்ர","அசுபமானது"],
						["ஆஹி","அசுபமானது"],
						["மித்ர","சுப"],
						["பித்ரு","அசுபமானது"],
						["வசு","சுப"],
						["வராஹ","சுப"],
						["விஸ்வேதேவா","சுப"],
						["விதி","மங்களகரமானது (திங்கள் மற்றும் வெள்ளி தவிர)"],
						["சுதாமுகீ","சுப"],
						["புருஹூத","அசுபமானது"],
						["வாஹிநீ","அசுபமானது"],
						["நக்தனகரா","அசுபமானது"],
						["வருண","சுப"],
						["ஆர்யமன்","மங்களகரமானது (ஞாயிற்றுக்கிழமைகள் தவிர)"],
						["பக","அசுபமானது"],
						["கிரீச","சுப"],
						["அஜபாத","அசுபமானது"],
						["அஹிர்புத்ன்ய","சுப"],
						["புஷ்ய","சுப"],
						["அஸ்வினி","சுப"],
						["யம","அசுபமானது"],
						["அக்னி","சுப"],
						["விதாத்ரு","சுப"],
						["கண்ட","சுப"],
						["அதிதி","சுப"],
						["ஜீவ/அம்ருத","மிகவும் மங்களகரமானது"],
						["விஷ்ணு","சுப"],
						["த்யுமத்கத்யுதி","சுப"],
						["பிரம்மா","மிகவும் மங்களகரமானது"],
						["சமுத்ர","சுப"]
					];
      
       var html_str='';
      
       html_str='<center><div class="divTable blueTable" style="width:100%;height:100%">';
       html_str=html_str+'<div class="divTableRow"><div class="divAnthraHeading" colspan="3">முகூர்த்தங்கள்</div></div></div>';
       html_str=html_str+'<center><div class="divTable blueTable" style="width:100%;height:100%">';
	   
	   
	   todaysunrise = new Date(today_sunrise);
	   tommsunrise = new Date(tomm_sunrise);
	   muhurt_time = (tommsunrise.getTime() - todaysunrise.getTime()) / (1000 * 60 )/30;

		start_time = new Date(todaysunrise.getTime());
		
		html_str = html_str+'<div class="divTableRow"><div class="divMuhurtHeading">ஆரம்பம்</div><div class="divMuhurtHeading">முடிவு</div><div class="divMuhurtHeading">முகூர்த்தம்</div><div class="divMuhurtHeading">பலன்</div></div>';

       for (var i = 0; i < 30; i++) {
		   end_time = new Date((start_time.getTime()+muhurt_time*60000));
		   
		   stime=String(start_time.getDate()).padStart(2, '0')+'-'+String(start_time.getMonth()+1).padStart(2, '0')+'-'+start_time.getFullYear()+' '+String(start_time.getHours()).padStart(2, '0')+':'+String(start_time.getMinutes()).padStart(2, '0');
		   etime=String(end_time.getDate()).padStart(2, '0')+'-'+String(end_time.getMonth()+1).padStart(2, '0')+'-'+end_time.getFullYear()+' '+String(end_time.getHours()).padStart(2, '0')+':'+String(end_time.getMinutes()).padStart(2, '0');
           //console.log(stime+'-'+etime+'   '+mukurt_arr[i]);
		   
		   if((mukurt_arr[i][1]).indexOf("அசுபமானது")!=-1){
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaRed">'+stime+'</div><div class="divMurtaRed">'+etime+'</div><div class="divMurtaRed">'+mukurt_arr[i][0]+'</div><div class="divMurtaRed">'+mukurt_arr[i][1]+'</div></div>';
		   }
		   else if((mukurt_arr[i][1]).indexOf("மங்களகரமானது")!=-1){
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaBrightGreen">'+stime+'</div><div class="divMurtaBrightGreen">'+etime+'</div><div class="divMurtaBrightGreen">'+mukurt_arr[i][0]+'</div><div class="divMurtaBrightGreen">'+mukurt_arr[i][1]+'</div></div>';
		   }
		   else{
   			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaGreen">'+stime+'</div><div class="divMurtaGreen">'+etime+'</div><div class="divMurtaGreen">'+mukurt_arr[i][0]+'</div><div class="divMurtaGreen">'+mukurt_arr[i][1]+'</div></div>';
		   }
          
		   start_time = end_time;
       }
       
       html_str=html_str+"</div></center>";
      
       document.getElementById('muhurthamDiv').innerHTML = html_str;
       document.getElementById('muhurthamDiv').style.display = 'block';
     }
	
	function generateMuhurtam_default() {
       var count = 0;
          
       mukurt_arr = [
						["ருத்ர","அசுபமானது"],
						["ஆஹி","அசுபமானது"],
						["மித்ர","சுப"],
						["பித்ரு","அசுபமானது"],
						["வசு","சுப"],
						["வராஹ","சுப"],
						["விஸ்வேதேவா","சுப"],
						["விதி","மங்களகரமானது (திங்கள் மற்றும் வெள்ளி தவிர)"],
						["சுதாமுகீ","சுப"],
						["புருஹூத","அசுபமானது"],
						["வாஹிநீ","அசுபமானது"],
						["நக்தனகரா","அசுபமானது"],
						["வருண","சுப"],
						["ஆர்யமன்","மங்களகரமானது (ஞாயிற்றுக்கிழமைகள் தவிர)"],
						["பக","அசுபமானது"],
						["கிரீச","சுப"],
						["அஜபாத","அசுபமானது"],
						["அஹிர்புத்ன்ய","சுப"],
						["புஷ்ய","சுப"],
						["அஸ்வினி","சுப"],
						["யம","அசுபமானது"],
						["அக்னி","சுப"],
						["விதாத்ரு","சுப"],
						["கண்ட","சுப"],
						["அதிதி","சுப"],
						["ஜீவ/அம்ருத","மிகவும் மங்களகரமானது"],
						["விஷ்ணு","சுப"],
						["த்யுமத்கத்யுதி","சுப"],
						["பிரம்மா","மிகவும் மங்களகரமானது"],
						["சமுத்ர","சுப"]
					];
      
       var html_str='';
      
       html_str='<center><div class="divTable blueTable" style="width:100%;height:100%">';
       html_str=html_str+'<div class="divTableRow"><div class="divAnthraHeading" colspan="3">முகூர்த்தங்கள்</div></div></div>';
       html_str=html_str+'<center><div class="divTable blueTable" style="width:100%;height:100%">';
	   
	   //this is to negate sunrise calculation
	   today_sunrise = '<?php echo $chart_date; ?>' + ' 06:00:00';
	   
	   
	   tomm_sunrise = '<?php echo $next_day; ?>' + ' 06:00:00';
	   
	   todaysunrise = new Date(today_sunrise);
	   tommsunrise = new Date(tomm_sunrise);
	   muhurt_time = (tommsunrise.getTime() - todaysunrise.getTime()) / (1000 * 60 )/30;

		start_time = new Date(todaysunrise.getTime());
		
		html_str = html_str+'<div class="divTableRow"><div class="divMuhurtHeading">ஆரம்பம்</div><div class="divMuhurtHeading">முடிவு</div><div class="divMuhurtHeading">முகூர்த்தம்</div><div class="divMuhurtHeading">பலன்</div></div>';

       for (var i = 0; i < 30; i++) {
		   end_time = new Date((start_time.getTime()+muhurt_time*60000));
		   
		   stime=String(start_time.getDate()).padStart(2, '0')+'-'+String(start_time.getMonth()+1).padStart(2, '0')+'-'+start_time.getFullYear()+' '+String(start_time.getHours()).padStart(2, '0')+':'+String(start_time.getMinutes()).padStart(2, '0');
		   etime=String(end_time.getDate()).padStart(2, '0')+'-'+String(end_time.getMonth()+1).padStart(2, '0')+'-'+end_time.getFullYear()+' '+String(end_time.getHours()).padStart(2, '0')+':'+String(end_time.getMinutes()).padStart(2, '0');
           //console.log(stime+'-'+etime+'   '+mukurt_arr[i]);
		   
		   if((mukurt_arr[i][1]).indexOf("அசுபமானது")!=-1){
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaRed">'+stime+'</div><div class="divMurtaRed">'+etime+'</div><div class="divMurtaRed">'+mukurt_arr[i][0]+'</div><div class="divMurtaRed">'+mukurt_arr[i][1]+'</div></div>';
		   }
		   else if((mukurt_arr[i][1]).indexOf("மங்களகரமானது")!=-1){
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaBrightGreen">'+stime+'</div><div class="divMurtaBrightGreen">'+etime+'</div><div class="divMurtaBrightGreen">'+mukurt_arr[i][0]+'</div><div class="divMurtaBrightGreen">'+mukurt_arr[i][1]+'</div></div>';
		   }
		   else{
   			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaGreen">'+stime+'</div><div class="divMurtaGreen">'+etime+'</div><div class="divMurtaGreen">'+mukurt_arr[i][0]+'</div><div class="divMurtaGreen">'+mukurt_arr[i][1]+'</div></div>';
		   }
          
		   start_time = end_time;
       }
       
       html_str=html_str+"</div></center>";
      
       document.getElementById('muhurthamDiv').innerHTML = html_str;
       document.getElementById('muhurthamDiv').style.display = 'block';
     }
	 
	 function hide(){
		document.getElementById('muhurthamDiv').style.visibility='hidden';
	}

				
	</script>
</head>

<body id='mybody' onLoad="init();">
<div class="karanamDiv" id="karanamDiv" onclick="hideKaranamDiv()"><!-- <img src='images/karanam.png' /> --></div>
<div class="muhurthamDiv" id="muhurthamDiv" onclick="this.style.display='none'"></div>
<div id="loader" class="center"></div>

<?php

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
*/

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
	  CURLOPT_POSTFIELDS => 'client_secret=ZAMQswTaD7wQJ43amLufXCZ73Ja8wY6mb3Irp63x&grant_type=client_credentials&client_id=8c1f4f14-9843-434b-a409-4c63fc3dcfdf',
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

/*function calculateBirthPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_nak, $g_karan, $panchang,$g_nak_pad_idx,$full_sun_deg,$full_moon_deg;
	
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
			
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
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
	
	debug("--THI IDX--".$idx."----");
	
	//Get nak end time
	$thithi_end_time = findThithiEndTime($thi_idx);
	$panchang["thithi-end-time"] = $thithi_end_time;
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
	
	//Store karanam for future use
	$g_karan = $panchang["karan"];
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	//insert panchang data into table for future use
	//insertArrayIntoDB("panchang",$panchang);
}*/

function displayHoroDetails()
{
	global $panchang, $in_name, $chart_date, $chart_time, $g_dasaBalance, $place, $g_thithi_soonyam_matching_arr,$g_dasaCurrent, $transit_date, $lagna_house, $country,$yogi_arr, 
		$g_pancha_pakshi, $g_thithi, $g_paksham, $moon_house, $today_sunrise, $tomo_sunrise,$today_sunset,$city,$g_nak, $g_thithi_idx, $g_yoga_idx, $nak_pad_idx, $g_kariNaal;
	
	echo "<div class='divTable blueTable' style='height:40px;' id='panchangDetailsHeading'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)'>";
				echo "<div class='divPanchangHeading' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>பஞ்சாங்கம்</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
	//display panchang table 
	echo "<div class='divTable blueTable' id='panchangDetails'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>ஆ.தேதி</div>";
			echo "<div class='divPanchang'>".date('d-m-Y',strtotime($chart_date))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>த.தேதி</div>";
			echo "<div class='divPanchang'>".getTamilDate()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நேரம்</div>";
			echo "<div class='divPanchang'>".date('h:i:s A',strtotime($chart_time))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>இடம்</div>";
			echo "<div class='divPanchang'>".$city."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['vaaram'])[1]."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>திதி</div>";
				echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'></div>";
			$g_thithi = explode('/',explode('[',$panchang['thithi'])[0])[1];
			
			if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
				$g_paksham = "";
				//echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi . $g_paksham . "<BR>". "</div>";
			}
			else{
				$g_paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
				//echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi ." [". $g_paksham . "]" . "</div>";
			}
		echo "</div>";
		
		displayDetailsDiv('thi',$g_thithi_idx);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			//echo "<div class='divPanchang' id='nak'>".explode('/',$panchang['nak'])[1]."<BR>".$panchang['nak-end-time']." வரை</div>";
			echo "<div class='divPanchang' id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'></div>";
		echo "</div>";
		displayDetailsDiv('nak',ceil($nak_pad_idx)/4);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			//echo "<div class='divPanchang' id='yogam'>".$panchang['yoga']."</div>";
			echo "<div class='divPanchang' id='yogam'></div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			$karan = explode('/',$panchang['karan'])[1];
			//echo "<div class='divPanchang' id='karanam' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
			echo "<div class='divPanchang' id='karanam' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'></div>";
		echo "</div>";
		displayDetailsDiv('kar',$karan);
		$yogi_arr = getYogi($panchang['yoga']);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'>".$yogi_arr[1]."(".$yogi_arr[0].")</div>";
		echo "</div>";
		displayDetailsDiv('yogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதய ஹோரை</div>";
			echo "<div class='divPanchang' id='hora'></div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>அமிர்தாதி யோகம்</div>";
			echo "<div class='divPanchang'>".getAmrithathiYogam()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நேத்திரம்</div>";
			echo "<div class='divPanchang'>".getNethram()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>ஜீவன்</div>";
			echo "<div class='divPanchang'>".getJeevan()."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நாள் நோக்கு</div>";
			echo "<div class='divPanchang'>".getDayType()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பஞ்ச பக்ஷி</div>";
			$g_pancha_pakshi = getPanchaPakshi();
			echo "<div class='divPanchang'>".$g_pancha_pakshi."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நக்ஷத்ர நாம எழுத்து </div>";
			echo "<div class='divPanchang'>".getStarLetters($g_nak)[0][0]."<BR>".getStarLetters($g_nak)[1][0]."</div>";
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
			echo "<div class='divPanchangHeading'>அபிஜித் நேரம்</div>";
			echo "<div class='divPanchang'>".getAbhijit()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>முகூர்த்தங்கள்</div>";
			//echo "<div class='divPanchang' onClick='generateMuhurtam(\"$today_sunrise\",\"$tomo_sunrise\");' style='color:blue;cursor:pointer'>Click to see</div>";
			echo "<div class='divPanchang' onClick='generateMuhurtam_default();' style='color:blue;cursor:pointer'>Click to see</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>அவயோகி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 4)' style='color:blue;cursor:pointer'>".$yogi_arr[3]."(".$yogi_arr[2].")</div>";
		echo "</div>";
		displayDetailsDiv('ayogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>திதி சூன்யம்</div>";
			echo "<div class='divPanchang'>".$g_thithi_soonyam_matching_arr[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>ராகு காலம்</div>";
			echo "<div class='divPanchang'>".getRahuKaalam()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>எம கண்டம்</div>";
			echo "<div class='divPanchang'>".getEmaKantam()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>குளிகை நேரம்</div>";
			echo "<div class='divPanchang'>".getKulikai()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>வார சூலை</div>";
			echo "<div class='divPanchang'>".getVaaraSoolai().'<BR>பரிகாரம் - '. getSoolaiParigaaram() ."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>சந்திராஷ்டம்</div>";
			echo "<div class='divPanchang'>".getRasi(targetHouse($moon_house-1,6))."</div>";
			//echo "<div class='divPanchang'>".getRasi(targetHouse($moon_house-1,8))."</div>";
		echo "</div>";
		//echo "<div class='divTableRow'>";
			//echo "<div class='divPanchangHeading'>பிறப்பு தசா இருப்பு</div>";
			//echo "<div class='divPanchang'>".$g_dasaBalance[0]."</div>";
		//echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய தசா புக்தி</div>";
			echo "<div class='divPanchang'>".$g_dasaCurrent[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய உதயம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சூரிய அஸ்தமனம்</div>";
			echo "<div class='divPanchang'>".explode(' ',$today_sunset)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>மற்ற விவரங்கள்</div>";
			echo "<div class='divPanchang'>$g_kariNaal</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கணக்கீட்டு முறை</div>";
			echo "<div class='divPanchang'>திருக்கணிதம்<BR>(லஹரி அயனாம்சம்)</div>";
		echo "</div>";
	echo "</div>";
}

function displayTransitChart()
{
	global $sw_planet,$g_thithi_soonyam_arr;
	
	echo "<BR>";
	echo "<div class='divTable blueTable' style='height:40px;'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)' id='transitChartHeading'>";
				echo "<div class='divPanchangHeading' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>தற்போதய கோட்சாரம்</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
echo <<<EOL
	<div class="divTable blueTable" id="transitChart">
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
					<div class="divTableCell">$sw_planet[11]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[11]</span></div>
					<div class="divTableCell">$sw_planet[0]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[0]</span></div>
					<div class="divTableCell">$sw_planet[1]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[1]</span></div>
					<div class="divTableCell">$sw_planet[2]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[2]</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$sw_planet[10]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[10]</span></div>
					<div class="divTableCenterCell" style='font-size:10px'>ராசி</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$sw_planet[3]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[3]</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$sw_planet[9]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[9]</span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'>D1</div>
					<div class="divTableCell">$sw_planet[4]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[4]</span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$sw_planet[8]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[8]</span></div>
					<div class="divTableCell">$sw_planet[7]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[7]</span></div>
					<div class="divTableCell">$sw_planet[6]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[6]</span></div>
					<div class="divTableCell">$sw_planet[5]<span class='thithi_soonyam'>$g_thithi_soonyam_arr[5]</span></div>
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



$sw_planet = sortDegree($sw_planet);

updateIndhuLagna();

echo <<<EOL
	<div class="title"></div>
		<div class='column-menu' id='appMenu' style='display:$home'>
			<div class='desktop'>
				
EOL;
				displayUserInfo($_SESSION['userinfo']);
				echo "<button class='menu' onClick=\"window.open('index.php','_self')\">Home</button>";
				include "indexMenu.php";
echo <<<EOL
			</div>
			<div class='mobile'>
EOL;
			
					//displayUserInfoMobile($_SESSION['userinfo']);
					include "panchangMenu_mobile.php";
					//include "setDefaultHome.php";
			

echo <<<EOL
			</div>
		</div>
		<div class='column-body' id='panchangBody'>
			<center>
			<form name='updatePanchang' id='panchangForm' action='new_panchang.php?debug=$DEBUG' method='post'>
				<input type='hidden' id='nPlace' name='nPlace' value='$place'>
				<input type='hidden' id='place_flag' name='place_flag' value=0>
					<H4 style="color:#000000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px; width:parent;">
						<TABLE style='width:inherit'>
						<CENTER><P style="color:#660000;font-weight:bold; font-size:16px; background-color:white;width:200px;">பஞ்சாங்கம்</P></CENTER>
						<TR>
							<TD>
								Date: <input type='date' id='nDate' name='nDate' onChange='onDateChange();' style="background-color:#FDE966;color:#000000;font-size:14px;font-weight:bold;width:110px;" value=$chart_date> 
								
							</TD>
							<TD>
								Time: <input type='time' id='nTime' name='nTime' onChange='onTimeChange();' style="background-color:#FDE966;color:#000000;font-size:14px;font-weight:bold;width:110px" step='1' value=$chart_time>								
							</TD>
						</TR>
						<TR>
							<TD colspan=2>
								<center>
									<input type='button' class='actionBtnSmall' id='prevDay' onClick='movePanchang(-1);' value='&#x25C0;நா' />	
									<button class='actionBtnSmall' id='prevMonth' onClick='movePanchang(-30);'>&#x25C0;மா</button> 
									<button class='actionBtnSmall' id='prevYear' onClick='movePanchang(-365);'>&#x25C0;வரு</button> 
									<button class='actionBtnSmall' id='nextYear' onClick='movePanchang(365);'>வரு&#x25B6;</button>
									<button class='actionBtnSmall' id='nextMonth' onClick='movePanchang(30);'>மா&#x25B6;</button>
									<button class='actionBtnSmall' id='nextDay' onClick='movePanchang(1);'>நா&#x25B6;</button>
									<BR>
									<button class='actionBtn' id='now' onClick='updateNow();window.reload();'>தற்போது</button>
								</center>
							</TD>
						</TR>
						<TR>
							<TD colspan=2>
								*Place : <div class="search-box" style='display:inline-block;'><input type="text" name="place" id="place" autocomplete="off" style="background-color:#FDE966;color:#000000;font-size:14px;font-weight:bold;width:200px" placeholder="Search place..." onfocus=this.value='' onBlur='placeOnBlur();' /><div class="result" ></div>
							</TD>
						</TR>
						</TABLE>
					</H4>
				<input type='button' id='sub_button' class='actionBtn' value='Submit' onClick="formSubmitButton();">
				<input type='button' id='downloadPanchang' class='actionBtn' value='Generate' onClick="genPanchang('$place');" /> 
				<BR><BR>
			</form>

EOL;
    
					calculatePanchang();
					calculateBirthPanchang();
					calculateVimDasaBalance($moon_degree,$moon_house,$chart_time,$chart_time,$panchang['nak'],$nak_pad_idx, true);
					calculateVimDasa();
					calculateCurrentDasaBukthi(true);
					
					//$panchang = getPanchangFromDB();
					calculateBirthThithiSoonyam($panchang["thithi"]);
					displayHoroDetails();
					$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);
					displayTransitChart();
					displayStarLord(true);
					displayDisclaimer();
					

echo <<<EOL
					<BR><BR><BR>
					</div></center>
					<div id='dummy'></div>
		
EOL;

getStarLetters($g_nak);

dbDisconnect($conn);

//$datetime=$chart_date.'T'.$chart_time.'%2B'.date('H:i',strtotime($gmt_offset));

//getProkeralaPunchangDetail($lat,$lon,$datetime,'ta');

$_SESSION["place"] = $place;
$_COOKIE['panchang_place']=$place;

unset($_SESSION['menu_clicked']);

?>

<div id='dummy'></div>
</body>
</html>