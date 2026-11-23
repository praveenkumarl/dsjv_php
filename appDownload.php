<?php
require_once __DIR__ . '/webview_only.php';
session_start();

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
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7102258981747108"
     crossorigin="anonymous"></script>
	 
    <script src="script.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#fullpage').fullpage({
              //sectionsColor: ['#1bbc9b', '#4BBFC3', '#7BAABE', 'whitesmoke', '#ccddff'],
			  sectionsColor: ['#FF8360', '#FFe6df', '#FFe6df', 'whitesmoke', '#ccddff'],
              anchors: ['firstPage', 'secondPage', '3rdPage', '4thpage', 'lastPage'],
			  responsiveHeight: 800
            });
        });

function init(){

}
		
    </script>
</head>

<body onLoad="init();">

	
<div class="title"></div>

<div id="fullpage">
    <div class="section" id="section0">

		<div class="intro">
			<div class="body">
				<BR><div>App is outdated, click below link to download latest version</div>
				<div class="bodytext"><center><a href="https://drive.google.com/uc?export=download&id=1NuGcn-A7AsAAF5E3ZoAoPWpXxM6JX5KV" style="color:blue">New app link</a></center></div>
			</div>
		</div>
	</div>	 
</div>

</body>

</html>


