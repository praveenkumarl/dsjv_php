<?php  
include "lib.php";

$conn="";
$imei="";
$appInfo=array();
$userInfo=array();
$results_per_page = 15; // number of results per page
if (isset($_GET["page"])) { $page  = $_GET["page"]; } else { $page=1; };
$start_from = ($page-1) * $results_per_page;

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

if(!isset($_SESSION['userinfo']) or (!strcmp($_SESSION['userinfo']['user_type'],'SUPER')==0 and !strcmp($_SESSION['userinfo']['user_type'],'ADMIN')==0)){
	echo "You are not athorized to access this page!!!";
	return;
}

function getUserDetails()
{
	global $conn,$userInfo, $page, $results_per_page, $start_from;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT rowid,name,phone,imei,last_login,user_type,registered_date FROM user ORDER BY registered_date DESC LIMIT $start_from, $results_per_page";
	
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
	global $conn,$results_per_page;
	
	$conn = dbConnect($conn);
	
	$sql = "SELECT COUNT(rowid) AS total FROM user";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$total_pages = ceil($row["total"] / $results_per_page); // calculate total pages with results
	
	return $total_pages;
}

//timezone for one NY co-ordinate

debug("<BR>TEST - " . get_nearest_timezone(-38.451931,145.238983));
// more faster and accurate if you can pass the country code 
//echo get_nearest_timezone(40.772222, -74.164581, 'US') ;
debug("<BR>TEST - " . get_nearest_timezone(19.076090, 72.854118,'IN'));

getUserDetails();


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
			<?php include "maintainMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
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
						
						for($i=0; $i<sizeof($userInfo['Name']); $i++){
							
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
					//Print pages for user detail	
					$total_pages = getUserTotal();
					  
					for ($i=1; $i<=$total_pages; $i++) {  // print links for all pages
						if ($i==$page)
							echo "<a style='display:box;border: 1px solid; background-color:black;color:white;width:auto;font-size:20px;padding:3px' href='maintenance.php?page=".$i."'>".$i."</a> ";
						else
							echo "<a style='display:box;border: 1px solid; background-color:gold;color:white;width:auto;font-size:20px;padding:3px' href='maintenance.php?page=".$i."'>".$i."</a> ";
					};
					echo "<BR><BR>";
					
?>
			<P><A href='current_expiry_report.html' style='color:blue;'>Upcoming expiry report</A></P>
			<P><A href='previous_expiry_report.html' style='color:blue;'>Expired report</A></P>
				<BR><BR><BR><BR><BR><BR>
			</div></center>
	  </div>
	</div>
</div>

</body>

</html>