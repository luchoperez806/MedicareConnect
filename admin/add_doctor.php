<?php
session_start();
include("../includes/db.php");

// Seguridad: Solo admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');
    $office_address = trim($_POST['office_address'] ?? '');
    $working_days = trim($_POST['working_days'] ?? '');
    $working_hours = trim($_POST['working_hours'] ?? '');
    $consultation_fee = $_POST['consultation_fee'] ?? 0;

    // Verificar que el email no exista
    $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->rowCount() > 0) {
        $mensaje = "El correo ya está registrado.";
    } else {
        // Insertar en users
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, 'doctor')");
        $stmt->execute([$fullName, $email, $hashedPassword]);
        $userId = $pdo->lastInsertId();

        // Insertar en doctors
        $stmt2 = $pdo->prepare("INSERT INTO doctors (user_id, specialization, office_address, working_days, working_hours, consultation_fee) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->execute([$userId, $specialization, $office_address, $working_days, $working_hours, $consultation_fee]);

        $mensaje = "Médico agregado correctamente.";
    }
}
?>

<?php include("../includes/admin_header.php"); ?>

<div class="main-content">
    <h2>Agregar Médico</h2>

    <?php if($mensaje): ?>
        <p class="info-msg"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <form method="POST" class="doctor-form">
        <label>Nombre completo:</label>
        <input type="text" name="fullName" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <label>Especialización:</label>
        <input type="text" name="specialization" required>

        <label>Dirección de consultorio:</label>
        <input type="text" name="office_address" required>

        <label>Días de trabajo:</label>
        <input type="text" name="working_days" placeholder="Lunes-Viernes" required>

        <label>Horario de trabajo:</label>
        <input type="text" name="working_hours" placeholder="09:00-17:00" required>

        <label>Honorarios:</label>
        <input type="number" name="consultation_fee" step="0.01" required>

        <button type="submit" class="btn">Agregar Médico</button>
    </form>
</div>

<?php include("../includes/admin_footer.php"); ?>

<style>
.main-content {
    max-width: 600px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
h2 { text-align:center; color:#1a237e; margin-bottom:20px; }
.doctor-form label { display:block; margin:12px 0 5px; font-weight:bold; color:#333; }
.doctor-form input { width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; font-size:14px; }
.doctor-form .btn { margin-top:20px; width:100%; padding:12px; background:#3f51b5; color:#fff; border:none; border-radius:8px; font-weight:bold; cursor:pointer; transition:0.3s; }
.doctor-form .btn:hover { background:#1a237e; }
.info-msg { text-align:center; color:#4caf50; font-weight:bold; margin-bottom:15px; }
@media(max-width:500px) { .main-content { padding:15px; } }
</style>
