<?php
session_start();
require_once "../includes/db.php";

// ===== Seguridad =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php?role=patient");
    exit();
}

$patient_user_id = (int)$_SESSION['user']['id'];

// Obtener el ID real del paciente
$stmtP = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmtP->execute([$patient_user_id]);
$patient = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$patient) {
    die("Error: No se encontró el registro del paciente.");
}
$patient_id = (int)$patient['id'];

// ===== Buscar médicos con chats activos o turnos confirmados =====
$stmt = $pdo->prepare("
    SELECT DISTINCT d.id AS doctor_id, u.fullName AS doctor_fullname, MAX(m.sent_at) AS last_msg
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    JOIN appointments a ON a.doctor_id = d.id
    LEFT JOIN messages m ON m.doctor_id = d.id AND m.patient_id = ?
    WHERE a.patient_id = ?
    GROUP BY d.id, u.fullName
    ORDER BY last_msg DESC, u.fullName ASC
");
$stmt->execute([$patient_id, $patient_id]);
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
    <div class="alert alert-info text-center">No hay chats activos con médicos aún.</div>
  <?php else: ?>
    <div class="list-group shadow-sm">
      <?php foreach ($chats as $c): ?>
        <a href="chat.php?doctor_id=<?= $c['doctor_id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <span>👨‍⚕️ <?= htmlspecialchars($c['doctor_fullname']) ?></span>
          <small class="text-muted"><?= $c['last_msg'] ? date('d/m/Y H:i', strtotime($c['last_msg'])) : 'Sin mensajes' ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include("../includes/footer.php"); ?>
