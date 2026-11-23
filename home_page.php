<!DOCTYPE html>
<?php
include_once 'lib.php';

$super_admin_image="";
$id="0";

if(isset($_SESSION['userinfo']) && strcmp($_SESSION['userinfo']['user_type'],ADMIN)==0){
	$super_admin_image = 'images/admin.jpg';
	$id="51";
}
else if(isset($_SESSION['userinfo']) && strcmp($_SESSION['userinfo']['user_type'],SUPER)==0){
	$super_admin_image = 'images/super.jpg';
	$id="5";
}
else{
	$super_admin_image = 'images/dsjv_logo_home.jpg';
	$id="0";
}
?>

<html lang="en">
<head>
    <!-- <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <title>My Company</title>
	<link rel="stylesheet" href="css/client_theme_css.php">
    <style>
        body {
            margin: 0;
            padding: 0;
            #display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        table {
            width: 100%;
            height: 85%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        td {
            padding: 0;
            text-align: center;
        }
        .logo {
            max-width: -webkit-fill-available;
            margin: 0 auto;
            display: block;
        }
        .image-cell {
            position: relative;
			background-color: var(--client-surface, white);
			align-content: center;
			padding: 5px;
        }
        .image {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
    </style>
	
	<script>
	
	function toggle3DEffect(id) {
		const button = document.getElementById(id);
		
		if(id=="0"){
			return;
		}
		
		button.classList.toggle('active');
		switch(id){
			case "0":
				break;
			case "1":
				window.location.href = 'Jamakol_input.php';
				break;
			case "2":
				window.location.href = 'new_panchang.php';
				break;
			case "3":
				window.location.href = 'numerology.php';
				break;
			case "4":
				window.location.href = 'Matching_input.php';
				break;
			case "5":
				window.location.href = 'Birth_input.php';
				break;
			case "51":
				window.location.href = 'admin.php';
				break;
			case "6":
				window.location.href = 'star_thaara_palan.php';
				break;
			case "7":
				window.location.href = 'hora.php';
				break;
			case "8":
				window.location.href = 'prasannam_home.php';
				break;
			case "9":
				window.location.href = 'planet_kaaragam.php';
				break;
			default:
				break;
		}
	}

	
	</script>
</head>
<body>
    <!-- <table>
        <tr>
            <td class="image-cell"><button class="home-button" onclick="toggle3DEffect()"  style="background-image: url('images/birth.jpg')"></td>
            <td class="image-cell"><button class="home-button" onclick="toggle3DEffect()"  style="background-image: url('images/panchang.jpg')"></td>
            <td class="image-cell"><button class="home-button" onclick="toggle3DEffect()"  style="background-image: url('images/matching.jpg')"></td>
        </tr>
		<tr>
            <td class="image-cell"><img src="images/matching.jpg" alt="Image 1" class="image"></td>
            <td class="image-cell"><img src="images/dsjv_logo_home.jpg" alt="DSJV" class="logo"></td>
            <td class="image-cell"><img src="images/thaara_palan.jpg" alt="Image 2" class="image"></td>
        </tr>
        <tr>
            <td class="image-cell"><img src="images/hora.jpg" alt="Image 3" class="image"></td>
            <td class="image-cell"><img src="images/prasannam.jpg" alt="Image 4" class="image"></td>
			<td class="image-cell"><img src="images/planet_kaaragam.jpg" alt="Image 2" class="image"></td>
        </tr>
    </table>
	-->
<?php
echo<<<EOL
	<table>
        <tr>
            <td class="image-cell"><button id=1 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/jamakol.jpg')"> </td>
            <td class="image-cell"><button id=2 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/panchang.jpg')"></td>
            <td class="image-cell"><button id=3 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/numerology.jpg')"></td>
        </tr>
		<tr>
            <td class="image-cell"><button id=4 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/matching.jpg')"></td>
			<!-- <td class="image-cell"><button id='$id' class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('$super_admin_image')"></td> -->
			<td class="image-cell"><button id=5 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/birth.jpg')"></td>
            <td class="image-cell"><button id=6 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/thaara_palan.jpg')"></td>
			
            
        </tr>
        <tr>
            <td class="image-cell"><button id=7 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/hora.jpg')"></td>
            <td class="image-cell"><button id=8 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/prasannam.jpg')"></td>
            <td class="image-cell"><button id=9 class="home-button" onclick="toggle3DEffect(this.id)"  style="background-image: url('images/planet_kaaragam.jpg')"></td>
        </tr>
    </table>
EOL;

//echo '<span style="display: inline-block; height: 200px;"></span>';
//echo '<P style="color:#660000;font-weight:bold; font-size:20px; text-align:center">தற்போதய  ஜாமக்கோள்</P>';
//include "live_jamakol_chart.php";

?>
</body>
</html>

