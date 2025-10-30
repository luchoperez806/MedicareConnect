<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifications.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    markNotificationAsRead($id);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
