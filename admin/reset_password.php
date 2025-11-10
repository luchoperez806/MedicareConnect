<?php
session_start();
require_once("../includes/db.php");

// Seguridad: solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    if (!empty($email)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generar contraseña temporal
            $tempPass = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789"), 0, 10);
            $hashed = password_hash($tempPass, PASSWORD_DEFAULT);

            $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->execute([$hashed, $user['id']]);

            $mensaje = "<div class='alert alert-success'>
                ✅ Contraseña temporal generada para <strong>$email</strong>:
                <br><strong>$tempPass</strong>
                <br><small>Enviásela al usuario, quien luego podrá cambiarla desde su perfil.</small>
            </div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ No se encontró ningún usuario con ese correo.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resetear Contraseña | MedicareConnect</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background:#f4f7fb;
  font-family:'Poppins',sans-serif;
}
.container {
  max-width:500px;
  margin-top:80px;
  background:#fff;
  padding:30px;
  border-radius:16px;
  box-shadow:0 6px 20px rgba(0,0,0,.1);
}
h2 {
  color:#2563eb;
  text-align:center;
  margin-bottom:20px;
}
.btn-primary {
  background:linear-gradient(90deg,#3b82f6,#06b6d4);
  border:none;
  font-weight:600;
}
</style>
</head>
<body>
<div class="container">
  <h2> Resetear Contraseña</h2>
  <?php echo $mensaje; ?>
  <form method="POST">
    <label for="email" class="form-label">Correo del usuario</label>
    <input type="email" class="form-control mb-3" id="email" name="email" required placeholder="usuario@ejemplo.com">
    <button type="submit" class="btn btn-primary w-100">Generar nueva contraseña</button>
  </form>
  <div class="text-center mt-3">
    <a href="dashboard.php" class="btn btn-secondary btn-sm">← Volver al panel</a>
  </div>
</div>
</body>
</html>
