<?php

if (!function_exists('bbx_log_contact_submission')) {
  /**
   * Log contact form submission with standardized format.
   */
  function bbx_log_contact_submission(string $status, array $recaptcha_data = [], string $reason = '', array $extra = []): void
  {
    $logDirectory = __DIR__ . '/../logs';

    if (defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) {
      error_log('CONTACT FORM DEBUG: entering bbx_log_contact_submission() with status=' . $status . ' reason=' . ($reason !== '' ? $reason : '[empty]'));
    }

    if (!is_dir($logDirectory)) {
      if (!mkdir($logDirectory, 0755, true)) {
        error_log('CONTACT FORM LOG ERROR: Could not create log directory: ' . $logDirectory);
        return;
      }
      error_log('CONTACT FORM: Created log directory: ' . $logDirectory);
    }

    $logFile = $logDirectory . '/contact-submissions.log';

    $entry = [
      'timestamp' => gmdate('c'),
      'ip'        => $_SERVER['REMOTE_ADDR']     ?? 'unknown',
      'hostname'  => $recaptcha_data['hostname'] ?? ($_SERVER['HTTP_HOST'] ?? 'unknown'),
      'action'    => $recaptcha_data['action']   ?? 'contact',
      'score'     => $recaptcha_data['score']    ?? null,
      'success'   => $status === 'success',
      'reason'    => $reason !== '' ? $reason : ($status === 'success' ? 'ok' : $status),
    ];

    if (!empty($extra)) {
      $entry = array_merge($entry, $extra);
    }

    $jsonLine = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($jsonLine === false) {
      error_log('CONTACT FORM ERROR: Could not encode log entry to JSON');
      return;
    }

    $result = @file_put_contents($logFile, $jsonLine . PHP_EOL, FILE_APPEND | LOCK_EX);

    if ($result === false) {
      $entry['success'] = false;
      $entry['reason']  = 'log_failure';
      $fallback         = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: $jsonLine;
      error_log('CONTACT FORM LOG ERROR: Could not write to log file: ' . $logFile);
      error_log('CONTACT FORM LOG ERROR: Fallback entry => ' . $fallback);
    } elseif (defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) {
      error_log('CONTACT FORM DEBUG: Successfully logged to: ' . $logFile);
    }
  }
}
