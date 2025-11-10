<?php
session_start();
require_once "../includes/db.php";

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
<title>Cambiar Contraseña | MedicareConnect</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
/* ==== ESTILOS GENERALES ==== */
body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #3b82f6, #06b6d4);
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ==== ENCABEZADO ==== */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 90%;
  max-width: 900px;
  background: #1e3a8a;
  color: white;
  padding: 15px 25px;
  border-radius: 12px;
  margin-top: 30px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.header h1 {
  font-size: 1.4rem;
  margin: 0;
  font-weight: 700;
}
.header a {
  background: linear-gradient(90deg, #4338ca, #1e3a8a);
  color: white;
  padding: 8px 15px;
  border-radius: 15px;
  text-decoration: none;
  font-weight: 600;
  transition: 0.3s;
}
.header a:hover {
  background: #0f2f8a;
}

/* ==== CONTENEDOR DEL FORM ==== */
.form-container {
  background: #fff;
  width: 400px;
  padding: 35px;
  border-radius: 20px;
  box-shadow: 0 10px 35px rgba(0,0,0,0.25);
  text-align: center;
  margin-top: 40px;
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

/* ==== MENSAJES ==== */
.mensaje {
  padding: 10px;
  border-radius: 8px;
  font-weight: 600;
  margin-bottom: 10px;
}
.mensaje.error { background: #fee2e2; color: #991b1b; }
.mensaje.success { background: #dcfce7; color: #065f46; }

/* ==== ENLACE VOLVER ==== */
.back {
  display: inline-block;
  margin-top: 15px;
  text-decoration: none;
  color: #3b82f6;
  font-weight: 600;
  transition: 0.3s;
}
.back:hover {
  color: #1e3a8a;
}

/* ==== ANIMACIÓN ==== */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>
</head>
<body>

<!-- Encabezado -->
<div class="header">
  <h1>MedicareConnect</h1>
  <a href="../logout.php">Cerrar sesión</a>
</div>

<!-- Formulario -->
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
