<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

$categoria_id = $_GET['categoria'] ?? null;

if (!$categoria_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Categoria não informada']);
    exit;
}

try {
    $pdo = conectar();
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.categoria_id = ? AND p.ativo = 1 
        ORDER BY p.created_at DESC
    ");
    
    $stmt->execute([$categoria_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'produtos' => $produtos
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
}
?>
