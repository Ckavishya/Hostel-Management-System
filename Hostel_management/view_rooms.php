<?php
session_start();
require 'db_connect.php';

// Only wardens can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'warden') {
    header("Location: login.php");
    exit();
}

// Fetch room records
$sql = "SELECT * FROM room ORDER BY Room_No";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelSync - Room List</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f8fafc, #e0e7ff);
        }
        .dashboard-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding-top: 80px;
            padding-bottom: 20px;
        }
        .dashboard-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            padding: 2rem;
            animation: fadeInUp 1.2s ease-out;
            width: 100%;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.98);
        }
        @keyframes fadeInUp {
            0% { 
                opacity: 0; 
                transform: translateY(60px); 
            }
            100% { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        .btn-elegant {
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
            gap: 0.5rem;
        }
        .btn-elegant::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-elegant:hover::before {
            left: 100%;
        }
        .btn-elegant:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        .btn-edit { 
            background: linear-gradient(135deg, #3b82f6, #60a5fa); 
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        .btn-edit:hover { background: linear-gradient(135deg, #2563eb, #3b82f6); }
        .btn-back { background: linear-gradient(135deg, #6b7280, #9ca3af); }
        .btn-back:hover { background: linear-gradient(135deg, #4b5563, #6b7280); }
        .btn-logout { background: linear-gradient(135deg, #ef4444, #f87171); }
        .btn-logout:hover { background: linear-gradient(135deg, #dc2626, #ef4444); }
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 1rem 3rem;
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
        .welcome-text {
            background: linear-gradient(135deg, #1e293b, #475569);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .subtitle-text {
            color: #64748b;
            line-height: 1.6;
        }
        .room-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .room-table th {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .room-table td {
            padding: 1rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }
        .room-table tr:hover td {
            background: rgba(59, 130, 246, 0.05);
            transform: scale(1.01);
        }
        .room-table tr:last-child td {
            border-bottom: none;
        }
        .occupancy-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            gap: 0.25rem;
        }
        .occupancy-full {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .occupancy-available {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .occupancy-partial {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            color: #d97706;
            border: 1px solid #fed7aa;
        }
        .room-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: #7c3aed;
            border: 1px solid #c4b5fd;
        }
        .ac-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .rent-amount {
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
        }
        .currency-symbol {
            color: #64748b;
            font-size: 0.9rem;
        }
        .actions-container {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .dashboard-content {
                padding: 1rem;
            }
            .glass-card {
                padding: 1rem;
            }
            .header {
                padding: 1rem 2rem;
            }
            .header h1 {
                font-size: 1.75rem;
            }
            .room-table {
                font-size: 0.85rem;
            }
            .room-table th,
            .room-table td {
                padding: 0.75rem 0.5rem;
            }
            .btn-elegant {
                padding: 0.6rem 1rem;
                font-size: 0.875rem;
            }
            .button-container {
                flex-direction: column;
                align-items: center;
            }
        }
        @media (max-width: 640px) {
            .room-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Header -->
    <header class="header text-white flex justify-between items-center">
        <h1>Hostel Management System</h1>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">Warden Dashboard</span>
            
        </div>
    </header>

    <!-- Main Content -->
    <section class="dashboard-section">
        <div class="dashboard-content">
            <div class="glass-card">
                <div class="mb-6 text-center">
                    <h2 class="text-4xl font-bold welcome-text mb-2">
                        <i class="fas fa-bed mr-3"></i>Room Management
                    </h2>
                    <p class="text-lg subtitle-text">
                        View and manage all hostel rooms with real-time occupancy status.
                    </p>
                </div>

                <?php if ($result->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="room-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-door-open mr-2"></i>Room No</th>
                                    <th><i class="fas fa-users mr-2"></i>Capacity</th>
                                    <th><i class="fas fa-home mr-2"></i>Room Type</th>
                                    <th><i class="fas fa-snowflake mr-2"></i>AC Type</th>
                                    <th><i class="fas fa-dollar-sign mr-2"></i>Monthly Rent</th>
                                    <th><i class="fas fa-chart-pie mr-2"></i>Occupancy</th>
                                    <th><i class="fas fa-cogs mr-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $capacity = $row['Capacity'];
                                    $occupied = $row['Occupied_count'];
                                    $occupancy_class = '';
                                    $occupancy_icon = '';
                                    
                                    if ($occupied == 0) {
                                        $occupancy_class = 'occupancy-available';
                                        $occupancy_icon = 'fas fa-check-circle';
                                    } elseif ($occupied >= $capacity) {
                                        $occupancy_class = 'occupancy-full';
                                        $occupancy_icon = 'fas fa-times-circle';
                                    } else {
                                        $occupancy_class = 'occupancy-partial';
                                        $occupancy_icon = 'fas fa-exclamation-circle';
                                    }
                                    ?>
                                    <tr>
                                        <td class="font-semibold text-lg"><?= htmlspecialchars($row['Room_No']) ?></td>
                                        <td class="font-medium"><?= htmlspecialchars($row['Capacity']) ?> persons</td>
                                        <td>
                                            <span class="room-type-badge">
                                                <i class="fas fa-bed mr-1"></i>
                                                <?= htmlspecialchars($row['Room_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="ac-badge">
                                                <i class="fas fa-snowflake mr-1"></i>
                                                <?= htmlspecialchars($row['ac_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="currency-symbol">Rs.</span>
                                            <span class="rent-amount"><?= number_format($row['Monthly_rent']) ?></span>
                                        </td>
                                        <td>
                                            <span class="occupancy-badge <?= $occupancy_class ?>">
                                                <i class="<?= $occupancy_icon ?>"></i>
                                                <?= $occupied ?>/<?= $capacity ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions-container">
                                                <a href="update_room.php?Room_No=<?= urlencode($row['Room_No']) ?>" class="btn-elegant btn-edit">
                                                    <i class="fas fa-edit"></i>
                                                    <span>Edit</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-bed text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Rooms Found</h3>
                        <p class="text-gray-500">There are currently no rooms in the system.</p>
                    </div>
                <?php endif; ?>

                <div class="button-container">
                    <a href="warden_dashboard.php" class="btn-elegant btn-back shadow-lg">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Warden Panel</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>
</html>