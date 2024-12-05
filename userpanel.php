<?php
session_start();
require_once 'connection.php';

// Add this code block
if (isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['last_page'] = $_SERVER['HTTP_REFERER'];
}

// Get user stats
$userId = $_SESSION['user_id'];

// Simplified notifications query
$notificationsQuery = "
    SELECT * FROM user_notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5";
$stmt = $conn->prepare($notificationsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$notifications = $stmt->get_result();

// Get pending orders with product details (excluding verified payments)
$pendingOrdersQuery = "
    SELECT 
        o.order_id, 
        o.order_date, 
        o.total_amount,
        GROUP_CONCAT(CONCAT(p.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as ordered_products,
        pp.status as proof_status,
        pp.image_path
    FROM orders o 
    LEFT JOIN order_item oi ON o.order_id = oi.order_id 
    LEFT JOIN product p ON oi.product_id = p.product_id
    LEFT JOIN payment_proofs pp ON o.order_id = pp.order_id
    WHERE o.user_id = ? 
    AND (pp.status IS NULL OR pp.status != 'verified')
    AND o.order_status = 0
    GROUP BY o.order_id, o.order_date, o.total_amount, pp.status, pp.image_path
    ORDER BY o.order_date DESC";

$stmt = $conn->prepare($pendingOrdersQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$pendingOrders = $stmt->get_result();

// Get user statistics
$statsQuery = "
    SELECT 
        COUNT(DISTINCT order_id) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_spent
    FROM orders 
    WHERE user_id = ?";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userStats = $stmt->get_result()->fetch_assoc();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch basic user information
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch order history with status
$orderStmt = $conn->prepare("
    SELECT o.order_id, o.order_date, o.total_amount, ot.status,
           GROUP_CONCAT(p.product_name SEPARATOR ', ') as products
    FROM orders o 
    LEFT JOIN order_tracking ot ON o.order_id = ot.order_id
    JOIN order_item oi ON o.order_id = oi.order_id 
    JOIN product p ON oi.product_id = p.product_id 
    WHERE o.user_id = ? 
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$orderStmt->bind_param("i", $userId);
$orderStmt->execute();
$orders = $orderStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>User Dashboard - Explore Batangas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Base Layout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: #f5f5f5;
        }

        /* Menu Styles */
        .menu {
            width: 250px;
            background: #1a1a1a;
            color: #e0e0e0;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .menu.collapsed {
            width: 80px;
        }

        /* Menu Toggle Button */
        .menu-toggle {
            padding: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            border-bottom: 1px solid #2d2d2d;
        }

        .menu-toggle i {
            color: #e0e0e0;
            font-size: 1.2em;
            transition: transform 0.3s ease;
        }

        /* Menu List */
        .menu-list {
            padding: 15px 10px;
        }

        .menu-list a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #e0e0e0;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 12px;
            transition: all 0.2s ease;
            font-size: 0.95em;
            position: relative;
        }

        .menu-list a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .menu-list a i {
            width: 20px;
            text-align: center;
            margin-right: 12px;
            font-size: 1.1em;
            color: #e0e0e0;
        }

        /* Collapsed State */
        .menu.collapsed .menu-list a {
            padding: 16px;
            justify-content: center;
        }

        .menu.collapsed .menu-list a span {
            display: none;
        }

        .menu.collapsed .menu-list a i {
            margin: 0;
            font-size: 1.4em;
            color: #e0e0e0;
        }

        .menu.collapsed .menu-list a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .menu.collapsed .menu-list a:hover i {
            color: #ffffff;
        }

        /* Notification Badge */
        .notification-count {
            background: #FF0000;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.75em;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
        }

        .menu.collapsed .notification-count {
            right: 8px;
            top: 8px;
            transform: none;
        }

        /* Tooltip for Collapsed Menu */
        .menu.collapsed .menu-list a::after {
            content: attr(data-title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            margin-left: 10px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .menu.collapsed .menu-list a:hover::after {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .menu {
                width: 80px;
            }
            
            .menu-list a {
                padding: 16px;
                justify-content: center;
            }
            
            .menu-list a span {
                display: none;
            }
            
            .menu-list a i {
                margin: 0;
                font-size: 1.4em;
                color: #e0e0e0;
            }
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 20px 30px;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Dashboard Layout */
        .dashboard-header {
            margin-bottom: 20px;
        }

        /* Stats Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            flex: 1;
            min-height: 0; /* Important for nested scrolling */
        }

        /* Section Styles */
        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .section-header {
            margin-bottom: 15px;
        }

        /* Scrollable Containers */
        .recent-orders, 
        .notifications-container {
            overflow-y: auto;
            flex: 1;
            padding-right: 5px;
        }

        /* Custom Scrollbar */
        .recent-orders::-webkit-scrollbar,
        .notifications-container::-webkit-scrollbar {
            width: 6px;
        }

        .recent-orders::-webkit-scrollbar-track,
        .notifications-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .recent-orders::-webkit-scrollbar-thumb,
        .notifications-container::-webkit-scrollbar-thumb {
            background: #8B0000;
            border-radius: 3px;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .content {
                padding: 15px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Enhanced Menu Styles */
        .menu {
            width: 250px;
            background: linear-gradient(180deg, #8B0000 0%, #660000 100%);
            color: white;
            transition: all 0.3s ease;
            overflow: hidden;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .menu.collapsed {
            width: 70px;
        }

        /* Brand/Logo Section */
        .menu-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .menu-brand img {
            height: 40px;
            transition: all 0.3s ease;
        }

        .menu.collapsed .menu-brand img {
            height: 30px;
        }

        /* Menu Icon */
        .menu-icon {
            font-size: 24px;
            padding: 15px;
            cursor: pointer;
            text-align: right;
            color: rgba(255,255,255,0.8);
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .menu-icon:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        /* Menu List */
        .menu-list {
            padding: 15px 10px;
            flex: 0 1 auto;
        }

        .menu-list a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.95rem;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-list a i {
            font-size: 20px;
            min-width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .menu-list a span {
            margin-left: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .menu-list a:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        /* Update the menu-list a.active styles */
        .menu-list a.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .menu-list a.active i {
            background: white;
            color: #8B0000;
            border-radius: 8px;
        }

        /* Add specific styles for collapsed state */
        .menu.collapsed .menu-list a.active {
            background: rgba(255,255,255,0.2);
            position: relative;
        }

        .menu.collapsed .menu-list a.active i {
            background: white;
            color: #8B0000;
            min-width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        /* Update hover effects for collapsed state */
        .menu.collapsed .menu-list a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .menu.collapsed .menu-list a:hover i {
            color: white;
            background: rgba(255,255,255,0.2);
        }

        .menu.collapsed .menu-list a span {
            opacity: 0;
            width: 0;
            display: none;
        }

        .menu.collapsed .menu-list a i {
            min-width: 100%;
        }

        /* Notification Badge */
        .notification-link {
            position: relative;
        }

        .notification-count {
            position: absolute;
            top: 8px;
            right: 15px;
            background: #ff4444;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: bold;
            border: 2px solid #660000;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .menu.collapsed .notification-count {
            right: 5px;
            top: 5px;
            padding: 2px 6px;
        }

        /* User Profile Section at Bottom */
        .menu-profile {
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8B0000;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .profile-info {
            flex: 1;
            transition: all 0.3s ease;
        }

        .profile-name {
            font-weight: 500;
            color: white;
            font-size: 0.9rem;
        }

        .profile-email {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
        }

        .menu.collapsed .profile-info {
            display: none;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .menu {
                width: 70px;
                min-height: 100vh;
            }

            .menu.collapsed {
                width: 0;
                padding: 0;
            }

            .menu-list a span,
            .profile-info {
                display: none;
            }

            .menu-list a i {
                min-width: 100%;
            }

            .notification-count {
                right: 5px;
                top: 5px;
                padding: 2px 6px;
            }
        }

        /* Content Area */
        .content {
            flex: 1;
            padding: 20px 30px;
            overflow-y: auto;
            height: 100vh;
            background: #f5f5f5;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card i {
            font-size: 2em;
            color: #8B0000;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 0;
            color: #333;
        }

        .card p {
            font-size: 1.5em;
            color: #8B0000;
            margin: 10px 0;
        }

        /* Order History Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 16px;
        }

        th, td {
            border: 2px solid black;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #8B0000;
            color: white;
        }

        td {
            color: black;
        }

        /* Profile Section */
        .profile-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1001;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            z-index: 1002;
        }

        .close:hover {
            color: #333;
        }

        .edit-profile-form {
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
        }

        .password-note {
            color: #666;
            font-size: 0.9em;
            margin: 15px 0;
        }

        .submit-btn {
            background: #8B0000;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background: #660000;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .menu {
                width: 100%;
            }

            .content {
                margin-left: 0;
                padding: 10px;
            }

            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-section {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .dashboard-section h2 {
            color: #8B0000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #8B0000;
        }

        .recent-orders {
            overflow-y: auto;
            max-height: calc(100vh - 250px); /* Adjust based on your header height */
            padding-right: 10px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #8B0000;
        }

        /* Custom scrollbar for orders */
        .recent-orders::-webkit-scrollbar {
            width: 6px;
        }

        .recent-orders::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .recent-orders::-webkit-scrollbar-thumb {
            background: #8B0000;
            border-radius: 3px;
        }

        /* Empty state styling */
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .no-orders i {
            font-size: 3em;
            color: #8B0000;
            margin-bottom: 15px;
        }

        .no-orders p {
            margin: 0;
            font-size: 1.1em;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .order-header h3 {
            color: #8B0000;
            margin: 0;
        }

        .order-date {
            color: #6c757d;
            font-size: 0.9em;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 0.95em;
        }

        .proof-preview {
            margin-top: 15px;
            text-align: center;
        }

        .proof-preview img {
            max-width: 200px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .no-orders {
            text-align: center;
            color: #666;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .profile-info {
            background: white;
            padding: 20px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .item-details {
            flex: 1;
        }

        .item-details h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .item-details p {
            margin: 5px 0;
            color: #666;
        }

        .btn-remove {
            background: #8B0000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 15px;
        }

        .btn-remove:hover {
            background: #a52a2a;
        }

        .password-note {
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
            font-style: italic;
        }

        .notifications-section {
            margin-top: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .notifications-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 20px;
        }

        .notification-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(139,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .notification-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #8B0000;
            opacity: 0.7;
        }

        .notification-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(139,0,0,0.15);
        }

        .notification-card.unread {
            background: #fff9f9;
        }

        .notification-card.unread::before {
            background: #ff4444;
            opacity: 1;
        }

        .notification-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(145deg, #8B0000, #a52a2a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 4px rgba(139,0,0,0.2);
        }

        .notification-info {
            flex: 1;
        }

        .notification-info h4 {
            margin: 0;
            color: #333;
            font-size: 1.1em;
            font-weight: 600;
        }

        .notification-date {
            color: #666;
            font-size: 0.85em;
            display: block;
            margin-top: 4px;
        }

        .new-badge {
            background: linear-gradient(145deg, #ff4444, #ff6b6b);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75em;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(255,68,68,0.2);
        }

        .notification-body {
            padding-left: 55px;
        }

        .notification-body p {
            color: #444;
            margin-bottom: 15px;
            line-height: 1.6;
            font-size: 0.95em;
        }

        .view-details-btn {
            background: linear-gradient(145deg, #8B0000, #a52a2a);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-details-btn:hover {
            background: linear-gradient(145deg, #a52a2a, #8B0000);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(139,0,0,0.2);
        }

        .view-details-btn i {
            font-size: 0.9em;
        }

        .no-notifications {
            text-align: center;
            padding: 40px 20px;
            background: #f8f8f8;
            border-radius: 8px;
            color: #666;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .no-notifications i {
            font-size: 2.5em;
            color: #8B0000;
            opacity: 0.5;
        }

        .no-notifications p {
            font-size: 1.1em;
            color: #555;
            margin: 0;
        }

        /* Add responsive styles */
        @media screen and (max-width: 768px) {
            .notification-body {
                padding-left: 0;
            }
            
            .notification-header {
                flex-wrap: wrap;
            }
            
            .notification-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9em;
            }
            
            .new-badge {
                margin-left: auto;
            }
        }

        .notification-link {
            position: relative;
        }

        .notification-count {
            position: absolute;
            top: 5px;
            right: 10px;
            background: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid #8B0000;
        }

        .menu.collapsed .notification-count {
            right: 5px;
        }

        /* Update the existing menu-list styles */
        .menu-list a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #e0e0e0;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .menu-list a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .menu-list a i {
            min-width: 30px;
            text-align: center;
            font-size: 1.2em;
        }

        /* Style specifically for Go Back and Logout */
        .menu-list a:nth-child(1),
        .menu-list a:nth-child(2) {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Dashboard Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .dashboard-header h2 {
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .last-update {
            color: #666;
            font-size: 0.9em;
        }

        /* Stats Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stat-icon {
            background: linear-gradient(145deg, #8B0000, #a52a2a);
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .stat-details {
            flex: 1;
        }

        .stat-details h3 {
            margin: 0;
            color: #666;
            font-size: 0.9em;
            font-weight: 500;
        }

        .stat-number {
            margin: 5px 0 0 0;
            font-size: 1.8em;
            font-weight: 600;
            color: #333;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .view-all {
            color: #8B0000;
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .view-all:hover {
            color: #a52a2a;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            background: #f8f8f8;
            border-radius: 8px;
            color: #666;
        }

        /* Responsive Design */
        @media screen and (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media screen and (max-width: 768px) {
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .stat-number {
                font-size: 1.5em;
            }
        }

        /* Recent Orders Styles */
        .recent-orders {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .order-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border-left: 4px solid #8B0000;
        }

        .order-card:hover {
            transform: translateX(5px);
        }

        .view-more-btn {
            background: transparent;
            color: #8B0000;
            border: 2px solid #8B0000;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .view-more-btn:hover {
            background: #8B0000;
            color: white;
        }

        .load-more-container {
            text-align: center;
            margin-top: 20px;
        }

        .load-more-btn {
            background: #f8f8f8;
            color: #8B0000;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .load-more-btn:hover {
            background: #8B0000;
            color: white;
            transform: translateY(-2px);
        }

        .items {
            font-size: 0.9em;
            color: #666;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Profile Styles */
        .profile-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.06);
            max-width: 1000px;
            margin: 30px auto;
        }

        /* Profile Header */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 40px;
            padding-bottom: 35px;
            margin-bottom: 35px;
            border-bottom: 1px solid #f0f0f0;
            position: relative;
        }

        .profile-image {
            position: relative;
            flex-shrink: 0;
        }

        .profile-image img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .profile-image:hover img {
            transform: scale(1.02);
        }

        .profile-info {
            flex-grow: 1;
        }

        .profile-info h2 {
            color: #2c2c2c;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .profile-info p {
            color: #666;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-info p i {
            color: #8B0000;
            font-size: 14px;
        }

        /* Profile Stats */
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin: 35px 0;
        }

        .stat-box {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }

        .stat-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #8B0000;
            opacity: 0.8;
        }

        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .stat-box i {
            font-size: 28px;
            color: #8B0000;
            background: rgba(139, 0, 0, 0.1);
            padding: 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .stat-box:hover i {
            transform: scale(1.1);
            background: rgba(139, 0, 0, 0.15);
        }

        .stat-info {
            flex: 1;
        }

        .stat-info h4 {
            color: #555;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            color: #2c2c2c;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.5px;
        }

        /* Profile Actions */
        .profile-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 30px;
            border-top: 1px solid #f0f0f0;
            margin-top: 20px;
        }

        .edit-btn {
            background: #8B0000;
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
        }

        .edit-btn:hover {
            background: #660000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }

        .edit-btn i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .edit-btn:hover i {
            transform: rotate(15deg);
        }

        /* Loading Animation */
        .profile-container.loading {
            position: relative;
            min-height: 500px;
        }

        .profile-container.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #8B0000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-container {
                padding: 25px;
                margin: 15px;
            }

            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 25px;
            }

            .profile-info h2 {
                font-size: 24px;
            }

            .profile-stats {
                grid-template-columns: 1fr;
            }

            .stat-box {
                padding: 20px;
            }

            .stat-box i {
                padding: 12px;
                font-size: 24px;
            }

            .stat-info p {
                font-size: 20px;
            }

            .profile-actions {
                justify-content: center;
            }

            .edit-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Animation for Stats */
        .stat-box {
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
        }

        .stat-box:nth-child(1) { animation-delay: 0.1s; }
        .stat-box:nth-child(2) { animation-delay: 0.2s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Profile Image Container Styles */
        .profile-image-container {
            position: relative;
            text-align: center;
        }

        .profile-image {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 15px;
        }

        .image-edit-btn {
            background: #8B0000;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 auto;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
        }

        .image-edit-btn:hover {
            background: #660000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }

        /* Image Upload Modal Styles */
        .image-upload-modal .modal-content {
            max-width: 500px;
            padding: 30px;
        }

        .upload-container {
            text-align: center;
            padding: 20px;
        }

        .preview-container {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #f0f0f0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-bottom: 15px;
        }

        .upload-btn {
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .upload-btn.primary {
            background: #8B0000;
            color: white;
        }

        .upload-btn.success {
            background: #28a745;
            color: white;
        }

        .upload-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .upload-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .upload-hint {
            color: #666;
            font-size: 13px;
            margin-top: 15px;
        }

        /* Animation for modal */
        .image-upload-modal {
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Profile Main Section */
        .profile-main {
            display: flex;
            align-items: center;
            gap: 30px;
            width: 100%;
        }

        /* Profile Image Container */
        .profile-image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            position: relative;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        /* User Name Display */
        .user-name-display {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .user-name-display h2 {
            color: #2c2c2c;
            font-size: 32px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .user-name-display .email {
            color: #666;
            font-size: 16px;
        }

        /* Image Edit Button */
        .image-edit-btn {
            background: #8B0000;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 0, 0, 0.2);
        }

        .image-edit-btn:hover {
            background: #660000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 0, 0, 0.3);
        }

        .image-edit-btn i {
            font-size: 14px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-main {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .user-name-display {
                align-items: center;
            }

            .user-name-display h2 {
                font-size: 28px;
            }

            .user-name-display .email {
                font-size: 15px;
            }

            .profile-image-container {
                margin: 0 auto;
            }

            .image-edit-btn {
                margin: 0 auto;
            }
        }

        /* Animation for profile load */
        .profile-main {
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-list {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px); /* Adjust based on your header height */
        }

        .menu-items {
            flex-grow: 1;
        }

        .menu-bottom {
            margin-top: auto;
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .menu-bottom a {
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .menu-bottom a:hover {
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <!-- Menu Section -->
    <div class="menu" id="sideMenu">
        <!-- Menu Toggle Button -->
        <div class="menu-toggle" onclick="toggleMenu()">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Menu List -->
        <div class="menu-list">
            <!-- Regular menu items -->
            <div class="menu-items">
                <a onclick="loadContent('dashboard')" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <a onclick="loadContent('notifications')">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="notification-count" style="display: <?php echo $notifications->num_rows > 0 ? 'inline' : 'none'; ?>">
                        <?php echo $notifications->num_rows; ?>
                    </span>
                </a>
                
                <a onclick="loadContent('profile')">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                
                <a href="home.php">
                    <i class="fas fa-arrow-left"></i>
                    <span>Go Back</span>
                </a>
            </div>

            <!-- Logout at bottom -->
            <div class="menu-bottom">
                <a href="javascript:void(0);" onclick="confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="content" id="content">
        <!-- Content will be loaded dynamically -->
    </div>

    <script> 
        function toggleMenu() {
            const menu = document.getElementById('sideMenu');
            menu.classList.toggle('collapsed');
        }

        // Add this to restore menu state on page load
        document.addEventListener('DOMContentLoaded', () => {
            const menu = document.getElementById('sideMenu');
            const isCollapsed = localStorage.getItem('menuCollapsed') === 'true';
            
            if (isCollapsed) {
                menu.classList.add('collapsed');
            }
        });

        function cleanupBeforePageChange() {
            // Remove any existing modals
            removeExistingModals();
            
            // Stop any running intervals
            stopAutoRefresh();
            
            // Clear any pending timeouts
            const highestTimeoutId = setTimeout(";");
            for (let i = 0; i < highestTimeoutId; i++) {
                clearTimeout(i);
            }
        }

        // Update loadContent to use cleanup
        function loadContent(page) {
            cleanupBeforePageChange();
            const contentDiv = document.getElementById('content');
            
            // Stop auto-refresh when not on dashboard
            if (page !== 'dashboard') {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
            
            // Remove active class from all links
            document.querySelectorAll('.menu-list a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to clicked link
            const clickedLink = document.querySelector(`.menu-list a[onclick="loadContent('${page}')"]`);
            if (clickedLink) {
                clickedLink.classList.add('active');
            }

            // Store the current page in session storage
            sessionStorage.setItem('currentPage', page);

            switch(page) {
                case 'dashboard':
                    
                    contentDiv.innerHTML = `
                        <div class="dashboard-header">
                            <h2><i class="fas fa-tachometer-alt"></i> Dashboard Overview</h2>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="dashboard-cards">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="stat-details">
                                    <h3>Total Orders</h3>
                                    <p class="stat-number"><?php echo $userStats['total_orders']; ?></p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div class="stat-details">
                                    <h3>Total Spent</h3>
                                    <p class="stat-number">₱<?php echo number_format($userStats['total_spent'], 2); ?></p>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-details">
                                    <h3>Pending Orders</h3>
                                    <p class="stat-number"><?php echo $pendingOrders->num_rows; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Main Content Grid -->
                        <div class="dashboard-grid">
                            <!-- Recent Orders Section -->
                            <div class="dashboard-section">
                                <div class="section-header">
                                    <h3><i class="fas fa-shopping-cart"></i> Pending Orders</h3>
                                </div>
                                <div class="recent-orders">
                                    <?php if ($pendingOrders->num_rows > 0): ?>
                                        <?php while($order = $pendingOrders->fetch_assoc()): ?>
                                            <div class="order-card">
                                                <div class="order-header">
                                                    <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                                                    <div class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></div>
                                                </div>
                                                <div class="order-details">
                                                    <div class="detail-row">
                                                        <span>Amount:</span>
                                                        <span class="amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span>Status:</span>
                                                        <span class="status">Pending</span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span>Items:</span>
                                                        <span class="items"><?php echo htmlspecialchars($order['ordered_products']); ?></span>
                                                    </div>
                                                    <?php if ($order['proof_status']): ?>
                                                        <div class="detail-row">
                                                            <span>Payment Status:</span>
                                                            <span class="proof-status"><?php echo ucfirst($order['proof_status']); ?></span>
                                                        </div>
                                                        <?php if ($order['image_path']): ?>
                                                            <div class="proof-preview">
                                                                <img src="<?php echo htmlspecialchars($order['image_path']); ?>" alt="Payment Proof">
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="no-orders">
                                            <i class="fas fa-shopping-cart"></i>
                                            <p>No pending orders</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Notifications Section -->
                            <div class="dashboard-section">
                                <div class="section-header">
                                    <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
                                    <button onclick="loadContent('notifications')" class="view-more-btn">View More</button>
                                </div>
                                <div class="notifications-container">
                                    <?php if ($notifications->num_rows > 0): ?>
                                        <?php while($notification = $notifications->fetch_assoc()): ?>
                                            <div class="notification-card <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                                                <div class="notification-header">
                                                    <div class="notification-icon">
                                                        <i class="fas fa-shopping-bag"></i>
                                                    </div>
                                                    <div class="notification-info">
                                                        <h4><?php echo htmlspecialchars($notification['title']); ?></h4>
                                                        <span class="notification-date">
                                                            <?php echo date('M d, Y h:i A', strtotime($notification['created_at'])); ?>
                                                        </span>
                                                    </div>
                                                    <?php if (!$notification['is_read']): ?>
                                                        <span class="new-badge">New</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="notification-body">
                                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                                    <?php if ($notification['type'] === 'order'): ?>
                                                        <button class="view-details-btn" onclick="viewOrderDetails(<?php echo $notification['reference_id']; ?>)">
                                                            <i class="fas fa-eye"></i> View Details
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="no-notifications">
                                            <i class="fas fa-bell-slash"></i>
                                            <p>No notifications yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    `;
                    break;
                case 'notifications':
                    fetch('get_user_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        contentDiv.innerHTML = `
                            <div class="section-header">
                                <h2>Notifications</h2>
                                ${data.length > 0 ? 
                                    `<button onclick="markAllNotificationsAsRead()" class="mark-all-read">
                                        Mark All as Read
                                    </button>` : ''
                                }
                            </div>
                            <div class="notifications-container">
                                ${data.length > 0 ? data.map(notification => `
                                    <div class="notification-card ${notification.is_read ? 'read' : 'unread'}" 
                                         data-notification-id="${notification.notification_id}">
                                        <div class="notification-header">
                                            <h3>${notification.title}</h3>
                                            <span class="notification-date">
                                                ${new Date(notification.created_at).toLocaleDateString()}
                                            </span>
                                        </div>
                                        <div class="notification-content">
                                            <p>${notification.message}</p>
                                        </div>
                                        ${!notification.is_read ? 
                                            `<button onclick="markAsRead(${notification.notification_id})" class="mark-read-btn">
                                                Mark as Read
                                            </button>` : ''
                                        }
                                    </div>
                                `).join('') : '<p class="no-notifications">No notifications found</p>'}
                            </div>
                        `;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        contentDiv.innerHTML = '<p class="error">Error loading notifications</p>';
                    });
                    break;
                case 'profile':
                    fetch('get_user_profile.php')
                        .then(response => response.json())
                        .then(userData => {
                            contentDiv.innerHTML = `
                                <div class="profile-container">
                                    <div class="profile-header">
                                        <div class="profile-main">
                                            <div class="profile-image-container">
                                                <div class="profile-image">
                                                    <img src="${userData.profile_picture || 'assets/images/default-profile.jpg'}" alt="Profile Picture">
                                                </div>
                                                <button onclick="showImageUploadModal()" class="image-edit-btn">
                                                    <i class="fas fa-camera"></i>
                                                    <span>Change Photo</span>
                                                </button>
                                            </div>
                                            <div class="user-name-display">
                                                <h2>${userData.username}</h2>
                                                <span class="email">${userData.email}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="profile-stats">
                                        <div class="stat-box">
                                            <i class="fas fa-shopping-bag"></i>
                                            <div class="stat-info">
                                                <h4>Total Orders</h4>
                                                <p>${userData.total_orders || '0'}</p>
                                            </div>
                                        </div>
                                        <div class="stat-box">
                                            <i class="fas fa-peso-sign"></i>
                                            <div class="stat-info">
                                                <h4>Total Spent</h4>
                                                <p>₱${(userData.total_spent || 0).toLocaleString('en-PH', {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                })}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="profile-actions">
                                        <button onclick="showEditProfileModal('${userData.username}', '${userData.email}')" class="edit-btn">
                                            <i class="fas fa-edit"></i>
                                            <span>Edit Profile</span>
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                    break;
            }
        }

        // Add this to maintain the current page on refresh
        document.addEventListener('DOMContentLoaded', () => {
            const currentPage = sessionStorage.getItem('currentPage') || 'dashboard';
            loadContent(currentPage);
        });

        // Add this function to remove any existing modals
        function removeExistingModals() {
            const existingModals = document.querySelectorAll('.modal');
            existingModals.forEach(modal => {
                modal.remove();
            });
            // Also remove any lingering overlay
            const overlays = document.querySelectorAll('.modal-overlay');
            overlays.forEach(overlay => overlay.remove());
        }

        // Update the showEditProfileModal function
        function showEditProfileModal(username, email) {
            // Remove any existing modals first
            removeExistingModals();
            
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="removeExistingModals()">&times;</span>
                    <h2>Edit Profile</h2>
                    <form onsubmit="updateProfile(event)" class="edit-profile-form">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="${username}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="${email}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <p class="password-note">Leave password fields empty if you don't want to change it</p>
                        
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);

            // Add click event to close modal when clicking outside
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    removeExistingModals();
                }
            });
        }

        // Update the updateProfile function
        function updateProfile(event) {
            event.preventDefault();
            stopAutoRefresh(); // Stop refresh while updating
            
            const formData = new FormData(event.target);
            formData.append('action', 'updateProfile');

            // Validate passwords if being changed
            const newPassword = formData.get('new_password');
            const confirmPassword = formData.get('confirm_password');
            
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    alert('New passwords do not match');
                    return;
                }
                
                if (newPassword.length < 6) {
                    alert('Password must be at least 6 characters long');
                    return;
                }
                
                if (!formData.get('current_password')) {
                    alert('Current password is required to change password');
                    return;
                }
            }

            fetch('userpanel_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    removeExistingModals();
                    loadContent('profile');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating profile');
                removeExistingModals();
            });
        }

        function removeFromCart(cartId) {
            if (!confirm('Are you sure you want to remove this item from your cart?')) {
                return;
            }

            // Stop auto-refresh temporarily
            stopAutoRefresh();

            const formData = new FormData();
            formData.append('action', 'removeFromCart');
            formData.append('cart_id', cartId);

            fetch('userpanel_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadContent('dashboard');
                    // Restart auto-refresh after operation is complete
                    startAutoRefresh();
                } else {
                    alert(data.message);
                    // Restart auto-refresh even if operation failed
                    startAutoRefresh();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing item');
                // Restart auto-refresh even if there was an error
                startAutoRefresh();
            });
        }

        // Function to refresh the dashboard content
        function refreshDashboard() {
            loadContent('dashboard');
        }

        // Set up auto refresh every 5 seconds (5000 milliseconds)
        let refreshInterval;

        function startAutoRefresh() {
            // Clear any existing interval first
            stopAutoRefresh();
            // Set new interval only if we're on the dashboard
            const currentPage = document.querySelector('.menu-list a.active');
            if (currentPage && currentPage.getAttribute('onclick').includes('dashboard')) {
                refreshInterval = setInterval(refreshDashboard, 5000);
            }
        }

        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
                refreshInterval = null;
            }
        }

        // Stop refresh when user leaves the page
        window.addEventListener('beforeunload', stopAutoRefresh);

        function viewOrderDetails(orderId) {
            // Create modal for order details
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                    <h2>Order Details</h2>
                    <div class="order-details-content">
                        <p>Loading order details...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Fetch order details
            fetch(`get_order_details.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    const orderDetails = modal.querySelector('.order-details-content');
                    orderDetails.innerHTML = `
                        <div class="order-info">
                            <p><strong>Order ID:</strong> #${data.order_id}</p>
                            <p><strong>Order Date:</strong> ${data.order_date}</p>
                            <p><strong>Status:</strong> ${data.status}</p>
                            <p><strong>Total Amount:</strong> ₱${data.total_amount}</p>
                        </div>
                        <div class="order-items">
                            <h3>Order Items</h3>
                            ${data.items.map(item => `
                                <div class="order-item">
                                    <p>${item.name}</p>
                                    <p>Quantity: ${item.quantity}</p>
                                    <p>Price: ₱${item.price}</p>
                                </div>
                            `).join('')}
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error:', error);
                    const orderDetails = modal.querySelector('.order-details-content');
                    orderDetails.innerHTML = '<p>Error loading order details</p>';
                });
        }

        // Function to mark notification as read
        function markNotificationAsRead(notificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${notificationId}`
            });
        }

        // Add function to mark notifications as read
        function markAllNotificationsAsRead() {
            fetch('mark_all_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadContent('notifications');
                    updateNotificationCount();
                }
            });
        }

        function loadMoreOrders() {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="this.parentElement.parentElement.remove()">&times;</span>
                    <h2>All Orders</h2>
                    <div class="orders-list">
                        Loading orders...
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Fetch all orders
            fetch('get_all_orders.php')
                .then(response => response.json())
                .then(data => {
                    const ordersList = modal.querySelector('.orders-list');
                    if (data.length > 0) {
                        ordersList.innerHTML = data.map(order => `
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-id">Order #${order.order_id}</div>
                                    <div class="order-date">${order.order_date}</div>
                                </div>
                                <div class="order-details">
                                    <div class="detail-row">
                                        <span>Amount:</span>
                                        <span class="amount">₱${order.total_amount}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span>Status:</span>
                                        <span class="status ${order.status.toLowerCase()}">${order.status}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span>Items:</span>
                                        <span class="items">${order.products}</span>
                                    </div>
                                </div>
                                <button onclick="viewOrderDetails(${order.order_id})" class="btn-view">
                                    View Details
                                </button>
                            </div>
                        `).join('');
                    } else {
                        ordersList.innerHTML = '<div class="no-data">No orders found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const ordersList = modal.querySelector('.orders-list');
                    ordersList.innerHTML = '<div class="no-data">Error loading orders</div>';
                });
        }

        function markAsRead(notificationId) {
            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadContent('notifications');
                    updateNotificationCount();
                }
            });
        }

        function markAllNotificationsAsRead() {
            fetch('mark_all_notifications_read.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadContent('notifications');
                    updateNotificationCount();
                }
            });
        }

        // Add this function to update the notification count
        function updateNotificationCount() {
            fetch('get_notification_count.php')
            .then(response => response.json())
            .then(data => {
                const notifCount = document.querySelector('.notification-count');
                if (notifCount) {
                    if (data.count > 0) {
                        notifCount.textContent = data.count;
                        notifCount.style.display = 'inline';
                    } else {
                        notifCount.style.display = 'none';
                    }
                }
            });
        }

        function showImageUploadModal() {
            const modal = document.createElement('div');
            modal.className = 'modal image-upload-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close" onclick="this.closest('.modal').remove()">&times;</span>
                    <h2>Update Profile Picture</h2>
                    <div class="upload-container">
                        <div class="preview-container">
                            <img id="image-preview" src="assets/images/default-profile.jpg" alt="Preview">
                        </div>
                        <div class="upload-controls">
                            <label for="image-upload" class="upload-btn primary">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Choose Image
                            </label>
                            <input type="file" id="image-upload" accept="image/*" onchange="previewImage(this)" hidden>
                            <button onclick="uploadProfileImage()" class="upload-btn success" id="save-image" disabled>
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                        </div>
                        <p class="upload-hint">Recommended: Square image, max 5MB</p>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const saveBtn = document.getElementById('save-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    saveBtn.disabled = false;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function uploadProfileImage() {
            const input = document.getElementById('image-upload');
            if (!input.files || !input.files[0]) return;

            const formData = new FormData();
            formData.append('profile_picture', input.files[0]);

            fetch('update_profile_picture.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Profile picture updated successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Error updating profile picture');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating profile picture');
            });
        }

        // Add this simple JavaScript function
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>

    <!-- Add necessary modal templates -->
    <div id="editProfileModal" class="modal">
        <!-- Edit profile form -->
    </div>

    <div id="addAddressModal" class="modal">
        <!-- Add address form -->
    </div>

    <div id="orderDetailsModal" class="modal">
        <!-- Order details content -->
    </div>
</body>
</html>
