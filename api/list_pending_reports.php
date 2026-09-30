<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    respond(['success' => false, 'message' => 'Unauthorized'], 401);
}

try {
    $pdo = db();
    
    $status = $_GET['status'] ?? 'pending';
    
    $sql = "SELECT ur.*, u.username as submitted_by
            FROM user_reports ur
            LEFT JOIN users u ON ur.user_id = u.id
            WHERE ur.status = ?
            ORDER BY ur.submitted_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    $reports = $stmt->fetchAll();

    respond(['success' => true, 'data' => $reports]);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}