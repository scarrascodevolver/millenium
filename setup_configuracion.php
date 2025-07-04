<?php
require_once 'admin/config.php';

try {
    $pdo = conectarDB();
    
    // Crear tabla de configuración
    $sql = "CREATE TABLE IF NOT EXISTS configuracion (
        id INT PRIMARY KEY,
        telefono VARCHAR(20) NOT NULL,
        whatsapp VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Insertar configuración por defecto si no existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM configuracion WHERE id = 1");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if (!$existe) {
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono, whatsapp, email, fecha_creacion, fecha_actualizacion) 
            VALUES (1, '+569 56287856', '56932385980', 'info@millenium.cl', NOW(), NOW())
        ");
        $stmt->execute();
        echo "✅ Configuración por defecto creada exitosamente<br>";
    }
    
    echo "✅ Tabla de configuración creada exitosamente<br>";
    echo "📋 Puedes acceder a la configuración desde: <a href='admin/configuracion.php'>Panel de Configuración</a><br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>