<?php
$jsonResponse = isset($_GET['format']) && $_GET['format'] === 'json';
$jsonBufferLevel = ob_get_level();
if ($jsonResponse) {
	ob_start();
}

function sendPlaceSearchJson($payload, $statusCode) {
	global $jsonBufferLevel;

	while (ob_get_level() > $jsonBufferLevel) {
		ob_end_clean();
	}

	http_response_code($statusCode);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($payload, JSON_UNESCAPED_UNICODE);
	exit;
}

require_once __DIR__ . '/webview_only.php';
include "db.php";

$conn="";

/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$conn=dbConnect($conn);

if ($jsonResponse) {
	if (!($conn instanceof mysqli)) {
		sendPlaceSearchJson(array('error' => 'Place search is temporarily unavailable.'), 503);
	}

	$places = array();
	$term = isset($_GET['term']) ? trim($_GET['term']) : '';

	if (strlen($term) >= 3) {
		$sql = "SELECT Place,TZ,country FROM world_cities WHERE other_names LIKE ? and country='India' UNION SELECT Place,TZ,country FROM world_cities WHERE other_names LIKE ? and country!='India' LIMIT 20";
		$stmt = null;
		try {
			$stmt = $conn->prepare($sql);
			$searchTerm = $term . '%';
			$stmt->bind_param('ss', $searchTerm, $searchTerm);
			$stmt->execute();
			$stmt->bind_result($place, $timezone, $country);

			while ($stmt->fetch()) {
				$places[] = array(
					'place' => $place,
					'timezone' => $timezone,
					'country' => $country
				);
			}

			$stmt->close();
		} catch (mysqli_sql_exception $exception) {
			if ($stmt instanceof mysqli_stmt) {
				$stmt->close();
			}
			dbDisconnect($conn);
			error_log('backend-search.php: place search failed: ' . $exception->getMessage());
			sendPlaceSearchJson(array('error' => 'Place search is temporarily unavailable.'), 500);
		}
	}

	dbDisconnect($conn);
	sendPlaceSearchJson($places, 200);
}
 
// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
if(isset($_REQUEST["term"])){
    // Prepare a select statement
    //$sql = "SELECT Place,TZ FROM world_cities WHERE Place LIKE ?";
	 $sql = "SELECT Place,TZ,country FROM world_cities WHERE other_names LIKE ? and country='India' UNION SELECT Place,TZ,country FROM world_cities WHERE other_names LIKE ? and country!='India'";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "ss", $param_term, $param_term);

        // Set parameters
        $param_term = $_REQUEST["term"] . '%';
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            $result = mysqli_stmt_get_result($stmt);            
			
            // Check number of rows in the result set
            if(mysqli_num_rows($result) > 0){
				//echo '<select name="placelist" id="placelist" size="10" style="z-index: 100">';
				//echo '<option value="Select value"></option>';
                // Fetch result rows as an associative array
                while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    //echo "<p id='search'>" . $row["Place"] . "|" . $row["TZ"] . "</p>";
					echo "<div id='search' style='display:box;background:gold;border-color: red;border: 1px solid;'>" . $row["Place"] . "<div style='display: none;'>|" . $row["TZ"] . "</div>|<div style='display: inline;color: blue;'>" . $row["country"] . "</div></div>";
					//echo '<option value="'.$row["Place"].'|'.$row["TZ"].'|'.$row["country"].'">'.$row["Place"].'('.$row["country"].')</option>';
                }
				
				//echo '</select>';
				
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