<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get student details
$stmt = $conn->prepare("SELECT id, name, email, phonenumber FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$studentId = $userInfo['id'];
$stmt->close();

// Get payment history for the logged-in student
$stmt = $conn->prepare("SELECT amount, Payment_date, due_date FROM payment WHERE Student_ID = ? ORDER BY Payment_date DESC");
$stmt->bind_param("s", $studentId);
$stmt->execute();
$payments = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - HostelSync</title>
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
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 2rem;
        }
        
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .info-item {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
            border-left: 4px solid #06b6d4;
        }
        
        .info-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        .payment-list {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .payment-item {
            background: white;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .payment-item:hover {
            background: #f8fafc;
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: #059669;
        }
        
        .payment-date {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(6, 182, 212, 0.3);
            color: white;
        }
        
        .action-btn.secondary {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .action-btn.secondary:hover {
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.3);
        }
        
        .action-btn.tertiary {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }
        
        .action-btn.tertiary:hover {
            box-shadow: 0 12px 24px rgba(139, 92, 246, 0.3);
        }
        
        .action-btn.quaternary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .action-btn.quaternary:hover {
            box-shadow: 0 12px 24px rgba(245, 158, 11, 0.3);
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-1px);
            color: white;
        }
        
        .section-title {
            font-size: 1.5rem;
        font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #64748b;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Student Portal</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
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
                        <i class="fas fa-user-graduate text-cyan-500 mr-3"></i>
                        Welcome, <?= htmlspecialchars($userInfo['name']) ?>!
                    </h2>
                    <p class="text-slate-600 text-lg">Your personal dashboard for hostel management</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500">Student ID</div>
                    <div class="text-slate-700 font-mono font-medium"><?= htmlspecialchars($studentId) ?></div>
                </div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="glass-card unfinished: card">
            <h3 class="section-title">
                <i class="fas fa-id-card text-blue-500"></i>
                Personal Information
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?= htmlspecialchars($userInfo['name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?= htmlspecialchars($userInfo['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?= htmlspecialchars($userInfo['phonenumber']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Student ID</div>
                    <div class="info-value"><?= htmlspecialchars($studentId) ?></div>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-credit-card text-green-500"></i>
                Payment History
            </h3>
            <?php if ($payments->num_rows > 0): ?>
                <div class="payment-list">
                    <?php while ($row = $payments->fetch_assoc()): ?>
                        <div class="payment-item">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="payment-amount">Rs. <?= number_format($row['amount'], 2) ?></div>
                                    <div class="payment-date">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        Paid on: <?= date("M d, Y", strtotime($row['Payment_date'])) ?>
                                    </div>
                                    <div class="payment-date">
                                        <i class="fas fa-clock mr-1"></i>
                                        Due Date: <?= date("M d, Y", strtotime($row['due_date'])) ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Paid
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-receipt text-6xl text-slate-300 mb-4"></i>
                    <p class="text-lg">No payment records found</p>
                    <p class="text-sm">Your payment history will appear here once you make payments</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-bolt text-yellow-500"></i>
                Quick Actions
            </h3>
            <div class="actions-grid">
                <a href="view_my_details.php" class="action-btn">
                    <i class="fas fa-eye"></i>
                    View My Details
                </a>
                <a href="view_my_payments.php" class="action-btn secondary">
                    <i class="fas fa-receipt"></i>
                    View My Payments
                </a>
                <a href="edit_my_details.php" class="action-btn tertiary">
                    <i class="fas fa-edit"></i>
                    Edit My Details
                </a>
                <a href="report_complaint.php" class="action-btn quaternary">
                    <i class="fas fa-exclamation-triangle"></i>
                    Report Complaint
                </a>
            </div>
        </div>
    </div>
</body>
</html>