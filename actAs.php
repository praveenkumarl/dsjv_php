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

var_dump($userInfo);

session_destroy();

header('Location: index.php?key='.$userInfo['imei'][0]);

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro admin page</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7102258981747108" crossorigin="anonymous"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
		
    </script>
</head>

</html>