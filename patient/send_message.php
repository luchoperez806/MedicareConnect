<?php
require_once "../includes/db.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $patient_id = $_POST['patient_id'];
    $message = trim($_POST['message']);
    $sender = 'patient';

    if ($message !== '') {
        $stmt = $pdo->prepare("INSERT INTO messages (doctor_id, patient_id, sender, message, sent_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$doctor_id, $patient_id, $sender, $message]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
