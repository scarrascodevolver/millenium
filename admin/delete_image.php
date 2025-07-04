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
    
    // Permitir eliminar todas las imágenes (se removió la restricción)
    
    // Obtener información de la imagen a eliminar
    $stmt = $pdo->prepare("SELECT ruta_archivo, es_principal FROM imagenes_inmuebles WHERE id = ?");
    $stmt->execute([$image_id]);
    $imagen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$imagen) {
        echo json_encode(['success' => false, 'message' => 'Imagen no encontrada']);
        exit();
    }
    
    // Eliminar archivo físico
    if ($imagen['ruta_archivo'] && file_exists('../' . $imagen['ruta_archivo'])) {
        unlink('../' . $imagen['ruta_archivo']);
    }
    
    // Eliminar de la base de datos
    $stmt = $pdo->prepare("DELETE FROM imagenes_inmuebles WHERE id = ?");
    $stmt->execute([$image_id]);
    
    // Si se eliminó la imagen principal, asignar nueva imagen principal
    if ($imagen['es_principal'] == 1) {
        $stmt = $pdo->prepare("UPDATE imagenes_inmuebles SET es_principal = 1 WHERE inmueble_id = ? ORDER BY orden_visualizacion LIMIT 1");
        $stmt->execute([$inmueble_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Imagen eliminada exitosamente']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la imagen: ' . $e->getMessage()]);
}
?>