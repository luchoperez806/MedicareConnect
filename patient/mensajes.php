<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}
require_once "../includes/db.php";
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_POST['doctor_id'];
    $mensaje = trim($_POST['mensaje']);
    if ($mensaje !== '') {
        $stmt = $pdo->prepare("INSERT INTO mensajes (paciente_id, doctor_id, contenido, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user['id'], $doctor_id, $mensaje]);
    }
}

// ✅ Corrección de nombres
$stmtDocs = $pdo->query("SELECT id, doctor_name FROM doctors ORDER BY doctor_name");
$doctores = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mensajes - MediCareConnect</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body{font-family:'Poppins',sans-serif;background:#f3f7fa;margin:0;}
header{background:linear-gradient(135deg,#4f6df5,#66a6ff);color:white;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;}
.container{max-width:700px;margin:40px auto;background:white;padding:30px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.1);}
textarea,select,button{width:100%;padding:10px;margin-top:10px;border-radius:8px;border:1px solid #ccc;}
button{background:#4f6df5;color:white;border:none;cursor:pointer;}
button:hover{background:#3d56d0;}
</style>
</head>
<body>
<header>
    <h1>Mensajes</h1>
    <a href="dashboard.php" style="color:white;text-decoration:none;">← Volver</a>
</header>
<div class="container">
    <form method="POST">
        <label>Médico destinatario</label>
        <select name="doctor_id" required>
            <option value="">Elegí un médico</option>
            <?php foreach($doctores as $doc): ?>
                <option value="<?= $doc['id'] ?>"><?= htmlspecialchars($doc['doctor_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <label>Mensaje</label>
        <textarea name="mensaje" rows="4" placeholder="Escribí tu mensaje..." required></textarea>
        <button type="submit">Enviar</button>
    </form>
</div>
</body>
</html>
