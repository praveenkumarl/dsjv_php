<?php

if (php_sapi_name() == 'cli') {   
   if (isset($_SERVER['TERM'])) {   
      //echo "The script was run from a manual invocation on a shell";   
	  require_once "/home/u835731528/public_html/dsjv_home/lib.php";
   } else {   
      //echo "The script was run from the crontab entry";
	  require_once "/home/u835731528/public_html/dsjv_home/lib.php";
   }   
} else { 
   //echo "The script was run from a webserver, or something else";   
   require_once "lib.php";
}

$conn="";
$day_threshold = 30;
$full_amount = 1000;
$individual_amount = 200;
$gpay_number = "+91 7305731547";
$appname = "DSJV App";
$email_content = "";

// Create connection
$conn = dbConnect($conn);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch expiry details for a given date range
function fetchExpiryDetails($conn, $startDate, $endDate) {
    $sql = "SELECT user_trial.phone, user.name, user.imei, user_trial.expiry_date 
            FROM user_trial, user 
            WHERE user_trial.imei = user.imei 
              AND user_trial.expiry_date BETWEEN ? AND ? ORDER BY user_trial.expiry_date";
			  
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

// Function to expire user based on imei#
function expireUser($conn, $key) {
    // Check if the $key is properly sanitized/validated
    if (empty($key)) {
        die("IMEI key is empty.");
    }

    // The SQL query
    $sql = "UPDATE user SET user_type = 'FREE' WHERE imei = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // This will give the exact error
    }

    // Bind the parameter
    $stmt->bind_param("s", $key); // Assuming $key is a string (IMEI as string)

    // Execute the statement
    $success = $stmt->execute();
    if (!$success) {
        die("Execution failed: " . $stmt->error); // Provides the exact execution error
    }

    // Close the statement
    $stmt->close();

    // Return true if rows were affected, false otherwise
    return $conn->affected_rows > 0;
}


// Function to send email notifications
function sendEmail($recipient, $subject, $message) {
    $headers = "From: noreply@pkastro.com";
    mail($recipient, $subject, $message, $headers);
}

function sendExpiryReport($data, $title, $curr) {
    global $appname, $full_amount, $individual_amount, $gpay_number,$conn;
	
	date_default_timezone_set('Asia/Kolkata'); // Set timezone
	$curr_datetime = date('d-m-Y H:i:s');

    // Generate the HTML content with proper encoding
    $message = "<!DOCTYPE html>";
    $message .= "<html lang='en'>";
    $message .= "<head>";
    $message .= "<meta charset='UTF-8'>";  // Ensure proper encoding for Tamil characters
    $message .= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    $message .= "<title>$title</title>";
    $message .= "</head>";
    $message .= "<body>";
    $message .= "<h2>$title</h2>";
	$message .= "<h4>Report generated @$curr_datetime</h2>";
    $message .= "<table border='1'>";
    $message .= "<tr><th>Phone</th><th>Name</th><th>Key</th><th>Expiry Date</th><th>WhatsApp</th></tr>";

    foreach ($data as $row) {
        $name = $row['name'];
        $phone = $row['phone'];
		$key = $row['imei'];
        //$expiry_date = $row['expiry_date'];
		$expiry_date = date("d-m-Y", strtotime($row['expiry_date']));
        $days_left = (strtotime($expiry_date) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
        $messageContent = "Hi $name, Your $appname subscription is going to expire in $days_left days. Pay ₹$full_amount if you are using full package or ₹$individual_amount if you use individual package to gpay#" . $gpay_number . " to continue subscription. உங்கள் $appname சந்தா $days_left நாட்களில் காலாவதியாகப் போகிறது. சந்தாவைத் தொடர, முழு தொகுப்புக்கு ₹$full_amount, தனி தொகுப்புக்கு ₹$individual_amount gpay#" . $gpay_number . " க்கு செலுத்தவும்";
        $whatsappLink = "whatsapp://send?phone=" . urlencode($phone) . "&text=" . urlencode($messageContent);

        $message .= "<tr><td>{$phone}</td><td>{$name}</td><td>{$key}</td><td>{$expiry_date}</td><td><a href='$whatsappLink' target='_blank'>Chat</a></td></tr>";
		
		if($days_left <= 0){
			expireUser($conn, $key);
		}
    }

    $message .= "</table>";
    $message .= "</body>";
    $message .= "</html>";

    // Determine file paths and URLs based on current/previous report
    if ($curr == 1) {
        $file_path = '/home/u835731528/public_html/dsjv_home/current_expiry_report.html';
        $file_url = 'https://pkastro.com/dsjv_home/current_expiry_report.html';
        $subject = "$appname Current Subscription Expiry Report";
        $body_message = "Please find the current expiry report at the following link:";
        $link_text = "View Current Expiry Report";
    } else {
        $file_path = '/home/u835731528/public_html/dsjv_home/previous_expiry_report.html';
        $file_url = 'https://pkastro.com/dsjv_home/previous_expiry_report.html';
        $subject = "$appname Previous Subscription Expiry Report";
        $body_message = "Please find the previous expiry report at the following link:";
        $link_text = "View Previous Expiry Report";
    }

    // Save the HTML content to the file on the server
    file_put_contents($file_path, $message);

    // Prepare the email content with UTF-8 encoding
    $to = "pkastro8888@gmail.com"; // Replace with recipient's email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";  // Ensure UTF-8 encoding in email
    $headers .= "From: DSJV <dsjv@yourdomain.com>\r\n";

    // Compose the email body with the link to the HTML report
    $body = "<p>$body_message</p>";
    $body .= "<p><a href='$file_url' target='_blank'>$link_text</a></p>";

    // Send the email with the link to the HTML file
    mail($to, $subject, $body, $headers);

    // Optional: Echo the email body for debugging purposes
    echo $body;
}



// Get current date
$currentDate = date("Y-m-d");

// Calculate date ranges for next 7 days
$nextStartDate = $currentDate;
$nextEndDate = date("Y-m-d", strtotime("$currentDate + $day_threshold days"));

// Calculate date ranges for previous 7 days
$prevStartDate = date("Y-m-d", strtotime("$currentDate - $day_threshold days"));
$prevEndDate = $currentDate;

// Fetch records for next 7 days
$nextExpiryDetails = fetchExpiryDetails($conn, $nextStartDate, $nextEndDate);

// Fetch records for previous 7 days
$prevExpiryDetails = fetchExpiryDetails($conn, $prevStartDate, $prevEndDate);

// Function to display expiry details in table
function displayExpiryTable($data, $title) {
    global $appname, $full_amount, $individual_amount, $gpay_number;
    echo "<h2>$title</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Phone</th><th>Name</th><th>Key</th><th>Expiry Date</th><th>WhatsApp</th></tr>";

    foreach ($data as $row) {
        $name = $row['name'];
        $phone = $row['phone'];
		$key = $row['imei'];
        $expiry_date = $row['expiry_date'];
        $days_left = (strtotime($expiry_date) - strtotime(date("Y-m-d"))) / (60 * 60 * 24);
        $messageContent = "Hi $name, Your $appname subscription is going to expire in $days_left days. Pay ₹$full_amount if you are using full package or ₹$individual_amount if you use individual package to gpay#" . $gpay_number . " to continue subscription. உங்கள் $appname சந்தா $days_left நாட்களில் காலாவதியாகப் போகிறது. சந்தாவைத் தொடர, முழு தொகுப்புக்கு ₹$full_amount, தனி தொகுப்புக்கு ₹$individual_amount gpay#" . $gpay_number . " க்கு செலுத்தவும்";
        $whatsappLink = "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . urlencode($messageContent);

        echo "<tr><td>{$phone}</td><td>{$name}</td><td>{$key}</td><td>{$expiry_date}</td><td><a href='$whatsappLink' target='_blank'>Chat</a></td></tr>";
    }

    echo "</table>";
}

// Display tables for both date ranges
displayExpiryTable($nextExpiryDetails, "Users Expiring in the Next $day_threshold Days");
displayExpiryTable($prevExpiryDetails, "Users Expired in the Previous $day_threshold Days");

// Send emails for both date ranges
sendExpiryReport($nextExpiryDetails, "DSJV App Upcoming Expiry", 1);
sendExpiryReport($prevExpiryDetails, "DSJV App Previously Expired", 0);



?>
