<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';

header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');
$domain   = trim($_POST['domain'] ?? '');

if ($username === '' || $domain === '') {
    echo json_encode(['ok' => false, 'error' => 'Missing username or domain']);
    exit;
}

// Basic syntax check
if (!preg_match('/^[a-z0-9._-]{3,32}$/i', $username)) {
    echo json_encode(['ok' => false, 'error' => 'Invalid characters or length (3-32)']);
    exit;
}

try {
    $pdo = getDB();

    // Check domain exists and is active
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM domains WHERE domain_name = ? AND active = 1");
    $stmt->execute([$domain]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['ok' => false, 'error' => 'Selected domain is not available']);
        exit;
    }

    // Check banned words
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM banned_words WHERE word = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['ok' => false, 'error' => 'Username is not allowed']);
        exit;
    }

    // Check if email already exists
    $email = $username . '@' . $domain;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['ok' => false, 'error' => 'Email already exists']);
        exit;
    }

    echo json_encode(['ok' => true]);

} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

