<?php 
session_start();
require "lib.php";

$conn="";
$phone=isset($_SESSION["phone"]) ? $_SESSION["phone"] : 1;
const REC_PER_PAGE = 10;

$chartInfo=array();
$total_pages=0;
$total_records=0;

function getChartDetails($page = 1, $records_per_page = REC_PER_PAGE)
{
    global $conn, $chartInfo, $phone, $total_pages,$total_records;

    $cnt = 0;
    $conn = dbConnect($conn);

    if ($phone == 1) {
        echo "ERROR: Missing phone number";
        return;
    }

    $offset = ($page - 1) * $records_per_page;

    try {
        $sql = "SELECT rowid, phone, question, analysis, place, birth_date, birth_time 
                FROM jama_chart 
                WHERE phone='$phone' 
                ORDER BY rowid DESC 
                LIMIT $offset, $records_per_page";

        if (($result = mysqli_query($conn, $sql))) {
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                while ($row = $result->fetch_assoc()) {
                    $chartInfo["rowid"][$cnt] = intval($row["rowid"]);
                    $chartInfo["phone"][$cnt] = $row["phone"];
                    $chartInfo["question"][$cnt] = $row["question"];
                    $chartInfo["analysis"][$cnt] = $row["analysis"];
                    $chartInfo["place"][$cnt] = $row["place"];
                    $chartInfo["birth_date"][$cnt] = $row["birth_date"];
                    $chartInfo["birth_time"][$cnt] = $row["birth_time"];

                    $cnt = $cnt + 1;
                }
            }
        }
    } catch (mysqli_sql_exception $exception) {
        mysqli_rollback($conn);
        throw $exception;
    }

	// Calculate total pages
	$total_records_query = "SELECT COUNT(*) FROM jama_chart WHERE phone='$phone'";
	$total_records_result = mysqli_query($conn, $total_records_query);
	$total_records = mysqli_fetch_array($total_records_result)[0];
	$total_pages = ceil($total_records / $records_per_page);
	
    dbDisconnect($conn);
}


$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
getChartDetails($page);

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro chart history</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="script.js"></script>
    <script type="text/javascript">

	function highlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col3"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col31"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col4"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col5"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col6"+rownum).style.backgroundColor="#fff4b3";
		
		document.getElementById("row"+rownum).style.cursor = 'pointer';
	}
	
	function removeHighlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col3"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col31"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col4"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col5"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col6"+rownum).style.backgroundColor="#ffffff";
	}
	
	function showChart(rownum, place)
	{	
		//document.getElementById("place").value = document.getElementById("col4"+rownum).innerHTML;
		document.getElementById("place").value = place;
		document.getElementById("question").value = document.getElementById("col3"+rownum).innerHTML;
		document.getElementById("analysis").value = document.getElementById("col31"+rownum).innerHTML;
		document.getElementById("in_date").value = document.getElementById("col5"+rownum).innerHTML;
		document.getElementById("in_time").value = document.getElementById("col6"+rownum).innerHTML;
		document.getElementById("lang").value = 'Tamil';
		document.getElementById("in_degree").value = 1;
		document.getElementById("rowid").value = rownum;
		
		document.getElementById("hiddenViewJamaChart").submit();
		
		$('#loader').show();
	}
	
	function deleteRecord(rownum)
	{
		event.stopPropagation();
		
		let text = "Do you want to delete this chart?\nEither OK or Cancel.";
		  if (confirm(text) == true) {
			document.getElementById("rowid").value = rownum;
			document.getElementById("charttype").value = 'j';

			var url = 'deleteChart.php?rowid=' + rownum + '&charttype=j';
			//alert(url);
			//window.open(url);
			window.location.replace(url);
			window.refresh();
			//document.getElementById("hiddenDeleteChart").submit();
		  } else {
			return;
		  }
	}
	
	$('.confirmation').on('click', function () {
        return confirm('Are you sure?');
    });
	
	function exportJamaCharts()
	{	
		var url = 'exportChartData.php?charttype=j&phone='+<?php echo $phone; ?>;
		//alert(url);
		window.open(url,'_self');
	}
		
	$(document).ready(function() 
	{
		$('#loader').hide();
			
		 $("#name-filter").on("keyup", function() {
			var value = $(this).val().toLowerCase().trim().normalize('NFC');
			$("#chart_table .divTableRow").each(function(index) {
				if (index === 0) {
					// Skip the header row
					return;
				}
				var nameCell = $(this).find(".divChartHistory:eq(1)"); // Assuming name is the second column (index 1)
				var nameText = nameCell.text().toLowerCase().normalize('NFC');
				if (nameText.includes(value)) {
					$(this).show(); // Display matching rows
				} else {
					$(this).hide(); // Hide non-matching rows
				}
			});
		});
	});
		
    </script>
</head>

<body>
<div id="loader" class="center"></div>

<div class="title"></div>

	<div class='column-menu'>
		<?php displayUserInfo($_SESSION['userinfo']);?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			<button class='menu' onClick='window.open("Jamakol_input.php","_self")'>New Chart</button>
		</div>
		<div class='mobile'>
				<?php include "jamaHistoryMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
			
			<center>

				<!-- (A) HIDDEN HTML FORM -->
				<form id="hiddenViewJamaChart" action="Jamakol_Chart.php" method="post" style="display:none;">
				  <input type="hidden" id="place" name="place" required/>
				  <input type="hidden" id="question" name="question" required/>
				   <input type="hidden" id="analysis" name="analysis" required/>
				  <input type="hidden" id="in_date" name="in_date" required/>
				  <input type="hidden" id="in_time" name="in_time" required/>
				  <input type="hidden" id="lang" name="lang" required/>
				  <input type="hidden" id="in_degree" name="in_degree" required/>
				  <input type="hidden" id="rowid" name="rowid" required/>
				</form>
				<!-- (A) HIDDEN HTML FORM -->
				<form id="hiddenDeleteChart" action="deleteChart.php" method="post" style="display:none;">
				  <input type="hidden" id="rowid" name="rowid" required/>
				  <input type="hidden" id="charttype" name="charttype" required/>
				</form>
		
					<P style="color:#660000;font-weight:bold; font-size:14px; text-align:center">சேமிக்கப்பட்ட ஜாமக்கோள் ஜாதகங்கள்</P>
					 <!-- <input type='button' class='actionBtn' id='Download' value='download' onClick='exportJamaCharts();'>  -->
					
				
<?php
					echo '<center><BR><div class="divPanchangHeading">Search by Name: <input type="text" id="name-filter" placeholder="Enter name" style="display:inline;font-size: 14px; width: auto;"></div></center>';
					echo '<Br>';
					
					// Display pagination buttons
					echo "<div class='pagination'>";
					if ($page > 1) {
						echo "<a href='?page=" . ($page - 1) . "'><button class='actionBtn'>Prev</button></a>";
					}
					if ($page < $total_pages) {
						echo " <a href='?page=" . ($page + 1) . "'><button class='actionBtn'>Next</button></a>";
					}
					echo "</div>";
					
					echo "<div class='divTable blueTable' id='chart_table' style='height:40px;'>";
						echo "<div class='divTableRow'>";
								echo "<div class='divChartHistoryHeading'>#</div>";
								echo "<div class='divChartHistoryHeading'>கே</div>";
								echo "<div class='divChartHistoryHeading'>கணி</div>";
								echo "<div class='divChartHistoryHeading'>இட</div>";
								echo "<div class='divChartHistoryHeading'>தே</div>";
								echo "<div class='divChartHistoryHeading'>நே</div>";
								echo "<div class='divChartHistoryHeading'>*</div>";
						echo "</div>";
						
						if(isset($chartInfo['rowid'])){
							for($i=0; $i<sizeof($chartInfo['rowid']); $i++){
								$x=$chartInfo['rowid'][$i];
								$place = $chartInfo['place'][$i];
								
								echo "<div class='divTableRow' id='row$x' onClick='showChart($x,\"$place\")' OnMouseOver='highlightRow($x);' OnMouseOut='removeHighlightRow($x);'>";
									echo "<div class='divChartHistory' id='col1$x'>".$chartInfo['rowid'][$i]."</div>";
									echo "<div class='divChartHistory' id='col3$x'>".$chartInfo['question'][$i]."</div>";
									echo "<div class='divChartHistory' id='col31$x'>".$chartInfo['analysis'][$i]."</div>";
									$city = explode('|',$chartInfo['place'][$i])[0];
									echo "<div class='divChartHistory' id='col4$x'>".$city."</div>";
									echo "<div class='divChartHistory' id='col5$x'>".$chartInfo['birth_date'][$i]."</div>";
									echo "<div class='divChartHistory' id='col6$x'>".$chartInfo['birth_time'][$i]."</div>";
									echo "<div class='divChartHistory' id='col7$x'><input type='button' id='delButton' onClick='deleteRecord($x)' style='color:black;font-size:10px;font-weight:bold;width:40px;background: url(images/delete.png);background-size:60%;background-repeat: no-repeat;background-position: center;color:#660000;background-color:#ffffff;'></div>";
								echo "</div>";
							}


							echo "</div>";
							echo "<BR><P style='color:#660000;font-weight:bold; font-size:12px; text-align:center'>மொத்தம் ".$total_records." ஜாதகங்கள்<BR>(திறக்க அதை கிளிக் செய்யவும்)</P>";
						}
						else{
							echo "</div>";
							echo "<BR><P style='color:#660000;font-weight:bold; font-size:14px; text-align:center; width:100%'>ஜாதகங்கள் இல்லை</P>";
						}
?>
				
				<BR><BR>
			</center>
	</div>

</body>

</html>