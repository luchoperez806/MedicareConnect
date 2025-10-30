<?php
// Parámetros de conexión a la base de datos
$host = 'localhost';
$dbname = 'medicareconnect';
$username = 'root';
$password = ''; // dejar vacío si no tenés clave en XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Habilita errores de PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
