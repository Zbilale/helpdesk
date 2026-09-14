<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireRole('admin');
$id = intval($_GET['id'] ?? 0);
if (!$id) jsonError('Ticket ID required.');

$conn->query("DELETE FROM tickets WHERE id = $id");
jsonResponse(['message' => 'Ticket deleted.']);
