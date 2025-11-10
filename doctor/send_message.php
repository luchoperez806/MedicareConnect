<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$sender_role = $_SESSION['user']['role']; // 'doctor' o 'patient'
$sender_id = $_SESSION['user']['id'];
$message = trim($_POST['message'] ?? '');
$doctor_id = $_POST['doctor_id'] ?? null;
$patient_id = $_POST['patient_id'] ?? null;

if ($message === '' || !$doctor_id || !$patient_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Insertar mensaje
$stmt = $pdo->prepare("
    INSERT INTO messages (doctor_id, patient_id, sender, message, sent_at, is_read)
    VALUES (?, ?, ?, ?, NOW(), 0)
");
$stmt->execute([$doctor_id, $patient_id, $sender_role, $message]);

// Crear notificación para el receptor
if ($sender_role === 'doctor') {
    $receiverQuery = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
    $receiverQuery->execute([$patient_id]);
    $receiver_user_id = $receiverQuery->fetchColumn();
} else {
    $receiverQuery = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
    $receiverQuery->execute([$doctor_id]);
    $receiver_user_id = $receiverQuery->fetchColumn();
}

$notif = $pdo->prepare("
    INSERT INTO notifications (user_id, title, message, read_status, created_at)
    VALUES (?, 'Nuevo mensaje recibido', ?, 0, NOW())
");
$notif->execute([$receiver_user_id, 'Tienes un nuevo mensaje en el chat.']);

echo json_encode(['success' => true]);
?>
