<?php

const DB_HOST = '127.0.0.1';
const DB_NAME = 'disaster_db';
const DB_USER = 'root';
const DB_PASS = '';

$servername = "localhost";
$username = "root";
$password = "";  
$dbname = "disaster_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}


function db() : PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function json_input() : array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];
    return $data;
}

function respond($payload, int $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function sanitize_str(?string $v, int $len = 255) : string {
    $v = trim((string)$v);
    if (strlen($v) > $len) $v = substr($v, 0, $len);
    return $v;
}

function parse_datetime(?string $v) : ?string {
    if (!$v) return null;
    $ts = strtotime($v);
    if ($ts === false) return null;
    return date('Y-m-d H:i:s', $ts);
}
