<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$cart_id = $data['cart_id'] ?? null;

if(!$cart_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid item']);
    exit();
}

try {
    $sql = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
