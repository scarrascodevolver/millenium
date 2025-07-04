<?php
require_once 'admin/config.php';

try {
    $pdo = conectarDB();
    
    // Verificar si la tabla configuracion existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'configuracion'");
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        // Crear tabla completa con todas las columnas
        $sql = "CREATE TABLE configuracion (
            id INT PRIMARY KEY,
            telefono_principal VARCHAR(20) NOT NULL,
            whatsapp_principal VARCHAR(20) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            whatsapp VARCHAR(20) NOT NULL,
            email VARCHAR(100) NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Tabla configuracion creada exitosamente<br>";
    } else {
        echo "ℹ️ Tabla configuracion ya existe<br>";
        
        // Verificar si las columnas nuevas existen
        $stmt = $pdo->prepare("SHOW COLUMNS FROM configuracion LIKE 'telefono_principal'");
        $stmt->execute();
        $telefono_principal_exists = $stmt->rowCount() > 0;
        
        $stmt = $pdo->prepare("SHOW COLUMNS FROM configuracion LIKE 'whatsapp_principal'");
        $stmt->execute();
        $whatsapp_principal_exists = $stmt->rowCount() > 0;
        
        // Agregar columnas si no existen
        if (!$telefono_principal_exists) {
            $pdo->exec("ALTER TABLE configuracion ADD COLUMN telefono_principal VARCHAR(20) AFTER id");
            echo "✅ Columna telefono_principal agregada<br>";
        } else {
            echo "ℹ️ Columna telefono_principal ya existe<br>";
        }
        
        if (!$whatsapp_principal_exists) {
            $pdo->exec("ALTER TABLE configuracion ADD COLUMN whatsapp_principal VARCHAR(20) AFTER telefono_principal");
            echo "✅ Columna whatsapp_principal agregada<br>";
        } else {
            echo "ℹ️ Columna whatsapp_principal ya existe<br>";
        }
    }
    
    // Verificar si ya existe configuración
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM configuracion WHERE id = 1");
    $stmt->execute();
    $existe = $stmt->fetchColumn();
    
    if (!$existe) {
        // Insertar configuración por defecto completa
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email, fecha_creacion, fecha_actualizacion) 
            VALUES (1, '+41 2799626', '56932385980', '+569 56287856', '56932385980', 'info@millenium.cl', NOW(), NOW())
        ");
        $stmt->execute();
        echo "✅ Configuración completa creada exitosamente<br>";
    } else {
        // Actualizar configuración existente para agregar valores por defecto a los nuevos campos
        $stmt = $pdo->prepare("
            UPDATE configuracion 
            SET telefono_principal = COALESCE(telefono_principal, '+41 2799626'),
                whatsapp_principal = COALESCE(whatsapp_principal, '56932385980')
            WHERE id = 1
        ");
        $stmt->execute();
        echo "✅ Configuración existente actualizada con nuevos campos<br>";
    }
    
    echo "📋 Puedes acceder a la configuración actualizada desde: <a href='admin/configuracion.php'>Panel de Configuración</a><br>";
    echo "<br><strong>📱 Ahora puedes configurar:</strong><br>";
    echo "• Números principales (header/footer)<br>";
    echo "• Números específicos para botones de contacto<br>";
    echo "• Email de contacto<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>