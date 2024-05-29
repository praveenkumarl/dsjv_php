<?php 
session_start();
include_once "lib.php";

$conn="";
$phone=isset($_SESSION["phone"]) ? $_SESSION["phone"] : 1;

$chartInfo=array();

function getChartDetails()
{
	global $conn,$chartInfo, $phone;
	
	$cnt=0;
	
	$conn = dbConnect($conn);
	
	if($phone==0){
		echo "ERROR: Missing phone number";
		return;
	}

	try {
		
		$sql = "SELECT rowid, phone, name, sex, place, birth_date, birth_time, notes FROM birth_chart WHERE phone='$phone' ORDER BY rowid DESC "; 
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$chartInfo["rowid"][$cnt] = intval($row["rowid"]);
					$chartInfo["phone"][$cnt] = $row["phone"];
					$chartInfo["name"][$cnt]= $row["name"];
					$chartInfo["sex"][$cnt] = $row["sex"];
					$chartInfo["place"][$cnt] = $row["place"];
					$chartInfo["birth_date"][$cnt] = $row["birth_date"];
					$chartInfo["birth_time"][$cnt] = $row["birth_time"];
					$chartInfo["notes"][$cnt] = $row["notes"];
					
					$cnt = $cnt + 1;
				  }
			}
		}
		
	} catch (mysqli_sql_exception $exception) {
		mysqli_rollback($conn);

		throw $exception;
	}
	
	dbDisconnect($conn);
}

function displayHistory()
{
	global $chartInfo;
	
	getChartDetails();
	
echo <<<EOL
	<center><div class="divTable blueTable" id="chart">

		<!-- (A) HIDDEN HTML FORM -->
		<form id="hiddenViewBirthChart" action="Birth_Chart.php" method="post" style="display:none;">
		  <input type="hidden" id="place" name="place" required/>
		  <input type="hidden" id="name" name="name" required/>
		  <input type="hidden" id="sex" name="sex" required/>
		  <input type="hidden" id="in_date" name="in_date" required/>
		  <input type="hidden" id="in_time" name="in_time" required/>
		  <input type="hidden" id="lang" name="lang" required/>
		  <input type="hidden" id="rowid" name="rowid" required/>
		  <input type="hidden" id="notes" name="notes" required/>
		</form>
		<!-- (A) HIDDEN HTML FORM -->
		<form id="hiddenDeleteChart" action="deleteChart.php" method="post" style="display:none;">
		  <input type="hidden" id="rowid" name="rowid" required/>
		  <input type="hidden" id="charttype" name="charttype" required/>
		</form>
	
		<BR>
			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">சேமிக்கப்பட்ட ஜாதகங்கள்</P>
			<!-- <input type='button' class='actionBtn' id='Download' value='download' onClick='exportBirthCharts();'> -->
		<BR>
EOL;
		
			echo "<div class='divTable blueTable'>";
				echo "<div class='divTableRow'>";
						echo "<div class='divChartHistoryHeading'>#</div>";
						echo "<div class='divChartHistoryHeading'>பெ</div>";
						echo "<div class='divChartHistoryHeading'>பாலி</div>";
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
							echo "<div class='divChartHistory' id='col3$x'>".$chartInfo['name'][$i]."</div>";
							echo "<div class='divChartHistory' id='col31$x'>".$chartInfo['sex'][$i]."</div>";
							$city = explode('|',$chartInfo['place'][$i])[0];
							echo "<div class='divChartHistory' id='col4$x'>".$city."</div>";
							echo "<div class='divChartHistory' id='col5$x'>".$chartInfo['birth_date'][$i]."</div>";
							echo "<div class='divChartHistory' id='col6$x'>".$chartInfo['birth_time'][$i]."</div>";
							echo "<div class='divHidden' id='col61$x'>".$chartInfo['notes'][$i]."</div>";
							echo "<div class='divChartHistory' id='col7$x'><input type='button' id='delButton' onClick='deleteRecord($x)' style='color:black;font-size:10px;font-weight:bold;width:40px;background: url(images/delete.png);background-size:60%;background-repeat: no-repeat;background-position: center;color:#660000;background-color:#ffffff;'></div>";
						echo "</div>";
					}
					
					echo "</div>";
					echo "<BR><P style='font-weight:bold; font-size:12px; text-align:center; width:100%'>மொத்தம் ".sizeof($chartInfo['rowid'])." ஜாதகங்கள்<BR>(திறக்க அதை தொடவும்/கிளிக் செய்யவும்)</P>";
				}
				else{
					echo "</div>";
					echo "<BR><P style='color:#660000;font-weight:bold; font-size:14px; text-align:center; width:100%'>ஜாதகங்கள் இல்லை</P>";
				}
	echo "</div></center>";
}

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
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
	
 

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
	
	function showChart(rownum,place)
	{	
		//document.getElementById("place").value = document.getElementById("col4"+rownum).innerHTML;
		document.getElementById("place").value = place;
		document.getElementById("name").value = document.getElementById("col3"+rownum).innerHTML;
		document.getElementById("sex").value = document.getElementById("col31"+rownum).innerHTML;
		document.getElementById("in_date").value = document.getElementById("col5"+rownum).innerHTML;
		document.getElementById("in_time").value = document.getElementById("col6"+rownum).innerHTML;
		document.getElementById("lang").value = 'Tamil';
		document.getElementById("rowid").value = rownum;
		document.getElementById("notes").value = document.getElementById("col61"+rownum).innerHTML;
		
		document.getElementById("hiddenViewBirthChart").submit();
		
		$('#loader').show();
	}
	
	function deleteRecord(rownum)
	{
		event.stopPropagation();
		
		let text = "Do you want to delete this chart?\nEither OK or Cancel.";
		  if (confirm(text) == true) {
			document.getElementById("rowid").value = rownum;
			document.getElementById("charttype").value = 'b';

			var url = 'deleteChart.php?rowid=' + rownum + '&charttype=b';
			//window.open(url,'_self');
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
	
	function exportBirthCharts()
	{	
		var url = 'exportChartData.php?charttype=b&phone='+<?php echo $phone; ?>;
		//alert(url);
		window.open(url,'_self');
	}
	
	$(document).ready(function() 
	{
		$('#loader').hide();
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
			<button class='menu' onClick='window.open("Birth_input.php","_self")'>New Chart</button>
		</div>
		<div class='mobile'>
			<?php include "birthHistoryMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		
				<?php displayHistory() ?>					

	</div>
</body>

</html>