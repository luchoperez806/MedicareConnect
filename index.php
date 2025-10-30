<?php include("includes/header.php"); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MedicareConnect - Inicio</title>

<!-- Bootstrap y fuentes -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #0a1128, #001f54, #034078);
    color: white;
    overflow-x: hidden;
}

/* NAVBAR */
.navbar {
    background: rgba(10,17,40,0.85);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.navbar-brand {
    font-weight: 700;
    font-size: 1.6rem;
    color: #4cc9f0 !important;
}
.navbar-nav .nav-link {
    color: white !important;
    font-weight: 500;
    margin: 0 8px;
    transition: all 0.3s ease;
}
.navbar-nav .nav-link:hover {
    color: #4cc9f0 !important;
    transform: translateY(-2px);
}

/* HERO */
.hero {
    text-align: center;
    padding: 130px 20px 70px;
    background: linear-gradient(160deg, #001233, #003566);
    position: relative;
    overflow: hidden;
}

/* círculo blanco con logo centrado */
.hero .logo-circle {
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: white;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 auto 30px;
    box-shadow: 0 0 50px rgba(76,201,240,0.4);
    animation: glow 4s infinite alternate;
}

.hero .logo-circle img {
    width: 300px;
    height: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.hero .logo-circle:hover img {
    transform: scale(1.05);
}

@keyframes glow {
    from { box-shadow: 0 0 25px rgba(76,201,240,0.3); }
    to { box-shadow: 0 0 55px rgba(37,99,235,0.6); }
}

.hero h1 {
    font-size: 3.4rem;
    font-weight: 700;
    background: linear-gradient(90deg, #4cc9f0 0%, #a1c4fd 100%);
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
    -webkit-text-fill-color: transparent;
    margin-bottom: 15px;
}

.hero p {
    font-size: 1.25rem;
    color: #dbeafe;
}

/* LOGIN SECTION */
.login-section {
    text-align: center;
    padding: 80px 20px;
    background: rgba(255,255,255,0.04);
}
.login-section h2 {
    color: #a1c4fd;
    font-weight: 600;
    margin-bottom: 35px;
}
.login-buttons a {
    display: inline-block;
    padding: 18px 38px;
    border-radius: 12px;
    margin: 10px;
    font-weight: 600;
    font-size: 1.1rem;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.login-buttons a:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
}
.admin-btn { background: linear-gradient(90deg,#4cc9f0,#4895ef); }
.doctor-btn { background: linear-gradient(90deg,#06d6a0,#118ab2); }
.patient-btn { background: linear-gradient(90deg,#ef476f,#f78c6b); }

/* FOOTER */
footer {
    text-align: center;
    padding: 25px;
    color: #adb5bd;
    font-size: 0.9rem;
    background: rgba(0,0,0,0.25);
    margin-top: 40px;
}

/* ANIMATIONS */
.fade-in {
    animation: fadeIn 1s ease forwards;
    opacity: 0;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .hero { padding: 100px 20px 50px; }
    .hero .logo-circle { width: 190px; height: 190px; }
    .hero .logo-circle img { width: 130px; }
    .hero h1 { font-size: 2.5rem; }
    .hero p { font-size: 1.1rem; }
}
@media (max-width: 480px) {
    .hero .logo-circle { width: 160px; height: 160px; }
    .hero .logo-circle img { width: 110px; }
    .hero h1 { font-size: 2rem; }
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">MedicareConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link active" href="#">Inicio</a></li>
            <li class="nav-item"><a class="nav-link" href="nosotros.php">Nosotros</a></li>
            <li class="nav-item"><a class="nav-link" href="comentarios.php">Comentarios</a></li>
            <li class="nav-item"><a class="nav-link" href="medicos.php">Médicos</a></li>
            <li class="nav-item"><a class="nav-link" href="acerca.php">Acerca de</a></li>
            <li class="nav-item"><a class="nav-link" href="contacto.php">Contacto</a></li>
        </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero fade-in">
    <div class="container">
        <div class="logo-circle">
        <img src="assets/images/logoMC.png" alt="Logo MedicareConnect">
        </div>
        <h1>MedicareConnect</h1>
        <p>Tu historia clínica digital, segura y accesible — desde cualquier dispositivo.</p>
    </div>
</section>

<!-- LOGIN -->
<section class="login-section fade-in">
    <h2>Iniciar Sesión</h2>
    <div class="login-buttons">
        <a href="login.php?role=admin" class="admin-btn">Administrador</a>
        <a href="login.php?role=doctor" class="doctor-btn">Médico</a>
        <a href="login.php?role=paciente" class="patient-btn">Paciente</a>
    </div>
    </section>

<footer>
    © <span id="year"></span> MedicareConnect — Innovando en salud digital
</footer>

<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
