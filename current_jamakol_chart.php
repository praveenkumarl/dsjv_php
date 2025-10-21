<?php
include_once "lib.php";

$DEBUG = isset($_GET['debug']) ? $_GET['debug'] : 0;
$g_kathir_data=array();

if(isset($_GET['key']) and checkAdminKey($_GET['key'])){
	$_SESSION['logged'] = true;
	$_SESISON['autojamakol'] = false; //true;
}	

function displayJamaChart($sw_planet,$outside,$chart_date,$chart_time,$place)
{
	global $g_kathir_data;
	//to display place in center of 2 cells
	$place_left_part = substr($place,0,strlen($place)/2);
	$place_right_part = substr($place,strlen($place)/2);

	echo <<<EOL
		<br><br><br>
		<center>
		<DIV class="heading">ஜாமக்கோள் Chart</DIV>
		<div class="divTable blueTable" id="chart" OnClick='window.location.reload();' style='margin-top:30px'>		
			<div class="divTableBody">
				<div class="divTableRow">
						<div class="divTableOuterCell">$outside[11]</div>
						<div class="divTableDummyCell"></div>
						<div class="divTableOuterCell">$outside[0]</div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableOuterCell">$outside[2]</div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$sw_planet[11]<span class='kathir'>$g_kathir_data[11]<span></div>
						<div class="divTableCell">$sw_planet[0]<span class='kathir'>$g_kathir_data[0]<span></div>
						<div class="divTableCell">$sw_planet[1]<span class='kathir'>$g_kathir_data[1]<span></div>
						<div class="divTableCell">$sw_planet[2]<span class='kathir'>$g_kathir_data[2]<span></div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$sw_planet[10]<span class='kathir'>$g_kathir_data[10]<span></div>
						<div class="divTableCenterCell1">$chart_date</div>
						<div class="divTableCenterCell2">$chart_time</div>
						<div class="divTableCell">$sw_planet[3]<span class='kathir'>$g_kathir_data[3]<span></div>
						<div class="divTableOuterCell">$outside[3]</div>
				</div>
				<div class="divTableRow">
						<div class="divTableOuterCell">$outside[9]</div>
						<div class="divTableCell">$sw_planet[9]<span class='kathir'>$g_kathir_data[9]<span></div>
						<div class="divTableCenterCell3">$place_left_part</div>
						<div class="divTableCenterCell4">$place_right_part</div>
						<div class="divTableCell">$sw_planet[4]<span class='kathir'>$g_kathir_data[4]<span></div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableDummyCell"></div>
						<div class="divTableCell">$sw_planet[8]<span class='kathir'>$g_kathir_data[8]<span></div>
						<div class="divTableCell">$sw_planet[7]<span class='kathir'>$g_kathir_data[7]<span></div>
						<div class="divTableCell">$sw_planet[6]<span class='kathir'>$g_kathir_data[6]<span></div>
						<div class="divTableCell">$sw_planet[5]<span class='kathir'>$g_kathir_data[5]<span></div>
						<div class="divTableDummyCell"></div>
				</div>
				<div class="divTableRow">
						<div class="divTableOuterCell">$outside[8]</div>
						<div class="divTableDummyCell"></div>
						<div class="divTableDummyCell"></div>
						<div class="divTableOuterCell">$outside[6]</div>
						<div class="divTableDummyCell"></div>
						<div class="divTableOuterCell">$outside[5]</div>
				</div>
			</div>	
		</div>
		</center>
		<div id='dummy'></div>
EOL;
}

if(!isLogged()){
	echo "You are not authorized to access this page, contact admin";
	exit;
}

$key = isset($_POST["key"]) ? $_POST["key"] : '-1';
$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : 1;
$in_place = isset($_COOKIE["place"]) ? $_COOKIE["place"] : 'Chennai|Asia/Kolkata';
$in_degree = 1;
$lan ='Tamil';
$TZ = explode("|",$in_place)[1];
$place = explode("|",$in_place)[0];

date_default_timezone_set($TZ);

$chart_date= date("Y-m-d");
$chart_time= date("H:i:s");
//$chart_time=date("H:i:s",strtotime(' +2 minutes 31 seconds')); // to adjust server time for live chart only
$chartNow=1;

$nQuestion="";

$_SESSION['debug'] = 0;

?>


<!doctype html>
<html lang="en" class="no-js">
<head>
	<!-- This is refresh this page every 60 seconds/1 minute -->
	<?php	
		if(isset($_SESISON['autojamakol'])){
			echo "<meta http-equiv='refresh' content='300' /> ";
		}
		else{
			echo "<!-- <meta http-equiv='refresh' content='300' /> -->";
		}
	?>
	
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
    
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script> -->
	
    <script src="script.js"></script>
    <script type="text/javascript">


			
	function init(){
		document.forms["currentChart"].submit();
	}
	
	document.addEventListener('DOMContentLoaded', function () {
		//handleSwipe();
	});
	
	function handleSwipe(){
		const bodyElement = document.body;
		const hammer = new Hammer(bodyElement);

		// Listen for swipe events
		hammer.on('swipe', function (event) {
			switch (event.direction) {
				case Hammer.DIRECTION_RIGHT:
					console.log('Right swipe detected');
					window.location.href = 'index.php';
					break;
			}
		});
	}
    </script>
</head>
<body onLoad="init();">

<div class='column-menu'>
<?php displayUserInfo($_SESSION['userinfo']);?>
	<div class='desktop'>
	<button class="menu" onClick="window.open('index.php','_self')">Home</button>
</div>
<div class='mobile'>
	<?php include "currentJamakolMenu_mobile.php"; ?>
</div>
</div>
<div class='column-body'>

<?php


if(!isset($_SESSION['jamadata'])){
	echo <<<EOL
		<div id="chartform">
			<form name='currentChart' action='Jamakol_Chart.php' method='post'>
				<input type='hidden' id='place' name='place' value=$in_place>
				<input type='hidden' id='nDegree' name='nDegree' value=$in_degree>
				<input type='hidden' id='lang' name='lang' value=$lan>
				<input type='hidden' id='in_date' name='in_date' value=$chart_date>
				<input type='hidden' id='in_time' name='in_time' value=$chart_time>
				<input type='hidden' id='nQuestion' name='nQuestion' value=$nQuestion>
				<input type='hidden' id='chartNow' name='chartNow' value=$chartNow>
			</form>
		</div>
EOL;
}
else{
	$g_kathir_data = calculateKathirData($_SESSION['jamadata'][0], $_SESSION['jamadata'][1]); //calculate and returns the kathir data
	displayJamaChart($_SESSION['jamadata'][0],$_SESSION['jamadata'][1],$chart_date,$chart_time,$place);
	echo '<P id="newChartText" style="color:blue;font-weight:bold; font-size:10px; font-align:center">Touch/click chart to generate new chart</P>';
	unset($_SESSION['jamadata']);
	
}

?>

</div>
</body>
</html>
