<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit();
}

require_once "../includes/db.php";
require_once "../includes/notifications.php"; // 🔔 importante

// Obtener patient_id real desde tabla patients
$user_id = $_SESSION['user']['id'];
$stmtP = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmtP->execute([$user_id]);
$patientRow = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$patientRow) {
    $ins = $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)");
    $ins->execute([$user_id]);
    $patient_id = $pdo->lastInsertId();
} else {
    $patient_id = $patientRow['id'];
}

// Validación básica
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

if (empty($_FILES) || !isset($_FILES['files'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron archivos.']);
    exit();
}

$uploadDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'No se pudo crear carpeta de uploads.']);
        exit();
    }
}

// Procesar múltiples archivos
$files = $_FILES['files'];
$uploaded = [];
$errors = [];

for ($i = 0; $i < count($files['name']); $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        $errors[] = $files['name'][$i] . ' -> Error de carga (código ' . $files['error'][$i] . ')';
        continue;
    }

    $originalName = $files['name'][$i];
    $tmpName = $files['tmp_name'][$i];

    // Sanitizar nombre
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $base = pathinfo($originalName, PATHINFO_FILENAME);
    $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base);
    $uniq = uniqid('study_') . '_' . $user_id;
    $safeName = $uniq . ($base ? '_' . $base : '') . ($ext ? '.' . $ext : '');
    $targetPath = $uploadDir . $safeName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        // Guardar en la BD
        $doctor_id = isset($_POST['doctor_id']) && is_numeric($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
        $stmtIns = $pdo->prepare("INSERT INTO studies (patient_id, doctor_id, file_name, uploaded_at) VALUES (?, ?, ?, NOW())");
        try {
            $stmtIns->execute([$patient_id, $doctor_id, $safeName]);
            $uploaded[] = $safeName;

            // 🔔 Enviar notificación automática al médico asignado
            if ($doctor_id) {
                // Buscamos el user_id del doctor
                $stmtD = $pdo->prepare("SELECT user_id FROM doctors WHERE id = ?");
                $stmtD->execute([$doctor_id]);
                $doctorUser = $stmtD->fetchColumn();

                if ($doctorUser) {
                    sendNotification(
                        $doctorUser,
                        "Nuevo estudio disponible 📄",
                        "El paciente <strong>{$_SESSION['user']['fullName']}</strong> subió un nuevo estudio para tu revisión."
                    );
                }
            }

        } catch (Exception $e) {
            @unlink($targetPath);
            $errors[] = $originalName . ' -> Error BD: ' . $e->getMessage();
        }
    } else {
        $errors[] = $originalName . ' -> No se pudo mover archivo al destino.';
    }
}

// Respuesta JSON
if (!empty($uploaded) && empty($errors)) {
    echo json_encode(['success' => true, 'message' => 'Archivos subidos correctamente.', 'files' => $uploaded]);
    exit();
}

if (!empty($uploaded)) {
    echo json_encode(['success' => true, 'message' => 'Algunos archivos subieron correctamente.', 'files' => $uploaded, 'errors' => $errors]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'No se subieron archivos.', 'errors' => $errors]);
exit();
?>
