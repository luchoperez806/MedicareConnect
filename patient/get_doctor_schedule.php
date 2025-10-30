<?php
header('Content-Type: application/json');
require_once "../includes/db.php";

// Verificar que vengan los datos
if (!isset($_GET['doctor_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$doctor_id = intval($_GET['doctor_id']);
$date = $_GET['date'];

// Obtener datos del doctor (días y horarios)
$stmt = $pdo->prepare("SELECT workingDays, workingHours, frequency FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    echo json_encode([]);
    exit;
}

// Convertir datos del doctor
$workingDays = explode(',', strtolower(trim($doctor['workingDays'])));
$workingHours = explode('-', trim($doctor['workingHours']));
$frequency = isset($doctor['frequency']) ? intval($doctor['frequency']) : 20;

// Verificar si el día elegido es laborable para el doctor
$dayOfWeek = strtolower(date('l', strtotime($date))); // monday, tuesday, etc.
$diasEnEspañol = [
    'monday' => 'lunes',
    'tuesday' => 'martes',
    'wednesday' => 'miércoles',
    'thursday' => 'jueves',
    'friday' => 'viernes',
    'saturday' => 'sábado',
    'sunday' => 'domingo'
];
$dayName = $diasEnEspañol[$dayOfWeek] ?? $dayOfWeek;

if (!in_array($dayName, $workingDays)) {
    echo json_encode([]); // ese día no trabaja
    exit;
}

// Generar todos los horarios posibles según la frecuencia
function generarHorarios($inicio, $fin, $frecuenciaMinutos) {
    $horarios = [];
    $horaInicio = strtotime($inicio);
    $horaFin = strtotime($fin);

    while ($horaInicio < $horaFin) {
        $horarios[] = date('H:i', $horaInicio);
        $horaInicio = strtotime("+{$frecuenciaMinutos} minutes", $horaInicio);
    }
    return $horarios;
}

$todosHorarios = generarHorarios($workingHours[0], $workingHours[1], $frequency);

// Buscar los horarios ya ocupados ese día
$stmt2 = $pdo->prepare("SELECT appointment_time FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$stmt2->execute([$doctor_id, $date]);
$turnosOcupados = $stmt2->fetchAll(PDO::FETCH_COLUMN);

$horariosDisponibles = array_values(array_diff($todosHorarios, $turnosOcupados));

echo json_encode($horariosDisponibles);
?>
