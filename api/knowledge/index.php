<?php
require_once '../../includes/helpers.php';
require_once '../../includes/auth.php';
require_once '../../config/db.php';

requireAuth();
$user   = currentUser();
$method = $_SERVER['REQUEST_METHOD'];
$id     = intval($_GET['id'] ?? 0);

if ($method === 'GET' && !$id) {
    $where = $user['role'] === 'user' ? 'WHERE k.is_published = 1' : '';
    $result = $conn->query("
        SELECT k.id, k.title, k.views, k.is_published, k.created_at, k.content,
               c.name AS category,
               CONCAT(u.first_name, ' ', u.last_name) AS created_by
        FROM knowledge_base k
        LEFT JOIN categories c ON k.category_id = c.id
        JOIN users u ON k.created_by = u.id
        $where
        ORDER BY k.created_at DESC
    ");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    jsonResponse($rows);
}

if ($method === 'GET' && $id) {
    $conn->query("UPDATE knowledge_base SET views = views + 1 WHERE id = $id");
    $stmt = $conn->prepare("
        SELECT k.*, c.name AS category, CONCAT(u.first_name, ' ', u.last_name) AS created_by
        FROM knowledge_base k
        LEFT JOIN categories c ON k.category_id = c.id
        JOIN users u ON k.created_by = u.id
        WHERE k.id = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $article = $stmt->get_result()->fetch_assoc();
    if (!$article) jsonError('Article not found.', 404);
    jsonResponse($article);
}

if ($method === 'POST') {
    requireRole('admin', 'technician');
    $body        = getBody();
    $title       = trim($body['title'] ?? '');
    $content     = trim($body['content'] ?? '');
    $category_id = intval($body['category_id'] ?? 0) ?: null;
    $published   = intval($body['is_published'] ?? 0);
    if (!$title || !$content) jsonError('Title and content are required.');
    $stmt = $conn->prepare('INSERT INTO knowledge_base (title, content, category_id, created_by, is_published) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssiii', $title, $content, $category_id, $user['id'], $published);
    $stmt->execute();
    jsonResponse(['message' => 'Article created.'], 201);
}

if ($method === 'PUT' && $id) {
    requireRole('admin', 'technician');
    $body        = getBody();
    $title       = trim($body['title'] ?? '');
    $content     = trim($body['content'] ?? '');
    $category_id = intval($body['category_id'] ?? 0) ?: null;
    $published   = intval($body['is_published'] ?? 0);
    $stmt = $conn->prepare('UPDATE knowledge_base SET title=?, content=?, category_id=?, is_published=?, updated_at=NOW() WHERE id=?');
    $stmt->bind_param('ssiii', $title, $content, $category_id, $published, $id);
    $stmt->execute();
    jsonResponse(['message' => 'Article updated.']);
}

if ($method === 'DELETE' && $id) {
    requireRole('admin');
    $conn->query("DELETE FROM knowledge_base WHERE id = $id");
    jsonResponse(['message' => 'Article deleted.']);
}

jsonError('Method not allowed.', 405);
