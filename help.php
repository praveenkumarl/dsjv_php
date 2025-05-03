<?php session_start();
require "lib.php";

require_once 'vendor/autoload.php';  // if using Composer

use phpseclib3\Net\SSH2;

if (function_exists('exec')) {
    echo "exec() is enabled.";
} else {
    echo "exec() is disabled.";
}


$key = isset($_COOKIE['key']) ? $_COOKIE['key'] : $_GET['key'];

$userInfo = getUserInfo_mobile($key);
$_SESSION['user_type'] = $userInfo['user_type'];


$helpInfo_arr = ["device-id" => $key,
				"Source Platform" => $_SERVER['HTTP_SEC_CH_UA_PLATFORM'],
				"Memory content" => $_SERVER['HTTP_COOKIE'],
				"Requested key" => $_SERVER['QUERY_STRING']//,
				//"host" => $_SERVER['REQUEST_SCHEME'].'//'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']
				];

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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">	

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	
	 
	<div id="google_translate_element"></div>
	<script type="text/javascript">
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'ta,hi,te,kn,ml', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
	}
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script src="script.js"></script>
	<script src="tz.js"></script>
    <script type="text/javascript">
	
	function init(){
		
	}
	
	function getGeoLocation(){
		// Check if the browser supports the Geolocation API.
		if (navigator.geolocation) {
		  // Get the user's current location.
		  navigator.geolocation.getCurrentPosition(function(position) {
			// The success callback function.
			// This function is called if the browser is able to successfully determine the user's location.

			// Get the user's latitude and longitude coordinates.
			var latitude = position.coords.latitude;
			var longitude = position.coords.longitude;
			
			//latitude = 15.065746974104158;
			//longitude = 101.12441707669265;
			
			url='https://maps.google.com/maps?q='+latitude+','+longitude+'&output=svembed'+'&z=18';
			var html = '<iframe src='+url+'></iframe>';
			
			const tz = tzlookup(latitude,longitude); //include tz.js
			
			//alert("Lat="+latitude+" Long="+longitude+" TZ="+tz);
			
			$("#map").append(html); //embed map
			
			//window.open(url,'_blank');

			// Display the user's location on a map.
			// ...
		  }, function(error) {
			// The error callback function.
			// This function is called if the browser is unable to determine the user's location.

			// Handle the error.
			// ...
			alert(error.message);
		  });
		} else {
		  // The browser does not support the Geolocation API.
		  // Handle the error.
		  // ...
		  alert('Bwoser does not support Geolocation API');
		}
	}
			
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

<div class='column-menu'>
<?php displayUserInfo($userInfo);?>
	<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
	</div>
	<div class='mobile'>
		<?php include "helpMenu_mobile.php"; ?>
	</div>
</div>
<div class='column-body'>
	<?php displayHelpInfo($helpInfo_arr); ?>
	<BR><div = class="help_heading">Installation/setup procedure </div>
	<BR>
	<div style='text-align:left'>
		<H6>1. Install the app</H6>
		<H6>2. Register with your phone/name</H6>
		<H6>3. Once successfully registered, contact admin</H6>
		<H6>4. Admin will activate your id post all validations and confirm (this will work only on one phone)</H6>
		<H6>5. Close the app and reopen</H6>
	</div>
	<BR>
	<BR>
	<BR><div = class="help_heading">Frequently asked questions</div>
	<div class="help_question">1. What to do when there is issue?</div>
	<div class="help_answer">Contact admin with the screenshot of your help page</div>
	<div class="help_question">2. How to download charts?</div>
	<div class="help_answer">You can use download button wherever available</div>
	<div class="help_question">3. Can I store my charts?</div>
	<div class="help_answer">Yes, you can use save button to store it</div>
	<div class="help_question">4. Can I use this app on multiple phones?</div>
	<div class="help_answer">No. Activated only for one phone. If you install it on another phone, existing phone will not work</div>
	<div class="help_question">5. Can I use this app without internet/wifi/data connection?</div>
	<div class="help_answer">No. This requires internet connection</div>
	<div class="help_question">6. Will I get any future updates?</div>
	<div class="help_answer">Yes. New features will be available</div>
</div>	 
	<BR>
	<BR>

<center>
<div class='help_video_heading'>DSJV app - குருஜி Dr.சாஸ்தா ந.மணிகண்டராஜ் ஐயாவின் விளக்கம்</div>
<!-- <object width="100%" height="300" data="http://www.youtube.com/v/HE9tqAjKYZs" type="application/x-shockwave-flash"><param name="src" value="http://www.youtube.com/v/Ahg6qcgoay4" /></object> -->
<iframe width="350" height="195" src="https://www.youtube.com/embed/HE9tqAjKYZs?si=rfS5aPL1viQUuOAJ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV Mobile App-ன் பயன்களும், அம்சங்களும் - வாராவாரம் ஆரவாரம் 153வது நிகழ்ச்சி</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/TVAHUfLiEmc?si=8zKRVeN0BwsH-b_R" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV app பயன்பாட்டு காணொளிகள்</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/QfBBAE30sY0?si=9bR16OTnP6oaQB50" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App - வருடம் எவ்வாறு மாற்ற வேண்டும்</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/rC26eJzLIt0?si=QFHZjYVBRj5cyuGi" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App - பிறந்த தேதி மற்றும் நேரம் எவ்வாறு உள்ளீடு செய்வது</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/M-D-liXzH-0?si=IJeqtlgPUHm4twwD" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App - இடத்தை தேர்வு செய்யும் சரியான முறை</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/Lz08Ibk-7KY?si=K7dXtBGVmuE-euAc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App -  கிரக காரகம் எவ்வாறு பார்ப்பது</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/MkdHsVMvBWI?si=vJSY9T1h6gHyvqJA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App - மாத பஞ்சாங்கம் கணிக்கும் முறை</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/vbeL6cQO4vs?si=UKEmoWBgbUSI8RMX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
<div class='help_video_heading'>DSJV App - 9 Nov 2024 Numerology feature update</div>
<iframe width="350" height="195" src="https://www.youtube.com/embed/Nr7ikPlR_zY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
</center>
</body>
</html>