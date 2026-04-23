<?php
session_start();
require '../config/db.php';

// Check if user is admin (for this simple project, we'll make users with ID 1 admin)
// In production, add an is_admin field to users table
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get dashboard statistics
$users_sql = "SELECT COUNT(*) as total FROM users";
$users_stmt = $conn->prepare($users_sql);
$users_stmt->execute();
$users_count = $users_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$bikes_sql = "SELECT COUNT(*) as total FROM bikes";
$bikes_stmt = $conn->prepare($bikes_sql);
$bikes_stmt->execute();
$bikes_count = $bikes_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$orders_sql = "SELECT COUNT(*) as total FROM orders";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->execute();
$orders_count = $orders_stmt->fetch(PDO::FETCH_ASSOC)['total'];

$revenue_sql = "SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'";
$revenue_stmt = $conn->prepare($revenue_sql);
$revenue_stmt->execute();
$revenue = $revenue_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Get recent orders
$recent_sql = "SELECT o.*, u.username FROM orders o 
               JOIN users u ON o.buyer_id = u.id 
               ORDER BY o.order_date DESC LIMIT 5";
$recent_stmt = $conn->prepare($recent_sql);
$recent_stmt->execute();
$recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BikeHub</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .admin-container {
            display: grid;
            grid-template-columns: 200px 1fr;
            min-height: 100vh;
        }

        .admin-sidebar {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .admin-sidebar h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .admin-sidebar ul {
            list-style: none;
        }

        .admin-sidebar ul li {
            margin-bottom: 15px;
        }

        .admin-sidebar ul li a {
            color: var(--white);
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s;
        }

        .admin-sidebar ul li a:hover,
        .admin-sidebar ul li a.active {
            opacity: 0.8;
            font-weight: 600;
        }

        .admin-content {
            padding: 40px;
        }

        .stat-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            padding: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 10px;
        }

        .stat-card .label {
            color: var(--secondary-color);
            font-size: 14px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .admin-table thead {
            background-color: var(--light-gray);
        }

        .admin-table th,
        .admin-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table th {
            font-weight: 600;
            font-size: 13px;
        }

        .admin-table td {
            font-size: 14px;
        }

        .admin-table tbody tr:hover {
            background-color: var(--light-gray);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h3>BikeHub Admin</h3>
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="bikes.php">Bikes</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <h1 style="margin-bottom: 40px;">Dashboard</h1>

            <!-- Statistics -->
            <div class="stat-cards">
                <div class="stat-card">
                    <div class="number"><?php echo $users_count; ?></div>
                    <div class="label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $bikes_count; ?></div>
                    <div class="label">Total Bikes</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $orders_count; ?></div>
                    <div class="label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="number">$<?php echo number_format($revenue, 0); ?></div>
                    <div class="label">Total Revenue</div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div>
                <h2 style="margin-bottom: 20px;">Recent Orders</h2>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Buyer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
