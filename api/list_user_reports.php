<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';


if (!isset($_SESSION['user_id'])) {
    respond(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $user_id = $_SESSION['user_id'];
    $pdo = db();
    
    $sql = "SELECT id, name, type, occurred_at, intensity_signal, latitude, longitude, radius_m, address, description, status, submitted_at, reviewed_at, admin_notes
            FROM user_reports
            WHERE user_id = ?
            ORDER BY submitted_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $reports = $stmt->fetchAll();

    respond(['success' => true, 'data' => $reports]);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}