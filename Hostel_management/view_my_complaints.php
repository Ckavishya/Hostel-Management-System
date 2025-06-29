<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$studentId = $user['id'];
$stmt->close();

// Get complaints
$stmt = $conn->prepare("SELECT issue_type, description, status, submitted_at FROM complaints WHERE student_id = ?");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$complaints = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Complaints</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f3f5f9; padding: 30px; }
        .container { max-width: 700px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ccc; }
        .back { margin-top: 20px; display: block; text-align: center; text-decoration: none; color: #6c63ff; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 My Complaints</h2>

    <?php if ($complaints->num_rows > 0): ?>
        <table>
            <tr><th>Type</th><th>Description</th><th>Status</th><th>Submitted</th></tr>
            <?php while ($row = $complaints->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['issue_type']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No complaints found.</p>
    <?php endif; ?>

    <a class="back" href="student_dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>
