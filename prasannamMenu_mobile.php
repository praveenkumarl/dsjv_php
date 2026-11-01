<?php
include_once "lib.php";

$usertype = $_SESSION['user_type'];
$_SESSION['menu_clicked'] = true;

if(strcmp($usertype,FREE)==0){
    echo <<<EOL
    <div class="menu-container">
        
        </div>
     </div>
EOL;

}
elseif(strcmp($usertype,PRASANNAM)==0){
    
    echo <<<EOL
    <div class="menu-container">
        <div class="navbar">
      <div class='menuHeading'>Prasannam</div>
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
              <a class='glow-button' href="Jamakol_input.php">Jamakol</a>
              <a class='glow-button' href="Birth_input.php">Birth Horoscope</a>
              <a class='glow-button' href="planet_kaaragam.php">Planet Indicator</a>
			  <a class='glow-button' href="hora.php">Horai</a>
              <a class='glow-button' href="star_thaara_palan.php">Star Result</a>
			  <a class='glow-button' href="new_panchang.php">Panchangam</a>
            </div>
          </div> 
		  <a href="index.php"><i class="fa fa-home fa-2x"></i></a>
      <div class='menuHeading'>Prasannam</div>
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
              <a class='glow-button' href="Jamakol_input.php">Jamakol</a>
              <a class='glow-button' href="Birth_input.php">Birth Horoscope</a>
              <a class='glow-button' href="Matching_input.php">Marriage Compatibility</a>
              <a class='glow-button' href="planet_kaaragam.php">Planet Indicator</a>
			  <a class='glow-button' href="hora.php">Horai</a>
              <a class='glow-button' href="star_thaara_palan.php">Star Result</a>
			  <a class='glow-button' href="new_panchang.php">Panchangam</a>
			  <a class='glow-button' href="maintenance.php">Manage</a>
              
            </div>
          </div> 
		  <a href="index.php"><i class="fa fa-home fa-2x"></i></a>
      <div class='menuHeading'>Prasannam</div>
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
              <a class='glow-button' href="Jamakol_input.php">Jamakol</a>
              <a class='glow-button' href="Birth_input.php">Birth Horoscope</a>
              <a class='glow-button' href="Matching_input.php">Marriage Compatibility</a>
              <a class='glow-button' href="planet_kaaragam.php">Planet Indicator</a>
			  <a class='glow-button' href="hora.php">Horai</a>
              <a class='glow-button' href="star_thaara_palan.php">Star Result</a>
			  <a class='glow-button' href="new_panchang.php">Panchangam</a>
              <a class='glow-button' href="admin.php">ADMIN</a>
			  <a class='glow-button' href="maintenance.php">Manage</a>
            </div>
          </div> 
		  <a href="index.php"><i class="fa fa-home fa-2x"></i></a>
      <div class='menuHeading'>Prasannam</div>
        </div>
        </div>
EOL;
}
else{
	echo "Error: Missing menu";
}




?>