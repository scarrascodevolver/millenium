<?php
require_once 'admin/config.php';

echo "<h2>🔍 Diagnóstico de Configuración</h2>";

try {
    $pdo = conectarDB();
    echo "✅ Conexión a base de datos exitosa<br>";
    
    // Verificar si la tabla existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'configuracion'");
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "❌ La tabla 'configuracion' NO existe<br>";
        echo "📋 Creando tabla...<br>";
        
        $sql = "CREATE TABLE configuracion (
            id INT PRIMARY KEY,
            telefono_principal VARCHAR(20) NOT NULL DEFAULT '+41 2799626',
            whatsapp_principal VARCHAR(20) NOT NULL DEFAULT '56932385980',
            telefono VARCHAR(20) NOT NULL DEFAULT '+569 56287856',
            whatsapp VARCHAR(20) NOT NULL DEFAULT '56932385980',
            email VARCHAR(100) NOT NULL DEFAULT 'info@millenium.cl',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "✅ Tabla creada exitosamente<br>";
        
        // Insertar datos por defecto
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email) 
            VALUES (1, '+41 2799626', '56932385980', '+569 56287856', '56932385980', 'info@millenium.cl')
        ");
        $stmt->execute();
        echo "✅ Datos por defecto insertados<br>";
    } else {
        echo "✅ La tabla 'configuracion' existe<br>";
        
        // Mostrar estructura actual
        $stmt = $pdo->prepare("DESCRIBE configuracion");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>📊 Estructura de la tabla:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si faltan columnas
        $required_columns = ['telefono_principal', 'whatsapp_principal'];
        $existing_columns = array_column($columns, 'Field');
        
        foreach ($required_columns as $col) {
            if (!in_array($col, $existing_columns)) {
                echo "❌ Falta columna: $col<br>";
                echo "📋 Agregando columna $col...<br>";
                
                if ($col == 'telefono_principal') {
                    $pdo->exec("ALTER TABLE configuracion ADD COLUMN telefono_principal VARCHAR(20) NOT NULL DEFAULT '+41 2799626' AFTER id");
                } elseif ($col == 'whatsapp_principal') {
                    $pdo->exec("ALTER TABLE configuracion ADD COLUMN whatsapp_principal VARCHAR(20) NOT NULL DEFAULT '56932385980' AFTER telefono_principal");
                }
                echo "✅ Columna $col agregada<br>";
            }
        }
    }
    
    // Verificar datos actuales
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        echo "❌ No hay configuración con id=1<br>";
        echo "📋 Insertando configuración por defecto...<br>";
        
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email) 
            VALUES (1, '+41 2799626', '56932385980', '+569 56287856', '56932385980', 'info@millenium.cl')
        ");
        $stmt->execute();
        echo "✅ Configuración por defecto insertada<br>";
        
        // Obtener datos recién insertados
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo "<h3>📋 Configuración Actual:</h3>";
    echo "<table border='1' style='border-collapse: collapse; padding: 10px;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach ($config as $key => $value) {
        echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    
    // Pruebas de enlaces
    echo "<h3>🔗 Pruebas de Enlaces:</h3>";
    echo "<p><strong>WhatsApp Principal:</strong> <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp_principal']) . "&text=Hola' target='_blank'>Probar WhatsApp Principal</a></p>";
    echo "<p><strong>WhatsApp Botón:</strong> <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp']) . "&text=Hola' target='_blank'>Probar WhatsApp Botón</a></p>";
    echo "<p><strong>Teléfono Principal:</strong> <a href='tel:" . htmlspecialchars(str_replace(' ', '', $config['telefono_principal'])) . "'>Probar Teléfono Principal</a></p>";
    echo "<p><strong>Teléfono Botón:</strong> <a href='tel:" . htmlspecialchars(str_replace(' ', '', $config['telefono'])) . "'>Probar Teléfono Botón</a></p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "📋 Detalles del error:<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>🎯 Próximos Pasos:</h3>";
echo "<p>1. Si todo está correcto, ve a <a href='admin/configuracion.php'>Panel de Configuración</a></p>";
echo "<p>2. Cambia los números según necesites</p>";
echo "<p>3. Prueba los botones en <a href='corretaje.php'>Corretaje</a> o <a href='detalle-propiedad.php?id=1'>Detalle de Propiedad</a></p>";
?>