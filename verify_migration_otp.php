<?php
// verify_migration_otp.php
include "lib.php";

header('Content-Type: application/json');

$phone       = isset($_POST['phone'])         ? trim($_POST['phone'])         : '';
$otp         = isset($_POST['otp'])           ? trim($_POST['otp'])           : '';
$newDeviceId = isset($_POST['new_device_id']) ? trim($_POST['new_device_id']) : '';

if (empty($phone) || empty($otp) || empty($newDeviceId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$conn = dbConnect("");
$phone       = sanitiseInput($conn, $phone);
$otp         = sanitiseInput($conn, $otp);
$newDeviceId = sanitiseInput($conn, $newDeviceId);

startSqlTxn($conn);

try {
    // 1. Find the most recent unused, unexpired OTP for this phone
    $stmt = $conn->prepare(
        "SELECT id FROM otp_codes
         WHERE phone = ? AND purpose = 'migration' AND used = 0
           AND expires_at > NOW()
           AND otp = ?
         ORDER BY id DESC LIMIT 1"
    );
    $stmt->bind_param('ss', $phone, $otp);
    $stmt->execute();
    $res = $stmt->get_result();

    if (mysqli_num_rows($res) !== 1) {
        $stmt->close();
        rollbackSqlTxn($conn);
        dbDisconnect($conn);
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired OTP']);
        exit;
    }
    $otpRow = $res->fetch_assoc();
    $stmt->close();

    // 2. Make sure the new device ID isn't already used by someone else
    $chk = $conn->prepare("SELECT phone FROM user WHERE imei = ?");
    $chk->bind_param('s', $newDeviceId);
    $chk->execute();
    $chkRes = $chk->get_result();
    if (mysqli_num_rows($chkRes) > 0) {
        $chk->close();
        rollbackSqlTxn($conn);
        dbDisconnect($conn);
        http_response_code(409);
        echo json_encode(['error' => 'This device is already linked to another account.']);
        exit;
    }
    $chk->close();

    // 3. THE MIGRATION: swap imei on the user row
    $upd = $conn->prepare("UPDATE user SET imei = ? WHERE phone = ?");
    $upd->bind_param('ss', $newDeviceId, $phone);
    $upd->execute();
    $affected = $upd->affected_rows;
    $upd->close();

    if ($affected !== 1) {
        rollbackSqlTxn($conn);
        dbDisconnect($conn);
        http_response_code(500);
        echo json_encode(['error' => 'Migration failed. Please contact support.']);
        exit;
    }

    // 4. Mark OTP used
    $mk = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
    $mk->bind_param('i', $otpRow['id']);
    $mk->execute();
    $mk->close();

    commitSqlTxn($conn);

    // 5. Audit log
    $log_line = date('c') . " | MIGRATED | phone=" . $phone . " | new_imei=" . $newDeviceId
              . " | " . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n";
    @file_put_contents(__DIR__ . '/../logs/migrations.log', $log_line, FILE_APPEND);

    dbDisconnect($conn);

    http_response_code(200);
    echo json_encode(['message' => 'Device migrated successfully']);
} catch (mysqli_sql_exception $e) {
    if ($conn) { rollbackSqlTxn($conn); }
    error_log('Migration error: ' . $e->getMessage());
    dbDisconnect($conn);
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}