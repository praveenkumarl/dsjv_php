<!doctype html>
<html lang="en" class="no-js">
<head>
	<!-- This is refresh this page every 60 seconds/1 minute -->
	<!-- <meta http-equiv='refresh' content='300' /> -->
	
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
    <script src="script.js"></script>
    <script type="text/javascript">


			
	function init(){
		document.forms["currentChart"].submit();
	}
	
	
    </script>
</head>
<body onLoad="init();">

<?php
$DEBUG = isset($_GET['debug']) ? $_GET['debug'] : 0;
$g_kathir_data=array();

$_SESSION['logged']=true;
$_SESSION['autojamakol']=true;

$g_thithi_soonyam_arr = array(null,null,null,null,null,null,null,null,null,null,null,null);

function displayChart($sw_planet,$outside,$chart_date,$chart_time,$place)
{
	global $g_kathir_data, $g_thithi_soonyam_arr;
	//to display place in center of 2 cells
	
	$place=strtok($place,"|");
	$place_left_part = substr($place,0,strlen($place)/2);
	$place_right_part = substr($place,strlen($place)/2);
	
	if(isMobileDevice())
		$requestor = "mobile";
	else
		$requestor = "desktop";
	
	$time = date('h:i A',strtotime($chart_time));

	echo <<<EOL
			<div class="$requestor">
				<!-- <div class="divTable blueTable" id="chart" OnClick='window.location.reload();'> -->
				<center>
				<div class="divTable blueTable" id="chart">
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
								<div class="divTableCell">$sw_planet[11]<span class='kathir'>$g_kathir_data[11]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[11]</span></div>
								<div class="divTableCell">$sw_planet[0]<span class='kathir'>$g_kathir_data[0]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[0]</span></div>
								<div class="divTableCell">$sw_planet[1]<span class='kathir'>$g_kathir_data[1]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[1]</span></div>
								<div class="divTableCell">$sw_planet[2]<span class='kathir'>$g_kathir_data[2]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[2]</span></div>
								<div class="divTableDummyCell"></div>
						</div>
						<div class="divTableRow">
								<div class="divTableDummyCell"></div>
								<div class="divTableCell">$sw_planet[10]<span class='kathir'>$g_kathir_data[10]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[10]</span></div>
								<div class="divTableCenterCell1">$chart_date</div>
								<div class="divTableCenterCell2">$time</div>
								<div class="divTableCell">$sw_planet[3]<span class='kathir'>$g_kathir_data[3]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[3]</span></div>
								<div class="divTableOuterCell">$outside[3]</div>
						</div>
						<div class="divTableRow">
								<div class="divTableOuterCell">$outside[9]</div>
								<div class="divTableCell">$sw_planet[9]<span class='kathir'>$g_kathir_data[9]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[9]</span></div>
								<div class="divTableCenterCell3">$place_left_part</div>
								<div class="divTableCenterCell4">$place_right_part</div>
								<div class="divTableCell">$sw_planet[4]<span class='kathir'>$g_kathir_data[4]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[4]</span></div>
								<div class="divTableDummyCell"></div>
						</div>
						<div class="divTableRow">
								<div class="divTableDummyCell"></div>
								<div class="divTableCell">$sw_planet[8]<span class='kathir'>$g_kathir_data[8]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[8]</span></div>
								<div class="divTableCell">$sw_planet[7]<span class='kathir'>$g_kathir_data[7]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[7]</span></div>
								<div class="divTableCell">$sw_planet[6]<span class='kathir'>$g_kathir_data[6]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[6]</span></div>
								<div class="divTableCell">$sw_planet[5]<span class='kathir'>$g_kathir_data[5]</span><span class='thithi_soonyam'>$g_thithi_soonyam_arr[5]</span></div>
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
		</div>
		<div id='dummy'></div>
EOL;
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
//$chart_time= date("H:i:s");
$chart_time=date("H:i:s",strtotime(' +2 minutes 31 seconds')); // to adjust server time for live chart only
$chartNow=1;

$nQuestion="";

$_SESSION['debug'] = 0;

if(!isset($_SESSION['jamadata'])){
	echo <<<EOL
		<div id="chartform">
			<form name='currentChart' action='Jamakol_Chart.php' method='post'>
				<input type='hidden' id='place' name='place' value='$in_place'>
				<input type='hidden' id='nDegree' name='nDegree' value=$in_degree>
				<input type='hidden' id='lang' name='lang' value=$lan>
				<input type='hidden' id='in_date' name='in_date' value=$chart_date>
				<input type='hidden' id='in_time' name='in_time' value=$chart_time>
				<input type='hidden' id='nQuestion' name='nQuestion' value='$nQuestion'>
				<input type='hidden' id='chartNow' name='chartNow' value=$chartNow>
			</form>
		</div>
EOL;
}
else{
	$g_kathir_data = calculateKathirData($_SESSION['jamadata'][0], $_SESSION['jamadata'][1]); //calculate and returns the kathir data
	$panchang = $_SESSION['panchang'];
	$thithi_soonyam_arr = updateThithiSoonyam($panchang["thithi"]);
	
	echo "<div style='display: flex;justify-content: center;'>";
		displayChart($_SESSION['jamadata'][0],$_SESSION['jamadata'][1],$chart_date,$chart_time,$_COOKIE['place']);
	echo "</div>";
	unset($_SESSION['jamadata']);
	unset($_SESSION['panchang']);
}

?>
</body>
</html>
