<?php
session_start();
include 'connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Product ID not provided']);
    exit;
}

$product_id = (int)$_GET['id'];

try {
    // Get product details with brand and category names
    $product_query = "SELECT p.*, b.brand_name, c.categories_name 
                     FROM product p
                     LEFT JOIN brands b ON p.brand_id = b.brand_id
                     LEFT JOIN categories c ON p.categories_id = c.categories_id
                     WHERE p.product_id = ?";

    $stmt = $conn->prepare($product_query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $product_result = $stmt->get_result();
    $product = $product_result->fetch_assoc();

    if (!$product) {
        throw new Exception("Product not found");
    }

    // Get approved reviews with user information
    $reviews_query = "SELECT pr.*, u.username 
                     FROM product_reviews pr
                     LEFT JOIN users u ON pr.user_id = u.user_id
                     WHERE pr.product_id = ? AND pr.status = 'approved'
                     ORDER BY pr.review_date DESC";

    $stmt = $conn->prepare($reviews_query);
    if (!$stmt) {
        throw new Exception("Prepare reviews failed: " . $conn->error);
    }

    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute reviews failed: " . $stmt->error);
    }

    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get active feedback with user information
    $feedback_query = "SELECT pf.*, u.username 
                      FROM product_feedback pf
                      LEFT JOIN users u ON pf.user_id = u.user_id
                      WHERE pf.product_id = ? AND pf.status = 'active'
                      ORDER BY pf.feedback_date DESC";

    $stmt = $conn->prepare($feedback_query);
    if (!$stmt) {
        throw new Exception("Prepare feedback failed: " . $conn->error);
    }

    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute feedback failed: " . $stmt->error);
    }

    $feedback = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Add debug information
    $response = [
        'product' => $product,
        'reviews' => $reviews,
        'feedback' => $feedback,
        'debug' => [
            'product_id' => $product_id,
            'product_found' => !empty($product),
            'reviews_count' => count($reviews),
            'feedback_count' => count($feedback)
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'debug' => [
            'product_id' => $product_id,
            'file' => __FILE__,
            'line' => $e->getLine()
        ]
    ]);
}

$conn->close();
?> 