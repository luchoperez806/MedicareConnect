<?php
session_start();
require_once "../includes/db.php";
require_once "../includes/header.php";

// Seguridad: solo pacientes logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php?role=paciente");
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$mensaje = "";
$mensajeClass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $actual = trim($_POST['actual'] ?? '');
    $nueva = trim($_POST['nueva'] ?? '');
    $confirmar = trim($_POST['confirmar'] ?? '');

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($actual, $user['password'])) {
        $mensaje = "⚠️ La contraseña actual no es correcta.";
        $mensajeClass = "error";
    } elseif ($nueva !== $confirmar) {
        $mensaje = "⚠️ Las contraseñas nuevas no coinciden.";
        $mensajeClass = "error";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "⚠️ La nueva contraseña debe tener al menos 6 caracteres.";
        $mensajeClass = "error";
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        $mensaje = "✅ Contraseña actualizada correctamente.";
        $mensajeClass = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Contraseña | MediCareConnect</title>
<style>
body {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  font-family: 'Poppins', sans-serif;
  margin: 0;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.form-container {
  background: #fff;
  width: 400px;
  padding: 30px;
  border-radius: 20px;
  box-shadow: 0 10px 35px rgba(0,0,0,0.2);
  text-align: center;
  animation: fadeIn .4s ease;
}
h2 {
  margin-bottom: 20px;
  color: #1e3a8a;
  font-weight: 700;
}
label {
  display: block;
  text-align: left;
  font-weight: 600;
  margin-top: 10px;
  color: #334155;
}
input {
  width: 100%;
  padding: 12px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  margin-top: 6px;
  font-size: 15px;
  transition: border 0.3s;
}
input:focus {
  border-color: #3b82f6;
  outline: none;
}
button {
  margin-top: 20px;
  padding: 12px;
  width: 100%;
  background: linear-gradient(90deg,#3b82f6,#06b6d4);
  color: white;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  filter: brightness(1.1);
}
.mensaje {
  padding: 10px;
  border-radius: 8px;
  font-weight: 600;
  margin-bottom: 10px;
}
.mensaje.error { background: #fee2e2; color: #991b1b; }
.mensaje.success { background: #dcfce7; color: #065f46; }
.back {
  display: inline-block;
  margin-top: 15px;
  text-decoration: none;
  color: #3b82f6;
  font-weight: 600;
}
@keyframes fadeIn { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }
</style>
</head>
<body>

<div class="form-container">
  <h2>Cambiar Contraseña</h2>

  <?php if ($mensaje): ?>
    <div class="mensaje <?= $mensajeClass; ?>"><?= htmlspecialchars($mensaje); ?></div>
  <?php endif; ?>

  <form method="POST">
    <label>Contraseña actual</label>
    <input type="password" name="actual" required>

    <label>Nueva contraseña</label>
    <input type="password" name="nueva" required>

    <label>Confirmar nueva contraseña</label>
    <input type="password" name="confirmar" required>

    <button type="submit">Actualizar contraseña</button>
  </form>

  <a href="dashboard.php" class="back">← Volver al panel</a>
</div>

</body>
</html>
