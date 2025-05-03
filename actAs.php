<?php  
include "lib.php";

$conn="";
$rowid=isset($_GET['rowid']) ? $_GET['rowid'] : -1;

$userInfo=array();

function getUserDetails()
{
	global $conn,$userInfo, $rowid;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where rowid='$rowid'";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$userInfo["Name"][$cnt] = $row["name"];
					$userInfo["phone"][$cnt] = $row["phone"];
					$userInfo["imei"][$cnt]= $row["imei"];
					$userInfo["last_login"][$cnt] = $row["last_login"];
					$userInfo["user_type"][$cnt] = $row["user_type"];
					$userInfo["registered_date"][$cnt] = $row["registered_date"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

getUserDetails();

session_destroy();

header('Location: index.php?key='.$userInfo['imei'][0]);


?>

