<?php
session_start();
require_once("../includes/db.php");

// Seguridad: solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

$adminName = $_SESSION['user']['name'] ?? 'Administrador';

/* ========================= MÉTRICAS RÁPIDAS ========================= */
$totDoctors = $totPatients = $totAppointments = $totStudies = 0;
try { $totDoctors      = (int) $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(); } catch(Exception $e){}
try { $totPatients     = (int) $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(); } catch(Exception $e){}
try { $totAppointments = (int) $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn(); } catch(Exception $e){}
try { $totStudies      = (int) $pdo->query("SELECT COUNT(*) FROM studies")->fetchColumn(); } catch(Exception $e){}

/* ========================= LISTADOS (TABLAS) ========================= */
// Próximas 10 citas
$upcoming = [];
try {
    $stmt = $pdo->query("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
                du.fullName AS doctor_name, pu.fullName AS patient_name
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        JOIN patients p ON a.patient_id = p.id
        JOIN users pu ON p.user_id = pu.id
        WHERE a.status IN ('pendiente','confirmada')
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 10
    ");
    $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Últimos 10 estudios
$latestFiles = [];
try {
    $stmt = $pdo->query("
        SELECT s.id, s.file_name, s.uploaded_at,
                u.fullName AS patient_name
        FROM studies s
        JOIN patients p ON s.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        ORDER BY s.uploaded_at DESC
        LIMIT 10
    ");
    $latestFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Actividad reciente (mezcla 10)
$activity = [];
try {
    $actAppointments = $pdo->query("
        SELECT 'cita' AS type, a.id, CONCAT(a.appointment_date,' ',a.appointment_time) AS ts,
                du.fullName AS doctor_name, pu.fullName AS patient_name, a.status
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        JOIN patients p ON a.patient_id = p.id
        JOIN users pu ON p.user_id = pu.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    $actStudies = $pdo->query("
        SELECT 'estudio' AS type, s.id, s.uploaded_at AS ts,
                u.fullName AS patient_name, s.file_name
        FROM studies s
        JOIN patients p ON s.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        ORDER BY s.uploaded_at DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);

    $activity = array_merge($actAppointments ?? [], $actStudies ?? []);
    usort($activity, function($a,$b){ return strcmp($b['ts'] ?? '', $a['ts'] ?? ''); });
    $activity = array_slice($activity, 0, 10);
} catch (Exception $e) {}

/* ========================= GRÁFICOS (ÚLTIMOS 6 MESES) ========================= */
function lastMonthsLabels($n = 6) {
    $labels = [];
    $dt = new DateTime('first day of this month');
    for ($i = $n-1; $i >= 0; $i--) {
        $copy = clone $dt;
        $copy->modify("-$i month");
        $labels[] = $copy->format('Y-m');
    }
    return $labels;
}
$labelsKeys = lastMonthsLabels(6);
$labelsHuman = [];
foreach ($labelsKeys as $k) {
    $labelsHuman[] = DateTime::createFromFormat('Y-m', $k)->format('M Y');
}

// Citas confirmadas por mes (appointment_date)
$seriesAppointments = array_fill(0, count($labelsKeys), 0);
try {
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(appointment_date, '%Y-%m') AS ym, COUNT(*) AS c
        FROM appointments
        WHERE status = 'confirmada'
            AND appointment_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY ym
        ORDER BY ym
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) { $map[$r['ym']] = (int)$r['c']; }
    foreach ($labelsKeys as $i => $k) {
        $seriesAppointments[$i] = $map[$k] ?? 0;
    }
} catch (Exception $e) {}

// Estudios por mes (uploaded_at)
$seriesStudies = array_fill(0, count($labelsKeys), 0);
try {
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(uploaded_at, '%Y-%m') AS ym, COUNT(*) AS c
        FROM studies
        WHERE uploaded_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY ym
        ORDER BY ym
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $map = [];
    foreach ($rows as $r) { $map[$r['ym']] = (int)$r['c']; }
    foreach ($labelsKeys as $i => $k) {
        $seriesStudies[$i] = $map[$k] ?? 0;
    }
} catch (Exception $e) {}

// Médicos nuevos por mes (intento con users.created_at para role=doctor; fallback a ceros si no existe)
$seriesDoctors = array_fill(0, count($labelsKeys), 0);
try {
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(u.created_at, '%Y-%m') AS ym, COUNT(*) AS c
        FROM users u
        JOIN doctors d ON d.user_id = u.id
        WHERE u.role = 'doctor'
            AND u.created_at IS NOT NULL
            AND u.created_at >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
        GROUP BY ym
        ORDER BY ym
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        $map = [];
        foreach ($rows as $r) { $map[$r['ym']] = (int)$r['c']; }
        foreach ($labelsKeys as $i => $k) {
            $seriesDoctors[$i] = $map[$k] ?? 0;
        }
    }
} catch (Exception $e) {
    // si hay error (p.ej. no existe created_at), dejamos los ceros
}

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
    --bg:#eef5ff;
    --card:#ffffff;
    --muted:#6b7280;
    --line:#e6eef8;
    --c1:#0ea5e9;
    --c2:#3b82f6;
    --c3:#06b6d4;
}
body{
    background:
        radial-gradient(1000px 500px at -10% -10%, rgba(14,165,233,.18), transparent),
        radial-gradient(800px 400px at 110% -10%, rgba(59,130,246,.12), transparent),
        var(--bg);
    font-family:'Poppins',sans-serif;
    color:#0f172a;
}
.container-fluid{max-width:1280px;}

.sidebar{
  background: linear-gradient(180deg, #0ea5e9, #3b82f6);
  color:#fff; min-height:100vh;
  box-shadow: 6px 0 30px rgba(59,130,246,.18);
}
.brand{
  display:flex; align-items:center; gap:12px; padding:20px 16px 10px;
}
.brand .logo{
  width:44px;height:44px;border-radius:50%;
  background:#fff;color:#0ea5e9;display:flex;align-items:center;justify-content:center;
  font-weight:800;
}
.brand .title{font-weight:700; letter-spacing:.2px}
.nav-link{
  color:#eaf6ff; font-weight:600; border-radius:12px; padding:10px 14px; margin:6px 10px;
}
.nav-link:hover, .nav-link.active{
  background: rgba(255,255,255,.18);
  color:#fff;
}
@media (max-width: 992px){
  .sidebar .title{display:none;}
  .sidebar .logo{margin-inline:auto;}
  .nav-link span.label{display:none;}
}

.topbar{
  display:flex; justify-content:space-between; align-items:center; gap:12px;
  padding:16px; background:var(--card); border-radius:16px; border:1px solid var(--line);
  box-shadow: 0 8px 30px rgba(59,130,246,.08);
}
.search{
  display:flex; align-items:center; gap:10px; border:1px solid var(--line);
  padding:8px 12px; border-radius:12px; min-width:240px;
  background:#f8fbff;
}
.search input{border:none; outline:none; background:transparent; width:100%}

.kpi .card{
  border:1px solid var(--line); border-radius:16px;
  box-shadow: 0 8px 24px rgba(14,165,233,.08);
}
.kpi .value{font-size:1.8rem; font-weight:800;}
.kpi .caption{color:var(--muted);}

.section-card{
  border:1px solid var(--line); border-radius:16px; background:var(--card);
  box-shadow: 0 10px 30px rgba(59,130,246,.08);
}

/* tamaño moderado para gráficos */
.chart-wrap{
  position:relative; width:100%;
  height: 260px;
}

/* timeline */
.timeline .item{display:flex; gap:10px; margin-bottom:10px;}
.timeline .dot{width:10px;height:10px;border-radius:50%; margin-top:8px;}
.dot-cita{background:#3b82f6}
.dot-estudio{background:#06b6d4}

/* botones */
.btn-soft{
  background:linear-gradient(90deg, #0ea5e9, #3b82f6); border:none;
  color:#fff; font-weight:700; border-radius:12px; padding:10px 14px;
  box-shadow:0 10px 20px rgba(59,130,246,.18);
}
.btn-outline{
  border:1px solid #cfe2ff; color:#2563eb; background:#fff; font-weight:700;
}
</style>

<div class="container-fluid py-3">
  <div class="row g-3">
    <!-- Sidebar -->
    <div class="col-12 col-lg-3">
      <div class="sidebar rounded-4 p-2 h-100">
        <div class="brand">
          <div class="logo">MC</div>
          <div class="title">MedicareConnect</div>
        </div>
        <nav class="nav flex-column mt-2">
          <a class="nav-link active" href="dashboard.php"><span class="label">Inicio</span></a>
          <a class="nav-link" href="medicos.php"> <span class="label">Médicos</span></a>
          <a class="nav-link" href="pacientes.php"> <span class="label">Pacientes</span></a>
          <a class="nav-link" href="appointments.php"><span class="label">Citas</span></a>
          <a class="nav-link" href="estudios.php"><span class="label">Estudios</span></a>
          <a class="nav-link" href="logout.php"><span class="label">Salir</span></a>
        </nav>
      </div>
    </div>

    <!-- Main -->
    <div class="col-12 col-lg-9">
      <!-- Topbar -->
      <div class="topbar mb-3">
        <div class="search">
          🔎 <input type="text" placeholder="Buscar rápido (Ctrl+/)">
        </div>
        <div>Bienvenido, <strong><?php echo htmlspecialchars($adminName); ?></strong></div>
      </div>

      <!-- KPIs -->
      <div class="row g-3 kpi">
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="caption">Médicos</div>
            <div class="value"><?php echo number_format($totDoctors); ?></div>
            <a class="btn btn-outline btn-sm mt-2" href="medicos.php">Ver listado</a>
            <a href="register-doctor.php" class="btn btn-soft btn-sm">➕ Nuevo médico</a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="caption">Pacientes</div>
            <div class="value"><?php echo number_format($totPatients); ?></div>
            <a class="btn btn-outline btn-sm mt-2" href="pacientes.php">Ver pacientes</a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="caption">Citas</div>
            <div class="value"><?php echo number_format($totAppointments); ?></div>
            <a class="btn btn-outline btn-sm mt-2" href="appointments.php">Gestionar</a>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="card p-3">
            <div class="caption">Estudios</div>
            <div class="value"><?php echo number_format($totStudies); ?></div>
            <a class="btn btn-outline btn-sm mt-2" href="estudios.php">Ver estudios</a>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
          <div class="section-card p-3">
            <h6 class="mb-2">Citas confirmadas (últimos 6 meses)</h6>
            <div class="chart-wrap"><canvas id="chartAppointments"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="section-card p-3">
            <h6 class="mb-2">Estudios subidos (últimos 6 meses)</h6>
            <div class="chart-wrap"><canvas id="chartStudies"></canvas></div>
          </div>
        </div>
        <div class="col-12 col-xl-4">
          <div class="section-card p-3">
            <h6 class="mb-2">Médicos nuevos (últimos 6 meses)</h6>
            <div class="chart-wrap"><canvas id="chartDoctors"></canvas></div>
          </div>
        </div>
      </div>

      <!-- Tablas -->
      <div class="row g-3 mt-1">
        <div class="col-12 col-xl-8">
          <div class="section-card p-3">
            <h6 class="mb-2">Próximas citas</h6>
            <?php if (empty($upcoming)): ?>
              <p class="text-muted">No hay citas próximas.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Paciente</th>
                      <th>Médico</th>
                      <th>Estado</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach($upcoming as $row): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                      <td><?php echo htmlspecialchars(substr($row['appointment_time'],0,5)); ?></td>
                      <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                      <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                      <td>
                        <?php
                          $badge = 'secondary';
                          if ($row['status']==='confirmada') $badge='success';
                          elseif ($row['status']==='pendiente') $badge='warning';
                          elseif ($row['status']==='cancelada') $badge='danger';
                        ?>
                        <span class="badge text-bg-<?php echo $badge; ?>">
                          <?php echo ucfirst($row['status']); ?>
                        </span>
                      </td>
                      <td><a class="btn btn-sm btn-soft" href="appointments.php?focus=<?php echo (int)$row['id']; ?>">Abrir</a></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-12 col-xl-4">
          <div class="section-card p-3">
            <h6 class="mb-2">Últimos estudios</h6>
            <?php if (empty($latestFiles)): ?>
              <p class="text-muted">Sin estudios cargados recientemente.</p>
            <?php else: ?>
              <div class="table-responsive" style="max-height:340px; overflow:auto;">
                <table class="table table-sm align-middle">
                  <thead class="table-light">
                    <tr><th>Paciente</th><th>Archivo</th><th>Fecha</th></tr>
                  </thead>
                  <tbody>
                  <?php foreach($latestFiles as $f): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($f['patient_name']); ?></td>
                      <td><a class="btn btn-sm btn-outline" href="../uploads/<?php echo htmlspecialchars($f['file_name']); ?>" target="_blank">Ver</a></td>
                      <td><?php echo htmlspecialchars($f['uploaded_at']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Actividad reciente -->
      <div class="section-card p-3 mt-3">
        <h6 class="mb-2">Actividad reciente</h6>
        <?php if (empty($activity)): ?>
          <p class="text-muted">Sin actividad por ahora.</p>
        <?php else: ?>
          <div class="timeline">
            <?php foreach($activity as $ev): ?>
              <?php if(($ev['type'] ?? '') === 'cita'): ?>
                <div class="item">
                  <div class="dot dot-cita"></div>
                  <div>
                    <div class="fw-semibold">Cita #<?php echo (int)$ev['id']; ?> • <?php echo htmlspecialchars($ev['status']); ?></div>
                    <div class="text-muted small">
                      <?php echo htmlspecialchars($ev['patient_name'] ?? ''); ?> con
                      <strong><?php echo htmlspecialchars($ev['doctor_name'] ?? ''); ?></strong>
                      — <?php echo htmlspecialchars($ev['ts'] ?? ''); ?>
                    </div>
                  </div>
                </div>
              <?php else: ?>
                <div class="item">
                  <div class="dot dot-estudio"></div>
                  <div>
                    <div class="fw-semibold">Nuevo estudio subido</div>
                    <div class="text-muted small">
                      <?php echo htmlspecialchars($ev['patient_name'] ?? ''); ?> —
                      <?php echo htmlspecialchars($ev['file_name'] ?? ''); ?> —
                      <?php echo htmlspecialchars($ev['ts'] ?? ''); ?>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
<script>
// Acceso rápido al buscador (Ctrl+/)
document.addEventListener('keydown', (e)=>{
  if((e.ctrlKey || e.metaKey) && e.key === '/'){
    const i = document.querySelector('.search input');
    i?.focus(); i?.select();
  }
});

// Datos PHP → JS
const labels = <?php echo json_encode($labelsHuman, JSON_UNESCAPED_UNICODE); ?>;
const dataAppointments = <?php echo json_encode($seriesAppointments); ?>;
const dataStudies      = <?php echo json_encode($seriesStudies); ?>;
const dataDoctors      = <?php echo json_encode($seriesDoctors); ?>;

const baseOpts = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display:false }, tooltip: { mode:'index', intersect:false } },
  scales: {
    x: { grid: { display:false } },
    y: { beginAtZero:true, ticks: { precision:0 } }
  }
};

// Citas (línea)
new Chart(document.getElementById('chartAppointments'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      data: dataAppointments,
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59,130,246,.15)',
      borderWidth: 2,
      tension: .35,
      fill: true,
      pointRadius: 3
    }]
  },
  options: baseOpts
});

// Estudios (barra)
new Chart(document.getElementById('chartStudies'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      data: dataStudies,
      backgroundColor: 'rgba(14,165,233,.35)',
      borderColor: '#0ea5e9',
      borderWidth: 1.5,
      borderRadius: 6
    }]
  },
  options: baseOpts
});

// Médicos (línea punteada)
new Chart(document.getElementById('chartDoctors'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      data: dataDoctors,
      borderColor: '#06b6d4',
      backgroundColor: 'rgba(6,182,212,.15)',
      borderWidth: 2,
      borderDash: [4,4],
      tension: .35,
      fill: true,
      pointRadius: 3
    }]
  },
  options: baseOpts
});
</script>

<?php include("../includes/footer.php"); ?>
