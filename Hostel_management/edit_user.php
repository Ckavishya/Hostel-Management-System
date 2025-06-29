<?php
session_start();

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo "<p class='text-red-500'>❌ Invalid request.</p><a href='manage_users.php' class='text-blue-500 hover:underline'>← Back to Manage Users</a>";
    exit();
}

$user_id = $_GET['id'];
$success = '';
$error = '';

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM user WHERE ID = ?");
$stmt->bind_param("s", $user_id); // changed to string binding
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "<p class='text-red-500'>❌ Invalid user ID.</p><a href='manage_users.php' class='text-blue-500 hover:underline'>← Back to Manage Users</a>";
    exit();
}

$user = $result->fetch_assoc();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);

    // Check for duplicate email
    $check = $conn->prepare("SELECT * FROM user WHERE email = ? AND ID != ?");
    $check->bind_param("ss", $new_email, $user_id); // both email and ID are strings
    $check->execute();
    $check_result = $check->get_result();

    if ($check_result->num_rows > 0) {
        $error = "❌ Email already in use.";
    } else {
        $update = $conn->prepare("UPDATE user SET name = ?, email = ?, phonenumber = ? WHERE ID = ?");
        $update->bind_param("ssss", $new_name, $new_email, $new_phone, $user_id); // all strings
        if ($update->execute()) {
            $success = "✅ User updated successfully.";
            $user['name'] = $new_name;
            $user['email'] = $new_email;
            $user['phonenumber'] = $new_phone;
        } else {
            $error = "❌ Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - HostelSync</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .header {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
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

        .form-container {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            max-width: 400px;
            width: 100%;
            margin-top: 7rem;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #6c63ff;
        }

        .form-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .success {
            color: #10b981;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .error {
            color: #ef4444;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        label {
            display: block;
            margin-top: 1rem;
            font-weight: 500;
            color: #1e293b;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #1e293b;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #6c63ff;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
        }

        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.75rem;
            background: #6c63ff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        button:hover {
            background: #4e47d2;
            transform: translateY(-1px);
        }

        .back-link {
            display: block;
            margin-top: 1rem;
            text-align: center;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Edit User</span>
            </div>
        </div>
    </header>

    <!-- Form Container -->
    <div class="form-container">
        <h2 class="form-title">
            <i class="fas fa-edit text-yellow-500"></i>
            Edit User
        </h2>

        <?php if ($success) echo "<div class='success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Phone Number:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phonenumber']) ?>" required>

            <button type="submit">
                <i class="fas fa-save"></i>
                Update
            </button>
        </form>

        <a class="back-link" href="view_users.php">
            <i class="fas fa-arrow-left mr-1"></i>
            Back to Manage Users
        </a>
    </div>
</body>
</html>