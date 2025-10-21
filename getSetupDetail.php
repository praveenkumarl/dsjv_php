<?php session_start();
include "db.php"; 

$conn="";

function getSetupDetail()
{
	global $conn,$device_id,$encryption;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "SELECT app_version,app_url,app_download_url,admin_phone from app_info";
		
		//echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				while($row = $result->fetch_assoc()) {
					$appver = $row["app_version"];
					$app_url = $row["app_url"];
					$app_download_url = $row["app_download_url"];
					$admin_phone = $row["admin_phone"];
				  }
				  
				echo $app_url . "|" . $appver . "|" . $app_download_url;

				header("HTTP/1.0 200");
			}else {
				header("HTTP/1.0 401");
			}
		} else{
				header("HTTP/1.0 500");
				echo "ERROR: Could not able to execute $sql contact admin" . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function getSetupDetail_from_file()
{
	$myfile = fopen("initial_setup", "r") or die("Unable to open file!");
	echo fgets($myfile);
	fclose($myfile);
}

//Getting initial setup Data from DB

//getSetupDetail_from_file();
getSetupDetail();

?>