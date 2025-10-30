<?php
session_start();
require_once("../includes/db.php");

// Seguridad admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php?role=admin");
    exit();
}

// Cargar todos los estudios
    $stmt = $pdo->query("
        SELECT s.id, s.file_name, s.uploaded_at, s.doctor_comment,
            u.fullName AS patient_name
        FROM studies s
        JOIN patients p ON s.patient_id = p.id
        JOIN users u ON p.user_id = u.id
        ORDER BY s.uploaded_at DESC
    ");
$studies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../includes/header.php"); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f4f8ff;
    font-family: 'Poppins', sans-serif;
}
.container {
    max-width: 1100px;
    margin-top: 30px;
}
.card {
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border-radius: 15px;
}
.table th {
    background: linear-gradient(90deg, #3b82f6, #06b6d4);
    color: white;
}
.btn-primary {
    background: linear-gradient(90deg,#3b82f6,#06b6d4);
    border: none;
}
.btn-primary:hover {
    filter: brightness(1.1);
}
</style>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold text-primary">📂 Estudios médicos</h2>
        <a href="dashboard.php" class="btn btn-secondary">← Volver al panel</a>
    </div>

    <div class="card p-4">
        <?php if (empty($studies)): ?>
            <p class="text-muted">No se encontraron estudios cargados.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Archivo</th>
                        <th>Fecha</th>
                        <th>Comentario del médico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($studies as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['patient_name']); ?></td>
                        <td>
                            <a href="../uploads/<?php echo htmlspecialchars($s['file_name']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Ver</a>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($s['uploaded_at'])); ?></td>
                        <td><?php echo htmlspecialchars($s['doctor_comment'] ?? '—'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary edit-btn"
                                    data-id="<?php echo $s['id']; ?>"
                                    data-comment="<?php echo htmlspecialchars($s['doctor_comment'] ?? ''); ?>">
                                ✏️ Editar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Editar comentario</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="editForm">
            <input type="hidden" name="id" id="studyId">
            <div class="mb-3">
                <label class="form-label">Comentario del médico</label>
                <textarea name="doctor_comment" id="doctorComment" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
            </form>
        </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('studyId').value = btn.dataset.id;
        document.getElementById('doctorComment').value = btn.dataset.comment;
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
    });
});

document.getElementById('editForm').addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const res = await fetch('update_study_comment.php', { method:'POST', body:formData });
    const data = await res.json();
    if(data.success){
        alert('Comentario actualizado ✅');
        location.reload();
    } else {
        alert('❌ Error al actualizar');
    }
});
</script>

<?php include("../includes/footer.php"); ?>
