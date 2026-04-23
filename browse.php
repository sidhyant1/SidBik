<?php
session_start();
require 'config/db.php';

// Handle filters
$type_filter = $_GET['type'] ?? '';
$price_min = $_GET['price_min'] ?? 0;
$price_max = $_GET['price_max'] ?? 999999;
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM bikes WHERE price BETWEEN ? AND ?";
$params = [$price_min, $price_max];

if($type_filter) {
    $sql .= " AND type = ?";
    $params[] = $type_filter;
}

if($search) {
    $sql .= " AND (name LIKE ? OR brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Bikes - BikeHub</title>
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
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="my-bikes.php">My Bikes</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main>
        <div class="container">
            <h2 style="margin: 40px 0 20px 0;">Browse Bikes</h2>
            
            <!-- Filters -->
            <div style="background-color: var(--light-gray); padding: 20px; border-radius: 4px; margin-bottom: 30px;">
                <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <input type="text" name="search" placeholder="Search by name or brand..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                    </div>
                    <select name="type" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                        <option value="">All Types</option>
                        <option value="Mountain" <?php echo $type_filter == 'Mountain' ? 'selected' : ''; ?>>Mountain</option>
                        <option value="Road" <?php echo $type_filter == 'Road' ? 'selected' : ''; ?>>Road</option>
                        <option value="Hybrid" <?php echo $type_filter == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        <option value="BMX" <?php echo $type_filter == 'BMX' ? 'selected' : ''; ?>>BMX</option>
                    </select>
                    <input type="number" name="price_min" placeholder="Min Price" value="<?php echo $price_min; ?>" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                    <input type="number" name="price_max" placeholder="Max Price" value="<?php echo $price_max; ?>" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 4px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <!-- Bikes Grid -->
            <div class="bikes-grid">
                <?php if(count($bikes) > 0): ?>
                    <?php foreach($bikes as $bike): ?>
                        <div class="bike-card">
                            <div class="bike-image" style="background-color: var(--light-gray);">
                                <?php if($bike['image_url']): ?>
                                    <img src="/SidBIk/<?php echo htmlspecialchars($bike['image_url'] ?? 'images/noimage.jpg'); ?>"
                                    onerror="this.src='/SidBIk/images/noimage.jpg';"
                                    alt="<?php echo htmlspecialchars($bike['name']); ?>">

                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--secondary-color);">No Image</div>
                                <?php endif; ?>
                            </div>
                            <div class="bike-info">
                                <div class="bike-name"><?php echo htmlspecialchars($bike['name']); ?></div>
                                <div class="bike-brand"><?php echo htmlspecialchars($bike['brand']); ?> • <?php echo htmlspecialchars($bike['type']); ?></div>
                                <div style="color: var(--secondary-color); font-size: 13px; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($bike['bike_condition'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($bike['year']); ?>
                                </div>

                                <div class="bike-price">$<?php echo number_format($bike['price'], 2); ?></div>
                                <div class="bike-actions">
                                    <a href="bike-detail.php?id=<?php echo $bike['id']; ?>" class="btn btn-secondary">View</a>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <button onclick="addToCart(<?php echo $bike['id']; ?>)" class="btn btn-primary">Add to Cart</button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">Add to Cart</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; grid-column: 1 / -1;">
                        <p>No bikes found matching your criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

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
                    alert(data.error);
                }
            });
        }
    </script>
</body>
</html>
