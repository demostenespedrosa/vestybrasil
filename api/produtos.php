<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

try {
    $pdo = conectar();
    
    // Buscar produtos
    $stmt = $pdo->query("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.ativo = 1 
        ORDER BY p.created_at DESC 
        LIMIT 20
    ");
    
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
