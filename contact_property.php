<?php
require_once 'admin/config.php';
require_once 'admin/email_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inmueble_id = intval($_POST['inmueble_id']);
    $nombre = limpiarDatos($_POST['nombre']);
    $email = limpiarDatos($_POST['email']);
    $telefono = limpiarDatos($_POST['telefono']);
    $mensaje = limpiarDatos($_POST['mensaje']);
    
    if (empty($nombre) || empty($email) || empty($inmueble_id)) {
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos obligatorios']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Por favor ingrese un email válido']);
        exit;
    }
    
    try {
        $pdo = conectarDB();
        
        // Obtener información completa del inmueble
        $stmt = $pdo->prepare("SELECT * FROM inmuebles WHERE id = ?");
        $stmt->execute([$inmueble_id]);
        $inmueble = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$inmueble) {
            echo json_encode(['success' => false, 'message' => 'Propiedad no encontrada']);
            exit;
        }
        
        // Insertar consulta
        $stmt = $pdo->prepare("
            INSERT INTO consultas (inmueble_id, nombre, email, telefono, mensaje, fecha_consulta) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$inmueble_id, $nombre, $email, $telefono, $mensaje]);
        
        $consulta_id = $pdo->lastInsertId();
        
        // Preparar datos de la consulta para el email
        $consulta = [
            'id' => $consulta_id,
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'fecha_consulta' => date('Y-m-d H:i:s')
        ];
        
        // Solo guardar en base de datos, sin emails automáticos
        echo json_encode([
            'success' => true, 
            'message' => 'Consulta enviada exitosamente. Te contactaremos pronto.'
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al procesar la consulta: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>