<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'task_reminder';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Simple check - if connection works, assume tables are correct
    $pdo->query("SELECT 1")->fetch();
    
} catch(PDOException $e) {
    die("
        <div style='padding: 20px; background: #ffebee; color: #c62828; border-radius: 10px; margin: 20px;'>
            <h3>Database Connection Error</h3>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            <p>Please run the database setup:</p>
            <a href='fix_database.php' style='background: #c62828; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>
                Run Database Setup
            </a>
        </div>
    ");
}

// Authentication check function
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>