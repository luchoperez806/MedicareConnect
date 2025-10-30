<?php
// doctor/login.php
session_start();
include("../includes/db.php");

// Si ya está logueado → ir al dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['doctor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = "Completá email y contraseña.";
    } else {
        // 1) Buscar en users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // 2) Verificar password: preferimos password_verify (contraseña hasheada)
            $valid = false;
            if (!empty($user['password'])) {
                // Intentamos como hash
                if (password_verify($password, $user['password'])) {
                    $valid = true;
                } else {
                    // fallback: tal vez están en texto plano (solo para testing local)
                    if ($password === $user['password']) {
                        $valid = true;
                    }
                }
            } else {
                // sin password en BD → inválido
                $valid = false;
            }

            if ($valid) {
                // 3) Verificar que ese usuario tenga registro en doctors (vinculado por user_id)
                $stmt2 = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ? LIMIT 1");
                $stmt2->execute([$user['id']]);
                $doctor = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($doctor) {
                    // Guardamos ambas referencias: user + doctor
                    $_SESSION['user_id'] = $user['id'];        // id en tabla users
                    $_SESSION['doctor_id'] = $doctor['id'];   // id en tabla doctors
                    $_SESSION['doctor_name'] = $user['fullName'] ?? $user['fullname'] ?? '';

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "El usuario no está registrado como médico.";
                }
            } else {
                $error = "Credenciales inválidas.";
            }
        } else {
            $error = "Credenciales inválidas.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>
<main class="main-content">
    <div class="login-container">
        <h2>Ingreso de Médicos</h2>
        <?php if ($error): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Contraseña</label>
            <input type="password" name="password" required>

            <button type="submit">Ingresar</button>
        </form>
    </div>
</main>
<?php include("../includes/footer.php"); ?>
<style>
.login-container{max-width:420px;margin:80px auto;padding:28px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.06);text-align:center}
.login-container label{display:block;margin:10px 0 6px;text-align:left}
.login-container input{width:100%;padding:10px;margin-bottom:12px;border:1px solid #ccc;border-radius:8px}
.login-container button{width:100%;padding:12px;background:#3f51b5;color:#fff;border:none;border-radius:8px;cursor:pointer}
</style>
