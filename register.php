<?php session_start();
include "db.php"; 
?>

<!doctype html>
<html lang="en" class="no-js">
<head>

<?php

$conn="";
$error="";
$phone="";
$imei="";
$name="";

function debug($o)
{
	global $DEBUG;
	
	if($DEBUG==1){
		print '<pre>';
		print_r($o);
		print '</pre>';
	}
}


function register()
{
	global $conn,$phone,$imei,$name, $error;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);
	
	//$phone = $_POST['phone'];
	//$pin = $_POST['pin']; 
	
	$phone = $_GET['phone_no'];
	$name = $_GET['name'];
    $imei = $_GET['imei'];
    
    echo $name."|".$phone."|".$imei."|";

	try {
		$sql = "INSERT INTO user (Name, phone, imei, Active, last_login, Active_trial,Paid,user_type,registered_date) VALUES ('$name','$phone','$imei',0,NULL,1,0,'FREE',CONVERT_TZ(LOCALTIMESTAMP(),'+00:00','+05:30'))";

		//$sql = "INSERT INTO user VALUES ('a','9999999','9999999',0,null)";
	
		if(($result = mysqli_query($conn, $sql))){
				header("HTTP/1.1 200 Success");
		} else{
		    	header("HTTP/1.1 404 Not Found");
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
		
		echo "fail";

	}
	
	dbDisconnect($conn);
}


register();


?>




</body>

</html>