<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $secret_key = trim($_POST['secret_key']);
    
    // Fetch the secret key from the database
    $stmt = $conn->prepare("SELECT setting_value FROM admin_settings WHERE setting_name = 'forgot_password_key'");
    $stmt->execute();
    $result = $stmt->get_result();
    $correct_secret_key = $result->fetch_assoc()['setting_value'];
    
    if ($secret_key !== $correct_secret_key) {
        $_SESSION['error'] = "Invalid secret key";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();
        header("Location: settings/reset_password.php?token=" . $token);
        exit();
    } else {
        $_SESSION['error'] = "No account found with that email address";
        header("Location: forgot_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forgot Password - Explore Batangas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="css/responsive.css">
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

        /* Forgot Password Specific Styles */
        .forgot-password-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .forgot-password-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        
        .forgot-password-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .input-group label {
            color: #666;
            font-size: 14px;
        }
        
        .input-group input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .submit-btn {
            background: #8B0000;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .submit-btn:hover {
            background: #660000;
        }
        
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        
        .back-link a {
            color: #8B0000;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Replace login button styles with back button styles */
        .btnBack {
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

        .btnBack:hover {
            background: #fff;
            color: #162938;
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

    <!-- Updated Header -->
    <header>
        <div class="logo">
            <img src="img/logo3.png" alt="Logo" />
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="libraryguest.php">Library</a></li>
                <li><a href="aboutguest.php">About</a></li>
                <button class="btnBack" onclick="history.back()">Go Back</button>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <form class="forgot-password-form" method="POST" action="forgot_password.php">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="secret_key">Secret Key</label>
                <input type="password" id="secret_key" name="secret_key" required>
            </div>
            <button type="submit" class="submit-btn">Reset Password</button>
        </form>
        <div class="back-link">
            <a href="index.php">Back to Login</a>
        </div>
        <p style="text-align: center; margin-top: 15px; font-size: 14px; color: #666;">
            Contact the admin to get the secret code: <a href="mailto:balaibatangas@example.com">balaibatangas@example.com</a>
        </p>
    </div>

    <!-- Updated Footer -->
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

    <!-- Add the alert auto-hide script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let successAlert = document.getElementById('success-alert');
        if (successAlert) {
            setTimeout(function() {
                successAlert.style.animation = 'slideOut 0.3s ease-in-out';
                setTimeout(function() {
                    successAlert.style.display = 'none';
                }, 300);
            }, 2700);
        }

        let errorAlert = document.getElementById('error-alert');
        if (errorAlert) {
            setTimeout(function() {
                errorAlert.style.animation = 'slideOut 0.3s ease-in-out';
                setTimeout(function() {
                    errorAlert.style.display = 'none';
                }, 300);
            }, 2700);
        }
    });
    </script>
</body>
</html>

<?php
$conn->close();
?> 