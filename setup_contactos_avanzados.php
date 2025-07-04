<?php
require_once 'admin/config.php';

echo "<h2>🏗️ Configuración Avanzada de Contactos por Sección</h2>";

try {
    $pdo = conectarDB();
    
    // Verificar si la tabla configuracion existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'configuracion'");
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "❌ Tabla configuracion no existe. Ejecuta primero diagnostico_completo.php<br>";
        exit;
    }
    
    // Agregar nuevas columnas para contactos específicos
    echo "<h3>📊 Agregando nuevas columnas...</h3>";
    
    $nuevas_columnas = [
        'email_corretaje' => "ALTER TABLE configuracion ADD COLUMN email_corretaje VARCHAR(100) DEFAULT 'corretaje@millenium.cl'",
        'whatsapp_corretaje' => "ALTER TABLE configuracion ADD COLUMN whatsapp_corretaje VARCHAR(20) DEFAULT '56932385980'",
        'telefono_corretaje' => "ALTER TABLE configuracion ADD COLUMN telefono_corretaje VARCHAR(20) DEFAULT '+569 56287856'",
        'email_administracion' => "ALTER TABLE configuracion ADD COLUMN email_administracion VARCHAR(100) DEFAULT 'administracion@millenium.cl'",
        'whatsapp_administracion' => "ALTER TABLE configuracion ADD COLUMN whatsapp_administracion VARCHAR(20) DEFAULT '56932385980'",
        'telefono_administracion' => "ALTER TABLE configuracion ADD COLUMN telefono_administracion VARCHAR(20) DEFAULT '+569 56287856'",
        'email_consultas' => "ALTER TABLE configuracion ADD COLUMN email_consultas VARCHAR(100) DEFAULT 'consultas@millenium.cl'",
        'telefono_emergencias' => "ALTER TABLE configuracion ADD COLUMN telefono_emergencias VARCHAR(20) DEFAULT '+41 2799626'"
    ];
    
    // Verificar columnas existentes
    $stmt = $pdo->prepare("DESCRIBE configuracion");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existing_columns = array_column($columns, 'Field');
    
    foreach ($nuevas_columnas as $columna => $sql) {
        if (!in_array($columna, $existing_columns)) {
            try {
                $pdo->exec($sql);
                echo "✅ Columna '$columna' agregada<br>";
            } catch (Exception $e) {
                echo "⚠️ Error agregando '$columna': " . $e->getMessage() . "<br>";
            }
        } else {
            echo "ℹ️ Columna '$columna' ya existe<br>";
        }
    }
    
    // Actualizar configuración existente
    echo "<h3>📝 Actualizando configuración...</h3>";
    
    $stmt = $pdo->prepare("
        UPDATE configuracion SET 
            email_corretaje = COALESCE(email_corretaje, 'corretaje@millenium.cl'),
            whatsapp_corretaje = COALESCE(whatsapp_corretaje, whatsapp),
            telefono_corretaje = COALESCE(telefono_corretaje, telefono),
            email_administracion = COALESCE(email_administracion, 'administracion@millenium.cl'),
            whatsapp_administracion = COALESCE(whatsapp_administracion, whatsapp),
            telefono_administracion = COALESCE(telefono_administracion, telefono),
            email_consultas = COALESCE(email_consultas, 'consultas@millenium.cl'),
            telefono_emergencias = COALESCE(telefono_emergencias, telefono_principal)
        WHERE id = 1
    ");
    $stmt->execute();
    echo "✅ Configuración actualizada<br>";
    
    // Mostrar configuración actual
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>📋 Configuración Actual por Sección:</h3>";
    
    echo "<h4>🌐 CONTACTOS GENERALES (Header/Footer)</h4>";
    echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
    echo "<tr><td><strong>Teléfono Principal</strong></td><td>" . htmlspecialchars($config['telefono_principal']) . "</td></tr>";
    echo "<tr><td><strong>WhatsApp Principal</strong></td><td>" . htmlspecialchars($config['whatsapp_principal']) . "</td></tr>";
    echo "<tr><td><strong>Email General</strong></td><td>" . htmlspecialchars($config['email']) . "</td></tr>";
    echo "</table>";
    
    echo "<h4>🏠 CONTACTOS CORRETAJE</h4>";
    echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
    echo "<tr><td><strong>Email Corretaje</strong></td><td>" . htmlspecialchars($config['email_corretaje']) . "</td></tr>";
    echo "<tr><td><strong>WhatsApp Corretaje</strong></td><td>" . htmlspecialchars($config['whatsapp_corretaje']) . "</td></tr>";
    echo "<tr><td><strong>Teléfono Corretaje</strong></td><td>" . htmlspecialchars($config['telefono_corretaje']) . "</td></tr>";
    echo "</table>";
    
    echo "<h4>🏢 CONTACTOS ADMINISTRACIÓN</h4>";
    echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
    echo "<tr><td><strong>Email Administración</strong></td><td>" . htmlspecialchars($config['email_administracion']) . "</td></tr>";
    echo "<tr><td><strong>WhatsApp Administración</strong></td><td>" . htmlspecialchars($config['whatsapp_administracion']) . "</td></tr>";
    echo "<tr><td><strong>Teléfono Administración</strong></td><td>" . htmlspecialchars($config['telefono_administracion']) . "</td></tr>";
    echo "</table>";
    
    echo "<h4>📞 CONTACTOS ADICIONALES</h4>";
    echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
    echo "<tr><td><strong>Email Consultas</strong></td><td>" . htmlspecialchars($config['email_consultas']) . "</td></tr>";
    echo "<tr><td><strong>Teléfono Emergencias</strong></td><td>" . htmlspecialchars($config['telefono_emergencias']) . "</td></tr>";
    echo "</table>";
    
    echo "<h3>🎯 Uso por Página:</h3>";
    echo "<ul>";
    echo "<li><strong>index.html:</strong> Contactos generales</li>";
    echo "<li><strong>corretaje.php:</strong> Contactos de corretaje</li>";
    echo "<li><strong>detalle-propiedad.php:</strong> Contactos de corretaje</li>";
    echo "<li><strong>Header/Footer:</strong> Contactos generales</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Próximos Pasos:</h3>";
    echo "<p>1. <a href='actualizar_panel_configuracion.php'>Actualizar Panel de Configuración</a> para manejar las nuevas secciones</p>";
    echo "<p>2. <a href='aplicar_contactos_por_seccion.php'>Aplicar Contactos por Sección</a> en las páginas</p>";
    echo "<p>3. <a href='admin/configuracion.php'>Panel de Configuración</a> (necesita actualización)</p>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>