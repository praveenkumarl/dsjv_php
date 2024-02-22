<?php

$usertype = $_SESSION['user_type'];

if(strcmp($usertype,"SUPER")==0){
    echo <<<EOL
   <div class="menu-container">
        <div class="navbar">
			<div class="dropdown1">
				<button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
					  <a href="Jamakol_input.php">ஜாமக்கோள்</a>
					  <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
					  <a href="planet_kaaragam.php">கிரக காரகம்</a>
					  <a href="hora.php">ஓரை</a>
					  <a href="star_thaara_palan.php">தாரா பலன்</a>
					  <a href="new_panchang.php">பஞ்சாங்கம்</a>
					  <a href="maintenance.php">Manage</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<div class='menuHeading'>Manage</div>
        </div>
    </div>
EOL;
}
elseif(strcmp($usertype,"ADMIN")==0){
    echo <<<EOL
   <div class="menu-container">
        <div class="navbar">
			<div class="dropdown1">
				<button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a href="hora.php">ஓரை</a>
				  <a href="star_thaara_palan.php">தாரா பலன்</a>
				  <a href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a href="maintenance.php">Manage</a>
				  <a href="admin.php">Admin</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<div class='menuHeading'>Admin</div>
        </div>
    </div>   
EOL;

}
else{
	echo "Error: Missing menu";
}

