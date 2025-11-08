<?php
// Configuration
// Auto-detect swetest executable based on OS
// On Linux, swetest is in sweph folder; on Windows it's in current directory
$swetest_path = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'swetest64' : './sweph/swetest';
$sweph_path = './sweph/'; // Path to Swiss Ephemeris data files
$longitude = '80.2785'; // Change to your longitude
$latitude = '13.0878'; // Change to your latitude
$timezone = 'Asia/Kolkata'; // Your local timezone
$date = date('Y-m-d'); // Today's date
$output_file = 'lagna.txt';

// Function to generate lagna data for a specific location and date
// This is the same function used in panchang_pushkara_times.php
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
    
    echo "Generating lagna data in batches...\n";
    
    for ($offset = 0; $offset < $total_minutes; $offset += $batch_size) {
        $remaining = min($batch_size, $total_minutes - $offset);
        
        // Calculate start time for this batch
        $batch_dt = clone $dt_utc;
        $batch_dt->modify("+$offset minutes");
        $batch_time = $batch_dt->format('H:i:s');
        $batch_date = $batch_dt->format('d.m.Y');
        
        $cmd = "$swetest_path -edir$sweph_path -b$batch_date -ut$batch_time -n$remaining -s1m -sid1 -eswe -fPl -head -house$lon,$lat,P";
        
        echo "Running command: $cmd\n";
        
        $output = [];
        $ret = 0;
        exec($cmd . " 2>&1", $output, $ret);
        
        if ($ret !== 0) {
            echo "ERROR: swetest failed at offset $offset with return code: $ret\n";
            echo "Command output:\n";
            foreach ($output as $line) {
                echo "  $line\n";
            }
            return false;
        }
        
        if (empty($output)) {
            echo "ERROR: No output from swetest at offset $offset\n";
            return false;
        }
        
        // Extract Ascendant lines from this batch
        $before_count = count($lagnaData);
        foreach ($output as $line) {
            $line = trim($line);
            if (preg_match('/^Ascendant\s+([0-9]+(\.[0-9]+)?)/', $line, $matches)) {
                $lagnaData[] = "Ascendant $matches[1]";
            }
        }
        
        $extracted = count($lagnaData) - $before_count;
        echo "Batch $offset-" . ($offset + $remaining) . ": extracted $extracted, total " . count($lagnaData) . "\n";
    }
    
    return $lagnaData;
}

// Debug info
$dt_local = new DateTime($date . ' 00:00:00', new DateTimeZone($timezone));
$dt_utc = clone $dt_local;
$dt_utc->setTimezone(new DateTimeZone('UTC'));

echo "Debug Info:\n";
echo "Local Timezone: $timezone\n";
echo "Local Midnight: " . $dt_local->format('Y-m-d H:i:s') . " $timezone\n";
echo "UTC Equivalent: " . $dt_utc->format('Y-m-d H:i:s') . " UTC\n";
echo "Location: Lat=$latitude, Lon=$longitude\n";
echo "\n";

// Generate lagna data using the same function as panchang_pushkara_times.php
$lagnaData = generateLagnaData($swetest_path, $sweph_path, $date, $longitude, $latitude, $timezone);

if ($lagnaData === false || count($lagnaData) !== 1440) {
    echo "ERROR: Failed to generate complete lagna data\n";
    exit(1);
}

// Write to lagna.txt file
$fh = fopen($output_file, 'w');
foreach ($lagnaData as $lagna_line) {
    fwrite($fh, $lagna_line . "\n");
}
fclose($fh);

echo "\nlagna.txt generated successfully!\n";
echo "File contains " . count($lagnaData) . " minutes of lagna data\n";
echo "Date: " . $dt_local->format('Y-m-d') . " (local timezone: $timezone)\n";
echo "Time range: 00:00 to 23:59 local time\n";
?>
