<?php
$filepath = __DIR__ . '/../new_panchang.php';
$lines = file($filepath);
$start = 203; $end = 1617;
$balance = 0;
$maxBalance = 0;
$maxLine = $start;
for ($i=$start; $i<=$end; $i++){
    $line = isset($lines[$i]) ? $lines[$i] : '';
    $opens = substr_count($line,'{');
    $closes = substr_count($line,'}');
    $balance += $opens - $closes;
    if ($balance > $maxBalance){ $maxBalance = $balance; $maxLine = $i+1; }
}
echo "Final balance: $balance\n";
echo "Max positive balance $maxBalance at line $maxLine\n";
for ($i=max($start,$maxLine-5); $i<=min($end,$maxLine+5); $i++){
    $num = $i+1;
    printf("%5d: %s", $num, $lines[$i]);
}
?>