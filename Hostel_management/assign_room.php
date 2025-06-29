<?php
session_start();

// Only warden can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$success = '';
$error = '';

// Fetch available rooms
$roomQuery = "SELECT Room_No FROM room WHERE Capacity > Occupied_count";
$roomsResult = $conn->query($roomQuery);

// Fetch students not assigned to any room
$studentQuery = "SELECT user.ID, user.name FROM user 
                 INNER JOIN student ON user.ID = student.ID 
                 WHERE user.role = 'student' AND user.ID NOT IN (SELECT Student_ID FROM assigned_to WHERE Checkout_date IS NULL)";
$studentsResult = $conn->query($studentQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $room_no = $_POST['room_no'];
    $assignment_date = date('Y-m-d');

    // Insert into assigned_to
    $stmt = $conn->prepare("INSERT INTO assigned_to (Student_ID, Room_No, Assignment_date) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $student_id, $room_no, $assignment_date);

    if ($stmt->execute()) {
        // Update occupied count
        $updateRoom = $conn->prepare("UPDATE room SET Occupied_count = Occupied_count + 1 WHERE Room_No = ?");
        $updateRoom->bind_param("i", $room_no);
        $updateRoom->execute();

        $success = "✅ Student assigned to room successfully!";
    } else {
        $error = "❌ Failed to assign student. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Room</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .success { color: green; margin-bottom: 15px; }
        .error { color: red; margin-bottom: 15px; }
        a.back {
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            color: #555;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>🏠 Assign Student to Room</h2>

    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Select Student:</label>
        <select name="student_id" required>
            <option value="">-- Select --</option>
            <?php while ($student = $studentsResult->fetch_assoc()) {
                echo "<option value='{$student['ID']}'>{$student['name']} (ID: {$student['ID']})</option>";
            } ?>
        </select>

        <label>Select Room:</label>
        <select name="room_no" required>
            <option value="">-- Select --</option>
            <?php while ($room = $roomsResult->fetch_assoc()) {
                echo "<option value='{$room['Room_No']}'>Room {$room['Room_No']}</option>";
            } ?>
        </select>

        <button type="submit">Assign</button>
    </form>
    <a href="warden_dashboard.php" class="back">⬅ Back to Warden Dashboard</a>
</div>
</body>
</html>
