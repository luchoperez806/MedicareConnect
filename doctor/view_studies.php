<?php
// doctor/view_studies.php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

include("../includes/db.php");

$doctor_id = $_SESSION['doctor_id'];

// Traer los estudios subidos por los pacientes al médico
$stmt = $pdo->prepare("
    SELECT s.*, u.fullName AS patient_name
    FROM studies s
    JOIN users u ON s.patient_id = u.id
    WHERE s.doctor_id = ?
    ORDER BY s.uploaded_at DESC
");
$stmt->execute([$doctor_id]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../includes/header.php"); ?>
<main class="main-content">
    <div class="studies-container">
        <h2>Estudios Médicos Recibidos</h2>
        <?php if (count($studies)): ?>
            <?php foreach ($studies as $study): ?>
                <div class="study-card">
                    <h3><?php echo htmlspecialchars($study['study_type'] ?? 'Estudio sin nombre'); ?></h3>
                    <p><strong>Paciente:</strong> <?php echo htmlspecialchars($study['patient_name']); ?></p>
                    <p><strong>Fecha de carga:</strong> <?php echo date("d/m/Y H:i", strtotime($study['uploaded_at'])); ?></p>

                    <?php if (!empty($study['file_name'])): ?>
                        <p>
                            <a href="../uploads/<?php echo htmlspecialchars($study['file_name']); ?>" target="_blank" class="btn-view">
                                Ver archivo
                            </a>
                        </p>
                    <?php endif; ?>

                    <div class="actions">
                        <a href="reply_study.php?study_id=<?php echo $study['id']; ?>" class="btn-reply">Ver / Responder</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-studies">Aún no se han recibido estudios médicos de pacientes.</p>
        <?php endif; ?>

        <a href="dashboard.php" class="btn-back">Volver al Dashboard</a>
    </div>
</main>
<?php include("../includes/footer.php"); ?>

<style>
.studies-container {
    max-width: 950px;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    padding: 30px;
    font-family: 'Poppins', sans-serif;
}
.studies-container h2 {
    text-align: center;
    color: #1a237e;
    margin-bottom: 25px;
}
.study-card {
    background: #f7f8ff;
    border-left: 6px solid #3f51b5;
    border-radius: 12px;
    padding: 15px 20px;
    margin-bottom: 15px;
    transition: transform 0.3s, box-shadow 0.3s;
}
.study-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.study-card h3 {
    margin: 0 0 10px 0;
    color: #303f9f;
}
.study-card p {
    margin: 5px 0;
    color: #333;
}
.btn-view, .btn-reply {
    display: inline-block;
    background: #3f51b5;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    margin-top: 10px;
}
.btn-reply {
    background: #283593;
}
.btn-view:hover, .btn-reply:hover {
    background: #1a237e;
}
.no-studies {
    text-align: center;
    color: #555;
    font-style: italic;
    margin-top: 20px;
}
.btn-back {
    display: inline-block;
    margin-top: 25px;
    background: #5c6bc0;
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
}
.btn-back:hover {
    background: #3949ab;
}
</style>
