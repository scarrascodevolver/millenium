<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'millenium_web');
define('DB_USER', 'millenium');
define('DB_PASS', 'Millenium#$%&');

// Iniciar sesión
session_start();

// Función para conectar a la base de datos
function conectarDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para verificar si el usuario está logueado
function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Función para limpiar datos de entrada
function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para formatear precios
function formatearPrecio($precio, $moneda = 'CLP') {
    $simbolos = [
        'CLP' => '$',
        'USD' => 'US$',
        'UF' => 'UF'
    ];
    
    $simbolo = $simbolos[$moneda] ?? '$';
    return $simbolo . ' ' . number_format($precio, 0, ',', '.');
}

// Función para subir archivos
function subirArchivo($archivo, $directorio = 'uploads/') {
    $directorio_completo = '../' . $directorio;
    
    // Crear directorio si no existe
    if (!file_exists($directorio_completo)) {
        mkdir($directorio_completo, 0777, true);
    }
    
    $nombre_archivo = time() . '_' . basename($archivo['name']);
    $ruta_completa = $directorio_completo . $nombre_archivo;
    
    // Verificar tipo de archivo
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $tipos_permitidos)) {
        return false;
    }
    
    // Verificar tamaño (5MB máximo)
    if ($archivo['size'] > 5000000) {
        return false;
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return $directorio . $nombre_archivo;
    }
    
    return false;
}
?>