<?php
// seed_test_users.php
// Ejecutar UNA sola vez desde el navegador para crear/actualizar cuentas de prueba.
// Luego borrar el archivo por seguridad.

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!file_exists(__DIR__ . '/includes/db.php')) {
    die("No se encuentra includes/db.php. Colocá este archivo en la raíz del proyecto o corregí la ruta.");
}

include(__DIR__ . '/includes/db.php'); // Debe exponer $pdo (PDO)

$results = [];

try {
    $pdo->beginTransaction();

    // --- ADMIN ---
    $adminEmail = 'admin@medicareconnect.site';
    $adminPassPlain = 'admin123';
    $adminHash = password_hash($adminPassPlain, PASSWORD_DEFAULT);

    // Intentar insertar/actualizar en users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    if ($stmt->rowCount()) {
        $id = $stmt->fetchColumn();
        $u = $pdo->prepare("UPDATE users SET password = ?, fullName = ?, role = ? WHERE id = ?");
        // role puede ser NULL si tu tabla no tiene esa columna; adaptá si es necesario
        $u->execute([$adminHash, 'Administrador MedicareConnect', 'admin', $id]);
        $results[] = "Admin actualizado: $adminEmail";
    } else {
        // Intentamos insertar; adaptá columnas si tu tabla es distinta
        $u = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, ?)");
        $u->execute(['Administrador MedicareConnect', $adminEmail, $adminHash, 'admin']);
        $results[] = "Admin creado: $adminEmail";
    }

    // --- DOCTOR (usuario + registro en doctors) ---
    $docEmail = 'dr.prueba@medicareconnect.site';
    $docPassPlain = 'doctor123';
    $docHash = password_hash($docPassPlain, PASSWORD_DEFAULT);

    // Usuarios table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$docEmail]);
    if ($stmt->rowCount()) {
        $user_id = $stmt->fetchColumn();
        $u = $pdo->prepare("UPDATE users SET password = ?, fullName = ? WHERE id = ?");
        $u->execute([$docHash, 'Dr. Prueba', $user_id]);
        $results[] = "Usuario médico actualizado: $docEmail (users.id={$user_id})";
    } else {
        $u = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, ?)");
        $u->execute(['Dr. Prueba', $docEmail, $docHash, 'doctor']);
        $user_id = $pdo->lastInsertId();
        $results[] = "Usuario médico creado: $docEmail (users.id={$user_id})";
    }

    // Insertar/actualizar registro en doctors vinculado por user_id
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if ($stmt->rowCount()) {
        $doc_id = $stmt->fetchColumn();
        $d = $pdo->prepare("UPDATE doctors SET specialization = ?, office_address = ?, working_days = ?, working_hours = ?, consultation_fee = ?, profile_pic = ? WHERE user_id = ?");
        $d->execute(['Medicina General', 'Calle Falsa 123', 'Lunes-Viernes', '09:00-17:00', '1000.00', 'default.png', $user_id]);
        $results[] = "Registro doctors actualizado (doctors.user_id={$user_id})";
    } else {
        $d = $pdo->prepare("INSERT INTO doctors (user_id, specialization, office_address, working_days, working_hours, consultation_fee, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $d->execute([$user_id, 'Medicina General', 'Calle Falsa 123', 'Lunes-Viernes', '09:00-17:00', '1000.00', 'default.png']);
        $doc_id = $pdo->lastInsertId();
        $results[] = "Registro doctors creado (doctors.id={$doc_id}, user_id={$user_id})";
    }

    // --- PACIENTE (usuario + tabla patients opcional) ---
    $patEmail = 'paciente.prueba@medicareconnect.site';
    $patPassPlain = 'patient123';
    $patHash = password_hash($patPassPlain, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$patEmail]);
    if ($stmt->rowCount()) {
        $pat_user_id = $stmt->fetchColumn();
        $u = $pdo->prepare("UPDATE users SET password = ?, fullName = ? WHERE id = ?");
        $u->execute([$patHash, 'Paciente Prueba', $pat_user_id]);
        $results[] = "Usuario paciente actualizado: $patEmail (users.id={$pat_user_id})";
    } else {
        $u = $pdo->prepare("INSERT INTO users (fullName, email, password, role) VALUES (?, ?, ?, ?)");
        $u->execute(['Paciente Prueba', $patEmail, $patHash, 'patient']);
        $pat_user_id = $pdo->lastInsertId();
        $results[] = "Usuario paciente creado: $patEmail (users.id={$pat_user_id})";
    }

    // Intentamos insertar paciente en tabla patients si existe (no falla si no)
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE NAME = ? OR email = ?");
        $stmt->execute(['Paciente Prueba', $patEmail]);
        if ($stmt->rowCount()) {
            $results[] = "Paciente ya existe en tabla patients.";
        } else {
            // Intentamos insertar (si falla por columnas diferentes lo capturamos)
            $r = $pdo->prepare("INSERT INTO patients (NAME, email) VALUES (?, ?)");
            $r->execute(['Paciente Prueba', $patEmail]);
            $results[] = "Paciente insertado en tabla patients.";
        }
    } catch (Exception $e) {
        $results[] = "No se pudo insertar en tabla patients (estructura distinta): " . $e->getMessage();
    }

    $pdo->commit();

    $results[] = "";
    $results[] = "CREDENCIALES (para pruebas):";
    $results[] = "ADMIN: {$adminEmail} / {$adminPassPlain}";
    $results[] = "DOCTOR: {$docEmail} / {$docPassPlain}";
    $results[] = "PACIENTE: {$patEmail} / {$patPassPlain}";

} catch (Exception $e) {
    $pdo->rollBack();
    $results[] = "Error: " . $e->getMessage();
}

?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Seed cuentas - MedicareConnect</title>
<style>body{font-family:Arial;background:#f4f6fb;padding:30px} .box{background:#fff;padding:20px;border-radius:10px;box-shadow:0 8px 30px rgba(0,0,0,0.06)}</style>
</head><body>
<div class="box">
    <h2>Seed - Cuentas de prueba</h2>
    <pre><?php echo htmlspecialchars(implode("\n", $results)); ?></pre>
    <p style="color:#b71c1c"><strong>IMPORTANTE:</strong> borrá este archivo después de usarlo.</p>
    <p><a href="/doctor/login.php">Ir a Login Médico</a></p>
</div>
</body></html>
