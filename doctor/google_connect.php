<?php
session_start();
require_once "../includes/db.php";

// Seguridad: solo doctores logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

// Cargar credenciales desde JSON
$credentials = json_decode(file_get_contents("../includes/google/credentials.json"), true);
$client_id = $credentials['web']['client_id'];
$redirect_uri = $credentials['web']['redirect_uris'][0];
$scope = urlencode("https://www.googleapis.com/auth/calendar.events https://www.googleapis.com/auth/calendar");

$auth_url = "https://accounts.google.com/o/oauth2/v2/auth"
    . "?scope={$scope}"
    . "&access_type=offline"
    . "&include_granted_scopes=true"
    . "&response_type=code"
    . "&redirect_uri=" . urlencode($redirect_uri)
    . "&client_id={$client_id}";

header("Location: $auth_url");
exit();
