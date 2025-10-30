<?php
session_start();
require_once "../includes/db.php";

// Seguridad: solo médicos logueados
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: ../login.php?role=doctor");
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
<title>Cambiar Contraseña | Médico</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  font-family: 'Poppins', sans-serif;
  margin: 0;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.container {
  background: #fff;
  width: 380px;
  padding: 30px;
  border-radius: 18px;
  box-shadow: 0 15px 40px rgba(0,0,0,0.15);
  text-align: center;
  animation: fadeIn .4s ease-out;
}
h2 {
  margin-bottom: 20px;
  font-weight: 700;
  color: #1e3a8a;
}
label {
  display: block;
  text-align: left;
  margin: 10px 0 5px;
  font-weight: 600;
  color: #334155;
}
input {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-size: 15px;
  transition: all .3s ease;
}
input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
  outline: none;
}
button {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(90deg,#3b82f6,#06b6d4);
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: all .3s;
}
button:hover { filter: brightness(1.1); transform: translateY(-1px); }

.mensaje {
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 10px;
  font-weight: 600;
}
.mensaje.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fecaca;
}
.mensaje.success {
  background: #dcfce7;
  color: #065f46;
  border: 1px solid #bbf7d0;
}
.back-link {
  display: inline-block;
  margin-top: 15px;
  text-decoration: none;
  color: #2563eb;
  font-weight: 600;
  transition: .2s;
}
.back-link:hover { text-decoration: underline; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>
</head>
<body>
  <div class="container">
    <h2>Cambiar Contraseña</h2>

    <?php if ($mensaje): ?>
      <div class="mensaje <?= $mensajeClass; ?>"><?= htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label for="actual">Contraseña actual</label>
      <input type="password" name="actual" id="actual" required>

      <label for="nueva">Nueva contraseña</label>
      <input type="password" name="nueva" id="nueva" required>

      <label for="confirmar">Confirmar nueva contraseña</label>
      <input type="password" name="confirmar" id="confirmar" required>

      <button type="submit">Actualizar contraseña</button>
    </form>

    <a href="dashboard.php" class="back-link">← Volver al panel</a>
  </div>
</body>
</html>
