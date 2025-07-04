<?php
require_once 'config.php';
verificarLogin();

// Obtener estadísticas
$pdo = conectarDB();

// Contar inmuebles por estado
$stmt = $pdo->query("SELECT estado, COUNT(*) as cantidad FROM inmuebles GROUP BY estado");
$estadisticas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Contar total de inmuebles
$stmt = $pdo->query("SELECT COUNT(*) FROM inmuebles");
$total_inmuebles = $stmt->fetchColumn();

// Contar total de consultas
$stmt = $pdo->query("SELECT COUNT(*) FROM consultas");
$total_consultas = $stmt->fetchColumn();

// Inmuebles recientes
$stmt = $pdo->query("SELECT * FROM inmuebles ORDER BY fecha_publicacion DESC LIMIT 5");
$inmuebles_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultas recientes
$stmt = $pdo->query("
    SELECT c.*, i.titulo as inmueble_titulo 
    FROM consultas c 
    LEFT JOIN inmuebles i ON c.inmueble_id = i.id 
    ORDER BY c.fecha_consulta DESC 
    LIMIT 5
");
$consultas_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Millenium Admin</title>
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
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: var(--surface-color);
            border-bottom: none;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8fa076 100%);
            color: white;
        }
        
        .stat-card-secondary {
            background: linear-gradient(135deg, var(--accent-color) 0%, #c7734f 100%);
            color: white;
        }
        
        .stat-card-info {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #48415a 100%);
            color: white;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
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
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 10px;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                            <a class="nav-link active" href="dashboard.php">
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
                                <?php if ($total_consultas > 0): ?>
                                    <span class="badge ms-1" style="background-color: var(--accent-color);"><?php echo $total_consultas; ?></span>
                                <?php endif; ?>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <div>
                            <a href="inmuebles.php?action=add" class="btn" style="background-color: var(--accent-color); border-color: var(--accent-color); color: white; border-radius: 10px; font-weight: 600;">
                                <i class="bi bi-plus-circle"></i> Agregar Inmueble
                            </a>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo $total_inmuebles; ?></div>
                                    <div>Total Inmuebles</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card-secondary">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo $estadisticas['activo'] ?? 0; ?></div>
                                    <div>Activos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stat-card-info">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo $estadisticas['vendido'] ?? 0; ?></div>
                                    <div>Vendidos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card" style="background: linear-gradient(135deg, var(--accent-color) 0%, #c7734f 100%); color: white;">
                                <div class="card-body text-center">
                                    <div class="stat-number"><?php echo $total_consultas; ?></div>
                                    <div>Total Consultas</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consultas Recientes -->
                    <div class="row mb-4">
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-envelope"></i> Consultas Recientes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($consultas_recientes)): ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-envelope text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No hay consultas</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($consultas_recientes as $consulta): ?>
                                            <div class="border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($consulta['nombre']); ?></h6>
                                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($consulta['fecha_consulta'])); ?></small>
                                                </div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong class="me-2">Email:</strong>
                                                    <span class="me-2"><?php echo htmlspecialchars($consulta['email']); ?></span>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                            onclick="copiarTexto('<?php echo htmlspecialchars($consulta['email']); ?>')"
                                                            title="Copiar email">
                                                        <i class="bi bi-copy"></i>
                                                    </button>
                                                </div>
                                                <?php if ($consulta['telefono']): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <strong class="me-2">Teléfono:</strong>
                                                        <span class="me-2"><?php echo htmlspecialchars($consulta['telefono']); ?></span>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                                onclick="copiarTexto('<?php echo htmlspecialchars($consulta['telefono']); ?>')"
                                                                title="Copiar teléfono">
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                                <p class="text-muted small mb-0">
                                                    <?php echo htmlspecialchars(substr($consulta['mensaje'], 0, 100)) . '...'; ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="text-center">
                                            <a href="consultas.php" class="btn btn-sm" style="border: 2px solid var(--accent-color); color: var(--accent-color);" 
                                               onmouseover="this.style.backgroundColor='var(--accent-color)'; this.style.color='white';" 
                                               onmouseout="this.style.backgroundColor='transparent'; this.style.color='var(--accent-color)';">
                                                Ver Todas las Consultas
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <!-- Inmuebles Recientes -->
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clock-history"></i> Inmuebles Recientes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($inmuebles_recientes)): ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">No hay inmuebles registrados</p>
                                            <a href="inmuebles.php?action=add" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Agregar Primer Inmueble
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($inmuebles_recientes as $inmueble): ?>
                                            <div class="border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($inmueble['titulo']); ?></h6>
                                                        <p class="text-muted small mb-1"><?php echo formatearPrecio($inmueble['precio'], $inmueble['moneda']); ?></p>
                                                        <div class="d-flex gap-2">
                                                            <span class="badge bg-secondary"><?php echo ucfirst($inmueble['tipo_propiedad']); ?></span>
                                                            <?php
                                                            $badge_class = 'bg-success';
                                                            if ($inmueble['estado'] == 'vendido') $badge_class = 'bg-danger';
                                                            if ($inmueble['estado'] == 'suspendido') $badge_class = 'bg-warning';
                                                            ?>
                                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($inmueble['estado']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="ms-2">
                                                        <a href="inmuebles.php?action=edit&id=<?php echo $inmueble['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="text-center">
                                            <a href="inmuebles.php" class="btn btn-outline-primary btn-sm">
                                                Ver Todos los Inmuebles
                                            </a>
                                        </div>
                                    <?php endif; ?>
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