<?php
session_start();
require_once "../includes/db.php";

// 🔒 Verificamos sesión y rol
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        // Si es una llamada AJAX
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Sesión no válida."]);
        exit();
    } else {
        header("Location: ../index.php");
        exit();
    }
}

$user_id = $_SESSION['user']['id'];

// ✅ Buscamos el ID del paciente
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "No se encontró el registro del paciente."]);
        exit();
    } else {
        die("Error: no se encontró el registro del paciente.");
    }
}

$patient_id = $patient['id'];

// Si vino una solicitud POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_id = $_POST['doctor_id'] ?? null;
    $appointment_date = $_POST['appointment_date'] ?? null;
    $appointment_time = $_POST['appointment_time'] ?? null;
    $video_call = isset($_POST['video_call']) ? 1 : 0;

    // Validación
    if (!$doctor_id || !$appointment_date || !$appointment_time) {
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
        exit();
    }

    // Verificar que el turno no esté ocupado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM appointments
        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
    ");
    $stmt->execute([$doctor_id, $appointment_date, $appointment_time]);
    if ($stmt->fetchColumn() > 0) {
        header("Content-Type: application/json");
        echo json_encode(["status" => "error", "message" => "Ese horario ya fue reservado."]);
        exit();
    }

    // Insertar el turno
    try {
        $stmt = $pdo->prepare("
            INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, video_call)
            VALUES (?, ?, ?, ?, 'pendiente', ?)
        ");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $video_call]);

        // ✅ Si se llama por AJAX
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header("Content-Type: application/json");
            echo json_encode(["status" => "success", "message" => "Turno confirmado con éxito."]);
            exit();
        }

    } catch (PDOException $e) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header("Content-Type: application/json");
            echo json_encode(["status" => "error", "message" => "Error al registrar el turno: " . $e->getMessage()]);
            exit();
        } else {
            die("❌ Error al registrar turno: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Turno Confirmado</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
.card {
    background: white;
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    max-width: 450px;
}
.card h2 {
    color: #1e40af;
    margin-bottom: 10px;
}
.card p {
    color: #333;
}
.btn {
    display: inline-block;
    margin-top: 20px;
    background: #1e40af;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}
.btn:hover {
    background: #0d2b86;
}
</style>
</head>
<body>
<div class="card">
    <h2>✅ Turno confirmado con éxito</h2>
    <p>Tu turno fue registrado y se encuentra pendiente de atención.</p>
    <a href="dashboard.php" class="btn">Volver al Panel</a>
</div>
</body>
</html>
