<?php
// Incluir configuración de base de datos
require_once 'admin/config.php';

$pdo = conectarDB();

// Obtener configuración de contacto
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

// Obtener filtros
$tipo_operacion = $_GET['tipo'] ?? '';
$tipo_propiedad = $_GET['propiedad'] ?? '';
$comuna = $_GET['comuna'] ?? '';
$precio_min = $_GET['precio_min'] ?? '';
$precio_max = $_GET['precio_max'] ?? '';
$dormitorios = $_GET['dormitorios'] ?? '';
$search = $_GET['search'] ?? '';

// Construir consulta
$where = "WHERE i.estado = 'activo'";
$params = [];

if ($tipo_operacion) {
    $where .= " AND i.tipo_operacion = ?";
    $params[] = $tipo_operacion;
}

if ($tipo_propiedad) {
    $where .= " AND i.tipo_propiedad = ?";
    $params[] = $tipo_propiedad;
}

if ($comuna) {
    $where .= " AND i.comuna LIKE ?";
    $params[] = "%$comuna%";
}

if ($precio_min) {
    $where .= " AND i.precio >= ?";
    $params[] = $precio_min;
}

if ($precio_max) {
    $where .= " AND i.precio <= ?";
    $params[] = $precio_max;
}

if ($dormitorios) {
    $where .= " AND i.dormitorios >= ?";
    $params[] = $dormitorios;
}

if ($search) {
    $where .= " AND (i.titulo LIKE ? OR i.descripcion LIKE ? OR i.comuna LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Obtener inmuebles con su imagen principal
$stmt = $pdo->prepare("
    SELECT i.*, 
           img.ruta_archivo as imagen_principal
    FROM inmuebles i
    LEFT JOIN imagenes_inmuebles img ON i.id = img.inmueble_id AND img.es_principal = 1
    $where 
    ORDER BY i.fecha_publicacion DESC
");
$stmt->execute($params);
$inmuebles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener comunas para filtro
$stmt = $pdo->query("SELECT DISTINCT comuna FROM inmuebles WHERE estado = 'activo' ORDER BY comuna");
$comunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Corretaje - Millenium Administración de Edificios y Condominios</title>
  <meta name="description" content="Encuentra tu propiedad ideal con Millenium. Casas, departamentos y más en venta y arriendo.">
  <meta name="keywords" content="corretaje, propiedades, casas, departamentos, venta, arriendo, millenium">

  <!-- Favicons -->
  <link href="assets/img/icono.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    .property-card {
      transition: all 0.3s ease;
      border: none;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      border-radius: 15px;
      overflow: hidden;
    }
    
    .property-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .property-image {
      height: 250px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .property-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--accent-color);
      color: white;
      padding: 5px 15px;
      border-radius: 25px;
      font-weight: 600;
      font-size: 0.8rem;
    }
    
    .property-price {
      position: absolute;
      bottom: 15px;
      right: 15px;
      background: rgba(255,255,255,0.95);
      color: var(--secondary-color);
      padding: 10px 15px;
      border-radius: 25px;
      font-weight: 700;
      font-size: 1.1rem;
    }
    
    .property-details {
      padding: 1.5rem;
    }
    
    .property-title {
      color: var(--secondary-color);
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .property-location {
      color: #666;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }
    
    .property-features {
      display: flex;
      gap: 15px;
      margin-bottom: 1rem;
    }
    
    .feature-item {
      display: flex;
      align-items: center;
      gap: 5px;
      color: #666;
      font-size: 0.9rem;
    }
    
    .contact-btn {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      font-weight: 600;
      width: 100%;
      transition: all 0.3s ease;
    }
    
    .contact-btn:hover {
      background-color: #8fa076;
      color: white;
      transform: translateY(-2px);
    }
    
    .filters-section {
      background-color: var(--surface-color);
      padding: 2rem 0;
      margin-bottom: 2rem;
    }
    
    .filter-tabs {
      margin-bottom: 2rem;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
    }
    
    .filter-tab {
      background: white;
      border: 2px solid transparent;
      color: var(--secondary-color);
      padding: 15px 30px;
      border-radius: 50px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      flex: 1;
      min-width: 150px;
      text-align: center;
    }
    
    .filter-tab:hover {
      border-color: var(--primary-color);
      color: var(--secondary-color);
    }
    
    .filter-tab.active {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }
    
    /* Responsive para móviles */
    @media (max-width: 768px) {
      .filter-tabs {
        flex-direction: column;
        align-items: center;
      }
      
      .filter-tab {
        margin: 5px 0;
        min-width: 200px;
        padding: 12px 20px;
      }
      
      .filter-tab i {
        display: block;
        margin-bottom: 5px;
        font-size: 1.2rem;
      }
    }
    
    .no-properties {
      text-align: center;
      padding: 4rem 0;
      color: #666;
    }
    
    .property-type-badge {
      background-color: var(--surface-color);
      color: var(--secondary-color);
      padding: 3px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    
  </style>
</head>

<body class="index-page">
  <header id="header" class="header fixed-top">
    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a
              href="mailto:info@millenium.cl">info@millenium.cl</a></i>
          <a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $config['telefono_principal'])); ?>"><i class="bi bi-phone d-flex align-items-center ms-4"><span><?php echo htmlspecialchars($config['telefono_principal']); ?></span></i></a>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
        </div>
      </div>
    </div>

    <div class="branding d-flex align-items-center">
      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center">
          <img src="assets/img/logofinal.png" alt="">
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="index.html">Inicio</a></li>
            <li><a href="index.html#about">Nosotros</a></li>
            <li><a href="index.html#services">Servicios</a></li>
            <li><a href="corretaje.php" class="active">Corretaje</a></li>
            <!-- <li><a href="beneficios.html">Beneficios</a></li> -->
            <li><a href="index.html#faq">Preguntas Frecuentes</a></li>
            <li><a href="https://tcel.cl/web/login"><img width="80px" src="assets/img/logo-kastor.svg" alt=""></a></li>
            <li><a href="index.html#contact">Contacto</a></li>
            <li><a href="admin/check_session.php" class="btn-login">Ingresar</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </div>
  </header>

  <main class="main">
    <!-- Hero Section -->
    <section id="hero" class="hero section" style="padding: 10px 0 5px 0; background-color: var(--surface-color);">
      <div class="container text-center" data-aos="fade-up">
        <h1 style="color: var(--secondary-color); margin-bottom: 5px;">Corretaje de Propiedades</h1>
        <p class="lead" style="color: var(--secondary-color); margin-bottom: 0;">Encuentra tu propiedad ideal con nosotros</p>
      </div>
    </section>

    <!-- Filtros Section -->
    <section class="filters-section">
      <div class="container">
        <!-- Tabs de operación -->
        <div class="filter-tabs text-center" data-aos="fade-up">
          <a href="corretaje.php" class="filter-tab <?php echo !$tipo_operacion ? 'active' : ''; ?>">
            Todas las Propiedades
          </a>
          <a href="corretaje.php?tipo=venta" class="filter-tab <?php echo $tipo_operacion == 'venta' ? 'active' : ''; ?>">
            <i class="bi bi-house"></i> En Venta
          </a>
          <a href="corretaje.php?tipo=arriendo" class="filter-tab <?php echo $tipo_operacion == 'arriendo' ? 'active' : ''; ?>">
            <i class="bi bi-key"></i> En Arriendo
          </a>
        </div>

        <!-- Filtros avanzados -->
        <div class="card" data-aos="fade-up" data-aos-delay="100">
          <div class="card-body">
            <form method="GET" action="corretaje.php">
              <?php if ($tipo_operacion): ?>
                <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($tipo_operacion); ?>">
              <?php endif; ?>
              
              <div class="row g-3">
                <div class="col-12 col-md-3">
                  <input type="text" class="form-control" name="search" id="searchInput"
                         placeholder="Buscar propiedades..."
                         value="<?php echo htmlspecialchars($search); ?>"
                         autocomplete="off">
                </div>
                <div class="col-6 col-md-2">
                  <select class="form-select" name="propiedad">
                    <option value="">Tipo de propiedad</option>
                    <option value="casa" <?php echo $tipo_propiedad == 'casa' ? 'selected' : ''; ?>>Casa</option>
                    <option value="departamento" <?php echo $tipo_propiedad == 'departamento' ? 'selected' : ''; ?>>Departamento</option>
                    <option value="parcela" <?php echo $tipo_propiedad == 'parcela' ? 'selected' : ''; ?>>Parcela</option>
                    <option value="local_comercial" <?php echo $tipo_propiedad == 'local_comercial' ? 'selected' : ''; ?>>Local Comercial</option>
                    <option value="oficina" <?php echo $tipo_propiedad == 'oficina' ? 'selected' : ''; ?>>Oficina</option>
                  </select>
                </div>
                <div class="col-6 col-md-2">
                  <select class="form-select" name="comuna">
                    <option value="">Comuna</option>
                    <?php foreach ($comunas as $c): ?>
                      <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $comuna == $c ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-6 col-md-2">
                  <input type="number" class="form-control" name="precio_min" 
                         placeholder="Precio mínimo"
                         value="<?php echo htmlspecialchars($precio_min); ?>">
                </div>
                <div class="col-6 col-md-2">
                  <input type="number" class="form-control" name="precio_max" 
                         placeholder="Precio máximo"
                         value="<?php echo htmlspecialchars($precio_max); ?>">
                </div>
                <div class="col-12 col-md-1">
                  <button type="submit" class="btn w-100" style="background-color: #A5B68D !important; color: white !important; border: none !important;">
                    <i class="bi bi-search"></i> <span class="d-md-none">Buscar</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Propiedades Section -->
    <section class="section">
      <div class="container">
        <div class="row mb-4">
          <div class="col-12">
            <h3>
              <?php 
              if ($tipo_operacion == 'venta') {
                echo 'Propiedades en Venta';
              } elseif ($tipo_operacion == 'arriendo') {
                echo 'Propiedades en Arriendo';
              } else {
                echo 'Todas las Propiedades';
              }
              ?>
              <small class="text-muted">(<?php echo count($inmuebles); ?> propiedades encontradas)</small>
            </h3>
          </div>
        </div>

        <?php if (empty($inmuebles)): ?>
          <div class="no-properties" data-aos="fade-up">
            <i class="bi bi-house text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3">No se encontraron propiedades</h4>
            <p>Intenta modificar los filtros de búsqueda</p>
            <a href="corretaje.php" class="btn" style="background-color: #A5B68D !important; color: #574964 !important; border: none !important;">Ver todas las propiedades</a>
          </div>
        <?php else: ?>
          <div class="row">
            <?php foreach ($inmuebles as $index => $inmueble): ?>
              <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo ($index % 3) * 100; ?>">
                <div class="card property-card h-100" onclick="window.location.href='detalle-propiedad.php?id=<?php echo $inmueble['id']; ?>'" style="cursor: pointer;">
                  <div class="property-image" style="background-image: url('<?php echo $inmueble['imagen_principal'] ? htmlspecialchars($inmueble['imagen_principal']) : 'assets/img/services/' . (($index % 6) + 1) . '.' . ($index % 2 ? 'jpg' : 'jpeg'); ?>');">
                    <div class="property-badge">
                      <?php echo ucfirst(str_replace('_', '/', $inmueble['tipo_operacion'])); ?>
                    </div>
                    <div class="property-price">
                      <?php echo formatearPrecio($inmueble['precio'], $inmueble['moneda']); ?>
                    </div>
                  </div>
                  <div class="property-details">
                    <h5 class="property-title"><?php echo htmlspecialchars($inmueble['titulo']); ?></h5>
                    <div class="property-location">
                      <i class="bi bi-geo-alt me-2" style="color: var(--accent-color);"></i>
                      <?php echo htmlspecialchars($inmueble['comuna'] . ', ' . $inmueble['region']); ?>
                    </div>
                    
                    <div class="property-features">
                      <?php if ($inmueble['dormitorios'] > 0): ?>
                        <div class="feature-item">
                          <i class="bi bi-house-door"></i>
                          <span><?php echo $inmueble['dormitorios']; ?> dorm</span>
                        </div>
                      <?php endif; ?>
                      <?php if ($inmueble['baños'] > 0): ?>
                        <div class="feature-item">
                          <i class="bi bi-droplet"></i>
                          <span><?php echo $inmueble['baños']; ?> baños</span>
                        </div>
                      <?php endif; ?>
                      <?php if ($inmueble['superficie_construida'] > 0): ?>
                        <div class="feature-item">
                          <i class="bi bi-rulers"></i>
                          <span><?php echo $inmueble['superficie_construida']; ?>m²</span>
                        </div>
                      <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                      <span class="property-type-badge">
                        <?php echo ucfirst(str_replace('_', ' ', $inmueble['tipo_propiedad'])); ?>
                      </span>
                    </div>
                    
                    <p class="text-muted small">
                      <?php echo htmlspecialchars(substr($inmueble['descripcion'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <div class="text-center mb-2">
                      <small class="text-muted">
                        <i class="bi bi-eye"></i> Click para ver detalles completos
                      </small>
                    </div>
                    
                    <button class="contact-btn" onclick="event.stopPropagation(); contactarPropiedad(<?php echo $inmueble['id']; ?>, '<?php echo htmlspecialchars($inmueble['titulo']); ?>')">
                      <i class="bi bi-envelope"></i> Contactar
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer accent-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-5 col-md-12 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">Millenium LTDA</span>
          </a>
          <p>Soluciones Integrales en Gestión de Edificios, Combinando Experiencia, Tecnología y Transparencia</p>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-6 footer-links">
          <h4>Links útiles</h4>
          <ul>
            <li><a href="index.html">Inicio</a></li>
            <li><a href="index.html#about">Nosotros</a></li>
            <li><a href="index.html#services">Servicios</a></li>
            <li><a href="corretaje.php">Corretaje</a></li>
            <!-- <li><a href="beneficios.html">Beneficios</a></li> -->
            <li><a href="index.html#contact">Contacto</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-6 footer-links">
          <h4>Nuestros Servicios</h4>
          <ul>
            <li><a href="#">Gestión de mantenimiento y reparaciones</a></li>
            <li><a href="#">Gestión Financiera</a></li>
            <li><a href="#">Gestion de recursos humanos</a></li>
            <li><a href="#">Cumplimiento normativo</a></li>
            <li><a href="#">Implementación de tecnología para la gestión comunitaria</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-12 footer-contact text-center text-md-start">
          <h4>Contactanos</h4>
          <p>Aurelio Manzano N°594</p>
          <p>Concepción</p>
          <p class="mt-4"><strong>Telefono:</strong> <span><?php echo htmlspecialchars($config['telefono']); ?></span></p>
          <p><strong>Email:</strong> <span>corretaje@millenium.cl</span></p>
        </div>
      </div>
    </div>

    <!-- WhatsApp fixed widget -->
    <a class="whapp animated pulse" href="https://api.whatsapp.com/send/?phone=<?php echo htmlspecialchars($config['whatsapp_principal']); ?>&text=Hola,%20me%20interesa%20obtener%20más%20información" target="_blank">
      <div class="whapp-btn">
        <img src="https://uploads-ssl.webflow.com/62b5ca109278e030a060e942/62bf28776d90ec6fa2bec0f4_iconmonstr-whatsapp-1-240.png" />
      </div>
    </a>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Modal de contacto -->
  <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="contactModalLabel">Contactar por esta propiedad</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="contactForm">
          <div class="modal-body">
            <input type="hidden" id="inmueble_id" name="inmueble_id">
            <div class="mb-3">
              <label for="nombre" class="form-label">Nombre *</label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Email *</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="telefono" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="telefono" name="telefono">
            </div>
            <div class="mb-3">
              <label for="mensaje" class="form-label">Mensaje</label>
              <textarea class="form-control" id="mensaje" name="mensaje" rows="4"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn" style="background-color: #DA8359; border-color: #DA8359; color: white;">Enviar Consulta</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <script>
    function contactarPropiedad(inmuebleId, titulo) {
      document.getElementById('inmueble_id').value = inmuebleId;
      document.getElementById('mensaje').value = `Hola, estoy interesado/a en la propiedad: ${titulo}. Me gustaría recibir más información.`;
      
      const modal = new bootstrap.Modal(document.getElementById('contactModal'));
      modal.show();
    }

    document.getElementById('contactForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      fetch('contact_property.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showToast('¡Consulta enviada exitosamente! Nos pondremos en contacto contigo pronto.', 'success');
          bootstrap.Modal.getInstance(document.getElementById('contactModal')).hide();
          this.reset();
        } else {
          showToast('Error al enviar la consulta: ' + data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showToast('Error al enviar la consulta. Por favor intenta nuevamente.', 'error');
      });
    });

    // Initialize AOS
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });

    // Búsqueda en tiempo real
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const propertiesContainer = document.querySelector('.section .container .row:last-of-type');
    let originalContent = null;
    
    if (searchInput && propertiesContainer) {
      // Guardar contenido original
      originalContent = propertiesContainer.innerHTML;
      
      searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
          // Restaurar contenido original
          propertiesContainer.innerHTML = originalContent;
          return;
        }
        
        searchTimeout = setTimeout(() => {
          searchProperties(query);
        }, 300);
      });
    }
    
    function searchProperties(query) {
      const urlParams = new URLSearchParams(window.location.search);
      const tipo = urlParams.get('tipo') || '';
      
      // Mostrar loading
      propertiesContainer.innerHTML = `
        <div class="col-12 text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Buscando...</span>
          </div>
          <p class="mt-2">Buscando propiedades...</p>
        </div>
      `;
      
      fetch(`search_suggestions.php?q=${encodeURIComponent(query)}&tipo=${tipo}`)
        .then(response => response.json())
        .then(data => {
          if (data.suggestions && data.suggestions.length > 0) {
            displayProperties(data.suggestions);
          } else {
            propertiesContainer.innerHTML = `
              <div class="col-12 text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ccc;"></i>
                <h4 class="mt-3 text-muted">No se encontraron propiedades</h4>
                <p class="text-muted">Intenta con otros términos de búsqueda</p>
              </div>
            `;
          }
        })
        .catch(error => {
          console.error('Error:', error);
          propertiesContainer.innerHTML = `
            <div class="col-12 text-center py-5">
              <i class="bi bi-exclamation-triangle" style="font-size: 4rem; color: #dc3545;"></i>
              <h4 class="mt-3 text-danger">Error en la búsqueda</h4>
              <p class="text-muted">Por favor intenta nuevamente</p>
            </div>
          `;
        });
    }
    
    function displayProperties(properties) {
      let html = '';
      
      properties.forEach((property, index) => {
        const price = formatPrice(property.precio, property.moneda);
        const features = [];
        
        if (property.dormitorios > 0) features.push(`${property.dormitorios} dorm`);
        if (property.baños > 0) features.push(`${property.baños} baños`);
        if (property.superficie_construida > 0) features.push(`${property.superficie_construida}m²`);
        
        html += `
          <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="${(index % 3) * 100}">
            <div class="card property-card h-100" onclick="window.location.href='detalle-propiedad.php?id=${property.id}'" style="cursor: pointer;">
              <div class="property-image" style="background-image: url('assets/img/services/${(index % 6) + 1}.jpg'); height: 250px; background-size: cover; background-position: center; position: relative;">
                <div class="property-badge">
                  ${property.tipo_operacion === 'venta' ? 'Venta' : property.tipo_operacion === 'arriendo' ? 'Arriendo' : 'Venta/Arriendo'}
                </div>
                <div class="property-price">
                  ${price}
                </div>
              </div>
              <div class="property-details">
                <h5 class="property-title">${property.titulo}</h5>
                <div class="property-location">
                  <i class="bi bi-geo-alt me-2" style="color: var(--accent-color);"></i>
                  ${property.comuna}, ${property.region}
                </div>
                
                <div class="property-features">
                  ${features.map(feature => `<div class="feature-item"><span>${feature}</span></div>`).join('')}
                </div>
                
                <div class="mb-3">
                  <span class="property-type-badge">
                    ${property.tipo_propiedad.charAt(0).toUpperCase() + property.tipo_propiedad.slice(1).replace('_', ' ')}
                  </span>
                </div>
                
                <div class="text-center mb-2">
                  <small class="text-muted">
                    <i class="bi bi-eye"></i> Click para ver detalles completos
                  </small>
                </div>
                
                <button class="contact-btn" onclick="event.stopPropagation(); contactarPropiedad(${property.id}, '${property.titulo}')">
                  <i class="bi bi-envelope"></i> Contactar
                </button>
              </div>
            </div>
          </div>
        `;
      });
      
      propertiesContainer.innerHTML = html;
      
      // Re-inicializar AOS para las nuevas propiedades
      if (typeof AOS !== 'undefined') {
        AOS.refresh();
      }
    }
    
    function formatPrice(precio, moneda) {
      const formatter = new Intl.NumberFormat('es-CL');
      const symbol = moneda === 'USD' ? 'US$' : moneda === 'UF' ? 'UF' : '$';
      return `${symbol} ${formatter.format(precio)}`;
    }

    // Función para mostrar toast
    function showToast(message, type = 'success') {
      const toastContainer = document.querySelector('.toast-container');
      const toastId = 'toast-' + Date.now();
      
      const toastHtml = `
        <div class="toast show" role="alert" id="${toastId}" data-bs-delay="4000">
          <div class="toast-header" style="background-color: ${type === 'success' ? '#A5B68D' : '#dc3545'}; color: white; border: none;">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
            <strong class="me-auto">${type === 'success' ? 'Éxito' : 'Error'}</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body" style="background-color: white; color: #333;">
            ${message}
          </div>
        </div>
      `;
      
      toastContainer.insertAdjacentHTML('beforeend', toastHtml);
      
      const toastElement = document.getElementById(toastId);
      const toast = new bootstrap.Toast(toastElement);
      
      // Mostrar toast
      toast.show();
      
      // Remover del DOM después de que se oculte
      toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
      });
    }

  </script>

  <!-- Toast Container -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <!-- Los toasts se insertarán aquí dinámicamente -->
  </div>

</body>
</html>