<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $termo = $input['termo'] ?? '';
    
    if (strlen($termo) < 2) {
        echo json_encode(['success' => true, 'produtos' => []]);
        exit;
    }
    
    $pdo = conectar();
    
    $stmt = $pdo->prepare("
        SELECT p.*, c.nome as categoria_nome 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.ativo = 1 
        AND (p.nome LIKE ? OR p.descricao LIKE ? OR c.nome LIKE ?)
        ORDER BY p.created_at DESC 
        LIMIT 20
    ");
    
    $searchTerm = "%{$termo}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    
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
