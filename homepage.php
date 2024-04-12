<?php 
require "lib.php";

$DEBUG= (int) isset($_GET['debug']) ? $_GET['debug'] : 0;

$usertype = $_SESSION['user_type'];
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Monthly Panchang</title>
    <meta name="description" content="One page panchang">
	
	<link rel="stylesheet" href="css/main.css">
	<link rel="stylesheet" href="css/chart_style.css">
	<link rel="stylesheet" href="style.css">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	 
 
</head>

<body>

	
<div class="title"></div>

<div style='display:fixed;width=100%;height:90vh;background-color:yellow'>
<?php
displayUserInfo($_SESSION['userinfo']);
displayHomePageButtons($usertype);

?>
</div>
<center><IMG SRC='images/dsjv_title.png' width='70%' style='padding:20px'></center>

</body>

</html>
