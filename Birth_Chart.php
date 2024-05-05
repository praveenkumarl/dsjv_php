<!DOCTYPE html>
<?php
include_once "lib.php";

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$in_place = isset($_POST["place"]) ? $_POST["place"] : $_POST["nPlace"];
$in_name = isset($_POST["name"]) ? $_POST["name"] : $_POST["nName"];
$in_sex = isset($_POST["sex"]) ? $_POST["sex"] : $_POST["nSex"];

$place=strtok($in_place,"|");
$TZ=strtok("|");

$_SESSION["place"]=$place;

if(isset($_POST["nRowid"])){
	$g_rowid = isset($_POST["rowid"]) ? $_POST["rowid"] : $_POST["nRowid"]; //rowid will be posted in case it's called from birthChartDisplay or transit changed
}
else{
	$g_rowid = isset($_POST["rowid"]) ? $_POST["rowid"] : "Not saved";
}

//disabling this place cookie. This interferes with Jamakol place whenever we generate birth chart
$cookie_name = "place";
$cookie_value = $in_place;
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day

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
$g_notes=isset($_POST["notes"]) ? $_POST["notes"] : (isset($_POST["hNotes"]) ? $_POST["hNotes"] : "");
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
$g_tamil_date_arr=array();
$g_vayadhotta_lagna="";
$g_age=0;
$g_mudakku_detail="";

if(isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'],'Birth_input.php')==false){
	$saveMode=1;
	$saveBtnTxt='Update';
}
?>

<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro</title>
    <meta name="description" content="One stop website for astrology">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="html2pdf.bundle.min.js" type="text/javascript"></script>

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
		
		function validateForm() {
		  let d = document.getElementById("in_date").value;
		  let t = document.getElementById("in_time").value;
		  
		  if (d == "" || t == "") {
				updateNow();
		  }
		  
		  p = document.getElementById("place").value;
		  if (p == "") {
			document.getElementById("error").innerHTML = "Place must be filled out";
			document.getElementById('place').style.borderColor = "red";
			return false;
		  }
		  
		  n = document.getElementById("name").value;
		  if (n == "") {
			document.getElementById("error").innerHTML = "Name must be filled out";
			document.getElementById('name').style.borderColor = "red";
			return false;
		  }
		}
		
		function validateTransitDate(){
			let ch_date = document.getElementById("in_date").value;
			let tr_date = document.getElementById("nTrDate").value;
			
			if(compareDates(tr_date,ch_date)<0){
				alert("கோ.தேதி பி.தேதிக்குப் பிறகு தேற்வு செய்யவும்");
				document.getElementById("nTrDate").value = ch_date;
			}
			
			return false;
		}
		
		const compareDates = (d1, d2) => {
		  let date1 = new Date(d1).getTime();
		  let date2 = new Date(d2).getTime();

		  if (date1 < date2) {
			//console.log(`${d1} is less than ${d2}`);
			return -1;
		  } else if (date1 > date2) {
			//console.log(`${d1} is greater than ${d2}`);
			return 1;
		  } else {
			//console.log(`Both dates are equal`);
			return 0;
		  }
		};

		function placeOnBlur() {
			let place = document.getElementById("place").value;
			
			if(place==""){
				document.getElementById('place').value = '<?php echo $place; ?>';
			}
		}

		function nameOnBlur() {
			let name = document.getElementById("name").value;
			
			if(name==""){
				document.getElementById('name').value = '<?php echo $in_name; ?>';
			}
		}

		var timestamp_flag = 0;
		
		function loadOnePage(url){
			//window.open("onepage_birthchart.php");
			window.open(url);
			//location.href = "onepage_birthchart.php";
			return false;
		}
		
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
		
		function showStarLordTable() {
			var x = document.getElementById("DasaMatable");
			var y = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			var z3 = document.getElementById("starlordHeadMutable_boy");
			var z4 = document.getElementById("starlordBodyMutable_boy");
			var z5 = document.getElementById("d9_chart");
			
			if (z1.style.display === "none" && z2.style.display === "none") {
				z1.style.display = "table-cell";
				z2.style.display = "table-cell";
				x.style.display = "none";
				y.style.display = "none";
				z3.style.display = "none";
				z4.style.display = "none";
				z5.style.display = "none";
			  } else {
				z1.style.display = "none";
				z2.style.display = "none";
				x.style.display = "none";
				y.style.display = "none";
			  }
			  
			
		}
		
		function showStarLordTable_transit() {
			var x = document.getElementById("DasaMatable_boy");
			var y = document.getElementById("BukthiMatable_boy");
			var z1 = document.getElementById("starlordHeadMutable_boy");
			var z2 = document.getElementById("starlordBodyMutable_boy");
			var z3 = document.getElementById("d9_chart");
			
			if (z1.style.display === "none" && z2.style.display === "none") {
				z1.style.display = "table-cell";
				z2.style.display = "table-cell";
				z3.style.display = "none";
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
			var z3 = document.getElementById("starlordHeadMutable_boy");
			var z4 = document.getElementById("starlordBodyMutable_boy");
			var z5 = document.getElementById("d9_chart");
			
			if (x.style.display === "none" && y.style.display === "none") {
				x.style.display = "block";
				y.style.display = "block";
				z1.style.display = "none";
				z2.style.display = "none";
				z3.style.display = "none";
				z4.style.display = "none";
				z5.style.display = "none";
			  } else {
				x.style.display = "none";
				y.style.display = "none";
				z1.style.display = "none";
				z2.style.display = "none";
			  }
		}
		
		function showNavamsam() {
			var x = document.getElementById("d9_chart");
			var y1 = document.getElementById("DasaMatable");
			var y2 = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			var z3 = document.getElementById("starlordHeadMutable_boy");
			var z4 = document.getElementById("starlordBodyMutable_boy");
			
			if (x.style.display === "none") {
				x.style.display = "table";
				y1.style.display = "none";
				y2.style.display = "none";
				z1.style.display = "none";
				z2.style.display = "none";
				z3.style.display = "none";
				z4.style.display = "none";
			  } else {
				x.style.display = "none";
				y1.style.display = "none";
				y2.style.display = "none";
				z1.style.display = "none";
				z2.style.display = "none";
				z3.style.display = "none";
				z4.style.display = "none";
			  }
		}
		
		function saveChart()
		{
			var name = document.getElementById("name").value;
			var sex = document.getElementById("sex").value;
			var place = document.getElementById("nPlace").value;
			var date = document.getElementById("nDate").value;
			var time = document.getElementById("nTime").value;
			var notes = document.getElementById("notesTxt").innerHTML;
			
			//var url = 'saveChartMultiple.php?name=' + name + '&place=' + place + '&date=' + date + '&time=' + time + '&phone=' +'<?php echo $phone; ?>' + '&charttype=b' + '&savemode=' +'<?php echo $saveMode;?>' + '&rowid=' +'<?php echo $g_rowid;?>' + '&notes=' + notes;
			var url = 'saveChartMultiple.php?name=' + name + '&place=' + place + '&date=' + date + '&time=' + time + '&phone=' +'<?php echo $phone; ?>' + '&charttype=b' + '&savemode=' +'<?php echo $saveMode;?>' + '&rowid=' +'<?php echo $g_rowid;?>' + '&sex=' + sex;
			document.getElementById('saveForm').action = url;
			document.getElementById('hNotesTxt').value = notes;
		
			//alert(url);
			//window.open(url,'_self');
			document.getElementById("saveForm").submit();
		}
		
		function addDateTime()
		{
			if(timestamp_flag==0){
				//document.getElementById("notesTxt").value = document.getElementById("notesTxt").value + '\n' + Date(Date.now())+'\n';
				var notes = document.getElementById('notesTxt');
				notes.innerHTML = notes.innerHTML + "<BR>" + "<mark>" + Date(Date.now()) + "</mark><BR><BR>";
				notes.innerHTML.replace(/\\r\\n/g, "<br />");
				timestamp_flag = 1;
				
				notes.focus()
				window.getSelection().selectAllChildren(notes)
				window.getSelection().collapseToEnd()
			}
		}

		function displayAll()
		{
			var x = document.getElementById("DasaMatable");
			var y = document.getElementById("BukthiMatable");
			var z1 = document.getElementById("starlordHeadMutable");
			var z2 = document.getElementById("starlordBodyMutable");
			var btns = document.getElementById("buttons");
			
			btns.style.display='none';
			x.style.display='block';
			y.style.display='block';
			z1.style.display='table-cell';
			z2.style.display='table-cell';
		}
		
		function generatePDF() {
			// hide unncessary elements
			var ele = document.getElementById('column-menu');
			ele.style.display = 'none';
			
			displayAll();
			
			// Choose the element that our invoice is rendered in.
			const element = document.getElementById('chart');
			
			var opt = {
			  margin:       1,
			  filename:     'pkastro.pdf',
			  image:        { type: 'png', quality: 2.0 },
			  html2canvas:  { scale: 1 },
			  jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
			};
			
			// Choose the element and save the PDF for our user.
			html2pdf().from(element).set(opt).save();
			
			//ele.style.display = 'block';
		}
		
		function showKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "block";
		}

		function hideKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "none";
		}
		
		function showDetailsDiv(e,type) {
			var divName="";
			
			switch(type){
				case 1:
					divName = "thithiDetailsDiv";
					document.getElementById("nakDetailsDiv").style.display = "none";
					document.getElementById("karanaDetailsDiv").style.display = "none";
					document.getElementById("yogiDetailsDiv").style.display = "none";
					document.getElementById("ayogiDetailsDiv").style.display = "none";
					document.getElementById("mudakkuDetailsDiv").style.display = "none";
					break;
				case 2:
					divName = "karanaDetailsDiv";
					document.getElementById("nakDetailsDiv").style.display = "none";
					document.getElementById("thithiDetailsDiv").style.display = "none";
					document.getElementById("yogiDetailsDiv").style.display = "none";
					document.getElementById("ayogiDetailsDiv").style.display = "none";
					document.getElementById("mudakkuDetailsDiv").style.display = "none";
					break;
				case 3:
					divName = "yogiDetailsDiv";
					document.getElementById("nakDetailsDiv").style.display = "none";
					document.getElementById("thithiDetailsDiv").style.display = "none";
					document.getElementById("karanaDetailsDiv").style.display = "none";
					document.getElementById("ayogiDetailsDiv").style.display = "none";
					document.getElementById("mudakkuDetailsDiv").style.display = "none";
					break;
				case 4:
					divName = "ayogiDetailsDiv";
					document.getElementById("nakDetailsDiv").style.display = "none";
					document.getElementById("thithiDetailsDiv").style.display = "none";
					document.getElementById("karanaDetailsDiv").style.display = "none";
					document.getElementById("yogiDetailsDiv").style.display = "none";
					document.getElementById("mudakkuDetailsDiv").style.display = "none";
					break;
				case 5:
					divName = "nakDetailsDiv";
					document.getElementById("ayogiDetailsDiv").style.display = "none";
					document.getElementById("thithiDetailsDiv").style.display = "none";
					document.getElementById("karanaDetailsDiv").style.display = "none";
					document.getElementById("yogiDetailsDiv").style.display = "none";
					document.getElementById("mudakkuDetailsDiv").style.display = "none";
					break;
				case 6:
					divName = "mudakkuDetailsDiv";
					document.getElementById("nakDetailsDiv").style.display = "none";
					document.getElementById("ayogiDetailsDiv").style.display = "none";
					document.getElementById("thithiDetailsDiv").style.display = "none";
					document.getElementById("karanaDetailsDiv").style.display = "none";
					document.getElementById("yogiDetailsDiv").style.display = "none";
					break;
			}
			
			$(divName).delay(3000).fadeOut('slow');
			document.getElementById(divName).style.display = "inline";
			
			//var left  = e.clientX - 100 + "px";
			//var top  = e.clientY  + "px";
			//var left  = e.pageX - 50 + "px";
			var left = e.ScreenX/2 + "px";
			var top  = e.pageY  + "px";
		
			var div = document.getElementById(divName);

			div.style.left = left;
			div.style.top = top;
			//div.style.left = (e.screenX + window.scrolLeft);
			//div.style.top = (e.screenY + window.scrolTop);
		  
		  //document.getElementById("detailsDiv").style.top = 0;
		  //document.getElementById("detailsDiv").style.left = 0;
		  //document.getElementById("detailsDiv").style. = 0;
		}

		function hideDetailsDiv() {
			document.getElementById("nakDetailsDiv").style.display = "none";
			document.getElementById("thithiDetailsDiv").style.display = "none";
			document.getElementById("karanaDetailsDiv").style.display = "none";
			document.getElementById("yogiDetailsDiv").style.display = "none";
			document.getElementById("ayogiDetailsDiv").style.display = "none";
			document.getElementById("mudakkuDetailsDiv").style.display = "none";
		}
		
		document.onreadystatechange = function() {
		
		if(document.getElementById('chart') != null){	//chart not loaded
			document.querySelector("#loader").style.display = "none";
			//document.querySelector("body").style.visibility = "visible";
			//document.getElementById("msg").style.visibility = "hidden";
		}
		else{
			//document.querySelector("body").style.visibility = "hidden";
			document.querySelector("#loader").style.visibility = "visible";
			//document.getElementById("msg").style.visibility = "visible";
		}
	};
	
	function genChart(form) {
		document.getElementById('chart').style.opacity=0.2;
		document.getElementById("loader").style.display = "block";
		
		form.submit();
	}
	
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
	
	function openWallpaper(){
		var x = document.getElementById("wallpaper");
		
		x.style.display='block';
		window.scrollTo(0, 0);
	}
	
	function closeWallpaper(){
		var x = document.getElementById("wallpaper");
		
		x.style.display='none';
	}
	
	function closeNotes(){
		var x = document.getElementById("divNotes");
		
		x.style.display='none';
	}
	
	function downloadWallpaper(name) {
		/*var container = document.getElementById("image-wrap");*/ /*specific element on page*/
		var container = document.getElementById("wallpaper_container"); /* full page */
		html2canvas(container, { allowTaint: true }).then(function (canvas) {
			
			var link = document.createElement("a");
			document.body.appendChild(link);
			link.download = name+"_birth_wallpaper.jpg";
			link.href = canvas.toDataURL();
			link.target = '_blank';
			link.click();
		});
	}
	
	function updateChartPicture(img1,img2,img3,img4) {
		document.getElementById("1").src = img1;
		document.getElementById("2").src = img2;
		document.getElementById("3").src = img3;
		document.getElementById("4").src = img4;
	}
	
	function showJamakolChart()
	{	
		document.getElementById("lang").value = 'Tamil';
		document.getElementById("in_degree").value = 1;
		
		document.getElementById("hiddenViewJamaChart").submit();
		
		$('#loader').show();
	}
	
	function showUpdateNotes()
	{
		const notes = document.getElementById('divNotes');
		rowid = '<?php ?>';
		notes.innerHTML = '<div class="x medium" style="z-index:100;text-align:right;color:blue;font-weight:bold"><A href="javascript:closeNotes()">&nbsp;CLOSE (X)&nbsp;</A></div>';
		notes.innerHTML = notes.innerHTML + '<object type="text/html" data="notesUpdate.php" width="100%" height="100%" style="overflow:hidden;"></object>';
		document.getElementById("divNotes").style.display = "block";
		window.scrollTo(0, document.body.scrollHeight);
	}
	
	function hide(){
		document.getElementById('anthramDiv').style.visibility='hidden';
	}
				
	</script>
</head>
<?php



// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time"];
$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date"];

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));

//$onePageURL="onepage_birthchart.php?place=".$in_place."&name=".$in_name."&sex=".$in_sex.'&rowid='.$g_rowid.'&nDate='.$chart_date.'&nTime='.$chart_time;
$onePageURL="onepage_form.php?place=".$in_place."&name=".$in_name."&sex=".$in_sex.'&rowid='.$g_rowid.'&nDate='.$chart_date.'&nTime='.$chart_time;

?>

<body onLoad="init();">
<div id="karanamDiv" onclick="hide ()"><img src='images/karanam.png' width='100%' /></div>
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

function getOtherPanchangamDetails()
{
	global $dayofweek,$otherPanchangDetails;
	
	$rahu_kaalam = array("மாலை 4:30-6:30","காலை 7:30-9:00","மதியம் 3:00-4:30","மதியம் 12:00-1:30","மதியம் 1:30-3:00","காலை 10:30-12:00","காலை 09:00-10:30");
	$kuligai_neram = array("மதியம் 3:00-4:30","மதியம் 1:30-3:00","மதியம்12:00-1:30","காலை 10:30-12:00","காலை 09:00-10:30","காலை 7:30-9:00","காலை 6:00-7:30");
	$yama_kantam = array("மதியம் 12:00-1:30","காலை 10:30-12:00","காலை 09:00-10:30","காலை 7:30-9:00","காலை 6:00-7:30","மதியம் 3:00-4:30","மதியம் 1:30-3:00");
	$vaara_soolai = array("மேற்கு","கிழக்கு","வடக்கு","வடக்கு","தெற்கு","மேற்கு","கிழக்கு");
	$parikaaram = array("வெல்லம் ","தயிர்","பால்","பால்","தைலம்","வெல்லம்","தயிர்");
	
	$otherPanchangDetails[0] = $rahu_kaalam[$dayofweek];
	$otherPanchangDetails[1] = $kuligai_neram[$dayofweek];
	$otherPanchangDetails[2] = $yama_kantam[$dayofweek];
	$otherPanchangDetails[3] = $vaara_soolai[$dayofweek];
	$otherPanchangDetails[4] = $parikaaram[$dayofweek];
	
	return $otherPanchangDetails;
}

function displayHoroDetails()
{
	global $panchang, $in_name, $chart_date, $chart_time, $g_dasaBalance, $place, $g_thithi_soonyam_matching_arr,$g_dasaCurrent, $transit_date, $lagna_house, $country,$yogi_arr, 
		$g_pancha_pakshi, $g_thithi, $g_paksham, $moon_house, $today_sunrise, $today_sunset, $otherPanchangDetails, $city,$g_nak, $g_thithi_idx, $g_yoga_idx, $in_place, $g_rowid, $in_sex,
		$g_lagnam, $g_rasi, $nak_pad_idx, $g_ucha_details, $g_neecha_details, $sun_house, $sun_degree, $g_tamil_date_arr, $g_vayadhotta_lagna, $g_age, $g_mudakku_detail  ;
	
	echo "<div class='divTable blueTable' style='height:40px;'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)'>";
				echo "<div class='divTableCell' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>விவரங்கள்</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
	//display panchang table 
	echo "<div class='divTable blueTable' style='height:40px;'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பெயர்</div>";
			echo "<div class='divPanchang' style='font-weight:bold'>".$in_name."(id=$g_rowid)</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பாலினம்</div>";
			echo "<div class='divPanchang' style='font-weight:bold'>".$in_sex."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			$g_age = round((strtotime($transit_date)-strtotime($chart_date))/(365*60*60*24),1);
			$vayadhotta_rasi = ceil($g_age) % 12;
			
			if($vayadhotta_rasi == 0){
				$vayadhotta_rasi = 12;
			}
			
			echo "<div class='divPanchangHeading'>பிறந்த தேதி</div>";
			echo "<div class='divPanchang' style='font-weight:bold'>".date('d-M-Y',strtotime($chart_date))."<BR>($g_age வயது)</div>";
		echo "</div>";
		
		$g_tamil_date_arr = getTamilMonthDay($sun_house,$sun_degree);
		$tamil_year = getTamilYear(date("Y",strtotime($chart_date)),$g_tamil_date_arr[0]);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தமிழ் தேதி</div>";
			echo "<div class='divPanchang' style='font-weight:bold'>".$tamil_year.", ".$g_tamil_date_arr[0]." ".$g_tamil_date_arr[1]."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த நேரம்</div>";
			echo "<div class='divPanchang'>".date('h:i:s a',strtotime($chart_time))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த இடம்</div>";
			echo "<div class='divPanchang'>".$place."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['vaaram'])[1]."</div>";
		echo "</div>";
		displayDetailsDiv('thi',$g_thithi_idx);
		echo "<div class='divTableRow'>";
		echo "<div class='divPanchangHeading'>திதி</div>";
		$g_thithi = explode('/',explode('[',$panchang['thithi'])[0])[1];
		
		if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
			$g_paksham = "";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi . $g_paksham . "<BR>". "</div>";
		}
		else{
			$g_paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi ." [". $g_paksham . "]" . "</div>";
		}
		echo "</div>";
		
		displayDetailsDiv('nak',ceil($nak_pad_idx)/4);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'>".explode('/',$panchang['nak'])[1]."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			echo "<div class='divPanchang'>".$panchang['yoga']."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			$karan = explode('/',$panchang['karan'])[1];
			//echo "<div class='divPanchang' OnClick='showKaranamDiv(event)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
		echo "</div>";
		displayDetailsDiv('kar',$karan);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நக்ஷத்ர நாம எழுத்து </div>";
			echo "<div class='divPanchang'>".getStarLetters($g_nak)[0][0]."<BR>".getStarLetters($g_nak)[1][0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>லக்னம்</div>";
			$g_lagnam = getRasi($lagna_house-1);
			echo "<div class='divPanchang'>".$g_lagnam."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			if($g_age >= 0){
				$g_vayadhotta_lagna = getRasi(targetHouse($lagna_house,$vayadhotta_rasi-1));
			}
			else{
				$g_vayadhotta_lagna = "சாத்தியம் இல்லை";
			}
			echo "<div class='divPanchangHeading'>வயதோட்ட லக்னம்</div>";
			echo "<div class='divPanchang'>".$g_vayadhotta_lagna."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			$g_rasi = getRasi($moon_house-1);
			echo "<div class='divPanchangHeading'>ராசி</div>";
			echo "<div class='divPanchang'>".$g_rasi."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>உச்சம்</div>";
			echo "<div class='divPanchang'>".$g_ucha_details."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நீசம்</div>";
			echo "<div class='divPanchang'>".$g_neecha_details."</div>";
		echo "</div>";
		$yogi_arr = getYogi($panchang['yoga']);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'>".$yogi_arr[1]."(".$yogi_arr[0].")</div>";
		echo "</div>";
		displayDetailsDiv('yogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பஞ்ச பக்ஷி</div>";
			$g_pancha_pakshi = getPanchaPakshi();
			echo "<div class='divPanchang'>".$g_pancha_pakshi."</div>";
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
			echo "<div class='divPanchangHeadingRed'>அவயோகி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 4)' style='color:blue;cursor:pointer'>".$yogi_arr[3]."(".$yogi_arr[2].")</div>";
		echo "</div>";
		displayDetailsDiv('ayogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>திதி சூன்யம்</div>";
			echo "<div class='divPanchang'>".$g_thithi_soonyam_matching_arr[0]."</div>";
		echo "</div>";
		    $mudakku_star = getMudakku(1);
			$mudakku_rasi = getMudakku(2);
			
			$mudakku_baavam = getBavam(getMudakku(2));
		displayDetailsDiv('mudakku',$mudakku_rasi.$mudakku_baavam);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>முடக்கு</div>";
			$g_mudakku_detail = $mudakku_star." (".$mudakku_rasi.")<BR>".$mudakku_baavam."ஆம் வீடு";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 6)' style='color:blue;cursor:pointer'>".$g_mudakku_detail."</div>";
		echo "</div>";
		/*echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>ராகு காலம்</div>";
			echo "<div class='divPanchang'>".$otherPanchangDetails[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>எம கண்டம்</div>";
			echo "<div class='divPanchang'>".$otherPanchangDetails[2]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>குளிகை நேரம்</div>";
			echo "<div class='divPanchang'>".$otherPanchangDetails[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வார சூலை</div>";
			echo "<div class='divPanchang'>".$otherPanchangDetails[3]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பரிகாரம்</div>";
			echo "<div class='divPanchang'>".$otherPanchangDetails[4]."</div>";
		echo "</div>";*/
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>உங்கள் சந்திராஷ்டம ராசி</div>";
			//echo "<div class='divPanchang'>".getRasi(targetHouse($moon_house,6)-1)."</div>";
			echo "<div class='divPanchang'>".getRasi(targetHouse($moon_house-1,8))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறப்பு தசா இருப்பு</div>";
			echo "<div class='divPanchang'>".$g_dasaBalance[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய தசா புக்தி</div>";
			echo "<div class='divPanchang'>".$g_dasaCurrent[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறப்பு ஜாமக்கோள்</div>";
echo <<<EOL
<form id="hiddenViewJamaChart" action="Jamakol_Chart.php" method="post" style="display:none;">
		  <input type="hidden" id="place" name="place" value="$in_place" required/>
		  <input type="hidden" id="question" name="question" value="$in_name Birth chart" required/>
		  <input type="hidden" id="analysis" name="analysis" value=" " required/>
		  <input type="hidden" id="in_date" name="in_date" value="$chart_date" required/>
		  <input type="hidden" id="in_time" name="in_time" value="$chart_time" required/>
		  <input type="hidden" id="lang" name="lang" required/>
		  <input type="hidden" id="in_degree" name="in_degree" required/>
		</form>
EOL;
			echo "<div class='divPanchang' OnClick='showJamakolChart();' style='color:blue;font-weight:bold;cursor:pointer'>Generate</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>உங்கள் பிறப்பு ஜாதக WALLPAPER</div>";
			echo "<div class='divPanchang' OnClick='openWallpaper()' style='color:blue;font-weight:bold;cursor:pointer'>Click here</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கணக்கீட்டு முறை</div>";
			echo "<div class='divPanchang'>திருக்கணிதம்<BR>(லஹரி அயனாம்சம்)</div>";
		echo "</div>";
	echo "</div></center>";
	
}



function prepareWallpaper()
{
	global $panchang,$yogi_arr, $g_pancha_pakshi, $g_thithi, $g_paksham, $in_name;
	
	$nak = explode(' ',explode('/',$panchang['nak'])[1])[0];
	
	$target_nak = getTargetNak($nak,6); //sambaththu thaarai
	
	$karanam = explode('/',$panchang['karan'])[1];
	$yogi = $yogi_arr[1];
	$yogi_nak = $yogi_arr[0];
	
	
	$nak_sym = "images/star_symbols/".$target_nak.".jpg";
	$nak_temple = "images/star_temples/".$target_nak.".jpg";
	$nak_tree = "images/star_trees/".$target_nak.".jpg";
	
	$karanam_animal = "images/karanam_animals/".$karanam.".jpg";
	$karanam_god = "images/karanam_gods/".$karanam.".jpg";
	$yogi_planet = "images/yogi_planets/".$yogi.".png"; 
	
	$pancha_pakshi = "images/pancha_pakshi/".$g_pancha_pakshi.".jpg";
	
	if($g_paksham==""){
		$thithi_god = "images/thithi_god/".$g_thithi . ".jpg";
	}
	elseif(strpos("தேய்",$g_paksham)===0){
		$thithi_god = "images/thithi_god/".$g_thithi."_தேய்.jpg";
	}
	else{
		$thithi_god = "images/thithi_god/".$g_thithi."_வளர்.jpg";
	}
	
	if($g_paksham==""){
		$thithi_devi = "images/thithi_devi/".$g_thithi.".jpg";
	}
	elseif(strpos("தேய்",$g_paksham)===0){
		$thithi_devi = "images/thithi_devi/".$g_thithi."_தேய்.jpg";
	}
	else{
		$thithi_devi = "images/thithi_devi/".$g_thithi."_வளர்.jpg";
	}
	
	
	if(isMobileDevice())
		$requestor = "mobile";
	else
		$requestor = "desktop";
	
echo <<<EOL
	
  
	<div class="wallpaper" id='wallpaper' style="display:none;position:fixed;margin:0px;top:0px;left:0px;background-color:white;z-index:99;width:100%;height:98%">
		<div class="x medium" style="z-index:100;text-align:right;color:blue;font-weight:bold">
			<!-- <A href="javascript:downloadWallpaper('$in_name')">&nbsp;Download&nbsp;</a> -->
			<A href="javascript:closeWallpaper()">&nbsp;CLOSE (X)&nbsp;</A>
		</div>
		<div class="wallpaper_container" id="wallpaper_container">
			<div class="wallpaper_item b">$target_nak குறியீடு<img class="wallpaper_img" src="$nak_sym" /></div>
			<div class="wallpaper_item b">$target_nak கோவில்<img class="wallpaper_img" src="$nak_temple" /></div>
			<div class="wallpaper_item b">$target_nak மரம்<img class="wallpaper_img" src="$nak_tree" /></div>
			<div class="wallpaper_item b">$karanam கரண தெய்வம் <img class="wallpaper_img" src="$karanam_god" /></div>
			<div class="wallpaper_item b">$karanam கரண விலங்கு <img class="wallpaper_img" src="$karanam_animal" /></div>
			<div class="wallpaper_item b">யோகி $yogi ($yogi_nak)<img class="wallpaper_img" src="$yogi_planet" /></div>
			<div class="wallpaper_item b">பஞ்ச பக்ஷி ($g_pancha_pakshi)<img class="wallpaper_img" src="$pancha_pakshi" /></div>
			<div class="wallpaper_item b">$g_thithi தேவதை<img class="wallpaper_img" src="$thithi_god" /></div>
			<div class="wallpaper_item b">$g_thithi நித்யா தேவி<img class="wallpaper_img" src="$thithi_devi" /></div>
		  </div>
	</div>
EOL;

	echo '<script type="text/javascript">updateChartPicture("'.$karanam_animal.'","'.$karanam_god.'","'.$yogi_planet.'","'.$pancha_pakshi.'");</script>';
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
					<button type="button" class='$class_name' id="starlord" name="starlord" onclick="$starlord_function">கோ நட் சாரம்</button>
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

function displayNavamsam()
{
	global $g_navamsam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="d9_chart" style='display:none'>
		
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
					<div class="divTableCell">$g_navamsam_arr[11]</div>
					<div class="divTableCell">$g_navamsam_arr[0]</div>
					<div class="divTableCell">$g_navamsam_arr[1]</div>
					<div class="divTableCell">$g_navamsam_arr[2]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$g_navamsam_arr[10]</div>
					<div class="divTableCenterCell" style='font-size:10px'>நவாம்சம்</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$g_navamsam_arr[3]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$g_navamsam_arr[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'>D9</div>
					<div class="divTableCell">$g_navamsam_arr[4]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$g_navamsam_arr[8]</div>
					<div class="divTableCell">$g_navamsam_arr[7]</div>
					<div class="divTableCell">$g_navamsam_arr[6]</div>
					<div class="divTableCell">$g_navamsam_arr[5]</div>
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

function prepareBukthiTable(){
	global $dasa_lord, $g_dasaCurrent, $g_bukthi_arr;
	$bukthi_arr = array();
	
	$dasa_lord = explode('-',$g_dasaCurrent[0])[0];
	$dasa_lord = $dasa_lord . "/";

	foreach($g_bukthi_arr as $bukthi => $data){
	if(mb_strpos($bukthi,$dasa_lord)===0){
			$bukthi_arr[$bukthi] = $data;
		}
	}
	
	return $bukthi_arr;
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

if(date('I',strtotime($chart_date))==1) { // DST
   debug('<br>Picking daylight savings '.$gmt_offset_dst);
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
		$sun_degree = getDegree(round(($tot_degree[$key] - $sw_house[$key]*30.0),2));
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
		
	//if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$tr_sw_planet[$key]."|".$tr_sw_planet[$key]."|".$tr_sw_degree[$key]."|".$tr_sw_dir[$key]."|"."<br>";}
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

calculatePanchang();
$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);

echo <<<EOL
	<div class="title"></div>
		<div class='column-menu' id='column-menu'>
			<div class='desktop'>
EOL;
			displayUserInfo($_SESSION['userinfo']);
echo <<<EOL
				<button class='menu' onClick='window.open("index.php","_self")'>Home</button>
				<button class='menu' onClick='window.open("Birth_input.php","_self")'>New Birth Chart</button>
				<button class='menu' onClick='window.open("birthChartDisplay.php","_self")'>Birth Chart history</button>
			</div>
			<div class='mobile'>
EOL;
			//displayUserInfoMobile($_SESSION['userinfo']);
			include "birthChartMenu_mobile.php";
			
			if(strcmp($in_sex,"ஆண்")==0){
				$m_selection="selected";
				$f_selection="";
				$o_selection="";
			}
			elseif(strcmp($in_sex,"பெண்")==0){
				$f_selection="selected";
				$m_selection="";
				$o_selection="";
			}
			elseif(strcmp($in_sex,"மூன்றாம்")==0){
				$f_selection="";
				$m_selection="";
				$o_selection="selected";
			}
			else{
				$m_selection="selected";
				$o_selection="";
				$f_selection="";
			}
			
echo <<<EOL
			</div>
		</div>
		<div class='column-body'>
					<center><P style="color:#660000;font-weight:bold; font-size:16px; text-align:center; background-color:white;width:200px;line-height: 30px;">பிறப்பு ஜாதகம்</P></center>
					
					<center id="pdf"><div class="divTable blueTable" style="height:70px;">
						<!-- <IMG SRC='images/ganpati2.jpg' width='10%' height='10%' />
						<center><P style="color:#660000;font-weight:bold; font-size:10px; text-align:center; background-color:white;width:200px;line-height: 20px;">பெரியநாயகி துணை</P></center> -->
								<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
									<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">		
										<form name='updateChart' action='Birth_Chart.php' method='post'>
											<input type='hidden' id='nPlace' name='nPlace' value='$in_place'>
											<input type='hidden' id='nName' name='nName' value='$in_name'>
											<input type='hidden' id='nSex' name='nSex' value='$in_sex'>
											<input type='hidden' id='nNotes' name='hNotes' value='$g_notes'>
											<input type='hidden' id='nRowid' name='nRowid' value='$g_rowid'>
											
											<div class='divTable blueTable' style='height:30px;'>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*பெயர்</div>
													<div class='divBirthInput' style='font-weight:bold'><input type="text" name="name" id="name" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="Enter name..." onfocus=this.value='$in_name' onBlur="nameOnBlur();" value='$in_name' /></div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*பாலினம்</div>
													<div class='divBirthInput'>
														<select name="sex" id="sex" class='divBirthInput' style="background-color:var(--chart-background);font-size:14px;font-weight:bold;width:100%;">
															<option value="ஆண்" $m_selection>&#128104;ஆண்</option>
															<option value="பெண்" $f_selection>&#128105;பெண்</option>
															<option value="மூன்றாம்" $o_selection>மூன்றாம்</option>
														</select>
													</div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*பி.தேதி</div>
													<div class='divBirthInput'>
														<input type='date' id='nDate' class='divBirthInput' name='nDate' style="background-color:var(--chart-background);font-size:14px;font-weight:bold;width:100%" value=$chart_date>
													</div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*பி.நேரம்</div>
													<div class='divBirthInput'>
														<input type='time' id='nTime' name='nTime' class='divBirthInput' style="background-color:var(--chart-background);font-size:14px;font-weight:bold;width:100%;" step='1' value=$chart_time>
													</div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*பி.இடம்</div>
													<div class='divBirthInput'>
														<div class="search-box"><input type="text" name="place" class='divBirthInput' id="place" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold" placeholder="Search place..." onfocus=this.value='' value='$in_place' onBlur="placeOnBlur();" /><div class="result"></div></div>
													</div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*கோ.தேதி</div>
													<div class='divBirthInput' style='font-weight:bold'>
														<input type='date' id='nTrDate' name='nTrDate' style="background-color:#FFFFFF;font-size:14px;font-weight:bold;width:100%;" value=$transit_date OnChange='validateTransitDate();'>
													</div>
												</div>
												<div class='divTableRow'>
													<div class='divBirthInputLable'>*கோ.நேரம்</div>
													<div class='divBirthInput' style='font-weight:bold'>
														<input type='time' id='nTrTime' name='nTrTime' style="background-color:#FFFFFF;font-size:14px;font-weight:bold;width:100%;" step='1' value=$transit_time>
													</div>
												</div>
											</div>
											
											<button type='button' class='actionBtn' id='submit1' value='Submit' onClick='return genChart(this.form);'>Submit</button>
											<input type='button' class='actionBtn' id='save' name='save' value='$saveBtnTxt' onClick='saveChart();'>
											<button class='actionBtn' value='Download' onClick='loadOnePage("$onePageURL");'>Download</button>
											</form>
											
											<!-- <button style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;" onclick="generatePDF()">Download</button> -->
											
										<!-- </form> -->
										
										
									</div>
								</div>
							</div>
				<div class="container" style="horizontal-align:center">
					
					<div class="divTable blueTable" id="chart">
						
						<div class="divTableBody">
							<div class="divTableRow">
									<div class="divTableOuterCellLeftTop">$tr_sw_planet[11]</div>
									<div class="divTableOuterCell" style='border-left:none'></div>
									<div class="divTableOuterCell">$tr_sw_planet[0]</div>
									<div class="divTableOuterCell">$tr_sw_planet[1]</div>
									<div class="divTableOuterCell"  style='border-right:none'></div>
									<div class="divTableOuterCellRightTop">$tr_sw_planet[2]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell" style='border-top:none'></div>
									<div class="divTableCell">$sw_planet[11]<span class='symbol'>&#9795;</span>$g_thithi_soonyam_arr[11]</div>
									<div class="divTableCell">$sw_planet[0]<span class='symbol'>&#9794;</span>$g_thithi_soonyam_arr[0]</div>
									<div class="divTableCell">$sw_planet[1]<span class='symbol'>&#9792;</span>$g_thithi_soonyam_arr[1]</div>
									<div class="divTableCell">$sw_planet[2]<span class='symbol'>&#9791;</span>$g_thithi_soonyam_arr[2]</div>
									<div class="divTableOuterCell"  style='border-top:none'></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[10]</div>
									<div class="divTableCell">$sw_planet[10]<span class='symbol'>&#9796;</span>$g_thithi_soonyam_arr[10]</div>
									<div class="divTableCenterCell1"><img class='centerImg' id='1' src='#'></div>
									<div class="divTableCenterCell2"><img class='centerImg' id='2' src='#'></div>
									<div class="divTableCell">$sw_planet[3]<span class='symbol'>&#9790;</span>$g_thithi_soonyam_arr[3]</div>
									<div class="divTableOuterCell">$tr_sw_planet[3]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[9]</div>
									<div class="divTableCell">$sw_planet[9]<span class='symbol'>&#9796;</span>$g_thithi_soonyam_arr[9]</div>
									<div class="divTableCenterCell3"><img class='centerImg' id='3' src='#'></div>
									<div class="divTableCenterCell4"><img class='centerImg' id='4' src='#'></div>
									<div class="divTableCell">$sw_planet[4]<span class='symbol'>&#9737;</span>$g_thithi_soonyam_arr[4]</div>
									<div class="divTableOuterCell">$tr_sw_planet[4]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell" style='border-bottom:none'></div>
									<div class="divTableCell">$sw_planet[8]<span class='symbol'>&#9795;</span>$g_thithi_soonyam_arr[8]</div>
									<div class="divTableCell">$sw_planet[7]<span class='symbol'>&#9794;</span>$g_thithi_soonyam_arr[7]</div>
									<div class="divTableCell">$sw_planet[6]<span class='symbol'>&#9792;</span>$g_thithi_soonyam_arr[6]</div>
									<div class="divTableCell">$sw_planet[5]<span class='symbol'>&#9791;</span>$g_thithi_soonyam_arr[5]</div>
									<div class="divTableOuterCell"  style='border-bottom:none'></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCellLeftBottom">$tr_sw_planet[8]</div>
									<div class="divTableOuterCell" style='border-left:none'></div>
									<div class="divTableOuterCell">$tr_sw_planet[7]</div>
									<div class="divTableOuterCell">$tr_sw_planet[6]</div>
									<div class="divTableOuterCell"  style='border-right:none'></div>
									<div class="divTableOuterCellRightBottom">$tr_sw_planet[5]</div>
							</div>
						</div>	
					</div>
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
						
EOL;
								/*if($autoAnalysisMenu==1){
									echo "<div class='footer'>Scroll down for Auto Analysis details &#9660;</div>";
								}*/
echo <<<EOL

						
					</div>
EOL;
					//NOTE: These functions should be called in these order because various global variables are filled which is dependent
					//displayPanchang();
					calculateBirthPanchang();
					//displayThithiSoonyam($panchang["thithi"]);
					//calculateBirthThithiSoonyam($panchang["thithi"]);
					calculateVimDasaBalance($moon_degree,$moon_house,$chart_time,$chart_time,$panchang['nak'],$nak_pad_idx, true);
					displayButtons(true); //this will calculate star lord as well 
					calculateCurrentDasaBukthi(true);
					displayTransitStartLordButton(); //starlord dasa buttons for transit planets
					
					echo "<div class='html2pdf__page-break'></div>";
					calculateNavamsam();
					displayNavamsam();
					getOtherPanchangamDetails();
					displayHoroDetails();


echo <<<EOL
			
						<div class='divTable blueTable' style='height:auto;' id='buttons'>
							<div class='divTableRow'>
								<div class="divTableCell" colspan='2'>
									<H6 style='font-weight:bold;color:black'>குறிப்புகள்</H6>
								</div>
							</div>
							<form id='saveForm' action='' method='post'>
								<input type="hidden" id="hNotesTxt" name="hNotesTxt" value="">
								<div class="editable" id="notesTxt" contenteditable="true" onClick='addDateTime();'>$g_notes</div>
								<input type='button' class='actionBtn' id='save' name='save' value='$saveBtnTxt' onClick='saveChart();'>
							</form> <!-- notes update form end tag -->
						</div>
						
						</center>
					</div>
					
					<BR><BR><BR>
					
					</div>
					
					<div id='dummy'></div>
					
EOL;

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'Birth|'.$place."|".$chart_date."|".$chart_time."|".$transit_date."|".$transit_time;
	logActivity($conn,$_COOKIE["phone"],$activity);
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
$_SESSION["thithi_soonyam_arr"]=$g_thithi_soonyam_arr;
$_SESSION["navamsam_arr"] = $g_navamsam_arr;
$_SESSION["starlord_arr"] = $planet_starlord;
$_SESSION["planetKaranaLord"] = $g_planetKaranaLord;
$_SESSION["dasa"] = $g_dasa_arr;
$_SESSION["birth_date"] = date('d/M/Y',strtotime($chart_date));
$_SESSION["birth_time"] = date('h:i:s a',strtotime($chart_time));
$_SESSION["name"] = $in_name;
$_SESSION["sex"] = $in_sex;
$_SESSION["panchang"] = $panchang;
$_SESSION["karan"] = $g_karan;
$_SESSION["yogi"] = $yogi_arr[1]."(".$yogi_arr[0].")";
$_SESSION["ayogi"] = $yogi_arr[3]."(".$yogi_arr[2].")";
$_SESSION["bukthi"] = $g_bukthi_arr;
$_SESSION["curr_dasa"] = $current_dasa_bukthi['dasa'];
$_SESSION["bukthi_table"] = prepareBukthiTable();
$_SESSION["lagnam"] = $g_lagnam;
$_SESSION["rasi"] = $g_rasi;
$_SESSION["ppakshi"] = $g_pancha_pakshi;
$_SESSION["thithi_soonyam"] = $g_thithi_soonyam_matching_arr[0];
$_SESSION["dasa_balance"] = $g_dasaBalance[0];
$_SESSION["current_dasa"] = $g_dasaCurrent[0];
$_SESSION["sunrise"] = explode(' ',$today_sunrise)[1];
$_SESSION["sunset"] = explode(' ',$today_sunset)[1];
$_SESSION["in_place"] = $place;


prepareWallpaper();
?>

</body>
</html>