<?php
// api/tasks/update.php
require_once __DIR__ . '/../../config.php'; // Fix path
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $course = $_POST['course'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $user_id = getCurrentUserId();
    
    // Validation
    if (empty($id) || empty($title) || empty($deadline) || empty($course)) {
        header('Location: ../../dashboard.php?message=Semua field wajib diisi&type=danger');
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE tasks 
            SET title = ?, description = ?, deadline = ?, priority = ?, course = ?, status = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$title, $description, $deadline, $priority, $course, $status, $id, $user_id]);
        
        header('Location: ../../dashboard.php?message=Tugas berhasil diperbarui&type=success');
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