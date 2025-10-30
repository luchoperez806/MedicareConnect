<?php
session_start();
require_once("../includes/db.php");

// Solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header("Location: ../login.php?role=admin");
  exit();
}

// Filtrado por búsqueda (opcional)
$q = trim($_GET['q'] ?? '');
$params = [];
$where = "";
if ($q !== '') {
  $where = "WHERE (du.fullName LIKE :q OR pu.fullName LIKE :q)";
  $params[':q'] = "%{$q}%";
}

// Consultar citas
$sql = "
  SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.video_call,
         du.fullName AS doctor_name, pu.fullName AS patient_name
  FROM appointments a
  JOIN doctors d ON a.doctor_id = d.id
  JOIN users du ON d.user_id = du.id
  JOIN patients p ON a.patient_id = p.id
  JOIN users pu ON p.user_id = pu.id
  $where
  ORDER BY a.appointment_date DESC, a.appointment_time DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
  --bg:#f4f7fb;
  --card:#ffffff;
  --muted:#6b7280;
  --c1:#0ea5e9;
  --c2:#6366f1;
}
body{ background:var(--bg); font-family:'Poppins',sans-serif; }
.page-head{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px;}
.page-head h2{margin:0; font-weight:800; color:#0f172a;}
.btn-back{background:linear-gradient(90deg,#06b6d4,#3b82f6); color:#fff; border:none;}
.card-lite{ background:var(--card); border:1px solid #e6edf7; border-radius:14px; box-shadow:0 10px 24px rgba(2,6,23,.06); }
.table thead th{ color:#334155; font-weight:700; }
.badge-soft{ background:linear-gradient(90deg,rgba(14,165,233,.12),rgba(99,102,241,.12)); color:#0f172a; border:1px solid rgba(14,165,233,.25); }
.status-badge{border-radius:999px;padding:6px 10px;font-weight:700;font-size:.8rem;}
.status-pendiente{background:rgba(250,204,21,.15);color:#92400e;}
.status-confirmada{background:rgba(34,197,94,.15);color:#065f46;}
.status-cancelada{background:rgba(239,68,68,.15);color:#991b1b;}
.search-wrap{ display:flex; gap:10px; flex-wrap:wrap; }
</style>

<div class="container py-4">
  <div class="page-head">
    <div>
      <h2>Citas</h2>
      <div class="text-muted">Gestión de turnos y reservas</div>
    </div>
    <a href="dashboard.php" class="btn btn-sm btn-back">← Volver al Panel</a>
  </div>

  <div class="card-lite p-3 mb-3">
    <form class="row g-2 align-items-center" method="get">
      <div class="col-sm-8 col-md-9">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control"
               placeholder="Buscar por paciente o médico">
      </div>
      <div class="col-sm-4 col-md-3 d-grid">
        <button class="btn btn-primary" style="background:linear-gradient(90deg,#0ea5e9,#6366f1); border:none;">Buscar</button>
      </div>
    </form>
  </div>

  <div class="card-lite p-0">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Paciente</th>
            <th>Médico</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Estado</th>
            <th>Teleconsulta</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No hay citas registradas.</td></tr>
        <?php else: foreach($rows as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
            <td><?php echo htmlspecialchars($r['doctor_name']); ?></td>
            <td><?php echo htmlspecialchars($r['appointment_date']); ?></td>
            <td><?php echo htmlspecialchars(substr($r['appointment_time'],0,5)); ?></td>
            <td><span class="status-badge status-<?php echo htmlspecialchars($r['status']); ?>"><?php echo ucfirst($r['status']); ?></span></td>
            <td><?php echo $r['video_call'] ? '✅ Sí' : '❌ No'; ?></td>
            <td class="text-end">
              <?php if ($r['status'] === 'pendiente'): ?>
                <div class="btn-group">
                  <form method="post" action="update_status.php" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="status" value="confirmada">
                    <button class="btn btn-sm btn-success">Confirmar</button>
                  </form>
                  <form method="post" action="update_status.php" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input type="hidden" name="status" value="cancelada">
                    <button class="btn btn-sm btn-danger">Cancelar</button>
                  </form>
                </div>
              <?php else: ?>
                <span class="text-muted small">Sin acciones</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include("../includes/footer.php"); ?>
