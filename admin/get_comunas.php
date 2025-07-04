<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if (!isset($_GET['region_id']) || empty($_GET['region_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de región requerido']);
    exit();
}

$region_id = intval($_GET['region_id']);

try {
    $pdo = conectarDB();
    
    $stmt = $pdo->prepare("SELECT id, nombre FROM comunas WHERE region_id = ? ORDER BY nombre");
    $stmt->execute([$region_id]);
    $comunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'comunas' => $comunas]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener comunas: ' . $e->getMessage()]);
}
?>