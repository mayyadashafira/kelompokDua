<?php
// api/tasks/create.php
require_once __DIR__ . '/../../config.php'; // Fix path
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $course = $_POST['course'] ?? '';
    $user_id = getCurrentUserId();
    
    // Validation
    if (empty($title) || empty($deadline) || empty($course)) {
        header('Location: ../../dashboard.php?message=Semua field wajib diisi&type=danger');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO tasks (user_id, title, description, deadline, priority, course) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $title, $description, $deadline, $priority, $course]);
        
        header('Location: ../../dashboard.php?message=Tugas berhasil ditambahkan&type=success');
        exit;
    } catch (PDOException $e) {
        header('Location: ../../dashboard.php?message=Error: ' . $e->getMessage() . '&type=danger');
        exit;
    }
} else {
    header('Location: ../../dashboard.php');
    exit;
}
?>