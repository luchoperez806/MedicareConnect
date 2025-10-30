<?php
session_start();
require_once "../includes/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit;
}

$appointment_id = $_POST['appointment_id'] ?? null;
if (!$appointment_id) {
    echo json_encode(['success' => false, 'message' => 'ID de turno inválido.']);
    exit;
}

// Verificar que el turno pertenezca al paciente
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("
    SELECT a.id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.id = ? AND p.user_id = ?
");
$stmt->execute([$appointment_id, $user_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    echo json_encode(['success' => false, 'message' => 'Turno no encontrado o no te pertenece.']);
    exit;
}

// Actualizar estado
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelada' WHERE id = ?");
$ok = $stmt->execute([$appointment_id]);

echo json_encode(['success' => $ok]);
?>
<style>
    .cancel-section {
    margin-top: 10px;
    text-align: right;
}
.btn-cancel {
    background: var(--danger);
    color: white;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 8px;
}
.btn-cancel:hover {
    background: #dc2626;
}

</style>