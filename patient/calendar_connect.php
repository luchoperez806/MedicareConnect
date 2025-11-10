<?php
session_start();

// Seguridad: sólo pacientes logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Cargar librerías de Google
require_once "../includes/google/vendor/autoload.php";

// Crear cliente de Google
$client = new Google_Client();
$client->setAuthConfig('../includes/google/credentials.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/patient/calendar_callback.php');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Generar URL de autorización
$authUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Conectar con Google Calendar</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
body {
    background:#f5f7fb; font-family:'Poppins',sans-serif;
    display:flex; justify-content:center; align-items:center; height:100vh;
}
.card {
    background:white; border-radius:16px; padding:40px; text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,0.1); max-width:420px;
}
.btn {
    display:inline-block; margin-top:20px; padding:12px 22px;
    background:linear-gradient(90deg,#3b82f6,#06b6d4);
    color:white; text-decoration:none; font-weight:600; border-radius:8px;
}
.btn:hover { opacity:.9; }
</style>
</head>
<body>
<div class="card">
    <h2>📅 Sincronizar con Google Calendar</h2>
    <p>Conectá tu cuenta de Google para que tus turnos confirmados se agenden automáticamente en tu calendario personal.</p>
    <a href="<?= htmlspecialchars($authUrl) ?>" class="btn">Conectar con Google</a>
    <br><br>
    <a href="dashboard.php" style="color:#64748b; text-decoration:none;">← Volver al panel</a>
</div>
</body>
</html>
