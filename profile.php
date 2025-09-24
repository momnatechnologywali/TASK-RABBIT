<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$stmt = executeQuery($pdo, "SELECT * FROM users WHERE id = ?", [$user_id]);
$user = $stmt->fetch();
 
if ($_POST && isset($_POST['update'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $bio = $_POST['bio'];
    executeQuery($pdo, "UPDATE users SET full_name=?, phone=?, location=?, bio=? WHERE id=?", [$full_name, $phone, $location, $bio, $user_id]);
    $user = array_merge($user, $_POST);  // Refresh
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - TaskRabbit Clone</title>
    <style>
        /* Profile-specific: Clean, editable form */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fa; padding: 80px 2rem 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 1rem; color: #007bff; }
        form { display: flex; flex-direction: column; }
        input, textarea { padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #28a745; color: white; padding: 0.75rem; border: none; border-radius: 5px; cursor: pointer; }
        .stats { display: flex; justify-content: space-around; margin-top: 2rem; }
        .stat { text-align: center; }
        a { color: #007bff; text-decoration: none; margin-top: 1rem; display: block; }
        @media (max-width: 480px) { .container { padding: 1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Profile</h2>
        <form method="POST">
            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" placeholder="Full Name" required>
            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="Phone">
            <input type="text" name="location" value="<?= htmlspecialchars($user['location']) ?>" placeholder="Location" required>
            <textarea name="bio" placeholder="Bio"><?= htmlspecialchars($user['bio']) ?></textarea>
            <button type="submit" name="update" class="btn">Update Profile</button>
        </form>
        <div class="stats">
            <div class="stat">
                <h3><?= $user['rating'] ?>/5</h3>
                <p>Rating</p>
            </div>
            <div class="stat">
                <h3><?= $_SESSION['role'] ?></h3>
                <p>Role</p>
            </div>
        </div>
        <a href="#" onclick="window.location.href='index.php'">Back to Home</a>
    </div>
</body>
</html>
