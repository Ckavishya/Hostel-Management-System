<?php
session_start();

// Only admin can delete users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "❌ Invalid request.";
    exit();
}

include 'db_connect.php';

$user_id = $_GET['id'];

// Step 1: Get user role
$stmt = $conn->prepare("SELECT role FROM user WHERE ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ User not found.";
    exit();
}

$row = $result->fetch_assoc();
$role = $row['role'];

// Step 2: Delete from respective table
switch ($role) {
    case 'student':
        $conn->query("DELETE FROM assigned_to WHERE Student_ID = $user_id");
        $conn->query("DELETE FROM complaint WHERE Student_ID = $user_id");
        $conn->query("DELETE FROM payment WHERE Student_ID = $user_id");
        $conn->query("DELETE FROM student WHERE ID = $user_id");
        break;

    case 'warden':
        $conn->query("DELETE FROM visitor_log WHERE Warden_ID = $user_id");
        $conn->query("DELETE FROM complaint WHERE Warden_ID = $user_id");
        $conn->query("DELETE FROM warden WHERE ID = $user_id");
        break;

    case 'admin':
        $conn->query("DELETE FROM admin WHERE ID = $user_id");
        break;
}

// Step 3: Delete from user table
$delete = $conn->prepare("DELETE FROM user WHERE ID = ?");
$delete->bind_param("i", $user_id);

if ($delete->execute()) {
    header("Location: manage_users.php?msg=deleted");
    exit();
} else {
    echo "❌ Error deleting user.";
}

$conn->close();
?>
