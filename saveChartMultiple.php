 <?php 
session_start();
include "lib.php"; 

$DEBUG= 1;//(int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$error="";

//for birthchart
$name = isset($_GET["name"]) ? $_GET["name"] : "";
$sex = isset($_GET["sex"]) ? $_GET["sex"] : "";
$savemode = isset($_GET["savemode"]) ? $_GET["savemode"] : "";
$savemode = intval($savemode);
$g_rowid = isset($_GET["rowid"]) ? $_GET["rowid"] : "-1";
$g_notes = isset($_POST["hNotesTxt"]) ? $_POST["hNotesTxt"] : "";


//for jamakol
$question = isset($_GET["question"]) ? $_GET["question"] : "";
$analysis = isset($_POST["analysis"]) ? $_POST["analysis"] : "";
$savemode = isset($_GET["savemode"]) ? $_GET["savemode"] : "";
$g_rowid = isset($_GET["rowid"]) ? $_GET["rowid"] : "-1";
$savemode = intval($savemode);

// for both birthchart and jamakol
$place = isset($_GET["place"]) ? $_GET["place"] : "";
$date = isset($_GET["date"]) ? $_GET["date"] : "";
$time = isset($_GET["time"]) ? $_GET["time"] : "";

//matching chart
$girlname = isset($_GET["girlname"]) ? $_GET["girlname"] : "";
$place_girl = isset($_GET["place_girl"]) ? $_GET["place_girl"] : "";
$date_girl = isset($_GET["in_date_girl"]) ? $_GET["in_date_girl"] : "";
$time_girl = isset($_GET["in_time_girl"]) ? $_GET["in_time_girl"] : "";
$mark = isset($_GET["mark"]) ? $_GET["mark"] : 0;

$boyname = isset($_GET["boyname"]) ? $_GET["boyname"] : "";
$place_boy = isset($_GET["place_boy"]) ? $_GET["place_boy"] : "";
$date_boy = isset($_GET["in_date_boy"]) ? $_GET["in_date_boy"] : "";
$time_boy = isset($_GET["in_time_boy"]) ? $_GET["in_time_boy"] : "";

$phone = isset($_COOKIE["phone"]) ? $_COOKIE["phone"] : $_SESSION['userinfo']['phone'];
$charttype = $_GET["charttype"];

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
	
    <title>PK Astro</title>
    <script type="text/javascript">
	
	function showSavedBirthCharts(){
		window.open('birthChartDisplay.php','_self');
	}
	
	function showSavedJamaCharts(){
		window.open('jamaChartDisplay.php','_self');
	}
	
	function showSavedMatchingCharts(){
		window.open('matchingChartDisplay.php','_self');
	}
		
    </script>
</head>
<body>

<!-- (A) HIDDEN HTML FORM -->
<form id="hiddenViewBirthChart" action="Birth_Input.php" method="post" style="display:none;">
  <input type="hidden" id="place" name="place" required/>
  <input type="hidden" id="name" name="name" required/>
  <input type="hidden" id="sex" name="sex" required/>
  <input type="hidden" id="in_date" name="in_date" required/>
  <input type="hidden" id="in_time" name="in_time" required/>
  <input type="hidden" id="lang" name="lang" required/>
</form>

<!-- (A) HIDDEN HTML FORM -->
<form id="hiddenViewJamaChart" action="Jamakol_Chart.php" method="post" style="display:none;">
  <input type="hidden" id="place" name="place" required/>
  <input type="hidden" id="name" name="name" required/>
  <input type="hidden" id="in_date" name="in_date" required/>
  <input type="hidden" id="in_time" name="in_time" required/>
  <input type="hidden" id="lang" name="lang" required/>
  <input type="hidden" id="in_degree" name="in_degree" required/>
   <input type="hidden" id="nDegree" name="nDegree" required/>
</form>

<?php

function saveBirthChartToDB($in_savemode)
{
	global $conn,$place,$date,$time,$phone,$name,$g_notes,$g_rowid,$sex;
	
	$db_date=date("Y-m-d",strtotime($date));
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {	
		if($in_savemode==0){ //insert
			$sql = "INSERT INTO birth_chart (phone,name,sex,place,birth_date,birth_time,notes) VALUES ('$phone','$name','$sex','$place','$date','$time','$g_notes')";
		}
		else{ //update existing record
			$sql = "UPDATE birth_chart SET phone='$phone',name='$name',sex='$sex',place='$place',birth_date='$date',birth_time='$time',notes='$g_notes' where rowid='$g_rowid'";
		}
		
	
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('Records inserted/updated successfully.');showSavedBirthCharts();</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} 
	catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
			echo "<script>alert('ERROR: Chart already exits');showSavedBirthCharts();</script>";
		}
		
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function saveMatchingChartToDB()
{
	global $conn,$phone,$girlname,$place_girl,$date_girl,$time_girl,$boyname,$place_boy,$date_boy,$time_boy, $mark;
	
	$date_girl=date("Y-m-d",strtotime($date_girl));
	$date_boy=date("Y-m-d",strtotime($date_boy));
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {	
		$sql = "INSERT INTO matching_chart (phone,g_name,g_place,g_birth_date,g_birth_time,b_name,b_place,b_birth_date,b_birth_time,mark) VALUES ('$phone','$girlname','$place_girl','$date_girl','$time_girl','$boyname','$place_boy','$date_boy','$time_boy','$mark')";
		
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('Records inserted successfully.');showSavedMatchingCharts();</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} 
	catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) {
		    echo "<script>alert('ERROR: Chart already exits');showSavedMatchingCharts();</script>";
		}
		
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function saveJamaChartToDB($in_savemode)
{
	global $conn,$place,$date,$time,$phone,$question, $analysis, $g_rowid;
	
	$db_date=date("Y-m-d",strtotime($date));
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		if($in_savemode==0){ //insert
			$sql = "INSERT INTO jama_chart (phone,question,analysis,place,birth_date,birth_time) VALUES ('$phone','$question','$analysis','$place','$date','$time')";
		}
		else{ //update existing record
			$sql = "UPDATE jama_chart SET question='$question',analysis='$analysis' where rowid='$g_rowid'";
		}
	
		if(mysqli_query($conn, $sql)){
			echo "<script>alert('Records inserted/updated successfully.');showSavedJamaCharts();</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} 
	catch (mysqli_sql_exception $exception) {
		if ($exception->getCode() == 1062) { //will not be required as we have added update statement, should be removed in future
			echo "<script>alert('ERROR: Chart already exits'.$sql);showSavedJamaCharts();</script>";
		}
		
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Saving the chart details, question, analysis to db

switch($charttype)
{
	case 'b':
		saveBirthChartToDB($savemode);
		break;
	case 'j':
		saveJamaChartToDB($savemode);
		break;
	case 'm':
		saveMatchingChartToDB();
		break;
	default:
		break;
}

?>



</body>

</html>