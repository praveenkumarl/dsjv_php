<?php 
include_once "lib.php"; 

$g_session_id = session_id();

$phone = $_SESSION['phone'];


function clearSession($session_id){
	global $conn, $g_session_id, $phone;
	
	$result = false;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user set imei='$phone' where imei='$g_session_id'"; 
		
		if(($result = mysqli_query($conn, $sql))){
				echo "<script>alert('Logged out');</script>";
				header( "Refresh:2; url=new_loginPage.php", true, 303);
		} else{
				echo "<script>alert('Error: Server error');</script>";
		    	header("HTTP/1.1 404 Not Found");
		}
	
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	/* If code reaches this point without errors then commit the data in the database */
	commitSqlTxn($conn);
	
	dbDisconnect($conn);
}

clearSession($g_session_id);

session_destroy();

header( "Refresh:0; url=new_loginPage.php", true, 303);

?>
