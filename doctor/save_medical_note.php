<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Acceso denegado"]);
    exit();
}

header('Content-Type: application/json; charset=utf-8');

$doctor_id  = (int)$_SESSION['doctor_id'];
$data       = json_decode(file_get_contents("php://input"), true);
$patient_id = (int)($data['patient_id'] ?? 0);
$note       = trim($data['note'] ?? '');

if (!$patient_id || $note === '') {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO medical_notes (doctor_id, patient_id, note) VALUES (?, ?, ?)");
$ok = $stmt->execute([$doctor_id, $patient_id, $note]);

echo json_encode([
    "success" => $ok,
    "message" => $ok ? "Nota médica guardada correctamente." : "Error al guardar nota."
]);
