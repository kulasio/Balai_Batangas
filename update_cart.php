<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in']);
    exit();
}

if (!isset($_POST['cart_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$cart_id = (int)$_POST['cart_id'];
$quantity = (int)$_POST['quantity'];
$user_id = $_SESSION['user_id'];

// Check if quantity is valid
if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit();
}

// Verify stock availability
$stock_query = "SELECT p.quantity as stock 
                FROM cart_items ci 
                JOIN product p ON ci.product_id = p.product_id 
                WHERE ci.cart_id = ? AND ci.user_id = ?";
$stmt = $conn->prepare($stock_query);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit();
}

if ($quantity > $item['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit();
}

// Update quantity
$update_query = "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND user_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("iii", $quantity, $cart_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
} 