<?php
require_once 'admin/config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: corretaje.php');
    exit();
}

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

// Obtener datos del inmueble
$stmt = $pdo->prepare("SELECT * FROM inmuebles WHERE id = ? AND estado = 'activo'");
$stmt->execute([$id]);
$inmueble = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inmueble) {
    header('Location: corretaje.php?error=' . urlencode('Propiedad no encontrada'));
    exit();
}

// Obtener todas las imágenes del inmueble
$stmt = $pdo->prepare("SELECT * FROM imagenes_inmuebles WHERE inmueble_id = ? ORDER BY es_principal DESC, orden_visualizacion");
$stmt->execute([$id]);
$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Incrementar contador de visitas
$stmt = $pdo->prepare("UPDATE inmuebles SET visitas = visitas + 1 WHERE id = ?");
$stmt->execute([$id]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo htmlspecialchars($inmueble['titulo']); ?> - Millenium</title>
  <meta name="description" content="<?php echo htmlspecialchars(substr($inmueble['descripcion'], 0, 160)); ?>">
  <meta name="keywords" content="<?php echo htmlspecialchars($inmueble['tipo_propiedad'] . ', ' . $inmueble['tipo_operacion'] . ', ' . $inmueble['comuna']); ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo htmlspecialchars($inmueble['titulo']); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars(substr($inmueble['descripcion'], 0, 160)); ?>">
  <meta property="og:image" content="<?php echo !empty($imagenes) ? $_SERVER['HTTP_HOST'] . '/' . $imagenes[0]['ruta_archivo'] : ''; ?>">
  <meta property="og:url" content="<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">

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
    .property-gallery {
      margin-bottom: 2rem;
    }
    
    .main-image {
      height: 500px;
      background-size: cover;
      background-position: center;
      border-radius: 15px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .thumbnail-gallery {
      margin-top: 1rem;
    }
    
    .thumbnail {
      height: 100px;
      background-size: cover;
      background-position: center;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      border: 3px solid transparent;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .thumbnail:hover,
    .thumbnail.active {
      border-color: var(--primary-color);
      transform: scale(1.05);
    }
    
    .property-badge {
      position: absolute;
      top: 15px;
      left: 15px;
      background: var(--accent-color);
      color: white;
      padding: 8px 20px;
      border-radius: 25px;
      font-weight: 600;
    }
    
    .property-price {
      position: absolute;
      bottom: 15px;
      right: 15px;
      background: rgba(255,255,255,0.95);
      color: var(--secondary-color);
      padding: 15px 25px;
      border-radius: 25px;
      font-weight: 700;
      font-size: 1.3rem;
    }
    
    .property-details {
      background: white;
      border-radius: 15px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 2rem;
    }
    
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin: 2rem 0;
    }
    
    .feature-item {
      background: var(--surface-color);
      padding: 1rem;
      border-radius: 10px;
      text-align: center;
    }
    
    .feature-icon {
      font-size: 2rem;
      color: var(--primary-color);
      margin-bottom: 0.5rem;
    }
    
    .contact-card {
      background: linear-gradient(135deg, var(--primary-color) 0%, #8fa076 100%);
      color: white;
      border-radius: 15px;
      padding: 2rem;
      position: sticky;
      top: 20px;
    }
    
    .contact-card h4.mb-3,
    .contact-card div.h2.mb-3,
    .contact-card p.mb-0 {
      color: #574964 !important;
    }
    
    .contact-card * {
      color: #574964 !important;
    }
    
    .contact-btn {
      background: #DA8359;
      color: white !important;
      border: 2px solid #DA8359;
      padding: 12px 24px;
      border-radius: 25px;
      font-weight: 600;
      width: 100%;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
    }
    
    .contact-btn i {
      position: absolute;
      left: 30px;
      width: 20px;
      text-align: center;
    }
    
    .contact-btn span {
      flex: 1;
    }
    
    .contact-btn:hover {
      background: white;
      color: #DA8359 !important;
      border-color: #DA8359;
      transform: translateY(-2px);
    }
    
    .contact-btn-whatsapp {
      background: #25D366;
      color: white;
      border: 2px solid #25D366;
    }
    
    .contact-btn-whatsapp:hover {
      background: transparent;
      color: #25D366;
      border-color: #25D366;
    }
    
    .contact-btn-phone {
      background: var(--accent-color);
      color: white;
      border: 2px solid var(--accent-color);
    }
    
    .contact-btn-phone:hover {
      background: transparent;
      color: var(--accent-color);
      border-color: var(--accent-color);
    }
    
    .back-btn {
      background-color: var(--secondary-color);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .back-btn:hover {
      background-color: #48415a;
      color: white;
      transform: translateY(-2px);
    }
    
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 1rem;
      margin: 1rem 0;
    }
    
    .service-item {
      text-align: center;
      padding: 1rem;
      background: var(--surface-color);
      border-radius: 10px;
    }
    
    .service-available {
      color: #28a745;
      font-size: 1.2rem;
    }
    
    .service-unavailable {
      color: #dc3545;
      font-size: 1.2rem;
    }
    
    .property-stats {
      background: var(--surface-color);
      padding: 1rem;
      border-radius: 10px;
      margin: 1rem 0;
    }
    
    .no-images {
      height: 400px;
      background: var(--surface-color);
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: #666;
    }
  </style>
</head>

<body>
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
            <li><a href="corretaje.php">Corretaje</a></li>
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

  <main class="main" style="padding-top: 0;">
    <!-- Breadcrumb Section -->
    <section style="background-color: var(--surface-color); padding: 1rem 0; margin-top: 120px;">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-8">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.html">Inicio</a></li>
                <li class="breadcrumb-item"><a href="corretaje.php">Corretaje</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($inmueble['titulo']); ?></li>
              </ol>
            </nav>
          </div>
          <div class="col-md-4 text-md-end">
            <a href="corretaje.php" class="back-btn">
              <i class="bi bi-arrow-left"></i> Volver a Corretaje
            </a>
          </div>
        </div>
      </div>
    </section>

    <div class="container" style="margin-top: 2rem;">
      <div class="row">
        <!-- Galería e Información Principal -->
        <div class="col-lg-8">
          <!-- Galería de Imágenes -->
          <div class="property-gallery" data-aos="fade-up">
            <?php if (!empty($imagenes)): ?>
              <div class="main-image" id="mainImage" style="background-image: url('<?php echo htmlspecialchars($imagenes[0]['ruta_archivo']); ?>');">
                <div class="property-badge">
                  <?php echo ucfirst(str_replace('_', '/', $inmueble['tipo_operacion'])); ?>
                </div>
                <div class="property-price">
                  <?php echo formatearPrecio($inmueble['precio'], $inmueble['moneda']); ?>
                </div>
              </div>
              
              <?php if (count($imagenes) > 1): ?>
                <div class="thumbnail-gallery">
                  <div class="row">
                    <?php foreach ($imagenes as $index => $imagen): ?>
                      <div class="col-2">
                        <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                             style="background-image: url('<?php echo htmlspecialchars($imagen['ruta_archivo']); ?>');"
                             onclick="changeMainImage('<?php echo htmlspecialchars($imagen['ruta_archivo']); ?>', this)">
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="no-images">
                <i class="bi bi-image" style="font-size: 4rem; margin-bottom: 1rem;"></i>
                <h5>Sin imágenes disponibles</h5>
                <p>Esta propiedad aún no tiene imágenes</p>
              </div>
            <?php endif; ?>
          </div>

          <!-- Título y Ubicación de la Propiedad -->
          <div data-aos="fade-up" data-aos-delay="100" style="margin-bottom: 2rem;">
            <h1 style="color: var(--secondary-color); margin-bottom: 1rem;"><?php echo htmlspecialchars($inmueble['titulo']); ?></h1>
            <p class="lead" style="margin-bottom: 0;">
              <i class="bi bi-geo-alt" style="color: var(--accent-color);"></i>
              <?php echo htmlspecialchars($inmueble['direccion'] ? $inmueble['direccion'] . ', ' : ''); ?>
              <?php echo htmlspecialchars($inmueble['comuna'] . ', ' . $inmueble['region']); ?>
            </p>
          </div>

          <!-- Detalles de la Propiedad -->
          <div class="property-details" data-aos="fade-up" data-aos-delay="100">
            <h3>Detalles de la Propiedad</h3>
            
            <!-- Grid de Características -->
            <div class="feature-grid">
              <?php if ($inmueble['superficie_construida'] > 0): ?>
                <div class="feature-item">
                  <div class="feature-icon"><i class="bi bi-rulers"></i></div>
                  <strong><?php echo $inmueble['superficie_construida']; ?>m²</strong>
                  <div>Superficie Construida</div>
                </div>
              <?php endif; ?>
              
              <?php if ($inmueble['superficie_terreno'] > 0): ?>
                <div class="feature-item">
                  <div class="feature-icon"><i class="bi bi-square"></i></div>
                  <strong><?php echo $inmueble['superficie_terreno']; ?>m²</strong>
                  <div>Superficie Terreno</div>
                </div>
              <?php endif; ?>
              
              <?php if ($inmueble['dormitorios'] > 0): ?>
                <div class="feature-item">
                  <div class="feature-icon"><i class="bi bi-house-door"></i></div>
                  <strong><?php echo $inmueble['dormitorios']; ?></strong>
                  <div>Dormitorios</div>
                </div>
              <?php endif; ?>
              
              <?php if ($inmueble['baños'] > 0): ?>
                <div class="feature-item">
                  <div class="feature-icon"><i class="bi bi-droplet"></i></div>
                  <strong><?php echo $inmueble['baños']; ?></strong>
                  <div>Baños</div>
                </div>
              <?php endif; ?>
              
              <?php if ($inmueble['estacionamientos'] > 0): ?>
                <div class="feature-item">
                  <div class="feature-icon"><i class="bi bi-car-front"></i></div>
                  <strong><?php echo $inmueble['estacionamientos']; ?></strong>
                  <div>Estacionamientos</div>
                </div>
              <?php endif; ?>
              
              <div class="feature-item">
                <div class="feature-icon"><i class="bi bi-building"></i></div>
                <strong><?php echo ucfirst(str_replace('_', ' ', $inmueble['tipo_propiedad'])); ?></strong>
                <div>Tipo de Propiedad</div>
              </div>
            </div>

            <!-- Descripción -->
            <h4>Descripción</h4>
            <p><?php echo nl2br(htmlspecialchars($inmueble['descripcion'])); ?></p>

            <!-- Servicios Disponibles -->
            <h4>Servicios y Características</h4>
            <div class="services-grid">
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['agua'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Agua</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['luz'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Electricidad</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['gas'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Gas</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['alcantarillado'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Alcantarillado</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['internet'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Internet</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['amoblado'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Amoblado</div>
              </div>
              <div class="service-item">
                <i class="bi bi-<?php echo $inmueble['mascotas_permitidas'] ? 'check-circle-fill service-available' : 'x-circle-fill service-unavailable'; ?>"></i>
                <div>Mascotas</div>
              </div>
            </div>

            <!-- Información Adicional -->
            <?php if ($inmueble['gastos_comunes'] > 0): ?>
              <div class="property-stats">
                <h5>Gastos Comunes</h5>
                <p class="mb-0"><?php echo formatearPrecio($inmueble['gastos_comunes'], 'CLP'); ?> mensuales</p>
              </div>
            <?php endif; ?>

            <div class="property-stats">
              <div class="row">
                <div class="col-6">
                  <strong>Fecha de Publicación:</strong><br>
                  <?php echo date('d/m/Y', strtotime($inmueble['fecha_publicacion'])); ?>
                </div>
                <div class="col-6">
                  <strong>Vistas:</strong><br>
                  <?php echo number_format($inmueble['visitas']); ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar de Contacto -->
        <div class="col-lg-4">
          <div class="contact-card" data-aos="fade-up" data-aos-delay="200">
            <div class="text-center mb-4">
              <h4 class="mb-3" style="color: #574964 !important;">¿Te interesa esta propiedad?</h4>
              <div class="h2 mb-3" style="color: #574964 !important;"><?php echo formatearPrecio($inmueble['precio'], $inmueble['moneda']); ?></div>
              <p class="mb-0" style="color: #574964 !important;">¡Contáctanos para más información!</p>
            </div>
            
            <div class="d-grid gap-3">
              <button class="contact-btn" onclick="contactarPropiedad(<?php echo $inmueble['id']; ?>, '<?php echo htmlspecialchars($inmueble['titulo']); ?>')">
                <i class="bi bi-envelope-fill"></i><span>Enviar Consulta</span>
              </button>
              
              <a href="https://api.whatsapp.com/send/?phone=<?php echo htmlspecialchars($config['whatsapp']); ?>&text=Hola, estoy interesado en la propiedad: <?php echo urlencode($inmueble['titulo']); ?>" 
                 target="_blank" class="contact-btn contact-btn-whatsapp">
                <i class="bi bi-whatsapp"></i><span>Contactar por WhatsApp</span>
              </a>
              
              <a href="tel:<?php echo htmlspecialchars($config['telefono']); ?>" class="contact-btn contact-btn-phone">
                <i class="bi bi-telephone-fill"></i><span>Llamar</span>
              </a>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.3); margin: 2rem 0;">
            
            <div class="text-center">
              <h5 class="mb-3">
                <img src="assets/img/logofinal.png" alt="Millenium" style="height: 40px; margin-right: 10px;">
                Millenium Ltda.
              </h5>
              <div class="row text-start">
                <div class="col-12 mb-2">
                  <i class="bi bi-geo-alt-fill me-2"></i>
                  <small>Aurelio Manzano N°594, Concepción</small>
                </div>
                <div class="col-12 mb-2">
                  <i class="bi bi-envelope-fill me-2"></i>
                  <small>info@millenium.cl</small>
                </div>
                <div class="col-12 mb-2">
                  <i class="bi bi-telephone-fill me-2"></i>
                  <small>+41 2799626</small>
                </div>
                <div class="col-12">
                  <i class="bi bi-eye-fill me-2"></i>
                  <small><?php echo number_format($inmueble['visitas']); ?> personas han visto esta propiedad</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer id="footer" class="footer accent-background" style="margin-top: 4rem;">
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
            <li><a href="beneficios.html">Beneficios</a></li>
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
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

  <!-- Toast Container -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    <!-- Los toasts se insertarán aquí dinámicamente -->
  </div>

  <script>
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

    // Cambiar imagen principal
    function changeMainImage(imageSrc, thumbnail) {
      document.getElementById('mainImage').style.backgroundImage = `url('${imageSrc}')`;
      
      // Actualizar thumbnails activos
      document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
      thumbnail.classList.add('active');
    }

    // Función para contactar por la propiedad
    function contactarPropiedad(inmuebleId, titulo) {
      document.getElementById('inmueble_id').value = inmuebleId;
      document.getElementById('mensaje').value = `Hola, estoy interesado/a en la propiedad: ${titulo}. Me gustaría recibir más información.`;
      
      const modal = new bootstrap.Modal(document.getElementById('contactModal'));
      modal.show();
    }

    // Manejo del formulario de contacto
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
  </script>
</body>
</html>