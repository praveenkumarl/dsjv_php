<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
<?php
include "db.php";

$conn="";

$phone = $_GET["phone"];

/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$conn=dbConnect($conn);
 
// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
if(isset($_REQUEST["term"])){
    // Prepare a select statement
	$sql = "SELECT rowid, name,sex,place,birth_date,birth_time FROM birth_chart WHERE phone=$phone and ((name LIKE ?) or (birth_date LIKE ?))";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        //mysqli_stmt_bind_param($stmt, "s", $param_term);
		mysqli_stmt_bind_param($stmt, "ss", $param_term1,$param_term1);

        // Set parameters
        //$param_term = '%' . $_REQUEST["term"] . '%';
		$param_term1 = '%' . $_REQUEST["term"] . '%';
		$param_term2 = '%' . $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
					 //echo "<p id='search'>" . $row["Name"] . "</p>";
					 //echo "<div id='search' style='display:box;background:gold;border-color: red;border: 1px solid;'>" . $row["Name"] . "</div>";
					 echo "<div id='search' style='display:box;background:gold;border-color: red;border: 1px solid;'>" . $row["name"] . ", " . $row["birth_date"] . ", " . $row["birth_time"] . ", " . $row["place"] . "</div>";
                }
            } else{
                echo "<p id='search_err'>No matches found</p>";
            }
        } else{
            echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
}
 
// close connection
dbDisconnect($conn);
?>