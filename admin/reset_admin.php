<?php
include("../includes/db.php");

// Nueva contraseña
$nuevaPassword = "admin123";
$hash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 2 AND role = 'admin'");
$stmt->execute([$hash]);

echo "Contraseña del admin reseteada a: $nuevaPassword";
