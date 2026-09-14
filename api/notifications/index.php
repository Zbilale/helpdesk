<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20');
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifs = [];
    while ($row = $result->fetch_assoc()) $notifs[] = $row;
    jsonResponse($notifs);
}

if ($method === 'PUT') {
    $id = intval($_GET['id'] ?? 0);
    if ($id) {
        $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $user['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->bind_param('i', $user['id']);
        $stmt->execute();
    }
    jsonResponse(['message' => 'Notifications updated.']);
}

jsonError('Method not allowed.', 405);
