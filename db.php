<?php
// Secure PDO connection - Update host if not localhost
$host = 'localhost';  // Change if remote (e.g., 'us-cdbr-east-06.cleardb.net')
$dbname = 'dbioo5uovh6m5r';
$username = 'um4u5gpwc3dwc';
$password = 'neqhgxo10ioe';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Helper function for prepared statements
function executeQuery($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
?>
