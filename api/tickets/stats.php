<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();

$result = $conn->query('
    SELECT
        COUNT(*) AS total,
        SUM(s.name = "Open") AS open_count,
        SUM(s.name = "In Progress") AS in_progress,
        SUM(s.name = "Resolved") AS resolved,
        SUM(s.name = "Closed") AS closed,
        SUM(p.name = "Critical") AS critical
    FROM tickets t
    JOIN statuses s ON t.status_id = s.id
    JOIN priorities p ON t.priority_id = p.id
')->fetch_assoc();

jsonResponse($result);
