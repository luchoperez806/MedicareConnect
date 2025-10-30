<?php
require_once "db.php";

header('Content-Type: application/json; charset=utf-8');

try {
    // Obtener lista de doctores
    $stmt = $pdo->query("SELECT id, doctorname, specilization FROM doctors ORDER BY doctorname ASC");
    $doctores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($doctores);
} catch (PDOException $e) {
    echo json_encode(["error" => "Error al obtener doctores: " . $e->getMessage()]);
}
?>
