<?php 
require_once "lib.php";

$place="";
$name="";
if(isset($_COOKIE["place"])) {
  $place = $_COOKIE["place"];
}
  
$_SESSION['debug']=isset($_GET['debug']) ? (int)$_GET['debug'] : 0;
$chart_data = isset($_SESSION['chart']) ? $_SESSION['chart'] : "";

$_SESSION["charttime"]="";

if(isset($_GET["phone"])) {
	$_SESSION['phone']=$_GET["phone"];
	
	$cookie_name = "phone";
	$cookie_value = $_GET["phone"];
	setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
} 

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
		
		$sql = "SELECT rowid, phone, name, place, birth_date, birth_time FROM birth_chart WHERE phone='$phone' ORDER BY rowid DESC "; 
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$chartInfo["rowid"][$cnt] = intval($row["rowid"]);
					$chartInfo["phone"][$cnt] = $row["phone"];
					$chartInfo["name"][$cnt]= $row["name"];
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
	
	dbDisconnect($conn);
}


function displayHistory()
{
	global $chartInfo;
	
	getChartDetails();
	
echo <<<EOL
	<div class="divTable blueTable" id="chart" style="width:400px;">

		<!-- (A) HIDDEN HTML FORM -->
		<form id="hiddenViewBirthChart" action="Birth_Chart.php" method="post" style="display:none;">
		  <input type="hidden" id="place" name="place" required/>
		  <input type="hidden" id="name" name="name" required/>
		  <input type="hidden" id="in_date" name="in_date" required/>
		  <input type="hidden" id="in_time" name="in_time" required/>
		  <input type="hidden" id="lang" name="lang" required/>
		</form>
		<!-- (A) HIDDEN HTML FORM -->
		<form id="hiddenDeleteChart" action="deleteChart.php" method="post" style="display:none;">
		  <input type="hidden" id="rowid" name="rowid" required/>
		  <input type="hidden" id="charttype" name="charttype" required/>
		</form>
	
		<BR>
			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">Saved Birth charts</P>
		<BR><BR>	
EOL;
		
			echo "<div class='divTable blueTable' style='height:40px;width:360px;margin-left:10px'>";
				echo "<div class='divTableRow'>";
						echo "<div class='divPanchangHeading'>Rowid</div>";
						echo "<div class='divPanchangHeading'>Phone</div>";
						echo "<div class='divPanchangHeading'>Name</div>";
						echo "<div class='divPanchangHeading'>Place</div>";
						echo "<div class='divPanchangHeading'>Birth Date</div>";
						echo "<div class='divPanchangHeading'>Birth Time</div>";
						echo "<div class='divPanchangHeading'>Action</div>";
				echo "</div>";
				
				if(isset($chartInfo['rowid'])){
					for($i=0; $i<sizeof($chartInfo['rowid']); $i++){
						$x=$chartInfo['rowid'][$i];
						
						echo "<div class='divTableRow' id='row$x' onClick='showChart($x)' OnMouseOver='highlightRow($x);' OnMouseOut='removeHighlightRow($x);'>";
							echo "<div class='divPanchang' id='col1$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['rowid'][$i]."</div>";
							echo "<div class='divPanchang' id='col2$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['phone'][$i]."</div>";
							echo "<div class='divPanchang' id='col3$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['name'][$i]."</div>";
							echo "<div class='divPanchang' id='col4$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['place'][$i]."</div>";
							echo "<div class='divPanchang' id='col5$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['birth_date'][$i]."</div>";
							echo "<div class='divPanchang' id='col6$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'>".$chartInfo['birth_time'][$i]."</div>";
							echo "<div class='divPanchang' id='col7$x' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='button' id='delButton' onClick='deleteRecord($x)' style='color:black;font-size:10px;font-weight:bold;width:40px;background: url(images/delete.png);background-size:60%;background-repeat: no-repeat;background-position: center;color:#660000;background-color:#ffffff;'></div>";
						echo "</div>";
					}
					
					echo "</div>";
					echo "<BR><P style='color:#660000;font-weight:bold; font-size:14px; text-align:center; width:100%'>Total ".sizeof($chartInfo['rowid'])." Charts (Click on it to open)</P>";
				}
				else{
					echo "</div>";
					echo "<BR><P style='color:#660000;font-weight:bold; font-size:14px; text-align:center; width:100%'>Total 0 Charts (Click on it to open)</P>";
				}
	echo "</div>";
}


?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/birth_chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="script.js"></script>
    <script type="text/javascript">
		
		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
        if(inputVal.length>2){
            $.get("backend-search.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
               resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
    });
    
// Set search input value on click of result item
	//$(document).on("click", ".result p", function(){
	$(document).on("click", ".result div", function(){
    //$(document).on("change", ".result select", function(){
        $(this).parents(".search-box").find('input[type="text"]').val($(this).text());		
		//$(this).parents(".search-box").find('input[type="text"]').val($('option:selected', this).val());
		
        $(this).parent(".result").empty();
		$(this).parent("#lplace").hide();
    });
});

function init(){
	document.getElementById('place').value = '<?php echo $place; ?>'; 
	updateNow();
}

function validateForm() {

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
  
  n = document.getElementById("name").value;
  if (n == "") {
    document.getElementById("error").innerHTML = "Name must be filled out";
	document.getElementById('name').style.borderColor = "red";
    return false;
  }
}

function placeOnBlur() {
	let place = document.getElementById("place").value;
	
	if(place==""){
		document.getElementById('place').value = '<?php echo $place; ?>';
	}
}

function nameOnBlur() {
	let name = document.getElementById("name").value;
	
	if(name==""){
		document.getElementById('name').value = '<?php echo $name; ?>';
	}
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


	function highlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col2"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col3"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col4"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col5"+rownum).style.backgroundColor="#fff4b3";
		document.getElementById("col6"+rownum).style.backgroundColor="#fff4b3";
		
		document.getElementById("row"+rownum).style.cursor = 'pointer';
	}
	
	function removeHighlightRow(rownum)
	{
		document.getElementById("col1"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col2"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col3"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col4"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col5"+rownum).style.backgroundColor="#ffffff";
		document.getElementById("col6"+rownum).style.backgroundColor="#ffffff";
	}
	
	function showChart(rownum)
	{	
		document.getElementById("place").value = document.getElementById("col4"+rownum).innerHTML;
		document.getElementById("name").value = document.getElementById("col3"+rownum).innerHTML;
		document.getElementById("in_date").value = document.getElementById("col5"+rownum).innerHTML;
		document.getElementById("in_time").value = document.getElementById("col6"+rownum).innerHTML;
		document.getElementById("lang").value = 'Tamil';
	
		document.getElementById("chartInput").submit();
	}
	
	function deleteRecord(rownum)
	{
		event.stopPropagation();
		
		let text = "Do you want to delete this chart?\nEither OK or Cancel.";
		  if (confirm(text) == true) {
			document.getElementById("rowid").value = rownum;
			document.getElementById("charttype").value = 'b';

			var url = 'deleteChart.php?rowid=' + rownum + '&charttype=b';
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
	
	function openLoader() {
		document.getElementById("loader").style.display = "block";
	  }
	
		
    </script>
</head>

<body onLoad="init()">
<div id="loader" class="center" style="display: none;"></div>

<div class="title"></div>
<div class='column-menu'>
<?php displayUserInfo($_SESSION['userinfo']);?>
	<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
		<button class='menu' onClick='window.open("birthChartDisplay.php","_self")'>Birth Chart history</button>
	</div>
	<div class='mobile'>
		<?php include "birthInputMenu_mobile.php"; ?>
	</div>
</div>
<div class='column-body'>
	<center>
		<div class="divTable blueTable" id="chart" style="width:360px;">
			<form name="chartInput" id="chartInput" action="Birth_Chart.php" method="post" onsubmit="openLoader()">
				<table style="width:100%; text-align: left">
					<tr>
						<td colspan=2><P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">பிறப்பு ஜாதகம்</P></td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable">*பெயர்</div></td>
						<td><input type="text" name="name" id="name" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Enter name..." onfocus=this.value='' onBlur="nameOnBlur();" />
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable">*பாலினம்</div></td>
						<td>
							<select name="sex" id="sex" style="width:auto;font-size:16px;">
								<option value="ஆண்" selected>ஆண்</option>
								  <option value="பெண்">பெண்</option>
								  <option value="மூன்றாம்">மூன்றாம்</option>
							</select>
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable">*பி.இடம்</div></td>
						<td><div class="search-box"><input type="text" name="place" id="place" autocomplete="off" style="background:#ffffff;font-weight:bold" placeholder="Search place..." onfocus=this.value='' onBlur="placeOnBlur();" /><div class="result"></div>
						</td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable">*பி.தேதி</div></td>
						<td><input type="date" id="in_date" name="in_date" style="border:1px solid lightgray;font-weight:bold"></td>
					</tr>
					<tr style="background-color: #FFFFFF">
						<td><div class="divBirthInputLable">*பி.நேரம்</div></td>
						<td><input type="time" id="in_time" name="in_time" step="1" style="border:1px solid lightgray;font-weight:bold"></td>
					</tr>
					<!-- <tr style="background-color: #FFFFFF">
						<td>
							<label for="lang" style="color:#660000; font-family: 'Source Sans Pro', Helvetica, sans-serif; font-weight:bold; font-size:20px">Choose Language:</label>
						</td>
						<td>
							<select name="lang" id="lang" style="width:auto;font-size:20px;">
								<option value="Tamil" selected>Tamil</option>
								  <option value="English">English</option>
							</select>
						</td>
					</tr> -->
					<tr style="background-color: #FFFFFF">
						<td colspan="2" align="center"><div id="error" style="color:red;"></div></td>
					</tr>
					<tr  style="background-color: #FFFFFF">
						<td colspan="2" align="center"><input type="submit" value="Submit" class="primary wide" style="text-align:center; font-weight:bold; background:var(--menu-background)" onclick="return validateForm();"></td>
					</tr>
					
				</table>
			</form>	
		</div>
		<?php //displayHistory() ?>
		
		</div>
	</center>
</div>
</body>

</html>