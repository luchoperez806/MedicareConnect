<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}
require_once "../includes/db.php";

$doctor_id     = (int)$_SESSION['doctor_id'];
$appointment_id= isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

// Validar turno
$st = $pdo->prepare("
  SELECT a.*, u.fullName AS patient_name
  FROM appointments a
  JOIN patients p ON a.patient_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE a.id = ? AND a.doctor_id = ? LIMIT 1
");
$st->execute([$appointment_id, $doctor_id]);
$appt = $st->fetch(PDO::FETCH_ASSOC);
if (!$appt) { die("Turno inválido."); }

?>
<?php include("../includes/header.php"); ?>

<main class="vc-page">
  <div class="vc-container">
    <header class="vc-header">
      <a href="dashboard.php" class="btn-secondary">← Volver al panel</a>
      <div>
        <h2>Videollamada con <?php echo htmlspecialchars($appt['patient_name']); ?></h2>
        <small class="muted"><?php echo htmlspecialchars($appt['appointment_date']." • ".substr($appt['appointment_time'],0,5)); ?></small>
      </div>
    </header>

    <div class="vc-stage" id="vcStage">
      <video id="localVideo" autoplay playsinline muted></video>
      <video id="remoteVideo" autoplay playsinline></video>
    </div>

    <div class="vc-controls">
      <button id="btnMic" class="btn-secondary">🎙️ Silenciar</button>
      <button id="btnFs" class="btn-secondary">⛶ Pantalla completa</button>
    </div>
  </div>
</main>

<?php include("../includes/footer.php"); ?>

<style>
.vc-container{ max-width:1000px; margin:20px auto; padding:0 16px; font-family:'Poppins',sans-serif;}
.vc-header{ display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;}
.vc-stage{ position:relative; background:#0b1220; border-radius:12px; overflow:hidden; border:1px solid #1f2937; aspect-ratio:16/9; display:grid; place-items:center; }
#localVideo{ position:absolute; right:10px; bottom:10px; width:280px; height:158px; background:#111827; border-radius:12px; object-fit:cover; border:2px solid rgba(255,255,255,.2); }
#remoteVideo{ width:100%; height:100%; object-fit:cover; }
.vc-controls{ display:flex; gap:10px; margin-top:10px; }
</style>

<script>
let localStream;
const localVideo  = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const btnMic      = document.getElementById('btnMic');
const btnFs       = document.getElementById('btnFs');
const stage       = document.getElementById('vcStage');

(async function init() {
  // Local preview (audio+video); mic mute toggle actúa sobre audio tracks
  try {
    localStream = await navigator.mediaDevices.getUserMedia({ video:true, audio:true });
    localVideo.srcObject = localStream;
    // (Señalización WebRTC real pendiente según infra de tu servidor/servicio TURN/STUN)
  } catch (e) {
    alert('No se pudo acceder a la cámara/micrófono: ' + e.message);
  }
})();

// Silenciar mic
let muted = false;
btnMic.addEventListener('click', () => {
  if (!localStream) return;
  localStream.getAudioTracks().forEach(tr => tr.enabled = muted);
  muted = !muted;
  btnMic.textContent = muted ? "🔇 Activar micrófono" : "🎙️ Silenciar";
});

// Pantalla completa
btnFs.addEventListener('click', () => {
  if (!document.fullscreenElement) {
    stage.requestFullscreen?.();
  } else {
    document.exitFullscreen?.();
  }
});
</script>
