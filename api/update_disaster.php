<?php
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'message' => 'Method not allowed'], 405);
}

$in = json_input();
$id = isset($in['id']) ? intval($in['id']) : 0;

if ($id <= 0) {
    respond(['success' => false, 'message' => 'Invalid ID'], 400);
}

try {
    $pdo = db();

    $sql = "UPDATE disasters
            SET name = ?, type = ?, occurred_at = ?, intensity_signal = ?,
                latitude = ?, longitude = ?, radius_m = ?, address = ?, description = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        sanitize_str($in['name']),
        sanitize_str($in['type']),
        parse_datetime($in['occurred_at']),
        sanitize_str($in['intensity_signal']),
        floatval($in['latitude']),
        floatval($in['longitude']),
        intval($in['radius_m']),
        sanitize_str($in['address']),
        sanitize_str($in['description'], 1000),
        $id
    ]);

    respond(['success' => true, 'message' => 'Report updated successfully']);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'DB error: ' . $e->getMessage()], 500);
}
