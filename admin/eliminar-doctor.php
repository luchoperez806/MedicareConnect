<?php
// admin/eliminar-doctor.php
session_start();
include('../includes/db.php'); // conexión correcta

// Seguridad: solo admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Validamos si llegó el ID
if (!isset($_GET['id'])) {
    die("ID de doctor no especificado.");
}

$id = intval($_GET['id']);

try {
    // Eliminamos el doctor usando la columna correcta "id"
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->execute([$id]);

    $mensaje = "Doctor eliminado correctamente";
} catch (PDOException $e) {
    $mensaje = "Error al eliminar doctor: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Doctor</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f8fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background: #fff;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
        }
        .message-box h2 {
            color: #1a237e;
            margin-bottom: 20px;
        }
        .message-box p {
            font-size: 1rem;
            color: #333;
            margin-bottom: 25px;
        }
        .message-box a {
            text-decoration: none;
            background: #3f51b5;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            transition: 0.3s;
        }
        .message-box a:hover {
            background: #303f9f;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2>Eliminar Doctor</h2>
        <p><?= htmlspecialchars($mensaje) ?></p>
        <a href="medicos.php">⬅ Volver a Médicos</a>
    </div>
</body>
</html>
