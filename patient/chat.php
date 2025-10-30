<?php
session_start();
require_once "../includes/db.php";

// Verificación básica
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Validar que se pase el doctor_id
if (!isset($_GET['doctor_id'])) {
    die("Falta el parámetro doctor_id.");
}

$doctor_id = (int) $_GET['doctor_id'];
$user_id = $_SESSION['user']['id'];

// Obtener ID del paciente desde users
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$patient) die("Paciente no encontrado.");
$patient_id = $patient['id'];

// Datos del médico
$stmt = $pdo->prepare("SELECT d.*, u.fullName AS doctor_name, u.email FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ? LIMIT 1");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$doctor) die("Médico no encontrado.");
?>

<?php include("../includes/header.php"); ?>

<main class="chat-container">
    <div class="chat-header">
        <div class="doctor-info">
            <img src="../uploads/<?php echo htmlspecialchars($doctor['profile_pic'] ?? 'default.png'); ?>" class="doctor-avatar">
            <div>
                <h2><?php echo htmlspecialchars($doctor['doctor_name']); ?></h2>
                <p class="muted"><?php echo htmlspecialchars($doctor['specialization'] ?? 'Especialista'); ?></p>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-outline">⬅ Volver</a>
    </div>

    <div id="chatBox" class="chat-box"></div>

    <form id="chatForm" class="chat-form">
        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <input type="text" name="message" id="messageInput" placeholder="Escribe tu mensaje..." required>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </form>
</main>

<?php include("../includes/footer.php"); ?>

<!-- ======================= ESTILOS DE CHAT ======================= -->
<style>
.chat-container {
    max-width: 800px;
    margin: 30px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    height: 80vh;
    overflow: hidden;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
}

.doctor-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.doctor-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.chat-box {
    flex: 1;
    padding: 15px 20px;
    overflow-y: auto;
    background: #f9fafb;
}

.message {
    max-width: 70%;
    margin-bottom: 10px;
    padding: 10px 14px;
    border-radius: 12px;
    line-height: 1.4;
}

.message.patient {
    background: #dbeafe;
    align-self: flex-end;
    margin-left: auto;
}

.message.doctor {
    background: #e2e8f0;
    align-self: flex-start;
}

.chat-form {
    display: flex;
    border-top: 1px solid #e5e7eb;
    padding: 12px 16px;
    background: #fff;
}

.chat-form input[type=text] {
    flex: 1;
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    margin-right: 8px;
}

.chat-form button {
    padding: 10px 16px;
}
</style>

<!-- ======================= FUNCIONALIDAD AJAX ======================= -->
<script>
const chatBox = document.getElementById('chatBox');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');

async function loadMessages() {
    const res = await fetch('load_messages.php?doctor_id=<?php echo $doctor_id; ?>&patient_id=<?php echo $patient_id; ?>');
    const data = await res.json();
    chatBox.innerHTML = '';
    data.forEach(msg => {
        const div = document.createElement('div');
        div.classList.add('message', msg.sender);
        div.innerHTML = `<p>${msg.message}</p><small class="muted">${msg.sent_at}</small>`;
        chatBox.appendChild(div);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Enviar mensaje
chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(chatForm);
    const res = await fetch('send_message.php', { method: 'POST', body: formData });
    const json = await res.json();
    if (json.success) {
        messageInput.value = '';
        loadMessages();
    }
});

// Cargar mensajes cada 3 segundos
setInterval(loadMessages, 3000);
loadMessages();
</script>

<!-- Topbar: volver al panel -->
<div class="topbar">
    <a href="dashboard.php" class="btn-back">← Volver al panel</a>
    <div class="topbar-title">Chat con tu médico</div>
</div>

<style>
.topbar{
    display:flex; align-items:center; justify-content:space-between;
    gap:10px; background:#fff; border:1px solid #e5e7eb;
    border-radius:12px; padding:10px 14px; margin-bottom:12px;
    box-shadow:0 6px 20px rgba(15,23,42,0.06);
    }
.btn-back{
    display:inline-block; text-decoration:none; font-weight:600;
    background:#f3f4f6; border:1px solid #e6e7ee; color:#111827;
    padding:8px 12px; border-radius:10px;
}
.btn-back:hover{ background:#e5e7eb; }
.topbar-title{ font-weight:700; color:#111827; }
@media (max-width:640px){ .topbar-title{ font-size:.95rem; } }
</style>
