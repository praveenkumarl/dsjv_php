<?php
include_once "lib.php";

$usertype = $_SESSION['user_type'];
$_SESSION['menu_clicked'] = true;

if(strcmp($usertype,FREE)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  
          </div> 
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;

}
elseif(strcmp($usertype,PRASANNAM)==0){
    
    echo <<<EOL
    <div class="menu-container">
        <div class="navbar">
		  <div class='menuHeading'>பிரசன்னம்</div>
        </div>
     </div>
EOL;
}
elseif(strcmp($usertype,PAID)==0){
    
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
				  <a class='glow-button' href="numerology.php">எண் கணிதம்</div</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				</div>
          </div> 
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,JAMAKOL)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  
          </div> 
			
			<a href="Jamakol_input.php"><i class="fa fa-plus-square fa-2x" aria-hidden="true"></i></a>
			<a href="jamaChartDisplay.php"><i class="fa fa-history fa-2x"></i></a>
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<div class='menuHeading'>ஜாமக்கோள்</div>
			<a href="planet_kaaragam.php" style='font-size:30px;'><img src='images/solar_system.png'></a>
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
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
				</div>
			</div>
			<a href="planet_kaaragam.php" style='font-size:30px;'><img src='images/solar_system.png'></a>
				<div class='menuHeading'>ஜாமக்கோள்</div>
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,MATCHING)==0){
    echo <<<EOL
	 <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</div</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				</div>
          </div> 
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
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
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				</div>
          </div> 
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>

EOL;
}
elseif(strcmp($usertype,BIRTH)==0){
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
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,PANCHANG)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
					<a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
					<a class='glow-button' href="hora.php">ஓரை</a>
					<a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  
				  <a href="planet_kaaragam.php">கிரக காரகம்</a>
				</div>
          </div> 
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,SUPER)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
		  
				<div class="dropdown1-content">
				  <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</div</a>
				  <a class='glow-button' href="numerology.php">எண் கணிதம்</div</a>
				  <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a class='glow-button' href="hora.php">ஓரை</a>
				  <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
				  <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
				  <a class='glow-button' href="maintenance.php">Manage</a>
				</div>
          </div> 
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,ADMIN)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
		  <button class="dropbtn" style='padding:10px; width:auto'>
              <i class="fa fa-caret-square-o-down fa-2x" ></i>
            </button>
            
            <div class="dropdown1-content">
              <a class='glow-button' href="Jamakol_input.php">ஜாமக்கோள்</a>
              <a class='glow-button' href="Birth_input.php">பிறப்பு ஜாதகம்</a>
              <a class='glow-button' href="Matching_input.php">திருமண பொருத்தம்</div</a>
			  <a class='glow-button' href="numerology.php">எண் கணிதம்</div</a>
              <a class='glow-button' href="planet_kaaragam.php">கிரக காரகம்</a>
              <a class='glow-button' href="hora.php">ஓரை</a>
              <a class='glow-button' href="star_thaara_palan.php">தாரா பலன்</a>
              <a class='glow-button' href="new_panchang.php">பஞ்சாங்கம்</a>
			  <a class='glow-button' href="prasannam_home.php">பிரசன்னம்</a>
			  <a class='glow-button' href="admin.php">ADMIN</a>
			  <a class='glow-button' href="maintenance.php">Manage</a>
            </div>
          </div> 
		<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
		<a href="admin.php"><i class="fa fa-cog fa-2x"></i></a>
		<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>

EOL;

}
else{
	echo "Error: Missing MENU";
}




?>