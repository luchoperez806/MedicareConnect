<?php include("includes/header.php"); ?>
<link rel="stylesheet" href="assets/css/sections.css">

<header class="section-hero" style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
    <div class="section-wrap text-center">
        <div class="kicker" style="color:#2563eb;">Quiénes somos</div>
        <h1 style="color:#1e293b; font-size:2.4rem; margin-top:8px;">MedicareConnect</h1>
        <p class="lead" style="color:#475569;">Transformamos la gestión médica: historias clínicas digitalizadas, citas inteligentes y comunicación segura entre paciente y profesional.</p>
    </div>
</header>

<main class="main-content">
    <div class="section-wrap">
        <div class="card reveal" style="padding:28px;">
        <div style="display:flex; gap:20px; flex-wrap:wrap; align-items:center;">
            <div style="flex:1; min-width:260px;">
            <h2 style="color:#1e293b;">Nuestra historia</h2>
            <p style="color:#64748b;">Empezamos con la idea de simplificar el flujo clínico: accesibilidad, seguridad y seguimiento. Cada funcionalidad fue pensada para reducir fricción entre paciente y profesional.</p>
            <ul style="margin-top:12px; color:#475569;">
                <li>Seguridad y privacidad</li>
                <li>Interfaz amigable</li>
                <li>Integración con estudios y consultas</li>
            </ul>
            <a class="btn btn-primary" href="medicos.php" style="margin-top:14px;">Ver profesionales</a>
            </div>
            <div style="flex:1; min-width:260px;">
            <div style="border-radius:14px; overflow:hidden; box-shadow:0 8px 25px rgba(0,0,0,0.08);">
                <img src="assets/images/hero-doctor.jpg" alt="Equipo" style="width:100%; height:260px; object-fit:cover;">
                <div style="padding:14px; background:#fff;">
                <strong style="color:#1e293b;">Visión</strong>
                <p style="color:#475569; margin-top:8px;">Conectar salud y tecnología para mejorar la experiencia clínica.</p>
                </div>
            </div>
            </div>
        </div>
        </div>

        <div style="margin-top:26px;" class="reveal">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px;">
            <div class="card card-accent"><h3>Privacidad</h3><p style="color:#475569;">Cumplimos estándares de manejo de datos.</p></div>
            <div class="card card-accent"><h3>Disponibilidad</h3><p style="color:#475569;">Acceso rápido desde web y mobile.</p></div>
            <div class="card card-accent"><h3>Experiencia</h3><p style="color:#475569;">Interfaz diseñada para profesionales y pacientes.</p></div>
        </div>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>

<style>
.card { background:#fff; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.05); padding:20px; transition:.3s; }
.card:hover { transform:translateY(-4px); box-shadow:0 12px 25px rgba(0,0,0,0.08); }
.reveal { opacity:0; transform:translateY(30px); transition:all .6s ease; }
.reveal.visible { opacity:1; transform:translateY(0); }
</style>
<script>
const reveals = document.querySelectorAll('.reveal');
const revealScroll = ()=>{ reveals.forEach(r=>{ if(r.getBoundingClientRect().top < innerHeight-60) r.classList.add('visible'); }); };
window.addEventListener('scroll', revealScroll); revealScroll();
</script>
