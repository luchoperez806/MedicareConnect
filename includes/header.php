<?php
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

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- ===== FIX GLOBAL para que el footer quede abajo ===== -->
    <style>
        html, body {
            height: 100%;
            margin:0;
            padding:0;
        }
        body {
            display:flex;
            flex-direction:column;
            background:#f5f7fb;
            font-family:'Poppins',sans-serif;
        }
        main {
            flex:1;
            padding:28px 16px;
        }
    </style>

    <!-- ===== HEADER ESTÉTICO ===== -->
    <style>
        header{
            width:100%;
            background:#ffffff;
            border-bottom:1px solid #e1e7f0;
            padding:14px 26px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
        }

        .logo{
            font-size:1.35rem;
            font-weight:800;
            color:#0f172a;
        }

        nav{
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }

        nav a{
            background:#ef4444;
            color:#fff !important;
            padding:8px 16px;
            border-radius:8px;
            font-weight:700;
            font-size:.9rem;
            text-decoration:none;
            transition:.2s;
        }

        nav a:hover{
            filter:brightness(1.15);
            transform:translateY(-2px);
        }

        /* RESPONSIVE */
        @media(max-width:600px){
            header{
                flex-direction:column;
                align-items:flex-start;
            }
            nav{
                width:100%;
                justify-content:flex-start;
            }
        }
    </style>

</head>

<body>

<header>
    <div class="logo">MedicareConnect</div>

    <nav>
        <?php if(isset($_SESSION['user'])): ?>
            <a href="/Medicareconnect/logout.php">Cerrar sesión</a>
        <?php endif; ?>
    </nav>
</header>

<main>
