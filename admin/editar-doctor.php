<?php
// admin/editar-doctor.php
session_start();
include("../includes/db.php"); // conexión $pdo

// Validamos si llegó el ID
if (!isset($_GET['id'])) {
    die("ID de doctor no especificado.");
}

$id = intval($_GET['id']);

// Obtenemos los datos del doctor
$stmt = $pdo->prepare("SELECT * FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor no encontrado.");
}

// Actualizar datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName          = $_POST['fullName'];
    $specialization    = $_POST['specialization'];
    $office_address    = $_POST['office_address'];
    $working_days      = $_POST['working_days'];
    $working_hours     = $_POST['working_hours'];
    $consultation_fee  = $_POST['consultation_fee'];

    // Actualizamos en doctors y users
    $updateUsers = $pdo->prepare("UPDATE users SET fullName=? WHERE id=?");
    $updateUsers->execute([$fullName, $doctor['user_id']]);

    $updateDoctors = $pdo->prepare("UPDATE doctors
        SET specialization=?, office_address=?, working_days=?, working_hours=?, consultation_fee=?
        WHERE id=?");

    if ($updateDoctors->execute([$specialization, $office_address, $working_days, $working_hours, $consultation_fee, $id])) {
        header("Location: medicos.php?msg=Doctor actualizado correctamente");
        exit();
    } else {
        echo "Error al actualizar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Doctor</title>
    <style>
        /* Reset y tipografía */
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f8fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        /* Contenedor del formulario */
        .container {
            background: #fff;
            padding: 30px 40px;
            margin: 40px auto;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            width: 450px;
        }

        h2 {
            text-align: center;
            color: #1a237e;
            margin-bottom: 25px;
        }

        /* Estilos de etiquetas e inputs */
        form label {
            display: block;
            margin-top: 15px;
            font-weight: 500;
            color: #333;
        }
        form input[type="text"],
        form input[type="number"] {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            border-radius: 8px;
            border: 1px solid #cbd2d9;
            transition: 0.3s;
            font-size: 1rem;
        }
        form input[type="text"]:focus,
        form input[type="number"]:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 5px rgba(63,81,181,0.3);
            outline: none;
        }

        /* Botón de guardar */
        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background: #3f51b5;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #303f9f;
        }

        /* Link volver */
        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #3f51b5;
            font-weight: 500;
            text-align: center;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Doctor</h2>
        <form method="POST">
            <label>Nombre Completo:</label>
            <input type="text" name="fullName" value="<?= htmlspecialchars($doctor['fullName']) ?>" required>

            <label>Especialidad:</label>
            <input type="text" name="specialization" value="<?= htmlspecialchars($doctor['specialization']) ?>" required>

            <label>Dirección del Consultorio:</label>
            <input type="text" name="office_address" value="<?= htmlspecialchars($doctor['office_address']) ?>" required>

            <label>Días de trabajo:</label>
            <input type="text" name="working_days" value="<?= htmlspecialchars($doctor['working_days']) ?>" required>

            <label>Horario de trabajo:</label>
            <input type="text" name="working_hours" value="<?= htmlspecialchars($doctor['working_hours']) ?>" required>

            <label>Honorarios:</label>
            <input type="number" name="consultation_fee" step="0.01" value="<?= htmlspecialchars($doctor['consultation_fee']) ?>" required>

            <button type="submit">Guardar Cambios</button>
        </form>
        <a href="medicos.php">⬅ Volver</a>
    </div>
</body>
</html>

