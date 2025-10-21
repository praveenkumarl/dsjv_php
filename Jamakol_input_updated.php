<?php 
require "lib.php";

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
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
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
    });
});

function init(){
	document.getElementById('place').value = '<?php echo $place; ?>';
	updateNow();
}

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
  
  p = document.getElementById("place").value;
  if(p.includes("|")==false)
  {
	  alert("தவறான இடம் கொடுத்துள்ளீர்கள், சரி பார்க்கவும்");,
	  return false;
  }

}

function placeOnBlur() {
	let place = document.getElementById("place").value;
	
	if(place==""){
		document.getElementById('place').value = '<?php echo $place; ?>';
	}
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
	<div class='row'>
			<div class='column-menu'>
					<div class='desktop'>
					<?php displayUserInfo($_SESSION['userinfo']); ?>
						<button class="menu" onClick="window.open('index.php','_self')">Home</button>
						<button class='menu' onClick='window.open("jamaChartDisplay.php","_self")'>Jama Chart history</button>
					</div>
					<div class='mobile'>
						<div class="menu-container">
							<div class='menuBtn' onClick="window.open('index.php','_self')">Home</div>
							<div class='menuBtn' onClick="window.open('jamaChartDisplay.php','_self')">History</div>
						</div> 
					</div>
			</div>
			<div class='column-body'>
		
			<center><div class="divTable blueTable" id="chart" style="width:360px;">
				<form name="chartInput" action="Jamakol_Chart.php" method="post">
					<table style="width:100%; text-align: left">
						<tr>
							<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">ஜாமக்கோள் Chart</P></td>
						</tr>
						
						<tr style="background-color: #FFFFFF">
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Question:</H5></td>
							<td><input type="text" name="question" id="question" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Enter question..." onfocus=this.value='' onBlur="questionOnBlur();" />
							</td>
						</tr>			
						<tr style="background-color: #FFFFFF">
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">*Place :</H5></td>
							<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff;font-weight:bold;font-size:10;width:250px" placeholder="Search place..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div>
							</td>
						</tr>
						<tr style="background-color: #FFFFFF">
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Date:</H5></td>
							<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold"></td>
						</tr>
						<tr style="background-color: #FFFFFF">
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Time:</H5></td>
							<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold"></td>
						</tr>
						<tr style="background-color: #FFFFFF">
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Degree:</H5></td>
							<td>
							<input type="radio" id="yes" name="in_degree" value="1" <?php echo $yes; ?> />
							<label for="yes" style="vertical-align: middle;">Yes</label>
							<input type="radio" id="no" name="in_degree" value="0" <?php echo $no; ?> 	/>
							<label for="no" style="vertical-align: middle;">No</label>
							
							</td>
						</tr>
							
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

	</div>

</body>

</html>