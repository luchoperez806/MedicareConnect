<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: ../index.php");
    exit();
}

require_once "../includes/db.php";
$user = $_SESSION['user'];

// Ahora traemos el nombre del médico desde la tabla `users`
$stmt = $pdo->query("
    SELECT d.id, u.fullName AS doctor_name, d.specialization, d.consultation_fee
    FROM doctors d
    INNER JOIN users u ON d.user_id = u.id
    ORDER BY d.specialization
");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reservar Turno - MediCareConnect</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {font-family:'Poppins',sans-serif;background:#f3f7fa;margin:0;}
header{background:linear-gradient(135deg,#4f6df5,#66a6ff);color:white;padding:15px 30px;display:flex;justify-content:space-between;align-items:center;}
.container{max-width:800px;margin:40px auto;background:white;padding:30px;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.1);}
select,input,button{width:100%;padding:10px;margin-top:10px;border-radius:8px;border:1px solid #ccc;font-size:15px;}
button{background:#4f6df5;color:white;border:none;cursor:pointer;transition:0.3s;}
button:hover{background:#3d56d0;}
label{font-weight:600;margin-top:10px;display:block;}
.slots-wrap{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;}
.slots-wrap button{background:#4CAF50;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;}
.slots-wrap button:hover{background:#45a049;}
.slots-wrap button.selected{background:#2e7d32;}
.info-small{font-size:13px;color:#666;margin-bottom:5px;}
</style>
</head>
<body>
<header>
    <h1>Reservar Turno</h1>
    <a href="dashboard.php" style="color:white;text-decoration:none;">← Volver</a>
</header>

<div class="container">
    <form id="reservaForm" action="reservar_turno_action.php" method="POST">
        <label for="doctor">Médico</label>
        <select name="doctor_id" id="doctor" required>
            <option value="">Seleccioná un médico</option>
            <?php foreach ($doctors as $doc): ?>
                <option value="<?= $doc['id'] ?>">
                    <?= htmlspecialchars($doc['doctor_name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>) - $<?= $doc['consultation_fee'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="fecha">Fecha del turno</label>
        <input type="date" id="fecha" name="fecha" required min="<?= date('Y-m-d') ?>">

        <!-- Horarios disponibles -->
        <div id="slotsSection" style="display:none;">
            <label>Horarios disponibles</label>
            <div class="info-small">Seleccioná una franja horaria (en verde).</div>
            <div id="slots" class="slots-wrap"></div>
            <input type="hidden" name="hora" id="horaSeleccionada" required>
        </div>

        <button class="primary" type="submit">Confirmar Turno</button>
    </form>
</div>

<!-- Script para cargar los horarios -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  const doctorSelect = document.getElementById("doctor");
  const fechaInput = document.getElementById("fecha");
  const slotsSection = document.getElementById("slotsSection");
  const slotsDiv = document.getElementById("slots");
  const horaSeleccionada = document.getElementById("horaSeleccionada");

  function cargarHorarios() {
    const doctorId = doctorSelect.value;
    const fecha = fechaInput.value;

    if (!doctorId || !fecha) return;

    fetch(`get_horarios.php?doctor_id=${doctorId}&fecha=${fecha}`)
      .then(res => res.json())
      .then(data => {
        slotsDiv.innerHTML = "";
        if (data.status === "ok" && data.horarios.length > 0) {
          slotsSection.style.display = "block";
          data.horarios.forEach(hora => {
            const btn = document.createElement("button");
            btn.textContent = hora;
            btn.type = "button";
            btn.onclick = () => {
              document.querySelectorAll(".slots-wrap button").forEach(b => b.classList.remove("selected"));
              btn.classList.add("selected");
              horaSeleccionada.value = hora;
            };
            slotsDiv.appendChild(btn);
          });
        } else {
          slotsSection.style.display = "block";
          slotsDiv.innerHTML = `<p style="color:red;">${data.mensaje || 'No hay horarios disponibles.'}</p>`;
        }
      })
      .catch(err => {
        console.error("Error al cargar horarios:", err);
        slotsSection.style.display = "block";
        slotsDiv.innerHTML = `<p style="color:red;">Error al obtener los horarios.</p>`;
      });
  }

  doctorSelect.addEventListener("change", cargarHorarios);
  fechaInput.addEventListener("change", cargarHorarios);
});
</script>

</body>
</html>
