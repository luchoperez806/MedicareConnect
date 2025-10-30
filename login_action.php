<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['role'])) {
    header("Location: index.php");
    exit;
}

$role = $_GET['role']; // admin | doctor | paciente
$email = trim($_POST['email']);
$password = trim($_POST['password']);

// 🔹 Traducimos el rol si llega en español
if ($role === 'paciente') {
    $role_db = 'patient';
} else {
    $role_db = $role;
}

// Buscar usuario con el rol correcto
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND role = :role LIMIT 1");
$stmt->execute([':email' => $email, ':role' => $role_db]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {

    // Sesión unificada general
    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['fullName'],
        'email' => $user['email'],
        'role'  => $user['role']
    ];

    // Sesiones específicas por rol
    if ($role_db === 'doctor') {
        $stmtDoc = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ? LIMIT 1");
        $stmtDoc->execute([$user['id']]);
        $doctor = $stmtDoc->fetch(PDO::FETCH_ASSOC);
        $_SESSION['doctor_id'] = $doctor['id'] ?? null;
    } elseif ($role_db === 'admin') {
        $_SESSION['admin_id'] = $user['id'];
    } elseif ($role_db === 'patient') {
        $_SESSION['paciente_id'] = $user['id'];
        $_SESSION['paciente_name'] = $user['fullName'];
    }

    // Redirigir según rol
    switch ($role_db) {
        case 'admin': header("Location: admin/dashboard.php"); break;
        case 'doctor': header("Location: doctor/dashboard.php"); break;
        case 'patient': header("Location: patient/dashboard.php"); break;
    }
    exit;
}

// Si falla el inicio de sesión
$error = "Correo o contraseña incorrectos.";
echo "<script>alert('{$error}'); window.location.href='login.php?role={$role}';</script>";
exit;
?>
