<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Get student details
$stmt = $conn->prepare("SELECT id, name FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$userInfo = $result->fetch_assoc();
$student_id = $userInfo['id'];
$stmt->close();

// Fetch payment history with penalty column
$stmt = $conn->prepare("SELECT * FROM payment WHERE Student_ID = ? ORDER BY Payment_date DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payment History - HostelSync</title>
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
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        .payments-table th {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .payments-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        
        .payments-table tbody tr:hover {
            background: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .payments-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .amount-cell {
            font-weight: 700;
            color: #059669;
            font-size: 1.125rem;
        }
        
        .penalty-cell {
            font-weight: 600;
            color: #dc2626;
            font-size: 1rem;
        }
        
        .date-cell {
            color: #64748b;
            font-weight: 500;
        }
        
        .payment-id-cell {
            font-family: 'Monaco', 'Menlo', monospace;
            background: #f1f5f9;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.875rem;
            color: #475569;
        }
        
        .no-data {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }
        
        .no-data i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(6, 182, 212, 0.3);
            color: white;
        }
        
        @media (max-width: 768px) {
            .payments-table {
                font-size: 0.875rem;
            }
            
            .payments-table th,
            .payments-table td {
                padding: 0.75rem 0.5rem;
            }
            
            .dashboard-container {
                padding: 1rem;
            }
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
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <h2 class="welcome-title">
                <i class="fas fa-credit-card text-green-500 mr-3"></i>
                💰 My Payment History
            </h2>
            <p class="text-slate-600 text-lg">Complete record of all your hostel payments</p>
        </div>

        <!-- Payment History Table -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-list text-blue-500"></i>
                Payment Records
            </h3>
            
            <?php if ($result->num_rows > 0): ?>
                <div style="overflow-x: auto;">
                    <table class="payments-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag mr-2"></i>Payment ID</th>
                                <th><i class="fas fa-dollar-sign mr-2"></i>Amount</th>
                                <th><i class="fas fa-exclamation-triangle mr-2"></i>Penalty</th>
                                <th><i class="fas fa-calendar-check mr-2"></i>Paid On</th>
                                <th><i class="fas fa-calendar-times mr-2"></i>Due Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="payment-id-cell">
                                        #<?= htmlspecialchars($row['Payment_ID']) ?>
                                    </div>
                                </td>
                                <td class="amount-cell">
                                    Rs. <?= number_format($row['amount'], 2) ?>
                                </td>
                                <td class="penalty-cell">
                                    <?php if (isset($row['penalty']) && $row['penalty'] > 0): ?>
                                        Rs. <?= number_format($row['penalty'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-green-600">Rs. 0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td class="date-cell">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?= date("M d, Y", strtotime($row['Payment_date'])) ?>
                                </td>
                                <td class="date-cell">
                                    <i class="fas fa-clock mr-1"></i>
                                    <?= date("M d, Y", strtotime($row['due_date'])) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-receipt"></i>
                    <h3 class="text-xl font-semibold text-slate-700 mb-2">No Payment Records Found</h3>
                    <p class="text-slate-500">❌ No payments found.</p>
                    <p class="text-sm text-slate-400 mt-2">Your payment history will appear here once you make payments</p>
                </div>
            <?php endif; ?>
            
            <a href="student_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                🔙 Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>