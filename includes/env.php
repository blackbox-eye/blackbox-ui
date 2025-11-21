<?php
if (!function_exists('bbx_env')) {
    function bbx_env(string $key, $default = ''): string
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return (string) $value;
    }
}

if (!defined('BBX_SITE_NAME')) {
    define('BBX_SITE_NAME', 'Blackbox EYE™');
}

if (!defined('BBX_SITE_BASE_URL')) {
    define('BBX_SITE_BASE_URL', rtrim(bbx_env('SITE_BASE_URL', 'https://blackbox.codes'), '/'));
}

if (!defined('BBX_RECAPTCHA_SITE_KEY')) {
    define('BBX_RECAPTCHA_SITE_KEY', bbx_env('RECAPTCHA_SITE_KEY'));
}

if (!defined('BBX_RECAPTCHA_SECRET_KEY')) {
    define('BBX_RECAPTCHA_SECRET_KEY', bbx_env('RECAPTCHA_SECRET_KEY'));
}

if (!defined('BBX_RECAPTCHA_PROJECT_ID')) {
    define('BBX_RECAPTCHA_PROJECT_ID', bbx_env('RECAPTCHA_PROJECT_ID')); // Optional - not used in Standard v3
}

if (!defined('BBX_DEBUG_RECAPTCHA')) {
    define('BBX_DEBUG_RECAPTCHA', bbx_env('RECAPTCHA_DEBUG') === 'true');
}

// Debug: Log all loaded reCAPTCHA values
if (BBX_DEBUG_RECAPTCHA) {
    error_log('BBX ENV DEBUG - RECAPTCHA_SITE_KEY: ' . (BBX_RECAPTCHA_SITE_KEY ? '[SET]' : '[EMPTY]'));
    error_log('BBX ENV DEBUG - RECAPTCHA_SECRET_KEY: ' . (BBX_RECAPTCHA_SECRET_KEY ? '[SET]' : '[EMPTY]'));
    error_log('BBX ENV DEBUG - RECAPTCHA_PROJECT_ID: ' . (BBX_RECAPTCHA_PROJECT_ID ? BBX_RECAPTCHA_PROJECT_ID : '[EMPTY]'));
}

// Log configuration warnings
if (BBX_RECAPTCHA_SITE_KEY !== '' && BBX_RECAPTCHA_SECRET_KEY === '') {
    error_log('reCAPTCHA Warning: RECAPTCHA_SITE_KEY is set but RECAPTCHA_SECRET_KEY is missing');
}
if (BBX_RECAPTCHA_SECRET_KEY !== '' && BBX_RECAPTCHA_PROJECT_ID === '') {
    error_log('reCAPTCHA Warning: RECAPTCHA_SECRET_KEY is set but RECAPTCHA_PROJECT_ID is missing for Enterprise API');
}
