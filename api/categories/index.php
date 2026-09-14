<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$method = $_SERVER['REQUEST_METHOD'];
$type   = $_GET['type'] ?? 'categories';
$id     = intval($_GET['id'] ?? 0);

if ($type === 'categories') {
    if ($method === 'GET') {
        $result = $conn->query('SELECT * FROM categories ORDER BY name');
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        jsonResponse($rows);
    }
    if ($method === 'POST') {
        requireRole('admin');
        $body = getBody();
        $name = trim($body['name'] ?? '');
        $desc = trim($body['description'] ?? '');
        if (!$name) jsonError('Name is required.');
        $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $desc);
        $stmt->execute();
        jsonResponse(['message' => 'Category created.'], 201);
    }
    if ($method === 'DELETE') {
        requireRole('admin');
        $conn->query("DELETE FROM categories WHERE id = $id");
        jsonResponse(['message' => 'Category deleted.']);
    }
}

if ($type === 'subcategories') {
    $category_id = intval($_GET['category_id'] ?? 0);
    $stmt = $conn->prepare('SELECT * FROM subcategories WHERE category_id = ? ORDER BY name');
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $rows = [];
    while ($row = $stmt->get_result()->fetch_assoc()) $rows[] = $row;
    jsonResponse($rows);
}

if ($type === 'priorities') {
    $result = $conn->query('SELECT * FROM priorities ORDER BY level');
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    jsonResponse($rows);
}

if ($type === 'statuses') {
    $result = $conn->query('SELECT * FROM statuses');
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    jsonResponse($rows);
}

jsonError('Invalid type.', 400);
