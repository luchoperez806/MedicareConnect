<?php
include("../includes/db.php");

// Solo se ejecuta una vez
$email = "admin@medicareconnect.site";
$passwordPlano = "admin123"; // Cambialo luego
$hash = password_hash($passwordPlano, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (fullName, email, password, role)
        VALUES ('Administrador principal', ?, ?, 'admin')";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email, $hash]);

echo "Admin creado correctamente.";
?>
