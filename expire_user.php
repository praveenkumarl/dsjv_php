<?php session_start();
include "lib.php";

$conn="";

function validateUserExpiry()
{
	global $conn, $phone;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "call remove_trial();";
		
		debug($sql);
	
		if(!mysqli_query($conn, $sql)){
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Execute user expiry

validateUserExpiry();

?>