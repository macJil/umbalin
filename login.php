<?php
session_start();
include 'config.php';


if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: index.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}

$error = '';
$adminMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];


    if ($user['role'] === 'admin') {
      header("Location: index.php");
      exit();
    } else {
      header("Location: user_dashboard.php");
      exit();
    }
  } else {
    $error = "Invalid username or password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | Itogon Disaster Alert System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
      

      background-image: 
        linear-gradient(rgba(255, 255, 255, 0.70), rgba(245, 245, 245, 0.75)),
        url('assets/images/login-background.jpg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-attachment: fixed;
    }


    body.no-bg-image {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    body::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background: url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><circle cx="30" cy="30" r="2" fill="rgba(100,100,100,0.08)"/></svg>');
      animation: float 20s linear infinite;
      pointer-events: none;
    }

    @keyframes float {
      0% { transform: translate(0, 0); }
      100% { transform: translate(-50%, -50%); }
    }

    .login-container {
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      padding: 50px 40px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 420px;
      position: relative;
      z-index: 1;
      animation: slideUp 0.5s ease-out;
      border: 1px solid rgba(200, 200, 200, 0.4);
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .logo-section {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 40px;
      box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    }

    .system-title {
      font-size: 24px;
      font-weight: 700;
      color: #2d3748;
      margin-bottom: 5px;
      line-height: 1.2;
    }

    .system-subtitle {
      font-size: 14px;
      color: #718096;
      font-weight: 400;
    }

    .welcome-text {
      text-align: center;
      color: #4a5568;
      font-size: 15px;
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #2d3748;
      font-weight: 500;
      font-size: 14px;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #a0aec0;
      font-size: 18px;
    }

    input {
      width: 100%;
      padding: 14px 15px 14px 45px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 15px;
      transition: all 0.3s ease;
      font-family: 'Poppins', sans-serif;
    }

    input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
      margin-top: 10px;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .error-message {
      background: #fed7d7;
      color: #c53030;
      padding: 12px 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid #c53030;
      animation: shake 0.5s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-10px); }
      75% { transform: translateX(10px); }
    }

    .register-link {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
      color: #718096;
    }

    .register-link a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .register-link a:hover {
      color: #2563eb;
      text-decoration: underline;
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 25px 0;
      color: #a0aec0;
      font-size: 13px;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #e2e8f0;
    }

    .divider span {
      padding: 0 15px;
    }

    .features {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
      margin-top: 30px;
      padding-top: 30px;
      border-top: 1px solid #e2e8f0;
    }

    .feature-item {
      text-align: center;
      font-size: 12px;
      color: #718096;
    }

    .feature-icon {
      font-size: 24px;
      margin-bottom: 8px;
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 40px 30px;
      }

      .system-title {
        font-size: 20px;
      }

      .features {
        grid-template-columns: 1fr;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="logo-section">
      <div class="logo-icon">🚨</div>
      <h1 class="system-title">Itogon Disaster Alert System</h1>
      <p class="system-subtitle">Benguet Province</p>
    </div>

    <p class="welcome-text">Sign in to access the disaster management dashboard</p>

    <?php if (!empty($error)): ?>
      <div class="error-message">
        <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="form-group">
        <label for="username">Username or Email</label>
        <div class="input-wrapper">
          <span class="input-icon">👤</span>
          <input 
            type="text" 
            id="username" 
            name="username" 
            placeholder="Enter your username"
            required 
            autofocus 
          />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <span class="input-icon">🔒</span>
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Enter your password"
            required 
          />
        </div>
      </div>

      <button type="submit" class="btn-login">
        Sign In to Dashboard
      </button>
    </form>

    <div class="divider">
      <span>Itogon, Benguet</span>
    </div>

    <div class="register-link">
      Don't have an account? <a href="register.php">Create one here</a>
    </div>
    </div>
  </div>
</body>
</html>
