<?php
session_start();
require 'config/db.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get cart items
$sql = "SELECT c.id, c.quantity, b.* FROM cart c 
        JOIN bikes b ON c.bike_id = b.id 
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - BikeHub</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h1>BikeHub</h1>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="browse.php">Browse Bikes</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin: 40px 0;">Shopping Cart</h2>

        <?php if(count($cart_items) > 0): ?>
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
                <!-- Cart Items -->
                <div>
                    <?php foreach($cart_items as $item): ?>
                        <div style="border: 1px solid var(--border-color); padding: 15px; margin-bottom: 15px; border-radius: 4px; display: flex; gap: 15px;">
                            <div style="flex: 1;">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p style="color: var(--secondary-color); margin-bottom: 10px;"><?php echo htmlspecialchars($item['brand']); ?> • <?php echo htmlspecialchars($item['type']); ?></p>
                                <p style="font-size: 18px; font-weight: 600; color: var(--accent-color);">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div style="text-align: right;">
                                <div style="margin-bottom: 10px;">
                                    <label>Qty: </label>
                                    <input type="number" min="1" max="1" value="<?php echo $item['quantity']; ?>" style="width: 50px; padding: 5px;">
                                </div>
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-secondary">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div style="border: 1px solid var(--border-color); padding: 20px; border-radius: 4px; height: fit-content;">
                    <h3 style="margin-bottom: 20px;">Order Summary</h3>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 18px; font-weight: 600;">
                        <span>Total:</span>
                        <span style="color: var(--accent-color);">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center;">Proceed to Checkout</a>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <p style="margin-bottom: 20px;">Your cart is empty</p>
                <a href="browse.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>

    <script>
        function removeFromCart(cartId) {
            if(confirm('Remove from cart?')) {
                fetch('api/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ cart_id: cartId })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>
