<?php
session_start();
require_once "../includes/db.php";
require_once "../fpdf/fpdf.php";
require_once "../phpqrcode/qrlib.php";

// ===== Seguridad =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];
$user_id   = (int)$_SESSION['user']['id'];

// ===== Datos del doctor =====
$stmt = $pdo->prepare("
    SELECT u.fullName, u.email, d.specialization, d.office_address
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doctor) die("Error: no se encontró el perfil del médico.");

// ===== Pacientes confirmados =====
$stmt = $pdo->prepare("
    SELECT DISTINCT p.id, u.fullName AS patient_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE a.doctor_id = ? AND a.status = 'confirmada'
    ORDER BY u.fullName ASC
");
$stmt->execute([$doctor_id]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== Generación de receta =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)$_POST['patient_id'];
    $medication = trim($_POST['medication']);
    $dosage     = trim($_POST['dosage']);
    $duration   = trim($_POST['duration']);
    $observations = trim($_POST['observations']);

    if ($patient_id && $medication !== '') {

        // Verificar datos del paciente
        $stmtP = $pdo->prepare("SELECT u.fullName, u.email FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmtP->execute([$patient_id]);
        $patient = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$patient) die("Paciente no encontrado.");

        // Crear carpeta si no existe
        $dir = "../uploads/recetas/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        // Crear QR único
        $codigo = md5(uniqid(rand(), true));
        $qrPath = $dir . "qr_" . $codigo . ".png";
        $verifyUrl = "https://medicareconnect.site/verificar.php?code=" . $codigo;
        QRcode::png($verifyUrl, $qrPath, QR_ECLEVEL_L, 3);

        // Crear PDF
        $pdfPath = $dir . "receta_" . $codigo . ".pdf";
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'RECETA MÉDICA DIGITAL', 0, 1, 'C');
        $pdf->Ln(8);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Emitida por: Dr/a. ' . utf8_decode($doctor['fullName']), 0, 1);
        $pdf->Cell(0, 8, 'Especialidad: ' . utf8_decode($doctor['specialization']), 0, 1);
        $pdf->Cell(0, 8, 'Consultorio: ' . utf8_decode($doctor['office_address']), 0, 1);
        $pdf->Ln(4);
        $pdf->Cell(0, 8, 'Paciente: ' . utf8_decode($patient['fullName']), 0, 1);
        $pdf->Cell(0, 8, 'Email: ' . $patient['email'], 0, 1);
        $pdf->Ln(8);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Medicamento / Tratamiento:', 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 8, utf8_decode($medication));
        $pdf->Ln(4);

        if ($dosage) $pdf->MultiCell(0, 8, 'Dosis / Frecuencia: ' . utf8_decode($dosage));
        if ($duration) $pdf->MultiCell(0, 8, 'Duración: ' . utf8_decode($duration));
        if ($observations) {
            $pdf->Ln(4);
            $pdf->MultiCell(0, 8, 'Observaciones: ' . utf8_decode($observations));
        }

        $pdf->Ln(10);
        $pdf->Image($qrPath, 170, 230, 25, 25);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, 'Verificá la autenticidad en: ' . $verifyUrl, 0, 1, 'L');

        $pdf->Output('F', $pdfPath);

        // Guardar en DB
        $stmtIns = $pdo->prepare("
            INSERT INTO prescriptions (doctor_id, patient_id, medication, dosage, duration, observations, pdf_file, qr_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtIns->execute([$doctor_id, $patient_id, $medication, $dosage, $duration, $observations, basename($pdfPath), basename($qrPath)]);

        echo "<script>alert('Receta generada correctamente ✅'); window.location='receta_digital.php';</script>";
        exit;
    } else {
        echo "<script>alert('Por favor, completá todos los campos obligatorios.');</script>";
    }
}
?>

<?php include("../includes/header.php"); ?>

<div class="container py-4" style="max-width:800px;">
    <h2 class="mb-4">🧾 Generar Receta Digital</h2>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label fw-bold">Seleccionar paciente</label>
            <select name="patient_id" class="form-select" required>
                <option value="">-- Elegí un paciente con turno confirmado --</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['patient_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Medicamento o tratamiento</label>
            <textarea name="medication" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Dosis / Frecuencia</label>
            <input type="text" name="dosage" class="form-control" placeholder="Ej: 1 comprimido cada 8 horas">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Duración</label>
            <input type="text" name="duration" class="form-control" placeholder="Ej: durante 7 días">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Observaciones</label>
            <textarea name="observations" class="form-control" rows="2" placeholder="Notas adicionales, cuidados, etc."></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100">💊 Generar Receta PDF</button>
    </form>
</div>

<?php include("../includes/footer.php"); ?>
