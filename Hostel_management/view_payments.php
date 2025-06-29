<?php
session_start();
include 'db_connect.php';

// Only allow admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM payment ORDER BY Payment_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Records - HostelSync</title>
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
        
        .table-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .table-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #06b6d4;
        }
        
        .table-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .payments-table th {
            background: #06b6d4;
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        
        .payments-table th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        .payments-table th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .payments-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #374151;
            white-space: nowrap;
        }
        
        .payments-table tr:hover {
            background: #f8fafc;
        }
        
        .payments-table tr:last-child td {
            border-bottom: none;
        }
        
        .date-cell {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875rem;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            margin-right: 0.5rem;
        }
        
        .edit-btn {
            background: #10b981;
            color: white;
        }
        
        .edit-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            color: white;
        }
        
        .delete-btn {
            background: #ef4444;
            color: white;
        }
        
        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            color: white;
        }
        
        .back-btn {
            background: #06b6d4;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }
        
        .back-btn:hover {
            background: #0891b2;
            transform: translateY(-1px);
            color: white;
        }
        
        .action-container {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Payment Records</span>
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
                        <i class="fas fa-chart-bar text-cyan-500 mr-3"></i>
                        Payment Records
                    </h2>
                    <p class="text-slate-600 text-lg">View and manage all payment records in the system</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500">Last Updated</div>
                    <div class="text-slate-700 font-medium"><?php echo date('M d, Y'); ?></div>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Student ID</th>
                            <th>Amount (Rs.)</th>
                            <th>Paid On</th>
                            <th>Due Date</th>
                            <th>Penalty (Rs.)</th>
                            <th>Month</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                // Format dates consistently in single line
                                $payment_date = date("M d, Y", strtotime($row['Payment_date']));
                                $due_date = date("M d, Y", strtotime($row['due_date']));
                                $month = date("F Y", strtotime($row['Payment_date']));
                            ?>
                            <tr>
                                <td class="font-mono text-sm"><?= htmlspecialchars($row['Payment_ID']) ?></td>
                                <td class="font-medium"><?= htmlspecialchars($row['Student_ID']) ?></td>
                                <td><?= number_format($row['amount'], 2) ?></td>
                                <td class="date-cell"><?= $payment_date ?></td>
                                <td class="date-cell"><?= $due_date ?></td>
                                <td><?= number_format($row['penalty'], 2) ?></td>
                                <td><?= $month ?></td>
                                <td>
                                    <div class="action-container">
                                        <a class="action-btn edit-btn" href="edit_payment.php?id=<?= $row['Payment_ID'] ?>">
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>
                                        <a class="action-btn delete-btn" href="delete_payment.php?id=<?= $row['Payment_ID'] ?>" onclick="return confirm('Are you sure you want to delete this payment?');">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>
</body>
</html>

<?php $conn->close(); ?>