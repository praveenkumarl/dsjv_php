<?php  
include "lib.php";

$conn="";
$sql="";
$tz_arr=array();
$country_arr=array();

if(isset($_POST['name'])){
	$user_name=isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
	$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where name='$user_name'";
}
elseif(isset($_GET['rowid'])){
	$rowid=isset($_POST['rowid']) ? $_POST['rowid'] : $_GET['rowid'];
	$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where rowid='$rowid'";
}

$usertype=isset($_SESSION['usertype']) ? $_SESSION['usertype'] : "";

$userInfo=array();

function getTimezones()
{
	global $conn, $rowid,$sql,$tz_arr;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT tz,GMT_offset,GMT_offset_dsl from tz";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);
				
				 while($row = $result->fetch_assoc()) {
					array_push($tz_arr,array($row["tz"],$row["GMT_offset"],$row["GMT_offset_dsl"]));
				  }
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function getCountries()
{
	global $conn, $rowid,$sql,$country_arr;
	
	array_push($country_arr,"India");
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT distinct country from world_cities";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);
				
				 while($row = $result->fetch_assoc()) {
					array_push($country_arr,$row["country"]);
				  }
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function updatePlace(){
	global $conn,$rowid,$sql,$tz_arr;
	
	$place = $_POST['Place'];
	$country = $_POST['country'];
	$lat = $_POST['lat'];
	$long = $_POST['long'];
	$TZ = $_POST['TZ'];
	$GMT_offset = $_POST['GMT_offset'];
	$GMT_offset_dsl = $_POST['GMT_offset_dsl'];
	
	
	$conn = dbConnect($conn);

	try {
		$sql = "INSERT INTO world_cities (Place,other_names,country,Latitude,Longitude,TZ,GMT_offset,GMT_offset_dsl) values ('$place','$place','$country',$lat,$long,'$TZ','$GMT_offset','$GMT_offset_dsl')";
		
		if($result = mysqli_query($conn, $sql)){
			echo "<script>alert('Place added successfully!!!')</script>";
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	//Log user acitivity
	if(isset($_COOKIE["phone"])) {
		$activity = 'Add Place|'.$place."|".$country ."|" . $lat . "|". $long ."|". $TZ . "|". $GMT_offset."|".$GMT_offset_dsl;
		logActivity($conn,$_COOKIE["phone"],$activity);
	}
	
	dbDisconnect($conn);
	
	header( "Refresh:0; url=admin.php", true, 303);
}

getTimezones();
getCountries();

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
	
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    
   

    <script src="script.js"></script>
    <script type="text/javascript">

function init(){

}

function updatePostValues(){	
	const timezone = document.getElementById('TZ_list').value;
	
	document.getElementById('TZ').value = timezone.split("|")[0];
	document.getElementById('country').value = document.getElementById('country_list').value;
	
	document.getElementById('GMT_offset').value = timezone.split("|")[1];
	document.getElementById('GMT_offset_dsl').value = timezone.split("|")[2];
	document.getElementById("addPlace").submit();
}
		
    </script>
</head>

<body onLoad="init();">

	

<div class="title"></div>
	<div class="column-menu">
		<?php displayUserInfo($_SESSION['userinfo']); ?>
			<div class='desktop'>
				<button class="menu" onClick="window.open('admin.php','_self')">Admin Home</button>
			</div>
			<div class='mobile'>
				<?php include "addplaceMenu_mobile.php";  ?>
			</div>
	</div>
	<div class='column-body'>
	
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
		<center>
		<div class="divTable blueTable" id="chart" style="width:400px;margin-top:-30px">
		<BR><BR><BR>
				<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">Add place</P>
			
<?php
		if(!isset($_POST['Place'])){
			echo "<div class='divTable blueTable' style='height:40px;width:360px;margin-left:10px'>";
			echo "<form name='addPlace' id='addPlace' action='addPlace.php' method='post'>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Place*</div>";
				echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='text' name='Place' autofocus style='width:220px'></div>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Country*</div>";
				echo "<select name='country_list' id='country_list' style='background-color:white;border:solid 1px black;color:black;font-weight:bold;border-radius:0'>";
					foreach ($country_arr as $i => $val) {
						if(isset($val) && strcmp($val,'India')==0){
							echo "<option value='$val' selected>$val</option>";
						}
						else{
							echo "<option value='$val'>$val</option>";
						}
					}
					
				echo "</select>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Latitude*</div>";
				echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='number' name='lat'></div>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>Longitude*</div>";
				echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='number' name='long'></div>";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchangHeading'>TimeZone</div>";
				echo "<select name='TZ_list' id='TZ_list' style='background-color:white;border:solid 1px black;color:black;font-weight:bold;border-radius:0'>";
					foreach ($tz_arr as $i => $val) {
						if(isset($val) && strcmp($val[0],'Asia/Kolkata')==0){
							echo "<option value='$val[0]|$val[1]|$val[2]' selected>$val[0]</option>";
						}
						else{
							echo "<option value='$val[0]|$val[1]|$val[2]'>$val[0]</option>";
						}
					}
					
				echo "</select>";
				echo "<input type='hidden' name='TZ' id='TZ' value='' />";
				echo "<input type='hidden' name='country' id='country' value='' />";
				echo "<input type='hidden' name='GMT_offset' id='GMT_offset' value='' />";
				echo "<input type='hidden' name='GMT_offset_dsl' id='GMT_offset_dsl' value='' />";
			echo "</div>";
			echo "<div class='divTableRow'>";
				echo "<div class='divPanchang'></div>";
				echo "<div class='divPanchang'><input type='button' id='btSubmit' value='Submit' onClick='updatePostValues();' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;'></div>";
				
			echo "</div>";
						
			echo "</form>";
		}
		else{
			updatePlace();
		}
		
?>
			<BR><BR><BR><BR><BR>
		</div></center>
  </div>

</body>

</html>