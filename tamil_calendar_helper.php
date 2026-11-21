<?php
class PNKAstroCalendar {
    
    private $tamilYearsEn = [
        "Prabhava", "Vibhava", "Sukla", "Pramodoota", "Prajotpatti", "Aangeerasa", 
        "Sreemukha", "Bhaava", "Yuva", "Dhaatu", "Eesvara", "Bahudhanya", "Pramaathi", 
        "Vikrama", "Vrusha", "Chitrabhaanu", "Subhaanu", "Taarana", "Paarthiva", "Vyaya", 
        "Sarvajittu", "Sarvadhaari", "Virodhi", "Vikruti", "Khara", "Nandana", "Vijaya", 
        "Jaya", "Manmatha", "Durmukhi", "Hevilambi", "Vilambi", "Vikari", "Sarvari", 
        "Plava", "Subhakrutu", "Sobhakrutu", "Krodhi", "Visvaavasu", "Paraabhava", 
        "Plavanga", "Keelaka", "Saumya", "Saadharana", "Virodhikrutu", "Paridhaavi", 
        "Pramaadee", "Aananda", "Raakshasa", "Nala", "Pingala", "Kaalayukti", "Siddhaarthi", 
        "Raudri", "Durmati", "Dundubhi", "Rudhirodgaari", "Raktaakshi", "Krodhana", "Akshaya"
    ];

    private $tamilYearsTa = [
        "பிரபவ", "விபவ", "சுக்ல", "பிரமோதூத", "பிரஜோற்பத்தி", "ஆங்கீரஸ", 
        "ஸ்ரீமுக", "பவ", "யுவ", "தாது", "ஈஸ்வர", "பகுதானிய", "பிரமாதி", 
        "விக்ரம", "விஷு", "சித்திரபானu", "சுபானு", "தாரண", "பார்த்திப", "விய", 
        "சர்வஜித்", "சர்வதாரி", "விரோதி", "விக்ருதி", "கர", "நந்தன", "விஜய", 
        "ஜெய", "மன்மத", "துன்முகi", "ஹேவிளம்பி", "விளம்பி", "விகாரி", "சார்வரி", 
        "பிலவ", "சுபகிருது", "சோபகிருது", "க்ரோதி", "விசுவாவசு", "பராபவ", 
        "பிலவங்க", "கீலக", "சௌமிய", "சாதாரண", "விரோதிகிருது", "பரிதாபி", 
        "பிரமாதீச", "ஆனந்த", "ராட்சச", "நள", "பிங்கள", "காளயுக்தி", "சித்தார்த்தி", 
        "ரௌத்திரி", "துன்மதி", "துந்துபி", "ருத்ரோத்காரி", "ரக்தாட்சி", "க்ரோதன", "அட்சய"
    ];

    private $tamilMonthsEn = [
        "Chithirai", "Vaikasi", "Aani", "Aadi", "Avani", "Purattasi", 
        "Aippasi", "Karthikai", "Margazhi", "Thai", "Masi", "Panguni"
    ];

    private $tamilMonthsTa = [
        "சித்திரை", "வைகாசி", "ஆனி", "ஆடி", "ஆவணி", "புரட்டாசி", 
        "ஐப்பசி", "கார்த்திகை", "மார்கழி", "தை", "மாசி", "பங்குனி"
    ];

    // Reference location for Sunrise/Sunset (Chennai, Tamil Nadu)
    private $lat = 13.0827;
    private $lon = 80.2707;

    private function getSwetestPath() {
        $exe = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'swetest64.exe' : 'swetest';
        $paths = [
            __DIR__ . '/' . $exe,
            __DIR__ . '/sweph/' . $exe,
            dirname(__DIR__) . '/sweph/' . $exe,
            './sweph/' . $exe,
            './' . $exe
        ];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }
        return $exe;
    }

    private function getLibPath() {
        $dirs = [
            __DIR__ . '/sweph/',
            __DIR__ . '/',
            dirname(__DIR__) . '/sweph/',
            './sweph/',
            './'
        ];
        foreach ($dirs as $dir) {
            if (is_dir($dir) && file_exists($dir . 'sweph.dir')) {
                return str_replace('\\', '/', $dir);
            }
        }
        return './sweph/';
    }

    private function tsToJD($ts) {
        return 2440587.5 + ($ts / 86400.0);
    }

    private function jdToTs($jd) {
        return ($jd - 2440587.5) * 86400.0;
    }

    private function getNirayanaLongitude($jd) {
        $exe = $this->getSwetestPath();
        $libPath = $this->getLibPath();
        
        $ts = (int)round($this->jdToTs($jd));
        $date_str = gmdate('d.m.Y', $ts);
        $time_str = gmdate('H:i:s', $ts);
        
        $output = [];
        $cmd = "\"$exe\" -edir\"$libPath\" -b$date_str -ut$time_str -p0 -n1 -sid1 -eswe -fPl -g, -head";
        exec($cmd, $output);
        
        if (!empty($output)) {
            $row = explode(',', $output[0]);
            if (isset($row[1])) {
                return (float)$row[1];
            }
        }
        
        // Fallback to approximate Keplerian formula if swetest fails
        $T = ($jd - 2451545.0) / 36525.0;
        $L0 = fmod(280.46646 + 36000.76983 * $T + 0.0003032 * pow($T, 2), 360);
        $M = fmod(357.52911 + 35999.05029 * $T - 0.0001537 * pow($T, 2), 360);
        $C = (1.914602 - 0.004817 * $T - 0.000014 * pow($T, 2)) * sin(deg2rad($M))
           + (0.019993 - 0.000101 * $T) * sin(deg2rad(2 * $M))
           + 0.000289 * sin(deg2rad(3 * $M));
        $theta = $L0 + $C; 
        $omega = 125.04 - 1934.136 * $T;
        $lambda = $theta - 0.00569 - 0.00478 * sin(deg2rad($omega)); 
        $ayanamsa = 23.858833 + 1.3961666 * $T; 
        return fmod($lambda - $ayanamsa + 360, 360);
    }

    private function findSankranti($targetRasi, $approxJD) {
        $low = $approxJD - 3;
        $high = $approxJD + 3;
        $targetLon = $targetRasi * 30;

        for ($i = 0; $i < 40; $i++) {
            $mid = ($low + $high) / 2.0;
            $lon = $this->getNirayanaLongitude($mid);

            if ($targetRasi == 0 && $lon > 180) {
                $lon -= 360;
            }

            if ($lon < $targetLon) {
                $low = $mid;
            } else {
                $high = $mid;
            }
        }
        return $mid;
    }

    public function getTamilDate($dateString, $conn = null) {
        $targetTs = strtotime($dateString . ' 00:00:00 UTC'); 
        $jd = $this->tsToJD($targetTs);
        
        $lon = $this->getNirayanaLongitude($jd);
        $currentRasi = (int)floor($lon / 30);
        
        $daysIntoMonth = ($lon - ($currentRasi * 30)) / 0.9856; 
        $approxSankrantiJD = $jd - $daysIntoMonth;
        
        $sankrantiJD = $this->findSankranti($currentRasi, $approxSankrantiJD);
        $sankrantiTs = (int)round($this->jdToTs($sankrantiJD));
        
        $sankrantiDateStr = gmdate('Y-m-d', $sankrantiTs);
        $sunInfo = date_sun_info(strtotime($sankrantiDateStr . " 00:00:00 UTC"), $this->lat, $this->lon);
        // Traditional rule: Tamil calendar day runs sunrise-to-sunrise.
        // If sankranti is before that day's sunrise, the new month starts on that same calendar day.
        // If sankranti is at or after sunrise, the current day already "belongs" to the old month,
        // so the new month starts on the next calendar day.
        $sunriseTs = $sunInfo['sunrise'];
        
        $monthStartTs = ($sankrantiTs < $sunriseTs) 
            ? strtotime($sankrantiDateStr . " 00:00:00 UTC") 
            : strtotime($sankrantiDateStr . " +1 day 00:00:00 UTC");

        if ($targetTs < $monthStartTs) {
            $currentRasi = ($currentRasi - 1 + 12) % 12;
            $approxSankrantiJD -= 30;
            $sankrantiJD = $this->findSankranti($currentRasi, $approxSankrantiJD);
            $sankrantiTs = (int)round($this->jdToTs($sankrantiJD));
            
            $sankrantiDateStr = gmdate('Y-m-d', $sankrantiTs);
            $sunInfo = date_sun_info(strtotime($sankrantiDateStr . " 00:00:00 UTC"), $this->lat, $this->lon);
            $monthStartTs = ($sankrantiTs < $sunInfo['sunrise']) 
                ? strtotime($sankrantiDateStr . " 00:00:00 UTC") 
                : strtotime($sankrantiDateStr . " +1 day 00:00:00 UTC");
        }

        $dayDiff = round(($targetTs - $monthStartTs) / 86400);
        $tamilDay = $dayDiff + 1;

        $sankrantiYear = (int)gmdate('Y', $monthStartTs);
        $startYear = ($currentRasi >= 9) ? ($sankrantiYear - 1) : $sankrantiYear;
        
        $yearIndex = (($startYear - 1987) % 60 + 60) % 60;
        
        $result = [
            'english_date' => gmdate('Y-m-d', $targetTs),
            'tamil_day' => $tamilDay,
            'tamil_month' => [
                'en' => $this->tamilMonthsEn[$currentRasi],
                'ta' => $this->tamilMonthsTa[$currentRasi]
            ],
            'tamil_year' => [
                'en' => $this->tamilYearsEn[$yearIndex],
                'ta' => $this->tamilYearsTa[$yearIndex]
            ],
            'month_start_date' => gmdate('Y-m-d', $monthStartTs),
            'rasi_index' => $currentRasi
        ];

        if ($conn) {
            try {
                $stmt = $conn->prepare("INSERT IGNORE INTO tamil_months_cache (gregorian_start_date, tamil_year_en, tamil_year_ta, tamil_month_en, tamil_month_ta, rasi_index, sankranti_timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $result['month_start_date'], 
                    $result['tamil_year']['en'], 
                    $result['tamil_year']['ta'],
                    $result['tamil_month']['en'], 
                    $result['tamil_month']['ta'],
                    $currentRasi, 
                    $sankrantiTs
                ]);
            } catch(Exception $e) {} 
        }

        return $result;
    }
}
?>
