<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Modified query to include Room Number
$sql = "SELECT u.ID, u.name, u.email, u.phonenumber, s.Duration_of_stay, a.Room_No
        FROM user u
        INNER JOIN student s ON u.ID = s.ID
        LEFT JOIN assigned_to a ON u.ID = a.Student_ID
        WHERE u.ID = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $user_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Details - HostelSync</title>
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
            max-width: 800px;
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
        
        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .detail-item {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #06b6d4;
            transition: all 0.3s ease;
        }
        
        .detail-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .detail-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .detail-value {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            word-break: break-word;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
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
        
        .back-btn:hover {
            background: linear-gradient(135deg, #0891b2, #0e7490);
            transform: translateY(-1px);
            color: white;
            box-shadow: 0 8px 16px rgba(6, 182, 212, 0.3);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .error-card {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: #dc2626;
        }
        
        .error-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .room-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .room-assigned {
            background: #dcfce7;
            color: #166534;
        }
        
        .room-not-assigned {
            background: #fef3c7;
            color: #92400e;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .profile-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        
        .profile-info p {
            font-size: 1rem;
            color: #64748b;
            margin: 0.25rem 0 0 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Student Details</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="welcome-title">
                        <i class="fas fa-id-card text-cyan-500 mr-3"></i>
                        My Personal Details
                    </h2>
                    <p class="text-slate-600 text-lg">View your complete profile information</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-slate-500">Last Updated</div>
                    <div class="text-slate-700 font-medium"><?php echo date('M d, Y'); ?></div>
                </div>
            </div>
        </div>

        <?php if ($student): ?>
            <!-- Profile Header -->
            <div class="glass-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                        <p>Student ID: <?php echo htmlspecialchars($student['ID']); ?></p>
                        <?php if ($student['Room_No']): ?>
                            <span class="room-status room-assigned">
                                <i class="fas fa-home"></i>
                                Room <?php echo htmlspecialchars($student['Room_No']); ?>
                            </span>
                        <?php else: ?>
                            <span class="room-status room-not-assigned">
                                <i class="fas fa-exclamation-triangle"></i>
                                No Room Assigned
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detailed Information -->
            <div class="glass-card">
                <h3 class="section-title">
                    <i class="fas fa-user text-blue-500"></i>
                    Personal Information
                </h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-id-badge text-blue-500"></i>
                            Student ID
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($student['ID']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-user text-green-500"></i>
                            Full Name
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($student['name']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-envelope text-purple-500"></i>
                            Email Address
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($student['email']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-phone text-orange-500"></i>
                            Phone Number
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($student['phonenumber']); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-calendar-alt text-red-500"></i>
                            Duration of Stay
                        </div>
                        <div class="detail-value"><?php echo htmlspecialchars($student['Duration_of_stay'] ?: 'Not specified'); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-home text-cyan-500"></i>
                            Room Number
                        </div>
                        <div class="detail-value">
                            <?php if ($student['Room_No']): ?>
                                Room <?php echo htmlspecialchars($student['Room_No']); ?>
                            <?php else: ?>
                                <span style="color: #f59e0b;">Not assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Error State -->
            <div class="glass-card">
                <div class="error-card">
                    <div class="error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">No Student Details Found</h3>
                    <p class="text-base">We couldn't find your student information in our system. Please contact the administrator or check your login session.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="text-center">
            <a href="student_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>