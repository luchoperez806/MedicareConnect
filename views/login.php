<?php
session_start();
require_once '../db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'doctor') {
            header("Location: ../doctor/dashboard.php");
        } else {
            header("Location: ../patient/dashboard.php");
        }
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Iniciar Sesión</h2>
<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>
<form method="POST">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>
    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Ingresar</button>
</form>

<?php include '../includes/footer.php'; ?>
