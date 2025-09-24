<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
include 'db.php';
 
$task_id = $_GET['id'];
$stmt = executeQuery($pdo, "SELECT t.*, c.name as category, u.full_name as client FROM tasks t JOIN categories c ON t.category_id=c.id JOIN users u ON t.client_id=u.id WHERE t.id=?", [$task_id]);
$task = $stmt->fetch();
 
$applications = executeQuery($pdo, "SELECT a.*, u.full_name, u.rating FROM applications a JOIN users u ON a.tasker_id=u.id WHERE a.task_id=? AND a.status='applied'", [$task_id]);
 
if ($_POST && isset($_POST['book'])) {
    $tasker_id = $_POST['tasker_id'];
    $total = $_POST['total'];  // Dummy calc
    $payment_id = 'DUMMY_' . time();  // Dummy gateway
 
    executeQuery($pdo, "UPDATE applications SET status='accepted' WHERE task_id=? AND tasker_id=?", [$task_id, $tasker_id]);
    executeQuery($pdo, "INSERT INTO bookings (task_id, tasker_id, client_id, total_amount, payment_id) VALUES (?, ?, ?, ?, ?)", 
        [$task_id, $tasker_id, $_SESSION['user_id'], $total, $payment_id]);
    executeQuery($pdo, "UPDATE tasks SET status='in_progress' WHERE id=?", [$task_id]);
    echo json_encode(['success' => true]);
    exit;
}
 
if ($_POST && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    executeQuery($pdo, "UPDATE tasks SET status=? WHERE id=?", [$status, $task_id]);
    echo json_encode(['success' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - TaskRabbit Clone</title>
    <style>
        /* Detailed view */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 80px 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: #007bff; margin-bottom: 1rem; }
        .task-info { margin-bottom: 2rem; }
        .applicants { margin-top: 2rem; }
        .applicant { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #ddd; margin-bottom: 0.5rem; border-radius: 5px; }
        .book-btn { background: #ffc107; color: #212529; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; }
        .status-select { padding: 0.5rem; margin-left: 1rem; }
        @media (max-width: 480px) { .applicant { flex-direction: column; } }
    </style>
</head>
<body>
    <div class="container">
        <h2><?= htmlspecialchars($task['title']) ?></h2>
        <div class="task-info">
            <p><strong>Description:</strong> <?= htmlspecialchars($task['description']) ?></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($task['category']) ?></p>
            <p><strong>Budget:</strong> $<?= $task['budget'] ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($task['location']) ?></p>
            <p><strong>Due:</strong> <?= $task['due_date'] ?></p>
            <p><strong>Status:</strong> <?= ucfirst($task['status']) ?></p>
            <?php if ($_SESSION['role'] === 'client' && $task['status'] === 'pending'): ?>
                <div class="status-update">
                    <select class="status-select" id="status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                    <button class="btn" onclick="updateStatus(<?= $task['id'] ?>)">Update</button>
                </div>
            <?php endif; ?>
        </div>
 
        <?php if ($_SESSION['role'] === 'client'): ?>
            <div class="applicants">
                <h3>Applicants</h3>
                <?php while ($app = $applications->fetch()): ?>
                    <div class="applicant">
                        <div>
                            <strong><?= htmlspecialchars($app['full_name']) ?></strong> - Rating: <?= $app['rating'] ?> | Rate: $<?= $app['hourly_rate'] ?>/hr
                            <p><?= htmlspecialchars(substr($app['cover_letter'], 0, 50)) ?>...</p>
                        </div>
                        <button class="book-btn" onclick="bookTask(<?= $app['tasker_id'] ?>, <?= $task['budget'] ?>)">Book</button>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
 
        <a href="#" onclick="window.location.href='browse_tasks.php'">Back to Browse</a>
        <a href="#" onclick="window.location.href='messaging.php?task_id=<?= $task_id ?>'" style="margin-left: 1rem;">Message</a>
    </div>
 
    <script>
        function updateStatus(taskId) {
            const status = document.getElementById('status').value;
            fetch('task_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `update_status=1&status=${status}&id=${taskId}`
            }).then(() => {
                alert('Status updated!');
                window.location.reload();
            });
        }
 
        function bookTask(taskerId, budget) {
            const total = prompt('Total amount? (e.g., based on budget)', budget);
            if (total) {
                fetch('task_details.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `book=1&tasker_id=${taskerId}&total=${total}&id=<?= $task_id ?>`
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        alert('Booked! Dummy payment processed.');
                        window.location.href = 'index.php';
                    }
                });
            }
        }
    </script>
</body>
</html>
