<?php
// PHP
session_start();

// Helper: stream file as PDF (inline)
function streamPdf($path, $name) {
    if (!file_exists($path)) {
        http_response_code(404);
        echo "Not found";
        return;
    }
    header('Content-Type: application/pdf');
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: inline; filename="' . basename($name) . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($path);
    exit;
}

// PDF storage directory (used by generation and download endpoint)
$pdfDir = __DIR__ . '/pdfs';
if (!is_dir($pdfDir)) @mkdir($pdfDir, 0755, true);

// Quick diagnostic endpoint: check PHP extensions used by Dompdf/wkhtmltopdf
if (isset($_GET['check_ext'])) {
    $required = ['mbstring', 'gd', 'fileinfo', 'openssl'];
    $missing = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) $missing[] = $ext;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['missing_extensions' => $missing]);
    exit;
}

// Secure download endpoint for saved PDFs (Android/clients can call ?getfile=FILENAME)
if (isset($_GET['getfile'])) {
    $name = basename($_GET['getfile']); // prevents path traversal
    $path = $pdfDir . DIRECTORY_SEPARATOR . $name;
    if (file_exists($path)) {
        streamPdf($path, $name);
    } else {
        http_response_code(404);
        echo "File not found";
    }
    exit;
}

// Get inputs (accept GET for PDF generation, POST for normal page)
$month = isset($_REQUEST['month']) ? $_REQUEST['month'] : null;
$year = isset($_REQUEST['year']) ? $_REQUEST['year'] : null;
$place = isset($_REQUEST['place']) ? $_REQUEST['place'] : (isset($_COOKIE['panchang_place']) ? $_COOKIE['panchang_place'] : null);
$TZ = $place ? explode('|', $place)[1] : 'UTC';

if (isset($_GET['generate_pdf'])) {
    // Server-side PDF generation flow
    // Validate minimal inputs
    if (empty($month) || empty($year) || empty($place)) {
        http_response_code(400);
        echo "Missing month/year/place";
        exit;
    }

    // Prepare output filename
    $hash = substr(md5($month . '|' . $year . '|' . $place), 0, 12);
    $time = time();
    $filename = "panchang_{$year}_{$month}_{$hash}_{$time}.pdf";
    $outPath = $pdfDir . DIRECTORY_SEPARATOR . $filename;

    // Build render URL pointing to same script but with pdf_render=1 so we don't re-enter generation branch
    // Allow caller (Android/emulator) to override host/scheme when needed (e.g., emulator uses 10.0.2.2)
    $scheme = isset($_GET['base_scheme']) && in_array(strtolower($_GET['base_scheme']), ['http','https']) ? strtolower($_GET['base_scheme']) : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    // Accept either 'base_host' param or fall back to server HTTP_HOST
    if (isset($_GET['base_host']) && is_string($_GET['base_host']) && $_GET['base_host'] !== '') {
        // basic sanitize: only allow host:port or ip:port
        $host = preg_replace('/[^A-Za-z0-9\-\.:]/', '', $_GET['base_host']);
    } else {
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'] ?? 'localhost');
    }
     $script = $_SERVER['PHP_SELF'];
     $qs = http_build_query(['pdf_render' => 1, 'month' => $month, 'year' => $year, 'place' => $place]);
     $renderUrl = $scheme . '://' . $host . $script . '?' . $qs;

    // Try vendor or system wkhtmltopdf first (executes JS; preferred)
    $possible = [
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltox' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wkhtmltopdf.exe',
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltox' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wkhtmltopdf',
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltopdf.exe',
        'wkhtmltopdf'
    ];

    $wk = null;
    foreach ($possible as $p) {
        if ($p === 'wkhtmltopdf') {
            // try unix-style lookup
            $bin = trim(@shell_exec('command -v wkhtmltopdf 2>/dev/null') ?: @shell_exec('which wkhtmltopdf 2>/dev/null'));
            if ($bin) { $wk = $bin; break; }
            // try Windows 'where'
            $bin = trim(@shell_exec('where wkhtmltopdf 2>NUL'));
            if ($bin) { $wk = $bin; break; }
        } else {
            if (file_exists($p) && is_executable($p)) { $wk = $p; break; }
            // on Windows is_executable may be false for .exe; accept existing file
            if (file_exists($p) && PHP_OS_FAMILY === 'Windows') { $wk = $p; break; }
        }
    }

    if ($wk) {
        // Ensure path to binary is safe for shell
        $safeWk = escapeshellarg($wk);
        $safeUrl = escapeshellarg($renderUrl);
        $safeOut = escapeshellarg($outPath);
        $cmd = "$safeWk --enable-local-file-access --no-stop-slow-scripts --javascript-delay 1500 --print-media-type --page-size A4 --quiet $safeUrl $safeOut 2>&1";
        exec($cmd, $out, $rc);
        if ($rc === 0 && file_exists($outPath)) {
            // If caller expects JSON (Android/app), return download URL; otherwise stream inline
            if (isset($_GET['api']) && $_GET['api']) {
                $downloadUrl = $scheme . '://' . $host . $script . '?getfile=' . urlencode($filename);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'download_url' => $downloadUrl, 'filename' => $filename]);
                exit;
            } else {
                streamPdf($outPath, $filename);
            }
        } else {
            error_log("wkhtmltopdf failed: " . implode("\n", (array)$out));
            // fall through to Dompdf fallback
        }
    }

    // Dompdf fallback (note: Dompdf does not run JS; JS-generated content may be missing)
    require_once __DIR__ . '/vendor/autoload.php';
    use Dompdf\Dompdf;
    use Dompdf\Options;

    $ctx = stream_context_create(['http' => ['timeout' => 15, 'follow_location' => 1]]);
    $html = @file_get_contents($renderUrl, false, $ctx);
    if ($html === false) {
        http_response_code(500);
        echo "Failed to fetch page HTML for PDF rendering";
        exit;
    }

    try {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $pdfOutput = $dompdf->output();
        if (file_put_contents($outPath, $pdfOutput) === false) {
            http_response_code(500);
            echo "Failed to write PDF file";
            exit;
        }
        // If caller expects JSON (Android/app), return download URL; otherwise stream inline
        if (isset($_GET['api']) && $_GET['api']) {
            $downloadUrl = $scheme . '://' . $host . $script . '?getfile=' . urlencode($filename);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'download_url' => $downloadUrl, 'filename' => $filename]);
            exit;
        } else {
            streamPdf($outPath, $filename);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo "PDF generation failed";
        exit;
    }
}

// Diagnostic endpoint to help debug installation issues
if (isset($_GET['diag'])) {
    $info = [];
    $info['php_version'] = phpversion();
    $info['php_ini'] = php_ini_loaded_file() ?: 'none';
    $info['extensions'] = get_loaded_extensions();
    $info['vendor_autoload_exists'] = file_exists(__DIR__ . '/vendor/autoload.php');

    // Detect wkhtmltopdf using same logic as generation
    $possible_diag = [
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltox' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wkhtmltopdf.exe',
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltox' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wkhtmltopdf',
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'wkhtmltopdf' . DIRECTORY_SEPARATOR . 'wkhtmltopdf.exe',
        'wkhtmltopdf'
    ];
    $found = null;
    foreach ($possible_diag as $p) {
        if ($p === 'wkhtmltopdf') {
            $bin = trim(@shell_exec('where wkhtmltopdf 2>NUL')) ?: trim(@shell_exec('command -v wkhtmltopdf 2>/dev/null')) ?: trim(@shell_exec('which wkhtmltopdf 2>/dev/null'));
            if ($bin) { $found = $bin; break; }
        } else {
            if (file_exists($p)) { $found = $p; break; }
        }
    }
    $info['wkhtmltopdf_path'] = $found ?: null;
    if ($found) {
        // Try to get version (safe short command)
        $verOut = null; $verRc = null;
        @exec(escapeshellarg($found) . ' --version 2>&1', $verOut, $verRc);
        $info['wkhtmltopdf_version_output'] = $verOut;
        $info['wkhtmltopdf_version_rc'] = $verRc;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Below: original page output (rendered in browser) ---
// If `pdf_render=1` is present this same HTML/JS will be served so server renderers can fetch a page that executes JS.
date_default_timezone_set($TZ);

function getMonthName($month,$lang) //1 - tamil
{
    $name_tamil_arr = ["dummy","ஜனவரி", "பிப்ரவரி", "மார்ச்", "ஏப்ரல்", "மே", "ஜூன்", "ஜூலை", "ஆகஸ்ட்", "செப்டம்பர்", "அக்டோபர்", "நவம்பர்", "டிசம்பர்"];
    if($lang == 1){
        return $name_tamil_arr[$month];
    }
    return "";
}

$monthName = $month ? getMonthName((int)$month,1) : '';
?>

<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro Birth Chart</title>
    <meta name="description" content="One stop website for Astrology">

    <link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
    <link rel="stylesheet" href="css/onepage_panchang_style.css?<?php echo date('ymdhis'); ?>">
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
   <script src="html2pdf.bundle.min.js" type="text/javascript"></script>
    <script src="panchang.js?<?php echo date('ymdhis'); ?>"></script>
    <script src="my_panchang.js?<?php echo date('ymdhis'); ?>"></script>
</head>
<body>

<div id='onepage' style="width: min-content">
<div class="header-parent">
  <div class="header-left"></div>
  <div class="header-center"><div class='divTableTextOfficeName'>பஞ்சாங்கம் (<?php echo htmlspecialchars("$monthName/$year/$place"); ?>)</div></div>
  <div class="header-right"></div>
</div>
<script>
    // Emit JS-safe literals from PHP to avoid syntax errors from quotes/characters
    var timezone = <?php echo json_encode($TZ, JSON_UNESCAPED_UNICODE); ?>;
    const year = <?php echo json_encode((int)$year); ?>;
    const month = <?php echo json_encode((int)$month); ?>;  //1 - jan

    generatePanchang(month,year);
</script>

<div id="panchangContainer"></div>

<div class="PageFooter" style="width:-webkit-fill-available">தர்ம சாஸ்தா ஜோதிட வித்யாலயா & Periyanayaki Astro Solutions</div>
</body>
</html>
