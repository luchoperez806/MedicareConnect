<?php
session_start();
include("../includes/db.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['fullName'];
        header("Location: dashboard.php");
        exit();
    } else {
        $mensaje = "Credenciales inválidas o usuario no autorizado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        header {
            background: #004aad;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
            font-size: 20px;
        }
        header a {
            background: white;
            color: #004aad;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }
        header a:hover {
            background: #e0e0e0;
        }
        .container {
            max-width: 400px;
            margin: 60px auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #004aad;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #004aad;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover {
            background: #00347d;
        }
        .mensaje {
            margin-top: 15px;
            text-align: center;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header general con botón para volver al inicio -->
    <header>
        <h1>Panel de Administrador</h1>
        <a href="../index.php">Volver al Inicio</a>
    </header>

    <div class="container">
        <h2>Iniciar Sesión</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>

        <?php if (!empty($mensaje)): ?>
            <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>
    </div>

</body>
</html>
