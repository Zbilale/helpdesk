<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$id = intval($_GET['id'] ?? 0);
if (!$id) jsonError('Ticket ID required.');

$stmt = $conn->prepare('
    SELECT t.*, 
           CONCAT(u1.first_name, " ", u1.last_name) AS created_by_name,
           CONCAT(u2.first_name, " ", u2.last_name) AS assigned_to_name,
           c.name AS category, sub.name AS subcategory,
           p.name AS priority, p.color AS priority_color,
           s.name AS status
    FROM tickets t
    JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.assigned_to = u2.id
    JOIN categories c ON t.category_id = c.id
    LEFT JOIN subcategories sub ON t.subcategory_id = sub.id
    JOIN priorities p ON t.priority_id = p.id
    JOIN statuses s ON t.status_id = s.id
    WHERE t.id = ?
');
$stmt->bind_param('i', $id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) jsonError('Ticket not found.', 404);
if ($user['role'] === 'user' && $ticket['created_by'] !== $user['id']) jsonError('Access denied.', 403);

jsonResponse($ticket);
