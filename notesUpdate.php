<?php  
include "lib.php";

$g_notes = isset($_SESSION['notes']) ? $_SESSION['notes'] : "";

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

    <script src="script.js"></script>
    <script type="text/javascript">

    </script>
</head>

<body>
					
<?php

if(isset($_POST['update'])){
	$_SESSION['notes'] = $_POST['notes'];
	echo 'inside if'.$_POST['notes'];
}
else{
	echo 'inside else';
	echo <<<EOL
		<form id='updateForm' mothod='post' action="notesUpdate.php">
			<table>
				<tr>
					<td>
						<textarea rows='5' name="notes" value='$g_notes'></textarea>
						<input type='hidden' name="update" value='$g_notes' />
					</td>
				</tr>
				<tr>
					<td>
						<center><input type='submit' class='actionBtn' name ='submit' value='Update'></input></center>
					</td>
				</tr>
			</table>
		</form>	
EOL;
}

echo isset($_SESSION['notes']) ? $_SESSION['notes'] : "null";

?>

</body>

</html>