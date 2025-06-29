<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['complaint_type'];
    $desc = $_POST['description'];

    // Get warden_id responsible for this student
    $stmt = $conn->prepare("SELECT Warden_ID FROM student WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($warden_id);
    $stmt->fetch();
    $stmt->close();

    // Insert complaint
    $stmt = $conn->prepare("INSERT INTO complaint (Complaint_type, Description, Student_ID, Warden_ID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $type, $desc, $student_id, $warden_id);
    
    if ($stmt->execute()) {
        $message = "<p style='color:green;'>✅ Complaint submitted.</p>";
    } else {
        $message = "<p style='color:red;'>❌ Error: {$stmt->error}</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head><title>Submit Complaint</title></head>
<body>
    <h2>Submit a Complaint</h2>
    <?= $message ?>
    <form method="post">
        <label>Complaint Type:</label><br>
        <select name="complaint_type" required>
            <option value="">--Select--</option>
            <option>Maintenance</option>
            <option>Cleanliness</option>
            <option>Noise</option>
            <option>Security</option>
        </select><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" cols="50" required></textarea><br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
