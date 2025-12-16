<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════════
 * Blackbox UI - Internationalization (i18n) System
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * Provides multilingual support for the Blackbox EYE platform.
 * Supports Danish (da) and English (en) with cookie/session-based language switching.
 *
 * Features:
 * - JSON-based translation files (lang/da.json, lang/en.json)
 * - Cookie + session language persistence (mirrors localStorage on client)
 * - Global default: English
 * - Query-param aware (handled in templates) without breaking links
 * - Fast caching mechanism for performance
 * - Fallback to English when translations are missing
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

const BBX_ALLOWED_LANGS = ['en', 'da'];
const BBX_DEFAULT_LANG = 'en';
const BBX_LANG_COOKIE = 'bbx_lang';
const BBX_LANG_COOKIE_TTL = 31536000; // 365 days

/**
 * Set language cookie (mirrors localStorage on the client)
 */
function bbx_set_language_cookie(string $lang): void
{
    if (!in_array($lang, BBX_ALLOWED_LANGS, true)) {
        return;
    }

    // Keep cookie accessible to JS (not HttpOnly) for client/server sync
    setcookie(BBX_LANG_COOKIE, $lang, [
        'expires' => time() + BBX_LANG_COOKIE_TTL,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => false,
        'samesite' => 'Lax',
    ]);

    // Ensure current request also sees the new cookie value
    $_COOKIE[BBX_LANG_COOKIE] = $lang;
}

/**
 * Detect user's preferred language
 * Priority: 1) Session/cookie set by query-param or JS, 2) Default EN
 */
function bbx_detect_language(): string
{
    // Session takes priority (set during this request or previous ones)
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], BBX_ALLOWED_LANGS, true)) {
        $lang = $_SESSION['lang'];
        bbx_set_language_cookie($lang);
        return $lang;
    }

    // Cookie mirrors localStorage (set by client or server)
    if (isset($_COOKIE[BBX_LANG_COOKIE]) && in_array($_COOKIE[BBX_LANG_COOKIE], BBX_ALLOWED_LANGS, true)) {
        $lang = $_COOKIE[BBX_LANG_COOKIE];
        $_SESSION['lang'] = $lang;
        return $lang;
    }

    // Default to English
    $_SESSION['lang'] = BBX_DEFAULT_LANG;
    bbx_set_language_cookie(BBX_DEFAULT_LANG);
    return BBX_DEFAULT_LANG;
}

// Initialize language
$GLOBALS['bbx_current_lang'] = bbx_detect_language();
$GLOBALS['bbx_translations'] = null;
$GLOBALS['bbx_translations_lang'] = null;
$GLOBALS['bbx_base_translations'] = null;

// ═══════════════════════════════════════════════════════════════════════════════
// TRANSLATION LOADING
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Load translation file for current language
 * Implements caching to avoid repeated file reads
 */
function bbx_load_base_translations(): array
{
    if ($GLOBALS['bbx_base_translations'] !== null) {
        return $GLOBALS['bbx_base_translations'];
    }

    $base_path = __DIR__ . '/../lang/en.json';
    if (!file_exists($base_path)) {
        error_log('BBX i18n ERROR: Base translation file missing: ' . $base_path);
        $GLOBALS['bbx_base_translations'] = [];
        return $GLOBALS['bbx_base_translations'];
    }

    $json_content = file_get_contents($base_path);
    $base = json_decode($json_content, true);
    if ($base === null) {
        error_log('BBX i18n ERROR: Invalid JSON in base file: ' . $base_path);
        $base = [];
    }

    $GLOBALS['bbx_base_translations'] = $base;
    return $base;
}

function bbx_load_translations($lang = null)
{
    if ($lang === null) {
        $lang = $GLOBALS['bbx_current_lang'];
    }

    // Return cached translations if already loaded
    if ($GLOBALS['bbx_translations'] !== null && $GLOBALS['bbx_translations_lang'] === $lang) {
        return $GLOBALS['bbx_translations'];
    }

    $base = bbx_load_base_translations();

    if ($lang === 'en') {
        $GLOBALS['bbx_translations'] = $base;
        $GLOBALS['bbx_translations_lang'] = 'en';
        return $base;
    }

    $lang_file = __DIR__ . '/../lang/' . $lang . '.json';
    $override = [];

    if (file_exists($lang_file)) {
        $json_content = file_get_contents($lang_file);
        $decoded = json_decode($json_content, true);
        if ($decoded === null) {
            error_log('BBX i18n ERROR: Invalid JSON in file: ' . $lang_file);
        } else {
            $override = $decoded;
        }
    } else {
        error_log('BBX i18n WARNING: Translation file not found: ' . $lang_file . ' (falling back to English)');
    }

    // Merge override onto base to guarantee fallback to English keys
    $merged = array_replace_recursive($base, $override);

    $GLOBALS['bbx_translations'] = $merged;
    $GLOBALS['bbx_translations_lang'] = $lang;

    return $merged;
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
    $translations = bbx_get_translations();

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
        if (is_array($value) && array_key_exists($k, $value)) {
            $value = $value[$k];
        } else {
            // Key not found - return fallback if provided, otherwise key
            if ($fallback !== null) {
                return $fallback;
            }
            error_log('BBX i18n WARNING: Translation key not found: ' . $key);
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
    if (!in_array($lang, BBX_ALLOWED_LANGS, true)) {
        return false;
    }

    $_SESSION['lang'] = $lang;
    $GLOBALS['bbx_current_lang'] = $lang;
    $GLOBALS['bbx_translations'] = null; // Clear cache
    $GLOBALS['bbx_translations_lang'] = null;
    bbx_set_language_cookie($lang);

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

/**
 * Retrieve translations for the current language (ensures cache is current)
 */
function bbx_get_translations(): array
{
    $current = bbx_get_language();

    if ($GLOBALS['bbx_translations'] === null || $GLOBALS['bbx_translations_lang'] !== $current) {
        return bbx_load_translations($current);
    }

    return $GLOBALS['bbx_translations'];
}
