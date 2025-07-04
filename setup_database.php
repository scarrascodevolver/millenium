<?php
require_once 'admin/config.php';

try {
    $pdo = conectarDB();
    
    // Eliminar tabla si existe para recrearla
    $pdo->exec("DROP TABLE IF EXISTS consultas");
    
    // Crear tabla de consultas
    $sql = "
    CREATE TABLE consultas (
        id int(11) NOT NULL AUTO_INCREMENT,
        inmueble_id int(11) NOT NULL,
        nombre varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        telefono varchar(20) DEFAULT NULL,
        mensaje text,
        fecha_consulta timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        respondido tinyint(1) DEFAULT '0',
        respuesta text DEFAULT NULL,
        fecha_respuesta timestamp NULL DEFAULT NULL,
        PRIMARY KEY (id),
        KEY fk_consultas_inmueble (inmueble_id),
        CONSTRAINT fk_consultas_inmueble FOREIGN KEY (inmueble_id) REFERENCES inmuebles (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'consultas' creada exitosamente!<br>";
    echo "✅ Sistema de emails configurado en modo desarrollo.<br>";
    echo "✅ Ya puedes probar el formulario de contacto.";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>