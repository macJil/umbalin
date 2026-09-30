<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $raw = file_get_contents('php://input');
    $in = json_decode($raw, true);
    
    if (!$in) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }
    
    $report_id = isset($in['id']) ? intval($in['id']) : 0;
    $action = isset($in['action']) ? $in['action'] : '';
    $admin_notes = isset($in['admin_notes']) ? trim($in['admin_notes']) : '';

    if ($report_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid report ID']);
        exit;
    }

    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    if ($action === 'approve') {

        $stmt = $conn->prepare("SELECT * FROM user_reports WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $report = $result->fetch_assoc();
        $stmt->close();

        if (!$report) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Report not found or already processed']);
            exit;
        }


        $insertStmt = $conn->prepare("INSERT INTO disasters (name, type, occurred_at, intensity_signal, latitude, longitude, radius_m, address, description)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insertStmt->bind_param("ssssddiis", 
            $report['name'],
            $report['type'],
            $report['occurred_at'],
            $report['intensity_signal'],
            $report['latitude'],
            $report['longitude'],
            $report['radius_m'],
            $report['address'],
            $report['description']
        );
        
        if (!$insertStmt->execute()) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add to disasters: ' . $insertStmt->error]);
            exit;
        }
        $insertStmt->close();


        $updateStmt = $conn->prepare("UPDATE user_reports SET status = 'approved', reviewed_at = NOW(), admin_notes = ? WHERE id = ?");
        $updateStmt->bind_param("si", $admin_notes, $report_id);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(['success' => true, 'message' => 'Report approved and added to map']);
    } else {

        $updateStmt = $conn->prepare("UPDATE user_reports SET status = 'rejected', reviewed_at = NOW(), admin_notes = ? WHERE id = ?");
        $updateStmt->bind_param("si", $admin_notes, $report_id);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(['success' => true, 'message' => 'Report rejected']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}