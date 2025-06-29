<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Warden Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f8fafc, #e0e7ff);
        }
        .dashboard-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding-top: 80px;
            padding-bottom: 20px;
        }
        .dashboard-content {
            position: relative;
            z-index: 2;
            max-width: 1000px;
            padding: 2rem;
            animation: fadeInUp 1.2s ease-out;
            width: 100%;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
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
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff;
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
        .btn-student { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .btn-student:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .btn-room { background: linear-gradient(135deg, #10b981, #34d399); }
        .btn-room:hover { background: linear-gradient(135deg, #059669, #10b981); }
        .btn-system { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .btn-system:hover { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .btn-logout { background: linear-gradient(135deg, #ef4444, #f87171); }
        .btn-logout:hover { background: linear-gradient(135deg, #dc2626, #ef4444); }
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
            line-height: 1.6;
        }
        .tab-container {
            display: flex;
            gap: 1rem;
            padding: 0;
            justify-content: space-between;
        }
        .tab-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            flex: 1;
            min-width: 180px;
            max-width: 240px;
        }
        .tab-group h3 {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        .tab-group h3 i {
            margin-right: 0.4rem;
            color: #3b82f6;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1.5rem;
            }
            .glass-card {
                padding: 1rem;
            }
            .header {
                padding: 1.5rem 2rem;
            }
            .header h1 {
                font-size: 1.75rem;
            }
            .tab-container {
                flex-direction: column;
                gap: 1rem;
            }
            .tab-group {
                min-width: 100%;
                max-width: 100%;
            }
            .btn-elegant {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
            .welcome-text {
                font-size: 2.5rem;
            }
            .subtitle-text {
                font-size: 1rem;
            }
            .tab-group h3 {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">Warden Dashboard</span>
        </div>
    </header>

    <!-- Main Content -->
    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="glass-card">
                <div class="mb-4 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        Warden Control Panel
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! Manage your hostel operations with precision and efficiency.
                    </p>
                </div>

                <div class="tab-container">
                    <div class="tab-group">
                        <h3><i class="fas fa-users"></i>Student Management</h3>
                        <a href="add_student.php" class="btn-elegant btn-student shadow-lg">
                            <i class="fas fa-user-plus"></i>
                            <span>Add Student</span>
                        </a>
                        <a href="view_students.php" class="btn-elegant btn-student shadow-lg">
                            <i class="fas fa-users"></i>
                            <span>View Students</span>
                        </a>
                        <a href="checkout_student.php" class="btn-elegant btn-student shadow-lg">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Check-Out</span>
                        </a>
                    </div>
                    
                    <div class="tab-group">
                        <h3><i class="fas fa-bed"></i>Room & Visitor Management</h3>
                        <a href="add_room.php" class="btn-elegant btn-room shadow-lg">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add Room</span>
                        </a>
                        <a href="view_rooms.php" class="btn-elegant btn-room shadow-lg">
                            <i class="fas fa-home"></i>
                            <span>View Rooms</span>
                        </a>
                        <a href="add_visitor.php" class="btn-elegant btn-room shadow-lg">
                            <i class="fas fa-user-tie"></i>
                            <span>Add Visitor</span>
                        </a>
                        <a href="view_visitors.php" class="btn-elegant btn-room shadow-lg">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Visitor Log</span>
                        </a>
                    </div>
                    
                    <div class="tab-group">
                        <h3><i class="fas fa-cogs"></i>System & Support</h3>
                        <a href="view_complaints.php" class="btn-elegant btn-system shadow-lg">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Complaints</span>
                        </a>
                        <a href="logout.php" class="btn-elegant btn-logout shadow-lg">
                            <i class="fas fa-power-off"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>

                <div class="mt-4 pt-2 border-t border-gray-200">
                    <div class="text-center">
                        <p class="text-slate-500 text-sm mb-2">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Trusted by hostels worldwide • Secure • Reliable • Professional
                        </p>
                        <div class="flex justify-center items-center space-x-4 text-xs text-slate-500">
                            <span><i class="fas fa-clock mr-1"></i>Last login: <?php echo date('M d, Y - H:i'); ?></span>
                            <span>•</span>
                            <span><i class="fas fa-user-shield mr-1"></i>Role: Warden</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>