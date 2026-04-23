<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$bike_id = $data['bike_id'] ?? null;

if(!$bike_id) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    // Get bike type for preference tracking
    $bike_sql = "SELECT type, price FROM bikes WHERE id = ?";
    $bike_stmt = $conn->prepare($bike_sql);
    $bike_stmt->execute([$bike_id]);
    $bike = $bike_stmt->fetch(PDO::FETCH_ASSOC);

    if(!$bike) {
        echo json_encode(['success' => false]);
        exit();
    }

    // Update user preferences with viewed bike type
    $pref_sql = "UPDATE user_preferences SET preferred_type = ?, updated_at = CURRENT_TIMESTAMP 
                 WHERE user_id = ?";
    $pref_stmt = $conn->prepare($pref_sql);
    $pref_stmt->execute([$bike['type'], $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
