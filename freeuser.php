<?php include_once 'lib.php' ;

$userinfo = isset($_SESSION['userinfo']) ? $_SESSION['userinfo'] : ['name'=>'Freeuser','phone'=>'9999999999','user_type'=>'FREE', 'expiry_date'=>'01-01-1970'];

?>

<!doctype html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>DSJV Freeuser msg</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css">
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="css/client_theme_css.php">
 
<body>

<div class="title"></div>
	<div class='column-menu'>
		<?php displayUserInfo($userinfo);?>
			<div class='desktop'>	
				 
			
			</div>
			<div class='mobile'>
				
			</div>
	</div>
	<?php displayRegistrationMsg(); ?>
		
</body>

</html>
