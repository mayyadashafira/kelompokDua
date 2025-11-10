<?php
// api/tasks/delete.php
require_once __DIR__ . '/../../config.php'; // Fix path

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || isset($_GET['id'])) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode([
            'success' => false,
            'error' => 'ID tugas tidak valid'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tugas berhasil dihapus'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Method tidak diizinkan'
    ]);
}
?>