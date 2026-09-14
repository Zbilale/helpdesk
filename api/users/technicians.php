<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();

$result = $conn->query("
    SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name, u.email
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'technician' AND u.is_active = 1
");
$techs = [];
while ($row = $result->fetch_assoc()) $techs[] = $row;
jsonResponse($techs);
