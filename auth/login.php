<?php
session_start();
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullName'];
        header("Location: ../dashboard.php"); // Redirige al dashboard
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <a href="../index.php">Volver al Inicio</a>
</header>

<div class="container">
    <h2>Iniciar Sesión</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Correo" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>
    <p>¿No tienes cuenta? <a href="register.php">Registrarse</a></p>
</div>

<footer>
    &copy; <?php echo date("Y"); ?> MediCareConnect / Todos los derecho reservados
</footer>
</body>
</html>
