<?php
require_once 'config.php';
require_once 'email_config.php';
verificarLogin();

$pdo = conectarDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'delete' && $id) {
        try {
            // Verificar que la consulta existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM consultas WHERE id = ?");
            $stmt->execute([$id]);
            $exists = $stmt->fetchColumn();
            
            if ($exists) {
                $stmt = $pdo->prepare("DELETE FROM consultas WHERE id = ?");
                $result = $stmt->execute([$id]);
                
                if ($result) {
                    // Usar JavaScript para redirección inmediata
                    echo '<!DOCTYPE html><html><head><script>window.location.href = "consultas.php?success=' . urlencode('Consulta eliminada exitosamente') . '";</script></head><body>Redirigiendo...</body></html>';
                    exit();
                } else {
                    echo '<!DOCTYPE html><html><head><script>window.location.href = "consultas.php?error=' . urlencode('Error al eliminar la consulta') . '";</script></head><body>Redirigiendo...</body></html>';
                    exit();
                }
            } else {
                echo '<!DOCTYPE html><html><head><script>window.location.href = "consultas.php?error=' . urlencode('La consulta no existe') . '";</script></head><body>Redirigiendo...</body></html>';
                exit();
            }
        } catch (Exception $e) {
            echo '<!DOCTYPE html><html><head><script>window.location.href = "consultas.php?error=' . urlencode('Error: ' . $e->getMessage()) . '";</script></head><body>Redirigiendo...</body></html>';
            exit();
        }
    }
}

// Obtener consultas para listado
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    
    $where = "WHERE 1=1";
    $params = [];
    
    if ($search) {
        $where .= " AND (c.nombre LIKE ? OR c.email LIKE ? OR i.titulo LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare("
        SELECT c.*, i.titulo as inmueble_titulo 
        FROM consultas c 
        LEFT JOIN inmuebles i ON c.inmueble_id = i.id 
        $where 
        ORDER BY c.fecha_consulta DESC
    ");
    $stmt->execute($params);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action == 'respond' ? 'Responder' : 'Gestionar'; ?> Consultas - Millenium Admin</title>
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
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background-color: var(--surface-color);
        }
        
        .badge {
            border-radius: 10px;
            font-weight: 500;
        }
        
        .main-content {
            padding: 2rem;
        }
        
        .consulta-card {
            transition: transform 0.3s ease;
        }
        
        .consulta-card:hover {
            transform: translateY(-5px);
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
                            <a class="nav-link active" href="consultas.php">
                                <i class="bi bi-envelope"></i> Consultas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="configuracion.php">
                                <i class="bi bi-gear"></i> Configuración
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">

                    <?php if ($action == 'list'): ?>
                        <!-- Listado de Consultas -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">Gestión de Consultas</h1>
                            <div class="d-flex gap-2">
                                <span class="badge" style="background-color: var(--accent-color); color: white; font-size: 0.9rem; padding: 8px 12px;">
                                    <i class="bi bi-envelope"></i>
                                    <?php echo count($consultas); ?> Consultas Totales
                                </span>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="consultas.php">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <input type="text" class="form-control" name="search" 
                                                   placeholder="Buscar por nombre, email o propiedad..."
                                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Lista de Consultas -->
                        <?php if (empty($consultas)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-envelope text-muted" style="font-size: 4rem;"></i>
                                    <h4 class="text-muted mt-3">No hay consultas</h4>
                                    <p class="text-muted">Las consultas de propiedades aparecerán aquí</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($consultas as $consulta): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card consulta-card h-100">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">
                                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($consulta['nombre']); ?>
                                                </h6>
                                                <span class="badge bg-info">
                                                    <i class="bi bi-clock"></i> Nueva
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-2">
                                                    <strong>Propiedad:</strong> 
                                                    <span class="text-muted"><?php echo htmlspecialchars($consulta['inmueble_titulo'] ?? 'Propiedad eliminada'); ?></span>
                                                </div>
                                                <div class="mb-2 d-flex align-items-center">
                                                    <strong class="me-2">Email:</strong> 
                                                    <span class="me-2"><?php echo htmlspecialchars($consulta['email']); ?></span>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                            onclick="copiarTexto('<?php echo htmlspecialchars($consulta['email']); ?>')"
                                                            title="Copiar email">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                </div>
                                                <?php if ($consulta['telefono']): ?>
                                                    <div class="mb-2 d-flex align-items-center">
                                                        <strong class="me-2">Teléfono:</strong> 
                                                        <span class="me-2"><?php echo htmlspecialchars($consulta['telefono']); ?></span>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                                onclick="copiarTexto('<?php echo htmlspecialchars($consulta['telefono']); ?>')"
                                                                title="Copiar teléfono">
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mb-3">
                                                    <strong>Mensaje:</strong>
                                                    <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($consulta['mensaje'])); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('d/m/Y H:i', strtotime($consulta['fecha_consulta'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <div class="d-flex gap-2">
                                                    <div class="flex-fill">
                                                        <a href="mailto:<?php echo htmlspecialchars($consulta['email']); ?>?subject=Re: Consulta sobre <?php echo htmlspecialchars($consulta['inmueble_titulo'] ?? 'Propiedad'); ?>&body=Hola <?php echo htmlspecialchars($consulta['nombre']); ?>,%0D%0A%0D%0AGracias por tu consulta sobre la propiedad.%0D%0A%0D%0ASaludos cordiales,%0D%0AEquipo Millenium" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-envelope"></i> Responder
                                                        </a>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="mostrarModalEliminar(<?php echo $consulta['id']; ?>, 'consulta')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer">
        <!-- Los toasts se insertarán aquí dinámicamente -->
    </div>

    <!-- Modal de Eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <div class="modal-header" style="background-color: var(--surface-color); border-bottom: none; border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title" id="modalEliminarLabel" style="color: var(--secondary-color);">
                        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <i class="bi bi-trash text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <p class="mb-0" id="mensajeEliminar"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <form method="POST" id="formEliminar" style="display: inline;">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para mostrar toast
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();
            
            const toastHtml = `
                <div class="toast toast-${type}" role="alert" id="${toastId}" data-bs-delay="1000">
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
            
            // Mostrar toast con animación
            toast.show();
            
            // Remover del DOM después de que se oculte
            toastElement.addEventListener('hidden.bs.toast', function() {
                toastElement.remove();
            });
        }

        // Verificar si hay mensajes de URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            showToast(urlParams.get('success'), 'success');
            // Limpiar URL sin recargar la página
            const newUrl = window.location.pathname + (window.location.search.replace(/[?&]success=[^&]*/, '').replace(/^&/, '?') || '');
            window.history.replaceState({}, '', newUrl);
        }
        if (urlParams.has('error')) {
            showToast(urlParams.get('error'), 'error');
            // Limpiar URL sin recargar la página
            const newUrl = window.location.pathname + (window.location.search.replace(/[?&]error=[^&]*/, '').replace(/^&/, '?') || '');
            window.history.replaceState({}, '', newUrl);
        }

        // Función para mostrar modal de eliminación
        function mostrarModalEliminar(id, tipo) {
            const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            const mensaje = document.getElementById('mensajeEliminar');
            const form = document.getElementById('formEliminar');
            
            if (tipo === 'consulta') {
                mensaje.textContent = '¿Estás seguro de que deseas eliminar esta consulta? Esta acción no se puede deshacer.';
                form.action = `consultas.php?action=delete&id=${id}`;
            }
            
            modal.show();
        }

        // Función para copiar texto al portapapeles
        function copiarTexto(texto) {
            navigator.clipboard.writeText(texto).then(function() {
                showToast('Texto copiado al portapapeles', 'success');
            }).catch(function(err) {
                // Fallback para navegadores antiguos
                const textArea = document.createElement('textarea');
                textArea.value = texto;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showToast('Texto copiado al portapapeles', 'success');
                } catch (err) {
                    showToast('No se pudo copiar el texto', 'error');
                }
                document.body.removeChild(textArea);
            });
        }
    </script>
</body>
</html>