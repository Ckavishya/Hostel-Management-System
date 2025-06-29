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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - HostelSync</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .page-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .filter-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .table-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        .custom-select {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            color: #1e293b;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        .custom-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .users-table th {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .users-table th:first-child {
            border-radius: 8px 0 0 0;
        }
        
        .users-table th:last-child {
            border-radius: 0 8px 0 0;
        }
        
        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: #374151;
        }
        
        .users-table tr:hover {
            background: #f8fafc;
        }
        
        .users-table tr:last-child td {
            border-bottom: none;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .role-admin {
            background: #fef3c7;
            color: #92400e;
        }
        
        .role-student {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .role-warden {
            background: #d1fae5;
            color: #065f46;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }
        
        .edit-btn {
            background: #10b981;
            color: white;
        }
        
        .edit-btn:hover {
            background: #059669;
            transform: translateY(-1px);
            color: white;
        }
        
        .delete-btn {
            background: #ef4444;
            color: white;
        }
        
        .delete-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            color: white;
        }
        
        .back-btn {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-top: 2rem;
        }
        
        .back-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
            color: white;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">User Management</span>
                
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="page-title">
                        <i class="fas fa-users text-blue-500 mr-3"></i>
                        Manage Users
                    </h2>
                    <p class="text-slate-600 text-lg">View, edit, and manage all user accounts in the system</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-card">
            <div class="flex items-center gap-4">
                <label for="role" class="text-slate-700 font-medium">Filter by Role:</label>
                <form method="GET" class="flex items-center gap-2">
                    <select name="role" class="custom-select" onchange="this.form.submit()">
                        <option value="all" <?= $role_filter === 'all' ? 'selected' : '' ?>>All Users</option>
                        <option value="student" <?= $role_filter === 'student' ? 'selected' : '' ?>>Students</option>
                        <option value="warden" <?= $role_filter === 'warden' ? 'selected' : '' ?>>Wardens</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Administrators</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
            <div class="overflow-x-auto">
                <table class="users-table">
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
                                <td class="font-mono text-sm"><?= $row['ID'] ?></td>
                                <td class="font-medium"><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phonenumber']) ?></td>
                                <td>
                                    <span class="role-badge role-<?= $row['role'] ?>">
                                        <?= ucfirst($row['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a class="action-btn edit-btn" href="edit_user.php?id=<?= $row['ID'] ?>">
                                        <i class="fas fa-edit"></i>
                                        Edit
                                    </a>
                                    <?php if ($row['role'] !== 'admin') { ?>
                                        <a class="action-btn delete-btn" href="delete_user.php?id=<?= $row['ID'] ?>" onclick="return confirm('Are you sure you want to delete this user?')">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>
</body>
</html>