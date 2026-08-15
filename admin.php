<?php  
include "lib.php";

//$prokerala_calender = getProkeralaTamilCalenderDetail('2024-11-13','tamil','ta');

//var_dump($prokerala_calender);
//echo $prokerala_calender['year_name'] . ',' . $prokerala_calender['month_name'] . ',' . $prokerala_calender['day'];

$g_prod_url_selected='';
$g_preprod_url_selected='';
$g_test_url_selected='';
$g_laptop_url_selected='';
$g_desktop_url_selected='';

$conn="";
$imei="";
$appInfo=array();
$userInfo=array();
$topActivityInfo=array();

$results_per_page = 15; // number of results per page
$page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
$start_from = ($page - 1) * $results_per_page;
$userTotalPages = 1;

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

if(!isset($_SESSION['userinfo']) or !strcmp($_SESSION['userinfo']['user_type'],'ADMIN')==0){
	echo "You are not athorized to access this page!!!";
	return;
}

function getAppInfo()
{
	global $conn,$appInfo,$g_prod_url_selected,$g_preprod_url_selected,$g_test_url_selected,$g_laptop_url_selected,$g_desktop_url_selected;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT app_name,app_version,app_url,app_download_url,admin_phone from app_info";
		
		//echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$appInfo["app_name"] = $row["app_name"];
					$appInfo["app_version"] = $row["app_version"];
					$appInfo["app_url"]= $row["app_url"];
					$appInfo["app_download_url"] = $row["app_download_url"];
					$appInfo["admin_phone"] = $row["admin_phone"];
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	
	if(strpos($appInfo['app_url'],"https://pkastro.com")===0 or strpos($appInfo['app_url'],"https://pkastro.com")>0){
		$g_prod_url_selected = 'selected';
	}
	elseif(strpos($appInfo['app_url'],"preprod.pkastro.com")===0 or strpos($appInfo['app_url'],"preprod.pkastro.com")>0){
		$g_preprod_url_selected = 'selected';
	}
	elseif(strpos($appInfo['app_url'],"test.pkastro.com")===0 or strpos($appInfo['app_url'],"test.pkastro.com")>0){
		$g_test_url_selected = 'selected';
	}
	elseif(strpos($appInfo['app_url'],"192.168.29.78")===0 or strpos($appInfo['app_url'],"192.168.29.78")>0){
		$g_laptop_url_selected = 'selected';
	}
	elseif(strpos($appInfo['app_url'],"192.168.29.14")===0 or strpos($appInfo['app_url'],"192.168.29.14")>0){	
		$g_desktop_url_selected = 'selected';
	}
	
	dbDisconnect($conn);
}

function getUserDetails()
{
	global $conn,$userInfo, $page, $results_per_page, $start_from, $userTotalPages;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		$countSql = "SELECT COUNT(rowid) AS total FROM user";
		$totalRows = 0;
		if (($countResult = mysqli_query($conn, $countSql))) {
			$countRow = $countResult->fetch_assoc();
			$totalRows = (int)$countRow["total"];
		}

		$userTotalPages = max(1, (int)ceil($totalRows / $results_per_page));
		if ($page > $userTotalPages) {
			$page = $userTotalPages;
			$start_from = ($page - 1) * $results_per_page;
		}

		$limitStart = (int)$start_from;
		$limitCount = (int)$results_per_page;
		$sql = "SELECT rowid,name,phone,imei,last_login,user_type,registered_date FROM user ORDER BY registered_date DESC LIMIT $limitStart, $limitCount";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$userInfo["rowid"][$cnt] = $row["rowid"];
					$userInfo["Name"][$cnt] = $row["name"];
					$userInfo["phone"][$cnt] = $row["phone"];
					$userInfo["imei"][$cnt]= $row["imei"];
					$userInfo["last_login"][$cnt] = $row["last_login"];
					$userInfo["user_type"][$cnt] = $row["user_type"];
					$userInfo["registered_date"][$cnt] = $row["registered_date"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function getTopActivityDetails()
{
	global $conn,$activityInfo;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT activity_time,user.name as name,activity FROM activity_log, user where activity_log.phone = user.phone ORDER BY activity_time DESC LIMIT 50";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$activityInfo["activity_time"][$cnt] = $row["activity_time"];
					$activityInfo["name"][$cnt] = $row["name"];
					$activityInfo["activity"][$cnt]= $row["activity"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function getTopActivitySummary()
{
	global $conn,$topActivityInfo;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT activity_name,activity_count FROM most_performed_activity";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$topActivityInfo["activity_name"][$cnt] = $row["activity_name"];
					$topActivityInfo["activity_count"][$cnt] = $row["activity_count"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function get_nearest_timezone($cur_lat, $cur_long, $country_code = '') {
    $timezone_ids = ($country_code) ? DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code)
                                    : DateTimeZone::listIdentifiers();

    if($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {

        $time_zone = '';
        $tz_distance = 0;

        //only one identifier?
        if (count($timezone_ids) == 1) {
            $time_zone = $timezone_ids[0];
        } else {

            foreach($timezone_ids as $timezone_id) {
                $timezone = new DateTimeZone($timezone_id);
                $location = $timezone->getLocation();
                $tz_lat   = $location['latitude'];
                $tz_long  = $location['longitude'];

                $theta    = $cur_long - $tz_long;
                $distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat))) 
                + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
                $distance = acos($distance);
                $distance = abs(rad2deg($distance));
                // echo '<br />'.$timezone_id.' '.$distance; 

                if (!$time_zone || $tz_distance > $distance) {
                    $time_zone   = $timezone_id;
                    $tz_distance = $distance;
                } 

            }
        }
        return  $time_zone;
    }
    return 'unknown';
}

function getUserTotal(){
	global $userTotalPages;
	return $userTotalPages;
}

//timezone for one NY co-ordinate

debug("<BR>TEST - " . get_nearest_timezone(-38.451931,145.238983));
// more faster and accurate if you can pass the country code 
//echo get_nearest_timezone(40.772222, -74.164581, 'US') ;
debug("<BR>TEST - " . get_nearest_timezone(19.076090, 72.854118,'IN'));

getAppInfo();
getUserDetails();
getTopActivityDetails();
getTopActivitySummary();
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro admin page</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
	<script src="tz.js"></script>
	<script src="calendar_ta.js"></script>
    <script type="text/javascript">
	
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
	
        if(inputVal.length>2){
            $.get("backend-search_admin.php", {term: inputVal}).done(function(data){
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
	document.getElementById('imei').value = '<?php echo $imei; ?>';
	getGeoLocation();
}

function getGeoLocation(){
	// Check if the browser supports the Geolocation API.
	if (navigator.geolocation) {
	  // Get the user's current location.
	  navigator.geolocation.getCurrentPosition(function(position) {
		// The success callback function.
		// This function is called if the browser is able to successfully determine the user's location.

		// Get the user's latitude and longitude coordinates.
		var latitude = position.coords.latitude;
		var longitude = position.coords.longitude;
		
		//latitude = 15.065746974104158;
		//longitude = 101.12441707669265;
		
		url='https://maps.google.com/maps?q='+latitude+','+longitude+'&output=svembed'+'&z=18';
		var html = '<iframe src='+url+'></iframe>';
		
		const tz = tzlookup(latitude,longitude); //include tz.js
		
		//alert("Lat="+latitude+" Long="+longitude+" TZ="+tz);
		
		$("#map").append(html); //embed map
		
		//window.open(url,'_blank');

		// Display the user's location on a map.
		// ...
	  }, function(error) {
		// The error callback function.
		// This function is called if the browser is unable to determine the user's location.

		// Handle the error.
		// ...
		alert(error.message);
	  });
	} else {
	  // The browser does not support the Geolocation API.
	  // Handle the error.
	  // ...
	  alert('Browser does not support Geolocation API');
	}
}

function placeOnBlur() {
	let imei = document.getElementById("imei").value;
	
	if(imei==""){
		document.getElementById('imei').value = '<?php echo $imei; ?>';
	}
}

function actAs(num){
	var url = 'actAs.php?rowid='+num;
	//alert(url);
	window.open(url,'_self');
}

function openUserInfo(rowid){
	window.location = "userInfo.php?rowid="+rowid;
}

function getTamilDate(){
	var temp = TC.getTamilYearName(2020,4,20);
	alert(temp);
}
		
    </script>
</head>

<body onLoad="init();">

<div class="title"></div>
<div class="">
	<div class="column-menu">
	<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			<button class='menu' onClick="window.open('addPlace.php','_self')">Add place</button>
		</div>
		<div class='mobile'>
			<?php include "adminMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		<p class="arrow-container">
		  <span class="arrow">&#x2190;</span>
		  <span>குரு</span>
		</p>

			<center><div class="divTable blueTable" id="chart">

			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">App Info update</P>
				<form name="adminAppInfo" action="AppInfoUpdate.php" method="post">
<?php

	if(isMobileDevice()){
		echo "Request from Mobile/tablet";
	}
	else {
		echo "Request from Desktop/Laptop";
	}

					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading'>App Name</div>";
								echo "<div class='divPanchangHeading'><input type='textbox' id='frmAppName' name='frmAppName' value='".$appInfo['app_name']."'></div>";
						echo "</div>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading'>App Version</div>";
								echo "<div class='divPanchang'><input type='textbox' id='frmAppVer' name='frmAppVer' value='".$appInfo['app_version']."'></div>";
								echo "</div>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading'>App URL</div>";
									echo '<select name="frmAppUrl" id="frmAppUrl" style="width:auto;font-size:20px;">';
										echo '<option value="https://pkastro.com/" '.$g_prod_url_selected.'>https://pkastro.com/</option>';
										echo '<option value="http://preprod.pkastro.com/" '.$g_preprod_url_selected.'>http://preprod.pkastro.com/</option>';
										echo '<option value="http://test.pkastro.com/" '.$g_test_url_selected.'>http://test.pkastro.com/</option>';
										echo '<option value="http://192.168.29.78/pkastro_test/" '.$g_laptop_url_selected.'>http://192.168.29.78/pkastro_test/</option>';
										echo '<option value="http://192.168.29.14/pkastro_test/" '.$g_desktop_url_selected.'>http://192.168.29.14/pkastro_test/</option>';
									echo '</select>';
								//echo "<div class='divPanchang'><textarea rows='1' cols='100' id='frmAppUrl' name='frmAppUrl' style='font-size:10pt'>".$appInfo['app_url']."</textarea></div>";
								echo "</div>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading'>Download URL</div>";
								echo "<div class='divPanchang'><textarea rows='3' cols='100' id='frmAppDownloadUrl' name='frmAppDownloadUrl' style='font-size:10pt'>".$appInfo['app_download_url']."</textarea></div>";
								echo "</div>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading'>Admin Phone</div>";
								echo "<div class='divPanchang'><input type='textbox' id='frmAdminPhone' name='frmAdminPhone' value='".$appInfo['admin_phone']."'></div>";
						echo "</div>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchang'></div>";
								echo "<div class='divPanchang'><input type='Submit' id='btSubmit' value='Submit' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;'></div>";
						echo "</div>";

					echo "</div>";
					
					
						
?>
				</form>
				<BR>
					<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">User detail</P>			
<?php
					echo "<form name='userInfo' action='userInfo.php' method='post'>";
					echo "<P>Search by Name/Phone</P>";
					echo "<div class='search-box'><input type='text' name='name' id='name' autocomplete='off' style='background:#ffffff' placeholder='Search name/phone...' onfocus=this.value='' onBlur='placeOnBlur();' x-webkit-speech /><div class='result'></div>";
					echo "<input type='Submit' id='btSubmit' value='Submit' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;'>";
					echo "</form>";
				
					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
							echo "<div class='divPanchangHeading'>Rowid</div>";
							echo "<div class='divPanchangHeading'>Name</div>";
							echo "<div class='divPanchangHeading'>phone</div>";
							echo "<div class='divPanchangHeading'>imei</div>";
							echo "<div class='divPanchangHeading'>last_login</div>";
							echo "<div class='divPanchangHeading'>user_type</div>";
							echo "<div class='divPanchangHeading'>reg_date</div>";
						echo "</div>";
						
						$userRowCount = isset($userInfo['Name']) ? count($userInfo['Name']) : 0;
						for($i=0; $i<$userRowCount; $i++){
							
								$rowid = $userInfo['rowid'][$i];
								
								echo "<div class='divTableRow'>";
									echo "<div class='divPanchang' style='color:blue;font-size:10px;font-weight:bold;width:40px;cursor:pointer' onClick='openUserInfo($rowid);'>".$rowid."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['Name'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['phone'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['imei'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['last_login'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['user_type'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['registered_date'][$i]."</div>";
									echo "<div class='divPanchang' style='color:blue;font-size:10px;font-weight:bold;width:40px;cursor:pointer' onClick='actAs($rowid)'>Act As</div>";
								echo "</div>";
								
						}


					echo "</div>";
					
					renderAdminUserPagination($page, getUserTotal(), 'admin.php');
					
					echo '<BR><BR><BR>';
					
					echo "<P style='color:#660000;font-weight:bold; font-size:20px; width:300px;text-align:center'>Activity log</P>";
					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading' style='width:30%'>Activity Time</div>";
								echo "<div class='divPanchangHeading' style='width:5%'>Name</div>";
								echo "<div class='divPanchangHeading' style='width:85%'>Activity</div>";
						echo "</div>";
						
						for($i=0; $i<sizeof($activityInfo['name']); $i++){
							
								echo "<div class='divTableRow'>";
								
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$activityInfo['activity_time'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$activityInfo['name'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$activityInfo['activity'][$i]."</div>";
								
								echo "</div>";
						}


					echo "</div>";
					echo "<BR><BR>";
				
					echo '<P style="color:#660000;font-weight:bold; font-size:20px; width:300px;text-align:center">Activity Summary</P>';
					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
								echo "<div class='divPanchangHeading' style='width:30%'>Activity</div>";
								echo "<div class='divPanchangHeading' style='width:5%'>Count</div>";
						echo "</div>";
						
						for($i=0; $i<sizeof($topActivityInfo['activity_name']); $i++){
							
								echo "<div class='divTableRow'>";
								
									echo "<div class='divPanchang'>".$topActivityInfo['activity_name'][$i]."</div>";
									echo "<div class='divPanchang'>".$topActivityInfo['activity_count'][$i]."</div>";
								
								echo "</div>";
						}


					echo "</div>";
					echo "<BR><BR>";
?>
				
				<BR>
			</div></center>
	  </div>
	</div>
</div>

<center><div id='map' width="400" height="400"></div></center>
<button class='actionBtn' onClick='getTamilDate();'>Tamil Date</button>

</body>

</html>