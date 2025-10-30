<?php
session_start();
require_once "../includes/db.php";

// Seguridad: solo doctores
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$doctor_id = (int)$_SESSION['doctor_id'];
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if (!$patient_id) {
    echo json_encode(["error" => "ID de paciente no especificado"]);
    exit();
}

/* ========================= Información del paciente ========================= */
$stmt = $pdo->prepare("
    SELECT u.fullName, u.email, p.phone, p.address, p.birthdate, p.blood_type
    FROM patients p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    echo json_encode(["error" => "Paciente no encontrado"]);
    exit();
}

/* ========================= Últimos turnos con este doctor ========================= */
$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.video_call
    FROM appointments a
    WHERE a.patient_id = ? AND a.doctor_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 6
");
$stmt->execute([$patient_id, $doctor_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================= Últimos estudios del paciente ========================= */
$stmt = $pdo->prepare("
    SELECT file_name, uploaded_at
    FROM studies
    WHERE patient_id = ?
    ORDER BY uploaded_at DESC
    LIMIT 6
");
$stmt->execute([$patient_id]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================= Respuesta JSON ========================= */
echo json_encode([
    "patient" => $patient,
    "appointments" => $appointments,
    "studies" => $studies
], JSON_UNESCAPED_UNICODE);
?>
