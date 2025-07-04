<?php
// Script para probar configuración de email

echo "<h3>Probando configuración de email...</h3>";

// Mostrar configuración actual
echo "<p><strong>Configuración PHP actual:</strong></p>";
echo "SMTP: " . ini_get('SMTP') . "<br>";
echo "smtp_port: " . ini_get('smtp_port') . "<br>";
echo "sendmail_from: " . ini_get('sendmail_from') . "<br>";

// Configurar SMTP para Gmail
ini_set('SMTP', 'smtp.gmail.com');
ini_set('smtp_port', '587');
ini_set('sendmail_from', 'flanker771@gmail.com');

echo "<p><strong>Nueva configuración:</strong></p>";
echo "SMTP: " . ini_get('SMTP') . "<br>";
echo "smtp_port: " . ini_get('smtp_port') . "<br>";
echo "sendmail_from: " . ini_get('sendmail_from') . "<br>";

// Probar envío simple
$to = 'flanker771@gmail.com';
$subject = 'Prueba desde Millenium';
$message = 'Esto es una prueba del sistema de emails.';
$headers = 'From: flanker771@gmail.com';

echo "<p><strong>Intentando enviar email...</strong></p>";

if (mail($to, $subject, $message, $headers)) {
    echo "<p style='color: green;'>✅ Email enviado exitosamente!</p>";
} else {
    echo "<p style='color: red;'>❌ Error al enviar email</p>";
    
    // Información adicional para debug
    $error = error_get_last();
    if ($error) {
        echo "<p>Error: " . $error['message'] . "</p>";
    }
}

echo "<p><strong>Nota:</strong> Para que funcione completamente necesitas una contraseña de aplicación de Gmail.</p>";
?>