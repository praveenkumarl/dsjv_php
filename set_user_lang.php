<?php
session_start();
include_once "lib.php";

// Accept POST lang parameter and update DB if user logged in
$lang = isset($_POST['lang']) ? trim($_POST['lang']) : '';
if(empty($lang)){
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => 'missing lang']);
    exit;
}

// Normalize to allowed values
$allowed = ['tamil','english'];
if(!in_array(strtolower($lang), $allowed)){
    $lang = 'tamil';
}

// Update session and cookie
$_SESSION['language'] = $lang;
setcookie('language', $lang, time() + (60*60*24*365), "/");

// If user is logged in, update DB
$phone = isset($_SESSION['phone']) ? $_SESSION['phone'] : (isset($_COOKIE['phone']) ? $_COOKIE['phone'] : null);
if($phone){
    $conn = dbConnect(null);
    $phone_safe = sanitiseInput($conn, $phone);
    $lang_safe = sanitiseInput($conn, $lang);
    $sql = "UPDATE `user` SET `lang`='$lang_safe' WHERE `phone`='$phone_safe'";
    mysqli_query($conn, $sql);
    dbDisconnect($conn);
}

echo json_encode(['ok' => true, 'lang' => $lang]);
exit;
?>