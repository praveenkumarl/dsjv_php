<?php session_start();
require "lib.php";

$language = isset($_COOKIE['language']) && strtolower(trim($_COOKIE['language'])) === 'english' ? 'english' : 'tamil';
init_language($language);

$key = isset($_COOKIE['key']) ? $_COOKIE['key'] : "";

?>

<!doctype html>
<html lang="<?php echo $language === 'english' ? 'en' : 'ta'; ?>" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://html5-templates.com/" />
    <title>PK Astro - <?php echo htmlspecialchars(__('prasannam'), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="One stop website for Jamakol Prasannam">
	
	<link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@500;600;700;800&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600&family=Noto+Serif+Tamil:wght@600;700&family=Noto+Sans:wght@400;500&family=Noto+Serif:wght@600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="css/main.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="style.css?<?php echo date('ymdhis'); ?>">
	<link rel="stylesheet" href="css/prasannam.css?<?php echo date('ymdhis'); ?>">
	<link href='https://fonts.googleapis.com/css?family=Julius Sans One' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">	

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	
	</script>
	<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
	
    <script type="text/javascript">
	
	function init(){
		
	}
			
    </script>
</head>

<body class="prasannam-page prasannam-home-page" onLoad="init();">

	
<div class="title"></div>

<div class='column-menu'>
<?php displayUserInfo($_SESSION['userinfo']);?>
	<div class='desktop'>
		<button class="menu" onClick="window.open('index.php','_self')"><?php echo htmlspecialchars(__('home'), ENT_QUOTES, 'UTF-8'); ?></button>
	</div>
	<div class='mobile'>
		<?php ob_start(); include "prasannamMenu_mobile.php"; echo translate_menu_html(ob_get_clean()); ?>
	</div>
</div>
<main class="column-body prasannam-home">
	<header class="prasannam-intro">
		<h1><?php echo htmlspecialchars(__('prasannam'), ENT_QUOTES, 'UTF-8'); ?></h1>
		<p><?php echo htmlspecialchars(__('prasannam_choose_method'), ENT_QUOTES, 'UTF-8'); ?></p>
	</header>
	<nav class="prasannam-home-grid" aria-label="<?php echo htmlspecialchars(__('prasannam_choose_method'), ENT_QUOTES, 'UTF-8'); ?>">
		<a class="prasannam-option" href="prasannams.php?type=1">
			<span class="prasannam-option-index">01</span>
			<span class="prasannam-option-name"><?php echo htmlspecialchars(__('prasannam_lagna_menu'), ENT_QUOTES, 'UTF-8'); ?></span>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="prasannam-option" href="prasannams.php?type=2">
			<span class="prasannam-option-index">02</span>
			<span class="prasannam-option-name"><?php echo htmlspecialchars(__('prasannam_clock_title'), ENT_QUOTES, 'UTF-8'); ?></span>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="prasannam-option" href="prasannams.php?type=3">
			<span class="prasannam-option-index">03</span>
			<span class="prasannam-option-name"><?php echo htmlspecialchars(__('prasannam_rasi_title'), ENT_QUOTES, 'UTF-8'); ?></span>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="prasannam-option" href="prasannams.php?type=4">
			<span class="prasannam-option-index">04</span>
			<span class="prasannam-option-name"><?php echo htmlspecialchars(__('prasannam_paadha_title'), ENT_QUOTES, 'UTF-8'); ?></span>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="prasannam-option" href="prasannams.php?type=5">
			<span class="prasannam-option-index">05</span>
			<span class="prasannam-option-name"><?php echo htmlspecialchars(__('prasannam_horai_title'), ENT_QUOTES, 'UTF-8'); ?></span>
			<i class="fa fa-arrow-right" aria-hidden="true"></i>
		</a>
	</nav>
</main>
	

</body>
</html>