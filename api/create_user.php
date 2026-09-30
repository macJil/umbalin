<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    respond(['success' => false, 'message' => 'Unauthorized'], 403);
}

$input = json_input();


if (empty($input['username']) || empty($input['password'])) {
    respond(['success' => false, 'message' => 'Username and password are required']);
}

$username = sanitize_str($input['username'], 100);
$password = $input['password'];
$role = 'user'; // Always set to 'user'

try {
    $pdo = db();
    

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->execute([$username]);
    
    if ($checkStmt->rowCount() > 0) {
        respond(['success' => false, 'message' => 'Username already exists']);
    }
    

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
 
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $role]);
    
    $userId = $pdo->lastInsertId();
    
    respond([
        'success' => true, 
        'message' => 'User created successfully',
        'user_id' => $userId
    ]);
    
} catch (PDOException $e) {
    respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
?>