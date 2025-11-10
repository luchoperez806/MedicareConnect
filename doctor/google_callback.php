<?php
session_start();
require_once "../includes/db.php";

// Seguridad: solo doctores logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$credentials = json_decode(file_get_contents("../includes/google/credentials.json"), true);

$client_id = $credentials['web']['client_id'];
$client_secret = $credentials['web']['client_secret'];
$redirect_uri = $credentials['web']['redirect_uris'][0];

if (!isset($_GET['code'])) {
    die("Error: no se recibió ningún código de autorización.");
}

$code = $_GET['code'];

// Intercambiar el código por el token
$token_url = "https://oauth2.googleapis.com/token";
$post_fields = [
    "code" => $code,
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "redirect_uri" => $redirect_uri,
    "grant_type" => "authorization_code"
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields));
$response = curl_exec($ch);
curl_close($ch);

$token_data = json_decode($response, true);

if (isset($token_data['access_token'])) {
    $access_token = $token_data['access_token'];
    $refresh_token = $token_data['refresh_token'] ?? null;
    $expires_in = $token_data['expires_in'] ?? 0;
    $expires_at = date('Y-m-d H:i:s', time() + $expires_in);

    // Guardar en DB
    $stmt = $pdo->prepare("
        UPDATE doctors
        SET google_access_token = ?, google_refresh_token = ?, google_token_expires = ?
        WHERE id = ?
    ");
    $stmt->execute([$access_token, $refresh_token, $expires_at, $doctor_id]);

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:60px;'>
            <h2 style='color:#22c55e;'>✅ Conexión exitosa con Google Calendar</h2>
            <p>Tu cuenta ha sido vinculada correctamente.</p>
            <a href='dashboard.php' style='display:inline-block;margin-top:15px;padding:10px 18px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;'>Volver al panel</a>
          </div>";
} else {
    echo "<div style='font-family:sans-serif; text-align:center; margin-top:60px;'>
            <h2 style='color:#dc2626;'>❌ Error al conectar</h2>
            <pre>".htmlspecialchars($response)."</pre>
            <a href='dashboard.php' style='display:inline-block;margin-top:15px;padding:10px 18px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none;'>Volver al panel</a>
          </div>";
}
