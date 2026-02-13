<?php
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Birth Chart API
 * Generates a birth chart in PDF format.
 * 
 * Parameters (GET or POST):
 * - name: Name of the person
 * - sex: Male/Female
 * - date: YYYY-MM-DD
 * - time: HH:MM:SS
 * - place: City|Timezone (e.g., Chennai|Asia/Kolkata)
 * - father: Father's name (optional)
 * - mother: Mother's name (optional)
 * - caste: Caste (optional)
 * - office_name: Office name for header (optional)
 * - office_phone: Office phone for header (optional)
 * 
 * example URL
 * http://127.0.0.1/dsjv_home/birth_Chart_API.php?name=Praveen&date=1976-07-17&time=14:14:00&place=Chennai|Asia/Kolkata
 * http://127.0.0.1/dsjv_home/birth_Chart_API.php?name=Srividya&date=1979-06-13&time=02:47:00&place=Trichy|Asia/Kolkata&type=json
 */

// Set parameters for onepage_birthchart_pdf.php
$_GET['name'] = $_REQUEST['name'] ?? 'Guest';
$_GET['sex'] = $_REQUEST['sex'] ?? 'Male';
$_GET['nDate'] = $_REQUEST['date'] ?? date('Y-m-d');
$_GET['nTime'] = $_REQUEST['time'] ?? date('H:i:s');
$_GET['place'] = $_REQUEST['place'] ?? 'Chennai|Asia/Kolkata';
$_GET['rowid'] = $_REQUEST['rowid'] ?? 'Not saved';

$requestedDate = $_REQUEST['date'] ?? date('Y-m-d');

if (!isset($_SESSION)) {
    session_start();
}

function make_self_contained_html($html) {
    $baseDir = __DIR__;
    // Inline <link rel="stylesheet" href="...">
    if (preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
        foreach ($m[0] as $i => $linkTag) {
            $href = $m[1][$i];
            $localPath = null;
            if (preg_match('#^https?://#i', $href)) {
                // skip remote stylesheets
                continue;
            } elseif (strpos($href, '/') === 0) {
                $localPath = $baseDir . $href;
            } else {
                $localPath = $baseDir . '/' . $href;
            }
            if (file_exists($localPath)) {
                $css = file_get_contents($localPath);
                // inline @import rules inside this css
                if (preg_match_all('/@import\s+url\(["\']?([^"\')]+)["\']?\)\s*;?/i', $css, $imp)) {
                    foreach ($imp[1] as $impHref) {
                        $impPath = (strpos($impHref, '/') === 0) ? $baseDir . $impHref : $baseDir . '/' . $impHref;
                        if (file_exists($impPath)) {
                            $css = str_replace($imp[0][0], file_get_contents($impPath), $css);
                        }
                    }
                }
                $css = "\n/* inlined from $href */\n" . $css . "\n";
                $styleTag = '<style type="text/css">' . $css . '</style>';
                $html = str_replace($linkTag, $styleTag, $html);
            }
        }
    }

    // Inline local scripts (or remove external ones) to avoid differences
    if (preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>\s*<\/script>/i', $html, $ms)) {
        foreach ($ms[0] as $i => $scriptTag) {
            $src = $ms[1][$i];
            if (preg_match('#^https?://#i', $src)) {
                // leave remote scripts alone
                continue;
            }
            $localPath = (strpos($src, '/') === 0) ? $baseDir . $src : $baseDir . '/' . $src;
            if (file_exists($localPath)) {
                $js = file_get_contents($localPath);
                $new = '<script>' . $js . '</script>';
                $html = str_replace($scriptTag, $new, $html);
            } else {
                // remove the script tag if file not found (prevents remote loading differences)
                $html = str_replace($scriptTag, '', $html);
            }
        }
    }

    // Inline local images as data URIs
    if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $mi)) {
        foreach ($mi[0] as $i => $imgTag) {
            $src = $mi[1][$i];
            if (preg_match('#^data:#i', $src)) continue; // already inlined
            if (preg_match('#^https?://#i', $src)) continue; // skip remote images
            $localPath = (strpos($src, '/') === 0) ? $baseDir . $src : $baseDir . '/' . $src;
            if (file_exists($localPath)) {
                $data = base64_encode(file_get_contents($localPath));
                $ext = pathinfo($localPath, PATHINFO_EXTENSION);
                $mime = 'image/' . ($ext === 'svg' ? 'svg+xml' : $ext);
                $dataUri = 'data:' . $mime . ';base64,' . $data;
                $newTag = str_replace($src, $dataUri, $imgTag);
                $html = str_replace($imgTag, $newTag, $html);
            }
        }
    }

    return $html;
}
if (!isset($_SESSION['phone'])) {
    $_SESSION['phone'] = '9999999999'; // Default phone for API
}
$_SESSION['debug'] = 0;

$_POST['father'] = $_REQUEST['father'] ?? '';
$_POST['mother'] = $_REQUEST['mother'] ?? '';
$_POST['caste'] = $_REQUEST['caste'] ?? '';
$_POST['office_name'] = $_REQUEST['office_name'] ?? 'PK Astro';
$_POST['office_phone'] = $_REQUEST['office_phone'] ?? '';

// Capture the HTML output from onepage_birthchart_pdf.php
ob_start();
include 'onepage_birthchart_pdf.php';
$html = ob_get_clean();

// wkhtmltopdf configuration
// Use absolute path for wkhtmltopdf
$wkhtmltopdf = 'd:\praveen\dsjv_home\vendor\wkhtmltopdf\bin\wkhtmltopdf.exe';

// Create temporary files for HTML and PDF
$tmpDir = 'd:\praveen\dsjv_home\pdfs';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

$tmp_html = $tmpDir . '\temp_' . uniqid() . '.html';
$tmp_pdf = $tmpDir . '\temp_' . uniqid() . '.pdf';

// 6. Reconstruct HTML with Modern Layout

// Ensure variables are available to avoid warnings
if (!isset($sw_planet) || !is_array($sw_planet)) $sw_planet = array_fill(0, 12, '');
if (!isset($g_navamsam_arr) || !is_array($g_navamsam_arr)) $g_navamsam_arr = array_fill(0, 12, '');
if (!isset($panchang) || !is_array($panchang)) $panchang = ['nak' => '', 'thithi' => '', 'yoga' => ''];
if (!isset($nak_pad_idx)) $nak_pad_idx = 0;
if (!isset($g_paksham)) $g_paksham = '';
if (!isset($g_dasaCurrent)) $g_dasaCurrent = '';
if (!isset($g_dasaBalance)) $g_dasaBalance = '';

$retro_planets = detect_retro_planets(isset($sw_dir) ? $sw_dir : []);

$day_map = array(
    'Sunday' => 'ஞாயிறு',
    'Monday' => 'திங்கள்',
    'Tuesday' => 'செவ்வாய்',
    'Wednesday' => 'புதன்',
    'Thursday' => 'வியாழன்',
    'Friday' => 'வெள்ளி',
    'Saturday' => 'சனி'
);
$day_en = date('l', strtotime($requestedDate));
$tamil_day = $day_map[$day_en] ?? $day_en;

function only_tamil_segment($text) {
    if (empty($text)) {
        return '';
    }
    if (preg_match_all('/[\p{Tamil}0-9\s\.\-\(\)\[\]]+/u', $text, $matches)) {
        $longest = '';
        foreach ($matches[0] as $segment) {
            $segment = trim($segment);
            if ($segment === '') continue;
            if ($longest === '' || mb_strlen($segment, 'UTF-8') > mb_strlen($longest, 'UTF-8')) {
                $longest = $segment;
            }
        }
        if ($longest !== '') {
            return $longest;
        }
    }
    return trim($text);
}

$header_nak = only_tamil_segment($panchang['nak'] ?? '');
$header_thithi = only_tamil_segment($panchang['thithi'] ?? '');
$header_yoga = only_tamil_segment($panchang['yoga'] ?? '');

// Fix for Thithi display having artifacts like "பிரதமை["
// Determine paksham in this order:
// 1) If already provided in $g_paksham keep it
// 2) Try to parse bracketed token inside $panchang['thithi']
// 3) Fallback: compute from moon-sun angle (use $tot_degree if available)
if (empty($g_paksham)) {
    // try bracket extraction first
    if (!empty($panchang['thithi']) && preg_match('/\[(.*?)\]/', $panchang['thithi'], $m)) {
        $raw_paksham = $m[1];
        $parts = preg_split('/\s*\/\s*/', $raw_paksham);
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') continue;
            // common tokens mapping -> K = Shukla, S = Krishna
            if (strcasecmp($p, 'S') === 0 || stripos($p, 'krishna') !== false || stripos($p, 'thei') !== false) {
                $g_paksham = 'S';
                break;
            }
            if (strcasecmp($p, 'K') === 0 || stripos($p, 'sukla') !== false || stripos($p, 'valar') !== false) {
                $g_paksham = 'K';
                break;
            }
        }
    }

    // fallback: compute using moon and sun longitudes if available
    if (empty($g_paksham)) {
        $sun_deg = null; $moon_deg = null;
        if (isset($tot_degree) && is_array($tot_degree)) {
            if (isset($tot_degree['Sun'])) $sun_deg = floatval($tot_degree['Sun']);
            if (isset($tot_degree['Moon'])) $moon_deg = floatval($tot_degree['Moon']);
        }
        // try alternative numeric sources (some codebases store as numeric arrays)
        if (($sun_deg === null || $moon_deg === null) && isset($sw_planet) && is_array($sw_planet)) {
            // attempt to parse strings like "Sun 12.34" or Tamil labels — look for numeric degree in each entry
            foreach ($sw_planet as $entry) {
                if (!is_string($entry)) continue;
                // find first number with optional decimal
                if (preg_match('/([0-9]{1,3}(?:\.[0-9]+)?)/', $entry, $mm)) {
                    $val = floatval($mm[1]);
                    // Heuristic: if entry contains English 'Sun' or Tamil 'சூரி' treat as sun
                    if ((stripos($entry, 'sun') !== false) || (function_exists('mb_stripos') && mb_stripos($entry, 'சூரி') !== false)) $sun_deg = $val;
                    if ((stripos($entry, 'moon') !== false) || (function_exists('mb_stripos') && mb_stripos($entry, 'சந்திர') !== false)) $moon_deg = $val;
                }
            }
        }

        if ($sun_deg !== null && $moon_deg !== null) {
            // moon - sun normalized to 0..360
            $thithi_deg = fmod(($moon_deg - $sun_deg) + 360.0, 360.0);
            // if angle < 180 => Shukla (K), else Krishna (S)
            if ($thithi_deg >= 0 && $thithi_deg < 180) {
                $g_paksham = 'K';
            } else {
                $g_paksham = 'S';
            }
        }
    }
}

// Keep original thithi text as provided by the panchang; trim and remove stray brackets
$header_thithi = trim($header_thithi);
// Remove any stray '[' or ']' that may cause malformed output like "சஷ்டி[ (கிருஷ்)"
$header_thithi = str_replace(array('[', ']'), '', $header_thithi);

$nak_corrections = array(
    'ந-ச.திர' => 'உ.திர-டாதி 3'
);
$header_nak = $nak_corrections[$header_nak] ?? $header_nak;

$paksham_map = array(
    'S' => 'கிருஷ்',
    'K' => 'சுக்ல',
    'Valar' => 'சுக்ல',
    'Thei' => 'கிருஷ்',
    'Sukla' => 'சுக்ல',
    'Krishna' => 'கிருஷ்'
);
$paksham_label = '';
if (!empty($g_paksham)) {
    $paksham_label = $paksham_map[$g_paksham] ?? only_tamil_segment($g_paksham);
}
$thithi_display = $header_thithi;
if ($paksham_label) {
    $thithi_display .= ' (' . $paksham_label . ')';
}

// Calculate Age
$age = round((time() - strtotime($requestedDate)) / (365*24*60*60), 1);

// Calculate Chandrashtamam (8th from Moon)
// Assuming $moon_house is 1-12. If not available, default to 1.
$moon_house_idx = isset($moon_house) ? $moon_house : 1;
$chandrashtamam_idx = ($moon_house_idx - 1 + 8) % 12;
$chandrashtamam_rasi = getRasi($chandrashtamam_idx);

// Generate Bukthi Table HTML (Footer) — include all periods
$bukthi_html = '<table class="dasa-table"><thead><tr><th>தசா / புக்தி</th><th>முடிவு தேதி</th></tr></thead><tbody>';
if (isset($bukthi_table) && is_array($bukthi_table)) {
    foreach ($bukthi_table as $key => $data) {
        $bukthi_html .= '<tr><td>' . translate_tamil_text($key) . '</td><td>' . ($data[2] ?? '') . '</td></tr>';
    }
}
 $bukthi_html .= '</tbody></table>';

// Build Planet / Degree table (if degrees available)
$planet_table_html = '';
// Build a richer planet-star table using absolute longitudes if available
if (isset($tot_degree) && is_array($tot_degree) && count($tot_degree) > 0) {
    $star_arr = array("அசுவினி","பரணி","கிருத்திகை","ரோகிணி","மிருகசிரீஷம்","திருவாதிரை","புனர்பூசம்","பூசம்","ஆயில்யம்","மகம்","பூரம்","உத்திரம்","ஹஸ்தம்","சித்திரை","சுவாதி","விசாகம்","அனுஷம்","கேட்டை","மூலம்","பூராடம்","உத்திராடம்","திருவோணம்","அவிட்டம்","சதயம்","பூரட்டாதி","உத்திரட்டாதி","ரேவதி");

    $planet_table_html = '<table class="dasa-table" style="margin-top:10px;font-size:11px;"><thead><tr><th>கிரகம்</th><th>ராசி</th><th>நட்சத்திரம்</th><th>பாதம்</th><th>அளவு</th></tr></thead><tbody>';

    // Preferred order for display
    $order = array('Sun','Moon','Mercury','Venus','Mars','Jupiter','Saturn','mean Node','Ascendant');
    foreach ($order as $key) {
        if (!isset($tot_degree[$key])) continue;
        $long = fmod(floatval($tot_degree[$key]), 360.0);
        if ($long < 0) $long += 360.0;

        // Rasi index 0-11
        $rasi_idx = (int) floor($long / 30.0);
        $rasi_name = getRasi($rasi_idx);

        // Compute total pada (1-108) using 3.333333... deg per pada
        $total_pada = (int) floor($long / (10.0/3.0)) + 1; // 3 + 20/60 = 3.333333 -> 10/3
        if ($total_pada < 1) $total_pada = 1;
        if ($total_pada > 108) $total_pada = 108;
        $nak_idx = (int) floor(($total_pada - 1) / 4);
        $pada_no = (($total_pada - 1) % 4) + 1;
        $nak_name = isset($star_arr[$nak_idx]) ? $star_arr[$nak_idx] : '';

        // Degree within rasi
        $deg_in_rasi = round($long - ($rasi_idx * 30.0), 2);
        $deg_disp = DECtoDMS_jamakol($deg_in_rasi);

        // Tamil planet name map (English -> Tamil)
        $pmap = array('Sun' => 'சூரியன்', 'Moon' => 'சந்திரன்', 'Mercury' => 'புத்தன்', 'Venus' => 'சுக்கிரன்', 'Mars' => 'செவ்வாய்', 'Jupiter' => 'குரு', 'Saturn' => 'சனி', 'mean Node' => 'ராகு', 'Ascendant' => 'லக்னம்');
        $p_name_tamil = isset($pmap[$key]) ? $pmap[$key] : $key;

        $planet_table_html .= '<tr><td>' . $p_name_tamil . '</td><td>' . $rasi_name . '</td><td>' . $nak_name . '</td><td>' . $pada_no . '</td><td>' . $deg_disp . '</td></tr>';
    }

    // Fallback: include any other planets present in tot_degree not in order
    foreach ($tot_degree as $k => $v) {
        if (in_array($k, $order)) continue;
        $long = fmod(floatval($v), 360.0);
        if ($long < 0) $long += 360.0;
        $rasi_idx = (int) floor($long / 30.0);
        $rasi_name = getRasi($rasi_idx);
        $total_pada = (int) floor($long / (10.0/3.0)) + 1;
        if ($total_pada < 1) $total_pada = 1; if ($total_pada > 108) $total_pada = 108;
        $nak_idx = (int) floor(($total_pada - 1) / 4);
        $pada_no = (($total_pada - 1) % 4) + 1;
        $nak_name = isset($star_arr[$nak_idx]) ? $star_arr[$nak_idx] : '';
        $deg_in_rasi = round($long - ($rasi_idx * 30.0), 2);
        $deg_disp = DECtoDMS_jamakol($deg_in_rasi);
        $planet_table_html .= '<tr><td>' . translate_tamil_text($k) . '</td><td>' . $rasi_name . '</td><td>' . $nak_name . '</td><td>' . $pada_no . '</td><td>' . $deg_disp . '</td></tr>';
    }

    $planet_table_html .= '</tbody></table>';
}

// Build Starlord table (if available)
$starlord_table_html = '';
if (isset($planet_starlord) && is_array($planet_starlord) && count($planet_starlord) > 0) {
    $starlord_table_html = '<table class="dasa-table" style="margin-top:10px;font-size:11px;"><thead><tr><th>கிரகம்</th><th>நட்சத்திர கணவன்</th></tr></thead><tbody>';
    foreach ($planet_starlord as $pl => $data) {
        // $data may be string or array; render sensibly
        $val = is_array($data) ? implode(' / ', $data) : $data;
        $display_pl = translate_tamil_text($pl);
        $display_val = translate_tamil_text($val);
        // determine english key for retro lookup
        $engKey = resolve_english_planet_key($pl) ?? resolve_english_planet_key($display_pl);
        global $retro_planets;
        if (!empty($engKey) && !empty($retro_planets) && isset($retro_planets[$engKey])) {
            // append suffix if not already present
            if (strpos($display_pl, '(வ)') === false) {
                $display_pl .= ' (வ)';
            }
        }
        // format any embedded retro spans in values
        $display_pl = format_chart_planet_display($display_pl);
        $display_val = format_chart_planet_display($display_val);
        $starlord_table_html .= '<tr><td>' . $display_pl . '</td><td>' . $display_val . '</td></tr>';
    }
    $starlord_table_html .= '</tbody></table>';
}

// Build compact starlord rows (for embedding into the modern layout)
$starlord_rows = '';
$preferred = array('Sun','Moon','Mercury','Venus','Mars','Jupiter','Saturn','mean Node');
$short_map = array('Sun' => 'சூரி', 'Moon' => 'சந்', 'Mercury' => 'புத', 'Venus' => 'சுக்', 'Mars' => 'செ', 'Jupiter' => 'குரு', 'Saturn' => 'சனி', 'mean Node' => 'ரா', 'Ascendant' => 'லக்');

if (isset($planet_starlord) && is_array($planet_starlord) && count($planet_starlord) > 0) {
        foreach ($preferred as $pl) {
        $key_to_use = null;
        if (isset($planet_starlord[$pl])) $key_to_use = $pl;
        elseif (isset($planet_starlord[translate_tamil_text($pl)])) $key_to_use = translate_tamil_text($pl);
        elseif (isset($short_map[$pl]) && isset($planet_starlord[$short_map[$pl]])) $key_to_use = $short_map[$pl];
        
        if (!$key_to_use) continue;
        
        $data = $planet_starlord[$key_to_use];
        if (!is_array($data)) continue;
        $star = translate_tamil_text($data[0] ?? '');
        $starlord = translate_tamil_text($data[1] ?? '');
        $full = isset($data[2]) ? round($data[2],2) . '°' : '';
        $rasi_deg = isset($data[3]) ? DECtoDMS_jamakol($data[3]) : '';
        $pmap = array('Sun' => 'சூரி', 'Moon' => 'சந்', 'Mercury' => 'புத', 'Venus' => 'சுக்', 'Mars' => 'செ', 'Jupiter' => 'குரு', 'Saturn' => 'சனி', 'mean Node' => 'ரா');
        $pname = $pmap[$pl] ?? ($key_to_use === $pl ? translate_tamil_text($pl) : $key_to_use);
        $isRetroPlanet = isset($retro_planets[$pl]);
        $pname = format_planet_label($pname, $isRetroPlanet);
        $star = add_retro_suffix_to_spans($star);
        $full = add_retro_suffix_to_spans($full);
        $rasi_deg = add_retro_suffix_to_spans($rasi_deg);
        $starlord_rows .= '<tr><td>' . $pname . '</td><td>' . $star . '</td><td>' . $full . '</td><td>' . $rasi_deg . '</td></tr>';
    }
    // any other entries
    foreach ($planet_starlord as $k => $data) {
        $engKey = resolve_english_planet_key($k);
        if ($engKey && in_array($engKey, $preferred)) continue;
        if (in_array($k, $preferred)) continue;
        
        if (!is_array($data)) continue;
        $star = translate_tamil_text($data[0] ?? '');
        $starlord = translate_tamil_text($data[1] ?? '');
        $full = isset($data[2]) ? round($data[2],2) . '°' : '';
        $rasi_deg = isset($data[3]) ? DECtoDMS_jamakol($data[3]) : '';
        $pname = is_string($k) ? translate_tamil_text($k) : translate_tamil_text($k);
        
        if ($engKey && isset($retro_planets[$engKey])) {
             $pname = format_planet_label($pname, true);
        } else {
             $pname = add_retro_suffix_to_spans($pname);
        }
        
        $star = add_retro_suffix_to_spans($star);
        $full = add_retro_suffix_to_spans($full);
        $rasi_deg = add_retro_suffix_to_spans($rasi_deg);
        $starlord_rows .= '<tr><td>' . $pname . '</td><td>' . $star . '</td><td>' . $full . '</td><td>' . $rasi_deg . '</td></tr>';
    }
}

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modern Astrology Chart</title>
    <style>
        /* Reset & Base */
        body { font-family: "Arial Unicode MS", "Noto Sans Tamil", sans-serif; background-color: #ffffff; margin: 0; padding: 20px; color: #0f172a; }
        * { box-sizing: border-box; }
        
        /* Layout Tables */
        table { border-collapse: collapse; width: 100%; }
        
        /* Header */
        .header-box { background-color: #ffffff; border: 2px solid #a00000; border-radius: 20px; overflow: hidden; margin-bottom: 20px; }
        .header-top { background: linear-gradient(140deg, #d61818 0%, #a00000 46%, #6e0000 92%); color: #ffffff; padding: 15px; }
        .header-bottom { padding: 15px; }
        .header-label { font-size: 10px; color: #a00000; font-weight: bold; text-transform: uppercase; margin-bottom: 2px; }
        .header-value { font-size: 16px; font-weight: bold; color: #1f1f1f; }
        
        /* Info Grid */
        .info-grid { width: 100%; border-spacing: 10px; border-collapse: separate; margin: 0 -10px 20px -10px; }
        .info-box { background-color: #ffffff; border-left: 4px solid #a00000; border-radius: 10px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.06); height: 100%; border: 1px solid #f0dede; border-left-width: 4px; }
        .info-title { font-size: 11px; font-weight: bold; color: #a00000; border-bottom: 1px solid #fdecec; padding-bottom: 5px; margin-bottom: 10px; text-transform: uppercase; }
        .info-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; color: #4a1f1f; font-weight: 500; }
        .info-row span:first-child { color: #7a2f2f; }
        .info-row span:last-child { font-weight: bold; color: #1f1f1f; }
        
        /* Charts */
        .chart-box { background-color: transparent; border: none; border-radius: 0; padding: 0; height: 100%; }
        
            .chart-table { width: 100%; border-collapse: collapse; border-spacing: 0; table-layout: fixed; background-color: transparent; border: none; border-radius: 0; overflow: visible; }
            .chart-cell { background-color: #ffffff; height: 70px; vertical-align: middle; text-align: center; padding: 4px 3px; position: relative; border: 2.5px solid #a00000; border-radius: 0; }
        .chart-cell-content { font-size: 12px; font-weight: bold; color: #1f1f1f; margin-top: 8px; line-height: 1.2; }
        
        .chart-center { background-color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000; }
        .chart-center-text { font-size: 18px; font-weight: bold; color: #1f1f1f; font-style: italic; font-family: serif; opacity: 1; }
        .retro { color: inherit; font-weight: bold; }

        /* Dasa Footer */
        .dasa-box { border: 2px solid #a00000; border-radius: 20px; overflow: hidden; margin-top: 20px; }
        .dasa-left { background-color: #fff0f0; padding: 20px; width: 45%; vertical-align: top; }
        .dasa-right { background-color: #a00000; color: #ffffff; padding: 20px; width: 55%; vertical-align: middle; border-left: 4px solid #f59e0b; position: relative; }
        
        .tables-row { display:flex; align-items:flex-start; gap:12px; margin-top:10px; flex-wrap:nowrap; }
        .table-card { background:#fff; border:1px solid #f0dede; border-radius:10px; padding:6px 8px; box-sizing:border-box; }
        .table-card.starlord-card { flex:1 1; max-width:fit-content; }
        .table-card.bukthi-card { flex:1 1; max-width:fit-content; }
        .table-card.currentdasa-card { flex:0 0; max-width:fit-content; }
        .table-card .table-heading { font-weight:bold; color:#ffffff; font-size:16px; margin-bottom:6px; background: linear-gradient(140deg, #d61818 0%, #a00000 46%, #6e0000 92%); padding:4px 10px; border-radius:8px; box-shadow: 0 2px 4px rgba(0,0,0,0.18); }
        .table-card table { font-size:12px; border-collapse:collapse; table-layout:auto; white-space:nowrap; margin:0; }
        .table-card.starlord-card table { width:auto; }
        .table-card.bukthi-card table { width:auto; }
        .table-card th { text-align:left; color:#7a2f2f; padding-bottom:2px; font-size:14px; width: 100px; }
        .table-card td { padding:2px 4px; color:#4a1f1f; font-weight:bold; border-bottom:1px solid #f0dede; font-size:12px; width: 60px;}
        .tables-dasa-line { width:100%; margin-top:10px; text-align:center; }
        .dasa-range { margin-top:8px; font-weight:bold; color:#a00000; text-align:center; font-size:12px}
        
        /* Footer */
        .footer { text-align: center; color: #8a2a2a; font-size: 10px; position: fixed; left: 0; right: 0; bottom: 1cm; background: #fff; padding: 8px 0 4px 0; text-transform: uppercase; letter-spacing: 1px; border-top: 1px solid #f0dede; z-index: 100; }

        /* Page container to constrain layout to A4 width on large screens */
        .page-container { max-width: 794px; margin: 0 auto; background: #ffffff; padding: 8px 12px; box-sizing: border-box; }

        /* Print-specific: ensure container matches physical A4 width */
        @media print {
            .page-container { width: 210mm; max-width: 100%; margin: 0 auto; padding: 0; box-shadow: none; }
            body { padding: 0; }
            .footer { position: fixed; bottom: 10mm; }
        }
    </style>
</head>
<body>
    <div class="page-container">

    <!-- Header -->
    <div class="table-card" style="width:100%; margin-bottom:18px; padding:10px;">
        <div class="table-heading" style="font-size:20px; padding:10px 15px; display:flex; justify-content:space-between; align-items:center;">
            <span>' . $in_name . '</span>
            <span style="font-size:14px; padding:10px 15px; display:flex; justify-content:space-between; align-items:center;">தேதி: ' . str_replace('/', '-', $birth_date) . ' &nbsp; நேரம்: ' . (trim($birth_time) ? date('h:i A', strtotime($birth_time)) : '') . ' &nbsp; இடம்: ' . $place . '</span>
        </div>
        <table style="width:100%; margin-top:8px; border-collapse: collapse;">
            <tr style="text-align:center;">
                <td style="padding:10px; border-right:1px solid #f0dede; width:16%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">ராசி</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $g_rasi . '</div>
                </td>
                <td style="padding:10px; border-right:1px solid #f0dede; width:16%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">லக்னம்</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $g_lagnam . '</div>
                </td>
                <td style="padding:10px; border-right:1px solid #f0dede; width:18%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">நட்சத்திரம்</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $header_nak . '</div>
                </td>
                <td style="padding:10px; border-right:1px solid #f0dede; width:16%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">வாரம்</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $tamil_day . '</div>
                </td>
                <td style="padding:10px; border-right:1px solid #f0dede; width:16%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">திதி</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $thithi_display . '</div>
                </td>
                <td style="padding:10px; width:18%;">
                    <div style="color:#7a2f2f; font-size:13px; margin-bottom:5px; font-weight:bold;">யோகம்</div>
                    <div style="color:#1f1f1f; font-weight:bold; font-size:16px;">' . $header_yoga . '</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- removed info-grid as requested -->

    <!-- Charts -->
    <table style="width: 100%; border-spacing: 15px; border-collapse: separate; margin: 0 -15px;">
        <tr>
            <td width="50%" valign="top">
                        <div class="chart-box">
                    <table class="chart-table">
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[11]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[0]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[1]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[2]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[10]) . '</div></td>
                            <td colspan="2" rowspan="2" class="chart-center"><span class="chart-center-text">ராசி</span></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[3]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[9]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[4]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[8]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[7]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[6]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($sw_planet[5]) . '</div></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td width="50%" valign="top">
                <div class="chart-box">
                    <table class="chart-table">
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[11]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[0]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[1]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[2]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[10]) . '</div></td>
                            <td colspan="2" rowspan="2" class="chart-center"><span class="chart-center-text">நவாம்சம்</span></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[3]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[9]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[4]) . '</div></td>
                        </tr>
                        <tr>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[8]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[7]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[6]) . '</div></td>
                            <td class="chart-cell"><div class="chart-cell-content">' . format_chart_planet_display($g_navamsam_arr[5]) . '</div></td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Tables for starlord and dasa/bukthi -->
    <div class="tables-row">
        <div class="table-card starlord-card">
            <div class="table-heading">நட்சத்திர சாரம்</div>
            <table class="starlord-table">
                <thead>
                    <tr><th>கிரகம்</th><th>நட்சத்திரம்</th><th>பாகை</th><th>ராசி பாகை</th></tr>
                </thead>
                <tbody>
                ' . $starlord_rows . '
                </tbody>
            </table>
        </div>
        <div class="table-card bukthi-card">
            <div class="table-heading">தசா / புக்தி</div>
            ' . $bukthi_html . '
            <div class="tables-dasa-line">
                <!-- DASA_LINE_PLACEHOLDER -->
            </div>
        </div>
        
        </div>

        <div class="footer">
        Generated by DSJV Matrimony @ ' . date('d-m-Y H:i') . '
    </div>

</body>
</html>';

// Inject CSS and Font directly
// $mainCss = file_get_contents('d:/praveen/dsjv_home/css/main.css'); // Unused
// $styleCss = file_get_contents('d:/praveen/dsjv_home/css/onepage_birth_style.css'); // Unused

$basePath = 'd:/praveen/dsjv_home/';

// Fix url() references in CSS to use absolute paths for wkhtmltopdf
// (Kept for reference but unused in new layout)
$cssFixer = function($css) use ($basePath) {
    return preg_replace_callback('/url\s*\(\s*["\']?([^"\'\)]+)["\']?\s*\)/i', function($matches) use ($basePath) {
        $url = $matches[1];
        if (strpos($url, 'http') === false && strpos($url, 'data:') === false) {
            // Handle relative paths like ../images/
            $cleanUrl = ltrim($url, './');
            if (strpos($url, '../') === 0) {
                $cleanUrl = substr($url, 3);
            }
            $absolutePath = $basePath . $cleanUrl;
            if (file_exists($absolutePath)) {
                return 'url("file:///' . str_replace('\\', '/', $absolutePath) . '")';
            }
        }
        return $matches[0];
    }, $css);
};

// $mainCss = $cssFixer($mainCss); // Not needed for new layout
// $styleCss = $cssFixer($styleCss); // Not needed for new layout

// Remove Google Fonts import
// $mainCss = preg_replace('/@import url\("https:\/\/fonts\.googleapis\.com\/css\?family=Source\+Sans\+Pro:[^"]*"\);/', '', $mainCss);

$fontPath = 'd:/praveen/dsjv_home/NotoSansTamil-Regular.ttf';
$fontStyle = '';
if (file_exists($fontPath)) {
    $fontData = base64_encode(file_get_contents($fontPath));
    $fontStyle = '
        @font-face {
            font-family: "Noto Sans Tamil";
            src: url(data:font/truetype;charset=utf-8;base64,' . $fontData . ') format("truetype");
            font-weight: normal;
            font-style: normal;
        }';
}

$html = finalize_html($html, $fontStyle, $g_dasaCurrent, $bukthi_table);
// Support output types: pdf (default), html (preview) and json (structured data)
$lang = strtolower($_REQUEST['lang'] ?? 'ta'); // 'ta' or 'en'
$type = strtolower($_REQUEST['type'] ?? 'pdf');
if ($type === 'json') {
    output_json_data($lang);
} elseif ($type === 'html') {
    output_html_preview($html);
} else {
    output_pdf_report($html, $tmp_html, $tmp_pdf, $wkhtmltopdf);
}

function finalize_html($html, $fontStyle, $g_dasaCurrent, $bukthi_table) {
    $combinedStyle = '<style>' . $fontStyle . '</style>';
    if (strpos($html, '</head>') !== false) {
        $html = str_replace('</head>', $combinedStyle . '</head>', $html);
    } else {
        $html = $combinedStyle . $html;
    }
    $summary = build_dasa_summary($g_dasaCurrent, $bukthi_table);
    $dasa_line_html = '';
    if (!empty($summary)) {
        $dasa_line_html = '<div class="dasa-range">தற்போதய தசா/புக்தி: ' . $summary . '</div>';
    }
    return str_replace('<!-- DASA_LINE_PLACEHOLDER -->', $dasa_line_html, $html);
}

function build_dasa_summary($g_dasaCurrent, $bukthi_table) {
    $summary = '';
    if (!empty($g_dasaCurrent)) {
        $current_raw = $g_dasaCurrent;
        $clean = preg_replace('/<br\s*\/?>/i', '|', $current_raw);
        $segments = array_filter(array_map('trim', explode('|', $clean)));
        $current_dasa_label = str_replace('->', ' / ', $segments[0] ?? '');
        $current_period_label = '';
        preg_match_all('/\d{2}-\d{2}-\d{4}/', $current_raw, $date_matches);
        if (count($date_matches[0]) >= 2) {
            $current_period_label = $date_matches[0][0] . ' முதல் ' . $date_matches[0][1] . ' வரை';
        } elseif (count($date_matches[0]) === 1) {
            $current_period_label = $date_matches[0][0];
        } elseif (isset($segments[1])) {
            $current_period_label = str_ireplace('to', ' முதல் ', $segments[1]);
        }
        if (!empty($current_dasa_label) || !empty($current_period_label)) {
            $summary = trim(($current_dasa_label ? $current_dasa_label . ': ' : '') . $current_period_label);
        }
    }
    if (empty($summary) && isset($bukthi_table) && is_array($bukthi_table) && count($bukthi_table) > 0) {
        $keys = array_keys($bukthi_table);
        $k0 = $keys[0];
        $d0 = $bukthi_table[$k0];
        $first_bukthi_key = translate_tamil_text($k0);
        $start = isset($d0[1]) ? $d0[1] : '';
        $end = isset($d0[2]) ? $d0[2] : '';
        if ($start && $end) {
            $first_bukthi_period = $start . ' முதல் ' . $end . ' வரை';
        } elseif ($end) {
            $first_bukthi_period = $end;
        } elseif ($start) {
            $first_bukthi_period = $start;
        }
        $parts = preg_split('/\s*\/\s*/u', $first_bukthi_key);
        $dasa_label = trim($parts[0] ?? '');
        $bukthi_label = trim($parts[1] ?? '');
        $labels = array_filter([$dasa_label, $bukthi_label]);
        if (!empty($labels)) {
            $first_bukthi_label = implode(' / ', $labels);
        }
        if ($first_bukthi_label && isset($first_bukthi_period) && $first_bukthi_period !== '') {
            $summary = $first_bukthi_label . ': ' . $first_bukthi_period;
        } elseif (!empty($first_bukthi_label)) {
            $summary = $first_bukthi_label;
        } else {
            $summary = $first_bukthi_period;
        }
    }
    return $summary;
}

function output_json_data($lang = 'ta') {
    global $tot_degree, $sw_planet, $retro_planets, $planet_starlord, $bukthi_table, $panchang;
    global $g_rasi, $g_lagnam, $thithi_display, $header_nak, $header_yoga, $tamil_day, $birth_date, $birth_time, $place, $g_paksham;

    $short_map = array('Sun' => 'Sun', 'Moon' => 'Moon', 'Mercury' => 'Mercury', 'Venus' => 'Venus', 'Mars' => 'Mars', 'Jupiter' => 'Jupiter', 'Saturn' => 'Saturn', 'mean Node' => 'Node', 'Ascendant' => 'Asc');

    $planets = [];
    if (isset($tot_degree) && is_array($tot_degree)) {
        foreach ($tot_degree as $k => $v) {
            $deg = floatval($v);
            $norm = fmod($deg + 360.0, 360.0);
            $rasi_idx = (int) floor($norm / 30.0);
            $rasi_name = function_exists('getRasi') ? getRasi($rasi_idx) : $rasi_idx;
            $isRetro = false;
            $engKey = null;
            if (function_exists('resolve_english_planet_key')) {
                $engKey = resolve_english_planet_key($k) ?? resolve_english_planet_key((string)$k);
            }
            if ($engKey && !empty($retro_planets) && isset($retro_planets[$engKey])) $isRetro = true;
            $planets[] = [
                'key' => (string)$k,
                'eng' => $engKey,
                'deg' => $deg,
                'norm' => $norm,
                'rasi_index' => $rasi_idx,
                'rasi' => $rasi_name,
                'short' => $short_map[$engKey] ?? (string)$k,
                'retro' => $isRetro
            ];
        }
    }

    $starlord = [];
    if (isset($planet_starlord) && is_array($planet_starlord)) {
        foreach ($planet_starlord as $k => $v) {
            $engKey = function_exists('resolve_english_planet_key') ? resolve_english_planet_key($k) : null;
            $isRetro = ($engKey && !empty($retro_planets) && isset($retro_planets[$engKey]));
            $starlord[] = ['key' => (string)$k, 'eng' => $engKey, 'raw' => $v, 'retro' => $isRetro];
        }
    }

    $bukthi = [];
    if (isset($bukthi_table) && is_array($bukthi_table)) {
        foreach ($bukthi_table as $k => $v) {
            $bukthi[] = ['label' => (string)$k, 'data' => $v];
        }
    }

    // Clean raw panchang entries to prefer Tamil-only segments (matching HTML view)
    $panchang_raw_clean = [];
    if (is_array($panchang)) {
        foreach ($panchang as $rk => $rv) {
            if (is_string($rv)) {
                $panchang_raw_clean[$rk] = only_tamil_segment($rv);
            } else {
                $panchang_raw_clean[$rk] = $rv;
            }
        }
    } else {
        $panchang_raw_clean = $panchang;
    }

    // Use Tamil label for paksham to match HTML display
    $paksham_for_json = '';
    if (!empty($g_paksham)) {
        $paksham_for_json = $paksham_label ?? only_tamil_segment($g_paksham);
    }

    $json = [
        'meta' => [
            'name' => $_GET['name'] ?? $_REQUEST['name'] ?? null,
            'date' => $birth_date ?? null,
            'time' => $birth_time ?? null,
            'place' => $place ?? null,
            'rasi' => $g_rasi ?? null,
            'lagnam' => $g_lagnam ?? null,
            'weekday' => $tamil_day ?? null,
            'paksham' => $paksham_for_json ?? ($g_paksham ?? null),
            'generated_at' => date('c')
        ],
        'panchang' => [
            'nakshatra' => $header_nak ?? null,
            'thithi' => $thithi_display ?? null,
            'yoga' => $header_yoga ?? null,
            'raw' => $panchang_raw_clean ?? []
        ],
        'planets' => $planets,
        // include rendered chart cell contents (if available) so clients can render exact HTML layout
        'sw_planet' => isset($sw_planet) && is_array($sw_planet) ? array_values($sw_planet) : [],
        'navamsam' => isset($g_navamsam_arr) && is_array($g_navamsam_arr) ? array_values($g_navamsam_arr) : [],
        'retro_planets' => array_values(array_keys($retro_planets ?? [])),
        'starlord' => $starlord,
        'bukthi' => $bukthi
    ];

    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function output_html_preview($html) {
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: text/html; charset=utf-8');
    }
    echo $html;
    exit;
}

function output_pdf_report($html, $tmp_html, $tmp_pdf, $wkhtmltopdf) {
    // Make HTML self-contained (inline local CSS/JS/images) so browsers/wkhtmltopdf render it identically
    $baseHref = 'file://' . str_replace('\\', '/', __DIR__) . '/';
    if (stripos($html, '<base') === false && stripos($html, '<head') !== false) {
        $html = preg_replace('/<head(.*?)>/i', '<head$1><base href="' . $baseHref . '">', $html, 1);
    }

    // Keep the in-page footer visible (user requested footer reinstated)
    // Clear document title for PDF rendering to reduce renderer-added headers
    $html = preg_replace('/<title>.*?<\/title>/is', '<title></title>', $html);
    // Add @page reset to reduce browser-added margins
    if (stripos($html, '</head>') !== false) {
        $pdfPageStyle = "<style>@page { size: A4; margin: 0; } body { margin: 0; padding: 18px; }</style>";
        $html = str_replace('</head>', $pdfPageStyle . '</head>', $html);
    }

    $html = make_self_contained_html($html);
    file_put_contents($tmp_html, $html);
    $return_var = 1;
    $command = '';
    $chromeExe = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
    if (file_exists($chromeExe)) {
        $tmp_html_url = 'file:///' . str_replace('\\', '/', $tmp_html);
        $command = "\"$chromeExe\" --headless --disable-gpu --no-margins --print-to-pdf=\"$tmp_pdf\" --run-all-compositor-stages-before-draw --virtual-time-budget=3000 --print-to-pdf-no-header --disable-print-preview --disable-pdf-header --no-pdf-header --dump-dom \"$tmp_html_url\" 2>&1";
        exec($command, $output, $return_var);
    }
    if (!file_exists($tmp_pdf) || $return_var !== 0) {
        $command = "\"$wkhtmltopdf\" --encoding utf-8 --enable-local-file-access --print-media-type --page-size A4 --margin-top 5mm --margin-bottom 5mm --margin-left 5mm --margin-right 5mm --zoom 1.0 --no-pdf-compression --enable-smart-shrinking --javascript-delay 1500 --no-stop-slow-scripts --load-error-handling ignore --load-media-error-handling ignore --header-left '' --header-right '' --footer-left '' --footer-right '' --header-spacing 0 --footer-spacing 0 \"$tmp_html\" \"$tmp_pdf\" 2>&1";
        exec($command, $output, $return_var);
    }
    if (($return_var === 0 || $return_var === 1) && file_exists($tmp_pdf)) {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="birth_chart.pdf"');
            header('Content-Length: ' . filesize($tmp_pdf));
        }
        readfile($tmp_pdf);
        if (file_exists($tmp_html)) unlink($tmp_html);
        if (file_exists($tmp_pdf)) unlink($tmp_pdf);
    } else {
        if (php_sapi_name() !== 'cli') {
            header('Content-Type: text/plain');
        }
        echo "Failed to generate PDF. Please check server logs.";
    }
}

function detect_retro_planets($sw_dir) {
    $planet_order = ['Sun' => 0, 'Moon' => 1, 'Mercury' => 2, 'Venus' => 3, 'Mars' => 4, 'Jupiter' => 5, 'Saturn' => 6];
    $results = [];
    foreach ($planet_order as $planet => $index) {
        if (isset($sw_dir[$index]) && (int)$sw_dir[$index] !== 1) {
            $results[$planet] = true;
        }
    }
    return $results;
}

function add_retro_suffix_to_spans($text) {
    if ($text === null) {
        return '';
    }
    $text = (string)$text;
    if (strpos($text, '<span class="retro">') === false) {
        return $text;
    }
    return preg_replace('/(<span class="retro">[^<]+<\/span>)(?!\s*\(வ\))/', '$1(வ)', $text);
}

function format_chart_planet_display($text) {
    global $retro_planets;
    $s = add_retro_suffix_to_spans($text);

    // mapping English -> Tamil short names used in charts
    $pmap = array('Sun' => 'சூரி', 'Moon' => 'சந்', 'Mercury' => 'புத', 'Venus' => 'சுக்', 'Mars' => 'செ', 'Jupiter' => 'குரு', 'Saturn' => 'சனி', 'mean Node' => 'ரா', 'Ascendant' => 'லக்');

    foreach ($pmap as $eng => $ta) {
        if (!empty($retro_planets) && isset($retro_planets[$eng])) {
            // append suffix if not already present
            $s = preg_replace('/(' . preg_quote($ta, '/') . ')(?!\s*\(வ\))/u', '$1(வ)', $s);
        }
    }

    return $s;
}

function format_planet_label($label, $isRetro) {
    $result = $label === null ? '' : (string)$label;
    if ($isRetro && strpos($result, '<span class="retro">') === false) {
        $result = '<span class="retro">' . $result . '</span>';
    }
    $result = add_retro_suffix_to_spans($result);
    // also ensure plain labels get suffix if retro flag present for this planet
    return $result;
}

function resolve_english_planet_key($pl) {
    $pmap = array('Sun' => 'சூரி', 'Moon' => 'சந்', 'Mercury' => 'புத', 'Venus' => 'சுக்', 'Mars' => 'செ', 'Jupiter' => 'குரு', 'Saturn' => 'சனி', 'mean Node' => 'ரா', 'Ascendant' => 'லக்');
    // direct English
    if (isset($pmap[$pl])) return $pl;
    // if input is Tamil, try to find key
    foreach ($pmap as $eng => $ta) {
        if ($pl === $ta || mb_stripos($pl, $ta) !== false) {
            return $eng;
        }
    }
    return null;
}

