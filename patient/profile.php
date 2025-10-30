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

// Obtener datos del paciente
$stmt = $pdo->prepare("SELECT birthdate, address, phone, blood_type, allergies, medical_conditions, emergency_contact_name, emergency_contact_phone FROM patients WHERE user_id = ?");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Actualizar perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $birthdate = $_POST['birthdate'] ?? null;
    $address = $_POST['address'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $blood_type = $_POST['blood_type'] ?? null;
    $allergies = $_POST['allergies'] ?? null;
    $medical_conditions = $_POST['medical_conditions'] ?? null;
    $emergency_contact_name = $_POST['emergency_contact_name'] ?? null;
    $emergency_contact_phone = $_POST['emergency_contact_phone'] ?? null;

    $updateStmt = $pdo->prepare("UPDATE patients SET birthdate=?, address=?, phone=?, blood_type=?, allergies=?, medical_conditions=?, emergency_contact_name=?, emergency_contact_phone=? WHERE user_id=?");
    $updateStmt->execute([$birthdate, $address, $phone, $blood_type, $allergies, $medical_conditions, $emergency_contact_name, $emergency_contact_phone, $user_id]);

    $success = "Perfil actualizado correctamente.";
}

// Subida de estudios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_study'])) {
    if (isset($_FILES['study_file']) && $_FILES['study_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['study_file']['tmp_name'];
        $fileName = $_FILES['study_file']['name'];
        $fileType = $_FILES['study_file']['type'];

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($fileType, $allowedTypes)) {
            $error = "Tipo de archivo no permitido. Solo PDF, JPG o PNG.";
        } else {
            $uploadsDir = '../uploads/';
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9_\-\.]/", "_", $fileName);
            $destPath = $uploadsDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $stmt = $pdo->prepare("INSERT INTO patient_studies (patient_id, file_name) VALUES (?, ?)");
                $stmt->execute([$user_id, $newFileName]);
                $success = "Archivo subido correctamente.";
            } else {
                $error = "Error al mover el archivo al servidor.";
            }
        }
    } else {
        $error = "No se seleccionó ningún archivo o ocurrió un error.";
    }
}

// Eliminar estudio
if (isset($_GET['delete_study'])) {
    $study_id = intval($_GET['delete_study']);
    $stmt = $pdo->prepare("SELECT file_name FROM patient_studies WHERE id=? AND patient_id=?");
    $stmt->execute([$study_id, $user_id]);
    $study = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($study) {
        $filePath = '../uploads/' . $study['file_name'];
        if (file_exists($filePath)) unlink($filePath);

        $delStmt = $pdo->prepare("DELETE FROM patient_studies WHERE id=?");
        $delStmt->execute([$study_id]);
        $success = "Estudio eliminado correctamente.";
    }
}

// Obtener lista de estudios
$studiesStmt = $pdo->prepare("SELECT * FROM patient_studies WHERE patient_id=? ORDER BY uploaded_at DESC");
$studiesStmt->execute([$user_id]);
$studies = $studiesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil del Paciente</title>
<style>
body { font-family: Arial; margin:0; padding:0; background:#f4f4f4;}
header, footer { background:#007bff; color:white; padding:15px; text-align:center;}
.container { max-width:950px; margin:20px auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);}
h1,h2 { text-align:center; margin-bottom:20px;}
form { display:flex; flex-wrap:wrap; gap:20px;}
label { display:block; margin-bottom:5px; font-weight:bold;}
input, textarea { width:100%; padding:8px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box;}
.form-group { flex:1 1 45%; min-width:250px;}
.full-width { flex:1 1 100%;}
button { padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px; cursor:pointer;}
button:hover { background:#0056b3;}
.btn-back { background:#6c757d; margin-bottom:10px;}
.btn-back:hover { background:#5a6268;}
.study-list { margin-top:20px; display:flex; flex-wrap:wrap; gap:10px;}
.study-item { border:1px solid #ccc; padding:10px; border-radius:5px; width: calc(33% - 10px); box-sizing:border-box; text-align:center; background:#f9f9f9;}
.study-item img { max-width:100%; max-height:150px; display:block; margin:0 auto 5px;}
.study-item a { display:block; margin-bottom:5px; word-break: break-word; color:#007bff;}
.study-item button { background:#dc3545; margin-top:5px; }
.study-item button:hover { background:#c82333; }
@media(max-width:768px){.study-item { width: calc(50% - 10px);}}
@media(max-width:480px){.study-item { width: 100%;}}
.toast { position:fixed; top:10px; right:10px; padding:15px; border-radius:5px; color:white; z-index:999; opacity:0.95;}
.toast-success { background:#28a745;}
.toast-error { background:#dc3545;}
</style>
</head>
<body>

<header>
    <h2>Medicare Connect - Panel del Paciente</h2>
</header>

<div class="container">
    <a href="dashboard.php"><button class="btn-back">← Volver al Panel</button></a>

    <?php if($success): ?>
        <div class="toast toast-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="toast toast-error"><?= $error ?></div>
    <?php endif; ?>

    <h1>Mi Perfil</h1>
    <form method="POST">
        <input type="hidden" name="update_profile">
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
            <button type="submit">Actualizar Perfil</button>
        </div>
    </form>

    <h2>Subir Estudios</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="upload_study">
        <div class="form-group full-width">
            <input type="file" name="study_file" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="form-group full-width">
            <button type="submit">Subir Estudio</button>
        </div>
    </form>

    <h2>Estudios Subidos</h2>
    <div class="study-list">
        <?php if(count($studies)===0) echo "<p>No hay estudios subidos.</p>"; ?>
        <?php foreach($studies as $s): ?>
            <div class="study-item">
                <?php if(preg_match('/\.(jpg|jpeg|png)$/i', $s['file_name'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($s['file_name']) ?>" alt="Estudio">
                <?php else: ?>
                    <span>PDF: <?= htmlspecialchars($s['file_name']) ?></span>
                <?php endif; ?>
                <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank">Ver / Descargar</a>
                <a href="profile.php?delete_study=<?= $s['id'] ?>"><button type="button">Eliminar</button></a>
                <div style="font-size:12px; margin-top:5px;"><?= $s['uploaded_at'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<footer>
    &copy; <?= date('Y') ?> MedicareConnect
</footer>

</body>
</html>
