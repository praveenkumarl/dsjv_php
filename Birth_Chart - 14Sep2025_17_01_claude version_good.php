<!DOCTYPE html>
<?php
include_once "lib.php";

$num = isset($_POST["num"]) ? $_POST["num"] : 0;

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$in_place = isset($_POST["place"]) ? $_POST["place"] : $_POST["nPlace"];
$in_name = isset($_POST["name"]) ? $_POST["name"] : $_POST["nName"];
$in_sex = isset($_POST["sex"]) ? $_POST["sex"] : $_POST["nSex"];

$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time"];
$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date"];

$default_save = isset($_POST["birthsave"]) ? $_POST["birthsave"] : 0; //should we save it by default?

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
$planetDeg_default_transit = array();
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
$saveMode=0;
$saveBtnTxt='Save';
$g_planetKaranaLord=array();
$g_planetThithiLord=array();
$g_planetVainasigamNak=array();
$g_planetAbhijitNak=array();
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
$full_sun_deg=0;
$g_bhava_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$g_asthangam_arr = array();
$g_astavargam_total_arr = array_fill(0,12,0);
$g_padham=0;

$g_all_parigaaram = "";

$divisional_chartname_arr = [
								0 => ["dummy",""],
								1 => ["ராசி","உடல், உடல் சார்ந்த விஷயங்கள் மற்றும் அனைத்து பொது விஷயங்கள்"],
								2 => ["ஹோரா","செல்வம், குடும்பம்"],
								3 => ["திரேக்காணம்","உடன்பிறப்புகள், இயற்கை"],
								4 => ["சதுர்த்தாம்சம்","அதிர்ஷ்டம் மற்றும் சொத்து"],
								5 => ["பஞ்சாம்சம்","அதிகாரம், புகழ், ஆன்மிகம்"],
								6 => ["ஷஷ்டாம்சம்","உடல் உபாதை, வழக்கு, எதிரி"],
								7 => ["சப்சாம்சம்","குழந்தைகள்/சந்ததியினர்"],
								8 => ["அஷ்டாம்சம்","ஆயுள், திடீர் இழப்பு,<BR>எதிர்பாராத பிரச்சனைகள்"],
								9 => ["நவாம்சம்","மனைவி, தர்மம் மற்றும் உறவுகள்"],
								10 => ["தசாம்சம்","சமூகத்தில் செயல்கள், தொழில்"],
								11 => ["ருத்ராம்சம்","இறப்பு,  அழிவு"],
								12 => ["துவாதசாம்சம்","பெற்றோர்<BR>(தந்தைவழி மற்றும் தாய்வழி மரபுகள்)"],
								16 => ["ஷோடசாம்சம்","வாகனங்கள், பயணம் மற்றும் வசதிகள்"],
								20 => ["விம்சாம்சம்","ஆன்மீக நோக்கங்கள்"],
								24 => ["சித்தாம்சம்","கல்வி, கற்றல் மற்றும் அறிவு<BR>(கல்வி சாதனைகள்)"],
								27 => ["சப்தவிம்சாம்சம்","பலம் மற்றும் பலவீனம்"],
								30 => ["த்ரிம்சாம்சம்","தீமைகள்"],
								40 => ["காவேடாம்சம்","ஜாதகத்தில் சுப மற்றும் அசுப பலன்களுக்கு"],
								45 => ["அக்ஷவேதாம்சம்","அனைத்து பொதுவான அறிகுறிகளுக்கும்<BR>(பூர்வீக குணம் மற்றும் நடத்தை)"],
								60 => ["ஷஷ்டியாம்சம்","அனைத்து பொதுவான அறிகுறிகளுக்கும்<BR>(பூர்வீகத்தின் கடந்த பிறப்பு அல்லது கர்மா)"]
							];

if((isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'],'Birth_input.php')==false) or $default_save){
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="script.js"></script>
	<script src="panchang.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="my_panchang.js?<?php echo date('ymdhis'); ?>"></script>
    <script type="text/javascript">
	var timezone = '<?php echo $TZ; ?>';
	
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
				//var inputVal = $(this).val();
				var inputVal = $(this).val().trim();
				document.getElementById('place').value=inputVal;
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
			
			let head = $('#birthHead').clone();
			let detail = $('#birthDetail').clone();
				$('#birth_info').append(head);
				$('#birth_info').append(detail);
				$('#birthdetails').hide();
				
			const datetime = new Date('<?php echo $chart_date." ".$chart_time; ?>');
			updatePanchangData(datetime);
			
			displayAll();
			
			// Show the D2 chart by default
			const d2Chart = document.getElementById('D2');
			if (d2Chart) {
				d2Chart.style.display = 'block';
			}
			
			// Show the lagna chart by default
			const sunChart = document.getElementById('a1');
			if (sunChart) {
				sunChart.style.display = 'block';
			}
			
			 const btn   = document.getElementById('showNadi');
			  const modal = document.getElementById('astroModal');
			  const close = document.getElementById('closeNadi');

			  if (!btn || !modal || !close) return;   // safety guard

			  btn.onclick   = () => modal.style.display = 'block';
			  close.onclick = () => modal.style.display = 'none';

			  window.onclick = e => {
				if (e.target === modal) modal.style.display = 'none';
			  };
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
			var z6 = document.getElementById("kpHeadMutable");
			var z7 = document.getElementById("kpBodyMutable");
			
			if (z1.style.display === "none" && z2.style.display === "none") {
				z1.style.display = "table-cell";
				z2.style.display = "table-cell";
				x.style.display = "none";
				y.style.display = "none";
				z3.style.display = "none";
				z4.style.display = "none";
				z5.style.display = "none";
				z6.style.display = "table-cell";
				z7.style.display = "table-cell";
			  } else {
				z1.style.display = "none";
				z2.style.display = "none";
				z6.style.display = "none";
				z7.style.display = "none";
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
			//var place = document.getElementById("nPlace").value;
			var place = document.getElementById("place").value;
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
			
			document.getElementById("starlordHeadMutable").style.display='table';
			document.getElementById("starlordBodyMutable").style.display='table';
			document.getElementById("DasaMatable").style.display='table';
			document.getElementById("BukthiMatable").style.display='table';
			document.getElementById("starlordHeadMutable_transit").style.display='table';
			document.getElementById("starlordBodyMutable_transit").style.display='table';
			document.getElementById("thithiYogamKaranamHeadMutable").style.display='table';
			document.getElementById("thithiYogamKaranamBodyMutable").style.display='table';
			document.getElementById("abhijitHeadMutable").style.display='table';
			document.getElementById("abhijitBodyMutable").style.display='table';
			document.getElementById("kpHeadMutable").style.display='table';
			document.getElementById("kpBodyMutable").style.display='table';
			
			var btns = document.getElementById("buttons");
			
			if(btns)
				btns.style.display='table';
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
				case 31:
					divName = "kuberaDetailsDiv";
					break;
				case 4:
					divName = "ayogiDetailsDiv";
					break;
				case 5:
					divName = "nakDetailsDiv";
					break;
				case 6:
					divName = "mudakkuDetailsDiv";
					break;
				case 7:
					divName = "rasiDetailsDiv";
					break;
				case 8:
					divName = "destinyPointDetailsDiv";
					break;
				case 9:
					divName = "athrishtaPointDetailsDiv";
					break;
				case 10:
					divName = "dhurathrishtaPointDetailsDiv";
					break;
				case 11:
					divName = "sugaPointDetailsDiv";
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
		
		function showAllParigaaramDiv() {
			document.getElementById("allParigaaramDiv").style.display = "block";
			
			const anthDiv = document.getElementById('jumpDropdown');
			anthDiv.style.display = 'none';
			
			// Get a reference to your select dropdown
			var dropdown = document.getElementById("birthSectionNav"); // Use your dropdown's ID here

			// Change the selected option back to the first one (index 0)
			dropdown.selectedIndex = 0;
		}
		
		function hideAllParigaaramDiv() {
			document.getElementById("allParigaaramDiv").style.display = "none";
			
			const anthDiv = document.getElementById('jumpDropdown');
			anthDiv.style.display = 'flex';
		}
		
		function hideSookshmaDiv() {
			document.getElementById("sookshmaDiv").style.display = "none";
		}
		
		function hideAnthramDiv() {
			document.getElementById("anthramDiv").style.display = "none";
			
			const anthDiv = document.getElementById('jumpDropdown');
			anthDiv.style.display = 'flex';
		}
		
		function hidePranaDiv() {
			document.getElementById("pranaDiv").style.display = "none";
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
	
	// ---------------------- Anthram ----------------------
	function generateAnthram(in_start, in_end, dasa, bukthi) {
	  var anthram_arr = {};
	  var count = 0;

	  var start = new Date(in_start);
	  var dasa_period = {'கே':7,'சுக்':20,'சூரி':6,'சந்':10,'செ':7,'ரா':18,'குரு':16,'சனி':19,'புத':17};
	  var index = Object.keys(dasa_period).indexOf(bukthi);
	  var html_str = "";
	  var curr_date = new Date();

	  html_str += '<center><div class="divTable blueTable" style="width:100%;height:auto">';
	  html_str += '<button class="dasa-close-btn" onclick="hideAnthramDiv()">X</button><div class="divTableRow"><div class="divAnthraHeading" colspan="3">அந்தரம்</div></div></div>';
	  html_str += '<center><div class="divTable blueTable" style="width:100%;height:auto">';

	  // first pass (from bukthi index to end)
	  for (var i = index; i <= index + 8; i++) {
		if (i > 8) break;

		var no_of_secs = Math.round((dasa_period[dasa] * dasa_period[bukthi] * Object.values(dasa_period)[i]) / 14400 * 365.2425 * 24 * 60 * 60);
		var end = new Date(start.getTime() + no_of_secs * 1000);

		anthram_arr[Object.keys(dasa_period)[i]] = [start, end];

		let temp1 = new Date(start), temp2 = new Date(end);
		let s_date = String(temp1.getDate()).padStart(2,'0')+'-'+String(temp1.getMonth()+1).padStart(2,'0')+'-'+temp1.getFullYear();
		let e_date = String(temp2.getDate()).padStart(2,'0')+'-'+String(temp2.getMonth()+1).padStart(2,'0')+'-'+temp2.getFullYear();

		// 🔶 highlight current anthram (inclusive boundaries)
		var isCurrent = (curr_date.getTime() >= temp1.getTime() && curr_date.getTime() <= temp2.getTime());
		var cellClass = isCurrent ? 'divCurrAnthra' : 'divAnthra';

		// pass timestamps (ms) to avoid timezone issues with YYYY-MM-DD
		var startMs = start.getTime(), endMs = end.getTime();
		var anthramLord = Object.keys(dasa_period)[i];

		html_str += `<div class="divTableRow"
						onclick="generateSookshma(${startMs}, ${endMs}, '${dasa}', '${bukthi}', '${anthramLord}')">
					  <div class="divAnthraHeading">${dasa} / ${bukthi} / ${anthramLord}</div>
					  <div class="${cellClass}">${s_date}</div>
					  <div class="${cellClass}">${e_date}</div>
					</div>`;

		start = end;
		count++;
	  }

	  // second pass (wrap remaining planets)
	  for (var i = 0; i < 9 - count; i++) {
		var no_of_secs = Math.round((dasa_period[dasa] * dasa_period[bukthi] * Object.values(dasa_period)[i]) / 14400 * 365.2425 * 24 * 60 * 60);
		var end = new Date(start.getTime() + no_of_secs * 1000);

		if (i == 9 - count - 1) {
		  end = new Date(in_end);
		}

		anthram_arr[Object.keys(dasa_period)[i]] = [start, end];

		let temp1 = new Date(start), temp2 = new Date(end);
		let s_date = String(temp1.getDate()).padStart(2,'0')+'-'+String(temp1.getMonth()+1).padStart(2,'0')+'-'+temp1.getFullYear();
		let e_date = String(temp2.getDate()).padStart(2,'0')+'-'+String(temp2.getMonth()+1).padStart(2,'0')+'-'+temp2.getFullYear();

		var isCurrent = (curr_date.getTime() >= temp1.getTime() && curr_date.getTime() <= temp2.getTime());
		var cellClass = isCurrent ? 'divCurrAnthra' : 'divAnthra';

		var startMs = start.getTime(), endMs = end.getTime();
		var anthramLord = Object.keys(dasa_period)[i];

		html_str += `<div class="divTableRow"
						onclick="generateSookshma(${startMs}, ${endMs}, '${dasa}', '${bukthi}', '${anthramLord}')">
					  <div class="divAnthraHeading">${dasa} / ${bukthi} / ${anthramLord}</div>
					  <div class="${cellClass}">${s_date}</div>
					  <div class="${cellClass}">${e_date}</div>
					</div>`;

		start = end;
	  }

	  html_str += "</div></center>";
	  document.getElementById('anthramDiv').innerHTML = html_str;
	  document.getElementById('anthramDiv').style.display = 'block';
	  
	  const anthDiv = document.getElementById('jumpDropdown');
	  anthDiv.style.display = 'none';

	  return anthram_arr;
	}


	// ---------------------- Sookshma ----------------------
	function generateSookshma(in_start_ms, in_end_ms, dasa, bukthi, anthram) {
		var start = new Date(Number(in_start_ms));
		var anthramEnd = new Date(Number(in_end_ms));
		var curr_date = new Date();

		var dasa_period = {'கே':7,'சுக்':20,'சூரி':6,'சந்':10,'செ':7,'ரா':18,'குரு':16,'சனி':19,'புத':17};
		var keys = Object.keys(dasa_period);

		var html_str = "<button class='dasa-close-btn' onclick='hideSookshmaDiv()'>X</button>";
		html_str += '<center><div class="divTable greenTable" style="width:100%;height:auto">';
		html_str += '<div class="divTableRow"><div class="divSookshmaHeading" colspan="3">சூட்சமம்</div></div></div>';
		html_str += '<center><div class="divTable greenTable" style="width:100%;height:auto">';

		var totalSecs = Math.max(0, Math.round((anthramEnd.getTime() - start.getTime()) / 1000));
		var accStart = new Date(start);

		var idx0 = keys.indexOf(anthram);
		if (idx0 === -1) idx0 = 0;

		for (var step = 0; step < 9; step++) {
			var k = keys[(idx0 + step) % 9];
			var sliceSecs = Math.round(totalSecs * (dasa_period[k] / 120));
			var end = (step === 8) ? new Date(anthramEnd) : new Date(accStart.getTime() + sliceSecs * 1000);

			let t1 = new Date(accStart);
			let t2 = new Date(end);
			let s_date = String(t1.getDate()).padStart(2, '0') + '-' + String(t1.getMonth()+1).padStart(2, '0') + '-' + t1.getFullYear();
			let e_date = String(t2.getDate()).padStart(2, '0') + '-' + String(t2.getMonth()+1).padStart(2, '0') + '-' + t2.getFullYear();

			var isCurrent = (curr_date.getTime() >= t1.getTime() && curr_date.getTime() <= t2.getTime());
			var cellClass = isCurrent ? 'divCurrSookshma' : 'divSookshma';

			// make row clickable to open Prana
			html_str += `<div class="divTableRow" onclick="generatePrana(${t1.getTime()}, ${t2.getTime()}, '${dasa}', '${bukthi}', '${anthram}', '${k}')">`;
			html_str += `<div class="divSookshmaHeading">${dasa} / ${bukthi} / ${anthram} / ${k}</div>`;
			html_str += `<div class="${cellClass}">${s_date}</div>`;
			html_str += `<div class="${cellClass}">${e_date}</div>`;
			html_str += '</div>';

			accStart = end;
		}

		html_str += '</div></center>';

		document.getElementById('sookshmaDiv').innerHTML = html_str;
		document.getElementById('sookshmaDiv').style.display = 'block';
	}




	// ---------------------- Prana ----------------------
	function generatePrana(in_start_ms, in_end_ms, dasa, bukthi, anthram, sookshma) {
		// Accept numbers or numeric strings
		var start = new Date(Number(in_start_ms));
		var sookshmaEnd = new Date(Number(in_end_ms));
		var curr_date = new Date();

		var dasa_period = {'கே':7,'சுக்':20,'சூரி':6,'சந்':10,'செ':7,'ரா':18,'குரு':16,'சனி':19,'புத':17};
		var keys = Object.keys(dasa_period);

		// Build markup similar to SookshmaDiv structure
		var html_str = "<button class='dasa-close-btn' onclick='hidePranaDiv()'>X</button>";
		html_str += '<center><div class="divTable pranaTable" style="width:100%;height:auto">';
		html_str += '<div class="divTableRow"><div class="divPranaHeading" colspan="3">பிராணன்</div></div></div>';
		html_str += '<center><div class="divTable pranaTable" style="width:100%;height:auto">';

		// total seconds in sookshma (to slice proportionally)
		var totalSecs = Math.max(0, Math.round((sookshmaEnd.getTime() - start.getTime()) / 1000));
		var accStart = new Date(start);

		// start index is the sookshma lord, then wrap through the 9 planets
		var idx0 = keys.indexOf(sookshma);
		if (idx0 === -1) idx0 = 0; // fallback

		for (var step = 0; step < 9; step++) {
			var k = keys[(idx0 + step) % 9];

			// proportional slice
			var sliceSecs = Math.round(totalSecs * (dasa_period[k] / 120));

			// last slice end = sookshmaEnd
			var end = (step === 8) ? new Date(sookshmaEnd) : new Date(accStart.getTime() + sliceSecs * 1000);

			// format dates like others
			let t1 = new Date(accStart);
			let t2 = new Date(end);
			let s_date = String(t1.getDate()).padStart(2, '0') + '-' + String(t1.getMonth()+1).padStart(2, '0') + '-' + t1.getFullYear();
			let e_date = String(t2.getDate()).padStart(2, '0') + '-' + String(t2.getMonth()+1).padStart(2, '0') + '-' + t2.getFullYear();

			var isCurrent = (curr_date.getTime() >= t1.getTime() && curr_date.getTime() <= t2.getTime());
			var cellClass = isCurrent ? 'divCurrPrana' : 'divPrana';

			html_str += '<div class="divTableRow">';
			html_str += `<div class="divPranaHeading">${dasa} / ${bukthi} / ${anthram} / ${sookshma} / ${k}</div>`;
			html_str += `<div class="${cellClass}">${s_date}</div>`;
			html_str += `<div class="${cellClass}">${e_date}</div>`;
			html_str += '</div>';

			// move pointer
			accStart = end;
		}

		html_str += '</div></center>';

		document.getElementById('pranaDiv').innerHTML = html_str;
		document.getElementById('pranaDiv').style.display = 'block';

		// return if needed
		var prana_arr = {};
		return prana_arr;
	}



	
	function generateAnthramOrig(in_start, in_end, dasa, bukthi) {
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
       //for (var i = index; i <= index + 7; i++) {
		for (var i = index; i <= index + 8; i++) {
           if (i > 8) {
             break;
           }
		   
		   console.log('i='+i);
		   
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
      
       
		//for (var i = 0; i < 9 - count; i++) {
			for (var i = 0; i < 9 - count; i++) {
           //console.log(Object.keys(dasa_period)[i]);
           var no_of_days = Math.round((dasa_period[dasa] * dasa_period[bukthi] * Object.values(dasa_period)[i]) / 14400 * 365.2425 * 24 * 60 * 60);
           var end = new Date(new Date(start).getTime() + no_of_days * 1000);
		   
			if(i==9-count-1)
		   { 
				end = new Date(in_end);
			}
			
			console.log('i='+i);
		   
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
		
		// Get a reference to your select dropdown
		var dropdown = document.getElementById("birthSectionNav"); // Use your dropdown's ID here

		// Change the selected option back to the first one (index 0)
		dropdown.selectedIndex = 0;
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
	
	function toggleLanguage() {
		if(document.getElementById('lang-toggle').innerText=='TAM'){
			document.getElementById('lang-toggle').innerText = 'ENG';	
			transliterateTamilToEnglish();
		} else {
			document.getElementById('lang-toggle').innerText = 'TAM';
			transliterateEnglishToTamil();
		}
	}
	
	function transliterateEnglishToTamil() {
		
		let tamilToEnglish;

		// Load the JSON file (example using fetch)
		fetch('tm_en.json')
			.then(response => response.json())
			.then(mappingObject => {
				// Access the mapping details
				tamilToEnglish = mappingObject; // Assuming the entire object is the mapping

				// Now that the data is available, proceed with the rest of your logic
				const divs = document.querySelectorAll("div");

				divs.forEach((div) => {
					const tamilText = div.innerHTML.trim();
					 //const pattern = new RegExp(Object.values(tamilToEnglish).join("|"), "gi");

					// Check if the div contains an image
					const isImage = div.querySelector("img") !== null;

					if (!isImage) {
						// Translate non-image div content
						const pattern = /(?<=>)[^<]*(?=<)/g; // Matches text between ">" and "<"
						div.innerHTML = tamilText.replace(pattern, (match) => {
							for (const key in tamilToEnglish) {
								if (tamilToEnglish[key] === match) {
									return key;
								}
							}
							return match;
						});
					}
				});
			})
			.catch(error => {
				console.error('Error loading translation mapping:', error);
			});
	}

	
	function transliterateTamilToEnglish() {
		let tamilToEnglish;

		// Load the JSON file (example using fetch)
		fetch('tm_en.json')
			.then(response => response.json())
			.then(mappingObject => {
				// Access the mapping details
				tamilToEnglish = mappingObject; // Assuming the entire object is the mapping

				// Now that the data is available, proceed with the rest of your logic
				const divs = document.querySelectorAll("div");

				divs.forEach((div) => {
					const tamilText = div.innerHTML.trim();

					// Check if the div contains an image
					const isImage = div.querySelector("img") !== null;

					if (!isImage) {
						// Translate non-image div content
						const pattern = new RegExp(Object.keys(tamilToEnglish).join("|"), "gi");
						div.innerHTML = tamilText.replace(pattern, (match) => tamilToEnglish[match] || match);
					}
				});
			})
			.catch(error => {
				console.error('Error loading translation mapping:', error);
			});
	}
	
	function updatePanchangData(in_datetime){
		//const t = new Date('<?php echo $chart_date." ".$chart_time; ?>');
		const t = in_datetime;
		
		newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate(),t.getHours(),t.getMinutes()); 
		
		//newdate = changeTimezone(newdate,timezone);
		//alert('updatePanchangData: in_datetime='+in_datetime+' newdate='+newdate+' timezone='+timezone);
	
		panchang.calculate(newdate, function() {
			const nak = panchang.Nakshatra;

			// Save for button use
			currentNakshatraName = nak.name;
			currentNakshatraStart = nak.start;
			currentNakshatraEnd = nak.end;
			//document.writeln('<div class="divTableRow">');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:80px">'+formatDate(newdate)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:50px">'+panchang.Day.name+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Nakshatra.name+" "+formatDateTime(panchang.Nakshatra.end)+' வரை'+displayNextNak(newdate,panchang.Nakshatra)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Tithi.name+' '+formatDateTime(panchang.Tithi.end)+' வரை'+displayNextThithi(newdate,panchang.Tithi)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.start)+" முதல் "+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.start)+" முதல் "+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
			//document.writeln('</div>');

			//alert(formatDateTime(changeTimezone(panchang.Yoga.start,timezone)));
			
			document.getElementById('thithi').innerHTML = panchang.Tithi.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Tithi.start)+' முதல்<BR>'+formatDateTime(panchang.Tithi.end)+' வரை</div>';
			document.getElementById('nak').innerHTML = panchang.Nakshatra.name+document.getElementById('nak').innerHTML+'<BR><div class="divSmall">'+formatDateTime(panchang.Nakshatra.start)+' முதல்<BR>'+formatDateTime(panchang.Nakshatra.end)+' வரை</div>';
			document.getElementById('yogam').innerHTML = panchang.Yoga.name+'<BR><div class="divSmall">'+formatDateTime(panchang.Yoga.start)+' முதல்<BR>'+formatDateTime(panchang.Yoga.end)+' வரை</div>';
			document.getElementById('karanam').innerHTML = panchang.Karna.name+' ('+getKaranaPlanet(panchang.Karna.name)+')'+'<BR><div class="divSmall">'+formatDateTime(panchang.Karna.start)+' முதல்<BR>'+formatDateTime(panchang.Karna.end)+' வரை</div>';
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

	
	function showChart() {
		// Hide all charts first
		const charts = document.querySelectorAll('.varga-chart');
		charts.forEach(chart => chart.style.display = 'none');

		// Get selected value
		const selectedChart = document.getElementById('vargaDropdown').value;

		// Show the selected chart
		if (selectedChart) {
			document.getElementById(selectedChart).style.display = 'block';
		}
		
	}
	
	function showAshtaChart() {
		// Hide all charts first
		const charts = document.querySelectorAll('.ashtavarga-chart');
		charts.forEach(chart => chart.style.display = 'none');

		// Get selected value
		const selectedChart = document.getElementById('ashtaDropdown').value;

		// Show the selected chart
		if (selectedChart) {
			document.getElementById(selectedChart).style.display = 'block';
		}
		
	}
 
    // Execute the transliteration function once the page is loaded
    //window.addEventListener('load', transliterateTamilToEnglish);
	
	function openTab(tabId) {
	  var i, tabContent, tabButtons;
	  
	  // Hide all tab contents
	  tabContent = document.getElementsByClassName("tabContent");
	  for (i = 0; i < tabContent.length; i++) {
		tabContent[i].style.display = "none";
	  }
	  
	  // Remove 'active' class from all buttons
	  tabButtons = document.getElementsByClassName("tabButton");
	  for (i = 0; i < tabButtons.length; i++) {
		tabButtons[i].classList.remove("active");
	  }
	  
	  // Show selected tab and highlight the button
	  document.getElementById(tabId).style.display = "block";
	  
	  if(tabId=='chartTab'){
		  document.getElementById('detailsTab').style.display = "block";
	  }
	  
	  event.currentTarget.classList.add("active");
	}
	
	function showAllContent() {
		  // Get all elements with the class "tabContent" and make them visible
		  var tabContent = document.getElementsByClassName("tabContent");
		  for (var i = 0; i < tabContent.length; i++) {
			tabContent[i].style.display = "block";
		  }

		  // Hide the entire tabs container if it exists
		  var tabsContainer = document.getElementById("tabsContainer"); // Assuming your tab buttons are in a div with id="tabsContainer"
		  if (tabsContainer) { // Check if the element exists before trying to hide it
			tabsContainer.style.display = "none";
		  }

		  // Although the container is hidden, it's good practice to clear active classes
		  // from buttons in case you re-enable them later or have other scripts
		  var tabButtons = document.getElementsByClassName("tabButton");
		  for (var i = 0; i < tabButtons.length; i++) {
			tabButtons[i].classList.remove("active");
		  }
		}
	
	document.addEventListener('DOMContentLoaded', function() {
		showAllContent();
	  });
	  
	  
	  function scrollToSection(id) {
		// Check for the specific option that needs a function call
		if (id === "wallpaper") { // You'll use this ID in your <option> tag
			openWallpaper();
		} 
		if (id === "parigaaram") { // You'll use this ID in your <option> tag
			showAllParigaaramDiv()();
		} 
		else if (id === "top") {
			window.scrollTo({
				top: 0,
				behavior: "instant"
			});
		} else if (id) {
			const targetElement = document.getElementById(id);
			if (targetElement) {
				const headerHeight = 128 + 20; // The height of your menu bar
				const elementPosition = targetElement.getBoundingClientRect().top + window.pageYOffset;
				const offsetPosition = elementPosition - headerHeight;

				window.scrollTo({
					top: offsetPosition,
					behavior: "instant"
				});
			}
		}
		
		hideDetailsDiv();
	}
	
async function generateOptimizedPDF() {
    // Check if html2canvas is available.
    if (typeof html2canvas === 'undefined') {
        console.error("html2canvas library is not loaded.");
        alert("The PDF generation library is not loaded. Please try refreshing the page.");
        return;
    }

    // Show loading indicator
    document.getElementById("loader").style.display = "block";

    // Show all content tabs to ensure all elements are in the DOM and visible for the PDF generation.
    showAllContent();

    // Hide any UI elements that should not be in the PDF.
    document.getElementById('column-menu').style.display = 'none';
    document.getElementById('jumpDropdown').style.display = 'none';

    // Helper function to convert a DOM node to an image
    const domToImage = async (node, title) => {
        if (!node) return null;
        const canvas = await html2canvas(node, { scale: 2, useCORS: true, backgroundColor: '#fff' });
        const img = document.createElement('img');
        img.src = canvas.toDataURL('image/jpeg', 0.98);
        img.style.width = '80%';
        img.style.display = 'block';
        img.style.breakInside = 'avoid';

        const container = document.createElement('div');
        container.style.breakInside = 'avoid';
        container.style.marginBottom = '15px';
        const titleElement = document.createElement('h4');
        titleElement.innerText = `-- ${title} --`;
        titleElement.style.textAlign = 'center';
        titleElement.style.margin = '10px 0';
        titleElement.style.fontSize = '12px';
        container.appendChild(titleElement);
        container.appendChild(img);
        return container;
    };

    // Helper function to convert div table to simple HTML table
    const convertDivTableToSimpleTable = (divTableElement, title) => {
        if (!divTableElement) return null;

        const container = document.createElement('div');
        container.style.breakInside = 'avoid';
        container.style.marginBottom = '15px';

        const titleElement = document.createElement('h4');
        titleElement.innerText = `-- ${title} --`;
        titleElement.style.textAlign = 'center';
        titleElement.style.margin = '10px 0';
        titleElement.style.fontSize = '12px';
        container.appendChild(titleElement);

        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.fontSize = '8px';
        table.style.border = '1px solid #000';

        const allRows = divTableElement.querySelectorAll('.divTableRow, .divTableHeading, .divTableHead, .divPanchangHeading');

        allRows.forEach((row) => {
            const tr = table.insertRow();

            const isHeader = row.classList.contains('divTableHeading') ||
                             row.classList.contains('divTableHead') ||
                             row.style.backgroundColor === 'gold' ||
                             row.querySelector('.divPanchangHeading');

            const cells = row.querySelectorAll('div, td, th');
            let cellsProcessed = 0;

            cells.forEach(cell => {
                if (cell.querySelectorAll('div').length > 0 && !cell.textContent.trim()) return;
                if (!cell.textContent.trim()) return;
                if (cell.parentElement !== row && !cell.classList.contains('divTableCell') &&
                    !cell.classList.contains('divPanchang') && !cell.classList.contains('divPanchangHeading')) return;

                const cellElement = isHeader ? document.createElement('th') : document.createElement('td');
                cellElement.innerHTML = cell.innerHTML || cell.textContent;
                cellElement.style.border = '1px solid #000';
                cellElement.style.padding = '3px';
                cellElement.style.fontSize = '8px';

                if (isHeader) {
                    cellElement.style.backgroundColor = '#f0f0f0';
                    cellElement.style.fontWeight = 'bold';
                }

                tr.appendChild(cellElement);
                cellsProcessed++;
            });

            if (cellsProcessed === 0) {
                Array.from(row.children).forEach(child => {
                    if (child.textContent.trim()) {
                        const cellElement = isHeader ? document.createElement('th') : document.createElement('td');
                        cellElement.innerHTML = child.innerHTML || child.textContent;
                        cellElement.style.border = '1px solid #000';
                        cellElement.style.padding = '3px';
                        cellElement.style.fontSize = '8px';

                        if (isHeader) {
                            cellElement.style.backgroundColor = '#f0f0f0';
                            cellElement.style.fontWeight = 'bold';
                        }
                        tr.appendChild(cellElement);
                    }
                });
            }

            if (tr.cells.length === 0) {
                table.deleteRow(tr.rowIndex);
            }
        });

        container.appendChild(table);
        return container;
    };

    // Create main PDF container
    const pdfContainer = document.createElement('div');
    pdfContainer.style.width = '210mm';
    pdfContainer.style.boxSizing = 'border-box';
    pdfContainer.style.fontFamily = 'Arial, sans-serif';

    // ===== PAGE 1 =====
    const page1 = document.createElement('div');
    page1.style.width = '100%';
    page1.style.minHeight = '297mm';
    page1.style.padding = '10mm';
    page1.style.boxSizing = 'border-box';
    page1.style.display = 'flex';
    page1.style.gap = '15mm';
    page1.style.pageBreakAfter = 'always';

    // Page 1 Left Column - Birth Details
    const page1Left = document.createElement('div');
    page1Left.style.flex = '1';
    page1Left.style.minWidth = '0';

    // Birth details section
    const birthInfoNode = document.getElementById('birth_info');
    if (birthInfoNode) {
        const birthSection = convertDivTableToSimpleTable(birthInfoNode, 'பிறப்பு விவரங்கள்');
        if (birthSection) page1Left.appendChild(birthSection);
    }

    // Page 1 Right Column - Main Charts
    const page1Right = document.createElement('div');
    page1Right.style.flex = '1';
    page1Right.style.minWidth = '0';
    page1Right.style.display = 'flex';
    page1Right.style.flexDirection = 'column';
    page1Right.style.gap = '10px';

    // Main charts as images - stacked vertically on right side
    const mainChartImage = await domToImage(document.getElementById('chart'), 'ஜாதக சக்கரம் (D1)');
    if (mainChartImage) page1Right.appendChild(mainChartImage);

    const navamsamChartImage = await domToImage(document.getElementById('d9_chart'), 'நவாம்சம்');
    if (navamsamChartImage) page1Right.appendChild(navamsamChartImage);

    const bhavaChartImage = await domToImage(document.getElementById('bhava_chart'), 'பாவ சக்கரம்');
    if (bhavaChartImage) page1Right.appendChild(bhavaChartImage);

    page1.appendChild(page1Left);
    page1.appendChild(page1Right);
    pdfContainer.appendChild(page1);

    // ===== PAGE 2 =====
    const page2 = document.createElement('div');
    page2.style.width = '100%';
    page2.style.minHeight = '297mm';
    page2.style.padding = '10mm';
    page2.style.boxSizing = 'border-box';
    page2.style.display = 'flex';
    page2.style.gap = '15mm';
    page2.style.pageBreakAfter = 'always';

    // Page 2 Left Column
    const page2Left = document.createElement('div');
    page2Left.style.flex = '1';
    page2Left.style.minWidth = '0';
    page2Left.style.display = 'flex';
    page2Left.style.flexDirection = 'column';
    page2Left.style.gap = '8px';

    // Starlord (கிரக சாரம்)
    const starlordHead = document.getElementById('starlordHeadMutable');
    const starlordBody = document.getElementById('starlordBodyMutable');
    if (starlordHead && starlordBody) {
        const starlordContainer = document.createElement('div');
        starlordContainer.appendChild(starlordHead.cloneNode(true));
        starlordContainer.appendChild(starlordBody.cloneNode(true));
        const starlordTable = convertDivTableToSimpleTable(starlordContainer, 'கிரக சாரம்');
        if (starlordTable) page2Left.appendChild(starlordTable);
    }

    // Gocharam (கோட்சாரம்)
    const gocharamHead = document.getElementById('starlordHeadMutable_transit');
    const gocharamBody = document.getElementById('starlordBodyMutable_transit');
    if (gocharamHead && gocharamBody) {
        const gocharamContainer = document.createElement('div');
        gocharamContainer.appendChild(gocharamHead.cloneNode(true));
        gocharamContainer.appendChild(gocharamBody.cloneNode(true));
        const gocharamTable = convertDivTableToSimpleTable(gocharamContainer, 'கோட்சாரம்');
        if (gocharamTable) page2Left.appendChild(gocharamTable);
    }

    // Thithi Yogam (திதி/யோகம்)
    const thithiYogamKaranamHead = document.getElementById('thithiYogamKaranamHeadMutable');
    const thithiYogamKaranamBody = document.getElementById('thithiYogamKaranamBodyMutable');
    if (thithiYogamKaranamHead && thithiYogamKaranamBody) {
        const thithiContainer = document.createElement('div');
        thithiContainer.appendChild(thithiYogamKaranamHead.cloneNode(true));
        thithiContainer.appendChild(thithiYogamKaranamBody.cloneNode(true));
        const thithiTable = convertDivTableToSimpleTable(thithiContainer, 'திதி/யோகம்');
        if (thithiTable) page2Left.appendChild(thithiTable);
    }

    // Page 2 Right Column
    const page2Right = document.createElement('div');
    page2Right.style.flex = '1';
    page2Right.style.minWidth = '0';
    page2Right.style.display = 'flex';
    page2Right.style.flexDirection = 'column';
    page2Right.style.gap = '8px';

    // Abhijit (வைநாசிகம்)
    const abhijitHead = document.getElementById('abhijitHeadMutable');
    const abhijitBody = document.getElementById('abhijitBodyMutable');
    if (abhijitHead && abhijitBody) {
        const abhijitContainer = document.createElement('div');
        abhijitContainer.appendChild(abhijitHead.cloneNode(true));
        abhijitContainer.appendChild(abhijitBody.cloneNode(true));
        const abhijitTable = convertDivTableToSimpleTable(abhijitContainer, 'வைநாசிகம்');
        if (abhijitTable) page2Right.appendChild(abhijitTable);
    }

    // KP (KP விவரங்கள்)
    const kpHead = document.getElementById('kpHeadMutable');
    const kpBody = document.getElementById('kpBodyMutable');
    if (kpHead && kpBody) {
        const kpContainer = document.createElement('div');
        kpContainer.appendChild(kpHead.cloneNode(true));
        kpContainer.appendChild(kpBody.cloneNode(true));
        const kpTable = convertDivTableToSimpleTable(kpContainer, 'KP விவரங்கள்');
        if (kpTable) page2Right.appendChild(kpTable);
    }

    page2.appendChild(page2Left);
    page2.appendChild(page2Right);
    pdfContainer.appendChild(page2);

    // ===== REMAINING CONTENT (PAGE 3+) =====
    const remainingPages = document.createElement('div');
    remainingPages.style.width = '100%';
    remainingPages.style.padding = '10mm';
    remainingPages.style.boxSizing = 'border-box';
    remainingPages.style.columnCount = '2';
    remainingPages.style.columnGap = '15mm';
    remainingPages.style.columnFill = 'balance';

    // Dasa
    const dasaTable = document.getElementById('DasaMutable');
    if (dasaTable) {
        const dasaSimpleTable = convertDivTableToSimpleTable(dasaTable, 'தசா');
        if (dasaSimpleTable) {
            dasaSimpleTable.style.breakInside = 'avoid';
            remainingPages.appendChild(dasaSimpleTable);
        }
    }

    // Bukthi
    const bukthiTable = document.getElementById('BukthiMutable');
    if (bukthiTable) {
        const bukthiSimpleTable = convertDivTableToSimpleTable(bukthiTable, 'புக்தி');
        if (bukthiSimpleTable) {
            bukthiSimpleTable.style.breakInside = 'avoid';
            remainingPages.appendChild(bukthiSimpleTable);
        }
    }

    // Varga Charts
    const vargaCharts = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 16, 20, 24, 27, 30, 40, 45, 60];
    for (const d of vargaCharts) {
        const chartId = `D${d}`;
        const chartTitle = `வர்க சக்கரம் (D${d})`;
        const chartImage = await domToImage(document.getElementById(chartId), chartTitle);
        if (chartImage) {
            chartImage.style.breakInside = 'avoid';
            remainingPages.appendChild(chartImage);
        }
    }

    // Ashtavarga Charts
    const ashtaCharts = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    const ashtaTitleMap = {
        1: 'சூரி', 2: 'சந்', 3: 'செ', 4: 'புத', 5: 'குரு',
        6: 'சுக்', 7: 'சனி', 8: 'லக்', 9: 'சர்வாஷ்டக வர்கம்'
    };
    for (const d of ashtaCharts) {
        const chartId = `a${d}`;
        const chartTitle = ashtaTitleMap[d] ? `${ashtaTitleMap[d]} பின்னாஷ்டக வர்கம்` : `அஷ்டவர்கம் (A${d})`;
        const chartImage = await domToImage(document.getElementById(chartId), chartTitle);
        if (chartImage) {
            chartImage.style.breakInside = 'avoid';
            remainingPages.appendChild(chartImage);
        }
    }

    // Parigaaram section
    const parigaaramSection = document.getElementById('allParigaaramDiv');
    if (parigaaramSection) {
        const parigaaramContainer = document.createElement('div');
        parigaaramContainer.style.breakInside = 'avoid';
        parigaaramContainer.style.marginBottom = '15px';
        const titleElement = document.createElement('h4');
        titleElement.innerText = `-- பரிகாரங்கள் --`;
        titleElement.style.textAlign = 'center';
        titleElement.style.margin = '10px 0';
        titleElement.style.fontSize = '12px';
        parigaaramContainer.appendChild(titleElement);
        parigaaramContainer.appendChild(parigaaramSection.cloneNode(true));
        remainingPages.appendChild(parigaaramContainer);
    }

    pdfContainer.appendChild(remainingPages);

    // Add to DOM for PDF generation
    document.body.appendChild(pdfContainer);

    const opt = {
        margin: 5,
        filename: 'birth_chart.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true, backgroundColor: '#fff' },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }
    };

    try {
        await html2pdf().set(opt).from(pdfContainer).save();
    } catch (error) {
        console.error("PDF generation failed:", error);
    }

    // Clean up
    document.body.removeChild(pdfContainer);
    document.getElementById("loader").style.display = 'none';

    document.getElementById('column-menu').style.display = 'block';
    document.getElementById('jumpDropdown').style.display = 'flex';
}

	
				
	</script>
</head>
<?php

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));

//$onePageURL="onepage_birthchart.php?place=".$in_place."&name=".$in_name."&sex=".$in_sex.'&rowid='.$g_rowid.'&nDate='.$chart_date.'&nTime='.$chart_time;
$onePageURL="onepage_form.php?place=".$in_place."&name=".$in_name."&sex=".$in_sex.'&rowid='.$g_rowid.'&nDate='.$chart_date.'&nTime='.$chart_time;

?>

<!-- <div class="container">
    <button class='actionBtn' id="lang-toggle" onclick="toggleLanguage()">ENG</button>
</div> -->

<body onLoad="init();">
<div id="karanamDiv" onclick="hide ()"><img src='images/karanam.png' width='100%' /></div>
<div class="anthramDiv" id="anthramDiv"></div>
<div class="sookshmaDiv" id="sookshmaDiv"></div>
<div class="pranaDiv" id="pranaDiv"></div>
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
	global $transit_datetime,$transit_today_sunrise, $planetDeg_transit, $planetDeg_default_transit,$mandhi_degree_transit, $planetDeg_default;
	
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



function displayDegree_transit($in_sw_degree)
{
	global $out_planet_degree, $planetDeg_default_transit, $planetDeg_default, $planetDeg_transit;
	
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
			
			//$planetDeg_default_transit[$planet] = $val;
			$planetDeg_default_transit[$planet] = $planetDeg_default_transit[$key][0];
			unset($planetDeg_default_transit[$key]);
		}
	}
		
	$txt = $txt . " " . "மா-" . $planetDeg_transit['மா']; 
	
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
		$g_lagnam, $g_rasi, $nak_pad_idx, $g_ucha_details, $g_neecha_details, $sun_house, $sun_degree, $g_tamil_date_arr, $g_vayadhotta_lagna, $g_age, $g_mudakku_detail,
		$current_dasa_bukthi,$planet_starlord, $g_padham,$lat,$lon;
	
	echo "<div id='birthdetails'><div class='divTable blueTable' style='height:40px;' id='birthHead'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)'>";
				echo "<div class='divTableCell' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>விவரங்கள்</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
	//display panchang table 
	echo "<div class='divTable blueTable' style='height:40px;' id='birthDetail'>"; 
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
		
		//$g_tamil_date_arr = getTamilMonthDay($sun_house,$sun_degree);
		//$tamil_year = getTamilYear(date("Y",strtotime($chart_date)),$g_tamil_date_arr[0]);
		/*echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தமிழ் தேதி</div>";
			//echo "<div class='divPanchang' style='font-weight:bold'>".$tamil_year.", ".$g_tamil_date_arr[0]." ".$g_tamil_date_arr[1]."</div>";
			echo "<div class='divPanchang' style='font-weight:bold'>".getTamilDate()."</div>";
		echo "</div>";
		*/
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த நேரம்</div>";
			echo "<div class='divPanchang'>".date('h:i:s a',strtotime($chart_time))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறந்த இடம்</div>";
			echo "<div class='divPanchang' id='placename'>".$place."<BR>(".$lat."°, ".$lon."°)</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>வாரம்</div>";
			echo "<div class='divPanchang'>".explode('/',$panchang['vaaram'])[1]."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
		echo "<div class='divPanchangHeading'>திதி</div>";
		$g_thithi = explode('/',explode('[',$panchang['thithi'])[0])[1];
		
		if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
			$g_paksham = "";
			echo "<div class='divPanchang'  id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi . $g_paksham . "<BR>". "</div>";
		}
		else{
			$g_paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
			echo "<div class='divPanchang'  id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi ." [". $g_paksham . "]" . "</div>";
		}
		echo "</div>";

		displayDetailsDiv('thi',$g_thithi_idx);
		
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>நட்சத்திரம்</div>";
			//echo "<div class='divPanchang'  id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'>".explode('/',$panchang['nak'])[1]."</div>";
			//echo "<div class='divPanchang'  id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'>(".(ceil($g_nak_idx/4/12)).")</div>";
			//$g_padham = explode(' ',explode('/',$panchang['nak'])[1])[1];
			echo "<div class='divPanchang'  id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'> (".($g_padham).")</div>";
		echo "</div>";
		displayDetailsDiv('nak',ceil($nak_pad_idx)/4);
		
		$yogi_arr = getYogi($panchang['yoga']);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>யோகம்</div>";
			echo "<div class='divPanchang' id='yogam' OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'>".$panchang['yoga']."</div>";
		echo "</div>";
		displayDetailsDiv('yogi',$g_yoga_idx);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கரணம்</div>";
			$karan = explode('/',$panchang['karan'])[1];
			//echo "<div class='divPanchang' OnClick='showKaranamDiv(event)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
			echo "<div class='divPanchang' id='karanam' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
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
			//echo "<div class='divPanchang'>".$g_rasi."</div>";
			echo "<div class='divPanchang' id='raasi'   OnClick='showDetailsDiv(event, 7)' style='color:blue;cursor:pointer'>" . $g_rasi . "</div>";
		echo "</div>";
		displayDetailsDiv('rasi',"");
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
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'>".$yogi_arr[1]." (".$yogi_arr[0].")</div>";
		echo "</div>";
		displayDetailsDiv('yogi',$g_yoga_idx);
		
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பஞ்ச பக்ஷி</div>";
			$g_pancha_pakshi = getPanchaPakshi();
			echo "<div class='divPanchang'>".$g_pancha_pakshi."</div>";
		echo "</div>";
		
		[$guberaStarPaadham,$guberaRasi,$guberaRasiHouse, $guberaStarIdx] = getKubera();  //$guberaRasiHouse not used as of now. It will be used if we need to show some symbold in chart
		echo "<div class='divTableRow' style='background-color:yellow'>";
			echo "<div class='divPanchangHeading'>குபேர புள்ளி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 31)' style='color:blue;cursor:pointer'>" . $guberaStarPaadham . "<BR>". $guberaRasi . "</div>";
		echo "</div>";
		displayDetailsDiv('kubera',$guberaStarIdx);
		
		[$destiny_star, $star_idx] = calculateThalaivithiPulli($planet_starlord['சந்'][2],$planet_starlord['ரா'][2]);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தலைவிதி புள்ளி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 8)' style='color:blue;cursor:pointer'>" .$destiny_star . "</div>";
			//echo "<div class='divPanchang'>" . $destiny_star . "</div>";
		echo "</div>";
		displayDetailsDiv('destinyPoint',$star_idx);
		
		[$athrishta_star, $star_idx] = calculateAthrishtaPulli();
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>அதிர்ஷ்ட புள்ளி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 9)' style='color:blue;cursor:pointer'>" .$athrishta_star . "</div>";
			//echo "<div class='divPanchang'>" . $athrishta_star . "</div>";
		echo "</div>";
		displayDetailsDiv('AthrishtaPoint',$star_idx);
		
		$sunPada = ceil($planet_starlord['சூரி'][2] / 3.333333);
		$moonPada = ceil($planet_starlord['சந்'][2] / 3.333333); 
		$lagnaPada = ceil($planet_starlord['லக்'][2] / 3.333333);
		
		[$star_idx,$sugaPaadham] = getSugaPaadham($sunPada, $moonPada, $lagnaPada);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>சுக பாதம்</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 11)' style='color:blue;cursor:pointer'>" .$sugaPaadham . "</div>";
			//echo "<div class='divPanchang'>" . $athrishta_star . "</div>";
		echo "</div>";
		displayDetailsDiv('SugaPoint',$star_idx);
		
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
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 4)' style='color:blue;cursor:pointer'>".$yogi_arr[3]." (".$yogi_arr[2].")</div>";
		echo "</div>";
		displayDetailsDiv('ayogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>திதி சூன்யம்</div>";
			echo "<div class='divPanchang'>".$g_thithi_soonyam_matching_arr[0]."</div>";
		echo "</div>";
		    $mudakku_star = getMudakku(1);
			$mudakku_rasi = getMudakku(2);
			$mudakku_star_idx = getMudakku(3);
						
			$mudakku_baavam = getBavam($mudakku_rasi);
			
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>முடக்கு</div>";
			$g_mudakku_detail = $mudakku_star." (".$mudakku_rasi.")<BR>".$mudakku_baavam."ஆம் வீடு";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 6)' style='color:blue;cursor:pointer'>".$g_mudakku_detail."</div>";
		echo "</div>";
		displayDetailsDiv('mudakku',$mudakku_rasi.$mudakku_baavam);
		
		$g_asthangam_arr = getAsthangam();
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>அஸ்தங்கம்</div>";
			//echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 6)' style='color:blue;cursor:pointer'>".join(',',$g_asthangam_arr)."</div>";
			echo "<div class='divPanchang'>".join(',',$g_asthangam_arr)."</div>";
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
		
		[$bad_star, $remedy_star, $remedy_star_idx] = calculateDhurathrishtaPulli();
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>துரதிர்ஷ்ட புள்ளி</div>";
			echo "<div class='divPanchang' OnClick='showDetailsDiv(event, 10)' style='color:blue;cursor:pointer'>". $bad_star . "<BR>பரிகாரம் - " . $remedy_star . "</div>";
			//echo "<div class='divPanchang'>" . $bad_star . "<BR>பரிகாரம் - " . $remedy_star . "</div>";
		echo "</div>";
		displayDetailsDiv('DhurathrishtaPoint',$remedy_star_idx);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>பிறப்பு தசா இருப்பு</div>";
			echo "<div class='divPanchang'>".$g_dasaBalance[0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய தசா புக்தி</div>";
			echo "<div class='divPanchang'>".$g_dasaCurrent[0] . "</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய தசா பலன்</div>";
			echo "<div class='divPanchang'>". getDasaInfluencers($current_dasa_bukthi['dasa']) . "</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>தற்போதைய புக்தி பலன்</div>";
			echo "<div class='divPanchang'>". getDasaInfluencers($current_dasa_bukthi['bukthi']) . "</div>";
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
			echo "<div class='divPanchangHeading'>உங்கள் பரிகாரங்கள்</div>";
			echo "<div class='divPanchang' OnClick='showAllParigaaramDiv()' style='color:blue;font-weight:bold;cursor:pointer'>Click here</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>கணக்கீட்டு முறை</div>";
			echo "<div class='divPanchang'>திருக்கணிதம்<BR>(லஹரி அயனாம்சம்)</div>";
		echo "</div>";
	echo "</div></center>";
	echo "</div>";
	
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
	
  
	<div class="wallpaper" id='wallpaper' style="display:none;position:fixed;margin:0px;top:0px;left:0px;background-color:white;z-index:999;width:100%;height:98%">
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

	echo "<center><div class='divTable blueTable' style='height:auto;display:none' id='buttons'>"; 
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



function displayNavamsam()
{
	global $g_navamsam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="d9_chart" style='display:table'>
		
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

if($default_save){ //save it to DB and show the chart
	$url = 'saveChartMultiple.php?name=' . $in_name . '&place=' . $in_place . '&date=' . $chart_date . '&time=' . $chart_time . '&sex=' . $in_sex . '&phone=' . $phone . '&charttype=b&savemode=0&default_save=1';
	echo "<script>window.location='$url';</script>";
}

// Create a DateTime object for the chart date
$chart_date_obj = new DateTime($chart_date);

// Check if DST is active for the given date and timezone
if ($chart_date_obj->format('I') == 1) { // DST is active
    debug('<br>Picking daylight savings ' . $gmt_offset_dst);
    list($hour, $min) = explode(':', $gmt_offset_dst);
    $offset = (int)$hour + (int)$min / 60;
    $tz_offset = $gmt_offset_dst;
} else { // DST is not active
    list($hour, $min) = explode(':', $gmt_offset);
    $offset = (int)$hour + (int)$min / 60;
    $tz_offset = $gmt_offset;
}

// ------- Swiss ephemeris // ------- 

$date = date("d.m.Y",strtotime($chart_date));
$time = date("H:i:s",strtotime($chart_time));
$datetime = $date . " " . $time;

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

/*
Housing system options
A  equal\n\
B  Alcabitius\n\
C  Campanus\n\
D  equal / MC\n\
E  equal = A\n\
F  Carter poli-equatorial\n\
G  36 Gauquelin sectors\n\
H  horizon / azimuth\n\
I  Sunshine\n\
i  Sunshine alternative\n\
K  Koch\n\
L  Pullen S-delta\n\
M  Morinus\n\
N  Whole sign, Aries = 1st house\n\
O  Porphyry\n\
P  Placidus\n\
Q  Pullen S-ratio\n\
R  Regiomontanus\n\
S  Sripati\n\
T  Polich/Page (\"topocentric\")\n\
U  Krusinski-Pisa-Goelzer\n\
V  equal Vehlow\n\
W  equal, whole sign\n\
X  axial rotation system/ Meridian houses\n\
Y  APC houses\n\
*/

//$h_sys='P';
$h_sys='V';

/*-sid<option> ayanamsam
 number of ayanamsha method:\n\
0 for Fagan/Bradley\n\
1 for Lahiri\n\
2 for De Luce\n\
3 for Raman\n\
4 for Usha/Shashi\n\
5 for Krishnamurti\n\
6 for Djwhal Khul\n\
7 for Yukteshwar\n\
8 for J.N. Bhasin\n\
9 for Babylonian/Kugler 1\n\
10 for Babylonian/Kugler 2\n\
11 for Babylonian/Kugler 3\n\
12 for Babylonian/Huber\n\
13 for Babylonian/Eta Piscium\n\
14 for Babylonian/Aldebaran = 15 Tau\n\
15 for Hipparchos\n\
16 for Sassanian\n\
17 for Galact. Center = 0 Sag\n\
18 for J2000\n\
19 for J1900\n\
20 for B1950\n\
21 for Suryasiddhanta\n\
22 for Suryasiddhanta, mean Sun\n\
23 for Aryabhata\n\
24 for Aryabhata, mean Sun\n\
25 for SS Revati\n\
26 for SS Citra\n\
27 for True Citra\n\
28 for True Revati\n\
29 for True Pushya (PVRN Rao)\n\
30 for Galactic (Gil Brand)\n\
31 for Galactic Equator (IAU1958)\n\
32 for Galactic Equator\n\
33 for Galactic Equator mid-Mula\n\
34 for Skydram (Mardyks)\n\
35 for True Mula (Chandra Hari)\n\
36 Dhruva/Gal.Center/Mula (Wilhelm)\n\
37 Aryabhata 522\n\
38 Babylonian/Britton\n\
39 Vedic/Sheoran\n\
40 Cochrane (Gal.Center = 0 Cap)\n\
41 Galactic Equator (Fiorenza)\n\
42 Vettius Valens\n\
43 Lahiri 1940\n\
44 Lahiri VP285 (1980)\n\
45 Krishnamurti VP291\n\
46 Lahiri ICRC\n\

-p<planet bodies>
  single body numbers/letters:\n\
        0 Sun (character zero)\n\
        1 Moon (character 1)\n\
        2 Mercury\n\
        3 Venus\n\
        4 Mars\n\
        5 Jupiter\n\
        6 Saturn\n\
        7 Uranus\n\
        8 Neptune\n\
        9 Pluto\n\
        m mean lunar node\n\
        t true lunar node\n\
        n nutation\n\
        o obliquity of ecliptic\n\
	q delta t\n\
	y time equation\n\
	b ayanamsha\n\
        A mean lunar apogee (Lilith, Black Moon) \n\
        B osculating lunar apogee \n\
        c intp. lunar apogee \n\
        g intp. lunar perigee \n\
        C Earth (in heliocentric or barycentric calculation)\n\
*/

// Define file path to save output
$filePath = 'output.txt';

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

$sw_planet = updateUpaGraha($sun_degree);


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
	
	$planetDeg_default_transit[$row[0]][0] = (float)($row[2]);
	
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
//calculateBirthPanchang();
$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);

echo <<<EOL
	<div class="title"></div>
		<div class='column-menu' id='column-menu'>
			<div class='desktop'>
EOL;
			displayUserInfo($_SESSION['userinfo']);
echo <<<EOL
				<button type="button" class='menu' onClick='window.open("index.php","_self")'>Home</button>
				<button type="button" class='menu' onClick='window.open("Birth_input.php","_self")'>New Birth Chart</button>
				<button type="button" class='menu' onClick='window.open("birthChartDisplay.php","_self")'>Birth Chart history</button>
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
						<!-- 🔹 Fixed Navigation Bar -->
						<div class="nav-container" id='jumpDropdown'>
							<div class="select-container">
								<select class="birthSectionNav" id="birthSectionNav" onchange="scrollToSection(this.value)">
									<option value="top">Top</option>
									<option value="birthHead">பிறப்பு விவரங்கள்</option>
									<option value="d9_chart">நவாம்சம்</option>
									<option value="bhava_chart">பாவம்</option>
									<option value="starlordHeadMutable">கிரக சாரம்</option>
									<option value="thithiYogamKaranamHeadMutable">திதி/யோகம்</option>
									<option value="abhijitHeadMutable">வைநாசிகம்</option>
									<option value="kpHeadMutable">KP details</option>
									<option value="divCurrentBukthi">தசாபுக்தி</option>
									<option value="starlordHeadMutable_transit">கோட்சாரம்</option>
									<option value="vargaDropdown">வர்க சக்ரம்</option>
									<option value="ashtaDropdown">அஷ்டவர்கம்</option>
									<option value="wallpaper">Wallpaper</option>
									<option value="parigaaram">பரிகாரங்கள்</option>
									
								</select>
							</div>
						</div>
		</div>
		<div class='column-body'>
EOL;
			
echo "<div id='tabsContainer' style='text-align: -webkit-left;'>";
		echo "<button class='tabButton' onclick=\"openTab('detailsTab')\">Details</button>";
		echo "<button class='tabButton' onclick=\"openTab('chartTab')\">Chart</button>";
echo "</div>";
			
echo <<<EOL
			<center><P style="color:#660000;font-weight:bold; font-size:16px; text-align:center; background-color:white;width:200px;margin-top: 50px;">பிறப்பு ஜாதகம்</P></center>
			<div id="detailsTab" class="tabContent">
			<div class="tableWrapper">		
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
											<button type="button" class='actionBtn' value='Download' onClick='loadOnePage("$onePageURL");'>PDF</button>
											<button type="button" class="actionBtn" value="DownloadFull" onClick="generateOptimizedPDF();">Full PDF</button>

											
											<!-- <button type='button' id="showNadi" class="actionBtn">BN.NADI</button> -->
										</form>
											
										<!-- <button style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;" onclick="generatePDF()">Download</button> -->
										<!-- </form> -->

										
									</div>
								</div>
							</div>
					<br>
					</div>
					</div>
					
					<div id="chartTab" class="tabContent">
					<div class="tableWrapper">
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
								
										// --- Tab Buttons ---

					calculateStarLord();
					resortStarLordArray(true);  //this is to sort the planets in correct order
					calculateNavamsam();
					displayNavamsam();
					displayBhavaChart();
					echo '</div>'; //chartTab end
					echo '</div>'; //chartTab end
					calculateBirthPanchang();
					echo "<br><div id='birth_info' style='text-align: -webkit-center;'></div>";
					calculateVimDasaBalance($moon_degree,$moon_house,$chart_time,$chart_time,$panchang['nak'],$nak_pad_idx, true);
					displayButtons(true); //this will calculate star lord as well 
					displayStarLord_transit();
					calculateCurrentDasaBukthi(true);
					
					echo "<div class='html2pdf__page-break'></div>";
					getOtherPanchangamDetails();
					echo '<div id="horoDetailsTab" class="tabContent">';
					echo '<div class="tableWrapper">';
						displayHoroDetails();
					echo "</div>";
					echo "</div>";


echo <<<EOL
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

$backtrace = debug_backtrace();

if($num == 1){ //clicked numerology button in chart history	
	$chartdata=join('|',$sw_planet);
	// Create a DateTime object from the original date
	$dateObject = new DateTime($chart_date);

	// Format the date to DD-MM-YYYY
	$newDateFormat = $dateObject->format('d/M/Y');
	
	$url = "numerology.php?referrer=Birth_Chart.php&name=$in_name&dob=".$newDateFormat."&chartdata=".$chartdata;
	echo "<script>window.open(\"$url\",'_self')</script>";
}

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

$g_divisional_arr = array_fill(0, 12, '');

$charts = [
    2 => calculateD2Chart(),
    3 => calculateD3Chart(),
    4 => calculateD4Chart(),
    5 => calculateD5Chart(),
    6 => calculateD6Chart(),
    7 => calculateD7Chart(),
    8 => calculateD8Chart(),
    9 => $g_navamsam_arr, // D-9 (Navamsa)
    10 => calculateD10Chart(),
    11 => calculateD11Chart(),
    12 => calculateD12Chart(),
    16 => calculateD16Chart(),
    20 => calculateD20Chart(),
    24 => calculateD24Chart(),
    27 => calculateD27Chart(),
    30 => calculateD30Chart(),
    40 => calculateD40Chart(),
    45 => calculateD45Chart(),
    60 => calculateD60Chart()
];


?>

 <!-- Dropdown list -->
    <select id="vargaDropdown" onchange="showChart()">
		<option value="D2" selected>D2 - <?php echo $divisional_chartname_arr[2][0]; ?></option>
		<?php
		foreach ($charts as $key => $data) {
			if ($key != 2) { // Skip D2 since it's already displayed
				$dropdown_string = 'D' . $key . ' - ' . $divisional_chartname_arr[$key][0];
				echo "<option value='D$key'>$dropdown_string</option>";
			}
		}
		?>
	</select>

    <!-- Div containers for each chart -->
    <?php
    foreach ($charts as $key => $data) {
        echo "<div id='D$key' class='varga-chart'>";
        displayVarga($data, $key);
        echo "</div>";
    }
	?>

 <!-- Dropdown list -->
    <select id="ashtaDropdown" onchange="showAshtaChart()">
		<?php
		foreach (array("dummy","சூரி","சந்","செ","புத","குரு","சுக்","சனி","லக்","சர்வாஷ்டக வர்கம்") as $key => $data) {
			if($key==0) continue;
			$dropdown_string = $data . ' - ' . "பின்னாஷ்டக வர்கம்";
			
			if($key==1){
				echo "<option value='a$key' selected>சூரி - பின்னாஷ்டக வர்கம்</option>";
			}
			else{
				if($key==9) $dropdown_string = "சர்வாஷ்டக வர்கம்";
				echo "<option value='a$key'>$dropdown_string</option>";
			}
		}
		?>
	</select>

    <!-- Div containers for each chart -->
    <?php
    foreach (array("dummy","சூரி","சந்","செ","புத","குரு","சுக்","சனி","லக்","All") as $key => $data) {
		if($key==0) continue;
        echo "<div id='a$key' class='ashtavarga-chart'>";
		
		if($key==9){
			displayAshtavargam($key, $data, $g_astavargam_total_arr,3);  //total or sarva ashtakavargam
		}
		else{
			$ashtavarga_arr = calculateAshtavarga($data);
			//displayAshtavargam($key, $data, $ashtavarga_arr,2); //display planets
			displayAshtavargam($key, $data, $ashtavarga_arr,1); //display only parals
		}
		
        echo "</div>";
    }
	
echo<<<EOL
	<center><div class='divTable blueTable' style='height:auto;' id='notes'>
			<br>
		<div class='divTableRow'>
			<div class="divTableCell" colspan='2'>
				<H6 style='font-weight:bold;color:black'>குறிப்புகள்</H6>
			</div>
		</div>
		<form id='saveForm' action='' method='post' style='width:inherit'>
			<input type="hidden" id="hNotesTxt" name="hNotesTxt" value="">
			<div class="editable" id="notesTxt" contenteditable="true" onClick='addDateTime();'>$g_notes</div>
			<input type='button' class='actionBtn' id='save' name='save' value='$saveBtnTxt' onClick='saveChart();'>
		</form> <!-- notes update form end tag -->
	</div>
	</center>
EOL;

displayAllParigaaramDiv();
	
/*$clubs = buildTriadClubsWithRetro($planet_starlord, $planetDeg_default);

foreach ($clubs as $label => $planets) {
    if (!$planets) continue;                          // skip empty triads
    #echo $label . ' : ' .
	echo '<BR><1/5/9> : ' .
         implode('+', array_column($planets, 'tag')) .
         PHP_EOL;
}

$pairs = buildSecondRasiPairs($planet_starlord, $planetDeg_default);

foreach ($pairs as $p) {
    echo '<BR><2/12>' . $p . PHP_EOL;
}

$sameRasi = buildSameRasiCombinations($planet_starlord, $planetDeg_default);

foreach ($sameRasi as $rasi => $pairs) {
    echo '<BR>Rasi '.$rasi.' : '.implode(' , ', $pairs).PHP_EOL;
}

// focus on Mercury (புத)
$combos = buildPlanetCentricCombinations('கே',
                                         $planet_starlord,
                                         $planetDeg_default);

foreach ($combos as $label => $items) {
    echo strtoupper($label).': '.implode(' , ', $items)."<br>\n";
}*/




/* ============= MAIN BUILD ======================================== */
$planetInfo = buildNaadi($planet_starlord,$planetDeg_default,$alwaysRetro);

/* bucket‑wise storage requested in point 2 */
$bucketArrays = [
    'same'=>[], 'triad'=>[], '2nd'=>[],
    '12th'=>[], '7th'=>[], '3/11'=>[]
];

/* row‑oriented data for the HTML table */
$tableRows=[];

foreach($allPlanets as $pl){
    $combo = combosForFocus($pl, $planetInfo, $excludeTags, $fmt, $enemyMap);

    // fill bucket arrays (planet -> joined string)
    foreach($bucketArrays as $k=>&$arr){
        $arr[$pl]=isset($combo[$k])?implode(' , ',$combo[$k]):'';
    } unset($arr);

    // build the row for the table
    $tableRows[$pl]=[
        'same'  =>$bucketArrays['same'][$pl],
        'triad' =>$bucketArrays['triad'][$pl],
        '2nd'   =>$bucketArrays['2nd'][$pl],
        '12th'  =>$bucketArrays['12th'][$pl],
        '7th'   =>$bucketArrays['7th'][$pl],
        '3/11'  =>$bucketArrays['3/11'][$pl],
    ];
}

/* =========  OVERLAY  ============================================ */
echo <<<HTML
<div id="astroModal" class="astro-modal">
  <div class="astro-modal-content">
      <span id="closeNadi" class="astro-close">&times;</span>
HTML;

/* put your existing rendering loop here — either the div‑table
   or the <details> layout we built earlier: */
foreach ($tableRows as $planet => $cols) {
    echo "<div class='astro-section'><details><summary>$planet</summary>";
    foreach ([
        'same'  => 'ஒரே ராசி',
        'triad' => 'திரிகோணம் (1,5,9)',
        '2nd'   => '2ஆம் ராசி',
        '12th'  => '12ஆம் ராசி',
        '7th'   => '7ஆம் ராசி',
        '3/11'  => '3/11ஆம் ராசி'
    ] as $key => $label) {
        if (!empty($cols[$key])) {
            echo "<div class='bucket'><div class='bucket-label'>$label:</div>";
            foreach (explode(',', $cols[$key]) as $combo) {
                echo "<div class='combo-line'>".trim($combo)."</div>";
            }
            echo "</div>";
        }
    }
    echo "</details></div>";
}

echo <<<HTML
  </div>
</div>
HTML;
	
	?>

</body>
</html>