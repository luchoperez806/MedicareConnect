<?php
require_once __DIR__ . '/db.php';

/**
 * Crear una notificación en la base de datos
 * @param int $user_id ID del usuario destinatario
 * @param string $title Título corto de la notificación
 * @param string $message Cuerpo o descripción de la notificación
 * @return bool
 */
function createNotification($user_id, $title, $message) {
    global $pdo;
    if (!$user_id || !$title) return false;

    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, created_at, read_status)
        VALUES (?, ?, ?, NOW(), 0)
    ");
    return $stmt->execute([$user_id, $title, $message]);
}

/**
 * Obtener notificaciones de un usuario
 * @param int $user_id
 * @return array
 */
function getNotifications($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT id, title, message, created_at, read_status
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 30
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Marcar una notificación como leída
 * @param int $id
 * @return bool
 */
function markNotificationAsRead($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET read_status = 1 WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Enviar una notificación (alias amigable para createNotification)
 * @param int $user_id
 * @param string $title
 * @param string $message
 */
function sendNotification($user_id, $title, $message) {
    return createNotification($user_id, $title, $message);
}
?>
