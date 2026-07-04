<?php session_start();
require "lib.php";
require_once "lang/lang.php";

// Initialize language from cookie/session
if(isset($_COOKIE['language'])){
	$_COOKIE['language'] = strtolower($_COOKIE['language']);
}
if(isset($_POST['language'])){
	$language = strtolower($_POST['language']);
	setcookie('language', $language, time() + (86400 * 365), "/");
}
elseif(isset($_COOKIE['language'])){
	$language = $_COOKIE['language'];
}
else{
	$language = 'tamil';
}
init_language($language);

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : (isset($_SESSION['phone']) ? $_SESSION['phone'] : 1);

$in_place = $_GET["place"];
$in_name = $_GET["name"];
$in_sex = $_GET["sex"];
$g_rowid = $_GET["rowid"];
$chart_time = $_GET["nTime"];
$chart_date = $_GET["nDate"];
$userinfo = $_SESSION['userinfo'];

if ((empty($userinfo['office_name']) || empty($userinfo['office_contact'])) && $phone != 1) {
	$refreshedUserInfo = getUserInfo_web($phone);
	if (!empty($refreshedUserInfo)) {
		$_SESSION['userinfo'] = $refreshedUserInfo;
		$userinfo = $refreshedUserInfo;
	}
}

$office_name = isset($userinfo['office_name']) ? $userinfo['office_name'] : '';
$office_contact = isset($userinfo['office_contact']) ? $userinfo['office_contact'] : '';

// Pre-populate translated labels for heredoc
$lbl_heading = __('form_onepage_heading');
$lbl_father = __('form_father_name');
$ph_father = __('form_father_placeholder');
$lbl_mother = __('form_mother_name');
$ph_mother = __('form_mother_placeholder');
$lbl_caste = __('form_caste');
$ph_caste = __('form_caste_placeholder');
$lbl_office = __('form_office_name');
$ph_office = __('form_office_placeholder');
$lbl_phone = __('form_office_phone');
$ph_phone = __('form_phone_placeholder');
$lbl_submit = __('form_submit');

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
		<div class="heading">*<?php echo $lbl_heading; ?></div>
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
					<div class='divBirthInputLable'>*$lbl_father</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="father" id="father" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="$ph_father" /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>*$lbl_mother</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="mother" id="mother" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="$ph_mother" /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>*$lbl_caste</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="caste" id="caste" autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="$ph_caste" /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>$lbl_office</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="office_name" id="office_name" value='$office_name' autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="$ph_office" /></div>
				</div>
				<div class='divTableRow'>
					<div class='divBirthInputLable'>$lbl_phone</div>
					<div class='divBirthInput' style='font-weight:bold'><input type="text" name="office_phone" id="office_phone" value='$office_contact' autocomplete="off" style="background-color:var(--chart-background);font-size:14px;font-weight:bold;height:fill_parent;width:fill_parent;display:inline" placeholder="$ph_phone" /></div>
				</div>
			</div>
			<input type='submit' class='actionBtn' id='submit1' name='submit1' value='$lbl_submit' OnClick='submitForm();'>
			
		</form>
		</center>
	</div>	 
EOL;
?>

</body>

</html>
