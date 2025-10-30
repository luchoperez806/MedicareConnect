<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

require_once "../includes/db.php";

$doctor_id = $_SESSION['doctor_id'];
// user_id del paciente pasado por GET
$patient_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$patient_user_id) {
    die("Paciente no especificado.");
}

// Obtener patient record id
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmt->execute([$patient_user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$patient) {
    die("No se encontró el registro del paciente.");
}
$patient_id = $patient['id'];

// Verificar que el doctor tenga al menos un turno con ese paciente (prevención básica)
$stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id = ? AND patient_id = ? LIMIT 1");
$stmt->execute([$doctor_id, $patient_id]);
$count = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$count || intval($count['cnt']) === 0) {
    die("No autorizado: no hay turnos entre este doctor y el paciente.");
}

// Obtener datos del paciente (nombre)
$stmt = $pdo->prepare("SELECT fullName, email FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$patient_user_id]);
$patientUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener estudios del paciente dirigidos a este doctor (o todos los estudios del paciente)
$stmt = $pdo->prepare("
    SELECT s.*, u.fullName AS patient_name
    FROM studies s
    JOIN patients p ON s.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY s.uploaded_at DESC
");
$stmt->execute([$patient_user_id]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
?>

<main class="main-content">
    <div class="dashboard-container">
        <h2>Estudios de: <?php echo htmlspecialchars($patientUser['fullName'] ?? 'Paciente'); ?></h2>
        <p class="muted">Listado de archivos subidos por el paciente. Podés descargar o abrirlos para revisarlos.</p>

        <?php if (!$studies): ?>
            <div class="card">
                <p>No se encontraron estudios subidos por este paciente.</p>
            </div>
        <?php else: ?>
            <?php foreach ($studies as $s): ?>
                <div class="card">
                    <p><strong>Archivo:</strong>
                        <a href="../uploads/<?php echo htmlspecialchars($s['file_name']); ?>" target="_blank">
                            <?php echo htmlspecialchars($s['file_name']); ?>
                        </a>
                    </p>
                    <p><strong>Subido:</strong> <?php echo htmlspecialchars($s['uploaded_at']); ?></p>
                    <?php if (!empty($s['description'])): ?>
                        <p><strong>Descripción:</strong> <?php echo htmlspecialchars($s['description']); ?></p>
                    <?php endif; ?>

                    <!-- Si querés que el doctor pueda añadir comentario al estudio desde acá -->
                    <form action="reply_study.php" method="POST" style="margin-top:10px;">
                        <input type="hidden" name="study_id" value="<?php echo $s['id']; ?>">
                        <textarea name="doctor_comment" placeholder="Agregar comentario..." required><?php echo htmlspecialchars($s['doctor_comment'] ?? ''); ?></textarea>
                        <button type="submit" class="btn-primary">Guardar comentario</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-secondary" style="margin-top:15px; display:inline-block;">Volver al dashboard</a>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<!-- Podés reutilizar estilos del dashboard -->
