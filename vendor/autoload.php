<?php

/**
 * Minimal PSR-4 style autoloader for vendored third-party libraries.
 */

spl_autoload_register(static function (string $class): void {
  $prefix = 'Firebase\\JWT\\';
  if (strpos($class, $prefix) !== 0) {
    return;
  }

  $relative = substr($class, strlen($prefix));
  $path = __DIR__ . '/firebase/php-jwt/src/' . str_replace('\\', '/', $relative) . '.php';

  if (is_readable($path)) {
    require_once $path;
  }
});
