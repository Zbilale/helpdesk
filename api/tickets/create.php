<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$body = getBody();

$title          = trim($body['title'] ?? '');
$description    = trim($body['description'] ?? '');
$post_number    = trim($body['post_number'] ?? '');
$category_id    = intval($body['category_id'] ?? 0);
$subcategory_id = intval($body['subcategory_id'] ?? 0) ?: null;
$priority_id    = intval($body['priority_id'] ?? 0);

if (!$title || !$description || !$category_id || !$priority_id || !$post_number) {
    jsonError('Please fill all required fields.');
}

$status = $conn->query("SELECT id FROM statuses WHERE name = 'Open'")->fetch_assoc();
$status_id = $status['id'];

$sla = $conn->query("SELECT response_time_minutes FROM sla_rules WHERE priority_id = $priority_id")->fetch_assoc();
$sla_deadline = null;
if ($sla) {
    $sla_deadline = date('Y-m-d H:i:s', strtotime('+' . $sla['response_time_minutes'] . ' minutes'));
}

$stmt = $conn->prepare('
    INSERT INTO tickets (title, description, post_number, created_by, category_id, subcategory_id, priority_id, status_id, sla_deadline)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->bind_param('sssiiiiis', $title, $description, $post_number, $user['id'], $category_id, $subcategory_id, $priority_id, $status_id, $sla_deadline);
$stmt->execute();
$ticket_id = $conn->insert_id;

// Notify all technicians
$techs = $conn->query("SELECT u.id FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name IN ('technician','admin') AND u.is_active = 1");
while ($tech = $techs->fetch_assoc()) {
    $msg = "New ticket #{$ticket_id}: {$title} at post {$post_number}";
    $stmt2 = $conn->prepare('INSERT INTO notifications (user_id, ticket_id, type, message) VALUES (?, ?, ?, ?)');
    $type = 'new_ticket';
    $stmt2->bind_param('iiss', $tech['id'], $ticket_id, $type, $msg);
    $stmt2->execute();
}

jsonResponse(['message' => 'Ticket created.', 'ticket_id' => $ticket_id], 201);
