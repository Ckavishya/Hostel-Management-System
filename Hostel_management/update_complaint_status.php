<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'warden' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $complaintId = intval($_GET['id']);
    
    $stmt = $conn->prepare("UPDATE complaint SET Status = 'Resolved' WHERE Complaint_ID = ?");
    $stmt->bind_param("i", $complaintId);
    $stmt->execute();
    $stmt->close();
}

header("Location: view_complaint.php");
exit();
