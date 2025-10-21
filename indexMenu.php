<?php
include_once 'lib.php';

$usertype = $_SESSION['user_type'];


if(strcmp($usertype,FREE)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</button>
	<button class="menu" onClick='window.open("planet_kaaragam.php","_self")'>கிரக காரகம்</button>
	<button class="menu" onClick='window.open("raasi_kaaragam.php","_self")'>ராசி காரகம்</button>
	<button class="menu" onClick='window.open("hora.php","_self")'>Horai/ஹோரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,JAMAKOL)==0){
	echo <<<EOL
	
	<button class="menu" onClick='window.open("planet_kaaragam.php","_self")'>கிரக காரகம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,JAMA_MATCH)==0){
    
}
elseif(strcmp($usertype,MATCHING)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Matching_input.php","_self")'>திருமண பொருத்தம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,BIRTH)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,BIRTHMATCH)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("Matching_input.php","_self")'>திருமண பொருத்தம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
if(strcmp($usertype,BIRTHPANCH)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</button>
	<button class="menu" onClick='window.open("hora.php","_self")'>ஓரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,PANCHANG)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("hora.php","_self")'>ஓரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,PAID)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Jamakol_input.php","_self")'>ஜாமக்கோல்</button>
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("planet_kaaragam.php","_self")'>கிரக காரகம்</button>
	<button class="menu" onClick='window.open("raasi_kaaragam.php","_self")'>ராசி காரகம்</button>
	<button class="menu" onClick='window.open("hora.php","_self")'>ஓரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,SUPER)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Jamakol_input.php","_self")'>ஜாமக்கோல்</button>
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("Matching_input.php","_self")'>திருமண பொருத்தம்</button>
	<button class="menu" onClick='window.open("planet_kaaragam.php","_self")'>கிரக காரகம்</button>
	<button class="menu" onClick='window.open("raasi_kaaragam.php","_self")'>ராசி காரகம்</button>
	<button class="menu" onClick='window.open("hora.php","_self")'>ஓரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
elseif(strcmp($usertype,ADMIN)==0){
	echo <<<EOL
	<button class="menu" onClick='window.open("Jamakol_input.php","_self")'>ஜாமக்கோல்</button>
	<button class="menu" onClick='window.open("Birth_input.php","_self")'>பிறப்பு ஜாதகம்</button>
	<button class="menu" onClick='window.open("Matching_input.php","_self")'>திருமண பொருத்தம்</button>
	<button class="menu" onClick='window.open("planet_kaaragam.php","_self")'>கிரக காரகம்</button>
	<button class="menu" onClick='window.open("raasi_kaaragam.php","_self")'>ராசி காரகம்</button>
	<button class="menu" onClick='window.open("hora.php","_self")'>ஓரை</button>
	<button class="menu" onClick='window.open("star_thaara_palan.php","_self")'>தாரா பலன்</button>
	<button class="menu" onClick='window.open("new_panchang.php","_self")'>பஞ்சாங்கம்</button>
	<button class="menu" onClick='window.open("admin.php","_self")'>ADMIN</button>
	<button class="menu" onClick='window.open("new_logout.php","_self")'>Logout</button>
EOL;
}
else{
	echo "Error: Missing menu";
}


?>