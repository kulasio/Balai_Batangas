<?php
// Start the session at the top before any output
session_start();

// Include the database connection file
include 'connection.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database
    $query = "SELECT profile_picture, username FROM users WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    $profile_picture = $user['profile_picture'];
    $username = $user['username'];
} else {
    // If the user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Initialize search and category filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Add price range filters
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? floatval($_GET['min_price']) : null;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? floatval($_GET['max_price']) : null;

// Add these near the top with your other filter initializations
$brands = isset($_GET['brands']) ? array_map('intval', $_GET['brands']) : [];
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$availability = isset($_GET['availability']) ? $_GET['availability'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Modify your existing query to include the new filters
$query = "SELECT p.*, b.brand_name, c.categories_name, 
          COALESCE(AVG(pr.rating), 0) as avg_rating,
          COUNT(DISTINCT pr.review_id) as review_count
          FROM product p 
          LEFT JOIN brands b ON p.brand_id = b.brand_id 
          LEFT JOIN categories c ON p.categories_id = c.categories_id 
          LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
          WHERE p.active = 1 AND p.status = 1";

// Add category filter
if ($category_filter !== 0) {
    $query .= " AND p.categories_id = $category_filter";
}

// Add search filter
if ($search) {
    $query .= " AND (p.product_name LIKE '%$search%' OR b.brand_name LIKE '%$search%')";
}

// Add price range filter
if ($min_price !== null) {
    $query .= " AND p.rate >= $min_price";
}
if ($max_price !== null) {
    $query .= " AND p.rate <= $max_price";
}

// Add brand filter
if (!empty($brands)) {
    $brands_str = implode(',', $brands);
    $query .= " AND p.brand_id IN ($brands_str)";
}

// Add availability filter
if ($availability === 'in_stock') {
    $query .= " AND CAST(p.quantity AS SIGNED) > 0";
}

// Group by to handle the aggregation
$query .= " GROUP BY p.product_id";

// Add rating filter
if ($rating > 0) {
    $query .= " HAVING avg_rating >= $rating";
}

// Add sorting
switch ($sort) {
    case 'name_desc':
        $query .= " ORDER BY p.product_name DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY CAST(p.rate AS DECIMAL(10,2)) ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY CAST(p.rate AS DECIMAL(10,2)) DESC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY avg_rating DESC, review_count DESC";
        break;
    case 'rating_asc':
        $query .= " ORDER BY avg_rating ASC, review_count DESC";
        break;
    default: // name_asc
        $query .= " ORDER BY p.product_name ASC";
}

$products = mysqli_query($conn, $query);

// Fetch all categories for the sidebar
$categories_query = "SELECT * FROM categories ORDER BY categories_name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch user's addresses
$addresses_query = "SELECT *, receiver_name FROM user_addresses WHERE user_id = ? AND is_default = 1";
$stmt = $conn->prepare($addresses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$default_address = $stmt->get_result()->fetch_assoc();

// Update the cart total calculation (around line 2156)
$cart_total_query = "SELECT SUM(c.quantity * p.rate) as subtotal 
                     FROM cart_items c 
                     JOIN product p ON c.product_id = p.product_id 
                     WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subtotal = $result->fetch_assoc()['subtotal'] ?? 0;
$shipping_fee = 100; // Fixed shipping fee
$cart_total = $subtotal + $shipping_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/responsive.css">
    <title>Explore Batangas - Shop</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html, body {
            height: 100%;
            background-color: rgb(244, 244, 244);
            color: rgb(51, 51, 51);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navigation Bar */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #8B0000;
            padding: 10px 20px;
            color: rgb(255, 255, 255);
            font-size: 25px;
        }

        /* Logo Section */
        header .logo img {
            max-height: 50px;
            margin-top: 10px;
        }

        /* Navigation Links */
        header nav ul {
            list-style: none;
            display: flex;
            align-items: center;
        }

        header nav ul li {
            margin-right: 30px;
        }

        header nav ul li a {
            text-decoration: none;
            color: rgb(255, 255, 255);
            font-weight: bold;
        }

        header nav ul li a:hover {
            color: rgb(0, 0, 0);
        }

        /* User Profile Styling */
        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f0f0;
            border: 2px solid #fff;
        }

        .user-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Ensure the image maintains aspect ratio */
        .user-menu img.user-icon {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .user-menu {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 150px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a, .dropdown-content p {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .user-menu:hover .dropdown-content {
            display: block;
        }

        /* Updated Shop Specific Styles */
        .shop-container {
            padding: 10px;
            flex: 1;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-template-rows: repeat(4, auto);
            gap: 15px;
            padding: 10px;
        }

        .product-card {
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
        }

        .product-card img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            background: #fff;
            padding: 10px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-info h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }

        .brand, .category {
            color: #666;
            margin: 5px 0;
            font-size: 0.9em;
        }

        .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #8B0000;
            margin: 10px 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .original-price {
            font-size: 0.9em;
            color: #666;
            text-decoration: none;
        }

        .peso-price {
            color: #8B0000;
        }

        .add-to-cart {
            width: 100%;
            padding: 10px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .add-to-cart:hover {
            background-color: #660000;
        }

        .out-of-stock {
            width: 100%;
            padding: 10px;
            background-color: #ccc;
            color: #666;
            border: none;
            border-radius: 4px;
            cursor: not-allowed;
        }

        /* Footer Section */
        footer {
            background-color: #8B0000;
            color: rgb(255, 255, 255);
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .footer-content p {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgb(0, 0, 0);
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Add this to your existing style section */
        .shop-layout {
            display: flex;
            gap: 20px;
            padding: 20px;
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            margin-bottom: 15px;
            color: #8B0000;
        }

        .category-list {
            list-style: none;
        }

        .category-list li {
            margin-bottom: 10px;
        }

        .category-list a {
            text-decoration: none;
            color: #333;
            display: block;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .category-list a:hover,
        .category-list a.active {
            background-color: #f0f0f0;
            color: #8B0000;
        }

        .search-container {
            margin-bottom: 20px;
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .search-container button {
            width: 100%;
            padding: 10px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #660000;
        }

        .main-content {
            flex: 1;
        }

        /* Add these new styles */
        .category-divider {
            margin: 30px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
        }

        .category-divider h2 {
            color: #8B0000;
            font-size: 1.5em;
            margin: 0;
        }

        .products-grid {
            margin-bottom: 30px;
        }

        /* Modify the existing products-grid style */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 10px 0;
        }

        /* Add to your existing styles */
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .buy-now, .add-to-cart {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .buy-now {
            background-color: #2ecc71;
            color: white;
        }

        .buy-now:hover {
            background-color: #27ae60;
        }

        .add-to-cart {
            background-color: #8B0000;
            color: white;
        }

        .add-to-cart:hover {
            background-color: #660000;
        }

        .out-of-stock {
            width: 100%;
            padding: 10px;
            background-color: #ccc;
            color: #666;
            border: none;
            border-radius: 4px;
            cursor: not-allowed;
        }

        /* Update product-info to accommodate the new button layout */
        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            margin: 0 0 10px 0;
            font-size: 1.2em;
        }

        /* Add to your existing styles */
        .cart-icon {
            position: relative;
            margin-right: 20px;
        }

        .cart-icon a {
            font-size: 24px;
            color: white;
            text-decoration: none;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #2ecc71;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            min-width: 18px;
            text-align: center;
        }

        .checkout-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .checkout-btn:hover {
            background-color: #27ae60;
        }

        .cart-total {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .cart-total.minimized {
            transform: translateY(calc(100% - 60px));
        }

        .cart-total-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .cart-total-header h3 {
            color: #333;
            font-size: 1.2em;
            margin: 0;
        }

        .minimize-btn {
            background: none;
            border: none;
            color: #8B0000;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .minimize-btn:hover {
            color: #660000;
        }

        .cart-total.minimized .minimize-btn {
            transform: rotate(180deg);
        }

        .cart-total-content {
            transition: opacity 0.3s ease;
        }

        .cart-total.minimized .cart-total-content {
            opacity: 0;
            pointer-events: none;
        }

        /* Update existing cart total styles */
        .cart-summary {
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .subtotal-row, .shipping-row, .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .shipping-row {
            color: #666;
            font-size: 0.95em;
            border-bottom: 1px dashed #eee;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .total-row {
            font-weight: 600;
            color: #8B0000;
            font-size: 1.2em;
            padding-top: 12px;
        }

        /* Shipping Address Styles */
        .shipping-address {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
        }

        .shipping-address h4 {
            color: #333;
            font-size: 1em;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #8B0000;
        }

        .shipping-address .receiver-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1em;
        }

        .shipping-address .receiver-name i {
            color: #8B0000;
            font-size: 14px;
        }

        .shipping-address p {
            margin: 8px 0;
            color: #555;
            font-size: 0.95em;
            line-height: 1.4;
        }

        .shipping-address p i {
            width: 20px;
            color: #666;
            margin-right: 8px;
        }

        .change-address {
            display: inline-block;
            margin-top: 15px;
            color: #8B0000;
            text-decoration: none;
            font-size: 0.9em;
            padding: 8px 12px;
            border: 1px solid #8B0000;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .change-address:hover {
            background: #8B0000;
            color: white;
        }

        .add-address-btn {
            display: block;
            text-align: center;
            background: #8B0000;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        .add-address-btn:hover {
            background: #660000;
        }

        /* Checkout Button */
        .checkout-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #8B0000;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .checkout-btn:hover {
            background: #660000;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .cart-total {
                position: fixed;
                bottom: 0;
                right: 0;
                width: 100%;
                border-radius: 12px 12px 0 0;
                padding: 20px;
            }

            .shipping-address {
                margin-bottom: 60px; /* Space for mobile navigation */
            }
        }

        /* Add these styles to your existing CSS */
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .shipping-address {
            background: #f8f8f8;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .change-address, .add-address-btn {
            display: inline-block;
            padding: 5px 10px;
            background: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }

        .change-address:hover, .add-address-btn:hover {
            background: #660000;
        }

        /* Modal Styles */
        .product-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            border-radius: 8px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            position: relative;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .modal-body {
            padding: 20px;
        }

        .product-details {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .product-info {
            flex: 1;
        }

        .product-info p {
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        #modalImage {
            max-width: 300px;
            height: auto;
            object-fit: cover;
            border-radius: 8px;
        }

        .modal-content {
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-body {
            padding: 20px;
        }

        /* Ensure proper spacing without the buttons */
        .product-info {
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-tabs {
            margin-top: 20px;
        }

        .tab-buttons {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: all 0.3s ease;
        }

        .tab-button:hover {
            color: #8B0000;
        }

        .tab-button.active {
            color: #8B0000;
            border-bottom: 2px solid #8B0000;
        }

        .tab-panel {
            display: none;
            padding: 20px 0;
        }

        .tab-panel.active {
            display: block;
        }

        /* Make sure the description tab is visible by default */
        #description.tab-panel {
            display: block;
        }

        /* Style the reviews and feedback content */
        .review-item, .feedback-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .review-item:last-child, .feedback-item:last-child {
            border-bottom: none;
        }

        .review-header, .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .username {
            font-weight: bold;
            color: #333;
        }

        .review-meta, .feedback-date {
            color: #666;
            font-size: 0.9em;
        }

        .rating {
            color: #ffd700;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .review-content, .feedback-content {
            margin: 15px 0;
            line-height: 1.6;
        }

        .admin-response {
            margin-top: 15px;
            padding: 15px;
            background: #f5f5f5;
            border-left: 3px solid #8B0000;
        }

        .helpful-count {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .helpful-count i {
            color: #8B0000;
            margin-right: 5px;
        }

        .review-item.pending {
            opacity: 0.7;
            background-color: #f9f9f9;
            border-left: 3px solid #ffd700;
            padding-left: 15px;
        }

        .no-reviews, .no-feedback {
            text-align: center;
            color: #666;
            padding: 20px;
            font-style: italic;
        }

        .rating-select {
            margin-bottom: 15px;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 25px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }

        /* Add to your existing CSS */
        .price-container {
            margin: 15px 0;
            font-size: 1.2em;
        }

        .usd-price {
            color: #666;
            font-size: 0.9em;
            text-decoration: line-through;
            margin-bottom: 5px;
        }

        .php-price {
            color: #8B0000;
            font-weight: bold;
            font-size: 1.2em;
        }

        /* Add to your existing CSS */
        .review-form, .feedback-form {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .review-form h3, .feedback-form h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .rating-select {
            margin-bottom: 15px;
        }

        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 25px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }

        textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
        }

        .submit-btn {
            background: #8B0000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #660000;
        }

        /* Reviews and Feedback Styles */
        .reviews-summary {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .average-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .big-rating {
            font-size: 2em;
            font-weight: bold;
            color: #8B0000;
        }

        .out-of {
            font-size: 1.2em;
            color: #666;
        }

        .total-reviews {
            font-size: 1.2em;
            color: #666;
        }

        .reviews-list {
            margin-bottom: 20px;
        }

        .review-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            margin-bottom: 10px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .username {
            font-weight: bold;
        }

        .review-meta {
            display: flex;
            align-items: center;
        }

        .rating {
            font-size: 1.2em;
            font-weight: bold;
            color: #8B0000;
            margin-right: 10px;
        }

        .date {
            font-size: 0.9em;
            color: #666;
        }

        .review-content {
            margin-bottom: 10px;
        }

        .admin-response {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
        }

        .no-reviews {
            text-align: center;
            color: #666;
        }

        .feedback-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
            margin-bottom: 10px;
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .feedback-author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .feedback-date {
            font-size: 0.9em;
            color: #666;
        }

        .feedback-content {
            margin-bottom: 10px;
        }

        .helpful-count {
            color: #666;
            font-size: 0.9em;
        }

        .no-feedback {
            text-align: center;
            color: #666;
        }

        /* Reviews and Feedback Styles */
        .reviews-summary {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .big-rating {
            font-size: 48px;
            color: #8B0000;
            font-weight: bold;
        }

        .out-of {
            font-size: 24px;
            color: #666;
        }

        .total-reviews {
            color: #666;
            margin-top: 10px;
        }

        .review-item, .feedback-item {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .username {
            font-weight: bold;
            color: #333;
        }

        .review-meta, .feedback-date {
            color: #666;
            font-size: 0.9em;
        }

        .rating {
            color: #ffd700;
            font-size: 1.2em;
        }

        .review-content, .feedback-content {
            margin: 15px 0;
            line-height: 1.6;
        }

        .admin-response {
            margin-top: 15px;
            padding: 15px;
            background: #f5f5f5;
            border-left: 3px solid #8B0000;
        }

        .helpful-count {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }

        .helpful-count i {
            color: #8B0000;
            margin-right: 5px;
        }

        .no-reviews, .no-feedback {
            text-align: center;
            color: #666;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .review-item.pending {
            opacity: 0.7;
            background-color: #f9f9f9;
            border-left: 3px solid #ffd700;
        }

        .review-item.pending::after {
            content: '(Pending Admin Approval)';
            display: block;
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }

        /* Modal Close Button Styles */
        .modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #666;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            color: #8B0000;
            background-color: rgba(0, 0, 0, 0.1);
        }

        /* Make sure modal header has proper positioning */
        .modal-header {
            position: relative;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        /* Bottom Navigation and Logout Styles */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            z-index: 1000;
        }

        .logout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: right;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #660000;
            transform: translateY(-2px);
        }

        .logout-btn i {
            font-size: 1.2em;
        }

        /* Adjust main content to prevent overlap with bottom nav */
        body {
            padding-bottom: 70px; /* Adjust this value based on your bottom nav height */
        }

        /* Make sure modal appears above bottom nav */
        .product-modal {
            z-index: 1001;
        }

        .navigation {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 20px;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-btn:hover {
            color: #8B0000;
        }

        /* Price Filter Styles */
        .price-filter {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .price-filter h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }

        .price-inputs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .input-group {
            flex: 1;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #666;
        }

        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .apply-price-filter {
            width: 100%;
            padding: 10px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .apply-price-filter:hover {
            background-color: #660000;
        }

        /* Add these styles to your existing CSS */
        .filter-section {
            margin-top: 20px;
        }

        .filter-group {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-group h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        .checkbox-group, .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .checkbox-label, .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
            cursor: pointer;
        }

        .checkbox-label input, .radio-label input {
            cursor: pointer;
        }

        .sort-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            color: #333;
            background-color: white;
            cursor: pointer;
        }

        .sort-select:focus {
            outline: none;
            border-color: #8B0000;
        }

        /* Star rating styles */
        .radio-label {
            color: #666;
        }

        .radio-label:hover {
            color: #8B0000;
        }

        .radio-label input:checked + span {
            color: #8B0000;
        }

        /* Add these styles to your existing CSS */
        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 8px 0;
        }

        .stars {
            color: #ffd700;
            font-size: 14px;
            display: flex;
            gap: 2px;
        }

        .rating-count {
            color: #666;
            font-size: 12px;
        }

        /* Star colors */
        .stars .fas.fa-star,
        .stars .fas.fa-star-half-alt {
            color: #ffd700;
        }

        .stars .far.fa-star {
            color: #ddd;
        }

        /* Hover effect on product cards */
        .product-card:hover .stars {
            transform: scale(1.05);
            transition: transform 0.2s ease;
        }

        /* Filter Buttons Styles */
        .filter-buttons {
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            position: sticky;
            bottom: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .apply-filters-btn, 
        .clear-filters-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .apply-filters-btn {
            background-color: #8B0000;
            color: white;
        }

        .apply-filters-btn:hover {
            background-color: #660000;
        }

        .clear-filters-btn {
            background-color: #f4f4f4;
            color: #333;
            border: 1px solid #ddd;
        }

        .clear-filters-btn:hover {
            background-color: #e4e4e4;
        }

        /* Make the filter buttons sticky on scroll */
        .sidebar {
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }

        /* Scrollbar styles for the sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Product Section Styles */
        .product-section {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Category Header */
        .category-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px 0;
            margin: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }

        .category-header h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        .category-tag {
            background-color: #8B0000;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            padding: 20px 0;
        }

        /* Product Card */
        .product-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Product Image */
        .product-image {
            position: relative;
            aspect-ratio: 1;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .out-of-stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        /* Product Info */
        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 16px;
            color: #333;
            margin: 0 0 10px 0;
            height: 40px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        /* Rating Stars */
        .product-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .stars {
            color: #ffd700;
            font-size: 14px;
        }

        .rating-count {
            color: #666;
            font-size: 12px;
        }

        /* Price */
        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #8B0000;
            margin: 10px 0;
        }

        /* Stock Status */
        .stock-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .in-stock {
            color: #28a745;
        }

        .out-of-stock {
            color: #dc3545;
        }

        /* Product Actions */
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .view-details-btn,
        .add-to-cart-btn {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .view-details-btn {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
        }

        .view-details-btn:hover {
            background-color: #e9ecef;
        }

        .add-to-cart-btn {
            background-color: #8B0000;
            color: white;
        }

        .add-to-cart-btn:hover {
            background-color: #660000;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 15px;
            }

            .category-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .product-actions {
                flex-direction: column;
            }
        }

        /* Star Rating Styles */
        .star-rating {
            display: inline-flex;
            flex-direction: row-reverse;
            gap: 8px;
            padding: 10px 0;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
            padding: 0 2px;
            line-height: 1;
        }

        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffd700;
        }

        /* Product Rating Display */
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            margin-left: 10px;
        }

        .stars {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        .stars .fas.fa-star,
        .stars .fas.fa-star-half-alt {
            color: #ffd700;
            font-size: 16px;
        }

        .stars .far.fa-star {
            color: #ddd;
            font-size: 16px;
        }

        .rating-count {
            color: #666;
            font-size: 14px;
            margin-left: 5px;
        }

        /* Rating Select in Review Form */
        .rating-select {
            margin: 15px 0;
        }

        .rating-select span {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        /* Product Card Rating Styles */
        .product-info .rating-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 12px 0;
            padding: 5px 0;
        }

        .product-info .stars {
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .product-info .stars i {
            font-size: 14px;
            color: #ddd;
        }

        .product-info .stars i.fas.fa-star,
        .product-info .stars i.fas.fa-star-half-alt {
            color: #ffd700;
        }

        .product-info .rating-count {
            font-size: 0.85em;
            color: #666;
            margin-left: 5px;
        }

        .product-info .rating-text {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .product-info .rating-value {
            font-weight: 500;
            color: #333;
        }

        .shipping-address .receiver-name {
            font-weight: bold;
            color: #8B0000;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .shipping-address .receiver-name i {
            font-size: 14px;
        }

        .shipping-address p {
            margin: 4px 0;
        }

        .shipping-address p i {
            margin-right: 8px;
            color: #666;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 1000px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .product-modal-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .product-image-section img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-info-section {
            padding: 20px;
        }

        .product-info-section h2 {
            margin: 0 0 15px 0;
            color: #333;
        }

        .price {
            font-size: 24px;
            color: #8B0000;
            font-weight: bold;
            margin: 15px 0;
        }

        .description {
            margin: 20px 0;
            line-height: 1.6;
            color: #666;
        }

        .reviews-section,
        .feedback-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .reviews-summary {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .reviews-list,
        .feedback-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .review-item,
        .feedback-item {
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .add-to-cart-btn,
        .buy-now-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .add-to-cart-btn {
            background-color: #8B0000;
            color: white;
        }

        .buy-now-btn {
            background-color: #4CAF50;
            color: white;
        }

        .add-to-cart-btn:hover {
            background-color: #660000;
        }

        .buy-now-btn:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .product-modal-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="img/logo3.png" alt="Logo" />
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="library.php">Library</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="about.php">About</a></li>
                
                <!-- Add Cart Icon -->
                <li class="cart-icon">
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </li>

                <!-- User Profile Section -->
                <div class="user-menu">
                    <?php 
                    // Clean up the profile picture path
                    $profile_path = $profile_picture;
                    
                    // Debug info (you can remove this later)
                    echo "<!-- Profile path: " . $profile_path . " -->";
                    ?>
                    
                    <?php if (!empty($profile_path)): ?>
                        <img src="<?php echo $profile_path; ?>" 
                             alt="Profile" 
                             class="user-icon"
                             onerror="this.src='assets/images/default-avatar.png'">
                    <?php else: ?>
                        <div class="user-icon">
                            <?php echo !empty($username) ? strtoupper(substr($username, 0, 1)) : '?' ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="dropdown-content">
                        <p><?php echo !empty($username) ? htmlspecialchars($username) : 'User'; ?></p>
                        <a href="userpanel.php">Dashboard</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </ul>
        </nav>
    </header>

    <main class="shop-container">
        <div class="shop-layout">
            <!-- Sidebar -->
            <div class="sidebar">
                <form class="filter-form" method="GET" action="">
                    <!-- Search Bar -->
                    <div class="search-container">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <!-- Categories -->
                    <div class="filter-group">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <label class="radio-label">
                                    <input type="radio" name="category" value="0" 
                                           <?php echo $category_filter === 0 ? 'checked' : ''; ?>>
                                    All Categories
                                </label>
                            </li>
                            <?php while($category = mysqli_fetch_assoc($categories_result)) { ?>
                                <li>
                                    <label class="radio-label">
                                        <input type="radio" name="category" value="<?php echo $category['categories_id']; ?>"
                                               <?php echo $category_filter === (int)$category['categories_id'] ? 'checked' : ''; ?>>
                                        <?php echo htmlspecialchars($category['categories_name']); ?>
                                    </label>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <h3>Price Range</h3>
                        <div class="price-inputs">
                            <div class="input-group">
                                <label for="min_price">Min Price:</label>
                                <input type="number" id="min_price" name="min_price" 
                                       value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>" 
                                       placeholder="₱ Min">
                            </div>
                            <div class="input-group">
                                <label for="max_price">Max Price:</label>
                                <input type="number" id="max_price" name="max_price" 
                                       value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>" 
                                       placeholder="₱ Max">
                            </div>
                        </div>
                    </div>

                    <!-- Brands -->
                    <div class="filter-group">
                        <h3>Brands</h3>
                        <?php
                        $brands_query = "SELECT * FROM brands WHERE brand_active = 1 ORDER BY brand_name";
                        $brands_result = mysqli_query($conn, $brands_query);
                        ?>
                        <div class="checkbox-group">
                            <?php while($brand = mysqli_fetch_assoc($brands_result)) { ?>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="brands[]" value="<?php echo $brand['brand_id']; ?>"
                                           <?php echo (isset($_GET['brands']) && in_array($brand['brand_id'], $_GET['brands'])) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($brand['brand_name']); ?>
                                </label>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="filter-group">
                        <h3>Rating</h3>
                        <div class="radio-group">
                            <?php for($i = 5; $i >= 1; $i--) { ?>
                                <label class="radio-label">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>"
                                           <?php echo (isset($_GET['rating']) && $_GET['rating'] == $i) ? 'checked' : ''; ?>>
                                    <?php echo str_repeat('★', $i) . str_repeat('☆', 5-$i); ?> & Up
                                </label>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="filter-group">
                        <h3>Availability</h3>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="availability" value="all"
                                       <?php echo (!isset($_GET['availability']) || $_GET['availability'] == 'all') ? 'checked' : ''; ?>>
                                All Items
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="availability" value="in_stock"
                                       <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'in_stock') ? 'checked' : ''; ?>>
                                In Stock Only
                            </label>
                        </div>
                    </div>

                    <!-- Sort By -->
                    <div class="filter-group">
                        <h3>Sort By</h3>
                        <select name="sort" class="sort-select">
                            <option value="name_asc" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="rating_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rating_desc') ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="filter-buttons">
                        <button type="submit" class="apply-filters-btn">Apply Filters</button>
                        <button type="button" class="clear-filters-btn" onclick="clearFilters()">Clear Filters</button>
                    </div>
                </form>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <h1>Our Products</h1>
                <?php if (mysqli_num_rows($products) > 0) { ?>
                    <div class="products-grid">
                        <?php while($product = mysqli_fetch_assoc($products)) { ?>
                            <div class="product-card" onclick="openProductModal(<?php echo $product['product_id']; ?>)">
                                <?php
                                switch($product['product_name']) {
                                    // Traditional Foods
                                    case 'Suman':
                                        $image_path = "assets/images/products/Suman.png";
                                        break;
                                    case 'Kalamay':
                                        $image_path = "assets/images/products/Kalamay.jpg";
                                        break;
                                    case 'Puto':
                                        $image_path = "assets/images/products/Puto.jpg";
                                        break;
                                    case 'Sinukmani':
                                        $image_path = "assets/images/products/Sinukmani.png";
                                        break;
                                    case 'Tinapay':
                                        $image_path = "assets/images/products/Tinapay.png";
                                        break;
                                    case 'Lomi':
                                        $image_path = "assets/images/products/Lomi.jpg";
                                        break;
                                    case 'Longganisang Batangas':
                                        $image_path = "assets/images/products/Longganisang Batangas.jpg";
                                        break;

                                    // Beverages
                                    case 'Kapeng Barako':
                                        $image_path = "assets/images/products/Kapeng-barako.jpg";
                                        break;
                                    case 'Lambanog':
                                        $image_path = "assets/images/products/Lambanog.jpg";
                                        break;
                                    case 'Kapeng Tablea':
                                        $image_path = "assets/images/products/Kapeng Tablea.png";
                                        break;
                                    case 'El Pasubat':
                                        $image_path = "assets/images/products/El pasubat.png";
                                        break;
                                    case 'Coconut Wine':
                                        $image_path = "assets/images/products/Coconut Wine.jpg";
                                        break;

                                    // Seafood & Preserved Foods
                                    case 'Dried Fish':
                                        $image_path = "assets/images/products/Dried-fish.jpg";
                                        break;
                                    case 'Bagoong Balayan':
                                        $image_path = "assets/images/products/bagoong-balayan.jpg";
                                        break;
                                    case 'Burdang Taal':
                                        $image_path = "assets/images/products/Burdang Taal.jpg";
                                        break;

                                    // Natural Products
                                    case 'Honey':
                                        $image_path = "assets/images/products/Honey.jpg";
                                        break;
                                    case 'Local Honey Products':
                                        $image_path = "assets/images/products/Local Honey-Infused Products.jpg";
                                        break;
                                    case 'Luyang Dilaw':
                                        $image_path = "assets/images/products/Luyang Dilaw.png";
                                        break;
                                    case 'Saging na Saba':
                                        $image_path = "assets/images/products/Saging na saba.jpg";
                                        break;
                                    case 'Cashew Nuts':
                                        $image_path = "assets/images/products/Cashew Nuts.png";
                                        break;

                                    // Handicrafts
                                    case 'Balisong':
                                        $image_path = "assets/images/products/Balisong.jpg";
                                        break;
                                    case 'Taal Lace':
                                        $image_path = "assets/images/products/Taal Lace.png";
                                        break;
                                    case 'Banig':
                                        $image_path = "assets/images/products/Banig.jpg";
                                        break;
                                    case 'Handwoven Basket':
                                        $image_path = "assets/images/products/handwoven basket.jpg";
                                        break;
                                    case 'Native Baskets':
                                        $image_path = "assets/images/products/Native Baskets and Mats.jpg";
                                        break;
                                    case 'Palayok':
                                        $image_path = "assets/images/products/palayok.jpg";
                                        break;
                                    case 'Embroidered Products':
                                        $image_path = "assets/images/products/Embroidered Taal Products.jpg";
                                        break;

                                    // Meat Products
                                    case 'Beef':
                                        $image_path = "assets/images/products/BEEF.png";
                                        break;
                                    case 'Lechon':
                                        $image_path = "assets/images/products/parada ng lechon.png";
                                        break;

                                    // Default case
                                    default:
                                        $image_path = "assets/images/products/default.png";
                                        break;
                                }
                                ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     onerror="this.src='assets/images/products/default.jpg'">
                                <div class="product-info">
                                    <h3><?php echo htmlspecialchars($product['product_name']); ?></h3>
                                    <p class="brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                    <p class="category"><?php echo htmlspecialchars($product['categories_name']); ?></p>
                                    <p class="price">
                                        <span class="peso-price">₱<?php echo number_format($product['rate'], 2); ?></span>
                                    </p>
                                    <p class="stock">Stock: <?php echo $product['quantity']; ?></p>
                                    <div class="button-group">
                                        <?php if($product['quantity'] > 0) { ?>
                                         <button class="add-to-cart" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                                Add to Cart
                                            </button>
                                            <button class="buy-now" onclick="buyNow(<?php echo $product['product_id']; ?>)">
                                                Buy Now
                                            </button>
                                           
                                        <?php } else { ?>
                                            <button class="out-of-stock" disabled>Out of Stock</button>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = round($product['avg_rating'] * 2) / 2; // Round to nearest 0.5
                                        $fullStars = floor($rating);
                                        $halfStar = $rating - $fullStars >= 0.5;
                                        $emptyStars = 5 - ceil($rating);

                                        // Full stars
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="fas fa-star"></i>';
                                        }
                                        // Half star
                                        if ($halfStar) {
                                            echo '<i class="fas fa-star-half-alt"></i>';
                                        }
                                        // Empty stars
                                        for ($i = 0; $i < $emptyStars; $i++) {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="rating-text">
                                        <span class="rating-value"><?php echo number_format($rating, 1); ?></span>
                                        <span class="rating-count">(<?php echo $product['review_count']; ?>)</span>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p>No products found.</p>
                <?php } ?>
            </div>
        </div>
    </main>

    <?php if ($subtotal > 0): ?>
    <div class="cart-total">
        <div class="cart-total-header">
            <h3>Cart Summary</h3>
            <button class="minimize-btn" onclick="toggleCartTotal()">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
        <div class="cart-total-content">
            <div class="cart-summary">
                <div class="subtotal-row">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="shipping-row">
                    <span>Shipping Fee:</span>
                    <span>₱<?php echo number_format($shipping_fee, 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Total:</span>
                    <span>₱<?php echo number_format($cart_total, 2); ?></span>
                </div>
            </div>
            
            <?php if ($default_address): ?>
                <div class="shipping-address">
                    <h4>Shipping to:</h4>
                    <p class="receiver-name">
                        <i class="fas fa-user"></i> 
                        <?php echo htmlspecialchars($default_address['receiver_name']); ?>
                    </p>
                    <p><?php echo htmlspecialchars($default_address['address_line1']); ?></p>
                    <p><?php echo htmlspecialchars($default_address['city']) . ', ' . htmlspecialchars($default_address['state']); ?></p>
                    <p><?php echo htmlspecialchars($default_address['postal_code']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($default_address['phone']); ?></p>
                    <a href="manage_addresses.php" class="change-address">Change Address</a>
                </div>
            <?php else: ?>
                <a href="manage_addresses.php" class="add-address-btn">Add Shipping Address</a>
            <?php endif; ?>
            
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    </div>
    <?php endif; ?>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All rights reserved.</p>
            
        </div>
    </footer>

    <script>
    function updateCartDisplay() {
        // Update cart count
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                // Update all cart count elements
                document.querySelectorAll('.cart-count').forEach(element => {
                    element.textContent = data.count;
                });
                
                // Update cart total
                return fetch('get_cart_total.php');
            })
            .then(response => response.json())
            .then(data => {
                const cartTotalDiv = document.querySelector('.cart-total');
                if (data.total > 0) {
                    if (!cartTotalDiv) {
                        const newCartTotal = document.createElement('div');
                        newCartTotal.className = 'cart-total';
                        newCartTotal.innerHTML = `
                            <p>Cart Total: ${data.formatted_total}</p>
                            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                        `;
                        document.body.appendChild(newCartTotal);
                    } else {
                        cartTotalDiv.querySelector('p').textContent = `Cart Total: ${data.formatted_total}`;
                    }
                } else if (cartTotalDiv) {
                    cartTotalDiv.remove();
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function addToCart(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                updateCartDisplay();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while adding to cart');
        });
    }

    function buyNow(productId) {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'checkout.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        });
    }

    // Update cart display every 5 seconds
    setInterval(updateCartDisplay, 5000);

    // Initial update when page loads
    document.addEventListener('DOMContentLoaded', function() {
        updateCartDisplay();
    });

    function checkNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    data.notifications.forEach(notification => {
                        if (!notification.is_read) {
                            showNotification(notification);
                        }
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function showNotification(notification) {
        // Create notification element
        const notifElement = document.createElement('div');
        notifElement.className = 'notification-toast';
        notifElement.innerHTML = `
            <h4>${notification.title}</h4>
            <p>${notification.message}</p>
        `;

        // Add to document
        document.body.appendChild(notifElement);

        // Remove after 5 seconds
        setTimeout(() => {
            notifElement.remove();
        }, 5000);

        // Mark as read
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notification.notification_id}`
        });
    }

    // Check for notifications every 30 seconds
    setInterval(checkNotifications, 30000);

    // Initial check when page loads
    document.addEventListener('DOMContentLoaded', checkNotifications);

    function openProductModal(productId) {
        // Store the current product ID for reviews/feedback submissions
        window.currentProductId = productId;

        // Show loading state
        const modal = document.getElementById('productModal');
        if (!modal) {
            console.error('Modal element not found');
            return;
        }

        modal.style.display = 'block';

        // Fetch product details, reviews, and feedback
        fetch(`get_product_details.php?id=${productId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data); // Debug log

                if (data.error) {
                    throw new Error(data.error);
                }

                const product = data.product;
                const reviews = data.reviews;
                const feedback = data.feedback;

                if (!product) {
                    throw new Error('Product data is missing');
                }

                // Helper function to safely update element content
                const updateElement = (id, content) => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = content;
                    } else {
                        console.error(`Element with id '${id}' not found`);
                    }
                };

                // Update modal content using the helper function
                updateElement('modalTitle', product.product_name);
                updateElement('modalBrand', `Brand: ${product.brand_name || 'N/A'}`);
                updateElement('modalCategory', `Category: ${product.categories_name || 'N/A'}`);
                updateElement('modalPrice', `₱${parseFloat(product.rate || 0).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`);
                updateElement('modalStock', `Stock: ${product.quantity || 0}`);
                updateElement('modalDescription', product.description || 'No description available.');

                // Handle image with fallback
                const modalImage = document.getElementById('modalImage');
                if (modalImage) {
                    modalImage.src = product.product_image || 'path/to/default-image.jpg';
                    modalImage.onerror = function() {
                        this.src = 'path/to/default-image.jpg';
                    };
                } else {
                    console.error('Modal image element not found');
                }

                // Handle reviews summary
                const reviewsSummary = document.getElementById('reviewsSummary');
                if (reviewsSummary) {
                    const avgRating = reviews.length > 0 
                        ? reviews.reduce((sum, review) => sum + review.rating, 0) / reviews.length 
                        : 0;
                    
                    reviewsSummary.innerHTML = `
                        <div class="average-rating">
                            <span class="big-rating">${avgRating.toFixed(1)}</span>
                            <span class="out-of">/5</span>
                        </div>
                        <div class="total-reviews">
                            Based on ${reviews.length} ${reviews.length === 1 ? 'review' : 'reviews'}
                        </div>
                    `;
                }

                // Handle reviews list
                const reviewsList = document.getElementById('reviewsList');
                if (reviewsList) {
                    if (reviews && reviews.length > 0) {
                        reviewsList.innerHTML = reviews.map(review => `
                            <div class="review-item ${review.status === 'pending' ? 'pending' : ''}" data-review-id="${review.review_id}">
                                <div class="review-header">
                                    <div class="user-info">
                                        <span class="username">${review.username}</span>
                                    </div>
                                    <div class="review-meta">
                                        <div class="rating">${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}</div>
                                        <div class="date">${new Date(review.review_date).toLocaleDateString()}</div>
                                    </div>
                                </div>
                                <div class="review-content">
                                    <p>${review.review_text}</p>
                                </div>
                                ${review.admin_response ? `
                                    <div class="admin-response">
                                        <strong>Admin Response:</strong>
                                        <p>${review.admin_response}</p>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('');
                    } else {
                        reviewsList.innerHTML = '<p class="no-reviews">No reviews yet. Be the first to review this product!</p>';
                    }
                }

                // Handle feedback list
                const feedbackList = document.getElementById('feedbackList');
                if (feedbackList) {
                    if (feedback && feedback.length > 0) {
                        feedbackList.innerHTML = feedback.map(fb => `
                            <div class="feedback-item">
                                <div class="feedback-header">
                                    <div class="user-info">
                                        <span class="username">${fb.username}</span>
                                    </div>
                                    <div class="feedback-date">${new Date(fb.feedback_date).toLocaleDateString()}</div>
                                </div>
                                <div class="feedback-content">
                                    <p>${fb.feedback_text}</p>
                                </div>
                                <div class="helpful-count">
                                    <i class="fas fa-thumbs-up"></i>
                                    ${fb.helpful_count} ${fb.helpful_count === 1 ? 'person found' : 'people found'} this helpful
                                </div>
                            </div>
                        `).join('');
                    } else {
                        feedbackList.innerHTML = '<p class="no-feedback">No feedback yet. Be the first to share your thoughts!</p>';
                    }
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                if (modal) {
                    modal.innerHTML = `
                        <div class="modal-content">
                            <div class="error-message">
                                <h3>Error Loading Product</h3>
                                <p>${error.message}</p>
                                <button onclick="document.getElementById('productModal').style.display='none'">Close</button>
                            </div>
                        </div>
                    `;
                }
            });
    }

    function submitReview(event) {
        event.preventDefault();
        
        if (!currentProductId) {
            alert('Error: Product ID not found');
            return;
        }
        
        const form = event.target;
        const rating = form.querySelector('input[name="rating"]:checked');
        const reviewText = form.querySelector('textarea[name="review_text"]');
        
        if (!rating) {
            alert('Please select a rating');
            return;
        }
        
        if (!reviewText.value.trim()) {
            alert('Please write a review');
            return;
        }
        
        const formData = new FormData();
        formData.append('product_id', currentProductId);
        formData.append('rating', rating.value);
        formData.append('review_text', reviewText.value.trim());
        
        // Show loading state
        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        fetch('submit_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const reviewsList = document.getElementById('reviewsList');
                const newReviewHTML = `
                    <div class="review-item pending" ${data.review.review_id ? `data-review-id="${data.review.review_id}"` : ''}>
                        <div class="review-header">
                            <div class="user-info">
                                <span class="username">${data.review.username}</span>
                            </div>
                            <div class="review-meta">
                                <div class="rating">
                                    ${'★'.repeat(data.review.rating)}${'☆'.repeat(5-data.review.rating)}
                                </div>
                                <div class="date">Just now (Pending Approval)</div>
                            </div>
                        </div>
                        <div class="review-content">
                            <p>${data.review.review_text}</p>
                        </div>
                    </div>
                `;
                
                if (data.review.is_update) {
                    // Try to find and update existing review
                    const existingReview = reviewsList.querySelector(`[data-review-id="${data.review.review_id}"]`);
                    if (existingReview) {
                        existingReview.outerHTML = newReviewHTML;
                    } else {
                        reviewsList.insertAdjacentHTML('afterbegin', newReviewHTML);
                    }
                } else {
                    if (reviewsList.querySelector('.no-reviews')) {
                        reviewsList.innerHTML = newReviewHTML;
                    } else {
                        reviewsList.insertAdjacentHTML('afterbegin', newReviewHTML);
                    }
                }
                
                // Reset form
                form.reset();
                alert('Review submitted successfully! It will be visible after approval.');
            } else {
                alert(data.message || 'Error submitting review');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting review. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = originalButtonText;
        });
    }

    function submitFeedback(event) {
        event.preventDefault();
        
        if (!currentProductId) {
            console.error('No product ID found');
            alert('Error: Product ID not found');
            return;
        }
        
        const formData = new FormData(event.target);
        formData.append('product_id', currentProductId);
        
        console.log('Submitting feedback for product:', currentProductId); // Debug line
        
        fetch('submit_feedback.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Feedback submission response:', data); // Debug line
            if (data.success) {
                alert(data.message);
                event.target.reset();
                
                // Add the new feedback to the display
                const feedbackList = document.getElementById('feedbackList');
                const newFeedbackHTML = `
                    <div class="feedback-item">
                        <div class="feedback-header">
                            <div class="user-info">
                                <span class="username">${data.feedback.username}</span>
                            </div>
                            <div class="feedback-date">Just now</div>
                        </div>
                        <div class="feedback-content">
                            <p>${data.feedback.feedback_text}</p>
                        </div>
                        <div class="helpful-count">
                            <i class="fas fa-thumbs-up"></i>
                            0 people found this helpful
                        </div>
                    </div>
                `;
                
                if (feedbackList.querySelector('.no-feedback')) {
                    feedbackList.innerHTML = newFeedbackHTML;
                } else {
                    feedbackList.insertAdjacentHTML('afterbegin', newFeedbackHTML);
                }
            } else {
                alert(data.message || 'Error submitting feedback');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error submitting feedback');
        });
    }

    // Add this function at the top level of your JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Initial tab setup
        showTab('description');
        
        // Add click handlers to all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                showTab(tabName);
            });
        });
    });

    function showTab(tabName) {
        // Hide all tab panels
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.style.display = 'none';
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        
        // Show the selected tab panel
        const selectedPanel = document.getElementById(tabName);
        if (selectedPanel) {
            selectedPanel.style.display = 'block';
        }
        
        // Add active class to the clicked button
        const selectedButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
        if (selectedButton) {
            selectedButton.classList.add('active');
        }
    }

    // Add these functions for modal handling
    function closeModal() {
        const modal = document.getElementById('productModal');
        modal.style.display = 'none';
        
        // Reset forms when closing
        document.getElementById('reviewForm')?.reset();
        document.getElementById('feedbackForm')?.reset();
        
        // Clear the current product ID
        currentProductId = null;
    }

    // Close modal when clicking the X button or outside the modal
    document.addEventListener('DOMContentLoaded', function() {
        // Close button handler
        const closeButton = document.querySelector('.modal-close');
        if (closeButton) {
            closeButton.addEventListener('click', closeModal);
        }
        
        // Click outside modal handler
        const modal = document.getElementById('productModal');
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        
        // Optional: Add escape key handler
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Function to update filters
        function updateFilters() {
            const form = document.querySelector('.filter-section').closest('form');
            form.submit();
        }

        // Add event listeners to all filter inputs
        document.querySelectorAll('.filter-section input, .filter-section select').forEach(input => {
            input.addEventListener('change', updateFilters);
        });

        // Price range validation
        const priceForm = document.querySelector('.price-range-form');
        const minPrice = document.getElementById('min_price');
        const maxPrice = document.getElementById('max_price');

        priceForm.addEventListener('submit', function(e) {
            const min = parseFloat(minPrice.value);
            const max = parseFloat(maxPrice.value);

            if (min && max && min > max) {
                e.preventDefault();
                alert('Minimum price cannot be greater than maximum price');
                return false;
            }

            if (min < 0 || max < 0) {
                e.preventDefault();
                alert('Price cannot be negative');
                return false;
            }
        });
    });

    function clearFilters() {
        // Get the form element
        const form = document.querySelector('.filter-form');
        
        // Reset search
        form.querySelector('input[name="search"]').value = '';
        
        // Reset category to "All Categories"
        const categoryAll = form.querySelector('input[name="category"][value="0"]');
        if (categoryAll) categoryAll.checked = true;
        
        // Reset price range
        form.querySelector('#min_price').value = '';
        form.querySelector('#max_price').value = '';
        
        // Reset brands
        form.querySelectorAll('input[name="brands[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Reset rating
        const ratingInputs = form.querySelectorAll('input[name="rating"]');
        ratingInputs.forEach(input => {
            input.checked = false;
        });
        
        // Reset availability to "All Items"
        const availabilityAll = form.querySelector('input[name="availability"][value="all"]');
        if (availabilityAll) availabilityAll.checked = true;
        
        // Reset sort to default
        form.querySelector('select[name="sort"]').value = 'name_asc';
        
        // Submit the form
        form.submit();
    }

    // Prevent form submission when pressing Enter in price inputs
    document.querySelectorAll('#min_price, #max_price').forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.apply-filters-btn').click();
            }
        });
    });

    function toggleCartTotal() {
        const cartTotal = document.querySelector('.cart-total');
        cartTotal.classList.toggle('minimized');
        
        // Save state to localStorage
        const isMinimized = cartTotal.classList.contains('minimized');
        localStorage.setItem('cartTotalMinimized', isMinimized);
    }

    // On page load, check saved state
    document.addEventListener('DOMContentLoaded', function() {
        const cartTotal = document.querySelector('.cart-total');
        if (cartTotal) {
            const isMinimized = localStorage.getItem('cartTotalMinimized') === 'true';
            if (isMinimized) {
                cartTotal.classList.add('minimized');
            }
        }
    });
    </script>

    <!-- Product Modal -->
    <div id="productModal" class="product-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"></h2>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="product-details">
                    <img id="modalImage" src="" alt="">
                    <div class="product-info">
                        <p id="modalBrand"></p>
                        <p id="modalCategory"></p>
                        <p id="modalPrice"></p>
                        <p id="modalStock"></p>
                    </div>
                </div>
                <div class="modal-tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="description">Description</button>
                        <button class="tab-button" data-tab="reviews">Reviews</button>
                        <button class="tab-button" data-tab="feedback">Feedback</button>
                    </div>
                    <div class="tab-content">
                        <div id="description" class="tab-panel active">
                            <p id="modalDescription"></p>
                        </div>
                        <div id="reviews" class="tab-panel">
                            <div class="review-form">
                                <h3>Write a Review</h3>
                                <form id="reviewForm" onsubmit="submitReview(event)">
                                    <div class="rating-select">
                                        <span>Your Rating:</span>
                                        <div class="star-rating">
                                            <input type="radio" id="star5" name="rating" value="5" required>
                                            <label for="star5">★</label>
                                            <input type="radio" id="star4" name="rating" value="4">
                                            <label for="star4">★</label>
                                            <input type="radio" id="star3" name="rating" value="3">
                                            <label for="star3">★</label>
                                            <input type="radio" id="star2" name="rating" value="2">
                                            <label for="star2">★</label>
                                            <input type="radio" id="star1" name="rating" value="1">
                                            <label for="star1">★</label>
                                        </div>
                                    </div>
                                    <textarea name="review_text" placeholder="Write your review here..." required></textarea>
                                    <button type="submit" class="submit-btn">Submit Review</button>
                                </form>
                            </div>
                            <div id="reviewsList"></div>
                        </div>
                        <div id="feedback" class="tab-panel">
                            <div class="feedback-form">
                                <h3>Share Your Feedback</h3>
                                <form id="feedbackForm" onsubmit="submitFeedback(event)">
                                    <textarea name="feedback_text" placeholder="Write your feedback here..." required></textarea>
                                    <button type="submit" class="submit-btn">Submit Feedback</button>
                                </form>
                            </div>
                            <div id="feedbackList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this at the bottom of your page, before the closing </body> tag -->
   
</body>
</html>