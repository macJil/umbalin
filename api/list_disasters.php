<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

require_once __DIR__ . '/../config.php';

try {
    $where = [];
    $params = [];
    $types = '';

    if (!empty($_GET['type'])) {
        $where[] = "type = ?";
        $params[] = $_GET['type'];
        $types .= 's';
    }
    if (!empty($_GET['from'])) {
        $where[] = "occurred_at >= ?";
        $params[] = $_GET['from'];
        $types .= 's';
    }
    if (!empty($_GET['to'])) {
        $where[] = "occurred_at <= ?";
        $params[] = $_GET['to'];
        $types .= 's';
    }
    if (!empty($_GET['q'])) {
        $where[] = "(name LIKE ? OR address LIKE ? OR description LIKE ?)";
        $q = "%" . $_GET['q'] . "%";
        $params[] = $q; $params[] = $q; $params[] = $q;
        $types .= 'sss';
    }

    $sql = "SELECT id, name, type, occurred_at, intensity_signal, latitude, longitude, radius_m, address, description 
            FROM disasters";
    if ($where) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY occurred_at DESC";

    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $r['latitude'] = floatval($r['latitude']);
        $r['longitude'] = floatval($r['longitude']);
        $r['radius_m'] = floatval($r['radius_m']);
        $rows[] = $r;
    }

    echo json_encode(['success' => true, 'data' => $rows]);
    $stmt->close();
    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'detail' => $e->getMessage()]);
}
