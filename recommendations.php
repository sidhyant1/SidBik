<?php
session_start();
require 'config/db.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user preferences
$pref_sql = "SELECT preferred_type FROM user_preferences WHERE user_id = ?";
$pref_stmt = $conn->prepare($pref_sql);
$pref_stmt->execute([$_SESSION['user_id']]);
$user_prefs = $pref_stmt->fetch(PDO::FETCH_ASSOC);

$recommendations = [];

// Get recommended bikes
if($user_prefs && $user_prefs['preferred_type']) {
    $sql = "SELECT * FROM bikes WHERE type = ? ORDER BY created_at DESC LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_prefs['preferred_type']]);
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If no type preference yet, show all bikes
if(count($recommendations) == 0) {
    $sql = "SELECT * FROM bikes ORDER BY created_at DESC LIMIT 8";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommended for You - BikeHub</title>
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
                <li><a href="recommendations.php" style="color: var(--accent-color); font-weight: 600;">For You</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin: 40px 0;">Recommended for You</h2>
        
        <?php if($user_prefs && $user_prefs['preferred_type']): ?>
            <p style="color: var(--secondary-color); margin-bottom: 30px;">
                Based on your interest in <strong><?php echo htmlspecialchars($user_prefs['preferred_type']); ?></strong> bikes
            </p>
        <?php else: ?>
            <p style="color: var(--secondary-color); margin-bottom: 30px;">
                Browse bikes to get personalized recommendations
            </p>
        <?php endif; ?>

        <div class="bikes-grid">
            <?php if(count($recommendations) > 0): ?>
                <?php foreach($recommendations as $bike): ?>
                    <div class="bike-card" onclick="trackBikeView(<?php echo $bike['id']; ?>)">
                        <div class="bike-image" style="background-color: var(--light-gray);">
                            <?php if($bike['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($bike['image_url']); ?>" alt="<?php echo htmlspecialchars($bike['name']); ?>">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="bike-info">
                            <div class="bike-name"><?php echo htmlspecialchars($bike['name']); ?></div>
                            <div class="bike-brand"><?php echo htmlspecialchars($bike['brand']); ?> • <?php echo htmlspecialchars($bike['type']); ?></div>
                            <div style="color: var(--secondary-color); font-size: 13px; margin-bottom: 8px;"><?php echo htmlspecialchars($bike['condition']); ?> - <?php echo $bike['year']; ?></div>
                            <div class="bike-price">$<?php echo number_format($bike['price'], 2); ?></div>
                            <div class="bike-actions">
                                <a href="bike-detail.php?id=<?php echo $bike['id']; ?>" class="btn btn-secondary">View</a>
                                <button onclick="addToCart(<?php echo $bike['id']; ?>)" class="btn btn-primary">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                    <p>No recommendations available yet. Browse bikes to get started!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>

    <script>
        function trackBikeView(bikeId) {
            fetch('api/track-bike-view.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ bike_id: bikeId })
            })
            .catch(error => console.log('Tracking bike view...'));
        }

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
