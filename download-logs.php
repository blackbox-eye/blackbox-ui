<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['agent_id'])) {
    header('Location: agent-login.php');
    exit;
}

$agent = preg_replace('/[^a-zA-Z0-9_-]/', '', $_SESSION['agent_id']); 
// Kun tillad alfanumeriske, underscore og bindestreg i filnavn

$logDir  = __DIR__ . '/logs/';
$logFile = $logDir . $agent . '.log';

if (!is_dir($logDir) || !is_readable($logDir)) {
    http_response_code(500);
    echo "Log-mappen mangler eller er ikke læsbar.";
    exit;
}

if (!file_exists($logFile)) {
    http_response_code(404);
    echo "Ingen log-fil fundet for agent “{$agent}”.";
    exit;
}

// Send som download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="login-logs-' . $agent . '.txt"');
readfile($logFile);
exit;
