<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['email'])) {
  $email = trim($_GET['email']);

  $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    echo json_encode(['exists' => true]);
  } else {
    echo json_encode(['exists' => false]);
  }

  $stmt->close();
}
?>
