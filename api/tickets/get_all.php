<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();

$where = ['1=1'];
$params = [];
$types = '';

if ($user['role'] === 'user') {
    $where[] = 't.created_by = ?';
    $params[] = $user['id'];
    $types .= 'i';
}

if (!empty($_GET['status'])) {
    $where[] = 's.name = ?';
    $params[] = $_GET['status'];
    $types .= 's';
}
if (!empty($_GET['priority'])) {
    $where[] = 'p.name = ?';
    $params[] = $_GET['priority'];
    $types .= 's';
}
if (!empty($_GET['category'])) {
    $where[] = 'c.name = ?';
    $params[] = $_GET['category'];
    $types .= 's';
}

$sql = '
    SELECT t.id, t.title, t.description, t.post_number, t.created_at, t.updated_at,
           t.resolved_at, t.closed_at, t.sla_breached, t.sla_deadline,
           CONCAT(u1.first_name, " ", u1.last_name) AS created_by,
           CONCAT(u2.first_name, " ", u2.last_name) AS assigned_to,
           t.assigned_to AS assigned_to_id,
           t.created_by AS created_by_id,
           c.name AS category,
           sub.name AS subcategory,
           p.name AS priority, p.color AS priority_color,
           s.name AS status
    FROM tickets t
    JOIN users u1 ON t.created_by = u1.id
    LEFT JOIN users u2 ON t.assigned_to = u2.id
    JOIN categories c ON t.category_id = c.id
    LEFT JOIN subcategories sub ON t.subcategory_id = sub.id
    JOIN priorities p ON t.priority_id = p.id
    JOIN statuses s ON t.status_id = s.id
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY t.created_at DESC
';

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$tickets = [];
while ($row = $result->fetch_assoc()) $tickets[] = $row;

jsonResponse($tickets);
