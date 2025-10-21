<?php session_start();
include "db.php";
include "lib.php"; 

$conn="";
$device_id = $_GET["device_id"];


function updateLoginTimestamp($phone)
{
	global $conn;
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE user SET last_login = CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30') where phone='$phone'";
		
		//echo $sql;
	
		if(mysqli_query($conn, $sql)){
			debug("Records inserted successfully.");
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
}

function login()
{
	global $conn,$device_id;
	$phone=0;
	$user_type="";
	$name="";
	$Active=0;
	$Paid=0;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		
		$sql = "SELECT phone,user_type,name,Active,Paid from user where imei='$device_id'";
		
		//echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$phone = $row["phone"];
					$user_type = $row["user_type"];
					$name = $row["name"];
					$Active = $row["Active"];
					$Paid = $row["Paid"];
				  }
				  
				  updateLoginTimestamp($phone);
				  
				 echo $phone . "|". $user_type . "|" . $name;
				 
				 if($Active==1 && $Paid==1){
					 header("HTTP/1.0 200");
				 }
				 elseif($Active==0 || $Paid==0){
					 header("HTTP/1.0 201");
				 }
			}
			else{
				header("HTTP/1.0 401");
			}
		}
		else{
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
	
	dbDisconnect($conn);
}

//Saving the chart details, question, analysis to db

login();

?>