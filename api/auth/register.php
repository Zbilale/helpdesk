<?php
require_once '../../includes/helpers.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Method not allowed', 405);

$body = getBody();
$first_name = trim($body['first_name'] ?? '');
$last_name  = trim($body['last_name'] ?? '');
$email      = trim($body['email'] ?? '');
$password   = $body['password'] ?? '';
$phone      = trim($body['phone'] ?? '');
$department = trim($body['department'] ?? '');

if (!$first_name || !$last_name || !$email || !$password) jsonError('Please fill all required fields.');

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) jsonError('Email already in use.', 409);

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$role = $conn->query("SELECT id FROM roles WHERE name = 'user'")->fetch_assoc();
$role_id = $role['id'];

$stmt = $conn->prepare('
    INSERT INTO users (role_id, first_name, last_name, email, password_hash, phone, department)
    VALUES (?, ?, ?, ?, ?, ?, ?)
');
$stmt->bind_param('issssss', $role_id, $first_name, $last_name, $email, $password_hash, $phone, $department);
$stmt->execute();

jsonResponse(['message' => 'Account created successfully.'], 201);
