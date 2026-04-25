<?php

declare(strict_types=1);

// We run this in CLI mode, so header/cookie functions will emit warnings.
// We intercept them by output buffering or just ignoring them.
// But better yet, let's wrap our assertions and check output.
// Actually, since includes/i18n.php calls setcookie, we might get "Cannot modify header information".
// To prevent issues in tests, we can use a custom error handler or just suppress.
// It seems `includes/i18n.php` checks `php_sapi_name() === 'cli'`? No, it doesn't.
// Let's just catch warnings and continue.
error_reporting(E_ALL & ~E_WARNING);

// Need HTTPS on to prevent secure cookie errors just in case
$_SERVER['HTTPS'] = 'on';

require_once __DIR__ . '/../includes/i18n.php';

$tests_passed = 0;
$tests_failed = 0;

function assert_equals($expected, $actual, $message) {
    global $tests_passed, $tests_failed;
    if ($expected === $actual) {
        $tests_passed++;
        echo "✅ PASS: $message\n";
    } else {
        $tests_failed++;
        echo "❌ FAIL: $message\n";
        echo "   Expected: " . var_export($expected, true) . "\n";
        echo "   Actual:   " . var_export($actual, true) . "\n";
    }
}

function reset_globals() {
    $_SESSION = [];
    $_COOKIE = [];
}

// Test Case 1: Default language is 'en' when no session/cookie is set
reset_globals();
$lang = bbx_detect_language();
assert_equals('en', $lang, 'Default language should be en');
assert_equals('en', $_SESSION['lang'] ?? null, 'Session should be populated with default en');
assert_equals('en', $_COOKIE[BBX_LANG_COOKIE] ?? null, 'Cookie array should be populated with default en');

// Test Case 2: Session takes priority over cookie
reset_globals();
$_SESSION['lang'] = 'da';
$_COOKIE[BBX_LANG_COOKIE] = 'en';
$lang = bbx_detect_language();
assert_equals('da', $lang, 'Session should take priority over cookie');

// Test Case 3: Cookie is used when session is absent
reset_globals();
$_COOKIE[BBX_LANG_COOKIE] = 'da';
$lang = bbx_detect_language();
assert_equals('da', $lang, 'Cookie should be used when session is absent');
assert_equals('da', $_SESSION['lang'] ?? null, 'Session should be populated with cookie value');

// Test Case 4: Invalid session language ignores session and falls back to cookie
reset_globals();
$_SESSION['lang'] = 'fr'; // Invalid
$_COOKIE[BBX_LANG_COOKIE] = 'da';
$lang = bbx_detect_language();
assert_equals('da', $lang, 'Invalid session language should fall back to valid cookie');

// Test Case 5: Invalid cookie language falls back to default
reset_globals();
$_COOKIE[BBX_LANG_COOKIE] = 'fr'; // Invalid
$lang = bbx_detect_language();
assert_equals('en', $lang, 'Invalid cookie language should fall back to default');

// Test Case 6: Invalid session and cookie falls back to default
reset_globals();
$_SESSION['lang'] = 'de';
$_COOKIE[BBX_LANG_COOKIE] = 'es';
$lang = bbx_detect_language();
assert_equals('en', $lang, 'Invalid session and cookie should fall back to default');

echo "\nTests Complete: $tests_passed Passed, $tests_failed Failed\n";

if ($tests_failed > 0) {
    exit(1);
} else {
    exit(0);
}
