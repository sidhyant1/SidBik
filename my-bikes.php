<?php
session_start();
require 'config/db.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's bikes
$sql = "SELECT * FROM bikes WHERE seller_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bikes - BikeHub</title>
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
                <li><a href="sell-bike.php">Sell Bike</a></li>
                <li><a href="my-bikes.php" style="color: var(--accent-color); font-weight: 600;">My Bikes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin: 40px 0;">My Listed Bikes</h2>

        <?php if(count($bikes) > 0): ?>
            <div class="bikes-grid">
                <?php foreach($bikes as $bike): ?>
                    <div class="bike-card">
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
                            <div class="bike-price">$<?php echo number_format($bike['price'], 2); ?></div>
                            <div class="bike-actions">
                                <a href="bike-detail.php?id=<?php echo $bike['id']; ?>" class="btn btn-secondary">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <p style="margin-bottom: 20px;">You haven't listed any bikes yet</p>
                <a href="sell-bike.php" class="btn btn-primary">List Your First Bike</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>
</body>
</html>
