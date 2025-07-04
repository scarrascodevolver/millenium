<?php
require_once 'admin/config.php';

echo "<h2>🎯 Aplicando Contactos Específicos por Sección</h2>";

try {
    $pdo = conectarDB();
    
    // Obtener configuración
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$config) {
        echo "❌ No hay configuración. Ejecuta setup_contactos_avanzados.php primero<br>";
        exit;
    }
    
    // Verificar si existen las nuevas columnas
    if (!isset($config['email_corretaje'])) {
        echo "❌ Faltan columnas nuevas. Ejecuta setup_contactos_avanzados.php primero<br>";
        exit;
    }
    
    echo "<h3>🔄 Actualizando archivos...</h3>";
    
    // 1. ACTUALIZAR CORRETAJE.PHP para usar contactos de corretaje
    echo "<h4>📝 Actualizando corretaje.php...</h4>";
    
    $corretaje_content = file_get_contents('corretaje.php');
    
    // Cambiar el footer para usar contactos de corretaje
    $old_footer = '<p class="mt-4"><strong>Telefono:</strong> <span><?php echo htmlspecialchars($config[\'telefono\']); ?></span></p>
          <p><strong>Email:</strong> <span><?php echo htmlspecialchars($config[\'email\']); ?></span></p>';
    
    $new_footer = '<p class="mt-4"><strong>Telefono Corretaje:</strong> <span><?php echo htmlspecialchars($config[\'telefono_corretaje\']); ?></span></p>
          <p><strong>Email Corretaje:</strong> <span><?php echo htmlspecialchars($config[\'email_corretaje\']); ?></span></p>';
    
    if (strpos($corretaje_content, $old_footer) !== false) {
        $corretaje_content = str_replace($old_footer, $new_footer, $corretaje_content);
        file_put_contents('corretaje.php', $corretaje_content);
        echo "✅ Footer de corretaje.php actualizado<br>";
    } else {
        echo "ℹ️ Footer de corretaje.php ya está actualizado o no se encontró<br>";
    }
    
    // 2. ACTUALIZAR DETALLE-PROPIEDAD.PHP para usar contactos de corretaje
    echo "<h4>📝 Actualizando detalle-propiedad.php...</h4>";
    
    $detalle_content = file_get_contents('detalle-propiedad.php');
    
    // Cambiar los botones de contacto para usar contactos de corretaje
    $old_whatsapp_btn = 'href="https://api.whatsapp.com/send/?phone=<?php echo htmlspecialchars($config[\'whatsapp\']); ?>';
    $new_whatsapp_btn = 'href="https://api.whatsapp.com/send/?phone=<?php echo htmlspecialchars($config[\'whatsapp_corretaje\']); ?>';
    
    $old_tel_btn = 'href="tel:<?php echo htmlspecialchars($config[\'telefono\']); ?>"';
    $new_tel_btn = 'href="tel:<?php echo htmlspecialchars($config[\'telefono_corretaje\']); ?>"';
    
    // Cambiar footer también
    if (strpos($detalle_content, $old_footer) !== false) {
        $detalle_content = str_replace($old_footer, $new_footer, $detalle_content);
        echo "✅ Footer de detalle-propiedad.php actualizado<br>";
    }
    
    if (strpos($detalle_content, $old_whatsapp_btn) !== false) {
        $detalle_content = str_replace($old_whatsapp_btn, $new_whatsapp_btn, $detalle_content);
        echo "✅ Botón WhatsApp de detalle-propiedad.php actualizado<br>";
    }
    
    if (strpos($detalle_content, $old_tel_btn) !== false) {
        $detalle_content = str_replace($old_tel_btn, $new_tel_btn, $detalle_content);
        echo "✅ Botón teléfono de detalle-propiedad.php actualizado<br>";
    }
    
    file_put_contents('detalle-propiedad.php', $detalle_content);
    
    echo "<h3>📊 Resumen de Contactos por Sección:</h3>";
    
    echo "<h4>🌐 PÁGINAS GENERALES (Header/Footer)</h4>";
    echo "<ul>";
    echo "<li><strong>index.html:</strong> " . htmlspecialchars($config['telefono_principal']) . " | " . htmlspecialchars($config['email']) . "</li>";
    echo "<li><strong>Headers:</strong> " . htmlspecialchars($config['telefono_principal']) . " | " . htmlspecialchars($config['whatsapp_principal']) . "</li>";
    echo "</ul>";
    
    echo "<h4>🏠 PÁGINAS DE CORRETAJE</h4>";
    echo "<ul>";
    echo "<li><strong>corretaje.php (footer):</strong> " . htmlspecialchars($config['telefono_corretaje']) . " | " . htmlspecialchars($config['email_corretaje']) . "</li>";
    echo "<li><strong>detalle-propiedad.php (botones):</strong> " . htmlspecialchars($config['telefono_corretaje']) . " | " . htmlspecialchars($config['whatsapp_corretaje']) . "</li>";
    echo "<li><strong>detalle-propiedad.php (footer):</strong> " . htmlspecialchars($config['telefono_corretaje']) . " | " . htmlspecialchars($config['email_corretaje']) . "</li>";
    echo "</ul>";
    
    echo "<h3>🔗 Enlaces de Prueba por Sección:</h3>";
    
    echo "<h4>📞 Contactos Generales:</h4>";
    echo "<p><a href='tel:" . htmlspecialchars(str_replace(' ', '', $config['telefono_principal'])) . "'>📞 " . htmlspecialchars($config['telefono_principal']) . "</a></p>";
    echo "<p><a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp_principal']) . "&text=Hola' target='_blank'>💬 WhatsApp General</a></p>";
    echo "<p><a href='mailto:" . htmlspecialchars($config['email']) . "'>📧 " . htmlspecialchars($config['email']) . "</a></p>";
    
    echo "<h4>🏠 Contactos Corretaje:</h4>";
    echo "<p><a href='tel:" . htmlspecialchars(str_replace(' ', '', $config['telefono_corretaje'])) . "'>📞 " . htmlspecialchars($config['telefono_corretaje']) . "</a></p>";
    echo "<p><a href='https://api.whatsapp.com/send/?phone=" . htmlspecialchars($config['whatsapp_corretaje']) . "&text=Hola,%20me%20interesa%20una%20propiedad' target='_blank'>💬 WhatsApp Corretaje</a></p>";
    echo "<p><a href='mailto:" . htmlspecialchars($config['email_corretaje']) . "'>📧 " . htmlspecialchars($config['email_corretaje']) . "</a></p>";
    
    echo "<h3>🎯 Próximos Pasos:</h3>";
    echo "<ol>";
    echo "<li>✅ Base de datos actualizada con contactos por sección</li>";
    echo "<li>✅ Archivos PHP actualizados</li>";
    echo "<li>🔄 <a href='actualizar_panel_admin.php'>Actualizar Panel de Administración</a></li>";
    echo "<li>🧪 <a href='corretaje.php'>Probar página de Corretaje</a></li>";
    echo "<li>🧪 <a href='detalle-propiedad.php?id=1'>Probar Detalle de Propiedad</a></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>