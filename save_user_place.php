<?php
$outputBufferLevel = ob_get_level();
ob_start();
require_once __DIR__ . '/lib.php';

function redirectProfileLocation($result) {
	global $outputBufferLevel;

	while (ob_get_level() > $outputBufferLevel) {
		ob_end_clean();
	}

	$path = 'index.php';
	$query = array();
	if (isset($_POST['return_to']) && is_string($_POST['return_to'])) {
		$returnTo = trim($_POST['return_to']);
		$returnParts = parse_url($returnTo);
		if ($returnParts !== false
			&& !isset($returnParts['scheme'])
			&& !isset($returnParts['host'])
			&& isset($returnParts['path'])
			&& strpos($returnParts['path'], '/') === 0
			&& strpos($returnParts['path'], '//') !== 0
			&& strpos($returnParts['path'], '\\') === false
			&& preg_match('/\.php$/i', $returnParts['path'])
			&& basename($returnParts['path']) !== 'save_user_place.php'
			&& !preg_match('/[\x00-\x1F\x7F]/', $returnTo)) {
			$path = $returnParts['path'];
			if (isset($returnParts['query'])) {
				parse_str($returnParts['query'], $query);
			}
		}
	}
	unset($query['profile_saved'], $query['profile_error']);
	$query['profile_' . $result] = '1';

	header('Location: ' . $path . '?' . http_build_query($query), true, 303);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLogged()
	|| empty($_SESSION['userinfo']['phone'])
	|| $_SESSION['userinfo']['phone'] === '9999999999'
	|| empty($_SESSION['profile_location_token'])
	|| !isset($_POST['csrf_token'])
	|| !is_string($_POST['csrf_token'])
	|| !hash_equals($_SESSION['profile_location_token'], $_POST['csrf_token'])) {
	redirectProfileLocation('error');
}

$place = isset($_POST['place']) ? trim($_POST['place']) : '';
$timezone = isset($_POST['tz']) ? trim($_POST['tz']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$officeName = isset($_POST['office_name']) ? trim($_POST['office_name']) : '';
$officeContact = isset($_POST['office_contact']) ? trim($_POST['office_contact']) : '';
$phone = $_SESSION['userinfo']['phone'];
$nameMatches = array();
$nameLength = preg_match_all('/./us', $name, $nameMatches);
$officeNameMatches = array();
$officeNameLength = preg_match_all('/./us', $officeName, $officeNameMatches);

if ($name === '' || $nameLength === false || $nameLength > 160 || $place === '' || $timezone === '' || $officeNameLength === false || $officeNameLength > 160 || strlen($officeContact) > 40) {
	redirectProfileLocation('error');
}

$conn = null;
try {
	$conn = dbConnect($conn);
	if (!($conn instanceof mysqli)) {
		error_log('save_user_place.php: database connection is unavailable.');
		redirectProfileLocation('error');
	}

	$placeStatement = $conn->prepare('SELECT GMT_offset, GMT_offset_dsl FROM world_cities WHERE Place = ? AND TZ = ? LIMIT 1');
	$placeStatement->bind_param('ss', $place, $timezone);
	$placeStatement->execute();
	$placeStatement->bind_result($gmtOffset, $gmtOffsetDsl);

	if (!$placeStatement->fetch()) {
		$placeStatement->close();
		dbDisconnect($conn);
		redirectProfileLocation('error');
	}
	$placeStatement->close();

	$updateStatement = $conn->prepare('UPDATE user SET name = ?, place = ?, tz = ?, GMT_offset = ?, GMT_offset_dsl = ?, office_name = ?, office_contact = ? WHERE phone = ?');
	$updateStatement->bind_param('ssssssss', $name, $place, $timezone, $gmtOffset, $gmtOffsetDsl, $officeName, $officeContact, $phone);
	$updateStatement->execute();
	$updateStatement->close();
	dbDisconnect($conn);

	$_SESSION['userinfo']['place'] = $place;
	$_SESSION['userinfo']['tz'] = $timezone;
	$_SESSION['userinfo']['name'] = $name;
	$_SESSION['userinfo']['GMT_offset'] = $gmtOffset;
	$_SESSION['userinfo']['GMT_offset_dsl'] = $gmtOffsetDsl;
	$_SESSION['userinfo']['office_name'] = $officeName;
	$_SESSION['userinfo']['office_contact'] = $officeContact;
	setcookie('place', $place . '|' . $timezone, time() + (86400 * 30), '/');
	redirectProfileLocation('saved');
} catch (mysqli_sql_exception $exception) {
	if ($conn instanceof mysqli) {
		dbDisconnect($conn);
	}
	error_log('save_user_place.php: unable to save user place/timezone: ' . $exception->getMessage());
	redirectProfileLocation('error');
}
