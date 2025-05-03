<?php session_start();
include "lib.php";

$conn="";

$user_name = $_POST['Name'];
$_SESSION['Name']=$user_name;
$phone = $_POST['phone'];
$imei = $_POST['imei'];
$expiry_date = $_POST['expiry_date'];
$old_expiry_date = $_SESSION['userinfo']['expiry_date'];
$usertype = $_POST['usertype'];
$old_usertype = $_POST['old_usertype'];
$id = $_POST['id'];

function updateUser()
{
	global $conn, $user_name, $phone, $imei, $usertype, $old_usertype, $id;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user SET name = '$user_name', phone = '$phone', imei='$imei', user_type='$usertype' where rowid='$id'";
		
		debug($sql);
	
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('Records updated successfully.');</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);
		echo "ERROR updating the data";

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function updateExpiryDate()
{
	global $conn, $expiry_date, $phone, $imei;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user_trial SET expiry_date = '$expiry_date' where imei='$imei'";
		
		debug($sql);
	
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('Records updated successfully.');</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);
		echo "ERROR updating the data";

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Saving the app info to db

updateUser();

if(!strcmp($old_expiry_date,$expiry_date)==0){
	updateExpiryDate();
}
else{
	echo "<script>alert('Expiry date same and not updated');</script>";
}

if(strcmp($_SESSION['user_type'],"ADMIN")==0){
	header( "Refresh:0; url=admin.php", true, 303);
}
else{
	header( "Refresh:0; url=maintenance.php", true, 303);
}

//Log user acitivity
if(isset($_COOKIE["phone"])) {
	$activity = 'User Update|'.$user_name."|".$imei."|".$old_usertype."|".$usertype."|"."old-expiry-date=$old_expiry_date,new-expiry-date=$expiry_date";
	logActivity($conn,$_COOKIE["phone"],$activity);
}

?>