<!doctype html>
<?php
require_once "lib.php";
require_once "lang/lang.php";

$isNumerologyEmbed = isset($_GET['numerology_embed']) && $_GET['numerology_embed'] === '1';

// Determine if language is set via session, cookie, or default to english
if(!isset($language)){
	$language = isset($_SESSION['language']) ? $_SESSION['language'] : (isset($_COOKIE['language']) ? $_COOKIE['language'] : 'english');
}

// Initialize the language system
init_language($language);

$place = getUserProfilePlace();
$placeJs = json_encode($place, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$name="";
  
$_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
$chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";

$_SESSION["charttime"]="";

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
} 

$conn="";
$phone=isset($_SESSION["phone"]) ? $_SESSION["phone"] : 1;

$chartInfo=array();

function getChartDetails()
{
	global $conn,$chartInfo, $phone;
	
	$cnt=0;
	
	$conn = dbConnect($conn);
	
	if($phone==0){
		echo "ERROR: Missing phone number";
		return;
	}

	try {
		
		$sql = "SELECT rowid, phone, name, place, birth_date, birth_time FROM birth_chart WHERE phone='$phone' ORDER BY rowid DESC "; 
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$chartInfo["rowid"][$cnt] = intval($row["rowid"]);
					$chartInfo["phone"][$cnt] = $row["phone"];
					$chartInfo["name"][$cnt]= $row["name"];
					$chartInfo["place"][$cnt] = $row["place"];
					$chartInfo["birth_date"][$cnt] = $row["birth_date"];
					$chartInfo["birth_time"][$cnt] = $row["birth_time"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}


?>


<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/module-landing.css?<?php echo date('ymdhis'); ?>">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="script.js"></script>
    <script type="text/javascript">
		
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
	
	if (window.self !== window.top) {    //called from Numerology.php 
		console.log("This page is inside an iframe.");
		document.getElementById('num').value = 1;
		document.getElementById('historyBtn').style.display = 'none';
		document.getElementById('nameNumerologyWarning').style.display = 'block';
		// Perform actions specific to iframe here
	} else {
		console.log("This page is not inside an iframe.");
		// Perform actions specific to standalone page here
	}
});

function openBirthHistory(){
	window.open("birthChartDisplay.php","_self");
}

function init(){
	document.getElementById('place').value = <?php echo $placeJs; ?>;
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
	alert('<?php echo addslashes(__("place_not_given")); ?>');
	document.getElementById('place').style.borderColor = "red";
    return false;
  }
  
  if (p.includes("|")==false || p.includes("/")==false || p.length<10){
	  alert('<?php echo addslashes(__("place_not_selected")); ?>');
	  document.getElementById('place').style.borderColor = "red";
	  return false;
  }
  
  n = document.getElementById("name").value;
  if (n == "") {
	alert('<?php echo addslashes(__("place_not_given")); ?>');
	document.getElementById('name').style.borderColor = "red";
    return false;
  }
  
  document.forms["chartInput"].submit();
  
}


function placeOnBlur() {
	let place = document.getElementById("place").value;
	
	if(place==""){
		document.getElementById('place').value = <?php echo $placeJs; ?>;
	}
}

function nameOnBlur() {
	let name = document.getElementById("name").value;
	
	if(name==""){
		document.getElementById('name').value = '<?php echo $name; ?>';
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

	
	function showLoader() {
		document.getElementById("loader").style.display = "block";
	  }
	
		
    </script>
</head>

<body class="module-landing module-form-page<?php echo $isNumerologyEmbed ? ' module-numerology-embed' : ''; ?>" onLoad="init()">
<div id="loader" class="center" style="display: none;"></div>

<div class="title"></div>
<div class='column-menu'>
<?php displayUserInfo($_SESSION['userinfo']);?>
	<div class='desktop'>
		<button type="button" class="menu" onClick="window.open('index.php','_self')"><?php _e('back'); ?></button>
		<button type="button" class='menu' onClick='openBirthHistory();'><?php _e('history'); ?></button>
	</div>
	<div class='mobile'>
		<?php include "birthInputMenu_mobile.php"; ?>
	</div>
</div>
<div class='column-body'>
	<center>
		<div class="divTable blueTable" id="chart">
			<form name="chartInput" id="chartInput" action="Birth_Chart.php" method="post" onsubmit="showLoader()" target="_top">
				<input type="hidden" name="num" id="num" value="0" />
				<table style="width:100%; text-align: left">
					<tr>
						<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center"><?php _e('birth_horoscope'); ?></P></td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable"><?php echo "*" . __('name'); ?></div></td>
						<td><input type="text" name="name" id="name" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="<?php _e('name'); ?>" onfocus=this.value='' onBlur="nameOnBlur();" />
							<div id='nameNumerologyWarning' class='error' style='display:none;background-color:white;color:red;font-size:12px'><?php _e('name_warning'); ?></div>
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable"><?php echo "*" . __('gender'); ?></div></td>
						<td>
							<select name="sex" id="sex" style="width:auto;font-size:16px;">
								<option value="<?php _e('male'); ?>" selected>&#128104;<?php _e('male'); ?></option>
								  <option value="<?php _e('female'); ?>">&#128105;<?php _e('female'); ?></option>
								  <option value="<?php _e('other'); ?>"><?php _e('other'); ?></option>
							</select>
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable"><?php echo "*" . __('birth_place'); ?></div></td>
						<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="<?php _e('search_place_placeholder'); ?>" onfocus=this.value='' onBlur="placeOnBlur();" /><div class="result"></div>
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable"><?php echo "*" . __('birth_date'); ?></div></td>
						<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold"></td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable"><?php echo "*" . __('birth_time'); ?></div></td>
						<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold"></td>
					</tr>
					<!-- <tr>
						<td></td>
						<td><input type="checkbox" id="birthsave" name="birthsave" value=1 style="display:none" /><label class='divBirthInputLable' for="birthsave">Save</label></td>
					</tr> -->
					<!-- <tr style="background-color: #FFFFFF">
						<td>
							<label for="lang" style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Choose Language:</label>
						</td>
						<td>
							<select name="lang" id="lang" style="width:auto;font-size:20px;">
								<option value="Tamil" selected>Tamil</option>
								  <option value="English">English</option>
							</select>
						</td>
					</tr> -->
					<tr style="background-color: #FFFFFF">
						<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
					</tr>
					<tr  style="background-color: #FFFFFF">
						<td align="center">
							<button type="button" id="historyBtn" class="primary wide" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="openBirthHistory()"><?php _e('history'); ?></button>
						</td>
						<td align="center">
							<input type="button" value="<?php _e('submit'); ?>" class="primary wide" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();">
						</td>
					</tr>
					
				</table>
			</form>	
		</div>
		
		</div>
	</center>
</div>

</body>

</html>