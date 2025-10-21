<?php
require "db_config.php";


function dbConnect($conn)
{
	global $servername,$username,$password,$db,$DEBUG;

	/* Tell mysqli to throw an exception if an error occurs */
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	try{
		// Create connection
		$conn = new mysqli($servername, $username, $password, $db);
	
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
	
		mysqli_set_charset($conn, 'utf8');
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}
	catch (InvalidArgumentException $e) {
		echo $e->getMessage();
	}
	
	return $conn;
}

function dbDisconnect($conn)
{
	$conn->close(); 
}

function startSqlTxn($conn)
{
	/* Start transaction */
	mysqli_begin_transaction($conn);
}

function commitSqlTxn($conn)
{
	/* Start transaction */
	mysqli_commit($conn);
}

function logActivity($conn,$phone,$activity)
{
	startSqlTxn($conn);

	try {
		$sql = "INSERT LOW_PRIORITY INTO activity_log VALUES (CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30'),'$phone','$activity')";
		//$sql = "INSERT LOW_PRIORITY INTO activity_log VALUES (LOCALTIMESTAMP(),'$phone','$activity')";
		if(!mysqli_query($conn, $sql)){
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
}

function sanitiseInput($db,$input)
{
	return mysqli_real_escape_string($db, $input);
}

?>

