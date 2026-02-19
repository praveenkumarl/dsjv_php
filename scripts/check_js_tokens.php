<?php
$filepath = __DIR__ . '/../new_panchang.php';
$lines = file($filepath);
$start = 203; $end = 1617;
$txt = '';
for ($i=$start; $i<=$end; $i++) { if (isset($lines[$i])) $txt .= $lines[$i]; }
$counts = [
    'open' => substr_count($txt,'{'),
    'close' => substr_count($txt,'}'),
    'back' => substr_count($txt,'`'),
    'single' => substr_count($txt,"'"),
    'double' => substr_count($txt,'"')
];
echo json_encode($counts, JSON_PRETTY_PRINT) . PHP_EOL;
?>