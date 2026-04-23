<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$bike_id = $data['bike_id'] ?? null;
$quantity = $data['quantity'] ?? 1;

if(!$bike_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid bike']);
    exit();
}

try {
    // Check if bike already in cart
    $check_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND bike_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$_SESSION['user_id'], $bike_id]);
    
    if($check_stmt->rowCount() > 0) {
        // Update quantity
        $item = $check_stmt->fetch(PDO::FETCH_ASSOC);
        $update_sql = "UPDATE cart SET quantity = quantity + ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$quantity, $item['id']]);
    } else {
        // Add to cart
        $sql = "INSERT INTO cart (user_id, bike_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $bike_id, $quantity]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Added to cart']);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
