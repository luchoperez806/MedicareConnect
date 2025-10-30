<?php include("includes/header.php"); ?>
<link rel="stylesheet" href="assets/css/sections.css">

<header class="section-hero" style="background: linear-gradient(135deg,#f8fafc,#eef2ff);">
    <div class="section-wrap text-center">
        <div class="kicker" style="color:#2563eb;">Voces reales</div>
        <h1 style="color:#1e293b;">Comentarios y testimonios</h1>
        <p class="lead" style="color:#475569;">Lo que dicen nuestros usuarios sobre la experiencia con MedicareConnect.</p>
    </div>
</header>

<main class="main-content">
    <div class="section-wrap">
        <div class="testimonials-grid" id="testimonials">
        <?php
        if (file_exists('includes/db.php')) {
            include('includes/db.php');
            try {
            $st = $pdo->query("SELECT author, message, created_at FROM comments ORDER BY created_at DESC LIMIT 12");
            $rows = $st->fetchAll();
            foreach($rows as $r){
                echo '<div class="testimonial card reveal"><p>'.htmlspecialchars($r['message']).'</p><div class="who">— '.htmlspecialchars($r['author']).'</div></div>';
            }
            } catch (Exception $e) {
            echo '<div class="testimonial card reveal"><p>Excelente plataforma, muy fácil de usar.</p><div class="who">— Juan P.</div></div>';
            echo '<div class="testimonial card reveal"><p>Ágil y confiable.</p><div class="who">— María G.</div></div>';
            }
        } else {
            echo '<div class="testimonial card reveal"><p>Excelente plataforma, muy fácil de usar.</p><div class="who">— Juan P.</div></div>';
            echo '<div class="testimonial card reveal"><p>Ágil y confiable.</p><div class="who">— María G.</div></div>';
        }
        ?>
        </div>

        <div style="margin-top:22px;" class="reveal">
        <div class="card" style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
            <div><strong>¿Querés dejar tu comentario?</strong><p style="color:#64748b;">Pronto habilitaremos un formulario público. Por ahora podés enviar tu opinión al mail.</p></div>
            <a class="btn btn-primary" href="mailto:medicareconnectmc@gmail.com">Enviar comentario</a>
        </div>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>

<style>
.card { background:#fff; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.05); padding:20px; transition:.3s; }
.card:hover { transform:translateY(-4px); box-shadow:0 12px 25px rgba(0,0,0,0.08); }
.testimonials-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px; }
.who { color:#64748b; margin-top:8px; font-style:italic; }
.btn-primary { background:linear-gradient(90deg,#2563eb,#4f46e5); color:white; border:none; padding:10px 20px; border-radius:10px; text-decoration:none; font-weight:600; }
.reveal { opacity:0; transform:translateY(30px); transition:all .6s ease; }
.reveal.visible { opacity:1; transform:translateY(0); }
</style>
<script>
const reveals=document.querySelectorAll('.reveal');
const scrollFx=()=>{reveals.forEach(r=>{if(r.getBoundingClientRect().top<innerHeight-60)r.classList.add('visible')})};
window.addEventListener('scroll',scrollFx);scrollFx();
</script>
