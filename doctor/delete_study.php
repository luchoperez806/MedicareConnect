<?php
session_start();
require_once "../includes/db.php";
header('Content-Type: application/json');

if(!isset($_SESSION['user']) || $_SESSION['user']['role']!=='doctor'){
  echo json_encode(['success'=>false,'message'=>'Acceso denegado']);
  exit();
}

$id = (int)($_GET['id'] ?? 0);
if(!$id){ echo json_encode(['success'=>false,'message'=>'ID inválido']); exit(); }

$stmt = $pdo->prepare("DELETE FROM studies WHERE id=?");
$ok = $stmt->execute([$id]);

echo json_encode(['success'=>$ok]);
?>
