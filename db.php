<?php
// Database configuration for MySQL (XAMPP)

$host = 'localhost';
$dbname = 'bike_marketplace';
$user = 'root';
$password = ''; // default XAMPP MySQL password is empty

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage();
    exit();
}
?>
