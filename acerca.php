<?php include("includes/header.php"); ?>
<link rel="stylesheet" href="assets/css/sections.css">

<header class="section-hero" style="background: linear-gradient(135deg,#ffffff,#f3f7fb); color:#0f172a;">
    <div class="section-wrap text-center">
        <div class="kicker">Conocé más</div>
        <h1 style="margin-top:6px;">Acerca de nuestro proyecto</h1>
        <p class="lead" style="max-width:700px;margin:auto;color:#4b5563;">
        Un vistazo en video que presenta la propuesta de valor de <strong>MedicareConnect</strong>.
        </p>
    </div>
</header>

<main class="main-content">
    <div class="section-wrap">

    <!-- VIDEO PRINCIPAL -->
    <div class="video-frame reveal card" style="
        border-radius:14px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(15,23,42,0.08);
        background:white;
        padding:0;">
        <video controls preload="metadata" style="display:block;width:100%;height:auto;">
            <source src="video/CONEXION-COMERCIAL.mp4" type="video/mp4">
            Tu navegador no soporta el video.
        </video>
    </div>

    <!-- TARJETA DE ENFOQUE -->
    <div style="margin-top:30px;" class="reveal">
        <div class="card" style="
            background:white;
            border-radius:14px;
            padding:24px;
            box-shadow:0 6px 25px rgba(0,0,0,0.06);
            text-align:left;">
            <h3 style="color:#1e3a8a;">Nuestro enfoque</h3>
            <p style="color:#4b5563; line-height:1.6; margin-top:10px;">
            Diseñamos herramientas digitales que conectan la atención médica con la tecnología,
            asegurando comunicación efectiva, privacidad de datos y trazabilidad completa en cada consulta.
            </p>
        </div>
        </div>

        <!-- BLOQUE DE PUNTOS CLAVE -->
        <div class="reveal" style="margin-top:30px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px;">
            <div class="card" style="background:#f9fafb; border-radius:12px; padding:20px;">
            <h4 style="color:#1e3a8a;">🔒 Seguridad</h4>
            <p style="color:#4b5563;">Cumplimos con protocolos de cifrado y protección de datos médicos.</p>
            </div>
            <div class="card" style="background:#f9fafb; border-radius:12px; padding:20px;">
            <h4 style="color:#1e3a8a;">📱 Accesibilidad</h4>
            <p style="color:#4b5563;">Disponible desde cualquier dispositivo, en todo momento.</p>
            </div>
            <div class="card" style="background:#f9fafb; border-radius:12px; padding:20px;">
            <h4 style="color:#1e3a8a;">🌐 Innovación</h4>
            <p style="color:#4b5563;">Integramos IA y experiencia de usuario para transformar la gestión clínica.</p>
            </div>
        </div>
        </div>

    </div>
</main>

<?php include("includes/footer.php"); ?>

<script>
// efecto reveal
const reveals = document.querySelectorAll('.reveal');
const scrollReveal = () => {
    reveals.forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight - 60) el.classList.add('visible');
    });
};
window.addEventListener('scroll', scrollReveal);
scrollReveal();
</script>

<style>
.section-hero {
    text-align:center;
    padding:80px 20px 60px;
    position:relative;
    overflow:hidden;
}
.section-hero .kicker {
    color:#2563eb;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:1px;
}
.section-hero h1 {
    font-size:2.4rem;
    font-weight:700;
    color:#0f172a;
}
.section-hero .lead {
    font-size:1.1rem;
}

/* efecto reveal */
.reveal { opacity:0; transform:translateY(30px); transition:all .6s ease; }
.reveal.visible { opacity:1; transform:none; }

/* video */
.video-frame video {
    border-radius:12px;
}

/* cards */
.card { transition:transform .3s ease, box-shadow .3s ease; }
.card:hover { transform:translateY(-4px); box-shadow:0 10px 25px rgba(0,0,0,0.08); }

/* responsive */
@media (max-width:768px) {
    .section-hero h1 { font-size:2rem; }
    .section-hero { padding:60px 16px; }
    }
</style>
