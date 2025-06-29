<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $duration = $_POST['duration']; // Reverted to text input, expecting a string (e.g., months)
    $password = $_POST['password'];
    $room_no  = $_POST['room_no'];
    $assignment_date = date('Y-m-d');
    $warden_id = $_SESSION['user_id'];

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "❌ Email already registered.";
    } else {
        // Room availability check
        $room_check = $conn->prepare("SELECT Capacity, Occupied_count FROM room WHERE Room_No = ?");
        $room_check->bind_param("s", $room_no);
        $room_check->execute();
        $room_result = $room_check->get_result();

        if ($room_result->num_rows === 0) {
            $error = "❌ Invalid room selected.";
        } else {
            $room_data = $room_result->fetch_assoc();
            if ($room_data['Occupied_count'] >= $room_data['Capacity']) {
                $error = "❌ Selected room is already full. Please choose another.";
            } else {
                // ✅ Generate new student ID like S01, S02
                $get_max = $conn->query("SELECT MAX(ID) AS max_id FROM user WHERE ID LIKE 'S%'");
                $row = $get_max->fetch_assoc();
                $last_id = $row['max_id'];
                $new_id = $last_id ? 'S' . str_pad((int)substr($last_id, 1) + 1, 2, '0', STR_PAD_LEFT) : 'S01';

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert into user
                $stmt = $conn->prepare("INSERT INTO user (ID, name, email, phonenumber, role, password) VALUES (?, ?, ?, ?, 'student', ?)");
                $stmt->bind_param("sssss", $new_id, $name, $email, $phone, $hashedPassword);
                if ($stmt->execute()) {

                    // Insert into student
                    $stmt2 = $conn->prepare("INSERT INTO student (ID, Duration_of_stay, Warden_ID) VALUES (?, ?, ?)");
                    $stmt2->bind_param("sss", $new_id, $duration, $warden_id); // Kept as 'sss' for string input
                    $stmt2->execute();

                    // Assign room
                    $stmt3 = $conn->prepare("INSERT INTO assigned_to (Student_ID, Room_No, Assignment_date) VALUES (?, ?, ?)");
                    $stmt3->bind_param("sss", $new_id, $room_no, $assignment_date);
                    $stmt3->execute();

                    // Update room occupancy
                    $update = $conn->prepare("UPDATE room SET Occupied_count = Occupied_count + 1 WHERE Room_No = ?");
                    $update->bind_param("s", $room_no);
                    $update->execute();

                    $success = "✅ Student added with ID $new_id and assigned to Room $room_no!";
                } else {
                    $error = "❌ Failed to add student. Try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Add Student</title>
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
        .form-container {
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
        .error {
            color: #ef4444;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .success {
            color: #10b981;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        label {
            text-align: left;
            color: #1e293b;
            font-size: 0.9rem;
            font-weight: 600;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            padding: 0.6rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        select:disabled {
            background: #e5e7eb;
            color: #6b7280;
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
            label {
                font-size: 0.85rem;
            }
            input[type="text"],
            input[type="email"],
            input[type="password"],
            select {
                font-size: 0.85rem;
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
                <div class="mb-4 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        Add Student
                    </h2>
                    <p class="text-lg subtitle-text mb-4">
                        Enroll a new student and assign them to a room.
                    </p>
                </div>

                <div class="form-container">
                    <?php if ($error): ?>
                        <p class="error"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p class="success"><?= htmlspecialchars($success) ?></p>
                    <?php endif; ?>

                    <form method="POST">
                        <label>Name:</label>
                        <input type="text" name="name" required>

                        <label>Email:</label>
                        <input type="email" name="email" required>

                        <label>Phone:</label>
                        <input type="text" name="phone" required>

                        <label>Duration of Stay (Months):</label>
                        <input type="text" name="duration" required>

                        <label>Password:</label>
                        <input type="password" name="password" required>

                        <label>Assign to Room:</label>
                        <select name="room_no" required>
                            <option value="">-- Select Room --</option>
                            <?php
                            $all_rooms = $conn->query("SELECT Room_No, Capacity, Occupied_count FROM room");
                            while ($room_option = $all_rooms->fetch_assoc()) {
                                $r_no = $room_option['Room_No'];
                                $cap = $room_option['Capacity'];
                                $occ = $room_option['Occupied_count'];
                                $full = ($occ >= $cap);
                                $disabled = $full ? 'disabled' : '';
                                $label = "Room $r_no ($occ/$cap" . ($full ? " - Full" : "") . ")";
                                echo "<option value='$r_no' $disabled>$label</option>";
                            }
                            ?>
                        </select>

                        <div class="button-container">
                            <button type="submit" class="btn-elegant btn-submit shadow-lg">
                                <i class="fas fa-user-plus"></i>
                                <span>Add Student</span>
                            </button>
                            <a href="warden_dashboard.php" class="btn-elegant btn-back shadow-lg">
                                <i class="fas fa-arrow-left"></i>
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