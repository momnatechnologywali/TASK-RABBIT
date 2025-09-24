<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tasker') { echo json_encode(['success' => false, 'error' => 'Unauthorized']); exit; }
 
$task_id = $_POST['task_id'];
$tasker_id = $_SESSION['user_id'];
$hourly_rate = $_POST['hourly_rate'];
$cover_letter = $_POST['cover_letter'];
 
try {
    executeQuery($pdo, "INSERT INTO applications (task_id, tasker_id, hourly_rate, cover_letter) VALUES (?, ?, ?, ?)", 
        [$task_id, $tasker_id, $hourly_rate, $cover_letter]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
