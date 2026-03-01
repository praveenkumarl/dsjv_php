<?php
/**
 * Language Translation Helper Functions
 * 
 * This file provides translation functionality for the application.
 * It loads the appropriate language file and provides a translation function.
 */

// Global variable to store translations
$GLOBALS['translations'] = [];
$GLOBALS['current_language'] = 'tamil';

/**
 * Initialize the language system
 * 
 * @param string $language The language code ('tamil' or 'english')
 */
function init_language($language = 'tamil') {
    $GLOBALS['current_language'] = $language;
    
    // Determine which language file to load
    $lang_file = __DIR__ . '/' . ($language === 'english' ? 'en.php' : 'ta.php');
    
    // Load the language file
    if (file_exists($lang_file)) {
        $GLOBALS['translations'] = include $lang_file;
    } else {
        // Fallback to Tamil if file not found
        $GLOBALS['translations'] = include __DIR__ . '/ta.php';
    }
}

/**
 * Translate a key to the current language
 * 
 * @param string $key The translation key
 * @param string $default Optional default value if key not found
 * @return string The translated string
 */
function __($key, $default = null) {
    // Keys that should ALWAYS be in Tamil (never translated to English)
    // These are used in pages that don't have language support yet
    $tamil_only_keys = [
        'naksh_tharai',
        'rasi_degree',
        // Nakshatra abbreviations - always Tamil
        'aswini_abbr',
        'bharani_abbr',
        'krittikai_abbr',
        'rohini_abbr',
        'mrigashirsha_abbr',
        'ardra_abbr',
        'punarvasu_abbr',
        'pushya_abbr',
        'ashlesha_abbr',
        'magha_abbr',
        'purva_phalguni_abbr',
        'uttara_phalguni_abbr',
        'hasta_abbr',
        'chitra_abbr',
        'swati_abbr',
        'vishakha_abbr',
        'anuradha_abbr',
        'jyeshtha_abbr',
        'mula_abbr',
        'purva_ashadha_abbr',
        'uttara_ashadha_abbr',
        'shravana_abbr',
        'dhanishta_abbr',
        'shatabhisha_abbr',
        'purva_bhadrapada_abbr',
        'uttara_bhadrapada_abbr',
        'revati_abbr'
    ];
    
    // If this is a Tamil-only key, always return the Tamil translation
    if (in_array($key, $tamil_only_keys)) {
        $tamil_translations = include(__DIR__ . '/ta.php');
        if (isset($tamil_translations[$key])) {
            return $tamil_translations[$key];
        }
    }
    
    // If translations not initialized, return the key (fail gracefully)
    if (!isset($GLOBALS['translations']) || empty($GLOBALS['translations'])) {
        return $default !== null ? $default : $key;
    }
    
    if (isset($GLOBALS['translations'][$key])) {
        return $GLOBALS['translations'][$key];
    }
    
    // Return default if provided, otherwise return the key itself
    return $default !== null ? $default : $key;
}

/**
 * Translate a key and echo it
 * 
 * @param string $key The translation key
 * @param string $default Optional default value if key not found
 */
function _e($key, $default = null) {
    echo __($key, $default);
}

/**
 * Get current language
 * 
 * @return string Current language code
 */
function get_current_language() {
    return $GLOBALS['current_language'];
}

/**
 * Check if current language is English
 * 
 * @return bool True if English, false otherwise
 */
function is_english() {
    return $GLOBALS['current_language'] === 'english';
}

/**
 * Check if current language is Tamil
 * 
 * @return bool True if Tamil, false otherwise
 */
function is_tamil() {
    return $GLOBALS['current_language'] === 'tamil';
}

/**
 * Translate text based on a mapping array
 * This is useful for dynamic content that comes from databases or calculations
 * 
 * @param string $text The text to translate
 * @param array $mapping Optional custom mapping array
 * @return string The translated text
 */
function translate_text($text, $mapping = null) {
    // If current language is Tamil, return as is
    if (is_tamil()) {
        return $text;
    }

    // Load Tamil and English translation arrays
    $tamil_translations = include __DIR__ . '/ta.php';
    $english_translations = include __DIR__ . '/en.php';

    // If English translations contain the exact Tamil string as a key, use it (handles short abbreviations)
    if (isset($english_translations[$text])) {
        return $english_translations[$text];
    }

    // Try exact match in Tamil translations and return corresponding English
    $key = array_search($text, $tamil_translations, true);
    if ($key !== false && isset($english_translations[$key])) {
        return $english_translations[$key];
    }

    // Try partial replacements: replace Tamil substrings with their English equivalents
    foreach ($tamil_translations as $k => $tamil_value) {
        if ($tamil_value === null || $tamil_value === '') continue;
        if (strpos(strip_tags($text), $tamil_value) !== false && isset($english_translations[$k])) {
            $eng = $english_translations[$k];
            // Preserve common HTML wrappers (retro/lagna) by replacing inner text only
            $text = str_replace('<span class="retro">'.$tamil_value.'</span>', '<span class="retro">'.$eng.'</span>', $text);
            $text = str_replace('<span class="lagna">'.$tamil_value.'</span>', '<span class="lagna">'.$eng.'</span>', $text);
            // Also handle cases with extra spaces
            $text = str_replace(' '.$tamil_value.' ', ' '.$eng.' ', $text);
            $text = str_replace(' '.$tamil_value, ' '.$eng, $text);
            $text = str_replace($tamil_value.' ', $eng.' ', $text);
            // Final fallback: plain replacement (may remove wrappers)
            $text = str_replace($tamil_value, $eng, $text);
        }
    }

    // Fallback: return original text if no replacements were made
    return $text;
}

/**
 * Get translation array for JavaScript
 * Returns JSON encoded translation array
 * 
 * @return string JSON encoded translations
 */
function get_translations_json() {
    return json_encode($GLOBALS['translations'], JSON_UNESCAPED_UNICODE);
}

/**
 * Get both Tamil and English translations for JavaScript
 * Returns JSON object with ta_to_en mapping
 * 
 * @return string JSON encoded translation mapping
 */
function get_translation_mapping_json() {
    $tamil_translations = include __DIR__ . '/ta.php';
    $english_translations = include __DIR__ . '/en.php';
    
    $mapping = [];
    foreach ($tamil_translations as $key => $tamil_value) {
        if (isset($english_translations[$key])) {
            $mapping[$tamil_value] = $english_translations[$key];
        }
    }

    // Keep paksha labels bracketed in dynamic strings like Thithi
    $mapping['(சுக்)'] = '(wax)';
    $mapping['(கிரு)'] = '(wan)';
    
    return json_encode($mapping, JSON_UNESCAPED_UNICODE);
}

/**
 * Map Tamil UI tokens to English display-only values (e.g., short planet abbreviations)
 * This does not modify stored values; it's only meant for preparing strings for rendering
 */
function map_display_text($text) {
    // Only map when UI language is English
    if (!is_english()) return $text;

    $english_translations = include __DIR__ . '/en.php';

    // Iterate keys in en.php — keys may be Tamil tokens like 'சூரி' => 'Su'
    foreach ($english_translations as $tamil_key => $eng_val) {
        if (empty($tamil_key) || $tamil_key === $eng_val) continue;

        // 1) Handle sub-planet tokens prefixed with '#': replace '#KEY' with '#Eng'
        if ($eng_val !== '') {
            // Replace inside span inner text
            $text = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/u', function($m) use ($tamil_key, $eng_val) {
                $attrs = $m[1];
                $inner = $m[2];
                if (strpos($inner, '#'.$tamil_key) !== false) {
                    $inner = str_replace('#'.$tamil_key, '#'.$eng_val, $inner);
                }
                return '<span'.$attrs.'>'.$inner.'</span>';
            }, $text);

            // Replace outside spans: look for '#KEY' preceded by boundary
            $text = preg_replace('/(?<=^|[\s>])#'.preg_quote($tamil_key, '/').'(?=$|[\s<\)\.,;:\-])/u', '#'.$eng_val, $text);
            // Fallback direct replace
            $text = str_replace('#'.$tamil_key, '#'.$eng_val, $text);
        }

        // 2) Replace occurrences inside spans (non-#)
        $text = preg_replace_callback('/<span\b([^>]*)>(.*?)<\/span>/u', function($m) use ($tamil_key, $eng_val) {
            $attrs = $m[1];
            $inner = $m[2];
            // If inner contains '#', we've already handled those; replace remaining plain occurrences
            if (strpos($inner, $tamil_key) !== false) {
                // avoid changing already replaced '#KEY'
                $inner = str_replace('#'.$tamil_key, '#'.$eng_val, $inner);
                $inner = str_replace($tamil_key, $eng_val, $inner);
            }
            return '<span'.$attrs.'>'.$inner.'</span>';
        }, $text);

        // 3) Replace standalone occurrences outside spans (not prefixed by '#')
        if ($eng_val !== '') {
            // Match pattern with a preceding boundary (start or whitespace or >) and trailing boundary
            $pattern = '/(^|[\s>])'.preg_quote($tamil_key, '/').'(?=$|[\s<\)\.,;:\-])/u';
            $text = preg_replace_callback($pattern, function($m) use ($eng_val) {
                return $m[1].$eng_val;
            }, $text);
        }
    }

    return $text;
}
