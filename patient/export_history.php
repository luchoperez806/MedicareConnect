<?php
/**
 * export_history.php
 * Genera la historia clínica digital del paciente en PDF
 * con QR que redirige a verify_patient.php?user={patient_id}
 */

session_start();
require_once "../includes/db.php";
require_once "../includes/fpdf.php";
require_once "../includes/phpqrcode/qrlib.php";

// ----------------------------------------------------
// 1) Validación y resolución del paciente según el rol
// ----------------------------------------------------
$actingRole = $_SESSION['user']['role'] ?? null;
$userId     = $_SESSION['user']['id']   ?? null;

$patientId = null;
$patientUserRow = null;

// Verifica si viene un parámetro (por si se accede desde el QR)
if (!empty($_GET['patient_id'])) {
    $patientId = (int)$_GET['patient_id'];
} elseif ($actingRole === 'patient' && $userId) {
    // Si es paciente logueado, obtiene su propio ID
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    $patientId = $p['id'] ?? null;
}

if (!$patientId) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Parámetro inválido</title>
          <style>body{font-family:Poppins,sans-serif;text-align:center;padding-top:10%;color:#444;}
          h1{color:#d32f2f;} a{color:#1976d2;text-decoration:none;font-weight:bold;}</style></head><body>
          <h1>⚠ Parámetro inválido</h1>
          <p>Por favor accedé a la historia clínica desde el panel o escaneando el código QR correspondiente.</p>
          <a href='../login.php'>Ir al inicio</a>
          </body></html>";
    exit;
}

// ----------------------------------------------------
// 2) Obtener datos del paciente
// ----------------------------------------------------
$stmt = $pdo->prepare("
    SELECT p.id AS patient_id, u.id AS user_id, u.fullName, u.email,
           p.phone, p.address, p.birthdate, p.blood_type,
           p.allergies, p.medical_conditions, p.observaciones
    FROM patients p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = ?
    LIMIT 1
");
$stmt->execute([$patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("<h2 style='text-align:center;color:#d32f2f;'>Paciente no encontrado</h2>");
}

$fullName = $patient['fullName'] ?? 'Paciente';
$email    = $patient['email'] ?? '';

// ----------------------------------------------------
// 3) Cargar historia clínica (turnos, estudios, mensajes)
// ----------------------------------------------------
$appointments = [];
$studies      = [];
$messages     = [];

try {
    // Turnos
    $st = $pdo->prepare("
        SELECT a.appointment_date, a.appointment_time, a.status, a.video_call,
               du.fullName AS doctor_name, d.specialization
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $st->execute([$patientId]);
    $appointments = $st->fetchAll(PDO::FETCH_ASSOC);

    // Estudios
    $hasComment = false;
    try { $pdo->query("SELECT doctor_comment FROM studies LIMIT 1"); $hasComment = true; } catch (Throwable $e) {}
    $sql = "SELECT file_name, uploaded_at" . ($hasComment ? ", doctor_comment" : "") . "
            FROM studies WHERE patient_id = ? ORDER BY uploaded_at DESC";
    $st = $pdo->prepare($sql);
    $st->execute([$patientId]);
    $studies = $st->fetchAll(PDO::FETCH_ASSOC);

    // Mensajes
    try {
        $st = $pdo->prepare("SELECT sender, message, sent_at FROM messages WHERE patient_id=? ORDER BY sent_at DESC LIMIT 100");
        $st->execute([$patientId]);
        $messages = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
} catch (Throwable $e) {
    die("<h2 style='text-align:center;color:#d32f2f;'>Error al cargar datos clínicos.</h2>");
}

// ----------------------------------------------------
// 4) Configurar PDF
// ----------------------------------------------------
class HC_PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, utf8_decode('Historia Clínica Digital'), 0, 1, 'C');
        $this->Ln(4);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, utf8_decode('© ' . date('Y') . ' MedicareConnect - Sistema de Historia Clínica Digital'), 0, 0, 'C');
    }
}
$pdf = new HC_PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// ----------------------------------------------------
// 5) Datos del paciente
// ----------------------------------------------------
$pdf->Cell(0, 8, utf8_decode("Paciente: {$fullName}"), 0, 1);
$pdf->Cell(0, 8, utf8_decode("Email: {$email}"), 0, 1);
if (!empty($patient['phone']))      $pdf->Cell(0,8,utf8_decode("Teléfono: {$patient['phone']}"),0,1);
if (!empty($patient['address']))    $pdf->Cell(0,8,utf8_decode("Dirección: {$patient['address']}"),0,1);
if (!empty($patient['birthdate']))  $pdf->Cell(0,8,utf8_decode("Nacimiento: ".date('d/m/Y',strtotime($patient['birthdate']))),0,1);
if (!empty($patient['blood_type'])) $pdf->Cell(0,8,utf8_decode("Grupo sanguíneo: {$patient['blood_type']}"),0,1);
$pdf->Ln(8);

// ----------------------------------------------------
// 6) Síntomas / Condiciones / Observaciones
// ----------------------------------------------------
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('Síntomas y Problemas Médicos'), 0, 1);
$pdf->SetFont('Arial', '', 11);
if (!empty($patient['observaciones']))
    $pdf->MultiCell(0, 6, utf8_decode("• Observaciones: {$patient['observaciones']}"));
if (!empty($patient['medical_conditions']))
    $pdf->MultiCell(0, 6, utf8_decode("• Condiciones Médicas: {$patient['medical_conditions']}"));
if (empty($patient['observaciones']) && empty($patient['medical_conditions']))
    $pdf->Cell(0, 6, utf8_decode("Sin información registrada."), 0, 1);
$pdf->Ln(8);

// ----------------------------------------------------
// 7) Citas médicas
// ----------------------------------------------------
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('Citas registradas'), 0, 1);
$pdf->SetFont('Arial', '', 11);
if (!$appointments) {
    $pdf->Cell(0, 6, utf8_decode("No se registran citas."), 0, 1);
} else {
    foreach ($appointments as $a) {
        $fecha = $a['appointment_date'] ?? '';
        $hora  = substr($a['appointment_time'] ?? '', 0, 5);
        $doctor = $a['doctor_name'] ?? '';
        $estado = ucfirst($a['status'] ?? '');
        $pdf->MultiCell(0, 6, utf8_decode("• $fecha $hora - $doctor ($estado)"));
    }
}
$pdf->Ln(8);

// ----------------------------------------------------
// 8) Estudios y mensajes
// ----------------------------------------------------
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('Estudios cargados'), 0, 1);
$pdf->SetFont('Arial', '', 11);
if (!$studies) {
    $pdf->Cell(0, 6, utf8_decode("No se registran estudios."), 0, 1);
} else {
    foreach ($studies as $s) {
        $fn = $s['file_name'] ?? '';
        $when = $s['uploaded_at'] ?? '';
        $pdf->MultiCell(0, 6, utf8_decode("• $fn ($when)"));
    }
}
$pdf->Ln(8);

// Mensajes
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('Mensajes recientes'), 0, 1);
$pdf->SetFont('Arial', '', 11);
if (!$messages) {
    $pdf->Cell(0, 6, utf8_decode("No se registran mensajes."), 0, 1);
} else {
    foreach ($messages as $m) {
        $who = ucfirst($m['sender']);
        $msg = $m['message'];
        $when = $m['sent_at'];
        $pdf->MultiCell(0, 6, utf8_decode("• [$when] $who: $msg"));
    }
}
$pdf->Ln(10);

// ----------------------------------------------------
// 9) Generar QR (enlace funcional a verify_patient.php)
// ----------------------------------------------------
$baseURL = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
    ? "http://localhost/Medicareconnect"
    : "https://medicareconnect.site";

$verificationLink = $baseURL . "/patient/verify_patient.php?user=" . urlencode((string)$patientId);

// Crear QR grande y limpio
$qrDir = __DIR__ . '/../uploads/qr/';
if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);
$qrPath = $qrDir . 'qr_patient_' . md5($patientId) . '.png';
QRcode::png($verificationLink, $qrPath, QR_ECLEVEL_M, 8, 2);

// Insertar QR en PDF
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('Verificación Digital (Acceso Online)'), 0, 1, 'C');
$pdf->Image($qrPath, 88, $pdf->GetY(), 35, 35);
$pdf->Ln(45);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 8, utf8_decode('Escanee este código QR para ver la Historia Clínica completa en MedicareConnect.'), 0, 1, 'C');

// ----------------------------------------------------
// 10) Salida del PDF
// ----------------------------------------------------
$filename = 'Historia_Clinica_' . preg_replace('/\s+/', '_', $fullName) . '.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$pdf->Output('D', $filename);
exit;
?>
