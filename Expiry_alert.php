<?php
require_once "require_once "/usr/bin/php /home/u835731528/public_html/dsjv_home/lib.php";

$conn="";
$day_threshold=7;
$amount=1000;
$gpay_number=917305731547;
$appname = "DSJV APP (pkastro)";

$message = "Your $appname subscription is going to expire in $days_left days. Pay ₹ $amount to gpay#$gpay_number to continue subscription.
உங்கள் $appname சந்தா $days_left நாட்களில் காலாவதியாகப் போகிறது. சந்தாவைத் தொடர gpay#$gpay_numberக்கு ₹ $amount செலுத்தவும்";

// Create connection
$conn = dbConnect($conn);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select records where expiry_date is in 5 days
$sql = "SELECT phone, expiry_date FROM user_trial WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $day_threshold DAY)";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Start building the email content
    $email_content = "<h2>Subscriptions Expiring in $day_threshold Days</h2>";
    $email_content .= "<table border='1'>
                        <tr>
                            <th>Phone</th>
                            <th>Expiry Date</th>
                            <th>WhatsApp Link</th>
                        </tr>";

    // Loop through each record
    while($row = $result->fetch_assoc()) {
        $phone = $row['phone'];
        $expiry_date = $row['expiry_date'];
        
        // Calculate the number of days left until expiry
        $current_date = new DateTime();
        $expiry_date_obj = new DateTime($expiry_date);
        $interval = $current_date->diff($expiry_date_obj);
        $days_left = $interval->days;

        // Generate WhatsApp message link
        $whatsapp_link = "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . urlencode("$message");

        $email_content .= "<tr>
                            <td>{$phone}</td>
                            <td>{$expiry_date}</td>
                            <td><a href='{$whatsapp_link}'>Send WhatsApp Message</a></td>
                           </tr>";
    }

    $email_content .= "</table>";

    // Email details
    $to = "pkastro8888@gmail.com"; // Replace with recipient's email
    $subject = "$appname Subscription Expiry Report";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $appname <no-reply@$appname>" . "\r\n"; // Replace with sender's name and email

    // Send email
    if(mail($to, $subject, $email_content, $headers)) {
        echo "Email sent successfully.";
    } else {
        echo "Failed to send email.";
    }
} else {
    echo "No subscriptions expiring in $day_threshold days.";
}

$conn->close();
?>
