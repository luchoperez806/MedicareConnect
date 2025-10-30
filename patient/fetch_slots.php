<?php
// Devuelve las franjas horarias disponibles solo si el médico trabaja ese día
header('Content-Type: application/json');
require_once "../includes/db.php";

$doctor_id = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';

if (!$doctor_id || !$date) {
    echo json_encode(['error' => 'Faltan parámetros', 'slots' => []]);
    exit;
}

// Obtener datos del doctor
$stmt = $pdo->prepare("SELECT working_days, working_hours FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    echo json_encode(['error' => 'Médico no encontrado', 'slots' => []]);
    exit;
}

// --- Verificar si el médico trabaja ese día ---
$working_days = strtolower($doc['working_days']); // ejemplo: "lunes, miércoles, viernes"
$dayOfWeek = strtolower(strftime('%A', strtotime($date))); // obtiene el día en español

// Normalizar nombres en inglés/español
$dayMap = [
    'monday' => 'lunes',
    'tuesday' => 'martes',
    'wednesday' => 'miércoles',
    'thursday' => 'jueves',
    'friday' => 'viernes',
    'saturday' => 'sábado',
    'sunday' => 'domingo'
];
if (isset($dayMap[$dayOfWeek])) $dayOfWeek = $dayMap[$dayOfWeek];

// Si el día no está en la lista, no hay horarios
if (stripos($working_days, $dayOfWeek) === false) {
    echo json_encode(['slots' => [], 'message' => "El médico no atiende los $dayOfWeek."]);
    exit;
}

// --- Generar franjas horarias ---
$working_hours = $doc['working_hours']; // ejemplo "09:00-12:00,14:00-18:00"
$intervalo = 20; // minutos entre turnos
$rangos = array_map('trim', explode(',', $working_hours));

function sumarMinutos($hora, $min) {
    $dt = DateTime::createFromFormat('H:i', $hora);
    if (!$dt) return false;
    $dt->modify("+{$min} minutes");
    return $dt->format('H:i');
}

// Generar todas las franjas
$franjas = [];
foreach ($rangos as $rango) {
    if (strpos($rango, '-') === false) continue;
    list($inicio, $fin) = explode('-', $rango);
    $horaActual = trim($inicio);
    while ($horaActual < $fin) {
        $franjas[] = $horaActual;
        $horaActual = sumarMinutos($horaActual, $intervalo);
    }
}

// --- Filtrar turnos ocupados ---
$ocupados = [];
try {
    $query = $pdo->prepare("SELECT hora FROM appointments WHERE doctor_id = ? AND fecha = ?");
    $query->execute([$doctor_id, $date]);
    $ocupados = array_column($query->fetchAll(PDO::FETCH_ASSOC), 'hora');
} catch (Exception $e) {
    // Si no existe la tabla 'appointments', ignoramos
    $ocupados = [];
}

$slots = [];
foreach ($franjas as $h) {
    $slots[] = [
        'time' => $h,
        'available' => !in_array($h, $ocupados)
    ];
}

echo json_encode(['slots' => $slots]);
