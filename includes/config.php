<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'vesty_brasil');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configurações gerais
define('SITE_URL', 'http://localhost/vestybrasil');
define('UPLOAD_PATH', 'assets/images/produtos/');

// Conexão com o banco
function conectar() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Erro na conexão: " . $e->getMessage());
    }
}

// Funções utilitárias
function formatarPreco($preco) {
    return 'R$ ' . number_format($preco, 2, ',', '.');
}

function iniciarSessao() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function verificarLogin() {
    iniciarSessao();
    return isset($_SESSION['usuario_id']);
}

function verificarLoginVendedor() {
    iniciarSessao();
    return isset($_SESSION['vendedor_id']);
}

function verificarLoginAdmin() {
    iniciarSessao();
    return isset($_SESSION['admin_id']);
}

function redirecionarSeNaoLogado($url = 'login.php') {
    if (!verificarLogin()) {
        header("Location: $url");
        exit;
    }
}

function sanitizar($dados) {
    return htmlspecialchars(strip_tags(trim($dados)));
}

function gerarToken() {
    return bin2hex(random_bytes(32));
}
?>
