<!doctype html>
<?php
include_once "lib.php";
include_once "lang/lang.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;
$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;

$home = isset($_GET['home'])? "none" : "";
$flag = (int)isset($_GET['flag']) ? $_GET['flag'] : 0;

if(isset($_POST['place'])){
	$cookie_name = "panchang_place";
	$cookie_value = $_POST['place'];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 day
}

//$place = isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : "Chennai|Asia/Kolkata";

if (isset($_POST['place'])) {
    if (!isset($_COOKIE['panchang_place']) || strcmp($_COOKIE['panchang_place'], $_POST['place']) !== 0) {
        setcookie('panchang_place', $_POST['place'], time() + 86400, "/"); // Cookie valid for 1 day
    }
    $place = $_POST['place'];
} else {
    if (isset($_COOKIE['panchang_place'])) {
        $place = $_COOKIE['panchang_place'];
    } else {
        $place = "Chennai|Asia/Kolkata";
        setcookie('panchang_place', $place, time() + 86400, "/"); // Set cookie on first run
    }
}

// Handle language selection
// First, normalize any existing cookie to lowercase
if(isset($_COOKIE['language'])){
	$_COOKIE['language'] = strtolower($_COOKIE['language']);
}

if(isset($_POST['language'])){
	// Language from form POST
	$language = strtolower($_POST['language']);
	setcookie('language', $language, time() + (86400 * 365), "/"); 
}
elseif(isset($_COOKIE['language'])){
	// Language from cookie
	$language = $_COOKIE['language'];
}
else{
	// Default language
	$language = 'tamil';
}

// Debug: Show what we're working with
if($DEBUG) {
	echo "<!-- DEBUG: Language = $language -->";
	echo "<!-- DEBUG: Cookie = " . (isset($_COOKIE['language']) ? $_COOKIE['language'] : 'NOT SET') . " -->";
	echo "<!-- DEBUG: POST = " . (isset($_POST['language']) ? $_POST['language'] : 'NOT SET') . " -->";
}

$lang = isset($_POST["lang"]) ? $_POST["lang"] : "tamil";

// Initialize the language system
init_language($language);

debug("PLACE=".$place);

$city=strtok($place,"|");
$TZ=strtok("|");

date_default_timezone_set($TZ);

$conn="";
$lat="";
$lon="";
$gmt_offset="";
$tz_offset;
$today_sunrise="";
$today_sunset="";
$tomo_sunrise="";
$transit_today_sunrise="";
$transit_today_sunset="";
$transit_tomo_sunrise="";
$transit_prev_sunset="";
$sun_degree=0;
$moon_degree=0;
$sun_house=0;
$moon_house=0;
$transit_sun_degree=0;
$transit_sun_house=0;
$transit_moon_degree=0;
$transit_moon_house=0;
$ragu_house=-1;
$tr_ragu_house=-1;
$sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$sw_degree = array();
$tr_sw_degree = array();
$out_planet_degree = array();
$sw_dir = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_dir = array(null,null,null,null,null,null,null,null,null,null,null,null);
$dayofweek=-1;
$transit_dayofweek=-1;
$day=-1;
$transit_day=-1;
$output=array();
$lagna_house=null;
$lagna_degree=null;
$transit_lagna_house=null;
$transit_lagna_degree=null;
$cricketMenu=0;
$autoAnalysisMenu=0;
$planetDeg = array();
$planetDeg_transit = array();
$planetDeg_default = array();
$mandhi_degree=0;
$mandhi_degree_transit=0;
$panchang = array();
$g_nak_pad_idx = 0;
$nak_pad_idx = 0;
$dasa_balance = array(null,null,null); // years, months, days
$birth_dasa_athipathi="";
$g_dasaCurrent=array();
$current_dasa_bukthi=array();
$yogi_arr=array();
$country="";
$g_thithi_soonyam_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$full_sun_deg=0.00000;
$sun_moon_data=array();
$full_moon_deg=0.00000;
$g_nak="";
$g_thithi_idx=-1;
$g_yoga_idx=-1;
$g_nak_idx=-1;
$g_dasaBalance=array();
$g_dasaCurrent=array();
$g_kariNaal ="இல்லை";
$next_day="";
$g_padham=0;
$g_bhava_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);
$g_rasi = "";

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 


$chart_date = isset($_POST['nDate']) && $_POST['nDate'] !== ''
	? $_POST['nDate']
	: date("Y-m-d");

$conn = dbConnect($conn);
$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl,country FROM world_cities WHERE Place="."'".$city."'"." AND TZ="."'".$TZ."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$lat = $row["Latitude"];
		$lon = $row["Longitude"];
		$gmt_offset = $row["GMT_offset"];
		$gmt_offset_dst = $row["GMT_offset_dsl"];
		$country = $row["country"];
	}
} else {
	echo "0 results";
}

$defaultPanchangTime = getSunrise(strtotime($chart_date), floatval($lat), floatval($lon));
$requestedTime = isset($_POST['nTime']) ? trim((string)$_POST['nTime']) : '';
$requestedTimeMode = isset($_POST['time_mode']) ? $_POST['time_mode'] : '';
if (isset($_POST['nDate']) && $_POST['nDate'] === '') {
	$timeMode = 'sunrise';
} elseif ($requestedTimeMode === 'custom' && $requestedTime !== '') {
	$timeMode = 'custom';
} elseif ($requestedTimeMode === 'sunrise' || $requestedTime === '') {
	$timeMode = 'sunrise';
} else {
	$timeMode = 'custom';
}
$chart_time = $timeMode === 'custom' ? $requestedTime : $defaultPanchangTime;

echo $today_sunrise;

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));

$next_day=date("Y-m-d",strtotime($chart_date. ' + 1 days'));
$previous_day=date("Y-m-d",strtotime($chart_date. ' - 1 days'));

?>

<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>DSJV app</title>
    <meta name="description" content="One stop website for astrology">
	
	<!-- Critical CSS to prevent flash of desktop buttons on mobile -->
	<style>
		@media only screen and (max-width: 600px) {
			.btn-desktop, .btn-desktop-nav {
				display: none !important;
			}
			.mobile-dropdown, .mobile-nav-dropdown, .btn-mobile {
				display: inline-flex !important;
			}
		}
		@media only screen and (min-width: 601px) {
			.mobile-dropdown, .mobile-nav-dropdown, .btn-mobile {
				display: none !important;
			}
		}
	</style>

	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/landing-theme.css?<?php echo date('ymdhis'); ?>">
	<style>
		#mybody1 {
			min-height:100vh;
			padding-bottom:100px;
			background:var(--landing-background);
			color:var(--theme-text);
		}
		#mybody1 > .app-topbar {
			margin-top:env(safe-area-inset-top);
		}
		#mybody1 .panchang-bottom-nav {
			position:fixed;
			right:auto;
			bottom:calc(10px + env(safe-area-inset-bottom));
			left:50%;
			z-index:130;
			display:flex;
			gap:6px;
			width:max-content;
			max-width:calc(100vw - 24px);
			margin:0;
			padding:7px;
			transform:translateX(-50%);
			border:1px solid var(--theme-border-accent,rgba(255,214,230,.12));
			border-radius:22px;
			background:var(--theme-surface-panel-solid,rgba(42,22,64,.95));
			box-shadow:0 8px 26px rgba(0,0,0,.32);
			backdrop-filter:blur(14px);
			-webkit-backdrop-filter:blur(14px);
		}
		#mybody1 .panchang-bottom-nav a {
			display:grid;
			width:46px;
			height:42px;
			place-items:center;
			border-radius:15px;
			color:var(--theme-text-secondary,#c4afd2);
			font-size:17px;
			text-decoration:none;
			transition:background-color .18s ease,color .18s ease;
		}
		#mybody1 .panchang-bottom-nav button {
			display:grid;
			width:46px;
			height:42px;
			place-items:center;
			padding:0;
			border:0;
			border-radius:15px;
			background:transparent;
			color:var(--theme-text-secondary,#c4afd2);
			font-size:17px;
			cursor:pointer;
			transition:background-color .18s ease,color .18s ease;
		}
		#mybody1 .panchang-bottom-nav a.is-active {
			background:var(--client-accent);
			color:var(--client-on-accent);
		}
		#mybody1 .panchang-bottom-nav a:focus-visible {
			outline:2px solid var(--client-accent);
			outline-offset:2px;
		}
		#mybody1 .panchang-bottom-nav button:focus-visible {
			outline:2px solid var(--client-accent);
			outline-offset:2px;
		}
		#mybody1 .column-body {
			display:block;
			float:none;
			width:calc(100% - 24px);
			max-width:1080px;
			margin:0 auto 20px;
			padding:16px;
			border:1px solid var(--theme-glass-border);
			border-radius:24px;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 16px 42px var(--theme-shadow-panel),inset 0 1px var(--theme-glass-highlight);
			color:var(--theme-text);
		}
		#divOrigContainer .divTable.blueTable {
			width:min(100%,760px)!important;
			max-width:100%;
			height:auto!important;
			margin:12px auto;
			padding:10px!important;
			border:1px solid var(--theme-border-strong)!important;
			border-radius:20px;
			background:var(--theme-surface-chart)!important;
			box-shadow:0 14px 36px var(--theme-shadow-chart),inset 0 1px var(--theme-highlight-inset);
			color:var(--theme-text);
		}
		#panchangDetailsHeading .divTableRow {
			display:block;
			background:transparent!important;
		}
		#panchangDetailsHeading {
			display:none!important;
		}
		#panchangDetailsHeading .divPanchangHeading {
			display:block;
			height:auto;
			padding:4px;
			border:0;
			background:transparent!important;
		}
		#panchangDetailsHeading h6, #transitChartHeading h6 {
			margin:2px;
			color:var(--theme-text-accent)!important;
			font-size:clamp(19px,4vw,24px);
			font-weight:800;
		}
		#vivahaChakram {
			display:block!important;
			width:min(100%,var(--chart-width))!important;
			max-width:100%;
			box-sizing:border-box;
			margin:18px auto!important;
			padding:16px!important;
			border:1px solid var(--theme-border-accent)!important;
			border-radius:16px!important;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 12px 30px rgba(48,22,44,.12),inset 0 1px rgba(255,255,255,.9);
			color:var(--theme-text)!important;
			font-size:14px!important;
			font-weight:400!important;
			line-height:1.45;
			text-align:left;
		}
		#vivahaChakram .vivaha-card-title {
			margin:0 0 12px;
			color:var(--theme-text-accent-strong)!important;
			font-size:17px;
			font-weight:800;
			text-align:center;
		}
		#vivahaChakram .vivagaResultRed,
		#vivahaChakram .vivagaResultGreen {
			display:block;
			height:auto!important;
			margin:0 0 12px!important;
			padding:9px 12px;
			border-radius:10px;
			font-size:13px;
			font-weight:700;
			line-height:1.4;
			text-align:center;
		}
		#vivahaChakram .vivagaResultRed {
			border:1px solid rgba(184,56,89,.24);
			background:var(--theme-danger-soft)!important;
			color:var(--theme-text-error-strong)!important;
		}
		#vivahaChakram .vivagaResultGreen {
			border:1px solid rgba(70,131,84,.24);
			background:var(--theme-success-soft)!important;
			color:var(--theme-text-success)!important;
		}
		#vivahaChakram .vivaha-star-row {
			display:flex;
			align-items:center;
			justify-content:space-between;
			gap:10px;
			margin-top:7px;
			padding:8px 10px;
			border:1px solid var(--theme-border-subtle);
			border-radius:10px;
			background:var(--theme-surface-cell);
			font-size:13px!important;
			text-align:left;
		}
		#vivahaChakram .vivaha-star-label {
			color:var(--theme-text-secondary);
			font-weight:600;
		}
		#vivahaChakram .vivaha-star-name {
			color:var(--theme-text-accent-strong);
			font-weight:700;
			text-align:right;
			overflow-wrap:anywhere;
		}
		#panchangDetails {
			display:block;
			padding:10px!important;
		}
		#panchangDetails .divTableRow {
			display:grid;
			grid-template-columns:minmax(108px,.8fr) minmax(0,1.2fr);
			align-items:stretch;
			gap:8px;
			padding:4px 0;
			border-bottom:1px solid var(--theme-border-subtle);
		}
		#panchangDetails .divTableRow:last-child {
			border-bottom:0;
		}
		#panchangDetails .divPanchangHeading,
		#panchangDetails .divPanchangHeadingRed {
			display:block;
			width:auto;
			height:auto;
			padding:10px 12px;
			border:0;
			border-radius:12px;
			background:transparent!important;
			color:var(--theme-text-secondary);
			font-size:clamp(13px,.75vw + 10px,15px);
			font-weight:700;
			line-height:1.4;
			text-align:left;
		}
		#panchangDetails .divPanchangHeadingRed {
			color:var(--theme-text-error-strong);
		}
		#panchangDetails .divPanchang {
			display:block;
			width:auto;
			height:auto;
			min-width:0;
			padding:10px 12px;
			border:1px solid var(--theme-border-soft);
			border-radius:12px;
			background:var(--theme-surface-cell);
			color:var(--theme-text);
			font-size:clamp(14px,.75vw + 11px,16px);
			font-weight:600;
			line-height:1.45;
			overflow-wrap:anywhere;
		}
		#panchangDetails .divPanchang[onclick] {
			position:relative;
			padding-right:38px;
			border-color:var(--theme-border-accent);
			background:var(--theme-accent-soft);
			color:var(--theme-text-accent)!important;
			cursor:pointer;
			box-shadow:inset 3px 0 0 var(--theme-text-accent);
		}
		#panchangDetails .divPanchang[onclick]::after {
			content:"+";
			position:absolute;
			top:10px;
			right:10px;
			display:flex;
			width:19px;
			height:19px;
			align-items:center;
			justify-content:center;
			border:1px solid currentColor;
			border-radius:50%;
			font-size:14px;
			font-weight:700;
			line-height:1;
			opacity:.75;
		}
		#panchangDetails .panchang-next-value {
			font-weight:400;
		}
		#panchangDetails .divPanchang[onclick]:hover {
			border-color:var(--theme-border-accent);
			background:var(--theme-accent-soft);
			color:var(--theme-text-accent-strong)!important;
		}
		#divOrigContainer .divTable.blueTable:not(#transitChart) .divTableCell,
		#divOrigContainer .divTable.blueTable:not(#transitChart) .divTableHead {
			border:1px solid var(--theme-border);
			background:var(--theme-surface-table);
			color:var(--theme-text);
			line-height:1.4;
		}
		#divOrigContainer .divTable.blueTable h6 {
			color:var(--theme-text-accent)!important;
		}
		#divOrigContainer .divPanchangHeadingSmall {
			border-color:var(--theme-border);
			background:var(--theme-accent-medium);
			color:var(--theme-text-accent-soft);
		}
		#divOrigContainer .divPanchangSmall {
			border-color:var(--theme-border-subtle);
			background:var(--theme-surface-table-light);
			color:var(--theme-text);
		}
		#divOrigContainer .divPanchangSmallGreen {
			border-color:var(--theme-border-strong);
			background:var(--theme-gold-soft);
			color:var(--theme-text-gold);
		}
		#divOrigContainer .divTable.blueTable .divThaaraPalanWhite,
		#divOrigContainer .divTable.blueTable .divThaaraPalanRed,
		#divOrigContainer .divTable.blueTable .divThaaraPalanGreen,
		#divOrigContainer .divTable.blueTable .divThaaraPalanRedBright,
		#divOrigContainer .divTable.blueTable .divThaaraPalanGreenBright {
			border:1px solid var(--theme-border-soft);
			color:var(--theme-text);
		}
		#divOrigContainer .divTable.blueTable .divThaaraPalanWhite {
			background:var(--theme-surface-table-light);
		}
		#divOrigContainer .divTable.blueTable .divThaaraPalanRed,
		#divOrigContainer .divTable.blueTable .divThaaraPalanRedBright {
			background:var(--theme-danger-soft);
			color:var(--theme-text-error);
		}
		#divOrigContainer .divTable.blueTable .divThaaraPalanGreen,
		#divOrigContainer .divTable.blueTable .divThaaraPalanGreenBright {
			background:var(--theme-success-soft);
			color:var(--theme-text-success);
		}
		#divOrigContainer #starlordHeadMutable {
			display:block!important;
			width:min(100%,760px)!important;
			max-width:100%;
			height:auto!important;
			margin:20px auto 0!important;
			padding:0!important;
			border:1px solid var(--theme-border-accent)!important;
			border-bottom:0!important;
			border-radius:16px 16px 0 0;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 12px 28px rgba(48,22,44,.1);
		}
		#starlordHeadMutable .divTableRow {
			display:block!important;
			background:transparent!important;
		}
		#starlordHeadMutable .divTableCell {
			display:block!important;
			width:auto!important;
			padding:13px 15px 11px!important;
			border:0!important;
			background:transparent!important;
			text-align:left;
		}
		#starlordHeadMutable h6 {
			display:flex;
			align-items:center;
			gap:8px;
			margin:0;
			padding-left:10px;
			border-left:3px solid var(--theme-text-accent);
			color:var(--theme-text-accent-strong)!important;
			font-size:17px;
			font-weight:800;
		}
		#divOrigContainer #starlordBodyMutable {
			display:block!important;
			width:min(100%,760px)!important;
			max-width:100%;
			height:auto!important;
			margin:0 auto 18px!important;
			padding:0!important;
			overflow-x:auto;
			overscroll-behavior-x:contain;
			border:1px solid var(--theme-border-accent)!important;
			border-top:0!important;
			border-radius:0 0 16px 16px;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 14px 30px rgba(48,22,44,.1);
			scrollbar-width:thin;
			scrollbar-color:var(--theme-border-accent) transparent;
		}
		#starlordBodyMutable::-webkit-scrollbar {
			height:7px;
		}
		#starlordBodyMutable::-webkit-scrollbar-track {
			border-radius:999px;
			background:var(--theme-surface-table-light);
		}
		#starlordBodyMutable::-webkit-scrollbar-thumb {
			border:2px solid transparent;
			border-radius:999px;
			background:var(--theme-border-accent);
			background-clip:padding-box;
		}
		#starlordBodyMutable .divTableRow {
			display:grid!important;
			grid-template-columns:minmax(78px,.8fr) minmax(108px,1fr) minmax(100px,.95fr) minmax(125px,1.15fr) minmax(140px,1.3fr);
			min-width:560px;
			background:var(--theme-surface-panel-solid);
		}
		#starlordBodyMutable .divTableRow:first-child {
			position:sticky;
			top:0;
			z-index:2;
			background:var(--theme-accent-soft);
		}
		#starlordBodyMutable .divTableRow > div {
			display:flex;
			width:auto!important;
			min-width:0;
			min-height:40px;
			align-items:center;
			justify-content:center;
			box-sizing:border-box;
			height:auto!important;
			margin:0;
			padding:8px 9px;
			border:0;
			border-right:1px solid var(--theme-border-subtle);
			border-bottom:1px solid var(--theme-border-subtle);
			border-radius:0;
			background:transparent;
			color:var(--theme-text);
			font-size:12px;
			line-height:1.35;
			text-align:center;
			overflow-wrap:anywhere;
		}
		#starlordBodyMutable .divTableRow > div:last-child {
			border-right:0;
		}
		#starlordBodyMutable .divTableRow > div:first-child {
			position:sticky;
			left:0;
			z-index:1;
			background:var(--theme-surface-panel-solid);
		}
		#starlordBodyMutable .divTableRow:first-child > .divTableCell {
			min-height:44px;
			background:var(--theme-accent-soft);
			color:var(--theme-text-accent-strong);
			font-size:12px;
			font-weight:800;
			line-height:1.25;
		}
		#starlordBodyMutable .divTableRow:first-child > .divTableCell:first-child {
			z-index:3;
		}
		#starlordBodyMutable .divTableRow:not(:first-child) > .divPanchangHeadingSmall {
			position:sticky;
			left:0;
			z-index:1;
			background:var(--theme-surface-panel-solid)!important;
			color:var(--theme-text-accent-strong);
			font-weight:800;
			box-shadow:inset 3px 0 var(--theme-text-accent);
		}
		#starlordBodyMutable .divTableRow:first-child > .divTableCell:first-child {
			background:var(--theme-text-accent-strong)!important;
			color:#fff!important;
		}
		#starlordBodyMutable .divTableRow > .divPanchangSmall:last-child {
			white-space:nowrap;
			font-size:11px;
			font-variant-numeric:tabular-nums;
		}
		#starlordBodyMutable #hidePdf1 {
			display:none!important;
		}
		#divOrigContainer .divTable.blueTable.transit-chart-legend {
			display:flex!important;
			width:min(100%,760px)!important;
			max-width:100%;
			flex-wrap:wrap;
			align-items:center;
			justify-content:center;
			gap:7px;
			margin:10px auto 18px!important;
			padding:9px!important;
			border:1px solid var(--theme-border)!important;
			border-radius:14px;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 8px 20px rgba(48,22,44,.08);
		}
		.transit-chart-legend .legend-item {
			display:inline-flex;
			min-height:30px;
			align-items:center;
			gap:7px;
			padding:4px 9px;
			border:1px solid var(--theme-border-subtle);
			border-radius:10px;
			background:var(--theme-surface-cell);
			color:var(--theme-text-secondary);
			font-size:12px;
			line-height:1.25;
		}
		.transit-chart-legend .legend-symbol {
			padding:3px 6px;
			border-radius:7px;
			background:var(--theme-accent-soft);
			color:var(--theme-text-accent-strong);
			font-size:10px;
			font-weight:800;
			white-space:nowrap;
		}
		.transit-chart-legend .legend-symbol-mandhi {
			background:#b52f45;
			color:#fff;
		}
		.transit-chart-legend .legend-symbol-indhu {
			background:var(--theme-success-soft);
			color:var(--theme-text-success);
		}
		.transit-chart-legend .legend-symbol-thithi {
			background:var(--theme-danger-soft);
			color:var(--theme-text-error-strong);
		}
		@media (max-width:640px) {
			#starlordHeadMutable h6::after {
				content:"↔";
				display:inline-flex;
				width:24px;
				height:24px;
				align-items:center;
				justify-content:center;
				margin-left:auto;
				border:1px solid var(--theme-border-accent);
				border-radius:50%;
				color:var(--theme-text-accent);
				font-size:14px;
			}
		}
		#divOrigContainer #transitChart {
			display:block!important;
			width:min(100%,420px)!important;
			max-width:100%;
			height:auto!important;
			aspect-ratio:1/1;
			box-sizing:border-box;
			margin:8px auto 14px!important;
			padding:8px!important;
			border:1px solid var(--theme-border-strong)!important;
			border-radius:20px;
			background:var(--theme-surface-chart)!important;
		}
		#transitChart .divTableBody {
			display:grid!important;
			width:100%;
			height:100%;
			grid-template-columns:repeat(4,minmax(0,1fr));
			grid-template-rows:repeat(4,minmax(0,1fr));
		}
		#transitChart .divTableRow {
			display:contents!important;
		}
		#transitChart .divTableCell,
		#transitChart .divTableCenterCell {
			display:flex!important;
			width:auto;
			height:auto;
			min-width:0;
			min-height:0;
			align-items:center;
			justify-content:center;
			flex-direction:column;
			gap:2px;
			padding:clamp(4px,1.4vw,10px);
			border:1px solid var(--theme-chart-border)!important;
			background:var(--theme-chart-background)!important;
			color:var(--theme-chart-text,var(--theme-text))!important;
			line-height:1.15;
			text-align:center;
			overflow-wrap:anywhere;
		}
		#transitChart .divTableCell {
			border-color:var(--theme-chart-box-border,var(--theme-chart-border))!important;
			background:var(--theme-chart-box-background,var(--theme-chart-background))!important;
			color:var(--theme-chart-box-text,var(--theme-chart-text,var(--theme-text)))!important;
			font-size:var(--transit-chart-cell-font-size,clamp(13px,3.8vw,18px));
		}
		#transitChart .divTableCenterCell {
			font-size:clamp(10px,2.4vw,14px);
		}
		#transitChart .transit-planet-label {
			display:block;
			width:100%;
			max-width:100%;
			overflow-wrap:normal;
		}
		#transitChart .transit-planet-label.is-two-column {
			display:grid;
			grid-template-columns:repeat(2,minmax(0,1fr));
			gap:1px 2px;
			align-items:center;
			justify-items:stretch;
		}
		#transitChart .transit-planet-label.is-crowded .transit-planet-entry {
			line-height:1;
		}
		#transitChart .retro::after {
			margin-left:1px;
			font-size:.72em;
			font-weight:800;
			white-space:nowrap;
		}
		#transitChart[data-language="english"] .retro::after {
			content:"(R)";
		}
		#transitChart[data-language="tamil"] .retro::after {
			content:"(வ)";
		}
		#transitChart .transit-planet-label.is-crowded .mandhi,
		#transitChart .transit-planet-label.is-crowded .moon,
		#transitChart .transit-planet-label.is-crowded .lagna {
			padding:0 1px;
		}
		#transitChart .divTableCell.is-crowded {
			padding:2px;
		}
		#transitChart .transit-planet-entry {
			display:inline-flex;
			flex-wrap:nowrap;
			align-items:center;
			min-width:0;
			max-width:100%;
			white-space:nowrap!important;
			vertical-align:middle;
		}
		#transitChart .is-two-column .transit-planet-entry {
			justify-content:center;
			transform:scaleX(.85);
		}
		#transitChart .mandhi,
		#transitChart .moon,
		#transitChart .lagna,
		#transitChart .thithi_soonyam,
		#transitChart .thithi_soonyam_main {
			display:inline-block;
			padding:1px 3px;
			border-radius:3px;
		}
		#transitChart .mandhi,
		#transitChart .indhu,
		#transitChart .thithi_soonyam,
		#transitChart .thithi_soonyam_main {
			font-size:10px!important;
			line-height:1;
		}
		#transitChart .mandhi,
		#transitChart .moon,
		#transitChart .lagna,
		#transitChart .indhu {
			display:inline-flex!important;
			min-height:14px;
			align-items:center;
			justify-content:center;
			padding:1px 5px!important;
			border:1px solid rgba(255,255,255,.24);
			border-radius:999px;
			font-weight:700;
			line-height:1!important;
			vertical-align:middle;
			white-space:nowrap;
		}
		#transitChart .mandhi {
			background-color:red!important;
			color:white!important;
		}
		#transitChart .moon {
			background-color:#00ffff!important;
			border:1px solid rgba(0,0,0,.14);
			border-radius:999px;
			color:#111!important;
		}
		#transitChart .is-lagna-house {
			background-image:linear-gradient(135deg,transparent 48.8%,rgba(153,93,42,.58) 49.6%,rgba(153,93,42,.58) 50.4%,transparent 51.2%)!important;
		}
		#transitChart .divTableCenterCell {
			border:0!important;
			background:var(--theme-surface-chart-center)!important;
			color:var(--theme-text-secondary)!important;
		}
		#transitChart .divTableDummyCell {
			display:none!important;
		}
		#transitChart .thithi_soonyam {
			background:var(--theme-danger-soft);
			color:var(--theme-chart-box-error,var(--theme-text-error-strong));
		}
		#muhurthamDiv {
			background:var(--theme-overlay)!important;
			backdrop-filter:blur(8px);
			-webkit-backdrop-filter:blur(8px);
		}
		#muhurthamDiv .table-container {
			position:fixed;
			top:50%;
			left:50%;
			width:min(94vw,900px);
			height:min(82vh,720px);
			margin:0;
			padding:24px 12px 12px;
			transform:translate(-50%,-50%);
			border:1px solid var(--theme-border-strong);
			border-radius:22px;
			background:var(--theme-surface-panel-solid);
			box-shadow:0 24px 70px var(--theme-shadow-strong),0 0 30px var(--theme-accent-soft);
		}
		#muhurthamDiv .scrollable-table {
			height:100%;
			padding:0 0 56px;
			overflow:auto;
		}
		#muhurthamDiv .divTable.blueTable {
			width:100%!important;
			height:auto!important;
			margin:0 auto;
			border:0;
			background:transparent;
		}
		#muhurthamDiv .divMuhurtHeading {
			border:1px solid var(--theme-border-strong);
			background:var(--theme-accent-medium);
			color:var(--theme-text-heading);
		}
		#muhurthamDiv .divMurtaRed,
		#muhurthamDiv .divMurtaBrightGreen,
		#muhurthamDiv .divMurtaGreen {
			border:1px solid var(--theme-border-soft);
			color:var(--theme-text);
		}
		#muhurthamDiv .divMurtaRed {
			background:var(--theme-danger-soft);
			color:var(--theme-text-error);
		}
		#muhurthamDiv .divMurtaBrightGreen {
			background:var(--theme-success-soft);
			color:var(--theme-text-success);
		}
		#muhurthamDiv .divMurtaGreen {
			background:var(--theme-success-soft);
			color:var(--theme-text-success);
		}
		#muhurthamDiv .close-btn {
			width:40px;
			height:40px;
			border:1px solid var(--theme-border-on-accent);
			border-radius:12px;
			background:var(--theme-button-gradient-close);
			opacity:1;
		}
		#mybody1 #thithiDetailsDiv,
		#mybody1 #karanaDetailsDiv,
		#mybody1 #yogiDetailsDiv,
		#mybody1 #ayogiDetailsDiv,
		#mybody1 #nakDetailsDiv,
		#mybody1 #rasiDetailsDiv {
			position:fixed;
			inset:0;
			z-index:30000;
			display:none;
			align-items:center;
			justify-content:center;
			width:auto;
			height:auto;
			padding:16px;
			background:var(--theme-overlay)!important;
			color:var(--theme-text);
			backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
			-webkit-backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
		}
		#mybody1 #thithiDetailsDiv .remedy-dialog-card,
		#mybody1 #karanaDetailsDiv .remedy-dialog-card,
		#mybody1 #yogiDetailsDiv .remedy-dialog-card,
		#mybody1 #ayogiDetailsDiv .remedy-dialog-card,
		#mybody1 #nakDetailsDiv .remedy-dialog-card,
		#mybody1 #rasiDetailsDiv .remedy-dialog-card {
			position:relative;
			display:grid;
			grid-template-columns:minmax(108px,.8fr) minmax(0,1.2fr);
			grid-auto-rows:min-content;
			align-content:start;
			gap:0;
			width:min(560px,100%);
			height:auto!important;
			max-height:82vh;
			max-height:82dvh;
			min-height:0!important;
			margin:0;
			padding:20px;
			overflow-y:auto;
			overscroll-behavior:contain;
			border:1px solid var(--theme-glass-border)!important;
			border-radius:22px;
			background:var(--theme-surface-panel-solid)!important;
			box-shadow:0 24px 70px var(--theme-glass-shadow),0 0 0 1px rgba(212,175,55,.16),inset 0 1px var(--theme-glass-highlight),inset 0 -1px var(--theme-glass-edge);
			color:var(--theme-text);
			backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
			-webkit-backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item {
			min-width:0;
			padding:12px;
			border:1px solid var(--theme-border-soft);
			background:var(--theme-surface-cell);
			color:var(--theme-text);
			line-height:1.45;
			overflow-wrap:anywhere;
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-head:first-of-type {
			grid-column:1/-1;
			justify-content:flex-start;
			padding:10px 52px 14px 12px;
			border:0;
			border-bottom:1px solid var(--theme-border);
			border-radius:12px 12px 0 0;
			background:transparent;
			color:var(--theme-text-accent-strong);
			font-size:1.2em;
			text-align:left;
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-left {
			font-weight:700;
			color:var(--theme-text-secondary);
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-right img,
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-right image {
			max-width:100%;
			height:auto;
			border-radius:12px;
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-right a {
			color:var(--theme-text-accent-strong)!important;
			font-weight:700;
			text-decoration:underline;
			text-underline-offset:2px;
		}
		#mybody1 .remedy-dialog-close {
			position:absolute;
			top:14px;
			right:14px;
			z-index:1;
			display:grid;
			width:38px;
			height:38px;
			place-items:center;
			padding:0;
			border:1px solid var(--theme-border-on-accent);
			border-radius:12px;
			background:var(--theme-button-gradient-close);
			box-shadow:inset 0 1px var(--theme-glass-highlight),0 6px 16px var(--theme-shadow-accent);
			color:var(--theme-button-text);
			font-size:24px;
			line-height:1;
			cursor:pointer;
		}
		#mybody1 .remedy-dialog-card .parigaaram-grid-item-head:last-child {
			grid-column:1/-1;
			background:transparent;
			border:0;
		}
		#mybody1 .remedy-dialog-card .actionBtn {
			min-height:40px;
			padding:8px 18px;
			border:1px solid var(--theme-border-on-accent);
			border-radius:12px;
			background:var(--theme-button-gradient);
			color:var(--theme-button-text);
			font-weight:700;
		}
		#mybody1.remedy-dialog-open {
			overflow:hidden;
		}
		#mybody1.remedy-dialog-open #thithiDetailsDiv,
		#mybody1.remedy-dialog-open #karanaDetailsDiv,
		#mybody1.remedy-dialog-open #yogiDetailsDiv,
		#mybody1.remedy-dialog-open #ayogiDetailsDiv,
		#mybody1.remedy-dialog-open #nakDetailsDiv,
		#mybody1.remedy-dialog-open #rasiDetailsDiv {
			top:82px;
		}
		#mybody1.remedy-dialog-open #thithiDetailsDiv .remedy-dialog-card,
		#mybody1.remedy-dialog-open #karanaDetailsDiv .remedy-dialog-card,
		#mybody1.remedy-dialog-open #yogiDetailsDiv .remedy-dialog-card,
		#mybody1.remedy-dialog-open #ayogiDetailsDiv .remedy-dialog-card,
		#mybody1.remedy-dialog-open #nakDetailsDiv .remedy-dialog-card,
		#mybody1.remedy-dialog-open #rasiDetailsDiv .remedy-dialog-card {
			max-height:calc(100vh - 114px);
			max-height:calc(100dvh - 114px);
		}
		#mybody1 .starDivition {
			border-color:var(--theme-border-strong);
			background:var(--theme-surface-panel-solid);
			box-shadow:0 12px 34px var(--theme-shadow-strong);
			color:var(--theme-text);
		}
		#mybody1 .starDivition .close-btn {
			border:1px solid var(--theme-border-on-accent);
			border-radius:12px;
			background:var(--theme-button-gradient-close);
			color:var(--theme-button-text);
			opacity:1;
		}
		#mybody1 .starDivition table th,
		#mybody1 .starDivition table td {
			border:1px solid var(--theme-border)!important;
			background:var(--theme-surface-table)!important;
			color:var(--theme-text);
			padding:5px;
		}
		#mybody1 .starDivition table tbody td:first-child {
			text-align:center;
		}
		#mybody1 .starDivition table thead tr {
			background:var(--theme-accent-medium)!important;
		}
		#panchangForm,
		#divOrigContainer .divTable.blueTable:not(#transitChart),
		#muhurthamDiv .table-container,
		#mybody1 .starDivition {
			border-color:var(--theme-glass-border)!important;
			box-shadow:0 18px 48px var(--theme-glass-shadow),0 0 0 1px rgba(212,175,55,.16),inset 0 1px var(--theme-glass-highlight),inset 0 -1px var(--theme-glass-edge);
			backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
			-webkit-backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
		}
		#divOrigContainer #transitChart {
			box-shadow:0 18px 42px var(--theme-glass-shadow),0 0 0 1px rgba(212,175,55,.22),inset 0 1px var(--theme-glass-highlight),inset 0 -1px var(--theme-glass-edge);
			backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
			-webkit-backdrop-filter:blur(var(--theme-glass-blur)) saturate(var(--theme-glass-saturation));
		}
		#divOrigContainer .divTable.blueTable:not(#transitChart) .divTableCell,
		#divOrigContainer .divTable.blueTable:not(#transitChart) .divTableHead,
		#panchangDetails .divPanchang {
			backdrop-filter:blur(10px);
			-webkit-backdrop-filter:blur(10px);
		}
		@media (max-width:600px) {
			#mybody1 #thithiDetailsDiv,
			#mybody1 #karanaDetailsDiv,
			#mybody1 #yogiDetailsDiv,
			#mybody1 #ayogiDetailsDiv,
			#mybody1 #nakDetailsDiv,
			#mybody1 #rasiDetailsDiv {
				padding:12px;
			}
			#mybody1.remedy-dialog-open #thithiDetailsDiv,
			#mybody1.remedy-dialog-open #karanaDetailsDiv,
			#mybody1.remedy-dialog-open #yogiDetailsDiv,
			#mybody1.remedy-dialog-open #ayogiDetailsDiv,
			#mybody1.remedy-dialog-open #nakDetailsDiv,
			#mybody1.remedy-dialog-open #rasiDetailsDiv {
				top:88px;
			}
			#mybody1 #thithiDetailsDiv .remedy-dialog-card,
			#mybody1 #karanaDetailsDiv .remedy-dialog-card,
			#mybody1 #yogiDetailsDiv .remedy-dialog-card,
			#mybody1 #ayogiDetailsDiv .remedy-dialog-card,
			#mybody1 #nakDetailsDiv .remedy-dialog-card,
			#mybody1 #rasiDetailsDiv .remedy-dialog-card {
				grid-template-columns:minmax(92px,.75fr) minmax(0,1.25fr);
				width:100%;
				max-height:86vh;
				max-height:86dvh;
				padding:12px;
				border-radius:18px;
			}
			#mybody1.remedy-dialog-open #thithiDetailsDiv .remedy-dialog-card,
			#mybody1.remedy-dialog-open #karanaDetailsDiv .remedy-dialog-card,
			#mybody1.remedy-dialog-open #yogiDetailsDiv .remedy-dialog-card,
			#mybody1.remedy-dialog-open #ayogiDetailsDiv .remedy-dialog-card,
			#mybody1.remedy-dialog-open #nakDetailsDiv .remedy-dialog-card,
			#mybody1.remedy-dialog-open #rasiDetailsDiv .remedy-dialog-card {
				max-height:calc(100vh - 112px);
				max-height:calc(100dvh - 112px);
			}
			#mybody1 .remedy-dialog-card .parigaaram-grid-item {
				padding:9px;
			}
			#mybody1 .remedy-dialog-close {
				top:10px;
				right:10px;
				width:36px;
				height:36px;
			}
			#mybody1 .column-body {
				width:calc(100% - 16px);
				padding:10px;
				border-radius:20px;
			}
			#panchangDetails .divTableRow {
				grid-template-columns:minmax(92px,.78fr) minmax(0,1.22fr);
				gap:5px;
			}
			#panchangDetails .divPanchangHeading,
			#panchangDetails .divPanchangHeadingRed,
			#panchangDetails .divPanchang {
				padding:9px 8px;
			}
			#muhurthamDiv .table-container {
				width:96vw;
				height:86vh;
				padding:22px 8px 8px;
			}
			#panchangDetails .divPanchang[onclick]::after {
				content:"\f25a";
				width:19px;
				height:19px;
				border:0;
				font-family:FontAwesome;
				font-size:16px;
				font-weight:normal;
			}
		}
	</style>
	
	<!-- this is used for swipe gestures, disabling this due to some side effects -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script> -->
	<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" defer></script>


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
	

    <script src="script.js?<?php echo date('ymdhis'); ?>" defer></script>
	<script src="panchang.js?<?php echo date('ymdhis'); ?>" defer></script>
	<script src="my_panchang.js?<?php echo date('ymdhis'); ?>" defer></script>
	<script src="common.js?<?php echo date('ymdhis'); ?>" defer></script>
    <script type="text/javascript">
	var timezone = '<?php echo $TZ; ?>';
	var currentLanguage = '<?php echo $language; ?>';
	// Minimal translation mapping for specific dynamic content (Tamil -> English)
	var translationMapping = <?php echo get_translation_mapping_json(); ?>;

	<?php
	// Provide mukurt (muhurtham) array server-side so we can emit English or Tamil directly
	$mukurt_arr_ta = [
		["ருத்ர","அசுபமானது"],["ஆஹி","அசுபமானது"],["மித்ர","சுப"],["பித்ரு","அசுபமானது"],["வசு","சுப"],["வராஹ","சுப"],["விஸ்வேதேவா","சுப"],["விதி","மங்களகரமானது (திங்கள் மற்றும் வெள்ளி தவிர)"],["சுதாமுகீ","சுப"],["புருஹூத","அசுபமானது"],["வாஹிநீ","அசுபமானது"],["நக்தனகரா","அசுபமானது"],["வருண","சுப"],["ஆர்யமன்","மங்களகரமானது (ஞாயிற்றுக்கிழமைகள் தவிர)"],["பக","அசுபமானது"],["கிரீச","சுப"],["அஜபாத","அசுபமானது"],["அஹிர்புத்ன்ய","சுப"],["புஷ்ய","சுப"],["அஸ்வினி","சுப"],["யம","அசுபமானது"],["அக்னி","சுப"],["விதாத்ரு","சுப"],["கண்ட","சுப"],["அதிதி","சுப"],["ஜீவ/அம்ருத","மிகவும் மங்களகரமானது"],["விஷ்ணு","சுப"],["த்யுமத்கத்யுதி","சுப"],["பிரம்மா","மிகவும் மங்களகரமானது"],["சமுத்ர","சுப"]
	];

	$mukurt_arr_en = [
		["Rudra","Inauspicious"],["Ahi","Inauspicious"],["Mitra","Favourable"],["Pitru","Inauspicious"],["Vasu","Favourable"],["Varaha","Favourable"],["Visvedevas","Favourable"],["Vidi","Auspicious (except Mon & Fri)"],["Sudamukhi","Favourable"],["Puruhuta","Inauspicious"],["Vahinee","Inauspicious"],["Nakthanakara","Inauspicious"],["Varuna","Favourable"],["Aryaman","Auspicious (except Sun)"],["Paka","Inauspicious"],["Kreesa","Favourable"],["Ajapada","Inauspicious"],["Ahirbudhnya","Favourable"],["Pushya","Favourable"],["Asvini","Favourable"],["Yama","Inauspicious"],["Agni","Favourable"],["Vithadru","Favourable"],["Kanda","Favourable"],["Athithi","Favourable"],["Jiva/Amrutha","Very Auspicious"],["Vishnu","Favourable"],["Dyumatkadhyuthi","Favourable"],["Brahma","Very Auspicious"],["Samudra","Favourable"]
	];

	$mukurt_arr_js = ($language === 'english') ? $mukurt_arr_en : $mukurt_arr_ta;
	?>

	var mukurt_arr = <?php echo json_encode($mukurt_arr_js, JSON_UNESCAPED_UNICODE); ?>;

	function mapIfEnglish(text) {
		if (currentLanguage === 'english' && translationMapping && translationMapping[text]) {
			return translationMapping[text];
		}
		return text;
	}

	function translateDynamic(text) {
		if (!text) return text;
		if (currentLanguage === 'tamil') return text;
		if (!translationMapping) return text;

		// Exact match
		if (translationMapping[text]) return translationMapping[text];

		// Partial replacements (longest keys first)
		var keys = Object.keys(translationMapping).sort(function(a,b){return b.length - a.length;});
		var out = text;
		for (var i=0;i<keys.length;i++){
			var k = keys[i];
			if (out.indexOf(k) !== -1) {
				out = out.split(k).join(translationMapping[k]);
			}
		}

		// handle parentheses like "X(Y)" or "X (Y)"
		var m = out.match(/^(.+?)\s*\((.+)\)$/);
		if (m) {
			return m[1].trim() + ' (' + m[2].trim() + ')';
		}

		return out;
	}

	function formatTithiName(text) {
		var translated = translateDynamic(text);
		if (currentLanguage !== 'english' || typeof translated !== 'string') return translated;
		return translated.replace(/\s*(wax|wan)$/i, ' ($1)');
	}
	
	// Language handled server-side; client-side translation removed
	
	const panchangTime = "<?php echo $defaultPanchangTime; ?>";
	const panchangDate = "<?php echo date('Y-m-d'); ?>";
	
	function init(){
			if(getCookie(focus)=='date'){
				document.getElementById('nDate').focus();
			}
			
			if(getCookie(focus)=='time'){
				document.getElementById('nTime').focus();
			}
			
			delete_cookie(focus);
			
			document.getElementById('place').value = '<?php echo $place; ?>';
			
			document.getElementById("starlordHeadMutable").style.display = 'table';
			document.getElementById("starlordBodyMutable").style.display = 'table';
			
			var language = '<?php echo $language; ?>';
			
				var __langSel = document.getElementById("languageSelector");
				if(__langSel) __langSel.value = language;
			}
			document.addEventListener('DOMContentLoaded', init);
	
/******** this code is to take screenshot of the chart **************	
		var dataURL = {};  
		 $(document).ready(function(){
		$('a').click(function(){
			html2canvas(document.querySelector("#chart")).then(canvas => {  
				document.body.appendChild(canvas);  
				dataURL = canvas.toDataURL(); 
				post_data(dataURL); 
		  });
		});
		});
  
		function post_data(imageURL) {  
            //console.log(imageURL);  
			alert(imageURL);
            $.ajax({  
                url: "save_data.php",  
                type: "POST",  
                data: {  
                    image: imageURL  
                },  
                dataType: "html",  
                success: function() {  
                    alert('Success!!');  
                    location.reload();  
                }  
            });  
        }   
****** this code is to take screenshot of the chart ***************/

		
		
		function updateNow() {
			  const date = new Date();
			  
			  var dd = date.getDate();
			  var mm = date.getMonth()+1; 
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

			  document.getElementById('nDate').value= today ; 
			  document.getElementById('nTime').value= time ; 
			  document.getElementById('time_mode').value = 'custom';
			  
			  return true;
			}
			
		function updateToday() {
			document.getElementById('time_mode').value = 'sunrise';
			document.getElementById('nTime').value = '';
			document.getElementById('nDate').value = panchangDate;
			document.forms["updatePanchang"].submit();
		}

		function preparePanchangTimeMode() {
			if (document.getElementById('time_mode').value !== 'custom') {
				document.getElementById('nTime').value = '';
			}
		}
		
		//Submit form on date change
		function dateChange(form) { 
			set_cookie(focus,'date');
			form.submit();
		}
		
		//Submit form on time change
		function timeChange(form)  {
			set_cookie(focus,'time');
			form.submit();
		}
		
		//get cookie value
		function getCookie(name) {
		  const value = `; ${document.cookie}`;
		  const parts = value.split(`; ${name}=`);
		  if (parts.length === 2) return parts.pop().split(';').shift();
		}
		
		function set_cookie(name, value) {
			document.cookie = name +'='+ value +'; Path=/;';
		}
		
		function delete_cookie(name) {
			document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}
		
		$(document).ready(function(){
			$('.search-box input[type="text"]').on("keyup input", function(){
				/* Get input value on change */
				//var inputVal = $(this).val();
				var inputVal = $(this).val().trim();
				document.getElementById('place').value=inputVal;
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
			$(document).on("click", ".result div", function(){
				$(this).parents(".search-box").find('input[type="text"]').val($(this).text());
				$(this).parent(".result").empty();
				$(this).parent("#lplace").hide();
				// Auto-submit form when place is selected
				if(validateForm()==true){
					document.forms["updatePanchang"].submit();
				}
			});
		});
		
		function placeOnBlur() {
			let place = document.getElementById("place").value;
			
			if(place==""){
				/*document.getElementById('place').value = '<?php echo $place; ?>';*/
				document.getElementById('place').value = '<?php echo $place; ?>';
			}
		}
		
		function dropdownFormSubmit() {
			document.getElementById('nDate').value = "";
			document.getElementById('nTime').value = "";
			document.getElementById('time_mode').value = 'sunrise';
			document.forms["updatePanchang"].submit();
		}
		
		function movePanchangPeriod(amount, unit) {
			const dateInput = document.getElementById('nDate');
			const dateParts = dateInput.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
			if (!dateParts) {
				console.error('Cannot move the Panchang timeline without a selected date.');
				return;
			}

			const year = Number(dateParts[1]);
			const month = Number(dateParts[2]);
			const day = Number(dateParts[3]);
			let targetDate;
			if (unit === 'month') {
				targetDate = new Date(year, month - 1 + amount, 1);
			} else if (unit === 'year') {
				targetDate = new Date(year + amount, month - 1, 1);
			} else {
				console.error('Unsupported Panchang timeline unit:', unit);
				return;
			}

			const lastDayOfMonth = new Date(targetDate.getFullYear(), targetDate.getMonth() + 1, 0).getDate();
			targetDate.setDate(Math.min(day, lastDayOfMonth));
			const pad = function(value) { return String(value).padStart(2, '0'); };
			dateInput.value = targetDate.getFullYear() + '-' + pad(targetDate.getMonth() + 1) + '-' + pad(targetDate.getDate());
			document.querySelector('#loader').style.visibility = 'visible';
			document.forms['updatePanchang'].submit();
		}
		
		function showKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "block";
		}

		function hideKaranamDiv() {
		  document.getElementById("karanamDiv").style.display = "none";
		}
		
		var activeDetailsTrigger = null;
		var remedyDetailsByType = {
			1: "thithiDetailsDiv",
			2: "karanaDetailsDiv",
			3: "yogiDetailsDiv",
			4: "ayogiDetailsDiv",
			5: "nakDetailsDiv",
			6: "rasiDetailsDiv"
		};

		function showDetailsDiv(event,type) {
			var divName = remedyDetailsByType[type];
			var overlay = divName ? document.getElementById(divName) : null;
			if (!overlay) {
				console.error("Remedy details overlay is unavailable for type:", type);
				return;
			}

			var card = overlay.querySelector('.parigaaram-grid-container');
			if (!card) {
				console.error("Remedy details content is missing in:", divName);
				return;
			}

			hideDetailsDiv();
			document.body.appendChild(overlay);
			activeDetailsTrigger = event && event.currentTarget;
			card.classList.add('remedy-dialog-card');
			overlay.style.display = 'flex';
			overlay.setAttribute('role','dialog');
			overlay.setAttribute('aria-modal','true');

			var heading = card.querySelector('.parigaaram-grid-item-head:first-of-type');
			if (heading) {
				heading.id = divName + 'Heading';
				overlay.setAttribute('aria-labelledby',heading.id);
			}

			var closeButton = card.querySelector('.remedy-dialog-close');
			if (!closeButton) {
				closeButton = document.createElement('button');
				closeButton.type = 'button';
				closeButton.className = 'remedy-dialog-close';
				closeButton.textContent = '×';
				closeButton.setAttribute('aria-label',<?php echo json_encode(__('close')); ?>);
				closeButton.title = <?php echo json_encode(__('close')); ?>;
				card.insertBefore(closeButton,card.firstChild);
			}
			closeButton.onclick = hideDetailsDiv;
			card.querySelectorAll('button').forEach(function(button) {
				button.type = 'button';
				button.onclick = hideDetailsDiv;
			});
			overlay.onclick = function(clickEvent) {
				if (clickEvent.target === overlay) hideDetailsDiv();
			};

			document.getElementById('mybody1').classList.add('remedy-dialog-open');
			closeButton.focus();
		}

		function hideDetailsDiv() {
			[
				"thithiDetailsDiv",
				"karanaDetailsDiv",
				"yogiDetailsDiv",
				"ayogiDetailsDiv",
				"nakDetailsDiv",
				"rasiDetailsDiv"
			].forEach(function(id) {
				var overlay = document.getElementById(id);
				if (overlay) overlay.style.display = 'none';
			});

			document.getElementById('mybody1').classList.remove('remedy-dialog-open');
			if (activeDetailsTrigger && activeDetailsTrigger.isConnected) activeDetailsTrigger.focus();
			activeDetailsTrigger = null;
		}

		document.addEventListener('keydown',function(event) {
			if (event.key === 'Escape' && document.getElementById('mybody1').classList.contains('remedy-dialog-open')) {
				hideDetailsDiv();
			}
		});
		
		function formSubmitButton(){
			if(validateForm()==true){
				document.forms["updatePanchang"].submit();
			}
		}
		
		function onDateChange(){
			document.forms["updatePanchang"].submit();
		}
		
		function onTimeChange(){
			document.getElementById('time_mode').value = 'custom';
			document.forms["updatePanchang"].submit();
		}
		
		/*function onPlaceChange(){
			document.updatePanchang.place_flag.value = 1;
			
			document.forms["updatePanchang"].submit();
		}*/
		
		document.onreadystatechange = function() {
		
			if(document.getElementById('dummy') != null){	//chart not loaded
				if(document.getElementById('loader'))
					//document.querySelector("#loader").style.display = "none";
					document.querySelector("#loader").style.visibility = "hidden";
			}
			else{
				//document.querySelector("body").style.visibility = "hidden";
				document.querySelector("#loader").style.visibility = "visible";
			}
			
			//handleLongPress();
			//handleSwipe();
			
			const button = document.getElementById("onePageBtn");
			const timeElement = document.getElementById("nTime");

			if (!timeElement) {
				console.error("Element with ID 'nTime' not found.");
				return;
			}

			const timeText = timeElement.value; // Get time
		
			// Enable the button only when time matches the selected date's sunrise.
			if (timeText !== panchangTime) {
				button.disabled = true;
			} else {
				button.disabled = false;
			}
		};
	
	function handleSwipe(){
		const bodyElement = document.body;
		const hammer = new Hammer(bodyElement);

		// Listen for swipe events
		hammer.on('swipe', function (event) {
			switch (event.direction) {
				case Hammer.DIRECTION_LEFT:
					console.log('Left swipe detected');
					window.location.href = 'index.php';
					break;
			}
		});
	}
	
	function handleLongPress(){
		// Assuming you have an element with the ID 'my-element'
		var myElement = document.getElementById('mybody1');

		// Initialize Hammer.js
		var mc = new Hammer.Manager(myElement);

		// Add the long press gesture
		mc.add(new Hammer.Press({ event: 'longpress', time: 500 }));

		// Handle the long press event
		mc.on('longpress', function (ev) {
			console.log('Long press detected!');
			window.location.href = 'index.php'
			// Perform your custom action here
			// For example, show a modal or trigger some other behavior
		});
	}
	
	function genPanchang(place){
			var url = "gen_panchang_form.php"+"?place="+encodeURIComponent(place);
			//window.open("gen_panchang_form.php","_self");
			window.open(url,"_self");
			return false;
		}
		
	function validateForm() {
		  p = document.getElementById("place").value;

		  if (p == "") {
		alert('<?php echo addslashes(__("place_not_given")); ?>');
			//document.getElementById("error").innerHTML = "பிறந்த இடம் கொடுல்லவில்லை";
			document.getElementById('place').style.borderColor = "red";
			return false;
		  }
		  
		  if (p.includes("|")==false || p.includes("/")==false || p.length<10){
			  alert('<?php echo addslashes(__("place_not_selected")); ?>');
			  document.getElementById('place').style.borderColor = "red";
			  return false;
		  }
		  
		  return true;
	}
	
	// Global variables to use on button click
	let currentNakshatraName = '';
	let currentNakshatraStart = null;
	let currentNakshatraEnd = null;
	
	function updatePanchangData(){
		const t = new Date('<?php echo $chart_date." ".$chart_time; ?>');

		newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate(),t.getHours(),t.getMinutes()); 
		
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {

			const nak = panchang.Nakshatra;
			currentNakshatraName = nak.name;
			currentNakshatraStart = nak.start;
			currentNakshatraEnd = nak.end;
			
			//document.writeln('<div class="divTableRow">');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:80px">'+formatDate(newdate)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:50px">'+panchang.Day.name+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Nakshatra.name+" "+formatDateTime(panchang.Nakshatra.end)+' வரை'+displayNextNak(newdate,panchang.Nakshatra)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Tithi.name+' '+formatDateTime(panchang.Tithi.end)+' வரை'+displayNextThithi(newdate,panchang.Tithi)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Karna.name+' '+formatDateTime(panchang.Karna.start)+" முதல் "+formatDateTime(panchang.Karna.end)+' வரை'+displayNextKar(newdate,panchang.Karna)+'</div>');
			//	document.writeln('<div class="divTablePanchangMedium" style="width:160px">'+panchang.Yoga.name+' '+formatDateTime(panchang.Yoga.start)+" முதல் "+formatDateTime(panchang.Yoga.end)+' வரை'+displayNextYoga(newdate,panchang.Yoga)+'</div>');
			//document.writeln('</div>');
			
			document.getElementById('thithi').innerHTML = formatTithiName(panchang.Tithi.name)+'<BR><div class="divSmall"> '+formatDateTime(panchang.Tithi.start)+' <?php echo __("from"); ?> <BR> '+formatDateTime(panchang.Tithi.end)+' <?php echo __("to"); ?> </div>';
			
			// Use nakshatra name from PHP data attribute instead of panchang library
			var nakDiv = document.getElementById('nak');
			var nakName = nakDiv.getAttribute('data-nak-name') || translateDynamic(panchang.Nakshatra.name);
			nakDiv.innerHTML = nakName + nakDiv.innerHTML + '<BR><div class="divSmall"> '+formatDateTime(panchang.Nakshatra.start)+' <?php echo __("from"); ?> <BR> '+formatDateTime(panchang.Nakshatra.end)+' <?php echo __("to"); ?> </div>';
			
			document.getElementById('yogam').innerHTML = translateDynamic(panchang.Yoga.name)+'<BR><div class="divSmall"> '+formatDateTime(panchang.Yoga.start)+' <?php echo __("from"); ?> <BR>'+formatDateTime(panchang.Yoga.end)+' <?php echo __("to"); ?> </div>';
			document.getElementById('karanam').innerHTML = translateDynamic(panchang.Karna.name)+' ('+translateDynamic(getKaranaPlanet(panchang.Karna.name))+')'+'<BR><div class="divSmall">'+formatDateTime(panchang.Karna.start)+' <?php echo __("from"); ?> <BR>'+formatDateTime(panchang.Karna.end)+' <?php echo __("to"); ?> </div>';
		});

		//storing initial end times for nak, thithi, yogam, karanam as new generation will result in changes to panchang object
		nak_end = panchang.Nakshatra.end;
		thithi_end = panchang.Tithi.end;
		yogam_end = panchang.Yoga.end;
		karanam_end = panchang.Karna.end;

		//To add next nak
		//newdate = new Date(t.getFullYear(),t.getMonth(),t.getDate(),t.getHours()+36); //adding 26 hours for thithi, nak, yogam
		//newdate = new Date(panchang.Nakshatra.end.getFullYear(),panchang.Nakshatra.end.getMonth(),panchang.Nakshatra.end.getDate(),panchang.Nakshatra.end.getHours(),panchang.Nakshatra.end.getMinutes()+1); //adding 26 hours for thithi, nak, yogam
		newdate = new Date(nak_end.getFullYear(),nak_end.getMonth(),nak_end.getDate(),nak_end.getHours(),nak_end.getMinutes()+1); //adding 1 minute from current nak to get next
		//newdate = changeTimezone(newdate,timezone);
	
		//To add next nak
		panchang.calculate(newdate, function() {
			document.getElementById('nak').innerHTML = document.getElementById('nak').innerHTML + '<span class="panchang-next-value">' + translateDynamic(panchang.Nakshatra.name) + '</span><BR><div class="divSmall"> '+formatDateTime(panchang.Nakshatra.start)+' <?php echo __("from"); ?> <BR>'+formatDateTime(panchang.Nakshatra.end)+' <?php echo __("to"); ?> </div>';
		});

		//To add next karanam
		newdate = new Date(karanam_end.getFullYear(),karanam_end.getMonth(),karanam_end.getDate(),karanam_end.getHours(), karanam_end.getMinutes()+1); //adding 1 mins to get next karanam
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('karanam').innerHTML = document.getElementById('karanam').innerHTML + '<span class="panchang-next-value">' + translateDynamic(panchang.Karna.name)+' ('+translateDynamic(getKaranaPlanet(panchang.Karna.name))+')</span>'+'<BR><div class="divSmall"> '+formatDateTime(panchang.Karna.start)+' <?php echo __("from"); ?> <BR>'+formatDateTime(panchang.Karna.end)+' <?php echo __("to"); ?> </div>';
		});

		//To add next thithi
		//newdate = new Date(thithi_end.getFullYear(),thithi_end.getMonth(),thithi_end.getDate(),thithi_end.getHours(),thithi_end.getMinutes()+1); //adding 1 min to get next thithi
		newdate = new Date(thithi_end.getFullYear(),thithi_end.getMonth(),thithi_end.getDate(),thithi_end.getHours()+1,thithi_end.getMinutes()); //adding 1 hour to get next thithi, above 1 min sometimes display same thithi for next too
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('thithi').innerHTML = document.getElementById('thithi').innerHTML + '<span class="panchang-next-value">' + formatTithiName(panchang.Tithi.name) + '</span> <BR><div class="divSmall">'+formatDateTime(panchang.Tithi.start)+' <?php echo __("from"); ?> <BR> '+formatDateTime(panchang.Tithi.end)+' <?php echo __("to"); ?> </div>';
		});

		//To add next yogam
		newdate = new Date(yogam_end.getFullYear(),yogam_end.getMonth(),yogam_end.getDate(),yogam_end.getHours(),yogam_end.getMinutes()+10); //adding 10 min to get next yogam
		//newdate = changeTimezone(newdate,timezone);
	
		panchang.calculate(newdate, function() {
			document.getElementById('yogam').innerHTML = document.getElementById('yogam').innerHTML + '<span class="panchang-next-value">' + translateDynamic(panchang.Yoga.name) + '</span><BR><div class="divSmall"> '+formatDateTime(panchang.Yoga.start)+' <?php echo __("from"); ?> <BR>'+formatDateTime(panchang.Yoga.end)+' <?php echo __("to"); ?> </div>';
		});
	}
	
	document.addEventListener('DOMContentLoaded', function() {
		const btn = document.getElementById('showNakshatraBtn');
		if (btn) {
			btn.addEventListener('click', function() {
				if (currentNakshatraStart && currentNakshatraEnd && currentNakshatraName) {
					displayNakshatraDivisions(currentNakshatraStart, currentNakshatraEnd, currentNakshatraName);
				} else {
					alert('<?php echo addslashes(__("please_load_panchang")); ?>');
				}
			});
		} else {
			console.warn('Button with id="showNakshatraBtn" not found in DOM.');
		}
	});
	
	
	function displayNakshatraDivisions(startStr, endStr, startNakshatra) {
		const nakshatras_en = [
		"Ashwini", "Bharani", "Krittika", "Rohini", "Mrigashirsha", "Ardra", "Punarvasu",
		"Pushya", "Ashlesha", "Magha", "Purva Phalguni", "Uttara Phalguni", "Hasta", "Chitra",
		"Swati", "Vishakha", "Anuradha", "Jyeshtha", "Mula", "Purva Ashadha", "Uttara Ashadha",
		"Shravana", "Dhanishta", "Shatabhisha", "Purva Bhadrapada", "Uttara Bhadrapada", "Revati"
	];

	const nakshatras_ta = [
		"அசுவினி", "பரணி", "கிருத்திகை", "ரோகிணி", "மிருகசிரீஷம்", "திருவாதிரை", "புனர்பூசம்",
		"பூசம்", "ஆயில்யம்", "மகம்", "பூரம்", "உத்திரம்", "ஹஸ்தம்", "சித்திரை",
		"சுவாதி", "விசாகம்", "அனுஷம்", "கேட்டை", "மூலம்", "பூராடம்", "உத்திராடம்",
		"திருவோணம்", "அவிட்டம்", "சதயம்", "பூரட்டாதி", "உத்திரட்டாதி", "ரேவதி"
	];

	const start = new Date(startStr);
	let end = new Date(endStr);
	if (end <= start) end.setDate(end.getDate() + 1);

	const totalMinutes = (end - start) / (1000 * 60);
	const segmentMinutes = totalMinutes / 27;

	// Determine which label array to display depending on language
	const displayArray = (currentLanguage === 'english') ? nakshatras_en : nakshatras_ta;

	// Find start index by checking both Tamil and English arrays (handles mixed inputs)
	let startIndex = -1;
	for (let i = 0; i < 27; i++) {
		if (nakshatras_ta[i].toLowerCase() === startNakshatra.toLowerCase() || nakshatras_en[i].toLowerCase() === startNakshatra.toLowerCase()) {
			startIndex = i;
			break;
		}
	}

	if (startIndex === -1) {
		alert("Invalid Nakshatra name.");
		return;
	}

	const existing = document.getElementById("nakshatraOverlay");
	if (existing) existing.remove();

	const overlay = document.createElement("div");
	overlay.id = "nakshatraOverlay";
	overlay.className = "starDivition";  // 👈 apply CSS class

	const closeBtn = document.createElement("button");
	closeBtn.innerText = "✖";
	//closeBtn.style.cssText = "position:absolute;top:5px;right:5px;background:red;color:white;border:none;padding:5px 10px;cursor:pointer;border-radius:5px;font-weight:bold;text-box-trim: trim-both;";
	closeBtn.className = "close-btn";
	closeBtn.onclick = () => overlay.remove();
	overlay.appendChild(closeBtn);

	const title = `<DIV class="divTableCell"><?php echo addslashes(__('nakshatra_division')); ?><br><small style="font-weight:normal;">(${displayArray[startIndex]})</small></DIV>`;
	let outputHTML = title;

	// Start Table
	outputHTML += `
		<table style="width:100%;border-collapse:collapse;margin-top:10px;">
			<thead>
				<tr class="nakshatra-table-header">
					<th>#</th>
					<th>${'<?php echo addslashes(__("from")); ?>'}</th>
					<th>${'<?php echo addslashes(__("to")); ?>'}</th>
					<th>${'<?php echo addslashes(__("nakshatra")); ?>'}</th>
				</tr>
			</thead>
			<tbody>
	`;

	// 27 Nakshatra Divisions
	for (let i = 0; i < 27; i++) {
		const segStart = new Date(start.getTime() + i * segmentMinutes * 60000);
		const segEnd = new Date(start.getTime() + (i + 1) * segmentMinutes * 60000);
		const currentNak = displayArray[(startIndex + i) % 27];

		outputHTML += `
			<tr>
				<td>${i + 1}</td>
				<td>${formatTime(segStart)}</td>
				<td>${formatTime(segEnd)}</td>
				<td>${currentNak}</td>
			</tr>
		`;
	}

	const contentDiv = document.createElement("div");
	contentDiv.style.padding = "20px"; // Add padding around the table
	contentDiv.innerHTML = outputHTML;
	overlay.appendChild(contentDiv);
	document.body.appendChild(overlay);

	// Helper function to format time as hh:mm
	function formatTime(date) {
		const hrs = date.getHours().toString().padStart(2, '0');
		const mins = date.getMinutes().toString().padStart(2, '0');
		return `${hrs}:${mins}`;
	}
}

	
	
	function getKaranaPlanet(karan) {
		const karanaPlanetArray = {
				"பவ": "செ",
				"பாலவ": "ரா",
				"கௌலவ": "சனி",
				"தைதுலை": "சுக்",
				"கரசை": "சந்",
				"வணிசை": "சூரி",
				"பத்தரை": "கே",
				"சகுனி": "சனி",
				"சதுஷ்பாத": "குரு",
				"நாகவ": "ரா",
				"கிம்ஸ்துக்கினம்": "புத"
		};

		return karanaPlanetArray[karan];
	}
	
	$(document).ready(function(){
		updatePanchangData();
		date='<?php echo $chart_date; ?>';
		time='<?php echo $chart_time; ?>';
		place='<?php echo $place; ?>';

		url = 'hora.php?date='+date+'&time='+time+'&place='+place;

		$('#hora').load(url+' div#horaLordTxt'); //getting the current hora lord running the hora.php and extract only the lord text
		
		//$('#hora').load(url); //getting the current hora lord running the hora.php and extract only the lord text
		
		var param = '<?php echo $flag; ?>';
		
		if(param==1){ //Panchang details alone
			document.getElementById('panchangForm').style.display = 'none';
			document.getElementById('panchangDetailsHeading').style.display = 'reset';
			document.getElementById('panchangDetails').style.display = 'table';
			document.getElementById('transitChartHeading').style.display = 'none';
			document.getElementById('transitChart').style.display = 'none';
		}
		else if(param==2){ //transit chart alone
			document.getElementById('panchangForm').style.display = 'none';
			document.getElementById('panchangDetailsHeading').style.display = 'none';
			document.getElementById('panchangDetails').style.display = 'none';
			document.getElementById('transitChartHeading').style.display = 'reset';
		document.getElementById('transitChart').style.display = 'reset';
	}
	
	// Language is now handled server-side via PHP, no client-side translation needed
	
}); 
	
	function generateMuhurtam(today_sunrise,tomm_sunrise) {
	   var count = 0; // mukurt_arr now provided by server-side `mukurt_arr` variable
      
       var html_str='';
      
       html_str='<center><div class="divTable blueTable" style="width:100%;height:100%">';
       html_str=html_str+'<div class="divTableRow"><div class="divAnthraHeading" colspan="3">முகூர்த்தங்கள்</div></div></div>';
       html_str=html_str+'<center><div class="divTable blueTable" style="width:100%;height:100%">';
	   
	   
	   todaysunrise = new Date(today_sunrise);
	   tommsunrise = new Date(tomm_sunrise);
	   muhurt_time = (tommsunrise.getTime() - todaysunrise.getTime()) / (1000 * 60 )/30;

		start_time = new Date(todaysunrise.getTime());
		
		html_str = html_str+'<div class="divTableRow"><div class="divMuhurtHeading"><?php _e("start"); ?></div><div class="divMuhurtHeading"><?php _e("end"); ?></div><div class="divMuhurtHeading"><?php _e("muhurtham"); ?></div><div class="divMuhurtHeading"><?php _e("result"); ?></div></div>';

	for (var i = 0; i < 30; i++) {
		   end_time = new Date((start_time.getTime()+muhurt_time*60000));
		   
		   stime=String(start_time.getDate()).padStart(2, '0')+'-'+String(start_time.getMonth()+1).padStart(2, '0')+'-'+start_time.getFullYear()+' '+String(start_time.getHours()).padStart(2, '0')+':'+String(start_time.getMinutes()).padStart(2, '0');
		   etime=String(end_time.getDate()).padStart(2, '0')+'-'+String(end_time.getMonth()+1).padStart(2, '0')+'-'+end_time.getFullYear()+' '+String(end_time.getHours()).padStart(2, '0')+':'+String(end_time.getMinutes()).padStart(2, '0');
           //console.log(stime+'-'+etime+'   '+mukurt_arr[i]);
		   
		   if ((mukurt_arr[i][1]).indexOf("அசுபமானது") != -1 || (mukurt_arr[i][1]).indexOf("Inauspicious") != -1) {
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaRed">' + stime + '</div><div class="divMurtaRed">' + etime + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   } else if ((mukurt_arr[i][1]).indexOf("மங்களகரமானது") != -1 || (mukurt_arr[i][1]).indexOf("Auspicious") != -1 || (mukurt_arr[i][1]).indexOf("Very Auspicious") != -1) {
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaBrightGreen">' + stime + '</div><div class="divMurtaBrightGreen">' + etime + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   } else {
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaGreen">' + stime + '</div><div class="divMurtaGreen">' + etime + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   }
          
		   start_time = end_time;
       }
       
       html_str=html_str+"</div></center>";
      
       document.getElementById('muhurthamDiv').innerHTML = html_str;
       document.getElementById('muhurthamDiv').style.display = 'block';
     }
	
function generateMuhurtam_default() {
	var count = 0; // using server-provided `mukurt_arr` variable (language-aware)

    var html_str = '<div class="table-container">';
    html_str += '<button class="close-btn" onclick="closeTable()">X</button>';
    html_str += '<div class="scrollable-table">';
    html_str += '<center><div class="divTable blueTable" style="width:100%;height:100%">';

    // this is to negate sunrise calculation
    today_sunrise = '<?php echo $chart_date; ?>' + ' 06:00:00';
    tomm_sunrise = '<?php echo $next_day; ?>' + ' 06:00:00';
    todaysunrise = new Date(today_sunrise);
    tommsunrise = new Date(tomm_sunrise);
    muhurt_time = (tommsunrise.getTime() - todaysunrise.getTime()) / (1000 * 60) / 30;
    start_time = new Date(todaysunrise.getTime());
	html_str = html_str + '<div class="divTableRow"><div class="divMuhurtHeading"><?php _e("start"); ?></div><div class="divMuhurtHeading"><?php _e("end"); ?></div><div class="divMuhurtHeading"><?php _e("muhurtham"); ?></div><div class="divMuhurtHeading"><?php _e("result"); ?></div></div>';

    for (var i = 0; i < 30; i++) {
        end_time = new Date((start_time.getTime() + muhurt_time * 60000));
        stime = String(start_time.getDate()).padStart(2, '0') + '-' + String(start_time.getMonth() + 1).padStart(2, '0') + '-' + start_time.getFullYear() + ' ' + String(start_time.getHours()).padStart(2, '0') + ':' + String(start_time.getMinutes()).padStart(2, '0');
        etime = String(end_time.getDate()).padStart(2, '0') + '-' + String(end_time.getMonth() + 1).padStart(2, '0') + '-' + end_time.getFullYear() + ' ' + String(end_time.getHours()).padStart(2, '0') + ':' + String(end_time.getMinutes()).padStart(2, '0');

		if ((mukurt_arr[i][1]).indexOf("அசுபமானது") != -1 || (mukurt_arr[i][1]).indexOf("Inauspicious") != -1) {
			html_str = html_str + '<div class="divTableRow"><div class="divMurtaRed">' + stime + '</div><div class="divMurtaRed">' + etime + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		} else if ((mukurt_arr[i][1]).indexOf("மங்களகரமானது") != -1 || (mukurt_arr[i][1]).indexOf("Auspicious") != -1 || (mukurt_arr[i][1]).indexOf("Very Auspicious") != -1) {
			html_str = html_str + '<div class="divTableRow"><div class="divMurtaBrightGreen">' + stime + '</div><div class="divMurtaBrightGreen">' + etime + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		} else {
			html_str = html_str + '<div class="divTableRow"><div class="divMurtaGreen">' + stime + '</div><div class="divMurtaGreen">' + etime + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		}
        start_time = end_time;
    }

    html_str = html_str + "</div></div></div>";
    document.getElementById('muhurthamDiv').innerHTML = html_str;
    document.getElementById('muhurthamDiv').style.display = 'block';
}

function closeTable() {
    document.getElementById('muhurthamDiv').style.display = 'none';
}

function showPushkaraTimes() {
    if (!pushkaraDataLoaded) {
		alert('<?php echo addslashes(__("pushkara_loading")); ?>');
        return;
    }
	var existing = document.getElementById('pushkaraOverlay');
	if (existing) existing.remove();

	var pushkaraContent = document.getElementById('pushkara-container').innerHTML;
	var overlay = document.createElement('div');
	overlay.id = 'pushkaraOverlay';
	overlay.className = 'starDivition';

	var closeBtn = document.createElement('button');
	closeBtn.className = 'close-btn';
	closeBtn.innerText = '✖';
	closeBtn.onclick = function() { overlay.remove(); };
	overlay.appendChild(closeBtn);

	var contentDiv = document.createElement('div');
	contentDiv.style.padding = '20px';
	contentDiv.innerHTML = pushkaraContent;
	overlay.appendChild(contentDiv);

	document.body.appendChild(overlay);
}

function closePushkaraTable() {
	var existing = document.getElementById('pushkaraOverlay');
	if (existing) existing.remove();
}


	function generateMuhurtam_default1() {
       var count = 0;
          
	   // use server-provided mukurt_arr
      
       var html_str='';
      
       html_str='<center><div class="divTable blueTable" style="width:100%;height:103px">';
       //html_str=html_str+'<div class="divTableRow"><div class="divAnthraHeading" colspan="3">முகூர்த்தங்கள்</div></div></div>';
       html_str=html_str+'<center><div class="divTable blueTable" style="width:100%;height:100%">';
	   
	   //this is to negate sunrise calculation
	   today_sunrise = '<?php echo $chart_date; ?>' + ' 06:00:00';
	   
	   
	   tomm_sunrise = '<?php echo $next_day; ?>' + ' 06:00:00';
	   
	   todaysunrise = new Date(today_sunrise);
	   tommsunrise = new Date(tomm_sunrise);
	   muhurt_time = (tommsunrise.getTime() - todaysunrise.getTime()) / (1000 * 60 )/30;

		start_time = new Date(todaysunrise.getTime());
		
		html_str = html_str+'<div class="divTableRow"><div class="divMuhurtHeading">ஆரம்பம்</div><div class="divMuhurtHeading">முடிவு</div><div class="divMuhurtHeading">முகூர்த்தம்</div><div class="divMuhurtHeading">பலன்</div></div>';

       for (var i = 0; i < 30; i++) {
		   end_time = new Date((start_time.getTime()+muhurt_time*60000));
		   
		   stime=String(start_time.getDate()).padStart(2, '0')+'-'+String(start_time.getMonth()+1).padStart(2, '0')+'-'+start_time.getFullYear()+' '+String(start_time.getHours()).padStart(2, '0')+':'+String(start_time.getMinutes()).padStart(2, '0');
		   etime=String(end_time.getDate()).padStart(2, '0')+'-'+String(end_time.getMonth()+1).padStart(2, '0')+'-'+end_time.getFullYear()+' '+String(end_time.getHours()).padStart(2, '0')+':'+String(end_time.getMinutes()).padStart(2, '0');
           //console.log(stime+'-'+etime+'   '+mukurt_arr[i]);
		   
		   if ((mukurt_arr[i][1]).indexOf("அசுபமானது") != -1 || (mukurt_arr[i][1]).indexOf("Inauspicious") != -1) {
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaRed">' + stime + '</div><div class="divMurtaRed">' + etime + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaRed">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   } else if ((mukurt_arr[i][1]).indexOf("மங்களகரமானது") != -1 || (mukurt_arr[i][1]).indexOf("Auspicious") != -1 || (mukurt_arr[i][1]).indexOf("Very Auspicious") != -1) {
			   html_str = html_str + '<div class="divTableRow"><div class="divMurtaBrightGreen">' + stime + '</div><div class="divMurtaBrightGreen">' + etime + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaBrightGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   } else {
			html_str = html_str + '<div class="divTableRow"><div class="divMurtaGreen">' + stime + '</div><div class="divMurtaGreen">' + etime + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][0]) + '</div><div class="divMurtaGreen">' + mapIfEnglish(mukurt_arr[i][1]) + '</div></div>';
		   }
          
		   start_time = end_time;
       }
       
       html_str=html_str+"</div></center>";
      
       document.getElementById('muhurthamDiv').innerHTML = html_str;
       document.getElementById('muhurthamDiv').style.display = 'block';
     }
	 
	 function hide(){
		document.getElementById('muhurthamDiv').style.visibility='hidden';
	}
	
	function captureDivAsImage(divId, imgId) {
		return new Promise((resolve, reject) => {
			const div = document.getElementById(divId);
			if (!div) {
				reject(`Div with ID ${divId} not found`);
				return;
			}
			
			// Check if div has visible content
			if (div.offsetWidth === 0 || div.offsetHeight === 0) {
				reject(`Div ${divId} has no dimensions`);
				return;
			}

			html2canvas(div, {
				allowTaint: true,
				useCORS: true,
				scale: 2, // Higher quality
				logging: false,
				backgroundColor: null
			}).then(canvas => {
				// Convert the canvas to an image data URL
				const imgDataUrl = canvas.toDataURL('image/png');

				// Find the image element and update its src
				const imgElement = document.getElementById(imgId);
				if (imgElement) {
					imgElement.src = imgDataUrl;
					imgElement.style.border = '1px solid black';
					resolve(imgElement);
				} else {
					reject(`Image with ID ${imgId} not found`);
				}
			}).catch(error => {
				reject(`Failed to capture div ${divId}: ${error}`);
			});
		});
	}
	
	function waitForImagesToLoad(callback) {
		const images = document.images;
		let loadedCount = 0;
		const totalImages = images.length;

		if (totalImages === 0) {
			callback();
			return;
		}

		for (let i = 0; i < totalImages; i++) {
			if (images[i].complete) {
				loadedCount++;
			} else {
				images[i].addEventListener('load', () => {
					loadedCount++;
					if (loadedCount === totalImages) callback();
				});
				images[i].addEventListener('error', () => {
					loadedCount++;
					if (loadedCount === totalImages) callback();
				});
			}
		}

		if (loadedCount === totalImages) callback();
	}
	
	// Persistent status logging
	function logStatus(message, isError = false) {
		const statusDiv = document.getElementById('captureStatus');
		if (statusDiv && isError) {
			const timestamp = new Date().toLocaleTimeString();
			const color = isError ? 'red' : 'green';
			statusDiv.style.display = 'block';
			statusDiv.innerHTML += `<div style="color:${color}">[${timestamp}] ${message}</div>`;
			// Keep last 20 messages
			const lines = statusDiv.querySelectorAll('div');
			if (lines.length > 20) {
				lines[0].remove();
			}
		}
		console.log(message);
	}
	
	// Wait for element to be fully rendered with content
	function waitForElementReady(elementId, timeout = 3000) {
		return new Promise((resolve) => {
			const startTime = Date.now();
			logStatus(`Waiting for ${elementId}...`);
			const checkElement = () => {
				const element = document.getElementById(elementId);
				if (!element) {
					if (Date.now() - startTime < timeout) {
						setTimeout(checkElement, 100);
					} else {
						logStatus(`${elementId} not found (timeout)`, true);
						resolve(false);
					}
					return;
				}
				
				// Check if element has content (text, SVG, canvas, or child elements)
				const hasContent = element.textContent.trim().length > 0 || 
								 element.querySelector('svg') !== null ||
								 element.querySelector('canvas') !== null ||
								 element.children.length > 0;
				
				if (hasContent) {
					logStatus(`${elementId} ready (${Date.now() - startTime}ms)`);
					// Give extra 200ms for rendering to complete
					setTimeout(() => resolve(true), 200);
				} else if (Date.now() - startTime < timeout) {
					setTimeout(checkElement, 100);
				} else {
					logStatus(`${elementId} has no content (timeout)`, true);
					resolve(false);
				}
			};
			checkElement();
		});
	}
	
	// Capture div as image with retry mechanism
	function captureDivAsImageWithRetry(divId, imgId, maxRetries = 2) {
		let attempts = 0;
		
		const attemptCapture = () => {
			attempts++;
			logStatus(`Capturing ${divId} (attempt ${attempts})...`);
			return captureDivAsImage(divId, imgId)
				.then(() => {
					logStatus(`✓ ${divId} captured successfully`);
				})
				.catch(error => {
					logStatus(`✗ Capture attempt ${attempts} failed for ${divId}: ${error}`, true);
					if (attempts < maxRetries) {
						// Wait 500ms before retry
						return new Promise(resolve => setTimeout(resolve, 500))
							.then(() => attemptCapture());
					} else {
						logStatus(`✗✗ Failed to capture ${divId} after ${maxRetries} attempts`, true);
						return Promise.reject(error);
					}
				});
		};
		
		return attemptCapture();
	}

		
	function displayOnePagePanchang() {
		// Ensure snapshot collected (if not yet) and delay until dynamic fields ready
		const ensureReady = () => {
			// If snapshot exists and key dynamic fields populated, proceed; else retry shortly
			const snap = window.panchangSnapshot || {};
			const needed = ['hora','thithi','nak','karanam','yogam'];
			const allHave = needed.every(k => (snap[k]||'').trim().length > 0);
			if(!allHave){
				// attempt capture again then retry
				capturePanchangValues().then(()=> setTimeout(displayOnePagePanchang,200));
				return false;
			}
			return true;
		};

		if(!ensureReady()) return; // will re-call after data ready
		const container = document.getElementById('divContainer');
		const divStarlord = document.getElementById('starlordBodyMutable');
		const divChart = document.getElementById('transitChart');
		const divDate = document.getElementById('edate');
		const divDay = document.getElementById('day');
		const divPlace = document.getElementById('placename');
		const divVivah = document.getElementById('vivahaChakram');
		
		const divHora = document.getElementById('hora');
		const divPakshi = document.getElementById('pakshi');
		const divAmirYogam = document.getElementById('amirtha_yogam');
		const divNethram = document.getElementById('nethram');
		const divJeevan = document.getElementById('jeevan');
		const divNokku = document.getElementById('nokku');
		const divLagnam = document.getElementById('lagnam');
		
		const divRaasi = document.getElementById('raasi');
		const divAbhijith = document.getElementById('abhijith');
		const divAvayogi = document.getElementById('avayogi');
		const divThithi_soonyam = document.getElementById('thithi_soonyam');
		const divRahu_kaalam = document.getElementById('rahu_kaalam');
		const divEma_kantam = document.getElementById('ema_kantam');
		const divKuligai = document.getElementById('kuligai');
		const divSoolai = document.getElementById('soolai');
		const divChandraashtamam = document.getElementById('chandraashtamam');
		const divSunrise = document.getElementById('sunrise');
		const divSunset = document.getElementById('sunset');
		
		const DivVivSunNak = document.getElementById('vivSunNak');
		const DivVivMooNak = document.getElementById('vivMooNak');
		const DivVivChakram = document.getElementById("vivahaRes");
		
		
		// Clear container
		/*container.innerHTML = '';

		// Append divs in new order
		//container.appendChild(div3);
		container.appendChild(div1);
		container.appendChild(div4);
		container.appendChild(div2);
		// Add more divs in the desired order
		
		div3.innerHTML = div3.innerHTML;*/
		
	// Use snapshot values when available to avoid capturing empty async content
	const snap = window.panchangSnapshot || {};
	document.getElementById('date').innerHTML = (divDate.textContent || snap.date || '');
	document.getElementById('day_local').innerHTML = (divDay.textContent || snap.day_local || '') + "@7:00 AM";
	document.getElementById('localPlace').innerHTML = (divPlace.textContent || snap.localPlace || '');
	
	// Simple approach: get label from source table's divPanchangHeading, append value
	// This is how it worked originally - just copy from source panchang table
	const labelMapping = {
		'lHorai': 'hora',
		'lPakshi': 'pakshi', 
		'lAmirYogam': 'amirtha_yogam',
		'lNethram': 'nethram',
		'lJeevan': 'jeevan',
		'lNokku': 'nokku',
		'lLagnam': 'lagnam',
		'lRaasi': 'raasi',
		'lAbhijith': 'abhijith',
		'lAvayogi': 'avayogi',
		'lThithi_soonyam': 'thithi_soonyam',
		'lRahu_kaalam': 'rahu_kaalam',
		'lEma_kantam': 'ema_kantam',
		'lKuligai': 'kuligai',
		'lSoolai': 'soolai',
		'lChandraashtamam': 'chandraashtamam',
		'lSunrise': 'sunrise',
		'lSunset': 'sunset'
	};
	
	// Update each inline label: find source value div, get its heading text, combine
	Object.keys(labelMapping).forEach(labelId => {
		const valueId = labelMapping[labelId];
		const sourceValueDiv = document.getElementById(valueId);
		const labelEl = document.getElementById(labelId);
		
		if(!sourceValueDiv || !labelEl) return;
		
		// Find the heading (label) that precedes this value div in the source table
		let headingText = '';
		const parent = sourceValueDiv.parentElement; // divTableRow
		if(parent){
			const heading = parent.querySelector('.divPanchangHeading, .divPanchangHeadingRed');
			if(heading){
				headingText = heading.textContent.trim();
			}
		}
		
		// Get value from source div or snapshot
		const value = sourceValueDiv.textContent.trim() || snap[valueId] || '';
		
		if(headingText && value){
			labelEl.innerHTML = headingText + ' - ' + value;
		}
	});
	
	// Populate image captions from source table headings (thithi, nakshatra, yogam, karanam)
	const captionMapping = {
		'cap_thithi': 'thithi',
		'cap_nak': 'nak',
		'cap_yogam': 'yogam',
		'cap_karanam': 'karanam'
	};
	
	Object.keys(captionMapping).forEach(capId => {
		const valueId = captionMapping[capId];
		const sourceDiv = document.getElementById(valueId);
		const capEl = document.getElementById(capId);
		
		if(!sourceDiv || !capEl) return;
		
		const parent = sourceDiv.parentElement;
		if(parent){
			const heading = parent.querySelector('.divPanchangHeading');
			if(heading){
				capEl.textContent = heading.textContent.trim();
			}
		}
	});
	
	// Populate vivaha chakram caption
	const vivCap = document.getElementById('cap_vivaha');
	if(vivCap){
		// Find vivaha chakram heading from source
		const allHeadings = document.querySelectorAll('.divPanchangHeading');
		for(const h of allHeadings){
			if(h.textContent.includes('விவாஹ') || h.textContent.includes('Vivaha') || h.textContent.toLowerCase().includes('marriage')){
				vivCap.textContent = h.textContent.trim();
				break;
			}
		}
		// Fallback if not found
		if(!vivCap.textContent){
			vivCap.textContent = 'Vivaha Chakram';
		}
	}
	
	// Populate current_transit and graha_saaram captions
	const capCurrentTransit = document.getElementById('cap_current_transit');
	const capGrahaSaaram = document.getElementById('cap_graha_saaram');
	
	if(capCurrentTransit){
		const allHeadings = document.querySelectorAll('.divPanchangHeading');
		for(const h of allHeadings){
			if(h.textContent.toLowerCase().includes('transit') || h.textContent.includes('கிரக')){
				capCurrentTransit.textContent = h.textContent.trim();
				break;
			}
		}
		if(!capCurrentTransit.textContent){
			capCurrentTransit.textContent = 'Current Transit';
		}
	}
	
	if(capGrahaSaaram){
		const allHeadings = document.querySelectorAll('.divPanchangHeading');
		for(const h of allHeadings){
			if(h.textContent.toLowerCase().includes('saaram') || h.textContent.includes('சாரம்')){
				capGrahaSaaram.textContent = h.textContent.trim();
				break;
			}
		}
		if(!capGrahaSaaram.textContent){
			capGrahaSaaram.textContent = 'Graha Saaram';
		}
	}
	
		//document.getElementById('lviv_chakram').innerHTML = document.getElementById('lviv_chakram').innerHTML + ' - ' + DivVivSunNak.textContent + "<BR>" + DivVivMooNak.textContent + "<BR>" + DivVivChakram.innerHTML;
		
		const lviv = document.getElementById('lviv_chakram');

		// Add separator and text
		// Preserve original bold label if present
		if(!lviv.dataset.done){
			lviv.append(' - ' + DivVivSunNak.textContent);
			lviv.append(document.createElement("br"));
			lviv.append(DivVivMooNak.textContent);
			lviv.append(document.createElement("br"));
			lviv.appendChild(DivVivChakram.cloneNode(true));
			lviv.dataset.done = '1';
		}
		// (Refactored once-only append logic)

		logStatus('=== Starting One-Page Panchang Display ===');
		
		// Wait for all chart elements to be ready before capturing
		logStatus('Step 1: Checking element readiness...');
		Promise.all([
			waitForElementReady('transitChart'),
			waitForElementReady('starlordBodyMutable'),
			waitForElementReady('thithi'),
			waitForElementReady('nak'),
			waitForElementReady('karanam'),
			waitForElementReady('yogam')
		]).then((results) => {
			logStatus(`Step 2: Elements ready. Results: ${results.filter(r => r).length}/6`);
			
			// Convert waitForImagesToLoad to Promise
			return new Promise((resolve) => {
				logStatus('Step 3: Waiting for images to load...');
				waitForImagesToLoad(() => {
					logStatus('Step 4: Images loaded. Starting capture...');
					resolve();
				});
			});
		}).then(() => {
			// Add small delay to ensure all rendering complete
			return new Promise(resolve => setTimeout(resolve, 300));
		}).then(() => {
			logStatus('Step 5: Capturing images...');
			// Use retry mechanism for more reliable capture
			return Promise.all([
				captureDivAsImageWithRetry('transitChart', 'chart'),
				captureDivAsImageWithRetry('starlordBodyMutable', 'starlord'),
				captureDivAsImageWithRetry('thithi', 'thithi_local'),
				captureDivAsImageWithRetry('nak', 'nak_local'),
				captureDivAsImageWithRetry('karanam', 'kara_local'),
				captureDivAsImageWithRetry('yogam', 'yogam_local')
			]);
		}).then(() => {
			logStatus('✓✓✓ All images captured successfully! ✓✓✓');
			
			// Update footer with current date/time before saving
			updateFooter();
			
			// Save content to localStorage and redirect to display page
			setTimeout(() => {
				saveContent();
			}, 1000);
			
			logStatus('Content saved. Redirecting to display page...');
		}).catch(error => {
			logStatus(`ERROR: ${error}`, true);
			alert('Error capturing images. Please try again.');
		});
	}
	
	function saveContent() {
		const content = document.getElementById('divContainer').innerHTML;
		localStorage.setItem('divContent', content);
		// Redirect to the display page
		window.location.href = 'pdfs/onePagePanchang_pdf.html';
	}
	
	function updateFooter() {
		const footerText = document.getElementById('footerText');
		if (!footerText) return;
		
		const now = new Date();
		const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
		
		const dayName = days[now.getDay()];
		const day = now.getDate();
		const month = months[now.getMonth()];
		const year = now.getFullYear();
		
		let hours = now.getHours();
		const minutes = String(now.getMinutes()).padStart(2, '0');
		const seconds = String(now.getSeconds()).padStart(2, '0');
		const ampm = hours >= 12 ? 'pm' : 'am';
		hours = hours % 12 || 12;
		
		const timeStr = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
		
		// Tamil text + English text
		const tamilText = currentLanguage === 'tamil' ? 'தமிழ் சங்கவள்ளி தேவசேநா - விதியாவயார்' : 'Tamil Sangavalli Thevasena - Vithiyaavayaar';
		footerText.textContent = `${tamilText} & Periyanayaki Astro Solutions | ${dayName} ${day} ${month}, ${year} at ${timeStr}`;
	}
	
	function displayOrigPanchang() {
		const container = document.getElementById('divContainer');
		const originalContainer = document.getElementById('divOrigContainer');
		const onePageBtn = document.getElementById('onePageBtn');
		const titleBar = document.getElementById('title');
		
		container.style.display = 'none';
		originalContainer.style.display = 'block';
		onePageBtn.style.display = 'initial';
		titleBar.style.display = 'initial';
		
		// TEMPORARILY DISABLED FOR DEBUGGING - check console first
		setTimeout(() => { saveContent(); }, 1000);
		// console.log('Auto-redirect disabled. Check console output above. Uncomment saveContent() to re-enable.');
	}
	function saveContent() {
		const content = document.getElementById('divContainer').innerHTML;
		localStorage.setItem('divContent', content);
		// Redirect to the destination page
		window.location.href = 'onePagePanchang_pdf.html';
	}
	
	function printDialog(){
		window.print();
	}
	
	function printPDF_orig() {
		const element = document.getElementById("targetDiv"); // The element you want to print
		html2pdf().from(element).set({
			filename: 'custom_filename.pdf' // Set desired filename
		}).save();
	}
	
	function printPDF() {
	  const element = document.getElementById("divContainer");
	  html2pdf()
		.from(element)
		.set({
		  margin: 5,
		  filename: 'panchang.pdf',
		  jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
		})
		.save();
	}



	function displayOrigPanchang(){
		const originalContainer = document.getElementById('divOrigContainer');
		const backBtn = document.getElementById('backBtn');
		const container = document.getElementById('divContainer');
		const onePageBtn = document.getElementById('onePageBtn');
		
		// reset again
		originalContainer.style.display = 'initial';
		backBtn.style.display = 'none';
		container.style.display = 'none';
		onePageBtn.style.display = 'initial';
	}
	
	function translateLanguage(language) {
	  // Submit the form to change language via POST instead of cookie
	  console.log('translateLanguage called with:', language);
	  
	  // Create a form and submit it
	  var form = document.getElementById('panchangForm');
	  if (form) {
	    // The select already has name='language', so just submit the form
	    form.submit();
	  } else {
	    // Fallback to cookie method
	    console.log('Form not found, using cookie method');
	    document.cookie = 'language=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
	    const expires = new Date();
	    expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
	    document.cookie = 'language=' + language + '; expires=' + expires.toUTCString() + '; path=/';
	    setTimeout(function() {
	      location.reload();
	    }, 100);
	  }
	}
	
	function transliterateTamilToHindi(){
		//no hindi as of now
	}
	
	</script>
</head>

<body id='mybody1' class='theme-panchang-dark theme-landing'>
<div class="karanamDiv" id="karanamDiv" onclick="hideKaranamDiv()"><!-- <img src='images/karanam.png' /> --></div>
<!-- <div class="muhurthamDiv" id="muhurthamDiv" onclick="this.style.display='none'"></div> -->
<div class="muhurthamDiv" id="muhurthamDiv"></div>
<div class="pushkaraDiv" id="pushkaraDiv"></div>
<div id="loader" class="center"></div>


<?php

/*function planetSort($inArray)
{
	global $planetDeg, $planetDeg_default, $DEBUG;
	$count = 0;
	
	foreach ($inArray as $key => $line)
	{
		$row = explode(',',$line);
		$count = $count + 1;
		
		if($count>8 && $count!=21) {continue;}

		debug($count."[".$row[0]."|".$row[1]."|".$row[2]."]<Br>");
		$planetDeg[trim($row[0])] = round(fmod(floatval($row[1]), 30.0),2);
	}
	
	debug("PLANET+DEGREE");
	if($DEBUG==1){var_dump($planetDeg);}
	
	debug("PLANET+DEGREE+SORTED");
	asort($planetDeg);
	
	if($DEBUG==1){var_dump($planetDeg);}
	
	foreach ($planetDeg as $key => $val) {
		if($val!=null or $val>=0) {
			
			switch (trim($key)) {
				case "Sun":
					$planet="சூரி";
					break;
				case "Moon":
					$planet="சந்";
					break;
				case "Mercury":
					$planet="புத";
					break;
				case "Venus":
					$planet="சுக்";
					break;
				case "Mars":
					$planet="செ";
					break;
				case "Jupiter":
					$planet="குரு";
					break;
				case "Saturn":
					$planet="சனி";
					break;
				//case "true Node":
				case "mean Node":
					$planet="ரா";
					break;
				case "Ascendant":
					$planet="லக்";
					break;
				case "mandhi":
					$planet="மா";
					break;
				default:
					break;
			}
			
			$out_planet_degree[$planet] = $val;
		}
	}
	
	$planetDeg = $out_planet_degree;
}

function calculateBirthPanchang(){
	global $moon_degree, $moon_house, $sun_house, $sun_degree, $dayofweek, $g_nak, $g_karan, $panchang,$g_nak_pad_idx,$full_sun_deg,$full_moon_deg;
	
	$tithi = array("Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Poornima/பௌர்ணமி","Prathame/பிரதமை","Dwithiya/துவிதியை","Thrithiya/திருதியை","Chathurthi/சதுர்த்தி","Panchami/பஞ்சமி","Shrashti/சஷ்டி","Saptami/சப்தமி","Ashtami/அஷ்டமி","Navami/நவமி","Dashami/தசமி","Ekadashi/ஏகாதசி","Dwadashi/துவாதசி","Thrayodashi/திரயோதசி","Chaturdashi/சதுர்த்தசி","Amavasya/அமாவாசை");
	
	$karan_sukla = array(array("Kimstughna/கிம்ஸ்துக்கினம்","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"));
	$karan_krishna = array(array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Bava/பவ"),
						array("Balava/பாலவ","Kaulava/கௌலவ"),
						array("Taitula/தைதுலை","Garija/கரசை"),
						array("Vanija/வணிசை","Visti/பத்தரை"),
						array("Bava/பவ","Balava/பாலவ"),
						array("Kaulava/கௌலவ","Taitula/தைதுலை"),
						array("Garija/கரசை","Vanija/வணிசை"),
						array("Visti/பத்தரை","Sakuni/சகுனி"),
						array("Chatuspada/சதுஷ்பாத","Naga/நாகவ"));
	
	$weekday_arr = array("Sunday/ஞாயிற்றுக்கிழமை","Monday/திங்கட்கிழமை","Tuesday/செவ்வாய்க்கிழமை","Wednesday/புதன்கிழமை",
					"Thursday/வியாழக்கிழமை","Friday/வெள்ளிக்கிழமை","Saturday/சனிக்கிழமை");
	
	$panchang = array();
	
	$nak_array = array("dummy","Aswini 1/அசுவினி 1","Aswini 2/அசுவினி 2","Aswini 3/அசுவினி 3","Aswini 4/அசுவினி 4",
						"Bharani 1/பரணி 1","Bharani 2/பரணி 2","Bharani 3/பரணி 3","Bharani 4/பரணி 4",
						"Krithika 1/கிருத்திகை 1","Krithika 2/கிருத்திகை 2","Krithika 3/கிருத்திகை 3","Krithika 4/கிருத்திகை 4",
						"Rohini 1/ரோகிணி 1","Rohini 2/ரோகிணி 2","Rohini 3/ரோகிணி 3","Rohini 4/ரோகிணி 4",
						"Mrigashirsha 1/மிருகசிரீஷம் 1","Mrigashirsha 2/மிருகசிரீஷம் 2","Mrigashirsha 3/மிருகசிரீஷம் 3","Mrigashirsha 4/மிருகசிரீஷம் 4",
						"Arudra 1/திருவாதிரை 1","Arudra 2/திருவாதிரை 2","Arudra 3/திருவாதிரை 3","Arudra 4/திருவாதிரை 4",
						"Punarvasu 1/புனர்பூசம் 1","Punarvasu 2/புனர்பூசம் 2","Punarvasu 3/புனர்பூசம் 3","Punarvasu 4/புனர்பூசம் 4",
						"Pushyami 1/பூசம் 1","Pushyami 2/பூசம் 2","Pushyami 3/பூசம் 3","Pushyami 4/பூசம் 4",
						"Ashlesha 1/ஆயில்யம் 1","Ashlesha 2/ஆயில்யம் 2","Ashlesha 3/ஆயில்யம் 3","Ashlesha 4/ஆயில்யம் 4",
						"Magha 1/மகம் 1","Magha 2/மகம் 2","Magha 3/மகம் 3","Magha 4/மகம் 4",
						"Puram 1/பூரம் 1","Puram 2/பூரம் 2","Puram 3/பூரம் 3","Puram 4/பூரம் 4",
						"Uthra phalguni 1/உத்திரம் 1","Uthra phalguni 2/உத்திரம் 2","Uthra phalguni 3/உத்திரம் 3","Uthra phalguni 4/உத்திரம் 4",
						"Hastha 1/ஹஸ்தம் 1","Hastha 2/ஹஸ்தம் 2","Hastha 3/ஹஸ்தம் 3","Hastha 4/ஹஸ்தம் 4",
						"Chitra 1/சித்திரை 1","Chitra 2/சித்திரை 2","Chitra 3/சித்திரை 3","Chitra 4/சித்திரை 4",
						"Swaathi 1/சுவாதி 1","Swaathi 2/சுவாதி 2","Swaathi 3/சுவாதி 3","Swaathi 4/சுவாதி 4",
						"Vishaakha 1/விசாகம் 1","Vishaakha 2/விசாகம் 2","Vishaakha 3/விசாகம் 3","Vishaakha 4/விசாகம் 4",
						"Anuraadha 1/அனுஷம் 1","Anuraadha 2/அனுஷம் 2","Anuraadha 3/அனுஷம் 3","Anuraadha 4/அனுஷம் 4",
						"Jyeshta 1/கேட்டை 1","Jyeshta 2/கேட்டை 2","Jyeshta 3/கேட்டை 3","Jyeshta 4/கேட்டை 4",
						"Moola 1/மூலம் 1","Moola 2/மூலம் 2","Moola 3/மூலம் 3","Moola 4/மூலம் 4",
						"Poorva Ashaada 1/பூராடம் 1","Poorva Ashaada 2/பூராடம் 2","Poorva Ashaada 3/பூராடம் 3","Poorva Ashaada 4/பூராடம் 4",
						"Uthra Ashaada 1/உத்திராடம் 1","Uthra Ashaada 2/உத்திராடம் 2","Uthra Ashaada 3/உத்திராடம் 3","Uthra Ashaada 4/உத்திராடம் 4",
						"Shraavan 1/திருவோணம் 1","Shraavan 2/திருவோணம் 2","Shraavan 3/திருவோணம் 3","Shraavan 4/திருவோணம் 4",
						"Dhanishta 1/அவிட்டம் 1","Dhanishta 2/அவிட்டம் 2","Dhanishta 3/அவிட்டம் 3","Dhanishta 4/அவிட்டம் 4",
						"Shathabhisha 1/சதயம் 1","Shathabhisha 2/சதயம் 2","Shathabhisha 3/சதயம் 3","Shathabhisha 4/சதயம் 4",
						"Purva Bhadrapada 1/பூரட்டாதி 1","Purva Bhadrapada 2/பூரட்டாதி 2","Purva Bhadrapada 3/பூரட்டாதி 3","Purva Bhadrapada 4/பூரட்டாதி 4",
						"Uttara Bhadrapada 1/உத்திரட்டாதி 1","Uttara Bhadrapada 2/உத்திரட்டாதி 2","Uttara Bhadrapada 3/உத்திரட்டாதி 3","Uttara Bhadrapada 4/உத்திரட்டாதி 4",
						"Revathi 1/ரேவதி 1","Revathi 2/ரேவதி 2","Revathi 3/ரேவதி 3","Revathi 4/ரேவதி 4");
						
	$yoga_arr = array("dummy","விஷ்கம்பம்","ப்ரீதி","ஆயுஷ்மான்", "செளபாக்யம்", "சோபனம்", "அதி கண்டம்", "சுகர்மம்", "திருதி", "சூலம்", "கண்டம்", "விருத்தி", "துருவம்","வ்யாகதம்",
					"ஹர்ஷணம்", "வஜ்ரம்","சித்தி","வியதீபாதம்","வரீயான்", "பரிகம்","சிவம்", "சித்தம்", "சாத்தியம்","சுபம்","சுப்பிரம்","பிராம்மியம்","ஐந்திரம்","வைதிருதி");
	
	//Day calculation
	$panchang["vaaram"] = $weekday_arr[$dayofweek];
	
	//Nakshatra calculation
	$full_moon_deg = (($moon_house-1) * 30 + $moon_degree);
	$nak_idx = ceil($full_moon_deg/3.333333);
	
	$panchang["nak"] = $nak_array[$nak_idx];
	$g_nak_pad_idx = $nak_idx;
	$g_nak = explode(' ',explode('/',$nak_array[$nak_idx])[1])[0];
	
	//Get nak end time
	$nak_end_time = findNakEndTime($nak_idx);
	$panchang["nak-end-time"] = $nak_end_time;
	debug("NAK_END_TIME=$nak_end_time");
	
	//thithi calculation
	$full_sun_deg = ($sun_house-1)*30+$sun_degree;
	
	if($full_moon_deg - $full_sun_deg < 180){
		if($full_moon_deg - $full_sun_deg < 0){
		
			$idx = ($full_moon_deg - $full_sun_deg+360) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 29){
				$thi_idx = 29;
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			}
		}
		else{
			$idx = ($full_moon_deg - $full_sun_deg) / 12;
			
			$thi_idx = floor($idx);
			
			if($idx > 14){
				$thi_idx = 14;
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			else{
				$panchang["thithi"] = $tithi[$thi_idx]."[shukla/வளர்]";
			}
			
		}
	}
	else{
		$idx = ($full_moon_deg - $full_sun_deg) / 12;
		
		if($idx < 1){
			$idx = 14;
			
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx];
		}
		else{
			$thi_idx = floor($idx);
		
			$panchang["thithi"] = $tithi[$thi_idx]."[krishna/தேய்]";
			
		}
	}
	
	debug("--THI IDX--".$idx."----");
	
	//Get nak end time
	$thithi_end_time = findThithiEndTime($thi_idx);
	$panchang["thithi-end-time"] = $thithi_end_time;
	debug("THITHI_END_TIME=$thithi_end_time");
	
	//karanam calculation
	$kar = $idx-floor($idx);
	//$kar = $thi_idx-floor($thi_idx);
	
	if($thi_idx < 15){
		
		if($kar < 0.5){
			$panchang["karan"] = $karan_sukla[$thi_idx][0];
		}
		else{
			$panchang["karan"] = $karan_sukla[$thi_idx][1];
		}
		
		debug("THI-IDX=".$thi_idx . "KAR=". $kar . "&&&&&&");
	}
	else{
		if($kar < 0.5){
			$panchang["karan"] = $karan_krishna[$thi_idx-15][0];
		}
		else{
			$panchang["karan"] = $karan_krishna[$thi_idx-15][1];
		}
		
		debug("THI-IDX-15=".($thi_idx-15) . "KAR=". $kar . "&&&&&&");
	}
	
	//Store karanam for future use
	$g_karan = $panchang["karan"];
	
	//calculate yogam
	$deg = ($full_moon_deg+$full_sun_deg);
	
	// deduct 360 if the sum is over 360 degrees
	if($deg > 360){
		$deg = $deg - 360;
	}
	
	$yoga_idx = ceil($deg / 13.333333); //divide by 13 deg 20 min in decimal, we will get the index of the yoga (1-27)
	
	$panchang["yoga"] = $yoga_arr[$yoga_idx];
	
	//insert panchang data into table for future use
	//insertArrayIntoDB("panchang",$panchang);
}*/

function displayHoroDetails()
{
	global $panchang, $in_name, $chart_date, $chart_time, $g_dasaBalance, $place, $g_thithi_soonyam_matching_arr,$g_dasaCurrent, $transit_date, $lagna_house, $country,$yogi_arr, 
		$g_pancha_pakshi, $g_thithi, $g_paksham, $moon_house, $today_sunrise, $tomo_sunrise,$today_sunset,$city,$g_nak, $g_thithi_idx, $g_yoga_idx, $nak_pad_idx, $g_kariNaal, 
		$lat,$lon, $g_padham, $g_rasi, $language ;
	
	// Determine language index: 0 for English, 1 for Tamil
	$lang_index = ($language === 'english') ? 0 : 1;
	
	echo "<div class='divTable blueTable' style='height:0;display:none;' id='panchangDetailsHeading'></div>";
	
	//display panchang table 
	echo "<div class='divTable blueTable' id='panchangDetails'>"; 
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('date') . "</div>";
			echo "<div class='divPanchang' id='edate'>".date('d-m-Y',strtotime($chart_date))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('tamil_date') . "</div>";
			echo "<div class='divPanchang'>".translate_tamil_date(getTamilDate())."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('time') . "</div>";
			echo "<div class='divPanchang' id='etime'>".date('h:i:s A',strtotime($chart_time))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('place') . "</div>";
			echo "<div class='divPanchang' id='placename'>".$city."<BR>(".$lat."°, ".$lon."°)</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('weekday') . "</div>";
			echo "<div class='divPanchang' id='day'>".explode('/',$panchang['vaaram'])[$lang_index]."&nbsp;</div>";
		echo "</div>";
		
	echo "<div class='divTableRow'>";
		echo "<div class='divPanchangHeading'>" . __('thithi') . "</div>";
			echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'></div>";
		$g_thithi = explode('/',explode('[',$panchang['thithi'])[0])[$lang_index];			if(strpos($panchang['thithi'],"Amavasya/அமாவாசை")===0 || strpos($panchang['thithi'],"Poornima/பௌர்ணமி")===0){
				$g_paksham = "";
				//echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi . $g_paksham . "<BR>". "</div>";
			}
			else{
				$g_paksham = rtrim(explode('/',$panchang['thithi'])[2],']');
				if ($lang_index == 0 && strpos($g_paksham, '/') !== false) {
					$g_paksham = explode('/', $g_paksham)[0];
				}
				//echo "<div class='divPanchang' id='thithi' OnClick='showDetailsDiv(event,1)' style='color:blue;cursor:pointer'>".$g_thithi ." [". $g_paksham . "]" . "</div>";
			}
		echo "</div>";
		
		displayDetailsDiv('thi',$g_thithi_idx);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('nakshatra') . "</div>";
			//echo "<div class='divPanchang' id='nak'>".explode('/',$panchang['nak'])[$lang_index]."<BR>".$panchang['nak-end-time']." வரை</div>";
			//echo "<div class='divPanchang' id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer'></div>";
			$nak_full = explode('/',$panchang['nak'])[$lang_index]; // Get full nakshatra string
			$nak_parts = explode(' ', $nak_full);
			$g_padham = end($nak_parts); // Get the last element which is the padham number
			array_pop($nak_parts); // Remove the padham number
			$nak_name = implode(' ', $nak_parts); // Get nakshatra name without padham
			$nak_name = translate_tamil_text($nak_name); // Translate nakshatra name using mapping file
			echo "<div class='divPanchang'  id='nak' OnClick='showDetailsDiv(event,5)' style='color:blue;cursor:pointer' data-nak-name='" . htmlspecialchars($nak_name) . "'> (".($g_padham).")</div>";
		echo "</div>";
		displayDetailsDiv('nak',ceil($nak_pad_idx)/4);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('yogam') . "</div>";
			//echo "<div class='divPanchang' id='yogam'>".$panchang['yoga']."</div>";
			echo "<div class='divPanchang' id='yogam'  OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'></div>";
		echo "</div>";
		displayDetailsDiv('yogi',$g_yoga_idx);
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('karanam') . "</div>";
			$karan = explode('/',$panchang['karan'])[$lang_index];
			//echo "<div class='divPanchang' id='karanam' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'>".$karan."(".getKaranaPlanet($karan).")</div>";
			echo "<div class='divPanchang' id='karanam' OnClick='showDetailsDiv(event,2)' style='color:blue;cursor:pointer'></div>";
		echo "</div>";
		displayDetailsDiv('kar',$karan);
		$yogi_arr = getYogi($panchang['yoga']);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('yogi') . "</div>";
			echo "<div class='divPanchang' id='yogi' OnClick='showDetailsDiv(event, 3)' style='color:blue;cursor:pointer'>".translate_tamil_text($yogi_arr[1])." (".translate_tamil_text($yogi_arr[0]).")</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('current_horai') . "</div>";
			echo "<div class='divPanchang' id='hora'></div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('amirthathi') . " " . __('yogam') . "</div>";
			echo "<div class='divPanchang' id='amirtha_yogam'>".translate_tamil_text(getAmrithathiYogam())."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('nethram') . "</div>";
			echo "<div class='divPanchang' id='nethram'>".translate_tamil_text(getNethram())."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('jeevan') . "</div>";
			echo "<div class='divPanchang' id='jeevan'>".translate_tamil_text(getJeevan())."</div>";
		echo "</div>";
		
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('star_direction') . "</div>";
			echo "<div class='divPanchang' id='nokku'>".translate_tamil_text(getDayType())."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('pancha_pakshi') . "</div>";
			$g_pancha_pakshi = getPanchaPakshi();
			echo "<div class='divPanchang' id='pakshi'>".translate_tamil_text($g_pancha_pakshi)."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('star_letters') . " </div>";
			echo "<div class='divPanchang'>".getStarLetters($g_nak)[0][0]."<BR>".getStarLetters($g_nak)[1][0]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('lagna') . "</div>";
			echo "<div class='divPanchang' id='lagnam'>".translate_tamil_text(getRasi($lagna_house-1))."</div>";
		echo "</div>";
		$g_rasi = getRasi($moon_house-1);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('rasi') . "</div>";
			echo "<div class='divPanchang' id='raasi'   OnClick='showDetailsDiv(event, 6)' style='color:blue;cursor:pointer'>" . translate_tamil_text($g_rasi) . "</div>";
		echo "</div>";
		displayDetailsDiv('rasi',"");
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('abhijit_time') . "</div>";
			echo "<div class='divPanchang' id='abhijith'>".getAbhijit()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('avayogi') . "</div>";
			echo "<div class='divPanchang' id='avayogi' OnClick='showDetailsDiv(event, 4)' style='color:blue;cursor:pointer'>".translate_tamil_text($yogi_arr[3])." (".translate_tamil_text($yogi_arr[2]).")</div>";
		echo "</div>";
		displayDetailsDiv('ayogi',$g_yoga_idx);
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('thithi_soonyam') . "</div>";
			echo "<div class='divPanchang' id='thithi_soonyam'>".translate_tamil_text($g_thithi_soonyam_matching_arr[0])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('rahu_kaalam') . "</div>";
			echo "<div class='divPanchang' id='rahu_kaalam'>".getRahuKaalam()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('ema_kantam') . "</div>";
			echo "<div class='divPanchang' id='ema_kantam'>".getEmaKantam()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('kuligai_time') . "</div>";
			echo "<div class='divPanchang' id='kuligai'>".getKulikai()."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('vaara_soolai') . "</div>";
			echo "<div class='divPanchang' id='soolai'>".translate_tamil_text(getVaaraSoolai()).',<BR> '. __('remedy') .' - '. translate_tamil_text(getSoolaiParigaaram()) ."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('chandraashtamam') . "</div>";
			echo "<div class='divPanchang' id='chandraashtamam'>".translate_tamil_text(getRasi(targetHouse($moon_house-1,6)))."</div>";
			//echo "<div class='divPanchang'>".getRasi(targetHouse($moon_house-1,8))."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeadingRed'>" . __('kari_naal') . "</div>";
			echo "<div class='divPanchang'>".translate_tamil_text($g_kariNaal)."</div>";
		echo "</div>";
		//echo "<div class='divTableRow'>";
			//echo "<div class='divPanchangHeading'>பிறப்பு தசா இருப்பு</div>";
			//echo "<div class='divPanchang'>".$g_dasaBalance[0]."</div>";
		//echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('current_dasa_bukthi') . "</div>";
			echo "<div class='divPanchang'>".translate_tamil_text($g_dasaCurrent[0])."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunrise') . "</div>";
			echo "<div class='divPanchang' id='sunrise'>".explode(' ',$today_sunrise)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('sunset') . "</div>";
			echo "<div class='divPanchang' id='sunset'>".explode(' ',$today_sunset)[1]."</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			echo "<div class='divPanchangHeading'>" . __('calculation_method') . "</div>";
			echo "<div class='divPanchang'>" . __('thirukanitham') . "<BR>(" . __('lahiri_ayanamsa') . ")</div>";
		echo "</div>";
	echo "</div>";
}

function displayTransitChart()
{
	global $sw_planet,$g_thithi_soonyam_arr, $language;
	$transit_language = ($language === 'english') ? 'english' : 'tamil';
	
	// Translate planet abbreviations if language is English
	$display_planets = $sw_planet;
	if ($language === 'english') {
		// Direct planet abbreviation mapping to avoid conflicts
		$planet_map = [
			'சூரி' => 'Su',
			'சந்' => 'Mo',
			'செ' => 'Ma',
			'சுக்' => 'Ve',
			'புத' => 'Me',
			'குரு' => 'Ju',
			'சனி' => 'Sa',
			'ரா' => 'Ra',
			'கே' => 'Ke',
			'லக்' => 'As'
		];
		
		for ($i = 0; $i < count($display_planets); $i++) {
			if (!empty($display_planets[$i])) {
				// Replace each planet abbreviation, handling HTML tags
				foreach ($planet_map as $tamil => $english) {
					$display_planets[$i] = str_replace($tamil, $english, $display_planets[$i]);
				}
				$display_planets[$i] = str_replace('(வ)', '(R)', $display_planets[$i]);
			}
		}
	}
	foreach ($display_planets as $i => $planetLabel) {
		if (!empty($planetLabel)) {
			$display_planets[$i] = preg_replace_callback(
				'/(<span\b[^>]*>.*?<\/span>|[^<\s]+)(?:-\d+°(?:\d+\')?)?/us',
				function ($matches) {
					return '<span class="transit-planet-entry">' . $matches[0] . '</span>';
				},
				$planetLabel
			);
		}
	}

	echo "<BR>";
	echo "<div class='divTable blueTable' style='height:40px;'>";
		echo "<div class='divTableRow' style='background-color:rgba(0,0,0,0)' id='transitChartHeading'>";
				echo "<div class='divPanchangHeading' colspan='3'>";
					echo "<H6 style='font-weight:bold;color:black'>" . __('current_transit') . "</H6>";
				echo "</div>";
			echo "</div>";
	echo "</div>";
	
echo <<<EOL
	<div class="divTable blueTable" id="transitChart" data-language="$transit_language">
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[11]</span>$g_thithi_soonyam_arr[11]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[0]</span>$g_thithi_soonyam_arr[0]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[1]</span>$g_thithi_soonyam_arr[1]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[2]</span>$g_thithi_soonyam_arr[2]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[10]</span>$g_thithi_soonyam_arr[10]</div>
					<div class="divTableCenterCell" style='font-size:10px'>
EOL;
	echo __('rasi_chart');
echo <<<EOL
</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[3]</span>$g_thithi_soonyam_arr[3]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[9]</span>$g_thithi_soonyam_arr[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell" style='font-size:10px'>D1</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[4]</span>$g_thithi_soonyam_arr[4]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[8]</span>$g_thithi_soonyam_arr[8]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[7]</span>$g_thithi_soonyam_arr[7]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[6]</span>$g_thithi_soonyam_arr[6]</div>
					<div class="divTableCell"><span class="transit-planet-label">$display_planets[5]</span>$g_thithi_soonyam_arr[5]</div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
			</div>
		</div>	
	</div>

EOL;

	echo "<div class='divTable blueTable transit-chart-legend' role='list' aria-label='Chart legend'>";
		echo "<span class='legend-item' role='listitem'><span class='legend-symbol legend-symbol-mandhi'>" . __('mandhi_abbr') . "</span><span>" . __('mandhi') . "</span></span>";
		echo "<span class='legend-item' role='listitem'><span class='legend-symbol legend-symbol-indhu'>" . __('indhu_abbr') . "</span><span>" . __('indhu') . " " . __('lagna') . "</span></span>";
		echo "<span class='legend-item' role='listitem'><span class='legend-symbol legend-symbol-thithi'>" . __('thrisoolam_abbr') . "</span><span>" . __('thithi_soonyam') . "</span></span>";
		if ($language === 'english') {
			echo "<span class='legend-item' role='listitem'><span class='legend-symbol'>(R)</span><span>Retrograde</span></span>";
		}
		echo "</div>";
}

if($DEBUG==1){
	echo "DATE: " . $chart_date."<BR>";
	echo "TIME: " . $chart_time."<BR>";
	echo "PLACE: " . $place."<BR>";
	echo "LAT: " . $lat."<BR>";
	echo "LONG: " . $lon."<BR>";
	echo "GMT offset: " . $gmt_offset."<BR>";
	echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
	echo "TZ: " . $TZ ."<BR>";
}

date_default_timezone_set($TZ);

if(date('I',strtotime($chart_date))==1) { // DST
   $hour=(int)strtok($gmt_offset_dst,":");
   $min=(int)strtok(":");
   $offset=$hour+$min/60;
   $tz_offset=$gmt_offset_dst;
}
else {
   $hour=(int)strtok($gmt_offset,":");
   $min=(int)strtok(":");
   $offset=$hour+$min/60;
   $tz_offset=$gmt_offset;
}

// ------- Swiss ephemeris // ------- 

$date = date("d.m.Y",strtotime($chart_date));
$time = date("H:i:s",strtotime($chart_time));
$datetime = $date . " " . $time;
$h_sys='P';

if($DEBUG==1){ 
	echo 'DATETIME =' . $datetime; echo '<br>';
	echo 'HOUSE SYSTEM =' . $h_sys; echo '<br>';
}

/**
 * Converting birth date/time to UTC
 */
//$birthTimestamp = date('Y-m-d H:i:s',strtotime($datetime));
$birthTimestamp = strtotime($datetime);
$utcTimestamp = $birthTimestamp - $offset * (60 * 60);

$date = date('d.m.Y', $utcTimestamp);
$time = date('H:i:s', $utcTimestamp);

if($DEBUG==1){ 
	echo 'DATE =' . $date ;
	echo 'TIME =' . $time;
	echo 'OFFSET =' . $offset; echo '<br>';
	echo 'birthTimestamp =' . $birthTimestamp; echo '<br>';
	echo 'utcTimestamp =' . $utcTimestamp; echo '<br>';
	echo 'UTC time=' . date('Y-m-d H:i:s', $utcTimestamp); echo '<br>';
	echo "----------".getUTCtimestamp($birthTimestamp,$offset);
}

$today_sunrise = $chart_date . " " . getSunrise(strtotime($chart_date),floatval($lat), floatval($lon));
$today_sunset = $chart_date . " " .  getSunset(strtotime($chart_date),floatval($lat), floatval($lon));
$tomo_sunrise = $next_day . " " .  getSunrise(strtotime($next_day),floatval($lat), floatval($lon));
$prev_sunset = $previous_day . " " .  getSunset(strtotime($previous_day),floatval($lat), floatval($lon));

debug("CHART date=".$chart_date);
debug("CHART TIME=".$chart_time);
debug("CHART SUNRISE=".date("H:i:s",strtotime($today_sunrise)));
debug("CHART SUNSET=".date("H:i:s",strtotime($today_sunset)));

// find if it's day or night for mandhi calculation
if(strtotime($datetime) >= strtotime($today_sunrise) && strtotime($datetime) <= strtotime($today_sunset)) {
	$day=1;
}else {
	$day=0;
}

debug("DAY=" . $day);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	debug('RUNNING ON WINDOWS');
	
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	//64 version runs little faster
	//exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // t - mean node
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output); // m - mean node
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	//exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);  // t - mean node
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456m -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>"); // m - mean node
}

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 
if($DEBUG==1){ 
	var_dump($output);
}

planetSort($output);

$count=0;
foreach ($output as $key => $line)
  {
	global $ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
	
	if($count>8 && $count!=21) {
		if($count > 8 and $count <= 20){
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = preg_replace('!\s+!', ' ', $row[0]);
			$house = explode(' ',$house)[1];
			$g_bhava_arr[$house-1] = array(round((float)trim($row[1]),4),DECtoDMS(fmod($row[1],30.0)));
			array_push($g_bhava_arr[$house-1],"");
		}
		
		continue;
	}
	
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	//$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	$sw_degree[$row[0]] = round($tot_degree[$key] - $sw_house[$key]*30,2);
	
	$planetDeg_default[$row[0]][0] = (float)($row[2]);
	
    if($count!=21){
		$sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($sun_house==0 and strpos($row[0],'Sun')===0){
		$sun_house = (int)($tot_degree[$key]/30)+1;
		$sun_degree = getDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	if($moon_house==0 and strpos($row[0],'Moon')===0){
		$moon_house = (int)($tot_degree[$key]/30)+1;
		$moon_degree = getDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	if($lagna_house==0 and strpos($row[0],'Ascendant')===0){
		$lagna_house = (int)($tot_degree[$key]/30)+1;
	}
	
	$sw_planet[$sw_house[$key]] = $sw_planet[$sw_house[$key]]." ".$row[0];
	
	$tot_degree[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	//if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
	if(strpos($row[0],"mean Node")===0){ // using === because this strpos returns 0 when mean Node found and also false(0) when it's not found. So we need to use ===
		
		$ragu_house = $sw_house[$key];
		if($DEBUG==1){ echo "ragu_house=" . $sw_house[$key]."<br>";}
	}
		
	if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$sw_house[$key]."|".$sw_planet[$key]."|".$sw_degree[$key]."|".$sw_dir[$key]."|"."<br>";}
  };
  
if($DEBUG==1){ 
  echo "<br>Birth[".$sw_planet[0]."]</br>";
  var_dump($sw_planet);
  var_dump($sw_degree);
  var_dump($sw_dir);
}

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}


$sw_planet = updatePlanet($sw_planet,$sw_dir, $ragu_house);

// Update Mandhi
$sw_planet = updateMandhi($sw_planet,$sw_planet,$sun_degree,$day,$dayofweek,$sun_house);

$Planet_degree=displayDegree($sw_degree); 

$sw_planet = sortDegree($sw_planet);

updateIndhuLagna();

calculatePanchang();
calculateBirthPanchang();

$formDateLabel = htmlspecialchars(__('date'), ENT_QUOTES, 'UTF-8');
$formTimeLabel = htmlspecialchars(__('time'), ENT_QUOTES, 'UTF-8');
$formPlaceLabel = htmlspecialchars(__('place'), ENT_QUOTES, 'UTF-8');
$placeSegments = explode('|', (string)$place);
$placeDisplayValue = trim($placeSegments[0]);
if (count($placeSegments) > 2) {
	$placeDisplayValue .= ', ' . trim($placeSegments[count($placeSegments) - 1]);
}
$formPlaceDisplay = htmlspecialchars($placeDisplayValue, ENT_QUOTES, 'UTF-8');
$formPlaceTimezone = htmlspecialchars(date('T', strtotime($chart_date)), ENT_QUOTES, 'UTF-8');
$formPlacePlaceholder = htmlspecialchars(__('search_place_placeholder'), ENT_QUOTES, 'UTF-8');
$formNavigationLabel = htmlspecialchars(__('date_navigation'), ENT_QUOTES, 'UTF-8');
$formAdvancedOptionsLabel = htmlspecialchars(__('advanced_options'), ENT_QUOTES, 'UTF-8');
$formNowLabel = htmlspecialchars(__('now'), ENT_QUOTES, 'UTF-8');
$formTodayLabel = htmlspecialchars(__('today'), ENT_QUOTES, 'UTF-8');
$timelinePrevYearLabel = htmlspecialchars(__('previous_year'), ENT_QUOTES, 'UTF-8');
$timelinePrevMonthLabel = htmlspecialchars(__('previous_month'), ENT_QUOTES, 'UTF-8');
$timelineNextMonthLabel = htmlspecialchars(__('next_month_full'), ENT_QUOTES, 'UTF-8');
$timelineNextYearLabel = htmlspecialchars(__('next_year_full'), ENT_QUOTES, 'UTF-8');
$timelineLocale = ($language === 'tamil') ? 'ta-IN' : 'en-IN';
$timelineDateItemsMarkup = '';
$timelineSelectedTimestamp = strtotime($chart_date);
for ($timelineOffset = -14; $timelineOffset <= 14; $timelineOffset++) {
	$timelineDateTimestamp = strtotime(($timelineOffset < 0 ? $timelineOffset : '+' . $timelineOffset) . ' days', $timelineSelectedTimestamp);
	$timelineDate = date('Y-m-d', $timelineDateTimestamp);
	$timelineCurrent = ($timelineOffset === 0) ? " aria-current='date'" : '';
	$timelineDateItemsMarkup .= "<button type='button' class='panchang-timeline-date' data-date='$timelineDate'$timelineCurrent>"
		. "<span class='panchang-timeline-weekday'></span>"
		. "<span class='panchang-timeline-day'>" . date('j', $timelineDateTimestamp) . "</span>"
		. "<span class='panchang-timeline-month-marker'></span>"
		. "</button>";
}
ob_start();
displayUserInfo($_SESSION['userinfo'], false);
$pageHeader = ob_get_clean();

echo <<<EOL
	<div class="title" id='title'></div>
		$pageHeader
		<div class='column-body' id='panchangBody'>
		<div id='divOrigContainer'>
			<form name='updatePanchang' id='panchangForm' action='new_panchang.php?debug=$DEBUG' method='post' onsubmit='preparePanchangTimeMode();'>
				<input type='hidden' id='nPlace' name='nPlace' value='$place'>
				<input type='hidden' id='place_flag' name='place_flag' value=0>
				<input type='hidden' id='time_mode' name='time_mode' value='$timeMode'>
				<div class='panchang-form-fields'>
					<label class='panchang-form-field panchang-date-time-field' for='nDate'>
						<span>$formDateLabel</span>
						<input type='date' id='nDate' name='nDate' onChange='onDateChange();' value='$chart_date'>
						<span class='panchang-date-time-value' id='nDateDisplay' aria-hidden='true'></span>
					</label>
					<label class='panchang-form-field panchang-date-time-field' for='nTime'>
						<span>$formTimeLabel</span>
						<input type='time' id='nTime' name='nTime' onChange='onTimeChange();' step='1' value='$chart_time'>
						<span class='panchang-date-time-value' id='nTimeDisplay' aria-hidden='true'></span>
					</label>
					<div class='panchang-form-field panchang-place-field'>
						<label for='place'>$formPlaceLabel</label>
						<div class='search-box panchang-place-search'>
							<input type='text' name='place' id='place' autocomplete='off' placeholder='$formPlacePlaceholder' onfocus=this.value='' onBlur='placeOnBlur();'>
							<span class='panchang-place-display' id='placeDisplay' aria-hidden='true'>
								<span>$formPlaceDisplay</span>
								<span class='panchang-place-timezone'>$formPlaceTimezone</span>
							</span>
							<i class='fa fa-angle-right panchang-place-chevron' aria-hidden='true'></i>
							<div class='result'></div>
						</div>
					</div>
				</div>
				<div class='panchang-form-navigation'>
					<section class='panchang-control-section' aria-labelledby='panchangNavigationLabel'>
						<h3 class='panchang-control-heading' id='panchangNavigationLabel'>$formNavigationLabel</h3>
						<div class='panchang-timeline' data-locale='$timelineLocale'>
							<div class='panchang-timeline-header'>
								<div class='panchang-timeline-month-controls' role='group' aria-label='$formNavigationLabel'>
									<button type='button' class='panchang-period-button' aria-label='$timelinePrevYearLabel' onclick="movePanchangPeriod(-1, 'year')"><i class='fa fa-angle-double-left' aria-hidden='true'></i></button>
									<button type='button' class='panchang-period-button' aria-label='$timelinePrevMonthLabel' onclick="movePanchangPeriod(-1, 'month')"><i class='fa fa-angle-left' aria-hidden='true'></i></button>
									<span class='panchang-timeline-month' id='panchangTimelineMonth' aria-live='polite'></span>
									<button type='button' class='panchang-period-button' aria-label='$timelineNextMonthLabel' onclick="movePanchangPeriod(1, 'month')"><i class='fa fa-angle-right' aria-hidden='true'></i></button>
									<button type='button' class='panchang-period-button' aria-label='$timelineNextYearLabel' onclick="movePanchangPeriod(1, 'year')"><i class='fa fa-angle-double-right' aria-hidden='true'></i></button>
								</div>
								<div class='panchang-timeline-shortcuts'>
									<button type='button' class='actionBtnSmall' id='now' onclick="updateNow();document.forms['updatePanchang'].submit();">$formNowLabel</button>
									<button type='button' class='actionBtnSmall' id='todayBtn' onclick='updateToday();'>$formTodayLabel</button>
								</div>
							</div>
							<div class='panchang-timeline-track' id='panchangTimelineTrack' role='group' aria-label='$formNavigationLabel'>
								$timelineDateItemsMarkup
							</div>
						</div>
					</section>
					<details class='panchang-advanced-options'>
						<summary><i class='fa fa-cog' aria-hidden='true'></i><span>$formAdvancedOptionsLabel</span></summary>
						<div class='button-container'>
EOL;
					echo "<input type='button' id='downloadPanchang' class='actionBtn' value='" . __("monthly") . "' onClick=\"genPanchang('$place');\" />";
					echo "<input type='button' class='actionBtn' id='onePageBtn' onclick=\"displayOnePagePanchang()\" value=\"" . __('download') . "\" />";
					echo "<input type='button' class=\"actionBtn\" id=\"showNakshatraBtn\" value='" . __("nakshatra_division") . "' />";
					echo "<input type='button' class=\"actionBtn\" id=\"showPushkaraBtn\" value='" . __("pushkara") . "' onclick=\"showPushkaraTimes()\" />";
					echo "<input type='button' class=\"actionBtn\" id=\"showMuhurtamBtn\" value='" . __("muhurtham") . "' onclick=\"generateMuhurtam_default()\" />";
echo <<<EOL
						</div>
					</details>
				</div>
			</form>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					const dateInput = document.getElementById('nDate');
					const timeInput = document.getElementById('nTime');
					const dateDisplay = document.getElementById('nDateDisplay');
					const timeDisplay = document.getElementById('nTimeDisplay');
					const dateTimeDateFormatter = new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
					const timeDisplayFormatter = new Intl.DateTimeFormat('en-US', { hour: 'numeric', minute: '2-digit' });
					const updateDateTimeDisplay = function() {
						const dateParts = dateInput.value.match(/^(\d{4})-(\d{2})-(\d{2})$/);
						dateDisplay.textContent = dateParts
							? dateTimeDateFormatter.format(new Date(Number(dateParts[1]), Number(dateParts[2]) - 1, Number(dateParts[3]), 12))
							: '';

						const timeParts = timeInput.value.match(/^(\d{2}):(\d{2})/);
						timeDisplay.textContent = timeParts
							? timeDisplayFormatter.format(new Date(2000, 0, 1, Number(timeParts[1]), Number(timeParts[2])))
							: '';
					};
					updateDateTimeDisplay();
					dateInput.addEventListener('input', updateDateTimeDisplay);
					timeInput.addEventListener('input', updateDateTimeDisplay);

					const timeline = document.querySelector('.panchang-timeline');
					const track = document.getElementById('panchangTimelineTrack');
					if (!timeline || !track) return;

					const locale = timeline.dataset.locale;
					const weekdayFormatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
					const dateFormatter = new Intl.DateTimeFormat(locale, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
					const monthYearFormatter = new Intl.DateTimeFormat(locale, { month: 'short', year: '2-digit' });
					const monthFormatter = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric' });
					const selectedDate = track.querySelector('[aria-current="date"]');
					const centerSelectedDate = function() {
						if (!selectedDate) return;
						track.scrollLeft += selectedDate.getBoundingClientRect().left - track.getBoundingClientRect().left - (track.clientWidth - selectedDate.offsetWidth) / 2;
					};

					let previousMonth = '';
					track.querySelectorAll('.panchang-timeline-date').forEach(function(button, index) {
						const date = new Date(button.dataset.date + 'T12:00:00');
						const monthKey = date.getFullYear() + '-' + date.getMonth();
						button.querySelector('.panchang-timeline-weekday').textContent = weekdayFormatter.format(date);
						button.setAttribute('aria-label', dateFormatter.format(date));
						if (index === 0 || monthKey !== previousMonth) {
							button.querySelector('.panchang-timeline-month-marker').textContent = monthYearFormatter.format(date);
						}
						previousMonth = monthKey;
					});

					if (selectedDate) {
						const selected = new Date(selectedDate.dataset.date + 'T12:00:00');
						document.getElementById('panchangTimelineMonth').textContent = monthFormatter.format(selected);
						requestAnimationFrame(centerSelectedDate);
					}
					window.addEventListener('resize', centerSelectedDate);

					track.addEventListener('click', function(event) {
						const button = event.target.closest('.panchang-timeline-date');
						if (!button) return;
						document.getElementById('nDate').value = button.dataset.date;
						document.getElementById('loader').style.visibility = 'visible';
						document.forms['updatePanchang'].submit();
					});
				});
			</script>

EOL;
    
					calculateVimDasaBalance($moon_degree,$moon_house,$chart_time,$chart_time,$panchang['nak'],$nak_pad_idx, true);
					calculateVimDasa();
					calculateCurrentDasaBukthi(true);
					
					//$panchang = getPanchangFromDB();
					calculateBirthThithiSoonyam($panchang["thithi"]);
					displayHoroDetails();
					$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);
					displayTransitChart();
					displayStarLord(true);
					
					echo "<BR>";
					/***** Vivaha chakram *****/
					//$sun_nakshatra = 'உத்திரம்';
					//$moon_nakshatra = 'உத்திராடம்';
					$sun_nakshatra = explode(' ',$planet_starlord['சூரி'][0])[0];
					$moon_nakshatra = $g_nak;

					debug("$sun_nakshatra,$moon_nakshatra");

					//$result = calculateVivahaChakra($sun_nakshatra, $moon_nakshatra);
					$result = displayVivahaChakra_manikantan($sun_nakshatra, $moon_nakshatra);
					/***** Vivaha chakram *****/

					displayDisclaimer();
					

echo <<<EOL
					<BR><BR><BR>
			</div>
			<div class="divOnePagePanchangContainer" id="divContainer" style='display:none;'>
			
				<table>
					<tr>
						<td style='vertical-align: bottom;'><img src='images/dsjv_title.png' width='200px' height="35px" style='display:inline;'></td>
						<td style='vertical-align: bottom;'>&#3059; &#2384; &#21328; &#10017<br><img src="images/panchangam_heading.png" alt="Panchangam" width='150px' style='display:inline;'></td>
						<td style='vertical-align: bottom;'><img src='images/periyanayaki_astro_solutions.png' height="35px" style='display:inline;'></div></td>
					</tr>
					<tr>
						<td colspan=5 style='text-align: center;'>
							<div class="onepage_panchang_date" id="date">date</div>
							<div class="onepage_panchang_day" id="day_local">day</div>
							<div class="onepage_panchang_place" id="localPlace">place</div>
							<!-- <img id="day_local" src="" alt="Captured Image" width='150px'' style='display:inline;'> -->
						</td>
						
					</tr>
					<tr>
						<td style='padding:unset; text-align: center;' colspan='5'>
							<div class="image-with-caption">
								<div class="imgcaption_small" id="cap_thithi"></div>
								<img id="thithi_local" src="" alt="Captured Image" width='150px' style='filter: grayscale(100%); display:inline; border:1px solid black'>
							</div>
							<div class="image-with-caption">
								<div class="imgcaption_small" id="cap_nak"></div>
								<img id="nak_local" src="" alt="Captured Image" width='150px' style='filter: grayscale(100%);display:inline; border:1px solid black'>
							</div>
							<div class="image-with-caption">
								<div class="imgcaption_small" id="cap_yogam"></div>
								<img id="yogam_local" src="" alt="Captured Image" width='150px' style='filter: grayscale(100%);display:inline; border:1px solid black'>
							</div>
							<div class="image-with-caption">
								<div class="imgcaption_small" id="cap_karanam"></div>
								<img id="kara_local" src="" alt="Captured Image" width='150px' style='filter: grayscale(100%);display:inline; border:1px solid black'>
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="4" style='text-align: center;'>
							<div class="panchangTextInline" id="lHorai" data-label="<?php echo __('horai'); ?>"><div class="panchangTextInlineBold"><?php echo __('horai'); ?></div></div>,
							<div class="panchangTextInline" id="lPakshi" data-label="<?php echo __('pancha_pakshi'); ?>"><div class="panchangTextInlineBold"><?php echo __('pancha_pakshi'); ?>	</div></div>,
							<div class="panchangTextInline" id="lAmirYogam" data-label="<?php echo __('amirthathi'); ?> <?php echo __('yogam'); ?>"><div class="panchangTextInlineBold"><?php echo __('amirthathi'); ?> <?php echo __('yogam'); ?></div></div>,
							
							<div class="panchangTextInline" id="lNethram" data-label="<?php echo __('nethram'); ?>"><div class="panchangTextInlineBold"><?php echo __('nethram'); ?></div></div>,
							<div class="panchangTextInline" id="lJeevan" data-label="<?php echo __('jeevan'); ?>"><div class="panchangTextInlineBold"><?php echo __('jeevan'); ?></div></div>,
							<div class="panchangTextInline" id="lNokku" data-label="<?php echo __('day_aspect'); ?>"><div class="panchangTextInlineBold"><?php echo __('day_aspect'); ?></div></div>,
							
							<div class="panchangTextInline" id="lLagnam" data-label="<?php echo __('lagna'); ?>"><div class="panchangTextInlineBold"><?php echo __('lagna'); ?></div></div>,
							<div class="panchangTextInline" id="lRaasi" data-label="<?php echo __('rasi'); ?>"><div class="panchangTextInlineBold"><?php echo __('rasi'); ?></div></div>,
							<div class="panchangTextInline" id="lAbhijith" data-label="<?php echo __('abhijith'); ?>"><div class="panchangTextInlineBold"><?php echo __('abhijith'); ?></div></div>,
							
							<div class="panchangTextInline" id="lAvayogi" data-label="<?php echo __('avayogi'); ?>"><div class="panchangTextInlineBold"><?php echo __('avayogi'); ?></div></div>,
							<div class="panchangTextInline" id="lThithi_soonyam" data-label="<?php echo __('thithi_soonyam'); ?>"><div class="panchangTextInlineBold"><?php echo __('thithi_soonyam'); ?></div></div>,
							<div class="panchangTextInline" id="lRahu_kaalam" data-label="<?php echo __('rahu_kaalam'); ?>"><div class="panchangTextInlineBold"><?php echo __('rahu_kaalam'); ?></div></div>,
							
							<div class="panchangTextInline" id="lEma_kantam" data-label="<?php echo __('ema_kantam'); ?>"><div class="panchangTextInlineBold"><?php echo __('ema_kantam'); ?></div></div>,
							<div class="panchangTextInline" id="lKuligai" data-label="<?php echo __('kulika'); ?>"><div class="panchangTextInlineBold"><?php echo __('kulika'); ?></div></div>,
							<div class="panchangTextInline" id="lSoolai" data-label="<?php echo __('vaara_soolai'); ?>"><div class="panchangTextInlineBold"><?php echo __('vaara_soolai'); ?></div></div>,
							
							<div class="panchangTextInline" id="lChandraashtamam" data-label="<?php echo __('chandraashtamam'); ?>"><div class="panchangTextInlineBold"><?php echo __('chandraashtamam'); ?></div></div>,
							<div class="panchangTextInline" id="lSunrise" data-label="<?php echo __('sunrise'); ?>"><div class="panchangTextInlineBold"><?php echo __('sunrise'); ?></div></div>,
							<div class="panchangTextInline" id="lSunset" data-label="<?php echo __('sunset'); ?>"><div class="panchangTextInlineBold"><?php echo __('sunset'); ?></div></div>
							<BR>
							<div class="panchangTextInline" id="lviv_chakram"><div class="panchangTextInlineBold" id="cap_vivaha"></div></div>
						</td>
					</tr>
					<tr>
						<td style='padding:unset' colspan=1>
							<div class="imgcaption" id="cap_current_transit"></div><img id="chart" src="" alt="Captured Image" width='280px'>
						</td>
						<td style='padding:unset;align-content:center'><img id="ganesha" src="images/panchang_ganesha.png" alt="Image" width='180px'>
						<td style='padding:unset;align-content:center'><div class="imgcaption" id="cap_graha_saaram"></div><img id="starlord" src="" alt="Captured Image" width='220px'></td>
						
						 <!-- <td style='padding:unset;align-content:center'><div class="imgcaption">விவாஹ சக்ரம்</div><img id="vivaha" src="" alt="Captured Image" width='220px'></td> -->
						<!-- <td style='padding:unset;align-content:center'><div class="imgcaption">கிரக சாரம்</div><img id="starlord" src="" alt="Captured Image" width='250px'></td> -->
					</tr>
					<!-- <tr>
						<td style='padding:unset;align-content:center'><div class="imgcaption">கிரக சாரம்</div><img id="starlord" src="" alt="Captured Image" width='220px'></td>
						<td style='padding:unset;align-content:center'><div class="imgcaption">விவாஹ சக்ரம்</div><img id="vivaha" src="" alt="Captured Image" width='220px'></td> 
					</tr> -->
				</table>
			<!-- Add more divs as needed -->
			<!-- <button class='actionBtn' id='backBtn' onclick="displayOrigPanchang()" style='display:none'>Back</button> -->
			</div>
EOL;

getStarLetters($g_nak);

dbDisconnect($conn);

//$datetime=$chart_date.'T'.$chart_time.'%2B'.date('H:i',strtotime($gmt_offset));

//getProkeralaPunchangDetail($lat,$lon,$datetime,'ta');

$_SESSION["place"] = $place;
$_COOKIE['panchang_place']=$place;

unset($_SESSION['menu_clicked']);



?>
<div id='dummy'></div>

<!-- Pushkara Navamsha Times - Load asynchronously (hidden container) -->
<div id="pushkara-container" style="display: none;">
    <h3>Pushkara Navamsha Times</h3>
    <div style="text-align: center; padding: 20px;">
        <div class="pushkara-loader"></div>
        <p>Loading Pushkara times...</p>
    </div>
</div>

<style>
.pushkara-loader {
    border: 4px solid var(--theme-loader-track);
    border-top: 4px solid var(--theme-loader-accent);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Button container */
.button-container, .nav-button-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 8px;
    justify-content: flex-start;
    align-items: center;
}

.nav-button-container {
    justify-content: center;
}

.panchang-timeline {
    min-width: 0;
    padding: 8px;
    border: 1px solid rgba(255, 255, 255, .1);
    border-radius: 14px;
    background: var(--panchang-topbar-gradient, linear-gradient(120deg, rgba(20,20,46,.97), rgba(30,22,58,.95), rgba(54,27,50,.95)));
}

.panchang-timeline-header,
.panchang-timeline-shortcuts,
.panchang-timeline-month-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}

.panchang-timeline-header {
    flex-wrap: wrap;
    justify-content: space-between;
    margin-bottom: 6px;
    padding: 0 2px;
}

.panchang-timeline-month {
    color: #fff4e8;
    min-width: 0;
    font-size: 12px;
    font-weight: 700;
    text-align: left;
}

.panchang-period-button {
    display: inline-flex !important;
    width: 16px;
    height: 26px;
    flex: 0 0 16px;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    border: 0 !important;
    border-radius: 0;
    background: transparent !important;
    box-shadow: none !important;
    color: #c3a9dc !important;
    cursor: pointer;
    line-height: 1;
}

.panchang-timeline-shortcuts .actionBtnSmall {
    min-height: 26px !important;
    padding: 4px 10px !important;
    border: 1px solid rgba(255, 255, 255, .1) !important;
    border-radius: 999px !important;
    background: #332e3e !important;
    box-shadow: none !important;
    color: #fff4e8 !important;
    font-size: 11px !important;
}

.panchang-timeline-track {
    display: flex;
    gap: 5px;
    overflow-x: auto;
    padding: 3px 10px;
    scroll-snap-type: x mandatory;
    overscroll-behavior-x: contain;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.panchang-timeline-track::-webkit-scrollbar {
    display: none;
}

.panchang-timeline-date {
    display: flex;
    flex: 0 0 44px;
    min-height: 52px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    border: 1px solid rgba(255, 255, 255, .1);
    border-radius: 11px;
    background: #2b2635;
    color: #fff4e8 !important;
    cursor: pointer;
    scroll-snap-align: center;
    transition: background .16s ease, border-color .16s ease, transform .16s ease;
}

.panchang-timeline-date:hover {
    border-color: rgba(181, 126, 255, .7);
}

.panchang-timeline-date[aria-current="date"] {
    border-color: #a76bff;
    background: linear-gradient(145deg, #a14dff, #7027d9);
    box-shadow: 0 5px 16px rgba(126, 53, 223, .34);
    color: #fff !important;
}

.panchang-timeline-weekday {
    color: inherit;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    opacity: .95;
    text-transform: uppercase;
}

.panchang-timeline-day {
    font-size: 16px;
    font-weight: 700;
    line-height: 1;
}

.panchang-timeline-month-marker {
    font-size: 8px;
    font-weight: 700;
    line-height: 1;
    opacity: .8;
}

.panchang-timeline-month-marker:empty {
    display: none;
}

.panchang-advanced-options {
    margin-top: 6px;
    border: 0;
    background: transparent;
}

.panchang-advanced-options > summary {
    display: inline-flex;
    min-height: 0;
    align-items: center;
    gap: 6px;
    padding: 4px 0;
    border-radius: 4px;
    color: var(--theme-text-accent-strong, #402276) !important;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    list-style: none;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.panchang-advanced-options > summary:hover {
    color: var(--theme-text-accent, #563092) !important;
}

.panchang-advanced-options > summary:focus-visible {
    outline: 2px solid var(--theme-border-focus, #5b2be0);
    outline-offset: 3px;
}

.panchang-advanced-options > summary::-webkit-details-marker {
    display: none;
}

.panchang-advanced-options > summary::after {
    content: "+";
    font-size: 14px;
    font-weight: 800;
}

.panchang-advanced-options[open] > summary::after {
    content: "−";
}

.panchang-advanced-options > .button-container {
    padding: 0 12px 12px;
}

/* Hide mobile dropdown by default */
.mobile-dropdown, .mobile-nav-dropdown {
    display: none;
}

/* Vertical centering for all buttons and dropdowns */
.actionBtn, .actionBtnSmall, select.mobile-dropdown, select.mobile-nav-dropdown {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    vertical-align: middle !important;
    line-height: normal !important;
}

/* Mobile view - show dropdown, hide other buttons */
@media only screen and (max-width: 600px) {
    .btn-desktop, .btn-desktop-nav {
        display: none !important;
    }
    
	/* Show mobile-only buttons */
	.btn-mobile {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		padding: 6px 12px !important;
		font-size: 13px !important;
		min-height: 34px !important;
	}
    
    .mobile-dropdown, .mobile-nav-dropdown {
		display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: auto !important;
        margin: 3px !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
		border: 2px solid var(--theme-border-strong) !important;
		background-color: var(--theme-button-background) !important;
        color: var(--theme-button-text) !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        vertical-align: middle !important;
        height: auto !important;
        min-height: 34px !important;
		appearance: none !important;
		-webkit-appearance: none !important;
		-moz-appearance: none !important;
		line-height: 1.2 !important;
		font-weight: bold !important;
    }
    
    select.mobile-nav-dropdown {
        font-size: 13px !important;
        padding: 6px 10px !important;
        min-height: 34px !important;
        max-width: 120px !important;
        width: auto !important;
    }    .button-container, .nav-button-container {
        gap: 8px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    #sub_button {
        padding: 10px 20px !important;
        font-size: 14px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
}

/* Desktop layout - hide dropdown, show all buttons */
@media only screen and (min-width: 601px) {
    .mobile-dropdown, .mobile-nav-dropdown {
        display: none !important;
    }
    
	/* Hide mobile-only buttons on desktop */
	.btn-mobile {
		display: none !important;
	}
    
    .btn-desktop, .btn-desktop-nav {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .button-container, .nav-button-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
        align-items: center;
    }
    
    .nav-button-container {
        justify-content: center;
    }
    
    .actionBtn, .actionBtnSmall {
        flex: 0 1 auto;
        margin: 3px !important;
        padding: 8px 15px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
}
</style>

<script>
// Global flag to track Pushkara data loading status
var pushkaraDataLoaded = false;

// Load Pushkara data asynchronously to avoid blocking page load
(function() {
    var xhr = new XMLHttpRequest();
	xhr.open('GET', 'panchang_pushkara_times.php?lon=<?php echo urlencode($lon); ?>&lat=<?php echo urlencode($lat); ?>&tz=<?php echo urlencode($TZ); ?>&lang=<?php echo $language; ?>', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            var container = document.getElementById('pushkara-container');
            if (xhr.status === 200) {
                container.innerHTML = xhr.responseText;
                pushkaraDataLoaded = true;
                // Enable the link once data is loaded
                var pushkaraLink = document.getElementById('pushkara-link');
                if (pushkaraLink) {
                    pushkaraLink.style.color = 'blue';
                    pushkaraLink.style.cursor = 'pointer';
                    pushkaraLink.innerHTML = 'Click to see';
                }
            } else {
                container.innerHTML = '<h3>Pushkara Navamsha Times</h3><p style="color: red;">Failed to load Pushkara data. Please refresh the page.</p>';
                var pushkaraLink = document.getElementById('pushkara-link');
                if (pushkaraLink) {
                    pushkaraLink.style.color = 'red';
                    pushkaraLink.innerHTML = 'Failed to load';
                }
            }
        }
    };
    xhr.send();
})();
</script>

<!-- <button class='actionBtn' id='Translate' onClick='transliterateTamilToEnglish()'>English</button> -->

<script>
// ---- Panchang value capture utilities (added for reliable post-load data collection) ----
// IDs we want to snapshot (static + dynamically populated)
const PANCHANG_IDS = [
	'date','day_local','localPlace','thithi','nak','yogam','karanam','hora','pakshi','amirtha_yogam','nethram','jeevan','nokku',
	'lagnam','raasi','abhijith','avayogi','thithi_soonyam','rahu_kaalam','ema_kantam','kuligai','soolai',
	'chandraashtamam','sunrise','sunset'
];

// Wait for element text to become non-empty (handles async loads)
function waitForText(id,{timeout=5000,interval=150}={}) {
	return new Promise(resolve => {
		const el = document.getElementById(id);
		if(!el){ return resolve(''); }
		const immediate = el.textContent.trim();
		if(immediate){ return resolve(immediate); }
		const obs = new MutationObserver(() => {
			const txt = el.textContent.trim();
			if(txt){ obs.disconnect(); resolve(txt); }
		});
		obs.observe(el,{childList:true,subtree:true,characterData:true});
		let elapsed = 0;
		const poll = setInterval(()=>{
			elapsed += interval;
			const txt = el.textContent.trim();
			if(txt){ clearInterval(poll); obs.disconnect(); resolve(txt); }
			else if(elapsed >= timeout){ clearInterval(poll); obs.disconnect(); resolve(txt); }
		},interval);
	});
}

// Capture all current values (returns Promise resolving to object)
async function capturePanchangValues(){
	const out = {};
	for(const id of PANCHANG_IDS){
		out[id] = document.getElementById(id)?.textContent.trim() || '';
	}
	// Ensure key dynamic ones fully loaded
	out.hora   = await waitForText('hora');
	out.thithi = await waitForText('thithi');
	out.nak    = await waitForText('nak');
	out.karanam= await waitForText('karanam');
	out.yogam  = await waitForText('yogam');
	out.pakshi = await waitForText('pakshi');
	window.panchangSnapshot = out; // expose globally
	return out;
}

// Update Horai inline label once value available
function updateHoraiInline(){
	const horaTxt = document.getElementById('hora')?.textContent.trim();
	if(!horaTxt) return; // wait until loaded
	const lHorai = document.getElementById('lHorai');
	if(!lHorai || lHorai.dataset.done) return;
	const labelEl = lHorai.querySelector('.panchangTextInlineBold');
	const labelHTML = labelEl ? labelEl.outerHTML : lHorai.innerHTML;
	lHorai.innerHTML = labelHTML + ' - ' + horaTxt;
	lHorai.dataset.done = '1';
}

// Public helper to obtain latest snapshot
function getPanchangSnapshot(){
	return window.panchangSnapshot || {};
}

// On full window load: capture and attempt horai append; retry if still missing
window.addEventListener('load', () => {
	capturePanchangValues().then(()=>{
		updateHoraiInline();
		if(!document.getElementById('lHorai')?.dataset.done){
			setTimeout(updateHoraiInline,250);
		}
	});
});
// ---- End Panchang value capture utilities ----
</script>

<script>
function labelStellarPositionCells() {
	var table = document.getElementById('starlordBodyMutable');
	if (!table) return;

	var rows = Array.from(table.querySelectorAll('.divTableRow'));
	if (!rows.length) return;

	var labels = Array.from(rows[0].children).map(function(cell) {
		return cell.textContent.trim();
	});

	rows.slice(1).forEach(function(row) {
		Array.from(row.children).forEach(function(cell, index) {
			if (!labels[index]) return;
			cell.dataset.label = labels[index];
			cell.setAttribute('aria-label', labels[index] + ': ' + cell.textContent.trim());
		});
	});
}
labelStellarPositionCells();

function fitTransitChartPlanetText() {
	document.querySelectorAll('#transitChart .divTableCell').forEach(function(cell) {
		var label = cell.querySelector('.transit-planet-label');
		if (!label) return;

		var entries = Array.from(label.querySelectorAll('.transit-planet-entry'));
		var planetCount = entries.length;
		var cellWidth = cell.getBoundingClientRect().width;
		var twoColumns = planetCount >= 8 || (planetCount >= 4 && cellWidth >= 96);
		label.classList.toggle('is-crowded', planetCount > 3);
		label.classList.toggle('is-two-column', twoColumns);
		cell.classList.toggle('is-crowded', planetCount > 3);
		cell.classList.toggle('is-lagna-house', Boolean(label.querySelector('.lagna')));

		var fontSize = Math.max(7, Math.min(18, cellWidth * 0.2) - Math.max(0, planetCount - 2) * 0.75);
		var cellStyle = getComputedStyle(cell);
		var availableHeight = cell.clientHeight - parseFloat(cellStyle.paddingTop) - parseFloat(cellStyle.paddingBottom);

		while (true) {
			cell.style.setProperty('--transit-chart-cell-font-size', fontSize + 'px');
			var contentHeight = Array.from(cell.children).reduce(function(total, child) {
				return total + child.getBoundingClientRect().height;
			}, 0);
			var labelOverflows = entries.some(function(entry) {
				return entry.scrollWidth * (twoColumns ? 0.85 : 1) > entry.clientWidth;
			}) || label.scrollWidth > label.clientWidth;
			if ((!labelOverflows && contentHeight <= availableHeight) || fontSize <= 7) break;
			fontSize = Math.max(7, fontSize - 0.5);
		}

		cell.style.setProperty('--transit-chart-cell-font-size', fontSize + 'px');
	});
}
fitTransitChartPlanetText();
window.addEventListener('resize', fitTransitChartPlanetText);
</script>

<?php if ($flag == 0 && $home !== "none") { ?>
<nav class="bottom-nav panchang-bottom-nav" aria-label="<?php echo ($language === 'english') ? 'Main navigation' : 'முதன்மை வழிசெலுத்தல்'; ?>">
	<a href="index.php" aria-label="<?php echo ($language === 'english') ? 'Home' : 'முகப்பு'; ?>" title="<?php echo ($language === 'english') ? 'Home' : 'முகப்பு'; ?>"><i class="fa fa-home" aria-hidden="true"></i></a>
	<a class="is-active" href="new_panchang.php" aria-label="<?php echo htmlspecialchars(__('panchangam'), ENT_QUOTES, 'UTF-8'); ?>" aria-current="page"><i class="fa fa-calendar" aria-hidden="true"></i></a>
	<a href="star_thaara_palan.php" aria-label="<?php echo htmlspecialchars(__('tara_palan'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-star-o" aria-hidden="true"></i></a>
	<a href="hora.php" aria-label="<?php echo htmlspecialchars(__('horai'), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-clock-o" aria-hidden="true"></i></a>
	<button type="button" aria-label="<?php echo ($language === 'english') ? 'Download PDF' : 'PDF பதிவிறக்கம்'; ?>" title="<?php echo ($language === 'english') ? 'Download PDF' : 'PDF பதிவிறக்கம்'; ?>" onclick="document.getElementById('onePageBtn').click();"><i class="fa fa-download" aria-hidden="true"></i></button>
</nav>
<?php } ?>

</body>
</html>