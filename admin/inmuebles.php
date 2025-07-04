<?php
require_once 'config.php';
verificarLogin();

$pdo = conectarDB();
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $titulo = limpiarDatos($_POST['titulo']);
        $descripcion = limpiarDatos($_POST['descripcion']);
        $precio = floatval($_POST['precio']);
        $moneda = $_POST['moneda'];
        $tipo_propiedad = $_POST['tipo_propiedad'];
        $tipo_operacion = $_POST['tipo_operacion'];
        $region = limpiarDatos($_POST['region_nombre']);
        $comuna = limpiarDatos($_POST['comuna']);
        $sector = limpiarDatos($_POST['sector']);
        $direccion = limpiarDatos($_POST['direccion']);
        $superficie_construida = intval($_POST['superficie_construida']);
        $superficie_terreno = intval($_POST['superficie_terreno']);
        $dormitorios = intval($_POST['dormitorios']);
        $baños = intval($_POST['baños']);
        $estacionamientos = intval($_POST['estacionamientos']);
        $estado = $_POST['estado'];
        $usuario_id = $_SESSION['usuario_id'];
        
        // Servicios básicos
        $agua = isset($_POST['agua']) ? 1 : 0;
        $luz = isset($_POST['luz']) ? 1 : 0;
        $gas = isset($_POST['gas']) ? 1 : 0;
        $alcantarillado = isset($_POST['alcantarillado']) ? 1 : 0;
        $internet = isset($_POST['internet']) ? 1 : 0;
        $amoblado = isset($_POST['amoblado']) ? 1 : 0;
        $mascotas_permitidas = isset($_POST['mascotas_permitidas']) ? 1 : 0;
        
        $gastos_comunes = floatval($_POST['gastos_comunes']);
        
        if ($action == 'add') {
            $stmt = $pdo->prepare("
                INSERT INTO inmuebles (
                    titulo, descripcion, precio, moneda, tipo_propiedad, tipo_operacion,
                    region, comuna, sector, direccion, superficie_construida, superficie_terreno,
                    dormitorios, baños, estacionamientos, agua, luz, gas, alcantarillado,
                    internet, amoblado, mascotas_permitidas, gastos_comunes, estado, usuario_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titulo, $descripcion, $precio, $moneda, $tipo_propiedad, $tipo_operacion,
                $region, $comuna, $sector, $direccion, $superficie_construida, $superficie_terreno,
                $dormitorios, $baños, $estacionamientos, $agua, $luz, $gas, $alcantarillado,
                $internet, $amoblado, $mascotas_permitidas, $gastos_comunes, $estado, $usuario_id
            ]);
            
            $inmueble_id = $pdo->lastInsertId();
            
            // Procesar imágenes subidas
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                foreach ($_FILES['imagenes']['name'] as $key => $nombre) {
                    if ($_FILES['imagenes']['error'][$key] == 0) {
                        $archivo_temporal = [
                            'name' => $_FILES['imagenes']['name'][$key],
                            'tmp_name' => $_FILES['imagenes']['tmp_name'][$key],
                            'size' => $_FILES['imagenes']['size'][$key]
                        ];
                        
                        $ruta_imagen = subirArchivo($archivo_temporal, 'uploads/');
                        if ($ruta_imagen) {
                            $es_principal = ($key == 0) ? 1 : 0; // Primera imagen es principal
                            $stmt_img = $pdo->prepare("
                                INSERT INTO imagenes_inmuebles (inmueble_id, nombre_archivo, ruta_archivo, es_principal, orden_visualizacion) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt_img->execute([$inmueble_id, $nombre, $ruta_imagen, $es_principal, $key]);
                        }
                    }
                }
            }
            
            $success = "Inmueble agregado exitosamente";
        } else {
            $stmt = $pdo->prepare("
                UPDATE inmuebles SET 
                    titulo = ?, descripcion = ?, precio = ?, moneda = ?, tipo_propiedad = ?,
                    tipo_operacion = ?, region = ?, comuna = ?, sector = ?, direccion = ?,
                    superficie_construida = ?, superficie_terreno = ?, dormitorios = ?, baños = ?,
                    estacionamientos = ?, agua = ?, luz = ?, gas = ?, alcantarillado = ?,
                    internet = ?, amoblado = ?, mascotas_permitidas = ?, gastos_comunes = ?, estado = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $titulo, $descripcion, $precio, $moneda, $tipo_propiedad, $tipo_operacion,
                $region, $comuna, $sector, $direccion, $superficie_construida, $superficie_terreno,
                $dormitorios, $baños, $estacionamientos, $agua, $luz, $gas, $alcantarillado,
                $internet, $amoblado, $mascotas_permitidas, $gastos_comunes, $estado, $id
            ]);
            
            // Procesar nuevas imágenes si se subieron
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                // Verificar si ya existe una imagen principal
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM imagenes_inmuebles WHERE inmueble_id = ? AND es_principal = 1");
                $stmt_check->execute([$id]);
                $tiene_principal = $stmt_check->fetchColumn() > 0;
                
                foreach ($_FILES['imagenes']['name'] as $key => $nombre) {
                    if ($_FILES['imagenes']['error'][$key] == 0) {
                        $archivo_temporal = [
                            'name' => $_FILES['imagenes']['name'][$key],
                            'tmp_name' => $_FILES['imagenes']['tmp_name'][$key],
                            'size' => $_FILES['imagenes']['size'][$key]
                        ];
                        
                        $ruta_imagen = subirArchivo($archivo_temporal, 'uploads/');
                        if ($ruta_imagen) {
                            // Obtener el último orden
                            $stmt_orden = $pdo->prepare("SELECT MAX(orden_visualizacion) FROM imagenes_inmuebles WHERE inmueble_id = ?");
                            $stmt_orden->execute([$id]);
                            $ultimo_orden = $stmt_orden->fetchColumn() ?? -1;
                            
                            // Si no hay imagen principal y es la primera imagen que se sube, hacerla principal
                            $es_principal = (!$tiene_principal && $key == 0) ? 1 : 0;
                            if ($es_principal) $tiene_principal = true; // Para que las siguientes no sean principales
                            
                            $stmt_img = $pdo->prepare("
                                INSERT INTO imagenes_inmuebles (inmueble_id, nombre_archivo, ruta_archivo, es_principal, orden_visualizacion) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt_img->execute([$id, $nombre, $ruta_imagen, $es_principal, $ultimo_orden + 1]);
                        }
                    }
                }
            }
            
            $success = "Inmueble actualizado exitosamente";
        }
        
        header('Location: inmuebles.php?success=' . urlencode($success));
        exit();
    }
    
    if ($action == 'delete' && $id) {
        // Eliminar imágenes físicas del servidor
        $stmt = $pdo->prepare("SELECT ruta_archivo FROM imagenes_inmuebles WHERE inmueble_id = ?");
        $stmt->execute([$id]);
        $imagenes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($imagenes as $ruta) {
            if (file_exists('../' . $ruta)) {
                unlink('../' . $ruta);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM inmuebles WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: inmuebles.php?success=' . urlencode('Inmueble eliminado exitosamente'));
        exit();
    }
    
}

// Obtener inmuebles para listado
if ($action == 'list') {
    $search = $_GET['search'] ?? '';
    $tipo_operacion = $_GET['tipo_operacion'] ?? '';
    $estado = $_GET['estado'] ?? '';
    
    $where = "WHERE 1=1";
    $params = [];
    
    if ($search) {
        $where .= " AND (titulo LIKE ? OR descripcion LIKE ? OR comuna LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($tipo_operacion) {
        $where .= " AND tipo_operacion = ?";
        $params[] = $tipo_operacion;
    }
    
    if ($estado) {
        $where .= " AND estado = ?";
        $params[] = $estado;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM inmuebles $where ORDER BY fecha_publicacion DESC");
    $stmt->execute($params);
    $inmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener inmueble para edición
if ($action == 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM inmuebles WHERE id = ?");
    $stmt->execute([$id]);
    $inmueble = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$inmueble) {
        header('Location: inmuebles.php?error=' . urlencode('Inmueble no encontrado'));
        exit();
    }
    
    // Obtener imágenes del inmueble
    $stmt = $pdo->prepare("SELECT * FROM imagenes_inmuebles WHERE inmueble_id = ? ORDER BY orden_visualizacion, es_principal DESC");
    $stmt->execute([$id]);
    $imagenes_inmueble = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action == 'add' ? 'Agregar' : ($action == 'edit' ? 'Editar' : 'Gestionar'); ?> Inmuebles - Millenium Admin</title>
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
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
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
                            <a class="nav-link active" href="inmuebles.php">
                                <i class="bi bi-buildings"></i> Inmuebles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="consultas.php">
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
                        <!-- Listado de Inmuebles -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">Gestión de Inmuebles</h1>
                            <a href="inmuebles.php?action=add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Agregar Inmueble
                            </a>
                        </div>

                        <!-- Filtros -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="inmuebles.php">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="search" 
                                                   placeholder="Buscar por título, descripción o comuna..."
                                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="tipo_operacion">
                                                <option value="">Todas las operaciones</option>
                                                <option value="venta" <?php echo ($_GET['tipo_operacion'] ?? '') == 'venta' ? 'selected' : ''; ?>>Venta</option>
                                                <option value="arriendo" <?php echo ($_GET['tipo_operacion'] ?? '') == 'arriendo' ? 'selected' : ''; ?>>Arriendo</option>
                                                <option value="venta_arriendo" <?php echo ($_GET['tipo_operacion'] ?? '') == 'venta_arriendo' ? 'selected' : ''; ?>>Venta/Arriendo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select" name="estado">
                                                <option value="">Todos los estados</option>
                                                <option value="activo" <?php echo ($_GET['estado'] ?? '') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                                <option value="vendido" <?php echo ($_GET['estado'] ?? '') == 'vendido' ? 'selected' : ''; ?>>Vendido</option>
                                                <option value="arrendado" <?php echo ($_GET['estado'] ?? '') == 'arrendado' ? 'selected' : ''; ?>>Arrendado</option>
                                                <option value="suspendido" <?php echo ($_GET['estado'] ?? '') == 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                                            </select>
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

                        <!-- Tabla de Inmuebles -->
                        <div class="card">
                            <div class="card-body">
                                <?php if (empty($inmuebles)): ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-building text-muted" style="font-size: 4rem;"></i>
                                        <h4 class="text-muted mt-3">No hay inmuebles registrados</h4>
                                        <p class="text-muted">Comienza agregando tu primer inmueble</p>
                                        <a href="inmuebles.php?action=add" class="btn btn-primary">
                                            <i class="bi bi-plus-circle"></i> Agregar Inmueble
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Precio</th>
                                                    <th>Tipo</th>
                                                    <th>Operación</th>
                                                    <th>Ubicación</th>
                                                    <th>Estado</th>
                                                    <th>Fecha</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($inmuebles as $inmueble): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?php echo htmlspecialchars($inmueble['titulo']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars(substr($inmueble['descripcion'], 0, 50)) . '...'; ?></small>
                                                        </td>
                                                        <td><?php echo formatearPrecio($inmueble['precio'], $inmueble['moneda']); ?></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst(str_replace('_', ' ', $inmueble['tipo_propiedad'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info">
                                                                <?php echo ucfirst(str_replace('_', '/', $inmueble['tipo_operacion'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo htmlspecialchars($inmueble['comuna']); ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($inmueble['region']); ?></small>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $badge_class = 'bg-success';
                                                            if ($inmueble['estado'] == 'vendido') $badge_class = 'bg-danger';
                                                            if ($inmueble['estado'] == 'arrendado') $badge_class = 'bg-warning';
                                                            if ($inmueble['estado'] == 'suspendido') $badge_class = 'bg-secondary';
                                                            ?>
                                                            <span class="badge <?php echo $badge_class; ?>">
                                                                <?php echo ucfirst($inmueble['estado']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($inmueble['fecha_publicacion'])); ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="inmuebles.php?action=edit&id=<?php echo $inmueble['id']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-pencil"></i>
                                                                </a>
                                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                                        onclick="mostrarModalEliminar(<?php echo $inmueble['id']; ?>, 'inmueble')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php elseif ($action == 'add' || $action == 'edit'): ?>
                        <!-- Formulario Agregar/Editar -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="h3 mb-0">
                                <?php echo $action == 'add' ? 'Agregar' : 'Editar'; ?> Inmueble
                            </h1>
                            <a href="inmuebles.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>

                        <form method="POST" action="inmuebles.php?action=<?php echo $action; ?><?php echo $id ? '&id=' . $id : ''; ?>" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Título *</label>
                                                <input type="text" class="form-control" name="titulo" required
                                                       value="<?php echo htmlspecialchars($inmueble['titulo'] ?? ''); ?>">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Descripción</label>
                                                <textarea class="form-control" name="descripcion" rows="4"><?php echo htmlspecialchars($inmueble['descripcion'] ?? ''); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Precio *</label>
                                                    <input type="number" class="form-control" name="precio" step="0.01" required
                                                           value="<?php echo $inmueble['precio'] ?? ''; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Moneda *</label>
                                                    <select class="form-select" name="moneda" required>
                                                        <option value="CLP" <?php echo ($inmueble['moneda'] ?? '') == 'CLP' ? 'selected' : ''; ?>>Peso Chileno (CLP)</option>
                                                        <option value="USD" <?php echo ($inmueble['moneda'] ?? '') == 'USD' ? 'selected' : ''; ?>>Dólar (USD)</option>
                                                        <option value="UF" <?php echo ($inmueble['moneda'] ?? '') == 'UF' ? 'selected' : ''; ?>>Unidad de Fomento (UF)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-house"></i> Tipo de Propiedad</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Tipo de Propiedad *</label>
                                                    <select class="form-select" name="tipo_propiedad" required>
                                                        <option value="">Seleccionar...</option>
                                                        <option value="casa" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'casa' ? 'selected' : ''; ?>>Casa</option>
                                                        <option value="departamento" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'departamento' ? 'selected' : ''; ?>>Departamento</option>
                                                        <option value="parcela" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'parcela' ? 'selected' : ''; ?>>Parcela</option>
                                                        <option value="local_comercial" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'local_comercial' ? 'selected' : ''; ?>>Local Comercial</option>
                                                        <option value="oficina" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'oficina' ? 'selected' : ''; ?>>Oficina</option>
                                                        <option value="terreno" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'terreno' ? 'selected' : ''; ?>>Terreno</option>
                                                        <option value="bodega" <?php echo ($inmueble['tipo_propiedad'] ?? '') == 'bodega' ? 'selected' : ''; ?>>Bodega</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Tipo de Operación *</label>
                                                    <select class="form-select" name="tipo_operacion" required>
                                                        <option value="">Seleccionar...</option>
                                                        <option value="venta" <?php echo ($inmueble['tipo_operacion'] ?? '') == 'venta' ? 'selected' : ''; ?>>Venta</option>
                                                        <option value="arriendo" <?php echo ($inmueble['tipo_operacion'] ?? '') == 'arriendo' ? 'selected' : ''; ?>>Arriendo</option>
                                                        <option value="venta_arriendo" <?php echo ($inmueble['tipo_operacion'] ?? '') == 'venta_arriendo' ? 'selected' : ''; ?>>Venta/Arriendo</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Ubicación</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Región *</label>
<?php
                                                    // Obtener regiones para el select
                                                    $stmt_regiones = $pdo->query("SELECT id, nombre FROM regiones ORDER BY nombre");
                                                    $regiones = $stmt_regiones->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <select class="form-select" name="region" id="regionSelect" required>
                                                        <option value="">Seleccionar región...</option>
                                                        <?php foreach ($regiones as $region): ?>
                                                            <option value="<?php echo $region['id']; ?>" 
                                                                    data-nombre="<?php echo htmlspecialchars($region['nombre']); ?>"
                                                                    <?php echo ($inmueble['region'] ?? '') == $region['nombre'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($region['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="region_nombre" id="regionNombre" value="<?php echo htmlspecialchars($inmueble['region'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Comuna *</label>
<?php
                                                    // Obtener la región seleccionada para cargar comunas en edición
                                                    $comunas_region = [];
                                                    if (isset($inmueble['region'])) {
                                                        $stmt_region_id = $pdo->prepare("SELECT id FROM regiones WHERE nombre = ?");
                                                        $stmt_region_id->execute([$inmueble['region']]);
                                                        $region_id = $stmt_region_id->fetchColumn();
                                                        
                                                        if ($region_id) {
                                                            $stmt_comunas = $pdo->prepare("SELECT id, nombre FROM comunas WHERE region_id = ? ORDER BY nombre");
                                                            $stmt_comunas->execute([$region_id]);
                                                            $comunas_region = $stmt_comunas->fetchAll(PDO::FETCH_ASSOC);
                                                        }
                                                    }
                                                    ?>
                                                    <select class="form-select" name="comuna" id="comunaSelect" required>
                                                        <option value="">Primero selecciona una región...</option>
                                                        <?php foreach ($comunas_region as $comuna): ?>
                                                            <option value="<?php echo htmlspecialchars($comuna['nombre']); ?>"
                                                                    <?php echo ($inmueble['comuna'] ?? '') == $comuna['nombre'] ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($comuna['nombre']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Sector</label>
                                                    <input type="text" class="form-control" name="sector"
                                                           value="<?php echo htmlspecialchars($inmueble['sector'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Dirección</label>
                                                    <input type="text" class="form-control" name="direccion"
                                                           value="<?php echo htmlspecialchars($inmueble['direccion'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-rulers"></i> Características</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Superficie Construida (m²)</label>
                                                    <input type="number" class="form-control" name="superficie_construida"
                                                           value="<?php echo $inmueble['superficie_construida'] ?? ''; ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Superficie Terreno (m²)</label>
                                                    <input type="number" class="form-control" name="superficie_terreno"
                                                           value="<?php echo $inmueble['superficie_terreno'] ?? ''; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Dormitorios</label>
                                                    <input type="number" class="form-control" name="dormitorios" min="0"
                                                           value="<?php echo $inmueble['dormitorios'] ?? '0'; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Baños</label>
                                                    <input type="number" class="form-control" name="baños" min="0"
                                                           value="<?php echo $inmueble['baños'] ?? '0'; ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Estacionamientos</label>
                                                    <input type="number" class="form-control" name="estacionamientos" min="0"
                                                           value="<?php echo $inmueble['estacionamientos'] ?? '0'; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-gear"></i> Estado</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Estado *</label>
                                                <select class="form-select" name="estado" required>
                                                    <option value="activo" <?php echo ($inmueble['estado'] ?? '') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                                                    <option value="vendido" <?php echo ($inmueble['estado'] ?? '') == 'vendido' ? 'selected' : ''; ?>>Vendido</option>
                                                    <option value="arrendado" <?php echo ($inmueble['estado'] ?? '') == 'arrendado' ? 'selected' : ''; ?>>Arrendado</option>
                                                    <option value="suspendido" <?php echo ($inmueble['estado'] ?? '') == 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                                                    <option value="borrador" <?php echo ($inmueble['estado'] ?? '') == 'borrador' ? 'selected' : ''; ?>>Borrador</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Gastos Comunes</label>
                                                <input type="number" class="form-control" name="gastos_comunes" step="0.01"
                                                       value="<?php echo $inmueble['gastos_comunes'] ?? '0'; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-check-circle"></i> Servicios</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="agua" id="agua" value="1"
                                                       <?php echo ($inmueble['agua'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="agua">Agua</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="luz" id="luz" value="1"
                                                       <?php echo ($inmueble['luz'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="luz">Luz</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="gas" id="gas" value="1"
                                                       <?php echo ($inmueble['gas'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="gas">Gas</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="alcantarillado" id="alcantarillado" value="1"
                                                       <?php echo ($inmueble['alcantarillado'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="alcantarillado">Alcantarillado</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="internet" id="internet" value="1"
                                                       <?php echo ($inmueble['internet'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="internet">Internet</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="amoblado" id="amoblado" value="1"
                                                       <?php echo ($inmueble['amoblado'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amoblado">Amoblado</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="mascotas_permitidas" id="mascotas_permitidas" value="1"
                                                       <?php echo ($inmueble['mascotas_permitidas'] ?? 0) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="mascotas_permitidas">Mascotas Permitidas</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0"><i class="bi bi-images"></i> Imágenes</h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if ($action == 'edit' && !empty($imagenes_inmueble)): ?>
                                                <h6>Imágenes actuales:</h6>
                                                <div class="row mb-3">
                                                    <?php foreach ($imagenes_inmueble as $imagen): ?>
                                                        <div class="col-md-4 mb-3">
                                                            <div class="card h-100">
                                                                <div class="position-relative">
                                                                    <img src="../<?php echo htmlspecialchars($imagen['ruta_archivo']); ?>" 
                                                                         class="card-img-top" style="height: 150px; object-fit: cover;" 
                                                                         alt="Imagen de la propiedad">
                                                                    <?php if ($imagen['es_principal']): ?>
                                                                        <span class="position-absolute top-0 start-0 m-2 badge bg-success">
                                                                            <i class="bi bi-star-fill"></i> Principal
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="card-body p-2">
                                                                    <div class="d-grid gap-2">
                                                                        <?php if ($imagen['es_principal']): ?>
                                                                            <button type="button" class="btn btn-success btn-sm" disabled>
                                                                                <i class="bi bi-check-circle"></i> Es Principal
                                                                            </button>
                                                                        <?php else: ?>
                                                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                                    onclick="establecerPrincipal(<?php echo $imagen['id']; ?>, <?php echo $id; ?>)">
                                                                                <i class="bi bi-star"></i> Seleccionar Principal
                                                                            </button>
                                                                        <?php endif; ?>
                                                                        
                                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                                                onclick="eliminarImagen(<?php echo $imagen['id']; ?>, <?php echo $id; ?>)">
                                                                            <i class="bi bi-trash"></i> Eliminar
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <hr>
                                            <?php endif; ?>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <?php echo $action == 'add' ? 'Subir Imágenes' : 'Agregar Más Imágenes'; ?>
                                                    <small class="text-muted">(Máximo 5MB por imagen, formatos: JPG, PNG, GIF, WEBP)</small>
                                                </label>
                                                <input type="file" class="form-control" name="imagenes[]" multiple accept="image/*" id="imageInput" data-bs-toggle="tooltip" title="Seleccionar Archivos">
                                                <div class="form-text">
                                                    <?php if ($action == 'add'): ?>
                                                        La primera imagen será la imagen principal.
                                                    <?php endif; ?>
                                                    Puedes seleccionar múltiples imágenes. La primera será la principal por defecto.
                                                </div>
                                            </div>
                                            
                                            <div id="imagePreview" class="row"></div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-save"></i> 
                                            <?php echo $action == 'add' ? 'Agregar' : 'Actualizar'; ?> Inmueble
                                        </button>
                                        <a href="inmuebles.php" class="btn btn-secondary">
                                            <i class="bi bi-x-circle"></i> Cancelar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
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

        // Función para eliminar imagen
        function eliminarImagen(imageId, inmuebleId) {
            
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('inmueble_id', inmuebleId);
            
            fetch('delete_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Recargar la página para actualizar la galería
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al eliminar la imagen', 'error');
            });
        }

        // Función para establecer imagen principal
        function establecerPrincipal(imageId, inmuebleId) {
            const formData = new FormData();
            formData.append('image_id', imageId);
            formData.append('inmueble_id', inmuebleId);
            
            fetch('set_main_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Recargar la página para actualizar la galería
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al establecer imagen principal', 'error');
            });
        }

        // Variable global para rastrear archivos y orden
        let selectedFiles = [];
        let principalIndex = 0;

        // Función para eliminar archivo del preview
        function eliminarArchivoPreview(index) {
            selectedFiles.splice(index, 1);
            
            // Ajustar índice principal si es necesario
            if (principalIndex >= selectedFiles.length) {
                principalIndex = Math.max(0, selectedFiles.length - 1);
            } else if (principalIndex > index) {
                principalIndex--;
            }
            
            // Actualizar el input file
            actualizarInputFile();
            
            // Re-generar el preview
            mostrarPreview();
        }

        // Función para actualizar el input file con los archivos seleccionados
        function actualizarInputFile() {
            const input = document.getElementById('imageInput');
            const dt = new DataTransfer();
            
            selectedFiles.forEach(file => {
                dt.items.add(file);
            });
            
            input.files = dt.files;
        }

        // Función para establecer imagen principal en preview
        function establecerPrincipalPreview(newIndex) {
            principalIndex = newIndex;
            // Re-generar el preview con el nuevo orden
            mostrarPreview();
        }

        // Función para mostrar preview actualizado
        function mostrarPreview() {
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    const isPrincipal = index === principalIndex;
                    
                    col.innerHTML = `
                        <div class="card">
                            <div class="position-relative">
                                <img src="${e.target.result}" class="card-img-top" style="height: 120px; object-fit: cover;" alt="Preview">
                                ${isPrincipal ? '<span class="position-absolute top-0 start-0 m-2 badge" style="background-color: var(--primary-color); color: white;"><i class="bi bi-star-fill"></i> Principal</span>' : ''}
                            </div>
                            <div class="card-body p-2">
                                <small class="text-muted d-block mb-2">${file.name}</small>
                                <div class="d-grid gap-1">
                                    ${isPrincipal ? 
                                        '<button type="button" class="btn btn-sm" style="background-color: var(--primary-color); color: white; border: none;" disabled><i class="bi bi-check-circle"></i> Es Principal</button>' : 
                                        '<button type="button" class="btn btn-sm" style="background-color: var(--secondary-color); color: white; border: none;" onclick="establecerPrincipalPreview(' + index + ')"><i class="bi bi-star"></i> Seleccionar Principal</button>'
                                    }
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarArchivoPreview(' + index + ')"><i class="bi bi-trash"></i> Eliminar</button>
                                </div>
                            </div>
                        </div>
                    `;
                    preview.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }

        // Preview de imágenes
        document.getElementById('imageInput')?.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                // Agregar nuevos archivos a los existentes en lugar de reemplazarlos
                const newFiles = Array.from(e.target.files);
                selectedFiles = [...selectedFiles, ...newFiles];
                
                // Si no había archivos antes, el primer archivo nuevo será principal
                if (selectedFiles.length === newFiles.length) {
                    principalIndex = 0;
                }
                
                mostrarPreview();
                
                // Actualizar el input file con todos los archivos
                actualizarInputFile();
            }
        });
        
        // Personalizar texto del input file
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('imageInput');
            if (fileInput) {
                // Crear botón personalizado simple
                const customBtn = document.createElement('button');
                customBtn.type = 'button';
                customBtn.className = 'btn btn-outline-secondary w-100 mb-2';
                customBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Seleccionar Archivos';
                
                // Insertar botón antes del input
                fileInput.parentNode.insertBefore(customBtn, fileInput);
                
                // Ocultar el input original
                fileInput.style.display = 'none';
                
                // Click en botón personalizado activa el input real
                customBtn.addEventListener('click', function() {
                    fileInput.click();
                });
                
                // Actualizar texto cuando se seleccionen archivos
                fileInput.addEventListener('change', function() {
                    const fileCount = this.files.length;
                    if (fileCount > 0) {
                        customBtn.innerHTML = `<i class="bi bi-check-circle"></i> ${fileCount} archivo${fileCount > 1 ? 's' : ''} seleccionado${fileCount > 1 ? 's' : ''}`;
                        customBtn.className = 'btn btn-success w-100 mb-2';
                    } else {
                        customBtn.innerHTML = '<i class="bi bi-cloud-upload"></i> Seleccionar Archivos';
                        customBtn.className = 'btn btn-outline-secondary w-100 mb-2';
                    }
                });
            }
        });

        // Manejar cambio de región para cargar comunas
        document.getElementById('regionSelect')?.addEventListener('change', function() {
            const regionId = this.value;
            const regionNombre = this.options[this.selectedIndex]?.getAttribute('data-nombre') || '';
            const comunaSelect = document.getElementById('comunaSelect');
            const regionNombreInput = document.getElementById('regionNombre');
            
            // Actualizar el input hidden con el nombre de la región
            if (regionNombreInput) {
                regionNombreInput.value = regionNombre;
            }
            
            // Limpiar select de comunas
            comunaSelect.innerHTML = '<option value="">Cargando comunas...</option>';
            
            if (regionId) {
                // Cargar comunas via AJAX
                fetch(`get_comunas.php?region_id=${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            comunaSelect.innerHTML = '<option value="">Seleccionar comuna...</option>';
                            data.comunas.forEach(comuna => {
                                const option = document.createElement('option');
                                option.value = comuna.nombre;
                                option.textContent = comuna.nombre;
                                comunaSelect.appendChild(option);
                            });
                        } else {
                            comunaSelect.innerHTML = '<option value="">Error al cargar comunas</option>';
                            console.error('Error:', data.message);
                        }
                    })
                    .catch(error => {
                        comunaSelect.innerHTML = '<option value="">Error al cargar comunas</option>';
                        console.error('Error:', error);
                    });
            } else {
                comunaSelect.innerHTML = '<option value="">Primero selecciona una región...</option>';
            }
        });

        // Función para mostrar modal de eliminación
        function mostrarModalEliminar(id, tipo) {
            const modal = new bootstrap.Modal(document.getElementById('modalEliminar'));
            const mensaje = document.getElementById('mensajeEliminar');
            const form = document.getElementById('formEliminar');
            
            if (tipo === 'inmueble') {
                mensaje.textContent = '¿Estás seguro de que deseas eliminar este inmueble? Se eliminarán también todas las imágenes asociadas. Esta acción no se puede deshacer.';
                form.action = `inmuebles.php?action=delete&id=${id}`;
            }
            
            modal.show();
        }
    </script>
</body>
</html>