 <?php 
session_start();
include "db.php"; 

$DEBUG= 0;//(int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn="";
$error="";
$rowid = $_GET["rowid"];
$charttype = $_GET["charttype"]; //chart type to delete b-birth j-jamakol


function deleteBirthChart()
{
	global $conn,$rowid;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {	
		$sql = "DELETE FROM birth_chart WHERE rowid=$rowid";
	
		if(mysqli_query($conn, $sql)){
			//echo "<script>alert('Records deleted successfully.');</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);
		echo "ERROR updating the data";

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function deleteMatchingChart()
{
	global $conn,$rowid;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {	
		$sql = "DELETE FROM matching_chart WHERE rowid=$rowid";
	
		if(mysqli_query($conn, $sql)){
			//echo "<script>alert('Records deleted successfully.');</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);
		echo "ERROR updating the data";

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function deleteJamaChart()
{
	global $conn,$rowid;
	
	$conn=dbConnect($conn);
	
	startSqlTxn($conn);

	try {	
		$sql = "DELETE FROM jama_chart WHERE rowid=$rowid";
	
		if(mysqli_query($conn, $sql)){
			//echo "<script>alert('Records deleted successfully.');</script>";
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);
		echo "ERROR updating the data";

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Saving the chart details, question, analysis to db

switch($charttype)
{
	case 'b':
		deleteBirthChart();
		break;
	case 'j':
		deleteJamaChart();
		break;
	case 'm':
		deleteMatchingChart();
		break;
	default:
		break;
}

header( "Refresh:0; url=".$_SERVER['HTTP_REFERER'], true, 303);
?>


