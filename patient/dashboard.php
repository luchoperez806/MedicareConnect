<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}
require_once "../includes/db.php";
require_once "../includes/notifications.php";

/*
 Tablas usadas:
 - users (id, fullName, email, ...)
 - patients (id, user_id, phone?, birthdate?, profile_pic?)
 - doctors (id, user_id, specialization, working_days, working_hours, consultation_fee, profile_pic)
 - appointments (id, patient_id, doctor_id, appointment_date, appointment_time, status['pendiente','confirmada','cancelada'], video_call TINYINT)
 - studies (id, patient_id, file_name, uploaded_at, ...)
*/

// -------------------- Bootstrap de datos del paciente --------------------
$user_id = $_SESSION['user']['id'];

// Obtener/crear registro patients
$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$patient) {
    $pdo->prepare("INSERT INTO patients (user_id) VALUES (?)")->execute([$user_id]);
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
}
$patient_id = (int)$patient['id'];

// Datos del usuario (para saludo)
$stmtUser = $pdo->prepare("SELECT fullName, email FROM users WHERE id = ? LIMIT 1");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Próximo turno (confirmado o pendiente)
$stmt = $pdo->prepare("
    SELECT a.*, d.id AS doctor_record_id, u.fullName AS doctor_name
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u ON d.user_id = u.id
    WHERE a.patient_id = ? AND a.status IN ('confirmada','pendiente')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
");
$stmt->execute([$patient_id]);
$nextAppointment = $stmt->fetch(PDO::FETCH_ASSOC);

// Lista de médicos para selector
$stmt = $pdo->prepare("
    SELECT d.*, u.fullName AS doctor_name, u.id AS user_id
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    ORDER BY u.fullName ASC
");
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pre-carga turnos ocupados próximos 60 días (para agenda)
$daysToShow = 60;
$dates = [];
for ($i = 0; $i < $daysToShow; $i++) {
    $d = new DateTime("+$i days");
    $dates[] = $d->format('Y-m-d');
}
$appointmentsByDoctor = []; // [doctor_id][date][time]=true

if (!empty($doctors)) {
    $doctorIds = array_column($doctors, 'id');
    $phDocs = rtrim(str_repeat('?,', count($doctorIds)), ',');
    $phDates = rtrim(str_repeat('?,', count($dates)), ',');

    $sql = "SELECT doctor_id, appointment_date, appointment_time, status
            FROM appointments
            WHERE doctor_id IN ($phDocs) AND appointment_date IN ($phDates) AND status <> 'cancelada'";
    $params = array_merge($doctorIds, $dates);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $did = (int)$r['doctor_id'];
        $ad  = $r['appointment_date'];
        $at  = substr($r['appointment_time'], 0, 5);
        $appointmentsByDoctor[$did][$ad][$at] = true;
    }
}

// -------------------- Métricas para el mini-dashboard --------------------
$counts = ['pendiente'=>0,'confirmada'=>0,'cancelada'=>0];
try {
    $qry = $pdo->prepare("
        SELECT status, COUNT(*) c
        FROM appointments
        WHERE patient_id = ?
        GROUP BY status
    ");
    $qry->execute([$patient_id]);
    foreach ($qry->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $st = $row['status'];
        if (isset($counts[$st])) $counts[$st] = (int)$row['c'];
    }
} catch (Exception $e) {}

$studiesSeries = [];
$labelsSeries  = [];
try {
    // últimos 6 meses contando el actual (de más viejo a más nuevo)
    $ref = new DateTime('first day of this month');
    for ($i = 5; $i >= 0; $i--) {
        $month = (clone $ref)->modify("-$i month");
        $labelsSeries[] = $month->format('M Y');

        $start = $month->format('Y-m-01 00:00:00');
        $end   = $month->format('Y-m-t 23:59:59');

        $st = $pdo->prepare("SELECT COUNT(*) FROM studies WHERE patient_id = ? AND uploaded_at BETWEEN ? AND ?");
        $st->execute([$patient_id, $start, $end]);
        $studiesSeries[] = (int)$st->fetchColumn();
    }
} catch (Exception $e) {}

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* ======= Paleta y base ======= */
:root{
  --bg:#f5f7fb;
  --card:#ffffff;
  --muted:#6b7280;
  --text:#0f172a;
  --acc:#2563eb;
  --acc2:#0ea5e9;
  --ok:#16a34a;
  --warn:#d97706;
  --danger:#ef4444;
}
body{background:var(--bg); color:var(--text); font-family:'Poppins',sans-serif; margin:0;}
.container-max{max-width:1200px; margin:28px auto; padding:0 16px;}

/* ======= Hero ======= */
.hero-wrap{
  background: linear-gradient(120deg, #3b82f6, #06b6d4);
  border-radius: 16px;
  padding: 18px;
  color:#fff;
  display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;
  box-shadow: 0 12px 30px rgba(59,130,246,.18);
}
.hero-title{margin:0; font-weight:800; font-size:1.5rem;}
.hero-sub{margin:0; opacity:.95;}
.quick-actions .btn{margin-left:8px; margin-top:6px;}
.btn-grad{background:linear-gradient(90deg,#2563eb,#6366f1); color:#fff; border:none;}
.btn-outline{border:1px solid #e5e7eb; color:#0f172a; background:#fff;}
.btn-danger{background:var(--danger); color:#fff; border:none;}
.btn-accent{background:linear-gradient(90deg,#00b4d8,#0096c7); color:#fff; border:none;}

/* ======= Grid de 3 columnas ======= */
.grid-3{display:grid; grid-template-columns:1fr 1.7fr 1fr; gap:16px; margin-top:16px;}
@media (max-width:1100px){ .grid-3{grid-template-columns:1fr;} .quick-actions{display:flex; gap:8px; flex-wrap:wrap;} }

/* ======= Tarjetas ======= */
.card-lite{background:var(--card); border:1px solid #e8eef7; border-radius:14px; padding:16px; box-shadow:0 10px 24px rgba(2,6,23,.05);}
.card-lite h2{font-size:1.1rem; margin:0 0 10px 0;}

/* ======= Próximo turno ======= */
.appointment-card{background:linear-gradient(90deg,#f8fafc,#ffffff); border-radius:12px; padding:14px; box-shadow:inset 0 -1px 0 rgba(0,0,0,.03);}
.meta strong{display:block; font-size:1.05rem;}
.status-row{margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;}
.badge{padding:6px 10px; border-radius:999px; font-weight:700; font-size:.8rem;}
.badge.pendiente{background:#fff7ed; color:#92400e;}
.badge.confirmada{background:#d1fae5; color:#065f46;}
.badge.cancelada{background:#fef2f2; color:#991b1b;}
.badge.video.active{background:#ecfeff; color:#0f172a; border:1px solid #67e8f9;}
.panel-actions{margin-top:10px; display:flex; gap:8px; flex-wrap:wrap;}
.btn-cancel{ background:#fff1f2; color:#991b1b; border:1px solid #fecaca; padding:8px 12px; border-radius:10px; font-weight:700; }

/* ======= Selector & Agenda ======= */
.doctor-select-form{display:flex; flex-direction:column; gap:10px; margin-bottom:10px;}
.doctor-select-wrapper{display:flex; align-items:center; gap:10px; flex-wrap:wrap;}
.doctor-preview{width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid #e5e7eb;}
.select-basic{padding:8px; border-radius:8px; border:1px solid #e5e7eb; min-width:240px;}
.agenda-header{display:flex; align-items:center; justify-content:space-between; margin:12px 0;}
.calendar-grid{display:grid; grid-template-columns:repeat(7,1fr); gap:8px;}
.calendar-grid.daynames > div{font-weight:700; color:#374151; text-align:center;}
.day{background:#fff; padding:10px; border-radius:10px; text-align:center; cursor:pointer; font-weight:600; color:#374151; border:1px solid #eef2ff; box-shadow:0 2px 10px rgba(2,6,23,.03); transition:.18s;}
.day:hover{transform:translateY(-2px); box-shadow:0 8px 24px rgba(2,6,23,.08);}
.day.working-day{background:#ecffe9; color:#16a34a; border:1px solid rgba(16,185,129,.28);}
.day.empty{background:transparent; border:none; box-shadow:none; cursor:default;}
.day.selected{outline:3px solid rgba(37,99,235,.22);}
.schedule-container{margin-top:12px; background:#fff; border:1px solid #eef2ff; border-radius:12px; padding:12px; box-shadow:0 6px 24px rgba(2,6,23,.05);}
.slot-grid{display:flex; flex-wrap:wrap; gap:8px; margin-top:6px;}
.slot{padding:6px 10px; border-radius:8px; border:1px solid #e6eef9; background:#fff; font-weight:700; min-width:62px; text-align:center; transition:.12s; cursor:pointer;}
.slot.available{background:linear-gradient(90deg,#ecffe9,#f8fffb); color:#16a34a; border:1px solid rgba(16,185,129,.15);}
.slot.booked{background:linear-gradient(90deg,#fff1f2,#fff6f6); color:#ef4444; border:1px solid rgba(239,68,68,.12); cursor:not-allowed; opacity:.9;}
.slot:hover{transform:translateY(-3px);}

/* ======= Modal ======= */
.modal{position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(2,6,23,.45); z-index:9999;}
.modal-content{background:#fff; border-radius:14px; padding:18px; width:420px; max-width:92vw; box-shadow:0 10px 40px rgba(2,6,23,.25); position:relative; animation:pop .18s ease;}
@keyframes pop{from{transform:scale(.96); opacity:.6} to{transform:scale(1); opacity:1}}
.modal-close{position:absolute; right:10px; top:10px; border:none; background:transparent; font-size:22px; cursor:pointer;}
.modal-actions{display:flex; justify-content:flex-end; gap:8px; margin-top:10px;}

/* ======= Historial de estudios ======= */
.studies-list{list-style:none; padding-left:0; margin:0; display:flex; flex-direction:column; gap:8px;}
.studies-list li{display:flex; justify-content:space-between; align-items:center; border-bottom:1px dashed #eef2ff; padding-bottom:8px;}
.study-info{display:flex; gap:12px; align-items:center;}
.small{font-size:.85rem; color:var(--muted);}

/* ======= Mini métricas & gráficos ======= */
.kpis{display:grid; grid-template-columns:repeat(3,1fr); gap:12px;}
@media(max-width:600px){ .kpis{grid-template-columns:1fr;} }
.kpi-card{background:var(--card); border:1px solid #e6edf7; border-radius:12px; padding:12px; text-align:center;}
.kpi-card .caption{color:var(--muted); font-weight:600; font-size:.9rem;}
.kpi-card .value{font-size:1.4rem; font-weight:800;}

.chart-card{background:var(--card); border:1px solid #e6edf7; border-radius:12px; padding:12px;}
.chart-wrap{position:relative; height:180px;} /* compacto */
@media (max-width:600px){ .chart-wrap{height:160px;} }

.list-group-item.bg-warning-subtle {
  background-color: #fff9db !important;
}
.list-group-item {
  border: none;
  border-bottom: 1px solid #f1f5f9;
  padding: 10px 12px;
  border-radius: 8px;
}

/* ======= Botones ======= */
.btn{border:none; border-radius:10px; padding:8px 12px; font-weight:700;}
.btn:hover{filter:brightness(1.06); transform:translateY(-1px); transition:.2s;}
</style>

<main class="container-max">
  <!-- HERO -->
  <section class="hero-wrap">
    <div>
      <h1 class="hero-title">Hola, <?php echo htmlspecialchars($user['fullName']); ?> 👋</h1>
      <p class="hero-sub">Tu espacio para gestionar turnos, subir estudios y comunicarte con tu médico.</p>
    </div>
    <div class="quick-actions">
      <a href="profile.php" class="btn btn-outline">Editar Perfil</a>
      <a href="change_password.php" class="btn btn-outline">Cambiar Contraseña</a>
      <a href="export_history.php" class="btn btn-accent">📄 Descargar Historia Clínica</a>
      <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
  </section>

  <!-- KPIs + CHARTS -->
  <section class="mt-3">
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="kpis">
          <div class="kpi-card">
            <div class="caption">Turnos pendientes</div>
            <div class="value"><?php echo (int)$counts['pendiente']; ?></div>
          </div>
          <div class="kpi-card">
            <div class="caption">Turnos confirmados</div>
            <div class="value"><?php echo (int)$counts['confirmada']; ?></div>
          </div>
          <div class="kpi-card">
            <div class="caption">Turnos cancelados</div>
            <div class="value"><?php echo (int)$counts['cancelada']; ?></div>
          </div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="chart-card">
          <div class="small" style="font-weight:700; margin-bottom:6px;">Distribución de turnos</div>
          <div class="chart-wrap"><canvas id="chartStatus"></canvas></div>
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <div class="chart-card">
          <div class="small" style="font-weight:700; margin-bottom:6px;">Estudios últimos 6 meses</div>
          <div class="chart-wrap"><canvas id="chartStudies"></canvas></div>
        </div>
      </div>
    </div>
  </section>

  <!-- GRID PRINCIPAL -->
  <section class="grid-3">
    <!-- Col A: Próximo turno + Subir estudios -->
    <div class="card-lite">
      <h2>Tu próximo turno</h2>
      <?php if ($nextAppointment): ?>
        <div class="appointment-card">
          <div class="meta">
            <strong class="doc-title"><?php echo htmlspecialchars($nextAppointment['doctor_name']); ?></strong>
            <span class="text-muted">
              <?php echo htmlspecialchars($nextAppointment['appointment_date']); ?> •
              <?php echo substr($nextAppointment['appointment_time'],0,5); ?>
            </span>
          </div>
          <div class="status-row">
            <span class="badge <?php echo $nextAppointment['status']; ?>"><?php echo ucfirst($nextAppointment['status']); ?></span>
            <span class="badge video <?php echo $nextAppointment['video_call'] ? 'active' : ''; ?>">
              <?php echo $nextAppointment['video_call'] ? 'Teleconsulta habilitada' : 'Teleconsulta no habilitada'; ?>
            </span>
          </div>
          <div class="panel-actions">
            <a class="btn btn-outline" href="chat.php?doctor_id=<?php echo $nextAppointment['doctor_id']; ?>">Ir al chat</a>
            <?php if ((int)$nextAppointment['video_call'] === 1): ?>
              <a class="btn btn-grad" href="video_call.php?appointment_id=<?php echo (int)$nextAppointment['id']; ?>">Entrar a Teleconsulta</a>
            <?php else: ?>
              <button class="btn btn-outline" disabled>Teleconsulta (esperando habilitación)</button>
            <?php endif; ?>
          </div>
          <div class="mt-2">
            <form id="cancelAppointmentForm" method="POST" action="cancel_appointment.php">
              <input type="hidden" name="appointment_id" value="<?php echo (int)$nextAppointment['id']; ?>">
              <button type="submit" class="btn-cancel">❌ Cancelar turno</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <p class="text-muted">No tenés turnos agendados. Reservá uno más abajo 👇</p>
      <?php endif; ?>

      <hr>

      <h2>Subir estudios</h2>
      <form id="uploadStudiesForm" action="upload_study.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <label class="file-label" style="display:inline-flex; align-items:center; gap:10px; padding:8px 12px; background:#fff; border:1px dashed #e6eef9; border-radius:8px; cursor:pointer;">
          <input type="file" name="files[]" multiple required style="display:none;">
          <span>Seleccionar archivos</span>
        </label>
        <p class="small mt-1">Podés subir PDF, JPG, PNG, DOCX u otros formatos.</p>
        <button type="submit" class="btn btn-grad">Subir estudios</button>
      </form>
      <div id="uploadResult" class="mt-2"></div>
    </div>

    <!-- Col B: Reserva con agenda -->
    <div class="card-lite">
      <h2>Reservar turno</h2>
      <p class="text-muted">Elegí un profesional y consultá su agenda mensual para reservar tu turno.</p>

      <form id="doctorSelectForm" class="doctor-select-form">
        <label for="doctorSelect" class="fw-semibold">Profesional:</label>
        <div class="doctor-select-wrapper">
          <img id="doctorPreview" src="../uploads/default.png" alt="Foto médico" class="doctor-preview">
          <select id="doctorSelect" name="doctor_id" class="select-basic" required>
            <option value="">-- Elegí un médico --</option>
            <?php foreach ($doctors as $doc): ?>
              <option value="<?php echo (int)$doc['id']; ?>"
                      data-img="../uploads/<?php echo htmlspecialchars($doc['profile_pic'] ?: 'default.png'); ?>">
                <?php echo htmlspecialchars($doc['doctor_name'] . ' - ' . ($doc['specialization'] ?? '')); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="button" id="viewAgendaBtn" class="btn btn-grad">Ver agenda</button>
        </div>
      </form>

      <div id="agendaContainer" style="display:none;">
        <div class="agenda-header">
          <button id="prevMonth" class="btn btn-outline">&lt;</button>
          <h3 id="monthLabel" class="m-0" style="font-size:1rem;"></h3>
          <button id="nextMonth" class="btn btn-outline">&gt;</button>
        </div>

        <div class="calendar-grid daynames">
          <div>Dom</div><div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div>
        </div>
        <div id="calendarGrid" class="calendar-grid"></div>
        <div id="scheduleContainer" class="schedule-container"></div>
      </div>
    </div>

    <!-- Col C: Mensajes & Historial -->
    <div class="card-lite">
      <h2>Mensajes & Estudios</h2>

      <div class="mb-2">
        <h6 class="mb-1">Contactá a tu médico</h6>
        <p class="small">Abrí el chat con tu médico cuando tengas un turno confirmado.</p>
        <?php if ($nextAppointment): ?>
          <a class="btn btn-outline" href="chat.php?doctor_id=<?php echo (int)$nextAppointment['doctor_id']; ?>">Ir al chat</a>
          <?php if ((int)$nextAppointment['video_call'] === 1): ?>
            <a class="btn btn-grad" href="video_call.php?appointment_id=<?php echo (int)$nextAppointment['id']; ?>">Entrar a Teleconsulta</a>
          <?php else: ?>
            <button class="btn btn-outline" disabled>Teleconsulta (esperando habilitación)</button>
          <?php endif; ?>
        <?php else: ?>
          <p class="small text-muted">No hay chat directo hasta que no reserves un turno.</p>
        <?php endif; ?>
      </div>

      <!-- 🔔 NOTIFICACIONES -->
<div class="card-lite mt-3">
  <h2>Notificaciones</h2>
  <?php
  $notifications = getNotifications($_SESSION['user']['id']);
  if (empty($notifications)):
  ?>
    <p class="text-muted">No tenés notificaciones nuevas.</p>
  <?php else: ?>
    <ul class="list-group list-group-flush">
      <?php foreach ($notifications as $n): ?>
      <li class="list-group-item d-flex justify-content-between align-items-start <?= $n['read_status'] ? 'bg-light' : 'bg-warning-subtle'; ?>">
        <div class="ms-2 me-auto">
          <div class="fw-bold"><?= htmlspecialchars($n['title']); ?></div>
          <?= htmlspecialchars($n['message']); ?>
          <small class="text-muted d-block"><?= date('d/m/Y H:i', strtotime($n['created_at'])); ?></small>
        </div>
        <?php if(!$n['read_status']): ?>
        <form method="post" action="../includes/mark_notification.php">
          <input type="hidden" name="id" value="<?= $n['id']; ?>">
          <button class="btn btn-sm btn-outline-success" title="Marcar como leída">✔</button>
        </form>
        <?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>


      <hr>

      <div>
        <h6 class="mb-1">Tus estudios subidos</h6>
        <a href="export_history.php" class="btn btn-outline mb-2">📄 Descargar Historia Clínica</a>
        <?php
          $stmt = $pdo->prepare("SELECT * FROM studies WHERE patient_id = ? ORDER BY uploaded_at DESC LIMIT 20");
          $stmt->execute([$patient_id]);
          $myStudies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if (!$myStudies): ?>
          <p class="text-muted small">Aún no subiste estudios.</p>
        <?php else: ?>
          <ul class="studies-list">
            <?php foreach ($myStudies as $s): ?>
              <li>
                <div class="study-info">
                  <a href="../uploads/<?php echo htmlspecialchars($s['file_name']); ?>" target="_blank">
                    <?php echo htmlspecialchars($s['file_name']); ?>
                  </a>
                  <span class="small"><?php echo htmlspecialchars($s['uploaded_at']); ?></span>
                </div>
                <div class="study-actions">
                  <a class="btn btn-sm btn-outline" href="edit_study.php?id=<?php echo (int)$s['id']; ?>">Editar</a>
                  <a class="btn btn-sm btn-danger" href="delete_study.php?id=<?php echo (int)$s['id']; ?>" onclick="return confirm('¿Eliminar este estudio?');">Eliminar</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<!-- Modal de reserva -->
<div id="reserveModal" class="modal">
  <div class="modal-content">
    <button class="modal-close" type="button" aria-label="Cerrar">&times;</button>
    <h3 class="mb-2" style="font-size:1.1rem;">Confirmar reserva</h3>
    <div id="reserveInfo" class="small"></div>
    <form id="reserveForm" class="mt-2">
      <input type="hidden" name="doctor_id" id="form_doctor_id">
      <input type="hidden" name="appointment_date" id="form_appointment_date">
      <input type="hidden" name="appointment_time" id="form_appointment_time">
      <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
      <div class="small text-muted">El turno quedará en estado <strong>pendiente</strong> hasta la confirmación del profesional.</div>
      <div class="modal-actions">
        <button type="button" class="btn btn-outline modal-cancel">Cancelar</button>
        <button type="submit" class="btn btn-grad">Confirmar Reserva</button>
      </div>
    </form>
    <div id="reserveFeedback" class="small mt-2"></div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Helpers
const qs  = s => document.querySelector(s);
const qsa = s => Array.from(document.querySelectorAll(s));

// --------- Charts (compactos) ---------
document.addEventListener('DOMContentLoaded', () => {
  const statCtx = document.getElementById('chartStatus');
  if (statCtx) {
    const dataStatus = {
      labels: ['Pend.', 'Conf.', 'Canc.'],
      datasets: [{
        data: [<?php echo (int)$counts['pendiente']; ?>, <?php echo (int)$counts['confirmada']; ?>, <?php echo (int)$counts['cancelada']; ?>],
        backgroundColor: ['#f59e0b','#22c55e','#ef4444']
      }]
    };
    new Chart(statCtx, {
      type: 'doughnut',
      data: dataStatus,
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '60%'
      }
    });
  }

  const studiesCtx = document.getElementById('chartStudies');
  if (studiesCtx) {
    const labels = <?php echo json_encode($labelsSeries); ?>;
    const series = <?php echo json_encode($studiesSeries); ?>;
    new Chart(studiesCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{ label: 'Estudios', data: series }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display:false } },
        scales: { y: { beginAtZero:true, ticks:{ precision:0 } } }
      }
    });
  }
});

// --------- Agenda (mensual) ----------
document.addEventListener('DOMContentLoaded', function() {
  const doctors = <?php echo json_encode($doctors); ?>;
  const appointments = <?php echo json_encode($appointmentsByDoctor); ?>;
  const weekFull = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
  let selectedDoctor = null;
  let currentMonth = new Date();

  const agendaContainer = qs('#agendaContainer');
  const calendarGrid = qs('#calendarGrid');
  const monthLabel = qs('#monthLabel');
  const scheduleContainer = qs('#scheduleContainer');
  const doctorPreview = qs('#doctorPreview');
  const doctorSelect = qs('#doctorSelect');

  doctorSelect.addEventListener('change', () => {
    const opt = doctorSelect.options[doctorSelect.selectedIndex];
    doctorPreview.src = opt?.getAttribute('data-img') || '../uploads/default.png';
  });

  function parseWorkingDays(raw) {
    const map = {'á':'a','é':'e','í':'i','ó':'o','ú':'u','Á':'a','É':'e','Í':'i','Ó':'o','Ú':'u'};
    const norm = (raw || '').replace(/[áéíóúÁÉÍÓÚ]/g, m => map[m]).toLowerCase();
    return {
      domingo:   norm.includes('domingo'),
      lunes:     norm.includes('lunes'),
      martes:    norm.includes('martes'),
      miercoles: norm.includes('miercoles'),
      jueves:    norm.includes('jueves'),
      viernes:   norm.includes('viernes'),
      sabado:    norm.includes('sabado')
    };
  }

  function generateSlots(start, end, stepMinutes=20) {
    const slots = [];
    if (!start || !end) return slots;
    const cur = new Date(`1970-01-01T${start}:00`);
    const endT = new Date(`1970-01-01T${end}:00`);
    if (isNaN(cur) || isNaN(endT)) return slots;
    while (cur < endT) {
      slots.push(cur.toTimeString().slice(0,5));
      cur.setMinutes(cur.getMinutes() + stepMinutes);
    }
    return slots;
  }

  function renderCalendar(doctor) {
    if (!doctor) return;
    agendaContainer.style.display = 'block';
    calendarGrid.innerHTML = '';
    scheduleContainer.innerHTML = '';

    const y = currentMonth.getFullYear();
    const m = currentMonth.getMonth();
    monthLabel.textContent = currentMonth.toLocaleString('es-AR', { month: 'long', year: 'numeric' });

    const first = new Date(y, m, 1);
    const last  = new Date(y, m+1, 0);
    const startDay = first.getDay();
    const totalDays = last.getDate();

    const wd = parseWorkingDays(doctor.working_days);
    const hours = (doctor.working_hours || '').replace(/\s/g,'') || '09:00-17:00';
    const [startH, endH] = hours.split('-');
    const slots = generateSlots(startH, endH, 20);

    for (let i=0; i<startDay; i++) {
      const empty = document.createElement('div');
      empty.className = 'day empty';
      calendarGrid.appendChild(empty);
    }
    for (let d=1; d<=totalDays; d++) {
      const date = new Date(y, m, d);
      const idx = date.getDay();
      const dayEl = document.createElement('div');
      dayEl.className = 'day';
      dayEl.innerHTML = `<span>${d}</span>`;

      const isWorking =
        (idx===0 && wd.domingo) || (idx===1 && wd.lunes) || (idx===2 && wd.martes) ||
        (idx===3 && wd.miercoles) || (idx===4 && wd.jueves) || (idx===5 && wd.viernes) ||
        (idx===6 && wd.sabado);

      if (isWorking) {
        dayEl.classList.add('working-day');
        dayEl.addEventListener('click', () => {
          qsa('.day').forEach(dn=>dn.classList.remove('selected'));
          dayEl.classList.add('selected');
          showSchedule(date, slots, doctor.id, doctor.doctor_name);
        });
      }
      calendarGrid.appendChild(dayEl);
    }
  }

  function showSchedule(date, slots, doctorId, doctorName) {
    const dateStr = date.toISOString().split('T')[0];
    const booked = appointments[doctorId]?.[dateStr] || {};
    scheduleContainer.innerHTML = `
      <h6 class="m-0">Horarios para el <strong>${date.toLocaleDateString('es-AR')}</strong></h6>
      <div class="slot-grid"></div>`;
    const slotGrid = scheduleContainer.querySelector('.slot-grid');

    slots.forEach(slot => {
      const btn = document.createElement('button');
      const isBooked = Boolean(booked[slot]);
      btn.className = `slot ${isBooked ? 'booked' : 'available'}`;
      btn.textContent = slot;
      btn.disabled = isBooked;
      btn.addEventListener('click', () => {
        qs('#form_doctor_id').value = doctorId;
        qs('#form_appointment_date').value = dateStr;
        qs('#form_appointment_time').value = slot;
        qs('#reserveInfo').innerHTML = `
          <p>Profesional: <strong>${doctorName || ''}</strong></p>
          <p>Fecha: <strong>${dateStr}</strong></p>
          <p>Horario: <strong>${slot}</strong></p>`;
        qs('#reserveModal').style.display = 'flex';
      });
      slotGrid.appendChild(btn);
    });
  }

  qs('#viewAgendaBtn').addEventListener('click', () => {
    const id = doctorSelect.value;
    if (!id) { alert('Seleccioná un médico.'); return; }
    selectedDoctor = doctors.find(d => String(d.id) === String(id));
    renderCalendar(selectedDoctor);
  });
  qs('#prevMonth').addEventListener('click', ()=>{ if(!selectedDoctor)return; currentMonth.setMonth(currentMonth.getMonth()-1); renderCalendar(selectedDoctor); });
  qs('#nextMonth').addEventListener('click', ()=>{ if(!selectedDoctor)return; currentMonth.setMonth(currentMonth.getMonth()+1); renderCalendar(selectedDoctor); });
});

// --------- Modal & reservas ----------
let selectedSlot = null;

qsa('.btn-toggle').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const id = btn.getAttribute('data-target');
    if(!id) return;
    const el = document.getElementById(id);
    if(!el) return;
    qsa('.slots').forEach(s=>{ if(s.id !== id) s.style.display='none'; });
    el.style.display = (el.style.display==='none'||el.style.display==='')?'block':'none';
  });
});

const reserveModal = qs('#reserveModal');
qs('.modal-close')?.addEventListener('click', ()=> reserveModal.style.display='none');
qsa('.modal-cancel').forEach(b=> b.addEventListener('click', ()=> reserveModal.style.display='none'));

const reserveForm = qs('#reserveForm');
if (reserveForm) {
  reserveForm.addEventListener('submit', async e=>{
    e.preventDefault();
    const data = new FormData(reserveForm);
    const btn = reserveForm.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Reservando...';
    try {
      const res = await fetch('book_appointment.php', { method:'POST', body:data });
      const json = await res.json();
      if (json.success) {
        qs('#reserveFeedback').innerHTML = `<span style="color:green;font-weight:700">Turno reservado correctamente ✅</span>`;
        setTimeout(()=>{ reserveModal.style.display='none'; location.reload(); }, 800);
      } else {
        qs('#reserveFeedback').innerHTML = `<span style="color:#b91c1c;font-weight:700">${json.message || 'Error al reservar'}</span>`;
      }
    } catch(err) {
      qs('#reserveFeedback').innerHTML = `<span style="color:#b91c1c;font-weight:700">Error de comunicación, intentá nuevamente.</span>`;
    } finally {
      btn.disabled = false; btn.textContent = 'Confirmar Reserva';
    }
  });
}

// ESC para cerrar modal
document.addEventListener('keydown', e => { if (e.key === 'Escape') qs('#reserveModal').style.display = 'none'; });

// Cancelar turno (AJAX)
const cancelForm = document.querySelector('#cancelAppointmentForm');
if (cancelForm) {
  cancelForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!confirm('¿Seguro que querés cancelar el turno?')) return;
    const fd = new FormData(cancelForm);
    try {
      const res = await fetch('cancel_appointment.php', { method:'POST', body: fd });
      const json = await res.json();
      if (json.success) { alert('Turno cancelado ✅'); location.reload(); }
      else { alert('❌ ' + (json.message || 'Error al cancelar.')); }
    } catch (err) { alert('❌ Error de comunicación.'); }
  });
}

// Upload de estudios (AJAX)
const uploadForm = qs('#uploadStudiesForm');
if (uploadForm) {
  uploadForm.addEventListener('submit', async e => {
    e.preventDefault();
    const fd = new FormData(uploadForm);
    const btn = uploadForm.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Subiendo...';
    try {
      const res = await fetch(uploadForm.action, { method:'POST', body:fd });
      const json = await res.json();
      const out = qs('#uploadResult');
      if (json.success) {
        out.innerHTML = `<div style="color:green;font-weight:700">Archivos subidos correctamente ✅</div>`;
        uploadForm.reset();
        setTimeout(()=> location.reload(), 900);
      } else {
        out.innerHTML = `<div style="color:#b91c1c;font-weight:700">${json.message || 'Error al subir'}</div>`;
      }
    } catch (err) {
      qs('#uploadResult').innerHTML = `<div style="color:#b91c1c;font-weight:700">Error de comunicación al subir archivos.</div>`;
    } finally {
      btn.disabled = false; btn.textContent = 'Subir estudios';
    }
  });
}


</script>
