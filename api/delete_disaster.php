<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Method not allowed'], 405);
}

$in = json_input();
$id = isset($in['id']) ? intval($in['id']) : 0;
if ($id <= 0) {
    respond(['success' => false, 'message' => 'Invalid id'], 400);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM disasters WHERE id = ?');
    $stmt->execute([$id]);
    respond(['success' => true]);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'DB error: ' . $e->getMessage()], 500);
}
