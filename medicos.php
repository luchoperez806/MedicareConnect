<?php include("includes/header.php"); ?>
<link rel="stylesheet" href="assets/css/sections.css">

<header class="section-hero" style="background: linear-gradient(135deg,#f8fafc,#eef2ff);">
    <div class="section-wrap text-center">
        <div class="kicker" style="color:#2563eb;">Profesionales</div>
        <h1 style="color:#1e293b;">Médicos registrados</h1>
        <p class="lead" style="color:#475569;">Conocé a los profesionales y ubicá su consultorio.</p>
    </div>
</header>

<main class="main-content">
    <div class="section-wrap">
        <div style="display:flex; justify-content:center; gap:12px; margin-bottom:16px;">
        <label for="filterSpec" style="align-self:center; color:#334155;">Filtrar por especialidad</label>
        <select id="filterSpec" style="padding:8px 12px; border-radius:8px; border:1px solid #cbd5e1;">
            <option value="">Todas</option>
        </select>
        </div>

        <div class="doctors-grid" id="doctorsGrid">
        <?php
        include('includes/db.php');
        $stmt = $pdo->query("SELECT d.id, u.fullName, d.office_address, d.specialization, d.profile_pic FROM doctors d JOIN users u ON d.user_id = u.id ORDER BY d.id DESC");
        $doctors = $stmt->fetchAll();
        if(count($doctors) === 0){
            echo '<div class="card">No hay médicos registrados aún.</div>';
        } else {
            foreach($doctors as $d){
            $photo = !empty($d['profile_pic']) ? 'uploads/'.$d['profile_pic'] : 'assets/images/default.png';
            $addr = htmlspecialchars($d['office_address'] ?: 'Dirección no disponible');
            $maps = 'https://www.google.com/maps/search/?api=1&query='.urlencode($addr);
            echo '<div class="doctor-card reveal card">
                    <img src="'.$photo.'" class="doctor-img">
                    <h3 style="color:#1e293b;">'.htmlspecialchars($d['fullName']).'</h3>
                    <p style="color:#475569;">'.htmlspecialchars($d['specialization']).'</p>
                    <p style="color:#64748b;">'.$addr.'</p>
                    <a href="'.$maps.'" target="_blank" class="btn btn-primary">📍 Ver en Maps</a>
                    </div>';
            }
        }
        ?>
        </div>
    </div>
</main>

<?php include("includes/footer.php"); ?>

<style>
.doctors-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; margin-top:20px; }
.doctor-card img.doctor-img { border-radius:50%; border:3px solid #3b82f6; width:120px; height:120px; object-fit:cover; margin-bottom:10px; }
.card { background:#fff; border-radius:14px; text-align:center; box-shadow:0 6px 20px rgba(0,0,0,0.05); padding:20px; transition:.3s; }
.card:hover { transform:translateY(-4px); box-shadow:0 12px 25px rgba(0,0,0,0.08); }
.btn-primary { background:linear-gradient(90deg,#2563eb,#4f46e5); color:white; border:none; padding:8px 16px; border-radius:10px; text-decoration:none; font-weight:600; }
.reveal { opacity:0; transform:translateY(30px); transition:all .6s ease; }
.reveal.visible { opacity:1; transform:translateY(0); }
</style>
<script>
(function(){
    const cards = Array.from(document.querySelectorAll('.doctor-card'));
    const sel = document.getElementById('filterSpec');
    const specs = new Set();
    cards.forEach(c=>{
        const s=c.querySelector('p')?.innerText?.trim();
        if(s) specs.add(s);
    });
    specs.forEach(s=>{
        const opt=document.createElement('option'); opt.value=s; opt.textContent=s; sel.appendChild(opt);
    });
    sel.addEventListener('change',()=>{
        const val=sel.value;
        cards.forEach(c=>{
        c.style.display = !val || c.querySelector('p').innerText.trim()===val ? '' : 'none';
        });
    });
    const reveals=document.querySelectorAll('.reveal');
    const show=()=>{reveals.forEach(r=>{if(r.getBoundingClientRect().top<innerHeight-60)r.classList.add('visible')})};
    window.addEventListener('scroll',show); show();
})();
</script>
