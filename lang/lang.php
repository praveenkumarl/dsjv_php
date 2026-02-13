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
        'thrisoolam_abbr',
        'indhu_abbr',
        'graha_saaram',
        'graha',
        'naksh_pad',
        'tharai',
        'sookshma_tharai',
        'bhagai',
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
    if ($mapping === null) {
        $mapping = $GLOBALS['translations'];
    }
    
    // If current language is Tamil, return as is
    if (is_tamil()) {
        return $text;
    }
    
    // Try to find exact match
    $key = array_search($text, $mapping);
    if ($key !== false) {
        return __($key);
    }
    
    // Try partial match for complex strings
    foreach ($mapping as $tamil_text) {
        if (strpos($text, $tamil_text) !== false) {
            $key = array_search($tamil_text, $mapping);
            if ($key !== false) {
                $text = str_replace($tamil_text, __($key), $text);
            }
        }
    }
    
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
    
    return json_encode($mapping, JSON_UNESCAPED_UNICODE);
}
