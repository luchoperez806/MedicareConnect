<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Mensajes
$success = '';
$error = '';

// ================================
// 1) OBTENER DATOS DEL USUARIO
// ================================
$stmtUser = $pdo->prepare("SELECT fullName FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

$currentName = $userData['fullName'] ?? "Paciente";

// ================================
// 2) OBTENER DATOS DEL PACIENTE
// ================================
$stmt = $pdo->prepare("SELECT birthdate, address, phone, blood_type, allergies, medical_conditions,
                              emergency_contact_name, emergency_contact_phone, whatsapp_optin, profile_photo
                       FROM patients WHERE user_id = ?");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// ================================
// 3) SUBIR FOTO DE PERFIL
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
        $fileName = $_FILES['profile_photo']['name'];
        $fileType = $_FILES['profile_photo']['type'];

        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowed)) {
            $error = "Solo se permiten imágenes JPG o PNG.";
        } else {
            $uploadsDir = '../uploads/profile_photos/';
            if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);

            $newFileName = 'p_' . $user_id . '_' . time() . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            $destPath = $uploadsDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {

                if (!empty($patient['profile_photo']) && file_exists($uploadsDir . $patient['profile_photo'])) {
                    unlink($uploadsDir . $patient['profile_photo']);
                }

                $stmt = $pdo->prepare("UPDATE patients SET profile_photo=? WHERE user_id=?");
                $stmt->execute([$newFileName, $user_id]);

                $patient['profile_photo'] = $newFileName;
                $success = "Foto de perfil actualizada correctamente.";
            } else {
                $error = "Error al subir la foto.";
            }
        }
    }
}

// ================================
// 4) ACTUALIZAR PERFIL COMPLETO
// Incluye edición de nombre de usuario
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {

    // Datos del usuario
    $fullName = $_POST['fullName'] ?? $currentName;

    // Actualizar nombre en tabla users
    $stmtUpdateUser = $pdo->prepare("UPDATE users SET fullName = ? WHERE id = ?");
    $stmtUpdateUser->execute([$fullName, $user_id]);

    // Actualizar sesión
    $_SESSION['user']['fullName'] = $fullName;

    // Datos médicos del paciente
    $birthdate = $_POST['birthdate'] ?? null;
    $address = $_POST['address'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $blood_type = $_POST['blood_type'] ?? null;
    $allergies = $_POST['allergies'] ?? null;
    $medical_conditions = $_POST['medical_conditions'] ?? null;
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? null;
    $emergency_contact_phone = $_POST['emergency_contact_phone'] ?? null;
    $whatsapp_optin = isset($_POST['whatsapp_optin']) ? 1 : 0;

    // Actualizar tabla patients
    $updateStmt = $pdo->prepare("UPDATE patients
        SET birthdate=?, address=?, phone=?, blood_type=?, allergies=?, medical_conditions=?,
            emergency_contact_name=?, emergency_contact_phone=?, whatsapp_optin=?
        WHERE user_id=?");
    $updateStmt->execute([
        $birthdate, $address, $phone, $blood_type, $allergies, $medical_conditions,
        $emergency_contact_name, $emergency_contact_phone, $whatsapp_optin, $user_id
    ]);

    $success = "Perfil actualizado correctamente.";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mi Perfil - MedicareConnect</title>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: #eef2f7;
        margin: 0;
        padding: 0;
    }

    header {
        background: linear-gradient(135deg, #4f6df5, #66a6ff);
        color: white;
        text-align: center;
        padding: 40px 20px;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    header h2 {
        font-size: 2rem;
        margin: 0;
        font-weight: 600;
    }

    .container {
        max-width: 960px;
        margin: -40px auto 40px;
        background: white;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn-back {
        background: #6c757d;
        color: white;
        padding: 10px 18px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        margin-bottom: 20px;
        transition: 0.3s;
    }
    .btn-back:hover {
        background: #5a6268;
    }

    /* Perfil */
    .profile-header {
        text-align: center;
        margin-bottom: 25px;
    }

    .profile-header img {
        width: 130px;
        height: 130px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #4f6df5;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 10px;
    }

    .profile-header h2 {
        font-size: 1.6rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .profile-header p {
        color: #666;
        margin: 5px 0 0;
    }

    /* Formularios */
    form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
    }

    label {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
        display: block;
    }

    input, textarea {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #cfd6e1;
        background: #f9fafc;
        transition: border 0.3s, box-shadow 0.3s;
    }

    input:focus, textarea:focus {
        border-color: #4f6df5;
        box-shadow: 0 0 6px rgba(79,109,245,0.3);
        outline: none;
    }

    textarea {
        min-height: 80px;
        resize: vertical;
    }

    .whatsapp-optin {
        grid-column: span 2;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: #e9f9ee;
        border-radius: 10px;
        border: 1px solid #a3e4b3;
    }

    /* Botones */
    button[type=submit] {
        background: linear-gradient(135deg, #4f6df5, #5273ff);
        color: white;
        padding: 12px 18px;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        grid-column: span 2;
        transition: 0.3s;
    }

    button[type=submit]:hover {
        opacity: 0.9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Responsive */
    @media (max-width: 600px) {
        header h2 {
            font-size: 1.5rem;
        }
        .container {
            padding: 20px;
        }
        .whatsapp-optin {
            grid-column: span 1;
            flex-wrap: wrap;
        }
        button[type=submit] {
            grid-column: span 1;
        }
    }
</style>

</head>
<body>

<header>
  <h2>MedicareConnect - Perfil del Paciente</h2>
</header>

<div class="container">
  <a href="dashboard.php"><button class="btn-back">← Volver al Panel</button></a>

  <?php if($success): ?><div class="toast toast-success"><?= $success ?></div><?php endif; ?>
  <?php if($error): ?><div class="toast toast-error"><?= $error ?></div><?php endif; ?>

<?php
$firstName = explode(' ', trim($currentName))[0];
?>

<div class="profile-header">
  <img src="../uploads/profile_photos/<?= htmlspecialchars($patient['profile_photo'] ?? 'default-avatar.png') ?>" alt="Foto de perfil">
  <div>
    <h2>¡Hola, <?= htmlspecialchars($firstName) ?>!</h2>
    <p style="color:#555;">Gestioná tus datos personales y actualizá tu información médica.</p>
  </div>
</div>

<div class="upload-photo-form">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="upload_photo">
    <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png" required>
    <button type="submit">Actualizar Foto de Perfil</button>
  </form>
</div>

<form method="POST">
  <input type="hidden" name="update_profile">

  <!-- NOMBRE COMPLETO DEL USUARIO -->
  <div class="form-group full-width">
    <label for="fullName">Nombre completo</label>
    <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($currentName) ?>" required>
  </div>

  <div class="form-group">
    <label for="birthdate">Fecha de Nacimiento</label>
    <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($patient['birthdate']) ?>">
  </div>

  <div class="form-group">
    <label for="address">Dirección</label>
    <input type="text" id="address" name="address" value="<?= htmlspecialchars($patient['address']) ?>">
  </div>

  <div class="form-group">
    <label for="phone">Teléfono</label>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>">
  </div>

  <div class="form-group full-width whatsapp-optin">
    <input type="checkbox" id="whatsapp_optin" name="whatsapp_optin" <?= ($patient['whatsapp_optin'] ?? 1) ? 'checked' : '' ?>>
    <label for="whatsapp_optin">Deseo recibir recordatorios automáticos por WhatsApp o SMS</label>
  </div>

  <div class="form-group">
    <label for="blood_type">Tipo de Sangre</label>
    <input type="text" id="blood_type" name="blood_type" value="<?= htmlspecialchars($patient['blood_type']) ?>">
  </div>

  <div class="form-group full-width">
    <label for="allergies">Alergias</label>
    <textarea id="allergies" name="allergies"><?= htmlspecialchars($patient['allergies']) ?></textarea>
  </div>

  <div class="form-group full-width">
    <label for="medical_conditions">Condiciones Médicas</label>
    <textarea id="medical_conditions" name="medical_conditions"><?= htmlspecialchars($patient['medical_conditions']) ?></textarea>
  </div>

  <div class="form-group">
    <label for="emergency_contact_name">Contacto de Emergencia</label>
    <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= htmlspecialchars($patient['emergency_contact_name']) ?>">
  </div>

  <div class="form-group">
    <label for="emergency_contact_phone">Teléfono de Emergencia</label>
    <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= htmlspecialchars($patient['emergency_contact_phone']) ?>">
  </div>

  <div class="form-group full-width">
    <button type="submit">Guardar Cambios</button>
  </div>
</form>
</div>

<footer style="background:#4f6df5;color:white;text-align:center;padding:15px;">
  &copy; <?= date('Y') ?> MedicareConnect
</footer>

</body>
</html>
