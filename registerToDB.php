<?php 
include "lib.php"; 

$name = isset($_POST['name']) ? $_POST['name'] : 'Unknown';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '0';
$place = isset($_POST['place']) ? $_POST['place'] : '';
$gmt_offset = isset($_POST['gmt_offset']) ? $_POST['gmt_offset'] : '';
$gmt_offset_dsl = isset($_POST['gmt_offset_dsl']) ? $_POST['gmt_offset_dsl'] : '';

$validity_days=365;

if(!isset($_GET['key']))
{
	$loginpass = password_hash($_POST['loginpass'],PASSWORD_DEFAULT);
}
else{
	$loginpass = 'mobile user';
}

$key = isset($_GET['key']) ? $_GET['key'] : "99999";
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
 <title>PK Astro</title>
    <script type="text/javascript">
	
		function showRegistrationPage(){
			window.open('new_registration.php','_self');
		}
		
    </script>

<?php

$conn="";
$error="";

function register()
{
	global $conn,$phone,$key,$name,$loginpass, $error, $validity_days;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);
	
	//$phone = $_POST['phone'];
	//$pin = $_POST['pin']; 

	try {
		$sql = "INSERT INTO user (name, phone, pass, imei, last_login, user_type,registered_date) VALUES ('$name','$phone','$loginpass','$key',NULL,'FREE',CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30'))";

		//$sql = "INSERT INTO user VALUES ('a','9999999','9999999',0,null)";
	
		if(($result = mysqli_query($conn, $sql))){
				echo "<script>alert('Successfully registered');</script>";
				//add_userExpiry_data($validity_days);
				header( "Refresh:2; url=new_loginPage.php", true, 303);
		} else{
				echo "<script>alert('Error: Server error');</script>";
		    	header("HTTP/1.1 404 Not Found");
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
			echo "<script>alert('DUPLICATE: User already exits');showRegistrationPage();</script>";
		}
		
		mysqli_rollback($conn);

		throw $exception;
		
		echo "<script>alert('EXCEPTION: Server error');</script>";

	}
	
	dbDisconnect($conn);
}

function register_mobile()
{
	global $conn,$phone,$key,$name,$loginpass, $place,$tz, $gmt_offset, $gmt_offset_dsl,$$error,$validity_days;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);
	
	//$phone = $_POST['phone'];
	//$pin = $_POST['pin']; 

	try {
		$sql = "INSERT INTO user (name, phone, pass, imei, place, tz, GMT_offset, GMT_offset_dsl, last_login, user_type,registered_date) VALUES ('$name','$phone','$loginpass','$key','$place', '$tz', '$gmt_offset', '$gmt_offset_dsl', NULL,'FREE',CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30'))";

		//$sql = "INSERT INTO user VALUES ('a','9999999','9999999',0,null)";
	
		if(($result = mysqli_query($conn, $sql))){
				echo "<script>alert('Successfully registered');</script>";
				$_SESSION['logged']=true;
				$_SESSION['name']=$name;
				$_SESSION['phone']=$phone;
				$_SESSION['user_type']="FREE";
				//add_userExpiry_data($validity_days);
				header( "Refresh:2; url=index.php", true, 303);
				
		} else{
				echo "<script>alert('Error: Server error');</script>";
		    	header("HTTP/1.1 404 Not Found");
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
			echo "<script>alert('DUPLICATE: User already exits')</script>";
			//header( "Refresh:0; url=new_registration_mobile.php?key=$key", true, 303);
		}
		
		mysqli_rollback($conn);

		throw $exception;
		
		echo "<script>alert('EXCEPTION: Server error');</script>";

	}
	
	dbDisconnect($conn);
}

function add_userExpiry_data($validity_days)
{
	global $conn,$phone,$key,$name,$loginpass, $place,$tz, $gmt_offset, $gmt_offset_dsl,$error;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);
	
	//$phone = $_POST['phone'];
	//$pin = $_POST['pin']; 

	try {
		$sql = "INSERT INTO user_trial (phone, imei, expiry_date) VALUES ('$phone','$key',CONVERT_TZ(DATE_ADD(LOCALTIMESTAMP(),INTERVAL $validity_days DAY),'+00:00','+05:30'))"; // one year expiry
	
		if(!($result = mysqli_query($conn, $sql))){
			echo "<script>alert('Error: Server error');</script>";
			header("HTTP/1.1 404 Not Found");
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
			echo "<script>alert('DUPLICATE: User already exits')</script>";
			//header( "Refresh:0; url=new_registration_mobile.php?key=$key", true, 303);
		}
		
		mysqli_rollback($conn);

		throw $exception;
		
		echo "<script>alert('EXCEPTION: Server error');</script>";

	}
	
	dbDisconnect($conn);
	
	header( "Refresh:0; url=index.php?key=$key", true, 303);
}

if(isset($_GET['key']))
{
	register_mobile();
}
else{
	register();
}


?>




</body>

</html>