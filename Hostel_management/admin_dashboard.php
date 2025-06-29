<?php
session_start();
include 'db_connect.php';
// Redirect to login if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$name = htmlspecialchars($_SESSION['name'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HostelSync</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .welcome-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .welcome-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-color);
        }
        
        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            background: var(--card-color);
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .card-description {
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        .card-button {
            background: var(--card-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-button:hover {
            background: var(--card-color-hover);
            transform: translateY(-1px);
            color: white;
        }
        
        .users-card { --card-color: #3b82f6; --card-color-hover: #2563eb; }
        .students-card { --card-color: #10b981; --card-color-hover: #059669; }
        .complaints-card { --card-color: #f59e0b; --card-color-hover: #d97706; }
        .add-payment-card { --card-color: #8b5cf6; --card-color-hover: #7c3aed; }
        .view-payments-card { --card-color: #06b6d4; --card-color-hover: #0891b2; }
        .logout-card { --card-color: #ef4444; --card-color-hover: #dc2626; }
        

    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Admin Dashboard</span>
            </div>
        </div>
    </header>

    <!-- Main Dashboard -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="welcome-title">
                        <i class="fas fa-crown text-yellow-500 mr-3"></i>
                        Welcome back, <?php echo $name; ?>!
                    </h2>
                    <p class="text-slate-600 text-lg">Manage your hostel operations from this central dashboard</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500">Last login</div>
                    <div class="text-slate-700 font-medium"><?php echo date('M d, Y'); ?></div>
                </div>
            </div>
        </div>



        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Manage Users -->
            <div class="dashboard-card users-card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="card-title">Manage Users</h3>
                <p class="card-description">
                    View, edit, and manage all user accounts in the system. Control user permissions and access levels.
                </p>
                <a href="view_users.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    Manage Users
                </a>
            </div>

            <!-- View Students -->
            <div class="dashboard-card students-card">
                <div class="card-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="card-title">Student Records</h3>
                <p class="card-description">
                    Access comprehensive student information, room assignments, and academic details.
                </p>
                <a href="view_students.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    View Students
                </a>
            </div>

            <!-- View Complaints -->
            <div class="dashboard-card complaints-card">
                <div class="card-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="card-title">Complaint Management</h3>
                <p class="card-description">
                    Review and respond to student complaints and facility issues. Track resolution status.
                </p>
                <a href="view_complaints.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    View Complaints
                </a>
            </div>

            <!-- Add Payment -->
            <div class="dashboard-card add-payment-card">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <h3 class="card-title">Record Payment</h3>
                <p class="card-description">
                    Add new payment records for students including fees, deposits, and other transactions.
                </p>
                <a href="add_payment.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    Add Payment
                </a>
            </div>

            <!-- View Payments -->
            <div class="dashboard-card view-payments-card">
                <div class="card-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="card-title">Financial Reports</h3>
                <p class="card-description">
                    View payment history, generate financial reports, and track revenue analytics.
                </p>
                <a href="view_payments.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    View Payments
                </a>
            </div>

            <!-- Logout -->
            <div class="dashboard-card logout-card">
                <div class="card-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h3 class="card-title">Secure Logout</h3>
                <p class="card-description">
                    Safely end your admin session and return to the login page with full security.
                </p>
                <a href="logout.php" class="card-button">
                    <i class="fas fa-arrow-right"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>