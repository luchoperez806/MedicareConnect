<?php
// Configuración de conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "medicareconnect"; // Nombre de tu base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
