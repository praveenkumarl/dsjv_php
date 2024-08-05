<?php 
include_once "lib.php";

$key = isset($_GET['key']) ? $_GET['key'] : "-1";
$_SESSION['menu_clicked'] = true;

$userinfo = isset($_SESSION['userinfo']) ? $_SESSION['userinfo'] : "";

$user_type = $userinfo['user_type'];

//only admin and super user can open registration page
if(! (strcmp($user_type,'ADMIN')==0 or strcmp($user_type,'SUPER')==0)){
	displayRegistrationMsg();
	return;
}

$paid = PAID;
$birth = BIRTH;
$match = MATCHING;
$birthmatch = BIRTHMATCH;
$panchang = PANCHANG;
$jamakol = JAMAKOL;
$prasannam = PRASANNAM;

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
      <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>DSJV app</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
		
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
			$(document).on("click", ".result div", function(){
				$(this).parents(".search-box").find('input[type="text"]').val($(this).text());
				$(this).parent(".result").empty();
			});
		});
		

function init(){
	var n = document.getElementById("name");
	
	if(n){
		document.getElementById("name").focus();
	}
}

function validateForm() {

  let n = document.getElementById("name").value;
  let k = document.getElementById("key").value;
  let p = document.getElementById("phone").value;
  
  if (n == "") {
    document.getElementById("error").innerHTML = "Name must be filled out";
	document.getElementById('name').style.borderColor = "red";
    return false;
  }
  
  if (k == "") {
    document.getElementById("error").innerHTML = "Key must be filled out";
	document.getElementById('key').style.borderColor = "red";
    return false;
  }
  
  if (p == 0) {
    document.getElementById("error").innerHTML = "Phone# must be filled out";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  
  if(p<1000000000)
  {
	document.getElementById("error").innerHTML = "Invalid phone#";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  else if(p.includes('+')==true)
  {
	document.getElementById("error").innerHTML = "Do not enter +";
	document.getElementById('phone').style.borderColor = "red";
    return false;
  }
  else if(document.getElementById("loginpass").value.length ===0)
  {  
	document.getElementById("error").innerHTML = "Password empty";
	document.getElementById('loginpass').style.borderColor = "red";
    return false;
  }

}

function onlyNumberKey(evt) {
          
        // Only ASCII character in that range allowed
        var ASCIICode = (evt.which) ? evt.which : evt.keyCode
        if (ASCIICode > 31 && (ASCIICode < 48 || ASCIICode > 57))
            return false;
        return true;
    }

function openMaintainance(){
	window.open('maintenance.php','_self');
}
		
    </script>
</head>

<body onLoad="init();">

<div class="title"></div>

<div class='column-menu'>
	<?php displayUserInfo($_SESSION['userinfo']);?>
					<div class='desktop'>	
						<?php //include "maintainMenu_mobile.php"; ?>
					</div>
					<div class='mobile'>
						<?php include "maintainMenu_mobile.php"; ?>
					</div>
</div>
<div class='column-body'>
	<BR><BR>

<?php
if(!isset($_POST['insert_submitted'])){
	echo <<<EOL
	<center><div class="divTable blueTable" id="chart" style="width:360px;">
		<form name="register" action="new_registration_mobile_admin.php" method="post">
			<input type="hidden" name="insert_submitted" value="1" />
			<input type="hidden" name="db_operation" value="i" />
			<table style="width:90%; text-align: left">
				<tr>
					<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">User Registration</P></td>
				</tr>
						
				<tr style="background-color: #FFFFFF">
					<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*Name:</H5></td>
					<td><input type="text" name="name" id="name" autocomplete="off" style="background:#ffffff" placeholder="Enter name..." />
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*phone:</H5></td>
					<td><input type="tel" name="phone" id="phone" autocomplete="off" style="background:#ffffff" placeholder="Phone number..." onkeypress="return onlyNumberKey(event)" /></td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*Key:</H5></td>
					<td><input type="text" name="key" id="key" autocomplete="off" style="background:#ffffff" placeholder="Enter key..." />
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px" required>*Usertype:</H5></td>
					<td>
EOL;
						echo "<select name='usertype' id='usertype' style='background-color:white;border:solid 1px black;color:black;font-weight:bold;border-radius:0'>";
						foreach($usertype_arr as $utype){
								if(strcmp($utype,SUPER)==0 or strcmp($utype,ADMIN)==0){
									continue;
								}
								
								if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],$utype)==0){
									echo "<option value='$utype' selected>$utype</option>";
								}
								else{
									echo "<option value='$utype'>$utype</option>";
								}
							}
							
						echo "</select>";

	echo<<<EOL
					</td>
				</tr>
				
				<tr style="background-color: #FFFFFF">
					<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">*Place :</H5></td>
					<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff" placeholder="Search place..." onfocus=this.value='' onblur="placeOnBlur();" /><div class="result"></div>
					</td>
				</tr>
				<tr style="background-color: #FFFFFF">
					<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
				</tr>
				<tr  style="background-color: #FFFFFF">
					<td colspan="2" align="center">
					<input type="submit" value="Submit" class="actionBtn" onclick="return validateForm();">
					</td>
				</tr>
			</table>
		</form>
		<table style="width:90%; height:15px; text-align: left;margin-top:-20px">
			<tr>
				<td colspan="2" align="center">
					<button class="actionBtn" onclick="openMaintainance();">Back<button>
				</td>
			</tr>
		</table>
	</div></center>	
	
EOL;
}
elseif(isset($_POST['insert_submitted'])){
	unset($_POST['insert_submitted']);
	register_mobile();
	
	echo '<script>openMaintainance();</script>';
}
	
function register_mobile()
{
	$name = isset($_POST['name']) ? $_POST['name'] : 'Unknown';
	$phone = isset($_POST['phone']) ? $_POST['phone'] : '0';
	$place_tz = isset($_POST['place']) ? $_POST['place'] : '';
	
	$place=strtok($place_tz,"|");
	$tz=strtok("|");
	
	$key = isset($_POST['key']) ? $_POST['key'] : '-1';
	$gmt_offset = isset($_POST['gmt_offset']) ? $_POST['gmt_offset'] : '';
	$gmt_offset_dsl = isset($_POST['gmt_offset_dsl']) ? $_POST['gmt_offset_dsl'] : '';
	$db_operation = isset($_POST['db_operation']) ? $_POST['db_operation'] : '';
	$loginpass="mobile user";
	$usertype=isset($_POST['usertype']) ? $_POST['usertype'] : 'FREE';

	$validity_days=365;
	
	$conn="";
	$error="";
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);
	
	//$phone = $_POST['phone'];
	//$pin = $_POST['pin']; 

	try {
		$sql = "INSERT INTO user (name, phone, pass, imei, place, tz, GMT_offset, GMT_offset_dsl, last_login, user_type,registered_date) VALUES ('$name','$phone','$loginpass','$key','$place', '$tz', '$gmt_offset', '$gmt_offset_dsl', NULL,'$usertype',CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30'))";

		//$sql = "INSERT INTO user VALUES ('a','9999999','9999999',0,null)";
	
		if(($result = mysqli_query($conn, $sql))){
			echo "<script>alert('Successfully registered');</script>";
				
		} else{
			echo "<script>alert('Error: Server error');</script>";
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
			echo "<script>alert('DUPLICATE: User already exits')</script>";
		}
		
		mysqli_rollback($conn);

		throw $exception;
		
		echo "<script>alert('EXCEPTION: Server error');</script>";
	}
	
	dbDisconnect($conn);
}

?>
</div>

</body>

</html>