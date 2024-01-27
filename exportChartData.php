<?php 
session_start();
// Library, this includes db config/db library functions 
require "lib.php";

$conn="";
$g_phone=isset($_GET["phone"]) ? $_GET["phone"] : 1;
 
function exportJamaChart()
{
	global $g_phone, $conn;
	
	$conn = dbConnect($conn);
	
	$sql = "SELECT rowid, phone, question, analysis, place, birth_date, birth_time FROM jama_chart WHERE phone='$g_phone' ORDER BY rowid DESC";
	
	// Fetch records from database 
	$query = $conn->query($sql);
	 
	if($query->num_rows > 0){ 
		$delimiter = ","; 
		$filename = "pkastro_jamachart_" . date('Y-m-d') . ".txt"; 
		 
		// Create a file pointer 
		$f = fopen('php://memory', 'w');
		fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
		 
		// Set column headers 
		$fields = array('ROWID', 'PHONE', 'QUESTION', 'ANALYSIS', 'PLACE', 'QUESTION_DATE', 'QUESTION_TIME'); 
		fputcsv($f, $fields, $delimiter); 
		 
		// Output each row of the data, format line as csv and write to file pointer 
		while($row = $query->fetch_assoc()){ 
			$lineData = array($row['rowid'], $row['phone'], $row['question'], $row['analysis'], $row['place'], $row['birth_date'], $row['birth_time']); 
			fputcsv($f, $lineData, $delimiter); 
		} 
		 
		// Move back to beginning of file 
		fseek($f, 0); 
		 
		// Set headers to download file rather than displayed 
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="' . $filename . '";'); 
		 
		//output all remaining data on a file pointer 
		fpassthru($f); 
	}
	
	dbDisconnect($conn);
}

function exportBirthChart()
{
	global $g_phone, $conn;
	
	$conn = dbConnect($conn);
	
	$sql = "SELECT rowid, phone, name, place, birth_date, birth_time FROM birth_chart WHERE phone='$g_phone' ORDER BY rowid DESC";
	
	// Fetch records from database 
	$query = $conn->query($sql);
	 
	if($query->num_rows > 0){ 
		$delimiter = ","; 
		$filename = "pkastro_birthchart_" . date('Y-m-d') . ".txt"; 
		 
		// Create a file pointer 
		$f = fopen('php://memory', 'w');
		fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
		 
		// Set column headers 
		$fields = array('ROWID', 'PHONE', 'NAME', 'PLACE', 'BIRTH_DATE', 'BIRTH_TIME'); 
		fputcsv($f, $fields, $delimiter); 
		 
		// Output each row of the data, format line as csv and write to file pointer 
		while($row = $query->fetch_assoc()){ 
			$lineData = array($row['rowid'], $row['phone'], $row['name'], $row['place'], $row['birth_date'], $row['birth_time']); 
			fputcsv($f, $lineData, $delimiter); 
		} 
		 
		// Move back to beginning of file 
		fseek($f, 0); 
		 
		// Set headers to download file rather than displayed 
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="' . $filename . '";'); 
		 
		//output all remaining data on a file pointer 
		fpassthru($f); 
	}
	
	dbDisconnect($conn);
}

function exportMatchingChart()
{
	global $g_phone, $conn;
	
	$conn = dbConnect($conn);
	
	$sql = "SELECT rowid, phone, g_name, g_place, g_birth_date , g_birth_time, b_name, b_place, b_birth_date, b_birth_time FROM matching_chart WHERE phone='$g_phone' ORDER BY rowid DESC";
	
	// Fetch records from database 
	$query = $conn->query($sql);
	 
	if($query->num_rows > 0){ 
		$delimiter = ","; 
		$filename = "pkastro_matchingchart_" . date('Y-m-d') . ".txt"; 
		 
		// Create a file pointer 
		$f = fopen('php://memory', 'w');
		fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
		 
		// Set column headers 
		$fields = array('ROWID', 'PHONE', 'GIRL NAME', 'GIRL PLACE', 'GIRL BIRTH_DATE', 'GIRL BIRTH_TIME','BOY NAME', 'BOY PLACE', 'BOY BIRTH_DATE', 'BOY BIRTH_TIME');
		fputcsv($f, $fields, $delimiter); 
		 
		// Output each row of the data, format line as csv and write to file pointer 
		while($row = $query->fetch_assoc()){ 
			$lineData = array($row['rowid'], $row['phone'], $row['g_name'], $row['g_place'], $row['g_birth_date'], $row['g_birth_time'], $row['b_name'], $row['b_place'], $row['b_birth_date'], $row['b_birth_time']); 
			fputcsv($f, $lineData, $delimiter); 
		} 
		 
		// Move back to beginning of file 
		fseek($f, 0); 
		 
		// Set headers to download file rather than displayed 
		header('Content-Type: text/csv'); 
		header('Content-Disposition: attachment; filename="' . $filename . '";'); 
		 
		//output all remaining data on a file pointer 
		fpassthru($f); 
	}
	
	dbDisconnect($conn);
}

if(isset($_GET["charttype"])){
	switch($_GET["charttype"]){
		case 'j':
			exportJamaChart();
			break;
		case 'b':
			exportBirthChart();
			break;
		case 'm':
			exportMatchingChart();
			break;
		default:
			echo "Invalid charttype - ".$_GET["charttype"];
			break;
	}
}
 
?>