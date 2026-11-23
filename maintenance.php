<?php  
include "lib.php";

$conn="";
$imei="";
$appInfo=array();
$userInfo=array();
$results_per_page = 15; // number of results per page
$page = isset($_GET["page"]) ? max(1, (int)$_GET["page"]) : 1;
$start_from = ($page - 1) * $results_per_page;
$userTotalPages = 1;

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

if(!isset($_SESSION['userinfo']) or (!strcmp($_SESSION['userinfo']['user_type'],'SUPER')==0 and !strcmp($_SESSION['userinfo']['user_type'],'ADMIN')==0)){
	echo "You are not athorized to access this page!!!";
	return;
}

$maintenanceSection = isset($_GET['section']) && $_GET['section'] === 'theme' ? 'theme' : 'users';

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

if ($maintenanceSection !== 'theme') {
	//timezone for one NY co-ordinate
	debug("<BR>TEST - " . get_nearest_timezone(-38.451931,145.238983));
	// more faster and accurate if you can pass the country code
	//echo get_nearest_timezone(40.772222, -74.164581, 'US') ;
	debug("<BR>TEST - " . get_nearest_timezone(19.076090, 72.854118,'IN'));
}

if ($maintenanceSection === 'theme') {
	require_once __DIR__ . '/client_theme_admin_controller.php';
	$themeState = clientThemeAdminState('maintenance.php?section=theme');
	$themePresets = $themeState['presets'];
	$themeConfig = $themeState['config'];
	$themeErrorMessage = $themeState['error'];
	$themeSaved = $themeState['saved'];
	$themeCsrf = $themeState['csrf'];
	$themeFormAction = $themeState['form_action'];
	$themeBackUrl = $themeState['back_url'];
	$themeCustomThemes = $themeState['custom_themes'];
	$themeCustomSaved = $themeState['custom_saved'];
	$themeDraft = $themeState['draft'];
	$themeCustomName = $themeState['custom_name'];
	$themeActiveCustomTheme = $themeState['active_custom_theme'];
	$themeActiveConfig = $themeState['active_config'];
	$themeActiveLabel = $themeState['active_theme_label'];
	$themeActiveSource = $themeState['active_theme_source'];
	$themeActiveLoaded = $themeState['active_theme_loaded'];
} else {
	getUserDetails();
}


?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title><?php echo $maintenanceSection === 'theme' ? 'Client theme settings' : 'PK Astro admin page'; ?></title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/client_theme_css.php">
	<link rel="stylesheet" href="css/admin-modern.css?<?php echo date('ymdhis'); ?>">
	<?php if ($maintenanceSection === 'theme') { ?><link rel="stylesheet" href="css/client-theme-admin.css?<?php echo date('ymdhis'); ?>"><?php } ?>
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
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
	
}

function placeOnBlur() {
	
}

function actAs(num){
	var url = 'actAs.php?rowid='+num;
	//alert(url);
	window.open(url,'_self');
}

function openUserInfo(rowid){
	window.location = "userInfo.php?rowid="+rowid;
}
		
    </script>
</head>

<body class="admin-ui admin-ui-maintenance" onLoad="init();">

<div class="title"></div>
<?php displayUserInfo($_SESSION['userinfo']); ?>
<div class="admin-shell">
	<div class="column-menu">
		<div class="admin-brand">
			<span class="admin-brand-mark">DS</span>
			<span class="admin-brand-copy"><strong>DSJV</strong><small>Maintenance console</small></span>
		</div>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			<button class='menu' onClick="window.open('addPlace.php','_self')">Add place</button>
			<button class='menu' onClick="window.open('maintenance.php?section=theme','_self')">Theme settings</button>
		</div>
		<div class='mobile'>
			<?php include "maintainMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		<?php if ($maintenanceSection === 'theme') { ?>
			<?php include __DIR__ . '/client_theme_admin_panel.php'; ?>
		<?php } else { ?>
			<header class="admin-page-heading">
				<div><span class="admin-page-eyebrow">MAINTENANCE</span><h1>User management</h1><p>Review accounts, search users and manage registration status.</p></div>
				<span class="admin-page-badge">Management workspace</span>
			</header>
			<center><div class="divTable blueTable" id="chart">
					<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">User detail</P>
			
<?php
			echo "<form name='userInfo' action='userInfo.php' method='post'>";
			echo "<H3>Type Name to search</H3>";
			echo "<div class='search-box'><input type='text' name='name' id='name' autocomplete='off' style='background:#ffffff' placeholder='Search name...' onfocus=this.value='' onBlur='placeOnBlur();' /><div class='result'></div>";
			echo "<input type='Submit' id='btSubmit' value='Submit' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;'>";
			echo "</form>";
			
			
			echo "<BR><BR>";
			////////////////// PRINT USER DETAILS ////////////////////////////////////////////////
					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
							echo "<div class='divPanchangHeading'>Rowid</div>";
							echo "<div class='divPanchangHeading'>Name</div>";
							echo "<div class='divPanchangHeading'>phone</div>";
							echo "<div class='divPanchangHeading'>imei</div>";
							//echo "<div class='divPanchangHeading'>last_login</div>";
							echo "<div class='divPanchangHeading'>user_type</div>";
							echo "<div class='divPanchangHeading'>reg_date</div>";
						echo "</div>";
						
						$userRowCount = isset($userInfo['Name']) ? count($userInfo['Name']) : 0;
						for($i=0; $i<$userRowCount; $i++){
							
								$rowid = $userInfo['rowid'][$i];
								
								echo "<div class='divTableRow'>";
									if(strcmp($_SESSION['userinfo']['user_type'],'ADMIN')==0){
										echo "<div class='divPanchang' style='color:blue;font-size:10px;font-weight:bold;width:40px;cursor:pointer' onClick='openUserInfo($rowid);'>".$rowid."</div>";
									}
									else{
										if(strcmp($userInfo['user_type'][$i],'ADMIN')==0){
											echo "<div class='divPanchang' style='color:gray;font-size:10px;font-weight:bold;width:40px;'>".$rowid."</div>";
										}
										else{
											echo "<div class='divPanchang' style='color:blue;font-size:10px;font-weight:bold;width:40px;cursor:pointer' onClick='openUserInfo($rowid);'>".$rowid."</div>";
										}
										
									}
									
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['Name'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['phone'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['imei'][$i]."</div>";
									//echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['last_login'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['user_type'][$i]."</div>";
									echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$userInfo['registered_date'][$i]."</div>";
									if(strcmp($_SESSION['userinfo']['user_type'],'ADMIN')==0){
										echo "<div class='divPanchang' style='color:blue;font-size:10px;font-weight:bold;width:40px;cursor:pointer' onClick='actAs($rowid)'>Act As</div>";
									}
									
								echo "</div>";
								
						}


					echo "</div>";
					echo "<BR>";
					renderAdminUserPagination($page, getUserTotal(), 'maintenance.php');
					echo "<BR><BR>";
					
?>
			<P><A href='current_expiry_report.html' style='color:blue;'>Upcoming expiry report</A></P>
			<P><A href='previous_expiry_report.html' style='color:blue;'>Expired report</A></P>
				<BR><BR><BR><BR><BR><BR>
			</div></center>
		<?php } ?>
	  </div>
	</div>
</div>

</body>

</html>