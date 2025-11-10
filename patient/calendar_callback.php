<?php
session_start();
require_once "../includes/google/vendor/autoload.php";
require_once "../includes/db.php";

// Seguridad
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Inicializar cliente Google
$client = new Google_Client();
$client->setAuthConfig('../includes/google/credentials.json');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/patient/calendar_callback.php');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if (!isset($token['error'])) {
        $client->setAccessToken($token);

        // Guardar el token en la BD para futuras sincronizaciones
        $user_id = $_SESSION['user']['id'];
        $access = json_encode($token);

        $stmt = $pdo->prepare("UPDATE users SET google_calendar_token = ? WHERE id = ?");
        $stmt->execute([$access, $user_id]);

        $success = true;
    } else {
        $success = false;
    }
} else {
    $success = false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Conexión con Google Calendar</title>
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
    <?php if ($success): ?>
        <h2>✅ Conectado correctamente</h2>
        <p>Tu cuenta de Google Calendar fue vinculada con éxito.<br>
        A partir de ahora, tus próximos turnos confirmados se agregarán automáticamente a tu calendario.</p>
    <?php else: ?>
        <h2>⚠️ Error en la conexión</h2>
        <p>No se pudo completar la vinculación con Google Calendar. Intentá nuevamente.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="btn">Volver al panel</a>
</div>
</body>
</html>
