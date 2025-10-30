<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}
require_once "../includes/db.php";

$doctor_id = (int)$_SESSION['doctor_id'];
$study_id  = isset($_POST['study_id']) ? (int)$_POST['study_id'] : 0;
$comment   = isset($_POST['doctor_comment']) ? trim($_POST['doctor_comment']) : '';

if ($study_id <= 0 || $comment === '') {
    header("Location: dashboard.php?error=Datos%20inv%C3%A1lidos%20al%20comentar%20estudio");
    exit();
}

// Verificar que el estudio es de este doctor
$stmt = $pdo->prepare("SELECT id, doctor_id FROM studies WHERE id = ? LIMIT 1");
$stmt->execute([$study_id]);
$study = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$study || (int)$study['doctor_id'] !== $doctor_id) {
    header("Location: dashboard.php?error=No%20autorizado");
    exit();
}

$up = $pdo->prepare("UPDATE studies SET doctor_comment = ? WHERE id = ?");
$up->execute([$comment, $study_id]);

header("Location: dashboard.php?success=comment_saved");
