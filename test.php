<?php
require_once 'config.php';

echo "<h2>Testing User APIs with config.php</h2>";


echo "<h3>1. Testing Database Connection</h3>";
try {
    $pdo = db();
    echo "<p style='color: green;'>✓ Database connected via db() function</p>";
    

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users: " . $result['count'] . "</p>";
    

    $stmt = $pdo->query("SELECT id, username, role FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h4>Current Users:</h4>";
    echo "<ul>";
    foreach ($users as $user) {
        echo "<li>ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}


echo "<h3>2. Testing API File Includes</h3>";
$apiFiles = ['get_users.php', 'create_user.php', 'update_user.php', 'delete_user.php'];

foreach ($apiFiles as $file) {
    $path = 'api/' . $file;
    if (file_exists($path)) {
        
        $content = file_get_contents($path);
        if (strpos($content, 'require_once') !== false && 
            (strpos($content, 'config.php') !== false || strpos($content, '../config.php') !== false)) {
            echo "<p style='color: green;'>✓ $file includes config.php</p>";
        } else {
            echo "<p style='color: red;'>✗ $file does not include config.php</p>";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
        }
    } else {
        echo "<p style='color: red;'>✗ $file not found in api/ folder</p>";
    }
}


echo <<<HTML
<h3>3. Simulate API Call</h3>
<button onclick="simulateGetUsers()">Test get_users.php</button>
<div id="simulate-result" style="margin-top: 20px; padding: 10px; background: #f5f5f5;"></div>

<script>
async function simulateGetUsers() {
    const resultDiv = document.getElementById('simulate-result');
    resultDiv.innerHTML = 'Testing...';
    
    try {
        const res = await fetch('api/get_users.php');
        const data = await res.json();
        
        if (data.success) {
            resultDiv.innerHTML = `
                <p style="color: green;">✓ API working!</p>
                <p>Found \${data.count || data.data.length} users</p>
                <pre>\${JSON.stringify(data.data, null, 2)}</pre>
            `;
        } else {
            resultDiv.innerHTML = `
                <p style="color: red;">✗ API error: \${data.message}</p>
                <pre>\${JSON.stringify(data, null, 2)}</pre>
            `;
        }
    } catch (err) {
        resultDiv.innerHTML = `<p style="color: red;">✗ Network error: \${err.message}</p>`;
    }
}
</script>
HTML;
?>