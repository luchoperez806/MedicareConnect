<?php
session_start();
require_once "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $patient_id = $_POST['patient_id'];
    $typing = $_POST['typing'];

    $stmt = $pdo->prepare("REPLACE INTO typing_status (doctor_id, patient_id, doctor_typing, patient_typing) VALUES (?, ?, 0, ?)");
    $stmt->execute([$doctor_id, $patient_id, $typing]);
    echo json_encode(['success'=>true]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $doctor_id = $_GET['doctor_id'];
    $patient_id = $_GET['patient_id'];
    $stmt = $pdo->prepare("SELECT doctor_typing, patient_typing FROM typing_status WHERE doctor_id=? AND patient_id=?");
    $stmt->execute([$doctor_id, $patient_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row ?: ['doctor_typing'=>0,'patient_typing'=>0]);
}
?>
