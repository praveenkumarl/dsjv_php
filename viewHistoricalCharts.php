<?php
require_once __DIR__ . '/webview_only.php';
session_start();
include "db.php";

?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>  
	<script type="text/javascript" src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>  
	
	<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7102258981747108"
     crossorigin="anonymous"></script>


    <script src="script.js"></script>
    <script type="text/javascript">
		$(document).ready(function() {
			$('#fullpage').fullpage({
			  //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
			  anchors: ['firstPage','secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 1000
			});
		});
		
		function moveForward()
		{
			document.getElementById('move').value="1";
			return;
		}
		
		function moveBackward()
		{
			document.getElementById('move').value="-1";
			return;
		}
			
				
	</script>
</head>
<?php

$DEBUG= (int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn;
$rec_count=isset($_POST['move']) ? $_SESSION['record'] : 0;

?>

<body>


<?php


function debug($o)
{
	global $DEBUG;
	
	if($DEBUG==1){
		print '<pre>';
		print_r($o);
		print '</pre>';
	}
}

function startSqlTxn($conn)
{
	/* Start transaction */
	mysqli_begin_transaction($conn);
}

function commitSqlTxn($conn)
{
	/* Start transaction */
	mysqli_commit($conn);
}



function displayChart($in,$out)
{
echo <<<EOL
<div class="container" id="container" style="horizontal-align:center">
	<div class="divTable blueTable" id="chart">
		<div class="overlay1"><br>பெரியநாயகி துணை</br></div>
		<div class="divTableBody">
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[11]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[0]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[2]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[11]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[0]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[1]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[2]<span class='symbol'>&#9791;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[10]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;<span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCenterCell"></div>
					<div class="divTableCell">$in[4]<span class='symbol'>&#9737;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableDummyCell"></div>
					<div class="divTableCell">$in[8]<span class='symbol'>&#9795;<span></div>
					<div class="divTableCell">$in[7]<span class='symbol'>&#9794;<span></div>
					<div class="divTableCell">$in[6]<span class='symbol'>&#9792;<span></div>
					<div class="divTableCell">$in[5]<span class='symbol'>&#9791;<span></div>
					<div class="divTableDummyCell"></div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[8]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[6]</div>
					<div class="divTableDummyCell"></div>
					<div class="divTableOuterCell">$out[5]</div>
			</div>
		</div>	
	</div>
</div>
EOL;
}

function getChartHistory()
{
	global $conn,$php_self,$rec_count;
	$total_count=0;
	
	isset($_SESSION['record']) ? debug("Record=".$_SESSION['record']) : "";
	
	$sql = "SELECT count(*) as count FROM chart_history";
	$result=mysqli_query($conn,$sql);
	$data=mysqli_fetch_assoc($result);
	$total_count = $data['count'];
	
	if(isset($_POST['move'])===true){
		if((int)$_POST['move'] == 1){
			$rec_count = $rec_count + 1;
			
			if($rec_count == $total_count){
				echo "<SCRIPT>alert('No further charts!!!');</SCRIPT>";
				$rec_count = $total_count-1;
			}
		}
		else{
			$rec_count = $rec_count - 1;
			
			if($rec_count < 0){
				echo "<SCRIPT>alert('You reached first record!!!');</SCRIPT>";
				$rec_count = 0;
			}
		}
		
		
	}
	else{
		$rec_count = 0;
	}
	
	$_SESSION['record'] = $rec_count;
	
	
	$sql = "SELECT * FROM chart_history LIMIT ". $_SESSION['record']. "," . "1";
	

	debug($sql);
	
	$result = $conn->query($sql);

	echo "<BR><BR><BR>";

	echo <<<EOL
	<center><div class="divTable blueTable" style="height:70px;width:360px;">
		<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
			<div class="divTableDummyCell" colspan=3 style="border-radius: 20px 20px 0 0;">
				<FORM method='POST' action='viewHistoricalCharts.php' id='history' align='center'>
				<INPUT type='hidden' id='move'- name='move'>		
				</FORM>
				
				<ul id="menu" align="center">
					<li data-menuanchor="firstPage"><a href="javascript: document.getElementById('history').submit();" OnClick='moveBackward();'>Prev</a></li>
					<li data-menuanchor="firstPage"><a href='javascript: window.close();'>Back</a></li>
					<li data-menuanchor="firstPage"><a href="javascript: document.getElementById('history').submit();" OnClick='moveForward();'>Next</a></li>
				</ul>
EOL;
		
	
	if ($result->num_rows > 0) {
	  // output data of each row
	  while($row = $result->fetch_assoc()) {
		echo " Date: ".$row["chart_date"];
		echo " Time: ".$row["chart_time"];
		echo " Place: ".$row["chart_place"];

	echo "<BR>";
	
	echo <<<EOL
			</div>
		</div>
	</div>
EOL;
			
	$out_inside = unserialize($row["chart_inside"]);
	$out_outside = unserialize($row["chart_outside"]);
	$chart_question = $row['chart_question'];
	$chart_analysis = $row['chart_analysis'];
	
	displayChart($out_inside,$out_outside);
	
	echo <<<EOL
	<div class="divTable blueTable" style="height:50px;width:360px;">
		<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
			<div class="divTableDummyCell" colspan=3 style="border-radius:  0 0 20px 20px;">
				<H6 style='font-weight:bold'>Question</H6>
				<H6>$chart_question</H6>
				<H6 style='font-weight:bold'>Analysis</H6>
				<H6>$chart_analysis</H6>
			</div>
		</div>
	</div>
EOL;
			
		  }
		} else {
			$_SESSION['record']=$_SESSION['record']-1;
			echo "<SCRIPT>alert('No charts to display')</SCRIPT>";
		}

}

dbConnect();

startSqlTxn($conn);


echo <<<EOL
	<div class="title"></div>
	<div id="fullpage">
		<div class="section fp-auto-height-responsive" id="section0">
		<div class="intro">

EOL;



getChartHistory();

echo "		</div>";
echo "	</div>";
echo "</div>";

dbDisconnect();
?>

</body>
</html>