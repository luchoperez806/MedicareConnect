<?php
require_once "../includes/db.php";

if (isset($_GET['doctor_id']) && isset($_GET['fecha'])) {
    $doctor_id = intval($_GET['doctor_id']);
    $fecha = $_GET['fecha'];

    // Obtener los datos del médico
    $stmt = $pdo->prepare("SELECT workingDays, workingHours, frecuencia FROM doctors WHERE id = ?");
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($doctor) {
        $dias = explode(',', $doctor['workingDays']);
        $horas = explode('-', $doctor['workingHours']);
        $frecuencia = intval($doctor['frecuencia']); // en minutos

        $diaSemana = date('N', strtotime($fecha)); // 1 = Lunes, 7 = Domingo
        $nombreDia = ['','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'][$diaSemana];

        if (in_array($nombreDia, $dias)) {
            $inicio = new DateTime($horas[0]);
            $fin = new DateTime($horas[1]);
            $intervalo = new DateInterval('PT' . $frecuencia . 'M');

            $horarios = [];
            while ($inicio < $fin) {
                $horarios[] = $inicio->format('H:i');
                $inicio->add($intervalo);
            }

            echo json_encode(['status' => 'ok', 'horarios' => $horarios]);
        } else {
            echo json_encode(['status' => 'no', 'mensaje' => 'El médico no atiende ese día.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Médico no encontrado.']);
    }
}
?>
