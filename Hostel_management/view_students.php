<?php
session_start();
require 'db_connect.php';

// Allow only wardens or admins
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['warden', 'admin'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$name = htmlspecialchars($_SESSION['name']);
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Student List</title>
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
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
        .btn-action { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .btn-action:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); }
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
            margin-top: 1.5rem;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.95);
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
        td a {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s ease;
            font-size: 0.9rem;
        }
        td a:hover {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
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
            th, td {
                padding: 0.5rem 0.75rem;
            }
            td a {
                padding: 0.3rem 0.6rem;
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
            <span class="text-slate-300 font-medium">Student List</span>
        </div>
    </header>

    <!-- Main Content -->
    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="glass-card">
                <div class="mb-4 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        📄 Student List
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        Welcome, <?php echo $name . " (" . ucfirst($role) . ")"; ?>!
                    </p>
                </div>

                <div class="table-container">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Duration of Stay</th>
                            <th>Room Number</th>
                            <th>Payments</th>
                            <th>Actions</th>
                        </tr>

                        <?php
                        if ($role === 'admin') {
                            $sql = "SELECT s.ID, u.name, u.email, u.phonenumber, s.Duration_of_stay, a.Room_No
                                    FROM student s 
                                    JOIN user u ON s.ID = u.ID
                                    LEFT JOIN assigned_to a ON s.ID = a.Student_ID";
                            $stmt = $conn->prepare($sql);
                        } else {
                            $sql = "SELECT s.ID, u.name, u.email, u.phonenumber, s.Duration_of_stay, a.Room_No
                                    FROM student s 
                                    JOIN user u ON s.ID = u.ID
                                    LEFT JOIN assigned_to a ON s.ID = a.Student_ID
                                    WHERE s.Warden_ID = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("s", $user_id);
                        }

                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["ID"]); ?></td>
                                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                <td><?php echo htmlspecialchars($row["phonenumber"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Duration_of_stay"]); ?></td>
                                <td><?php echo htmlspecialchars($row["Room_No"] ?? 'Not Assigned'); ?></td>
                                <td><a href="view_payment_student.php?id=<?php echo htmlspecialchars($row['ID']); ?>" class="btn-action">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span>View</span>
                                </a></td>
                                <td><a href="edit_student.php?id=<?php echo htmlspecialchars($row['ID']); ?>" class="btn-action">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a></td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                            echo "<tr><td colspan='8' class='text-center text-slate-600 py-4'>No students found.</td></tr>";
                        endif;
                        $stmt->close();
                        ?>
                    </table>
                </div>

                <div class="mt-4 text-center">
                    <a href="<?php echo ($role === 'admin') ? 'admin_dashboard.php' : 'warden_dashboard.php'; ?>" class="btn-elegant btn-back shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>