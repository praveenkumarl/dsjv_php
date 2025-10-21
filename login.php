<?php session_start();
include "db.php"; 
?>

<!doctype html>
<html lang="en" class="no-js">
<head>

</head>
<body onLoad="init();">

<?php

$conn="";
$phone = $_GET["phone"];
$pin = $_GET["PIN"];

function login()
{
	global $conn,$phone,$pin;
	
	dbConnect();
	
	startSqlTxn($conn);

	try {
		$sql = "SELECT phone from user where phone=$phone AND pin=$pin";
		
		echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
				$count = mysqli_num_rows($result);

				if($count==1){
					$sql = "UPDATE user SET logged=1, last_login=CURRENT_TIMESTAMP() where phone=$phone AND pin=$pin";
					mysqli_query($conn, $sql);

					header("HTTP/1.0 200");
					echo "Success";
				}else {
					header("HTTP/1.0 400");
					echo "You don't have access, contact 9445321790";
				}
		} else{
				header("HTTP/1.0 500");
				echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
			
			$_SESSION['logged'] = 0;
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect();
}

//Saving the chart details, question, analysis to db

login();

?>



</body>

</html>