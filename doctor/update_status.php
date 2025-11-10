<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json; charset=utf-8');

// 🔒 Seguridad: solo médicos logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = $_POST['status'] ?? '';

if (!$id || !in_array($status, ['confirmada', 'cancelada'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit();
}

// Verificar que el turno pertenece al médico actual
$stmt = $pdo->prepare("SELECT a.id, a.status, a.doctor_id, p.user_id AS patient_user_id, u.fullName AS patient_name 
                       FROM appointments a
                       JOIN patients p ON a.patient_id = p.id
                       JOIN users u ON p.user_id = u.id
                       WHERE a.id = ? AND a.doctor_id = ? LIMIT 1");
$stmt->execute([$id, $doctor_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    echo json_encode(['success' => false, 'message' => 'Turno no encontrado o no autorizado.']);
    exit();
}

// =============================
// ctualizar estado + video_call
// =============================
$video_call_value = ($status === 'confirmada') ? 1 : 0;

$update = $pdo->prepare("UPDATE appointments SET status = ?, video_call = ? WHERE id = ? AND doctor_id = ?");
$success = $update->execute([$status, $video_call_value, $id, $doctor_id]);

if ($success) {
    // =========================================
    // Notificación para el PACIENTE
    // =========================================
    $msg_paciente = ($status === 'confirmada')
        ? "Su turno fue confirmado y la teleconsulta está habilitada 🎥"
        : "Su turno fue cancelado ❌";

    $notify_paciente = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, created_at)
        VALUES (?, 'Actualización de turno', ?, NOW())
    ");
    $notify_paciente->execute([$appointment['patient_user_id'], $msg_paciente]);

    // =========================================
    // Notificación para el MÉDICO
    // =========================================
    $msg_doctor = ($status === 'confirmada')
        ? "Confirmaste el turno del paciente {$appointment['patient_name']} ✅ Teleconsulta habilitada."
        : "Cancelaste el turno del paciente {$appointment['patient_name']} ❌";

    $notify_doctor = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, created_at)
        SELECT u.id, 'Gestión de turno', ?, NOW()
        FROM users u
        JOIN doctors d ON d.user_id = u.id
        WHERE d.id = ?
    ");
    $notify_doctor->execute([$msg_doctor, $doctor_id]);

    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el turno.']);
}
?>
