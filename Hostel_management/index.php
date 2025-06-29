<?php
session_start(); // Start session at the top to avoid headers error
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Professional Hostel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .hero-section {
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
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            backdrop-filter: blur(3px);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 1000px;
            padding: 3rem;
            animation: fadeInUp 1.2s ease-out;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.98);
        }
        @keyframes fadeInUp {
            0% { 
                opacity: 0; 
                transform: translateY(60px); 
            }
            100% { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        .btn-elegant {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        .btn-elegant::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-elegant:hover::before {
            left: 100%;
        }
        .btn-elegant:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
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
        .welcome-text {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle-text {
            color: #64748b;
            line-height: 1.8;
        }
        .feature-highlight {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-left: 4px solid #3b82f6;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            text-align: left;
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">Professional Management Solution</span>
        </div>
    </header>

    <!-- Main Content -->
    <section class="hero-section">
        <div class="hero-content">
            <div class="glass-card p-10 rounded-2xl shadow-2xl">
                <div class="mb-6">
                    <h2 class="text-5xl font-bold welcome-text mb-3">
                        Where Comfort Meets Excellence
                    </h2>
                    <p class="text-xl subtitle-text mb-4">
                        Transform your hostel operations with intelligent management
                    </p>
                </div>

                <div class="feature-highlight mb-6">
                    <h3 class="text-base font-semibold text-slate-700 mb-2">
                        <i class="fas fa-star text-yellow-500 mr-2"></i>
                        Why Choose Our System?
                    </h3>
                    <p class="text-sm text-slate-600">
                        Experience seamless student management, smart room allocation, and comprehensive visitor tracking.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-3 mb-8 text-sm">
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <i class="fas fa-users text-blue-600 text-lg mb-1"></i>
                        <p class="font-semibold text-blue-800 text-xs">Student Management</p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <i class="fas fa-bed text-green-600 text-lg mb-1"></i>
                        <p class="font-semibold text-green-800 text-xs">Smart Room Allocation</p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <i class="fas fa-clipboard-check text-purple-600 text-lg mb-1"></i>
                        <p class="font-semibold text-purple-800 text-xs">Visitor Tracking</p>
                    </div>
                </div>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="flex justify-center space-x-6">
                        <a href="login.php" class="bg-blue-600 text-white btn-elegant shadow-lg">
                            <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                        </a>
                        <a href="register_user.php" class="bg-green-600 text-white btn-elegant shadow-lg">
                            <i class="fas fa-user-plus mr-2"></i>Sign Up
                        </a>
                    </div>
                    <p class="text-slate-500 mt-4 text-sm">
                        Trusted by hostels worldwide • Secure • Reliable • Professional
                    </p>
                <?php else: ?>
                    <div class="mb-4">
                        <h3 class="text-xl font-semibold text-slate-700 mb-2">
                            Welcome back, Administrator!
                        </h3>
                        <p class="text-base text-slate-600 mb-4">
                            Your hostel management dashboard is ready.
                        </p>
                    </div>
                    <a href="logout.php" class="inline-block bg-red-600 text-white btn-elegant shadow-lg">
                        <i class="fas fa-sign-out-alt mr-2"></i>Secure Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>