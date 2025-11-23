<?php
session_start();
require_once "../includes/db.php";

// ===============================
// 1. Validar parámetro
// ===============================
$study_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$study_id) {
    die("ID de estudio no proporcionado.");
}

// ===============================
// 2. Buscar estudio en BD
// ===============================
$stmt = $pdo->prepare("SELECT file_name FROM studies WHERE id = ?");
$stmt->execute([$study_id]);
$study = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$study) {
    die("Estudio no encontrado.");
}

// ===============================
// 3. Ruta del archivo
// ===============================
$filepath = "../uploads/" . $study['file_name'];

if (!file_exists($filepath)) {
    die("El archivo no existe en el servidor: " . htmlspecialchars($filepath));
}

// Detectar extensión
$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
$isDicom = ($ext === "dcm");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Visor de Estudio</title>

<?php if ($isDicom): ?>
    <!-- CORNERSTONE Y DEPENDENCIAS -->
    <script src="../assets/cornerstone/cornerstone.min.js"></script>
    <script src="../assets/cornerstone/cornerstoneWADOImageLoader.min.js"></script>
    <script src="../assets/cornerstone/dicomParser.min.js"></script>
    <script src="../assets/cornerstone/cornerstoneTools.min.js"></script>
    <script src="../assets/cornerstone/hammer.min.js"></script>

    <style>
        body { margin: 20px; font-family: Arial; }
        #viewer {
            width: 100%;
            height: 600px;
            background: black;
            border: 2px solid #333;
        }
        .btns button {
            padding: 8px 14px;
            margin-right: 8px;
            cursor: pointer;
        }
    </style>
<?php endif; ?>

</head>
<body>

<h2>Visualizar Estudio</h2>

<?php if (!$isDicom): ?>

    <!-- Mostrar PDF o JPG/PNG -->
    <?php if ($ext === "pdf"): ?>
        <iframe src="<?= $filepath ?>" style="width:100%; height:700px;" frameborder="0"></iframe>
    <?php else: ?>
        <img src="<?= $filepath ?>" style="max-width:100%; border:1px solid #aaa;">
    <?php endif; ?>

<?php else: ?>

    <!-- Herramientas -->
    <div class="btns">
        <button onclick="setTool('Pan')">Mover</button>
        <button onclick="setTool('Zoom')">Zoom</button>
        <button onclick="setTool('Wwwc')">Brillo / Contraste</button>
        <button onclick="setTool('Length')">Medir distancia</button>
        <button onclick="setTool('Angle')">Medir ángulo</button>
    </div>

    <!-- Contenedor del visor -->
    <div id="viewer"></div>

    <script>
        // Configurar WADO Loader
        cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
        cornerstoneWADOImageLoader.external.dicomParser = dicomParser;

        cornerstone.enable(document.getElementById("viewer"));

        const imageId = "wadouri://<?= $filepath ?>";

        cornerstone.loadImage(imageId).then(function(image) {
            cornerstone.displayImage(document.getElementById("viewer"), image);
        });

        function setTool(tool) {
            const tools = cornerstoneTools;
            tools.init();

            if (tool === "Wwwc") {
                tools.wwwc.activate("viewer", 1);
                return;
            }
            if (tool === "Pan") {
                tools.pan.activate("viewer", 1);
                return;
            }
            if (tool === "Zoom") {
                tools.zoom.activate("viewer", 1);
                return;
            }
            if (tool === "Length") {
                tools.length.activate("viewer", 1);
                return;
            }
            if (tool === "Angle") {
                tools.angle.activate("viewer", 1);
                return;
            }
        }
    </script>

<?php endif; ?>

</body>
</html>
