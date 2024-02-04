<?php
include "lib.php";

$LANG = 1; //1 tamil  2 english
$sw_planet = $_SESSION["inside"];
$g_thithi_soonyam_arr = $_SESSION["thithi_soonyam_arr"];
$g_navamsam_arr = $_SESSION["navamsam_arr"];
$starlord_arr = $_SESSION["starlord_arr"];
$planetKaranaLord = $_SESSION["planetKaranaLord"];
$dasa = $_SESSION["dasa"];
$birth_date = $_SESSION["birth_date"];
$birth_time = $_SESSION["birth_time"];
$name = $_SESSION["name"];
$sex = $_SESSION["sex"];
$panchang = $_SESSION["panchang"];
$karan = $_SESSION["karan"];
$yogi = $_SESSION["yogi"];
$ayogi = $_SESSION["ayogi"];
$bukthi_table = $_SESSION["bukthi_table"];
$g_lagnam = $_SESSION["lagnam"];
$g_rasi = $_SESSION["rasi"];
$g_pancha_pakshi = $_SESSION["ppakshi"];
$g_thithi_soonyam = $_SESSION["thithi_soonyam"];
$g_dasaBalance = $_SESSION["dasa_balance"];
$g_dasaCurrent = $_SESSION["current_dasa"];
$g_sunrise = $_SESSION["sunrise"];
$g_sunset = $_SESSION["sunset"];
$place = $_SESSION["in_place"];
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
	<link rel="stylesheet" href="css/onepage_birth_style.css?<?php echo date('ymdhis'); ?>">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>

<div class="header-parent">
  <div class="header-child1"><img src='images/dsjv_title.png'></div>
  <div class="header-child2"><img src='images/pkastro_title.png'></div>
</div>

<center>
<div class="divTableBody">
	<div class="divTableRow">
		<div class="divHoroDetailCell" style="border:none;"><?php displayDetailsCol1(); ?></div>
		<div class="divHoroDetailCell" style="border:none;"><?php displayDetailsCol2(); ?></div>
	</div>
</div>	
	
<div class="divTable blueTable" id="onepage">
	<div class="divTableBody">
		<div class="divTableRow">
			<div class="divTableCell" style="border:none; padding:15px"><?php displayChart(); ?></div>
			<div class="divTableCell" style="border:none; padding:15px"><?php displayNavamsam(); ?></div>
		</div>
	</div>	
</div>

<div class="divTableBody">
	<div class="divTableRow">
		<div class="divStarDetailCell" style="border:none;"><?php displayStarLord(); ?></div>
		<div class="divStarDetailCell" style="border:none;"><?php displayDasa(); ?></div>
		<div class="divStarDetailCell" style="border:none;"><?php displayBukthi(); ?></div>
	</div>
</div>	
</center>


<?php


function displayDetailsCol1()
{
	global $name, $birth_date, $birth_time, $sex, $g_lagnam, $g_rasi, $g_dasaBalance, $g_dasaCurrent, $g_sunrise, $g_sunset, $place;
	
	echo <<<EOL
	
			<div class="divTableRow">
				<div class='divTableTextHeading'>பெயர்</div><div class='divTableTextBig'>$name</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பாலினம்</div><div class='divTableTextBig'>$sex</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த தேதி</div><div class='divTableTextBig'>$birth_date</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த நேரம்</div><div class='divTableTextBig'>$birth_time</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறந்த இடம்</div><div class='divTableTextBig'>$place</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>லக்கினம்</div><div class='divTableTextBig'>$g_lagnam </div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>ராசி</div><div class='divTableTextBig'>$g_rasi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>சூரிய உதயம்</div><div class='divTableTextBig'>$g_sunrise</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>சூரிய அஸ்தமனம்</div><div class='divTableTextBig'>$g_sunset</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பிறப்பு தசா இருப்பு</div><div class='divTableTextMedium'>$g_dasaBalance</div>
			</div>
	
EOL;
}

function displayDetailsCol2()
{
	global $panchang, $LANG, $karan, $yogi, $ayogi, $g_thithi_soonyam , $g_pancha_pakshi, $g_dasaBalance, $g_dasaCurrent;
	
	$vaaram = getStr($panchang['vaaram'],$LANG);
	$nak = getStr($panchang['nak'],$LANG);
	$thithi = getStr($panchang['thithi'],$LANG);
	$yogam	= getStr($panchang['yoga'],$LANG);
	$karan = getStr($karan,$LANG);
	
	echo <<<EOL
			<div class="divTableRow">
				<div class='divTableTextHeading'>வாரம்</div><div class='divTableTextMedium'>$vaaram</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>நட்சத்திரம்</div><div class='divTableTextMedium'>$nak</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>திதி</div><div class='divTableTextMedium'>$thithi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>கரணம்</div><div class='divTableTextMedium'>$karan</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>யோகம்</div><div class='divTableTextMedium'>$yogam</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>யோகி</div><div class='divTableTextMedium'>$yogi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>அவயோகி</div><div class='divTableTextMedium'>$ayogi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>திதி சூன்யம்</div><div class='divTableTextMedium'>$g_thithi_soonyam</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>பஞ்ச பக்ஷி</div><div class='divTableTextMedium'>$g_pancha_pakshi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>தற்போதைய தசா புக்தி</div><div class='divTableTextMedium'>$g_dasaCurrent</div>
			</div>
EOL;
}

function displayDetailsCol3()
{
	global $panchang, $LANG, $karan, $yogi, $ayogi;
	
	$vaaram = getStr($panchang['vaaram'],$LANG);
	$nak = getStr($panchang['nak'],$LANG);
	$thithi = getStr($panchang['thithi'],$LANG);
	$yogam	= getStr($panchang['yoga'],$LANG);
	$karan = getStr($karan,$LANG);
	
	echo <<<EOL
		<div class="divHoroDetailCell">
			<div class="divTableRow">
				<div class='divTableTextHeading'>வாரம்</div><div class='divTableTextMedium'>$vaaram</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>நட்சத்திரம்</div><div class='divTableTextMedium'>$nak</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>திதி</div><div class='divTableTextMedium'>$thithi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>கரணம்</div><div class='divTableTextMedium'>$karan</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>யோகம்</div><div class='divTableTextMedium'>$yogam</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>யோகி</div><div class='divTableTextMedium'>$yogi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>அவயோகி</div><div class='divTableTextMedium'>$ayogi</div>
			</div>
		</div>
EOL;
}

function displayDasa()
{
	global $dasa;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>தசா அட்டவணை</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>கிரகம்</div>
			<div class='divTableTextHeading'>ஆரம்பம்</div>
			<div class='divTableTextHeading'>முடிவு</div>
		</div>
EOL;
	
	foreach($dasa as $planet => $data){
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$planet</div>
			<div class="divTableTextMedium">$data[0]</div>
			<div class="divTableTextMedium">$data[1]</div>
		</div>
EOL;
	}
	
	echo '</div>';
}

function displayBukthi()
{
	global $bukthi_table;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>தசா/புக்தி அட்டவணை</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>தசா/புக்தி</div>
			<div class='divTableTextHeading'>ஆரம்பம்</div>
			<div class='divTableTextHeading'>முடிவு</div>
		</div>
EOL;
	
	foreach($bukthi_table as $key => $data){
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$key</div>
			<div class="divTableTextMedium">$data[1]</div>
			<div class="divTableTextMedium">$data[2]</div>
		</div>
EOL;
	}
	
	echo '</div>';
}

function displayStarLord()
{
	global $starlord_arr, $planetKaranaLord;
	
echo <<<EOL
	<div class="divTableRow">
		<div class="divTableTextMainHeading" colspan=5>நட் சாரம்</div>
	</div>
	<div class="divStarDetailCell">
		<div class="divTableRow">
			<div class='divTableTextHeading'>கிரகம்</div>
			<div class='divTableTextHeading'>நட் பாதம்</div>
			<div class='divTableTextHeading'>நட் அதிபதி</div>
			<div class='divTableTextHeading'>பாகை</div>
			<div class='divTableTextHeading'>கரணம்</div>
		</div>
	
EOL;
	
	foreach($starlord_arr as $idx => $data){
		
	$deg = DECtoDMS($data[2]);
		
echo <<<EOL
		<div class="divTableRow">
			<div class="divTableTextHeading">$idx</div>
			<div class="divTableTextMedium">$data[0]</div>
			<div class="divTableTextMedium">$data[1]</div>
			<div class="divTableTextMedium">$deg</div>
			<div class="divTableTextMedium">$planetKaranaLord[$idx]</div>
		</div>
EOL;
	}
	echo '</div>';
}

function displayChart()
{
	global $sw_planet, $g_thithi_soonyam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="chart">
						
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[11] $g_thithi_soonyam_arr[11]</div>
					<div class="divTableCell">$sw_planet[0] $g_thithi_soonyam_arr[0]</div>
					<div class="divTableCell">$sw_planet[1] $g_thithi_soonyam_arr[1]</div>
					<div class="divTableCell">$sw_planet[2] $g_thithi_soonyam_arr[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[10] $g_thithi_soonyam_arr[10]</div>
					<div class="divTableCenterCell">ராசி</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$sw_planet[3] $g_thithi_soonyam_arr[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[9] $g_thithi_soonyam_arr[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell">D1</div>
					<div class="divTableCell">$sw_planet[4] $g_thithi_soonyam_arr[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$sw_planet[8] $g_thithi_soonyam_arr[8]</div>
					<div class="divTableCell">$sw_planet[7] $g_thithi_soonyam_arr[7]</div>
					<div class="divTableCell">$sw_planet[6] $g_thithi_soonyam_arr[6]</div>
					<div class="divTableCell">$sw_planet[5] $g_thithi_soonyam_arr[5]</div>
			</div>
		</div>	
	</div>
EOL;
}

function displayNavamsam()
{
	global $g_navamsam_arr;
	
echo <<<EOL
	<div class="divTable blueTable" id="d9_chart">
		
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableCell">$g_navamsam_arr[11]</div>
					<div class="divTableCell">$g_navamsam_arr[0]</div>
					<div class="divTableCell">$g_navamsam_arr[1]</div>
					<div class="divTableCell">$g_navamsam_arr[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$g_navamsam_arr[10]</div>
					<div class="divTableCenterCell">நவாம்சம்</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$g_navamsam_arr[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$g_navamsam_arr[9]</div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell">D9</div>
					<div class="divTableCell">$g_navamsam_arr[4]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableCell">$g_navamsam_arr[8]</div>
					<div class="divTableCell">$g_navamsam_arr[7]</div>
					<div class="divTableCell">$g_navamsam_arr[6]</div>
					<div class="divTableCell">$g_navamsam_arr[5]</div>
			</div>
		</div>	
	</div>

EOL;
}

?>


</body>

