<?php
require_once '../includes/config.php';
iniciarSessao();

session_destroy();
header("Location: login.php");
exit;
?>
