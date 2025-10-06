<?php
// ---------------- CONFIG ----------------
$lagnaFile = './lagna.txt'; // path to lagna.txt
$pushkara_padas = [7,9,12,14,24,26,28,30,43,45,48,50,60,62,64,66,79,81,84,86,96,98,100,102];
$timezone = 'Asia/Kolkata'; // desired timezone (display/output tz)
// The lagna.txt file lines are minutes starting at 00:00 UTC for a given date.
// You may pass a date via ?date=YYYY-MM-DD (UTC) to map the minutes to that day.
$date = isset($_GET['date']) ? $_GET['date'] : gmdate('Y-m-d');
date_default_timezone_set($timezone); // set default for output formatting
$utcTz = new DateTimeZone('UTC');
$localTz = new DateTimeZone($timezone);
// -----------------------------------------

// Function to convert Lagna degree to total Pada (1-108)
function getPadaFromLongitude($longitude) {
    $longitude = fmod($longitude,360);
    if($longitude < 0) $longitude += 360;
    $pada_size = 3 + 20/60; // 3°20'
    return floor($longitude / $pada_size) + 1;
}

// Function to get Nakshatra and Pada number from total Pada
function getNakshatraAndPada($total_pada) {
    $nakshatras = [
        "Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardra",
        "Punarvasu","Pushya","Ashlesha","Magha","Purva Phalguni","Uttara Phalguni",
        "Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshtha",
        "Mula","Purva Ashadha","Uttara Ashadha","Shravana","Dhanishta",
        "Shatabhisha","Purva Bhadrapada","Uttara Bhadrapada","Revati"
    ];
    $nakshatra_index = floor(($total_pada - 1) / 4);
    $pada_number = (($total_pada - 1) % 4) + 1;
    return ['nakshatra' => $nakshatras[$nakshatra_index], 'pada_number' => $pada_number];
}

// Read Lagna data from file
// Read Lagna data from file. Each line represents one minute starting at 00:00 UTC.
$lagnaData = file($lagnaFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lagnaData === false) die("Failed to read lagna.txt");

// Pushkara detection
$results = [];
$inPush = false;
$pushStartMinute = 0;
$startLagna = 0;
$pushStartPada = null;
$prevLagna = null; // lagna of previous minute (used to set end lagna accurately)
$prevPada = null;

// helper: convert UTC minute index to DateTime in local timezone
function minuteIndexToLocalDateTime($dateUtcStr, $minuteIndex, $utcTz, $localTz) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateUtcStr . ' 00:00', $utcTz);
    if ($dt === false) return false;
    $dt = $dt->add(new DateInterval('PT' . intval($minuteIndex) . 'M'));
    return $dt->setTimezone($localTz);
}

foreach ($lagnaData as $minute => $line) {
    $parts = preg_split('/\s+/', trim($line));
    if (count($parts) < 2) {
        // still advance prev values
        $prevLagna = $prevLagna;
        continue;
    }

    $lagna = floatval($parts[1]); // numeric column with lagna degrees
    $pada = getPadaFromLongitude($lagna);
    $isPush = in_array($pada, $pushkara_padas);

    // when entering a push period
    if ($isPush && !$inPush) {
        $inPush = true;
        $pushStartMinute = $minute;
        $startLagna = $lagna;
        $pushStartPada = $pada;
    }

    // when leaving a push period: current minute is non-push, so last push minute = minute-1
    if (!$isPush && $inPush) {
        $inPush = false;
        $endMinute = max(0, $minute - 1);
        // try to use previous lagna if available, otherwise use current
        $endLagna = ($prevLagna !== null) ? $prevLagna : $lagna;

        // prepare display times in local timezone
        $dtStartLocal = minuteIndexToLocalDateTime($date, $pushStartMinute, $utcTz, $localTz);
        $dtEndLocal = minuteIndexToLocalDateTime($date, $endMinute, $utcTz, $localTz);
        $startStr = $dtStartLocal ? $dtStartLocal->format('Y-m-d H:i') : sprintf('%02d:%02d', floor($pushStartMinute/60), $pushStartMinute%60);
        $endStr = $dtEndLocal ? $dtEndLocal->format('Y-m-d H:i') : sprintf('%02d:%02d', floor($endMinute/60), $endMinute%60);

        $nak = getNakshatraAndPada($pushStartPada);

        $results[] = [
            'start' => $startStr,
            'end' => $endStr,
            'nakshatra' => $nak['nakshatra'],
            'pada' => $nak['pada_number'],
            'start_lagna' => round($startLagna, 2),
            'end_lagna' => round($endLagna, 2)
        ];
    }

    // advance prev
    $prevLagna = $lagna;
    $prevPada = $pada;
}

// If still in Pushkara at the end of file
if ($inPush) {
    $lastIndex = max(0, count($lagnaData) - 1);
    $endMinute = $lastIndex;
    $endLagna = ($prevLagna !== null) ? $prevLagna : $lagna;

    $dtStartLocal = minuteIndexToLocalDateTime($date, $pushStartMinute, $utcTz, $localTz);
    $dtEndLocal = minuteIndexToLocalDateTime($date, $endMinute, $utcTz, $localTz);
    $startStr = $dtStartLocal ? $dtStartLocal->format('Y-m-d H:i') : sprintf('%02d:%02d', floor($pushStartMinute/60), $pushStartMinute%60);
    $endStr = $dtEndLocal ? $dtEndLocal->format('Y-m-d H:i') : '23:59';

    $nak = getNakshatraAndPada($pushStartPada ?? getPadaFromLongitude($lagna ?? 0));
    $results[] = [
        'start' => $startStr,
        'end' => $endStr,
        'nakshatra' => $nak['nakshatra'],
        'pada' => $nak['pada_number'],
        'start_lagna' => round($startLagna, 2),
        'end_lagna' => round($endLagna, 2)
    ];
}

// ---------------- HTML Output ----------------
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pushkara Navamsha Times</title>
<style>
body{font-family:sans-serif;padding:20px;}
table{border-collapse:collapse;width:100%;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
th{background:#f2f2f2;}
</style>
</head>
<body>
<h2>Pushkara Navamsha Times (Timezone: <?=htmlspecialchars($timezone)?>)</h2>

<?php if (empty($results)): ?>
<p>No Pushkara Navamsha periods on this day.</p>
<?php else: ?>
<table>
<tr><th>Start</th><th>End</th><th>Nakshatra</th><th>Pada</th><th>Start Lagna°</th><th>End Lagna°</th></tr>
<?php foreach ($results as $r): ?>
<tr>
<td><?=$r['start']?></td>
<td><?=$r['end']?></td>
<td><?=$r['nakshatra']?></td>
<td><?=$r['pada']?></td>
<td><?=$r['start_lagna']?></td>
<td><?=$r['end_lagna']?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>
