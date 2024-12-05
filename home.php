<?php
session_start();

// Include the database connection file
include 'connection.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database
    $query = "SELECT u.profile_picture, u.username 
              FROM users u 
              WHERE u.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $profile_picture = $user['profile_picture'];
    $username = $user['username'];

    // Store the profile picture in the session
    $_SESSION['profile_picture'] = $profile_picture;
} else {
    // If the user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}

// Define an array of default product images from reliable sources
$product_images = [
    'Kapeng Barako' => 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=800',  // Coffee
    'Dried Fish' => 'https://images.unsplash.com/photo-1574591620322-f14404a9ff65?w=800',      // Dried Fish
    'Lambanog' => 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=800',        // Local Drink
    'Balisong' => 'https://images.unsplash.com/photo-1593001874117-c99c800e3eb7?w=800',        // Knife
    'Batangas Lomi' => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?w=800',   // Noodles
    'Batangas Bulalo' => 'https://images.unsplash.com/photo-1583835746434-cf1534674b41?w=800', // Soup
    'Tapang Taal' => 'https://images.unsplash.com/photo-1602414393797-455945716c67?w=800',     // Dried Meat
    'Panutsa' => 'https://images.unsplash.com/photo-1587132137056-bfbf0166836e?w=800',         // Sweet Snack
    'Suman' => 'https://images.unsplash.com/photo-1606790948592-7d8751b8f275?w=800',           // Rice Cake
    // Backup food/product images
    'backup1' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800',
    'backup2' => 'https://images.unsplash.com/photo-1516684732162-798a0062be99?w=800',
    'backup3' => 'https://images.unsplash.com/photo-1559181567-c3190ca9959b?w=800',
    // Default image for products without specific images
    'default' => 'https://images.unsplash.com/photo-1553531384-cc64ac80f931?w=800'
];

// Alternative image sources (if any of the above don't work)
$alternative_images = [
    'Kapeng Barako' => 'https://source.unsplash.com/800x600/?coffee',
    'Dried Fish' => 'https://source.unsplash.com/800x600/?dried,fish',
    'Lambanog' => 'https://source.unsplash.com/800x600/?drink,bottle',
    'default' => 'https://source.unsplash.com/800x600/?product'
];

// Function to check if image exists
function imageExists($url) {
    $headers = get_headers($url);
    return stripos($headers[0], "200 OK") ? true : false;
}

// Get image URL with fallback
function getProductImage($product_name, $product_images, $alternative_images) {
    // Try primary image
    if (isset($product_images[$product_name])) {
        $image_url = $product_images[$product_name];
        if (imageExists($image_url)) {
            return $image_url;
        }
    }
    
    // Try alternative image
    if (isset($alternative_images[$product_name])) {
        return $alternative_images[$product_name];
    }
    
    // Return default image
    return $product_images['default'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Explore Batangas - Shop</title>
    <link rel="stylesheet" href="css/responsive.css">

    <!-- CSS styles -->
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html,
        body {
            height: 100%;
            background-color: rgb(244, 244, 244);
            /* #f4f4f4 */
            color: rgb(51, 51, 51);
            /* #333 */
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
            /* Dark Red background */
            padding: 10px 20px;
            color: rgb(255, 255, 255);
            /* White text color */
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
            /* Change text color to black when hovered */
        }

        /* Circular Profile Picture or Default Circle */
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

        /* Update dropdown positioning */
        .user-menu {
            position: relative;
            display: inline-block;
            margin-left: 20px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            overflow: hidden;
        }

        .dropdown-content a,
        p {
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

        /* Username styling under the circle */
        .user-name {
            text-align: center;
            font-size: 12px;
            color: #333;
        }


        /* Footer Section */
        footer {
            background-color: #8B0000;
            color: rgb(255, 255, 255);
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
            /* Ensure it stays at the bottom */
            margin-top: 40px; /* Adjust this value to increase space above the footer */
        }

        .footer-content p {
            margin-bottom: 10px;
            color: white;
        }

        .footer-links a {
            color: rgb(255, 255, 255);
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        /* Image Gallery Layout */
        .image-gallery {
            display: flex;
            justify-content: space-evenly;
            flex-wrap: wrap;
            margin-top: 50px;
        }

        .image-item {
            width: 300px;
            background-color: rgb(255, 255, 255);
            border-radius: 10px;
            margin: 20px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .image-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #fff;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: transform 0.3s ease;
        }

        .image-item img:hover {
            transform: scale(1.05);
        }

        /* Add loading animation */
        @keyframes imageLoading {
            0% { opacity: 0.6; }
            50% { opacity: 0.8; }
            100% { opacity: 0.6; }
        }

        .image-item img[src=""] {
            animation: imageLoading 1.5s infinite;
            background-color: #f0f0f0;
        }

        .image-item h3 {
            margin-top: 10px;
            font-size: 18px;
            color: rgb(51, 51, 51);
        }

        /* Button Styling */
        .view-more-container {
            text-align: center;
            margin-top: 100px;
        }

        .view-more-btn {
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .view-more-btn:hover {
            background-color: #a62b2b;
        }

        /* Hero Section */
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('img/batangas-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .hero-content h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        .cta-button {
            padding: 15px 30px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cta-button:hover {
            background-color: #a62b2b;
        }

        /* Featured Categories */
        .categories-section {
            padding: 50px 20px;
            text-align: center;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        /* Featured Products Section */
        .featured-products {
            background-color: #f9f9f9;
            padding: 50px 20px 20px 20px;
            text-align: center;
        }

        /* About Section */
        .about-section {
            display: flex;
            align-items: center;
            padding: 50px 20px;
            background: white;
        }

        .about-content {
            flex: 1;
            padding: 0 50px;
        }

        .about-image {
            flex: 1;
            text-align: center;
        }

        .about-image img {
            max-width: 100%;
            border-radius: 10px;
        }

       

        .newsletter-form {
            max-width: 500px;
            margin: 20px auto;
        }

        .newsletter-form input {
            padding: 10px;
            width: 60%;
            margin-right: 10px;
            border: none;
            border-radius: 5px;
        }

        .newsletter-form button {
            padding: 10px 20px;
            background: black;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Featured Products Styling */
        .image-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .image-item h3 {
            margin-top: 10px;
            font-size: 18px;
            color: rgb(51, 51, 51);
        }

        .brand,
        .category {
            font-size: 12px;
            color: #333;
        }

        .price {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
        }

        .original-price {
            text-decoration: line-through;
        }

        .peso-price {
            margin-left: 10px;
        }

        .button-group {
            margin-top: 10px;
        }

        .buy-now,
        .add-to-cart,
        .out-of-stock {
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-right: 10px;
        }

        .buy-now:hover,
        .add-to-cart:hover {
            background-color: #a62b2b;
        }

        .out-of-stock {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background-color: #f5f5f5;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            position: relative;
        }

        .product-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f5f5f5;
            border-radius: 8px;
        }

        .product-image.loaded::before {
            display: none;
        }

        /* Loading animation */
        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }
            100% {
                background-position: 468px 0;
            }
        }

        .product-image:not(.loaded) {
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 800px 104px;
            animation: shimmer 1.5s infinite linear;
        }

        /* Enhanced Hero Section */
        .hero-section {
            height: 600px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('img/hero-batangas.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }

        .cta-button.secondary {
            background-color: transparent;
            border: 2px solid white;
        }

        /* Section Headers */
        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .section-header p {
            color: #666;
            font-size: 1.1em;
        }

        /* Enhanced Category Cards */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 0 20px;
        }

        .category-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            text-align: center;
        }

        .category-card:hover {
            transform: translateY(-10px);
        }

        /* Product Cards */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            position: relative;
            height: 250px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-size: 0.8em;
        }

        .stock-badge.out {
            background-color: #dc3545;
        }

        .stock-badge.low {
            background-color: #ffc107;
        }

        /* Cultural Highlights */
        .cultural-highlights {
            padding: 60px 20px;
            background-color: #f9f9f9;
        }

        .festivals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .festival-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .festival-card:hover {
            transform: translateY(-5px);
        }

        .festival-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .festival-info {
            padding: 20px;
        }

        .festival-info h3 {
            color: #333;
            font-size: 1.4em;
            margin-bottom: 10px;
        }

        .festival-intro {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .festival-date, .festival-location {
            color: #555;
            font-size: 0.9em;
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .festival-date i, .festival-location i {
            color: #8B0000;
        }

        .learn-more {
            display: inline-block;
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .learn-more:hover {
            background-color: #a62b2b;
        }

        .view-more {
            text-align: center;
            margin-top: 30px;
        }

        .view-more-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .view-more-btn:hover {
            background-color: #444;
        }

        /* Enhanced Newsletter Section */
        

        .input-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .input-group input {
            flex: 1;
            padding: 15px;
            border-radius: 5px;
            border: none;
        }

        .input-group button {
            padding: 15px 30px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .input-group button:hover {
            background-color: #444;
        }

        .form-notice {
            font-size: 0.8em;
            margin-top: 10px;
            opacity: 0.8;
        }

        /* Update the view-more container styling */
        .view-more {
            text-align: center;
            margin: 40px 0;
            padding: 20px;
        }

        .view-more-btn {
            display: inline-block;
            padding: 15px 30px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            cursor: pointer;
        }

        .view-more-btn:hover {
            background-color: #a62b2b;
            transform: translateY(-2px);
        }

        /* Adjust the featured products section spacing */
        .featured-products {
            background-color: #f9f9f9;
            padding: 50px 20px 20px 20px;
            text-align: center;
        }

        .products-grid {
            margin-bottom: 20px;
        }

        /* Carousel Styling */
        .swiper {
            width: 100%;
            height: 500px;
            margin-bottom: 50px;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .category-showcase {
            padding: 50px 0;
            background: #fff;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .category-item {
            position: relative;
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .category-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .category-item:hover img {
            transform: scale(1.05);
        }

        .category-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }

        .category-overlay h3 {
            margin: 0;
            font-size: 1.5em;
        }

        /* Carousel Styling */
        .main-carousel {
            width: 100%;
            height: 500px;
            margin-bottom: 50px;
        }

        .swiper-slide {
            position: relative;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }

        .slide-content {
            position: absolute;
            bottom: 40px;
            left: 40px;
            color: white;
            z-index: 1;
        }

        /* Category Grid Styling */
        .product-categories {
            padding: 40px 20px;
        }

        .category-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        .category-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
        }

        .product-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-item h4 {
            padding: 10px;
            text-align: center;
            background: rgba(255,255,255,0.9);
            margin: 0;
        }

        /* Festival Carousel Styling */
        .festival-carousel {
            width: 100%;
            height: 400px;
            margin-bottom: 40px;
        }

        .festival-carousel .swiper-slide {
            position: relative;
        }

        .festival-carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.8);
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }

        .slide-caption h3 {
            font-size: 1.8em;
            margin-bottom: 8px;
        }

        .slide-caption p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        /* Swiper Navigation Buttons */
        .festival-carousel .swiper-button-next,
        .festival-carousel .swiper-button-prev {
            color: white;
        }

        .festival-carousel .swiper-pagination-bullet {
            background: white;
            opacity: 0.7;
        }

        .festival-carousel .swiper-pagination-bullet-active {
            opacity: 1;
        }

        /* Existing Festival Cards Styling */
        .cultural-highlights {
            padding: 60px 20px;
            background-color: #f9f9f9;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-header h2 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .section-header p {
            color: #666;
            font-size: 1.2em;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .festival-carousel {
                height: 300px;
            }

            .slide-caption h3 {
                font-size: 1.5em;
            }

            .slide-caption p {
                font-size: 1em;
            }
        }

        /* Cultural Highlights Header */
        .cultural-header {
            margin-bottom: 40px;
        }

        .content-header {
            padding: 80px 0 100px;
            position: relative;
            background: #f5f5f5;
            margin-bottom: 60px;
        }

        .offset-background {
            position: relative;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            max-width: 1200px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(50px);
            text-align: center;
        }

        .content-header h1 {
            font-size: 2.8em;
            color: #1a1a1a;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .content-header p {
            font-size: 1.2em;
            color: #666;
            line-height: 1.6;
            margin: 0;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .content-header {
                padding: 60px 20px 80px;
            }

            .offset-background {
                padding: 30px 20px;
                margin: 0 20px;
            }

            .content-header h1 {
                font-size: 2em;
            }

            .content-header p {
                font-size: 1.1em;
            }
        }

        /* General Styling */
        .section-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .section-header h2 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .section-header p {
            color: #666;
            font-size: 1.1em;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Hero Section Enhancement */
        .hero-section {
            position: relative;
            height: 80vh;
            min-height: 600px;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/products/Balsa.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .hero-content {
            text-align: center;
            padding: 0 20px;
            max-width: 800px;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .cta-button.secondary {
            background-color: transparent;
            border: 2px solid white;
        }

        /* Stats Section */
        .stats-section {
            padding: 80px 20px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 3em;
            font-weight: 700;
            color: #8B0000;
            margin-bottom: 10px;
            display: block;
        }

        .stat-item p {
            color: #555;
            font-size: 1.1em;
            font-weight: 500;
        }

        /* Festival Section */
        .festival-section {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .festival-carousel {
            margin-bottom: 60px;
        }

        .festivals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .festival-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .festival-card:hover {
            transform: translateY(-5px);
        }

        .festival-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .festival-info {
            padding: 25px;
        }

        .festival-info h3 {
            font-size: 1.4em;
            color: #333;
            margin-bottom: 15px;
        }

        .festival-intro {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .festival-date, .festival-location {
            color: #777;
            font-size: 0.9em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .learn-more {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .learn-more:hover {
            background: #a62b2b;
        }

        /* News Section */
        .news-section {
            padding: 80px 20px;
            background: white;
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .news-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .news-card:hover {
            transform: translateY(-5px);
        }

        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .news-content {
            padding: 25px;
        }

        .news-content h3 {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 12px;
        }

        .news-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .read-more {
            color: #8B0000;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .read-more:hover {
            color: #a62b2b;
        }

        /* Testimonials Section (new) */
        .testimonials-section {
            padding: 80px 20px;
            background: #f8f9fa;
        }

        .testimonials-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
        }

        .testimonial-content p {
            color: #444;
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        .testimonial-author {
            color: #8B0000;
            font-weight: 600;
            text-align: right;
        }

        

        /* Responsive Design */
        @media (max-width: 768px) {
            .section-header h2 {
                font-size: 2em;
            }

            .hero-content h1 {
                font-size: 2.5em;
            }

            .stat-number {
                font-size: 2.5em;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 15px;
            }

            .festivals-grid, .news-grid, .testimonials-container {
                grid-template-columns: 1fr;
                padding: 0 15px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-item, .festival-card, .news-card, .testimonial-card {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Text Color Improvements */
        .section-header h2 {
            color: #1a1a1a; /* Darker than before for better contrast */
            font-weight: 700;
        }

        .section-header p {
            color: #333; /* Darker than before, was #666 */
        }

        /* Hero Section Text */
        .hero-content {
            text-align: center;
            padding: 0 20px;
            max-width: 800px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Better readability on background image */
        }

        .hero-content h1 {
            color: #ffffff;
            font-size: 3.5em;
            margin-bottom: 20px;
        }

        .hero-content p {
            color: #ffffff;
            font-size: 1.2em;
            margin-bottom: 30px;
        }

        /* Stats Section */
        .stat-number {
            color: #8B0000;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.1); /* Subtle shadow for better contrast */
        }

        .stat-item p {
            color: #333; /* Darker than before */
            font-weight: 500;
        }

        /* Festival Cards */
        .festival-info h3 {
            color: #1a1a1a; /* Darker for better readability */
            font-weight: 600;
        }

        .festival-intro {
            color: #333; /* Was #666, now darker */
        }

        .festival-date, .festival-location {
            color: #444; /* Was #777, now darker */
        }

        /* News Cards */
        .news-content h3 {
            color: #1a1a1a;
            font-weight: 600;
        }

        .news-content p {
            color: #333; /* Was #666 */
        }

        .read-more {
            color: #8B0000;
            font-weight: 700; /* Bolder for better visibility */
        }

        .read-more:hover {
            color: #660000; /* Darker red on hover */
        }

        /* Testimonials */
        .testimonial-content p {
            color: #333; /* Was #444 */
            font-weight: 500;
        }

        .testimonial-author {
            color: #660000; /* Darker red for better contrast */
            font-weight: 700;
        }

        /* Button Improvements */
        .learn-more {
            background: #8B0000;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 24px;
        }

        .learn-more:hover {
            background: #660000;
        }

        /* Dark Mode Support (optional) */
        @media (prefers-color-scheme: dark) {
            .festival-card,
            .news-card,
            .testimonial-card {
                background: #ffffff; /* Keep cards light even in dark mode */
            }
            
            .stats-section,
            .news-section {
                background: #f8f9fa; /* Light background for content sections */
            }
        }

        /* Accessibility Improvements */
        .cta-button, 
        .learn-more, 
        .read-more {
            position: relative;
            z-index: 1;
        }

        /* High Contrast Text for Images */
        .festival-card img,
        .news-card img {
            position: relative;
        }

        .festival-card img::after,
        .news-card img::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(transparent, rgba(0,0,0,0.3));
            pointer-events: none;
        }

        /* Carousel Text Improvements */
        .swiper-slide {
            position: relative;
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8)); /* Darker gradient overlay */
            color: #ffffff;
            text-align: left;
            z-index: 2;
        }

        .slide-caption h3 {
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Strong text shadow */
        }

        .slide-caption p {
            font-size: 1.2em;
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
            max-width: 600px;
        }

        /* Carousel Navigation Improvements */
        .swiper-button-next,
        .swiper-button-prev {
            color: #ffffff !important; /* Make arrows more visible */
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 50%;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background: rgba(0, 0, 0, 0.7);
        }

        .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: #ffffff;
            opacity: 0.7;
        }

        .swiper-pagination-bullet-active {
            background: #8B0000;
            opacity: 1;
        }

        /* Additional overlay for better text visibility */
        .swiper-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3); /* Overall dark overlay */
            z-index: 1;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .slide-caption h3 {
                font-size: 1.5em;
            }
            
            .slide-caption p {
                font-size: 1em;
            }
            
            .swiper-button-next,
            .swiper-button-prev {
                padding: 20px;
            }
        }

        /* Add this to your CSS */
        html {
            scroll-behavior: smooth;
        }

        /* Optional: Offset for fixed headers if you have any */
        #festivals {
            scroll-margin-top: 80px; /* Adjust this value based on your header height */
        }

        /* Add this to your existing CSS */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .modal-content {
            position: relative;
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 80%;
            max-width: 800px;
            border-radius: 10px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
        }

        .close-modal:hover {
            color: #000;
        }

        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .modal-details h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .modal-details p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .modal-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: #8B0000;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            z-index: 9999;
            animation: slideIn 0.3s ease-in-out;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>

    <!-- Add this in your <head> section for carousel functionality -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
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
                <li><a href="about.php ">About</a></li>

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

    <main>
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-content">
                <h1>Discover Batangas</h1>
                <p>Experience the rich culture, traditions, and flavors of Batangas</p>
                <div class="hero-buttons">
                    <a href="library.php" class="cta-button">Start Exploring</a>
                    <a href="#festivals" class="cta-button secondary">Learn More</a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section (new) -->
        <section class="stats-section">
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-number">12+</span>
                    <p>Festivals</p>
                </div>
                <div class="stat-item">
                    <span class="stat-number">40+</span>
                    <p>Local Products</p>
                </div>
                <div class="stat-item">
                    <span class="stat-number">20+</span>
                    <p>Municipalities</p>
                </div>
                <div class="stat-item">
                    <span class="stat-number">1000+</span>
                    <p>Happy Visitors</p>
                </div>
            </div>
        </section>

        <!-- Festival Section (keeping both carousel and cards) -->
        <section id="festivals" class="festival-section">
            <div class="section-header">
                <h2>Featured Festivals</h2>
                <p>Experience the vibrant celebrations of Batangas</p>
            </div>
            
            <!-- Existing Festival Carousel -->
            <div class="swiper festival-carousel">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="img/Anihan.png" alt="Anihan Festival">
                        <div class="slide-caption">
                            <h3>Anihan Festival</h3>
                            <p>Celebrating the bountiful harvest season</p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="img/Sublian.png" alt="Sublian Festival">
                        <div class="slide-caption">
                            <h3>Sublian Festival</h3>
                            <p>Traditional dance and cultural celebration</p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="img/parada ng lechon.png" alt="Parada ng Lechon">
                        <div class="slide-caption">
                            <h3>Parada ng Lechon</h3>
                            <p>Famous roasted pig festival of Balayan</p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="img/El pasubat.png" alt="El Pasubat Festival">
                        <div class="slide-caption">
                            <h3>El Pasubat Festival</h3>
                            <p>Showcasing Batangas' finest products</p>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <!-- Existing Festival Cards -->
            <div class="festivals-grid">
                <?php
                $query = "SELECT * FROM library 
                          WHERE status = 1 
                          ORDER BY STR_TO_DATE(date_celebrated, '%M') 
                          LIMIT 3";
                $result = mysqli_query($conn, $query);
                
                while($festival = mysqli_fetch_assoc($result)): ?>
                    <div class="festival-card">
                        <img src="img/<?php echo htmlspecialchars($festival['festival_image']); ?>" 
                             alt="<?php echo htmlspecialchars($festival['festival_name']); ?>"
                             onerror="this.src='img/default.png'">
                        <div class="festival-info">
                            <h3><?php echo htmlspecialchars($festival['festival_name']); ?></h3>
                            <p class="festival-intro"><?php echo htmlspecialchars(substr($festival['short_intro'], 0, 100)) . '...'; ?></p>
                            <p class="festival-date">
                                <i class="fas fa-calendar"></i> 
                                <?php echo htmlspecialchars($festival['date_celebrated']); ?>
                            </p>
                            <p class="festival-location">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($festival['location']); ?>
                            </p>
                            <a href="library.php" class="learn-more">Learn More</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <div class="view-more">
                <a href="library.php" class="view-more-btn">View All Festivals</a>
            </div>
        </section>

        <!-- Latest News & Updates (new) -->
        <section class="news-section">
            <div class="section-header">
                <h2>Latest Updates</h2>
                <p>Stay informed about Batangas events and announcements</p>
            </div>
            <div class="news-grid">
                <div class="news-card">
                    <img src="assets/images/products/Anihan.png" alt="Festival Update">
                    <div class="news-content">
                        <h3>Upcoming Anihan Festival</h3>
                        <p>Join us in celebrating the bountiful harvest season of Batangas</p>
                        <a href="library.php" class="read-more">Read More</a>
                    </div>
                </div>
                <div class="news-card">
                    <img src="assets/images/products/Sublian.png" alt="Cultural Event">
                    <div class="news-content">
                        <h3>Sublian Festival Highlights</h3>
                        <p>Experience the traditional dance and cultural celebration</p>
                        <a href="library.php" class="read-more">Read More</a>
                    </div>
                </div>
                <div class="news-card">
                    <img src="assets/images/products/tapusan.jpg" alt="Food Festival">
                    <div class="news-content">
                        <h3>Tapusan Festival</h3>
                        <p>Experience the grand celebration of faith and culture</p>
                        <a href="library.php" class="read-more">Read More</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section (new) -->
        <section class="testimonials-section">
            <div class="section-header">
                <h2>What Visitors Say</h2>
                <p>Experiences shared by our community</p>
            </div>
            <div class="testimonials-container">
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The festivals in Batangas are truly spectacular! The Anihan Festival was a wonderful experience."</p>
                        <div class="testimonial-author">- Maria Santos</div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"Sublian Festival showed me the rich cultural heritage of Batangas. Unforgettable!"</p>
                        <div class="testimonial-author">- Juan Dela Cruz</div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-content">
                        <p>"The local hospitality and festive atmosphere make every visit special."</p>
                        <div class="testimonial-author">- Ana Reyes</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p style="color: white;">&copy; 2024 Explore Batangas. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image');
            
            images.forEach(img => {
                img.onload = function() {
                    this.classList.add('loaded');
                }
                
                img.onerror = function() {
                    // Try loading alternative image
                    if (!this.src.includes('default')) {
                        this.src = '<?php echo $alternative_images['default']; ?>';
                    }
                }
            });
        });
    </script>

    <script>
    // Initialize Festival Carousel
    const festivalSwiper = new Swiper('.festival-carousel', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        }
    });
    </script>

    <script>
    // Initialize Product Carousels
    document.addEventListener('DOMContentLoaded', function() {
        <?php
        mysqli_data_seek($category_result, 0);
        while($category = mysqli_fetch_assoc($category_result)): ?>
            new Swiper('.product-carousel-<?php echo $category['categories_id']; ?>', {
                slidesPerView: 1,
                spaceBetween: 25,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                breakpoints: {
                    640: {
                        slidesPerView: 2,
                        spaceBetween: 20,
                    },
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 25,
                    },
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 30,
                    }
                }
            });
        <?php endwhile; ?>
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all buttons that should trigger smooth scroll
        const scrollButtons = document.querySelectorAll('.hero-buttons a');
        
        scrollButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
    </script>

    <div id="festivalModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img class="modal-image" src="" alt="Festival Image">
            <div class="modal-details">
                <h2></h2>
                <div class="modal-info">
                    <div class="info-item">
                        <i class="fas fa-calendar"></i>
                        <span class="festival-date"></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="festival-location"></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map"></i>
                        <span class="festival-venue"></span>
                    </div>
                </div>
                <p class="festival-description"></p>
                <h3>Cultural Significance</h3>
                <p class="cultural-significance"></p>
                <h3>Activities</h3>
                <p class="activities"></p>
            </div>
        </div>
    </div>

</body>

</html>