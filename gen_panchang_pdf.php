<?php
session_start();
require_once 'lib.php';

$month = isset($_POST['month']) ? (int)$_POST['month'] : (isset($_GET['month']) ? (int)$_GET['month'] : 1);
$year = isset($_POST['year']) ? (int)$_POST['year'] : (isset($_GET['year']) ? (int)$_GET['year'] : date('Y'));
$place = isset($_POST['place']) ? $_POST['place'] : (isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : '');
$TZ = 'Asia/Kolkata';
if ($place && strpos($place, '|') !== false) {
	$parts = explode('|', $place);
	if (isset($parts[1]) && $parts[1] !== '') $TZ = $parts[1];
}

// Determine language (POST > GET > SESSION > COOKIE) and initialize translations
$detected_lang = 'tamil';
if (isset($_POST['lang']) && $_POST['lang'] === 'english') {
	$detected_lang = 'english';
} elseif (isset($_GET['lang']) && $_GET['lang'] === 'english') {
	$detected_lang = 'english';
} elseif (isset($_SESSION['language'])) {
	$detected_lang = $_SESSION['language'];
} elseif (isset($_COOKIE['language'])) {
	$detected_lang = $_COOKIE['language'];
}
init_language($detected_lang);

// Month name via translation keys (greg_month_1..12)
$monthName = __('greg_month_' . (int)$month);

date_default_timezone_set($TZ);

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title><?php echo htmlspecialchars(__('panchangam') . ' - ' . $monthName . ' ' . $year, ENT_QUOTES); ?></title>
    <meta name="description" content="One stop website for Astrology">
	<meta name="pdf-exportable" content="true">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/onepage_panchang_style.css?<?php echo date('ymdhis'); ?>">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
	<script>
		var PDF_FILENAME = <?php echo json_encode('panchang_' . $month . '_' . $year . '.pdf'); ?>;
	</script>
	<script src="pdf_bridge.js?v=1" type="text/javascript"></script>
	<script src="panchang.js?<?php echo date('ymdhis'); ?>"></script>
	<script src="my_panchang.js?<?php echo date('ymdhis'); ?>"></script>
</head>
<body>

<div id='onepage' style="width: min-content">
<div class="header-parent">
  <div class="header-left"></div>
  <!-- <div class="header-left"><button class="actionBtn" onclick="window.open('gen_panchang_form.php','_self')">Back</button></div>  -->
	<div class="header-center"><div class='divTableTextOfficeName'><?php echo __("panchangam") . ' (' . htmlspecialchars($monthName, ENT_QUOTES) . '/' . htmlspecialchars($year, ENT_QUOTES) . '/' . htmlspecialchars($place, ENT_QUOTES) . ')'; ?></div></div>
  <!-- <a href='#' onClick='generatePDF();'>download</a> -->
  <div class="header-right"></div>
</div>
<script>

	var timezone = '<?php echo $TZ; ?>';
	//const year = 1976;
	//const month = 7;  //1 - jan
	const year = <?php echo $year; ?>;
	const month = <?php echo $month; ?>;  //1 - jan
	
<?php if (is_english()): ?>
// Override Tamil arrays with English equivalents for PDF when language is English
zn = ["Mesha","Vrushabha","Mithuna","Karkataka","Simha","Kanya","Tula","Vrushchika","Dhanu","Makara","Kumbha","Meena"];
wd = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
naks = ["Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardra","Punarvasu","Pushya","Ashlesha","Magha","Purva Phalguni","Uttara Phalguni","Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshta","Mula","Purva Ashadha","Uttara Ashadha","Shravana","Dhanishta","Shatabhisha","Purva Bhadrapada","Uttara Bhadrapada","Revathi"];
tith = ["Prathame","Thwithiya","Thrithiya","Chathurthi","Panchami","Sashti","Saptami","Ashtami","Navami","Dashami","Ekadashi","Dwadashi","Thrayodashi","Chaturdashi","Poornima","Prathame","Thwithiya","Thrithiya","Chathurthi","Panchami","Sashti","Saptami","Ashtami","Navami","Dashami","Ekadashi","Dwadashi","Thrayodashi","Chaturdashi","Amavasya"];
kar = ["Bava","Baalaava","Koulava","Thaithulai","Vathai","Karasai","Vanisai","Patharai","Sakuni","Sadhushpaadha","Naakava","Kimstukkina"];
yog = ["Vishkambham","Preethi","Aayushman","Saubhaghyam","Shobhanam","Adhikandam","Sugarmam","Thiruthi","Soolam","Kandam","Viruthi","Dhruvam","Vyaghatham","Harshanam","Vajram","Siddhi","Vyateepatham","Variyan","Parigham","Shivam","Siddham","Saadhyam","Shubham","Subram","Brahmiyam","Aindram","Vaidhriti"];
<?php endif; ?>

	// Localized label variables from server-side translations
	var L_DATE = <?php echo json_encode(__('date')); ?>;
	var L_WEEKDAY = <?php echo json_encode(__('weekday')); ?>;
	var L_NAKSHATRA = <?php echo json_encode(__('nakshatra')); ?>;
	var L_TITHI = <?php echo json_encode(__('thithi')); ?>;
	var L_KARANAM = <?php echo json_encode(__('karanam')); ?>;
	var L_YOGA = <?php echo json_encode(__('yogam')); ?>;
	var L_FROM = <?php echo json_encode(__('from')); ?>;
	var L_TO = <?php echo json_encode(__('to')); ?>;
	var L_AFTER = <?php echo json_encode(__('after')); ?>;
	var L_PAKSHA_SUK = <?php echo json_encode(__('paksha_suk')); ?>;
	var L_PAKSHA_KRI = <?php echo json_encode(__('paksha_kri')); ?>;
	var L_PAKSHA_SUK_ABBR = <?php echo json_encode(__('paksha_suk_abbr')); ?>;
	var L_PAKSHA_KRI_ABBR = <?php echo json_encode(__('paksha_kri_abbr')); ?>;
	var IS_ENGLISH = <?php echo is_english() ? 'true' : 'false'; ?>;

	generatePanchang(month,year);

  
</script>

<div id="panchangContainer"></div>

<div class="PageFooter" style="width:-webkit-fill-available">தர்ம சாஸ்தா ஜோதிட வித்யாலயா & Periyanayaki Astro Solutions</div>
</body>
</html>
