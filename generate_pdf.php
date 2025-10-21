<?php

require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Set the Content-Type header to tell the browser it's a PDF file.
header('Content-Type: application/pdf');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    // Return a JSON error message if the input is not valid JSON.
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON received.']);
    exit;
}

if (empty($data)) {
    // Return a JSON error message if no data is received.
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'No data received.']);
    exit;
}

// Generate the HTML content for the PDF
function generateHtmlContent($data) {
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Birth Chart</title>
        <style>
            @page {
                size: a4;
                margin: 10mm;
            }
            body {
                font-family: DejaVu Sans, sans-serif;
                margin: 0;
                padding: 0;
            }
            .page-container {
                width: 100%;
                height: 277mm;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .content-container {
                display: flex;
                gap: 15mm;
                flex-wrap: nowrap;
            }
            .col {
                flex: 1;
                min-width: 0;
            }
            .chart-image, .div-table-wrapper {
                break-inside: avoid;
                margin-bottom: 15px;
            }
            .chart-image img {
                width: 80%;
                display: block;
                margin: 0 auto;
            }
            .section-title {
                text-align: center;
                margin: 10px 0;
                font-size: 12px;
                font-weight: bold;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 8px;
            }
            th, td {
                border: 1px solid #000;
                padding: 3px;
                font-size: 8px;
            }
            th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            .remaining-content {
                column-count: 2;
                column-gap: 15mm;
                column-fill: balance;
            }
        </style>
    </head>
    <body>';

    // Page 1
    $html .= '<div class="page-container" style="display: flex; flex-direction: row; gap: 15mm;">';
    $html .= '<div class="col">';
    if (!empty($data['birthInfo'])) {
        $html .= '<div class="div-table-wrapper"><h4 class="section-title">-- பிறப்பு விவரங்கள் --</h4>' . $data['birthInfo'] . '</div>';
    }
    $html .= '</div>';
    $html .= '<div class="col">';
    if (!empty($data['mainChartImage'])) {
        $html .= '<div class="chart-image"><h4 class="section-title">-- ஜாதக சக்கரம் (D1) --</h4><img src="' . $data['mainChartImage'] . '"/></div>';
    }
    if (!empty($data['navamsamChartImage'])) {
        $html .= '<div class="chart-image"><h4 class="section-title">-- நவாம்சம் --</h4><img src="' . $data['navamsamChartImage'] . '"/></div>';
    }
    if (!empty($data['bhavaChartImage'])) {
        $html .= '<div class="chart-image"><h4 class="section-title">-- பாவ சக்கரம் --</h4><img src="' . $data['bhavaChartImage'] . '"/></div>';
    }
    $html .= '</div>';
    $html .= '</div>';

    // Page 2 (conditional)
    $page2Content = '';
    $page2Col1 = '';
    $page2Col2 = '';
    if (!empty($data['starlord'])) $page2Col1 .= '<div class="div-table-wrapper"><h4 class="section-title">-- கிரக சாரம் --</h4>' . $data['starlord'] . '</div>';
    if (!empty($data['gocharam'])) $page2Col1 .= '<div class="div-table-wrapper"><h4 class="section-title">-- கோட்சாரம் --</h4>' . $data['gocharam'] . '</div>';
    if (!empty($data['thithiYogamKaranam'])) $page2Col1 .= '<div class="div-table-wrapper"><h4 class="section-title">-- திதி/யோகம் --</h4>' . $data['thithiYogamKaranam'] . '</div>';
    if (!empty($data['abhijit'])) $page2Col2 .= '<div class="div-table-wrapper"><h4 class="section-title">-- வைநாசிகம் --</h4>' . $data['abhijit'] . '</div>';
    if (!empty($data['kp'])) $page2Col2 .= '<div class="div-table-wrapper"><h4 class="section-title">-- KP விவரங்கள் --</h4>' . $data['kp'] . '</div>';

    if (!empty($page2Col1) || !empty($page2Col2)) {
        $html .= '<div class="page-container" style="display: flex; flex-direction: row; gap: 15mm;">';
        $html .= '<div class="col">' . $page2Col1 . '</div>';
        $html .= '<div class="col">' . $page2Col2 . '</div>';
        $html .= '</div>';
    }

    // Remaining Content
    $html .= '<div class="remaining-content">';
    if (!empty($data['dasa'])) {
        $html .= '<div class="div-table-wrapper"><h4 class="section-title">-- தசா --</h4>' . $data['dasa'] . '</div>';
    }
    if (!empty($data['bukthi'])) {
        $html .= '<div class="div-table-wrapper"><h4 class="section-title">-- புக்தி --</h4>' . $data['bukthi'] . '</div>';
    }

    // Varga Charts
    $ashtaTitleMap = ['1' => 'சூரி', '2' => 'சந்', '3' => 'செ', '4' => 'புத', '5' => 'குரு', '6' => 'சுக்', '7' => 'சனி', '8' => 'லக்', '9' => 'சர்வாஷ்டக வர்கம்'];

    foreach ($data['vargaCharts'] as $id => $image) {
        if ($image) {
            $title = "வர்க சக்கரம் ({$id})";
            $html .= "<div class=\"chart-image\"><h4 class=\"section-title\">-- {$title} --</h4><img src=\"{$image}\"/></div>";
        }
    }
    foreach ($data['ashtaCharts'] as $id => $image) {
        if ($image) {
            $title = isset($ashtaTitleMap[str_replace('a', '', $id)]) ? $ashtaTitleMap[str_replace('a', '', $id)] . ' பின்னாஷ்டக வர்கம்' : "அஷ்டவர்கம் ({$id})";
            $html .= "<div class=\"chart-image\"><h4 class=\"section-title\">-- {$title} --</h4><img src=\"{$image}\"/></div>";
        }
    }

    // Parigaaram
    if (!empty($data['parigaaram'])) {
        $html .= '<div class="div-table-wrapper"><h4 class="section-title">-- பரிகாரங்கள் --</h4>' . $data['parigaaram'] . '</div>';
    }

    $html .= '</div></body></html>';
    return $html;
}

// Dompdf setup and execution
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('isPhpEnabled', true);
$options->set('defaultFont', 'DejaVu Sans'); // Required for Unicode support

$dompdf = new Dompdf($options);
$dompdf->loadHtml(generateHtmlContent($data));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('birth_chart.pdf', ['Attachment' => true]);
?>
