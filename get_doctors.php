<?php
// get_doctors.php
include("includes/db.php");

$specialty = $_GET['specialty'] ?? '';

if ($specialty) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE specialization = ?");
    $stmt->execute([$specialty]);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM doctors");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($doctors);
