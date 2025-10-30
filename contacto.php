<?php include("includes/header.php"); ?>
<link rel="stylesheet" href="assets/css/sections.css">

<header class="section-hero" style="background: linear-gradient(135deg, #f8fafc, #eef2ff);">
    <div class="section-wrap text-center">
        <div class="kicker" style="color:#2563eb;">Contacto</div>
        <h1 style="color:#1e293b; font-size:2.4rem; margin-top:8px;">Hablemos</h1>
        <p class="lead" style="color:#475569;">Escribinos o envianos un mensaje por WhatsApp. Estamos para asistirte.</p>
    </div>
</header>

<main class="main-content">
    <div class="section-wrap">
        <div class="contact-grid">
        <!-- WhatsApp -->
        <div class="contact-card reveal card" style="text-align:center; padding:30px; border:1px solid #e2e8f0;">
            <div style="font-size:42px; color:#25D366; margin-bottom:12px;">
            <i class="bi bi-whatsapp"></i>
            </div>
            <h3 style="color:#1e293b;">WhatsApp</h3>
            <p style="color:#64748b;">Responderemos en breve.</p>
            <a href="https://wa.me/541168596464" target="_blank" class="btn btn-success" style="margin-top:10px;">Enviar mensaje</a>
        </div>

        <!-- Email -->
        <div class="contact-card reveal card" style="text-align:center; padding:30px; border:1px solid #e2e8f0;">
            <div style="font-size:42px; color:#0ea5e9; margin-bottom:12px;">
            <i class="bi bi-envelope-fill"></i>
            </div>
            <h3 style="color:#1e293b;">Email</h3>
            <p style="color:#64748b;">Consultas generales y soporte.</p>
            <a href="mailto:medicareconnectmc@gmail.com" class="btn btn-primary" style="margin-top:10px;">Enviar correo</a>
        </div>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>

<style>
.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 30px;
    }
    .card {
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    background: #fff;
    }
    .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    .btn {
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    color: white !important;
    }
.btn-primary { background: linear-gradient(90deg,#2563eb,#4f46e5); }
.btn-success { background: linear-gradient(90deg,#10b981,#059669); }
.reveal { opacity: 0; transform: translateY(30px); transition: all .6s ease; }
.reveal.visible { opacity: 1; transform: translateY(0); }
</style>

<script>
const reveals = document.querySelectorAll('.reveal');
const revealScroll = ()=>{ reveals.forEach(r=>{ if(r.getBoundingClientRect().top < innerHeight-60) r.classList.add('visible'); }); };
window.addEventListener('scroll', revealScroll);
revealScroll();
</script>
