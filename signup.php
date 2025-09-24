<?php
session_start();
include 'db.php';
 
if ($_POST) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];  // client or tasker
 
    try {
        executeQuery($pdo, "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)", [$username, $email, $password, $full_name, $role]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = $role;
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Signup failed: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - TaskRabbit Clone</title>
    <style>
        /* Same amazing CSS base, form-focused */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        input, select { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; transition: border 0.3s; }
        input:focus { border-color: #007bff; outline: none; }
        .btn { width: 100%; background: #007bff; color: white; padding: 0.75rem; border: none; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        .btn:hover { background: #0056b3; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .error { color: red; text-align: center; }
        @media (max-width: 480px) { form { margin: 1rem; } }
    </style>
</head>
<body>
    <form id="signupForm">
        <h2>Create Account</h2>
        <input type="text" id="username" placeholder="Username" required>
        <input type="email" id="email" placeholder="Email" required>
        <input type="password" id="password" placeholder="Password" required>
        <input type="text" id="full_name" placeholder="Full Name" required>
        <select id="role" required>
            <option value="client">Client (Post Tasks)</option>
            <option value="tasker">Tasker (Provide Services)</option>
        </select>
        <button type="submit" class="btn">Signup</button>
        <p style="text-align: center;"><a href="#" onclick="window.location.href='login.php'" style="color: #007bff;">Already have account? Login</a></p>
        <div id="message"></div>
    </form>
 
    <script>
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('signup.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Signup successful!');
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('message').innerHTML = `<p class="error">${data.error}</p>`;
                    }
                });
        });
    </script>
</body>
</html>
