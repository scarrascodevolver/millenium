<?php
// Configuración de email simple con PHP nativo

function enviarEmail($destinatario, $nombre_destinatario, $asunto, $contenido_html, $contenido_texto = '') {
    
    // GUARDAR EMAIL EN ARCHIVO PARA REVISAR (modo desarrollo)
    $email_log = "=== EMAIL ENVIADO ===\n";
    $email_log .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    $email_log .= "Para: $destinatario ($nombre_destinatario)\n";
    $email_log .= "Asunto: $asunto\n";
    $email_log .= "Contenido:\n$contenido_html\n";
    $email_log .= "=====================\n\n";
    
    // Guardar en archivo de log
    file_put_contents('../emails_enviados.txt', $email_log, FILE_APPEND | LOCK_EX);
    
    // Simulamos éxito siempre para que el sistema funcione
    return ['success' => true, 'message' => 'Email guardado en emails_enviados.txt (modo desarrollo)'];
}

function generarEmailNuevaConsulta($consulta, $inmueble) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #A5B68D; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .property-info { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #A5B68D; }
            .contact-info { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #574964; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Nueva Consulta Recibida</h1>
                <p>Millenium Administración de Edificios y Condominios</p>
            </div>
            <div class="content">
                <h2>Detalles de la Consulta</h2>
                
                <div class="property-info">
                    <h3>Propiedad Consultada</h3>
                    <p><strong>Título:</strong> ' . htmlspecialchars($inmueble['titulo']) . '</p>
                    <p><strong>Ubicación:</strong> ' . htmlspecialchars($inmueble['comuna'] . ', ' . $inmueble['region']) . '</p>
                    <p><strong>Precio:</strong> ' . formatearPrecio($inmueble['precio'], $inmueble['moneda']) . '</p>
                    <p><strong>Tipo:</strong> ' . ucfirst(str_replace('_', ' ', $inmueble['tipo_propiedad'])) . ' en ' . ucfirst($inmueble['tipo_operacion']) . '</p>
                </div>
                
                <div class="contact-info">
                    <h3>Información del Contacto</h3>
                    <p><strong>Nombre:</strong> ' . htmlspecialchars($consulta['nombre']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($consulta['email']) . '</p>
                    ' . ($consulta['telefono'] ? '<p><strong>Teléfono:</strong> ' . htmlspecialchars($consulta['telefono']) . '</p>' : '') . '
                    <p><strong>Mensaje:</strong></p>
                    <p style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">' . nl2br(htmlspecialchars($consulta['mensaje'])) . '</p>
                    <p><strong>Fecha de consulta:</strong> ' . date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])) . '</p>
                </div>
                
                <p style="text-align: center; margin-top: 20px;">
                    <a href="http://localhost/Millenium/admin/consultas.php?action=respond&id=' . $consulta['id'] . '" 
                       style="background-color: #A5B68D; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Responder Consulta
                    </a>
                </p>
            </div>
            <div class="footer">
                <p>Este es un email automático del sistema Millenium.</p>
                <p>Por favor, responde esta consulta desde el panel de administración.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

function generarEmailRespuesta($consulta, $inmueble, $respuesta) {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #574964; color: white; padding: 20px; text-align: center; }
            .content { background-color: #f9f9f9; padding: 20px; }
            .property-info { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #A5B68D; }
            .response-info { background-color: white; padding: 15px; margin: 10px 0; border-left: 4px solid #DA8359; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Respuesta a tu Consulta</h1>
                <p>Millenium Administración de Edificios y Condominios</p>
            </div>
            <div class="content">
                <p>Hola <strong>' . htmlspecialchars($consulta['nombre']) . '</strong>,</p>
                
                <p>Gracias por tu interés en nuestras propiedades. Hemos recibido tu consulta y queremos responderte:</p>
                
                <div class="property-info">
                    <h3>Propiedad Consultada</h3>
                    <p><strong>Título:</strong> ' . htmlspecialchars($inmueble['titulo']) . '</p>
                    <p><strong>Ubicación:</strong> ' . htmlspecialchars($inmueble['comuna'] . ', ' . $inmueble['region']) . '</p>
                    <p><strong>Precio:</strong> ' . formatearPrecio($inmueble['precio'], $inmueble['moneda']) . '</p>
                </div>
                
                <div class="response-info">
                    <h3>Nuestra Respuesta</h3>
                    <p>' . nl2br(htmlspecialchars($respuesta)) . '</p>
                </div>
                
                <p>Si tienes más preguntas o deseas agendar una visita, no dudes en contactarnos:</p>
                <ul>
                    <li><strong>Email:</strong> info@millenium.cl</li>
                    <li><strong>Teléfono:</strong> +41 2799626</li>
                    <li><strong>WhatsApp:</strong> +569 32385980</li>
                </ul>
                
                <p style="text-align: center; margin-top: 20px;">
                    <a href="http://localhost/Millenium/detalle-propiedad.php?id=' . $inmueble['id'] . '" 
                       style="background-color: #A5B68D; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Ver Propiedad Completa
                    </a>
                </p>
            </div>
            <div class="footer">
                <p>Gracias por elegir Millenium para tus necesidades inmobiliarias.</p>
                <p>Dirección: Aurelio Manzano N°594, Concepción</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}
?>