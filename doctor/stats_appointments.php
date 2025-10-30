<?php
// Devuelve JSON con métricas y series para los gráficos del doctor
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
  header('Content-Type: application/json');
  echo json_encode(['success'=>false]); exit;
}
$doctor_id = (int)$_SESSION['doctor_id'];

// Conteos actuales
$pending = $confirmed = $cancelled = $total = 0;
try{
  $stmt = $pdo->prepare("SELECT status, COUNT(*) c FROM appointments WHERE doctor_id=? GROUP BY status");
  $stmt->execute([$doctor_id]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($rows as $r){
    if($r['status']==='pendiente')   $pending   = (int)$r['c'];
    if($r['status']==='confirmada')  $confirmed = (int)$r['c'];
    if($r['status']==='cancelada')   $cancelled = (int)$r['c'];
  }
  $total = $pending + $confirmed + $cancelled;
}catch(Exception $e){}

// Series de estudios últimos 6 meses
$months = [];
$series = [];
try{
  // últimos 6 meses desde el actual
  $labels = [];
  $dt = new DateTime('first day of this month');
  for($i=5; $i>=0; $i--){
    $c = clone $dt; $c->modify("-{$i} month");
    $labels[] = $c->format('Y-m');
    $months[] = $c->format('M Y');
  }

  $map = array_fill_keys($labels, 0);
  $stmt = $pdo->prepare("
    SELECT DATE_FORMAT(uploaded_at,'%Y-%m') ym, COUNT(*) c
    FROM studies
    WHERE doctor_id=? AND uploaded_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
    GROUP BY ym
    ORDER BY ym
  ");
  $stmt->execute([$doctor_id]);
  foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $r){
    $map[$r['ym']] = (int)$r['c'];
  }
  foreach($labels as $k){ $series[] = $map[$k] ?? 0; }
}catch(Exception $e){ $months = ['-','-','-','-','-','-']; $series=[0,0,0,0,0,0]; }

header('Content-Type: application/json');
echo json_encode([
  'success'=>true,
  'pending'=>$pending,
  'confirmed'=>$confirmed,
  'cancelled'=>$cancelled,
  'total'=>$total,
  'months'=>$months,
  'studies_series'=>$series,
  'total_studies'=>array_sum($series)
]);
