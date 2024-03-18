<?php 
include_once "lib.php";

$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : "9999999999";

//dummy user info
$userInfo=['name'=>'dummy','phone'=>'9999999999','user_type'=>'FREE'];

if(isMobile() and isset($_GET['key'])){
	$userInfo = getUserInfo_mobile($_GET['key']);
	
	if(!empty($userInfo)){
		$_SESSION['logged']=true;
		$_SESSION['key']=$_GET['key'];
		$_SESSION['userinfo']=$userInfo;
		
	}
	else{ // if no user exists in databse force the user to register first
		echo "alert('INFO: Please register to access');";
		header('Location: new_registration_mobile.php?key='.$_GET['key']);
	}
}
elseif(isMobile() and isset($_SESSION['key'])){
	$_SESSION['logged']=true;
	$userInfo = getUserInfo_mobile($_SESSION['key']);
	$_SESSION['userinfo']=$userInfo;
}
elseif(!strcmp($phone,"9999999999")==0){
	$_SESSION['logged']=true;
	$userInfo = getUserInfo_web($phone);
	$_SESSION['userinfo']=$userInfo;
}
elseif(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'admin.php')>0){ //this else is to allow login from admin page for other users (Act As)
	$userInfo = getUserInfo_mobile($_GET['key']);
	
	if(!empty($userInfo)){
		$_SESSION['logged']=true;
		$_SESSION['key']=$_GET['key'];
		$_SESSION['userinfo']=$userInfo;
		
	}
	else{ // if no user exists in databse force the user to register first
		echo "alert('INFO: Please register to access');";
		header('Location: new_registration_mobile.php?key='.$_GET['key']);
	}
}
else{
	$_SESSION['logged']=true;
	$_SESSION['userinfo']=$userInfo;
}

$_SESSION['user_type'] = isset($userInfo['user_type']) ? $userInfo['user_type'] : "FREE";
$_SESSION['phone'] = isset($userInfo['phone']) ? $userInfo['phone'] : 1;
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>PK Astro Jamakol</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
	<!-- <div id="google_translate_element"></div>
	<script type="text/javascript">
	function googleTranslateElementInit() {
	  new google.translate.TranslateElement({pageLanguage: 'ta', includedLanguages: 'en,hi,te,kn,ml', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
	}
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->
    <script src="script.js"></script>
    <script type="text/javascript">
 

	function init(){

	}
		
	document.onreadystatechange = function() {
		
		if(document.getElementById('dummy') != null){	//chart not loaded
			document.querySelector("#loader").style.display = "none";
			document.getElementById("msg").style.visibility = "hidden";
			document.getElementById("intro2").style.display = "block";
		}
		else{
			//document.querySelector("body").style.visibility = "hidden";
			document.querySelector("#loader").style.visibility = "visible";
			document.getElementById("msg").style.visibility = "visible";
			document.getElementById("intro2").style.display = "none";
		}
		
		/*if (document.readyState != "complete") {
			document.querySelector("body").style.visibility = "hidden";
			document.querySelector("#loader").style.visibility = "visible";
		} else {
			document.querySelector("#loader").style.display = "none";
			document.querySelector("body").style.visibility = "visible";	
			document.getElementById("msg").style.visibility = "none";
		}*/
	};
		
    </script>
</head>

<body onLoad="init();">
<div id="loader" class="center"></div>

<div class="title"></div>

		<div class="column-menu">
			<span class="notranslate">
				<div class='desktop'>
					<?php
						if(!isLogged()){
							echo <<<EOL
							<button class='menu' onClick='window.open("new_loginPage.php","_self")'>Login</button>
EOL;
						}
						else
						{
							displayUserInfo($_SESSION['userinfo']);
							include "indexMenu.php";
						}
				echo "</div>";
				echo "<div class='mobile'>";
						if(!isLogged()){
							$key = $_GET['key'];
							echo <<<EOL
							<div class="dropdown">
								<button class="dropbtn"><a href="new_registration_mobile.php?key=$key" target="_self">Registration</a></button>
							</div>
EOL;
						}
						else
						{
							include "indexMenu_mobile.php";
						}
				echo "</div>";
					?>
			</span>
		</div>
		<div class="column-body">
		
		<?php
		if(!isFreeUser($_SESSION['user_type'])){
			echo <<<EOL
			
			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">தற்போதய  ஜாமக்கோள்</P>
			<DIV id='msg'>Please wait, jamakol chart is loading</DIV>
			<div class='current-jama' id='current-jama' onClick='window.open("Jamakol_input.php","_self");' style='cursor: pointer;margin-top:-10px'>
EOL;
				include "live_jamakol_chart.php";
				
			echo <<<EOL
				</div>
EOL;
		}
		else{
			echo <<<EOL
			
			<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">தற்போதய  ஜாமக்கோள்</P>
			<DIV id='msg'>Please wait, jamakol chart is loading</DIV>
			<div class='current-jama' id='current-jama' style='cursor: pointer;'>
EOL;

				echo '<div style="margin-top:auto">';
					include "live_jamakol_chart.php";
				echo '</div>';
			echo '</div>';

		}
		?>

		</div>
	</div>
		

</body>

</html>