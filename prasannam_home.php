<?php session_start();
require "lib.php";

$key = isset($_COOKIE['key']) ? $_COOKIE['key'] : "";

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
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">	

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
	
    <script type="text/javascript">
	
	function init(){
		
	}
			
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

<div class='column-menu'>
<?php displayUserInfo($_SESSION['userinfo']);?>
	<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')">Home</button>
	</div>
	<div class='mobile'>
		<?php include "prasannamMenu_mobile.php"; ?>
	</div>
</div>
<div class='column-body'>
	<div class="grid-container">
	  <div class="grid-item" onClick="window.open('prasannams.php?type=1','_self')" style="cursor:pointer;">லக்ன பிரசன்னம்</div>
	  <div class="grid-item" onClick="window.open('prasannams.php?type=2','_self')" style="cursor:pointer;">கடிகார பிரசன்னம்</div>
	  <div class="grid-item" onClick="window.open('prasannams.php?type=3','_self')" style="cursor:pointer;">ராசி பிரசன்னம்</div>
	  <div class="grid-item" onClick="window.open('prasannams.php?type=4','_self')" style="cursor:pointer;">பாத பிரசன்னம்</div>
	  <div class="grid-item" onClick="window.open('prasannams.php?type=5','_self')" style="cursor:pointer;">ஹோரை பிரசன்னம்</div>
	</div>
	
	
</div>	 
	

</body>
</html>