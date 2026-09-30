<?php

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log'); 

require_once __DIR__ . '/../config.php';

try {

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE || !$input) {
        $input = $_POST; 
    }


    $required = ['name', 'type', 'occurred_at', 'intensity_signal', 'latitude', 'longitude', 'radius_m'];
    foreach ($required as $f) {
        if (empty($input[$f]) && $input[$f] !== '0') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing field: $f"]);
            exit;
        }
    }


    $name = trim($input['name']);
    $type = trim($input['type']);
    $occurred_at = $input['occurred_at'];
    $intensity_signal = trim($input['intensity_signal']);
    $latitude = floatval($input['latitude']);
    $longitude = floatval($input['longitude']);
    $radius_m = intval($input['radius_m']);
    $address = isset($input['address']) ? trim($input['address']) : null;
    $description = isset($input['description']) ? trim($input['description']) : null;


    $stmt = $conn->prepare("INSERT INTO disasters (name, type, occurred_at, intensity_signal, latitude, longitude, radius_m, address, description)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssddiss", $name, $type, $occurred_at, $intensity_signal, $latitude, $longitude, $radius_m, $address, $description);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✅ Report added successfully!']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database insert failed', 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred', 'detail' => $e->getMessage()]);
}
