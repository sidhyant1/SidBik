<?php
session_start();
require '../config/db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['recommendations' => []]);
    exit();
}

try {
    // Get user's preferences
    $pref_sql = "SELECT preferred_type FROM user_preferences WHERE user_id = ?";
    $pref_stmt = $conn->prepare($pref_sql);
    $pref_stmt->execute([$_SESSION['user_id']]);
    $prefs = $pref_stmt->fetch(PDO::FETCH_ASSOC);

    $recommendations = [];

    // Strategy 1: If user has a preferred type, recommend bikes of that type
    if($prefs && $prefs['preferred_type']) {
        $sql = "SELECT * FROM bikes 
                WHERE type = ? 
                ORDER BY created_at DESC 
                LIMIT 4";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$prefs['preferred_type']]);
        $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Strategy 2: If not enough recommendations, add popular bikes
    if(count($recommendations) < 4) {
        $limit = 4 - count($recommendations);
        $sql = "SELECT * FROM bikes 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$limit]);
        $popular = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recommendations = array_merge($recommendations, $popular);
    }

    echo json_encode(['recommendations' => $recommendations]);
} catch(Exception $e) {
    echo json_encode(['recommendations' => [], 'error' => $e->getMessage()]);
}
?>
