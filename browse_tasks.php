<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
include 'db.php';
 
// Filters: category, search, sort by budget/status
$where = "status = 'pending'";
$params = [];
if (isset($_GET['category'])) { $where .= " AND category_id = ?"; $params[] = $_GET['category']; }
if (isset($_GET['search'])) { $where .= " AND title LIKE ?"; $params[] = "%{$_GET['search']}%"; }
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'budget DESC';
 
$tasks = executeQuery($pdo, "SELECT t.*, c.name as category_name, u.full_name as client_name FROM tasks t JOIN categories c ON t.category_id = c.id JOIN users u ON t.client_id = u.id WHERE $where ORDER BY $sort", $params);
 
$categories = executeQuery($pdo, "SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Tasks - TaskRabbit Clone</title>
    <style>
        /* Search/filter heavy, card grid */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 80px 2rem 2rem; }
        .filters { background: white; padding: 1rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .filters input, select { padding: 0.5rem; margin-right: 1rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #007bff; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; margin-right: 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .card:hover { transform: translateY(-3px); }
        .card h3 { color: #007bff; margin-bottom: 0.5rem; }
        .apply-btn { background: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; }
        h2 { margin-bottom: 1rem; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } .filters { flex-direction: column; } }
    </style>
</head>
<body>
    <h2>Browse Available Tasks</h2>
    <div class="filters">
        <input type="text" id="search" placeholder="Search tasks..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select id="category">
            <option value="">All Categories</option>
            <?php while ($cat = $categories->fetch()): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
            <?php endwhile; ?>
        </select>
        <select id="sort">
            <option value="budget DESC" <?= ($_GET['sort'] ?? '') == 'budget DESC' ? 'selected' : '' ?>>Highest Budget</option>
            <option value="created_at DESC">Newest</option>
        </select>
        <button class="btn" onclick="applyFilters()">Filter</button>
        <?php if ($_SESSION['role'] === 'client'): ?>
            <a href="#" onclick="window.location.href='post_task.php'" class="btn">Post New Task</a>
        <?php endif; ?>
    </div>
 
    <div class="grid" id="tasksGrid">
        <?php while ($task = $tasks->fetch()): ?>
            <div class="card">
                <h3><?= htmlspecialchars($task['title']) ?></h3>
                <p><strong>Category:</strong> <?= htmlspecialchars($task['category_name']) ?></p>
                <p><strong>Client:</strong> <?= htmlspecialchars($task['client_name']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($task['location']) ?></p>
                <p><strong>Budget:</strong> $<?= $task['budget'] ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars(substr($task['description'], 0, 100)) ?>...</p>
                <p><strong>Status:</strong> <?= ucfirst($task['status']) ?></p>
                <?php if ($_SESSION['role'] === 'tasker' && $task['status'] === 'pending'): ?>
                    <button class="apply-btn" onclick="applyToTask(<?= $task['id'] ?>)">Apply</button>
                <?php endif; ?>
                <a href="#" onclick="window.location.href='task_details.php?id=<?= $task['id'] ?>'">View Details</a>
            </div>
        <?php endwhile; ?>
    </div>
 
    <script>
        function applyFilters() {
            const search = document.getElementById('search').value;
            const category = document.getElementById('category').value;
            const sort = document.getElementById('sort').value;
            let url = 'browse_tasks.php?';
            if (search) url += 'search=' + encodeURIComponent(search) + '&';
            if (category) url += 'category=' + category + '&';
            url += 'sort=' + sort;
            window.location.href = url;
        }
 
        function applyToTask(taskId) {
            const rate = prompt('Your hourly rate?');
            const letter = prompt('Cover letter?');
            if (rate && letter) {
                fetch('apply_task.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `task_id=${taskId}&hourly_rate=${rate}&cover_letter=${encodeURIComponent(letter)}`
                }).then(res => res.json()).then(data => {
                    if (data.success) alert('Applied!'); else alert('Apply failed.');
                    window.location.reload();
                });
            }
        }
    </script>
</body>
</html>
