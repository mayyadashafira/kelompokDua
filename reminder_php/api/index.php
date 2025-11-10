<?php
require_once 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    } else {
        $error = 'Harap isi semua field!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Task Reminder</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #d8bfd8 0%, #c89ac8 100%);
        }
        
        .login-box {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #6a0572;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
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
        
        .btn-login {
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
        
        .btn-login:hover {
            background: #8e24aa;
        }
        
        .btn-register {
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
        
        .btn-register:hover {
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><i class="fas fa-tasks"></i> TASK REMINDER </h1>
                <p> Silakan login terlebih dahulu! </p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <a href="register.php" class="btn-register">
                <i class="fas fa-user-plus"></i> Daftar Akun Baru
            </a>
        </div>
    </div>
</body>
</html>