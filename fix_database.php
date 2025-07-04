<?php
require_once 'admin/config.php';

echo "<h2>🔧 Arreglando Base de Datos - Configuración de WhatsApp y Teléfonos</h2>";

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
            $pdo->exec("ALTER TABLE configuracion ADD COLUMN telefono_principal VARCHAR(20) NOT NULL DEFAULT '+41 2799626' AFTER id");
            echo "✅ Columna telefono_principal agregada<br>";
        } else {
            echo "ℹ️ Columna telefono_principal ya existe<br>";
        }
        
        if (!$whatsapp_principal_exists) {
            $pdo->exec("ALTER TABLE configuracion ADD COLUMN whatsapp_principal VARCHAR(20) NOT NULL DEFAULT '56932385980' AFTER telefono_principal");
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
                whatsapp_principal = COALESCE(whatsapp_principal, '56932385980'),
                telefono = COALESCE(telefono, '+569 56287856'),
                whatsapp = COALESCE(whatsapp, '56932385980'),
                email = COALESCE(email, 'info@millenium.cl')
            WHERE id = 1
        ");
        $stmt->execute();
        echo "✅ Configuración existente actualizada con nuevos campos<br>";
    }
    
    // Mostrar configuración actual
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<br><h3>📋 Configuración Actual:</h3>";
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>Teléfono Principal (Header/Footer)</td><td>" . htmlspecialchars($config['telefono_principal']) . "</td></tr>";
    echo "<tr><td>WhatsApp Principal (Widget)</td><td>" . htmlspecialchars($config['whatsapp_principal']) . "</td></tr>";
    echo "<tr><td>Teléfono Botón Llamar</td><td>" . htmlspecialchars($config['telefono']) . "</td></tr>";
    echo "<tr><td>WhatsApp Botón Contactar</td><td>" . htmlspecialchars($config['whatsapp']) . "</td></tr>";
    echo "<tr><td>Email</td><td>" . htmlspecialchars($config['email']) . "</td></tr>";
    echo "</table>";
    
    echo "<br><h3>🔗 Enlaces de Prueba:</h3>";
    echo "<p><strong>WhatsApp Principal:</strong> <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp_principal']) . "' target='_blank'>https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp_principal']) . "</a></p>";
    echo "<p><strong>WhatsApp Botón:</strong> <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp']) . "' target='_blank'>https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp']) . "</a></p>";
    echo "<p><strong>Teléfono Principal:</strong> <a href='tel:" . htmlspecialchars($config['telefono_principal']) . "'>tel:" . htmlspecialchars($config['telefono_principal']) . "</a></p>";
    echo "<p><strong>Teléfono Botón:</strong> <a href='tel:" . htmlspecialchars($config['telefono']) . "'>tel:" . htmlspecialchars($config['telefono']) . "</a></p>";
    
    echo "<br><h3>🎯 Próximos Pasos:</h3>";
    echo "<p>1. Accede al <a href='admin/configuracion.php'>Panel de Configuración</a> para cambiar los números</p>";
    echo "<p>2. Los cambios se aplicarán automáticamente en todo el sitio</p>";
    echo "<p>3. Verifica que los botones funcionen correctamente</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>