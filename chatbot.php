<?php
require 'vendor/autoload.php'; // Load Composer dependencies

use DeepSeek\DeepSeekClient;
use GuzzleHttp\Client;

// Replace with your actual DeepSeek API key
$apiKey = 'sk-9023fec5d97f4a3f90842add63ba352a';

// Initialize the DeepSeek client
$client = DeepSeekClient::build($apiKey);

function getDeepSeekResponse($userInput, $language = 'en') {
	global $apiKey;
	
    if (empty($userInput)) {
        return "Error: Message cannot be empty.";
    }

    // Set system message in the correct language
    $systemMessage = ($language === 'ta') ? 'நீங்கள் ஒரு உதவியாளர்' : 'You are a helpful assistant';

    // Initialize Guzzle client
    $client = new Client();

    try {
        // Send request to DeepSeek API
        $response = $client->post('https://api.deepseek.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model' => 'deepseek-chat', // Make sure this is the correct model name
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage], // System role
                    ['role' => 'user', 'content' => $userInput] // User input
                ]
            ]
        ]);

        // Get the response body
        $body = json_decode($response->getBody(), true);

        // Debugging: Print full response
        echo "<pre>";
        print_r($body);
        echo "</pre>";

        // Return the chatbot's reply
        return $body['choices'][0]['message']['content'] ?? 'No response received.';
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}



// Handle user input
$chatResponse = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = $_POST['message'] ?? '';
    $language = $_POST['language'] ?? 'en';

    if (!empty($userMessage)) {
        $chatResponse = getDeepSeekResponse($userMessage, $language);
    } else {
        $chatResponse = "Please enter a message.";
    }
}
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeepSeek Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        .chat-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            margin: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        textarea {
            width: 100%;
            height: 80px;
            margin-top: 10px;
        }
        button {
            padding: 10px;
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #218838;
        }
        .response {
            margin-top: 20px;
            background: #ddd;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <h2>DeepSeek Chatbot</h2>
    <form method="POST">
        <label for="language">Select Language:</label>
        <select name="language" id="language">
            <option value="en">English</option>
            <option value="ta">தமிழ் (Tamil)</option>
        </select>
        <br><br>
        <label for="message">Your Message:</label>
        <textarea name="message" id="message"></textarea>
        <br>
        <button type="submit">Send</button>
    </form>

    <?php if (!empty($chatResponse)): ?>
        <div class="response">
            <strong>Chatbot:</strong> <?php echo htmlspecialchars($chatResponse); ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
