<?php
require_once __DIR__ . '/webview_only.php';
require_once __DIR__ . '/language_switch.php';
session_start();

$currentLanguage = isset($_COOKIE['language']) && strtolower($_COOKIE['language']) === 'english'
	? 'english'
	: 'tamil';
$isEnglish = $currentLanguage === 'english';
?>
<!doctype html>
<html lang="<?php echo $isEnglish ? 'en' : 'ta'; ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#1a0b2e">
	<title>PK Astro</title>
	<meta name="description" content="Astrology tools and insights from PkAstro">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600&family=Noto+Serif+Tamil:wght@600;700&family=Noto+Sans:wght@400;500&family=Noto+Serif:wght@600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/chart_style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/landing-theme.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/client_theme_css.php">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
	<script src="script.js?<?php echo date('ymdhis'); ?>"></script>
</head>
<body class="theme-landing theme-index-preview mobile-welcome-page">
	<div class="aurora" aria-hidden="true"></div>
	<main class="wrap">
		<header class="topbar app-topbar mobile-welcome-topbar">
			<div class="mobile-welcome-brand" aria-label="PK Astro">
				<span class="mobile-welcome-brand-mark"><i class="fa fa-star-o" aria-hidden="true"></i></span>
				<span class="mobile-welcome-brand-copy">
					<small><?php echo $isEnglish ? 'ASTROLOGY TOOLS' : 'ஜோதிடக் கருவிகள்'; ?></small>
					<strong>PK Astro</strong>
				</span>
			</div>
			<?php renderLanguageSwitch($currentLanguage); ?>
		</header>

		<section class="mobile-welcome-card" aria-labelledby="mobileWelcomeTitle">
			<div class="mobile-welcome-icon" aria-hidden="true"><i class="fa fa-moon-o"></i></div>
			<p class="mobile-welcome-kicker"><?php echo $isEnglish ? 'Astrology, all in one place' : 'ஜோதிடம், ஒரே இடத்தில்'; ?></p>
			<h1 class="headline mobile-welcome-heading" id="mobileWelcomeTitle"><?php echo $isEnglish ? 'Welcome to PkAstro' : 'PkAstro-க்கு வரவேற்கிறோம்'; ?></h1>
			<p class="mobile-welcome-description"><?php
				echo $isEnglish
					? 'Your search for an astrology app ends here. Explore Jamakol, birth and Brihu Nandi charts, Nakshatra Tara, Karanam, and planet and zodiac insights.'
					: 'ஜாமக்கோள், பிறப்பு மற்றும் பிருகு நந்தி ஜாதகங்கள், நட்சத்திர தாராபலம், கரணம், கிரக மற்றும் ராசி தகவல்கள் உள்ளிட்ட பல ஜோதிடக் கருவிகளை ஒரே இடத்தில் பயன்படுத்துங்கள்.';
			?></p>
		</section>
		<footer class="site-footer">Developed &amp; Maintained by PNK Astro Labs</footer>
	</main>
</body>
</html>
