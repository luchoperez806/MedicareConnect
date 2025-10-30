<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruir todas las variables de sesión
$_SESSION = [];

// Destruir la sesión completamente
session_destroy();

// Detectar si estás en localhost o en hosting
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$projectFolder = '/MedicareConnect'; // 👈 Cambiá esto si el nombre de tu carpeta cambia

// Redirigir al inicio público
header("Location: http://$host$projectFolder/index.php");
exit();
?>
