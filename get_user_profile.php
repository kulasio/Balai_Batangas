<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

$query = "SELECT 
    u.username, 
    u.email, 
    u.profile_picture,
    COUNT(DISTINCT o.order_id) as total_orders,
    COALESCE(SUM(o.total_amount), 0) as total_spent
FROM users u 
LEFT JOIN orders o ON u.user_id = o.user_id
WHERE u.user_id = ?
GROUP BY u.user_id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Ensure total_spent is always a number
$userData['total_spent'] = floatval($userData['total_spent']);

echo json_encode($userData);
?> 