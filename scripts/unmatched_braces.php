<?php
$filepath = __DIR__ . '/../new_panchang.php';
$lines = file($filepath);
$start = 203; $end = 1617;
$stack = [];
for ($i=$start;$i<=$end;$i++){
    $line = isset($lines[$i]) ? $lines[$i] : '';
    for ($j=0;$j<strlen($line);$j++){
        $ch = $line[$j];
        if ($ch === '{') $stack[] = [$i+1, $j+1, trim($line)];
        if ($ch === '}') array_pop($stack);
    }
}
if (count($stack) === 0){
    echo "All braces matched\n";
} else {
    echo "Unmatched opens: " . count($stack) . "\n";
    foreach ($stack as $s){
        echo "Line {$s[0]} Col {$s[1]}: {$s[2]}\n";
    }
}
?>