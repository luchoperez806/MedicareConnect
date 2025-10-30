<?php
session_start();
require_once("../includes/db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success'=>false]); exit;
}

$pid = (int)($_GET['patient_id'] ?? 0);
if (!$pid) { echo json_encode(['success'=>false]); exit; }

try {
    $totA = (int)$pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=?")
                     ->execute([$pid]) ? $pdo->query("SELECT COUNT(*) FROM appointments WHERE patient_id={$pid}")->fetchColumn() : 0;

    $totS = (int)$pdo->prepare("SELECT COUNT(*) FROM studies WHERE patient_id=?")
                     ->execute([$pid]) ? $pdo->query("SELECT COUNT(*) FROM studies WHERE patient_id={$pid}")->fetchColumn() : 0;

    // actividad reciente (5 eventos mezclados)
    $activity = [];

    $stmt1 = $pdo->prepare("
      SELECT 'cita' AS type, CONCAT(appointment_date,' ',appointment_time) AS ts, status
      FROM appointments WHERE patient_id=? ORDER BY created_at DESC, appointment_date DESC LIMIT 5
    ");
    $stmt1->execute([$pid]);
    foreach ($stmt1->fetchAll(PDO::FETCH_ASSOC) as $a) {
        $activity[] = ['type'=>'cita','label'=>ucfirst($a['status']),'when'=>$a['ts']];
    }

    $stmt2 = $pdo->prepare("
      SELECT 'estudio' AS type, file_name AS ts
      FROM studies WHERE patient_id=? ORDER BY uploaded_at DESC LIMIT 5
    ");
    $stmt2->execute([$pid]);
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $activity[] = ['type'=>'estudio','label'=>$s['ts'],'when'=>'archivo'];
    }

    echo json_encode([
        'success'=>true,
        'total_appointments'=>$totA,
        'total_studies'=>$totS,
        'activity'=>$activity
    ]);
} catch (Exception $e) {
    echo json_encode(['success'=>false]);
}
