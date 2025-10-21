<?php 
require "lib.php";

$conn="";
$phone=isset($_SESSION["phone"]) ? $_SESSION["phone"] : 1;
$curr_idx=0;

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
		
		$sql = "SELECT rowid,phone,g_name,g_place,g_birth_date,g_birth_time,b_name,b_place,b_birth_date,b_birth_time,mark FROM matching_chart WHERE phone='$phone' ORDER BY rowid DESC ";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$chartInfo["rowid"][$cnt] = intval($row["rowid"]);
					$chartInfo["phone"][$cnt] = $row["phone"];
					$chartInfo["g_name"][$cnt]= $row["g_name"];
					$chartInfo["g_place"][$cnt] = $row["g_place"];
					$chartInfo["g_birth_date"][$cnt] = $row["g_birth_date"];
					$chartInfo["g_birth_time"][$cnt] = $row["g_birth_time"];
					$chartInfo["b_name"][$cnt]= $row["b_name"];
					$chartInfo["b_place"][$cnt] = $row["b_place"];
					$chartInfo["b_birth_date"][$cnt] = $row["b_birth_date"];
					$chartInfo["b_birth_time"][$cnt] = $row["b_birth_time"];
					$chartInfo["mark"][$cnt] = $row["mark"];
					
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


getChartDetails();

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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script src="script.js"></script>
    <script type="text/javascript">

	function highlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col2"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col6"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col10"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col11"+rownum).style.backgroundColor="#fff4b3";
		
		document.getElementById("row"+rownum).style.cursor = 'pointer';
	}
	
	function removeHighlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col2"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col6"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col10"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col11"+rownum).style.backgroundColor="#ffffff";
	}
	
	function showChart(rownum)
	{	
		document.getElementById("rowid").value = rownum;
		document.getElementById("nGirlName").value = document.getElementById("col2"+rownum).innerHTML;
		document.getElementById("nPlace_girl").value = document.getElementById("col3"+rownum).innerHTML;
		document.getElementById("in_date_girl").value = document.getElementById("col4"+rownum).innerHTML;
		document.getElementById("in_time_girl").value = document.getElementById("col5"+rownum).innerHTML;
		document.getElementById("nBoyName").value = document.getElementById("col6"+rownum).innerHTML;
		document.getElementById("nPlace_boy").value = document.getElementById("col7"+rownum).innerHTML;
		document.getElementById("in_date_boy").value = document.getElementById("col8"+rownum).innerHTML;
		document.getElementById("in_time_boy").value = document.getElementById("col9"+rownum).innerHTML;
		document.getElementById("mark").value = document.getElementById("col10"+rownum).innerHTML;
		document.getElementById("lang").value = 'Tamil';
		
		//alert(document.getElementById("nPlace_girl").value);
		//alert(document.getElementById("nPlace_boy").value);
		
		document.getElementById("hiddenViewMatchingChart").submit();
		
		$('#loader').show();
	}

	
	function deleteRecord(rownum)
	{
		event.stopPropagation();
		
		let text = "Do you want to delete this chart?\nEither OK or Cancel.";
		  if (confirm(text) == true) {
			document.getElementById("rowid").value = rownum;
			document.getElementById("charttype").value = 'm';

			var url = 'deleteChart.php?rowid=' + rownum + '&charttype=m';
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
	
	function exportMatchingCharts()
	{	
		var url = 'exportChartData.php?charttype=m&phone='+<?php echo $phone; ?>;
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
	<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
			<button class='menu' onClick='window.open("Matching_input.php","_self")'>New Chart</button>
		</div>
		<div class='mobile'>
			<?php include "matchingHistoryMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		<div class="container">
		
			<center><div class="divTable blueTable" id="chart">

				<!-- (A) HIDDEN HTML FORM -->
				<form id="hiddenViewMatchingChart" action="Matching_Chart.php" method="post" style="display:none;">
					<input type='hidden' id='nPlace_girl' name='nPlace_girl'>
					<input type='hidden' id='nPlace_boy' name='nPlace_boy'>
					<input type='hidden' id='nGirlName' name='nGirlName'>
					<input type='hidden' id='nBoyName' name='nBoyName'>
					<input type='hidden' id='in_date_girl' name='in_date_girl'>
					<input type='hidden' id='in_time_girl' name='in_time_girl'>
					<input type='hidden' id='in_date_boy' name='in_date_boy'>
					<input type='hidden' id='in_time_boy' name='in_time_boy'>
					<input type='hidden' id='mark' name='mark'>
					<input type="hidden" id="lang" name="lang" required />
					<input type="hidden" id="rowid" name="rowid" required />
				</form>
				<!-- (A) HIDDEN HTML FORM -->
				<form id="hiddenDeleteChart" action="deleteChart.php" method="post" style="display:none;">
				  <input type="hidden" id="rowid" name="rowid" required />
				  <input type="hidden" id="charttype" name="charttype" required />
				</form>
				
				<BR>
					<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">சேமிக்கப்பட்ட பொருத்தங்கள்</P>
					<!-- <input type='button' class='actionBtn' id='Download' value='download' onClick='exportMatchingCharts();'> -->
				<BR><BR>		
				
<?php
					echo "<div class='divTable blueTable' style='height:40px;'>";
						echo "<div class='divTableRow'>";
								echo "<div class='divChartHistoryHeading'>எண்</div>";
								echo "<div class='divChartHistoryHeading'>பெ.பெயர்</div>";
								echo "<div class='divChartHistoryHeading'>ஆ.பெயர்</div>";
								echo "<div class='divChartHistoryHeading'>மதிப்பெண்</div>";
								echo "<div class='divChartHistoryHeading'>செய்கை</div>";
						echo "</div>";
						
						if(isset($chartInfo['rowid'])){
							for($i=0; $i<sizeof($chartInfo['rowid']); $i++){
								$curr_idx=$i;
								$x=$chartInfo['rowid'][$i];
								
								echo "<div class='divTableRow' id='row$x' onClick='showChart($x)' OnMouseOver='highlightRow($x);' OnMouseOut='removeHighlightRow($x);'>";
									echo "<div class='divChartHistory' id='col1$x'>".$chartInfo['rowid'][$i]."</div>";
									echo "<div class='divChartHistory' id='col2$x'>".$chartInfo['g_name'][$i]."</div>";
									echo "<div class='divHidden' id='col3$x'>".$chartInfo['g_place'][$i]."</div>";
									echo "<div class='divHidden' id='col4$x'>".$chartInfo['g_birth_date'][$i]."</div>";
									echo "<div class='divHidden' id='col5$x'>".$chartInfo['g_birth_time'][$i]."</div>";
									echo "<div class='divChartHistory' id='col6$x'>".$chartInfo['b_name'][$i]."</div>";
									echo "<div class='divHidden' id='col7$x'>".$chartInfo['b_place'][$i]."</div>";
									echo "<div class='divHidden' id='col8$x'>".$chartInfo['b_birth_date'][$i]."</div>";
									echo "<div class='divHidden' id='col9$x'>".$chartInfo['b_birth_time'][$i]."</div>";
									echo "<div class='divChartHistory' id='col10$x'>".$chartInfo['mark'][$i]."</div>";
									echo "<div class='divChartHistory' id='col11$x'><input type='button' id='delButton' onClick='deleteRecord($x)' style='color:black;font-size:10px;font-weight:bold;width:40px;background: url(images/delete.png);background-size:60%;background-repeat: no-repeat;background-position: center;color:#660000;background-color:#ffffff;'></div>";
								echo "</div>";
							}

							
							echo "</div>";
							echo "<BR><P style='font-weight:bold; font-size:12px; text-align:center; width:100%'>மொத்தம் ".sizeof($chartInfo['rowid'])." ஜாதகங்கள்<BR>(திறக்க அதை தொடவும்/கிளிக் செய்யவும்)</P>";
						}
						else{
							echo "</div>";
							echo "<BR><P style='color:#660000;font-weight:bold; font-size:14px; text-align:center; width:100%'>ஜாதகங்கள் இல்லை</P>";
							
						}

?>
				
				<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>
			</div></center>
		</div>
	</div>

</body>

</html>