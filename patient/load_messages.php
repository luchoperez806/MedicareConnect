<?php
require_once "../includes/db.php";

$doctor_id = $_GET['doctor_id'] ?? 0;
$patient_id = $_GET['patient_id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM messages WHERE doctor_id = ? AND patient_id = ? ORDER BY sent_at ASC");
$stmt->execute([$doctor_id, $patient_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
