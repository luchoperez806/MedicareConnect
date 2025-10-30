<?php
// doctor/profile.php
session_start();
if (!isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../includes/db.php");

$doctor_id = $_SESSION['doctor_id'];
$message = "";

// Obtener datos del doctor
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

// Actualizar datos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $specialization = $_POST['specialization'] ?? '';
    $working_days = $_POST['working_days'] ?? '';
    $working_hours = $_POST['working_hours'] ?? '';
    $consultation_fee = $_POST['consultation_fee'] ?? '';
    $password = $_POST['password'] ?? "";

    // Manejar subida de imagen
    $profile_pic = $doctor['profile_pic'];
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../uploads/";
        $file_name = "doctor_" . $doctor_id . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $file_name;
        }
    }

    // Actualizar la base de datos
    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE doctors SET specialization=?, working_days=?, working_hours=?, consultation_fee=?, profile_pic=?, password=? WHERE id=?");
        $update->execute([$specialization, $working_days, $working_hours, $consultation_fee, $profile_pic, $hashed, $doctor_id]);
    } else {
        $update = $pdo->prepare("UPDATE doctors SET specialization=?, working_days=?, working_hours=?, consultation_fee=?, profile_pic=? WHERE id=?");
        $update->execute([$specialization, $working_days, $working_hours, $consultation_fee, $profile_pic, $doctor_id]);
    }

    $message = "✅ Perfil actualizado correctamente.";

    // Refrescar datos
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php include("../includes/header.php"); ?>

<main class="main-content">
    <div class="profile-container">
        <h2>Editar Perfil del Doctor</h2>

        <?php if ($message): ?>
            <div class="alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="profile-photo">
                <img src="../uploads/<?php echo htmlspecialchars($doctor['profile_pic'] ?? 'default.png'); ?>" alt="Foto de perfil">
                <input type="file" name="profile_pic" accept="image/*">
            </div>

            <label for="specialization">Especialidad</label>
            <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>

            <label for="working_days">Días de atención</label>
            <input type="text" id="working_days" name="working_days" value="<?php echo htmlspecialchars($doctor['working_days']); ?>" placeholder="Ej: Lunes a Viernes" required>

            <label for="working_hours">Horario de atención</label>
            <input type="text" id="working_hours" name="working_hours" value="<?php echo htmlspecialchars($doctor['working_hours']); ?>" placeholder="Ej: 09:00 a 17:00" required>

            <label for="consultation_fee">Honorarios ($)</label>
            <input type="number" id="consultation_fee" name="consultation_fee" step="0.01" value="<?php echo htmlspecialchars($doctor['consultation_fee'] ?? ''); ?>" required>

            <label for="password">Nueva contraseña (opcional)</label>
            <input type="password" id="password" name="password" placeholder="Dejar vacío si no desea cambiarla">

            <button type="submit" class="btn-primary">Guardar Cambios</button>
            <a href="dashboard.php" class="btn-secondary">Volver al Dashboard</a>
        </form>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<style>
.profile-container {
    max-width: 700px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    text-align: center;
}
.profile-container h2 { color: #1a237e; margin-bottom: 25px; }
.profile-photo { margin-bottom: 20px; }
.profile-photo img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid #3f51b5;
    margin-bottom: 10px;
    object-fit: cover;
}
.profile-form { display: flex; flex-direction: column; gap: 15px; }
input, textarea { padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
.btn-primary, .btn-secondary {
    padding: 10px 15px;
    border: none;
    border-radius: 8px;
    color: white;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    transition: 0.3s;
}
.btn-primary { background: #3f51b5; }
.btn-primary:hover { background: #303f9f; }
.btn-secondary { background: #5c6bc0; }
.btn-secondary:hover { background: #3949ab; }
.alert {
    background: #c8e6c9;
    color: #256029;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
}
</style>
