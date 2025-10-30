<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID de estudio no válido.");

$stmt = $pdo->prepare("SELECT * FROM studies WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$study = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$study) die("Estudio no encontrado.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileName = $study['file_name'];

    if (!empty($_FILES['new_file']['name'])) {
        $uploadDir = "../uploads/";
        $tmp = $_FILES['new_file']['tmp_name'];
        $newFile = basename($_FILES['new_file']['name']);
        $target = $uploadDir . $newFile;

        if (move_uploaded_file($tmp, $target)) {
            if (file_exists($uploadDir . $fileName)) unlink($uploadDir . $fileName);
            $fileName = $newFile;
        }
    }

    $stmt = $pdo->prepare("UPDATE studies SET file_name = ? WHERE id = ?");
    $stmt->execute([$fileName, $id]);
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Estudio</title>
<style>
body { font-family:'Poppins',sans-serif; margin:0; background:#f1f5f9; }
.header { background:#1e40af; color:white; padding:14px 20px; font-weight:600; }
.card { background:white; max-width:420px; margin:60px auto; padding:30px; border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.1); }
h2 { color:#1e3a8a; }
input,button { width:100%; margin-top:10px; padding:10px; border-radius:8px; border:1px solid #ccc; }
button { background:#1e40af; color:white; font-weight:600; cursor:pointer; }
button:hover { background:#0f2c85; }
a { display:inline-block; margin-top:15px; color:#2563eb; text-decoration:none; font-weight:600; }
</style>
</head>
<body>
<div class="header">Panel del Paciente</div>
<div class="card">
    <h2>Editar Estudio</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Archivo actual:</label>
        <input type="text" name="file_name" value="<?php echo htmlspecialchars($study['file_name']); ?>" disabled>
        <label>Reemplazar archivo:</label>
        <input type="file" name="new_file">
        <button type="submit">Guardar cambios</button>
    </form>
    <a href="dashboard.php">← Volver al Panel</a>
</div>
</body>
</html>
