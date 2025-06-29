<?php
session_start();
include 'db_connect.php';

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle GET to fetch current values
if (isset($_GET['id'])) {
    $payment_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM payment WHERE Payment_ID = ?");
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    if (!$payment) {
        echo "❌ Payment record not found.";
        exit();
    }
}

// Handle POST to update values
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_id = $_POST['payment_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $due_date = $_POST['due_date'];
    $penalty = $_POST['penalty']; // allow admin to override
    
    $stmt = $conn->prepare("UPDATE payment SET amount = ?, Payment_date = ?, due_date = ?, penalty = ? WHERE Payment_ID = ?");
    $stmt->bind_param("dssdi", $amount, $payment_date, $due_date, $penalty, $payment_id);
    
    if ($stmt->execute()) {
        header("Location: view_payments.php");
        exit();
    } else {
        echo "❌ Error updating payment: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Payment - HostelSync</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 140px);
        }
        
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        
        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #06b6d4;
        }
        
        .form-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        
        .form-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .form-subtitle {
            color: #64748b;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1rem;
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
            letter-spacing: 0.05em;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            color: #374151;
            background: #f8fafc;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #06b6d4;
            background: white;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        
        .form-input:hover {
            border-color: #cbd5e1;
            background: white;
        }
        
        .submit-btn {
            width: 100%;
            background: #06b6d4;
            color: white;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .submit-btn:hover {
            background: #0891b2;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }
        
        .back-btn {
            background: #64748b;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
            box-sizing: border-box;
        }
        
        .back-btn:hover {
            background: #475569;
            transform: translateY(-1px);
            color: white;
        }
        
        .payment-id-badge {
            background: #06b6d4;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="flex justify-between items-center max-w-6xl mx-auto">
            <h1>Hostel Management System</h1>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 font-medium">Edit Payment</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="form-card">
            <h2 class="form-title">
                <i class="fas fa-edit text-cyan-500 mr-3"></i>
                Edit Payment
            </h2>
            <p class="form-subtitle">Update payment record details</p>
            
            <div class="payment-id-badge">
                Payment ID: <?= htmlspecialchars($payment['Payment_ID']) ?>
            </div>
            
            <form method="post">
                <input type="hidden" name="payment_id" value="<?= htmlspecialchars($payment['Payment_ID']) ?>">
                
                <div class="form-group">
                    <label class="form-label">Amount (Rs.)</label>
                    <input type="number" step="0.01" name="amount" class="form-input" 
                           value="<?= htmlspecialchars($payment['amount']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-input" 
                           value="<?= htmlspecialchars($payment['Payment_date']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-input" 
                           value="<?= htmlspecialchars($payment['due_date']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Penalty (Rs.)</label>
                    <input type="number" step="0.01" name="penalty" class="form-input" 
                           value="<?= htmlspecialchars($payment['penalty']) ?>" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i>
                    Update Payment
                </button>
                
                <a href="view_payments.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Payment Records
                </a>
            </form>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>