<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$ticket_id = intval($_GET['ticket_id'] ?? 0);
if (!$ticket_id) jsonError('Ticket ID required.');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = '
        SELECT c.id, c.content, c.is_internal, c.created_at,
               CONCAT(u.first_name, " ", u.last_name) AS author,
               r.name AS author_role
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN roles r ON u.role_id = r.id
        WHERE c.ticket_id = ?
    ';
    if ($user['role'] === 'user') $sql .= ' AND c.is_internal = 0';
    $sql .= ' ORDER BY c.created_at ASC';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    while ($row = $result->fetch_assoc()) $comments[] = $row;
    jsonResponse($comments);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = getBody();
    $content     = trim($body['content'] ?? '');
    $is_internal = ($user['role'] !== 'user') ? intval($body['is_internal'] ?? 0) : 0;

    if (!$content) jsonError('Comment content is required.');

    $stmt = $conn->prepare('INSERT INTO comments (ticket_id, user_id, content, is_internal) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iisi', $ticket_id, $user['id'], $content, $is_internal);
    $stmt->execute();

    jsonResponse(['message' => 'Comment added.', 'comment_id' => $conn->insert_id], 201);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $comment_id = intval($_GET['comment_id'] ?? 0);
    $comment = $conn->query("SELECT user_id FROM comments WHERE id = $comment_id")->fetch_assoc();
    if (!$comment) jsonError('Comment not found.', 404);
    if ($user['role'] !== 'admin' && $comment['user_id'] !== $user['id']) jsonError('Access denied.', 403);
    $conn->query("DELETE FROM comments WHERE id = $comment_id");
    jsonResponse(['message' => 'Comment deleted.']);
}

jsonError('Method not allowed.', 405);
