<?php
session_start();
require_once 'db_connect.php';

// Allow only logged-in students
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$studentId = $_SESSION['user_id'];  // Directly use the session user_id
$success = '';
$error = '';

// Submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);
    if (empty($type) || empty($description)) {
        $error = "❌ Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO complaint (Complaint_type, Description, Student_ID, Warden_ID) VALUES (?, ?, ?, NULL)");
        $stmt->bind_param("sss", $type, $description, $studentId);
        if ($stmt->execute()) {
            $success = "✅ Complaint submitted successfully.";
        } else {
            $error = "❌ Failed to submit complaint. Please check foreign key constraints.";
        }
        $stmt->close();
    }
}

// Fetch student's complaints
$complaints = $conn->prepare("
    SELECT Complaint_ID, Complaint_type, Description, Status 
    FROM complaint 
    WHERE Student_ID = ?
    ORDER BY Complaint_ID DESC
");
$complaints->bind_param("s", $studentId);
$complaints->execute();
$result = $complaints->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Complaint - HostelSync</title>
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
        
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #1f2937;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
            transform: translateY(-1px);
        }
        
        .form-input:hover, .form-select:hover, .form-textarea:hover {
            border-color: #9ca3af;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        
        .form-select {
            cursor: pointer;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="%236b7280" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4"/></svg>');
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            appearance: none;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #f59e0b, #d97706);
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
            box-shadow: 0 12px 24px rgba(245, 158, 11, 0.3);
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
        
        .input-icon .form-input, .input-icon .form-select {
            padding-left: 2.5rem;
        }
        
        .complaint-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .complaint-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #f1f5f9;
            color: #64748b;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .table-container {
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        th {
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            letter-spacing: 0.5px;
            border: none;
        }
        
        th:first-child {
            border-top-left-radius: 12px;
        }
        
        th:last-child {
            border-top-right-radius: 12px;
        }
        
        td {
            padding: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }
        
        tr:hover td {
            background-color: rgba(59, 130, 246, 0.05);
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }
        
        tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }
        
        .status-pending {
            color: #f59e0b;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .status-resolved {
            color: #10b981;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .no-data {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 2rem;
        }
        
        .description-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
            
            .table-container {
                margin: 1rem -0.5rem;
            }
            
            th, td {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
            
            .description-cell {
                max-width: 150px;
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
                <i class="fas fa-exclamation-triangle text-orange-500 mr-3"></i>
                🛠️ Report a Complaint
            </h2>
            <p class="text-slate-600 text-lg">Submit your complaint and we'll address it promptly</p>
            
            <!-- Complaint Types Preview -->
            <div class="complaint-type-grid">
                <div class="complaint-type-badge">
                    <i class="fas fa-tools"></i>
                    Maintenance
                </div>
                <div class="complaint-type-badge">
                    <i class="fas fa-broom"></i>
                    Cleanliness
                </div>
                <div class="complaint-type-badge">
                    <i class="fas fa-volume-up"></i>
                    Noise
                </div>
                <div class="complaint-type-badge">
                    <i class="fas fa-shield-alt"></i>
                    Security
                </div>
                <div class="complaint-type-badge">
                    <i class="fas fa-ellipsis-h"></i>
                    Other
                </div>
            </div>
        </div>

        <!-- Complaint Form -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-500"></i>
                Complaint Details
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
                        <i class="fas fa-list mr-1"></i>
                        Complaint Type *
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-list"></i>
                        <select name="type" class="form-select" required>
                            <option value="">-- Select Complaint Type --</option>
                            <option value="Maintenance">🔧 Maintenance</option>
                            <option value="Cleanliness">🧹 Cleanliness</option>
                            <option value="Noise">🔊 Noise</option>
                            <option value="Security">🛡️ Security</option>
                            <option value="Other">📝 Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-comment-alt mr-1"></i>
                        Description *
                    </label>
                    <textarea name="description" class="form-textarea" 
                              required placeholder="Please provide detailed information about your complaint..."></textarea>
                    <div class="text-xs text-slate-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Be specific about the issue, location, and any relevant details to help us resolve it quickly.
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Submit Complaint
                </button>
            </form>
        </div>

        <!-- Complaints Table -->
        <div class="glass-card">
            <h3 class="section-title">
                <i class="fas fa-list-alt text-blue-500"></i>
                Your Complaints
            </h3>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>
                                    <i class="fas fa-hashtag mr-2"></i>
                                    ID
                                </th>
                                <th>
                                    <i class="fas fa-tag mr-2"></i>
                                    Type
                                </th>
                                <th>
                                    <i class="fas fa-file-text mr-2"></i>
                                    Description
                                </th>
                                <th>
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['Complaint_ID'] ?></td>
                                <td><?= htmlspecialchars($row['Complaint_type']) ?></td>
                                <td class="description-cell" title="<?= htmlspecialchars($row['Description']) ?>">
                                    <?= htmlspecialchars($row['Description']) ?>
                                </td>
                                <td>
                                    <?php if ($row['Status'] === 'Pending'): ?>
                                        <span class="status-pending">
                                            <i class="fas fa-clock"></i>
                                            Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="status-resolved">
                                            <i class="fas fa-check-circle"></i>
                                            Resolved
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="no-data">
                        <i class="fas fa-info-circle mr-2"></i>
                        You haven't submitted any complaints yet.
                    </div>
                </div>
            <?php endif; ?>
            <?php $complaints->close(); ?>
            
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