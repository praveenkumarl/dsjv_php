<?php
/**
 * WebView Restriction Checker
 * Restricts hosted requests to the Android WebView application.
 */

function isAndroidWebView() {
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

    return strpos($ua, 'android') !== false && strpos($ua, '; wv)') !== false;
}

$webviewHost = isset($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : '';
$webviewHost = preg_replace('/:\d+$/', '', $webviewHost);
$webviewRemoteAddress = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
$isLocalMachineRequest = in_array($webviewHost, array('localhost', '127.0.0.1', '::1', '[::1]'), true)
    && in_array($webviewRemoteAddress, array('127.0.0.1', '::1', '::ffff:127.0.0.1'), true);

// Keep local development usable; hosted requests must use Android WebView.
if (!$isLocalMachineRequest && !isAndroidWebView()) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Access Restricted: Please use the official Android application.']);
        exit();
    }

    http_response_code(403);
        
    ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Access Restricted - DSJV App</title>
            <!-- Modern Typography -->
            <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
            <style>
                :root {
                    --bg-gradient: radial-gradient(circle at top right, #1a1b3a, #0c0d1e);
                    --card-bg: rgba(255, 255, 255, 0.05);
                    --card-border: rgba(255, 255, 255, 0.08);
                    --accent-glow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
                    --text-primary: #ffffff;
                    --text-secondary: #a0aec0;
                    --glow-color: #6366f1;
                    --glow-hover: #4f46e5;
                }

                * {
                    box-sizing: border-box;
                    margin: 0;
                    padding: 0;
                }

                body {
                    font-family: 'Outfit', sans-serif;
                    background: var(--bg-gradient);
                    color: var(--text-primary);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 20px;
                    overflow-x: hidden;
                }

                .container {
                    width: 100%;
                    max-width: 480px;
                    background: var(--card-bg);
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    border: 1px solid var(--card-border);
                    border-radius: 24px;
                    padding: 40px 30px;
                    text-align: center;
                    box-shadow: var(--accent-glow);
                    animation: fadeIn 0.8s ease-out;
                }

                @keyframes fadeIn {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }

                .icon-container {
                    position: relative;
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 30px;
                    background: rgba(99, 102, 241, 0.1);
                    border-radius: 50%;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    border: 1px solid rgba(99, 102, 241, 0.2);
                    box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
                    animation: pulse 2s infinite alternate;
                }

                @keyframes pulse {
                    0% { transform: scale(1); box-shadow: 0 0 20px rgba(99, 102, 241, 0.2); }
                    100% { transform: scale(1.05); box-shadow: 0 0 30px rgba(99, 102, 241, 0.4); }
                }

                .icon-container svg {
                    width: 40px;
                    height: 40px;
                    fill: var(--glow-color);
                }

                h1 {
                    font-size: 24px;
                    font-weight: 700;
                    margin-bottom: 20px;
                    letter-spacing: -0.5px;
                    background: linear-gradient(to right, #ffffff, #cbd5e1);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }

                .message-ta {
                    font-size: 17px;
                    line-height: 1.6;
                    color: var(--text-primary);
                    margin-bottom: 16px;
                    font-weight: 400;
                }

                .message-en {
                    font-size: 15px;
                    line-height: 1.6;
                    color: var(--text-secondary);
                    margin-bottom: 35px;
                    font-weight: 300;
                }

                .btn-download {
                    display: inline-block;
                    width: 100%;
                    padding: 16px 24px;
                    background: linear-gradient(135deg, var(--glow-color), #4338ca);
                    color: #ffffff;
                    text-decoration: none;
                    font-weight: 600;
                    font-size: 16px;
                    border-radius: 14px;
                    border: none;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
                }

                .btn-download:hover {
                    transform: translateY(-2px);
                    background: linear-gradient(135deg, #4f46e5, #3730a3);
                    box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
                }

                .btn-download:active {
                    transform: translateY(0);
                }

                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: rgba(255, 255, 255, 0.3);
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon-container">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 1.01L8 1c-1.1 0-2 .9-2 2v3h2V5h10v14H8v-1H6v3c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM10 10V7L5 12l5 5v-3h6v-4h-6z"/>
                    </svg>
                </div>
                
                <h1>Access Restricted</h1>
                
                <p class="message-ta">
                    அனுமதி மறுக்கப்பட்டது: இந்த இணையதளத்தை அதிகாரப்பூர்வ ஆண்ட்ராய்டு ஆப் மூலமாக மட்டுமே பயன்படுத்த முடியும்.
                </p>
                
                <p class="message-en">
                    Access Denied: This website is only allowed to be opened via the official Android WebView application. Please download and install the app using the link below.
                </p>
                
                <a href="https://drive.google.com/file/d/1pyzTmRByGcXLuEMJLzRki12b2JisWABK/view?usp=sharing" class="btn-download">
                    Download Android App
                </a>
                


                <div class="footer">
                    &copy; <?php echo date('Y'); ?> DSJV App. All rights reserved.
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
}
