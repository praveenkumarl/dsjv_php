<?php
require_once __DIR__ . '/client_theme_config.php';

function clientThemeAdminState($formAction)
{
	$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	$userType = isset($_SESSION['userinfo']['user_type']) && is_string($_SESSION['userinfo']['user_type'])
		? strtoupper($_SESSION['userinfo']['user_type'])
		: '';
	if ($userType !== 'ADMIN' && $userType !== 'SUPER') {
		http_response_code(403);
		exit('You are not authorized to manage the client theme.');
	}

	if (!isset($_SESSION['client_theme_csrf'])) {
		$_SESSION['client_theme_csrf'] = bin2hex(random_bytes(32));
	}

	$state = array(
		'presets' => clientThemePresets(),
		'config' => clientThemeDefaultConfig(),
		'active_config' => clientThemeDefaultConfig(),
		'active_theme_label' => 'Rose + Purple',
		'active_theme_source' => 'Built-in preset',
		'active_theme_loaded' => false,
		'error' => '',
		'saved' => isset($_GET['theme_saved']) && $_GET['theme_saved'] === '1',
		'custom_saved' => isset($_GET['custom_saved']) && $_GET['custom_saved'] === '1',
		'draft' => false,
		'custom_name' => isset($_POST['custom_theme_name']) && is_string($_POST['custom_theme_name']) ? $_POST['custom_theme_name'] : '',
		'custom_themes' => array(),
		'active_custom_theme' => '',
		'csrf' => $_SESSION['client_theme_csrf'],
		'form_action' => $formAction,
		'back_url' => $formAction === 'admin.php?section=theme' ? 'admin.php' : 'maintenance.php'
	);
	$hasSubmittedConfig = false;

	if ($requestMethod === 'POST' && (isset($_POST['client_theme_submit']) || isset($_GET['client_theme_action']))) {
		$postedToken = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
		if (!is_string($postedToken) || !hash_equals($state['csrf'], $postedToken)) {
			http_response_code(403);
			exit('The request could not be verified. Reload the page and try again.');
		}

		$submittedAction = isset($_GET['client_theme_action']) && is_string($_GET['client_theme_action'])
			? $_GET['client_theme_action']
			: '';
		if ($submittedAction === '' && isset($_POST['client_theme_action']) && is_string($_POST['client_theme_action'])) {
			$submittedAction = $_POST['client_theme_action'];
		}
		if ($submittedAction === '') {
			if (isset($_POST['client_theme_save_custom'])) {
				$submittedAction = 'save_custom';
			} elseif (isset($_POST['client_theme_apply_custom'])) {
				$submittedAction = 'apply_custom';
			} elseif (isset($_POST['client_theme_apply_preset'])) {
				$submittedAction = 'apply_preset';
			}
		}

		try {
			$conn = clientThemeConnect();
			clientThemeEnsureTable($conn);
			if ($submittedAction === 'save_custom') {
				$presetKey = isset($_POST['preset']) && is_string($_POST['preset']) ? $_POST['preset'] : '';
				$colors = isset($_POST['colors']) && is_array($_POST['colors']) ? $_POST['colors'] : array();
				$state['config'] = clientThemeNormalizeConfig($presetKey, $colors);
				$hasSubmittedConfig = true;
				$conn->begin_transaction();
				clientThemeSaveCustomTheme($conn, $state['custom_name'], $state['config']);
				clientThemeSaveConfig($conn, $state['config']);
				$conn->commit();
				$conn->close();
				header('Location: ' . $formAction . '&custom_saved=1', true, 303);
				exit;
			}

			if ($submittedAction === 'apply_custom') {
				$selectedCustomTheme = isset($_POST['saved_custom_theme']) && is_string($_POST['saved_custom_theme'])
					? $_POST['saved_custom_theme']
					: '';
				if ($selectedCustomTheme === '') {
					throw new InvalidArgumentException('Choose a saved custom theme to apply.');
				}
				$customThemeError = null;
				$customThemes = clientThemeLoadCustomThemes($conn, $customThemeError);
				if (!array_key_exists($selectedCustomTheme, $customThemes)) {
					throw new InvalidArgumentException('Choose a saved custom theme to apply.');
				}
				$state['config'] = $customThemes[$selectedCustomTheme];
				clientThemeAssertAccessibleConfig($state['config']);
			} elseif ($submittedAction === 'apply_preset') {
				$queryPreset = isset($_GET['preset']) && is_string($_GET['preset']) ? $_GET['preset'] : '';
				$postedPreset = isset($_POST['preset']) && is_string($_POST['preset']) ? $_POST['preset'] : '';
				$postedColors = isset($_POST['colors']) && is_array($_POST['colors']) ? $_POST['colors'] : array();
				$presetKey = clientThemeResolvePresetKey($queryPreset, $postedPreset, $postedColors);
				$state['config'] = clientThemeNormalizeConfig($presetKey, array());
			} else {
				throw new InvalidArgumentException('No theme action was received. Click Save as custom theme, Apply built-in preset, or Apply selected custom theme.');
			}
			$hasSubmittedConfig = true;
			$conn->begin_transaction();
			clientThemeSaveConfig($conn, $state['config']);
			$conn->commit();
			$conn->close();

			header('Location: ' . $formAction . '&theme_saved=1', true, 303);
			exit;
		} catch (InvalidArgumentException $exception) {
			$state['error'] = $exception->getMessage();
			if (isset($conn) && $conn instanceof mysqli) {
				$conn->rollback();
				$conn->close();
			}
		} catch (mysqli_sql_exception $exception) {
			error_log('Client theme save failed with MySQL error ' . (int)$exception->getCode() . '.');
			$state['error'] = 'Theme settings could not be saved. Check the database connection and try again.';
			if (isset($conn) && $conn instanceof mysqli) {
				$conn->rollback();
				$conn->close();
			}
		} catch (RuntimeException $exception) {
			$state['error'] = 'Theme settings could not be saved. Please try again.';
			if (isset($conn) && $conn instanceof mysqli) {
				$conn->rollback();
				$conn->close();
			}
		}
	}

	try {
		$conn = clientThemeConnect();
		clientThemeEnsureTable($conn);
		$loadError = null;
		$storedConfig = clientThemeLoadConfig($conn, $loadError);
		$customThemeError = null;
		$state['custom_themes'] = clientThemeLoadCustomThemes($conn, $customThemeError);
		$conn->close();
		if ($loadError !== null) {
			$state['error'] = $loadError;
		} elseif ($customThemeError !== null && $state['error'] === '') {
			$state['error'] = $customThemeError;
		}
		$state['active_config'] = $storedConfig;
		$state['active_theme_loaded'] = true;
		$activeMatchesPreset = false;
		foreach ($state['presets'] as $presetKey => $preset) {
			if ($storedConfig === clientThemeNormalizeConfig($presetKey, array())) {
				$state['active_theme_label'] = $preset['label'];
				$state['active_theme_source'] = 'Built-in preset';
				$activeMatchesPreset = true;
				break;
			}
		}
		if (!$activeMatchesPreset) {
			$state['active_theme_label'] = 'Custom palette (unnamed)';
			$state['active_theme_source'] = 'Unlabelled custom palette';
			foreach ($state['custom_themes'] as $name => $customConfig) {
				if ($storedConfig === $customConfig) {
					$state['active_theme_label'] = $name;
					$state['active_theme_source'] = 'Saved custom theme';
					$basePreset = clientThemeNormalizeConfig($customConfig['preset'], array());
					if ($customConfig !== $basePreset) {
						$state['active_custom_theme'] = $name;
					}
					break;
				}
			}
		}
		if ($requestMethod !== 'POST' || !$hasSubmittedConfig) {
			$state['config'] = $storedConfig;
		}
	} catch (mysqli_sql_exception $exception) {
		error_log('Client theme settings page failed to load (MySQL error ' . (int)$exception->getCode() . ').');
		if ($state['error'] === '') {
			$state['error'] = 'Theme settings could not be loaded. Check the database connection and try again.';
		}
	}

	$state['draft'] = $requestMethod === 'POST' && $hasSubmittedConfig;
	return $state;
}
