<?php
session_start();
require '../config/db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Get all users
$sql = "SELECT id, username, email, phone, city, created_at FROM users ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - BikeHub Admin</title>
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
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h3>BikeHub Admin</h3>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="bikes.php">Bikes</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <h1 style="margin-bottom: 30px;">Users Management</h1>
            <p style="margin-bottom: 20px; color: var(--secondary-color);">Total Users: <?php echo count($users); ?></p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>City</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['city'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
