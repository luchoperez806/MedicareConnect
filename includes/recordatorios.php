<?php
require_once "db.php";

// 🚀 Buscar turnos confirmados dentro de las próximas 24h
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
    echo "✅ No hay recordatorios pendientes para enviar hoy.";
    exit;
}

foreach ($turnos as $t) {
    $paciente = htmlspecialchars($t['patient_name']);
    $telefono = htmlspecialchars($t['patient_phone']);
    $fecha = date('d/m/Y', strtotime($t['appointment_date']));
    $hora = substr($t['appointment_time'], 0, 5);
    $doctor = htmlspecialchars($t['doctor_name']);

    $mensaje = "📅 Hola $paciente, te recordamos tu turno con el Dr/a. $doctor el $fecha a las $hora en MedicareConnect.";

    // Aquí es donde iría el envío real vía API de WhatsApp o SMS.
    // Ejemplo con Twilio (descomentá y agregá tus credenciales):
    /*
    require_once 'vendor/autoload.php';
    use Twilio\Rest\Client;

    $sid = 'TU_TWILIO_SID';
    $token = 'TU_TWILIO_TOKEN';
    $twilio = new Client($sid, $token);
    $twilio->messages->create(
        $telefono,
        ["from" => "whatsapp:+14155238886", "body" => $mensaje]
    );
    */

    echo "📤 Enviar a $telefono → $mensaje<br>";
}

echo "<hr>Proceso finalizado.";
