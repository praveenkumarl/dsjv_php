<?php
// Configuration
$swetest_path = 'swetest64.exe'; // Path to swetest64.exe
$sweph_path = './sweph/'; // Path to Swiss Ephemeris data files
$longitude = '80.2785'; // Change to your longitude
$latitude = '13.0878'; // Change to your latitude
$timezone = 'Asia/Kolkata'; // Your local timezone
$date = date('d.m.Y'); // Today's date in DD.MM.YYYY
$output_file = 'lagna.txt';

// Calculate UTC start time accounting for daylight saving time
// Calculate UTC time for 5:30 AM in local timezone (accounts for DST)

// Always start at midnight UTC
$dt_utc = new DateTime('today 00:00', new DateTimeZone('UTC'));
$utc_start_time = '00:00:00';
$utc_date = $dt_utc->format('d.m.Y');

// Debug info

echo "Debug Info:\n";
echo "Date: " . $dt_utc->format('Y-m-d') . "\n";
echo "Timezone: UTC\n";
echo "UTC Midnight: " . $dt_utc->format('Y-m-d H:i:s') . " UTC\n";
echo "Swetest Command: $swetest_path -edir$sweph_path -b$utc_date -ut00:00:00 -n1440 -s1m -sid1 -eswe -fPl -head -house$longitude,$latitude,P\n";




// Build command - always start from midnight UTC
$cmd = "$swetest_path -edir$sweph_path -b$utc_date -ut00:00:00 -n1440 -s1m -sid1 -eswe -fPl -head -house$longitude,$latitude,P";
echo $cmd;

// Run swetest and capture output
exec($cmd, $output, $ret);
if ($ret !== 0 || empty($output)) {
    file_put_contents($output_file, "ERROR: swetest64.exe failed or no output\n");
    exit(1);
}

// Write formatted output
$fh = fopen($output_file, 'w');
foreach ($output as $line) {
    $line = trim($line);
    // Only keep lines that start with 'Ascendant' and have a number
    if (preg_match('/^Ascendant\s+([0-9]+(\.[0-9]+)?)/', $line, $matches)) {
        fwrite($fh, "Ascendant $matches[1]\n");
    }
}
fclose($fh);

echo "lagna.txt generated for $date\n";
?>
