<?php
require_once 'config.php';
verificarLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$image_id = intval($_POST['image_id']);
$inmueble_id = intval($_POST['inmueble_id']);

if (!$image_id || !$inmueble_id) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit();
}

try {
    $pdo = conectarDB();
    
    // Verificar que la imagen existe y pertenece al inmueble
    $stmt = $pdo->prepare("SELECT id FROM imagenes_inmuebles WHERE id = ? AND inmueble_id = ?");
    $stmt->execute([$image_id, $inmueble_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Imagen no encontrada']);
        exit();
    }
    
    // Quitar la imagen principal actual
    $stmt = $pdo->prepare("UPDATE imagenes_inmuebles SET es_principal = 0 WHERE inmueble_id = ?");
    $stmt->execute([$inmueble_id]);
    
    // Establecer la nueva imagen principal
    $stmt = $pdo->prepare("UPDATE imagenes_inmuebles SET es_principal = 1 WHERE id = ?");
    $stmt->execute([$image_id]);
    
    echo json_encode(['success' => true, 'message' => 'Imagen principal seleccionada correctamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar imagen principal: ' . $e->getMessage()]);
}
?>