<?php
// Inicia la sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MediCareConnect - Admin</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <header>
        <h1>Panel de Administración</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Inicio</a></li>
                <li><a href="appointments.php">Citas</a></li>
                <li><a href="patients.php">Pacientes</a></li>
                <li><a href="doctors.php">Médicos</a></li>
                <li><a href="settings.php">Configuración</a></li>
                <li><a href="../logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
    </header>
    <main>
