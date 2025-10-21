<?php session_start(); 
include "db.php";
$place=$_SESSION["place"];
$date=$_SESSION["date"];
$time=$_SESSION["time"];
$transit_date=$_SESSION["transit_date"];
$transit_time=$_SESSION["transit_time"];
$_SESSION['error']="";
$_SESSION['message']="";

$inside=$_SESSION["inside"];
$outside=$_SESSION["outside"];
$Planet_degree=$_SESSION["planet_degree"];
$Transit_Planet_degree=$_SESSION["transit_planet_degree"];
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro</title>
    <meta name="description" content="One stop website for astrology">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/BN_chart_style.css?<?php echo date('ymdhis'); ?>">
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
              menu: '#menu',
			  responsiveHeight: 1000
            });
        });

function init(){
}

function validateForm() {

  let name = document.getElementById("name").value;
  
  if (name == "") {
    document.getElementById("error").innerHTML = "Name must be filled out";s
	document.getElementById('name').style.borderColor = "red";
    return false;
  }

}
		
    </script>
</head>

<?php

$DEBUG= 1;//(int) isset($_SESSION['debug']) ? $_SESSION['debug'] : 0;
$conn;
$error="";


function debug($o)
{
	global $DEBUG;
	
	if($DEBUG==1){
		print '<pre>';
		print_r($o);
		print '</pre>';
	}
}

function displayChart($sw_planet,$tr_sw_planet)
{
	global $Planet_degree, $Transit_Planet_degree;
	
	echo <<<EOL
				<div class="container" id="container" style="horizontal-align:center">
					
					<div class="divTable blueTable" id="chart">
						<div class="overlay2"><br>பெரியநாயகி துணை<br><br><br><br><br><br><br><br><br><br><br><br><br><br><P>Brihu Nandhi<P></div>
						<div class="divTableBody">
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[11]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[0]</div>
									<div class="divTableOuterCell">$tr_sw_planet[1]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[2]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell"></div>
									<div class="divTableCell">$sw_planet[11]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[0]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[1]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[2]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[10]</div>
									<div class="divTableCell">$sw_planet[10]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCell">$sw_planet[3]<span class='symbol'>&#9790;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[3]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[9]</div>
									<div class="divTableCell">$sw_planet[9]<span class='symbol'>&#9796;<span></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCenterCell"></div>
									<div class="divTableCell">$sw_planet[4]<span class='symbol'>&#9737;<span></div>
									<div class="divTableOuterCell">$tr_sw_planet[4]</div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell"></div>
									<div class="divTableCell">$sw_planet[8]<span class='symbol'>&#9795;<span></div>
									<div class="divTableCell">$sw_planet[7]<span class='symbol'>&#9794;<span></div>
									<div class="divTableCell">$sw_planet[6]<span class='symbol'>&#9792;<span></div>
									<div class="divTableCell">$sw_planet[5]<span class='symbol'>&#9791;<span></div>
									<div class="divTableOuterCell"></div>
							</div>
							<div class="divTableRow">
									<div class="divTableOuterCell">$tr_sw_planet[8]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[7]</div>
									<div class="divTableOuterCell">$tr_sw_planet[6]</div>
									<div class="divTableOuterCell"></div>
									<div class="divTableOuterCell">$tr_sw_planet[5]</div>
							</div>
						</div>	
					</div>
					<div class="divTable blueTable" style="height:40px;width:360px;">
						<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
							<div class="divTableCell" colspan=3 style="border-radius:  0 0 20px 20px">
								<H6 style='font-weight:bold'>Birth chart (inner)</H6>
								<H6 style='background-color:white;'>$Planet_degree</H6>
							</div>
						</div>
					
					<div class="divTableRow" style="background-color:rgba(0,0,0,0)">
						<div class="divTableOuterCell" colspan=3 style="border-radius:  0 0 20px 20px;">
							<H6 style='font-weight:bold;font-size:12px'>Transit chart (outer)</H6>
							<H6 style='background-color:white'>$Transit_Planet_degree</H6>
							<div style="color:blue;font-size:14px;background-color:white"><a href="saveBNChart.php" id="save" onClick="window.open('saveBNChat.php', 'saveChart', 'toolbar=no, directories=no,location=no, status=yes, menubar=no, resizable=no, scrollbars=yes'); return false">Click to save chart</a></div>
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
						<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px;background:#FFFFFF">*Name</H5></td>
						<td><input type="text" rows="3" cols="50" name="name" id="name" placeholder="Type name here..." style="border:1px solid lightgray;font-size:14px;font-weight:bold;color:black;background:white" autofocus /><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ""; ?></td>
					</tr>
					<tr style="background:none">
						<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px;background:#FFFFFF">*Sex</H5></td>
						<td>
							<select name="sex" id="sex" style="width:auto;font-size:14px;background-color:white;width:100px;height:40px;font-weight:bold;color:black">
							  <option value="English" selected>Male</option>
							  <option value="Tamil">Female</option>
							</select>
								<input type="hidden" name="place" value=<?php echo $place ?> />
								<input type="hidden" name="date" value=<?php echo $date ?> />
								<input type="hidden" name="time" value=<?php echo $time ?> />
								<input type="hidden" name="transit_date" value=<?php echo $transit_date ?> />
								<input type="hidden" name="transit_time" value=<?php echo $transit_time ?> />
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


</body>
</html>