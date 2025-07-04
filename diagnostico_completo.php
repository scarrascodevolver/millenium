<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico Completo - WhatsApp y Botones</h1>";

// Test 1: Verificar conexión a base de datos
echo "<h2>1. Conexión a Base de Datos</h2>";
try {
    require_once 'admin/config.php';
    $pdo = conectarDB();
    echo "✅ Conexión exitosa<br>";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Verificar estructura de tabla
echo "<h2>2. Estructura de Tabla</h2>";
try {
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'configuracion'");
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;
    
    if (!$table_exists) {
        echo "❌ Tabla 'configuracion' no existe. Creando...<br>";
        
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
        echo "✅ Tabla creada<br>";
        
        // Insertar datos
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email) 
            VALUES (1, '+41 2799626', '56932385980', '+569 56287856', '56932385980', 'info@millenium.cl')
        ");
        $stmt->execute();
        echo "✅ Datos insertados<br>";
    } else {
        echo "✅ Tabla existe<br>";
        
        // Verificar columnas necesarias
        $stmt = $pdo->prepare("DESCRIBE configuracion");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $column_names = array_column($columns, 'Field');
        
        $required = ['telefono_principal', 'whatsapp_principal'];
        foreach ($required as $col) {
            if (!in_array($col, $column_names)) {
                echo "❌ Falta columna: $col. Agregando...<br>";
                if ($col == 'telefono_principal') {
                    $pdo->exec("ALTER TABLE configuracion ADD COLUMN telefono_principal VARCHAR(20) NOT NULL DEFAULT '+41 2799626' AFTER id");
                } elseif ($col == 'whatsapp_principal') {
                    $pdo->exec("ALTER TABLE configuracion ADD COLUMN whatsapp_principal VARCHAR(20) NOT NULL DEFAULT '56932385980' AFTER telefono_principal");
                }
                echo "✅ Columna $col agregada<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Error en estructura: " . $e->getMessage() . "<br>";
}

// Test 3: Verificar datos
echo "<h2>3. Datos de Configuración</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        echo "❌ No hay configuración. Insertando datos por defecto...<br>";
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email) 
            VALUES (1, '+41 2799626', '56932385980', '+569 56287856', '56932385980', 'info@millenium.cl')
        ");
        $stmt->execute();
        echo "✅ Datos insertados<br>";
        
        // Obtener datos recién insertados
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo "✅ Configuración encontrada:<br>";
    echo "<table border='1' style='border-collapse: collapse; padding: 5px;'>";
    foreach ($config as $key => $value) {
        echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "❌ Error en datos: " . $e->getMessage() . "<br>";
    exit;
}

// Test 4: Generar URLs de prueba
echo "<h2>4. URLs Generadas</h2>";
$whatsapp_principal = $config['whatsapp_principal'] ?? '56932385980';
$whatsapp_botton = $config['whatsapp'] ?? '56932385980';
$telefono_principal = $config['telefono_principal'] ?? '+41 2799626';
$telefono_botton = $config['telefono'] ?? '+569 56287856';

echo "<p><strong>WhatsApp Principal:</strong><br>";
echo "URL: https://api.whatsapp.com/send/?phone=" . htmlspecialchars($whatsapp_principal) . "&text=Hola<br>";
echo "Link: <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($whatsapp_principal) . "&text=Hola' target='_blank'>Probar WhatsApp Principal</a></p>";

echo "<p><strong>WhatsApp Botón:</strong><br>";
echo "URL: https://api.whatsapp.com/send/?phone=" . htmlspecialchars($whatsapp_botton) . "&text=Hola<br>";
echo "Link: <a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($whatsapp_botton) . "&text=Hola' target='_blank'>Probar WhatsApp Botón</a></p>";

echo "<p><strong>Teléfono Principal:</strong><br>";
$tel_clean = str_replace(' ', '', $telefono_principal);
echo "URL: tel:" . htmlspecialchars($tel_clean) . "<br>";
echo "Link: <a href='tel:" . htmlspecialchars($tel_clean) . "'>Probar Teléfono Principal</a></p>";

echo "<p><strong>Teléfono Botón:</strong><br>";
$tel_clean2 = str_replace(' ', '', $telefono_botton);
echo "URL: tel:" . htmlspecialchars($tel_clean2) . "<br>";
echo "Link: <a href='tel:" . htmlspecialchars($tel_clean2) . "'>Probar Teléfono Botón</a></p>";

// Test 5: Verificar archivos PHP
echo "<h2>5. Verificar Archivos PHP</h2>";

$archivos_verificar = [
    'detalle-propiedad.php' => 'Detalle de Propiedad',
    'corretaje.php' => 'Corretaje',
    'admin/configuracion.php' => 'Panel de Configuración'
];

foreach ($archivos_verificar as $archivo => $nombre) {
    if (file_exists($archivo)) {
        echo "✅ $nombre ($archivo) existe<br>";
    } else {
        echo "❌ $nombre ($archivo) NO existe<br>";
    }
}

// Test 6: Instrucciones paso a paso
echo "<h2>6. Instrucciones Paso a Paso</h2>";
echo "<ol>";
echo "<li>✅ La base de datos está configurada</li>";
echo "<li>🔧 Ve a <a href='admin/configuracion.php' target='_blank'>Panel de Configuración</a></li>";
echo "<li>📝 Cambia los números según necesites</li>";
echo "<li>💾 Guarda los cambios</li>";
echo "<li>🧪 Prueba los botones en <a href='corretaje.php' target='_blank'>Corretaje</a></li>";
echo "<li>📱 Verifica que los enlaces funcionen en tu dispositivo</li>";
echo "</ol>";

echo "<h3>🎯 Enlaces de Prueba Rápida:</h3>";
echo "<p><a href='admin/configuracion.php' target='_blank'>🔧 Panel de Configuración</a></p>";
echo "<p><a href='corretaje.php' target='_blank'>🏠 Página de Corretaje</a></p>";
if (file_exists('detalle-propiedad.php')) {
    echo "<p><a href='detalle-propiedad.php?id=1' target='_blank'>📄 Detalle de Propiedad (ID=1)</a></p>";
}

echo "<hr>";
echo "<p><strong>Diagnóstico completado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>