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
	$userInfo = getUserInfo_mobile($_COOKIES['key']);
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
$landingTamilMonths = array(
	1 => 'ஜனவரி', 2 => 'பிப்ரவரி', 3 => 'மார்ச்', 4 => 'ஏப்ரல்',
	5 => 'மே', 6 => 'ஜூன்', 7 => 'ஜூலை', 8 => 'ஆகஸ்ட்',
	9 => 'செப்டம்பர்', 10 => 'அக்டோபர்', 11 => 'நவம்பர்', 12 => 'டிசம்பர்'
);
$landingNow = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$landingTodayLabel = ($currentLanguage === 'english')
	? 'Today, ' . $landingNow->format('j F Y') . '.'
	: 'இன்று, ' . $landingNow->format('j') . ' ' . $landingTamilMonths[(int)$landingNow->format('n')] . ' ' . $landingNow->format('Y') . '.';
$landingPanchang = ($currentLanguage === 'english')
	? array(
		array(__('weekday'), 'Saturday'),
		array(__('thithi'), 'Trayodashi'),
		array(__('nakshatra'), 'Uttara Ashadha'),
		array(__('yogam'), 'Vyaghata'),
		array(__('karanam'), 'Vanija')
	)
	: array(
		array(__('weekday'), 'சனி'),
		array(__('thithi'), 'திரயோதசி'),
		array(__('nakshatra'), 'உத்திராடம்'),
		array(__('yogam'), 'வ்யாகாதம்'),
		array(__('karanam'), 'வனிஜை')
	);
$landingIntro = ($currentLanguage === 'english')
	? 'Jamakol, horoscopes, matching, and hora. Choose a tool to get started.'
	: 'ஜாமகோல், ஜாதகம், பொருத்தம், ஹோரை. தேவையான கருவியைத் தேர்ந்தெடுங்கள்.';
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
} catch (Exception $e) {
	// fail silently
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
	<header class="topbar">
		<div class="brand"><i class="fa fa-th" aria-hidden="true"></i><span class="brand-name">DSJV</span></div>
		<div class="dock">
			<div class="menu-wrap">
				<button class="dock-btn" onclick="toggleMenu(event)" aria-label="Menu" aria-expanded="false"><i class="fa fa-bars"></i></button>
				<div class="dropdown-panel" id="menuPanel">
					<div class="notranslate">
						<div class="desktop">
							<?php
							if (!isLogged()) {
								echo '<button class="menu" onclick="window.open(\'new_loginPage.php\',\'_self\')">Login</button>';
							} else {
								displayUserInfo($_SESSION['userinfo']);
								$hideIndexMenuLanguageToggle = true;
								include "indexMenu.php";
								unset($hideIndexMenuLanguageToggle);
							}
							?>
						</div>
						<div class="mobile">
							<?php
							if (!isLogged()) {
								echo '<a class="mobile-registration" href="new_registration_mobile.php?key=' . rawurlencode($key) . '">Registration</a>';
							} else {
								include "indexMenu_mobile.php";
							}
							?>
						</div>
						<?php
						$currentLang = isset($_COOKIE['language']) ? strtolower($_COOKIE['language']) : 'tamil';
						$languageLabel = ($currentLang === 'english') ? 'EN' : 'தமிழ்';
						?>
						<div class="lang-selector">
							<button class="lang-btn" type="button" onclick="toggleLanguage()" aria-label="Toggle language">
								<i class="fa fa-globe" aria-hidden="true"></i>
								<span id="currentLangLabel"><?php echo htmlspecialchars($languageLabel, ENT_QUOTES, 'UTF-8'); ?></span>
							</button>
						</div>
					</div>
				</div>
			</div>
			<button class="dock-btn language-toggle" type="button" onclick="toggleLanguage()" aria-label="Toggle language" title="Language / மொழி">
				<span class="lang-options" aria-hidden="true">
					<span class="lang-option<?php echo ($currentLanguage === 'tamil') ? ' active' : ''; ?>" lang="ta">தமிழ்</span>
					<span class="lang-option<?php echo ($currentLanguage === 'english') ? ' active' : ''; ?>" lang="en">English</span>
				</span>
			</button>
			<?php if (strcmp($_SESSION['user_type'], ADMIN) == 0 || strcmp($_SESSION['user_type'], SUPER) == 0) { ?>
			<a class="dock-btn" href="admin.php" aria-label="Settings"><i class="fa fa-cog"></i></a>
			<?php } ?>
		</div>
	</header>

	<section class="daybar" aria-label="<?php echo ($currentLanguage === 'english') ? 'Today\'s Panchangam' : 'இன்றைய பஞ்சாங்கம்'; ?>">
		<div class="daybar-head">
			<span><?php echo htmlspecialchars($landingTodayLabel, ENT_QUOTES, 'UTF-8'); ?></span>
			<a href="new_panchang.php"><?php echo ($currentLanguage === 'english') ? 'Full Panchangam' : 'முழு பஞ்சாங்கம்'; ?></a>
		</div>
		<dl class="panchang-list">
			<?php foreach ($landingPanchang as $panchangItem) { ?>
			<div>
				<dt><?php echo htmlspecialchars($panchangItem[0], ENT_QUOTES, 'UTF-8'); ?></dt>
				<dd><?php echo htmlspecialchars($panchangItem[1], ENT_QUOTES, 'UTF-8'); ?></dd>
			</div>
			<?php } ?>
		</dl>
	</section>

	<section class="intro">
		<h1 class="headline"><?php echo htmlspecialchars(__('school_name'), ENT_QUOTES, 'UTF-8'); ?></h1>
		<p class="lede"><?php echo htmlspecialchars($landingIntro, ENT_QUOTES, 'UTF-8'); ?></p>
	</section>

	<div class="chips">
		<span class="chip">&#128241; <?php echo htmlspecialchars((string)$_SESSION['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
		<span class="chip">&#128197; <?php echo htmlspecialchars(date('d-m-Y', strtotime($expiry_date)), ENT_QUOTES, 'UTF-8'); ?></span>
	</div>

	<?php if (!isFreeUser($_SESSION['user_type'])) { ?>
	<div class="deck">
		<a class="card hero" href="Jamakol_input.php"><img class="card-image" src="images/jamakol.jpg" alt="" loading="lazy" decoding="async"><span class="card-content"><span class="card-label"><?php echo htmlspecialchars(__('jamakol'), ENT_QUOTES, 'UTF-8'); ?></span><span class="card-sub"><?php echo htmlspecialchars($landingToolDescriptions[0], ENT_QUOTES, 'UTF-8'); ?></span></span></a>
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
</main>

<script>
function toggleMenu(event) {
	event.stopPropagation();
	var panel = document.getElementById('menuPanel');
	var button = event.currentTarget;
	panel.classList.toggle('open');
	button.setAttribute('aria-expanded', panel.classList.contains('open') ? 'true' : 'false');
}
document.addEventListener('click', function(event) {
	var panel = document.getElementById('menuPanel');
	if (panel && !panel.contains(event.target)) {
		panel.classList.remove('open');
		var button = document.querySelector('.dock-btn[aria-label="Menu"]');
		if (button) button.setAttribute('aria-expanded', 'false');
	}
});
document.addEventListener('DOMContentLoaded', function() {
	initStars();
});
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