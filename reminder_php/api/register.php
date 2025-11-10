<?php
require_once 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle registration form
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'Harap isi semua field!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $stmt->execute([$email, $username]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                $error = 'Email atau username sudah digunakan!';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO users (full_name, username, email, password) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$full_name, $username, $email, $hashed_password]);
                
                $success = 'Akun berhasil dibuat! Silakan login.';
                
                // Clear form
                $full_name = $username = $email = '';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Task Reminder</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #d8bfd8 0%, #c89ac8 100%);
        }
        
        .register-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            color: #6a0572;
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: #8e24aa;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #6a0572;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e1bee7;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #d8bfd8;
        }
        
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: #6a0572;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-bottom: 1rem;
        }
        
        .btn-register:hover {
            background: #8e24aa;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: transparent;
            color: #6a0572;
            border: 2px solid #6a0572;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .btn-login:hover {
            background: #6a0572;
            color: white;
        }
        
        .error-message {
            background: #ffebee;
            color: #d32f2f;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #ffcdd2;
        }
        
        .success-message {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #c8e6c9;
        }
        
        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <div class="register-header">
                <h1><i class="fas fa-user-plus"></i> Daftar Akun</h1>
                <p>jom buat akun baru hehe</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name ?? '') ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-tag"></i> Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="password-requirements">Minimal 6 karakter</div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>

            <a href="index.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Sudah punya akun? Login
            </a>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Password tidak cocok');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
</body>
</html>