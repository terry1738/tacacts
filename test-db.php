<?php
require __DIR__ . '/db.php';
try {
    $pdo = getDB();
    $r = $pdo->query('SELECT 1')->fetchColumn();
    echo 'DB test OK: ' . ($r ? $r : 'no result');
} catch (Exception $e) {
    echo 'DB test ERROR: ' . $e->getMessage();
}
