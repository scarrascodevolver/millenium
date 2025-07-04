-- Configuración de la base de datos para XAMPP/MariaDB
-- Crear base de datos y configurar para usuario root sin contraseña
-- Ejecuta este script completo en phpMyAdmin

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS millenium_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE millenium_web;


-- Tabla de usuarios/agentes
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'agente', 'cliente') DEFAULT 'agente',
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla principal de inmuebles
CREATE TABLE inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(12,2) NOT NULL,
    moneda ENUM('CLP', 'USD', 'UF') DEFAULT 'CLP',
    precio_negociable BOOLEAN DEFAULT FALSE,
    
    -- Tipo y operación
    tipo_propiedad ENUM('casa', 'departamento', 'parcela', 'local_comercial', 'oficina', 'terreno', 'bodega') NOT NULL,
    tipo_operacion ENUM('venta', 'arriendo', 'venta_arriendo') NOT NULL,
    
    -- Ubicación
    region VARCHAR(50) NOT NULL,
    comuna VARCHAR(50) NOT NULL,
    sector VARCHAR(100),
    direccion VARCHAR(200),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    
    -- Características físicas
    superficie_construida INT,
    superficie_terreno INT,
    dormitorios INT DEFAULT 0,
    baños INT DEFAULT 0,
    estacionamientos INT DEFAULT 0,
    bodegas INT DEFAULT 0,
    año_construccion INT,
    pisos_total INT,
    piso_ubicacion INT,
    
    -- Servicios básicos
    agua BOOLEAN DEFAULT FALSE,
    luz BOOLEAN DEFAULT FALSE,
    gas BOOLEAN DEFAULT FALSE,
    alcantarillado BOOLEAN DEFAULT FALSE,
    internet BOOLEAN DEFAULT FALSE,
    
    -- Características adicionales
    amoblado BOOLEAN DEFAULT FALSE,
    mascotas_permitidas BOOLEAN DEFAULT FALSE,
    gastos_comunes DECIMAL(10,2) DEFAULT 0,
    
    -- Gestión del sistema
    estado ENUM('activo', 'vendido', 'arrendado', 'suspendido', 'borrador') DEFAULT 'activo',
    destacado BOOLEAN DEFAULT FALSE,
    visitas INT DEFAULT 0,
    usuario_id INT NOT NULL,
    
    -- Multimedia
    video_url VARCHAR(500),
    tour_virtual_url VARCHAR(500),
    
    -- Timestamps
    fecha_publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Claves foráneas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    
    -- Índices
    INDEX idx_tipo_propiedad (tipo_propiedad),
    INDEX idx_tipo_operacion (tipo_operacion),
    INDEX idx_precio (precio),
    INDEX idx_ubicacion (region, comuna),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha_publicacion)
);

-- Tabla de imágenes
CREATE TABLE imagenes_inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inmueble_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    descripcion VARCHAR(200),
    es_principal BOOLEAN DEFAULT FALSE,
    orden_visualizacion INT DEFAULT 0,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (inmueble_id) REFERENCES inmuebles(id) ON DELETE CASCADE,
    INDEX idx_inmueble (inmueble_id),
    INDEX idx_principal (es_principal)
);

-- Tabla de contactos/consultas
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inmueble_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    mensaje TEXT,
    fecha_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    respondido BOOLEAN DEFAULT FALSE,
    
    FOREIGN KEY (inmueble_id) REFERENCES inmuebles(id) ON DELETE CASCADE,
    INDEX idx_inmueble (inmueble_id),
    INDEX idx_fecha (fecha_consulta)
);

-- Tabla de características adicionales (flexible)
CREATE TABLE caracteristicas_inmuebles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inmueble_id INT NOT NULL,
    caracteristica VARCHAR(100) NOT NULL,
    valor VARCHAR(200),
    
    FOREIGN KEY (inmueble_id) REFERENCES inmuebles(id) ON DELETE CASCADE,
    INDEX idx_inmueble (inmueble_id)
);

-- Tabla de favoritos (si implementas esa funcionalidad)
CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    inmueble_id INT NOT NULL,
    fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (inmueble_id) REFERENCES inmuebles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorito (usuario_id, inmueble_id)
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, telefono, password, tipo_usuario) 
VALUES ('Administrador', 'admin@inmobiliaria.cl', '+56912345678', MD5('admin123'), 'admin');

-- Insertar datos de ejemplo
INSERT INTO inmuebles (
    titulo, descripcion, precio, tipo_propiedad, tipo_operacion, 
    region, comuna, sector, superficie_construida, superficie_terreno,
    dormitorios, baños, estacionamientos, agua, luz, gas, usuario_id
) VALUES (
    'Casa en Las Condes', 
    'Hermosa casa en excelente ubicación, cerca de centros comerciales y colegios.',
    250000000, 
    'casa', 
    'venta',
    'Metropolitana',
    'Las Condes',
    'El Golf',
    180,
    300,
    4,
    3,
    2,
    TRUE,
    TRUE,
    TRUE,
    1
);

-- Ver estructura creada
SHOW TABLES;