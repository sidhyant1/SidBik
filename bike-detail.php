<?php
session_start();
require 'config/db.php';

$bike_id = $_GET['id'] ?? null;

if(!$bike_id) {
    header('Location: browse.php');
    exit();
}

// Track view if logged in
if(isset($_SESSION['user_id'])) {
    $pref_sql = "SELECT preferred_type FROM user_preferences WHERE user_id = ?";
    $pref_stmt = $conn->prepare($pref_sql);
    $pref_stmt->execute([$_SESSION['user_id']]);
}

// Get bike details
$sql = "SELECT b.*, u.username, u.phone, u.email, u.address, u.city 
        FROM bikes b 
        JOIN users u ON b.seller_id = u.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$bike_id]);
$bike = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$bike) {
    header('Location: browse.php');
    exit();
}

// Get similar bikes recommendation
$similar_sql = "SELECT * FROM bikes 
                WHERE type = ? AND id != ? 
                ORDER BY created_at DESC 
                LIMIT 4";
$similar_stmt = $conn->prepare($similar_sql);
$similar_stmt->execute([$bike['type'], $bike_id]);
$similar_bikes = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bike['name']); ?> - BikeHub</title>
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
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="recommendations.php">For You</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin: 40px 0;">
            <!-- Bike Image -->
            <div>
                <div class="bike-image" style="background-color: var(--light-gray); height: 400px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                    <?php if($bike['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" alt="<?php echo htmlspecialchars($bike['name']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    <?php else: ?>
                        <div style="color: var(--secondary-color);">No Image Available</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bike Details -->
            <div>
                <h1 style="font-size: 28px; margin-bottom: 10px;"><?php echo htmlspecialchars($bike['name']); ?></h1>
                <div style="color: var(--secondary-color); margin-bottom: 20px; font-size: 16px;">
                    <?php echo htmlspecialchars($bike['brand']); ?> • <?php echo htmlspecialchars($bike['type']); ?>
                </div>

                <div style="font-size: 32px; font-weight: 600; color: var(--accent-color); margin-bottom: 20px;">
                    $<?php echo number_format($bike['price'], 2); ?>
                </div>

                <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <div style="color: var(--secondary-color); font-size: 13px;">Type</div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($bike['type']); ?></div>
                        </div>
                        <div>
                            <div style="color: var(--secondary-color); font-size: 13px;">Year</div>
                            <div style="font-weight: 600;"><?php echo $bike['year']; ?></div>
                        </div>
                        <div>
                            <div style="color: var(--secondary-color); font-size: 13px;">Condition</div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($bike['bike_condition']); ?></div>
                        </div>
                        <div>
                            <div style="color: var(--secondary-color); font-size: 13px;">Listed On</div>
                            <div style="font-weight: 600;"><?php echo date('M d, Y', strtotime($bike['created_at'])); ?></div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <h3 style="margin-bottom: 10px;">Description</h3>
                    <p style="color: var(--secondary-color); line-height: 1.6;">
                        <?php echo htmlspecialchars($bike['description']); ?>
                    </p>
                </div>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <button onclick="addToCart(<?php echo $bike['id']; ?>)" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 16px; margin-bottom: 15px;">Add to Cart</button>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary" style="display: block; width: 100%; padding: 15px; font-size: 16px; text-align: center; margin-bottom: 15px;">Login to Buy</a>
                <?php endif; ?>

                <div style="border: 1px solid var(--border-color); padding: 15px; border-radius: 4px;">
                    <h3 style="margin-bottom: 15px;">Seller Information</h3>
                    <div style="margin-bottom: 10px;">
                        <div style="color: var(--secondary-color); font-size: 13px;">Name</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($bike['username']); ?></div>
                    </div>
                    <div>
                        <div style="color: var(--secondary-color); font-size: 13px;">Location</div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($bike['city']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Similar Bikes -->
        <?php if(count($similar_bikes) > 0): ?>
            <div style="margin: 60px 0;">
                <h2 style="margin-bottom: 20px;">Similar Bikes</h2>
                <div class="bikes-grid">
                    <?php foreach($similar_bikes as $similar_bike): ?>
                        <div class="bike-card">
                            <div class="bike-image" style="background-color: var(--light-gray);">
                                <?php if($similar_bike['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($similar_bike['image_url']); ?>" alt="<?php echo htmlspecialchars($similar_bike['name']); ?>">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="bike-info">
                                <div class="bike-name"><?php echo htmlspecialchars($similar_bike['name']); ?></div>
                                <div class="bike-brand"><?php echo htmlspecialchars($similar_bike['brand']); ?></div>
                                <div class="bike-price">$<?php echo number_format($similar_bike['price'], 2); ?></div>
                                <div class="bike-actions">
                                    <a href="bike-detail.php?id=<?php echo $similar_bike['id']; ?>" class="btn btn-secondary">View</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>

    <script>
        function addToCart(bikeId) {
            fetch('api/add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ bike_id: bikeId, quantity: 1 })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Added to cart!');
                } else {
                    alert('Please login to add items to cart');
                }
            });
        }
    </script>
</body>
</html>
