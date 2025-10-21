<?php
session_start();
//$var_name = $_GET['var_name'];

//echo json_encode($_SESSION[$var_name]);

$response = array(
    'chartdata' => $_SESSION['inside'],
    'name' => $_SESSION['name'],
    'date' => $_SESSION['birth_date']
);

echo json_encode($response);

?>