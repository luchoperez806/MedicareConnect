<?php
require_once("../includes/db.php");
require_once("../includes/phpqrcode/qrlib.php");

if (!isset($_GET['user'])) {
    die("<h2>Falta el parámetro de usuario</h2>");
}

$userParam = $_GET['user'];

// Buscar paciente por ID o email
if (filter_var($userParam, FILTER_VALIDATE_EMAIL)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$userParam]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userParam]);
}

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("<h3>No se encontró ningún registro asociado a este paciente.</h3>");
}

// Buscar sus citas y estudios
$stmt = $pdo->prepare("
    SELECT a.*, u.fullName AS doctor_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    JOIN patients p ON a.patient_id = p.id
    WHERE p.user_id = ?
    ORDER BY a.appointment_date DESC
");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT file_name, uploaded_at
    FROM studies s
    JOIN patients p ON s.patient_id = p.id
    WHERE p.user_id = ?
    ORDER BY uploaded_at DESC
");
$stmt->execute([$user['id']]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar QR si no existe
$qrDir = __DIR__ . '/../uploads/qr/';
if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);
$qrPath = $qrDir . 'qr_' . md5($user['email']) . '.png';

if (!file_exists($qrPath)) {
    $verificationLink = "http://localhost/Medicareconnect/patient/verify_patient.php?user=" . urlencode($user['email']);
    QRcode::png($verificationLink, $qrPath, 'L', 4, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de Historia Clínica Digital</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #eef5ff; margin: 0; padding: 40px; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px; max-width: 700px; margin: auto; }
        h1 { color: #1e3a8a; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f1f5f9; }
        .qr { text-align: center; margin-top: 20px; }
        .verified { color: white; background: #16a34a; padding: 6px 12px; border-radius: 8px; font-weight: 600; display: inline-block; margin-top: 10px; }
        .footer { text-align: center; color: #6b7280; font-size: 0.85rem; margin-top: 25px; }
    </style>
</head>
<body>
<div class="card">
    <h1> Verificación de Historia Clínica Digital</h1>
    <hr>

    <h2><?php echo htmlspecialchars($user['fullName']); ?></h2>
    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    <span class="verified">Registro verificado</span>

    <h3>Últimas citas</h3>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Hora</th><th>Médico</th><th>Estado</th></tr>
        </thead>
        <tbody>
        <?php if (empty($appointments)): ?>
            <tr><td colspan="4"><em>Sin registros</em></td></tr>
        <?php else: ?>
            <?php foreach ($appointments as $a): ?>
            <tr>
                <td><?php echo $a['appointment_date']; ?></td>
                <td><?php echo substr($a['appointment_time'], 0, 5); ?></td>
                <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                <td><?php echo ucfirst($a['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h3>Últimos estudios</h3>
    <table>
        <thead><tr><th>Archivo</th><th>Fecha</th></tr></thead>
        <tbody>
        <?php if (empty($studies)): ?>
            <tr><td colspan="2"><em>No hay estudios subidos.</em></td></tr>
        <?php else: ?>
            <?php foreach ($studies as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['file_name']); ?></td>
                <td><?php echo htmlspecialchars($s['uploaded_at']); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="qr">
        <h4>🧾 Código de Verificación:</h4>
        <img src="<?php echo '../uploads/qr/qr_' . md5($user['email']) . '.png'; ?>" alt="QR de verificación" width="130">
        <p style="color:#6b7280;font-size:0.9rem">Escaneá este código para validar la autenticidad del registro.</p>
    </div>

    <div class="footer">
        MedicareConnect © <?php echo date('Y'); ?> — Historia Clínica Digital Segura.
    </div>
</div>
</body>
</html>
