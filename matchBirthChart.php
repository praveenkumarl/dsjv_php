<!doctype html>
<?php 
session_start();

include_once "lib.php";

const AGE_LIMIT = 17;
const REC_PER_PAGE = 25;

$conn="";
$phone=isset($_SESSION["phone"]) ? $_SESSION["phone"] : 1;

$g_girlpage = isset($_GET['girlpage']) ? $_GET['girlpage'] : 1;
$g_boypage = isset($_GET['boypage']) ? $_GET['boypage'] : 1;

$girlCharts =[];
$boyCharts =[];

function getGirlChartDetails($girlpage)
{
    global $conn, $girlCharts, $phone, $g_boypage;
	
	$records_per_page = REC_PER_PAGE;
    
    $cnt = 0;
    
    $conn = dbConnect($conn);
    
    if ($phone == 0) {
        echo "ERROR: Missing phone number";
        return;
    }

    try {
        // Calculate the offset for the SQL query
        $offset = ($girlpage - 1) * $records_per_page;
        
        // Get the total number of records
        //$total_sql = "SELECT COUNT(*) FROM birth_chart WHERE phone='$phone' and sex='பெண்'";
		$total_sql = "SELECT COUNT(*) FROM birth_chart 
              WHERE phone='$phone' 
              AND sex='பெண்' 
              AND DATEDIFF(CURDATE(), birth_date) / 365 > 15";
        $total_result = mysqli_query($conn, $total_sql);
        $total_records = mysqli_fetch_array($total_result)[0];
        $total_pages = ceil($total_records / $records_per_page);
        
        // Fetch the records for the current page
        /*$sql = "SELECT rowid, name, place, birth_date, birth_time 
                FROM birth_chart 
                WHERE phone='$phone' and sex='பெண்' 
                ORDER BY rowid DESC 
                LIMIT $offset, $records_per_page";*/
		$sql = "SELECT rowid, name, place, birth_date, birth_time 
				FROM birth_chart 
				WHERE phone='$phone' 
				AND sex='பெண்' 
				AND DATEDIFF(CURDATE(), birth_date) / 365 > 15 
				ORDER BY rowid DESC 
				LIMIT $offset, $records_per_page";
    
        if (($result = mysqli_query($conn, $sql))) {
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                echo '<style>
                       .girl-table, .girl-table th, .girl-table td {
                            border: 1px solid black;
                            border-collapse: collapse;
                            background-color:#ffe6f2;
                            font-weight:bold;
                            font-size:8px;
                            vertical-align:middle;
                            padding:5px;
                        }
                      </style>';
                echo '<table class="girl-table" id="girl_table" border="1">';
                echo '<tr><th>Select</th><th>பெண் பெயர்</th><th>பெண் வயது</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $birthDate = new DateTime($row["birth_date"]);
                    $currentDate = new DateTime();
                    $age = $currentDate->diff($birthDate)->y;
                    
                    echo '<tr>';
                    //echo '<td><input type="radio" name="girl_radio" value="' . $row["rowid"] . '" onChange="selectGirl(' . $row["rowid"] .');"></td>';
					echo '<td><input type="radio" name="girl_radio" value="' . $row["name"] . '" onChange="selectGirl(' . $row["rowid"] .');"></td>';
                    echo '<td>' . $row["name"] . '</td>';
                    echo '<td>' . $age . '</td>';
                    echo '</tr>';
                    
                    array_push($girlCharts, $row);
                }
                
                echo '</table>';
                
                /*
				// Display pagination
                echo '<div class="pagination" id="girlPagination">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    //echo '<a href="?boypage='.$g_boypage.'&girlpage=' . $i . '">' . $i . '</a> ';
					$activeClass = ($i == $girlpage) ? 'active' : '';
					echo '<a href="?boypage='.$g_boypage.'&girlpage=' . $i . '" class="' . $activeClass . '">' . $i . '</a> ';
                }
                echo '</div>';*/
				
				// Display pagination
				echo '<div class="pagination" id="girlPagination">';

				// Previous button
				if ($girlpage > 1) {
					$prevPage = $girlpage - 1;
					echo '<a href="matchBirthChart.php?boypage='.$g_boypage.'&girlpage=' . $prevPage . '">Previous</a> ';
				}

				// Next button
				if ($girlpage < $total_pages) {
					$nextPage = $girlpage + 1;
					echo '<a href="matchBirthChart.php?boypage='.$g_boypage.'&girlpage=' . $nextPage . '">Next</a> ';
				}

				echo '</div>';


            } else {
                echo "No records found.";
            }
        }
        
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        throw $exception;
    }
    
    dbDisconnect($conn);
}


function getGirlChartDetails1()
{
    global $conn, $girlCharts, $phone;
    
    $cnt = 0;
    
    $conn = dbConnect($conn);
    
    if ($phone == 0) {
        echo "ERROR: Missing phone number";
        return;
    }

    try {
        
        $sql = "SELECT rowid,name, place, birth_date, birth_time FROM birth_chart WHERE phone='$phone' and sex='பெண்' ORDER BY rowid DESC";
    
        if (($result = mysqli_query($conn, $sql))) {
            $count = mysqli_num_rows($result);

            if ($count > 0) {
				echo '<style>
                       .girl-table, .girl-table th, .girl-table td {
                            border: 1px solid black;
                            border-collapse: collapse;
							background-color:#ffe6f2;
							font-weight:bold;
							font-size:8px;
							vertical-align:middle;
							padding:5px;
                        }
                      </style>';
                echo '<table class="girl-table" id="girl_table" border="1">';
                echo '<tr><th>Select</th><th>பெண் பெயர்</th><th>பெண் வயது</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
					$birthDate = new DateTime($row["birth_date"]);
                    $currentDate = new DateTime();
                    $age = $currentDate->diff($birthDate)->y;
					
                    echo '<tr>';
                    //echo '<td><input type="radio" name="girl_radio" value="' . $row["rowid"] . '" onChange="selectGirl(' . $row["rowid"] .');"></td>';
					echo '<td><input type="radio" name="girl_radio" value="' . $row["name"] . '" onChange="selectGirl(' . $row["rowid"] .');"></td>';
                    echo '<td>' . $row["name"] . '</td>';
					echo '<td>' . $age . '</td>';
                    echo '</tr>';
					
					array_push($girlCharts,$row);
                }
                
                echo '</table>';
            } else {
                echo "No records found.";
            }
        }
        
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        throw $exception;
    }
    
    dbDisconnect($conn);
}

function getBoyChartDetails($boypage)
{
    global $conn, $boyCharts, $phone, $g_girlpage;
	
	$records_per_page = REC_PER_PAGE;
    
    $cnt = 0;
    
    $conn = dbConnect($conn);
    
    if ($phone == 0) {
        echo "ERROR: Missing phone number";
        return;
    }

    try {
        // Calculate the offset for the SQL query
        $offset = ($boypage - 1) * $records_per_page;
        
        // Get the total number of records
        //$total_sql = "SELECT COUNT(*) FROM birth_chart WHERE phone='$phone' and sex='ஆண்'";
		$total_sql = "SELECT COUNT(*) FROM birth_chart 
					  WHERE phone='$phone' 
					  AND sex='ஆண்' 
					  AND DATEDIFF(CURDATE(), birth_date) / 365 > 15";
        $total_result = mysqli_query($conn, $total_sql);
        $total_records = mysqli_fetch_array($total_result)[0];
        $total_pages = ceil($total_records / $records_per_page);
        
        // Fetch the records for the current page
        /*$sql = "SELECT rowid, name, place, birth_date, birth_time 
                FROM birth_chart 
                WHERE phone='$phone' and sex='ஆண்' 
                ORDER BY rowid DESC 
                LIMIT $offset, $records_per_page";*/
		$sql = "SELECT rowid, name, place, birth_date, birth_time 
				FROM birth_chart 
				WHERE phone='$phone' 
				AND sex='ஆண்' 
				AND DATEDIFF(CURDATE(), birth_date) / 365 > 15 
				ORDER BY rowid DESC 
				LIMIT $offset, $records_per_page";
    
        if (($result = mysqli_query($conn, $sql))) {
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                echo '<style>
                       .boy-table, .boy-table th, .boy-table td {
                            border: 1px solid black;
                            border-collapse: collapse;
							background-color:#ccffff;
							font-weight:bold;
							font-size:8px;
							vertical-align:middle;
							padding:5px;
                        }
                      </style>';
                echo '<table class="boy-table" id="boy_table" border="1">';
                echo '<tr><th>Select</th><th>ஆண் பெயர்</th><th>ஆண் வயது</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
                    $birthDate = new DateTime($row["birth_date"]);
                    $currentDate = new DateTime();
                    $age = $currentDate->diff($birthDate)->y;
                    
                    echo '<tr>';
                    //echo '<td><input type="radio" name="boy_radio" value="' . $row["rowid"] . '" onChange="selectBoy(' . $row["rowid"] .');"></td>';
					echo '<td><input type="radio" name="boy_radio" value="' . $row["name"] . '" onChange="selectBoy(' . $row["rowid"] .');"></td>';
                    echo '<td>' . $row["name"] . '</td>';
                    echo '<td>' . $age . '</td>';
                    echo '</tr>';
                    
                    array_push($boyCharts, $row);
                }
                
                echo '</table>';
                
				/*
                // Display pagination
                echo '<div class="pagination" id="boyPagination">';
                for ($i = 1; $i <= $total_pages; $i++) {
                    //echo '<a href="?girlpage='.$g_girlpage.'&boypage=' . $i . '">' . $i . '</a> ';
					$activeClass = ($i == $boypage) ? 'active' : '';
					echo '<a href="?girlpage='.$g_girlpage.'&boypage=' . $i . '" class="' . $activeClass . '">' . $i . '</a> ';
                }
                echo '</div>';*/
				
				// Display pagination for boys
				echo '<div class="pagination" id="boyPagination">';

				// Previous button
				if ($boypage > 1) {
					$prevPage = $boypage - 1;
					echo '<a href="matchBirthChart.php?girlpage='.$g_girlpage.'&boypage=' . $prevPage . '" onclick="updatePageState(' . $prevPage . ', ' . $g_girlpage . ')">Previous</a> ';
				}

				// Next button
				if ($boypage < $total_pages) {
					$nextPage = $boypage + 1;
					echo '<a href="matchBirthChart.php?girlpage='.$g_girlpage.'&boypage=' . $nextPage . '" onclick="updatePageState(' . $nextPage . ', ' . $g_girlpage . ')">Next</a> ';
				}

				echo '</div>';
            } else {
                echo "No records found.";
            }
        }
        
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        throw $exception;
    }
    
    dbDisconnect($conn);
}

function getBoyChartDetails1()
{
    global $conn, $boyCharts, $phone;
    
    $cnt = 0;
    
    $conn = dbConnect($conn);
    
    if ($phone == 0) {
        echo "ERROR: Missing phone number";
        return;
    }

    try {
        
        $sql = "SELECT rowid,name, place, birth_date, birth_time FROM birth_chart WHERE phone='$phone' and sex='ஆண்' ORDER BY rowid DESC";
    
        if (($result = mysqli_query($conn, $sql))) {
            $count = mysqli_num_rows($result);

            if ($count > 0) {
				echo '<style>
                       .boy-table, .boy-table th, .boy-table td {
                            border: 1px solid black;
                            border-collapse: collapse;
							background-color:#ccffff;
							font-weight:bold;
							font-size:8px;
							vertical-align:middle;
							padding:5px;
                        }
                      </style>';
                echo '<table id="boy_table" class="boy-table" border="1" style="font-size:8px">';
                echo '<tr><th>Select</th><th>ஆண் பெயர்</th><th>ஆண் வயது</th></tr>';
                
                while ($row = $result->fetch_assoc()) {
					array_push($boyCharts,$row);
					
					$birthDate = new DateTime($row["birth_date"]);
                    $currentDate = new DateTime();
                    $age = $currentDate->diff($birthDate)->y;
					
					if($age <= AGE_LIMIT) continue;
					
                    echo '<tr>';
                    //echo '<td><input type="radio" name="boy_radio" value="' . $row["rowid"] . '" onChange="selectBoy('. $row["rowid"] .');"></td>';
					echo '<td><input type="radio" name="boy_radio" value="' . $row["name"] . '" onChange="selectBoy('. $row["rowid"] .');"></td>';
                    echo '<td>' . $row["name"] . '</td>';
                    echo '<td>' . $age . '</td>';
                    echo '</tr>';
					
					
                }
                
                echo '</table>';
            } else {
                echo "No records found.";
            }
        }
        
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        throw $exception;
    }
    
    dbDisconnect($conn);
}

?>

<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>DSJV App</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/matching_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
    <script src="script.js"></script>
	
	<style>
		.submitDiv {
			display: none;
			justify-content: center;
			align-items: center;
		}

		.submitDiv-content {
			background: white;
			padding: 20px;
			border-radius: 5px;
			text-align: center;
		}
		
		.pagination a {
			margin: 0 5px;
			padding: 5px 10px;
			text-decoration: none;
			border: 1px solid #ccc;
			color: #333;
			display: inline-block;
			border-radius: 5px; /* Optional: for rounded corners */
			background-color: #f9f9f9; /* Background color for the box */
		}

		.pagination a:hover {
			background-color: #ddd;
		}
		
		.pagination a.active {
			background-color: blue;
			color: white;
		}

	</style>
    <script type="text/javascript">
	
	var boyResult=null;
	var girlResult=null;
	
	$(document).ready(function(){
		
		$('#loader').hide();
		
		//Search the girl table
		$("#searchGirl").on("keyup", function() {
			var value = $(this).val().toLowerCase().trim().normalize('NFC');
			$("#girl_table tbody tr").each(function(index) {
				if (index === 0) {
					// Skip the header row
					return;
				}
				var nameCell = $(this).find("td:eq(1)"); // Assuming name is the second column (index 1)
				var nameText = nameCell.text().toLowerCase().normalize('NFC');
				if (nameText.includes(value)) {
					$(this).show(); // Display matching rows
				} else {
					$(this).hide(); // Hide non-matching rows
				}
			});
		});
		
		//Search the boy table
		$("#searchBoy").on("keyup", function() {
			var value = $(this).val().toLowerCase().trim().normalize('NFC');
			$("#boy_table tbody tr").each(function(index) {
				if (index === 0) {
					// Skip the header row
					return;
				}
				var nameCell = $(this).find("td:eq(1)"); // Assuming name is the second column (index 1)
				var nameText = nameCell.text().toLowerCase().normalize('NFC');
				if (nameText.includes(value)) {
					$(this).show(); // Display matching rows
				} else {
					$(this).hide(); // Hide non-matching rows
				}
			});
		});
	});
	
	window.onload = function () {
		// Restore currGirl and girlResult if available
		const savedCurrGirl = sessionStorage.getItem('currGirl');
		const savedGirlResult = sessionStorage.getItem('girlResult');

		if (savedCurrGirl) {
			currGirl = JSON.parse(savedCurrGirl); // Parse the JSON string back to an object
			girlDiv = document.getElementById('girlResult');
			girlDiv.innerHTML = savedGirlResult;

			console.log('Restored currGirl:', currGirl);
		} else {
			console.log('No currGirl data found in sessionStorage.');
		}

		// Restore currBoy and boyResult if available
		const savedCurrBoy = sessionStorage.getItem('currBoy');
		const savedBoyResult = sessionStorage.getItem('boyResult');

		if (savedCurrBoy) {
			currBoy = JSON.parse(savedCurrBoy); // Parse the JSON string back to an object
			boyDiv = document.getElementById('boyResult');
			boyDiv.innerHTML = savedBoyResult;

			console.log('Restored currBoy:', currBoy);
		} else {
			console.log('No currBoy data found in sessionStorage.');
		}
	};


	
	var girlCharts = [];
	var boyCharts = [];
	
	var currGirl = null;
	var currBoy = null;
	
	function selectBoy(rowid) {
		const result = boyCharts.find(element => element.rowid.trim() === String(rowid).trim());
		console.log(result);
		currBoy = result;

		boyDiv = document.getElementById('boyResult');
		if (result) {
			boyDiv.innerHTML = `${result.name}<BR>${result.place.split('|')[0]}<BR>${result.birth_date}<BR>${result.birth_time}`;
			
			// Save currBoy object in sessionStorage
			sessionStorage.setItem('currBoy', JSON.stringify(result));
			
			// Save boyDiv content in sessionStorage (if needed for display)
			sessionStorage.setItem('boyResult', boyDiv.innerHTML);
		} else {
			boyDiv.innerHTML = 'No element found with the given rowid';
		}

		checkSelection();
	}

	
	function selectGirl(rowid) {
		const result = girlCharts.find(element => element.rowid.trim() === String(rowid).trim());
		console.log(result);
		currGirl = result;

		girlDiv = document.getElementById('girlResult');
		if (result) {
			girlDiv.innerHTML = `${result.name}<BR>${result.place.split('|')[0]}<BR>${result.birth_date}<BR>${result.birth_time}`;
			
			// Save currGirl object in sessionStorage
			sessionStorage.setItem('currGirl', JSON.stringify(result));
			
			// Save girlDiv content in sessionStorage (if needed for display)
			sessionStorage.setItem('girlResult', girlDiv.innerHTML);
		} else {
			girlDiv.innerHTML = 'No element found with the given rowid';
		}

		checkSelection();
	}

	
	function checkSelection() {
		var girlData = document.getElementById('girlResult').innerHTML;
		var boyData = document.getElementById('boyResult').innerHTML;
		
		console.log('checkSelection:girlResult:'+girlData);
		console.log('checkSelection:boyResult:'+boyData);
		
		//if (currBoy && currGirl) {
		if (girlData && boyData) {
			document.getElementById('searchGirl').style.display = 'none';
			document.getElementById('searchBoy').style.display = 'none';
			document.getElementById('girl_table').style.display = 'none';
			document.getElementById('boy_table').style.display = 'none';
			document.getElementById('submitDiv').style.display = 'block';
			document.getElementById('girlPagination').style.display = 'none';
			document.getElementById('boyPagination').style.display = 'none';
			
			document.getElementById("nGirlName").value = currGirl.name;
			document.getElementById("nPlace_girl").value = currGirl.place;
			document.getElementById("in_date_girl").value = currGirl.birth_date;
			document.getElementById("in_time_girl").value = currGirl.birth_time;
			document.getElementById("nBoyName").value = currBoy.name;
			document.getElementById("nPlace_boy").value = currBoy.place;
			document.getElementById("in_date_boy").value = currBoy.birth_date;
			document.getElementById("in_time_boy").value = currBoy.birth_time;
			document.getElementById("lang").value = 'Tamil';
		}
	}
	
	document.addEventListener("DOMContentLoaded", function() {
		document.getElementById('cancelBtn').addEventListener('click', function() {
			event.preventDefault(); // Prevent the form from submitting
			document.getElementById('submitDiv').style.display = 'none';
			document.querySelectorAll('input[type="radio"]').forEach(radio => {
				radio.checked = false;
			});
			
			currBoy = null;
			currGirl = null;
			
			boyResult = null;
			girlResult = null;
			
			document.getElementById('searchGirl').style.display = 'block';
			document.getElementById('searchBoy').style.display = 'block';
			document.getElementById('girl_table').style.display = 'table';
			document.getElementById('boy_table').style.display = 'table';
			document.getElementById('girlPagination').style.display = 'block';
			document.getElementById('boyPagination').style.display = 'block';
			
			document.getElementById('boyResult').innerHTML = '';
			document.getElementById('girlResult').innerHTML = '';
			sessionStorage.removeItem('girlResult');
			sessionStorage.removeItem('boyResult');
		});
	});
	
	document.addEventListener("DOMContentLoaded", function() {
		document.getElementById('submitBtn').addEventListener('click', function() {
			event.preventDefault(); // Prevent the form from submitting
			document.getElementById('submitDiv').style.display = 'none';
			document.querySelectorAll('input[type="radio"]').forEach(radio => {
				radio.checked = false;
			});
			
			// Submit the form programmatically
			document.getElementById("matchingForm").submit();
		});
	});
	
	window.addEventListener('pageshow', function(event) {
		if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
			const radios = document.querySelectorAll('input[type="radio"]');
			radios.forEach(radio => {
				radio.checked = false;
			});
			
			window.location.href = "?girlpage=1&boypage=1";
		}
		
		girlResult = sessionStorage.getItem('girlResult');
		
		if(girlResult){
			document.getElementById('girlResult').innerHTML = girlResult;
			var names = girlResult.split("<br>");
			selectRadioButton(1,names[0]);
		}
		
		boyResult = sessionStorage.getItem('boyResult');
		
		if(boyResult){
			document.getElementById('boyResult').innerHTML = boyResult;
			var names = boyResult.split("<br>");
			selectRadioButton(2,names[0]);
		}
		
		checkSelection();
	});
	
	function updatePageState(boypage, girlpage) {
		//history.pushState({boypage: boypage, girlpage: girlpage}, '', '?boypage=' + boypage + '&girlpage=' + girlpage);
	}
	
	window.onpopstate = function(event) {
		if (event.state) {
			// Reset page numbers
			window.location.href = "?girlpage=1&boypage=1";
			sessionStorage.removeItem('girlResult');
			sessionStorage.removeItem('boyResult');
		}
	};
	
	// Function to select the radio button based on the name
	function selectRadioButton(flag,name) {
		if(flag==1){ //girl
			// Select the radio button with the value matching the name
			var radioButton = document.querySelector(`input[type="radio"][name="girl_radio"][value="${name}"]`);
		}
		else{
			var radioButton = document.querySelector(`input[type="radio"][name="boy_radio"][value="${name}"]`);
		}
		
		// Check the radio button if it exists
		if (radioButton) {
			radioButton.checked = true;
		} else {
			console.log('Radio button not found for name:', name);
		}
	}
			
    </script>
</head>

<body>
<div id="loader" class="center"></div>

<div class="title"></div>
	<div class='column-menu'>
	<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			<button class='menu' onClick='window.open("Birth_input.php","_self")'>New Chart</button>
		</div>
		<div class='mobile'>
			<?php include "matchingHistoryMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
	
	<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">திருமண பொருத்தம்</P>
	
	<form id="matchingForm" method="post" action="Matching_Chart.php" target="_self">
		<input type='hidden' id='nPlace_girl' name='nPlace_girl'>
		<input type='hidden' id='nPlace_boy' name='nPlace_boy'>
		<input type='hidden' id='nGirlName' name='nGirlName'>
		<input type='hidden' id='nBoyName' name='nBoyName'>
		<input type='hidden' id='in_date_girl' name='in_date_girl'>
		<input type='hidden' id='in_time_girl' name='in_time_girl'>
		<input type='hidden' id='in_date_boy' name='in_date_boy'>
		<input type='hidden' id='in_time_boy' name='in_time_boy'>
		<input type="hidden" id="lang" name="lang" required />
		
		<div class="search-container" id="search-container">
			<div class="search-box-girl" id="search-box-girl">
				<input type="text" id="searchGirl" autocomplete="off" placeholder="Search for girl...">
				<div id='girlResult'></div>
				<?php getGirlChartDetails($g_girlpage); ?>
				<script>girlCharts = <?php echo json_encode($girlCharts); ?>; </script>
			</div>

			<div class="search-box-boy" id="search-box-boy">
				<input type="text" id="searchBoy" autocomplete="off" placeholder="Search for boy...">
				<div id='boyResult'></div>
				<?php getBoyChartDetails($g_boypage); ?>
				<script>boyCharts = <?php echo json_encode($boyCharts); ?>; </script>
			</div>
		</div>
		
		<div id="submitDiv" class="submitDiv">
			<div class="submitDiv-content">
				<button id="submitBtn" class='actionBtn'>Submit</button>
				<button id="cancelBtn" class='actionBtn'>Cancel</button>
			</div>
		</div>
	</form>
	

	</div>
</body>

</html>