<?php
include 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $role = 'user';


  $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
  $check->bind_param("s", $username);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $error = "That email is already registered. Please log in instead.";
  } else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $role);

    if ($stmt->execute()) {
      $success = "Registration successful! You can now log in.";
    } else {
      $error = "An unexpected error occurred. Please try again.";
    }

    $stmt->close();
  }
  $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | Itogon Disaster Alert System</title>
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
        url('assets/images/register-background.jpg');
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

    .register-container {
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
      background: white;
    }

    input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .btn-register {
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

    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
    }

    .btn-register:active {
      transform: translateY(0);
    }

    .btn-register:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 12px 15px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid #28a745;
      animation: slideDown 0.5s ease;
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

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-link {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
      color: #718096;
    }

    .login-link a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .login-link a:hover {
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

    small {
      display: block;
      margin-top: 4px;
      font-size: 13px;
    }

    small#email-message {
      margin-top: 6px;
      font-weight: 500;
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
      .register-container {
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
  <div class="register-container">
    <div class="logo-section">
      <div class="logo-icon">🚨</div>
      <h1 class="system-title">Itogon Disaster Alert System</h1>
      <p class="system-subtitle">Benguet Province</p>
    </div>

    <p class="welcome-text">Create your account to access disaster reports</p>

    <?php if (!empty($success)): ?>
      <div class="success-message">
        <strong>✅ Success!</strong> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="error-message">
        <strong>⚠️ Error:</strong> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="register-form" autocomplete="off">
      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrapper">
          <span class="input-icon">📧</span>
          <input 
            type="email" 
            id="email" 
            name="username" 
            placeholder="Enter your email"
            required 
            autofocus 
          />
        </div>
        <small id="email-message"></small>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrapper">
          <span class="input-icon">🔒</span>
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Create a strong password"
            required 
          />
        </div>
      </div>

      <button type="submit" id="register-btn" class="btn-register">
        Create Account
      </button>
    </form>

    <div class="divider">
      <span>Already have an account?</span>
    </div>

    <div class="login-link">
      <a href="login.php">Sign in here</a>
    </div>
    </div>
  </div>

  <script>
  const emailField = document.getElementById('email');
  const emailMsg = document.getElementById('email-message');
  const registerBtn = document.getElementById('register-btn');

  emailField.addEventListener('input', async () => {
    const email = emailField.value.trim();

    if (email.length === 0) {
      emailMsg.textContent = '';
      emailMsg.style.color = '';
      registerBtn.disabled = false;
      return;
    }


    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      emailMsg.textContent = '⚠️ Please enter a valid email address.';
      emailMsg.style.color = '#d9534f';
      registerBtn.disabled = true;
      return;
    }

    try {
      const response = await fetch(`check_email.php?email=${encodeURIComponent(email)}`);
      const data = await response.json();

      if (data.exists) {
        emailMsg.textContent = '⚠️ This email is already registered.';
        emailMsg.style.color = '#d9534f';
        registerBtn.disabled = true;
      } else {
        emailMsg.textContent = '✅ This email is available.';
        emailMsg.style.color = '#28a745';
        registerBtn.disabled = false;
      }
    } catch (err) {
      emailMsg.textContent = 'Error checking email.';
      emailMsg.style.color = '#d9534f';
    }
  });
  </script>
</body>
</html>