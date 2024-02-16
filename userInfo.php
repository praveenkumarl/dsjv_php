<?php  
include "lib.php";

$conn="";
$sql="";
//$user_name=isset($_POST['name']) ? $_POST['name'] : 'unknown';
//$user_name=isset($_POST['name']) ? $_POST['name'] : $_GET['name'];

if(isset($_POST['name'])){
	//$user_name=isset($_POST['name']) ? $_POST['name'] : $_GET['name'];
	$user_name=isset($_POST['name']) ? explode('|',$_POST['name'])[1] : explode('|',$_GET['name'])[1];
	//$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where name='$user_name'";
	$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where (name LIKE '%$user_name%') OR (phone LIKE '%$user_name%')";
}
elseif(isset($_GET['rowid'])){
	$rowid=isset($_POST['rowid']) ? $_POST['rowid'] : $_GET['rowid'];
	$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where rowid='$rowid'";
}

$usertype=isset($_SESSION['usertype']) ? $_SESSION['usertype'] : "";

$userInfo=array();

function getUserDetails()
{
	global $conn,$userInfo, $rowid,$sql;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		//$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where name='$user_name'";
		echo $sql;
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$userInfo["Name"][$cnt] = $row["name"];
					$userInfo["phone"][$cnt] = $row["phone"];
					$userInfo["imei"][$cnt]= $row["imei"];
					$userInfo["last_login"][$cnt] = $row["last_login"];
					$userInfo["user_type"][$cnt] = $row["user_type"];
					$userInfo["registered_date"][$cnt] = $row["registered_date"];
					
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

getUserDetails();

?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro admin page</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7102258981747108" crossorigin="anonymous"></script>

    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 700
            });
        });

		$(document).ready(function(){
    $('.search-box input[type="text"]').on("keyup input", function(){
        /* Get input value on change */
        var inputVal = $(this).val();
        var resultDropdown = $(this).siblings(".result");
	
        if(inputVal.length>2){
            $.get("backend-search_admin.php", {term: inputVal}).done(function(data){
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
	
}

function placeOnBlur() {
	
}

function updateUser(){
	document.getElementById("userUpdate").submit();
}

function deleteUser(in_phone){
	window.location = "./userDelete.php?phone="+in_phone;
}
		
    </script>
</head>

<body onLoad="init();">

	

<div class="title"></div>

<div id="fullpage">
    <div class="section" id="section0">

		<div class="intro">
		
			<div class="container">
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
				<center><div class="divTable blueTable" id="chart" style="width:400px;margin-top:-30px">
				<BR><BR><BR>
						<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">User update</P>
					<BR><BR>
					
					
<?php
						echo "<div class='divTable blueTable' style='height:40px;width:360px;margin-left:10px'>";
						echo "<form name='userUpdate' id='userUpdate' action='userUpdate.php' method='post'>";
							
							for($i=0; $i<sizeof($userInfo['Name']); $i++){
									echo "<div class='divTableRow'>";
										echo "<div class='divPanchangHeading'>Name</div>";
										echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='textbox' name='Name' value='".$userInfo['Name'][$i]."' ></div>";
									echo "</div>";
									echo "<div class='divTableRow'>";
										echo "<div class='divPanchangHeading'>phone</div>";
										echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='textbox' name='phone' value='".$userInfo['phone'][$i]."' ></div>";
										$phone = $userInfo['phone'][$i];
									echo "</div>";
									echo "<div class='divTableRow'>";
										echo "<div class='divPanchangHeading'>imei</div>";
										echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='textbox' name='imei' value='".$userInfo['imei'][$i]."' ></div>";
									echo "</div>";
									echo "<div class='divTableRow'>";
										echo "<div class='divPanchangHeading'>user_type</div>";
										echo "<select name='usertype' id='usertype' style='background-color:white;border:solid 1px black;color:black;font-weight:bold;border-radius:0'>";
											if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],'FREE')==0){
												echo "<option value='FREE' selected>FREE</option>";
											}
											else{
												echo "<option value='FREE'>FREE</option>";
											}
											if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],'PAID')==0){
												echo "<option value='PAID' selected>PAID</option>";
											}
											else{
												echo "<option value='PAID'>PAID</option>";
											}
											if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],'SUPER')==0){
												echo "<option value='SUPER' selected>SUPER</option>";
											}
											else{
												echo "<option value='SUPER'>SUPER</option>";
											}
											if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],'ADMIN')==0){
												echo "<option value='ADMIN' selected>ADMIN</option>";
											}
											else{
												echo "<option value='ADMIN'>ADMIN</option>";
											}
											
										echo "</select>";
									echo "</div>";
									echo "<div class='divTableRow'>";
										echo "<div class='divPanchang'><input type='Button' id='btDelete' value='Delete' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;' onClick='deleteUser($phone);'></div>";
										echo "<div class='divPanchang'><input type='Button' id='btSubmit' value='Submit' style='background-color:gold;color:#660000;font-weight:bold;font-size:10px;' onClick='updateUser();'></div>";
										
									echo "</div>";
									
									
							}
							echo "<form>";
?>
					<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>
				</div></center>
		  </div>
		</div>
	</div>
	
</div>

</body>

</html>