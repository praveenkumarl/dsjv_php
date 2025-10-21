<!doctype html>
<?php 
session_start(); 
include "db.php";
include "lib.php";
$phone=$_SESSION["phone"];
$place=$_SESSION["place"];
$date=$_SESSION["date"];
$time=$_SESSION["time"];
$_SESSION['error']="";
$_SESSION['message']="";

$inside=$_SESSION["inside"];
$outside=$_SESSION["outside"];

$DEBUG= 1;//(int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn;
$error="";

?>


<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7102258981747108"
     crossorigin="anonymous"></script>


    <script src="script.js"></script>
    <script type="text/javascript">
	
	   $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FFe6df', '#FFcdbf', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 800
            });
        });

function init(){
}

function validateForm() {

  let d = document.getElementById("question").value;
  
  if (d == "") {
    document.getElementById("error").innerHTML = "Question must be filled out";
	document.getElementById('question').style.borderColor = "red";
    return false;
  }

}
		
    </script>
</head>

<?php


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
					<div class="divTableCenterCell1"></div>
					<div class="divTableCenterCell2"></div>
					<div class="divTableCell">$in[3]<span class='symbol'>&#9790;<span></div>
					<div class="divTableOuterCell">$out[3]</div>
			</div>
			<div class="divTableRow">
					<div class="divTableOuterCell">$out[9]</div>
					<div class="divTableCell">$in[9]<span class='symbol'>&#9796;<span></div>
					<div class="divTableCenterCell3"></div>
					<div class="divTableCenterCell4"></div>
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

?>

<body onLoad="init();">

<div class="title"></div>
<div id="fullpage">
	<div class="section fp-auto-height-responsive" id="section0">
		
		<div class="intro">
		<BR>
		<center>
		 <div class="container" style="horizontal-align:left" >
				<form name="chartSave" action="saveChartToDB.php" method="post">
				<table style="width:100%; margin-left:1px; text-align: left" >
				<tr style="background:none"><td colspan=2><H5 style="color:#660000;font-weight:bold; font-size:20px; text-align:center;background:#FFFFFF">Save Chart</H5></td></tr>
					<tr style="background:none">
						<td colspan=2 align="center"><div style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px;background:#FFFFFF"><?php echo $place."&emsp;".$date."&emsp;".$time ?></div>
							<BR>
							<?php displayChart($inside,$outside); ?>
						</td>
					</tr>
					<tr style="background:none">
						<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px;background:#FFFFFF">*Question</H5></td>
						<td><textarea rows="3" cols="100" id="question" name="question" id="question" placeholder="Type question here..." style="border:1px solid lightgray;font-size:14px;font-weight:bold;color:black;background:white"><?php echo isset($_SESSION['question']) ? $_SESSION['question'] : ""; ?></textarea></td>
					</tr>
					<tr style="background:none">
						<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px;background:#FFFFFF">Analysis</H5></td>
						<td><textarea rows="5" cols="100" id="analysis" name="analysis" id="analysis" placeholder="Type analysis here..." style="border:1px solid lightgray;font-size:14px;font-weight:bold;color:black;background:white"><?php echo isset($_SESSION['analysis']) ? $_SESSION['analysis'] : ""; ?></textarea>
							<input type="hidden" name="place" value=<?php echo $place; ?> />
							<input type="hidden" name="date" value=<?php echo $date; ?> />
							<input type="hidden" name="time" value=<?php echo $time; ?> />
							</td>
					</tr>
					<tr style="background:none">
						<td colspan="2" align="center"><input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:#660000" onclick="return validateForm();"></td>
					</tr>
					
					<tr style="background:none">
						<td colspan='2' align='center'><div id='error' style='color:red;'></div></td>
					</tr>
					<tr style="background:none">
						<td colspan='2' align='center'><div id='message' style='color:red;'><?php echo $_SESSION['message']; ?></div></td>
					</tr>
				</table>

				</form>
		  </div>
		  </center>
		</div>	
		</div>
</div>


</body>
</html>