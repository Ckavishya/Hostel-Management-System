<?php
session_start();
require 'db_connect.php';

// Allow only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $warden_id = $_POST['warden_id'];
    $room_no = $_POST['room_no'];

    if (!empty($warden_id) && !empty($room_no)) {
        // Optional: Check if already assigned (prevent duplicates)
        $check = $conn->prepare("SELECT * FROM manages WHERE warden_id = ? AND room_no = ?");
        $check->bind_param("ii", $warden_id, $room_no);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO manages (warden_id, room_no) VALUES (?, ?)");
            $stmt->bind_param("ii", $warden_id, $room_no);
            if ($stmt->execute()) {
                $success = "✅ Room assigned successfully!";
            } else {
                $error = "❌ Failed to assign room.";
            }
        } else {
            $error = "⚠️ This room is already managed by the selected warden.";
        }
    } else {
        $error = "❌ Please select both warden and room.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Warden to Room</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f5f9;
            margin: 0;
            padding: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 500px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        select, button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        button {
            background-color: #6c63ff;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #4e47d2;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            text-align: center;
            color: #6c63ff;
            text-decoration: none;
            font-weight: bold;
            display: block;
        }

        .back-link:hover {
            color: #4e47d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧑‍🏫 Assign Warden to Room</h2>

        <?php if (!empty($success)) echo "<div class='message' style='color: green;'>$success</div>"; ?>
        <?php if (!empty($error)) echo "<div class='message' style='color: red;'>$error</div>"; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="warden_id">Select Warden</label>
                <select name="warden_id" id="warden_id" required>
                    <option value="">-- Select Warden --</option>
                    <?php
                    $warden_sql = "SELECT w.ID, u.name FROM warden w JOIN user u ON w.ID = u.ID";
                    $warden_result = $conn->query($warden_sql);
                    while ($row = $warden_result->fetch_assoc()) {
                        echo "<option value='{$row['ID']}'>{$row['name']} (ID: {$row['ID']})</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="room_no">Select Room</label>
                <select name="room_no" id="room_no" required>
                    <option value="">-- Select Room --</option>
                    <?php
                    $room_sql = "SELECT Room_No FROM room";
                    $room_result = $conn->query($room_sql);
                    while ($row = $room_result->fetch_assoc()) {
                        echo "<option value='{$row['Room_No']}'>Room {$row['Room_No']}</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit">Assign Room</button>
        </form>

        <a class="back-link" href="admin_dashboard.php">🔙 Back to Admin Dashboard</a>
    </div>
</body>
</html>
