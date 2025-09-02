<?php
session_start();
require_once 'config.php';


if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Password salah!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <style>
        :root {
            --primary: #FF6B35;
            --primary-light: #FF9E6D;
            --secondary: #2EC4B6;
            --accent: #FF9F1C;
            --dark: #1A1A2E;
            --light: #F8F9FA;
            --success: #28a745;
            --danger: #dc3545;
            --glass: rgba(255, 255, 255, 0.1);
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 20px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 15px 35px rgba(0, 0, 0, 0.15);
            --border-radius: 16px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: cardEntrance 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        
        @keyframes cardEntrance {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .login-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
        }
        
        .login-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
        }
        
        .login-card h1:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 3px;
        }
        
        .login-card h1 i {
            color: var(--primary);
            margin-right: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            background: rgba(220, 53, 69, 0.15);
            color: var(--danger);
            border: 1px solid rgba(220, 53, 69, 0.2);
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
        
        @keyframes shake {
            10%, 90% { transform: translateX(-1px); }
            20%, 80% { transform: translateX(2px); }
            30%, 50%, 70% { transform: translateX(-3px); }
            40%, 60% { transform: translateX(3px); }
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #eaeaea;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
            transform: translateY(-2px);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            z-index: 1;
            width: 100%;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .btn:hover:before {
            width: 100%;
        }
        
        .btn-primary {
            background: linear-gradient(45deg, var(--primary), var(--accent));
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.6);
        }
        
        .login-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
        
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .login-footer a:hover {
            color: var(--accent);
            transform: translateX(-5px);
        }
        
        .login-footer a i {
            margin-right: 0.5rem;
        }
        
        /* Particles Background */
        .particles-container {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            opacity: 0.3;
        }
        
        @media (max-width: 576px) {
            .login-card {
                padding: 2rem 1.5rem;
            }
            
            .login-card h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="particles-container" id="particles"></div>
    
    <div class="login-container">
        <div class="login-card">
            <h1><i class="fas fa-lock"></i> Admin Login</h1>
            
            <?php if ($error): ?>
            <div class="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="login-footer">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    
    <script>
        // Create particles background
        function createParticles() {
            const container = document.getElementById('particles');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 5 and 15px
                const size = Math.random() * 10 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                
                // Random animation
                const animDuration = Math.random() * 10 + 10;
                particle.style.animation = `float ${animDuration}s ease-in-out infinite`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                
                container.appendChild(particle);
            }
        }
        
        // Initialize particles when page loads
        window.addEventListener('load', createParticles);
    </script>
</body>
</html>