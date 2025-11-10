<?php
// fix_database.php
$host = 'localhost';
$username = 'root';
$password = '';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Database</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>
    <h1>🔧 Fixing Database Structure</h1>";

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS task_reminder");
    $pdo->exec("USE task_reminder");
    
    echo "<div class='info'>✅ Connected to database 'task_reminder'</div>";
    
    // Drop existing tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS tasks");
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    echo "<div class='info'>🗑️ Dropped existing tables</div>";
    
    // Create users table
    $pdo->exec("
        CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✅ Table 'users' created successfully</div>";
    
    // Create tasks table
    $pdo->exec("
        CREATE TABLE tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            deadline DATE NOT NULL,
            priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
            course VARCHAR(100) NOT NULL,
            status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "<div class='success'>✅ Table 'tasks' created successfully</div>";
    
    // Verify the structure
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>📊 Tables in database: " . implode(', ', $tables) . "</div>";
    
    // Check columns in tasks table
    $columns = $pdo->query("DESCRIBE tasks")->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>📋 Columns in 'tasks' table: " . implode(', ', $columns) . "</div>";
    
    echo "<div class='success' style='background: #d4edda; color: #155724;'>
            <h2>🎉 Database Fixed Successfully!</h2>
            <p>All tables have been created with the correct structure.</p>
        </div>";
    
    echo "<a href='index.php' class='btn'>🚀 Go to Login Page</a>";
    echo "<a href='register.php' class='btn' style='background: #28a745;'>📝 Go to Registration</a>";
    
} catch (PDOException $e) {
    echo "<div class='error'>
            <h2>❌ Error Fixing Database</h2>
            <p><strong>Error:</strong> " . $e->getMessage() . "</p>
            <p>Please check your MySQL configuration and try again.</p>
        </div>";
}

echo "</body></html>";
?>