<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}
require_once "../includes/db.php";

if (!isset($_GET['appointment_id'])) {
    die("Falta el parámetro appointment_id.");
}

$appointment_id = (int)$_GET['appointment_id'];

// Datos del turno + médico
$stmt = $pdo->prepare("
    SELECT a.*, u.fullName AS doctor_name, d.profile_pic
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$appointment) die("Turno no encontrado.");

$doctor_name = htmlspecialchars($appointment['doctor_name']);
$doctor_pic = htmlspecialchars($appointment['profile_pic'] ?? 'default.png');
?>

<?php include("../includes/header.php"); ?>

<main class="video-call-container">
    <div class="video-header">
        <div class="doctor-info">
            <img src="../uploads/<?php echo $doctor_pic; ?>" class="doctor-avatar">
            <div>
                <h2><?php echo $doctor_name; ?></h2>
                <p class="muted">Teleconsulta en curso</p>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-outline">⬅ Volver</a>
    </div>

    <div class="video-grid">
        <video id="localVideo" autoplay muted playsinline></video>
        <video id="remoteVideo" autoplay playsinline></video>
    </div>

    <div class="controls">
        <button id="muteBtn" class="btn-control" title="Silenciar micrófono">
            <span id="micIcon">🎤</span>
        </button>
        <button id="hangupBtn" class="btn-control hangup" title="Finalizar llamada">
            🔴
        </button>
    </div>
</main>

<?php include("../includes/footer.php"); ?>

<!-- ====================== ESTILOS ====================== -->
<style>
.video-call-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    height: 100vh;
    background: #f8fafc;
    padding: 20px;
}

.video-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    max-width: 900px;
    margin-bottom: 15px;
}

.doctor-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.doctor-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.video-grid {
    position: relative;
    width: 90%;
    max-width: 900px;
    height: 65vh;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
}

video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#localVideo {
    position: absolute;
    width: 200px;
    height: 150px;
    bottom: 15px;
    right: 15px;
    border: 2px solid #fff;
    border-radius: 10px;
}

.controls {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.btn-control {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: none;
    background: #e5e7eb;
    font-size: 24px;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-control:hover {
    transform: scale(1.1);
}

.hangup {
    background: #ef4444;
    color: white;
}
</style>

<!-- ====================== SCRIPT VIDEOLLAMADA ====================== -->
<script>
let localStream;
let micEnabled = true;

const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const muteBtn = document.getElementById('muteBtn');
const micIcon = document.getElementById('micIcon');
const hangupBtn = document.getElementById('hangupBtn');

// ✅ Inicializar cámara y micrófono
async function initMedia() {
    try {
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        // Simulación (no conexión real WebRTC local)
        remoteVideo.srcObject = localStream;
    } catch (err) {
        alert('No se pudo acceder a la cámara o micrófono.');
        console.error(err);
    }
}

// 🔇 Silenciar / activar micrófono
muteBtn.addEventListener('click', () => {
    micEnabled = !micEnabled;
    localStream.getAudioTracks().forEach(track => track.enabled = micEnabled);
    micIcon.textContent = micEnabled ? '🎤' : '🔇';
    muteBtn.style.background = micEnabled ? '#e5e7eb' : '#fca5a5';
});

// 🔴 Finalizar llamada
hangupBtn.addEventListener('click', () => {
    if (localStream) {
        localStream.getTracks().forEach(track => track.stop());
    }
    alert('La videollamada ha finalizado.');
    window.location.href = 'dashboard.php';
});

initMedia();
</script>

<!-- Topbar: volver al panel -->
    <div class="topbar">
    <a href="dashboard.php" class="btn-back">← Volver al panel</a>
    <div class="topbar-title">Teleconsulta</div>
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
