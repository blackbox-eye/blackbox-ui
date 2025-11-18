<?php
session_start();
session_unset();
session_destroy();
header('Location: agent-login.php');
exit;
