<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/notifications.php";

header('Content-Type: application/json; charset=utf-8');

// 🔒 Seguridad: solo médicos autenticados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];

// Datos recibidos por AJAX (JSON)
$data = json_decode(file_get_contents("php://input"), true);
$appointment_id = (int)($data['appointment_id'] ?? 0);
$newStatus = $data['status'] ?? '';

if (!$appointment_id || !in_array($newStatus, ['confirmada', 'cancelada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos']);
    exit();
}

// Buscar el turno en la base de datos
$stmt = $pdo->prepare("
    SELECT a.id, a.patient_id, a.status, u.id AS patient_user_id, u.fullName AS patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE a.id = ? AND a.doctor_id = ?
    LIMIT 1
");
$stmt->execute([$appointment_id, $doctor_id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appt) {
    echo json_encode(['success' => false, 'message' => 'Turno no encontrado o no pertenece a este médico.']);
    exit();
}

// Actualizar estado del turno
$stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$stmt->execute([$newStatus, $appointment_id]);

// Si el turno fue confirmado, habilitar chat y videollamada
if ($newStatus === 'confirmada') {
    $stmt = $pdo->prepare("UPDATE appointments SET video_call = 1 WHERE id = ?");
    $stmt->execute([$appointment_id]);
}

// Crear notificación al paciente
try {
    $title = 'Actualización de turno';
    $msg = ($newStatus === 'confirmada')
        ? 'Tu turno fue confirmado ✅. Ya puedes acceder al chat y la videollamada desde tu panel.'
        : 'Tu turno fue cancelado ❌. Puedes reservar otro desde tu panel.';

    sendNotification((int)$appt['patient_user_id'], $title, $msg);
} catch (Exception $e) {
    // No interrumpe ejecución si la notificación falla
}

// Respuesta final
echo json_encode([
    'success' => true,
    'message' => "Turno {$newStatus} correctamente",
    'status'  => $newStatus
]);
?>
