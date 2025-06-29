<?php
session_start();

// Only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Handle filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$query = "SELECT * FROM user";
if ($role_filter !== 'all') {
    $query .= " WHERE role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $role_filter);
} else {
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .filter-form {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: center;
        }

        th {
            background: #6c63ff;
            color: white;
        }

        a.btn {
            padding: 6px 12px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .edit-btn {
            background-color: #28a745;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .back-btn {
            background-color: #007bff;
            margin: 20px auto;
            display: block;
            width: fit-content;
        }

        select {
            padding: 8px;
            font-size: 14px;
        }

        .logout {
            float: right;
            color: #fff;
            background-color: #ff5733;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .logout:hover {
            background-color: #c43e00;
        }
    </style>
</head>
<body>

<a href="logout.php" class="logout">Logout</a>

<h2>👥 Manage Users</h2>

<div class="filter-form">
    <form method="GET">
        <label for="role">Filter by Role:</label>
        <select name="role" onchange="this.form.submit()">
            <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="student" <?= $role_filter === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="warden" <?= $role_filter === 'warden' ? 'selected' : '' ?>>Warden</option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['ID']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['phonenumber']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td>
                    <a class="btn edit-btn" href="edit_user.php?id=<?= urlencode($row['ID']) ?>">Edit</a>
                    <?php if ($row['role'] !== 'admin'): ?>
                        <a class="btn delete-btn" href="delete_user.php?id=<?= urlencode($row['ID']) ?>" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="admin_dashboard.php" class="btn back-btn">← Back to Dashboard</a>

</body>
</html>
