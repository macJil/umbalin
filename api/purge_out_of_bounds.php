<?php
require_once __DIR__ . '/../config.php';


$south = 16.2700;
$north = 16.4800;
$west = 120.6000;
$east = 120.8000;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $pdo = db();
    $stmt = $pdo->prepare("DELETE FROM disasters WHERE latitude < ? OR latitude > ? OR longitude < ? OR longitude > ?");
    $stmt->execute([$south, $north, $west, $east]);
    $deleted = $stmt->rowCount();
    respond(['success' => true, 'deleted' => $deleted]);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'DB error: ' . $e->getMessage()], 500);
}
