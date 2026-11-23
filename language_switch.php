<?php
function renderLanguageSwitch($currentLanguage, $optionClass = '')
{
	static $languageScriptRendered = false;

	$currentLanguage = strtolower((string)$currentLanguage) === 'english' ? 'english' : 'tamil';
	$languageLabel = ($currentLanguage === 'english') ? 'Choose language' : 'மொழியைத் தேர்ந்தெடுக்கவும்';
	$optionClass = trim((string)$optionClass);

	if (!$languageScriptRendered) {
		echo "<script>
if (typeof window.setLanguage !== 'function') {
	window.setLanguage = function(language) {
		if (language !== 'tamil' && language !== 'english') return;
		document.cookie = 'language=' + language + ';path=/;max-age=31536000';
		if (typeof window.fetch !== 'function') {
			window.location.reload();
			return;
		}
		window.fetch('set_user_lang.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: 'lang=' + encodeURIComponent(language)
		}).then(function(response) {
			if (!response.ok) throw new Error('Language update failed with status ' + response.status);
			return response.json();
		}).then(function() {
			window.location.reload();
		}).catch(function(error) {
			console.error('Unable to save language preference:', error);
			window.location.reload();
		});
	};
}
</script>";
		$languageScriptRendered = true;
	}

	echo "<div class='language-switch' role='group' aria-label='" . htmlspecialchars($languageLabel, ENT_QUOTES, 'UTF-8') . "'>";
	foreach (array('tamil' => 'தமிழ்', 'english' => 'EN') as $language => $label) {
		$isActive = ($currentLanguage === $language);
		$buttonClass = 'language-option'
			. ($optionClass !== '' ? ' ' . $optionClass : '')
			. ($isActive ? ' is-active' : '');
		echo "<button class='" . htmlspecialchars($buttonClass, ENT_QUOTES, 'UTF-8') . "' type='button' onclick=\"window.setLanguage('$language')\" aria-pressed='" . ($isActive ? 'true' : 'false') . "'" . ($isActive ? ' disabled' : '') . ">$label</button>";
	}
	echo "</div>";
}
