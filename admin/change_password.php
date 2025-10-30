<?php
session_start();
include("../includes/db.php");
include("../includes/header.php"); // Incluye el header común

// Seguridad: solo admin logueado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$mensajeClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    // Traer la contraseña actual
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($actual, $user['password'])) {
        $mensaje = "⚠️ La contraseña actual no es correcta.";
        $mensajeClass = "error";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "⚠️ Las contraseñas nuevas no coinciden.";
        $mensajeClass = "error";
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $_SESSION['admin_id']]);

        $mensaje = "✅ Contraseña actualizada correctamente.";
        $mensajeClass = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .password-container {
            max-width: 450px;
            margin: 60px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.1);
        }

        .password-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1a237e;
        }

        .password-container label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
            color: #333;
        }

        .password-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .password-container button {
            width: 100%;
            padding: 12px;
            background: #3f51b5;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .password-container button:hover {
            background: #1a237e;
        }

        .mensaje {
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .mensaje.error {
            background: #fdecea;
            color: #b71c1c;
        }

        .mensaje.success {
            background: #e8f5e9;
            color: #1b5e20;
        }
    </style>
</head>
<body>
    <main>
        <div class="password-container">
            <h2>Cambiar Contraseña</h2>

            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $mensajeClass; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label for="actual">Contraseña actual:</label>
                <input type="password" name="actual" id="actual" required>

                <label for="nueva">Nueva contraseña:</label>
                <input type="password" name="nueva" id="nueva" required>

                <label for="confirmar">Confirmar nueva contraseña:</label>
                <input type="password" name="confirmar" id="confirmar" required>

                <button type="submit">Actualizar contraseña</button>
            </form>
        </div>
    </main>

    <?php include("../includes/footer.php"); ?>
</body>
</html>
