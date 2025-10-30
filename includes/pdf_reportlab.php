<?php
define('FPDF_FONTPATH', __DIR__ . '/font/');
require_once(__DIR__ . '/fpdf.php');
require_once(__DIR__ . '/phpqrcode/qrlib.php');

function generatePatientHistoryPDF($user, $appointments, $studies, $messages) {
    // Inicializar PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Encabezado
    $pdf->Cell(0, 10, utf8_decode('Historia Clínica Digital'), 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode('Paciente: ') . utf8_decode($user['fullName']), 0, 1);
    $pdf->Cell(0, 8, 'Email: ' . $user['email'], 0, 1);
    $pdf->Ln(10);

    /* ======================== SECCIÓN 1: CITAS MÉDICAS ======================== */
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Citas Médicas'), 0, 1);
    $pdf->SetFont('Arial', '', 11);

    if (!empty($appointments)) {
        foreach ($appointments as $a) {
            $pdf->MultiCell(0, 8, sprintf("• %s %s - %s (%s)",
                $a['appointment_date'],
                substr($a['appointment_time'], 0, 5),
                utf8_decode($a['doctor_name']),
                ucfirst($a['status'])
            ));
        }
    } else {
        $pdf->Cell(0, 8, utf8_decode('No hay citas registradas.'), 0, 1);
    }

    $pdf->Ln(8);

    /* ======================== SECCIÓN 2: ESTUDIOS ======================== */
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Estudios Cargados'), 0, 1);
    $pdf->SetFont('Arial', '', 11);

    if (!empty($studies)) {
        foreach ($studies as $s) {
            $pdf->MultiCell(0, 8, sprintf("• %s (%s)",
                utf8_decode($s['file_name']),
                $s['uploaded_at']
            ));
        }
    } else {
        $pdf->Cell(0, 8, utf8_decode('No hay estudios cargados.'), 0, 1);
    }

    $pdf->Ln(8);

    /* ======================== SECCIÓN 3: MENSAJES ======================== */
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, utf8_decode('Mensajes Recientes'), 0, 1);
    $pdf->SetFont('Arial', '', 11);

    if (!empty($messages)) {
        foreach ($messages as $m) {
            $pdf->MultiCell(0, 8, sprintf("• [%s] %s: %s",
                $m['sent_at'],
                ucfirst($m['sender']),
                utf8_decode($m['message'])
            ));
        }
    } else {
        $pdf->Cell(0, 8, utf8_decode('Sin mensajes registrados.'), 0, 1);
    }

    $pdf->Ln(10);

    /* ========================
       SECCIÓN 4: QR DE VERIFICACIÓN
       ======================== */

    // Detectar si estamos en localhost o en hosting
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseURL = (strpos($host, 'localhost') !== false)
        ? "http://localhost/Medicareconnect"
        : "https://$host";

    $verificationLink = $baseURL . "/patient/verify_patient.php?user=" . urlencode($user['email']);

    // Crear carpeta si no existe
    $qrDir = __DIR__ . '/../uploads/qr/';
    if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);

    $qrPath = $qrDir . 'qr_' . md5($user['email']) . '.png';

    // Generar el QR (baja redundancia y tamaño controlado)
    QRcode::png($verificationLink, $qrPath, 'L', 3, 2);

    // Insertar el QR en el PDF
    $pdf->Image($qrPath, 170, 250, 28, 28);
    $pdf->SetY(-15);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 10, utf8_decode('Verifique autenticidad escaneando el código QR'), 0, 0, 'R');

    // Guardar archivo PDF en /uploads
    $filePath = __DIR__ . '/../uploads/historia_' . time() . '.pdf';
    $pdf->Output('F', $filePath);

    return $filePath;
}
?>
