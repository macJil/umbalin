<?php
session_start();
require_once '../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    respond(['success' => false, 'message' => 'Unauthorized'], 403);
}

$input = json_input();


if (empty($input['id']) || empty($input['username'])) {
    respond(['success' => false, 'message' => 'User ID and username are required']);
}

$id = (int)$input['id'];
$username = sanitize_str($input['username'], 100);
$password = !empty($input['password']) ? $input['password'] : null;


if ($id == $_SESSION['user_id']) {
    respond(['success' => false, 'message' => 'Cannot edit your own account from here']);
}

try {
    $pdo = db();
    

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkStmt->execute([$username, $id]);
    
    if ($checkStmt->rowCount() > 0) {
        respond(['success' => false, 'message' => 'Username already exists']);
    }
    

    if ($password) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $hashedPassword, $id]);
    } else {

        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$username, $id]);
    }
    
    respond([
        'success' => true, 
        'message' => 'User updated successfully'
    ]);
    
} catch (PDOException $e) {
    respond(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
?>