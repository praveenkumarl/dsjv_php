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
	$sql = "SELECT rowid,name,phone,imei,last_login,user_type,registered_date from user where (name LIKE '%$user_name%') OR (phone LIKE '%$user_name%')";
}
elseif(isset($_GET['rowid'])){
	$rowid=isset($_POST['rowid']) ? $_POST['rowid'] : $_GET['rowid'];
	$sql = "SELECT rowid,name,phone,imei,last_login,user_type,registered_date from user where rowid='$rowid'";
}

$usertype=isset($_SESSION['usertype']) ? $_SESSION['usertype'] : "";

$userInfo=array();

function getUserDetails()
{
	global $conn,$userInfo, $rowid,$sql, $old_usertype;
	
	$cnt=0;
	
	$conn = dbConnect($conn);

	try {
		
		//$sql = "SELECT name,phone,imei,last_login,user_type,registered_date from user where name='$user_name'";
	
		if(($result = mysqli_query($conn, $sql))){
			$count = mysqli_num_rows($result);

			if($count>0){
				
				 while($row = $result->fetch_assoc()) {
					$userInfo["rowid"][$cnt] = $row["rowid"];
					$userInfo["Name"][$cnt] = $row["name"];
					$userInfo["phone"][$cnt] = $row["phone"];
					$userInfo["imei"][$cnt]= $row["imei"];
					$userInfo["last_login"][$cnt] = $row["last_login"];
					$userInfo["user_type"][$cnt] = $row["user_type"];
					$userInfo["registered_date"][$cnt] = $row["registered_date"];
					
					$old_usertype = $row["user_type"];
					
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
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">    

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
 

    <script src="script.js"></script>
    <script type="text/javascript">

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

function goBack(){
	window.open('maintenance.php','_self');
	return false;
}

function deleteUser(in_phone){
	window.location = "./userDelete.php?phone="+in_phone;
}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>
		
	<div class="column-menu">
	<?php displayUserInfo($_SESSION['userinfo']); ?>
		<div class='desktop'>
			<button class="menu" onClick="window.open('index.php','_self')">Home</button>
		</div>
		<div class='mobile'>
			<?php include "maintainMenu_mobile.php"; ?>
		</div>
	</div>
	<div class='column-body'>
		
<?php
	echo "<center>";
	echo "<P style='color:#660000;font-weight:bold; font-size:20px; text-align:center'>User update</P>";
	echo "<div class='divTable blueTable' style='height:40px;width:360px;margin-left:10px'>";
	echo "<form name='userUpdate' id='userUpdate' action='userUpdate.php' method='post'>";
		for($i=0; $i<sizeof($userInfo['Name']); $i++){
			$id = $userInfo['rowid'][$i];
				echo "<div class='divTableRow'>";
					echo "<div class='divPanchangHeading'>ID</div>";
					echo "<div class='divPanchang' style='color:black;font-size:10px;font-weight:bold;width:40px;'><input type='textbox' name='Name' value='".$userInfo['rowid'][$i]."' readonly></div>";
				echo "</div>";
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
					echo "<div class='divPanchangHeading'>User Type</div>";
					echo "<select name='usertype' id='usertype' style='background-color:white;border:solid 1px black;color:black;font-weight:bold;border-radius:0'>";
						foreach($usertype_arr as $utype){
							if(strcmp($_SESSION['user_type'],SUPER)==0 && strcmp($utype,ADMIN)==0){
								continue;
							}
							
							if(isset($userInfo['user_type'][$i]) && strcmp($userInfo['user_type'][$i],$utype)==0){
								echo "<option value='$utype' selected>$utype</option>";
							}
							else{
								echo "<option value='$utype'>$utype</option>";
							}
						}
						
					echo "</select>";
				echo "</div>";
				echo "<div class='divTableRow'>";
					if(strcmp($usertype,'ADMIN')==0){
						echo "<div class='divPanchang'><input type='Button' class='actionBtn' id='btDelete' value='Delete' style='background-color:red !important' onClick='deleteUser($phone);'></div>";
					}
					
					if(strcmp($old_usertype,'ADMIN')==0 && !strcmp($_SESSION['user_type'],"ADMIN")==0){
						echo "<div class='divPanchang'><input type='Button' class='actionBtn' id='btBack' value='Back' OnClick='goBack();'></div>";
						echo "<div class='divPanchang'><input type='Button' class='actionBtn' id='btSubmit' value='Submit' disabled></div>";
					}
					else{
						echo "<div class='divPanchang'><input type='Button' class='actionBtn' id='btBack' value='Back' OnClick='goBack();'></div>";
						echo "<div class='divPanchang'><input type='Button' class='actionBtn' id='btSubmit' value='Submit' OnClick='updateUser();'></div>";
					}
				echo "</div>";
				
				
		}
		
		echo '<input type="hidden" id="old_usertype" name="old_usertype" value="'.$old_usertype.'" required/>';
		echo '<input type="hidden" id="id" name="id" value="'.$id.'" required/>';
		echo "<form>";
?>
	</div></center>

</body>

</html>