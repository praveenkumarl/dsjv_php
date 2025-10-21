<?php
include_once 'lib.php';
$usertype = $_SESSION['user_type'];
$_SESSION['menu_clicked'] = true;

if(strcmp($usertype,FREE)==0){
    echo <<<EOL
    <div class="menu-container">
        <div class="navbar">
         
         
        </div>
        </div>
EOL;

}
elseif(strcmp($usertype,BIRTH)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
			<div class='menuHeading'>HELP</div>
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,MATCHING)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
			<div class='menuHeading'>HELP</div>
		</div>
	</div>
EOL;
}
elseif(strcmp($usertype,BIRTHMATCH)==0){
    echo <<<EOL
	 <div class="MENU-container">
        <div class="navbar">
			<div class='menuHeading'>HELP</div>
		</div>
	</div>

EOL;
}
elseif(strcmp($usertype,PAID)==0){
    
	echo <<<EOL
		<div class="MENU-container">
			<div class="navbar">
				<div class='menuHeading'>HELP</div>
			</div>
		</div>
EOL;
    
}
elseif(strcmp($usertype,SUPER)==0){
	echo <<<EOL
		<div class="MENU-container">
			<div class="navbar">
				<div class='menuHeading'>HELP</div>
			</div>
		</div>
EOL;
}
elseif(strcmp($usertype,ADMIN)==0){
    echo <<<EOL
    <div class="MENU-container">
        <div class="navbar">
			<div class='menuHeading'>HELP</div>
		</div>
	</div>
EOL;

}
else{
	echo "Error: Missing menu";
}



?>