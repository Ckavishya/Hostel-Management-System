<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$currentEmail = $_SESSION['email'];
$success = '';
$error = '';

// Fetch current user details
$stmt = $conn->prepare("SELECT id, name, email, phonenumber FROM user WHERE email = ?");
$stmt->bind_param("s", $currentEmail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$studentId = $user['id'];
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newName = trim($_POST['name']);
    $newPhone = trim($_POST['phone']);
    $newEmail = trim($_POST['email']);
    $newPassword = trim($_POST['password']);

    if (empty($newName) || empty($newPhone) || empty($newEmail)) {
        $error = "❌ Name, phone number, and email cannot be empty.";
    } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Invalid email format.";
    } else {
        // Check if email is being changed and already exists
        if ($newEmail !== $currentEmail) {
            $check = $conn->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
            $check->bind_param("si", $newEmail, $studentId);
            $check->execute();
            $checkResult = $check->get_result();
            if ($checkResult->num_rows > 0) {
                $error = "❌ Email already exists. Please use another.";
                $check->close();
            } else {
                $check->close();
            }
        }

        if (empty($error)) {
            // Update query
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $error = "❌ Password must be at least 6 characters.";
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE user SET name = ?, phonenumber = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $newName, $newPhone, $newEmail, $hashedPassword, $studentId);
                }
            } else {
                $stmt = $conn->prepare("UPDATE user SET name = ?, phonenumber = ?, email = ? WHERE id = ?");
                $stmt->bind_param("sssi", $newName, $newPhone, $newEmail, $studentId);
            }

            if ($stmt->execute()) {
                $_SESSION['email'] = $newEmail;
                $_SESSION['name'] = $newName;
                $success = "✅ Your details were updated successfully.";
                // Refresh user data
                $user['name'] = $newName;
                $user['email'] = $newEmail;
                $user['phonenumber'] = $newPhone;
            } else {
                $error = "❌ Failed to update details.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit My Details - HostelSync</title>
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
        
        .dashboard-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .welcome-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .welcome-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #1f2937;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
            transform: translateY(-1px);
        }
        
        .form-input:hover {
            border-color: #9ca3af;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(6, 182, 212, 0.3);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .back-btn {
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 1rem;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(100, 116, 139, 0.3);
            color: white;
        }
        
        .back-btn-center {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        .input-icon .form-input {
            padding-left: 2.5rem;
        }
        
        .password-note {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .welcome-title {
                font-size: 2rem;
            }
            
            .glass-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Student Portal</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <h2 class="welcome-title">
                <i class="fas fa-edit text-purple-500 mr-3"></i>
                ✏️ Edit My Details
            </h2>
            <p class="text-slate-600 text-lg">Update your personal information and account settings</p>
        </div>

        <!-- Edit Form -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-user-edit text-blue-500"></i>
                Personal Information
            </h3>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user mr-1"></i>
                        Full Name
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="form-input" 
                               value="<?= htmlspecialchars($user['name']); ?>" 
                               required placeholder="Enter your full name">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope mr-1"></i>
                        Email Address
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-input" 
                               value="<?= htmlspecialchars($user['email']); ?>" 
                               required placeholder="Enter your email address">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-phone mr-1"></i>
                        Phone Number
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="phone" class="form-input" 
                               value="<?= htmlspecialchars($user['phonenumber']); ?>" 
                               required placeholder="Enter your phone number">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock mr-1"></i>
                        New Password
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-input" 
                               placeholder="Enter new password (optional)">
                    </div>
                    <div class="password-note">
                        <i class="fas fa-info-circle mr-1"></i>
                        Leave blank to keep your current password. Must be at least 6 characters if changed.
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i>
                    Update Details
                </button>
            </form>

            <div class="back-btn-center">
                <a href="student_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>