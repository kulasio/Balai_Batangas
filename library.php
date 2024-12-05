<?php
// Start the session at the top before any output
session_start();

// Include the database connection file
include 'connection.php';

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch user data from the database using prepared statement
    $query = "SELECT profile_picture, username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $user = $result->fetch_assoc()) {
        $profile_picture = $user['profile_picture'];
        $username = $user['username'];
    } else {
        // Handle database error
        error_log("Error fetching user data: " . $conn->error);
        $profile_picture = '';
        $username = 'User';
    }
    
    $stmt->close();
} else {
    // If the user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Festival in Batangas</title>
    <link rel="stylesheet" href="css/responsive.css">
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

        /* Dropdown menu styling */
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
            color: #ffffff; /* Change this line to make the text white */
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

        /* Professional Main Content Styles */
        main {
            padding: 0;
            background: #ffffff;
        }

        .content-header {
            padding: 60px 0 40px;
            text-align: center;
            background: #ffffff;
            border-bottom: 1px solid #eee;
        }

        .content-header .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .content-header h1 {
            font-size: 2.5em;
            color: #1a1a1a;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .content-header p {
            font-size: 1.2em;
            color: #666;
            line-height: 1.6;
        }

        .search-container {
            padding: 30px 0;
            background: #ffffff;
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-form {
            display: flex;
            gap: 15px;
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .search-form input[type="text"] {
            flex: 1;
            padding: 15px 25px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-form input[type="text"]:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
            outline: none;
        }

        .search-btn, .clear-search {
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn {
            background: #8B0000;
            color: white;
            border: none;
        }

        .clear-search {
            background: #f5f5f5;
            color: #666;
            text-decoration: none;
            border: 1px solid #ddd;
        }

        .search-btn:hover {
            background: #660000;
        }

        .clear-search:hover {
            background: #ebebeb;
        }

        .cultural-heritage-layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-results {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-style: italic;
        }

        .gallery-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease;
        }

        .gallery-item:hover {
            transform: translateY(-5px);
        }

        .gallery-content {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 30px;
        }

        .gallery-text {
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .festival-intro {
            color: #666;
            line-height: 1.6;
            margin: 15px 0;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .gallery-content {
                grid-template-columns: 1fr;
            }
            
            .content-header h1 {
                font-size: 2em;
            }
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }
            
            .search-btn, .clear-search {
                width: 100%;
            }
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            position: relative;
            background-color: white;
            margin: auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
        }

        /* Pagination styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }

        .page-link {
            padding: 8px 16px;
            text-decoration: none;
            color: #333;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .page-link:hover {
            background-color: #ddd;
        }

        .page-link.active {
            background-color: #8B0000;
            color: white;
            border-color: #8B0000;
        }

        /* Responsive styles */
        @media (min-width: 576px) {
            .gallery-text p {
                font-size: 1rem;
            }
            .gallery-label {
                font-size: 1.1rem;
            }
            .gallery-item img {
                max-width: 250px;
            }
        }

        @media (min-width: 768px) {
            .gallery-text p {
                font-size: 1.1rem;
            }
            .gallery-label {
                font-size: 1.2rem;
            }
            .gallery-item img {
                max-width: 300px;
            }
        }

        @media (min-width: 992px) {
            .gallery-text p {
                font-size: 1.2rem;
            }
            .gallery-label {
                font-size: 1.3rem;
            }
        }

        /* Search Bar Styling */
        .search-container {
            margin-bottom: 30px;
            text-align: center;
        }

        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-form input[type="text"] {
            padding: 10px 15px;
            border: 2px solid #8B0000;
            border-radius: 5px;
            width: 100%;
            max-width: 400px;
            font-size: 16px;
        }

        .search-btn {
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background-color: #660000;
        }

        .clear-search {
            padding: 10px 20px;
            background-color: #666;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .clear-search:hover {
            background-color: #444;
        }

        .search-results {
            margin: 20px 0;
            font-style: italic;
            color: #666;
            text-align: center;
        }

        /* Responsive Search Bar */
        @media (max-width: 576px) {
            .search-form {
                flex-direction: column;
                padding: 0 15px;
            }

            .search-form input[type="text"],
            .search-btn,
            .clear-search {
                width: 100%;
            }
        }

        /* Typing Indicator */
        .typing-indicator {
            background-color: #f0f0f0;
            padding: 15px;
            display: flex;
            align-items: center;
        }

        .typing-indicator span {
            height: 8px;
            width: 8px;
            background: #8B0000;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            animation: bounce 1.3s linear infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.15s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.3s;
        }

        @keyframes bounce {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-4px);
            }
        }

        /* Improve message styling */
        .message {
            margin: 8px 0;
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 85%;
            word-wrap: break-word;
            line-height: 1.4;
        }

        .message.user {
            background: #8B0000;
            color: white;
            margin-left: auto;
        }

        .message.bot {
            background: #f0f0f0;
            margin-right: auto;
        }

        /* Professional Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 380px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .chat-header {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            padding: 20px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .chat-header span {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-header span::before {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            background: #4CAF50;
            border-radius: 50%;
            margin-right: 5px;
            box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.3);
        }

        .minimize-btn {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s ease;
        }

        .minimize-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .chat-body {
            height: 500px;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .message {
            margin: 8px 0;
            padding: 12px 16px;
            border-radius: 15px;
            max-width: 85%;
            font-size: 0.95rem;
            line-height: 1.4;
            position: relative;
            transition: all 0.3s ease;
        }

        .message.user {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message.bot {
            background: white;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .typing-indicator {
            background: white;
            padding: 15px 20px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 5px;
            width: fit-content;
            margin: 10px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #8B0000;
            border-radius: 50%;
            display: inline-block;
            opacity: 0.4;
            animation: typing 1.4s infinite;
        }

        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .typing-indicator span:nth-child(1) { animation-delay: 0s; }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

        .chat-input {
            display: flex;
            padding: 20px;
            background: white;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            gap: 10px;
        }

        .chat-input input {
            flex-grow: 1;
            padding: 12px 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 25px;
            outline: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .chat-input input:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 2px rgba(139, 0, 0, 0.1);
            background: white;
        }

        .chat-input button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .chat-input button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(139, 0, 0, 0.2);
        }

        .chat-input button:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .chat-widget {
                width: 100%;
                height: 100%;
                bottom: 0;
                right: 0;
                border-radius: 0;
                position: fixed;
            }

            .chat-body {
                height: calc(100vh - 140px);
            }

            .chat-input {
                padding: 15px;
                position: sticky;
                bottom: 0;
            }
        }

        /* Add these styles to your existing CSS */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .details-btn, .location-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
            font-size: 0.95rem;
        }

        .details-btn {
            background: #8B0000;
            color: white;
        }

        .location-btn {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }

        .details-btn:hover {
            background: #660000;
        }

        .location-btn:hover {
            background: #e9ecef;
        }

        .details-btn i, .location-btn i {
            font-size: 1rem;
        }

        /* Enhanced Festival Modal Styles */
        .festival-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            overflow-y: auto;
            padding: 20px;
        }

        .festival-modal-content {
            background: white;
            margin: 30px auto;
            width: 90%;
            max-width: 1100px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            animation: modalFadeIn 0.4s ease;
        }

        .modal-header {
            background: linear-gradient(135deg, #8B0000, #660000);
            color: white;
            padding: 25px 40px;
            position: relative;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h2 {
            margin: 0;
            font-size: 2em;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .close-modal {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%) scale(1.1);
        }

        .modal-body {
            padding: 40px;
        }

        .festival-details-grid {
            display: grid;
            gap: 40px;
        }

        .festival-modal-image {
            width: 100%;
            height: 450px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .festival-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .festival-modal-image:hover img {
            transform: scale(1.05);
        }

        .detail-section {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .detail-section h3 {
            color: #8B0000;
            font-size: 1.4em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .info-item i {
            font-size: 24px;
            color: #8B0000;
            background: rgba(139, 0, 0, 0.1);
            padding: 12px;
            border-radius: 8px;
        }

        .info-content {
            flex: 1;
        }

        .info-item strong {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 1.1em;
        }

        .info-item p {
            color: #666;
            line-height: 1.6;
            margin: 0;
        }

        .activities-list {
            display: grid;
            gap: 15px;
            padding: 0;
        }

        .activities-list li {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
        }

        .activities-list li:hover {
            background: #f0f0f0;
            transform: translateX(5px);
        }

        .activities-list li::before {
            content: "•";
            color: #8B0000;
            font-size: 1.5em;
        }

        /* Modal animations */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .modal-body {
                padding: 30px;
            }
            
            .festival-modal-image {
                height: 350px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .festival-modal-content {
                margin: 15px auto;
                width: 95%;
            }
            
            .modal-header {
                padding: 20px 25px;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .festival-modal-image {
                height: 250px;
            }
            
            .detail-section {
                padding: 20px;
            }
        }

        /* Add these styles to your existing CSS */
        .message.bot {
            padding: 15px;
            line-height: 1.5;
            background-color: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .message.bot p {
            margin: 0;
            padding: 0;
        }

        .message.bot div {
            line-height: 1.8;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid #ddd;
        }

        .chat-input input {
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #ddd;
            width: calc(100% - 70px);
            margin-right: 10px;
        }

        .chat-input button {
            padding: 8px 15px;
            border-radius: 20px;
            border: none;
            background-color: #8B0000;
            color: white;
            cursor: pointer;
        }

        .chat-input button:hover {
            background-color: #660000;
        }
    </style>
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
        <div class="content-header">
            <div class="container">
                <h1>Discover Batangas Festivals</h1>
                <p>Explore the vibrant cultural celebrations that make our province unique</p>
            </div>
        </div>

        <div class="search-container">
            <form method="GET" action="" class="search-form">
                <input type="text" name="search" placeholder="Search festivals..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit" class="search-btn">Search</button>
                <?php if(isset($_GET['search'])): ?>
                    <a href="library.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="cultural-heritage-layout">
            <?php
            // Define items per page
            $items_per_page = 3; // You can adjust this number
            
            // Get search term if it exists
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

            // Modify the count query to include search
            $count_query = "SELECT COUNT(*) as total FROM library WHERE status = 1";
            if ($search) {
                $count_query .= " AND (festival_name LIKE '%$search%' 
                                      OR description LIKE '%$search%' 
                                      OR location LIKE '%$search%'
                                      OR date_celebrated LIKE '%$search%')";
            }
            $count_result = mysqli_query($conn, $count_query);
            $row = mysqli_fetch_assoc($count_result);
            $total_items = $row['total'];
            $total_pages = ceil($total_items / $items_per_page);

            // Get current page
            $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $current_page = max(1, min($current_page, $total_pages));
            $offset = ($current_page - 1) * $items_per_page;

            // Modify the main query to include search
            $query = "SELECT * FROM library WHERE status = 1";
            if ($search) {
                $query .= " AND (festival_name LIKE '%$search%' 
                                 OR description LIKE '%$search%' 
                                 OR location LIKE '%$search%'
                                 OR date_celebrated LIKE '%$search%')";
            }
            $query .= " ORDER BY festival_id LIMIT $offset, $items_per_page";
            $result = mysqli_query($conn, $query);

            // Add this after the query to show search results count
            if ($search) {
                echo "<p class='search-results'>Found " . $total_items . " result(s) for '" . htmlspecialchars($search) . "'</p>";
            }

            while ($festival = mysqli_fetch_assoc($result)) {
                ?>
                <div class="gallery-item">
                    <div class="gallery-content">
                        <img src="img/<?php echo $festival['festival_image']; ?>" alt="<?php echo $festival['festival_name']; ?>" />
                        <div class="gallery-text">
                            <div>
                                <h2 class="gallery-label"><?php echo htmlspecialchars($festival['festival_name']); ?></h2>
                                <p class="festival-intro">
                                    <?php echo htmlspecialchars(substr($festival['short_intro'], 0, 150)) . '...'; ?>
                                </p>
                            </div>
                            <div class="action-buttons">
                                <button class="details-btn" onclick="showFestivalDetails(<?php echo $festival['festival_id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Show Details
                                </button>
                                <button class="location-btn" onclick="openMap('mapModal<?php echo $festival['festival_id']; ?>')">
                                    <i class="fas fa-map-marker-alt"></i> View Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal for this festival -->
                <div id="mapModal<?php echo $festival['festival_id']; ?>" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeMap('mapModal<?php echo $festival['festival_id']; ?>')">&times;</span>
                        <h2><?php echo $festival['festival_name']; ?> - Location</h2>
                        <p>Coordinates: <?php echo $festival['map_coordinates']; ?></p>
                        <iframe 
                            src="https://maps.google.com/maps?q=<?php echo urlencode($festival['location']); ?>&output=embed"
                            width="100%" 
                            height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>

                <!-- Enhanced Modal with all details -->
                <div id="festivalModal<?php echo $festival['festival_id']; ?>" class="festival-modal">
                    <div class="festival-modal-content">
                        <div class="modal-header">
                            <h2><?php echo htmlspecialchars($festival['festival_name']); ?></h2>
                            <button class="close-modal" onclick="closeFestivalDetails(<?php echo $festival['festival_id']; ?>)">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="festival-details-grid">
                                <div class="festival-modal-image">
                                    <img src="img/<?php echo $festival['festival_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($festival['festival_name']); ?>">
                                </div>
                                
                                <div class="detail-section">
                                    <h3>About the Festival</h3>
                                    <p><?php echo nl2br(htmlspecialchars($festival['description'])); ?></p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3>Festival Information</h3>
                                    <div class="info-grid">
                                        <div class="info-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <div>
                                                <strong>Date Celebrated</strong>
                                                <p><?php echo htmlspecialchars($festival['date_celebrated']); ?></p>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <div>
                                                <strong>Location</strong>
                                                <p><?php echo htmlspecialchars($festival['location']); ?></p>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-place-of-worship"></i>
                                            <div>
                                                <strong>Venue</strong>
                                                <p><?php echo htmlspecialchars($festival['venue'] ?? 'Various locations'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-section">
                                    <h3>Cultural Significance</h3>
                                    <p><?php echo htmlspecialchars($festival['cultural_significance']); ?></p>
                                </div>

                                <div class="detail-section">
                                    <h3>Activities & Highlights</h3>
                                    <ul class="activities-list">
                                        <?php 
                                        $activities = explode("\n", $festival['activities']);
                                        foreach($activities as $activity): 
                                            if(trim($activity) !== ''): 
                                        ?>
                                            <li><?php echo htmlspecialchars(trim($activity)); ?></li>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <!-- Pagination -->
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                    <a href="?page=<?php echo ($current_page - 1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link">&laquo; Previous</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=<?php echo ($current_page + 1); ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>" 
                       class="page-link">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All rights reserved.</p>
            
        </div>
    </footer>

    <script>
        function openMap(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeMap(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let i = 0; i < modals.length; i++) {
                if (event.target == modals[i]) {
                    modals[i].style.display = "none";
                }
            }
            
            // New festival modal handling
            const festivalModals = document.getElementsByClassName('festival-modal');
            for (let i = 0; i < festivalModals.length; i++) {
                if (event.target == festivalModals[i]) {
                    festivalModals[i].style.display = "none";
                    document.body.style.overflow = 'auto';
                }
            }
        };

        let isChatOpen = true;
        let isWaitingForResponse = false;

        function toggleChat() {
            const chatBody = document.querySelector('.chat-body');
            const minimizeBtn = document.querySelector('.minimize-btn');
            
            if (isChatOpen) {
                chatBody.style.display = 'none';
                minimizeBtn.textContent = '+';
            } else {
                chatBody.style.display = 'flex';
                minimizeBtn.textContent = '−';
            }
            isChatOpen = !isChatOpen;
        }

        async function sendMessage() {
            const input = document.getElementById('userInput');
            const message = input.value.trim();
            
            if (message && !isWaitingForResponse) {
                // Add user message
                addMessage(message, 'user');
                input.value = '';
                
                // Show typing indicator
                isWaitingForResponse = true;
                const typingIndicator = addTypingIndicator();
                
                try {
                    // Send message to backend
                    const response = await fetch('chat_handler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ message: message })
                    });
                    
                    const data = await response.json();
                    
                    // Remove typing indicator
                    typingIndicator.remove();
                    
                    if (data.status === 'success') {
                        addMessage(data.message, 'bot');
                    } else {
                        addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    typingIndicator.remove();
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
                
                isWaitingForResponse = false;
            }
        }

        function addMessage(text, sender) {
            const messagesDiv = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            messageDiv.textContent = text;
            messagesDiv.appendChild(messageDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function addTypingIndicator() {
            const messagesDiv = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot typing-indicator';
            typingDiv.innerHTML = '<span></span><span></span><span></span>';
            messagesDiv.appendChild(typingDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
            return typingDiv;
        }

        // Add event listener for Enter key in input
        document.getElementById('userInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function showFestivalDetails(festivalId) {
            const modal = document.getElementById('festivalModal' + festivalId);
            modal.style.display = "block";
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeFestivalDetails(festivalId) {
            const modal = document.getElementById('festivalModal' + festivalId);
            modal.style.display = "none";
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('festival-modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = "none";
                    document.body.style.overflow = 'auto';
                }
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                const modals = document.getElementsByClassName('festival-modal');
                for (let modal of modals) {
                    if (modal.style.display === "block") {
                        modal.style.display = "none";
                        document.body.style.overflow = 'auto';
                    }
                }
            }
        });
    </script>

    <!-- Chat Widget -->
    <div class="chat-widget" id="chatWidget">
        <div class="chat-header" onclick="toggleChat()">
            <span>CHIP Assistant</span>
            <button class="minimize-btn">−</button>
        </div>
        <div class="chat-body">
            <div class="chat-messages" id="chatMessages">
                <div class="message bot">
                    <p style="margin-bottom: 15px;">👋 Hi! I'm CHIP (Cultural Heritage Information Provider). I'm your personal guide to Batangas festivals and cultural heritage. How can I assist you today?</p>
                    
                    <p style="margin-bottom: 10px;">I can help you with these topics about Batangas:</p>

                    <div style="padding-left: 15px; margin-bottom: 15px;">
                        1. 🍲 Local Food & Delicacies<br>
                        2. ☕ Kapeng Barako<br>
                        3. 🎨 Traditional Handicrafts<br>
                        4. 🎉 Festivals & Events<br>
                        5. 🐟 Marine Products<br>
                        6. 🏺 Local Industries
                    </div>

                    <p>Please choose one of these topics or ask a specific question about them!</p>
                </div>
            </div>
            <div class="chat-input">
                <input type="text" id="userInput" placeholder="Ask CHIP about Batangas festivals...">
                <button onclick="sendMessage()">
                    Send
                </button>
            </div>
        </div>
    </div>
</body>
</html>