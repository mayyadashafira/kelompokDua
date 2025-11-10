<?php
require_once 'config.php';
checkAuth();

// Handle actions
if (isset($_GET['action'])) {
    $user_id = getCurrentUserId();
    
    switch ($_GET['action']) {
        case 'delete':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user_id]);
                header('Location: dashboard.php?message=Tugas berhasil dihapus&type=success');
                exit;
            }
            break;
        case 'toggle_status':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("UPDATE tasks SET status = IF(status = 'pending', 'completed', 'pending') WHERE id = ? AND user_id = ?");
                $stmt->execute([$_GET['id'], $user_id]);
                header('Location: dashboard.php?message=Status tugas berhasil diubah&type=success');
                exit;
            }
            break;
    }
}

// Get tasks for current user
$user_id = getCurrentUserId();
$status_filter = $_GET['status'] ?? 'all';
$priority_filter = $_GET['priority'] ?? 'all';

$sql = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$user_id];

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if ($priority_filter !== 'all') {
    $sql .= " AND priority = ?";
    $params[] = $priority_filter;
}

$sql .= " ORDER BY deadline ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Calculate stats
$total_tasks = count($tasks);
$pending_tasks = array_filter($tasks, fn($task) => $task['status'] === 'pending');
$completed_tasks = array_filter($tasks, fn($task) => $task['status'] === 'completed');

// Urgent tasks (deadline dalam 3 hari)
$urgent_tasks = array_filter($tasks, function($task) {
    if ($task['status'] !== 'pending') return false;
    $deadline = new DateTime($task['deadline']);
    $today = new DateTime();
    $diff = $today->diff($deadline);
    return $diff->days <= 3 && !$diff->invert;
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Task Reminder</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
     <div class="header-content">
        <h1><i class="fas fa-tasks"></i> Task Reminder</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
        <div class="header-actions">
            <span class="user-info">
                <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                <small>(<?= htmlspecialchars($_SESSION['email']) ?>)</small>
            </span>
            <a href="auth/logout.php" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
</header>

    <!-- Flash Messages -->
    <?php if (isset($_GET['message'])): ?>
        <div class="flash-messages">
            <div class="alert alert-<?= $_GET['type'] ?? 'success' ?>">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <div class="user-info">
                    <div class="avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3><?= htmlspecialchars($_SESSION['full_name']) ?></h3>
                    <p>Status: Aktif</p>
                </div>

                <nav class="menu">
                    <a href="#" class="menu-item active" data-target="dashboard">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="#" class="menu-item" data-target="tasks">
                        <i class="fas fa-list"></i> Daftar Tugas
                    </a>
                    <a href="#" class="menu-item" data-target="add-task">
                        <i class="fas fa-plus"></i> Tambah Tugas
                    </a>
                </nav>
            </div>

            <div class="content">
                <!-- Dashboard Section -->
                <section id="dashboard" class="content-section active">
                    <h2>Dashboard</h2>
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= $total_tasks ?></h3>
                                <p>Total Tugas</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon pending">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= count($pending_tasks) ?></h3>
                                <p>Tugas Pending</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon completed">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= count($completed_tasks) ?></h3>
                                <p>Tugas Selesai</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon urgent">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?= count($urgent_tasks) ?></h3>
                                <p>Tugas Mendesak</p>
                            </div>
                        </div>
                    </div>

                    <div class="upcoming-tasks">
                        <h3>Tugas Mendatang</h3>
                        <div class="task-list">
                            <?php if (count($tasks) > 0): ?>
                                <?php 
                                $upcoming_tasks = array_filter($tasks, function($task) {
                                    return $task['status'] === 'pending';
                                });
                                $upcoming_tasks = array_slice($upcoming_tasks, 0, 5);
                                ?>
                                
                                <?php foreach ($upcoming_tasks as $task): ?>
                                    <div class="task-item <?= $task['priority'] ?>">
                                        <div class="task-header">
                                            <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
                                            <span class="task-priority priority-<?= $task['priority'] ?>">
                                                <?= ucfirst($task['priority']) ?>
                                            </span>
                                        </div>
                                        <div class="task-meta">
                                            <span><i class="fas fa-book"></i> <?= htmlspecialchars($task['course']) ?></span>
                                            <span><i class="fas fa-calendar"></i> <?= date('d F Y', strtotime($task['deadline'])) ?></span>
                                        </div>
                                        <p><?= htmlspecialchars($task['description'] ?: 'Tidak ada deskripsi') ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-clipboard-list fa-3x"></i>
                                    <h3>Belum Ada Tugas</h3>
                                    <p>Silakan tambah tugas pertama Anda dengan mengklik menu "Tambah Tugas"</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>

                <!-- Daftar Tugas Section -->
                <section id="tasks" class="content-section">
                    <div class="section-header">
                        <h2>Daftar Tugas</h2>
                        <div class="filters">
                            <form method="GET" class="filter-form">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Selesai</option>
                                </select>
                                <select name="priority" onchange="this.form.submit()">
                                    <option value="all" <?= $priority_filter === 'all' ? 'selected' : '' ?>>Semua Prioritas</option>
                                    <option value="low" <?= $priority_filter === 'low' ? 'selected' : '' ?>>Rendah</option>
                                    <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Sedang</option>
                                    <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>Tinggi</option>
                                </select>
                                <?php if ($status_filter !== 'all' || $priority_filter !== 'all'): ?>
                                    <a href="dashboard.php" class="btn btn-secondary">Reset Filter</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <div class="task-list">
                        <?php if (count($tasks) > 0): ?>
                            <?php foreach ($tasks as $task): ?>
                                <?php
                                $days_left = floor((strtotime($task['deadline']) - time()) / (60 * 60 * 24));
                                $days_text = $days_left < 0 ? 'Terlambat' : 
                                           ($days_left == 0 ? 'Hari ini' : 
                                           ($days_left == 1 ? 'Besok' : $days_left . ' hari lagi'));
                                ?>
                                <div class="task-item <?= $task['priority'] ?> <?= $task['status'] ?>">
                                    <div class="task-header">
                                        <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>
                                        <div>
                                            <span class="task-priority priority-<?= $task['priority'] ?>">
                                                <?= ucfirst($task['priority']) ?>
                                            </span>
                                            <span class="task-status status-<?= $task['status'] ?>">
                                                <?= $task['status'] === 'completed' ? 'Selesai' : 'Pending' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-meta">
                                        <span><i class="fas fa-book"></i> <?= htmlspecialchars($task['course']) ?></span>
                                        <span><i class="fas fa-calendar"></i> <?= date('d F Y', strtotime($task['deadline'])) ?></span>
                                        <span><i class="fas fa-clock"></i> <?= $days_text ?></span>
                                    </div>
                                    <p><?= htmlspecialchars($task['description'] ?: 'Tidak ada deskripsi') ?></p>
                                    <div class="task-actions">
                                        <a href="dashboard.php?action=toggle_status&id=<?= $task['id'] ?>" class="btn <?= $task['status'] === 'completed' ? 'btn-secondary' : 'btn-success' ?>">
                                            <i class="fas <?= $task['status'] === 'completed' ? 'fa-undo' : 'fa-check' ?>"></i>
                                            <?= $task['status'] === 'completed' ? 'Tandai Pending' : 'Tandai Selesai' ?>
                                        </a>
                                        <button class="btn btn-primary" onclick="openEditModal(
                                            <?= $task['id'] ?>,
                                            '<?= addslashes($task['title']) ?>',
                                            '<?= addslashes($task['description']) ?>',
                                            '<?= $task['deadline'] ?>',
                                            '<?= $task['priority'] ?>',
                                            '<?= addslashes($task['course']) ?>',
                                            '<?= $task['status'] ?>'
                                        )">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="dashboard.php?action=delete&id=<?= $task['id'] ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tasks fa-3x"></i>
                                <h3>Belum Ada Tugas</h3>
                                <p>Anda belum memiliki tugas. Mulai dengan menambahkan tugas pertama Anda!</p>
                                <a href="#" class="btn-primary" onclick="switchToAddTask()">
                                    <i class="fas fa-plus"></i> Tambah Tugas Pertama
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Tambah Tugas Section -->
                <section id="add-task" class="content-section">
                    <h2>Tambah Tugas Baru</h2>
                    <form action="api/tasks/create.php" method="POST" class="task-form">
                        <div class="form-group">
                            <label for="task-title">Judul Tugas *</label>
                            <input type="text" id="task-title" name="title" required placeholder="Masukkan judul tugas">
                        </div>
                        <div class="form-group">
                            <label for="task-description">Deskripsi</label>
                            <textarea id="task-description" name="description" rows="3" placeholder="Masukkan deskripsi tugas (opsional)"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="task-deadline">Deadline *</label>
                                <input type="date" id="task-deadline" name="deadline" required>
                            </div>
                            <div class="form-group">
                                <label for="task-priority">Prioritas *</label>
                                <select id="task-priority" name="priority" required>
                                    <option value="low">Rendah</option>
                                    <option value="medium" selected>Sedang</option>
                                    <option value="high">Tinggi</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="task-course">Mata Kuliah *</label>
                            <select id="task-course" name="course" required>
                                <option value="">Pilih Mata Kuliah</option>
                                <option value="Praktikum Sistem Operasi">Praktikum Sistem Operasi</option>
                                <option value="Sistem Operasi">Sistem Operasi</option>
                                <option value="Matematika Diskrit">Matematika Diskrit</option>
                                <option value="Aljabar Linear">Aljabar Linear</option>
                                <option value="Elektronika Dasar">Elektronika Dasar</option>
                                <option value="Praktikum Elektronika Dasar">Praktikum Elektronika Dasar</option>
                                <option value="Temu Kembali Informasi">Temu Kembali Informasi</option>
                                <option value="Data Mining">Data Mining</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-plus"></i> Tambah Tugas
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </div>

    <!-- Modal untuk edit tugas -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Tugas</h2>
            <form action="api/tasks/update.php" method="POST">
                <input type="hidden" id="edit-task-id" name="id">
                <div class="form-group">
                    <label for="edit-task-title">Judul Tugas</label>
                    <input type="text" id="edit-task-title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit-task-description">Deskripsi</label>
                    <textarea id="edit-task-description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-task-deadline">Deadline</label>
                        <input type="date" id="edit-task-deadline" name="deadline" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-task-priority">Prioritas</label>
                        <select id="edit-task-priority" name="priority" required>
                            <option value="low">Rendah</option>
                            <option value="medium">Sedang</option>
                            <option value="high">Tinggi</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit-task-course">Mata Kuliah</label>
                    <select id="edit-task-course" name="course" required>
                        <option value="Praktikum Sistem Operasi">Praktikum Sistem Operasi</option>
                        <option value="Sistem Operasi">Sistem Operasi</option>
                        <option value="Matematika Diskrit">Matematika Diskrit</option>
                        <option value="Aljabar Linear">Aljabar Linear</option>
                        <option value="Elektronika Dasar">Elektronika Dasar</option>
                        <option value="Praktikum Elektronika Dasar">Praktikum Elektronika Dasar</option>
                        <option value="Temu Kembali Informasi">Temu Kembali Informasi</option>
                        <option value="Data Mining">Data Mining</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit-task-status">Status</label>
                    <select id="edit-task-status" name="status">
                        <option value="pending">Pending</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script src="/js/script.js"></script>
    <script>
        function switchToAddTask() {
            // Switch to add task section
            document.querySelectorAll('.menu-item').forEach(item => item.classList.remove('active'));
            document.querySelectorAll('.content-section').forEach(section => section.classList.remove('active'));
            
            document.querySelector('[data-target="add-task"]').classList.add('active');
            document.getElementById('add-task').classList.add('active');
        }
    </script>
</body>
</html>