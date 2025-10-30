<?php
/**
 * Exporta la Historia Clínica del paciente a PDF
 * - Paciente logueado: exporta la propia
 * - Admin: puede pasar ?patient_id=ID
 */

session_start();
require_once "../includes/db.php";
require_once "../includes/pdf_reportlab.php"; // Debe exponer la clase FPDF

// ---------------------------
// 1) Resolver el patient_id
// ---------------------------
$actingRole = $_SESSION['user']['role'] ?? null;
$userId     = $_SESSION['user']['id']   ?? null;

$patientId = null;
$patientUserRow = null;

try {
    if ($actingRole === 'admin' && !empty($_GET['patient_id'])) {
        // Admin exportando la HC de un paciente específico
        $patientId = (int)$_GET['patient_id'];

        $stmt = $pdo->prepare("
            SELECT p.id AS patient_id, u.id AS user_id, u.fullName, u.email
            FROM patients p
            JOIN users u ON u.id = p.user_id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$patientId]);
        $patientUserRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$patientUserRow) {
            http_response_code(404);
            die("Paciente no encontrado.");
        }
    } elseif ($actingRole === 'patient' && $userId) {
        // Paciente exportando la propia
        $stmt = $pdo->prepare("SELECT id, user_id FROM patients WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$p) {
            http_response_code(404);
            die("Paciente no encontrado.");
        }
        $patientId = (int)$p['id'];

        $stmt = $pdo->prepare("SELECT fullName, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        $patientUserRow = [
            'patient_id' => $patientId,
            'user_id'    => $userId,
            'fullName'   => $u['fullName'] ?? 'Paciente',
            'email'      => $u['email'] ?? '',
        ];
    } else {
        // No autorizado
        header("Location: ../login.php");
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    die("Error al resolver el paciente: " . htmlspecialchars($e->getMessage()));
}

$fullName = $patientUserRow['fullName'] ?? 'Paciente';
$email    = $patientUserRow['email']    ?? '';

// ---------------------------
// 2) Cargar datos clínicos
// ---------------------------
$appointments = [];
$studies      = [];
$messages     = [];

try {
    // Citas
    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.video_call,
               du.fullName AS doctor_name, d.specialization
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$patientId]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estudios
    // Intentar traer doctor_comment si existe la columna (evitar error si no está en algunos entornos)
    $hasComment = false;
    try {
        $pdo->query("SELECT doctor_comment FROM studies LIMIT 1");
        $hasComment = true;
    } catch (Exception $e) { /* columna no existe; seguimos sin comentarios */ }

    $sqlStudies = "
        SELECT file_name, uploaded_at" . ($hasComment ? ", doctor_comment" : "") . "
        FROM studies
        WHERE patient_id = ?
        ORDER BY uploaded_at DESC
    ";
    $stmt = $pdo->prepare($sqlStudies);
    $stmt->execute([$patientId]);
    $studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mensajes (si existe la tabla)
    try {
        $stmt = $pdo->prepare("
            SELECT sender, message, sent_at
            FROM messages
            WHERE patient_id = ?
            ORDER BY sent_at DESC
            LIMIT 100
        ");
        $stmt->execute([$patientId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Sin tabla messages: omitimos sección
        $messages = [];
    }

} catch (Exception $e) {
    http_response_code(500);
    die("Error al cargar datos clínicos: " . htmlspecialchars($e->getMessage()));
}

// ---------------------------
// 3) PDF (FPDF)
// ---------------------------
if (!class_exists('FPDF')) {
    http_response_code(500);
    die("No se encontró FPDF. Asegurate que includes/pdf_reportlab.php incluye/define la clase FPDF.");
}

class HC_PDF extends FPDF
{
    public string $docTitle = 'Historia Clínica Digital';
    public string $brand    = 'MedicareConnect';
    public string $patient  = '';

    function Header()
    {
        // Logo opcional (comentar si no existe archivo)
        // $this->Image(__DIR__ . '/../assets/images/logo.png', 10, 8, 14);

        // Título
        $this->SetTextColor(33, 37, 41); // gris oscuro
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, utf8_decode($this->docTitle), 0, 1, 'R');

        // Línea fina
        $this->SetDrawColor(200, 220, 255);
        $this->SetLineWidth(0.4);
        $this->Line(10, 22, 200, 22);
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-18);
        $this->SetDrawColor(230, 235, 245);
        $this->SetLineWidth(0.2);
        $this->Line(10, $this->GetY(), 200, $this->GetY());

        $this->SetY(-15);
        $this->SetTextColor(108, 117, 125);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, utf8_decode("Generado por {$this->brand} • " . date('d/m/Y H:i')), 0, 0, 'L');
        $this->Cell(0, 6, utf8_decode("Pág. ") . $this->PageNo() . "/{nb}", 0, 0, 'R');
    }

    function SectionTitle($txt)
    {
        $this->Ln(2);
        $this->SetTextColor(33, 37, 41);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, utf8_decode($txt), 0, 1, 'L');
        // subrayado suave
        $this->SetDrawColor(185, 210, 245);
        $this->SetLineWidth(0.3);
        $y = $this->GetY();
        $this->Line(10, $y, 200, $y);
        $this->Ln(2);
    }

    function KV($k, $v)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(51, 65, 85);
        $this->Cell(42, 6, utf8_decode($k . ':'), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(33, 37, 41);
        $this->MultiCell(0, 6, utf8_decode($v));
    }

    function Row2Cols($left, $right)
    {
        $x = $this->GetX();
        $y = $this->GetY();
        $w = 95;

        // Left
        $this->SetFont('Arial', '', 10);
        $this->MultiCell($w, 6, utf8_decode($left), 0, 'L');
        $h = $this->GetY() - $y;

        // Right
        $this->SetXY($x + $w + 10, $y);
        $this->MultiCell(0, 6, utf8_decode($right), 0, 'L');

        $h2 = $this->GetY() - $y;
        $this->SetY($y + max($h, $h2) + 2);
    }
}

$pdf = new HC_PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->docTitle = 'Historia Clínica Digital';
$pdf->patient  = $fullName;
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Encabezado de paciente
$pdf->SectionTitle("Datos del paciente");
$pdf->KV("Nombre completo", $fullName);
if ($email) $pdf->KV("Email", $email);

// Si querés sumar más datos del paciente (teléfono, nacimiento) intenta leerlos si existen:
try {
    $stmt = $pdo->prepare("SELECT phone, birthdate FROM patients WHERE id = ? LIMIT 1");
    $stmt->execute([$patientId]);
    if ($pr = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($pr['phone']))     $pdf->KV("Teléfono", $pr['phone']);
        if (!empty($pr['birthdate'])) $pdf->KV("Fecha de nacimiento", date('d/m/Y', strtotime($pr['birthdate'])));
    }
} catch (Exception $e) { /* opcional */ }

// Citas
$pdf->Ln(2);
$pdf->SectionTitle("Citas");
if (!$appointments) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, utf8_decode("No se registran citas."), 0, 1);
} else {
    foreach ($appointments as $a) {
        $fecha = $a['appointment_date'] ? date('d/m/Y', strtotime($a['appointment_date'])) : '';
        $hora  = $a['appointment_time'] ? substr($a['appointment_time'], 0, 5) : '';
        $linea1 = "• $fecha $hora  —  " . ($a['doctor_name'] ?? 'Médico') . (isset($a['specialization']) && $a['specialization'] ? " ({$a['specialization']})" : "");
        $estado = ucfirst($a['status'] ?? '');
        $video  = !empty($a['video_call']) ? "Teleconsulta: Sí" : "Teleconsulta: No";

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(20, 24, 31);
        $pdf->Cell(0, 6, utf8_decode($linea1), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 64, 67);
        $pdf->Row2Cols("Estado: $estado", $video);
    }
}

// Estudios
$pdf->Ln(2);
$pdf->SectionTitle("Estudios");
if (!$studies) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 6, utf8_decode("No se registran estudios."), 0, 1);
} else {
    foreach ($studies as $s) {
        $fn   = $s['file_name'] ?? '';
        $when = !empty($s['uploaded_at']) ? date('d/m/Y H:i', strtotime($s['uploaded_at'])) : '';
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(20, 24, 31);
        $pdf->Cell(0, 6, utf8_decode("• $fn"), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 64, 67);
        $rowRight = "Fecha de carga: $when";
        if (array_key_exists('doctor_comment', $s) && !empty($s['doctor_comment'])) {
            $pdf->Row2Cols("Comentario del médico:\n" . $s['doctor_comment'], $rowRight);
        } else {
            $pdf->Row2Cols("Comentario del médico: —", $rowRight);
        }
    }
}

// Mensajes (si hay)
if (!empty($messages)) {
    $pdf->Ln(2);
    $pdf->SectionTitle("Mensajes (últimos)");
    foreach ($messages as $m) {
        $who  = ($m['sender'] ?? '') === 'doctor' ? 'Médico' : 'Paciente';
        $when = !empty($m['sent_at']) ? date('d/m/Y H:i', strtotime($m['sent_at'])) : '';
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(20, 24, 31);
        $pdf->Cell(0, 6, utf8_decode("• $who — $when"), 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(60, 64, 67);
        $pdf->MultiCell(0, 6, utf8_decode($m['message'] ?? ''));
        $pdf->Ln(1);
    }
}

// Nota / verificación (texto simple)
$pdf->Ln(2);
$pdf->SectionTitle("Verificación");
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(60, 64, 67);
$verifyUrl = (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : '') . '/verificar';
$pdf->MultiCell(0, 6, utf8_decode("Documento generado digitalmente por MedicareConnect. Para verificar la autenticidad, contacte a su proveedor o visite: $verifyUrl"));

$filename = 'Historia_Clinica_' . preg_replace('/\s+/', '_', $fullName) . '.pdf';

// Forzar descarga
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$pdf->Output('D', $filename);
exit;
