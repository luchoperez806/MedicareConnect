<?php
session_start();
require_once("../includes/db.php");

// Solo admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

$q = trim($_GET['q'] ?? '');
$filter = trim($_GET['filter'] ?? '');

$params = [];
$where = "WHERE 1=1";
if ($q !== '') {
    $where .= " AND (u.fullName LIKE :q OR d.specialization LIKE :q)";
    $params[':q'] = "%$q%";
}
if ($filter === 'active') $where .= " AND d.working_days <> ''";

$sql = "
    SELECT d.id, u.fullName, d.office_address, d.working_days, d.working_hours,
           d.consultation_fee, d.profile_pic, d.specialization, u.email
    FROM doctors d
    JOIN users u ON d.user_id = u.id
    $where
    ORDER BY u.fullName ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Médicos | Panel Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #e3f2fd, #f9fbff);
    font-family: 'Poppins', sans-serif;
}
.container { max-width: 1200px; margin-top: 30px; }

.header-bar {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 20px; flex-wrap: wrap; gap: 10px;
}
h1 { font-weight: 800; color: #1e3a8a; }

.btn-soft {
    background: linear-gradient(90deg, #0ea5e9, #3b82f6);
    color: #fff; border: none; border-radius: 10px;
    padding: 8px 16px; font-weight: 600;
    box-shadow: 0 4px 10px rgba(59,130,246,0.25);
}
.btn-soft:hover { filter: brightness(1.1); transform: translateY(-2px); }

.card {
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    border: 1px solid #e0e7ff;
    transition: transform 0.25s ease;
}
.card:hover { transform: translateY(-3px); }

.doctor-img {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; border: 3px solid #3b82f6;
}
.specialization {
    font-weight: 600; color: #2563eb; font-size: 0.95rem;
}
.fee {
    font-weight: 700; color: #0f172a;
}
.text-muted-small {
    color: #64748b; font-size: 0.85rem;
}
</style>
</head>

<body>
<div class="container">
    <div class="header-bar">
        <h1>Médicos Registrados</h1>
        <div class="d-flex flex-wrap gap-2">
            <form class="d-flex" method="get" role="search">
                <input class="form-control me-2" type="text" name="q" placeholder="Buscar por nombre o especialidad" value="<?php echo htmlspecialchars($q); ?>">
                <button class="btn btn-primary">Buscar</button>
            </form>
            <a href="register-doctor.php" class="btn-soft">➕ Nuevo médico</a>
            <a href="dashboard.php" class="btn btn-outline-secondary">← Volver</a>
        </div>
    </div>

    <?php if (empty($doctors)): ?>
        <div class="alert alert-info text-center">No se encontraron médicos registrados.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($doctors as $doc):
                $photo = !empty($doc['profile_pic']) ? "../uploads/" . htmlspecialchars($doc['profile_pic']) : "../assets/images/default.png";
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card p-3 text-center">
                    <img src="<?php echo $photo; ?>" alt="Foto del médico" class="doctor-img mx-auto mb-2" onerror="this.src='../assets/images/default.png'">
                    <h5 class="mb-1"><?php echo htmlspecialchars($doc['fullName']); ?></h5>
                    <div class="specialization"><?php echo htmlspecialchars($doc['specialization']); ?></div>
                    <div class="text-muted-small mb-2"><?php echo htmlspecialchars($doc['office_address']); ?></div>
                    <div class="text-muted-small">🗓️ <?php echo htmlspecialchars($doc['working_days']); ?></div>
                    <div class="text-muted-small">⏰ <?php echo htmlspecialchars($doc['working_hours']); ?></div>
                    <div class="fee mt-2">💰 $<?php echo number_format($doc['consultation_fee'], 2); ?></div>
                    <div class="mt-3 d-flex justify-content-center gap-2">
                        <a href="editar-doctor.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-success">✏️ Editar</a>
                        <a href="eliminar-doctor.php?id=<?php echo $doc['id']; ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('¿Seguro que deseas eliminar a <?php echo htmlspecialchars($doc['fullName']); ?>?')">🗑️</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
