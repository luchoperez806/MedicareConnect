<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once "../includes/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

// obtener patient_id real (tabla patients)
$user_id = $_SESSION['user']['id'];
$stmtP = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmtP->execute([$user_id]);
$patientRow = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$patientRow) {
    // crear si no existe
    $ins = $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)");
    $ins->execute([$user_id]);
    $patient_id = $pdo->lastInsertId();
} else {
    $patient_id = $patientRow['id'];
}

// Esperamos datos via POST (desde dashboard: doctor_id, appointment_date, appointment_time)
$doctor_id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
$appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : (isset($_POST['date']) ? $_POST['date'] : null);
$appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : (isset($_POST['time']) ? $_POST['time'] : null);

if (!$doctor_id || !$appointment_date || !$appointment_time) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit();
}

// Validar formato simple de fecha y hora
$dateOk = DateTime::createFromFormat('Y-m-d', $appointment_date) !== false;
$timeOk = DateTime::createFromFormat('H:i', $appointment_time) !== false || DateTime::createFromFormat('H:i:s', $appointment_time) !== false;
if (!$dateOk || !$timeOk) {
    echo json_encode(['success' => false, 'message' => 'Formato de fecha u hora inválido. Usa YYYY-MM-DD y HH:MM.']);
    exit();
}

// Prevenir duplicados: buscar si ya existe turno para ese médico, fecha y hora y que no esté cancelada
$stmtCheck = $pdo->prepare("SELECT id, status FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status <> 'cancelada' LIMIT 1");
$stmtCheck->execute([$doctor_id, $appointment_date, $appointment_time]);
if ($stmtCheck->rowCount() > 0) {
    echo json_encode(['success' => false, 'message' => 'Ese horario ya está reservado.']);
    exit();
}

// Insertar turno con estado 'pendiente'
try {
    $insert = $pdo->prepare("INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, status, created_at) VALUES (?, ?, ?, ?, 'pendiente', NOW())");
    $insert->execute([$doctor_id, $patient_id, $appointment_date, $appointment_time]);

    // devolver id del turno insertado si hace falta
    $appointmentId = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Turno reservado correctamente.', 'appointment_id' => $appointmentId]);
    exit();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al reservar turno: ' . $e->getMessage()]);
    exit();
}
