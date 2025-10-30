<?php
session_start();
include("../includes/db.php");

$mensaje = "";
$registroMensaje = "";

// LOGIN PACIENTE
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'patient'");
    $stmt->execute([$email]);
    $patient = $stmt->fetch();

    if ($patient && password_verify($password, $patient['password'])) {
        $_SESSION['patient_id'] = $patient['id'];
        $_SESSION['patient_name'] = $patient['fullName'];
        header("Location: dashboard.php");
        exit();
    } else {
        $mensaje = "Credenciales inválidas o usuario no autorizado.";
    }
}

// REGISTRO PACIENTE
if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if ($password !== $confirmPassword) {
        $registroMensaje = "Las contraseñas no coinciden.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $registroMensaje = "El correo ya está registrado.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, 'patient')");
            $stmt->execute([$name, $email, $hashedPassword]);
            $registroMensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
        }
    }
}
?>

<?php include("../includes/header.php"); ?>

<main class="main-content">
    <div class="login-container">
        <h2>Ingreso Paciente</h2>
        <?php if ($mensaje): ?>
            <p class="error"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <input type="email" name="email" placeholder="Correo electrónico" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit" name="login" class="btn">Ingresar</button>
        </form>
        <p class="register-link">¿No estás registrado? <span id="openRegister">Regístrate</span></p>
    </div>

    <!-- MODAL REGISTRO -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Registro Paciente</h2>
            <?php if ($registroMensaje): ?>
                <p class="error"><?php echo $registroMensaje; ?></p>
            <?php endif; ?>
            <form method="POST" class="register-form">
                <input type="text" name="name" placeholder="Nombre completo" required>
                <input type="email" name="reg_email" placeholder="Correo electrónico" required>
                <input type="password" name="reg_password" placeholder="Contraseña" required>
                <input type="password" name="confirmPassword" placeholder="Confirmar contraseña" required>
                <button type="submit" name="register" class="btn">Registrarse</button>
            </form>
        </div>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
.login-container {
    max-width: 400px;
    margin: 80px auto;
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    text-align: center;
}
.login-container h2 { margin-bottom: 20px; color: #1a237e; }
.login-form input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}
.login-form .btn {
    width: 100%;
    background: #3f51b5;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}
.login-form .btn:hover { background: #1a237e; }
.register-link { margin-top: 15px; font-size: 0.95rem; }
.register-link span { color: #3f51b5; cursor: pointer; text-decoration: underline; }

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}
.modal-content {
    background-color: #fff;
    margin: 80px auto;
    padding: 30px;
    border-radius: 15px;
    width: 90%;
    max-width: 400px;
    position: relative;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}
.modal-content h2 { margin-bottom: 20px; color: #1a237e; }
.register-form input {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}
.register-form .btn {
    width: 100%;
    background: #3f51b5;
    color: #fff;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
}
.register-form .btn:hover { background: #1a237e; }
.error { color: red; font-weight: bold; margin-bottom: 15px; }

/* RESPONSIVE */
@media(max-width:500px){
    .login-container { margin: 50px 20px; padding: 25px; }
    .modal-content { margin: 50px 10px; padding: 25px; }
}
</style>

<script>
// Modal JS
var modal = document.getElementById("registerModal");
var btn = document.getElementById("openRegister");
var span = document.getElementsByClassName("close")[0];

btn.onclick = function() { modal.style.display = "block"; }
span.onclick = function() { modal.style.display = "none"; }
window.onclick = function(event) { if (event.target == modal) { modal.style.display = "none"; } }
</script>
