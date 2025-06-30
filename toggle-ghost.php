<?php
// public_html/toggle-ghost.php
session_start();
require __DIR__ . '/db.php';

// 1) Tjek login
if (!isset($_SESSION['agent_id'])) {
    header('Location: index.php');
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

// 4) Tilbage til settings
header('Location: settings.php');
exit;
