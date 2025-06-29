<?php
session_start();
require 'db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['warden', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Validate student ID from URL
if (!isset($_GET['id']) || !preg_match('/^S\d{2}$/', $_GET['id'])) {
    echo "<p class='text-red-500'>❌ Invalid student ID.</p>";
    echo "<a href='view_students.php' class='text-blue-500 hover:underline'>← Back to Student List</a>";
    exit();
}

$student_id = $_GET['id'];

// Fetch payment records including penalty
$sql = "SELECT Payment_ID, amount, Payment_date, due_date, penalty 
        FROM payment 
        WHERE Student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payments - HostelSync</title>
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
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
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
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            margin-top: 7rem;
        }

        .glass-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #3b82f6;
        }

        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }

        .welcome-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            margin-top: 1.5rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background: linear-gradient(135deg, #2d3748, #4a5568);
            color: white;
            font-weight: 600;
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
        }

        tr:hover td {
            background: linear-gradient(90deg, #f7fafc, #edf2f7);
        }

        .penalty-amount {
            color: #dc2626;
            font-weight: 600;
        }

        .no-penalty {
            color: #16a34a;
            font-weight: 500;
        }

        .card-button {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
            justify-content: center;
        }

        .card-button:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1.5rem;
                max-width: 100%;
            }
            .glass-card {
                padding: 1rem;
            }
            .welcome-title {
                font-size: 1.8rem;
                flex-direction: column;
                text-align: center;
            }
            th, td {
                padding: 0.5rem 0.5rem;
                font-size: 0.875rem;
            }
            .table-container {
                font-size: 0.875rem;
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
                <span class="text-slate-300 font-medium">Student Payments</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="glass-card">
            <div class="mb-4 text-center">
                <h2 class="welcome-title">
                    <i class="fas fa-money-bill-wave text-yellow-500"></i>
                    Payment Records for Student ID: <?php echo htmlspecialchars($student_id); ?>
                </h2>
            </div>

            <div class="table-container">
                <table>
                    <tr>
                        <th>Payment ID</th>
                        <th>Amount</th>
                        <th>Payment Date</th>
                        <th>Due Date</th>
                        <th>Penalty</th>
                    </tr>

                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Payment_ID']); ?></td>
                                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['Payment_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                                <td>
                                    <?php if ($row['penalty'] && $row['penalty'] > 0): ?>
                                        <span class="penalty-amount"><?php echo htmlspecialchars($row['penalty']); ?></span>
                                    <?php else: ?>
                                        <span class="no-penalty">No Penalty</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-slate-600 py-4">No payment records found for this student.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="mt-4 text-center">
                <a href="view_students.php" class="card-button">
                    <i class="fas fa-arrow-left"></i>
                    Back to Student List
                </a>
            </div>
        </div>
    </div>
</body>
</html>