<?php
session_start();
require_once '../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    respond(['success' => false, 'message' => 'Unauthorized'], 403);
}

$input = json_input();


if (empty($input['id'])) {
    respond(['success' => false, 'message' => 'User ID required']);
}

$id = (int)$input['id'];


if ($id == $_SESSION['user_id']) {
    respond(['success' => false, 'message' => 'Cannot delete your own account']);
}


if ($id == 1) {
    respond(['success' => false, 'message' => 'Cannot delete the primary admin account']);
}

try {
    $pdo = db();
    

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->execute([$id]);
    
    if ($checkStmt->rowCount() === 0) {
        respond(['success' => false, 'message' => 'User not found']);
    }
    

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    
    respond([
        'success' => true, 
        'message' => 'User deleted successfully'
    ]);
    
} catch (PDOException $e) {
    respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
?>