<?php
session_start();
include 'db.php';
 
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];
 
    $stmt = executeQuery($pdo, "SELECT * FROM users WHERE email = ?", [$email]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Invalid credentials']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TaskRabbit Clone</title>
    <style>
        /* Reuse signup CSS for consistency */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; transition: border 0.3s; }
        input:focus { border-color: #007bff; outline: none; }
        .btn { width: 100%; background: #007bff; color: white; padding: 0.75rem; border: none; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #0056b3; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .error { color: red; text-align: center; }
        @media (max-width: 480px) { form { margin: 1rem; } }
    </style>
</head>
<body>
    <form id="loginForm">
        <h2>Login</h2>
        <input type="email" id="email" placeholder="Email" required>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit" class="btn">Login</button>
        <p style="text-align: center;"><a href="#" onclick="window.location.href='signup.php'" style="color: #007bff;">New? Signup</a></p>
        <div id="message"></div>
    </form>
 
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('login.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Login successful!');
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('message').innerHTML = `<p class="error">${data.error}</p>`;
                    }
                });
        });
    </script>
</body>
</html>
