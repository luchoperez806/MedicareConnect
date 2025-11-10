<?php
require_once "db.php";
require_once "enviar_sms.php";

echo "<h3>📅 Verificando turnos confirmados para mañana...</h3>";

// Buscar turnos confirmados para el día siguiente
$stmt = $pdo->prepare("
    SELECT 
        a.id AS appointment_id,
        a.appointment_date,
        a.appointment_time,
        u.fullName AS patient_name,
        p.phone AS patient_phone,
        d.id AS doctor_id,
        du.fullName AS doctor_name
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users du ON d.user_id = du.id
    WHERE a.status = 'confirmada'
      AND a.appointment_date = CURDATE() + INTERVAL 1 DAY
      AND p.phone IS NOT NULL
");
$stmt->execute();
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$turnos) {
    echo "<p>✅ No hay recordatorios pendientes.</p>";
    exit;
}

foreach ($turnos as $t) {
    $paciente = htmlspecialchars($t['patient_name']);
    $telefono = htmlspecialchars($t['patient_phone']);
    $fecha = date('d/m/Y', strtotime($t['appointment_date']));
    $hora = substr($t['appointment_time'], 0, 5);
    $doctor = htmlspecialchars($t['doctor_name']);

    $mensaje = "📅 Hola $paciente, te recordamos tu turno con el Dr/a. $doctor el $fecha a las $hora en MedicareConnect.";

    if (enviarMensajePaciente($telefono, $mensaje)) {
        echo "📤 Enviado a $telefono → $mensaje<br>";
    } else {
        echo "⚠️ Error al enviar a $telefono<br>";
    }
}

echo "<hr><strong>Proceso finalizado.</strong>";
