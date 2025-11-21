<?php
if (!function_exists('bbx_env')) {
    /**
     * Get environment variable from multiple sources
     * 
     * Checks in order: getenv(), $_SERVER, $_ENV
     * This ensures compatibility with Apache SetEnv, php-fpm, and CLI environments
     * 
     * @param string $key Environment variable name
     * @param string $default Default value if not found
     * @return string The environment variable value or default
     */
    function bbx_env(string $key, $default = ''): string
    {
        // Try getenv() first (works in some configurations)
        $value = getenv($key);
        
        // If getenv() fails or returns empty, fallback to $_SERVER (Apache SetEnv)
        if ($value === false || $value === '') {
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return (string)$_SERVER[$key];
            }
            
            // Final fallback to $_ENV
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return (string)$_ENV[$key];
            }
            
            return $default;
        }
        
        return (string)$value;
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

if (!defined('BBX_DEBUG_SMTP')) {
    $smtpDebugFlag = strtolower(bbx_env('SMTP_DEBUG', ''));
    $smtpDebugEnabled = $smtpDebugFlag === 'true' || $smtpDebugFlag === '1' || $smtpDebugFlag === 'on';
    define('BBX_DEBUG_SMTP', $smtpDebugEnabled);
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

if (BBX_DEBUG_SMTP) {
    error_log('BBX ENV DEBUG - SMTP debugging enabled');
}
