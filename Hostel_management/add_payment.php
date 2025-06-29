<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$message_class = '';

$monthly_fee = 1000.00;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id       = $_POST['student_id'];
    $amount           = floatval($_POST['amount']);
    $payment_date     = $_POST['payment_date'];
    $due_date         = $_POST['due_date'];
    $penalty          = floatval($_POST['penalty']); // Added penalty input
    $month_of_payment = date('F Y', strtotime($payment_date));
    $paid             = 1;

    // Get already paid amount for this student in the same month
    $stmt = $conn->prepare("SELECT SUM(amount) AS total_paid FROM payment WHERE Student_ID = ? AND month_of_payment = ?");
    $stmt->bind_param("ss", $student_id, $month_of_payment);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $total_paid = floatval($res['total_paid']) + $amount;
    $stmt->close();

    // Remaining balance and status
    $remaining_due = max($monthly_fee - $total_paid, 0);
    if ($remaining_due == 0) {
        $status = "Paid in Full";
    } else {
        $status = ($penalty > 0) ? "Late Payment" : "Partially Paid";
    }

    // Insert payment record (removed Payment_ID from bind as it's likely auto-incremented)
    $stmt = $conn->prepare("INSERT INTO payment (amount, Payment_date, due_date, penalty, month_of_payment, Student_ID) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("dsssss", $amount, $payment_date, $due_date, $penalty, $month_of_payment, $student_id);

    if ($stmt->execute()) {
        $message = "✅ Payment recorded successfully.<br>Penalty: Rs. $penalty<br>Remaining Due: Rs. $remaining_due<br>Status: $status";
        $message_class = 'success';
    } else {
        $message = "❌ Failed to add payment: " . htmlspecialchars($stmt->error);
        $message_class = 'error';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment - HostelSync</title>
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
            max-width: 500px;
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
            background: #8b5cf6;
        }

        .form-title {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-align: center;
        }

        .success {
            color: #10b981;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }

        .error {
            color: #ef4444;
            margin-bottom: 1rem;
            font-weight: 500;
            text-align: center;
        }

        label {
            display: block;
            margin-top: 1rem;
            font-weight: 500;
            color: #1e293b;
        }

        select, input[type="number"], input[type="date"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #1e293b;
            transition: border-color 0.3s ease;
        }

        select:focus, input[type="number"]:focus, input[type="date"]:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
        }

        button {
            margin-top: 1.5rem;
            width: 100%;
            padding: 0.75rem;
            background: #8b5cf6;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        button:hover::before {
            left: 100%;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            background: #7c3aed;
        }

        .btn-back {
            margin-top: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff;
            background: linear-gradient(135deg, #6b7280, #9ca3af);
        }

        .btn-back::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-back:hover::before {
            left: 100%;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #4b5563, #6b7280);
        }

        .button-container {
            text-align: center;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
                margin-top: 6rem;
            }
            .form-title {
                font-size: 2rem;
            }
            select, input[type="number"], input[type="date"] {
                padding: 0.6rem;
            }
            button, .btn-back {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
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
                <span class="text-slate-300 font-medium">Add Payment</span>
            </div>
        </header>

    <!-- Form Container -->
    <div class="form-container">
        <h2 class="form-title">
            <i class="fas fa-plus-circle text-yellow-500"></i>
            Add Payment
        </h2>
        <?php if ($message): ?>
            <div class="<?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="student_id">Student:</label>
            <select name="student_id" id="student_id" required>
                <option value="">-- Select Student --</option>
                <?php
                $students = $conn->query("SELECT s.ID, u.name FROM student s INNER JOIN user u ON s.ID = u.ID WHERE u.role = 'student'");
                while ($row = $students->fetch_assoc()) {
                    echo "<option value='" . $row['ID'] . "'>" . htmlspecialchars($row['name']) . " (ID: " . $row['ID'] . ")</option>";
                }
                ?>
            </select>

            <label for="amount">Amount (Rs.):</label>
            <input type="number" name="amount" step="0.01" required>

            <label for="penalty">Penalty (Rs.):</label>
            <input type="number" name="penalty" step="0.01" value="0.00" required>

            <label for="payment_date">Payment Date:</label>
            <input type="date" name="payment_date" required>

            <label for="due_date">Due Date:</label>
            <input type="date" name="due_date" required>

            <button type="submit">
                <i class="fas fa-save"></i>
                Add Payment
            </button>

            <div class="button-container">
                <a href="admin_dashboard.php" class="btn-back mt-4 shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
            </div>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>