<?php

/*
$servername = "localhost";
$username = "u835731528_root";
$password = "Lrpvaa@123";
$db="u835731528_jamakol";
*/

$servername = "localhost";
$username = "u835731528_root";
$password = "Lrpvaa@123";
$db="u835731528_jamakol";

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;

function dbConnect()
{
	global $servername,$username,$password,$conn,$db,$DEBUG;

	/* Tell mysqli to throw an exception if an error occurs */
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $db);

	// Check connection
	if ($conn->connect_error) {
  	die("Connection failed: " . $conn->connect_error);
	}
 
    if($DEBUG==1){
	    echo "Connected successfully"; 
    }
}

function dbDisconnect()
{
	global $conn;
	$conn->close(); 
}

?>