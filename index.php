<?php 
include_once "lib.php";

$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : "9999999999";
unset($_SESSION['menu_clicked']);
$menuClicked =false;

$key = '';

if(!isset($_COOKIE['key'])){
	$key = isset($_GET['key']) ? $_GET['key'] : "";
	setcookie('key', $key, strtotime('+1 days'));
	debug ("no cookie");
}
elseif($_COOKIE['key']==""){
	unset($_COOKIE['key']);
	$key = isset($_COOKIE['key']) ? $_COOKIE['key'] : "";
	setcookie('key', $key, strtotime('+1 days'));
	debug("cookie updated");
}
else{
	$key = isset($_COOKIE['key']) ? $_COOKIE['key'] : "";
	debug ("cookie exists");
}

//dummy user info
$userInfo=['name'=>'Freeuser','phone'=>'9999999999','user_type'=>'FREE', 'expiry_date'=>'01-01-1970'];

if(isMobile() and $key){
	$userInfo = getUserInfo_mobile($key);
	
	if(!empty($userInfo)){
		$_SESSION['logged']=true;
		$_SESSION['userinfo']=$userInfo;
	}
	else{ // if no user exists in databse force the user to register first
		echo "alert('INFO: Please register to access');";
		header('Location: new_registration_mobile.php?key='.$key);
	}
}
elseif(isMobile() and isset($_COOKIE['key'])){
	$_SESSION['logged']=true;
	$userInfo = getUserInfo_mobile($_COOKIE['key']);
	$_SESSION['userinfo']=$userInfo;
}
elseif(!strcmp($phone,"9999999999")==0){
	$_SESSION['logged']=true;
	$userInfo = getUserInfo_web($phone);
	$_SESSION['userinfo']=$userInfo;
}
elseif(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'admin.php')>0){ //this else is to allow login from admin page for other users (Act As)
	$userInfo = getUserInfo_mobile($_GET['key']);
	
	if(!empty($userInfo)){
		$_SESSION['logged']=true;
		$_SESSION['userinfo']=$userInfo;
	}
	else{ // if no user exists in databse force the user to register first
		echo "alert('INFO: Please register to access');";
		header('Location: new_registration_mobile.php?key='.$key);
	}
}
else{
	$_SESSION['logged']=true;
	$_SESSION['userinfo']=$userInfo;
}

$_SESSION['user_type'] = isset($userInfo['user_type']) ? $userInfo['user_type'] : "FREE";
$_SESSION['phone'] = isset($userInfo['phone']) ? $userInfo['phone'] : 1;

if(strcmp($_SESSION['user_type'],PANCHANG)==0){
	header("Location: new_panchang.php");
}
elseif(strcmp($_SESSION['user_type'],BIRTH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCHMATCH)==0 OR strcmp($_SESSION['user_type'],BIRTHTHARA)==0){
	header("Location: Birth_input.php");
}
elseif(strcmp($_SESSION['user_type'],MATCHING)==0 OR strcmp($_SESSION['user_type'],BIRTHMATCH)==0 OR strcmp($_SESSION['user_type'],BIRTHPANCHMATCH)==0){
	header("Location: Matching_input.php");
}
elseif(strcmp($_SESSION['user_type'],JAMAKOL)==0){
	header("Location: Jamakol_input.php");
}
elseif(strcmp($_SESSION['user_type'],JAMA_MATCH)==0){
	header("Location: Jamakol_input.php");
}
elseif(strcmp($_SESSION['user_type'],JAMA_BIRTH)==0){
	header("Location: Jamakol_input.php");
}
elseif(strcmp($_SESSION['user_type'],PRASANNAM)==0){
	header("Location: prasannam_home.php");
}
elseif(strcmp($_SESSION['user_type'],NUMEROLOGY)==0){
	header("Location: numerology.php");
}
elseif(strcmp($_SESSION['user_type'],FREE)==0){
	header("Location: freeuser.php");
}

$expiry_date = $_SESSION['userinfo']['expiry_date'];
$currentLanguage = isset($_COOKIE['language']) ? strtolower($_COOKIE['language']) : 'tamil';
require_once "lang/lang.php";
init_language($currentLanguage);
$landingUserInfo = isset($_SESSION['userinfo']) && is_array($_SESSION['userinfo'])
	? $_SESSION['userinfo']
	: array();
$landingUserPhone = isset($landingUserInfo['phone']) ? trim((string)$landingUserInfo['phone']) : '';
$landingPlace = isset($landingUserInfo['place']) ? trim((string)$landingUserInfo['place']) : '';
$landingTimezone = isset($landingUserInfo['tz']) ? trim((string)$landingUserInfo['tz']) : '';
$landingPlaceParts = explode('|', $landingPlace);
if (count($landingPlaceParts) > 1) {
	if ($landingTimezone === '') {
		$landingTimezone = trim($landingPlaceParts[1]);
	}
	$landingPlace = trim($landingPlaceParts[0]);
}
$landingCanSaveProfile = $landingUserPhone !== '' && $landingUserPhone !== '9999999999';
$landingProfileNeedsLocation = $landingCanSaveProfile && ($landingPlace === '' || $landingTimezone === '');
$landingProfilePanelOpen = $landingProfileNeedsLocation || isset($_GET['profile_error']);
$landingProfileEditOpen = $landingProfileNeedsLocation || isset($_GET['profile_error']);
if (!isset($_SESSION['profile_location_token'])) {
	$_SESSION['profile_location_token'] = bin2hex(random_bytes(32));
}
$landingProfileError = '';
if (isset($_GET['profile_error'])) {
	$landingProfileError = ($currentLanguage === 'english')
		? 'Your profile could not be saved. Please select a city from the suggestions and try again.'
		: 'உங்கள் சுயவிவரத்தைச் சேமிக்க முடியவில்லை. பரிந்துரைகளிலிருந்து நகரத்தைத் தேர்ந்தெடுத்து மீண்டும் முயற்சிக்கவும்.';
}
$landingPanchangPlace = ($landingPlace !== '' && $landingTimezone !== '')
	? $landingPlace . '|' . $landingTimezone
	: '';
$landingPanchangLoading = '...';
$landingPanchang = ($currentLanguage === 'english')
	? array(
		array('Tithi', $landingPanchangLoading, 'thithi'),
		array('Nakshatra', $landingPanchangLoading, 'nakshatra'),
		array('Yoga', $landingPanchangLoading, 'yoga'),
		array('Karana', $landingPanchangLoading, 'karanam')
	)
	: array(
		array(__('thithi'), $landingPanchangLoading, 'thithi'),
		array(__('nakshatra'), $landingPanchangLoading, 'nakshatra'),
		array(__('yogam'), $landingPanchangLoading, 'yoga'),
		array(__('karanam'), $landingPanchangLoading, 'karanam')
	);
$landingInauspiciousPeriods = array(
	array(__('ema_kantam'), 'ema_kantam'),
	array(__('kuligai_time'), 'kuligai_time'),
	array(__('rahu_kaalam'), 'rahu_kaalam')
);
$landingUserType = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'FREE';
$landingSubscriptionNames = ($currentLanguage === 'english')
	? array(
		'FREE' => 'Free',
		'PAID' => 'Premium',
		'BIRTH' => 'Birth Horoscope',
		'MATCH' => 'Marriage Matching',
		'BIRTHMATCH' => 'Birth Horoscope + Marriage Matching',
		'BIRTHPANCH' => 'Birth Horoscope + Panchangam',
		'BIRTHPANCHMATCH' => 'Birth Horoscope + Panchangam + Matching',
		'BIRTHTHARA' => 'Birth Horoscope + Thaara Palan',
		'PANCHANG' => 'Panchangam',
		'JAMAKOL' => 'Jamakol',
		'JAMAMATCH' => 'Jamakol + Marriage Matching',
		'JAMABIRTH' => 'Jamakol + Birth Horoscope',
		'PRASANNAM' => 'Prasannam',
		'NUMEROLOGY' => 'Numerology',
		'ADMIN' => 'Admin',
		'SUPER' => 'Super Admin'
	)
	: array(
		'FREE' => 'இலவசம்',
		'PAID' => 'பிரீமியம்',
		'BIRTH' => 'பிறப்பு ஜாதகம்',
		'MATCH' => 'திருமணப் பொருத்தம்',
		'BIRTHMATCH' => 'பிறப்பு ஜாதகம் + திருமணப் பொருத்தம்',
		'BIRTHPANCH' => 'பிறப்பு ஜாதகம் + பஞ்சாங்கம்',
		'BIRTHPANCHMATCH' => 'பிறப்பு ஜாதகம் + பஞ்சாங்கம் + பொருத்தம்',
		'BIRTHTHARA' => 'பிறப்பு ஜாதகம் + தாரா பலன்',
		'PANCHANG' => 'பஞ்சாங்கம்',
		'JAMAKOL' => 'ஜாமக்கோள்',
		'JAMAMATCH' => 'ஜாமக்கோள் + திருமணப் பொருத்தம்',
		'JAMABIRTH' => 'ஜாமக்கோள் + பிறப்பு ஜாதகம்',
		'PRASANNAM' => 'பிரசன்னம்',
		'NUMEROLOGY' => 'எண் கணிதம்',
		'ADMIN' => 'நிர்வாகி',
		'SUPER' => 'முதன்மை நிர்வாகி'
	);
$landingSubscriptionName = isset($landingSubscriptionNames[$landingUserType])
	? $landingSubscriptionNames[$landingUserType]
	: $landingUserType;
$landingUserName = isset($_SESSION['userinfo']['name']) ? trim((string)$_SESSION['userinfo']['name']) : '';
$landingExpiryTimestamp = strtotime((string)$expiry_date);
$landingExpiryDisplay = ($landingExpiryTimestamp !== false)
	? date('d-m-Y', $landingExpiryTimestamp)
	: (string)$expiry_date;
$landingToolDescriptions = ($currentLanguage === 'english')
	? array(
		'Answers for the question you ask',
		"Today's tithi and star",
		'Name and birth numbers',
		'Compare two birth charts',
		'Astrology for your birth time',
		'Star-based guidance',
		"Today's hora timings",
		'A live prasannam reading',
		'The role of each planet'
	)
	: array(
		'கேட்ட நேரத்தின் பதில்',
		'இன்றைய திதி, நட்சத்திரம்',
		'பெயர் மற்றும் பிறந்த எண்',
		'இரு ஜாதகங்களின் ஒப்பீடு',
		'பிறந்த நேரத்தின் கணிப்பு',
		'நட்சத்திர பலன்',
		'இன்றைய ஹோரை நேரம்',
		'நேரடி பிரசன்ன குறிப்பு',
		'ஒவ்வொரு கிரகத்தின் பொறுப்பு'
	);

?>
<?php
// Announcement message logic
require_once "db.php";
$announcement_msg = "";
date_default_timezone_set('Asia/Kolkata'); // Ensure correct timezone
try {
	$conn = dbConnect(0);
	if ($conn instanceof mysqli) {
		$today = date('Y-m-d');
		$sql = "SELECT message FROM announcement_msgs WHERE is_active=1 AND show_date<=? AND (expiry_date IS NULL OR expiry_date>=?) ORDER BY show_date DESC LIMIT 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param('ss', $today, $today);
		$stmt->execute();
		$stmt->bind_result($msg);
		if ($stmt->fetch()) {
			$announcement_msg = $msg;
		}
		$stmt->close();
		dbDisconnect($conn);
	} else {
		error_log('index.php: announcement lookup skipped because the database connection failed.');
	}
} catch (mysqli_sql_exception $e) {
	error_log('index.php: announcement lookup failed: ' . $e->getMessage());
	if (isset($conn) && $conn instanceof mysqli) {
		dbDisconnect($conn);
	}
}
?>

<!doctype html>
<html lang="<?php echo ($currentLanguage === 'english') ? 'en' : 'ta'; ?>" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="canonical" href="https://html5-templates.com/" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">	
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600&family=Noto+Serif+Tamil:wght@600;700&family=Noto+Sans:wght@400;500&family=Noto+Serif:wght@600&display=swap" rel="stylesheet">

    <title>DSJV app</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/landing-theme.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/client_theme_css.php">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>

    
	<!-- <div id="google_translate_element"></div>
	<script type="text/javascript">
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({pageLanguage: 'ta', includedLanguages: 'en,hi,te,kn,ml', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
	}
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->
    <script src="script.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="common.js?<?php echo date('ymdhis'); ?>"></script>
    <script type="text/javascript">
 

	function init(){
		//generateServerIssueAlert();
		generateExpiryAlert();
	}
		
	document.onreadystatechange = function() {
		
		if(document.getElementById('dummy') != null){	//chart not loaded
			document.querySelector("#loader").style.display = "none";
			document.getElementById("msg").style.visibility = "hidden";
			
		}
		else{
			//document.querySelector("body").style.visibility = "hidden";
			if(document.querySelector("#loader"))
				document.querySelector("#loader").style.visibility = "visible";
			
			if(document.querySelector("msg"))
			document.getElementById("msg").style.visibility = "visible";
			
		}
		
		checkDefaultHome();
	};
	

document.addEventListener('DOMContentLoaded', function () {
    handleSwipe();
});

function handleSwipe(){
	const bodyElement = document.body;
    const hammer = new Hammer(bodyElement);

    // Listen for swipe events
    hammer.on('swipe', function (event) {
        switch (event.direction) {
            case Hammer.DIRECTION_LEFT:
                console.log('Left swipe detected');
				window.location.href = 'current_jamakol_chart.php';
                break;
            case Hammer.DIRECTION_RIGHT:
                console.log('Right swipe detected');
				window.location.href = 'new_panchang.php';
                break;
            case Hammer.DIRECTION_UP:
                console.log('Up swipe detected');
				window.location.href = 'hora.php';
                break;
            case Hammer.DIRECTION_DOWN:
                console.log('Down swipe detected');
				window.location.href = 'star_thaara_palan.php';
                break;
        }
    });
}

	function generateExpiryAlert() {
		var expiry_date_str = '<?php echo date("d-m-Y", strtotime($expiry_date)); ?>';
		var expiry_date_parts = expiry_date_str.split('-');
		var expiry_date = new Date(expiry_date_parts[2], expiry_date_parts[1] - 1, expiry_date_parts[0]);
		var today = new Date();
		today.setHours(0, 0, 0, 0);

		// Calculate the difference in milliseconds
		var timeDiff = expiry_date - today;
		var daysLeft = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

		//if (daysLeft > 0 && daysLeft <= 30) {
		if (daysLeft <= 30) {
			// Get the last shown time from localStorage
			var lastShownTime = localStorage.getItem('expiryAlertLastShown');
			var currentTime = new Date().getTime();

			if (lastShownTime && currentTime - lastShownTime < 1 * 60 * 60 * 1000) {
				// If last shown is within the last 2 hours, do nothing
				return;
			}

			// Update localStorage with the current time
			localStorage.setItem('expiryAlertLastShown', currentTime);

			var formatted_expiry_date = expiry_date_parts[0] + '-' + expiry_date_parts[1] + '-' + expiry_date_parts[2];
			if(daysLeft > 0){
				var message_tam = 'இன்னும் ' + daysLeft + ' நாட்களே உள்ளன!!!.<BR>DSJV App ன் உங்கள் சந்தா ' + formatted_expiry_date + ' அன்று முடிகிறது. நீட்டிக்க நிர்வாகியைத் தொடர்பு கொள்ளவும்.<BR> &#128241; திருமதி. சௌம்யா<BR>(+91 7305731547)';
				var message = 'Only ' + daysLeft + ' days left!!!.<BR>Your DSJV App subscription is going to expire on ' + formatted_expiry_date + '. Please contact admin to extend. <BR>&#128241; Mrs. Sowmya<BR>(+91 7305731547)';
			}
			else{
				var message_tam = 'உங்கள் DSJV App ன் சந்தா ' + formatted_expiry_date + ' அன்று முடிந்துவிட்டது. புதுப்பிக்க நிர்வாகியைத் தொடர்பு கொள்ளவும்.<BR> &#128241; திருமதி. சௌம்யா (+91 7305731547>';
				var message = 'Your DSJV App subscription expired on <BR>' + formatted_expiry_date + '. Please contact admin to renew. <BR> &#128241; Mrs. Sowmya<BR>(+91 7305731547)';
			}

			var html_str = '<div class="expiry-box">';
			html_str += '<button class="close-btn" onclick="closeTable()" aria-label="Close alert">X</button>';
			html_str += '<SPAN style="color:#F5C542; font-size:22px; font-weight:800">IMPORTANT ALERT</SPAN><BR><BR>';
			html_str += message_tam + '<br><br>' + message + '</div>';

			document.getElementById('expiryAlertDiv').innerHTML = html_str;
			document.getElementById('expiryAlertDiv').style.display = 'block';
		}
	}
	
	function generateServerIssueAlert() {
			var html_str = '<div class="table-container">';
			html_str += '<div class="scrollable-table">';
			html_str += '<center><div class="divTable blueTable" style="width:100%;height:100%">';
			html_str += '<div class="divTableRow"><div class="divExpiryAlertHeading"><BR><BR><SPAN style="color:red; font-size: 26px">***UPDATE***<BR>There is an issue in the server which is causing this error. Working with service provider. Server பிரச்சினை இருப்பதால் app வேலை செய்யவில்லை. சர்வர் கம்பெனியுடன் வேலை செய்துகொண்டுள்ளேன். விரைவில் சரி செய்யப்படும்.</SPAN><BR><BR>' + '</div></div>';
			html_str += '</div></div></div>';

			document.getElementById('expiryAlertDiv').innerHTML = html_str;
			document.getElementById('expiryAlertDiv').style.display = 'block';
	}


	function closeTable() {
		document.getElementById('expiryAlertDiv').style.display = 'none';
	}

		
    </script>
</head>

<body class="theme-landing theme-index-preview" onLoad="init();">
<?php if (!empty($announcement_msg)) { ?>
<div id="announcementOverlay" onclick="this.style.display='none'">
	<div onclick="event.stopPropagation();" style="cursor:default;">
		<?php echo htmlspecialchars($announcement_msg); ?>
	</div>
</div>
<script>
	setTimeout(function() {
		var overlay = document.getElementById('announcementOverlay');
		if (overlay) overlay.style.display = 'none';
	}, 4000); // Auto-hide after 4 seconds
	// Optional: close on ESC key
	document.addEventListener('keydown', function(e){
		if(e.key === 'Escape'){
			var overlay = document.getElementById('announcementOverlay');
			if (overlay) overlay.style.display = 'none';
		}
	});
</script>
<?php } ?>
<div class="expiryAlertDiv" id="expiryAlertDiv"></div>

<div class="aurora" aria-hidden="true">
	<div class="blob b1"></div>
	<div class="blob b2"></div>
	<div class="blob b3"></div>
</div>
<canvas id="stars" aria-hidden="true"></canvas>

<main class="wrap">
	<header class="topbar app-topbar">
		<div class="topbar-user">
			<div class="welcome-row">
				<button class="profile-trigger" id="profileTrigger" type="button" aria-controls="profilePanel" aria-expanded="<?php echo $landingProfilePanelOpen ? 'true' : 'false'; ?>" aria-label="<?php echo ($currentLanguage === 'english') ? 'Open user profile' : 'பயனர் சுயவிவரத்தைத் திற'; ?>">
					<i class="fa fa-user-o" aria-hidden="true"></i>
					<span class="profile-badge"><?php echo isFreeUser($landingUserType) ? 'FREE' : 'PRO'; ?></span>
				</button>
				<div class="welcome-copy">
					<p class="welcome"><?php
						$welcomeLabel = ($currentLanguage === 'english') ? 'Welcome' : '';
						echo $welcomeLabel;
						if ($landingUserName !== '') {
							echo ($welcomeLabel !== '' ? ', ' : '') . htmlspecialchars($landingUserName, ENT_QUOTES, 'UTF-8');
						}
					?></p>
					<p class="subscription-details">
						<?php echo htmlspecialchars($landingSubscriptionName, ENT_QUOTES, 'UTF-8'); ?>
					</p>
				</div>
			</div>
			<section class="profile-panel" id="profilePanel" aria-label="<?php echo ($currentLanguage === 'english') ? 'User profile' : 'பயனர் விவரம்'; ?>" <?php echo $landingProfilePanelOpen ? '' : 'hidden'; ?>>
				<div class="profile-heading">
					<h2><?php echo ($currentLanguage === 'english') ? 'Profile' : 'சுயவிவரம்'; ?></h2>
					<?php if ($landingCanSaveProfile && !$landingProfileNeedsLocation) { ?>
					<button class="profile-edit-toggle" id="profileEditToggle" type="button" aria-controls="profileLocationForm" aria-expanded="<?php echo $landingProfileEditOpen ? 'true' : 'false'; ?>" aria-label="<?php echo $landingProfileEditOpen ? (($currentLanguage === 'english') ? 'Cancel editing' : 'திருத்தத்தை ரத்துசெய்') : (($currentLanguage === 'english') ? 'Edit profile' : 'சுயவிவரத்தைத் திருத்து'); ?>" title="<?php echo $landingProfileEditOpen ? (($currentLanguage === 'english') ? 'Cancel editing' : 'திருத்தத்தை ரத்துசெய்') : (($currentLanguage === 'english') ? 'Edit profile' : 'சுயவிவரத்தைத் திருத்து'); ?>" data-edit-label="<?php echo ($currentLanguage === 'english') ? 'Edit profile' : 'சுயவிவரத்தைத் திருத்து'; ?>" data-cancel-label="<?php echo ($currentLanguage === 'english') ? 'Cancel editing' : 'திருத்தத்தை ரத்துசெய்'; ?>" onclick="toggleProfileEdit()"><i class="fa <?php echo $landingProfileEditOpen ? 'fa-times' : 'fa-pencil'; ?>" aria-hidden="true"></i></button>
					<?php } ?>
				</div>
				<dl class="profile-details">
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Name' : 'பெயர்'; ?></dt><dd><?php echo htmlspecialchars($landingUserName, ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Phone' : 'தொலைபேசி'; ?></dt><dd><?php echo htmlspecialchars($landingUserPhone, ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<?php if (!empty($landingUserInfo['imei'])) { ?>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Key' : 'அணுகு விசை'; ?></dt><dd class="profile-key-value"><span id="profileKeyValue"><?php echo htmlspecialchars($landingUserInfo['imei'], ENT_QUOTES, 'UTF-8'); ?></span><button class="profile-copy-key" type="button" onclick="copyProfileKey(this)" aria-label="<?php echo ($currentLanguage === 'english') ? 'Copy key' : 'விசையை நகலெடு'; ?>" title="<?php echo ($currentLanguage === 'english') ? 'Copy key' : 'விசையை நகலெடு'; ?>" data-copy-label="<?php echo ($currentLanguage === 'english') ? 'Copy key' : 'விசையை நகலெடு'; ?>" data-copied-label="<?php echo ($currentLanguage === 'english') ? 'Key copied' : 'விசை நகலெடுக்கப்பட்டது'; ?>" data-failed-label="<?php echo ($currentLanguage === 'english') ? 'Copy failed' : 'நகலெடுக்க முடியவில்லை'; ?>"><i class="fa fa-copy" aria-hidden="true"></i></button></dd></div>
					<?php } ?>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Subscription' : 'சந்தா'; ?></dt><dd><?php echo htmlspecialchars($landingSubscriptionName, ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Expires' : 'காலாவதி'; ?></dt><dd><?php echo htmlspecialchars($landingExpiryDisplay, ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<?php if (!empty($landingUserInfo['office_name'])) { ?>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Office' : 'அலுவலகம்'; ?></dt><dd><?php echo htmlspecialchars($landingUserInfo['office_name'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<?php } ?>
					<?php if (!empty($landingUserInfo['office_contact'])) { ?>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Office contact' : 'அலுவலகத் தொடர்பு'; ?></dt><dd><?php echo htmlspecialchars($landingUserInfo['office_contact'], ENT_QUOTES, 'UTF-8'); ?></dd></div>
					<?php } ?>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Place' : 'இடம்'; ?></dt><dd><?php echo htmlspecialchars($landingPlace, ENT_QUOTES, 'UTF-8') ?: '—'; ?></dd></div>
					<div><dt><?php echo ($currentLanguage === 'english') ? 'Timezone' : 'நேர மண்டலம்'; ?></dt><dd><?php echo htmlspecialchars($landingTimezone, ENT_QUOTES, 'UTF-8') ?: '—'; ?></dd></div>
				</dl>
				<?php if ($landingProfileError !== '') { ?>
				<p class="profile-message<?php echo isset($_GET['profile_error']) ? ' is-error' : ''; ?>" role="status"><?php echo htmlspecialchars($landingProfileError, ENT_QUOTES, 'UTF-8'); ?></p>
				<?php } ?>
				<?php if ($landingProfileNeedsLocation) { ?>
				<p class="profile-location-prompt"><?php echo ($currentLanguage === 'english') ? 'Add your place and timezone to show accurate Panchang details.' : 'சரியான பஞ்சாங்க விவரங்களைக் காண உங்கள் இடத்தையும் நேர மண்டலத்தையும் சேர்க்கவும்.'; ?></p>
				<?php } ?>
				<?php if ($landingCanSaveProfile) { ?>
				<form class="profile-location-form" action="save_user_place.php" method="post" id="profileLocationForm" <?php echo $landingProfileEditOpen ? '' : 'hidden'; ?>>
					<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['profile_location_token'], ENT_QUOTES, 'UTF-8'); ?>">
					<label for="profileName"><?php echo ($currentLanguage === 'english') ? 'Name' : 'பெயர்'; ?></label>
					<input id="profileName" name="name" type="text" maxlength="160" required value="<?php echo htmlspecialchars($landingUserName, ENT_QUOTES, 'UTF-8'); ?>">
					<label for="profileOfficeName"><?php echo ($currentLanguage === 'english') ? 'Office name' : 'அலுவலகப் பெயர்'; ?></label>
					<input id="profileOfficeName" name="office_name" type="text" maxlength="160" value="<?php echo htmlspecialchars(isset($landingUserInfo['office_name']) ? $landingUserInfo['office_name'] : '', ENT_QUOTES, 'UTF-8'); ?>">
					<label for="profileOfficePhone"><?php echo ($currentLanguage === 'english') ? 'Office phone' : 'அலுவலகத் தொலைபேசி'; ?></label>
					<input id="profileOfficePhone" name="office_contact" type="tel" maxlength="40" value="<?php echo htmlspecialchars(isset($landingUserInfo['office_contact']) ? $landingUserInfo['office_contact'] : '', ENT_QUOTES, 'UTF-8'); ?>">
					<label for="profilePlace"><?php echo ($currentLanguage === 'english') ? 'Search city' : 'நகரத்தைத் தேடுங்கள்'; ?></label>
					<input id="profilePlace" name="place" type="text" autocomplete="off" required value="<?php echo htmlspecialchars($landingPlace, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo ($currentLanguage === 'english') ? 'Start typing a city' : 'நகரத்தின் பெயரை உள்ளிடுங்கள்'; ?>">
					<div id="profilePlaceResults" class="profile-place-results" role="listbox"></div>
					<label for="profileTimezone"><?php echo ($currentLanguage === 'english') ? 'Timezone' : 'நேர மண்டலம்'; ?></label>
					<input id="profileTimezone" name="tz" type="text" readonly required value="<?php echo htmlspecialchars($landingTimezone, ENT_QUOTES, 'UTF-8'); ?>">
					<button type="submit" aria-label="<?php echo ($currentLanguage === 'english') ? 'Save profile' : 'சுயவிவரத்தைச் சேமி'; ?>" title="<?php echo ($currentLanguage === 'english') ? 'Save profile' : 'சுயவிவரத்தைச் சேமி'; ?>"><i class="fa fa-floppy-o" aria-hidden="true"></i></button>
				</form>
				<?php } ?>
				</section>
		</div>
		<?php renderLanguageSwitch($currentLanguage); ?>
	</header>

	<section class="daybar" data-panchang-place="<?php echo htmlspecialchars($landingPanchangPlace, ENT_QUOTES, 'UTF-8'); ?>" aria-label="<?php echo htmlspecialchars(__('panchang_summary'), ENT_QUOTES, 'UTF-8'); ?>">
		<section class="daybar-hero" aria-label="<?php echo ($currentLanguage === 'english') ? 'Date and day' : 'தேதி மற்றும் கிழமை'; ?>">
			<div class="daybar-calendar-date">
				<strong class="daybar-day" data-panchang-value="gregorian_day">...</strong>
				<span class="daybar-month-year" data-panchang-value="gregorian_month_year">...</span>
			</div>
			<div class="daybar-primary-details">
				<div class="daybar-weekday-row">
					<strong class="daybar-weekday" data-panchang-value="weekday">...</strong>
					<div class="daybar-location">
						<i class="fa fa-map-marker" aria-hidden="true"></i>
						<span data-panchang-value="place"><?php echo htmlspecialchars($landingPlace !== '' ? $landingPlace : 'Chennai', ENT_QUOTES, 'UTF-8'); ?></span>
					</div>
				</div>
				<span class="daybar-tamil-date" data-panchang-value="tamil_date">...</span>
				<div class="daybar-sun-times">
					<span class="daybar-sun-pill"><i class="fa fa-sun-o" aria-hidden="true"></i><span><?php echo htmlspecialchars(__('sunrise'), ENT_QUOTES, 'UTF-8'); ?></span><strong data-panchang-value="sunrise">...</strong></span>
					<span class="daybar-sun-pill"><i class="fa fa-moon-o" aria-hidden="true"></i><span><?php echo htmlspecialchars(__('sunset'), ENT_QUOTES, 'UTF-8'); ?></span><strong data-panchang-value="sunset">...</strong></span>
				</div>
			</div>
		</section>
		<div class="daybar-limbs" role="list" aria-label="<?php echo ($currentLanguage === 'english') ? 'Panchang elements' : 'பஞ்சாங்க அங்கங்கள்'; ?>">
			<?php foreach ($landingPanchang as $panchangItem) { ?>
			<div class="daybar-limb" role="listitem">
				<span><?php echo htmlspecialchars($panchangItem[0], ENT_QUOTES, 'UTF-8'); ?></span>
				<strong data-panchang-value="<?php echo htmlspecialchars($panchangItem[2], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($panchangItem[1], ENT_QUOTES, 'UTF-8'); ?></strong>
			</div>
			<?php } ?>
		</div>
		<details class="daybar-inauspicious">
			<summary class="daybar-section-heading">
				<strong><?php echo htmlspecialchars(__('inauspicious_times'), ENT_QUOTES, 'UTF-8'); ?></strong>
				<span class="daybar-period-status" data-panchang-value="period_status">...</span>
				<span class="daybar-hora-inline">
					<span><?php echo htmlspecialchars(__('current_horai'), ENT_QUOTES, 'UTF-8'); ?></span>
					<strong data-panchang-value="hora_name">...</strong>
					<time data-panchang-value="hora_time">...</time>
				</span>
				<span class="daybar-disclosure-icon" aria-hidden="true"></span>
			</summary>
			<div class="daybar-inauspicious-content">
				<div class="daybar-periods">
					<?php foreach ($landingInauspiciousPeriods as $periodItem) { ?>
					<div class="daybar-period" data-period-key="<?php echo htmlspecialchars($periodItem[1], ENT_QUOTES, 'UTF-8'); ?>">
						<strong><?php echo htmlspecialchars($periodItem[0], ENT_QUOTES, 'UTF-8'); ?></strong>
						<span data-panchang-value="<?php echo htmlspecialchars($periodItem[1], ENT_QUOTES, 'UTF-8'); ?>">...</span>
					</div>
					<?php } ?>
				</div>
			</div>
		</details>
	</section>

	<a class="daybar-personal" href="star_thaara_palan.php">
		<span class="daybar-personal-icon"><i class="fa fa-star-o" aria-hidden="true"></i></span>
		<span class="daybar-personal-copy">
			<small><?php echo ($currentLanguage === 'english') ? 'For your star today' : 'உங்கள் நட்சத்திரத்திற்கு இன்று'; ?></small>
			<strong><?php echo ($currentLanguage === 'english') ? 'View Tara Balam and Chandrashtama' : 'தாராபலம், சந்திராஷ்டமம் பார்க்க'; ?></strong>
		</span>
		<i class="fa fa-chevron-right daybar-personal-chevron" aria-hidden="true"></i>
	</a>

	<nav class="quick-links" aria-label="<?php echo ($currentLanguage === 'english') ? 'Quick links' : 'விரைவு இணைப்புகள்'; ?>">
		<a href="Jamakol_input.php"><i class="fa fa-th" aria-hidden="true"></i><?php echo htmlspecialchars(__('jamakol'), ENT_QUOTES, 'UTF-8'); ?></a>
		<a href="Matching_input.php"><i class="fa fa-link" aria-hidden="true"></i><?php echo htmlspecialchars(__('marriage_matching'), ENT_QUOTES, 'UTF-8'); ?></a>
		<a href="star_thaara_palan.php"><i class="fa fa-star-o" aria-hidden="true"></i><?php echo htmlspecialchars(__('tara_palan'), ENT_QUOTES, 'UTF-8'); ?></a>
		<a href="raasi_kaaragam.php"><i class="fa fa-compass" aria-hidden="true"></i><?php echo ($currentLanguage === 'english') ? 'Rasi Kaaragam' : 'ராசி காரகம்'; ?></a>
	</nav>

	<?php if (!isFreeUser($_SESSION['user_type'])) { ?>
	<div class="deck">
		<a class="card" href="Jamakol_input.php"><img class="card-image" src="images/jamakol.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('jamakol'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[0], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="new_panchang.php"><img class="card-image" src="images/panchang.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('panchangam'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[1], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="numerology.php"><img class="card-image" src="images/numerology.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('numerology'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[2], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="Matching_input.php"><img class="card-image" src="images/matching.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('marriage_matching'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[3], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="Birth_input.php"><img class="card-image" src="images/birth.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('birth_horoscope'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[4], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="star_thaara_palan.php"><img class="card-image" src="images/thaara_palan.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('tara_palan'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[5], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="hora.php"><img class="card-image" src="images/hora.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('horai'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[6], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="prasannam_home.php"><img class="card-image" src="images/prasannam.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('prasannam'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[7], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
		<a class="card" href="planet_kaaragam.php"><img class="card-image" src="images/planet_kaaragam.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('planet_indicator'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[8], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
	</div>
	<?php } else { ?>
	<section class="live-card">
		<h2 class="live-title">தற்போதைய ஜாமக்கோள்</h2>
		<div id="msg">Please wait, jamakol chart is loading</div>
		<div class="current-jama" id="current-jama">
			<?php include "live_jamakol_chart.php"; ?>
		</div>
	</section>
	<?php } ?>
	<nav class="bottom-nav" aria-label="<?php echo ($currentLanguage === 'english') ? 'Main navigation' : 'முதன்மை வழிசெலுத்தல்'; ?>">
		<a href="new_panchang.php" aria-label="<?php echo ($currentLanguage === 'english') ? 'Panchangam' : 'பஞ்சாங்கம்'; ?>"><i class="fa fa-calendar" aria-hidden="true"></i></a>
		<a href="star_thaara_palan.php" aria-label="<?php echo ($currentLanguage === 'english') ? 'Tara Balam' : 'தாரா பலன்'; ?>"><i class="fa fa-star-o" aria-hidden="true"></i></a>
		<a href="hora.php" aria-label="<?php echo ($currentLanguage === 'english') ? 'Hora' : 'ஹோரை'; ?>"><i class="fa fa-clock-o" aria-hidden="true"></i></a>
		<?php if (strcmp($_SESSION['user_type'], ADMIN) == 0 || strcmp($_SESSION['user_type'], SUPER) == 0) { ?>
		<a href="admin.php" aria-label="<?php echo ($currentLanguage === 'english') ? 'Settings' : 'அமைப்புகள்'; ?>" title="<?php echo ($currentLanguage === 'english') ? 'Settings' : 'அமைப்புகள்'; ?>"><i class="fa fa-cog" aria-hidden="true"></i></a>
		<?php } ?>
	</nav>
	<footer class="site-footer">Developed &amp; Maintained by PNK Astro Labs</footer>
</main>

<?php if (isset($_GET['profile_saved'])) { ?>
<div class="profile-toast" id="profileToast" role="status" aria-live="polite"><?php echo ($currentLanguage === 'english') ? 'Your profile has been saved.' : 'உங்கள் சுயவிவரம் சேமிக்கப்பட்டது.'; ?></div>
<?php } ?>

<script>
function toggleProfilePanel(event) {
	event.stopPropagation();
	var panel = document.getElementById('profilePanel');
	var button = document.getElementById('profileTrigger');
	panel.hidden = !panel.hidden;
	button.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
}
function toggleProfileEdit() {
	var form = document.getElementById('profileLocationForm');
	var button = document.getElementById('profileEditToggle');
	form.hidden = !form.hidden;
	button.setAttribute('aria-expanded', form.hidden ? 'false' : 'true');
	var label = form.hidden ? button.getAttribute('data-edit-label') : button.getAttribute('data-cancel-label');
	button.setAttribute('aria-label', label);
	button.title = label;
	button.querySelector('i').className = form.hidden ? 'fa fa-pencil' : 'fa fa-times';
}
function copyProfileKey(button) {
	var key = document.getElementById('profileKeyValue');
	if (!key) return;

	function showCopyResult(success) {
		var icon = button.querySelector('i');
		var label = success ? button.getAttribute('data-copied-label') : button.getAttribute('data-failed-label');
		icon.className = success ? 'fa fa-check' : 'fa fa-exclamation-triangle';
		button.setAttribute('aria-label', label);
		button.title = label;
		window.setTimeout(function() {
			icon.className = 'fa fa-copy';
			label = button.getAttribute('data-copy-label');
			button.setAttribute('aria-label', label);
			button.title = label;
		}, 1800);
	}

	function copyWithSelection() {
		var input = document.createElement('textarea');
		input.value = key.textContent;
		input.setAttribute('readonly', '');
		input.style.position = 'fixed';
		input.style.opacity = '0';
		document.body.appendChild(input);
		input.select();
		var copied = false;
		try {
			copied = document.execCommand('copy');
		} catch (error) {
			console.error(error);
		}
		document.body.removeChild(input);
		return copied;
	}

	if (navigator.clipboard && navigator.clipboard.writeText) {
		navigator.clipboard.writeText(key.textContent).then(function() {
			showCopyResult(true);
		}).catch(function(error) {
			console.error(error);
			showCopyResult(copyWithSelection());
		});
		return;
	}

	showCopyResult(copyWithSelection());
}
document.getElementById('profileTrigger').addEventListener('click', toggleProfilePanel);
document.addEventListener('click', function(event) {
	var profilePanel = document.getElementById('profilePanel');
	var profileTrigger = document.getElementById('profileTrigger');
	if (profilePanel && !profilePanel.hidden && !profilePanel.contains(event.target) && !profileTrigger.contains(event.target)) {
		profilePanel.hidden = true;
		profileTrigger.setAttribute('aria-expanded', 'false');
	}
});
document.addEventListener('DOMContentLoaded', function() {
	initStars();
	loadLandingPanchang();
	initProfileLocationForm();
	var toast = document.getElementById('profileToast');
	if (toast) {
		toast.hidden = false;
		window.requestAnimationFrame(function() {
			toast.classList.add('is-visible');
		});
		window.setTimeout(function() {
			toast.classList.remove('is-visible');
			window.setTimeout(function() {
				toast.hidden = true;
			}, 200);
		}, 3000);
		var currentUrl = new URL(window.location.href);
		currentUrl.searchParams.delete('profile_saved');
		window.history.replaceState(null, document.title, currentUrl.pathname + currentUrl.search + currentUrl.hash);
	}
});
function readJsonResponse(response) {
	return response.text().then(function(body) {
		var data;
		try {
			data = JSON.parse(body);
		} catch (error) {
			throw new Error('Expected JSON but received HTTP ' + response.status + ': ' + body.slice(0, 240));
		}
		if (!response.ok) {
			throw new Error(data.error || 'Request failed with status ' + response.status);
		}
		return data;
	});
}
function initProfileLocationForm() {
	var form = document.getElementById('profileLocationForm');
	if (!form) return;

	var placeInput = document.getElementById('profilePlace');
	var timezoneInput = document.getElementById('profileTimezone');
	var results = document.getElementById('profilePlaceResults');
	var searchTimer;
	var latestSearch = 0;

	placeInput.addEventListener('input', function() {
		timezoneInput.value = '';
		placeInput.setCustomValidity('');
		results.textContent = '';
		window.clearTimeout(searchTimer);
		var searchId = ++latestSearch;
		var term = placeInput.value.trim();
		if (term.length < 3) return;

		searchTimer = window.setTimeout(function() {
			fetch('backend-search.php?format=json&term=' + encodeURIComponent(term), {
				headers: { 'X-Requested-With': 'XMLHttpRequest' }
			})
				.then(readJsonResponse)
				.then(function(places) {
					if (searchId !== latestSearch) return;
					if (!Array.isArray(places) || places.length === 0) {
						results.textContent = <?php echo json_encode(($currentLanguage === 'english') ? 'No matching places found.' : 'பொருந்தும் இடங்கள் இல்லை.', JSON_UNESCAPED_UNICODE); ?>;
						return;
					}

					places.forEach(function(place) {
						var option = document.createElement('button');
						option.type = 'button';
						option.setAttribute('role', 'option');
						option.textContent = place.place + ', ' + place.country + ' (' + place.timezone + ')';
						option.addEventListener('click', function(event) {
							event.stopPropagation();
							placeInput.value = place.place;
							timezoneInput.value = place.timezone;
							placeInput.setCustomValidity('');
							results.textContent = '';
						});
						results.appendChild(option);
					});
				})
				.catch(function(error) {
					if (searchId !== latestSearch) return;
					results.textContent = <?php echo json_encode(($currentLanguage === 'english') ? 'Unable to search places. Please try again.' : 'இடங்களைத் தேட முடியவில்லை. மீண்டும் முயற்சிக்கவும்.', JSON_UNESCAPED_UNICODE); ?>;
					console.error(error);
				});
		}, 250);
	});

	form.addEventListener('submit', function(event) {
		if (!timezoneInput.value) {
			event.preventDefault();
			placeInput.setCustomValidity(<?php echo json_encode(($currentLanguage === 'english') ? 'Select a city from the suggestions.' : 'பரிந்துரைகளிலிருந்து நகரத்தைத் தேர்ந்தெடுக்கவும்.', JSON_UNESCAPED_UNICODE); ?>);
			placeInput.reportValidity();
			return;
		}
		placeInput.setCustomValidity('');
	});
}
function loadLandingPanchang() {
	var values = document.querySelectorAll('[data-panchang-value]');
	var placeCookie = document.cookie.match(/(?:^|; )place=([^;]*)/);
	var daybar = document.querySelector('.daybar');
	var savedPlace = daybar ? daybar.getAttribute('data-panchang-place') : '';
	var place = savedPlace || (placeCookie ? decodeURIComponent(placeCookie[1]) : 'Chennai|Asia/Kolkata');
	var placeParts = place.split('|');
	var timeZone = placeParts[1] || 'Asia/Kolkata';
	place = placeParts[0] + '|' + timeZone;
	var now = new Date();
	var dateParts = {};
	var timeParts = {};

	try {
		new Intl.DateTimeFormat('en', {
			timeZone: timeZone,
			year: 'numeric',
			month: '2-digit',
			day: '2-digit'
		}).formatToParts(now).forEach(function(part) {
			dateParts[part.type] = part.value;
		});
		new Intl.DateTimeFormat('en', {
			timeZone: timeZone,
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
			hourCycle: 'h23'
		}).formatToParts(now).forEach(function(part) {
			timeParts[part.type] = part.value;
		});
	} catch (error) {
		dateParts = {
			year: now.getFullYear(),
			month: String(now.getMonth() + 1).padStart(2, '0'),
			day: String(now.getDate()).padStart(2, '0')
		};
		timeParts = {
			hour: String(now.getHours()).padStart(2, '0'),
			minute: String(now.getMinutes()).padStart(2, '0'),
			second: String(now.getSeconds()).padStart(2, '0')
		};
	}

	var date = dateParts.year + '-' + dateParts.month + '-' + dateParts.day;
	var time = timeParts.hour + ':' + timeParts.minute + ':' + timeParts.second;
	var query = '?format=json&place=' + encodeURIComponent(place) +
		'&date=' + encodeURIComponent(date) + '&time=' + encodeURIComponent(time);

	fetch('new_panchang_api.php' + query, {
		headers: { 'X-Requested-With': 'XMLHttpRequest' }
	})
		.then(readJsonResponse)
		.then(function(data) {
			values.forEach(function(element) {
				if (Object.prototype.hasOwnProperty.call(data, element.getAttribute('data-panchang-value'))) {
					element.textContent = data[element.getAttribute('data-panchang-value')];
				}
			});
			var periodStatus = document.querySelector('.daybar-period-status');
			if (periodStatus) {
				periodStatus.className = 'daybar-period-status is-' + data.period_status_type;
			}
			document.querySelectorAll('.daybar-period').forEach(function(period) {
				period.classList.remove('is-active', 'is-upcoming');
				if (period.getAttribute('data-period-key') === data.highlighted_period) {
					period.classList.add('is-' + data.period_status_type);
				}
			});
		})
		.catch(function(error) {
			var unavailable = <?php echo json_encode(($currentLanguage === 'english') ? 'Unavailable' : 'கிடைக்கவில்லை', JSON_UNESCAPED_UNICODE); ?>;
			values.forEach(function(element) {
				element.textContent = unavailable;
			});
			console.error(error);
		});
}
function initStars() {
	var canvas = document.getElementById('stars');
	var ctx = canvas.getContext('2d');
	var width, height, points = [];
	function resize() {
		width = canvas.width = window.innerWidth;
		height = canvas.height = window.innerHeight;
		var count = Math.min(60, Math.floor(width * height / 16000));
		points = Array.from({length:count}, function() {
			return {x:Math.random()*width, y:Math.random()*height, vx:(Math.random()-.5)*.2, vy:(Math.random()-.5)*.2, r:Math.random()+.4};
		});
	}
	function frame() {
		ctx.clearRect(0, 0, width, height);
		points.forEach(function(point) {
			point.x += point.vx;
			point.y += point.vy;
			if (point.x < 0 || point.x > width) point.vx *= -1;
			if (point.y < 0 || point.y > height) point.vy *= -1;
		});
		for (var i = 0; i < points.length; i++) {
			for (var j = i + 1; j < points.length; j++) {
				var dx = points[i].x - points[j].x;
				var dy = points[i].y - points[j].y;
				var distance = Math.sqrt(dx * dx + dy * dy);
				if (distance < 130) {
					var opacity = (1 - distance / 130) * .32;
					ctx.strokeStyle = 'rgba(255,190,225,' + opacity.toFixed(3) + ')';
					ctx.lineWidth = .8;
					ctx.beginPath();
					ctx.moveTo(points[i].x, points[i].y);
					ctx.lineTo(points[j].x, points[j].y);
					ctx.stroke();
				}
			}
		}
		points.forEach(function(point) {
			ctx.beginPath();
			ctx.arc(point.x, point.y, point.r, 0, Math.PI * 2);
			ctx.fillStyle = 'rgba(255,236,248,.85)';
			ctx.fill();
		});
		requestAnimationFrame(frame);
	}
	resize();
	window.addEventListener('resize', resize);
	frame();
}
</script>
</body>

</html>