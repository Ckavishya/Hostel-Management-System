<?php
ob_start();
session_start();
require 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT ID, name, email, password, role FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['ID'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'student') {
                    header("Location: student_dashboard.php");
                } elseif ($user['role'] == 'warden') {
                    header("Location: warden_dashboard.php");
                } elseif ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }

        $stmt->close();
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Hostel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .login-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(30, 41, 59, 0.85)), 
                        url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1469&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        .login-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            backdrop-filter: blur(3px);
            z-index: 1;
        }
        .login-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.98);
        }
        @keyframes fadeInUp {
            0% { 
                opacity: 0; 
                transform: translateY(40px); 
            }
            100% { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        .input-field {
            transition: all 0.3s ease;
        }
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.15);
        }
        .btn-login {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-login:hover::before {
            left: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.3);
        }
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 2rem 3rem;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .error-alert {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border-left: 4px solid #ef4444;
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-slate-300 hover:text-white transition-colors" onclick="console.log('Clicking home link'); return true;">
                <i class="fas fa-home mr-2"></i>Back to Home
            </a>
        </div>
    </header>

    <!-- Login Section -->
    <section class="login-section">
        <div class="login-content w-full max-w-md px-6">
            <div class="glass-card p-8 rounded-2xl shadow-2xl">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-white text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 mb-2">Welcome Back</h2>
                    <p class="text-slate-600">Sign in to access your dashboard</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-alert p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                            <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-slate-500"></i>Email Address
                        </label>
                        <input 
                            type="email" 
                            id="email"
                            name="email" 
                            required
                            class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white"
                            placeholder="Enter your email address"
                        >
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fas fa-key mr-2 text-slate-500"></i>Password
                        </label>
                        <input 
                            type="password" 
                            id="password"
                            name="password" 
                            required
                            class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white"
                            placeholder="Enter your password"
                        >
                    </div>

                    <button 
                        type="submit" 
                        class="btn-login w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-slate-500">
                        Secure login powered by advanced encryption
                    </p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Debug function to check what happens when clicking the home link
        document.addEventListener('DOMContentLoaded', function() {
            const homeLink = document.querySelector('a[href="index.php"]');
            if (homeLink) {
                homeLink.addEventListener('click', function(e) {
                    console.log('Home link clicked');
                    console.log('Current URL:', window.location.href);
                    console.log('Target URL:', this.href);
                    
                    // Check if index.php exists by making a simple request
                    fetch('index.php', {method: 'HEAD'})
                        .then(response => {
                            if (response.ok) {
                                console.log('index.php exists and is accessible');
                            } else {
                                console.log('index.php returned status:', response.status);
                                alert('index.php file not found or not accessible. Status: ' + response.status);
                                e.preventDefault();
                            }
                        })
                        .catch(error => {
                            console.log('Error checking index.php:', error);
                            alert('Cannot access index.php. Error: ' + error.message);
                            e.preventDefault();
                        });
                });
            }
        });
    </script>
</body>
</html>