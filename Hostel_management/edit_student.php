<?php
session_start();
include 'db_connect.php';

// Restrict access to admins and wardens
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'warden')) {
    header("Location: login.php");
    exit();
}

// Check if ID is passed
$error = '';
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch student data with prepared statement
    $sql = "SELECT user.name, user.email, user.phonenumber, student.Duration_of_stay 
            FROM user 
            INNER JOIN student ON user.ID = student.ID 
            WHERE user.ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $error = "❌ Student not found.";
    }
    $stmt->close();
} else {
    $error = "❌ No student ID provided.";
}

$role = $_SESSION['role'];
$name = htmlspecialchars($_SESSION['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Edit Student</title>
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
            max-width: 600px;
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
            padding: 2rem;
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
        .btn-submit { background: linear-gradient(135deg, #3b82f6, #60a5fa); }
        .btn-submit:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
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
        .error {
            color: #dc2626;
            margin-bottom: 1.5rem;
            font-size: 1rem;
            text-align: center;
        }
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        label {
            font-weight: 500;
            color: #2d3748;
            font-size: 0.9rem;
        }
        input[type="text"],
        input[type="email"],
        input[type="hidden"] {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            color: #2d3748;
            outline: none;
            transition: border-color 0.3s ease;
            width: 100%;
            box-sizing: border-box;
            background: #f7fafc;
        }
        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1.5rem;
            }
            .glass-card {
                padding: 1.5rem;
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
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">Edit Student</span>
        </div>
    </header>

    <!-- Main Content -->
    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="glass-card">
                <div class="mb-4 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        ✏️ Edit Student Details
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        Welcome, <?php echo $name . " (" . ucfirst($role) . ")"; ?>!
                    </p>
                </div>

                <div class="form-container">
                    <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php elseif (isset($row)): ?>
                        <form method="POST" action="update_student.php" class="form-container">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                            <div class="form-group">
                                <label for="name">Name:</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone:</label>
                                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phonenumber']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="duration">Duration of Stay:</label>
                                <input type="text" id="duration" name="duration" value="<?php echo htmlspecialchars($row['Duration_of_stay']); ?>" required>
                            </div>
                            <button type="submit" class="btn-elegant btn-submit shadow-lg">
                                <i class="fas fa-save"></i>
                                <span>Update Student</span>
                            </button>
                        </form>
                    <?php endif; ?>
                    <div class="mt-4 text-center">
                        <a href="<?php echo $role === 'admin' ? 'admin_dashboard.php' : 'view_students.php'; ?>" class="btn-elegant btn-back shadow-lg">
                            <i class="fas fa-arrow-left"></i>
                            <span>Back to Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>