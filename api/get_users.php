<?php
session_start();
require_once '../config.php'; 

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    respond(['success' => false, 'message' => 'Not logged in'], 401);
}


if ($_SESSION['role'] !== 'admin') {
    respond(['success' => false, 'message' => 'Admin access required'], 403);
}

try {
    
    $pdo = db();
    
  
    $stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    respond([
        'success' => true, 
        'data' => $users,
        'count' => count($users)
    ]);
    
} catch (PDOException $e) {
    respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
?>