<?php
session_start(); // Start the session
// Include the database connection
include 'connection.php'; // Make sure the path is correct

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Explore Batangas - Shop</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
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

        /* Footer Section */
        footer {
            background-color: #8B0000;
            color: rgb(255, 255, 255);
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
            /* Ensure it stays at the bottom */
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

        /* Login Button */
        .btnLogin-popup {
            width: 110px;
            height: 30px;
            background: transparent;
            border: 2px solid #fff;
            outline: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: small;
            color: #fff;
            font-weight: 500;
            transition: .5s;
            margin-left: 10px;
        }

        .btnLogin-popup:hover {
            background: #fff;
            color: #162938;
        }

        /* Form Popup Background */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        /* Form Box */
        .form-box {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            position: relative;
            z-index: 10000;
        }

        .form-box h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
            font-weight: 600;
        }

        /* Input Fields */
        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }

        .input-box input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .input-box input:focus {
            border-color: #8B0000;
            outline: none;
        }

        /* Remember-Forgot Section */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #555;
        }

        .remember-forgot a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .btn {
            width: 100%;
            padding: 14px;
            background-color: #8B0000;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .btn:hover {
            background-color: #660000;
        }

        /* Login-Register Switch */
        .login-register {
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .login-register a {
            color: #8B0000;
            font-weight: 600;
            text-decoration: none;
            margin-left: 5px;
        }

        .login-register a:hover {
            text-decoration: underline;
        }

        /* Close Button */
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: transparent;
            color: #666;
            border: none;
            font-size: 24px;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #333;
        }

        /* reCAPTCHA Styling */
        .g-recaptcha {
            margin-bottom: 25px;
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            .form-box {
                padding: 30px 20px;
            }
            
            .form-box h2 {
                font-size: 20px;
                margin-bottom: 25px;
            }
            
            .input-box {
                margin-bottom: 20px;
            }
            
            .btn {
                padding: 12px;
                font-size: 14px;
            }
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

        /* Newsletter Section */
        .newsletter-section {
            background-color: #8B0000;
            color: white;
            padding: 50px 20px;
            text-align: center;
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
        .newsletter-section {
            background-color: #8B0000;
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .newsletter-content {
            max-width: 600px;
            margin: 0 auto;
        }

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

        /* Newsletter Section Enhancement */
        .newsletter-section {
            background-color: #8B0000;
            color: white;
            padding: 80px 20px;
            text-align: center;
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

        /* Newsletter Section */
        .newsletter-section {
            background-color: #8B0000;
        }

        .newsletter-section h2,
        .newsletter-section p {
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
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
            z-index: 10000;
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
            z-index: 10001;
        }

        .close-modal {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 28px;
            cursor: pointer;
            color: #666;
            z-index: 10001;
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
            left: 20px;
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

        .input-box select {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            background-color: white;
        }

        .input-box select:focus {
            border-color: #8B0000;
            outline: none;
        }

        .input-box textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
            resize: vertical;
            min-height: 60px;
        }

        .input-box textarea:focus {
            border-color: #8B0000;
            outline: none;
        }

        /* Adjust form height for scrolling */
        .form-box.register {
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Registration Form Styles */
        .form-box.register {
            max-width: 500px;
            width: 90%;
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .form-box h2 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 25px;
        }

        .form-section h3 {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-box {
            position: relative;
            margin-bottom: 20px;
        }

        .input-box.half {
            flex: 1;
        }

        .input-box input,
        .input-box select,
        .input-box textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-box label {
            position: absolute;
            left: 10px;
            top: -8px;
            background: #fff;
            padding: 0 5px;
            font-size: 12px;
            color: #666;
        }

        .input-box input:focus,
        .input-box select:focus,
        .input-box textarea:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 2px rgba(139, 0, 0, 0.1);
        }

        .input-box textarea {
            min-height: 60px;
            resize: vertical;
        }

        .textarea-label {
            top: -8px;
        }

        .date-label {
            top: -8px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #8B0000;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #660000;
        }

        .login-register {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }

        .login-register a {
            color: #8B0000;
            text-decoration: none;
            font-weight: 500;
        }

        .login-register a:hover {
            text-decoration: underline;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 20px;
            color: #666;
            background: none;
            border: none;
            cursor: pointer;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-box.register {
                padding: 20px;
            }
        }

        /* Add these styles to your existing CSS */
        .terms-section {
            margin-top: 15px;
        }

        .terms-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #666;
        }

        .terms-link {
            color: #8B0000;
            text-decoration: none;
            font-weight: 500;
        }

        .terms-link:hover {
            text-decoration: underline;
        }

        /* Terms and Conditions Modal Styling */
        .terms-content {
            max-height: 60vh;
            overflow-y: auto;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .terms-content section {
            margin-bottom: 20px;
        }

        .terms-content h3 {
            color: #8B0000;
            margin-bottom: 10px;
        }

        .terms-content p, .terms-content ul {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .terms-content ul {
            padding-left: 20px;
        }

        .terms-content li {
            margin-bottom: 5px;
        }

        .terms-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .terms-footer button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .terms-footer button {
            width: auto;
            padding: 12px 24px;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" id="success-alert">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" id="error-alert">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <header>
        <div class="logo">
            <img src="img/logo3.png" alt="Logo" />
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="libraryguest.php">Library</a></li>
                <li><a href="aboutguest.php">About</a></li>
                <button class="btnLogin-popup">Login</button>
            </ul>
        </nav>
    </header>

    <!-- Popup Form -->
    <div class="popup" id="popupForm">
        <div class="form-box login" id="loginForm">
            <button class="close-btn" id="closeBtn">&times;</button>
            <h2>Login</h2>
            <form method="POST" action="login.php"> <!-- Point to the login handler -->
                <div class="input-box">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-box">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <div class="g-recaptcha" data-sitekey="6LeOP2AqAAAAAHQB2xmCuXSJzy1yqinSx01NeCxJ"></div>
                <div class="remember-forgot">
                    <label><input type="checkbox"> Remember me</label>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
                </div>
            </form>
        </div>

        <div class="form-box register" id="registerForm" style="display: none;">
            <button class="close-btn" id="closeRegisterBtn">&times;</button>
            <h2>Create Account</h2>
            <form method="POST" action="register.php">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-row">
                        <div class="input-box half">
                            <input type="text" name="first_name" required>
                            <label>First Name</label>
                        </div>
                        <div class="input-box half">
                            <input type="text" name="last_name" required>
                            <label>Last Name</label>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="input-box half">
                            <input type="date" name="date_of_birth" required>
                            <label class="date-label">Date of Birth</label>
                        </div>
                        <div class="input-box half">
                            <select name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="form-section">
                    <h3>Account Information</h3>
                    <div class="input-box">
                        <input type="text" name="username" required>
                        <label>Username</label>
                    </div>
                    <div class="input-box">
                        <input type="email" name="email" required>
                        <label>Email Address</label>
                    </div>
                    <div class="input-box">
                        <input type="password" name="password" required>
                        <label>Password</label>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="form-section">
                    <h3>Contact Information</h3>
                    <div class="input-box">
                        <input type="tel" name="phone_number" pattern="[0-9]{11}" placeholder="09123456789">
                        <label>Phone Number</label>
                    </div>
                    <div class="input-box">
                        <textarea name="address" rows="2"></textarea>
                        <label class="textarea-label">Address</label>
                    </div>
                    
                    <!-- Terms and Conditions Section -->
                    <div class="terms-section">
                        <label class="terms-label">
                            <input type="checkbox" name="terms" id="termsCheckbox" required>
                            I agree to the <a href="#" class="terms-link" id="termsLink">Terms and Conditions</a>
                        </label>
                    </div>
                </div>

                <button type="submit" name="register" class="btn" id="registerButton" disabled>Create Account</button>
                <div class="login-register">
                    <p>Already have an account? <a href="#" class="login-link">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <main>
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-content">
                <h1>Discover Batangas</h1>
                <p>Experience the rich culture, traditions, and flavors of Batangas</p>
                <div class="hero-buttons">
                    <!-- Update the href to point to the testimonials section -->
                    <a href="#testimonials" class="cta-button">Start Exploring</a>
                    <a href="#festivals" class="cta-button secondary">Learn More</a>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
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

        <!-- Festival Section -->
        <section id="festivals" class="festival-section">
            <div class="section-header">
                <h2>Featured Festivals</h2>
                <p>Experience the vibrant celebrations of Batangas</p>
            </div>
            
            <!-- Festival Carousel -->
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
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="testimonials-section">
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

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All Rights Reserved.</p>
        </div>
        <div class="footer-links">
            <a href="#">Privacy Policy</a> |
            <a href="#">Terms of Service</a> |
            <a href="#">Contact Us</a>
        </div>
    </footer>
    <script>
        const loginBtn = document.querySelector('.btnLogin-popup');
        const popup = document.getElementById('popupForm');
        const closeBtn = document.getElementById('closeBtn');
        const closeRegisterBtn = document.getElementById('closeRegisterBtn');
        const registerLink = document.querySelector('.register-link');
        const loginLink = document.querySelector('.login-link');

        loginBtn.addEventListener('click', () => {
            popup.style.display = 'flex';
            document.getElementById('loginForm').style.display = 'block';
        });

        closeBtn.addEventListener('click', () => {
            popup.style.display = 'none';
        });

        closeRegisterBtn.addEventListener('click', () => {
            popup.style.display = 'none';
        });

        registerLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        });

        loginLink.addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
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

    // Smooth scroll functionality
    document.addEventListener('DOMContentLoaded', function() {
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

    <script>
    // Auto-hide alerts after 3 seconds
    document.addEventListener('DOMContentLoaded', function() {
        // Success alert
        let successAlert = document.getElementById('success-alert');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.animation = 'slideOut 0.3s ease-in-out'; // Made animation faster
                setTimeout(function() {
                    successAlert.style.display = 'none';
                }, 300); // Reduced from 500 to 300
            }, 2700); // Reduced from 3000 to 2700 to account for animation time
        }

        // Error alert
        let errorAlert = document.getElementById('error-alert');
        if (errorAlert) {
            setTimeout(function() {
                errorAlert.style.animation = 'slideOut 0.3s ease-in-out'; // Made animation faster
                setTimeout(function() {
                    errorAlert.style.display = 'none';
                }, 300); // Reduced from 500 to 300
            }, 2700); // Reduced from 3000 to 2700 to account for animation time
        }
    });

    // Add slide out animation
    const style = document.createElement('style');
    style.textContent = `
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
    `;
    document.head.appendChild(style);
    </script>

    <!-- Terms and Conditions Modal -->
    <div class="modal" id="termsModal">
        <div class="modal-content">
            <span class="close-modal" id="closeTermsModal">&times;</span>
            <h2>Terms and Conditions</h2>
            
            <div class="terms-content" id="termsContent">
                <section>
                    <h3>1. General Terms</h3>
                    <p>By accessing and using Explore Batangas, you accept and agree to be bound by the terms and provision of this agreement.</p>
                </section>

                <section>
                    <h3>2. User Account</h3>
                    <p>2.1. You must provide accurate, current, and complete information during registration.</p>
                    <p>2.2. You are responsible for maintaining the confidentiality of your account and password.</p>
                    <p>2.3. You agree to accept responsibility for all activities that occur under your account.</p>
                </section>

                <section>
                    <h3>3. E-Library Services</h3>
                    <p>3.1. The E-Library content is for personal, non-commercial use only.</p>
                    <p>3.2. You may not distribute, modify, or reproduce the materials without explicit permission.</p>
                    <p>3.3. Access to certain materials may be restricted based on user privileges.</p>
                </section>

                <section>
                    <h3>4. E-Commerce Terms</h3>
                    <p>4.1. Product Information:</p>
                    <ul>
                        <li>We strive to display accurate product information and pricing.</li>
                        <li>Colors may vary depending on your device's display.</li>
                    </ul>
                    
                    <p>4.2. Shipping and Delivery:</p>
                    <ul>
                        <li><strong>IMPORTANT: All shipping fees are to be handled and paid by the customer. The shop is not responsible for shipping costs.</strong></li>
                        <li>Delivery times are estimates and not guaranteed.</li>
                        <li>Risk of loss and title for items pass to you upon delivery to the carrier.</li>
                    </ul>

                    <p>4.3. Returns and Refunds:</p>
                    <ul>
                        <li>Returns must be initiated within 7 days of receipt.</li>
                        <li>Products must be unused and in original packaging.</li>
                        <li>Return shipping costs are the responsibility of the customer.</li>
                    </ul>
                </section>

                <section>
                    <h3>5. Privacy and Data Protection</h3>
                    <p>5.1. We collect and process your personal data in accordance with our Privacy Policy.</p>
                    <p>5.2. Your information will be used only for order processing and account management.</p>
                </section>

                <section>
                    <h3>6. Intellectual Property</h3>
                    <p>6.1. All content on Explore Batangas is protected by copyright and other intellectual property rights.</p>
                    <p>6.2. You may not use our content without explicit permission.</p>
                </section>

                <section>
                    <h3>7. Limitation of Liability</h3>
                    <p>7.1. We are not liable for any indirect, incidental, or consequential damages.</p>
                    <p>7.2. Our liability is limited to the amount paid for the product or service.</p>
                </section>

                <section>
                    <h3>8. Changes to Terms</h3>
                    <p>8.1. We reserve the right to modify these terms at any time.</p>
                    <p>8.2. Users will be notified of significant changes via email or website announcement.</p>
                </section>
            </div>
            
            <div class="terms-footer">
                <button id="acceptTerms" class="btn" disabled>I Have Read and Accept the Terms</button>
            </div>
        </div>
    </div>

    <script>
    // Terms and Conditions Modal
    const termsLink = document.getElementById('termsLink');
    const termsModal = document.getElementById('termsModal');
    const closeTermsModal = document.getElementById('closeTermsModal');

    termsLink.addEventListener('click', (e) => {
        e.preventDefault();
        termsModal.style.display = 'block';
    });

    closeTermsModal.addEventListener('click', () => {
        termsModal.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === termsModal) {
            termsModal.style.display = 'none';
        }
    });
    </script>

    <!-- Terms and Conditions Scroll Check -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const termsContent = document.getElementById('termsContent');
        const acceptButton = document.getElementById('acceptTerms');
        const termsCheckbox = document.getElementById('termsCheckbox');
        const registerButton = document.getElementById('registerButton');
        
        let hasRead = false;

        termsContent.addEventListener('scroll', function() {
            if (!hasRead && (termsContent.scrollHeight - termsContent.scrollTop <= termsContent.clientHeight + 100)) {
                hasRead = true;
                acceptButton.disabled = false;
            }
        });

        acceptButton.addEventListener('click', function() {
            termsCheckbox.checked = true;
            termsModal.style.display = 'none';
            registerButton.disabled = false; // Enable the register button when terms are accepted
        });

        // Prevent checkbox from being checked directly
        termsCheckbox.addEventListener('click', function(e) {
            if (!hasRead) {
                e.preventDefault();
                termsModal.style.display = 'block';
            }
        });

        // Update register button state when checkbox changes
        termsCheckbox.addEventListener('change', function() {
            registerButton.disabled = !termsCheckbox.checked;
        });
    });
    </script>

    <!-- Enable the register button only if terms are accepted -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const termsCheckbox = document.getElementById('termsCheckbox');
        const registerButton = document.getElementById('registerButton');

        termsCheckbox.addEventListener('change', function() {
            registerButton.disabled = !termsCheckbox.checked;
        });
    });
    </script>

</body>

</html>

<?php
$conn->close(); // Close the database connection
?>