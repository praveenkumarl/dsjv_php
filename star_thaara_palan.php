<?php session_start();
require "lib.php";
require "plib.php";

date_default_timezone_set("Asia/Kolkata");

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$time = isset($_POST["in_time"]) ? $_POST["in_time"] : date("H:i");
$date = isset($_POST["in_date"]) ? $_POST["in_date"] : date("Y-m-d");

if(isset($_POST['star'])){
	$star = isset($_POST['temp']) && $_POST['temp']==1  ? getStar($date,$time) : $_POST['star'];
}
else{
	$star = getStar($date,$time);
}

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
}

//$star = getStar($date,$time);

function getStar($date,$time)
{
	//Usage: panchanga -d dd/mm/yyy -t hh:mm -z [+/-]hh:mm
	//-d : Date in dd/mm/yyyy format.
	//-t : Time in hh:mm 24-hour format.
	//-z : Zone with respect to GMT in [+/-]hh:mm format.

	$p = new Panchang();
	$dd = date('d',strtotime($date));
	$mm = date('m',strtotime($date));
	$yy = date('Y',strtotime($date));
	$hh = date('H',strtotime($time));
	$mn = date('i',strtotime($time));

	debug("DD=".$dd . "|" . "MM=".$mm . "|" . "YY=" . $yy . "|"."hh=".$hh  . "|" . "mn=".$mn); 

	$zhh = +5;
	$zmn = 30;

	$hr = $hh + $mn/60.0;
	$zhr = $zhh + $zmn/60.0;

	$data = $p->calcPanchanga($dd, $mm, $yy, $hr, $zhr);

	$temp_arr = explode("/",$data['nakshatra']);
	
	return $temp_arr[0];
}


function calculateThaaraPalan($inStar){
	
	$thaarapalan = array();
	
	$star_arr = array("dummy","Aswini","Bharani","Krithika","Rohini","Mrigashirsha","Arudra", "Punarvasu","Pushyami","Ashlesha","Magha","Poorva Phalguni","Uthra phalguni","Hastha","Chitra","Swaathi","Vishaakha","Anuraadha","Jyeshta","Moola","Poorva Ashaada","Uthra Ashaada","Shraavan","Dhanishta","Shathabhisha","Poorva bhadrapada","Uthra bhadrapada","Revathi");
	//$star_arr_result = array("dummy","Aswini/அசுவினி","Bharani/பரணி","Krithika/கிருத்திகை","Rohini/ரோகிணி","Mrigashirsha/மிருகசிரீஷம்","Arudra/திருவாதிரை","Punarvasu/புனர்பூசம்","Pushyami/பூசம்","Ashlesha/ஆயில்யம்","Magha/மகம்","Poorva Phalguni/பூரம்","Uthra phalguni/உத்திரம்","Hastha/ஹஸ்தம்","Chitra/சித்திரை","Swaathi/சுவாதி","Vishaakha/விசாகம்","Anuraadha/அனுஷம்","Jyeshta/கேட்டை","Moola/மூலம்","Poorva Ashaada/பூராடம்","Uthra Ashaada/உத்திராடம்","Shraavan/திருவோணம்","Dhanishta/அவிட்டம்","Shathabhisha/சதயம்","Poorva bhadrapada/பூரட்டாதி","Uthra bhadrapada/உத்திரட்டாதி","Revathi/ரேவதி");
	$star_arr_result = array("dummy","அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி");
	foreach ($star_arr as $key => $value){
		if($key==0) {continue;}
		
		$start_pos = array_search($inStar,$star_arr);
		$end_pos = array_search($value,$star_arr);
		
		if($start_pos > 1 && $start_pos>$end_pos){
			//echo $value . (27-$start_pos+$end_pos+1) . "<br>";
			debug("[" . $inStar . "=" . $start_pos . "]" . "[" . $value . "=" . (27-$start_pos+$end_pos+1) . "]" . "<br>");
			$thaarapalan[$star_arr_result[$key]] = 27-$start_pos+$end_pos+1;
			
			if((27-$start_pos+$end_pos+1)==22) {
				debug("<span style='color:white;background-color:red;font-size:11pt'>VAINASIKA STAR - ". $value . "</span><br>");
				$thaarapalan['VAINASIKA'] = $star_arr_result[$key];
			}
			continue;
		}
		else{
			debug("[" . $inStar . "=" . $start_pos . "]" . "[" . $value . "=" . ($end_pos-$start_pos+1) . "]" . "<br>");
			$thaarapalan[$star_arr_result[$key]] = $end_pos-$start_pos+1;
		}
		
		if(($end_pos-$start_pos+1)==22) {
			debug("<span style='color:white;background-color:red;font-size:11pt'>VAINASIKA STAR - ". $value . "</span><br>");
			$thaarapalan['VAINASIKA'] = $star_arr_result[$key];
		}
	}
	
	return $thaarapalan;
}

function displayThaaraPalan(){
	global $star;
	
	$palan = array("dummy","ஜென்ம தாரை","சம்பத் தாரை","விபத் தாரை","ஷேம தாரை","பிரத்யக் தாரை","சாதக தாரை","வதை தாரை","மைத்ர தாரை","பரம மைத்ர தாரை");
	$palan_detail = array("dummy","மனக்குழப்பம், பதற்றம் , தொழில் துவங்க நல்லது இல்லை","தனவரவு, பொருள் வரவு, கரிய சித்தி, சுபகாரியம் செய்யலாம்","தவிர்க்க வேண்டிய நாள்..பயணங்கள் தவிர்ப்பது நல்லது,கோபத்தால் காரிய  இழப்பு, வாய்ப்புகள் தவறுதல் ","ஆரோக்கியம், மகிழ்ச்சி, நன்மை தரக்கூடியது என்று அறியவும்","வீண் அலைச்சல், சிக்கல், மன குழப்பம், கவன சிதறல் தரும்","எண்ணம் ஈடேறும், புதிய முயற்சி பலிதம், செயல்களுக்கு சாதகமானது","கடுமையான தீமை தரக்கூடியது,உடலில் சோர்வு, மனதில் இனம் புரியாத கவலை,  பணிகளில் நாட்டமில்லாமை","தெய்வ காரியங்கள் செய்தல், புதிய முயற்சி, புதிய செயல்கள் செய்யலாம்","அனைத்து சுப செயல்களுக்கு உகந்த நாள்");
	
	$thaarapalan_arr = calculateThaaraPalan($star);
	
	debug($thaarapalan_arr);

	echo "<div class='divTable blueTable' style='width:95%'>";
	echo "<div class='divTableRow'>";
			echo "<div class='divThaaraPalanHeading'>Star/நட்</div>";
			echo "<div class='divThaaraPalanHeading'>Thaara palan#</div>";
			echo "<div class='divThaaraPalanHeading'>Thaara palan</div>";
			echo "<div class='divThaaraPalanHeading'>Thaara palan detail</div>";
	echo "</div>";
	
	foreach($thaarapalan_arr as $key => $value)
	{
		if(!is_numeric($value)) {
			continue;
		}
		
		$reminder = $value % 9;

		echo "<div class='divTableRow'>";
			echo "<div class='divThaaraPalanWhite'>".$key."</div>";
			
			if($reminder == 0){
				if($value == 18){
					//echo "<div class='divThaaraPalanRedBright'>".($value % 9)."</div>";
					echo "<div class='divThaaraPalanRedBright'>$value</div>";
					echo "<div class='divThaaraPalanRedBright'>பணப்பகை நட்சத்திரம்</div>";
					echo "<div class='divThaaraPalanRedBright'>நல்லதல்ல</div>";
				}
				else{
					//echo "<div class='divThaaraPalanGreenBright'>"."9"."</div>";
					echo "<div class='divThaaraPalanGreenBright'>$value</div>";
					echo "<div class='divThaaraPalanGreenBright'>".($palan[9])."</div>";
					echo "<div class='divThaaraPalanGreenBright'>".($palan_detail[9])."</div>";
				}
			}
			else{
				if($reminder % 2 == 0){
					
					if($value == 22){
						//echo "<div class='divThaaraPalanRedBright'>".($value % 9)."</div>";
						echo "<div class='divThaaraPalanRedBright'>$value</div>";
						echo "<div class='divThaaraPalanRedBright'>வைநாசிக நட்சத்திரம்</div>";
						echo "<div class='divThaaraPalanRedBright'>நல்லதல்ல</div>";
					}
					else{
						//echo "<div class='divThaaraPalanGreen'>".($value % 9)."</div>";
						echo "<div class='divThaaraPalanGreen'>$value</div>";
						echo "<div class='divThaaraPalanGreen'>".($palan[$value % 9])."</div>";
						echo "<div class='divThaaraPalanGreen'>".($palan_detail[$reminder])."</div>";
					}
				}
				else{
					//echo "<div class='divThaaraPalanRed'>".($value % 9)."</div>";
					echo "<div class='divThaaraPalanRed'>$value</div>";
					echo "<div class='divThaaraPalanRed'>".($palan[$value % 9])."</div>";
					echo "<div class='divThaaraPalanRed'>".($palan_detail[$reminder])."</div>";
				}
			}
			
		echo "</div>";
	}
	
	echo "</div>";
}

$conn="";
$conn=dbConnect($conn);

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'ThaaraPalan';
	date_default_timezone_set('Asia/Kolkata');
	logActivity($conn,$_COOKIE["phone"],$activity,date('Y-m-d H:i:s'));
}

dbDisconnect($conn);
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Thaara palan</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	 
    <script src="script.js"></script>
    <script type="text/javascript">
		

function init(){
	
}

function formSubmitButton(){
	document.thaaraForm.temp.value = 1;
	document.forms["thaaraForm"].submit();
}

function formSubmitDropdown(){
	//document.thaaraForm.temp.value = 0;
	//document.thaaraForm.in_date.value = "";
	//document.thaaraForm.in_time.value = "";
	document.forms["thaaraForm"].submit();
}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

	<div class='column-menu'>
	<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
	</div>
	<div class='mobile'>
		<?php include "thaaraPalanMenu_mobile.php"; ?>
	</div>
	</div>
	<div class='column-body'>
				<BR>
				<div class="heading">*தாரா பலன்</div>
				<BR>
				<form name="thaaraForm" action="star_thaara_palan.php" method="post">
				<table width="95%">
				
					<!-- 
					<tr style="background:none;"> 
						<td>
							<H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">Date :</H5>
							<input type="date" id="in_date" name="in_date" style="width:auto;font-size:12px;background:#FDE966" value = '<?php echo $date; ?>' />
						</td>
						<td>		
							<H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">Time :</H5>
							<input type="time" id="in_time" name="in_time" step="1" style="width:auto;font-size:12px;background:#FDE966" value = '<?php echo $time; ?>' />
							<input type="hidden" name="temp" id="temp" value="">
							<input type='button' id='click' value='Submit' onClick="formSubmitButton()" style="background-color:gold;color:#660000;font-weight:bold;font-size:10px;">
						</td>
					</tr>
					-->
					
					<tr style="background:none;">
						<td  style="vertical-align:middle">
							<DIV class='divPanchangHeading' style='border:none'>*உங்கள் நட்சத்திரம்</div>
						</td>
						<td style="vertical-align:middle">
								<!-- <select name="star" id="star" style="width:fit;font-size:12px;background:#FDE966;font-weight:bold" onChange="formSubmitDropdown()">
										<option value="Aswini" <?php if(strcmp($star,'Aswini')==0){echo 'selected';} ?> >Aswini/அசுவினி</option>
										<option value="Bharani" <?php if(strcmp($star,'Bharani')==0){echo 'selected';} ?> >Bharani/பரணி</option>
										<option value="Krithika" <?php if(strcmp($star,'Krithika')==0){echo 'selected';} ?> >Krithika/கிருத்திகை</option>
										<option value="Rohini" <?php if(strcmp($star,'Rohini')==0){echo 'selected';} ?> >Rohini/ரோகிணி</option>
										<option value="Mrigashirsha" <?php if(strcmp($star,'Mrigashirsha')==0){echo 'selected';} ?> >Mrigashirsha/மிருகசிரீஷம்</option>
										<option value="Arudra" <?php if(strcmp($star,'Arudra')==0){echo 'selected';} ?> >Arudra/திருவாதிரை</option>
										<option value="Punarvasu" <?php if(strcmp($star,'Punarvasu')==0){echo 'selected';} ?> >Punarvasu/புனர்பூசம்</option>
										<option value="Pushyami" <?php if(strcmp($star,'Pushyami')==0){echo 'selected';} ?> >Pushyami/பூசம்</option>
										<option value="Ashlesha" <?php if(strcmp($star,'Ashlesha')==0){echo 'selected';} ?> >Ashlesha/ஆயில்யம்</option>
										<option value="Magha" <?php if(strcmp($star,'Magha')==0){echo 'selected';} ?> >Magha/மகம்</option>
										<option value="Poorva Phalguni" <?php if(strcmp($star,'Poorva Phalguni')==0){echo 'selected';} ?> >Poorva Phalguni/பூரம்</option>
										<option value="Uthra phalguni" <?php if(strcmp($star,'Uthra phalguni')==0){echo 'selected';} ?> >Uthra phalguni/உத்திரம்</option>
										<option value="Hastha" <?php if(strcmp($star,'Hastha')==0){echo 'selected';} ?> >Hastha/ஹஸ்தம்</option>
										<option value="Chitra" <?php if(strcmp($star,'Chitra')==0){echo 'selected';} ?> >Chitra/சித்திரை</option>
										<option value="Swaathi" <?php if(strcmp($star,'Swaathi')==0){echo 'selected';} ?> >Swaathi/சுவாதி</option>
										<option value="Vishaakha" <?php if(strcmp($star,'Vishaakha')==0){echo 'selected';} ?> >Vishaakha/விசாகம்</option>
										<option value="Anuraadha" <?php if(strcmp($star,'Anuraadha')==0){echo 'selected';} ?> >Anuraadha/அனுஷம்</option>
										<option value="Jyeshta" <?php if(strcmp($star,'Jyeshta')==0){echo 'selected';} ?> >Jyeshta/கேட்டை</option>
										<option value="Moola" <?php if(strcmp($star,'Moola')==0){echo 'selected';} ?> >Moola/மூலம்</option>
										<option value="Poorva Ashaada" <?php if(strcmp($star,'Poorva Ashaada')==0){echo 'selected';} ?> >Poorva Ashaada/பூராடம்</option>
										<option value="Uthra Ashaada" <?php if(strcmp($star,'Uthra Ashaada')==0){echo 'selected';} ?> >Uthra Ashaada/உத்திராடம்</option>
										<option value="Shraavan" <?php if(strcmp($star,'Shraavan')==0){echo 'selected';} ?> >Shraavan/திருவோணம்</option>
										<option value="Dhanishta" <?php if(strcmp($star,'Dhanishta')==0){echo 'selected';} ?> >Dhanishta/அவிட்டம்</option>
										<option value="Shathabhisha" <?php if(strcmp($star,'Shathabhisha')==0){echo 'selected';} ?> >Shathabhisha/சதயம்</option>
										<option value="Poorva bhadrapada" <?php if(strcmp($star,'Poorva bhadrapada')==0){echo 'selected';} ?> >Poorva bhadrapada/பூரட்டாதி</option>
										<option value="Uthra bhadrapada" <?php if(strcmp($star,'Uthra bhadrapada')==0){echo 'selected';} ?> >Uthra bhadrapada/உத்திரட்டாதி</option>
										<option value="Revathi" <?php if(strcmp($star,'Revathi')==0){echo 'selected';} ?> >Revathi/ரேவதி</option>
									</select> -->
									
							<select name="star" id="star" style="width:fit;font-size:12px;background:#FDE966;font-weight:bold" onChange="formSubmitDropdown()">
										<option value="Aswini" <?php if(strcmp($star,'Aswini')==0){echo 'selected';} ?> >அசுவினி</option>
										<option value="Bharani" <?php if(strcmp($star,'Bharani')==0){echo 'selected';} ?> >பரணி</option>
										<option value="Krithika" <?php if(strcmp($star,'Krithika')==0){echo 'selected';} ?> >கிருத்திகை</option>
										<option value="Rohini" <?php if(strcmp($star,'Rohini')==0){echo 'selected';} ?> >ரோகிணி</option>
										<option value="Mrigashirsha" <?php if(strcmp($star,'Mrigashirsha')==0){echo 'selected';} ?> >மிருகசிரீஷம்</option>
										<option value="Arudra" <?php if(strcmp($star,'Arudra')==0){echo 'selected';} ?> >திருவாதிரை</option>
										<option value="Punarvasu" <?php if(strcmp($star,'Punarvasu')==0){echo 'selected';} ?> >புனர்பூசம்</option>
										<option value="Pushyami" <?php if(strcmp($star,'Pushyami')==0){echo 'selected';} ?> >பூசம்</option>
										<option value="Ashlesha" <?php if(strcmp($star,'Ashlesha')==0){echo 'selected';} ?> >ஆயில்யம்</option>
										<option value="Magha" <?php if(strcmp($star,'Magha')==0){echo 'selected';} ?> >மகம்</option>
										<option value="Poorva Phalguni" <?php if(strcmp($star,'Poorva Phalguni')==0){echo 'selected';} ?> >பூரம்</option>
										<option value="Uthra phalguni" <?php if(strcmp($star,'Uthra phalguni')==0){echo 'selected';} ?> >உத்திரம்</option>
										<option value="Hastha" <?php if(strcmp($star,'Hastha')==0){echo 'selected';} ?> >ஹஸ்தம்</option>
										<option value="Chitra" <?php if(strcmp($star,'Chitra')==0){echo 'selected';} ?> >சித்திரை</option>
										<option value="Swaathi" <?php if(strcmp($star,'Swaathi')==0){echo 'selected';} ?> >சுவாதி</option>
										<option value="Vishaakha" <?php if(strcmp($star,'Vishaakha')==0){echo 'selected';} ?> >விசாகம்</option>
										<option value="Anuraadha" <?php if(strcmp($star,'Anuraadha')==0){echo 'selected';} ?> >அனுஷம்</option>
										<option value="Jyeshta" <?php if(strcmp($star,'Jyeshta')==0){echo 'selected';} ?> >கேட்டை</option>
										<option value="Moola" <?php if(strcmp($star,'Moola')==0){echo 'selected';} ?> >மூலம்</option>
										<option value="Poorva Ashaada" <?php if(strcmp($star,'Poorva Ashaada')==0){echo 'selected';} ?> >பூராடம்</option>
										<option value="Uthra Ashaada" <?php if(strcmp($star,'Uthra Ashaada')==0){echo 'selected';} ?> >உத்திராடம்</option>
										<option value="Shraavan" <?php if(strcmp($star,'Shraavan')==0){echo 'selected';} ?> >திருவோணம்</option>
										<option value="Dhanishta" <?php if(strcmp($star,'Dhanishta')==0){echo 'selected';} ?> >அவிட்டம்</option>
										<option value="Shathabhisha" <?php if(strcmp($star,'Shathabhisha')==0){echo 'selected';} ?> >சதயம்</option>
										<option value="Poorva bhadrapada" <?php if(strcmp($star,'Poorva bhadrapada')==0){echo 'selected';} ?> >பூரட்டாதி</option>
										<option value="Uthra bhadrapada" <?php if(strcmp($star,'Uthra bhadrapada')==0){echo 'selected';} ?> >உத்திரட்டாதி</option>
										<option value="Revathi" <?php if(strcmp($star,'Revathi')==0){echo 'selected';} ?> >ரேவதி</option>
									</select>
									
						</td>
					</tr>
					<tr style="background:none">
						<td colspan="2" align="center">
						<?php displayThaaraPalan(); ?>
						</td>
					</tr>
					
					<tr style="background:none">
						<td colspan='2' align='center'><div id='error' style='color:red;'></div></td>
					</tr>
				</table>
				</form>
	</div>	 

</body>

</html>
