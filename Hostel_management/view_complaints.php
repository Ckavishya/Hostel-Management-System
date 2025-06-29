<?php
session_start();
require_once 'db_connect.php';

// Only admin or warden can access this
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'warden' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Handle status update if requested
if (isset($_GET['action']) && $_GET['action'] === 'resolve' && isset($_GET['id'])) {
    $complaint_id = intval($_GET['id']);
    
    // Update complaint status to resolved
    $update_stmt = $conn->prepare("UPDATE complaint SET Status = 'Resolved' WHERE Complaint_ID = ?");
    $update_stmt->bind_param("i", $complaint_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Complaint #$complaint_id has been marked as resolved successfully!";
    } else {
        $error_message = "Failed to update complaint status. Please try again.";
    }
    $update_stmt->close();
}

// Fetch complaints with student names and status
$complaints = $conn->query("
    SELECT c.Complaint_ID, c.Complaint_type, c.Description, c.Student_ID, c.Status, u.name AS student_name 
    FROM complaint c
    JOIN user u ON c.Student_ID = u.ID
    ORDER BY c.Complaint_ID ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - View Complaints</title>
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
            max-width: 1200px;
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
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
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
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }
        .btn-action { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .btn-action:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .btn-back { 
            background: linear-gradient(135deg, #6b7280, #9ca3af); 
            padding: 0.8rem 2rem;
            font-size: 1rem;
        }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
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
        .table-container {
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
            position: relative;
        }
        th:first-child {
            border-top-left-radius: 12px;
        }
        th:last-child {
            border-top-right-radius: 12px;
        }
        td {
            padding: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        tr:hover td {
            background-color: rgba(59, 130, 246, 0.05);
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }
        tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }
        .status-pending {
            color: #f59e0b;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .status-resolved {
            color: #10b981;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .no-data {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 2rem;
        }
        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .description-cell {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        .student-name {
            font-weight: 600;
            color: #1e293b;
        }
        .student-id {
            font-size: 0.8rem;
            color: #64748b;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
        }
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
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
            .welcome-text {
                font-size: 2rem;
            }
            .subtitle-text {
                font-size: 0.85rem;
            }
            .btn-elegant {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 0.5rem;
            }
            .description-cell {
                max-width: 150px;
            }
        }
        @media (max-width: 480px) {
            .table-container {
                margin: 1rem -0.5rem;
                border-radius: 8px;
            }
            th, td {
                padding: 0.4rem 0.2rem;
                font-size: 0.75rem;
            }
            .description-cell {
                max-width: 100px;
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">
                <?php echo ucfirst($_SESSION['role']); ?> Dashboard
            </span>
            
        </div>
    </header>

    <!-- Main Content -->
    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="glass-card">
                <div class="mb-4 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        Student Complaints
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        View and manage all student complaints in the system.
                    </p>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <?php if ($complaints->num_rows > 0): ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>
                                        <i class="fas fa-hashtag mr-2"></i>
                                        ID
                                    </th>
                                    <th>
                                        <i class="fas fa-user mr-2"></i>
                                        Student
                                    </th>
                                    <th>
                                        <i class="fas fa-tag mr-2"></i>
                                        Type
                                    </th>
                                    <th>
                                        <i class="fas fa-file-text mr-2"></i>
                                        Description
                                    </th>
                                    <th>
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Status
                                    </th>
                                    <th>
                                        <i class="fas fa-cog mr-2"></i>
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $complaints->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['Complaint_ID'] ?></td>
                                    <td>
                                        <div class="student-info">
                                            <span class="student-name"><?= htmlspecialchars($row['student_name']) ?></span>
                                            <span class="student-id">(<?= $row['Student_ID'] ?>)</span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['Complaint_type']) ?></td>
                                    <td class="description-cell" title="<?= htmlspecialchars($row['Description']) ?>">
                                        <?= htmlspecialchars($row['Description']) ?>
                                    </td>
                                    <td>
                                        <?php if ($row['Status'] === 'Pending'): ?>
                                            <span class="status-pending">
                                                <i class="fas fa-clock"></i>
                                                Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="status-resolved">
                                                <i class="fas fa-check-circle"></i>
                                                Resolved
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['Status'] === 'Pending'): ?>
                                            <a class="btn-elegant btn-action" 
                                               href="view_complaints.php?action=resolve&id=<?= $row['Complaint_ID'] ?>"
                                               onclick="return confirm('Are you sure you want to mark this complaint as resolved?');">
                                                <i class="fas fa-check"></i>
                                                Mark Resolved
                                            </a>
                                        <?php else: ?>
                                            <span class="status-resolved">
                                                <i class="fas fa-check-circle"></i>
                                                Resolved
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <div class="no-data">
                            <i class="fas fa-info-circle mr-2"></i>
                            No complaints found in the system.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="button-container">
                    <?php
                    $dashboard = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'warden_dashboard.php';
                    ?>
                    <a href="<?= $dashboard ?>" class="btn-elegant btn-back shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>