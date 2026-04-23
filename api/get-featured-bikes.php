<?php
require '../config/db.php';

header('Content-Type: application/json');

try {
    // Get 6 featured bikes (latest or highest rated)
    $sql = "SELECT * FROM bikes ORDER BY created_at DESC LIMIT 6";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $bikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['bikes' => $bikes]);
} catch(Exception $e) {
    echo json_encode(['bikes' => [], 'error' => $e->getMessage()]);
}
?>
