<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
<?php
include "db.php";

$conn="";

/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$conn=dbConnect($conn);
 
// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
if(isset($_REQUEST["term"])){
    // Prepare a select statement
    $sql = "SELECT planet,kaaragam FROM kaaragam WHERE kaaragam LIKE ? ";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "s", $param_term);

	
        // Set parameters
        $param_term = '%' . $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);
            
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
                // Fetch result rows as an associative array
				echo "<table style='margin-left:-150px; white-space:nowrap;'>";
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
					echo "<tr>";
                    echo "<td><span class='kaaragam'>" . trim($row["kaaragam"]) ."</td>";
					echo "<td></span> &#8594 <span class='planet'>" . trim($row["planet"]) . "</span></td>";
					echo "</tr>";
                }
				echo "</table>";
            } else{
                echo "<p id='search_err'>No matches found, please add</p>";
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