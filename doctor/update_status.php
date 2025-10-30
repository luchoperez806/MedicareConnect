<?php
session_start();
require_once("../includes/db.php");

// Seguridad: solo médicos autenticados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$doctor_id = (int)$_SESSION['doctor_id'];
$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$validStatuses = ['confirmada', 'cancelada'];

if (!$id || !in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Validar que el turno pertenezca al médico actual
$stmt = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
$stmt->execute([$id, $doctor_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Turno no pertenece a este médico']);
    exit();
}

// Actualizar estado
$upd = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$upd->execute([$status, $id]);

echo json_encode(['success' => true, 'message' => 'Estado actualizado a ' . $status]);
