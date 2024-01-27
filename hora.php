<?php 

require "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$conn="";
$lat="";
$lon="";
$gmt_offset="";
$gmt_offset_dst="";
$city="";
$sunrise=""; 
$sunset="";
$sunrise_tom="";
$day_hora_data=array();
$night_hora_data=array();
$current_hora=array();

$place = isset($_POST['place']) ? $_POST['place'] : "Chennai|Asia/Kolkata";
$sub_type = isset($_POST['sub_type']) ? $_POST['sub_type'] : "2"; //to identify form submission button

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
}

$city=strtok($place,"|");

$TZ=strtok("|");

if(!empty($TZ))
{
	date_default_timezone_set($TZ);
}
else{
	date_default_timezone_set("Asia/Kolkata");
}

if(strpos($sub_type,'2')===0){
	$curr_time = isset($_POST["nTime"]) ? $_POST["nTime"] : date("H:i");
}
else{
	$curr_time = date("H:i");
}

if(strpos($sub_type,'2')===0){
	$curr_date = isset($_POST["nDate"]) ? $_POST["nDate"] : date("Y-m-d");
}
else{
	$curr_date = date("Y-m-d");
}

function displayHorai($lat,$lon)
{
	global $curr_time,$sunrise,$sunset,$curr_date,$day_hora_data,$night_hora_data;
	
	$sunrise = getSunrise(strtotime($curr_date),floatval($lat), floatval($lon));
	$sunrise = substr($sunrise,0,5);
	debug("SUNRISE=".$sunrise);

	$sunset = getSunset(strtotime($curr_date),floatval($lat), floatval($lon));
	$sunset = substr($sunset,0,5);
	debug("SUNSET=".$sunset);
	
	if(strtotime($curr_time) < strtotime($sunrise)){	//Before sunset, hence previous day hora chart
		calculateHoraPrevDay($lat,$lon);
		displayHoraTable($night_hora_data);
	}
	else{
		calculateHoraCurrentDay($lat,$lon);
		displayHoraTable($day_hora_data);
	}
}

function getSubHora($start_time,$curr_time,$hora_lord,$hora_time)
{
	$sub_hora_order_sun = array("சூரி","சுக்","புத","சந்","சனி","குரு","செ");
	$sub_hora_order_moo = array("சந்","சனி","குரு","செ","சூரி","சுக்","புத");
	$sub_hora_order_mer = array("புத","சந்","சனி","குரு","செ","சூரி","சுக்");
	$sub_hora_order_ven = array("சுக்","புத","சந்","சனி","குரு","செ","சூரி");
	$sub_hora_order_mar = array("செ","சூரி","சுக்","புத","சந்","சனி","குரு");
	$sub_hora_order_jup = array("குரு","செ","சூரி","சுக்","புத","சந்","சனி");
	$sub_hora_order_sat = array("சனி","குரு","செ","சூரி","சுக்","புத","சந்");
	
	debug ("getSubHoraTEST=".$hora_time);
	debug("getSubHora-start_time=".$start_time."getSubHora-curr_time=".$curr_time);
	
	$subHoraTime = minutesToTime(timeToMinutes($hora_time)/7);
	//$sub_hora_idx = ceil(timeToMinutes(subtractTime($curr_time,$start_time))/timeToMinutes($subHoraTime))-1;
	$sub_hora_idx = floor(timeToMinutes(subtractTime($curr_time,$start_time))/timeToMinutes($subHoraTime));
	
	debug ("subHoraTime=".$subHoraTime);

	if($sub_hora_idx==7)
	{
		$subHora_starttime = addTime($start_time,minutesToTime($sub_hora_idx*timeToMinutes($subHoraTime)));
		$sub_hora_idx = 6;
	}
	else{
		$subHora_starttime = addTime($start_time,minutesToTime($sub_hora_idx*timeToMinutes($subHoraTime)));
	}
	
	debug ("sub_hora_idx=".$sub_hora_idx);
	
	//$subHora_endtime = subtractTime(addTime($subHora_starttime,$subHoraTime),"00:01");
	$subHora_endtime = addTime($subHora_starttime,$subHoraTime);
	
	debug("subHora-starttime=".$subHora_starttime."subHora-endtime=".$subHora_endtime);
	
	$subHora = "";
	
	/*
	switch($hora_lord){
		case "சூரி":
			$subHora = "<BR>" . $sub_hora_order_sun[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "சுக்":
			$subHora = "<BR>" . $sub_hora_order_ven[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "புத":
			$subHora = "<BR>" . $sub_hora_order_mer[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "சந்":
			$subHora = "<BR>" . $sub_hora_order_moo[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "சனி":
			$subHora = "<BR>" . $sub_hora_order_sat[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "குரு":
			$subHora = "<BR>" . $sub_hora_order_jup[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
		case "செ":
			$subHora = "<BR>" . $sub_hora_order_mar[$sub_hora_idx] . "(" . date("h:i A",strtotime($subHora_starttime)) . "-" . date("h:i A",strtotime($subHora_endtime)) .")";
			break;
	}
	*/
	
	switch($hora_lord){
		case "சூரி":
			$subHora = "<BR>" . $sub_hora_order_sun[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "சுக்":
			$subHora = "<BR>" . $sub_hora_order_ven[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "புத":
			$subHora = "<BR>" . $sub_hora_order_mer[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "சந்":
			$subHora = "<BR>" . $sub_hora_order_moo[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "சனி":
			$subHora = "<BR>" . $sub_hora_order_sat[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "குரு":
			$subHora = "<BR>" . $sub_hora_order_jup[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
		case "செ":
			$subHora = "<BR>" . $sub_hora_order_mar[$sub_hora_idx] . "<BR>". date("h:i A",strtotime($subHora_endtime)) ." வரை";
			break;
	}
	
	return $subHora;
}

function calculateHoraCurrentDay($lat,$lon)
{
	global $curr_date, $curr_time, $sunrise, $sunset, $sunrise_tom, $next_day,$prev_day,$day_hora_data,$current_hora;
	$current_hora_flag = false;
	
	$sun_hora = array("சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத");
	$mon_hora = array("சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு");
	$tue_hora = array("செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்");
	$wed_hora = array("புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி");
	$thu_hora = array("குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி");
	$fri_hora = array("சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்");
	$sat_hora = array("சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ");
	
	$palan_detail = array("dummy");
	
	$dayofweek = date('w', strtotime($curr_date)); 
	
	$prev_day=date("Y-m-d",strtotime($curr_date. ' - 1 days'));
	$next_day=date("Y-m-d",strtotime($curr_date. ' + 1 days'));
	
	$sunrise_tom = getSunrise(strtotime($next_day),floatval($lat), floatval($lon));
	$sunrise_tom = substr($sunrise_tom,0,5);
	debug("SUNRISE TOMORROW=".$sunrise_tom);
	
	$sunset_prev = getSunset(strtotime($prev_day),floatval($lat), floatval($lon));
	$sunset_prev = substr($sunset_prev,0,5);
	debug("SUNSET YESTERDAY=".$sunset_prev);
	
	$day_hora_time = MinutesToTime(timeToMinutes(subtractTime($sunset,$sunrise))/12);
	
	$night_hora_time = MinutesToTime(timeToMinutes(addTime(subtractTime("23:59",$sunset),$sunrise_tom))/12);
	
	debug("***DAYOFWEEK=".$dayofweek."***");
	
	switch($dayofweek){
		case 0:
			$curr_day = $sun_hora;
			break;
		case 1:
			$curr_day = $mon_hora;
			break;
		case 2:
			$curr_day = $tue_hora;
			break;
		case 3:
			$curr_day = $wed_hora;
			break;
		case 4:
			$curr_day = $thu_hora;
			break;
		case 5:
			$curr_day = $fri_hora;
			break;
		case 6:
			$curr_day = $sat_hora;
			break;
	}
	
	$start_time = substr($sunrise,0,5);
	
	$count = 1;
	$temp =$sunrise;
	
	debug('[[[['.$curr_time.'[[[['.$sunrise.'[[[['.$sunset.'[[[[');
	
	if(strtotime($curr_time) > strtotime($sunrise) && strtotime($curr_time) < strtotime($sunset)){	
		$hora_period = $day_hora_time;
		debug("day_hora_time=".$day_hora_time);
	}
	else{
		$hora_period = $night_hora_time;
		debug("night_hora_time=".$night_hora_time);
	}
	
	foreach($curr_day as $value){
		
		if($count <= 12){ //day time
			debug("Inside if count<=12");
			$end_time = addTime($start_time,$day_hora_time);
			$end_time = subtractTime($end_time,"00:00:01"); //hora ends at end of the every minute, so subtracting 1 sec
			
			if($count==12) {
				$end_time = $sunset;
			}
			debug("<BR>C".$curr_date." s".$start_time."---c".$curr_time." e".$end_time."---".$value."<BR>");
			
			if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
				//$value = $value . getSubHora($start_time,$curr_time,$value,$day_hora_time);
				$day_hora_data[$count] = array($curr_date, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$day_hora_time));
				$current_hora_flag = true;
				$current_hora = $day_hora_data[$count];
			}
			else{
				$day_hora_data[$count] = array($curr_date, $start_time, $end_time, $value);
			}
			
			$start_time = $end_time;
		}
		else{  //night time
			$end_time = addTime($start_time,$night_hora_time);
			
			// To adjust time past midnight
			if(strtotime($end_time) > strtotime("23:59")){
				$end_time = addTime("00:00",subtractTime($end_time,"23:59"));
			}
			
			if($count==24) {
				debug("Inside if count==24");
				$end_time = $sunrise_tom;
			}
			
			if($count>18){
				debug("Inside if count>18");
				debug("<BR>n".$next_day." s".$start_time."---n".$curr_time." e".$end_time."---".$value."<BR>");
				
				if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
					//$value = $value . getSubHora($start_time,$curr_time,$value,$night_hora_time);
					$day_hora_data[$count] = array($next_day, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$night_hora_time));
					$current_hora_flag = true;
					$current_hora = $day_hora_data[$count];
				}
				else{
					$day_hora_data[$count] = array($next_day, $start_time, $end_time, $value);
				}
			}
			else{
				debug("<BR>c".$curr_date." s".$start_time."---c".$curr_time." e".$end_time."---".$value."<BR>");
				
				if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
					//$value = $value . getSubHora($start_time,$curr_time,$value,$night_hora_time);
					$day_hora_data[$count] = array($curr_date, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$night_hora_time));
					$current_hora_flag = true;
					$current_hora = $day_hora_data[$count];
				}
				else{
					$day_hora_data[$count] = array($curr_date, $start_time, $end_time, $value);
				}
			}	
			
			$start_time = $end_time;
		}
		
		$count = $count + 1;
	}
	
	if($current_hora_flag==false){
		$day_hora_data[18][4] =  "current hora";
	}
	else{
		unset($day_hora_data[18][4]);
	}
}

function calculateHoraPrevDay($lat,$lon)
{
	global $curr_date, $curr_time, $sunrise, $sunset, $sunrise_tom, $next_day,$prev_day,$night_hora_data,$current_hora;
	
	$sun_hora = array("சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத");
	$mon_hora = array("சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு");
	$tue_hora = array("செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்");
	$wed_hora = array("புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி");
	$thu_hora = array("குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி");
	$fri_hora = array("சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்");
	$sat_hora = array("சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ","சூரி","சுக்","புத","சந்","சனி","குரு","செ");
	
	$palan_detail = array("dummy");
	
	$curr_date = date('Y-m-d', strtotime($curr_date.'-1 day'));
	
	$dayofweek = date('w', strtotime($curr_date)); 
	
	$prev_day=date("Y-m-d",strtotime($curr_date. ' - 1 days'));
	$next_day=date("Y-m-d",strtotime($curr_date. ' + 1 days'));
	
	$sunrise_tom = getSunrise(strtotime($next_day),floatval($lat), floatval($lon));
	$sunrise_tom = substr($sunrise_tom,0,5);
	debug($sunrise_tom);
	debug("<BR>");
	$sunset_prev = getSunset(strtotime($prev_day),floatval($lat), floatval($lon));
	$sunset_prev = substr($sunset_prev,0,5);
	debug($sunset_prev);
	debug("<BR>");
	
	$night_hora_time = MinutesToTime(timeToMinutes(subtractTime($sunset,$sunrise))/12); 
	
	$night_hora_time = MinutesToTime(timeToMinutes(addTime(subtractTime("23:59",$sunset),$sunrise_tom))/12);
	
	debug("***".$dayofweek."***");
	
	switch($dayofweek){
		case 0:
			$curr_day = $sun_hora;
			break;
		case 1:
			$curr_day = $mon_hora;
			break;
		case 2:
			$curr_day = $tue_hora;
			break;
		case 3:
			$curr_day = $wed_hora;
			break;
		case 4:
			$curr_day = $thu_hora;
			break;
		case 5:
			$curr_day = $fri_hora;
			break;
		case 6:
			$curr_day = $sat_hora;
			break;
	}
	
	$start_time = substr($sunrise,0,5);
	
	$count = 1;
	$temp =$sunrise;
	
	debug('[[[['.$curr_time.'[[[['.$sunrise.'[[[['.$sunset.'[[[[');
	
	if(strtotime($curr_time) > strtotime($sunrise) && strtotime($curr_time) < strtotime($sunset)){	
		$hora_period = $night_hora_time;
		debug("night_hora_time=".$night_hora_time);
	}
	else{
		$hora_period = $night_hora_time;
		debug("night_hora_time=".$night_hora_time);
	}
	
	foreach($curr_day as $value){
		
		if($count <= 12){
			$end_time = addTime($start_time,$night_hora_time);
			
			if($count==12) {
				$end_time = $sunset;
			}
			debug("<BR>".$curr_date." ".$start_time."---".$curr_date." ".$end_time."---".$value."<BR>");
			
			if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
				$night_hora_data[$count] = array($curr_date, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$night_hora_time));
				$current_hora = $night_hora_data[$count];
			}
			else{
				$night_hora_data[$count] = array($curr_date, $start_time, $end_time, $value);
			}
			
			$start_time = $end_time;
		}
		else{
			$end_time = addTime($start_time,$night_hora_time);
			
			// To adjust time past midnight
			if(strtotime($end_time) > strtotime("23:59")){
				$end_time = addTime("00:00",subtractTime($end_time,"23:59"));
			}
			
			if($count==24) {
				$end_time = $sunrise_tom;
			}
			
			if($count>18){
				debug("<BR>".$next_day." ".$start_time."---".$next_day." ".$end_time."---".$value."<BR>");
				
				if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
					$night_hora_data[$count] = array($next_day, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$night_hora_time));
					$current_hora = $night_hora_data[$count];
				}
				else{
					$night_hora_data[$count] = array($next_day, $start_time, $end_time, $value);
				}
			}
			else{
				debug("<BR>".$curr_date." ".$start_time."---".$curr_date." ".$end_time."---".$value."<BR>");
				
				if(strtotime($curr_time) >= strtotime($start_time) && strtotime($curr_time) < strtotime($end_time)){	
					$night_hora_data[$count] = array($curr_date, $start_time, $end_time, $value, "current hora",getSubHora($start_time,$curr_time,$value,$night_hora_time));
					$current_hora = $night_hora_data[$count];
				}
				else{
					$night_hora_data[$count] = array($curr_date, $start_time, $end_time, $value);
				}
			}
			
			$start_time = $end_time;
		}
		
		$count = $count + 1;
	}
}

function displayHoraTable($hora_data)
{
	global $current_hora;
	
	echo "<div class='divTable blueTable' style='height:40px;width:320px;margin-left:20px'>";
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=4>தற்போதைய ஓரை</div>";
		echo "</div>";
	echo "</div>";
	
	echo "<div class='divTable blueTable' style='height:40px;width:320px;margin-left:20px'>";
		echo "<div class='divTableRow'>";
				echo "<div class='divThaaraPalanHeading'>ஆரம்பம்</div>";
				echo "<div class='divThaaraPalanHeading'>முடிவு</div>";
				echo "<div class='divThaaraPalanHeading'>ஓரை</div>";
				echo "<div class='divThaaraPalanHeading'>உப ஓரை</div>";
		echo "</div>";
		echo "<div class='divTableRow'>";
			//Condition is to mark current hora in green color for good horas
			if(strpos($current_hora[3],"குரு")===0 or strpos($current_hora[3],"சுக்")===0 or strpos($current_hora[3],"புத")===0 or strpos($current_hora[3],"சந்")===0){  //== will not differenciate between 0 and false, so should use ===
				echo "<div class='divThaaraPalanGreen'>".date("h:i A",strtotime($current_hora[1]))."</div>";
				echo "<div class='divThaaraPalanGreen'>".date("h:i A",strtotime($current_hora[2]))."</div>";	
				echo "<div class='divThaaraPalanGreen'>".$current_hora[3]."</div>";
				if(strpos($current_hora[3],"குரு")===0 or strpos($current_hora[3],"சுக்")===0 or strpos($current_hora[3],"புத")===0 or strpos($current_hora[3],"சந்")===0){  //== will not differenciate between 0 and false, so should use ===
					echo "<div class='divThaaraPalanGreen'>".$current_hora[5]."</div>";
				}
				else{
					echo "<div class='divThaaraPalanRed'>".$current_hora[5]."</div>";
				}
			}
			else{ //Condition is to mark current hora in red color for bad horas
				
				echo "<div class='divThaaraPalanRed'>".date("h:i A",strtotime($current_hora[1]))."</div>";
				echo "<div class='divThaaraPalanRed'>".date("h:i A",strtotime($current_hora[2]))."</div>";	
				echo "<div class='divThaaraPalanRed'>".$current_hora[3]."</div>";
				if(strpos($current_hora[3],"குரு")===0 or strpos($current_hora[3],"சுக்")===0 or strpos($current_hora[3],"புத")===0 or strpos($current_hora[3],"சந்")===0){  //== will not differenciate between 0 and false, so should use ===
					echo "<div class='divThaaraPalanGreen'>".$current_hora[5]."</div>";
				}
				else{
					echo "<div class='divThaaraPalanRed'>".$current_hora[5]."</div>";
				}
			}
		echo "</div>";
	echo "</div>";
	
	echo "<BR><BR>";
	
	echo "<div class='divTable blueTable' style='height:40px;width:320px;margin-left:20px'>";
		echo "<div class='divTableRow'>";
			echo "<div class='divTableCell' colspan=4>ஓரை அட்டவணை</div>";
		echo "</div>";
	echo "</div>";
	
	echo "<div class='divTable blueTable' style='height:40px;width:320px;margin-left:20px'>";
	echo "<div class='divTableRow'>";
			echo "<div class='divThaaraPalanHeading'>Date</div>";
			echo "<div class='divThaaraPalanHeading'>Start</div>";
			echo "<div class='divThaaraPalanHeading'>End</div>";
			echo "<div class='divThaaraPalanHeading'>Planet</div>";
	echo "</div>";
	
	echo array_search("current hora",$hora_data);
	
	for($count = 1; $count <= 24; $count++)
	{
		$data = $hora_data[$count];
		
		echo "<div class='divTableRow'>";
			//if(isset($data[4])){
				//Condition is to mark current hora in green color for good horas
				if(strpos($data[3],"குரு")===0 or strpos($data[3],"சுக்")===0 or strpos($data[3],"புத")===0 or strpos($data[3],"சந்")===0){  //== will not differenciate between 0 and false, so should use ===
					echo "<div class='divThaaraPalanGreen'>".$data[0]."</div>";
					echo "<div class='divThaaraPalanGreen'>".date("h:i A",strtotime($data[1]))."</div>";
					echo "<div class='divThaaraPalanGreen'>".date("h:i A",strtotime($data[2]))."</div>";	
					//echo "<div class='divThaaraPalanGreen'>".$data[3]."<div class='divSubHora'>".$data[5]."</div></div>";
					echo "<div class='divThaaraPalanGreen'>".$data[3]."</div>";
				}
				else{ //Condition is to mark current hora in red color for bad horas
					echo "<div class='divThaaraPalanRed'>".$data[0]."</div>";
					echo "<div class='divThaaraPalanRed'>".date("h:i A",strtotime($data[1]))."</div>";
					echo "<div class='divThaaraPalanRed'>".date("h:i A",strtotime($data[2]))."</div>";	
					//echo "<div class='divThaaraPalanRed'>".$data[3]."<div class='divSubHora'>".$data[5]."</div></div>";
					echo "<div class='divThaaraPalanRed'>".$data[3]."</div>";
				}
			/*}
			else{
				echo "<div class='divThaaraPalanWhite'>".$data[0]."</div>";
				echo "<div class='divThaaraPalanWhite'>".date("h:i A",strtotime($data[1]))."</div>";
				echo "<div class='divThaaraPalanWhite'>".date("h:i A",strtotime($data[2]))."</div>";	
				echo "<div class='divThaaraPalanWhite'>".$data[3]."</div>";
			}*/
		echo "</div>";
	}
	
	echo "</div>";
}

$conn=dbConnect($conn);

//$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$city."'";
$sql = "SELECT Latitude, Longitude, GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place="."'".$city."'"." AND TZ="."'".$TZ."'";
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
	echo "0 results, check your input. Assumed Chennai location and timezone";
	$lat = 13.08;
	$lon = 80.27;
	$place='Chennai|Asia/Kolkata';
}

if($DEBUG==1){ 
	echo 'DATE =' . $lat ; echo '<br>';
	echo 'TIME =' . $lon; echo '<br>';
	echo 'OFFSET =' . $gmt_offset; echo '<br>';
	echo 'LAT =' . $lat; echo '<br>';
	echo 'LON =' . $lon; echo '<br>';
}

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'Hora';
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>PK Astro Thaara palan</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> 
    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 800
            });
        });
		
	$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
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
    //$(document).on("click", ".result p", function(){
		$(document).on("click", ".result div", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
    });
});

function init(){
	document.getElementById('place').value = '<?php echo $place; ?>';
}


function initialize() {
	let place = document.getElementById("place").value="";
}

function placeOnBlur() {
	let place = document.getElementById("place").value;
	
	if(place==""){
		document.getElementById('place').value = '<?php echo $city; ?>';
	}
}

function dropdownFormSubmit() {
	document.getElementById('sub_type').value = '1';
	document.forms["horaForm"].submit();
}

function formSubmitButton(){
	document.getElementById('sub_type').value = '2';
	document.forms["horaForm"].submit();
}

function onDateChange(){
	document.getElementById('sub_type').value = '2';
	document.forms["horaForm"].submit();
}

function onTimeChange(){
	document.getElementById('sub_type').value = '2';
	document.forms["horaForm"].submit();
}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

	<div class='column-menu'>
		<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
		</div>
		<div class='mobile'>
				<?php include "horaMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>			
				<form name="horaForm" style="width:320px" action="hora.php?debug=<?php echo $DEBUG; ?>" method="post">
				<CENTER><P style="color:#660000;font-weight:bold; font-size:16px; background-color:white;">ஓரை</P></CENTER>
					<table style="width:25%; text-align:left;">
					
						<tr>
							<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">*Place:</H5></td>
							<td style="width:150px">
								<div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="color:#660000;width:auto;font-size:15px;background:#FDE966" placeholder="Search place..." onFocus="initialize();" onBlur="setTimeout(dropdownFormSubmit, 500)" /><div class="result" ></div>
							</td>
						</tr>
						<tr>
						<td></td>
							<td style="width:100px">
								<input type='date' id='nDate' name='nDate' onChange="onDateChange();" style="color:#660000;font-size:15px;font-weight:bold;width:auto;background:#FDE966" value='<?php echo $curr_date ?>' >
								<input type='time' id='nTime' name='nTime' onChange="onTimeChange();" style="color:#660000;font-size:15px;font-weight:bold;width:auto;background:#FDE966" step='1' value='<?php echo $curr_time ?>' >
								<input type='hidden' id='sub_type' name='sub_type'>
							</td>
						</tr>
						<!-- <tr>
							<td></td>
							<td><input type='button' id='sub_button' value='Submit' onClick="formSubmitButton();" style="background-color:gold;color:#660000;font-weight:bold;font-size:12px;width:100px"></td>
							<td></td>
						</tr> -->
					</table>
				</form>
				<?php displayHorai($lat,$lon); ?>
				<BR>
				<!-- <img src="images/Horai Kaalam.jpg" alt="Horai/ஹோரை" width="100%" height="100%"> -->
	</div>	 
</body>

</html>
