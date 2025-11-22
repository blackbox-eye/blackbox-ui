<?php

declare(strict_types=1);

/**
 * Robust mail sending function with fallback to SMTP
 *
 * This function first attempts to send email using PHP's mail() function
 * with enhanced headers. If SMTP credentials are configured via environment
 * variables, it will use PHPMailer with SMTP authentication instead.
 *
 * Environment variables for SMTP (optional):
 * - SMTP_HOST: SMTP server hostname (e.g., smtp.protonmail.ch, smtp.gmail.com)
 * - SMTP_PORT: SMTP port (typically 587 for TLS, 465 for SSL)
 * - SMTP_USERNAME: SMTP authentication username
 * - SMTP_PASSWORD: SMTP authentication password
 * - SMTP_SECURE: Encryption type ('tls' or 'ssl')
 *
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $message Email body (plain text)
 * @param string $fromName Sender name
 * @param string $fromEmail Sender email address
 * @param string $replyToEmail Reply-To email address (optional)
 * @param string $replyToName Reply-To name (optional)
 * @return bool True if mail was sent successfully, false otherwise
 */
function bbx_send_mail(
    string $to,
    string $subject,
    string $message,
    string $fromName = 'Blackbox EYE',
    string $fromEmail = '',
    string $replyToEmail = '',
    string $replyToName = ''
): bool {
    // Determine if SMTP is configured
    $smtpHost = bbx_env('SMTP_HOST', '');
    $smtpPort = (int)bbx_env('SMTP_PORT', '587');
    $smtpUsername = bbx_env('SMTP_USERNAME', '');
    $smtpPassword = bbx_env('SMTP_PASSWORD', '');
    $smtpSecure = bbx_env('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'

    $useSmtp = !empty($smtpHost) && !empty($smtpUsername) && !empty($smtpPassword);

    if (BBX_DEBUG_SMTP) {
        $maskedUsername = $smtpUsername !== '' ? substr($smtpUsername, 0, 3) . '***' : '[EMPTY]';
        error_log('CONTACT FORM MAIL DEBUG: SMTP configuration check');
        error_log('CONTACT FORM MAIL DEBUG: Host=' . ($smtpHost !== '' ? $smtpHost : '[EMPTY]'));
        error_log('CONTACT FORM MAIL DEBUG: Port=' . ($smtpPort > 0 ? (string)$smtpPort : '[INVALID]'));
        error_log('CONTACT FORM MAIL DEBUG: Username=' . $maskedUsername);
        error_log('CONTACT FORM MAIL DEBUG: Secure=' . ($smtpSecure !== '' ? $smtpSecure : '[EMPTY]'));
    }

    if (!$useSmtp && BBX_DEBUG_SMTP) {
        $missing = [];
        if ($smtpHost === '') {
            $missing[] = 'SMTP_HOST';
        }
        if ($smtpUsername === '') {
            $missing[] = 'SMTP_USERNAME';
        }
        if ($smtpPassword === '') {
            $missing[] = 'SMTP_PASSWORD';
        }
        error_log('CONTACT FORM MAIL DEBUG: SMTP disabled – missing env vars: ' . implode(', ', $missing));
    }

    if ($useSmtp) {
        error_log('CONTACT FORM MAIL: Using SMTP mode (host: ' . $smtpHost . ')');
        return bbx_send_mail_smtp(
            $to,
            $subject,
            $message,
            $fromName,
            $fromEmail,
            $replyToEmail,
            $replyToName,
            $smtpHost,
            $smtpPort,
            $smtpUsername,
            $smtpPassword,
            $smtpSecure
        );
    } else {
        error_log('CONTACT FORM MAIL: Using PHP mail() function');
        return bbx_send_mail_native(
            $to,
            $subject,
            $message,
            $fromName,
            $fromEmail,
            $replyToEmail,
            $replyToName
        );
    }
}

/**
 * Send email using PHP's native mail() function with enhanced headers
 *
 * @return bool True if mail was sent successfully
 */
function bbx_send_mail_native(
    string $to,
    string $subject,
    string $message,
    string $fromName,
    string $fromEmail,
    string $replyToEmail,
    string $replyToName
): bool {
    // Use server's domain for better deliverability
    $serverDomain = $_SERVER['HTTP_HOST'] ?? 'blackbox.codes';
    if (empty($fromEmail)) {
        $fromEmail = 'noreply@' . $serverDomain;
    }

    // Sanitize inputs
    $to = str_replace(["\r", "\n"], '', $to);
    $fromEmail = str_replace(["\r", "\n"], '', $fromEmail);
    $fromName = str_replace(["\r", "\n"], '', $fromName);
    $replyToEmail = str_replace(["\r", "\n"], '', $replyToEmail);
    $replyToName = str_replace(["\r", "\n"], '', $replyToName);

    // Generate unique Message-ID
    $messageId = '<' . md5(uniqid((string)time(), true)) . '@' . $serverDomain . '>';

    // Build headers with all necessary fields for deliverability
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Return-Path: ' . $fromEmail,
        'Message-ID: ' . $messageId,
        'Date: ' . date('r'),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: PHP/' . phpversion(),
        'X-Priority: 3',
    ];

    // Add Reply-To if provided
    if (!empty($replyToEmail)) {
        if (!empty($replyToName)) {
            $headers[] = 'Reply-To: ' . $replyToName . ' <' . $replyToEmail . '>';
        } else {
            $headers[] = 'Reply-To: ' . $replyToEmail;
        }
    }

    // Prepare message body
    $body = wordwrap($message, 78, PHP_EOL);

    // Log mail attempt
    error_log('CONTACT FORM MAIL DEBUG: Sending via mail() to ' . $to);
    error_log('CONTACT FORM MAIL DEBUG: From: ' . $fromEmail);
    error_log('CONTACT FORM MAIL DEBUG: Subject: ' . $subject);
    error_log('CONTACT FORM MAIL DEBUG: Message-ID: ' . $messageId);

    // Send mail with envelope sender parameter for better deliverability
    $additionalParams = '-f' . $fromEmail;
    $result = @mail($to, $subject, $body, implode("\r\n", $headers), $additionalParams);

    if (!$result) {
        $lastError = error_get_last();
        error_log('CONTACT FORM WARNING: mail() failed');
        if ($lastError && (strpos($lastError['message'], 'mail') !== false || strpos($lastError['message'], 'sendmail') !== false)) {
            error_log('CONTACT FORM WARNING: PHP error: ' . $lastError['message']);
        }
        if (!function_exists('mail')) {
            error_log('CONTACT FORM ERROR: mail() function is not available');
        }
    } else {
        error_log('CONTACT FORM MAIL DEBUG: mail() dispatched successfully');
    }

    return $result;
}

/**
 * Send email using PHPMailer with SMTP authentication
 *
 * @return bool True if mail was sent successfully
 */
function bbx_send_mail_smtp(
    string $to,
    string $subject,
    string $message,
    string $fromName,
    string $fromEmail,
    string $replyToEmail,
    string $replyToName,
    string $smtpHost,
    int $smtpPort,
    string $smtpUsername,
    string $smtpPassword,
    string $smtpSecure
): bool {
    // Load PHPMailer
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = $smtpSecure === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtpPort;

        // Enable debug logging if in debug mode
        if ((defined('BBX_DEBUG_RECAPTCHA') && BBX_DEBUG_RECAPTCHA) || (defined('BBX_DEBUG_SMTP') && BBX_DEBUG_SMTP)) {
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str, $level) {
                error_log('SMTP DEBUG: ' . $str);
            };
        }

        error_log('CONTACT FORM MAIL DEBUG: Initialising SMTP transport');

        // Use server domain if no from email provided
        if (empty($fromEmail)) {
            $serverDomain = $_SERVER['HTTP_HOST'] ?? 'blackbox.codes';
            $fromEmail = 'noreply@' . $serverDomain;
        }

        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        // Add Reply-To if provided
        if (!empty($replyToEmail)) {
            $mail->addReplyTo($replyToEmail, $replyToName);
        }

        // Content
        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $message;

        error_log('CONTACT FORM MAIL DEBUG: Sending via SMTP to ' . $to);
        error_log('CONTACT FORM MAIL DEBUG: SMTP Host: ' . $smtpHost . ':' . $smtpPort);
        error_log('CONTACT FORM MAIL DEBUG: From: ' . $fromEmail);
        error_log('CONTACT FORM MAIL DEBUG: Subject: ' . $subject);

        error_log('CONTACT FORM MAIL DEBUG: Attempting to send SMTP message');
        $mail->send();
        error_log('CONTACT FORM MAIL DEBUG: SMTP mail sent successfully');
        return true;
    } catch (Exception $e) {
        error_log('CONTACT FORM ERROR: SMTP send failed: ' . $mail->ErrorInfo);
        error_log('CONTACT FORM ERROR: Exception: ' . $e->getMessage());
        if (BBX_DEBUG_SMTP) {
            error_log('CONTACT FORM MAIL DEBUG: SMTP parameters used - Host=' . $smtpHost . ' Port=' . $smtpPort . ' Secure=' . $smtpSecure);
        }
        return false;
    }
}
