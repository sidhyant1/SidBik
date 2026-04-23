<?php
session_start();
require 'config/db.php';

// Redirect if not logged in
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $brand = $_POST['brand'] ?? '';
    $type = $_POST['type'] ?? '';
    $price = $_POST['price'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $year = $_POST['year'] ?? '';
    $description = $_POST['description'] ?? '';

    if(empty($name) || empty($brand) || empty($type) || empty($price) || empty($condition)) {
        $error = 'All required fields must be filled';
    } else {
        try {
            $sql = "INSERT INTO bikes (seller_id, name, brand, type, price, condition, year, description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if($stmt->execute([$_SESSION['user_id'], $name, $brand, $type, $price, $condition, $year, $description])) {
                $success = 'Bike listed successfully!';
                // Clear form
                $_POST = [];
            } else {
                $error = 'Error listing bike. Please try again.';
            }
        } catch(Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Bike - BikeHub</title>
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
                <li><a href="sell-bike.php" style="color: var(--accent-color); font-weight: 600;">Sell Bike</a></li>
                <li><a href="my-bikes.php">My Bikes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="form-container">
        <h2>List Your Bike for Sale</h2>
        
        <?php if($error): ?>
            <div style="background-color: #fee; color: #c33; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div style="background-color: #efe; color: #3c3; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Bike Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Brand *</label>
                <input type="text" name="brand" value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Type *</label>
                <select name="type" required>
                    <option value="">Select a type</option>
                    <option value="Mountain" <?php echo ($_POST['type'] ?? '') == 'Mountain' ? 'selected' : ''; ?>>Mountain</option>
                    <option value="Road" <?php echo ($_POST['type'] ?? '') == 'Road' ? 'selected' : ''; ?>>Road</option>
                    <option value="Hybrid" <?php echo ($_POST['type'] ?? '') == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    <option value="BMX" <?php echo ($_POST['type'] ?? '') == 'BMX' ? 'selected' : ''; ?>>BMX</option>
                </select>
            </div>
            <div class="form-group">
                <label>Price (USD) *</label>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Condition *</label>
                <select name="condition" required>
                    <option value="">Select condition</option>
                    <option value="New" <?php echo ($_POST['condition'] ?? '') == 'New' ? 'selected' : ''; ?>>New</option>
                    <option value="Like New" <?php echo ($_POST['condition'] ?? '') == 'Like New' ? 'selected' : ''; ?>>Like New</option>
                    <option value="Good" <?php echo ($_POST['condition'] ?? '') == 'Good' ? 'selected' : ''; ?>>Good</option>
                    <option value="Fair" <?php echo ($_POST['condition'] ?? '') == 'Fair' ? 'selected' : ''; ?>>Fair</option>
                </select>
            </div>
            <div class="form-group">
                <label>Year</label>
                <input type="number" name="year" value="<?php echo htmlspecialchars($_POST['year'] ?? date('Y')); ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe your bike's features and condition..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">List Bike</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2026 BikeHub. All rights reserved.</p>
    </footer>
</body>
</html>
