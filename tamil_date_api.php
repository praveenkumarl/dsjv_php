<?php
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}
require_once __DIR__ . '/api_auth.php';

date_default_timezone_set('Asia/Kolkata');

require_once "db_config.php";

$conn = null;
try {
    // Ensuring UTF-8 encoding on the connection using config from db_config.php
    $conn = new PDO("mysql:host=$servername;dbname=$db;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Fails silently if no DB, falls back to real-time computation
}

require_once "tamil_calendar_helper.php";

$inputDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $inputDate)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid date format. Use YYYY-MM-DD.',
        'provider' => 'PNK Astro'
    ]);
    exit;
}

$calendar = new PNKAstroCalendar();
$data = $calendar->getTamilDate($inputDate, $conn);

echo json_encode(
    [
        'status' => 'success',
        'provider' => 'PNK Astro',
        'data' => $data
    ], 
    JSON_UNESCAPED_UNICODE // Ensures Tamil text renders natively instead of \uXXXX
);
?>