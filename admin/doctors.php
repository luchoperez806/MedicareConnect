<?php
include("../includes/db.php");
include("../includes/admin_header.php");

$stmt = $pdo->query("SELECT * FROM doctors ORDER BY id DESC");
$doctors = $stmt->fetchAll();
?>

<h2>Listado de Médicos</h2>
<table border="1" cellpadding="8" cellspacing="0" style="width:100%; background:#fff;">
    <tr>
        <th>Foto</th>
        <th>Nombre</th>
        <th>Especialidad</th>
        <th>Consultorio</th>
        <th>Días</th>
        <th>Horarios</th>
        <th>Honorarios</th>
    </tr>
    <?php foreach ($doctors as $doc): ?>
    <tr>
        <td><img src="../uploads/<?php echo $doc['profile_pic'] ?: 'default.png'; ?>" width="50"></td>
        <td><?php echo $doc['doctorname']; ?></td>
        <td><?php echo $doc['specialization']; ?></td>
        <td><?php echo $doc['office_address']; ?></td>
        <td><?php echo $doc['working_days']; ?></td>
        <td><?php echo $doc['working_hours']; ?></td>
        <td>$<?php echo number_format($doc['consultation_fee'], 2); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php include("../includes/admin_footer.php"); ?>
