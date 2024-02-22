<?php session_start();
include "lib.php";

$conn="";

$user_name = $_POST['Name'];
$_SESSION['Name']=$user_name;
$phone = $_POST['phone'];
$imei = $_POST['imei'];
$usertype = $_POST['usertype'];
$old_usertype = $_POST['old_usertype'];

echo $usertype;

function updateUser()
{
	global $conn, $user_name, $phone, $imei, $usertype, $old_usertype;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user SET name = '$user_name', phone = '$phone', imei='$imei', user_type='$usertype' where name='$user_name'";
		
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
	
	//Log user acitivity
	if(isset($_COOKIE["phone"])) {
		$activity = 'User Update|'.$phone."|".$user_name."|".$imei."|".$old_usertype."|".$usertype;
		echo $activity;
		logActivity($conn,$_COOKIE["phone"],$activity);
	}
	
	dbDisconnect($conn);
}

//Saving the app info to db

updateUser();

header( "Refresh:0; url=admin.php", true, 303);

?>