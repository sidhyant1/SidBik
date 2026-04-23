<?php
session_start();
require 'config/db.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$order_id = $_GET['order_id'] ?? null;

if(!$order_id) {
    header('Location: index.php');
    exit();
}

// Get order details
$order_sql = "SELECT * FROM orders WHERE id = ? AND buyer_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$items_sql = "SELECT oi.*, b.name, b.brand FROM order_items oi 
              JOIN bikes b ON oi.bike_id = b.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - BikeHub</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>BikeHub</h1>
            </div>
        </div>
    </nav>

    <div class="container">
        <div style="text-align: center; padding: 40px 20px;">
            <h1 style="color: var(--accent-color); margin-bottom: 20px;">Order Confirmed!</h1>
            <p style="color: var(--secondary-color); margin-bottom: 30px;">Thank you for your purchase</p>
        </div>

        <div style="max-width: 600px; margin: 0 auto; border: 1px solid var(--border-color); padding: 30px; border-radius: 4px;">
            <h2 style="margin-bottom: 20px;">Order #<?php echo $order['id']; ?></h2>
            
            <div style="margin-bottom: 30px;">
                <h3 style="margin-bottom: 15px;">Order Items</h3>
                <?php foreach($items as $item): ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <div>
                            <div><?php echo htmlspecialchars($item['name']); ?></div>
                            <div style="color: var(--secondary-color); font-size: 13px;">Qty: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div style="font-weight: 600;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background-color: var(--light-gray); padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['total_amount'] - $order['tax_amount'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <span>Tax:</span>
                    <span>$<?php echo number_format($order['tax_amount'], 2); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 600;">
                    <span>Total:</span>
                    <span style="color: var(--accent-color);">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="color: var(--secondary-color); font-size: 13px;">Payment Method</div>
                <div style="font-weight: 600;"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="color: var(--secondary-color); font-size: 13px;">Order Status</div>
                <div style="font-weight: 600;"><?php echo ucfirst($order['status']); ?></div>
            </div>

            <a href="browse.php" class="btn btn-primary" style="display: block; text-align: center; padding: 12px; width: 100%;">Continue Shopping</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>
</body>
</html>
