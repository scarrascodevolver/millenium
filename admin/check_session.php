<?php
// Archivo para verificar sesión activa
require_once 'config.php';

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
} else {
    // Si no hay sesión, redirigir al login
    header('Location: login.php');
    exit();
}
?>