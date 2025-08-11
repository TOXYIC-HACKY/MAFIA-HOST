<?php
// public/check_username.php
require_once __DIR__ . '/../db.php';

// Return JSON
header('Content-Type: application/json; charset=utf-8');

$username = trim($_GET['username'] ?? '');

// Basic validation
if ($username === '') {
    echo json_encode(['status' => 'error', 'message' => 'No username provided']);
    exit;
}

if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
    echo json_encode(['status' => 'invalid', 'message' => 'Invalid username format']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['status' => 'taken', 'message' => 'Username is already taken']);
} else {
    echo json_encode(['status' => 'available', 'message' => 'Username is available']);
}
