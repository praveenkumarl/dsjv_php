<?php 
require "lib.php";

$place="";
$question="";

if(isset($_COOKIE["place"])) {
  $place = $_COOKIE["place"];
  
  $_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
  $chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";
  
  $_SESSION["charttime"]="";
  
} 

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
}
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 700
            });
        });
		
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length>2){
            $.get("backend-search - Copy.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
               resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
    // Set search input value on click of result item
    $(document).on("click", ".result option", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
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

<div id="fullpage">
    <div class="section" id="section0">
		<div class='row'>
		<div class='column-menu'>
			<div class='desktop'>
				<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			</div>
			<div class='mobile'>
				<div class="menu-container">
					<div class='menuBtn' onClick="window.open('index.php','_self')">Home</div>
				</div> 
			</div> 
		</div>
		<div class='column-body'>
			<div class="container">
			
				<center><div class="divTable blueTable" id="chart" style="width:400px">
					<form name="chartInput" action="pmr_Jamakol_Chart.php" method="post">
						<table style="width:90%; text-align: left">
							<tr>
								<td colspan=2><P style="color:#660000;font-weight:bold; font-size:16px; text-align:center">PMR ஜாமக்கோள் Chart</P></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Question:</H5></td>
								<td><input type="text" name="question" id="question" autocomplete="off" style="background:#ffffff" placeholder="Enter question..." onfocus=this.value='' onBlur="questionOnBlur();" />
								</td>
							</tr>	
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">*Place :</H5></td>
								<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff" placeholder="Search place..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div>
								</td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Date :</H5></td>
								<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray"></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Time :</H5></td>
								<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray"></td>
							</tr>
								
							<tr style="background-color: #FFFFFF">
								<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td>
									<label for="lang" style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Choose Language:</label>
								</td>
								<td>
									<select name="lang" id="lang" style="width:auto;font-size:20px;">
									  <option value="English">English</option>
									  <option value="Tamil" selected>Tamil</option>
									</select>
								</td>
							</tr>
							<tr>
								<td colspan="2" align="center" style="background-color:white"><H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">Click Submit to generate chart</H4></td>
							</tr>
							<tr  style="background-color: #FFFFFF">
								<td colspan="2" align="center"><input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="return validateForm();"></td>
							</tr>
							
						</table>
					</form>
				</div></center>
			</div>
		</div>
	</div>	 
</div>

</body>

</html>