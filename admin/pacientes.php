<?php
session_start();
require_once("../includes/db.php");

// Solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

$q = trim($_GET['q'] ?? '');

// Búsqueda
$params = [];
$where = "";
if ($q !== '') {
    $where = "WHERE (u.fullName LIKE :q OR u.email LIKE :q)";
    $params[':q'] = "%{$q}%";
    }

$sql = "
    SELECT p.id AS patient_id, p.user_id,
            u.fullName, u.email,
            COALESCE(p.phone, '') AS phone,
            COALESCE(p.birthdate, '') AS birthdate,
            COALESCE(u.profile_pic, 'default.png') AS profile_pic
    FROM patients p
    JOIN users u ON p.user_id = u.id
    $where
    ORDER BY u.fullName ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root {
    --c1:#0ea5e9;
    --c2:#6366f1;
    }
    body {
    background: linear-gradient(180deg, #f9fbff, #e8f1ff);
    font-family: 'Poppins', sans-serif;
    }
    .container {
    max-width: 1100px;
    padding-top: 30px;
    padding-bottom: 40px;
    }
    .page-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 20px;
    }
    .page-head h2 {
    margin: 0;
    font-weight: 800;
    color: #1e293b;
    }
    .card-lite {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
    border: 1px solid #e3e8ef;
    }
    .btn-back {
    background: linear-gradient(90deg, var(--c1), var(--c2));
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    transition: .3s;
    }
    .btn-back:hover {
    filter: brightness(1.1);
    transform: translateY(-2px);
    }
    .table thead {
    background: linear-gradient(90deg,#3b82f6,#06b6d4);
    color: #fff;
    }
    .table th, .table td {
    vertical-align: middle;
    }
    .avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    }
    .badge-soft {
    background: linear-gradient(90deg, rgba(14,165,233,.12), rgba(99,102,241,.12));
    color: #0f172a;
    border: 1px solid rgba(14,165,233,.25);
    font-size: 0.75rem;
}
</style>

<div class="container">
    <div class="page-head">
        <div>
        <h2>👥 Pacientes</h2>
        <div class="text-muted">Listado general y búsqueda avanzada</div>
        </div>
        <a href="dashboard.php" class="btn btn-sm btn-back">← Volver al Panel</a>
    </div>

    <div class="card-lite p-3 mb-3">
        <form class="row g-2 align-items-center" method="get">
        <div class="col-md-9 col-sm-8">
            <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Buscar por nombre o correo...">
        </div>
        <div class="col-md-3 col-sm-4 d-grid">
            <button class="btn btn-primary" style="background:linear-gradient(90deg,#0ea5e9,#6366f1); border:none;">🔍 Buscar</button>
        </div>
        </form>
    </div>

    <div class="card-lite p-0">
        <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>Paciente</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Nacimiento</th>
                <th class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron pacientes registrados.</td></tr>
            <?php else: foreach($rows as $r):
                $pic = !empty($r['profile_pic']) ? "../uploads/".htmlspecialchars($r['profile_pic']) : "../assets/images/default.png";
            ?>
            <tr>
                <td>
                <div class="d-flex align-items-center gap-2">
                    <img src="<?php echo $pic; ?>" alt="Foto" class="avatar" onerror="this.src='../assets/images/default.png'">
                    <div>
                    <div class="fw-semibold"><?php echo htmlspecialchars($r['fullName']); ?></div>
                    <div><span class="badge badge-soft">ID <?php echo (int)$r['patient_id']; ?></span></div>
                    </div>
                </div>
                </td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['phone']); ?></td>
                <td><?php echo htmlspecialchars($r['birthdate']); ?></td>
                <td class="text-end">
                <div class="btn-group">
                    <a class="btn btn-sm btn-outline-primary" href="appointments.php?patient_id=<?php echo (int)$r['patient_id']; ?>">Citas</a>
                    <a class="btn btn-sm btn-outline-secondary" href="estudios.php?patient_id=<?php echo (int)$r['patient_id']; ?>">Estudios</a>
                    <a class="btn btn-sm btn-outline-success" href="../patient/export_history.php?patient_id=<?php echo (int)$r['patient_id']; ?>">Historia Clínica</a>
                </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
