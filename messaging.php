<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;
 
// Fetch messages for this task/conversation (simplified: between client and tasker on task)
if ($task_id) {
    $task = executeQuery($pdo, "SELECT client_id FROM tasks WHERE id=?", [$task_id])->fetch();
    $other_id = $task['client_id'] === $user_id ? executeQuery($pdo, "SELECT tasker_id FROM applications WHERE task_id=? LIMIT 1", [$task_id])->fetch()['tasker_id'] : $task['client_id'];
} else {
    $other_id = $_GET['other_id'] ?? null;  // Fallback
}
 
$messages = executeQuery($pdo, "SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY sent_at", 
    [$user_id, $other_id, $other_id, $user_id]);
 
if ($_POST && isset($_POST['send'])) {
    $message = $_POST['message'];
    executeQuery($pdo, "INSERT INTO messages (sender_id, receiver_id, task_id, message) VALUES (?, ?, ?, ?)", 
        [$user_id, $other_id, $task_id, $message]);
    echo json_encode(['success' => true]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging - TaskRabbit Clone</title>
    <style>
        /* Chat-like UI */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #e9ecef; padding: 80px 2rem; height: 100vh; display: flex; flex-direction: column; }
        .chat-container { flex: 1; background: white; border-radius: 10px; padding: 1rem; overflow-y: auto; margin-bottom: 1rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .message { margin-bottom: 1rem; padding: 0.75rem; border-radius: 10px; max-width: 70%; }
        .sent { background: #007bff; color: white; margin-left: auto; }
        .received { background: #f8f9fa; color: #333; }
        form { display: flex; }
        input[type="text"] { flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 20px 0 0 20px; }
        .btn { background: #007bff; color: white; border: none; padding: 0.75rem 1rem; border-radius: 0 20px 20px 0; cursor: pointer; }
        h2 { margin-bottom: 1rem; }
        @media (max-width: 480px) { body { padding: 80px 1rem; } }
    </style>
</head>
<body>
    <h2>Messages</h2>
    <div class="chat-container" id="messages">
        <?php while ($msg = $messages->fetch()): ?>
            <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                <?= htmlspecialchars($msg['message']) ?> <small style="opacity: 0.7;">- <?= $msg['sent_at'] ?></small>
            </div>
        <?php endwhile; ?>
    </div>
    <form id="messageForm">
        <input type="text" id="message" placeholder="Type a message..." required>
        <button type="submit" class="btn">Send</button>
    </form>
 
    <script>
        const taskId = <?= $task_id ?? 'null' ?>;
        const otherId = <?= $other_id ?? 'null' ?>;
 
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = document.getElementById('message').value;
            fetch('messaging.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `send=1&message=${encodeURIComponent(msg)}&task_id=${taskId}&other_id=${otherId}`
            }).then(() => {
                document.getElementById('message').value = '';
                loadMessages();
            });
        });
 
        function loadMessages() {
            // Simplified: Reload page for demo
            window.location.reload();
        }
        setInterval(loadMessages, 5000);  // Poll every 5s
    </script>
</body>
</html>
