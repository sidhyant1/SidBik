<?php
session_start();
require 'config/db.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get cart items
$sql = "SELECT c.id, c.quantity, b.* FROM cart c 
        JOIN bikes b ON c.bike_id = b.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * 0.1;
$total = $subtotal + $tax;

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';

    if(empty($payment_method)) {
        $error = 'Please select a payment method';
    } else {
        try {
            $conn->beginTransaction();

            // Create order
            $order_sql = "INSERT INTO orders (buyer_id, total_amount, tax_amount, payment_method) 
                         VALUES (?, ?, ?, ?)";
            $order_stmt = $conn->prepare($order_sql);
            $order_stmt->execute([$_SESSION['user_id'], $total, $tax, $payment_method]);
            
            $order_id = $conn->lastInsertId();

            // Add order items
            foreach($cart_items as $item) {
                $item_sql = "INSERT INTO order_items (order_id, bike_id, price, quantity) 
                            VALUES (?, ?, ?, ?)";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->execute([$order_id, $item['id'], $item['price'], $item['quantity']]);
            }

            // Clear cart
            $clear_sql = "DELETE FROM cart WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_sql);
            $clear_stmt->execute([$_SESSION['user_id']]);

            $conn->commit();

            $_SESSION['success_order'] = $order_id;
            header('Location: order-confirmation.php?order_id=' . $order_id);
            exit();
        } catch(Exception $e) {
            $conn->rollBack();
            $error = 'Error processing order: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BikeHub</title>
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
        <h2 style="margin: 40px 0;">Checkout</h2>

        <?php if($error): ?>
            <div style="background-color: #fee; color: #c33; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <!-- Checkout Form -->
            <div>
                <form method="POST">
                    <h3 style="margin-bottom: 20px;">Delivery Address</h3>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" required>
                    </div>

                    <h3 style="margin-top: 30px; margin-bottom: 20px;">Payment Method</h3>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="credit_card" required> Credit Card
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="debit_card"> Debit Card
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="payment_method" value="net_banking"> Net Banking
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">Place Order</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div style="border: 1px solid var(--border-color); padding: 20px; border-radius: 4px; height: fit-content;">
                <h3 style="margin-bottom: 20px;">Order Summary</h3>
                <?php foreach($cart_items as $item): ?>
                    <div style="font-size: 13px; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?>
                        <span style="float: right;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <div style="border-top: 1px solid var(--border-color); padding-top: 15px; margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: 600;">
                        <span>Total:</span>
                        <span style="color: var(--accent-color);">$<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>
</body>
</html>
