<?php session_start();
require "lib.php";

$devInfo = $_GET;

displayUserInfo($_SESSION['userinfo']);

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
	 
	<div id="google_translate_element"></div>
	<script type="text/javascript">
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'ta,hi,te,kn,ml', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
	}
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

	<script type="text/javascript" src="scrolloverflow.js"></script>
    <script src="script.js"></script>
    <script type="text/javascript">
	function init(){

	}
			
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

<div class='row'>
	<div class='column-menu'>
		<div class='desktop'>
		</div>
		<div class='mobile'>
			<?php displayDeviceInfo($devInfo); ?>
		</div>
	</div>
	<div class='column-body'>
		<div class="body">
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
			<div class="help_answer">As now you can take screenshot until PDF feature is enabled</div>
			<div class="help_question">3. Can I store my charts?</div>
			<div class="help_answer">Yes, you can use save button to store it</div>
			<div class="help_question">4. Can I use this app on multiple phones?</div>
			<div class="help_answer">No. Activated only for one phone</div>
			<div class="help_question">5. Can I use this app without internet/wifi/data connection?</div>
			<div class="help_answer">No. This requires internet connection</div>
			<div class="help_question">6. Will I get any future updates?</div>
			<div class="help_answer">Yes. New features will be available</div>
			<BR>
			<BR>
		</div>
	</div>	 
</div>

</body>

</html>