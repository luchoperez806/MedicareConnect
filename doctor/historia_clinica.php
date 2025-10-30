<?php
session_start();
require_once("../includes/db.php");

// Seguridad: solo médicos logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_GET['patient_id'])) {
    header("Location: dashboard.php");
    exit();
}

$patient_id = (int)$_GET['patient_id'];

// Traer datos del paciente y su usuario
$stmt = $pdo->prepare("
    SELECT u.fullName, u.email, p.*
    FROM patients p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Paciente no encontrado.");
}

// Actualizar historia clínica si se envió formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $blood_type = $_POST['blood_type'] ?? null;
    $allergies = $_POST['allergies'] ?? null;
    $medical_conditions = $_POST['medical_conditions'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;

    $stmt = $pdo->prepare("UPDATE patients 
        SET blood_type=?, allergies=?, medical_conditions=?, observaciones=? 
        WHERE id=?");
    $stmt->execute([$blood_type, $allergies, $medical_conditions, $observaciones, $patient_id]);

    $mensaje = "Historia clínica actualizada correctamente.";
}

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f7fb; font-family: 'Poppins', sans-serif; }
.card { border-radius:15px; box-shadow:0 6px 18px rgba(0,0,0,0.05); border:none; }
.btn-gradient { background:linear-gradient(90deg,#3b82f6,#06b6d4); border:none; color:#fff; font-weight:600; }
</style>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Historia clínica de <?= htmlspecialchars($patient['fullName']) ?></h4>
    <a href="dashboard.php" class="btn btn-secondary btn-sm">⬅️ Volver al panel</a>
  </div>

  <?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
  <?php endif; ?>

  <div class="card p-4">
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Tipo de sangre</label>
        <input type="text" name="blood_type" value="<?= htmlspecialchars($patient['blood_type'] ?? '') ?>" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Alergias</label>
        <textarea name="allergies" rows="2" class="form-control"><?= htmlspecialchars($patient['allergies'] ?? '') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Condiciones médicas</label>
        <textarea name="medical_conditions" rows="3" class="form-control"><?= htmlspecialchars($patient['medical_conditions'] ?? '') ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Observaciones / notas del médico</label>
        <textarea name="observaciones" rows="3" class="form-control"><?= htmlspecialchars($patient['observaciones'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-gradient">💾 Guardar cambios</button>
    </form>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
