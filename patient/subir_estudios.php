<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}
require_once "../includes/db.php";
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $paciente_id = $user['id'];
    $doctor_id = $_POST['doctor_id'];
    $nombre_archivo = basename($_FILES['archivo']['name']);
    $ruta_destino = "../uploads/" . $nombre_archivo;

    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
        $stmt = $pdo->prepare("INSERT INTO studies (patient_id, doctor_id, file_name, file_path, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$paciente_id, $doctor_id, $nombre_archivo, $ruta_destino]);
        $mensaje = "📁 Archivo subido correctamente.";
    } else {
        $mensaje = "❌ Error al subir el archivo.";
    }
}

// ✅ Nombres de columnas correctos
$stmtDocs = $pdo->query("SELECT id, doctor_name FROM doctors ORDER BY doctor_name");
$doctores = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Subir Estudios - MediCareConnect</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f3f7fa;margin:0;}
header{background:linear-gradient(135deg,#4f6df5,#66a6ff);color:white;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;}
.container{max-width:700px;margin:40px auto;background:white;padding:30px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.1);}
input,select,button{width:100%;padding:10px;margin-top:10px;border-radius:8px;border:1px solid #ccc;}
button{background:#4f6df5;color:white;border:none;cursor:pointer;}
button:hover{background:#3d56d0;}
.message{margin-bottom:15px;padding:10px;border-radius:8px;background:#e6f7e6;color:#2a7b2a;}
</style>
</head>
<body>
<header>
    <h1>Subir Estudios</h1>
    <a href="dashboard.php" style="color:white;text-decoration:none;">← Volver</a>
</header>

<div class="container">
    <?php if(isset($mensaje)) echo "<p class='message'>$mensaje</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Seleccioná médico</label>
        <select name="doctor_id" required>
            <option value="">Elegí un profesional</option>
            <?php foreach($doctores as $doc): ?>
                <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['doctor_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Archivo (PDF, JPG, PNG, DOCX...)</label>
        <input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.docx" required>
        <button type="submit">Subir Estudio</button>
    </form>
</div>
</body>
</html>
