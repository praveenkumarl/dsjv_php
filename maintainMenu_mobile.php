<?php
include_once "lib.php";

$usertype = $_SESSION['user_type'];
$_SESSION['menu_clicked'] = true;

if(strcmp($usertype,SUPER)==0){
    echo <<<EOL
	<div class="menu-container">
        <div class="navbar">
			<div class="dropdown1">
				<button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a class='glow-button' href="numerology.php">எண் கணிதம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				  <a class='glow-button' href="new_registration_mobile_admin.php">User Registration</a>
				  <a class='glow-button' href="addPlace.php">Add Place</a>
				  <a class='glow-button' href="maintenance.php?section=theme">Theme settings</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<a href="new_registration_mobile_admin.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>Manage</div>
        </div>
    </div>    
EOL;

}
elseif(strcmp($usertype,ADMIN)==0){
    echo <<<EOL
	<div class="menu-container">
        <div class="navbar">
			<div class="dropdown1">
				<button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a class='glow-button' href="numerology.php">எண் கணிதம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				  <a class='glow-button' href="admin.php">Admin</a>
				  <a class='glow-button' href="addPlace.php">Add Place</a>
				  <a class='glow-button' href="new_registration_mobile_admin.php">User Registration</a>
				  <a class='glow-button' href="maintenance.php?section=theme">Theme settings</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a> 
			<a href="addPlace.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>Manage</div>
        </div>
    </div>    
EOL;

}
else{
	echo "Error: Missing menu";
}

?>