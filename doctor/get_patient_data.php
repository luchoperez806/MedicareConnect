<?php
header('Content-Type: application/json; charset=utf-8');
require_once("../includes/db.php");

$logFile = __DIR__ . '/../debug_log.txt';
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] get_patient_data.php ejecutado\n", FILE_APPEND);

if (empty($_GET['patient_id']) || !is_numeric($_GET['patient_id'])) {
    file_put_contents($logFile, "❌ ID inválido o ausente\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'ID de paciente inválido.']);
    exit();
}

$patient_id = (int)$_GET['patient_id'];

// ✅ Buscar paciente por ID en tabla patients (y traer user)
$stmt = $pdo->prepare("
    SELECT 
        u.fullName, u.email, 
        p.phone, p.address, p.birthdate, 
        p.blood_type, p.allergies, p.medical_conditions, 
        p.emergency_contact_name, p.emergency_contact_phone
    FROM patients p
    LEFT JOIN users u ON u.id = p.user_id
    WHERE p.id = :id OR p.user_id = :id
    LIMIT 1
");
$stmt->execute(['id' => $patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if ($patient) {
    file_put_contents($logFile, "✅ Paciente encontrado correctamente\n", FILE_APPEND);
    echo json_encode(['success' => true, 'patient' => $patient]);
} else {
    file_put_contents($logFile, "⚠️ No se encontró paciente con ID $patient_id\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Paciente no encontrado.']);
}
