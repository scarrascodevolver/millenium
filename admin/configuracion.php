<?php
require_once 'config.php';
verificarLogin();

$pdo = conectarDB();

// Procesar formulario de configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefono_principal = limpiarDatos($_POST['telefono_principal']);
    $whatsapp_principal = limpiarDatos($_POST['whatsapp_principal']);
    $telefono = limpiarDatos($_POST['telefono']);
    $whatsapp = limpiarDatos($_POST['whatsapp']);
    $email = limpiarDatos($_POST['email']);
    
    if (!empty($telefono_principal) && !empty($whatsapp_principal) && !empty($telefono) && !empty($whatsapp) && !empty($email)) {
        try {
            // Verificar si ya existe configuración
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM configuracion WHERE id = 1");
            $stmt->execute();
            $existe = $stmt->fetchColumn();
            
            if ($existe) {
                // Actualizar configuración existente
                $stmt = $pdo->prepare("
                    UPDATE configuracion SET 
                        telefono_principal = ?,
                        whatsapp_principal = ?,
                        telefono = ?, 
                        whatsapp = ?, 
                        email = ?, 
                        fecha_actualizacion = NOW() 
                    WHERE id = 1
                ");
                $stmt->execute([$telefono_principal, $whatsapp_principal, $telefono, $whatsapp, $email]);
            } else {
                // Crear nueva configuración
                $stmt = $pdo->prepare("
                    INSERT INTO configuracion (id, telefono_principal, whatsapp_principal, telefono, whatsapp, email, fecha_creacion, fecha_actualizacion) 
                    VALUES (1, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$telefono_principal, $whatsapp_principal, $telefono, $whatsapp, $email]);
            }
            
            $success = "Configuración actualizada exitosamente";
        } catch (Exception $e) {
            $error = "Error al actualizar la configuración: " . $e->getMessage();
        }
    } else {
        $error = "Por favor complete todos los campos";
    }
}

// Obtener configuración actual
try {
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si no existe configuración, usar valores por defecto
    if (!$config) {
        $config = [
            'telefono_principal' => '+41 2799626',
            'whatsapp_principal' => '56932385980',
            'telefono' => '+569 56287856',
            'whatsapp' => '56932385980',
            'email' => 'info@millenium.cl'
        ];
    }
} catch (Exception $e) {
    $config = [
        'telefono_principal' => '+41 2799626',
        'whatsapp_principal' => '56932385980',
        'telefono' => '+569 56287856',
        'whatsapp' => '56932385980',
        'email' => 'info@millenium.cl'
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Millenium Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #A5B68D;
            --secondary-color: #574964;
            --accent-color: #DA8359;
            --surface-color: #ECDFCC;
            --background-color: #F9F6E6;
        }
        
        body {
            background-color: var(--background-color);
            font-family: 'Roboto', sans-serif;
        }
        
        .navbar {
            background-color: var(--secondary-color) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .nav-pills .nav-link {
            color: var(--secondary-color);
            border-radius: 10px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link:hover {
            background-color: var(--surface-color);
            color: var(--secondary-color);
        }
        
        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-header {
            background-color: var(--surface-color);
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: #8fa076;
            border-color: #8fa076;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(165, 182, 141, 0.25);
        }
        
        .main-content {
            padding: 2rem;
        }
        
        /* Toast Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1055;
        }
        
        .toast {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            min-width: 300px;
        }
        
        .toast-success {
            background-color: var(--primary-color);
            color: white;
        }
        
        .toast-success .toast-header {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .toast-error {
            background-color: #dc3545;
            color: white;
        }
        
        .toast-error .toast-header {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .toast .btn-close {
            filter: invert(1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../assets/img/logofinal.png" alt="Millenium" style="height: 40px; margin-right: 10px;">
                Millenium Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a href="../index.html" class="nav-link me-3" style="background-color: var(--primary-color); color: white; border-radius: 20px; padding: 8px 16px;">
                    <i class="bi bi-house"></i> Ir al Sitio Web
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo $_SESSION['usuario_nombre']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="inmuebles.php">
                                <i class="bi bi-buildings"></i> Inmuebles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="consultas.php">
                                <i class="bi bi-envelope"></i> Consultas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">Configuración del Sitio</h1>
                    </div>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-telephone"></i> Información de Contacto</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <h6 class="text-primary mb-3">📍 Números Principales (Header/Footer)</h6>
                                        
                                        <div class="mb-3">
                                            <label for="telefono_principal" class="form-label">Teléfono Principal *</label>
                                            <input type="text" class="form-control" id="telefono_principal" name="telefono_principal" 
                                                   value="<?php echo htmlspecialchars($config['telefono_principal'] ?? '+41 2799626'); ?>" 
                                                   placeholder="+41 2799626" required>
                                            <div class="form-text">Número que aparece en el header y footer del sitio</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="whatsapp_principal" class="form-label">WhatsApp Principal *</label>
                                            <input type="text" class="form-control" id="whatsapp_principal" name="whatsapp_principal" 
                                                   value="<?php echo htmlspecialchars($config['whatsapp_principal'] ?? '56932385980'); ?>" 
                                                   placeholder="56932385980" required>
                                            <div class="form-text">WhatsApp del header (solo números, sin espacios)</div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        <h6 class="text-success mb-3">📞 Números de Contacto Directo (Sidebar Detalles)</h6>
                                        
                                        <div class="mb-3">
                                            <label for="telefono" class="form-label">Teléfono para Botón "Llamar" *</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" 
                                                   value="<?php echo htmlspecialchars($config['telefono'] ?? '+569 56287856'); ?>" 
                                                   placeholder="+569 56287856" required>
                                            <div class="form-text">Número para el botón "Llamar" en el sidebar de detalles de propiedades</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="whatsapp" class="form-label">WhatsApp para Botón "Contactar por WhatsApp" *</label>
                                            <input type="text" class="form-control" id="whatsapp" name="whatsapp" 
                                                   value="<?php echo htmlspecialchars($config['whatsapp'] ?? '56932385980'); ?>" 
                                                   placeholder="56932385980" required>
                                            <div class="form-text">WhatsApp para botón "Contactar por WhatsApp" en el sidebar de detalles de propiedades (solo números)</div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email de Contacto *</label>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($config['email'] ?? 'info@millenium.cl'); ?>" 
                                                   placeholder="info@millenium.cl" required>
                                            <div class="form-text">Email que aparece en el footer y páginas de contacto</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Guardar Cambios
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información</h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <small>
                                            <strong>Nota:</strong> Los cambios se aplicarán en todas las páginas del sitio web donde aparezca información de contacto.
                                        </small>
                                    </div>
                                    
                                    <h6>Vista Previa:</h6>
                                    <div class="border rounded p-3 bg-light mb-3">
                                        <small>
                                            <strong>📍 Header/Footer:</strong><br>
                                            Tel: <?php echo htmlspecialchars($config['telefono_principal'] ?? '+41 2799626'); ?><br>
                                            WhatsApp: +<?php echo htmlspecialchars($config['whatsapp_principal'] ?? '56932385980'); ?><br><br>
                                            
                                            <strong>📞 Sidebar Detalles de Propiedades:</strong><br>
                                            Botón "Llamar": <?php echo htmlspecialchars($config['telefono'] ?? '+569 56287856'); ?><br>
                                            Botón "Contactar por WhatsApp": +<?php echo htmlspecialchars($config['whatsapp'] ?? '56932385980'); ?><br><br>
                                            
                                            <strong>📧 Email:</strong> <?php echo htmlspecialchars($config['email'] ?? 'info@millenium.cl'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer">
        <!-- Los toasts se insertarán aquí dinámicamente -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para validar formato de teléfono
        function validarTelefono(telefono) {
            // Permitir números con o sin espacios, guiones y paréntesis
            const regex = /^[\+]?[0-9\s\-\(\)]{7,20}$/;
            return regex.test(telefono);
        }

        // Función para validar formato de WhatsApp (solo números)
        function validarWhatsApp(whatsapp) {
            // Solo números, sin espacios ni caracteres especiales
            const regex = /^[0-9]{8,15}$/;
            return regex.test(whatsapp);
        }

        // Función para mostrar toast
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <div class="toast toast-${type}" role="alert" id="${toastId}" data-bs-delay="5000">
                    <div class="toast-header">
                        <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Éxito' : 'Error'}</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            
            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement);
            
            toast.show();
            
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Verificar si hay mensajes
        <?php if (isset($success)): ?>
            showToast('<?php echo $success; ?>', 'success');
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            showToast('<?php echo $error; ?>', 'error');
        <?php endif; ?>

        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const telefonoPrincipal = document.getElementById('telefono_principal').value;
            const whatsappPrincipal = document.getElementById('whatsapp_principal').value;
            const telefono = document.getElementById('telefono').value;
            const whatsapp = document.getElementById('whatsapp').value;
            
            let errores = [];
            
            if (!validarTelefono(telefonoPrincipal)) {
                errores.push('Teléfono Principal: formato inválido');
            }
            
            if (!validarWhatsApp(whatsappPrincipal)) {
                errores.push('WhatsApp Principal: debe contener solo números (8-15 dígitos)');
            }
            
            if (!validarTelefono(telefono)) {
                errores.push('Teléfono para botón "Llamar": formato inválido');
            }
            
            if (!validarWhatsApp(whatsapp)) {
                errores.push('WhatsApp para botón: debe contener solo números (8-15 dígitos)');
            }
            
            if (errores.length > 0) {
                e.preventDefault();
                showToast('Errores de validación:<br>• ' + errores.join('<br>• '), 'error');
            }
        });

        // Validación en tiempo real
        document.getElementById('whatsapp_principal').addEventListener('input', function() {
            const valor = this.value;
            if (valor && !validarWhatsApp(valor)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('whatsapp').addEventListener('input', function() {
            const valor = this.value;
            if (valor && !validarWhatsApp(valor)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>