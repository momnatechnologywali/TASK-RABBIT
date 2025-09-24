<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') { header('Location: login.php'); exit; }
include 'db.php';
 
$client_id = $_SESSION['user_id'];
$categories = executeQuery($pdo, "SELECT * FROM categories");
 
if ($_POST) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $budget = $_POST['budget'];
    $location = $_POST['location'];
    $category_id = $_POST['category_id'];
    $due_date = $_POST['due_date'];
 
    executeQuery($pdo, "INSERT INTO tasks (title, description, budget, location, category_id, client_id, due_date) VALUES (?, ?, ?, ?, ?, ?, ?)", 
        [$title, $description, $budget, $location, $category_id, $client_id, $due_date]);
    echo json_encode(['success' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Task - TaskRabbit Clone</title>
    <style>
        /* Form-focused, vibrant */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 80px 2rem; }
        form { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 600px; margin: 0 auto; }
        input, textarea, select { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; background: #28a745; color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1.1rem; }
        h2 { text-align: center; margin-bottom: 1.5rem; }
        @media (max-width: 480px) { body { padding: 80px 1rem; } }
    </style>
</head>
<body>
    <form id="postForm">
        <h2>Post a New Task</h2>
        <input type="text" id="title" placeholder="Task Title" required>
        <textarea id="description" placeholder="Description" required rows="4"></textarea>
        <input type="number" id="budget" placeholder="Budget ($)" step="0.01" required>
        <input type="text" id="location" placeholder="Location" required>
        <select id="category_id" required>
            <option value="">Select Category</option>
            <?php while ($cat = $categories->fetch()): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="date" id="due_date" required>
        <button type="submit" class="btn">Post Task</button>
        <div id="message"></div>
    </form>
 
    <script>
        document.getElementById('postForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('post_task.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Task posted!');
                        window.location.href = 'index.php';
                    } else {
                        document.getElementById('message').innerHTML = '<p style="color:red;">Error posting task.</p>';
                    }
                });
        });
    </script>
</body>
</html>
