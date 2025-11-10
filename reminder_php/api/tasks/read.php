<?php
// api/tasks/read.php
require_once __DIR__ . '/../../config.php'; // Fix path

header('Content-Type: application/json');

try {
    $sql = "SELECT * FROM tasks ORDER BY deadline ASC";
    
    // Filter parameters
    if (isset($_GET['status']) && $_GET['status'] !== 'all') {
        $sql .= " WHERE status = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['status']]);
    } else if (isset($_GET['priority']) && $_GET['priority'] !== 'all') {
        $sql .= " WHERE priority = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['priority']]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    $tasks = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $tasks,
        'total' => count($tasks)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>