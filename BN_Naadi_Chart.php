<?php
session_start();
include "db.php";
include "lib.php";

?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/BN_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>  
	<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>  

	<script type="text/javascript" src="scrolloverflow.js"></script>
    <script src="script.js"></script>
    <script type="text/javascript">
		$(document).ready(function() {
			$('#fullpage').fullpage({
			  //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  //sectionsColor: ['#FFe6df', '#FFcdbf', '#FFe6df', 'whitesmoke', '#ccddff'],
			   sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
			  anchors: ['firstPage','secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 800
			});
		});
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

		function init(){
			
		} 
		
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
			  
			  return true;
			}

		
		$('nTime').on('change', function() {
			var dataString = $(this).serialize();
			alert("test");
			$.ajax({
			  type: "POST",
			  url: "Jamakol_Chart.php",
			  data: dataString,
			  success: function () {
				// Display message back to the user here
			  }
					});
			 
				e.preventDefault();
		});
		
		$('nDate').on('change', function() {
			var dataString = $(this).serialize();
			alert("test");
			$.ajax({
			  type: "POST",
			  url: "Jamakol_Chart.php",
			  data: dataString,
			  success: function () {
				// Display message back to the user here
			  }
					});
			 
				e.preventDefault();
		});
				
	</script>
</head>
<?php

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$lat="";
$lon="";
$gmt_offset="";
$tz_offset;
$today_sunrise="";
$today_sunset="";
$tomo_sunrise="";
$sun_degree=0;
$sun_house=0;
$transit_sun_degree=0;
$transit_sun_house=0;
$ragu_house=-1;
$tr_ragu_house=-1;
$sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_planet = array(null,null,null,null,null,null,null,null,null,null,null,null);
$sw_degree = array(null,null,null,null,null,null,null,null,null,null,null,null);
$tr_sw_degree = array(null,null,null,null,null,null,null,null,null,null,null,null);
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
$cricketMenu=0;
$autoAnalysisMenu=0;

// ------- Swiss ephemeris // ------- 
$libPath = './sweph/';
putenv("PATH=$libPath");
// ------- Swiss ephemeris // ------- 

$chart_time = isset($_POST["nTime"]) ? $_POST["nTime"] : $_POST["in_time"];
$chart_date = isset($_POST["nDate"]) ? $_POST["nDate"] : $_POST["in_date"];

$_SESSION["date"] = date("d-m-Y",strtotime($chart_date));
$_SESSION["time"] = $chart_time;

$dayofweek = date('w', strtotime($chart_date));


?>

<body onLoad="init();">


<?php
function isFilled($str)
{
	if(isset($str)) {
		echo "already has value";
		return 1;
	}
	else {
		return 0;
	}
}

function findKethuPos($pos)
{
	global $DEBUG;
	$kethuHouse=0;
	
	if($DEBUG==1) {
		echo "<BR>findKethuPos() pos=" . $pos . "</BR>";
	}
	
	switch ($pos) {
		case 0:
			$kethuHouse=6;
			break;
		case 1:
			$kethuHouse=7;
			break;
		case 2:
			$kethuHouse=8;
			break;
		case 3:
			$kethuHouse=9;
			break;
		case 4:
			$kethuHouse=10;
			break;
		case 5:
			$kethuHouse=11;
			break;
		case 6:
			$kethuHouse=0;
			break;
		case 7:
			$kethuHouse=1;
			break;
		case 8:
			$kethuHouse=2;
			break;
		case 9:
			$kethuHouse=3;
			break;
		case 10:
			$kethuHouse=4;
			break;
		case 11:
			$kethuHouse=5;
			break;
	  default:
		break;
	}
	
	return $kethuHouse;
}



function updatePlanet(array $planets,array $retro, $in_ragu_house)
{
	global $DEBUG;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($planets);
		echo "<BR>UPDATE PLANET before update</BR>";
		var_dump($retro);
	}
	
	
	$out = str_replace('Sun','சூரி',$planets);
	$out = str_replace('Moon','சந்',$out);
	
	if($retro[4]==1){
		$out = str_replace('Mars','செ',$out);
	}else{
		$out = str_replace('Mars','<span style="color:red">செ<span>',$out);
	}
	
	if($retro[3]==1){
		$out = str_replace('Venus','சுக்',$out);
	}else{
		$out = str_replace('Venus','<span style="color:red">சுக்</span>',$out);
	}
	
	if($retro[2]==1){
		$out = str_replace('Mercury','புத',$out);
	}else{
		$out = str_replace('Mercury','<span style="color:red">புத</span>',$out);
	}
	
	if($retro[5]==1){
		$out = str_replace('Jupiter','குரு',$out);
	}else{
		$out = str_replace('Jupiter','<span style="color:red">குரு<span>',$out);
	}
	
	if($retro[6]==1){
		$out = str_replace('Saturn','சனி',$out);
	}else{
		$out = str_replace('Saturn','<span style="color:red">சனி</span>',$out);
	}
	
	$out = str_replace('true Node','ரா',$out);
	$out = str_replace('Ascendant','லக்',$out);
	
	//trim spaces
	$out = array_map('trim', $out);
	
	$Kethu_pos = findKethuPos($in_ragu_house);
	
	$out[$Kethu_pos] = 'கே ' . $out[$Kethu_pos];
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE PLANET after update</BR>";
		echo "<BR>KETHU position: ".$Kethu_pos . "</BR>";
		var_dump($out);
	}
	
	return $out;
}

function updateSymbols(array $planets)
{
	global $DEBUG;
	
	if($DEBUG==1) {
		echo "<BR>UPDATE SYMBOL before update</BR>";
		var_dump($planets);
	}
	
	$out = str_replace("சூரி","சூரி<br><span class='symbol'>&#9737;<span>",$planets);
	$out = str_replace("செ","செ<br><span class='symbol'>&#9794;<span>",$out);
	$out = str_replace("ரா","ரா<br><span class='symbol'>&#9738;<span>",$out);
	$out = str_replace("குரு","குரு<br><span class='symbol'>&#9795;<span>",$out);
	$out = str_replace("புத","புத<br><span class='symbol'>&#9791;<span>",$out);
	$out = str_replace("சுக்","சுக்<br><span class='symbol'>&#9792;<span>",$out);
	$out = str_replace("சனி","சனி<br><span class='symbol'>&#9796;<span>",$out);
	$out = str_replace("சந்","சந்<br><span class='symbol'>&#9790;<span>",$out);
	
	
	if($DEBUG==1) {
		
		echo "<BR>UPDATE SYMBOL after update</BR>";
		var_dump($out);
	}
	
	return $out;
}



function targetHouse($start_house,$number_of_houses)
{
	debug($start_house);
	debug($number_of_houses);
	
	if($start_house+$number_of_houses > 12){
		return $start_house - 1 + $number_of_houses-12;
	}
	else {
		return $number_of_houses + $start_house - 1;
	}
}

function targetJamaHouse($start_house,$number_of_houses)
{
	$temp = 0;
	debug($start_house);
	debug($number_of_houses);
	
	if($start_house-$number_of_houses > 0){
		$temp = $start_house - $number_of_houses;
		
		if($temp == 1 || $temp == 4 || $temp == 7 || $temp == 10){
			$temp = $temp - 1;
		}
	}
	else {
		$temp = 12 - abs($start_house-$number_of_houses);
		
		if($temp == 1 || $temp == 4 || $temp == 7 || $temp == 10){
			$temp = $temp - 1;
		}
	}
	
	debug("targetJH-".$start_house."-".$number_of_houses."-".$temp."<BR>");
	
	return $temp;
}

function updateMandhi(array $out, array $in, $in_sun_degree, $in_day, $in_dayofweek,$in_sun_house)
{	
	
	$mandhi_house = targetHouse($in_sun_house,findMandhiHouse($in_dayofweek,$in_sun_degree, $in_day));
	
	debug($mandhi_house);
	
	foreach ($in as $key => $value) {
		//mandhi house update
		if($key == $mandhi_house){
			$out[$key] = $out[$key] . " <span>மா</span>";
		}
	}
	
	return $out;
}

function updateSunDegree($in_deg)
{
	global $sun_degree;
// Converts decimal longitude / latitude to DMS
// ( Degrees / minutes / seconds ) 

// This is the piece of code which may appear to 
// be inefficient, but to avoid issues with floating
// point math we extract the integer part and the float
// part by using a string function.

    $vars = explode(".",$in_deg);
    $deg = $vars[0]+(isset($vars[1]) ? $vars[1] : 0)/100;
    
	return $deg;
} 



function displayDegree($sw_degree)
{
	global $out_planet_degree;
	
	$txt="";
	$planet="";
	
	foreach ($sw_degree as $key => $val) {
		if($val!=null) {
			
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
				case "true Node":
					$planet="ரா/கே";
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
			
			$txt = $txt . " " . $planet . "-" . $val;
		}
	}
	
	return $txt;
}

function uchchaDetails()
{
	global $outside;
	$uchchaDetails="இல்லை";
	$uPlanet="";
	
	foreach ($outside as $value){
		if(strlen($value)>0 && strpos($value,"&#8593;")>0){
		$nPlanet=$value;
		}
	}
	
	if(strlen($uPlanet)>0){
		$uchchaDetails = $uPlanet;
	}
	
	return $uchchaDetails;
}

function nechchaDetails()
{
	global $outside;
	$nechchaDetails="இல்லை";
	$nPlanet="";
	
	foreach ($outside as $value){
		if(strlen($value)>0 && strpos($value,"&#8595;")>0){
		$nPlanet=$value;
		}
	}
	
	if(strlen($nPlanet)>0){
		$nechchaDetails = $nPlanet;
	}
	
	return $nechchaDetails;
}

function displayAnalysis()
{
	global $outside, $inside, $udhayam_house, $aarudam_house,$udhaya_athipathi,$ubhaya_udhayam;
	
	$analysis= array ('ஆருட வேதை'=>null,'உதயம் வேதை'=>null,'உதயம் முதல் ஆருடம்'=>null,'உதயாதிபதி'=>null,'உபய உதயம்'=>null,'10ஆம் இடம்'=>null,'11ஆம் இடம்'=>null,'உச்சம்'=>null,'நீசம்'=>null);
	
	$analysis['ஆருட வேதை'] = aarudaVedhai();
	$analysis['உதயம் வேதை'] = udhayaVedhai();
	$analysis['உதயம் முதல் ஆருடம்'] = countHouses($udhayam_house,$aarudam_house);
	$analysis['உதயாதிபதி']=$udhaya_athipathi;
	$analysis['உபய உதயம்']=$ubhaya_udhayam;
	
	if($outside[targetHouse($udhayam_house,10)]==null){
		$analysis['10ஆம் இடம்']=$outside[targetHouse($udhayam_house,11)]."(நோக்கி)";
	}
	else{
		$analysis['10ஆம் இடம்']=$outside[targetHouse($udhayam_house,10)];
	}
	
	if($outside[targetHouse($udhayam_house,11)]==null){
		$analysis['11ஆம் இடம்']=$outside[targetHouse($udhayam_house,12)]."(நோக்கி)";
	}
	else{
		$analysis['11ஆம் இடம்']=$outside[targetHouse($udhayam_house,11)];
	}
	
	$analysis["கவிப்பு"." (".kavippuDetails()[1].")"]=kavippuDetails()[0];
	$analysis['உச்சம்']=uchchaDetails();
	$analysis['நீசம்']=nechchaDetails();
	
	return $analysis;
}



function findMandhiHouse($in_dayofweek,$in_sun_degree,$in_day)
{	
	switch ($in_dayofweek) {
		case 0: //Sun
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+156;
			}
			else{
				$mandhi_degree=$in_sun_degree+240;
			}
			break;
		case 1: //Mon
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+132;
			}
			else{
				$mandhi_degree=$in_sun_degree+216;
			}
			break;
		case 2: //Tue
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+108;
			}
			else{
				$mandhi_degree=$in_sun_degree+192;
			}
			break;
		case 3: //Wed
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+84;
			}
			else{
				$mandhi_degree=$in_sun_degree+336;
			}
			break;
		case 4: //Thur
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+60;
			}
			else{
				$mandhi_degree=$in_sun_degree+312;
			}
			break;
		case 5: //Fri
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+36;
			}
			else{
				$mandhi_degree=$in_sun_degree+288;
			}
			break;
		case 6:  //Sat
			if($in_day==1){
				$mandhi_degree=$in_sun_degree+12;
			}
			else{
				$mandhi_degree=$in_sun_degree+264;
			}
			break;
	  default:
		break;
	}
	
	$mandhi_house = (int)(($mandhi_degree % 360)/30);
	
	debug("FINDMANDHI - WEEK DAY=" . $in_dayofweek);
	debug("FINDMANDHI - DAYTIME=" . $in_day);
	debug("FINDMANDHI - MANDHI DEGREE=" . ($mandhi_degree % 30));
	debug("FINDMANDHI - SUN DEGREE=" . $in_sun_degree);
	debug("FINDMANDHI - MANDHI DEGREE FROM SUN=" . $mandhi_degree);
	debug("FINDMANDHI - MANDHI HOUSE=" . $mandhi_house);
	
	//update mandhi degree in degree array
	$sw_degree["mandhi"] = DECtoDMS($mandhi_degree % 30);
	
	return $mandhi_house;
}

function displayChart($in,$out)
{

echo <<<EOL
	<div class="divTable blueTable" id="chart">
		<div class="overlay1"><br>பெரியநாயகி துணை</br></div>
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[11]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[0]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[11]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[0]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[1]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[2]<span class='symbol'>&#9791;<Cspan></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[10]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;<span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[4]<span class='symbol'>&#9737;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[8]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[7]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[6]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[5]<span class='symbol'>&#9791;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[8]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[6]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[5]</div>
			</div>
		</div>	
	</div>

EOL;
}

function createAutoAnalysisSection()
{
	global $Analysis;
	
	echo <<<EOL
		<div class="section" id="section1">
			<div class="intro">
				<div class="container">
					<div style="height:60px"></div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableOuterCell" colspan=3 style="border-radius: 20px 20px 0 0;border:none;">
							<div class="header">Auto Analysis</div>
							</div>
						</div>
					</div>
			
					<div class="divTable blueTable">
						<div class="divTableBody">
EOL;

foreach ($Analysis as $key => $value){
//foreach ($out_planet_degree as $key => $value){
	echo "<div class='divTableRow'>";
	echo "<div class='divTableDegreeCell'>$key</div>";
	echo "<div class='divTableDegreeCell'>$value</div>";
	echo "</div>";	
}

echo <<<EOL
						</div>
					</div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableDummyCell" colspan=3 style="border-radius:  0 0 20px 20px;">
							<div class="footer">Scroll up for chart details	&#9650;</div>
							</div>
					</div>
				</div>
			</div>
		</div>
	</div>
EOL;

}

dbConnect($conn);

startSqlTxn($conn);

if($DEBUG==0){
	try {
		$sql="CREATE TEMPORARY TABLE `input_data` (
		  `in_sunrise` datetime NOT NULL,
		  `in_sunset` datetime NOT NULL,
		  `in_sunrise_tom` datetime NOT NULL,
		  `in_sunset_prev` datetime NOT NULL,
		  `in_chart_date` date NOT NULL,
		  `in_chart_time` datetime NOT NULL,
		  `in_place` varchar(20) NOT NULL,
		  `in_timezone` time NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

		if($res=mysqli_query($conn, $sql)){
			if($DEBUG==1){ echo "prepareChartData() executed"; }
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);	
	}catch (mysqli_sql_exception $exception) {
		mysqli_rollback($mysqli);

		throw $exception;
	}	

	try {
		$sql="CREATE TEMPORARY TABLE `temp_data` (
		  `house` tinyint(4) NOT NULL,
		  `outside` varchar(20) DEFAULT NULL,
		  `inside` varchar(250) DEFAULT NULL,
		  `start_time` datetime NOT NULL,
		  `end_time` datetime NOT NULL
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


		if($res=mysqli_query($conn, $sql)){
			if($DEBUG==1){ echo "prepareChartData() executed"; }
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);	
	}catch (mysqli_sql_exception $exception) {
		mysqli_rollback($mysqli);

		throw $exception;
	}
}

$in_place = isset($_POST["place"]) ? $_POST["place"] : $_POST["nPlace"];
$place=strtok($in_place,"|");
$TZ=strtok("|");

$_SESSION["place"]=$place;

$cookie_name = "place";
$cookie_value = $in_place;
setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day

$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$place."'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $lat = $row["Latitude"];
    $lon = $row["Longitude"];
    $gmt_offset = $row["GMT_offset"];
    $gmt_offset_dst=$row["GMT_offset_dsl"];
  }
} else {
  echo "0 results";
}

if($DEBUG==1){
echo "DATE: " . $_POST["in_date"]."<BR>";
echo "TIME: " . $_POST["in_time"]."<BR>";
echo "PLACE: " . $_POST["place"]."<BR>";
echo "LAT: " . $lat."<BR>";
echo "LONG: " . $lon."<BR>";
echo "GMT offset: " . $gmt_offset."<BR>";
echo "GMT offset dst: " . $gmt_offset_dst."<BR>";
echo "TZ: " . $TZ ."<BR>";
}

date_default_timezone_set($TZ);

if(date('I')==1) { // DST
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
}

$next_day=date("Y-m-d",strtotime($chart_date. ' + 1 days'));
$previous_day=date("Y-m-d",strtotime($chart_date. ' - 1 days'));

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
	exec ("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	debug("<BR>swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	exec ("swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $output);
	debug("<BR>swetest -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

debug("swetest64 -edir$libPath -b$date -ut$time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys");

# OUTPUT ARRAY
# Planet Name, Planet Degree, Planet Speed per day
 
if($DEBUG==1){ 
	var_dump($output);
}

$count=0;
foreach ($output as $key => $line)
  {
	global $ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
    if($count!=21){
		$sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($transit_sun_house==0 and strpos($row[0],'Sun')===0){
		$sun_house = (int)($tot_degree[$key]/30)+1;
		$sun_degree = updateSunDegree(round($tot_degree[$key] - $sw_house[$key]*30,2));
	}
	
	$sw_planet[$sw_house[$key]] = $sw_planet[$sw_house[$key]]." ".$row[0];
	
	$tot_degree[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
		
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
  $sw_planet = updatePlanet($sw_planet,$sw_dir, $ragu_house);

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

// Update Mandhi
$sw_planet = updateMandhi($sw_planet,$sw_planet,$sun_degree,$day,$dayofweek,$sun_house);

if($DEBUG==1){
	var_export($sw_planet);
}

//Update planet symbol in Jama planets (tr_sw_planet)
//$tr_sw_planet = updateSymbols($tr_sw_planet);

//Update planet strengths
$sw_planet = updateStrength($sw_planet);

//Get transit data (outside)

$transit_date = isset($_POST['nTrDate']) ? $_POST['nTrDate'] : date('Y-m-d');
$transit_time = isset($_POST['nTrTime']) ? $_POST['nTrTime'] : date('H:i:s');

$date = date("d.m.Y",strtotime($transit_date));
$time = date("H:i:s",strtotime($transit_time));

$transit_datetime = $date . " " . $time;

$transitTimestamp = strtotime($transit_datetime);
$utcTimestamp = $transitTimestamp - $offset * (60 * 60);

$currUTC_date = date('d.m.Y', $utcTimestamp);
$currUTC_time = date('H:i:s', $utcTimestamp);

$transit_dayofweek = date('w', strtotime($transit_date));

$transit_next_day=date("Y-m-d",strtotime($transit_date. ' + 1 days'));
$transit_previous_day=date("Y-m-d",strtotime($transit_date. ' - 1 days'));

$transit_today_sunrise = $transit_date . " " . getSunrise(strtotime($transit_date),floatval($lat), floatval($lon));
$transit_today_sunset = $transit_date . " " .  getSunset(strtotime($transit_date),floatval($lat), floatval($lon));
$transit_tomo_sunrise = $transit_next_day . " " .  getSunrise(strtotime($transit_next_day),floatval($lat), floatval($lon));
$transit_prev_sunset = $transit_previous_day . " " .  getSunset(strtotime($transit_previous_day),floatval($lat), floatval($lon));

debug("TRANSIT DATE=".$transit_date);
debug("TRANSIT TIME=".$transit_time);
debug("TRANSIT TODAY SUNRISE=".date("H:i:s",strtotime($transit_today_sunrise)));
debug("TRANSIT TODAY SUNSET=".date("H:i:s",strtotime($transit_today_sunset)));

// find if it's day or night for mandhi calculation
if(strtotime($transit_datetime) >= strtotime($transit_today_sunrise) && strtotime($transit_datetime) <= strtotime($transit_today_sunset)) {
	$transit_day=1;
}else {
	$transit_day=0;
}

debug("TRANSIT DAY=" . $transit_day);

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	debug('RUNNING ON WINDOWS');
	
	//exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);
	//64 version runs little faster
	exec ("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);
	debug("<BR>swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
} else {
	debug('RUNNING ON LINUX');
	
	//on linux
	exec ("swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys", $transit);
	debug("<BR>swetest -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys</BR>");
}

debug("swetest64 -edir$libPath -b$currUTC_date -ut$currUTC_time -p0123456t -n1 -sid1 -eswe -fPls -g, -head -house$lon,$lat,$h_sys");

$count=0;
foreach ($transit as $key => $line)
  {
	global $tr_ragu_house,$transit_sun_degree, $transit_sun_house;
	$count=$count+1;
    $row = explode(',',$line);
	
	if($DEBUG==1){ var_dump($row);}
    
	if($count>8 && $count!=21) {continue;}
	//$sw_planet[$key] = $row[0];
    $tot_degree[$key] = $row[1];
	
	$tr_sw_house[$key] = (int)((float)$tot_degree[$key]/30.0);
	
	//$sw_degree[$key] = DECtoDMS(round($tot_degree[$key] - $sw_house[$key]*30,2));
	
	$tr_sw_degree[$row[0]] = DECtoDMS(round($tot_degree[$key] - $tr_sw_house[$key]*30,2));
	
    if($count!=21){
		$tr_sw_dir[$key] = ($row[2] > 0) - ($row[2] < 0);
	}
	
	if($transit_sun_house==0 and strpos($row[0],'Sun')===0){
		$transit_sun_house = (int)($tot_degree[$key]/30)+1;
		$transit_sun_degree = updateSunDegree(round($tot_degree[$key] - $tr_sw_house[$key]*30,2)); 
	}
	
	$tr_sw_planet[$tr_sw_house[$key]] = $tr_sw_planet[$tr_sw_house[$key]]." ".$row[0];
	
	$tot_degree[$row[0]] = $row[1];
	
	// Store ragu house position for future use to find kethu position.
	if(strpos($row[0],"true Node")===0){ // using === because this strpos returns 0 when true Node found and also false(0) when it's not found. So we need to use ===
		
		$tr_ragu_house = $tr_sw_house[$key];
		if($DEBUG==1){ echo "tr_ragu_house=" . $tr_sw_house[$key]."<br>";}
	}
		
	if($DEBUG==1 && $count!=21){ echo $row[0] ."|".$tr_sw_planet[$key]."|".$tr_sw_planet[$key]."|".$tr_sw_degree[$key]."|".$tr_sw_dir[$key]."|"."<br>";}
  };
  
if($DEBUG==1){ 
  echo "<br>Transit[".$sw_planet[0]."]</br>";
  var_dump($tr_sw_planet);
  var_dump($tr_sw_degree);
  var_dump($tr_sw_dir);
}
  $tr_sw_planet = updatePlanet($tr_sw_planet,$tr_sw_dir, $tr_ragu_house);

// ------- Swiss ephemeris -------------

if($DEBUG==1){
	echo "HOUR:" . $hour."<BR>";
	echo "MIN:" . $min."<BR>";
	echo "OFFSET:" . $offset."<BR>";
	echo "PLACE:" . $place."<BR>";
	echo "tz offset :" . $tz_offset."<BR>";
}

// Update Mandhi
$tr_sw_planet = updateMandhi($tr_sw_planet,$tr_sw_planet,$transit_sun_degree,$transit_day,$transit_dayofweek,$transit_sun_house);


if($DEBUG==1){
	var_export($tr_sw_planet);
	var_export($sw_planet);
}

//Update planet symbol in Jama planets (tr_sw_planet)
//$tr_sw_planet = updateSymbols($tr_sw_planet);

//Update planet strengths
$tr_sw_planet = updateStrength($tr_sw_planet);


$chartdate=date("d/m/Y",strtotime($chart_date));
$Planet_degree=displayDegree($sw_degree);
$Transit_Planet_degree=displayDegree($tr_sw_degree);


echo <<<EOL
	<div class="title"></div>
	<div id="fullpage">
		<div class="section fp-auto-height-responsive" id="section0">

			<div class="intro">
					<BR><BR><BR><BR><BR>
					<center><div class="divTable blueTable" style="height:70px;width:360px;">
								<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
									<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">
										
										<H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">$place</H4>
										<form name='updateChart' action='BN_Naadi_Chart.php' method='post'>
											<input type='hidden' id='nPlace' name='nPlace' value=$in_place>
											<H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px; text-align:center">Birth Date: <input type='date' id='nDate' name='nDate' style="background-color:#FDE966;color:#660000;font-size:12px;font-weight:bold;width:80px;" value=$chart_date > Birth Time: <input type='time' id='nTime' name='nTime' style="background-color:#FDE966;color:#660000;font-size:12px;font-weight:bold;width:70px" step='1' value=$chart_time></H4>
											<H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:14px; text-align:center">Transit Date: <input type='date' id='nTrDate' name='nTrDate' style="background-color:#83EEFD;color:#660000;font-size:12px;font-weight:bold;width:80px;" value=$transit_date> Transit Time: <input type='time' id='nTrTime' name='nTrTime' style="background-color:#83EEFD;color:#660000;font-size:12px;font-weight:bold;width:70px" step='1' value=$transit_time></H4>
											<BR>
											<input type='submit' id='submit' value='Submit' style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;">

										</form>
										
										<ul id="menu" align="center">
											<li data-menuanchor="firstPage"><a href="BN_Naadi_input.php">New BN Naadi chart</a></li>
EOL;
												if(!isset($_SESSION['phone'])){
													echo '<li data-menuanchor="firstPage"><a href="index.php">Home</a></li>';
												}
											echo<<<EOL
												<li data-menuanchor="secondPage"><a href="viewHistoricalCharts.php" target="_blank">Charts history</a></li>
											</ul>
									</div>
								</div>
							</div>
				<div class="container" id="container" style="horizontal-align:center">
					
					<div class="divTable blueTable" id="chart">
						<div class="overlay2"><br>பெரியநாயகி துணை<br><br><br><br><br><br><br><br><br><br><br><br><br><br><P>Brihu Nandhi<P></div>
						<div class="divTableBody">
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[11]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[0]</div>
									<div class="divTableOuterCell">$tr_sw_planet[1]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[2]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell"></div>
									<div class="divTableCell">$sw_planet[11]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[0]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[1]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[2]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[10]</div>
									<div class="divTableCell">$sw_planet[10]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCell">$sw_planet[3]<span class='symbol'>&#9790;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[3]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[9]</div>
									<div class="divTableCell">$sw_planet[9]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCell">$sw_planet[4]<span class='symbol'>&#9737;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[4]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell"></div>
									<div class="divTableCell">$sw_planet[8]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[7]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[6]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[5]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[8]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[7]</div>
									<div class="divTableOuterCell">$tr_sw_planet[6]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[5]</div>
							</div>
						</div>	
					</div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableCell" colspan=3 style="border-radius:  0 0 20px 20px">
								<H6 style='font-weight:bold'>Birth chart (inner)</H6>
								<H6 style='background-color:white;'>$Planet_degree</H6>
							</div>
						</div>
					
					<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
						<div class="divTableOuterCell" colspan=3 style="border-radius:  0 0 20px 20px;">
							<H6 style='font-weight:bold;font-size:12px'>Transit chart (outer)</H6>
							<H6 style='background-color:white'>$Transit_Planet_degree</H6>
							<div style="color:blue;font-size:14px;background-color:white"><a href="saveBNChart.php" id="save" onClick="window.open('saveBNChart.php', 'saveChart', 'toolbar=no, directories=no,location=no, status=yes, menubar=no, resizable=no, scrollbars=yes'); return false">Click to save chart</a></div>
						</div>
							
					</div>

					</div></center>
				</div>
			</div>
		</div>
		
		
EOL;

if($autoAnalysisMenu==1){
	createAutoAnalysisSection();
}

if($cricketMenu==1){
	createCricketSection();
}

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'BNN|'.$place."|".$chart_date."|".$chart_time."|".$transit_date."|".$transit_time;
	logActivity($conn,$_COOKIE["phone"],$activity,date('Y-m-d H:i:s'));
}

dbDisconnect($conn);

$_POST["mTime"]="";
$_SESSION["charttime"]="";
$_SESSION["inside"]=$sw_planet;
$_SESSION["outside"]=$tr_sw_planet;
$_SESSION["planet_degree"]=$Planet_degree;
$_SESSION["transit_planet_degree"]=$Transit_Planet_degree;
$_SESSION["transit_date"]=$transit_date;
$_SESSION["transit_time"]=$transit_time;
?>

</body>
</html>