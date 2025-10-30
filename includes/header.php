<?php
// Iniciar sesión solo si no fue iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MedicareConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="/Medicareconnect/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f7fb;
            color: #333;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: linear-gradient(135deg, #3f51b5, #1a237e);
            color: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 0 0 15px 15px;
            flex-wrap: wrap;
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        nav {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        nav a {
            padding: 10px 22px;
            background: linear-gradient(135deg, #5c6bc0, #3f51b5);
            color: white;
            text-decoration: none;
            font-weight: 600;
            border-radius: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            text-align: center;
        }

        nav a:hover {
            background: linear-gradient(135deg, #7986cb, #5c6bc0);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.25);
        }

        /* MENU HAMBURGUESA (solo móvil) */
        .menu-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
        }

        .menu-toggle span {
            background: white;
            height: 3px;
            width: 26px;
            margin: 4px 0;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            nav {
                display: none;
                flex-direction: column;
                width: 100%;
                text-align: center;
                margin-top: 10px;
            }

            nav.show {
                display: flex;
            }

            .menu-toggle {
                display: flex;
            }
        }

        main {
            padding: 40px 20px;
            min-height: 80vh;
        }
    </style>
</head>

<body>
<header>
    <h1>MedicareConnect</h1>
    <div class="menu-toggle" onclick="document.querySelector('nav').classList.toggle('show')">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="/Medicareconnect/logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="/Medicareconnect/index.php">Inicio</a>
        <?php endif; ?>
    </nav>
</header>

<main>
