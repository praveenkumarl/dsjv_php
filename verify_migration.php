<?php
include "lib.php";
header('Content-Type: application/json');

$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$oldSuffix = isset($_POST['old_device_suffix']) ? trim($_POST['old_device_suffix']) : '';
$newDeviceId = isset($_POST['new_device_id']) ? trim($_POST['new_device_id']) : '';

// CHANGED: Now requires only 4 characters
if (empty($phone) || empty($oldSuffix) || empty($newDeviceId) || strlen($oldSuffix) < 4) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input. Please enter the last 4 characters of your Old ID.']);
    exit;
}

$conn = dbConnect("");
$phone = sanitiseInput($conn, $phone);
$oldSuffix = sanitiseInput($conn, $oldSuffix);

// 1. RATE LIMITING: Block hackers after 5 failed attempts per hour
$rl = $conn->prepare("SELECT COUNT(*) as c FROM migration_attempts WHERE phone = ? AND attempt_time > NOW() - INTERVAL 1 HOUR");
$rl->bind_param('s', $phone);
$rl->execute();
$rlCount = $rl->get_result()->fetch_assoc()['c'];
$rl->close();

if ($rlCount >= 5) {
    dbDisconnect($conn);
    http_response_code(429);
    echo json_encode(['error' => 'Too many failed attempts. Please contact DSJV Admin for help.']);
    exit;
}

// 2. FUZZY MATCH: Check if the stored ID ends with the 4 characters typed
$stmt = $conn->prepare("SELECT name FROM user WHERE phone = ? AND RIGHT(imei, CHAR_LENGTH(?)) = ?");
$stmt->bind_param('sss', $phone, $oldSuffix, $oldSuffix);
$stmt->execute();
$res = $stmt->get_result();

if (mysqli_num_rows($res) !== 1) {
    // Log the failed attempt
    $log = $conn->prepare("INSERT INTO migration_attempts (phone) VALUES (?)");
    $log->bind_param('s', $phone);
    $log->execute();
    $log->close();
    
    $stmt->close();
    dbDisconnect($conn);
    http_response_code(401);
    echo json_encode(['error' => 'Invalid Phone Number or Device ID.']);
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

// 3. Prevent stealing an already active device
$chk = $conn->prepare("SELECT phone FROM user WHERE imei = ?");
$chk->bind_param('s', $newDeviceId);
$chk->execute();
if (mysqli_num_rows($chk->get_result()) > 0) {
    $chk->close();
    dbDisconnect($conn);
    http_response_code(409);
    echo json_encode(['error' => 'This device is already linked to another account.']);
    exit;
}
$chk->close();

// 4. MIGRATION SUCCESS
$conn->query("DELETE FROM migration_attempts WHERE phone = '$phone'"); // Clear rate limits

$upd = $conn->prepare("UPDATE user SET imei = ? WHERE phone = ?");
$upd->bind_param('ss', $newDeviceId, $phone);
$upd->execute();
$upd->close();
dbDisconnect($conn);

http_response_code(200);
echo json_encode(['message' => 'Device migrated successfully', 'name' => $row['name']]);