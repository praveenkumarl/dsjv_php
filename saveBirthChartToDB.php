<?php
require_once __DIR__ . '/webview_only.php';
session_start();
include "db.php"; 
$place=$_SESSION["place"];
$date=$_SESSION["date"];
$time=$_SESSION["time"];
$_SESSION['error']="";
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
	
	   $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FFe6df', '#FFcdbf', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
              menu: '#menu',
            });
        });

function init(){
}


		
    </script>
</head>
<body onLoad="init();">

<?php

$DEBUG = (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$error="";
$place = $_POST["place"];
$chart_date = $_POST["date"];
$chart_time = $_POST["time"];
$chart_name = $_POST["name"];

function saveChartDetailsToDB()
{
	global $conn,$place,$chart_date,$chart_time,$chart_question,$chart_analysis;
	
	$db_date=date("Y-m-d",strtotime($chart_date));
	
	$inside = serialize($_SESSION["inside"]);
	$outside = serialize($_SESSION["outside"]);
	
	dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "INSERT INTO chart_history VALUES ('$db_date','$chart_time','$place','$chart_question','$chart_analysis','$inside','$outside')";
	
		if(mysqli_query($conn, $sql)){
			debug("Records inserted successfully.");
			echo "<script>alert('Records inserted successfully.');window.close();</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($mysqli);

		throw $exception;
	}
	
	dbDisconnect();
}

//Saving the chart details, question, analysis to db

saveChartDetailsToDB();

echo "<script>window.close();</script>";

?>



</body>

</html>