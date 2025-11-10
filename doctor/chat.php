<?php
session_start();
require_once "../includes/db.php";

// ===== Seguridad =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];
$doctor_user_id = (int)$_SESSION['user']['id'];
$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

// 🩺 Verificamos que el paciente exista y esté vinculado al doctor (evita chats ajenos)
$stmt = $pdo->prepare("
    SELECT p.id, u.fullName, u.id AS user_id
    FROM patients p
    JOIN users u ON p.user_id = u.id
    JOIN appointments a ON a.patient_id = p.id
    WHERE p.id = ? AND a.doctor_id = ?
    LIMIT 1
");
$stmt->execute([$patient_id, $doctor_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("<div style='padding:20px;font-family:sans-serif;color:#b91c1c;'>
        ⚠ Paciente inválido o no asignado a este doctor.
        <br><br><a href='dashboard.php' style='color:#2563eb;text-decoration:none;'>← Volver al panel</a>
    </div>");
}

$patient_user_id = (int)$patient['user_id'];
$patientName = $patient['fullName'];

// ✅ Enviar mensaje (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['message'] ?? '');
    if ($text !== '') {
        // Insertar mensaje
        $ins = $pdo->prepare("
            INSERT INTO messages (doctor_id, patient_id, sender, message, sent_at, is_read)
            VALUES (?, ?, 'doctor', ?, NOW(), 0)
        ");
        $ins->execute([$doctor_id, $patient_id, $text]);

        // Crear notificación para el paciente
        $notif = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, read_status, created_at)
            VALUES (?, 'Nuevo mensaje del médico', ?, 0, NOW())
        ");
        $notif->execute([$patient_user_id, 'Has recibido un nuevo mensaje en el chat']);

        header("Location: chat.php?patient_id=" . $patient_id);
        exit();
    }
}

// ✅ Cargar mensajes
$stmt = $pdo->prepare("
    SELECT * FROM messages
    WHERE doctor_id = ? AND patient_id = ?
    ORDER BY sent_at ASC, id ASC
");
$stmt->execute([$doctor_id, $patient_id]);
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
?>

<main class="chat-page">
  <div class="chat-container">
    <header class="chat-header">
      <a href="dashboard.php" class="btn-secondary back">← Volver al panel</a>
      <div class="peer">
        <div class="avatar"></div>
        <div>
          <h2>Chat con <?= htmlspecialchars($patientName) ?></h2>
          <small class="muted">Canal seguro doctor ↔ paciente</small>
        </div>
      </div>
    </header>

    <div class="chat-body" id="chatBody">
      <?php if (empty($msgs)): ?>
        <div class="text-muted text-center mt-5">No hay mensajes aún.</div>
      <?php else: ?>
        <?php foreach ($msgs as $m): ?>
          <div class="bubble <?= $m['sender']==='doctor' ? 'me' : 'them' ?>">
            <div class="msg"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
            <div class="ts"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($m['sent_at']))) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <form class="chat-input" method="POST" autocomplete="off">
      <textarea name="message" rows="2" placeholder="Escribí tu mensaje..." required></textarea>
      <button type="submit" class="btn-primary">Enviar</button>
    </form>
  </div>
</main>

<?php include("../includes/footer.php"); ?>

<style>
.chat-container { max-width:900px; margin:20px auto; padding:0 16px; font-family:'Poppins',sans-serif; }
.chat-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; }
.back { text-decoration:none; color:#2563eb; font-weight:600; }
.peer { display:flex; align-items:center; gap:8px; }
.peer .avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#eef2ff; font-size:1.3rem; }
.chat-body { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px; height:65vh; overflow:auto; display:flex; flex-direction:column; gap:8px; }
.bubble { max-width:75%; padding:10px 12px; border-radius:12px; }
.bubble.me { align-self:flex-end; background:linear-gradient(90deg,#2563eb,#4f46e5); color:#fff; }
.bubble.them { align-self:flex-start; background:#f3f4f6; color:#0f172a; }
.ts { font-size:.75rem; opacity:.75; margin-top:4px; }
.chat-input { display:flex; gap:8px; margin-top:10px; }
.chat-input textarea { flex:1; border:1px solid #e5e7eb; border-radius:10px; padding:10px; resize:none; }
.chat-input button { background:linear-gradient(90deg,#2563eb,#06b6d4); border:none; color:#fff; border-radius:10px; padding:10px 20px; font-weight:600; }
.chat-input button:hover { opacity:.9; cursor:pointer; }
</style>

<script>
const bodyEl = document.getElementById('chatBody');
if (bodyEl) bodyEl.scrollTop = bodyEl.scrollHeight;
</script>
