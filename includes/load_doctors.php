<?php
include("db.php");

$stmt = $pdo->query("SELECT * FROM doctors ORDER BY specialization");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($doctors);
