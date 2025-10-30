<?php
session_start();

// Destruir toda la sesión
session_unset();
session_destroy();

// Redirigir al inicio
header("Location: ../index.php");
exit;
?>
