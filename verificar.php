<?php
// ==========================================================
//  Sistema de Verificación Digital - MedicareConnect
// ==========================================================
require_once "includes/db.php";
$code = $_GET['code'] ?? '';
$isValid = false;
$record = null;

// ✅ Validar que el código exista en la tabla verification_codes
if (preg_match('/^[a-f0-9]{32}$/i', $code)) {
    $stmt = $pdo->prepare("SELECT v.*, u.fullName AS patient_name
                           FROM verification_codes v
                           LEFT JOIN patients p ON v.patient_id = p.id
                           LEFT JOIN users u ON p.user_id = u.id
                           WHERE v.code = ? LIMIT 1");
    $stmt->execute([$code]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    $isValid = $record ? true : false;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Verificación de Documento | MedicareConnect</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.card {
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    padding: 30px;
    max-width: 520px;
    background: #fff;
}
h1 {
    font-weight: 800;
    color: #2563eb;
    margin-bottom: 10px;
}
.result-icon {
    font-size: 60px;
    margin-bottom: 15px;
}
.valid { color: #16a34a; }
.invalid { color: #dc2626; }
footer {
    text-align: center;
    color: #6b7280;
    margin-top: 20px;
    font-size: 0.85rem;
}
.info {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px;
    margin-top: 15px;
}
.info strong { color: #1e3a8a; }
</style>
</head>
<body>

<div class="card text-center animate__animated animate__fadeIn">
    <?php if ($isValid): ?>
        <div class="result-icon valid">✅</div>
        <h1>Documento Válido</h1>
        <p>Este documento fue emitido por <strong>MedicareConnect</strong> y verificado en línea.</p>

        <div class="info text-start">
            <p><strong>Paciente:</strong> <?php echo htmlspecialchars($record['patient_name'] ?? 'Desconocido'); ?></p>
            <p><strong>Código:</strong> <?php echo htmlspecialchars($record['code']); ?></p>
            <p><strong>Emitido el:</strong> <?php echo htmlspecialchars($record['created_at']); ?></p>
        </div>

        <hr>
        <a href="index.php" class="btn btn-primary mt-3">Volver al inicio</a>
    <?php else: ?>
        <div class="result-icon invalid">❌</div>
        <h1>Código No Reconocido</h1>
        <p>El documento no se encuentra registrado o el código ingresado no es válido.</p>
        <p class="text-muted small">Verificá que el QR o el enlace sean correctos.</p>
        <hr>
        <a href="index.php" class="btn btn-outline-danger mt-3">Volver</a>
    <?php endif; ?>
</div>

<footer>
    © <?php echo date('Y'); ?> MedicareConnect · Sistema de Verificación Digital
</footer>

</body>
</html>
