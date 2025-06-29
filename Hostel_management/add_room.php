<?php
session_start();
require_once 'db_connect.php'; // Ensure this file exists and connects to your database

// Allow only wardens
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_no = trim($_POST['room_no']);
    $capacity = intval($_POST['capacity']);
    $monthly_rent = floatval($_POST['monthly_rent']);
    $room_type = trim($_POST['room_type']);
    $ac_type = trim($_POST['ac_type']);

    // Validate input
    if (empty($room_no) || empty($capacity) || empty($monthly_rent) || empty($room_type) || empty($ac_type)) {
        $error = "❌ Please fill in all fields.";
    } elseif ($capacity < 1 || $capacity > 6) {
        $error = "❌ Capacity must be between 1 and 6.";
    } elseif ($monthly_rent < 1000) {
        $error = "❌ Monthly rent must be at least 1000.";
    } else {
        // Prepare and execute the insert query
        $stmt = $conn->prepare("INSERT INTO room (Room_no, Capacity, Monthly_rent, Room_type, AC_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sidss", $room_no, $capacity, $monthly_rent, $room_type, $ac_type);
        
        if ($stmt->execute()) {
            $success = "✅ Room added successfully.";
        } else {
            $error = "❌ Failed to add room. Please check if the room number already exists.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Add Room</title>
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
            max-width: 750px;
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
            padding: 2.5rem;
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
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        .btn-submit { background: linear-gradient(135deg, #10b981, #34d399); }
        .btn-submit:hover { background: linear-gradient(135deg, #059669, #10b981); }
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
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
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        label {
            text-align: left;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 600;
        }
        input[type="text"],
        input[type="number"],
        select {
            padding: 0.8rem 1.2rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        .logout-btn {
            position: absolute;
            top: 2rem;
            right: 3rem;
            z-index: 4;
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
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
            .logout-btn {
                top: 1.5rem;
                right: 2rem;
            }
            label {
                font-size: 0.85rem;
            }
            input[type="text"],
            input[type="number"],
            select {
                font-size: 0.85rem;
            }
            .button-container {
                gap: 0.75rem;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0.75rem;
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
                        <i class="fas fa-door-open mr-2"></i>
                        Add Room
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        Create a new room and set its configuration for student accommodation.
                    </p>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= $success ?>
                    </div>
                <?php elseif ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST">
                        <div class="form-group">
                            <label>Room Number:</label>
                            <input type="text" name="room_no" required placeholder="e.g., R101">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Capacity:</label>
                                <input type="number" name="capacity" required min="1" max="6" placeholder="Number of students">
                            </div>
                            <div class="form-group">
                                <label>Monthly Rent (Rs.):</label>
                                <input type="number" name="monthly_rent" required min="1000" placeholder="Amount in rupees">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Room Type:</label>
                                <select name="room_type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="Single">🛏️ Single</option>
                                    <option value="Double">🛏️🛏️ Double</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>AC Type:</label>
                                <select name="ac_type" required>
                                    <option value="">-- Select AC Type --</option>
                                    <option value="ac">❄️ AC</option>
                                    <option value="non-ac">🌿 Non-AC</option>
                                </select>
                            </div>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn-elegant btn-submit shadow-lg">
                                <i class="fas fa-plus-circle mr-2"></i>
                                <span>Add Room</span>
                            </button>
                            <a href="warden_dashboard.php" class="btn-elegant btn-back shadow-lg">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <span>Back to Warden Panel</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>