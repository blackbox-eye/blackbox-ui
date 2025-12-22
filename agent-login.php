<?php
// Legacy entrypoint retained for compatibility. Redirect to the renamed GDI login surface.
$target = '/gdi-login.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}
header('Location: ' . $target, true, 301);
exit;
