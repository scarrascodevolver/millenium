<?php
require_once 'admin/config.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || strlen($_GET['q']) < 2) {
    echo json_encode(['suggestions' => []]);
    exit();
}

$search = $_GET['q'];
$tipo_operacion = $_GET['tipo'] ?? '';

try {
    $pdo = conectarDB();
    
    $where = "WHERE i.estado = 'activo' AND (i.titulo LIKE ? OR i.descripcion LIKE ? OR i.comuna LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
    
    if ($tipo_operacion) {
        $where .= " AND i.tipo_operacion = ?";
        $params[] = $tipo_operacion;
    }
    
    $stmt = $pdo->prepare("
        SELECT i.id, i.titulo, i.precio, i.moneda, i.tipo_propiedad, i.tipo_operacion,
               i.comuna, i.region, i.dormitorios, i.baños, i.superficie_construida
        FROM inmuebles i
        $where 
        ORDER BY i.fecha_publicacion DESC
        LIMIT 6
    ");
    
    $stmt->execute($params);
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['suggestions' => $properties]);
    
} catch (Exception $e) {
    echo json_encode(['suggestions' => [], 'error' => $e->getMessage()]);
}
?>