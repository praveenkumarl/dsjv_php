<?php
include_once 'lib.php';
$usertype = $_SESSION['user_type'];

if(strcmp($usertype,FREE)==0){
    echo <<<EOL
    <div class="menu-container">
        <div class="navbar">
         
         
        </div>
        </div>
EOL;

}
elseif(strcmp($usertype,MATCHING)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,BIRTHMATCH)==0){
    echo <<<EOL
	 <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				</div>
          </div> 
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
		</div>
	</div>

EOL;
}
elseif(strcmp($usertype,JAMA_MATCH)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
			<div class="dropdown1">
				<button class="dropbtn" style="padding:10px; width:auto">
				  <i class="fa fa-caret-square-o-down fa-2x"></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
				</div>
			</div>
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<a href="planet_kaaragam.php" style='font-size:30px;'><img src='images/solar_system.png'></a>
				<div class='menuHeading'>ஜாமக்கோள்</div>
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,PAID)==0){
    
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
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
        </div> 
    </div>
EOL;
    
}
elseif(strcmp($usertype,SUPER)==0){
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
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				  <a class='glow-button' href="maintenance.php">Manage</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
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
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				  <a class='glow-button' href="maintenance.php">Manage</a>
				</div>
			</div> 
			<a href="index.php"><i class="fa fa-home fa-2x"></i></a>
			<a href="Matching_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<div class='menuHeading'>பொருத்தம்</div>
        </div> 
    </div>
EOL;

}
else{
	echo "Error: Missing menu";
}



?>