<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}
require_once "../includes/db.php";

$doctor_id = (int)$_SESSION['doctor_id'];
$appointment_id = isset($_GET['appointment_id']) ? (int)$_GET['appointment_id'] : 0;

$stmt = $pdo->prepare("
  SELECT a.*, u.fullName AS patient_name
  FROM appointments a
  JOIN patients p ON a.patient_id = p.id
  JOIN users u ON p.user_id = u.id
  WHERE a.id = ? AND a.doctor_id = ? LIMIT 1
");
$stmt->execute([$appointment_id, $doctor_id]);
$appt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$appt) { die("Turno inválido."); }
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Videollamada - Doctor</title>
<script src="https://meet.jit.si/external_api.js"></script>
<style>
html, body {
  height: 100%;
  margin: 0;
  background: #000;
  font-family: 'Poppins', sans-serif;
  overflow: hidden;
}
#meet {
  width: 100%;
  height: 100vh;
}
.header-bar {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 1000;
  background: rgba(0,0,0,0.6);
  color: #fff;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.header-bar a {
  background: #06b6d4;
  color: white;
  text-decoration: none;
  padding: 6px 10px;
  border-radius: 6px;
  font-weight: 600;
}
</style>
</head>
<body>

<div class="header-bar">
  <a href="dashboard.php">← Volver</a>
  <div>Videollamada con <strong><?= htmlspecialchars($appt['patient_name']); ?></strong></div>
</div>
<div id="meet"></div>

<script>
const domain = "meet.jit.si";
const roomName = "MedicareConnect_<?php echo $appointment_id; ?>";

const api = new JitsiMeetExternalAPI(domain, {
  roomName,
  width: "100%",
  height: "100%",
  parentNode: document.querySelector('#meet'),
  userInfo: { displayName: "Dr. <?php echo addslashes($_SESSION['user']['fullName'] ?? 'Médico'); ?>" },
  interfaceConfigOverwrite: {
    SHOW_JITSI_WATERMARK: false,
    SHOW_BRAND_WATERMARK: false,
    TOOLBAR_BUTTONS: [
      'microphone','camera','desktop','chat','raisehand','tileview','hangup'
    ]
  },
  configOverwrite: { disableDeepLinking: true }
});

// Pantalla completa automática
api.addEventListener('videoConferenceJoined', async () => {
  try { await document.documentElement.requestFullscreen(); } catch(e){}
});

// Al salir, vuelve al dashboard
api.addEventListener('readyToClose', async () => {
  if (document.fullscreenElement) { try { await document.exitFullscreen(); } catch(e){} }
  window.location.href = 'dashboard.php';
});
</script>
</body>
</html>
