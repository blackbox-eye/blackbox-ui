<?php
session_start();

require_once __DIR__ . '/includes/jwt_helper.php';

bbx_clear_agent_jwt();
bbx_clear_agent_jwt_cookie();

session_unset();
session_destroy();

if (isset($_GET['qaInvalidate'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['status' => 'ok', 'message' => 'SSO token invalidated']);
  exit;
}

header('Location: agent-login.php');
exit;
