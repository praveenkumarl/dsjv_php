<?php 
require "lib.php";

$TZlist=array("Asia/Kolkata");
$conn="";

if(!isLogged()){
	header("Refresh:0; url=new_loginPage.php", true, 303);
}

$question="";
$place = isset($_COOKIE["place"]) ?  $_COOKIE["place"] : "";

if(isset($_COOKIE['degree'])){
	if($_COOKIE['degree']==1){
		$yes='checked';
		$no='';
	}
	else{
		$yes='';
		$no='checked';
	}
}
else{
	$yes='checked';
	$no='';
}
  
$_SESSION['debug']=isset($_GET['debug']) ? $_GET['debug'] : 0;
$chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";

$_SESSION["charttime"]="";

if(isset($_SESSION["phone"])) {
	$cookie_name = "phone";
	$cookie_value = $_SESSION["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
}

function fillTZDropdown()
{	
	global $TZlist,$conn;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT distinct TZ from world_cities order by TZ";
		
		//echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					array_push($TZlist,$row["TZ"]);
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//fillTZDropdown();
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
    
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	
    <script src="script.js"></script>
    <script type="text/javascript">
		
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        //if(inputVal.length>2){
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
		
		var place = document.getElementById('place').value;
		
		if(place.includes("|")==true){
			document.getElementById('lat_input').setAttribute("hidden","hidden");
			document.getElementById('long_input').setAttribute("hidden","hidden");
			document.getElementById('tz_input').setAttribute("hidden","hidden");
		}
    });
});

function init(){
	document.getElementById('place').value = '<?php echo $place; ?>';
	updateNow();
	
	/*if(document.getElementById('place').value.includes("|")==false){
		document.getElementById('lat_input').removeAttribute("hidden");
		document.getElementById('long_input').removeAttribute("hidden");
		document.getElementById('tz_input').removeAttribute("hidden");
		document.getElementById('in_lat').value = getCookie('lat');
		document.getElementById('in_long').value = getCookie('long');
		document.getElementById('in_tz').value = getCookie('tz');
	}*/
}

function getCookie(cName) {
  const name = cName + "=";
  const cDecoded = decodeURIComponent(document.cookie); //to be careful
  const cArr = cDecoded.split('; ');
  let res;
  cArr.forEach(val => {
    if (val.indexOf(name) === 0) res = val.substring(name.length);
  })
  return res
}

function validateForm() {

  let d = document.getElementById("in_date").value;
  let t = document.getElementById("in_time").value;
  
  if (d == "" || t == "") {
		updateNow();
  }
  
  p = document.getElementById("place").value;
  if (p == "") {
    document.getElementById("error").innerHTML = "இடம் கொடுக்கவில்லை";
	document.getElementById('place').style.borderColor = "red";
    return false;
  }
  
  if (p.includes("|")==false || p.includes("/")==false || p.length<10){
	  alert('நீங்கள் இடத்தை கீழ் வரும் பட்டியலில் தேர்ந்தெடுக்கவில்லை');
	  document.getElementById('place').style.borderColor = "red";
	  return false;
  }
  
/*  p = document.getElementById("place").value;
  if(p.includes("|")==false && document.getElementById('lat_input').getAttribute("hidden")!=null)
  {
	alert("நீங்கள் இடத்தை கீழ் வரும் பட்டியலில் தேர்ந்தெடுக்கவில்லை. உங்கள் இடத்திற்கான அட்சரேகை மற்றும் தீர்க்கரேகை கொடுக்கவும்");
	document.getElementById('place').style.borderColor = "red";
	document.getElementById('place').style.bgColor = "yellow";
	document.getElementById('lat_input').removeAttribute("hidden");
	document.getElementById('long_input').removeAttribute("hidden");
	document.getElementById('tz_input').removeAttribute("hidden");
	
	return false;
  }
 */

}

function placeOnBlur() {
	let place = document.getElementById("place").value;
	
	if(place==""){
		document.getElementById('place').value = '<?php echo $place; ?>';
	}
	
/*	if(place.includes("|")==true){
		document.getElementById('lat_input').setAttribute("hidden")="hidden";
		document.getElementById('long_input').setAttribute("hidden")="hidden";
		document.getElementById('tz_input').setAttribute("hidden")="hidden";
	}
	else{
		document.getElementById('lat_input').removeAttribute("hidden");
		document.getElementById('long_input').removeAttribute("hidden");
		document.getElementById('tz_input').removeAttribute("hidden");
		
		document.getElementById('lat_input').focus();
	}
*/
}

function questionOnBlur() {
	let ques = document.getElementById("question").value;
	
	if(ques==""){
		document.getElementById('question').value = '<?php echo $question; ?>';
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
		
    </script>
</head>

<body onLoad="init();">

<div class="title"></div>
			<div class='column-menu'>
				<?php displayUserInfo($_SESSION['userinfo']);?>
					<div class='desktop'>	
						 
						<button class="menu" onClick="window.open('index.php','_self')">Home</button>
						<button class='menu' onClick='window.open("jamaChartDisplay.php","_self")'>Jama Chart history</button>
					</div>
					<div class='mobile'>
						<?php include "jamaInputMenu_mobile.php"; ?>
					</div>
			</div>
			<div class='column-body'>
		
			<center><div class="divTable blueTable" id="chart">
				<form name="chartInput" action="Jamakol_Chart.php" method="post">
					<table style="width:100%; text-align: left">
						<tr>
							<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">ஜாமக்கோள் Chart</P></td>
						</tr>
						
						<tr style="background-color: #FFFFFF">
							<td><div class="divBirthInputLable">கேள்வி:</div></td>
							<td>
							<!-- <input type="text" name="question1" id="question1" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Enter question..." onfocus=this.value=''  /> -->
							<textarea rows = "4" cols = "60" name="question" id="question" autocomplete="off" style="background:#ffffff;font-weight:bold" onBlur="questionOnBlur();" placeholder="Enter question here..." /></textarea>
							</td>
						</tr>			
						<tr style="background-color: #FFFFFF">
							<td><div class="divBirthInputLable">*இடம் :</div></td>
							<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div>
							</td>
						</tr>
						<tr style="background-color: #FFFFFF;" id="lat_input" hidden="hidden">
							<td><div class="divBirthInputLable">*Latitude:</div></td>
							<td><input type="number" id="in_lat" name="in_lat" style="border:1px solid lightgray;font-weight:bold;width:100px"></td>
							</td>
						</tr>
						<tr style="background-color: #FFFFFF" id="long_input" hidden="hidden">
								<td><div class="divBirthInputLable">*Longitude:</div></td>
								<td><input type="number" id="in_long" name="in_long" style="border:1px solid lightgray;font-weight:bold;width:100px"></td>
							</td>
						</tr>
						<tr style="background-color: #FFFFFF" id="tz_input" hidden="hidden">
								<td><div class="divBirthInputLable">*TimeZone:</div></td>
<?php
/*								echo '<td><select name="in_tz" id="in_tz" style="width:auto;font-size:16px;font-weight:bold;">';
								foreach ($TZlist as $value){
										echo '<option value='.$value.'>'.$value.'</option>';
								}
								echo '</select></td>';
*/
?>
						</tr>
						<tr style="background-color: #FFFFFF">
							<td><div class="divBirthInputLable">தேதி:</div></td>
							<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold"></td>
						</tr>
						<tr style="background-color: #FFFFFF">
							<td><div class="divBirthInputLable">நேரம்:</div></td>
							<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold"></td>
						</tr>
						<input type="hidden" id="yes" name="in_degree" value="1" />
						
							
						<tr style="background-color: #FFFFFF">
							<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
						</tr>
						
						<!-- <tr style="background-color: #FFFFFF">
							<td>
								<label for="lang" style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Choose Language:</label>
							</td>
							<td>
								<select name="lang" id="lang" style="width:auto;font-size:20px;">
								  <option value="English">English</option>
								  <option value="Tamil" selected>Tamil</option>
								</select>
							</td>
						</tr> -->
						<tr  style="background-color: #FFFFFF">
							<td colspan="2" align="center"><input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="return validateForm();"></td>
							
						</tr>
						
					</table>
				</form>
			</div></center>

	</div>

</body>

</html>