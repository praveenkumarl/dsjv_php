<?php require_once __DIR__ . '/webview_only.php'; ?>
<!doctype html>
<html lang="ta">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@500;600;700;800&display=swap" rel="stylesheet">

    <title>DSJV app</title>
    <meta name="description" content="One stop website for Jamakol Prasannam">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
    <script src="common.js"></script>

<style>
/* ================= CELESTIAL BENTO DECK ================= */
* { margin:0; padding:0; box-sizing:border-box; -webkit-tap-highlight-color:transparent; }

:root {
    --gold:#F5C542;
    --gold-glow:rgba(245,197,66,.45);
    --ink:#0A0A18;
}

html,body { height:100%; }
body {
    font-family:'Catamaran','Latha','Nirmala UI',sans-serif;
    color:#F2EEFF;
    background:var(--ink);
    overflow-x:hidden;
}

/* --- animated aurora blobs --- */
.aurora { position:fixed; inset:0; z-index:0; overflow:hidden; }
.blob {
    position:absolute; border-radius:50%;
    filter:blur(90px); opacity:.5;
    animation:drift 18s ease-in-out infinite alternate;
}
.blob.b1 { width:340px; height:340px; background:#5B2BE0; top:-90px; left:-70px; }
.blob.b2 { width:300px; height:300px; background:#B8860B; bottom:-80px; right:-60px; animation-delay:-6s; }
.blob.b3 { width:220px; height:220px; background:#8B1E4B; top:40%; left:60%; animation-delay:-12s; }
@keyframes drift {
    0%   { transform:translate(0,0) scale(1); }
    50%  { transform:translate(50px,40px) scale(1.15); }
    100% { transform:translate(-40px,-30px) scale(.95); }
}

/* --- constellation canvas --- */
#stars { position:fixed; inset:0; z-index:1; pointer-events:none; }

/* --- content --- */
.wrap { position:relative; z-index:2; max-width:520px; margin:0 auto; padding:18px 18px 120px; }

/* --- header --- */
.topbar {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom:6px;
}
.brand {
    display:flex; align-items:center; gap:10px;
}
.brand img { height:52px; filter:drop-shadow(0 0 12px var(--gold-glow)); animation:logoPulse 4s ease-in-out infinite; }
@keyframes logoPulse { 0%,100%{filter:drop-shadow(0 0 6px rgba(245,197,66,.25))} 50%{filter:drop-shadow(0 0 18px var(--gold-glow))} }

.chips { display:flex; gap:6px; margin:10px 0 18px; overflow-x:auto; scrollbar-width:none; }
.chips::-webkit-scrollbar{ display:none; }
.chip {
    font-size:11px; font-weight:700; letter-spacing:.5px;
    padding:5px 12px; border-radius:20px; white-space:nowrap;
    background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.12);
    backdrop-filter:blur(8px); color:#DCD5F2;
}
.chip.admin {
    background:linear-gradient(135deg,var(--gold),#E8951F);
    color:#2A1A00; border:none;
    box-shadow:0 0 16px var(--gold-glow);
}

/* headline with animated gradient text */
.headline {
    font-size:26px; font-weight:800; line-height:1.2;
    margin:4px 0 20px;
    background:linear-gradient(90deg,#FFF,#F5C542,#B98CFF,#FFF);
    background-size:300% 100%;
    -webkit-background-clip:text; background-clip:text;
    -webkit-text-fill-color:transparent;
    animation:sheen 6s linear infinite;
}
@keyframes sheen { to { background-position:300% 0; } }

/* --- bento grid --- */
.deck {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}
.card {
    position:relative; border:none; cursor:pointer; padding:0;
    border-radius:22px; overflow:hidden;
    aspect-ratio:1/1;
    background-size:cover; background-position:center;
    transform-style:preserve-3d;
    opacity:0;
    animation:deal .7s cubic-bezier(.2,.9,.3,1.2) forwards;
    box-shadow:0 10px 30px rgba(0,0,0,.45);
}
.card.hero { grid-column:1 / -1; aspect-ratio:16/10; }

/* cascade entrance with rotation */
@keyframes deal {
    from { opacity:0; transform:translateY(46px) rotate(4deg) scale(.85); }
    to   { opacity:1; transform:none; }
}
.card:nth-child(1){animation-delay:.05s} .card:nth-child(2){animation-delay:.14s}
.card:nth-child(3){animation-delay:.23s} .card:nth-child(4){animation-delay:.32s}
.card:nth-child(5){animation-delay:.41s} .card:nth-child(6){animation-delay:.50s}
.card:nth-child(7){animation-delay:.59s} .card:nth-child(8){animation-delay:.68s}
.card:nth-child(9){animation-delay:.77s}

/* neon edge that lights on touch */
.card::before {
    content:''; position:absolute; inset:0; z-index:2; border-radius:22px;
    border:1.5px solid rgba(245,197,66,.25);
    pointer-events:none; transition:border-color .3s, box-shadow .3s;
}
.card:active::before {
    border-color:var(--gold);
    box-shadow:inset 0 0 30px rgba(245,197,66,.15), 0 0 30px var(--gold-glow);
}

/* periodic shimmer sweep */
.card::after {
    content:''; position:absolute; top:0; bottom:0; left:-80%; width:55%; z-index:2;
    background:linear-gradient(105deg, transparent, rgba(255,255,255,.28), transparent);
    transform:skewX(-20deg);
    animation:sweep 7s ease-in-out infinite;
    pointer-events:none;
}
.card:nth-child(odd)::after { animation-delay:2.2s; }
.card:nth-child(3n)::after  { animation-delay:4.1s; }
@keyframes sweep {
    0%, 78%  { left:-80%; }
    92%, 100%{ left:130%; }
}

/* moving glare that follows pointer (set via JS vars) */
.glare {
    position:absolute; inset:0; z-index:3; pointer-events:none;
    background:radial-gradient(circle at var(--gx,50%) var(--gy,50%), rgba(255,255,255,.22), transparent 45%);
    opacity:0; transition:opacity .25s;
}
.card:hover .glare, .card.touching .glare { opacity:1; }

/* label plate */
.card-label {
    position:absolute; left:0; right:0; bottom:0; z-index:4;
    padding:34px 12px 12px;
    background:linear-gradient(transparent, rgba(6,6,20,.9));
    color:#FFF6DE;
    font-size:16.5px; font-weight:800; text-align:center;
    text-shadow:0 0 14px rgba(245,197,66,.55);
    letter-spacing:.3px;
}
.card.hero .card-label { font-size:22px; padding:44px 12px 16px; }

/* arrow hint on hero */
.hero .go-hint {
    position:absolute; top:12px; right:12px; z-index:4;
    width:34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background:rgba(245,197,66,.9); color:#2A1A00;
    font-size:14px;
    animation:nudge 2s ease-in-out infinite;
}
@keyframes nudge { 0%,100%{transform:translateX(0)} 50%{transform:translateX(5px)} }

/* --- floating glass dock --- */
.dock {
    position:fixed; left:50%; bottom:18px; transform:translateX(-50%);
    z-index:100;
    display:flex; gap:6px; padding:8px;
    border-radius:24px;
    background:rgba(18,18,40,.6);
    backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,.14);
    box-shadow:0 12px 40px rgba(0,0,0,.55);
}
.dock-btn {
    width:50px; height:50px; border:none; cursor:pointer;
    border-radius:18px; font-size:19px;
    display:flex; align-items:center; justify-content:center;
    background:transparent; color:var(--gold); text-decoration:none;
    transition:all .25s;
}
.dock-btn:active { transform:scale(.85); background:rgba(245,197,66,.15); box-shadow:0 0 18px var(--gold-glow); }

/* --- dropdown --- */
.menu-wrap { position:relative; }
.dropdown-panel {
    display:none;
    position:absolute; bottom:calc(100% + 14px); left:0;
    min-width:230px; border-radius:18px; overflow:hidden;
    background:rgba(20,20,46,.94);
    backdrop-filter:blur(18px); -webkit-backdrop-filter:blur(18px);
    border:1px solid rgba(255,255,255,.14);
    box-shadow:0 16px 50px rgba(0,0,0,.6);
}
.dropdown-panel.open { display:block; animation:pop .22s cubic-bezier(.2,.9,.3,1.3); }
@keyframes pop { from{opacity:0; transform:translateY(12px) scale(.95)} }
.dropdown-panel a {
    display:block; padding:13px 20px;
    color:#F2EEFF; font-weight:600; font-size:15px; text-decoration:none;
    border-bottom:1px solid rgba(255,255,255,.07);
    transition:background .2s, color .2s;
}
.dropdown-panel a:last-child { border-bottom:none; }
.dropdown-panel a:active { background:rgba(245,197,66,.14); color:var(--gold); }

/* --- expiry alert --- */
.expiryAlertDiv {
    display:none; position:fixed; inset:0; z-index:999;
    background:rgba(5,5,16,.7); backdrop-filter:blur(8px); padding:20px;
}
.expiry-box {
    position:relative; max-width:480px; margin:40px auto;
    background:rgba(20,20,48,.96);
    border:1px solid rgba(245,197,66,.45);
    border-radius:20px; padding:28px 22px;
    font-size:15px; line-height:1.65; text-align:center;
    box-shadow:0 0 50px rgba(245,197,66,.18);
    animation:pop .3s ease;
}
.close-btn {
    position:absolute; top:10px; right:12px;
    width:32px; height:32px; border:none; border-radius:50%;
    background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15);
    color:#fff; font-size:14px; cursor:pointer;
}
</style>
</head>

<body onLoad="init();">

<!-- animated background layers -->
<div class="aurora">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
</div>
<canvas id="stars"></canvas>

<div class="expiryAlertDiv" id="expiryAlertDiv"></div>

<div class="wrap">

    <!-- header -->
    <div class="topbar">
        <div class="brand">
            <img src="images/dsjv_title.png" alt="DSJV">
        </div>
    </div>

    <div class="chips">
        <span class="chip admin">ADMIN</span>
        <span class="chip">&#128241; 7010996514</span>
        <span class="chip">&#128197; 30-06-2025</span>
    </div>

    <div class="headline">தர்மசாஸ்தா<br>ஜோதிட வித்யாலயா</div>

    <!-- ============ BENTO DECK — same images / labels / links ============ -->
    <div class="deck">
        <button class="card hero" style="background-image:url('images/jamakol.jpg')" onclick="go('Jamakol_input.php')">
            <span class="go-hint"><i class="fa fa-arrow-right"></i></span>
            <span class="glare"></span>
            <span class="card-label">ஜாமக்கோள்</span>
        </button>
        <button class="card" style="background-image:url('images/panchang.jpg')" onclick="go('new_panchang.php')">
            <span class="glare"></span><span class="card-label">பஞ்சாங்கம்</span>
        </button>
        <button class="card" style="background-image:url('images/numerology.jpg')" onclick="go('numerology.php')">
            <span class="glare"></span><span class="card-label">எண் கணிதம்</span>
        </button>
        <button class="card" style="background-image:url('images/matching.jpg')" onclick="go('Matching_input.php')">
            <span class="glare"></span><span class="card-label">பொருத்தம்</span>
        </button>
        <button class="card" style="background-image:url('images/birth.jpg')" onclick="go('Birth_input.php')">
            <span class="glare"></span><span class="card-label">பிறப்பு</span>
        </button>
        <button class="card" style="background-image:url('images/thaara_palan.jpg')" onclick="go('star_thaara_palan.php')">
            <span class="glare"></span><span class="card-label">தாரா பலன்</span>
        </button>
        <button class="card" style="background-image:url('images/hora.jpg')" onclick="go('hora.php')">
            <span class="glare"></span><span class="card-label">ஓரை</span>
        </button>
        <button class="card" style="background-image:url('images/prasannam.jpg')" onclick="go('prasannam_home.php')">
            <span class="glare"></span><span class="card-label">பிரசன்னம்</span>
        </button>
        <button class="card" style="background-image:url('images/planet_kaaragam.jpg')" onclick="go('planet_kaaragam.php')">
            <span class="glare"></span><span class="card-label">கிரக காரகம்</span>
        </button>
    </div>
</div>

<!-- floating dock -->
<div class="dock">
    <div class="menu-wrap">
        <button class="dock-btn" onclick="toggleMenu(event)" aria-label="Menu"><i class="fa fa-bars"></i></button>
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
    <a class="dock-btn" href="javascript:location.reload();" aria-label="Refresh"><i class="fa fa-refresh"></i></a>
    <a class="dock-btn" href="admin.php" aria-label="Settings"><i class="fa fa-cog"></i></a>
</div>

<script>
/* ---------- existing logic, unchanged ---------- */
function init(){ generateExpiryAlert(); }

function generateExpiryAlert() {
    var expiry_date_str = '30-06-2025';   // keep PHP echo here if dynamic
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
            '<span style="color:#F5C542;font-size:22px;font-weight:800">IMPORTANT ALERT</span><BR><BR>' +
            msg_tam + '<br><br>' + msg + '</div>';
        document.getElementById('expiryAlertDiv').style.display = 'block';
    }
}
function closeTable(){ document.getElementById('expiryAlertDiv').style.display = 'none'; }

function toggleMenu(e){
    e.stopPropagation();
    document.getElementById('menuPanel').classList.toggle('open');
}
document.addEventListener('click', function(){
    var p = document.getElementById('menuPanel');
    if (p) p.classList.remove('open');
});

function go(url){ window.location.href = url; }

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

    initStars();
    initTilt();
});

/* ---------- constellation starfield ---------- */
function initStars(){
    const cv = document.getElementById('stars');
    const ctx = cv.getContext('2d');
    let W, H, pts = [];

    function resize(){
        W = cv.width  = innerWidth;
        H = cv.height = innerHeight;
        const n = Math.min(70, Math.floor(W*H/14000));
        pts = Array.from({length:n}, () => ({
            x: Math.random()*W, y: Math.random()*H,
            vx: (Math.random()-.5)*.25, vy: (Math.random()-.5)*.25,
            r: Math.random()*1.4 + .4
        }));
    }
    resize();
    addEventListener('resize', resize);

    (function frame(){
        ctx.clearRect(0,0,W,H);
        for (const p of pts){
            p.x += p.vx; p.y += p.vy;
            if (p.x<0||p.x>W) p.vx*=-1;
            if (p.y<0||p.y>H) p.vy*=-1;
            ctx.beginPath();
            ctx.arc(p.x,p.y,p.r,0,7);
            ctx.fillStyle = 'rgba(255,255,255,.75)';
            ctx.fill();
        }
        for (let i=0;i<pts.length;i++){
            for (let j=i+1;j<pts.length;j++){
                const dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y;
                const d=dx*dx+dy*dy;
                if (d < 11000){
                    ctx.strokeStyle = 'rgba(245,197,66,'+(0.14*(1-d/11000))+')';
                    ctx.beginPath();
                    ctx.moveTo(pts[i].x,pts[i].y);
                    ctx.lineTo(pts[j].x,pts[j].y);
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(frame);
    })();
}

/* ---------- 3D tilt + glare (mouse & touch) ---------- */
function initTilt(){
    document.querySelectorAll('.card').forEach(card => {
        const glare = card.querySelector('.glare');
        const max = 10; // degrees

        function tilt(x, y){
            const r = card.getBoundingClientRect();
            const px = (x - r.left) / r.width;
            const py = (y - r.top)  / r.height;
            card.style.transform =
                'perspective(700px) rotateY('+((px-.5)*max*2)+'deg) rotateX('+((0.5-py)*max*2)+'deg) scale(1.03)';
            if (glare){
                glare.style.setProperty('--gx', (px*100)+'%');
                glare.style.setProperty('--gy', (py*100)+'%');
            }
        }
        function reset(){
            card.style.transform = '';
            card.classList.remove('touching');
        }

        card.addEventListener('mousemove', e => tilt(e.clientX, e.clientY));
        card.addEventListener('mouseleave', reset);
        card.addEventListener('touchstart', e => {
            card.classList.add('touching');
            const t = e.touches[0];
            tilt(t.clientX, t.clientY);
        }, {passive:true});
        card.addEventListener('touchmove', e => {
            const t = e.touches[0];
            tilt(t.clientX, t.clientY);
        }, {passive:true});
        card.addEventListener('touchend', reset);
    });
}
</script>

</body>
</html>