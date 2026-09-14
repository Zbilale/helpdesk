<?php
function requireAuth() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
        exit;
    }
}

function requireRole(...$roles) {
    requireAuth();
    if (!in_array($_SESSION['user']['role'], $roles)) {
        http_response_code(403);
        echo json_encode(['message' => 'Access denied']);
        exit;
    }
}

function currentUser() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return $_SESSION['user'] ?? null;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function jsonError($message, $code = 400) {
    jsonResponse(['message' => $message], $code);
}
