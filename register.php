<?php
session_start();
require_once __DIR__ . "/includes/db.php";

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($fullName) || empty($email) || empty($password)) {
        $error = "Por favor completá todos los campos.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Este correo ya está registrado.";
        } else {
            // Crear usuario
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, 'patient')");
            $stmt->execute([$fullName, $email, $hashedPass]);

            $user_id = $pdo->lastInsertId();

            // Crear registro del paciente
            $stmt = $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)");
            $stmt->execute([$user_id]);

            // Iniciar sesión automáticamente
            $_SESSION['user'] = [
                'id' => $user_id,
                'fullName' => $fullName,
                'email' => $email,
                'role' => 'patient'
            ];

            $success = "Registro exitoso. Redirigiendo a tu panel...";
            header("refresh:2; url=patient/dashboard.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Paciente | MediCareConnect</title>
    <style>
        body {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-container {
            background: #fff;
            padding: 40px 35px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            width: 380px;
            text-align: center;
        }
        h2 {
            color: #0f172a;
            margin-bottom: 25px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        input {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            width: 100%;
        }
        input:focus {
            border-color: #06b6d4;
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            transform: translateY(-2px);
            background: linear-gradient(90deg, #2563eb, #0891b2);
        }
        .msg {
            margin-top: 10px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .error { color: #dc2626; }
        .success { color: #16a34a; }
        a {
            display: block;
            margin-top: 15px;
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
        }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>🩺 Crear cuenta de paciente</h2>
        <form method="POST">
            <input type="text" name="fullName" placeholder="Nombre completo" required>
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required minlength="6">
            <button type="submit">Registrarme</button>
        </form>

        <?php if ($error): ?>
            <p class="msg error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p class="msg success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <a href="login.php?role=paciente">Ya tengo una cuenta → Ingresar</a>
    </div>
</body>
</html>
