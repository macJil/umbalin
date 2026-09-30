<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';


if (!isset($_SESSION['user_id'])) {
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
    
    $user_id = $_SESSION['user_id'];


    $required = ['name', 'type', 'occurred_at', 'intensity_signal', 'latitude', 'longitude', 'radius_m', 'description'];
    foreach ($required as $field) {
        if (!isset($in[$field]) || trim($in[$field]) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing field: $field"]);
            exit;
        }
    }


    $checkTable = $conn->query("SHOW TABLES LIKE 'user_reports'");
    if ($checkTable->num_rows === 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database table not found. Please run the SQL setup first.']);
        exit;
    }

    $name = trim($in['name']);
    $type = trim($in['type']);
    $occurred_at = $in['occurred_at'];
    $intensity_signal = trim($in['intensity_signal']);
    $latitude = floatval($in['latitude']);
    $longitude = floatval($in['longitude']);
    $radius_m = intval($in['radius_m']);
    $address = isset($in['address']) ? trim($in['address']) : '';
    $description = trim($in['description']);
    
    $stmt = $conn->prepare("INSERT INTO user_reports (user_id, name, type, occurred_at, intensity_signal, latitude, longitude, radius_m, address, description, status, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    $stmt->bind_param("issssddiis", $user_id, $name, $type, $occurred_at, $intensity_signal, $latitude, $longitude, $radius_m, $address, $description);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully and is pending approval']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to insert report: ' . $stmt->error]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}