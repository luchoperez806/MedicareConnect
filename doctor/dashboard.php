<?php
session_start();
require_once("../includes/db.php");
require_once("../includes/notifications.php");

// ===== Seguridad =====
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor' || !isset($_SESSION['doctor_id'])) {
    header("Location: ../login.php?role=doctor");
    exit();
}

$user_id   = (int)$_SESSION['user']['id'];
$doctor_id = (int)$_SESSION['doctor_id'];

/* ========================= PERFIL DEL MÉDICO ========================= */
$stmt = $pdo->prepare("
    SELECT u.fullName, u.email, d.specialization, d.office_address, d.profile_pic
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    WHERE d.id = ?
");
$stmt->execute([$doctor_id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

$doctorName = $doc['fullName'] ?? 'Médico/a';
$specialization = $doc['specialization'] ?? '';
$office_address = $doc['office_address'] ?? '';
$profile_pic = !empty($doc['profile_pic']) ? "../uploads/" . $doc['profile_pic'] : "../assets/images/default.png";

/* ========================= CITAS / TURNOS ========================= */
$stmt = $pdo->prepare("
    SELECT a.*, pu.id AS patient_user_id, pu.fullName AS patient_name, pu.email AS patient_email, p.id AS patient_id
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users pu ON p.user_id = pu.id
    WHERE a.doctor_id = ?
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================= ESTUDIOS ========================= */
$stmt = $pdo->prepare("
    SELECT s.*, p.id AS patient_id, u.fullName AS patient_name
    FROM studies s
    JOIN patients p ON s.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE s.doctor_id = ? OR s.doctor_id IS NULL
    ORDER BY s.uploaded_at DESC
");
$stmt->execute([$doctor_id]);
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ========================= NOTIFICACIONES ========================= */
$notifications = getNotifications($user_id);

/* ========================= MÉTRICAS ========================= */
$totalAppointments = count($appointments);
$pendingCount = count(array_filter($appointments, fn($a) => $a['status'] === 'pendiente'));
$confirmedCount = count(array_filter($appointments, fn($a) => $a['status'] === 'confirmada'));
$totalStudies = count($studies);

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
  background: #f4f7fb;
  font-family: 'Poppins', sans-serif;
}

/* ====== Encabezado principal ====== */
.header {
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
  color: white;
  padding: 25px;
  border-radius: 16px;
  margin-bottom: 20px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
.header img {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #fff;
}

/* ====== Tarjetas ====== */
.card {
  border-radius: 15px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
  border: none;
}

/* ====== Botones ====== */
.btn-gradient {
  background: linear-gradient(90deg, #3b82f6, #06b6d4);
  border: none;
  color: #fff;
  font-weight: 600;
}

.badge-soft {
  background-color: #eef2ff;
  color: #1e3a8a;
}

.btn-status-sm {
  font-size: 0.75rem;
  padding: 2px 8px;
}

/* ====== Botón Eliminar Estudio ====== */
.list-group-item button.btn-delete-study {
  border: none;
  color: #ef4444;
  background: none;
}
.list-group-item button.btn-delete-study:hover {
  text-decoration: underline;
}

/* ====== Botón Receta Digital ====== */
.btn-receta {
  background: linear-gradient(90deg, #06b6d4, #0891b2);
  color: #fff !important;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 6px 12px;
  transition: all 0.2s ease-in-out;
}
.btn-receta:hover {
  filter: brightness(1.1);
  transform: translateY(-1px);
  color: #fff;
}

/* ====== Botón Google Calendar ====== */
.btn-calendar {
  background: linear-gradient(90deg, #16a34a, #22c55e);
  color: #fff !important;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 6px 12px;
  transition: all 0.2s ease-in-out;
}
.btn-calendar:hover {
  filter: brightness(1.1);
  transform: translateY(-1px);
}
.btn, .btn-receta, .btn-calendar {
  transition: all 0.2s ease-in-out;
}

.btn:hover, .btn-receta:hover, .btn-calendar:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* ====== Responsivo ====== */
@media (max-width: 768px) {
  .header {
    text-align: center;
  }
  .header img {
    margin-bottom: 10px;
  }
  .d-flex.flex-md-row {
    flex-direction: column !important;
    align-items: stretch !important;
  }
  .d-flex.flex-md-row .btn {
    width: 100%;
  }
}
</style>


<div class="container py-3">

  <!-- HEADER -->
  <div class="header d-flex justify-content-between align-items-center flex-wrap">
    <div class="d-flex align-items-center gap-3">
      <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Perfil">
      <div>
        <h3 class="mb-0">Dr/a. <?= htmlspecialchars($doctorName) ?></h3>
        <small><?= htmlspecialchars($specialization) ?> — <?= htmlspecialchars($office_address) ?></small>
      </div>
    </div>

    <?php
    $stmtMsg = $pdo->prepare("
        SELECT COUNT(*) FROM messages
        WHERE doctor_id = ? AND sender = 'patient' AND is_read = 0
    ");
    $stmtMsg->execute([$doctor_id]);
    $newMessages = $stmtMsg->fetchColumn();
    ?>
    
    <div class="d-flex flex-column flex-md-row align-items-center gap-2 mt-3 mt-md-0">
      <a href="mensajes.php" class="btn btn-warning btn-sm position-relative">
        💬 Mensajes
        <?php if ($newMessages > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $newMessages ?>
          </span>
        <?php endif; ?>
      </a>

      <!-- 🔹 NUEVO: BOTÓN RECETA DIGITAL GENERAL -->
      <a href="https://rcta.me/" target="_blank" class="btn btn-receta btn-sm"> Receta Digital</a>
      <!-- 📅 CONECTAR CON GOOGLE CALENDAR -->
      <a href="google_connect.php" class="btn btn-success btn-sm">📅 Conectar con Google Calendar</a>
      <a href="profile.php" class="btn btn-light btn-sm">Editar Perfil</a>
      <a href="change_password.php" class="btn btn-outline-primary btn-sm">Cambiar Contraseña</a>
      <a href="logout.php" class="btn btn-danger btn-sm">Salir</a>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card p-3 text-center"><div class="fw-bold text-muted">Turnos Totales</div><h3><?= $totalAppointments ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><div class="fw-bold text-muted">Pendientes</div><h3><?= $pendingCount ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><div class="fw-bold text-muted">Confirmados</div><h3><?= $confirmedCount ?></h3></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><div class="fw-bold text-muted">Estudios</div><h3><?= $totalStudies ?></h3></div></div>
  </div>

  <!-- Turnos -->
  <div class="card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5>Turnos próximos</h5>
      <span class="badge badge-soft">Total: <?= $totalAppointments ?></span>
    </div>

    <?php if ($appointments): ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
        <tr>
          <td><?= $a['appointment_date'] ?></td>
          <td><?= substr($a['appointment_time'],0,5) ?></td>
          <td><?= htmlspecialchars($a['patient_name']) ?></td>
          <td><span class="badge text-bg-<?= $a['status']=='confirmada'?'success':($a['status']=='pendiente'?'warning':'danger') ?>"><?= ucfirst($a['status']) ?></span></td>
          <td class="text-end">
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-info btn-patient" data-patient-id="<?= $a['patient_id'] ?>">🩺 Ficha</button>
              <a href="chat.php?patient_id=<?= $a['patient_id'] ?>" class="btn btn-sm btn-gradient">💬 Chat</a>
              <?php if ($a['status']==='pendiente'): ?>
                <button class="btn btn-success btn-status-sm btn-confirm" data-id="<?= $a['id'] ?>">Confirmar</button>
                <button class="btn btn-danger btn-status-sm btn-cancel" data-id="<?= $a['id'] ?>">Cancelar</button>
              <?php elseif ($a['status']==='confirmada' && $a['video_call']): ?>
                <a href="video_call.php?appointment_id=<?= $a['id'] ?>" class="btn btn-primary btn-status-sm">🎥 Teleconsulta</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?><p class="text-muted">No hay turnos registrados aún.</p><?php endif; ?>
  </div>

  <!-- Estudios recientes -->
  <div class="card p-3 mb-4">
    <h5>Estudios recientes</h5>
    <?php if ($studies): ?>
    <ul class="list-group list-group-flush" id="studiesList">
      <?php foreach (array_slice($studies, 0, 8) as $s): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <div class="fw-semibold"><?= htmlspecialchars($s['patient_name']) ?></div>
            <small class="text-muted"><?= $s['uploaded_at'] ?></small>
          </div>
          <div>
            <a href="../uploads/<?= htmlspecialchars($s['file_name']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
            <button class="btn btn-sm btn-delete-study" data-id="<?= $s['id'] ?>">Eliminar</button>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?><p class="text-muted">Sin estudios cargados aún.</p><?php endif; ?>
  </div>

  <!-- Notificaciones -->
  <div class="card p-3">
    <h5>🔔 Notificaciones</h5>
    <?php if (empty($notifications)): ?>
      <p class="text-muted">No hay notificaciones nuevas.</p>
    <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($notifications as $n): ?>
          <li class="list-group-item <?= $n['read_status'] ? 'bg-light' : '' ?>">
            <div class="fw-bold"><?= htmlspecialchars($n['title']) ?></div>
            <?= htmlspecialchars($n['message']) ?><br>
            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<!-- FICHA PACIENTE -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="fichaPaciente">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Ficha del paciente</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body"><div id="ficha-content" class="text-center text-muted">Selecciona un paciente...</div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.btn-patient').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.patientId;
    const contentEl = document.getElementById('ficha-content');
    contentEl.innerHTML = '<div class="text-center text-muted p-3">⏳ Cargando ficha del paciente...</div>';

    try {
      const res = await fetch('get_patient_data.php?patient_id=' + id);
      const data = await res.json();
      if (data.success && data.patient) {
        const p = data.patient;
        const encodedName = encodeURIComponent(p.fullName || '');
        contentEl.innerHTML = `
          <div class="text-start">
            <h5 class="mb-3">${p.fullName}</h5>
            <p><strong>Email:</strong> ${p.email || '-'}</p>
            <p><strong>Teléfono:</strong> ${p.phone || '-'}</p>
            <p><strong>Dirección:</strong> ${p.address || '-'}</p>
            <p><strong>Fecha de nacimiento:</strong> ${p.birthdate || '-'}</p>
            <p><strong>Tipo de sangre:</strong> ${p.blood_type || '-'}</p>
            <p><strong>Alergias:</strong> ${p.allergies || '-'}</p>
            <p><strong>Condiciones médicas:</strong> ${p.medical_conditions || '-'}</p>
            <hr>
            <p><strong>Contacto de emergencia:</strong><br>
            ${p.emergency_contact_name || '-'} (${p.emergency_contact_phone || '-'})</p>
            <hr>
            <div class="text-center mt-3 d-flex flex-column gap-2">
              <a href="historia_clinica.php?patient_id=${id}" class="btn btn-gradient btn-sm">📋 Ver historia clínica completa</a>
              <a href="https://rcta.me/?paciente=${encodedName}" target="_blank" class="btn btn-receta btn-sm">💊 Receta Digital del paciente</a>
            </div>
          </div>
        `;
      } else {
        contentEl.innerHTML = `<div class="text-danger text-center p-3">❌ ${data.message || 'Error al cargar la ficha.'}</div>`;
      }
    } catch (e) {
      contentEl.innerHTML = '<div class="text-danger text-center p-3">❌ Error al cargar la ficha.</div>';
    }

    new bootstrap.Offcanvas(document.getElementById('fichaPaciente')).show();
  });
});

// ====== CONFIRMAR / CANCELAR ======
document.querySelectorAll('.btn-confirm, .btn-cancel').forEach(btn => {
  btn.addEventListener('click', async () => {
    const id = btn.dataset.id;
    const status = btn.classList.contains('btn-confirm') ? 'confirmada' : 'cancelada';
    if (!confirm(`¿Desea marcar esta cita como ${status}?`)) return;

    try {
      const res = await fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `id=${id}&status=${status}`
      });
      const data = await res.json();
      if (data.success) location.reload();
      else alert('Error: ' + data.message);
    } catch {
      alert('❌ Error de conexión con el servidor.');
    }
  });
});
</script>

<?php include("../includes/footer.php"); ?>
