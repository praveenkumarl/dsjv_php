<?php

$usertype = $_SESSION['user_type'];

if(strcmp($usertype,"ADMIN")==0){
    echo <<<EOL
   <div class="menu-container">
        <div class="navbar">
		<a href="index.php">Home</a>
          <div class="dropdown1">

         
          </div> 
        </div>
        </div>
EOL;

}
else{
	echo "Error: Missing menu";
}

