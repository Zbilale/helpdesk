<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$body = getBody();
$id = intval($_GET['id'] ?? 0);
if (!$id) jsonError('Ticket ID required.');

$ticket = $conn->query("SELECT * FROM tickets WHERE id = $id")->fetch_assoc();
if (!$ticket) jsonError('Ticket not found.', 404);

$fields = [];
$params = [];
$types  = '';

if (isset($body['title'])) { $fields[] = 'title = ?'; $params[] = $body['title']; $types .= 's'; }
if (isset($body['description'])) { $fields[] = 'description = ?'; $params[] = $body['description']; $types .= 's'; }
if (isset($body['post_number'])) { $fields[] = 'post_number = ?'; $params[] = $body['post_number']; $types .= 's'; }
if (isset($body['category_id'])) { $fields[] = 'category_id = ?'; $params[] = intval($body['category_id']); $types .= 'i'; }
if (isset($body['subcategory_id'])) { $fields[] = 'subcategory_id = ?'; $params[] = intval($body['subcategory_id']); $types .= 'i'; }
if (isset($body['priority_id'])) { $fields[] = 'priority_id = ?'; $params[] = intval($body['priority_id']); $types .= 'i'; }
if (isset($body['solution'])) { $fields[] = 'solution = ?'; $params[] = $body['solution']; $types .= 's'; }

if (isset($body['assigned_to'])) {
    $assigned = $body['assigned_to'] ? intval($body['assigned_to']) : null;
    $fields[] = 'assigned_to = ?';
    $params[] = $assigned;
    $types .= 'i';
    if ($assigned) {
        $fields[] = 'taken_at = NOW()';
    }
}

if (isset($body['status_id'])) {
    $status_id = intval($body['status_id']);
    $fields[] = 'status_id = ?';
    $params[] = $status_id;
    $types .= 'i';

    $status = $conn->query("SELECT name FROM statuses WHERE id = $status_id")->fetch_assoc();
    if ($status['name'] === 'Resolved') $fields[] = 'resolved_at = NOW()';
    if ($status['name'] === 'Closed')   $fields[] = 'closed_at = NOW()';
    if ($status['name'] === 'Open')     $fields[] = 'assigned_to = NULL, taken_at = NULL';

    // Notify ticket creator of status change
    $msg = "Your ticket #{$id} status changed to: {$status['name']}";
    $type = 'status_changed';
    $stmt2 = $conn->prepare('INSERT INTO notifications (user_id, ticket_id, type, message) VALUES (?, ?, ?, ?)');
    $stmt2->bind_param('iiss', $ticket['created_by'], $id, $type, $msg);
    $stmt2->execute();
}

if (empty($fields)) jsonError('Nothing to update.');

$fields[] = 'updated_at = NOW()';
$sql = 'UPDATE tickets SET ' . implode(', ', $fields) . ' WHERE id = ?';
$params[] = $id;
$types .= 'i';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

// Log history
foreach (['status_id', 'assigned_to', 'priority_id'] as $field) {
    if (isset($body[$field]) && $body[$field] != $ticket[$field]) {
        $old = strval($ticket[$field] ?? '');
        $new = strval($body[$field] ?? '');
        $stmt3 = $conn->prepare('INSERT INTO ticket_history (ticket_id, changed_by, field_changed, old_value, new_value) VALUES (?, ?, ?, ?, ?)');
        $stmt3->bind_param('iisss', $id, $user['id'], $field, $old, $new);
        $stmt3->execute();
    }
}

jsonResponse(['message' => 'Ticket updated.']);
