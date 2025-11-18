<?php
// public_html/toggle-ghost.php
session_start();
require __DIR__ . '/db.php';

// 1) Tjek login
if (!isset($_SESSION['agent_id'])) {
    header('Location: agent-login.php');
    exit;
}

$agentId = $_SESSION['agent_id'];

// 2) Hent eksisterende flag
$stmt = $pdo->prepare("SELECT ghost FROM agents WHERE agent_id = ?");
$stmt->execute([$agentId]);
$current = (int)$stmt->fetchColumn();

// 3) Vend flag og opdater
$new = $current ? 0 : 1;
$pdo->prepare("UPDATE agents SET ghost = ? WHERE agent_id = ?")
    ->execute([$new, $agentId]);

$_SESSION['settings_success'] = $new ? 'Ghost-mode er nu aktiveret.' : 'Ghost-mode er nu deaktiveret.';

// 4) Tilbage til settings
header('Location: settings.php');
exit;
