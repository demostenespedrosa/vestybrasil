<?php
require_once 'includes/config.php';
iniciarSessao();

// Destruir sessão
session_destroy();

// Redirecionar para página inicial
header("Location: index.php");
exit;
?>
