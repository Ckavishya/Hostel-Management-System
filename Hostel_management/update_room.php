<?php
session_start();
require 'db_connect.php';

// Only wardens can access this
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

// Check if Room_No is passed
if (!isset($_GET['Room_No'])) {
    echo "Invalid request.";
    exit();
}

$room_no = $_GET['Room_No'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $capacity = $_POST['capacity'];
    $room_type = $_POST['room_type'];
    $ac_type = $_POST['ac_type'];
    $monthly_rent = $_POST['monthly_rent'];
    $occupied_count = $_POST['occupied_count'];

    // Basic validation
    if ($capacity < 1 || $monthly_rent < 0 || $occupied_count < 0) {
        $error = "❌ Please enter valid numbers.";
    } elseif ($occupied_count > $capacity) {
        $error = "❌ Occupied count cannot exceed room capacity.";
    } else {
        $stmt = $conn->prepare("UPDATE room SET Capacity=?, Room_type=?, ac_type=?, Monthly_rent=?, Occupied_count=? WHERE Room_No=?");
        $stmt->bind_param("issdis", $capacity, $room_type, $ac_type, $monthly_rent, $occupied_count, $room_no);
        if ($stmt->execute()) {
            $success = "✅ Room updated successfully!";
        } else {
            $error = "❌ Error updating room. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch room data for display
$stmt = $conn->prepare("SELECT * FROM room WHERE Room_No = ?");
$stmt->bind_param("s", $room_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo "Room not found.";
    exit();
}

$room = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Update Room</title>
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
            transform: translateY(-4px);
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
        .btn-update { background: linear-gradient(135deg, #10b981, #34d399); }
        .btn-update:hover { background: linear-gradient(135deg, #059669, #10b981); }
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 1rem 3rem;
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
            gap: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
            padding: 0.75rem;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 8px;
        }
        .success {
            color: #10b981;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            text-align: center;
            padding: 0.75rem;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 8px;
        }
        label {
            text-align: left;
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        input[type="number"],
        select {
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        input[type="number"]:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        .room-info-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .room-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .capacity-info {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1.5rem;
            }
            .glass-card {
                padding: 1.5rem;
            }
            .header {
                padding: 1rem 2rem;
            }
            .header h1 {
                font-size: 1.75rem;
            }
            .welcome-text {
                font-size: 2rem;
            }
            .subtitle-text {
                font-size: 0.9rem;
            }
            .btn-elegant {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .button-container {
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
                <div class="mb-6 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        <i class="fas fa-edit mr-3"></i>Update Room
                    </h2>
                    <p class="text-lg subtitle-text">
                        Modify room details and occupancy information.
                    </p>
                </div>

                <!-- Room Info Card -->
                <div class="room-info-card">
                    <div class="room-number">
                        <i class="fas fa-door-open mr-2"></i>
                        Room <?= htmlspecialchars($room_no) ?>
                    </div>
                </div>

                <div class="form-container">
                    <?php if ($error): ?>
                        <div class="error">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="success">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-users"></i>
                                    Capacity:
                                </label>
                                <input type="number" name="capacity" value="<?= htmlspecialchars($room['Capacity']) ?>" min="1" max="10" required>
                                <div class="capacity-info">Maximum number of students</div>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-chart-pie"></i>
                                    Occupied Count:
                                </label>
                                <input type="number" name="occupied_count" value="<?= htmlspecialchars($room['Occupied_count']) ?>" min="0" required>
                                <div class="capacity-info">Current occupancy</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-home"></i>
                                    Room Type:
                                </label>
                                <select name="room_type" required>
                                    <option value="Single" <?= $room['Room_type'] == 'Single' ? 'selected' : '' ?>>Single Room</option>
                                    <option value="Double" <?= $room['Room_type'] == 'Double' ? 'selected' : '' ?>>Double Room</option>
                                    
                                </select>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-snowflake"></i>
                                    AC Type:
                                </label>
                                <select name="ac_type" required>
                                    <option value="ac" <?= $room['ac_type'] == 'ac' ? 'selected' : '' ?>>Air Conditioned</option>
                                    <option value="non-ac" <?= $room['ac_type'] == 'non-ac' ? 'selected' : '' ?>>Non-AC</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-dollar-sign"></i>
                                Monthly Rent (Rs):
                            </label>
                            <input type="number" step="0.01" name="monthly_rent" value="<?= htmlspecialchars($room['Monthly_rent']) ?>" min="0" required>
                            <div class="capacity-info">Amount in Sri Lankan Rupees</div>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn-elegant btn-update shadow-lg">
                                <i class="fas fa-save"></i>
                                <span>Update Room</span>
                            </button>
                            <a href="view_rooms.php" class="btn-elegant btn-back shadow-lg">
                                <i class="fas fa-arrow-left"></i>
                                <span>Back to Room List</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>