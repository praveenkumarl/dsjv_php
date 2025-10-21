<?php session_start(); 
$place="";
if(isset($_COOKIE["place"])) {
  $place = $_COOKIE["place"];
  
  $_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
  $chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";
  
  $_SESSION["charttime"]="";
  
  if(!isset($_SESSION['phone_no'])){
	  header("location: login.php");
  }
} 
?>
<?php
// Announcement message logic
require_once "db.php";
$announcement_msg = "";
try {
    $conn = dbConnect(0);
    $today = date('Y-m-d');
    $sql = "SELECT message FROM announcement_msgs WHERE is_active=1 AND show_date<=? AND (expiry_date IS NULL OR expiry_date>=?) ORDER BY show_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $today, $today);
    $stmt->execute();
    $stmt->bind_result($msg);
    if ($stmt->fetch()) {
        $announcement_msg = $msg;
    }
    $stmt->close();
    dbDisconnect($conn);
} catch (Exception $e) {
    // fail silently
}
?>

<!doctype html>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>

	<script type="text/javascript" src="scrolloverflow.js"></script>
    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 500
            });
        });
		
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
                // Display the returned data in browser
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
    // Set search input value on click of result item
    $(document).on("click", ".result p", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());
        $(this).parent(".result").empty();
    });
});

function init(){
	document.getElementById('place').value = '<?php echo $place; ?>';
	updateNow();
}

function validateForm(action) {

  let d = document.getElementById("in_date").value;
  let t = document.getElementById("in_time").value;
  
  if (d == "" || t == "") {
		updateNow();
  }
  
  p = document.getElementById("place").value;
  if (p == "") {
    document.getElementById("error").innerHTML = "Place must be filled out";
	document.getElementById('place').style.borderColor = "red";
    return false;
  }
  
  var form = document.getElementById('chartInput')
  
  form.action = action;
  
  form.submit();
}

function updateNow() {
  const date = new Date();
  
  var dd = date.getDate();
  var mm = date.getMonth()+1; 
  var yyyy = date.getFullYear();
  if(dd<10) 
  {
      dd='0'+dd;
  } 

  if(mm<10) 
  {
      mm='0'+mm;
  } 
  var today = yyyy+'-'+mm+'-'+dd;

  var H=date.getHours();
  var M=date.getMinutes();
  var S=date.getSeconds();

  if(H<10) 
  {
      H='0'+H;
  } 

  if(M<10) 
  {
      M='0'+M;
  } 

  if(S<10) 
  {
      S='0'+S;
  } 
  var time = H+':'+M+':'+S;

  document.getElementById('in_date').value= today ; 
  document.getElementById('in_time').value= time ; 
}
		
    </script>
</head>

<body onLoad="init();">

<?php if (!empty($announcement_msg)) { ?>
<!-- DEBUG: Current date and announcement message -->
<div style="background: #fffbe6; color: #660000; font-size: 1em; padding: 8px 16px; border: 1px solid #ffd700; margin: 10px auto; max-width: 600px; text-align: left;">
    <strong>Debug Info:</strong><br>
    Current date (server): <b><?php echo date('Y-m-d'); ?></b><br>
    Announcement message: <b><?php echo htmlspecialchars($announcement_msg); ?></b>
</div>
<div id="announcementOverlay" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.7);color:#fff;display:flex;align-items:center;justify-content:center;z-index:9999;font-size:1.5em;text-align:center;">
    <div style="background:rgba(0,0,0,0.85);padding:30px 40px;border-radius:12px;box-shadow:0 2px 12px #000;max-width:90vw;max-height:80vh;overflow:auto;">
        <?php echo htmlspecialchars($announcement_msg); ?>
    </div>
</div>
<script>
    setTimeout(function() {
        var overlay = document.getElementById('announcementOverlay');
        if (overlay) overlay.style.display = 'none';
    }, 4000); // Show for 4 seconds
</script>
<?php } ?>


<div class="title"></div>
<div id="fullpage">
    <div class="section fp-auto-height-responsive" id="section0">
		<div class="intro">
	
			<div class="container">
			
				<center><div class="divTable blueTable" id="chart" style="width:400px;margin-top:-30px">
					<form id="chartInput" method="post">
						<table style="width:90%; text-align: left">
							<tr>
								<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">Chart input</P></td></tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">*Place :</H5></td>
								<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff" placeholder="Search place..." onfocus=this.value='' /><div class="result"></div></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Date :</H5></td>
								<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray"></td>
							</tr>
							<tr style="background-color: #FFFFFF">
								<td><H5 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Time :</H5></td>
								<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray"></td>
							</tr>
								
							<tr style="background-color: #FFFFFF">
								<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
							</tr>
							<tr>
								<td colspan="2" align="center" style="background-color:white"><H4 style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:15px">Click Submit to create chart</H4></td>
							</tr>
							<tr  style="background-color: #FFFFFF">
								<td colspan="2" align="center">
									<input type="button" value="Jamakol" class="primary short" style="text-align:center; font-weight:bold; background:#660000; font-size:10px" onclick="validateForm('Jamakol_Chart.php')">
									<input type="button" value="Birth Chart" class="primary short" style="text-align:center; font-weight:bold; background:#440000; font-size:10px" onclick="validateForm('Birth_Chart.php')">
									<input type="button" value="BN Naadi Chart" class="primary short" style="text-align:center; font-weight:bold; background:#330000; font-size:10px" onclick="validateForm('BN_Naadi_Chart.php')">
								</td>
							</tr>
						</table>
					</form>
					
					<ul id="menu" style="margin-top:10px">
						<li data-menuanchor="firstPage" class="active"><a href="#firstPage">Create chart</a></li>
						<li data-menuanchor="3rdPage"><a href="viewHistoricalCharts.php" target="_blank">Charts history</a></li>
						<li data-menuanchor="5thPage"><a href="logout.php">Logout</a></li>
					</ul>
				</div></center>
		  </div>
		</div>
	</div>	 
</div>

</body>

</html>