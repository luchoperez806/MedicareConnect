<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID inválido.");

$stmt = $pdo->prepare("SELECT file_name FROM studies WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file) {
    $path = "../uploads/" . $file['file_name'];
    if (file_exists($path)) unlink($path);
    $pdo->prepare("DELETE FROM studies WHERE id = ?")->execute([$id]);
}
header("Location: dashboard.php");
exit();
?>
