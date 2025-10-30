<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}
require_once "../includes/db.php";

$doctor_id = (int)$_SESSION['doctor_id'];
$appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

if ($appointment_id <= 0) {
    header("Location: dashboard.php?error=ID%20de%20turno%20inv%C3%A1lido");
    exit();
}

// Verificar que el turno pertenece a este doctor
$stmt = $pdo->prepare("SELECT id, doctor_id FROM appointments WHERE id = ? LIMIT 1");
$stmt->execute([$appointment_id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt || (int)$appt['doctor_id'] !== $doctor_id) {
    header("Location: dashboard.php?error=No%20autorizado");
    exit();
}

// Habilitar videollamada
$up = $pdo->prepare("UPDATE appointments SET video_call = 1 WHERE id = ?");
$up->execute([$appointment_id]);

header("Location: dashboard.php?success=video_enabled");
