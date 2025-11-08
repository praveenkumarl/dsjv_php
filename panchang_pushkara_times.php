<?php
// ---------------- CONFIG ----------------
$lagnaFile = './lagna.txt'; // path to lagna.txt
$pushkara_padas = [7,9,12,14,24,26,28,30,43,45,48,50,60,62,64,66,79,81,84,86,96,98,100,102];
// Base precomputed location (Chennai)
$baseLat = 13.0878;
$baseLon = 80.2785;
// Desired display timezone, overridable by query param 'tz'
$timezone = isset($_GET['tz']) ? $_GET['tz'] : 'Asia/Kolkata';
// Swiss Ephemeris CLI (auto-detect based on OS)
// On Linux, swetest is in sweph folder; on Windows it's in current directory
$swetest_path = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'swetest64' : './sweph/swetest';
$sweph_path = './sweph/';
// The lagna.txt file contains 1440 minutes starting at 00:00 LOCAL time for a given date.
// Each line represents one minute of the local day (minute 0 = 00:00 local, minute 1439 = 23:59 local).
// You may pass a date via ?date=YYYY-MM-DD to generate lagna for that local date.
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
date_default_timezone_set($timezone); // set default for output formatting
$utcTz = new DateTimeZone('UTC');
$localTz = new DateTimeZone($timezone);
// Optional target location (used for fast shift without regenerating per-user)
// Accept multiple aliases and optional city name
$targetLat = null;
$targetLon = null;
if (isset($_GET['lat'])) $targetLat = floatval($_GET['lat']);
if (isset($_GET['latitude'])) $targetLat = floatval($_GET['latitude']);
if (isset($_GET['lon'])) $targetLon = floatval($_GET['lon']);
if (isset($_GET['lng'])) $targetLon = floatval($_GET['lng']);
if (isset($_GET['longitude'])) $targetLon = floatval($_GET['longitude']);

// Basic city lookup map (extend as needed)
$cityMap = [
    'chennai' => ['lat' => 13.0878, 'lon' => 80.2785, 'tz' => 'Asia/Kolkata'],
    'bengaluru' => ['lat' => 12.9716, 'lon' => 77.5946, 'tz' => 'Asia/Kolkata'],
    'bangalore' => ['lat' => 12.9716, 'lon' => 77.5946, 'tz' => 'Asia/Kolkata'],
    'mumbai' => ['lat' => 19.0760, 'lon' => 72.8777, 'tz' => 'Asia/Kolkata'],
    'delhi' => ['lat' => 28.6139, 'lon' => 77.2090, 'tz' => 'Asia/Kolkata'],
    'hyderabad' => ['lat' => 17.3850, 'lon' => 78.4867, 'tz' => 'Asia/Kolkata'],
    'kolkata' => ['lat' => 22.5726, 'lon' => 88.3639, 'tz' => 'Asia/Kolkata'],
    'pune' => ['lat' => 18.5204, 'lon' => 73.8567, 'tz' => 'Asia/Kolkata']
];
if (isset($_GET['city'])) {
    $cityKey = strtolower(trim($_GET['city']));
    if (isset($cityMap[$cityKey])) {
        if ($targetLat === null) $targetLat = $cityMap[$cityKey]['lat'];
        if ($targetLon === null) $targetLon = $cityMap[$cityKey]['lon'];
        if (isset($_GET['tz']) === false && !empty($cityMap[$cityKey]['tz'])) {
            $timezone = $cityMap[$cityKey]['tz'];
            $localTz = new DateTimeZone($timezone);
        }
    }
}

// Use target location if provided, otherwise use base (Chennai)
$useLat = ($targetLat !== null) ? $targetLat : $baseLat;
$useLon = ($targetLon !== null) ? $targetLon : $baseLon;

// Exact refinement toggle
$exactRefine = isset($_GET['exact']) && $_GET['exact'] == '1';
// -----------------------------------------

// Function to generate lagna data for a specific location and date
function generateLagnaData($swetest_path, $sweph_path, $date, $lon, $lat, $timezone) {
    // Start from midnight LOCAL time, convert to UTC for swetest
    // This ensures lagna.txt contains data for local day (00:00 to 23:59 local time)
    $dt_local = new DateTime($date . ' 00:00:00', new DateTimeZone($timezone));
    $dt_utc = clone $dt_local;
    $dt_utc->setTimezone(new DateTimeZone('UTC'));
    
    $utc_start_time = $dt_utc->format('H:i:s');
    $utc_date = $dt_utc->format('d.m.Y');
    
    // Run in batches to avoid swetest's line limit (36525 lines)
    // Each iteration outputs ~33 lines, so max iterations per batch = 36525/33 ≈ 1100
    $batch_size = 1100;
    $total_minutes = 1440;
    $lagnaData = [];
    
    for ($offset = 0; $offset < $total_minutes; $offset += $batch_size) {
        $remaining = min($batch_size, $total_minutes - $offset);
        
        // Calculate start time for this batch
        $batch_dt = clone $dt_utc;
        $batch_dt->modify("+$offset minutes");
        $batch_time = $batch_dt->format('H:i:s');
        $batch_date = $batch_dt->format('d.m.Y');
        
        $cmd = "$swetest_path -edir$sweph_path -b$batch_date -ut$batch_time -n$remaining -s1m -sid1 -eswe -fPl -head -house$lon,$lat,P";
        
        $output = [];
        $ret = 0;
        // Capture stderr as well for debugging
        @exec($cmd . " 2>&1", $output, $ret);
        
        if ($ret !== 0 || empty($output)) {
            error_log("ERROR: swetest failed for lon=$lon, lat=$lat, date=$date, offset=$offset, ret=$ret");
            error_log("Command: $cmd");
            if (!empty($output)) {
                error_log("Output: " . implode("\n", $output));
            }
            return false;
        }
        
        // Extract Ascendant lines from this batch
        foreach ($output as $line) {
            $line = trim($line);
            if (preg_match('/^Ascendant\s+([0-9]+(\.[0-9]+)?)/', $line, $matches)) {
                $lagnaData[] = "Ascendant $matches[1]";
            }
        }
    }
    
    return $lagnaData;
}

// Function to convert Lagna degree to total Pada (1-108)
function getPadaFromLongitude($longitude) {
    $longitude = fmod($longitude,360);
    if($longitude < 0) $longitude += 360;
    $pada_size = 3 + 20/60; // 3°20'
    $pada = floor($longitude / $pada_size) + 1;
    
    return $pada;
}

// Function to get Nakshatra and Pada number from total Pada
function getNakshatraAndPada($total_pada) {
    // Short Tamil names from lib.php $nak_arr
    $nakshatras = [
        "அசுவி","பரணி","கிருத்","ரோகி","மிருக","திருவா",
        "புனர்","பூசம்","ஆயில்","மகம்","பூரம்","உத்ர",
        "ஹஸ்த","சித்தி","சுவா","விசா","அனுஷ","கேட்",
        "மூலம்","பூரா","உத்ராட","திருவோ","அவிட்",
        "சதய","பூரட்","உத்ரட்","ரேவதி"
    ];
    $nakshatra_index = floor(($total_pada - 1) / 4);
    $pada_number = (($total_pada - 1) % 4) + 1;
    
    return ['nakshatra' => $nakshatras[$nakshatra_index], 'pada_number' => $pada_number];
}

// Clean up old lagna cache files (from previous dates)
function cleanupOldLagnaCache($currentDate) {
    $dir = __DIR__ . DIRECTORY_SEPARATOR;
    $pattern = $dir . 'lagna_*.txt';
    $files = glob($pattern);
    
    foreach ($files as $file) {
        // Extract date from filename: lagna_YYYY-MM-DD_lat_lon.txt
        if (preg_match('/lagna_(\d{4}-\d{2}-\d{2})_/', basename($file), $matches)) {
            $fileDate = $matches[1];
            // Delete if not today's date
            if ($fileDate !== $currentDate) {
                @unlink($file);
            }
        }
    }
}

// Clean up cache files from previous dates
cleanupOldLagnaCache($date);

// Generate Lagna data dynamically for the requested location and date
$lagnaData = generateLagnaData($swetest_path, $sweph_path, $date, $useLon, $useLat, $timezone);
if ($lagnaData === false || count($lagnaData) < 1000) {
    die("Failed to generate lagna data for lon=$useLon lat=$useLat");
}

// Persist lagna data to disk for visibility and reuse
$lagna_txt_path = __DIR__ . DIRECTORY_SEPARATOR . 'lagna.txt';
@file_put_contents($lagna_txt_path, implode(PHP_EOL, $lagnaData) . PHP_EOL);
// Also save a location/date-specific cache file
$safeLat = str_replace(['-','.', ' '], ['m','d',''], sprintf('%.4f', $useLat));
$safeLon = str_replace(['-','.', ' '], ['m','d',''], sprintf('%.4f', $useLon));
$cacheName = __DIR__ . DIRECTORY_SEPARATOR . 'lagna_' . $date . '_' . $safeLat . '_' . $safeLon . '.txt';
@file_put_contents($cacheName, implode(PHP_EOL, $lagnaData) . PHP_EOL);

// Pushkara detection
$results = [];
$inPush = false;
$pushStartMinute = 0;
$startLagna = 0;
$pushStartPada = null;
$prevLagna = null; // lagna of previous minute (used to set end lagna accurately)
$prevPada = null;

// helper: convert minute index to DateTime in local timezone
// Since lagna.txt starts at 00:00 LOCAL time, minute index directly maps to local time
function minuteIndexToLocalDateTime($dateLocalStr, $minuteIndex, $utcTz, $localTz) {
    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateLocalStr . ' 00:00', $localTz);
    if ($dt === false) return false;
    $dt = $dt->add(new DateInterval('PT' . intval($minuteIndex) . 'M'));
    return $dt;
}

// Flex variant to support negative and >1440 minute indices
function minuteIndexToLocalDateTimeFlex($dateLocalStr, $minuteIndex, $utcTz, $localTz) {
    $dt = new DateTimeImmutable($dateLocalStr . ' 00:00:00', $localTz);
    $dt = $dt->modify(($minuteIndex >= 0 ? '+' : '') . intval($minuteIndex) . ' minutes');
    return $dt;
}

// Normalize minute index into [0, 1439]
function normIndex1440($i) {
    $m = $i % 1440;
    if ($m < 0) $m += 1440;
    return $m;
}

// Shift computed results by longitude-only offset (approx): shift = -(lon - baseLon)*4 minutes
function shiftResultsForLongitude(array $results, $date, $baseLon, $targetLon, $utcTz, $targetTzObj, array $lagnaData) {
    $deltaLon = $targetLon - $baseLon;
    $shift = (int) round(-$deltaLon * 4.0);
    $out = [];
    foreach ($results as $r) {
        if (!isset($r['start_minute'], $r['end_minute'])) { $out[] = $r; continue; }
        $s = intval($r['start_minute']) + $shift;
        $e = intval($r['end_minute']) + $shift;

        // Recompute display strings in target TZ
        $dtS = minuteIndexToLocalDateTimeFlex($date, $s, $utcTz, $targetTzObj);
        $dtE = minuteIndexToLocalDateTimeFlex($date, $e, $utcTz, $targetTzObj);
        $r['start'] = $dtS ? $dtS->format('Y-m-d H:i') : '-';
        $r['end']   = $dtE ? $dtE->format('Y-m-d H:i') : '-';

        // Keep shifted indices (may be outside 0..1439)
        $r['start_minute'] = $s;
        $r['end_minute']   = $e;

        // Optional: degrees from base file at modulo indices (approx across latitudes)
        $sIdx = normIndex1440($s);
        $eIdx = normIndex1440($e);
        if (isset($lagnaData[$sIdx])) {
            $p = preg_split('/\s+/', trim($lagnaData[$sIdx]));
            if (isset($p[1]) && is_numeric($p[1])) $r['start_lagna'] = round(floatval($p[1]), 4);
        }
        if (isset($lagnaData[$eIdx])) {
            $p = preg_split('/\s+/', trim($lagnaData[$eIdx]));
            if (isset($p[1]) && is_numeric($p[1])) $r['end_lagna'] = round(floatval($p[1]), 4);
        }
        $out[] = $r;
    }
    return $out;
}

    // ---------------- EXACT REFINEMENT (optional) ----------------
    // Run swetest for a UTC DateTime start and number of minutes at a given lon/lat and return array of Ascendant degrees
    function runAscSeries($swetest_path, $sweph_path, DateTimeImmutable $startUtc, int $minutes, float $lon, float $lat): array {
        if ($minutes <= 0) return [];
        $bDate = $startUtc->format('d.m.Y');
        $ut = $startUtc->format('H:i');
        $cmd = $swetest_path . ' -edir' . $sweph_path . ' -b' . $bDate . ' -ut' . $ut . ' -n' . $minutes . ' -s1 -sid1 -eswe -fPl -head -house=' . $lon . ',' . $lat;
        $lines = [];
        $ret = 0;
        @exec($cmd, $lines, $ret);
        if ($ret !== 0 || empty($lines)) return [];
        $out = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^Ascendant\s+([0-9]+(?:\.[0-9]+)?)/', $line, $m)) {
                $out[] = (float)$m[1];
            }
        }
        return $out;
    }

    // Given a degree series, find first index where isPush becomes true with previous false (entry)
    function findEntryIndex(array $degrees, array $pushkara_padas): ?int {
        $prevPush = null;
        foreach ($degrees as $i => $deg) {
            $p = getPadaFromLongitude($deg);
            $isPush = in_array($p, $pushkara_padas);
            if ($i > 0 && $isPush && $prevPush === false) return $i;
            if ($prevPush === null) $prevPush = $isPush; else $prevPush = $isPush;
        }
        return null;
    }

    // Given a degree series, find last index inside pushkara before it switches to non-push (exit minute index relative to series start)
    function findExitIndex(array $degrees, array $pushkara_padas): ?int {
        $lastPush = null;
        foreach ($degrees as $i => $deg) {
            $p = getPadaFromLongitude($deg);
            $isPush = in_array($p, $pushkara_padas);
            if ($isPush) $lastPush = $i;
            elseif ($lastPush !== null) break;
        }
        return $lastPush;
    }

    // Refine each window around its (possibly shifted) start/end minutes using target lat/lon
    function refineWindowsExact(array $results, string $date, float $targetLon, float $targetLat, DateTimeZone $utcTz, DateTimeZone $targetTzObj, array $pushkara_padas, $swetest_path, $sweph_path): array {
        $band = 40; // minutes around boundaries
        $out = [];
        foreach ($results as $r) {
            if (!isset($r['start_minute'], $r['end_minute'])) { $out[] = $r; continue; }
            $s = intval($r['start_minute']);
            $e = intval($r['end_minute']);

            // Refine start
            $startBandStart = $s - intdiv($band, 2);
            $startDt = (new DateTimeImmutable($date . ' 00:00:00', $utcTz))->modify(($startBandStart >= 0 ? '+' : '') . $startBandStart . ' minutes');
            $seriesS = runAscSeries($swetest_path, $sweph_path, $startDt, $band, $targetLon, $targetLat);
            $entry = findEntryIndex($seriesS, $pushkara_padas);
            $newS = ($entry !== null) ? ($startBandStart + $entry) : $s;

            // Refine end
            $endBandStart = $e - intdiv($band, 2);
            $endDt = (new DateTimeImmutable($date . ' 00:00:00', $utcTz))->modify(($endBandStart >= 0 ? '+' : '') . $endBandStart . ' minutes');
            $seriesE = runAscSeries($swetest_path, $sweph_path, $endDt, $band, $targetLon, $targetLat);
            $exit = findExitIndex($seriesE, $pushkara_padas);
            $newE = ($exit !== null) ? ($endBandStart + $exit) : $e;

            // Update times and lagna from refined indices
            $dtS = minuteIndexToLocalDateTimeFlex($date, $newS, $utcTz, $targetTzObj);
            $dtE = minuteIndexToLocalDateTimeFlex($date, $newE, $utcTz, $targetTzObj);
            $r['start'] = $dtS ? $dtS->format('Y-m-d H:i') : $r['start'];
            $r['end']   = $dtE ? $dtE->format('Y-m-d H:i') : $r['end'];
            $r['start_minute'] = $newS;
            $r['end_minute'] = $newE;

            // Also compute refined degrees at the exact minutes
            // We can reuse the series if within their bands; else compute one-off
            $getDegAt = function(int $idx, int $bandStart, array $series) use ($swetest_path, $sweph_path, $date, $utcTz, $targetLon, $targetLat) {
                $offset = $idx - $bandStart;
                if ($offset >= 0 && $offset < count($series)) return round((float)$series[$offset], 4);
                // one-off fetch
                $dt = (new DateTimeImmutable($date . ' 00:00:00', $utcTz))->modify(($idx >= 0 ? '+' : '') . $idx . ' minutes');
                $one = runAscSeries($swetest_path, $sweph_path, $dt, 1, $targetLon, $targetLat);
                return !empty($one) ? round((float)$one[0], 4) : null;
            };
            $startDeg = $getDegAt($newS, $startBandStart, $seriesS);
            $endDeg   = $getDegAt($newE, $endBandStart, $seriesE);
            if ($startDeg !== null) $r['start_lagna'] = $startDeg;
            if ($endDeg !== null)   $r['end_lagna'] = $endDeg;

            $out[] = $r;
        }
        return $out;
    }

// helper: get lagna for a specific local time (hour, minute)
// Since lagna.txt now starts at 00:00 LOCAL time, minute index = hour*60 + minute
function getLagnaForLocalTime($lagnaData, $hour, $minute) {
    $idx = $hour * 60 + $minute;
    if (isset($lagnaData[$idx])) {
        $parts = preg_split('/\s+/', trim($lagnaData[$idx]));
        return isset($parts[1]) ? floatval($parts[1]) : null;
    }
    return null;
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

    // Only start a pushkara period on transition from non-pushkara to pushkara pada
    if ($isPush && !$inPush && (!$prevPada || !in_array($prevPada, $pushkara_padas))) {
        $inPush = true;
        $pushStartMinute = $minute;
        $startLagna = $lagna;
        $pushStartPada = $pada;
    }
    // Only end a pushkara period when pada changes OR at least 13 minutes have elapsed (one pada = 3.333° × 4 min/° = 13.33 min)
    $pada_duration_minutes = 13; // Minimum duration for one pada
    $elapsed_minutes = $minute - $pushStartMinute;
    if ($inPush && ($pada !== $pushStartPada || !$isPush || !in_array($pada, $pushkara_padas)) && $elapsed_minutes >= $pada_duration_minutes) {
        $inPush = false;
        $endMinute = $minute - 1;
        $endLagna = $prevLagna;
        // prepare display times in local timezone
        $dtStartLocal = minuteIndexToLocalDateTime($date, $pushStartMinute, $utcTz, $localTz);
        $dtEndLocal = minuteIndexToLocalDateTime($date, $endMinute, $utcTz, $localTz);
        $startStr = $dtStartLocal ? $dtStartLocal->format('Y-m-d H:i') : sprintf('%02d:%02d', floor($pushStartMinute/60), $pushStartMinute%60);
        $endStr = $dtEndLocal ? $dtEndLocal->format('Y-m-d H:i') : sprintf('%02d:%02d', floor($endMinute/60), $endMinute%60);
        $nak = getNakshatraAndPada($pushStartPada);
        // Only add if the period is at least 2 minutes (to avoid 0-min glitches)
        if ($endMinute > $pushStartMinute) {
            $results[] = [
                'start' => $startStr,
                'end' => $endStr,
                'nakshatra' => $nak['nakshatra'],
                'pada' => $nak['pada_number'],
                'total_pada' => $pushStartPada,
                'start_lagna' => round($startLagna, 4),
                'end_lagna' => round($endLagna, 4),
                'start_minute' => $pushStartMinute,
                'end_minute' => $endMinute
            ];
        }
        // Reset variables for next pushkara period
        $startLagna = 0;
        $pushStartPada = null;
        $pushStartMinute = 0;
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
        'total_pada' => $pushStartPada ?? getPadaFromLongitude($lagna ?? 0),
        'start_lagna' => round($startLagna, 2),
        'end_lagna' => round($endLagna, 2)
    ];
}

// Note: Since we generate lagna data for the exact location, no additional shifting is required.
// Optional: an exact boundary refinement could still be applied here if desired for micro-accuracy.

// ---------------- HTML Output ----------------
?>
<center>
<div class='divTable blueTable' style='height:40px;'>
    <div class='divTableRow' style='background-color:rgba(0,0,0,0)'>
        <div class='divPanchangHeading' colspan='3'>
            <H6 style='font-weight:bold;color:black'>புஷ்கர நவாம்சம்</H6>
        </div>
    </div>
</div>

<?php if (empty($results)): ?>
<p>இன்றைய தினத்தில் புஷ்கர நவாம்சம் இல்லை.</p>
<?php else: ?>
<div class='divTable blueTable' id='pushkaraDetails'>
<div class='divTableRow'>
    <div class='divPanchangHeading'>தொடக்கம்</div>
    <div class='divPanchangHeading'>முடிவு</div>
    <div class='divPanchangHeading'>நட்சத்திர பாதம்</div>
</div>
<?php
foreach ($results as $row) {
    // Derive times and degrees directly from minute indices to ensure alignment
    $startIdx = isset($row['start_minute']) ? intval($row['start_minute']) : null;
    $endIdx = isset($row['end_minute']) ? intval($row['end_minute']) : null;

    $dtStartLocal = $startIdx !== null ? minuteIndexToLocalDateTimeFlex($date, $startIdx, $utcTz, $localTz) : null;
    $dtEndLocal = $endIdx !== null ? minuteIndexToLocalDateTimeFlex($date, $endIdx, $utcTz, $localTz) : null;

    $startStr = $dtStartLocal ? $dtStartLocal->format('Y-m-d H:i') : (isset($row['start']) ? $row['start'] : '-');
    $endStr = $dtEndLocal ? $dtEndLocal->format('Y-m-d H:i') : (isset($row['end']) ? $row['end'] : '-');

    // Combine Nakshatra and Pada with a space
    $nakshatraPada = htmlspecialchars($row['nakshatra']) . ' ' . htmlspecialchars($row['pada']);

    echo '<div class="divTableRow">';
    echo '<div class="divPanchang">' . htmlspecialchars($startStr) . '</div>';
    echo '<div class="divPanchang">' . htmlspecialchars($endStr) . '</div>';
    echo '<div class="divPanchang">' . $nakshatraPada . '</div>';
    echo '</div>';
}
?>
</div>
<?php endif; ?>
</center>

<style>
#pushkaraDetails .divTableRow {
    font-weight: bold !important;
}
#pushkaraDetails .divPanchang {
    font-weight: bold !important;
}
#pushkaraDetails .divPanchangHeading {
    font-weight: bold !important;
}
</style>