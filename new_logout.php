<?php session_start();

session_destroy();

header( "Refresh:0; url=new_loginPage.php", true, 303);

?>
