<?php session_start();
require "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$in_place = $_GET["place"];
$in_name = $_GET["name"];
$in_sex = $_GET["sex"];
$g_rowid = $_GET["rowid"];
$chart_time = $_GET["nTime"];
$chart_date = $_GET["nDate"];
$userinfo = $_SESSION['userinfo'];
$office_name = $userinfo['office_name'];
$office_contact = $userinfo['office_contact'];

setcookie("key", "", time() - 60);  // empty value and old timestamp
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>One page input</title>
    <meta name="description" content="One page panchang">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	 
    <script src="script.js"></script>
    <script type="text/javascript">
	
	function init(){
		const currentYear = new Date().getFullYear();
		document.getElementById("year").value=currentYear;
	}
	
	function submitForm(){
		var frm = document.getElementById('onepageForm');
		
		document.getElementById('loader').style.visibility = "visible";
		
		frm.submit();
	}
		
    </script>
</head>

<body onLoad="init();">
<div id="loader" class="center" style='visibility:hidden'></div>
	
<div class="title"></div>

	<div class='column-menu'>
	<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
	</div>
	<div class='mobile'>
		<?php include "birthChartMenu_mobile.php"; ?>
	</div>
	</div>
	<div class='column-body'>
		<BR>
		<div class="heading">*ஒரு பக்க ஜாதகம் உள்ளீடு</div>
		<BR>
<?php
$onePageURL="onepage_birthchart_pdf.php?place=".$in_place."&name=".$in_name."&sex=".$in_sex.'&rowid='.$g_rowid.'&nDate='.$chart_date.'&nTime='.$chart_time;
echo <<<EOL
		<center><form name="onepageForm" action="$onePageURL" method="post" target="_SELF">
			<input type='hidden' name='place' value='$in_place'>
			<input type='hidden' name='name' value='$in_name'>
			<input type='hidden' name='sex' value='$in_sex'>
			<input type='hidden' name='rowid' value='$g_rowid'>
			<input type='hidden' name='nDate' value='$chart_date'>
			<input type='hidden' name='nTime' value='$chart_time'>
			<div class='divTable blueTable' style='height:30px;'>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>*தந்தை பெயர்</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="father" id="father" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="தந்தை பெயர்..." /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>*தாய் பெயர்</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="mother" id="mother" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="தாய் பெயர்..." /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>*பிரிவு</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="caste" id="caste" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="பிரிவு பெயர்..." /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>தங்கள் ஜோதிட நிலையத்தின் பெயர்</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="office_name" id="office_name" value='$office_name' autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="தங்கள் ஜோதிட..." /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>தங்கள் கைபேசி எண்</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="office_phone" id="office_phone" value='$office_contact' autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="தங்கள் கைபேசி..." /></div>
				</div>
			</div>
			<input type='submit' class='actionBtn' id='submit1' name='submit1' value='Submit' OnClick='submitForm();'>
			
		</form>
		</center>
	</div>	 
EOL;
?>

</body>

</html>
