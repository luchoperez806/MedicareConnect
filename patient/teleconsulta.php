<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}
require_once "../includes/db.php";
$user = $_SESSION['user'];

// Tomamos el ID del doctor desde GET
$doctor_id = $_GET['doctor_id'] ?? null;
if (!$doctor_id) {
    die("Error: falta el identificador del médico.");
}

// Obtenemos datos del doctor
$stmt = $pdo->prepare("SELECT doctor_name FROM doctors WHERE id=?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doctor) {
    die("Médico no encontrado.");
}

$roomName = "Consulta_" . $user['id'] . "_" . $doctor_id;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Teleconsulta con <?= htmlspecialchars($doctor['doctor_name']) ?></title>
<script src='https://meet.jit.si/external_api.js'></script>
<style>
body{margin:0;font-family:'Poppins',sans-serif;background:#f3f7fa;}
header{background:#4f6df5;color:white;padding:15px;text-align:center;font-weight:600;}
#jitsi-container{width:100%;height:90vh;}
</style>
</head>
<body>
<header>Teleconsulta con <?= htmlspecialchars($doctor['doctor_name']) ?></header>
<div id="jitsi-container"></div>
<script>
const domain = "meet.jit.si";
const options = {
    roomName: "<?= $roomName ?>",
    width: "100%",
    height: "100%",
    parentNode: document.querySelector('#jitsi-container'),
    userInfo: { displayName: "<?= htmlspecialchars($user['fullName']) ?>" }
};
const api = new JitsiMeetExternalAPI(domain, options);
</script>
</body>
</html>
