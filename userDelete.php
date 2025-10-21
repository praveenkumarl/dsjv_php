<?php session_start();
include "lib.php";

$conn="";

$phone = $_GET['phone'];

function deleteUser()
{
	global $conn, $phone;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "DELETE FROM user where phone='$phone'";
		
		debug($sql);
	
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('User " . $phone . " deleted successfully.');</script>";
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
		$activity = 'User Delete|'.$phone;
		logActivity($conn,$_COOKIE["phone"],$activity);
	}
	
	dbDisconnect($conn);
}

//Saving the app info to db

deleteUser();

header( "Refresh:0; url=admin.php", true, 303);

?>