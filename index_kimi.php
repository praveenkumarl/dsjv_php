<!doctype html>
<html lang="ta">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@500;600;700&display=swap" rel="stylesheet">

    <title>DSJV app</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script src="common.js"></script>

<style>
/* ---------- MODERN THEME ---------- */
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

body {
    font-family:'Catamaran', 'Latha', 'Nirmala UI', sans-serif;
    background:#FAF7F2;
    color:#333;
}

/* ---- Header ---- */
.app-header {
    background:#6B1F2A;               /* flat deep maroon, no gradient */
    padding:10px 14px;
    position:sticky; top:0; z-index:100;
    box-shadow:0 2px 10px rgba(0,0,0,.18);
}
.header-top {
    display:flex; align-items:center; gap:8px;
    overflow-x:auto; scrollbar-width:none;
}
.header-top::-webkit-scrollbar { display:none; }

.badge {
    background:rgba(255,255,255,.14);
    color:#fff;
    font-size:12px; font-weight:600;
    padding:5px 12px;
    border-radius:20px;
    white-space:nowrap;
    letter-spacing:.3px;
}
.badge.admin { background:#F4B400; color:#4A2C00; }

.header-row {
    display:flex; align-items:center; justify-content:space-between;
    margin-top:10px;
}
.header-icons { display:flex; align-items:center; gap:6px; }
.icon-btn {
    width:38px; height:38px;
    display:flex; align-items:center; justify-content:center;
    border-radius:12px;
    color:#fff; background:rgba(255,255,255,.12);
    font-size:17px; text-decoration:none;
    transition:background .2s, transform .15s;
}
.icon-btn:active { transform:scale(.92); background:rgba(255,255,255,.25); }

.app-title-img { height:44px; object-fit:contain; }

/* ---- Menu grid ---- */
.menu-grid {
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:14px;
    padding:18px 16px 30px;
    max-width:900px;
    margin:0 auto;
}
@media (min-width:700px){ .menu-grid { grid-template-columns:repeat(4, 1fr); } }

.card {
    position:relative;
    border:none; cursor:pointer;
    border-radius:16px; overflow:hidden;
    aspect-ratio:1/1;
    background-size:cover; background-position:center;
    box-shadow:0 4px 14px rgba(0,0,0,.10);
    transition:transform .15s ease, box-shadow .2s ease;
    padding:0;
}
.card:active { transform:scale(.96); box-shadow:0 2px 6px rgba(0,0,0,.15); }

.card-label {
    position:absolute; left:0; right:0; bottom:0;
    padding:26px 8px 9px;
    background:linear-gradient(transparent, rgba(0,0,0,.78));
    color:#fff;
    font-size:15px; font-weight:700;
    text-align:center;
    line-height:1.25;
}

/* ---- Dropdown menu ---- */
.dropdown-panel {
    display:none;
    position:absolute; left:12px; top:100%;
    background:#fff; border-radius:14px;
    box-shadow:0 8px 30px rgba(0,0,0,.22);
    min-width:210px; overflow:hidden; z-index:200;
}
.dropdown-panel.open { display:block; }
.dropdown-panel a {
    display:block; padding:12px 18px;
    color:#5A1B24; font-weight:600; font-size:15px;
    text-decoration:none;
    border-bottom:1px solid #F3E9E4;
}
.dropdown-panel a:last-child { border-bottom:none; }
.dropdown-panel a:active { background:#F9F1EC; }

.menu-wrap { position:relative; }

/* ---- Expiry alert (kept, lightly restyled) ---- */
.expiryAlertDiv {
    display:none; position:fixed; inset:0; z-index:999;
    background:rgba(0,0,0,.55); padding:20px;
}
.expiry-box {
    position:relative; background:#fff; border-radius:16px;
    padding:26px 20px; max-width:480px; margin:40px auto;
    font-size:15px; line-height:1.6; text-align:center;
}
.close-btn {
    position:absolute; top:10px; right:12px;
    width:32px; height:32px; border:none; border-radius:50%;
    background:#EEE; font-size:15px; cursor:pointer;
}
</style>

<script>
/* ---------- existing logic, unchanged ---------- */
function init(){ generateExpiryAlert(); }

function generateExpiryAlert() {
    var expiry_date_str = '30-06-2025';   // keep your PHP echo here if dynamic
    var p = expiry_date_str.split('-');
    var expiry_date = new Date(p[2], p[1]-1, p[0]);
    var today = new Date(); today.setHours(0,0,0,0);
    var daysLeft = Math.ceil((expiry_date - today) / (1000*60*60*24));

    if (daysLeft <= 30) {
        var last = localStorage.getItem('expiryAlertLastShown');
        var now = new Date().getTime();
        if (last && now - last < 1*60*60*1000) return;
        localStorage.setItem('expiryAlertLastShown', now);

        var fd = p[0]+'-'+p[1]+'-'+p[2];
        var msg_tam, msg;
        if (daysLeft > 0) {
            msg_tam = 'இன்னும் '+daysLeft+' நாட்களே உள்ளன!!!.<BR>DSJV App ன் உங்கள் சந்தா '+fd+' அன்று முடிகிறது. நீட்டிக்க நிர்வாகியைத் தொடர்பு கொள்ளவும்.<BR> &#128241; திருமதி. சௌம்யா<BR>(+91 7305731547)';
            msg = 'Only '+daysLeft+' days left!!!.<BR>Your DSJV App subscription is going to expire on '+fd+'. Please contact admin to extend.<BR>&#128241; Mrs. Sowmya<BR>(+91 7305731547)';
        } else {
            msg_tam = 'உங்கள் DSJV App ன் சந்தா '+fd+' அன்று முடிந்துவிட்டது. புதுப்பிக்க நிர்வாகியைத் தொடர்பு கொள்ளவும்.<BR> &#128241; திருமதி. சௌம்யா (+91 7305731547)';
            msg = 'Your DSJV App subscription expired on <BR>'+fd+'. Please contact admin to renew.<BR> &#128241; Mrs. Sowmya<BR>(+91 7305731547)';
        }
        document.getElementById('expiryAlertDiv').innerHTML =
            '<div class="expiry-box"><button class="close-btn" onclick="closeTable()">✕</button>' +
            '<span style="color:red;font-size:22px;font-weight:700">IMPORTANT ALERT</span><BR><BR>' +
            msg_tam + '<br><br>' + msg + '</div>';
        document.getElementById('expiryAlertDiv').style.display = 'block';
    }
}
function closeTable(){ document.getElementById('expiryAlertDiv').style.display = 'none'; }

/* swipe navigation — unchanged */
document.addEventListener('DOMContentLoaded', function () {
    const hammer = new Hammer(document.body);
    hammer.on('swipe', function (event) {
        switch (event.direction) {
            case Hammer.DIRECTION_LEFT:  window.location.href = 'current_jamakol_chart.php'; break;
            case Hammer.DIRECTION_RIGHT: window.location.href = 'new_panchang.php'; break;
            case Hammer.DIRECTION_UP:    window.location.href = 'hora.php'; break;
            case Hammer.DIRECTION_DOWN:  window.location.href = 'star_thaara_palan.php'; break;
        }
    });
});

/* dropdown toggle */
function toggleMenu(e){
    e.stopPropagation();
    document.getElementById('menuPanel').classList.toggle('open');
}
document.addEventListener('click', function(){
    var p = document.getElementById('menuPanel');
    if (p) p.classList.remove('open');
});

function go(url){ window.location.href = url; }
</script>
</head>

<body onLoad="init();">

<div class="expiryAlertDiv" id="expiryAlertDiv"></div>

<!-- ================= HEADER ================= -->
<div class="app-header">
    <div class="header-top">
        <span class="badge admin">ADMIN</span>
        <span class="badge">&#128241; 7010996514</span>
        <span class="badge">&#128197; 30-06-2025</span>
    </div>
    <div class="header-row">
        <div class="header-icons">
            <div class="menu-wrap">
                <button class="icon-btn" onclick="toggleMenu(event)" aria-label="Menu">
                    <i class="fa fa-bars"></i>
                </button>
                <div class="dropdown-panel" id="menuPanel">
                    <a href="Jamakol_input.php">ஜாமக்கோள்</a>
                    <a href="Birth_input.php">பிறப்பு ஜாதகம்</a>
                    <a href="Matching_input.php">திருமண பொருத்தம்</a>
                    <a href="numerology.php">எண் கணிதம்</a>
                    <a href="planet_kaaragam.php">கிரக காரகம்</a>
                    <a href="hora.php">ஓரை</a>
                    <a href="star_thaara_palan.php">தாரா பலன்</a>
                    <a href="new_panchang.php">பஞ்சாங்கம்</a>
                    <a href="prasannam_home.php">பிரசன்னம்</a>
                    <a href="admin.php">ADMIN</a>
                    <a href="maintenance.php">Manage</a>
                </div>
            </div>
            <a class="icon-btn" href="javascript:location.reload();" aria-label="Refresh">
                <i class="fa fa-refresh"></i>
            </a>
            <a class="icon-btn" href="admin.php" aria-label="Settings">
                <i class="fa fa-cog"></i>
            </a>
        </div>
        <img class="app-title-img" src="images/dsjv_title.png" alt="DSJV">
    </div>
</div>

<!-- ================= MENU GRID (same images, same labels, same links) ================= -->
<div class="menu-grid">
    <button class="card" style="background-image:url('images/jamakol.jpg')"
            onclick="go('Jamakol_input.php')">
        <span class="card-label">ஜாமக்கோள்</span>
    </button>
    <button class="card" style="background-image:url('images/panchang.jpg')"
            onclick="go('new_panchang.php')">
        <span class="card-label">பஞ்சாங்கம்</span>
    </button>
    <button class="card" style="background-image:url('images/numerology.jpg')"
            onclick="go('numerology.php')">
        <span class="card-label">எண் கணிதம்</span>
    </button>
    <button class="card" style="background-image:url('images/matching.jpg')"
            onclick="go('Matching_input.php')">
        <span class="card-label">பொருத்தம்</span>
    </button>
    <button class="card" style="background-image:url('images/birth.jpg')"
            onclick="go('Birth_input.php')">
        <span class="card-label">பிறப்பு</span>
    </button>
    <button class="card" style="background-image:url('images/thaara_palan.jpg')"
            onclick="go('star_thaara_palan.php')">
        <span class="card-label">தாரா பலன்</span>
    </button>
    <button class="card" style="background-image:url('images/hora.jpg')"
            onclick="go('hora.php')">
        <span class="card-label">ஹோரை</span>
    </button>
    <button class="card" style="background-image:url('images/prasannam.jpg')"
            onclick="go('prasannam_home.php')">
        <span class="card-label">பிரசன்னம்</span>
    </button>
    <button class="card" style="background-image:url('images/planet_kaaragam.jpg')"
            onclick="go('planet_kaaragam.php')">
        <span class="card-label">கிரக காரகம்</span>
    </button>
</div>

</body>
</html>