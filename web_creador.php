<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diseños Web Profesionales | Imagine Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat&display=swap" rel="stylesheet">
    <style>
        :root {
        --color-principal: #1e1e2f;
        --color-secundario: #2e2f4f;
        --color-acento: #ff784f;
        --color-texto: #eaeaea;
        --color-fondo: #0f101a;
        --resplandor: #ff784f88;
        }

        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: 'Montserrat', sans-serif;
        background: radial-gradient(ellipse at top left, #151522, #0f101a);
        color: var(--color-texto);
        overflow-x: hidden;
        }

        header {
        padding: 4rem 1rem;
        text-align: center;
        background: linear-gradient(120deg, var(--color-principal), var(--color-secundario));
        color: var(--color-texto);
        box-shadow: 0 0 40px var(--resplandor);
        position: relative;
        animation: fadeIn 1.5s ease-out;
        }

        header h1 {
        font-family: 'Playfair Display', serif;
        font-size: 3.2rem;
        letter-spacing: 1px;
        text-shadow: 0 0 10px var(--resplandor);
        animation: pulse 3s infinite ease-in-out;
        }

        header p {
        font-size: 1.3rem;
        margin-top: 1rem;
        opacity: 0.85;
        }

        .contenido {
        max-width: 1100px;
        margin: 3rem auto;
        padding: 2rem;
        background: #1a1b2e;
        border-radius: 15px;
        box-shadow: 0 0 25px rgba(255, 120, 79, 0.15);
        animation: fadeInUp 1s ease-out;
        }

        .contenido h2 {
        color: var(--color-acento);
        font-size: 1.9rem;
        margin-bottom: 1rem;
        }

        .contenido p, .contenido ul {
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 1rem;
        }

        .contenido ul li {
        margin-bottom: 0.6rem;
        position: relative;
        padding-left: 1.5rem;
        }

        .contenido ul li::before {
        content: "★";
        position: absolute;
        left: 0;
        color: var(--color-acento);
        }

        .galeria {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
        }

        .galeria img {
        width: 100%;
        border-radius: 12px;
        transition: all 0.4s ease;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        filter: brightness(0.85) saturate(1.2);
        }

        .galeria img:hover {
        transform: scale(1.04);
        filter: brightness(1.1) saturate(1.6);
        box-shadow: 0 15px 30px rgba(255, 120, 79, 0.4);
        }

        .cta {
        margin-top: 3rem;
        text-align: center;
        }

        .cta a {
        display: inline-block;
        background: var(--color-acento);
        color: white;
        text-decoration: none;
        font-weight: bold;
        padding: 1rem 2.2rem;
        border-radius: 50px;
        box-shadow: 0 0 20px var(--resplandor);
        transition: all 0.3s ease;
        }

        .cta a:hover {
        background: #ff5f33;
        transform: scale(1.05);
        box-shadow: 0 0 30px #ff5f3380;
        }

        footer {
        margin-top: 4rem;
        text-align: center;
        font-size: 0.9rem;
        color: #888;
        padding: 2rem 1rem;
        }

        .firma {
        position: fixed;
        bottom: 20px;
        left: 20px;
        background: var(--color-acento);
        color: white;
        padding: 0.7rem 1.2rem;
        border-radius: 25px;
        font-weight: bold;
        box-shadow: 0 0 15px var(--resplandor);
        cursor: pointer;
        transition: transform 0.3s, background 0.3s;
        }

        .firma:hover {
        transform: translateY(-3px);
        background: #ff5f33;
        }

        /* Botón flotante de WhatsApp */
        .whatsapp {
        position: fixed;
        bottom: 90px; /* lo subimos para no chocar con la firma */
        right: 20px;
        z-index: 999;
        background-color: #25D366; /* verde oficial WhatsApp */
        border-radius: 50%;
        padding: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .whatsapp img {
        width: 50px;
        height: 50px;
        display: block;
        }

        .whatsapp:hover {
        transform: scale(1.1);
        box-shadow: 0 0 25px rgba(37, 211, 102, 0.8);
        }

        /* Tooltip personalizado */
        .whatsapp::after {
        content: "Escribime por WhatsApp";
        position: absolute;
        bottom: 110%;
        right: 50%;
        transform: translateX(50%);
        background: #25D366;
        color: white;
        padding: 6px 10px;
        border-radius: 5px;
        font-size: 0.9rem;
        opacity: 0;
        white-space: nowrap;
        pointer-events: none;
        transition: opacity 0.3s ease;
        box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }

        .whatsapp:hover::after {
        opacity: 1;
        }

        @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
        0% { text-shadow: 0 0 10px var(--resplandor); }
        50% { text-shadow: 0 0 20px var(--color-acento); }
        100% { text-shadow: 0 0 10px var(--resplandor); }
        }
    </style>
    </head>
    <body>

    <header>
        <h1>Diseños Web Profesionales</h1>
        <p>Soluciones digitales impactantes y funcionales</p>
    </header>

    <div class="contenido">
        <h2>Diseño Personalizado</h2>
        <p>Desarrollo sitios web únicos que combinan estética moderna, efectos visuales envolventes y funcionalidad profesional para destacar tu marca y conectar con tu audiencia.</p>

        <h2>¿Por qué trabajar conmigo?</h2>
        <ul>
        <li>Diseño actual, elegante y con identidad visual fuerte.</li>
        <li>Optimización para dispositivos móviles y desktop.</li>
        <li>Efectos visuales de luz, hover y movimiento sutil.</li>
        <li>Interfaz intuitiva centrada en la experiencia del usuario.</li>
        <li>Soporte directo y enfoque humano desde el primer contacto.</li>
        </ul>

        <div class="galeria">
        <img src="assets/images/diseño1.jpg" alt="Diseño 1">
        <img src="assets/images/diseño2.jpg" alt="Diseño 2">
        <img src="assets/images/efecto-web.jpg" alt="Diseño 3">
        </div>

        <div class="cta">
        <a href="mailto:luchoperez806@gmail.com">Solicitá tu sitio web</a>
        </div>
    </div>

    <footer>
        &copy; <?php echo date("Y"); ?> Imagine Studio | Todos los derechos reservados
    </footer>

    <div class="firma" onclick="window.location.href='https://medicareconnect.site'">
        Sitio creado por Eduardo Perez
    </div>

    <!-- Botón flotante WhatsApp -->
    <a href="https://wa.me/5491168596464" class="whatsapp" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
    </a>

    </body>
</html>
