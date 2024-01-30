<?php
include "lib.php";

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

<body onLoad="init()">

<div class="divTable blueTable" id="onepage">
	<div class="divTableBody">
		<div class="divTableRow">
			<div class="divTableCell" style="border:none; padding:15px"><?php displayHeadeLeft(); ?></div>
			<div class="divTableCell" style="border:none; padding:15px"><?php displayHeadeRight(); ?></div>
		</div>
	</div>	
	<div class="divTableBody">
		<div class="divTableRow">
			<div class="divTableCell" style="border:none; padding:15px"><?php displayChart(); ?></div>
			<div class="divTableCell" style="border:none; padding:15px"><?php displayNavamsam(); ?></div>
		</div>
		<div class="divTableRow">
			<div class="divTableCell" style="border:none; padding:15px"><?php displayStarLord(); ?></div>
			<div class="divTableCell" style="border:none; padding:15px"><?php displayDasa(); ?></div>
		</div>
	</div>	
</div>

<?php

function displayHeadeLeft()
{
	global $name, $birth_date, $birth_time, $sex;
	echo <<<EOL
	<div class="divTable blueTable" id="chart" style="height:auto">
		<div class="divTableBody">
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
		</div>
	</div>
EOL;
}

function displayHeadeRight()
{
	global $panchang;

	
	var_dump($panchang);
	$vaaram = $panchang['vaaram'];
	$nak = $panchang['nak'];
	$thithi = $panchang['thithi'];
	$yogam	= $panchang['yoga'];
	
	echo <<<EOL
	<div class="divTable blueTable" id="chart" style="height:auto">
		<div class="divTableBody">
			<div class="divTableRow">
				<div class='divTableTextHeading'>வாரம்</div><div class='divTableTextBig'>$vaaram</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>நட்சத்திரம்</div><div class='divTableTextBig'>$nak</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>திதி</div><div class='divTableTextBig'>$thithi</div>
			</div>
			<div class="divTableRow">
				<div class='divTableTextHeading'>யோகம்</div><div class='divTableTextBig'>$yogam</div>
			</div>
		</div>
	</div>
EOL;
}

function displayDasa()
{
	global $dasa;
	
echo <<<EOL
	<div class="divTable blueTable" id="chart" style="height:auto">
		<div class="divTableBody">
			<div class="divTableRow">
				<div class='divTableTextHeading'>தசா அட்டவணை</div>
			</div>
		</div>
	</div>
	<div class="divTable blueTable" id="dasa">
	<div class="divTableBody">
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
			<div class="divTableTextBig">$data[0]</div>
			<div class="divTableTextBig">$data[1]</div>
		</div>
EOL;
	}
	
	echo '</div>';
	echo '</div>';
}

function displayStarLord()
{
	global $starlord_arr, $planetKaranaLord;
	
echo <<<EOL
	<div class="divTable blueTable" id="chart" style="height:auto">
		<div class="divTableBody">
			<div class="divTableRow">
				<div class='divTableTextHeading'>நட் சாரம்</div>
			</div>
		</div>
	</div>
	<div class="divTable blueTable" id="chart">
	
	<div class="divTableBody">
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

</html>