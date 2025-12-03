<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * Blackbox UI - Internationalization (i18n) System
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * Provides multilingual support for the Blackbox EYE platform.
 * Supports Danish (da) and English (en) with session-based language switching.
 *
 * Features:
 * - JSON-based translation files (lang/da.json, lang/en.json)
 * - Session-based language persistence
 * - Browser language detection fallback
 * - Fast caching mechanism for performance
 * - Fallback to Danish if translation missing
 *
 * Usage:
 *   include 'includes/i18n.php';
 *   echo t('header.menu.about');        // Returns translated text
 *   echo bbx_get_text('pricing.mvp.basis.title');  // Alternative syntax
 *
 * Language Switching:
 *   $_SESSION['lang'] = 'en';  // Switch to English
 *   $_SESSION['lang'] = 'da';  // Switch to Danish
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ═══════════════════════════════════════════════════════════════════════════════
// LANGUAGE DETECTION & INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Detect user's preferred language
 * Priority: 1) Session, 2) Browser Accept-Language, 3) Default (Danish)
 */
function bbx_detect_language()
{
    // 1. Check session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['da', 'en'])) {
        return $_SESSION['lang'];
    }

    // 2. Check browser language
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $browser_lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        if (in_array($browser_lang, ['da', 'en'])) {
            $_SESSION['lang'] = $browser_lang;
            return $browser_lang;
        }
    }

    // 3. Default to Danish
    $_SESSION['lang'] = 'da';
    return 'da';
}

// Initialize language
$GLOBALS['bbx_current_lang'] = bbx_detect_language();
$GLOBALS['bbx_translations'] = null;

// ═══════════════════════════════════════════════════════════════════════════════
// TRANSLATION LOADING
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Load translation file for current language
 * Implements caching to avoid repeated file reads
 */
function bbx_load_translations($lang = null)
{
    if ($lang === null) {
        $lang = $GLOBALS['bbx_current_lang'];
    }

    // Return cached translations if already loaded
    if ($GLOBALS['bbx_translations'] !== null && isset($GLOBALS['bbx_translations_lang']) && $GLOBALS['bbx_translations_lang'] === $lang) {
        return $GLOBALS['bbx_translations'];
    }

    // Build path to language file
    $lang_file = __DIR__ . '/../lang/' . $lang . '.json';

    // Check if file exists
    if (!file_exists($lang_file)) {
        // Fallback to Danish if file not found
        $lang_file = __DIR__ . '/../lang/da.json';
        if (!file_exists($lang_file)) {
            error_log("BBX i18n ERROR: Translation file not found: " . $lang_file);
            return [];
        }
    }

    // Load and decode JSON
    $json_content = file_get_contents($lang_file);
    $translations = json_decode($json_content, true);

    if ($translations === null) {
        error_log("BBX i18n ERROR: Invalid JSON in file: " . $lang_file);
        return [];
    }

    // Cache translations
    $GLOBALS['bbx_translations'] = $translations;
    $GLOBALS['bbx_translations_lang'] = $lang;

    return $translations;
}

// ═══════════════════════════════════════════════════════════════════════════════
// TRANSLATION RETRIEVAL
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Get translated text by key (supports dot notation for nested keys)
 *
 * @param string $key Translation key (e.g., 'header.menu.about')
 * @param string|array $fallbackOrReplacements Optional fallback string or replacement array
 * @param array $replacements Optional associative array for variable replacement (when fallback is provided)
 * @return string Translated text or fallback/key if not found
 *
 * Examples:
 *   bbx_get_text('header.menu.about')  // Returns: "Om os"
 *   bbx_get_text('pricing.from', ['price' => '1.799'])  // Returns: "Fra 1.799 DKK"
 *   bbx_get_text('some.key', 'Fallback text')  // Returns translation or 'Fallback text'
 */
function bbx_get_text($key, $fallbackOrReplacements = [], $replacements = [])
{
    static $translations = null;

    // Load translations on first call
    if ($translations === null) {
        $translations = bbx_load_translations();
    }

    // Determine fallback and replacements based on second argument type
    $fallback = null;
    if (is_string($fallbackOrReplacements)) {
        $fallback = $fallbackOrReplacements;
    } elseif (is_array($fallbackOrReplacements)) {
        $replacements = $fallbackOrReplacements;
    }

    // Navigate nested keys (e.g., 'header.menu.about' -> ['header']['menu']['about'])
    $keys = explode('.', $key);
    $value = $translations;

    foreach ($keys as $k) {
        if (is_array($value) && isset($value[$k])) {
            $value = $value[$k];
        } else {
            // Key not found - return fallback if provided, otherwise key
            if ($fallback !== null) {
                return $fallback;
            }
            error_log("BBX i18n WARNING: Translation key not found: " . $key);
            return $key;
        }
    }

    // Apply variable replacements if provided
    if (!empty($replacements) && is_string($value)) {
        foreach ($replacements as $placeholder => $replacement) {
            $value = str_replace('{' . $placeholder . '}', $replacement, $value);
        }
    }

    return $value;
}

/**
 * Shorthand alias for bbx_get_text()
 *
 * @param string $key Translation key
 * @param string|array $fallbackOrReplacements Optional fallback string or replacement array
 * @param array $replacements Optional replacements (when fallback is provided)
 * @return string Translated text
 */
function t($key, $fallbackOrReplacements = [], $replacements = [])
{
    return bbx_get_text($key, $fallbackOrReplacements, $replacements);
}

// ═══════════════════════════════════════════════════════════════════════════════
// LANGUAGE SWITCHING
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Switch to a different language
 *
 * @param string $lang Language code ('da' or 'en')
 * @return bool Success status
 */
function bbx_set_language($lang)
{
    if (!in_array($lang, ['da', 'en'])) {
        return false;
    }

    $_SESSION['lang'] = $lang;
    $GLOBALS['bbx_current_lang'] = $lang;
    $GLOBALS['bbx_translations'] = null; // Clear cache

    return true;
}

/**
 * Get current language code
 *
 * @return string Current language ('da' or 'en')
 */
function bbx_get_language()
{
    return $GLOBALS['bbx_current_lang'];
}

/**
 * Get language name in native form
 *
 * @param string $lang Language code
 * @return string Language name
 */
function bbx_get_language_name($lang = null)
{
    if ($lang === null) {
        $lang = bbx_get_language();
    }

    $names = [
        'da' => 'Dansk',
        'en' => 'English'
    ];

    return $names[$lang] ?? $lang;
}

// ═══════════════════════════════════════════════════════════════════════════════
// AUTO-INITIALIZATION
// ═══════════════════════════════════════════════════════════════════════════════

// Preload translations for better performance
bbx_load_translations();
