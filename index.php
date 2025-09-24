<?php
session_start();
include 'db.php';
 
// Fetch popular services (categories) and top taskers (users with rating >4)
$categories = executeQuery($pdo, "SELECT * FROM categories LIMIT 4");
$top_taskers = executeQuery($pdo, "SELECT * FROM users WHERE role='tasker' AND rating >=4.0 ORDER BY rating DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskRabbit Clone - Get Things Done</title>
    <style>
        /* Amazing CSS: Modern, responsive, TaskRabbit-inspired */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
        header { background: linear-gradient(90deg, #007bff, #0056b3); color: white; padding: 1rem 0; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        nav ul { display: flex; list-style: none; }
        nav li { margin-left: 2rem; }
        nav a { color: white; text-decoration: none; transition: color 0.3s; }
        nav a:hover { color: #ffd700; }
        .btn { background: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; transition: background 0.3s; text-decoration: none; display: inline-block; }
        .btn:hover { background: #218838; }
        .hero { background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%23f0f0f0" width="1200" height="600"/><circle fill="%23007bff" cx="200" cy="200" r="50"/><circle fill="%2328a745" cx="800" cy="300" r="80"/></svg>') no-repeat center/cover; height: 60vh; display: flex; align-items: center; justify-content: center; text-align: center; color: white; margin-top: 70px; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); animation: fadeIn 1s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .services { max-width: 1200px; margin: 3rem auto; padding: 0 2rem; }
        .services h2 { text-align: center; margin-bottom: 2rem; font-size: 2.5rem; color: #007bff; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; }
        .card { background: white; border-radius: 10px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .taskers { background: #f8f9fa; padding: 3rem 0; }
        .taskers h2 { text-align: center; margin-bottom: 2rem; }
        .tasker-card { display: flex; align-items: center; margin-bottom: 1rem; padding: 1rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .tasker-card img { width: 60px; height: 60px; border-radius: 50%; margin-right: 1rem; }
        footer { background: #343a40; color: white; text-align: center; padding: 2rem; margin-top: 3rem; }
        @media (max-width: 768px) { .hero h1 { font-size: 2rem; } nav ul { flex-direction: column; } .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">TaskRabbit Clone</div>
            <ul>
                <li><a href="#" onclick="window.location.href='index.php'">Home</a></li>
                <li><a href="#" onclick="window.location.href='browse_tasks.php'">Browse Tasks</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="#" onclick="window.location.href='profile.php'">Profile</a></li>
                    <li><a href="#" onclick="logout()">Logout</a></li>
                <?php else: ?>
                    <li><a href="#" onclick="window.location.href='login.php'">Login</a></li>
                    <li><a href="#" onclick="window.location.href='signup.php'">Signup</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
 
    <section class="hero">
        <div>
            <h1>Get Things Done Locally</h1>
            <p>Post tasks, hire pros, done!</p>
            <a href="#" onclick="window.location.href='post_task.php'" class="btn">Post a Task</a>
        </div>
    </section>
 
    <section class="services">
        <h2>Popular Services</h2>
        <div class="grid">
            <?php while ($cat = $categories->fetch()): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    <p><?= htmlspecialchars($cat['description']) ?></p>
                    <a href="#" onclick="window.location.href='browse_tasks.php?category=<?= $cat['id'] ?>'" class="btn">Browse</a>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
 
    <section class="taskers">
        <div class="services">
            <h2>Top Taskers</h2>
            <div class="grid">
                <?php while ($tasker = $top_taskers->fetch()): ?>
                    <div class="tasker-card">
                        <img src="<?= htmlspecialchars($tasker['profile_image']) ?>" alt="Profile">
                        <div>
                            <h4><?= htmlspecialchars($tasker['full_name']) ?></h4>
                            <p>Rating: <?= $tasker['rating'] ?>/5 | <?= htmlspecialchars($tasker['location']) ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
 
    <footer>
        <p>&copy; 2025 TaskRabbit Clone. All rights reserved.</p>
    </footer>
 
    <script>
        function logout() {
            if (confirm('Logout?')) {
                fetch('logout.php', { method: 'POST' }).then(() => window.location.href = 'index.php');
            }
        }
    </script>
</body>
</html>
