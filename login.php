<?php
// Obtener el rol desde la URL
$role = $_GET['role'] ?? '';

// Determinar título y acción según el rol
switch ($role) {
  case 'admin':
    $title = 'Ingreso Admin';
    $action = 'login_action.php?role=admin';
    break;
  case 'doctor':
    $title = 'Ingreso Médico';
    $action = 'login_action.php?role=doctor';
    break;
  case 'paciente':
    $title = 'Ingreso Paciente';
    $action = 'login_action.php?role=paciente';
    break;
  default:
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= htmlspecialchars($title) ?> | MediCareConnect</title>
<style>
  * { box-sizing: border-box; }
  body{
    margin:0; height:100vh; display:flex; justify-content:center; align-items:center;
    background: radial-gradient(circle at 30% 30%, #0f172a, #1e293b 60%, #0f172a);
    font-family:'Poppins',sans-serif; color:#fff; overflow:hidden;
  }

  /* Fondo animado (se genera con JS) */
  .background-circles{ position:fixed; inset:0; overflow:hidden; z-index:1; }
  .circle{
    position:absolute; border-radius:50%; background:rgba(255,255,255,.08);
    animation:float 12s infinite ease-in-out alternate;
  }
  @keyframes float{
    from{ transform: translateY(0) scale(1); opacity:.6; }
    to  { transform: translateY(-60px) scale(1.25); opacity:1; }
  }

  .login-box{
    position:relative; z-index:2;
    width:360px; padding:45px 40px; text-align:center;
    background: rgba(255,255,255,.07);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255,255,255,.15);
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.35);
    animation:appear .6s ease-out;
  }
  @keyframes appear{ from{transform:translateY(30px);opacity:0} to{transform:translateY(0);opacity:1} }

  .login-box h2{
    margin:0 0 25px 0; font-size:1.8rem; font-weight:700;
    background: linear-gradient(90deg,#38bdf8,#a5f3fc);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    color: transparent;
    text-align:center;
  }

  form{ display:flex; flex-direction:column; gap:16px; }
  input{
    background: rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.2);
    border-radius:12px;
    padding:12px 15px; color:#fff; font-size:1rem; outline:none; transition:.25s;
  }
  input:focus{ border-color:#38bdf8; box-shadow:0 0 10px rgba(56,189,248,.4); }

  button{
    background: linear-gradient(90deg,#3b82f6,#06b6d4);
    border:none; color:#fff; border-radius:12px; padding:12px;
    font-size:1rem; font-weight:600; cursor:pointer; transition:.3s;
  }
  button:hover{
    background: linear-gradient(90deg,#2563eb,#0891b2);
    transform:scale(1.03);
    box-shadow:0 0 18px rgba(56,189,248,.35);
  }

  a{ display:inline-block; margin-top:15px; color:#a5f3fc; text-decoration:none; font-weight:500; transition:.3s; }
  a:hover{ color:#fff; text-decoration:underline; }

  .register-link{ margin-top:25px; font-size:.9rem; color:#cbd5e1; }
  .register-link a{ color:#38bdf8; font-weight:600; }
  .register-link a:hover{ color:#fff; }
</style>
</head>
<body>

<!-- Fondo animado (se llena con JS, sin PHP inline) -->
<div class="background-circles" id="bg"></div>

<div class="login-box">
  <h2><?= htmlspecialchars($title) ?></h2>

  <form action="<?= htmlspecialchars($action) ?>" method="POST" novalidate>
    <input type="email" name="email" placeholder="Correo electrónico" required />
    <input type="password" name="password" placeholder="Contraseña" required />
    <button type="submit">Ingresar</button>
  </form>

  <?php if ($role === 'paciente'): ?>
    <div class="register-link">¿No tenés cuenta? <a href="register.php">Registrate aquí</a></div>
  <?php endif; ?>

  <a href="index.php">← Volver al inicio</a>
</div>

<script>
  // Genero 15 círculos aleatorios con JS (evita errores de PHP en estilos inline)
  const bg = document.getElementById('bg');
  const n = 15;
  for (let i=0; i<n; i++){
    const c = document.createElement('div');
    c.className = 'circle';
    const size = Math.floor(Math.random() * (100-30+1)) + 30;   // 30-100 px
    c.style.width  = size + 'px';
    c.style.height = size + 'px';
    c.style.left   = Math.floor(Math.random()*101) + '%';
    c.style.top    = Math.floor(Math.random()*101) + '%';
    c.style.animationDelay = (Math.random()*1).toFixed(2) + 's';
    bg.appendChild(c);
  }
</script>
</body>
</html>
