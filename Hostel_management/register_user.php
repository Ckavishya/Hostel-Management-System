<?php
session_start();
include 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $phone    = trim($_POST['phone']);
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($phone) || empty($role) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!in_array($role, ['warden', 'admin'])) {
        $error = "Only 'warden' or 'admin' roles are allowed.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $prefix = ($role === 'warden') ? 'W' : 'A';
            $like = $prefix . '%';

            $stmt = $conn->prepare("SELECT ID FROM user WHERE ID LIKE ? ORDER BY ID DESC LIMIT 1");
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $res = $stmt->get_result();

            $lastNum = ($row = $res->fetch_assoc()) ? intval(substr($row['ID'], 1)) + 1 : 1;
            $newId = $prefix . str_pad($lastNum, 2, '0', STR_PAD_LEFT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO user (ID, name, email, phonenumber, role, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $newId, $name, $email, $phone, $role, $password);

            if ($stmt->execute()) {
                $role_stmt = $conn->prepare("INSERT INTO $role (ID) VALUES (?)");
                $role_stmt->bind_param("s", $newId);
                $role_stmt->execute();
                $role_stmt->close();
                $success = "$role registered successfully with ID: $newId";
            } else {
                $error = "Error: " . htmlspecialchars($stmt->error);
            }
            $stmt->close();
        }
        $check->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hostel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }
        .register-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(30, 41, 59, 0.85)),
                        url('https://images.unsplash.com/photo-1555854877-bab0e564b8d5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1469&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            padding: 2rem 0;
        }
        .register-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            backdrop-filter: blur(3px);
            z-index: 1;
        }
        .register-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s ease;
        }
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.15);
        }
        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(34, 197, 94, 0.15);
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(34, 197, 94, 0.3);
        }
        .error-alert {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            animation: shake 0.5s ease-in-out;
        }
        .success-alert {
            background: #dcfce7;
            border-left: 4px solid #22c55e;
            animation: slideIn 0.5s ease-in-out;
        }
        @keyframes shake {
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
            0%, 100% { transform: translateX(0); }
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(40px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 2rem 3rem;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .header h1 {
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff, #e2e8f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-slate-50">
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <a href="login.php" class="text-slate-300 hover:text-white transition-colors">
            <i class="fas fa-sign-in-alt mr-2"></i>Login
        </a>
    </header>

    <section class="register-section">
        <div class="register-content w-full max-w-md px-6">
            <div class="glass-card p-8 rounded-2xl shadow-2xl">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-plus text-white text-2xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 mb-2">Create Account</h2>
                    <p class="text-slate-600">Register as Admin or Warden</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-alert p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                            <span class="text-red-700 font-medium"><?= htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php elseif ($success): ?>
                    <div class="success-alert p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i>
                            <span class="text-green-700 font-medium"><?= htmlspecialchars($success); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <input type="text" name="name" required placeholder="Full Name"
                        class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    
                    <input type="email" name="email" required placeholder="Email"
                        class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    
                    <input type="text" name="phone" required placeholder="Phone Number"
                        class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    
                    <select name="role" required
                        class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white">
                        <option value="">Select Role</option>
                        <option value="warden">Warden</option>
                        <option value="admin">Admin</option>
                    </select>
                    
                    <input type="password" name="password" required placeholder="Password (min 6 characters)"
                        class="input-field w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white">
                    
                    <button type="submit"
                        class="btn-register w-full bg-green-600 text-white py-3 px-6 rounded-lg font-semibold text-lg shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
