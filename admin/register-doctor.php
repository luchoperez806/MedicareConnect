<?php
session_start();
require_once("../includes/db.php");

// Solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $specialization = trim($_POST['specialization'] ?? '');
    $office_address = trim($_POST['office_address'] ?? '');
    $working_days = trim($_POST['working_days'] ?? '');
    $working_hours = trim($_POST['working_hours'] ?? '');
    $consultation_fee = trim($_POST['consultation_fee'] ?? '');
    $profile_pic = '';

    try {
        // Validar email duplicado
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) throw new Exception("Ya existe un usuario con ese email.");

        // Subir imagen
        if (!empty($_FILES['profile_pic']['name'])) {
            $fileName = uniqid("doc_") . "_" . basename($_FILES['profile_pic']['name']);
            $targetPath = "../uploads/" . $fileName;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetPath);
            $profile_pic = $fileName;
        }

        // Crear usuario
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (fullName, email, password, role, profile_pic, created_at)
                               VALUES (?, ?, ?, 'doctor', ?, NOW())");
        $stmt->execute([$fullName, $email, $hashedPassword, $profile_pic]);
        $user_id = $pdo->lastInsertId();

        // Crear médico
        $stmt = $pdo->prepare("INSERT INTO doctors (user_id, specialization, office_address, working_days, working_hours, consultation_fee, profile_pic)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $specialization, $office_address, $working_days, $working_hours, $consultation_fee, $profile_pic]);

        $success = "✅ Médico registrado correctamente.";
    } catch (Exception $e) {
        $error = "⚠️ " . $e->getMessage();
    }
}

// Consultar últimos 5 médicos registrados
$stmt = $pdo->query("
    SELECT u.fullName, u.email, d.specialization, d.office_address, d.profile_pic
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    ORDER BY u.id DESC
    LIMIT 5
");
$recentDoctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Médico | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,#e0f2ff,#f5faff);
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 750px;
            margin-top: 40px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(59,130,246,0.15);
            padding: 30px 40px;
        }
        h1 { font-weight: 800; color: #0f172a; }
        label { font-weight: 600; margin-top: 10px; }
        .btn-primary {
            background: linear-gradient(90deg,#3b82f6,#0ea5e9);
            border: none; font-weight: 700;
            box-shadow: 0 8px 18px rgba(59,130,246,0.2);
        }
        .btn-primary:hover { filter: brightness(1.1); }
        .btn-back {
            background: #e2e8f0; color: #1e3a8a;
            border-radius: 10px; font-weight: 600;
        }
        .doctor-card {
            display:flex; align-items:center; gap:14px;
            background:#f9fafb; border-radius:12px;
            padding:10px 14px; margin-bottom:10px;
            border:1px solid #e5e7eb;
        }
        .doctor-card img {
            width:50px; height:50px; border-radius:50%;
            object-fit:cover; border:2px solid #e0e7ff;
        }
        .doctor-info { line-height:1.2; }
        .doctor-info .name { font-weight:700; color:#1e293b; }
        .doctor-info .spec { color:#64748b; font-size:.9rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1> Registrar Médico</h1>
        <a href="dashboard.php" class="btn btn-back">← Volver al Panel</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <div class="col-md-6">
            <label>Nombre completo</label>
            <input type="text" name="fullName" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="col-md-6">
            <label>Especialidad</label>
            <input type="text" name="specialization" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label>Dirección del consultorio</label>
            <input type="text" name="office_address" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Días laborales</label>
            <input type="text" name="working_days" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Horario de atención</label>
            <input type="text" name="working_hours" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Honorarios ($)</label>
            <input type="number" step="0.01" name="consultation_fee" class="form-control" required>
        </div>

        <div class="col-md-6">
            <label>Foto de perfil</label>
            <input type="file" name="profile_pic" class="form-control">
        </div>

        <div class="col-12 mt-3 text-end">
            <button type="submit" class="btn btn-primary px-4 py-2">Registrar Médico</button>
        </div>
    </form>

    <!-- Listado de médicos recientes -->
    <div class="recent-doctors mt-5">
        <h4 class="fw-bold mb-3">Últimos médicos registrados</h4>
        <?php if (empty($recentDoctors)): ?>
            <p class="text-muted">Aún no hay médicos registrados.</p>
        <?php else: ?>
            <?php foreach ($recentDoctors as $doc): ?>
                <div class="doctor-card">
                    <img src="../uploads/<?php echo htmlspecialchars($doc['profile_pic'] ?: 'default.png'); ?>" onerror="this.src='../assets/images/default.png'">
                    <div class="doctor-info">
                        <div class="name"><?php echo htmlspecialchars($doc['fullName']); ?></div>
                        <div class="spec"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                        <small><?php echo htmlspecialchars($doc['email']); ?></small><br>
                        <small class="text-muted"><?php echo htmlspecialchars($doc['office_address']); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
