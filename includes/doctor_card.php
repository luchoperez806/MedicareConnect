<?php
function getDoctorInfo($doctor_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT fullName, specialization, profile_image FROM doctors
                           JOIN users ON doctors.user_id = users.id WHERE doctors.id = ?");
    $stmt->execute([$doctor_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
