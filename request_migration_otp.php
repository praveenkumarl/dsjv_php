<?php
// request_migration_otp.php
include "lib.php";

header('Content-Type: application/json');

$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$newDeviceId = isset($_POST['new_device_id']) ? trim($_POST['new_device_id']) : '';

if (empty($phone) || empty($newDeviceId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing phone or device ID']);
    exit;
}

$conn = dbConnect("");
$phone = sanitiseInput($conn, $phone);
$newDeviceId = sanitiseInput($conn, $newDeviceId);

// 1. Confirm the phone number is registered
$stmt = $conn->prepare("SELECT name, imei FROM user WHERE phone = ?");
$stmt->bind_param('s', $phone);
$stmt->execute();
$res = $stmt->get_result();

if (mysqli_num_rows($res) !== 1) {
    $stmt->close();
    dbDisconnect($conn);
    http_response_code(404);
    echo json_encode([
        'error' => 'Phone number not registered. Please register as a new user.'
    ]);
    exit;
}

$row = $res->fetch_assoc();
$stmt->close();

// If the phone is already tied to the SAME new device ID → no migration needed
if ($row['imei'] === $newDeviceId) {
    dbDisconnect($conn);
    http_response_code(200);
    echo json_encode(['message' => 'This device is already linked to your account.']);
    exit;
}

// 2. Rate limiting: max 1 OTP per phone per 60 seconds
$rl = $conn->prepare(
    "SELECT COUNT(*) AS n FROM otp_codes
     WHERE phone = ? AND purpose = 'migration'
       AND created_at > (NOW() - INTERVAL 60 SECOND)"
);
$rl->bind_param('s', $phone);
$rl->execute();
$rlRes = $rl->get_result()->fetch_assoc();
$rl->close();

if (intval($rlRes['n']) > 0) {
    dbDisconnect($conn);
    http_response_code(429);
    echo json_encode(['error' => 'Please wait 60 seconds before requesting another OTP.']);
    exit;
}

// 3. Generate OTP, store, and send
$otp = random_int(100000, 999999);

$ins = $conn->prepare(
    "INSERT INTO otp_codes (phone, otp, purpose, expires_at)
     VALUES (?, ?, 'migration', NOW() + INTERVAL 10 MINUTE)"
);
$ins->bind_param('ss', $phone, $otp);
$ins->execute();
$ins->close();

// --- USE YOUR EXISTING SMS FUNCTION HERE ---
// If you have sendSms() in lib.php, call it:
// sendSms($phone, "Your DSJV migration OTP is $otp. Valid 10 minutes.");
// Otherwise, wire in your SMS provider (MSG91, Twilio, etc.)

dbDisconnect($conn);

http_response_code(200);
echo json_encode([
    'message' => 'OTP sent to your registered mobile number.'
    // , 'otp_debug' => $otp   // ← TEMPORARY for testing; remove in production
]);