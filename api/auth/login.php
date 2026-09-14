<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$body = getBody();
$email = trim($body['email'] ?? '');
$password = $body['password'] ?? '';

if (!$email || !$password) jsonError('Email and password are required.');

$stmt = $conn->prepare('
    SELECT u.id, u.first_name, u.last_name, u.email, u.password_hash, u.is_active, r.name AS role
    FROM users u
    JOIN roles r ON u.role_id = r.id
    WHERE u.email = ?
');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) jsonError('Invalid email or password.', 401);
if (!$user['is_active']) jsonError('Account is disabled. Contact admin.', 403);
if (!password_verify($password, $user['password_hash'])) jsonError('Invalid email or password.', 401);

$_SESSION['user'] = [
    'id'         => $user['id'],
    'first_name' => $user['first_name'],
    'last_name'  => $user['last_name'],
    'email'      => $user['email'],
    'role'       => $user['role'],
];

jsonResponse([
    'message' => 'Login successful.',
    'user'    => $_SESSION['user'],
]);
