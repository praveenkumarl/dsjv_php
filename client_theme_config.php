<?php

function clientThemePresets()
{
	return array(
		'rose_purple' => array(
			'label' => 'Rose + Purple',
			'colors' => array(
				'page' => '#1d0f2e',
				'surface' => '#2a1640',
				'surface_alt' => '#35204b',
				'primary' => '#5b2be0',
				'secondary' => '#8b1e4b',
				'accent' => '#f2a7c3',
				'text' => '#fbeef4',
				'muted' => '#c4afd2',
				'on_accent' => '#1d0f2e',
				'chart_box' => '#e8c07a',
				'chart_text' => '#2a1640'
			)
		),
		'gold_purple' => array(
			'label' => 'Gold + Purple',
			'colors' => array(
				'page' => '#1d0f2e',
				'surface' => '#2a1640',
				'surface_alt' => '#35204b',
				'primary' => '#5b2be0',
				'secondary' => '#8b1e4b',
				'accent' => '#e8c07a',
				'text' => '#fbeef4',
				'muted' => '#c4afd2',
				'on_accent' => '#1d0f2e',
				'chart_box' => '#e8c07a',
				'chart_text' => '#2a1640'
			)
		),
		'navy_gold' => array(
			'label' => 'Navy + Gold',
			'colors' => array(
				'page' => '#101b30',
				'surface' => '#172744',
				'surface_alt' => '#203657',
				'primary' => '#315b9a',
				'secondary' => '#624a8e',
				'accent' => '#e5b84f',
				'text' => '#f4f6fb',
				'muted' => '#b8c5d9',
				'on_accent' => '#14233b',
				'chart_box' => '#e5b84f',
				'chart_text' => '#14233b'
			)
		),
		'forest_gold' => array(
			'label' => 'Forest + Gold',
			'colors' => array(
				'page' => '#101d1a',
				'surface' => '#1c332d',
				'surface_alt' => '#24443b',
				'primary' => '#146b55',
				'secondary' => '#2a7a78',
				'accent' => '#e4be6a',
				'text' => '#f3f5ef',
				'muted' => '#b7c8bd',
				'on_accent' => '#173027',
				'chart_box' => '#e4be6a',
				'chart_text' => '#173027'
			)
		),
		'light_rose_purple' => array(
			'label' => 'Light Rose + Purple',
			'colors' => array(
				'page' => '#f7f3fa',
				'surface' => '#ffffff',
				'surface_alt' => '#eee7f5',
				'primary' => '#6440a2',
				'secondary' => '#9a4769',
				'accent' => '#a14970',
				'text' => '#30243c',
				'muted' => '#6d6078',
				'on_accent' => '#ffffff',
				'chart_box' => '#e8c07a',
				'chart_text' => '#2a1640'
			)
		),
		'light_gold_purple' => array(
			'label' => 'Light Gold + Purple',
			'colors' => array(
				'page' => '#faf7ef',
				'surface' => '#ffffff',
				'surface_alt' => '#f1ead8',
				'primary' => '#604397',
				'secondary' => '#805d30',
				'accent' => '#896219',
				'text' => '#302b3d',
				'muted' => '#6c6470',
				'on_accent' => '#ffffff',
				'chart_box' => '#e8c07a',
				'chart_text' => '#2a1640'
			)
		),
		'light_navy_gold' => array(
			'label' => 'Light Navy + Gold',
			'colors' => array(
				'page' => '#f2f6fb',
				'surface' => '#ffffff',
				'surface_alt' => '#e8eff8',
				'primary' => '#315b91',
				'secondary' => '#536a87',
				'accent' => '#88631b',
				'text' => '#1d2b40',
				'muted' => '#5d6b7f',
				'on_accent' => '#ffffff',
				'chart_box' => '#e5b84f',
				'chart_text' => '#14233b'
			)
		),
		'light_forest_gold' => array(
			'label' => 'Light Forest + Gold',
			'colors' => array(
				'page' => '#f2f7f3',
				'surface' => '#ffffff',
				'surface_alt' => '#e7f0e9',
				'primary' => '#286b55',
				'secondary' => '#456b60',
				'accent' => '#81621e',
				'text' => '#20372e',
				'muted' => '#566b60',
				'on_accent' => '#ffffff',
				'chart_box' => '#e4be6a',
				'chart_text' => '#173027'
			)
		)
	);
}

function clientThemeDefaultConfig()
{
	$presets = clientThemePresets();
	return array(
		'preset' => 'rose_purple',
		'colors' => $presets['rose_purple']['colors']
	);
}

function clientThemeNormalizeConfig($presetKey, $colors)
{
	$presets = clientThemePresets();
	if (!isset($presets[$presetKey])) {
		throw new InvalidArgumentException('Choose a valid theme preset.');
	}

	if (!is_array($colors)) {
		$colors = array();
	}

	$normalized = array();
	foreach ($presets[$presetKey]['colors'] as $name => $defaultColor) {
		if (array_key_exists($name, $colors) && !is_string($colors[$name])) {
			throw new InvalidArgumentException('Each theme color must be a six-digit hex value.');
		}
		$value = array_key_exists($name, $colors) ? trim($colors[$name]) : $defaultColor;
		if (!preg_match('/\A#[0-9a-fA-F]{6}\z/', $value)) {
			throw new InvalidArgumentException('Each theme color must be a six-digit hex value.');
		}
		$normalized[$name] = strtolower($value);
	}

	return array('preset' => $presetKey, 'colors' => $normalized);
}

function clientThemeResolvePresetKey($queryPreset, $postedPreset, $postedColors)
{
	$presets = clientThemePresets();
	$expectedColors = $presets['rose_purple']['colors'];
	$hasCompleteColorSet = is_array($postedColors) && count($postedColors) === count($expectedColors);
	if ($hasCompleteColorSet) {
		foreach (array_keys($expectedColors) as $colorName) {
			if (!array_key_exists($colorName, $postedColors)) {
				$hasCompleteColorSet = false;
				break;
			}
		}
	}

	if ($hasCompleteColorSet) {
		foreach ($presets as $candidateKey => $preset) {
			try {
				$submittedConfig = clientThemeNormalizeConfig($candidateKey, $postedColors);
				if ($submittedConfig['colors'] === $preset['colors']) {
					return $candidateKey;
				}
			} catch (InvalidArgumentException $exception) {
				continue;
			}
		}
	}

	foreach (array($queryPreset, $postedPreset) as $candidateKey) {
		if (is_string($candidateKey) && isset($presets[$candidateKey])) {
			return $candidateKey;
		}
	}

	throw new InvalidArgumentException('The selected built-in preset was not received. Reload the page and select it again.');
}

function clientThemeContrastPairs()
{
	return array(
		array('label' => 'Main text on page', 'foreground' => 'text', 'background' => 'page'),
		array('label' => 'Main text on cards', 'foreground' => 'text', 'background' => 'surface'),
		array('label' => 'Main text on secondary surfaces', 'foreground' => 'text', 'background' => 'surface_alt'),
		array('label' => 'Secondary text on page', 'foreground' => 'muted', 'background' => 'page'),
		array('label' => 'Secondary text on cards', 'foreground' => 'muted', 'background' => 'surface'),
		array('label' => 'Secondary text on secondary surfaces', 'foreground' => 'muted', 'background' => 'surface_alt'),
		array('label' => 'Accent on page', 'foreground' => 'accent', 'background' => 'page'),
		array('label' => 'Accent on cards', 'foreground' => 'accent', 'background' => 'surface'),
		array('label' => 'Accent on secondary surfaces', 'foreground' => 'accent', 'background' => 'surface_alt'),
		array('label' => 'Text on accent labels', 'foreground' => 'on_accent', 'background' => 'accent'),
		array('label' => 'Chart text on chart boxes', 'foreground' => 'chart_text', 'background' => 'chart_box')
	);
}

function clientThemeContrastRatio($firstColor, $secondColor)
{
	$luminance = function ($hex) {
		$channels = array(
			hexdec(substr($hex, 1, 2)) / 255,
			hexdec(substr($hex, 3, 2)) / 255,
			hexdec(substr($hex, 5, 2)) / 255
		);
		foreach ($channels as &$channel) {
			$channel = $channel <= 0.04045
				? $channel / 12.92
				: pow(($channel + 0.055) / 1.055, 2.4);
		}
		unset($channel);
		return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
	};

	$firstLuminance = $luminance($firstColor);
	$secondLuminance = $luminance($secondColor);
	$lighter = max($firstLuminance, $secondLuminance);
	$darker = min($firstLuminance, $secondLuminance);
	return ($lighter + 0.05) / ($darker + 0.05);
}

function clientThemeContrastTextColor($backgrounds)
{
	$bestColor = '#ffffff';
	$bestRatio = -1;
	foreach (array('#ffffff', '#000000') as $candidate) {
		$minimumRatio = INF;
		foreach ($backgrounds as $background) {
			$minimumRatio = min($minimumRatio, clientThemeContrastRatio($candidate, $background));
		}
		if ($minimumRatio > $bestRatio) {
			$bestColor = $candidate;
			$bestRatio = $minimumRatio;
		}
	}
	return $bestColor;
}

function clientThemeContrastIssues($config)
{
	$issues = array();
	foreach (clientThemeContrastPairs() as $pair) {
		$ratio = clientThemeContrastRatio(
			$config['colors'][$pair['foreground']],
			$config['colors'][$pair['background']]
		);
		if ($ratio < 4.5) {
			$issues[] = $pair['label'] . ' (' . number_format($ratio, 2) . ':1; needs 4.5:1)';
		}
	}
	return $issues;
}

function clientThemeAssertAccessibleConfig($config)
{
	$issues = clientThemeContrastIssues($config);
	if ($issues) {
		throw new InvalidArgumentException('Improve color contrast: ' . implode('; ', $issues) . '.');
	}
}

function clientThemeConnect()
{
	global $servername, $username, $password, $db;
	require_once __DIR__ . '/db_config.php';
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	$conn = new mysqli($servername, $username, $password, $db);
	$conn->set_charset('utf8mb4');
	return $conn;
}

function clientThemeEnsureTable($conn)
{
	$sql = "CREATE TABLE IF NOT EXISTS client_theme_settings (
		setting_id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
		preset_key VARCHAR(40) NOT NULL,
		palette_json TEXT NOT NULL,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	$conn->query($sql);
	$conn->query("CREATE TABLE IF NOT EXISTS client_theme_custom_themes (
		theme_name VARCHAR(80) NOT NULL PRIMARY KEY,
		preset_key VARCHAR(40) NOT NULL,
		palette_json TEXT NOT NULL,
		updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function clientThemeSaveCustomTheme($conn, $name, $config)
{
	clientThemeAssertAccessibleConfig($config);
	$name = trim($name);
	if (!preg_match('/\A[^\p{C}]{1,80}\z/u', $name)) {
		throw new InvalidArgumentException('Enter a custom theme name with 1 to 80 characters.');
	}
	foreach (clientThemePresets() as $preset) {
		if (strcasecmp($name, $preset['label']) === 0) {
			throw new InvalidArgumentException('Custom theme names must be different from built-in preset names.');
		}
	}

	$json = json_encode($config['colors'], JSON_UNESCAPED_SLASHES);
	if ($json === false) {
		throw new RuntimeException('Theme colors could not be encoded.');
	}

	$stmt = $conn->prepare(
		'INSERT INTO client_theme_custom_themes (theme_name, preset_key, palette_json)
		 VALUES (?, ?, ?)
		 ON DUPLICATE KEY UPDATE preset_key = VALUES(preset_key), palette_json = VALUES(palette_json)'
	);
	$stmt->bind_param('sss', $name, $config['preset'], $json);
	$stmt->execute();
	$stmt->close();
}

function clientThemeLoadCustomThemes($conn, &$error = null)
{
	$themes = array();
	$stmt = $conn->prepare('SELECT theme_name, preset_key, palette_json FROM client_theme_custom_themes ORDER BY theme_name');
	$stmt->execute();
	$stmt->bind_result($name, $presetKey, $paletteJson);

	while ($stmt->fetch()) {
		$colors = json_decode($paletteJson, true);
		try {
			if (json_last_error() !== JSON_ERROR_NONE || !is_array($colors)) {
				throw new InvalidArgumentException('Invalid palette data.');
			}
			$config = clientThemeNormalizeConfig($presetKey, $colors);
			$themes[$name] = $config;
		} catch (InvalidArgumentException $exception) {
			$error = 'A saved custom theme is invalid and was skipped.';
			error_log('A saved client custom theme failed palette validation.');
		}
	}

	$stmt->close();
	return $themes;
}

function clientThemeLoadConfig($conn, &$error = null)
{
	$default = clientThemeDefaultConfig();
	try {
		$stmt = $conn->prepare('SELECT preset_key, palette_json FROM client_theme_settings WHERE setting_id = 1');
		$stmt->execute();
		$stmt->bind_result($presetKey, $paletteJson);
		if (!$stmt->fetch()) {
			$stmt->close();
			return $default;
		}
		$stmt->close();
	} catch (mysqli_sql_exception $exception) {
		if ((int)$exception->getCode() === 1146) {
			return $default;
		}
		$error = 'Saved theme settings could not be loaded.';
		error_log('Client theme load failed with MySQL error ' . (int)$exception->getCode() . '.');
		return $default;
	}

	$colors = json_decode($paletteJson, true);
	if (json_last_error() !== JSON_ERROR_NONE || !is_array($colors)) {
		$error = 'Saved theme colors are invalid; the default theme is being shown.';
		error_log('Client theme settings contain invalid JSON.');
		return $default;
	}

	try {
		return clientThemeNormalizeConfig($presetKey, $colors);
	} catch (InvalidArgumentException $exception) {
		$error = 'Saved theme colors are invalid; the default theme is being shown.';
		error_log('Client theme settings failed palette validation.');
		return $default;
	}
}

function clientThemeSaveConfig($conn, $config)
{
	$json = json_encode($config['colors'], JSON_UNESCAPED_SLASHES);
	if ($json === false) {
		throw new RuntimeException('Theme colors could not be encoded.');
	}

	$stmt = $conn->prepare(
		'INSERT INTO client_theme_settings (setting_id, preset_key, palette_json)
		 VALUES (1, ?, ?)
		 ON DUPLICATE KEY UPDATE preset_key = VALUES(preset_key), palette_json = VALUES(palette_json)'
	);
	$stmt->bind_param('ss', $config['preset'], $json);
	$stmt->execute();
	$stmt->close();
}

function clientThemeHexToRgba($hex, $alpha)
{
	$red = hexdec(substr($hex, 1, 2));
	$green = hexdec(substr($hex, 3, 2));
	$blue = hexdec(substr($hex, 5, 2));
	return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
}

function clientThemeEscape($value)
{
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
