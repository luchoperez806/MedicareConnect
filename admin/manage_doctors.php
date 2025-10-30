<?php
// manage_doctors.php
// ==================================
// Listado de médicos registrados
// ==================================

include("includes/config.php");
session_start();

$sql = "SELECT * FROM doctors ORDER BY id DESC";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Médicos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h2>Lista de Médicos Registrados</h2>
    <a href="add_doctor.php">➕ Registrar Nuevo Médico</a>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Foto</th>
            <th>Nombre</th>
            <th>Especialidad</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Honorarios</th>
            <th>Días</th>
            <th>Horarios</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td>
                    <img src="uploads/doctors/<?php echo $row['photo']; ?>"
                            alt="Foto" width="60" height="60"
                            style="border-radius:50%;">
                </td>
                <td><?php echo $row['doctorname']; ?></td>
                <td><?php echo $row['specilization']; ?></td>
                <td><?php echo $row['address']; ?></td>
                <td><?php echo $row['contactno']; ?></td>
                <td><?php echo $row['docemail']; ?></td>
                <td>$<?php echo $row['fees']; ?></td>
                <td><?php echo $row['workingDays']; ?></td>
                <td><?php echo $row['workingHours']; ?></td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
