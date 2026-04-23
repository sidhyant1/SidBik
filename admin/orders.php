<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get all orders
$sql = "SELECT o.*, u.username FROM orders o 
        JOIN users u ON o.buyer_id = u.id 
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - BikeHub Admin</title>
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
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="bikes.php">Bikes</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <h1 style="margin-bottom: 30px;">Orders Management</h1>
            <p style="margin-bottom: 20px; color: var(--secondary-color);">Total Orders: <?php echo count($orders); ?></p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Buyer</th>
                        <th>Total Amount</th>
                        <th>Tax</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Order Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>$<?php echo number_format($order['tax_amount'], 2); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
