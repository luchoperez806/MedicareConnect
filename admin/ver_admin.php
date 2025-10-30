<?php
include("../includes/db.php");

$email = 'admin@medicareconnect.site';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

echo "<pre>";
print_r($user);
echo "</pre>";
?>
