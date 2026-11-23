<?php
$themePresetJson = json_encode($themePresets, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$themeCustomJson = json_encode($themeCustomThemes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$themeContrastPairsJson = json_encode(clientThemeContrastPairs(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$themePreviewStyle = '';
foreach ($themeConfig['colors'] as $name => $color) {
	$themePreviewStyle .= '--preview-' . $name . ':' . clientThemeEscape($color) . ';';
}
$themePreviewStyle .= '--preview-button-text:' . clientThemeEscape(clientThemeContrastTextColor(array(
	$themeConfig['colors']['primary'],
	$themeConfig['colors']['secondary']
))) . ';';
$themeActiveSwatches = array(
	'page' => 'Page',
	'surface' => 'Cards',
	'primary' => 'Buttons',
	'accent' => 'Accent',
	'chart_box' => 'Chart'
);
$themeColorLabels = array(
	'page' => 'Page background',
	'surface' => 'Cards and panels',
	'surface_alt' => 'Fields and secondary surfaces',
	'primary' => 'Primary buttons and navigation',
	'secondary' => 'Secondary accent',
	'accent' => 'Highlights and borders',
	'text' => 'Main text',
	'muted' => 'Secondary text',
	'on_accent' => 'Text on accent labels',
	'chart_box' => 'Chart box fill',
	'chart_text' => 'Text inside chart boxes'
);
?>
<section class="client-theme-settings">
	<h1>Client theme</h1>
	<p>Built-in presets are fixed. To change colors, save them as a named custom theme. Apply a preset to restore its original colors. Keep each text/background pair at 4.5:1 contrast or higher. If a pair fails, use darker text on light surfaces or lighter text on dark surfaces; check chart text against the chart fill too.</p>
	<div class="theme-currently-applied" style="--active-surface:<?php echo clientThemeEscape($themeActiveConfig['colors']['surface']); ?>;--active-text:<?php echo clientThemeEscape($themeActiveConfig['colors']['text']); ?>;--active-muted:<?php echo clientThemeEscape($themeActiveConfig['colors']['muted']); ?>;--active-accent:<?php echo clientThemeEscape($themeActiveConfig['colors']['accent']); ?>">
		<?php if ($themeActiveLoaded) { ?>
		<div class="theme-currently-applied-heading">
			<div><small>Currently applied site-wide</small><strong><?php echo clientThemeEscape($themeActiveLabel); ?></strong><span><?php echo clientThemeEscape($themeActiveSource); ?></span></div>
		</div>
		<div class="theme-active-swatches">
			<?php foreach ($themeActiveSwatches as $key => $label) { ?>
				<div><span class="theme-active-swatch" style="--swatch-color:<?php echo clientThemeEscape($themeActiveConfig['colors'][$key]); ?>"></span><small><?php echo clientThemeEscape($label); ?></small><code><?php echo clientThemeEscape($themeActiveConfig['colors'][$key]); ?></code></div>
			<?php } ?>
		</div>
		<?php } else { ?>
			<div class="theme-currently-applied-heading"><div><small>Currently applied site-wide</small><strong>Unable to load theme status</strong><span>Check the database connection to see the active palette.</span></div></div>
		<?php } ?>
	</div>
	<?php if ($themeSaved) { ?><div class="theme-notice" role="status">Selected theme applied site-wide.</div><?php } ?>
	<?php if ($themeCustomSaved) { ?><div class="theme-notice" role="status">Custom theme saved and applied. Built-in presets are unchanged.</div><?php } ?>
	<?php if ($themeErrorMessage !== '') { ?><div class="theme-notice theme-notice-error" role="alert"><?php echo clientThemeEscape($themeErrorMessage); ?></div><?php } ?>

	<div class="theme-live-preview" id="theme-live-preview" style="<?php echo $themePreviewStyle; ?>">
		<div class="theme-live-preview-heading"><div><strong>Live preview</strong><small>Updates as you edit. Click an apply or save button to make it site-wide.</small></div><span id="theme-preview-status"><?php echo $themeSaved || $themeCustomSaved ? 'Applied' : ($themeDraft ? 'Draft changes' : 'Current palette'); ?></span></div>
		<div class="theme-preview-content">
			<div class="theme-preview-sample-card">
				<small>Sample card</small>
				<strong>Today's Panchang</strong>
				<p>Secondary text preview</p>
				<div><button type="button" tabindex="-1">Primary button</button><span>Accent label</span></div>
			</div>
			<div class="theme-preview-chart"><small>Chart box</small><strong>12</strong><span>Chart text preview</span></div>
		</div>
	</div>

	<form id="client-theme-form" method="post" action="<?php echo clientThemeEscape($themeFormAction); ?>">
		<input type="hidden" name="client_theme_submit" value="1">
		<input type="hidden" id="client-theme-action" name="client_theme_action" value="">
		<input type="hidden" name="csrf_token" value="<?php echo clientThemeEscape($themeCsrf); ?>">
		<div class="theme-field theme-preset-field">
			<label for="saved-custom-theme">Saved custom themes</label>
			<select id="saved-custom-theme" name="saved_custom_theme">
				<option value="">Choose a custom theme to load</option>
				<?php foreach ($themeCustomThemes as $name => $config) { ?>
					<option value="<?php echo clientThemeEscape($name); ?>" <?php echo $themeActiveCustomTheme === $name ? 'selected' : ''; ?>><?php echo clientThemeEscape($name); ?></option>
				<?php } ?>
			</select>
			<small>Selecting a saved theme loads its colors. Edited colors are applied only when saved under a custom name.</small>
		</div>
		<div class="theme-field theme-preset-field">
			<label for="theme-preset">Base palette</label>
			<select id="theme-preset" name="preset">
				<?php foreach ($themePresets as $key => $preset) { ?>
					<option value="<?php echo clientThemeEscape($key); ?>" <?php echo $themeConfig['preset'] === $key ? 'selected' : ''; ?>><?php echo clientThemeEscape($preset['label']); ?></option>
				<?php } ?>
			</select>
			<small id="theme-selected-preset">Selected: <?php echo clientThemeEscape($themePresets[$themeConfig['preset']]['label']); ?></small>
		</div>

		<div class="theme-color-grid">
			<?php foreach ($themeColorLabels as $key => $label) { ?>
				<div class="theme-field">
					<label for="theme-color-<?php echo clientThemeEscape($key); ?>"><?php echo clientThemeEscape($label); ?></label>
					<div class="theme-color-field">
						<input type="color" id="theme-color-<?php echo clientThemeEscape($key); ?>" name="colors[<?php echo clientThemeEscape($key); ?>]" value="<?php echo clientThemeEscape($themeConfig['colors'][$key]); ?>">
						<span class="theme-color-value"><?php echo clientThemeEscape($themeConfig['colors'][$key]); ?></span>
					</div>
				</div>
			<?php } ?>
		</div>
		<div id="theme-contrast-status" class="theme-contrast-status" role="status" aria-live="polite"></div>

		<div class="theme-actions">
			<div class="theme-field theme-custom-name">
				<label for="custom-theme-name">Custom theme name</label>
				<input id="custom-theme-name" name="custom_theme_name" type="text" maxlength="80" value="<?php echo clientThemeEscape($themeCustomName); ?>" placeholder="e.g. Client brand palette">
				<small>Saving with an existing name updates that custom theme.</small>
			</div>
			<button type="submit" name="client_theme_save_custom" value="1" formaction="<?php echo clientThemeEscape($themeFormAction . '&client_theme_action=save_custom'); ?>" data-client-theme-action="save_custom">Save as custom theme</button>
			<button type="submit" name="client_theme_apply_preset" value="1" formaction="<?php echo clientThemeEscape($themeFormAction . '&client_theme_action=apply_preset&preset=' . rawurlencode($themeConfig['preset'])); ?>" data-client-theme-action="apply_preset">Apply built-in preset</button>
			<button type="submit" name="client_theme_apply_custom" value="1" formaction="<?php echo clientThemeEscape($themeFormAction . '&client_theme_action=apply_custom'); ?>" data-client-theme-action="apply_custom" <?php echo $themeActiveCustomTheme === '' ? 'disabled' : ''; ?>>Apply selected custom theme</button>
			<a href="<?php echo clientThemeEscape($themeBackUrl); ?>">Back</a>
		</div>
	</form>
</section>
<script>
	(function () {
		var presets = <?php echo $themePresetJson ? $themePresetJson : '{}'; ?>;
		var customThemes = <?php echo $themeCustomJson ? $themeCustomJson : '{}'; ?>;
		var contrastPairs = <?php echo $themeContrastPairsJson ? $themeContrastPairsJson : '[]'; ?>;
		var selector = document.getElementById('theme-preset');
		var customSelector = document.getElementById('saved-custom-theme');
		var customNameInput = document.getElementById('custom-theme-name');
		var form = document.getElementById('client-theme-form');
		var actionInput = document.getElementById('client-theme-action');
		var colorInputs = document.querySelectorAll('.client-theme-settings input[type="color"]');
		var contrastStatus = document.getElementById('theme-contrast-status');
		var saveCustomButton = document.querySelector('[name="client_theme_save_custom"]');
		var applyCustomButton = document.querySelector('[name="client_theme_apply_custom"]');
		var applyPresetButton = document.querySelector('[data-client-theme-action="apply_preset"]');
		var selectedPresetLabel = document.getElementById('theme-selected-preset');
		var preview = document.getElementById('theme-live-preview');
		var previewStatus = document.getElementById('theme-preview-status');
		var editor = document.querySelector('.client-theme-settings');
		var presetActionBase = <?php echo json_encode($themeFormAction . '&client_theme_action=apply_preset&preset=', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
		var activePresetKey = <?php echo json_encode($themeConfig['preset'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
		function updatePresetAction() {
			var selectedOption = selector.options[selector.selectedIndex];
			var presetKey = selectedOption && presets[selectedOption.value] ? selectedOption.value : activePresetKey;
			applyPresetButton.setAttribute('formaction', presetActionBase + encodeURIComponent(presetKey));
			selectedPresetLabel.textContent = 'Selected: ' + presets[presetKey].label;
		}
		form.querySelectorAll('[data-client-theme-action]').forEach(function (button) {
			button.addEventListener('click', function () {
				actionInput.value = button.getAttribute('data-client-theme-action');
				if (button === applyPresetButton) updatePresetAction();
			});
		});
		form.addEventListener('submit', function (event) {
			var submitter = event.submitter;
			if (submitter && submitter.getAttribute('data-client-theme-action')) {
				actionInput.value = submitter.getAttribute('data-client-theme-action');
				if (submitter === applyPresetButton) updatePresetAction();
			}
		});
		function applyPalette(palette) {
			if (!palette) return;
			selector.value = palette.preset;
			updatePresetAction();
			colorInputs.forEach(function (input) {
				var match = input.name.match(/^colors\[([a-z_]+)\]$/);
				if (match && palette.colors[match[1]]) {
					input.value = palette.colors[match[1]];
					input.nextElementSibling.textContent = input.value;
				}
			});
		}
		function updatePresetColors() {
			applyPalette(presets[selector.value]);
		}
		function colorLuminance(hex) {
			var channels = [1, 3, 5].map(function (offset) {
				var value = parseInt(hex.slice(offset, offset + 2), 16) / 255;
				return value <= 0.04045 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
			});
			return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
		}
		function contrastRatio(first, second) {
			var values = [colorLuminance(first), colorLuminance(second)].sort(function (a, b) { return b - a; });
			return (values[0] + 0.05) / (values[1] + 0.05);
		}
		function buttonTextColor(primary, secondary) {
			var whiteContrast = Math.min(contrastRatio('#ffffff', primary), contrastRatio('#ffffff', secondary));
			var blackContrast = Math.min(contrastRatio('#000000', primary), contrastRatio('#000000', secondary));
			return whiteContrast >= blackContrast ? '#ffffff' : '#000000';
		}
		function updateContrastStatus(markDraft) {
			var colors = {};
			colorInputs.forEach(function (input) {
				var match = input.name.match(/^colors\[([a-z_]+)\]$/);
				if (match) colors[match[1]] = input.value;
			});
			if (markDraft) previewStatus.textContent = 'Draft changes';
			Object.keys(colors).forEach(function (key) {
				preview.style.setProperty('--preview-' + key, colors[key]);
			});
			editor.style.setProperty('--theme-surface-panel-solid', colors.surface);
			editor.style.setProperty('--theme-surface-panel', colors.surface);
			editor.style.setProperty('--theme-surface-field', colors.surface_alt);
			editor.style.setProperty('--theme-text', colors.text);
			editor.style.setProperty('--theme-text-heading', colors.text);
			editor.style.setProperty('--theme-text-secondary', colors.muted);
			editor.style.setProperty('--theme-text-muted', colors.muted);
			editor.style.setProperty('--theme-border-strong', colors.accent);
			editor.style.setProperty('--theme-border-input', colors.accent);
			editor.style.setProperty('--theme-border', colors.accent);
			editor.style.setProperty('--theme-border-accent', colors.accent);
			editor.style.setProperty('--theme-button-gradient', 'linear-gradient(135deg,' + colors.primary + ',' + colors.secondary + ')');
			var buttonText = buttonTextColor(colors.primary, colors.secondary);
			preview.style.setProperty('--preview-button-text', buttonText);
			editor.style.setProperty('--theme-button-text', buttonText);
			editor.style.setProperty('--button-text-color', buttonText);
			editor.style.setProperty('--theme-accent-soft', colors.surface_alt);
			editor.style.setProperty('--theme-text-error', colors.accent);
			var failed = contrastPairs.filter(function (pair) {
				return contrastRatio(colors[pair.foreground], colors[pair.background]) < 4.5;
			}).map(function (pair) {
				return pair.label + ' (' + contrastRatio(colors[pair.foreground], colors[pair.background]).toFixed(2) + ':1)';
			});
			saveCustomButton.disabled = failed.length > 0;
			applyCustomButton.disabled = customSelector.value === '';
			contrastStatus.classList.toggle('is-warning', failed.length > 0);
			contrastStatus.textContent = failed.length
				? 'Low contrast (minimum 4.5:1): ' + failed.join('; ') + '. Adjust the listed colors before saving or applying a custom theme.'
				: 'Contrast check passed: all listed text/background pairs meet 4.5:1.';
		}
		colorInputs.forEach(function (input) {
			input.addEventListener('input', function () {
				input.nextElementSibling.textContent = input.value;
				updateContrastStatus(true);
			});
		});
		selector.addEventListener('change', function () {
			customSelector.value = '';
			updatePresetColors();
			updateContrastStatus(true);
		});
		customSelector.addEventListener('change', function () {
			var palette = customThemes[customSelector.value];
			if (palette) {
				applyPalette(palette);
				if (!customNameInput.value) {
					customNameInput.value = customSelector.value;
				}
			}
			updateContrastStatus(true);
		});
		updatePresetAction();
		updateContrastStatus();
	}());
</script>
