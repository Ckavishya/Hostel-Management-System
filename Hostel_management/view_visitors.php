<?php
session_start();
require 'db_connect.php';

// Allow only wardens
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

$warden_id = $_SESSION['user_id'];

// Fetch visitor logs for students under this warden
$sql = "
    SELECT v.Student_ID, v.Visitor_name, v.Phonenumber
    FROM visitor_log v
    JOIN student s ON v.Student_ID = s.ID
    WHERE s.Warden_ID = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $warden_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - View Visitors</title>
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
            gap: 0.5rem;
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
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
        .btn-logout { background: linear-gradient(135deg, #ef4444, #f87171); }
        .btn-logout:hover { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .btn-delete { background: linear-gradient(135deg, #ef4444, #f87171); }
        .btn-delete:hover { background: linear-gradient(135deg, #dc2626, #ef4444); }
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
            font-size: 0.95rem;
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
            padding: 1rem;
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
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
            table {
                font-size: 0.85rem;
            }
            th, td {
                padding: 0.75rem 0.5rem;
            }
        }
        @media (max-width: 480px) {
            .table-container {
                margin: 1rem -0.5rem;
                border-radius: 8px;
            }
            th, td {
                padding: 0.5rem 0.25rem;
                font-size: 0.8rem;
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
                        Visitor List
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        View all visitors registered for your students.
                    </p>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <i class="fas fa-id-badge mr-2"></i>
                                    Student ID
                                </th>
                                <th>
                                    <i class="fas fa-user mr-2"></i>
                                    Visitor Name
                                </th>
                                <th>
                                    <i class="fas fa-phone mr-2"></i>
                                    Phone Number
                                </th>
                                <th>
                                    <i class="fas fa-cog mr-2"></i>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Student_ID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Visitor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Phonenumber']); ?></td>
                                    <td>
                                        <a href="delete_visitor.php?log_id=<?php echo htmlspecialchars($row['Student_ID']); ?>" 
                                           class="btn-elegant btn-delete shadow-lg"
                                           onclick="return confirm('Are you sure you want to delete this visitor record?');">
                                            <i class="fas fa-trash"></i>
                                            <span>Delete</span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-data">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        No visitors found for your students.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="button-container">
                    <a href="warden_dashboard.php" class="btn-elegant btn-back shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Warden Panel</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>