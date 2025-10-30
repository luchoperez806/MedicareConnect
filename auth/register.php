<?php
// register.php

session_start();
require '../includes/db.php';

$message = "";

// Procesar el registro si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "⚠️ Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "⚠️ El correo electrónico no es válido.";
    } elseif ($password !== $confirmPassword) {
        $message = "⚠️ Las contraseñas no coinciden.";
    } else {
        try {
            // Verificar si el email ya está registrado
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $message = "⚠️ Este correo ya está registrado.";
            } else {
                // Insertar usuario nuevo
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$fullName, $email, $hash]);

                $message = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
            }
        } catch (PDOException $e) {
            $message = "❌ Error en el registro: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - MediCareConnect</title>
    <style>
        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #e0f7fa, #e3f2fd);
        margin: 0;
        padding: 0;
        }
        header, footer {
        background: #1565c0;
        color: white;
        text-align: center;
        padding: 15px;
        }
        header a {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        }
        .container {
        max-width: 400px;
        margin: 50px auto;
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #1565c0;
        }
        .form-group {
        margin-bottom: 15px;
        }
        label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        outline: none;
        transition: 0.3s;
        }
        input:focus {
        border-color: #1565c0;
        box-shadow: 0 0 5px rgba(21,101,192,0.3);
        }
        button {
        width: 100%;
        padding: 12px;
        background: #1565c0;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
        }
        button:hover {
        background: #0d47a1;
        }
        .message {
        text-align: center;
        margin: 15px 0;
        font-weight: bold;
        color: #d32f2f;
        }
        .success {
        color: #2e7d32;
        }
        footer {
        margin-top: 40px;
        }
    </style>
    </head>
    <body>
    <header>
        <a href="../index.php">Volver al inicio</a>
    </header>

    <div class="container">
        <h2>Crear cuenta</h2>

        <?php if (!empty($message)) : ?>
        <p class="message <?php echo (strpos($message, '✅') !== false) ? 'success' : ''; ?>">
            <?php echo $message; ?>
        </p>
        <?php endif; ?>

        <form method="POST" action="">
        <div class="form-group">
            <label for="fullName">Nombre completo</label>
            <input type="text" name="fullName" id="fullName" required>
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="confirmPassword">Confirmar contraseña</label>
            <input type="password" name="confirmPassword" id="confirmPassword" required>
        </div>

        <button type="submit">Registrarse</button>
        </form>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> MediCareConnect - Todos los derechos reservados
    </footer>
</body>
</html>
