<?php
// test_accounts.php
// Archivo visual para no olvidar las credenciales de prueba y facilitar el ingreso al login del doctor.
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Credenciales de prueba - MedicareConnect</title>
    <style>
        body{font-family:Inter,Arial; background:#f7f9ff; padding:40px;}
        .card{background:white; padding:20px; border-radius:12px; box-shadow:0 12px 30px rgba(0,0,0,0.06); max-width:800px; margin:20px auto;}
        h1{color:#17327a;}
        .cred{display:flex; gap:12px; align-items:center; justify-content:space-between; margin:12px 0; padding:12px; border-radius:8px; background:#fbfdff; border:1px solid #e6eefc;}
        .cred .left{font-size:15px;}
        .actions{display:flex; gap:8px;}
        button{padding:8px 12px; border-radius:8px; border:none; cursor:pointer; background:#3f51b5; color:white; font-weight:600;}
        button.secondary{background:#4a6578;}
        a.link{color:#1a237e; text-decoration:none; font-weight:600;}
    </style>
</head>
<body>
    <div class="card">
        <h1>Credenciales de prueba</h1>
        <p>Usá estas cuentas para pruebas. Si ya corriste <code>seed_test_users.php</code> los usuarios deberían existir en la BD.</p>

        <div class="cred">
            <div class="left">
                <strong>Administrador</strong><br>
                Email: <code>admin@medicareconnect.site</code><br>
                Contraseña: <code>admin123</code>
            </div>
            <div class="actions">
                <button onclick="copyToClipboard('admin@medicareconnect.site','admin123')">Copiar</button>
                <a class="link" href="/admin/login.php" target="_blank">Abrir login admin</a>
            </div>
        </div>

        <div class="cred">
            <div class="left">
                <strong>Médico</strong><br>
                Email: <code>dr.prueba@medicareconnect.site</code><br>
                Contraseña: <code>doctor123</code>
            </div>
            <div class="actions">
                <button onclick="copyToClipboard('dr.prueba@medicareconnect.site','doctor123')">Copiar</button>
                <a class="link" href="/doctor/login.php" target="_blank">Abrir login doctor</a>
            </div>
        </div>

        <div class="cred">
            <div class="left">
                <strong>Paciente</strong><br>
                Email: <code>paciente.prueba@medicareconnect.site</code><br>
                Contraseña: <code>patient123</code>
            </div>
            <div class="actions">
                <button onclick="copyToClipboard('paciente.prueba@medicareconnect.site','patient123')">Copiar</button>
                <a class="link" href="/patient/login.php" target="_blank">Abrir login paciente</a>
            </div>
        </div>

        <p style="margin-top:18px;">
            Notas:
            <ul>
                <li>Si tu admin login está en otra ruta, corregí el enlace <code>/admin/login.php</code>.</li>
                <li>El botón "Copiar" copia email y contraseña al portapapeles (para pegar en el formulario de login).</li>
            </ul>
        </p>

        <p style="margin-top:10px; font-size:13px; color:#666;">
            Recordá: cuando termines de probar, borrá <code>seed_test_users.php</code> para no dejar credenciales de prueba.
        </p>
    </div>

<script>
function copyToClipboard(email, pass) {
    const text = `Email: ${email}\nContraseña: ${pass}`;
    navigator.clipboard.writeText(text).then(() => {
        alert('Credenciales copiadas al portapapeles. Pegalas en el formulario de login.');
    }, (err) => {
        alert('No se pudo copiar automáticamente. Seleccioná y copiá manualmente:\n\n' + text);
    });
}
</script>
</body>
</html>
