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
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">	

    <title>DSJV app</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>

    
	<!-- <div id="google_translate_element"></div>
	<script type="text/javascript">
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({pageLanguage: 'ta', includedLanguages: 'en,hi,te,kn,ml', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
	}
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->
    <script src="script.js"?<?php echo date('ymdhis'); ?>></script>
	<script src="common.js"?<?php echo date('ymdhis'); ?>></script>
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

			var html_str = '<div class="table-container">';
			html_str += '<button class="close-btn" onclick="closeTable()">X</button>';
			html_str += '<div class="scrollable-table">';
			html_str += '<center><div class="divTable blueTable" style="width:100%;height:100%">';
			html_str += '<div class="divTableRow"><div class="divExpiryAlertHeading"><BR><BR><SPAN style="color:red; font-size: 26px">IMPORTANT ALERT</SPAN><BR><BR>' + message_tam + '<br><br>' + message + '</div></div>';
			html_str += '</div></div></div>';

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

<body onLoad="init();">
<?php if (!empty($announcement_msg)) { ?>
<div id="announcementOverlay" onclick="this.style.display='none'" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);color:#fff;display:flex;align-items:center;justify-content:center;z-index:9999;font-size:1.2em;text-align:center;cursor:pointer;">
	<div onclick="event.stopPropagation();" style="background:rgba(253, 216, 7, 0.92);padding:16px 22px;border-radius:999px;box-shadow:0 2px 12px #000;max-width:90vw;max-height:80vh;overflow:auto;color:#1a237e;font-weight:700;cursor:default;line-height:1.5;">
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
<!-- <div id="loader" class="center"></div> -->
<div class="expiryAlertDiv" id="expiryAlertDiv"></div>

<div class="title"></div>

		<div class="column-menu">
			<span class="notranslate">
				<div class='desktop'>
					<?php
						if(!isLogged()){
							echo <<<EOL
							<button class='menu' onClick='window.open("new_loginPage.php","_self")'>Login</button>
EOL;
						}
						else
						{
							displayUserInfo($_SESSION['userinfo']);
							include "indexMenu.php";
						}
				echo "</div>";
				echo "<div class='mobile'>";
						if(!isLogged()){
							//$key = $_GET['key'];
							echo <<<EOL
							<div class="dropdown">
								<button class="dropbtn"><a href="new_registration_mobile.php?key=$key" target="_self">Registration</a></button>
							</div>
EOL;
						}
						else
						{
							include "indexMenu_mobile.php";
							//include "setDefaultHome.php";
						}
				echo "</div>";
					?>
			</span>
		</div>

		<div class="column-body" id='colbody'>
		
		<!-- <form>
			<input type="radio" id="jamakol" name="defaultHome" value=1 style='width:auto' />
			<label class='divBirthInputLable' for="jamakol" style='width:auto'>Jamakol</label>
			<input type="radio" id="Panchang" name="defaultHome" style='width:auto' value=2 />
			<label class='divBirthInputLable' for="Panchang" style='width:auto'>Panchang</label>
			<button class='actionBtn' onClick="handleSelection()">Set Home</button>
		</form> -->
		
		<?php
		if(!isFreeUser($_SESSION['user_type'])){
			echo <<<EOL
			
			<!-- <P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">தற்போதய  ஜாமக்கோள்</P>
			<DIV id='msg'>Please wait, jamakol chart is loading</DIV> 
			<div class='current-jama' id='current-jama' onClick='window.open("Jamakol_input.php","_self");' style='cursor: pointer;margin-top:-25px'> -->
EOL;
				//include "live_jamakol_chart.php";
				include "home_page.php";
				
			//echo <<<EOL
			//	</div>
//EOL;
		}
		else{
			echo <<<EOL
			
			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">தற்போதய  ஜாமக்கோள்</P>
			<DIV id='msg'>Please wait, jamakol chart is loading</DIV>
			<div class='current-jama' id='current-jama' style='cursor: pointer;'>
EOL;

				echo '<div style="margin-top:auto">';
					include "live_jamakol_chart.php";
				echo '</div>';
			echo '</div>';

		}
		?>

		</div>
	</div>
		
</body>

</html>