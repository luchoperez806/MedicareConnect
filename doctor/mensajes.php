<?php
session_start();
require_once "../includes/db.php";

// ===== Seguridad =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];
$user_id   = (int)$_SESSION['user']['id'];

// ===== Buscar pacientes con chats activos o turnos confirmados =====
$stmt = $pdo->prepare("
    SELECT DISTINCT p.id AS patient_id, u.fullName, MAX(m.sent_at) AS last_msg
    FROM patients p
    JOIN users u ON p.user_id = u.id
    JOIN appointments a ON a.patient_id = p.id
    LEFT JOIN messages m ON m.patient_id = p.id AND m.doctor_id = ?
    WHERE a.doctor_id = ?
    GROUP BY p.id, u.fullName
    ORDER BY last_msg DESC, u.fullName ASC
");
$stmt->execute([$doctor_id, $doctor_id]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>💬 Chats activos</h4>
    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">← Volver</a>
  </div>

  <?php if (empty($chats)): ?>
    <div class="alert alert-info text-center">No hay chats activos con pacientes aún.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php foreach ($chats as $c): ?>
        <a href="chat.php?patient_id=<?= $c['patient_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <span>🧍 <?= htmlspecialchars($c['fullName']) ?></span>
          <small class="text-muted"><?= $c['last_msg'] ? date('d/m/Y H:i', strtotime($c['last_msg'])) : 'Sin mensajes' ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
