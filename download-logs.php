<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['agent_id'])) {
    header('Location: gdi-login.php');
    exit;
}

$agent = preg_replace('/[^a-zA-Z0-9_-]/', '', $_SESSION['agent_id']); 
// Kun tillad alfanumeriske, underscore og bindestreg i filnavn

$logDir  = __DIR__ . '/logs/';
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0750, true) && !is_dir($logDir)) {
        http_response_code(500);
        echo "Log-mappen kunne ikke oprettes.";
        exit;
    }
}

if (!is_readable($logDir)) {
    http_response_code(500);
    echo "Log-mappen er ikke læsbar.";
    exit;
}

$logFile = $logDir . $agent . '.log';

if (!file_exists($logFile)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Ingen log-posteringer for agent \"{$agent}\" endnu.";
    exit;
}

// Send som download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="login-logs-' . $agent . '.txt"');
readfile($logFile);
exit;
