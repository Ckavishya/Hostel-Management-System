<?php
session_start();
require 'db_connect.php';

// Only wardens can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

// Check if Room_No is provided
if (!isset($_GET['Room_No'])) {
    echo "Invalid request.";
    exit();
}

$room_no = $_GET['Room_No'];

// First, check if the room exists
$stmt = $conn->prepare("SELECT * FROM room WHERE Room_No = ?");
$stmt->bind_param("i", $room_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Room not found.";
    exit();
}
$stmt->close();

// Then, delete the room
$stmt = $conn->prepare("DELETE FROM room WHERE Room_No = ?");
$stmt->bind_param("i", $room_no);

if ($stmt->execute()) {
    // Redirect back to room list with a success message (optional)
    header("Location: view_rooms.php?deleted=success");
    exit();
} else {
    echo "Failed to delete room.";
}

$stmt->close();
$conn->close();
?>
