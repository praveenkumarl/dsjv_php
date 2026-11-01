<?php 
require_once "lib.php";
require_once "lang/lang.php";

// Determine if language is set via session, cookie, or default to english
if(!isset($language)){
	$language = isset($_SESSION['language']) ? $_SESSION['language'] : (isset($_COOKIE['language']) ? $_COOKIE['language'] : 'english');
}

// Initialize the language system
init_language($language);

$place_boy="";
$place_girl="";
if(isset($_COOKIE["place_boy"])) {
  $place_boy = $_COOKIE["place_boy"];
  
  $_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
  $chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";
}

if(isset($_COOKIE["place_girl"])) {
  $place_girl = $_COOKIE["place_girl"];
  
  $_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
  $chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";
}

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
}

$girlname = isset($_SESSION['girlname']) ? $_SESSION['girlname'] : "";
$boyname = isset($_SESSION['boyname']) ? $_SESSION['boyname'] : "";

$girldate = isset($_SESSION['girldate']) ? $_SESSION['girldate'] : "";
$girltime = isset($_SESSION['girltime']) ? $_SESSION['girltime'] : "";

$boydate = isset($_SESSION['boydate']) ? $_SESSION['boydate'] : "";
$boytime = isset($_SESSION['boytime']) ? $_SESSION['boytime'] : "";

?>

<!doctype html>
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
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	 
    <script src="script.js"></script>
    <script type="text/javascript">
		
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
    //$(document).on("click", ".result div", function(){
    $(document).on("click", ".result div", function(){    
		$(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
    });
});

function init(){
	document.getElementById('place_girl').value = '<?php echo $place_girl; ?>';
	document.getElementById('place_boy').value = '<?php echo $place_boy; ?>';
	document.getElementById('girlname').value = '<?php echo $girlname; ?>';
	document.getElementById('boyname').value = '<?php echo $boyname; ?>';
	document.getElementById('in_date_girl').value = '<?php echo $girldate; ?>';
	document.getElementById('in_time_girl').value = '<?php echo $girltime; ?>';
	document.getElementById('in_date_boy').value = '<?php echo $boydate; ?>';
	document.getElementById('in_time_boy').value = '<?php echo $boytime; ?>';
	updateNow();
}

function validateForm() {

  let gn = document.getElementById("girlname").value;
  let gd = document.getElementById("in_date_girl").value;
  let gt = document.getElementById("in_time_girl").value;
  let bn = document.getElementById("boyname").value;
  let bd = document.getElementById("in_date_boy").value;
  let bt = document.getElementById("in_time_boy").value;
  
  pb = document.getElementById("place_boy").value;
  
  if (pb == "") {
	alert('<?php echo addslashes(__('boy_birth_place_required')); ?>');
	document.getElementById('place_boy').style.borderColor = "red";
    return false;
  }
  
   if (pb.includes("|")==false || pb.includes("/")==false || pb.length<10){
	  alert('<?php echo addslashes(__('boy_birth_place_select_from_list')); ?>');
	  document.getElementById('place_boy').style.borderColor = "red";
	  return false;
  }
  
  pg = document.getElementById("place_girl").value;
  
  if (pg == "") {
    alert('<?php echo addslashes(__('girl_birth_place_required')); ?>');
	document.getElementById('place_girl').style.borderColor = "red";
    return false;
  }
  
  if (pg.includes("|")==false || pg.includes("/")==false || pg.length<10){
	  alert('<?php echo addslashes(__('girl_birth_place_select_from_list')); ?>');
	  document.getElementById('place_girl').style.borderColor = "red";
	  return false;
  }
  
   if (gn == "") {
    alert('<?php echo addslashes(__('girl_name_required')); ?>');
	document.getElementById('girlname').style.borderColor = "red";
    return false;
  }

  if (gd == "") {
    alert('<?php echo addslashes(__('girl_birth_date_required')); ?>');
	document.getElementById('in_date_girl').style.borderColor = "red";
    return false;
  }
  
   if (gt == "") {
    alert('<?php echo addslashes(__('girl_birth_time_required')); ?>');
	document.getElementById('in_time_girl').style.borderColor = "red";
    return false;
  }
  
  if (bn == "") {
    //document.getElementById("error").innerHTML = "Boy name must be filled out";
	alert('<?php echo addslashes(__('boy_name_required')); ?>');
	document.getElementById('boyname').style.borderColor = "red";
    return false;
  }
  
  if (bd == "") {
    //document.getElementById("error").innerHTML = "Date must be filled out";
	alert('<?php echo addslashes(__('boy_birth_date_required')); ?>');
	document.getElementById('in_date_boy').style.borderColor = "red";
    return false;
  }
  
  if (bt == "") {
    //document.getElementById("error").innerHTML = "Time must be filled out";
	alert('<?php echo addslashes(__('boy_birth_time_required')); ?>');
	document.getElementById('in_time_boy').style.borderColor = "red";
    return false;
  } 
  
 
}

function placeOnBlur() {
	let place_boy = document.getElementById("place_boy").value;
	let place_girl = document.getElementById("place_girl").value;
	
	if(place_boy==""){
		document.getElementById('place_boy').value = '<?php echo $place_boy; ?>';
	}
	
	if(place_girl==""){
		document.getElementById('place_girl').value = '<?php echo $place_girl; ?>';
	}
}


function updateNow() {
  const date = new Date();
  
  var dd = date.getDate();
  var mm = date.getMonth()+1
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

  if(!document.getElementById('in_date_boy').value){
	  document.getElementById('in_date_boy').value = today;
	  document.getElementById('in_time_boy').value = time; 
  }
  
  if(!document.getElementById('in_date_girl').value){
	document.getElementById('in_date_girl').value = today;
	document.getElementById('in_time_girl').value = time; 
  }
}

	function openLoader() {
		document.getElementById("loader").style.display = "block";
	  }
		
    </script>
</head>

	<body onLoad="init();">
<div id="loader" class="center" style="display: none;"></div>

<div class="title"></div>

		<div class='column-menu'>
			<div class='desktop'>
				<?php displayUserInfo($_SESSION['userinfo']); ?>
				<button class="menu" onClick="window.open('index.php','_self')"><?php _e('back'); ?></button>
				<button class='menu' onClick='window.open("matchingChartDisplay.php","_self")'><?php _e('history'); ?></button>
				<button class='menu' onclick="toggleLanguage()" style="background:transparent;border:1px solid #fff;color:#fff;min-width:80px;padding:8px;margin-left:6px;">
					<?php echo ($language === 'english' ? 'EN' : 'TA'); ?>
				</button>
			</div>
			<div class='mobile'>
				<?php include "matchingInputMenu_mobile.php"; ?>
				<div style="text-align:right;padding:8px 10px;">
					<button class='menu' onclick="toggleLanguage()" style="background:transparent;border:1px solid #fff;color:#fff;min-width:72px;padding:8px;font-size:12px;">
						<?php echo ($language === 'english' ? 'EN' : 'TA'); ?>
					</button>
				</div>
			</div>
		</div>
		<div class='column-body'>
					<div class="container">
					
						<center><div class="divTable blueTable" id="chart">
							<form name="chartInput" action="Matching_Chart.php" method="post" onsubmit="openLoader()">
								<table style="width: max-content;text-align: left">
									<tr>
									<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center"><?php _e('matching_horoscope'); ?></P></td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('girl_name'); ?></div></td>
									<td><input type="text" name="girlname" id="girlname" autocomplete="off" placeholder="<?php _e('enter_girl_name'); ?>" onfocus=this.value='' onBlur="girlnameOnBlur();" style="color:#000000;font-size:12px;font-weight:bold;" />
									</td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('girl_birth_place'); ?></div></td>
									<td><div class="search-box"><input type="text" name="place_girl" id="place_girl" autocomplete="off" placeholder="<?php _e('search_place_placeholder'); ?>" onfocus=this.value='' onBlur="placeOnBlur();" style="color:#000000;font-size:12px;font-weight:bold;width:175px;" /><div class="result"></div>
									</td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('girl_birth_date'); ?></div></td>
									<td><input type="date" id="in_date_girl" name="in_date_girl" style="color:#000000;font-size:12px;font-weight:bold;width:100px;" /></td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('girl_birth_time'); ?></div></td>
									<td><input type="time" id="in_time_girl" name="in_time_girl" step="1" style="color:#000000;font-size:12px;font-weight:bold;width:100px;" /></td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('boy_name'); ?></div></td>
									<td><input type="text" name="boyname" id="boyname" autocomplete="off" style="color:#000000;font-size:12px;font-weight:bold;width:175px;" placeholder="<?php _e('enter_boy_name'); ?>" onfocus=this.value='' onBlur="boynameOnBlur();" />
									</td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('boy_birth_place'); ?></div></td>
									<td><div class="search-box"><input type="text" name="place_boy" id="place_boy" autocomplete="off" style="color:#000000;font-size:12px;font-weight:bold;width:175px;" placeholder="<?php _e('search_place_placeholder'); ?>" onfocus=this.value='' onBlur="placeOnBlur();" /><div class="result"></div>
									</td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('boy_birth_date'); ?></div></td>
									<td><input type="date" id="in_date_boy" name="in_date_boy" style="color:#000000;font-size:12px;font-weight:bold;width:100px;" /></td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td><div class="divBirthInputLable"><?php echo "*" . __('boy_birth_time'); ?></div></td>
									<td><input type="time" id="in_time_boy" name="in_time_boy" step="1" style="color:#000000;font-size:12px;font-weight:bold;width:100px;" /></td>
								</tr>
								
								<tr  style="background-color: #FFFFFF">
									<td align="center"><button type="button" value="MatchBirth" class="primary wide" style="width:100%; text-align:center; font-weight:bold; background:#660000; font-size:11px; border:none; color:white; cursor:pointer;" onclick="window.open('matchBirthChart.php','_self')"><?php _e('match_birth_chart'); ?></button></td>
									<td align="center"><input type="submit" value="<?php echo htmlspecialchars(__('submit'), ENT_QUOTES, 'UTF-8'); ?>" class="primary wide" style="width:100%; text-align:center; font-weight:bold; background:#660000; font-size:11px; border:none; color:white; cursor:pointer;" onclick="return validateForm();"></td>
								</tr>
								<tr style="background-color: #FFFFFF">
									<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
								</tr>
									
									<!-- Language selector removed - integrated into header above -->
									
			
									
								</table>
							</form>
						</div></center>
				  </div>
				</div>
			</div>
		</div>

</body>

</html>