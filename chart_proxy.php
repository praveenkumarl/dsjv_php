<?php
/**
 * chart_proxy.php
 * Server-side proxy + renderer for Birth_Chart_API JSON.
 * Usage: request this file with the same GET params you would send to the API.
 * Example: chart_proxy.php?name=Srividya&date=1979-06-13&time=02:47:00&place=Trichy|Asia/Kolkata
 *
 * This script fetches the JSON (type=json) from the configured remote API and renders
 * the same HTML layout server-side so the embedding page does not need CORS permissions.
 */

// ---- Configuration ----
// Point this to your Birth_Chart_API base URL (no query string)
$remote_api_base = 'http://127.0.0.1/dsjv_home/Birth_Chart_API.php';
// Timeout for remote fetch (seconds)
$fetch_timeout = 8;

// Security: token required to use proxy. Set via environment variable CHART_PROXY_TOKEN
// Change the default below or set env var on the server: export CHART_PROXY_TOKEN=your_secret
// Domain restriction: only allow requests coming from one of the configured origins or referers.
// Set `CHART_ALLOWED_DOMAINS` as an env var (comma- or space-separated list),
// e.g. "https://embed.example.com, http://localhost, http://127.0.0.1"
$allowed_domains_raw = getenv('CHART_ALLOWED_DOMAINS') ?: 'http://127.0.0.1';
$allowed_domains = array_map('trim', array_filter(preg_split('/[,\s]+/', $allowed_domains_raw)));

// Allowed remote addresses which are permitted to call when Origin/Referer is absent
// Set CHART_ALLOWED_REMOTE_ADDRS env var as comma/space-separated list, e.g. "127.0.0.1 192.0.2.5"
$allowed_remote_raw = getenv('CHART_ALLOWED_REMOTE_ADDRS') ?: '127.0.0.1';
$allowed_remote_addrs = array_map('trim', array_filter(preg_split('/[,\s]+/', $allowed_remote_raw)));

// Debug: log received Origin/Referer and resolved allowed domains (remove in production)
$debug_log = __DIR__ . DIRECTORY_SEPARATOR . 'chart_proxy_debug.log';
$dbg = sprintf("%s origin=%s referer=%s allowed=%s remote=%s\n",
    date('c'),
    $_SERVER['HTTP_ORIGIN'] ?? '-',
    $_SERVER['HTTP_REFERER'] ?? '-',
    $allowed_domains_raw,
    $_SERVER['REMOTE_ADDR'] ?? '-'
);
@file_put_contents($debug_log, $dbg, FILE_APPEND | LOCK_EX);

// Emit secure response headers for proxy responses
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header("Referrer-Policy: no-referrer-when-downgrade");
header('X-Content-Security-Policy: default-src \'self\'');
header("X-XSS-Protection: 1; mode=block");
// HSTS (only effective over HTTPS)
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Simple origin/referer-based domain check against allowed list
$origin = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
$origin_host = $origin ? parse_url($origin, PHP_URL_HOST) : '';
$ok = false;
// If no Origin/Referer header is present (server-to-server or curl), allow only from configured remote IPs.
if (empty($origin_host)) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remote && in_array($remote, $allowed_remote_addrs, true)) {
        $ok = true;
    }
} else {
    foreach ($allowed_domains as $ad) {
        $ah = parse_url($ad, PHP_URL_HOST) ?: $ad;
        if ($ah && strcasecmp($origin_host, $ah) === 0) { $ok = true; break; }
    }
}
if (!$ok) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden: invalid origin";
    exit;
}

// Build query: forward known params and force type=json & lang
$params = [];
$forward_keys = ['name','date','time','place','rowid','lang'];
foreach ($forward_keys as $k) {
    if (isset($_GET[$k]) && $_GET[$k] !== '') {
        $params[$k] = $_GET[$k];
    }
}
$type = $_GET['type'] ?? null;
// accept shorthand flags
if ($type === null) {
    if (!empty($_GET['pdf'])) $type = 'pdf';
    elseif (!empty($_GET['html'])) $type = 'html';
    elseif (!empty($_GET['json'])) $type = 'json';
}
// normalize
$type = $type ? strtolower($type) : 'json';
$params['type'] = $type;
$params['lang'] = $_GET['lang'] ?? 'ta';

// If rowid not provided, generate a deterministic rowid from date+time: ddmmyyyyHHMM
if (empty($params['rowid']) && !empty($params['date']) && !empty($params['time'])) {
    // Try to parse using strtotime; accepts formats like YYYY-MM-DD and DD-MM-YYYY
    $ts = strtotime($params['date'] . ' ' . $params['time']);
    if ($ts !== false) {
        $generated = date('dmYHi', $ts); // ddmmyyyyHHMM
        $params['rowid'] = $generated;
    } else {
        // Fallback: extract digits from date and time and build ddmmyyyyHHMM where possible
        $d = preg_replace('/[^0-9]/', '', $params['date']);
        $t = preg_replace('/[^0-9]/', '', $params['time']);
        // try common patterns
        if (strlen($d) === 8 && strlen($t) >= 4) {
            // assume d is ddmmyyyy or yyyymmdd
            // prefer ddmmyyyy; if looks like yyyymmdd (starts with 20 or 19), reorder
            if (preg_match('/^20|^19/', $d)) {
                // yyyymmdd -> ddmmyyyy
                $yyyy = substr($d,0,4); $mm = substr($d,4,2); $dd = substr($d,6,2);
                $dpart = $dd . $mm . $yyyy;
            } else {
                $dpart = $d;
            }
            $tpart = substr($t,0,4); // HHMM
            $params['rowid'] = $dpart . $tpart;
        }
    }
}

$query = http_build_query($params);
$remote_url = rtrim($remote_api_base, '?') . '?' . $query;

// Simple file cache to reduce API calls. TTL in seconds (default 300).
$cache_ttl = intval(getenv('CHART_PROXY_CACHE_TTL') ?: 300);
$cache_dir = __DIR__ . DIRECTORY_SEPARATOR . 'chart_proxy_cache';
if (!is_dir($cache_dir)) @mkdir($cache_dir, 0700, true);
$cache_key = 'chart_' . md5($remote_url);
$cache_file = $cache_dir . DIRECTORY_SEPARATOR . $cache_key . '.' . ($params['type'] ?? 'json') . '.cache';

$remote_json = null;
if ($cache_ttl > 0 && file_exists($cache_file) && (filemtime($cache_file) + $cache_ttl) >= time()) {
    // load from cache
    $remote_json = @file_get_contents($cache_file);
}

if ($remote_json === null || $remote_json === false) {
    // Fetch from remote API using Accept header matching requested type
    $accept = 'application/json';
    if ($params['type'] === 'html') $accept = 'text/html';
    if ($params['type'] === 'pdf')  $accept = 'application/pdf';
    $opts = [
        'http' => [
            'method' => 'GET',
            'timeout' => $fetch_timeout,
            'header' => "User-Agent: chart_proxy/1.0\r\nAccept: {$accept}\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $remote_json = @file_get_contents($remote_url, false, $context);
    if ($remote_json === false) {
        // fallback: if cache exists, use stale cache
        if (file_exists($cache_file)) {
            $remote_json = @file_get_contents($cache_file);
        } else {
            header('Content-Type: text/plain; charset=utf-8', true, 502);
            echo "Failed to fetch remote API: $remote_url";
            exit;
        }
    } else {
        // write to cache (best effort)
        if ($cache_ttl > 0) {
            $tmp = $cache_file . '.tmp';
            @file_put_contents($tmp, $remote_json, LOCK_EX);
            @rename($tmp, $cache_file);
            @chmod($cache_file, 0600);
        }
    }
}

// If caller asked for HTML, return the raw HTML from remote API so formatting matches exactly.
if ($params['type'] === 'html') {
    header('Content-Type: text/html; charset=utf-8');
    echo $remote_json;
    exit;
}

// If caller asked for PDF, return raw PDF bytes with proper headers
if ($params['type'] === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="chart.pdf"');
    if (ob_get_level()) ob_end_clean();
    echo $remote_json;
    exit;
}

$data = json_decode($remote_json, true);
if (!is_array($data)) {
    header('Content-Type: text/plain; charset=utf-8', true, 502);
    echo 'Invalid JSON from API';
    exit;
}

// Helper escape
function esc($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

// CSS (kept inline so it's self-contained and matches API HTML)
$css = <<<'CSS'
body { font-family: "Arial Unicode MS", "Noto Sans Tamil", sans-serif; background-color: #f7f7f7; margin: 0; padding: 10px; color: #0f172a; }
.page-container{max-width:794px;margin:0 auto;background:#fff;padding:12px;box-sizing:border-box;box-shadow:0 2px 6px rgba(0,0,0,0.04)}
.header-box { background-color: #ffffff; border: 2px solid #a00000; border-radius: 8px; overflow: hidden; margin-bottom: 12px; }
.table-heading { font-weight:bold; color:#ffffff; font-size:20px; background: linear-gradient(140deg,#d61818,#a00000); padding:8px 12px; border-radius:6px; display:flex; justify-content:space-between; align-items:center; }
.info-grid { width: 100%; border-spacing: 10px; border-collapse: separate; margin: 10px 0; display:flex; gap:8px; flex-wrap:wrap; }
.info-box { background-color: #ffffff; border-left: 4px solid #a00000; border-radius: 6px; padding: 8px; border: 1px solid #f0dede; flex:1 1 150px; min-width:140px; box-sizing:border-box;}
.info-title { font-size: 11px; font-weight: bold; color: #a00000; margin-bottom: 6px; text-transform: uppercase; }
.info-value { font-size: 14px; font-weight:bold; color:#1f1f1f; }
.chart-row { display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap; }
.chart-box { flex:1 1 380px; background:transparent; }
.chart-table { width:100%; border-collapse: collapse; table-layout: fixed; background-color: transparent; }
.chart-cell { background-color: #ffffff; height: 70px; vertical-align: middle; text-align: center; padding: 4px 3px; position: relative; border: 2.5px solid #a00000; border-radius: 0; }
.chart-center { background-color: #ffffff; text-align: center; vertical-align: middle; border: 1px solid #000000; }
.chart-center-text { font-size: 18px; font-weight: bold; color: #1f1f1f; font-style: italic; font-family: serif; opacity: 1; }
.chart-cell .planet { font-size:12px; font-weight:bold; color:#1f1f1f; display:block; line-height:1.2 }
.tables-row { display:flex; align-items:flex-start; gap:12px; margin-top:12px; flex-wrap:wrap; }
.table-card { background:#fff; border:1px solid #f0dede; border-radius:8px; padding:8px; box-sizing:border-box; }
.table-card .table-heading { font-weight:bold; color:#ffffff; font-size:14px; margin-bottom:8px; background: linear-gradient(140deg,#d61818,#a00000); padding:6px 8px; border-radius:6px; }
.starlord-table, .bukthi-table { width:100%; border-collapse:collapse; font-size:12px; }
.starlord-table td, .bukthi-table td { padding:6px 8px; border-bottom:1px solid #f5eeee; color:#4a1f1f; font-weight:bold; }
.footer { text-align:center; color:#8a2a2a; font-size:10px; margin-top:10px; }
@media print { .page-container{width:210mm;max-width:100%;margin:0 auto;padding:0;box-shadow:none} .footer{position:fixed;bottom:10mm;} }
/* Mobile responsive tweaks */
@media (max-width: 900px) {
    body { padding: 6px; }
    .page-container { max-width: 100%; margin: 6px auto; padding: 10px; box-shadow: none; }
    .table-heading { font-size: 16px; padding: 6px 10px; }
    .info-grid { gap: 6px; }
    .info-box { padding: 6px; min-width: 120px; }
    .info-title { font-size: 11px; }
    .info-value { font-size: 13px; }
    .chart-row { flex-direction: column; gap: 12px; }
    .chart-box { flex: none; width: 100%; }
    .chart-table { width: 100%; table-layout: fixed; font-size: 12px; }
    .chart-cell { height: 56px; padding: 4px; border-width: 1.5px; }
    .chart-center-text { font-size: 16px; }
    .tables-row { flex-direction: column; gap: 10px; }
    .table-card { width: 100%; }
    .starlord-table td, .bukthi-table td { padding: 6px 6px; font-size: 12px; }
}

@media (max-width: 420px) {
    .chart-cell { height: 48px; }
    .chart-center-text { font-size: 14px; }
    .info-value { font-size: 12px; }
}
CSS;

// Extract expected fields from JSON
$meta = $data['meta'] ?? [];
$panchang = $data['panchang'] ?? [];
$sw_planet = $data['sw_planet'] ?? [];
$navamsam = $data['navamsam'] ?? [];
$starlord = $data['starlord'] ?? [];
$bukthi = $data['bukthi'] ?? [];
$planets = $data['planets'] ?? [];

// Build chart cell content using sw_planet array if available, otherwise from planets[]
function cell_content_from_sw($i, $sw_planet, $planets) {
    if (isset($sw_planet[$i]) && trim((string)$sw_planet[$i]) !== '') return (string)$sw_planet[$i];
    $shortMap = ['Sun'=>'சூரி','Moon'=>'சந்','Mercury'=>'புத','Venus'=>'சுக்','Mars'=>'செ','Jupiter'=>'குரு','Saturn'=>'சனி','mean Node'=>'ரா','Ascendant'=>'லக்னம்'];
    $items = [];
    foreach ($planets as $p) {
        if (!isset($p['rasi_index'])) continue;
        if ((int)$p['rasi_index'] !== (int)$i) continue;
        $short = $shortMap[$p['eng'] ?? ''] ?? ($p['short'] ?? $p['key'] ?? $p['eng'] ?? '');
        $deg = isset($p['deg']) && is_numeric($p['deg']) ? ' ' . number_format((float)$p['deg'], 2) . '°' : '';
        $items[] = '<span class="planet">' . esc($short . $deg) . '</span>';
    }
    return implode('', $items);
}

function build_chart_table($getter, $centerLabel) {
    $t = [];
    $t[] = '<table class="chart-table"><tr>';
    $t[] = '<td class="chart-cell">' . $getter(11) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(0) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(1) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(2) . '</td>';
    $t[] = '</tr><tr>';
    $t[] = '<td class="chart-cell">' . $getter(10) . '</td>';
    $t[] = '<td colspan="2" rowspan="2" class="chart-center"><span class="chart-center-text">' . esc($centerLabel) . '</span></td>';
    $t[] = '<td class="chart-cell">' . $getter(3) . '</td>';
    $t[] = '</tr><tr>';
    $t[] = '<td class="chart-cell">' . $getter(9) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(4) . '</td>';
    $t[] = '</tr><tr>';
    $t[] = '<td class="chart-cell">' . $getter(8) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(7) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(6) . '</td>';
    $t[] = '<td class="chart-cell">' . $getter(5) . '</td>';
    $t[] = '</tr></table>';
    return implode('', $t);
}

// Render HTML
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html lang="ta"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Embedded Birth Chart</title><style>' . $css . '</style></head><body>';
echo '<div class="page-container">';

// Header
echo '<div class="header-box"><div class="table-heading"><span>' . esc($meta['name'] ?? '') . '</span><span style="font-size:13px">தேதி: ' . esc($meta['date'] ?? '') . ' &nbsp; நேரம்: ' . esc($meta['time'] ?? '') . ' &nbsp; இடம்: ' . esc($meta['place'] ?? '') . '</span></div></div>';

// Info gridecho '<div class="info-grid">';
echo '<div class="info-box"><div class="info-title">ராசி</div><div class="info-value">' . esc($meta['rasi'] ?? '') . '</div></div>';
echo '<div class="info-box"><div class="info-title">லக்னம்</div><div class="info-value">' . esc($meta['lagnam'] ?? '') . '</div></div>';
echo '<div class="info-box"><div class="info-title">நட்சத்திரம்</div><div class="info-value">' . esc($panchang['nakshatra'] ?? '') . '</div></div>';
echo '<div class="info-box"><div class="info-title">வாரம்</div><div class="info-value">' . esc($meta['weekday'] ?? '') . '</div></div>';
echo '<div class="info-box"><div class="info-title">திதி</div><div class="info-value">' . esc($panchang['thithi'] ?? '') . '</div></div>';
echo '<div class="info-box"><div class="info-title">யோகம்</div><div class="info-value">' . esc($panchang['yoga'] ?? '') . '</div></div>';
echo '</div>'; // info-grid

// Charts
echo '<div class="chart-row">';
// left chart (main)
echo '<div class="chart-box">';
echo build_chart_table(function($i) use ($sw_planet, $planets) { return cell_content_from_sw($i, $sw_planet, $planets); }, 'ராசி');
echo '</div>';
// right chart (navamsam) if present
if (is_array($navamsam) && count($navamsam) > 0) {
    echo '<div class="chart-box">';
    echo build_chart_table(function($i) use ($navamsam) { return isset($navamsam[$i]) ? $navamsam[$i] : ''; }, 'நவாம்சம்');
    echo '</div>';
}
echo '</div>'; // chart-row

// Tables
echo '<div class="tables-row">';
// starlordecho '<div class="table-card" style="min-width:220px;max-width:320px"><div class="table-heading">நட்சத்திர சாரம்</div><table class="starlord-table"><tbody>';
if (is_array($starlord) && count($starlord) > 0) {
    foreach ($starlord as $s) {
        $key = esc($s['key'] ?? '');
        $raw = $s['raw'] ?? '';
        $val = is_array($raw) ? esc(implode(' / ', $raw)) : esc((string)$raw);
        echo '<tr><td>' . $key . '</td><td>' . $val . '</td></tr>';
    }
} else {
    echo '<tr><td>—</td><td>—</td></tr>';
}
echo '</tbody></table></div>';

// bukthiecho '<div class="table-card" style="min-width:220px;max-width:320px"><div class="table-heading">தசா / புக்தி</div><table class="bukthi-table"><tbody>';
if (is_array($bukthi) && count($bukthi) > 0) {
    foreach ($bukthi as $b) {
        $lbl = esc($b['label'] ?? '');
        $d = $b['data'] ?? '';
        $dd = is_array($d) ? esc(implode(' | ', $d)) : esc((string)$d);
        echo '<tr><td>' . $lbl . '</td><td>' . $dd . '</td></tr>';
    }
} else {
    echo '<tr><td>—</td><td>—</td></tr>';
}
echo '</tbody></table></div>';

echo '</div>'; // tables-row

// Footer
echo '<div class="footer">Generated by DSJV Matrimony @ ' . esc(date('d-m-Y H:i')) . '</div>';

echo '</div></body></html>';

exit;
