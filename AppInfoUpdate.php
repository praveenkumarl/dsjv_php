<?php session_start();
include "db.php";
include "lib.php";

$conn="";

$appname = $_POST['frmAppName'];
$appver = $_POST['frmAppVer'];
$appurl = $_POST['frmAppUrl'];
$appdownloadurl = $_POST['frmAppDownloadUrl'];
$adminphone = $_POST['frmAdminPhone'];

function updateAppInfo()
{
	global $conn, $appname, $appver, $appurl, $appdownloadurl, $adminphone;
	
	$conn = dbConnect($conn);
	
	startSqlTxn($conn);

	try {
		$sql = "UPDATE app_info SET app_name = '$appname', app_version = '$appver', app_url='$appurl',app_download_url='$appdownloadurl',admin_phone='$adminphone'";
		
		debug($sql);
	
		if(mysqli_query($conn, $sql)){
			echo("Records updated successfully.");
		} else{
			echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
		}
		
		/* If code reaches this point without errors then commit the data in the database */
		commitSqlTxn($conn);
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

//Saving the app info to db

updateAppInfo();

header( "Refresh:2; url=".$_SERVER['HTTP_REFERER'], true, 303);
?>