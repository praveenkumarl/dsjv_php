<?php
require_once __DIR__ . '/webview_only.php';
session_start();

$month = $_POST['month'];
$year = $_POST['year'];
$place = isset($_POST['place']) ? $_POST['place'] : $_COOKIE['panchang_place'];
$TZ = explode('|',$place)[1];

$monthName = getMonthName($month,1); //1-tamil

function getMonthName($month,$lang) //1 - tamil
{
	$name_tamil_arr = ["dummy","ஜனவரி", "பிப்ரவரி", "மார்ச்", "ஏப்ரல்", "மே", "ஜூன்", "ஜூலை", "ஆகஸ்ட்", "செப்டம்பர்", "அக்டோபர்", "நவம்பர்", "டிசம்பர்"];
	
	if($lang == 1){
		return $name_tamil_arr[$month];
	}
}

date_default_timezone_set($TZ);

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Astrology">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/onepage_panchang_style.css?<?php echo date('ymdhis'); ?>">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
	<script src="panchang.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="my_panchang.js?<?php echo date('ymdhis'); ?>"></script>
</head>
<body>

<div id='onepage' style="width: min-content">
<div class="header-parent">
  <div class="header-left"></div>
  <!-- <div class="header-left"><button class="actionBtn" onclick="window.open('gen_panchang_form.php','_self')">Back</button></div>  -->
  <div class="header-center"><div class='divTableTextOfficeName'>பஞ்சாங்கம் (<?php echo "$monthName/$year/$place"; ?>)</div></div>
  <!-- <a href='#' onClick='generatePDF();'>download</a> -->
  <div class="header-right"></div>
</div>
<script>

	var timezone = '<?php echo $TZ; ?>';
	//const year = 1976;
	//const month = 7;  //1 - jan
	const year = <?php echo $year; ?>;
	const month = <?php echo $month; ?>;  //1 - jan
	
	generatePanchang(month,year);
  
</script>

<div id="panchangContainer"></div>

<div class="PageFooter" style="width:-webkit-fill-available">தர்ம சாஸ்தா ஜோதிட வித்யாலயா & Periyanayaki Astro Solutions</div>
</body>
</html>
