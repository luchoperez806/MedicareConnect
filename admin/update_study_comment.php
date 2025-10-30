<?php
require_once("../includes/db.php");
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$comment = $_POST['doctor_comment'] ?? '';

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no válido']);
    exit;
}

$stmt = $pdo->prepare("UPDATE studies SET doctor_comment = :comment WHERE id = :id");
$ok = $stmt->execute([':comment' => $comment, ':id' => $id]);

echo json_encode(['success' => $ok]);
