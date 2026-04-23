<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BikeHub - Bike Marketplace</title>
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
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="signup.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content">
            <h2>Welcome to BikeHub</h2>
            <p>Find your perfect bike or sell yours today</p>
            <a href="browse.php" class="btn btn-primary">Browse Bikes</a>
        </div>
    </header>

    <main>
        <section class="featured">
            <div class="container">
                <h2>Featured Bikes</h2>
                <div class="bikes-grid" id="featured-bikes">
                    <!-- Featured bikes will load here -->
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
