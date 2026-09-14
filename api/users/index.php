<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user = currentUser();
$method = $_SERVER['REQUEST_METHOD'];
$id = intval($_GET['id'] ?? 0);

if ($method === 'GET' && !$id) {
    requireRole('admin');
    $result = $conn->query('
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.department,
               u.avatar_url, u.is_active, u.created_at, r.name AS role
        FROM users u JOIN roles r ON u.role_id = r.id
        ORDER BY u.created_at DESC
    ');
    $users = [];
    while ($row = $result->fetch_assoc()) $users[] = $row;
    jsonResponse($users);
}

if ($method === 'GET' && $id) {
    if ($user['role'] !== 'admin' && $user['id'] !== $id) jsonError('Access denied.', 403);
    $stmt = $conn->prepare('
        SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.department,
               u.avatar_url, u.is_active, u.created_at, r.name AS role
        FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?
    ');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    if (!$u) jsonError('User not found.', 404);
    jsonResponse($u);
}

if ($method === 'PUT' && $id) {
    if ($user['role'] !== 'admin' && $user['id'] !== $id) jsonError('Access denied.', 403);
    $body = getBody();
    $current = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
    if (!$current) jsonError('User not found.', 404);

    $first_name = $body['first_name'] ?? $current['first_name'];
    $last_name  = $body['last_name']  ?? $current['last_name'];
    $phone      = $body['phone']      ?? $current['phone'];
    $department = $body['department'] ?? $current['department'];
    $password_hash = $current['password_hash'];

    if (!empty($body['password'])) {
        $password_hash = password_hash($body['password'], PASSWORD_BCRYPT);
    }

    $role_id  = ($user['role'] === 'admin' && isset($body['role_id'])) ? intval($body['role_id']) : $current['role_id'];
    $is_active = ($user['role'] === 'admin' && isset($body['is_active'])) ? intval($body['is_active']) : $current['is_active'];

    $stmt = $conn->prepare('
        UPDATE users SET first_name=?, last_name=?, phone=?, department=?, password_hash=?, role_id=?, is_active=?, updated_at=NOW()
        WHERE id=?
    ');
    $stmt->bind_param('sssssiii', $first_name, $last_name, $phone, $department, $password_hash, $role_id, $is_active, $id);
    $stmt->execute();
    jsonResponse(['message' => 'User updated.']);
}

jsonError('Method not allowed.', 405);
