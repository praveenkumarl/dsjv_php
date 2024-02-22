<?php

$usertype = $_SESSION['user_type'];

if(strcmp($usertype,"FREE")==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
		  
				<div class="dropdown1-content">
				  <a href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a href="hora.php">ஓரை</a>
				  <a href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a href="new_panchang.php">பஞ்சாங்கம்</a>
				</div>
          </div> 
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;

}
elseif(strcmp($usertype,"PAID")==0){
    
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
				<div class="dropdown1-content">
				  <a href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a href="Matching_input.php">திருமண பொருத்தம்</div</a>
				  <a href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a href="hora.php">ஓரை</a>
				  <a href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a href="new_panchang.php">பஞ்சாங்கம்</a>
				</div>
          </div> 
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
    
}
elseif(strcmp($usertype,"SUPER")==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
			  <button class="dropbtn" style='padding:10px; width:auto'>
				  <i class="fa fa-caret-square-o-down fa-2x" ></i>
				</button>
		  
				<div class="dropdown1-content">
				  <a href="Jamakol_input.php">ஜாமக்கோள்</a>
				  <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
				  <a href="Matching_input.php">திருமண பொருத்தம்</div</a>
				  <a href="planet_kaaragam.php">கிரக காரகம்</a>
				  <a href="hora.php">ஓரை</a>
				  <a href="star_thaara_palan.php">தாரா பலன்</d</a>
				  <a href="new_panchang.php">பஞ்சாங்கம்</a>
				  <a href="maintenance.php">Manage</a>
				</div>
          </div> 
			<a href="javascript:location.reload();"><i class="fa fa-refresh fa-2x"></i></a>
			<img src='images/dsjv_title.png' style='padding:10px;width:40%' />
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,"ADMIN")==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <div class="dropdown1">
		  <button class="dropbtn" style='padding:10px; width:auto'>
              <i class="fa fa-caret-square-o-down fa-2x" ></i>
            </button>
            
            <div class="dropdown1-content">
              <a href="Jamakol_input.php">ஜாமக்கோள்</a>
              <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
              <a href="Matching_input.php">திருமண பொருத்தம்</div</a>
              <a href="planet_kaaragam.php">கிரக காரகம்</a>
              <a href="hora.php">ஓரை</a>
              <a href="star_thaara_palan.php">தாரா பலன்</d</a>
              <a href="new_panchang.php">பஞ்சாங்கம்</a>
			  <a href="admin.php">ADMIN</a>
			  <a href="maintenance.php">Manage</a>
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


/*
if(strcmp($usertype,"FREE")==0){
	echo <<<EOL
	<div class="MENU-container">
		<div class='MENUBtn' onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</div>
		<div class='MENUBtn' onClick="window.open('planet_kaaragam.php','_self')">கிரக காரகம்</div>
		<div class='MENUBtn' onClick="window.open('raasi_kaaragam.php','_self')">ராசி காரகம்</div>
		<div class='MENUBtn' onClick="window.open('hora.php','_self')">ஓரை</div>
		<div class='MENUBtn' onClick="window.open('star_thaara_palan.php','_self')">தாரா பலன்</div>
	</div>
EOL;
}
elseif(strcmp($usertype,"PAID")==0){
	echo <<<EOL
	<div class="MENU-container">
		<div class='MENUBtn' onClick="window.open('Jamakol_input.php','_self')">ஜாமக்கோள்</div>
		<div class='MENUBtn' onClick="window.open('Birth_input.php','_self')">பிறப்பு ஜாதகம்</div>
		<div class='MENUBtn' onClick="window.open('planet_kaaragam.php','_self')">கிரக காரகம்</div>
		<div class='MENUBtn' onClick="window.open('raasi_kaaragam.php','_self')">ராசி காரகம்</div>
		<div class='MENUBtn' onClick="window.open('hora.php','_self')">ஓரை</div>
		<div class='MENUBtn' onClick="window.open('star_thaara_palan.php','_self')">தாரா பலன்</div>
		<div class='MENUBtn' onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</div>
	</div>
EOL;
}
elseif(strcmp($usertype,"SUPER")==0){
	echo <<<EOL
	<div class="MENU-container">
		<div class='MENUBtn' onClick="window.open('Jamakol_input.php','_self')">ஜாமக்கோள்</div>
		<div class='MENUBtn' onClick="window.open('Birth_input.php','_self')">பிறப்பு ஜாதகம்</div>
		<div class='MENUBtn' onClick="window.open('Matching_input.php','_self')">திருமண பொருத்தம்</div>
		<div class='MENUBtn' onClick="window.open('planet_kaaragam.php','_self')">கிரக காரகம்</div>
		<div class='MENUBtn' onClick="window.open('raasi_kaaragam.php','_self')">ராசி காரகம்</div>
		<div class='MENUBtn' onClick="window.open('hora.php','_self')">ஓரை</div>
		<div class='MENUBtn' onClick="window.open('star_thaara_palan.php','_self')">தாரா பலன்</div>
		<div class='MENUBtn' onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</div>
	</div>
EOL;
}
elseif(strcmp($usertype,"ADMIN")==0){
/*	echo <<<EOL
	<div class="MENU-container">
		<div class='MENUBtn' onClick="window.open('Jamakol_input.php','_self')">ஜாமக்கோள்</div>
		<div class='MENUBtn' onClick="window.open('Birth_input.php','_self')">பிறப்பு ஜாதகம்</div>
		<div class='MENUBtn' onClick="window.open('Matching_input.php','_self')">திருமண பொருத்தம்</div>
		<div class='MENUBtn' onClick="window.open('planet_kaaragam.php','_self')">கிரக காரகம்</div>
		<div class='MENUBtn' onClick="window.open('raasi_kaaragam.php','_self')">ராசி காரகம்</div>
		<div class='MENUBtn' onClick="window.open('hora.php','_self')">ஓரை</div>
		<div class='MENUBtn' onClick="window.open('star_thaara_palan.php','_self')">தாரா பலன்</div>
		<div class='MENUBtn' onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</div>
		<div class='MENUBtn' onClick="window.open('admin.php','_self')">ADMIN</div>
	</div>
EOL; 

    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
          <a href="index.php">HOME</a>
          <a href="javascript:location.reload();">REFRESH</a>
          <div class="dropdown1">
            <button class="dropbtn">More 
              <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown1-content">
              <a href="Jamakol_input.php">ஜாமக்கோள்</a>
              <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
              <a href="Matching_input.php">திருமண பொருத்தம்</div</a>
              <a href="planet_kaaragam.php">கிரக காரகம்</a>
              <a href="hora.php">ஓரை</a>
              <a href="star_thaara_palan.php">தாரா பலன்</d</a>
              <a href="new_panchang.php">பஞ்சாங்கம்</a>
              <a href="admin.php">ADMIN</a>
            </div>
          </div> 
        </div>
        </div>
EOL;

}
else{
	echo "Error: Missing MENU";
}

*/


?>