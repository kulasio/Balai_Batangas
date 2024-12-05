<?php
// Start the session at the top before any output
session_start();
include 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/responsive.css">
    <title>About - Balai Batangas</title>
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

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #8B0000;
            padding: 10px 20px;
            color: rgb(255, 255, 255);
            font-size: 25px;
        }

        header .logo img {
            max-height: 50px;
            margin-top: 10px;
        }

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

        /* User Menu Styles */
        .user-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: #333;
            border: 2px solid #333;
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

        .dropdown-content a, p {
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

        /* Footer Styles */
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

        /* About Hero Section */
        .about-hero {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/batangas-landscape.jpg');
            background-size: cover;
            background-position: center;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .about-hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }

        /* Mission & Vision Section */
        .mission-vision {
            padding: 60px 20px;
            background-color: #fff;
            text-align: center;
        }

        .mission-vision-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto;
        }

        .mission-vision-card {
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: #f9f9f9;
        }

        .mission-vision-card h3 {
            color: #8B0000;
            margin-bottom: 15px;
        }

        /* Team Section */
        .team-section {
            padding: 60px 20px;
            background-color: #f4f4f4;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto;
        }

        .team-member {
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 15px;
            object-fit: cover;
        }

        /* History Section */
        .history-section {
            padding: 60px 20px;
            background: white;
        }

        .history-content {
            max-width: 1000px;
            margin: 0 auto;
            text-align: justify;
        }

        .timeline {
            margin: 40px 0;
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            margin: 20px 0;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Contact Section */
        .contact-section {
            padding: 60px 20px;
            background-color: #8B0000;
            color: white;
            text-align: center;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 40px auto;
        }

        .contact-info {
            padding: 20px;
        }

        .contact-info i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        /* Add these styles in your existing <style> section */
        .auth-buttons {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .login-btn, .register-btn {
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .login-btn {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .register-btn {
            background-color: white;
            color: #8B0000;
            border: 2px solid white;
        }

        .login-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .register-btn:hover {
            background-color: #f1f1f1;
        }

        /* Remove these user menu styles since they're not needed */
        .user-icon,
        .user-menu,
        .dropdown-content {
            display: none;
        }

        /* Login Button Styles */
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

        /* Form Popup Styles */
        .popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .form-box {
            background: #fff;
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            position: relative;
            z-index: 10000;
        }

        .form-box h2 {
            text-align: center;
            margin-bottom: 35px;
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        /* Input Fields */
        .input-box {
            position: relative;
            margin-bottom: 25px;
        }

        .input-box label {
            display: block;
            margin-bottom: 10px;
            font-size: 15px;
            color: #555;
            font-weight: 500;
        }

        .input-box input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .input-box input:focus {
            border-color: #8B0000;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
            outline: none;
        }

        /* Remember-Forgot Section */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 25px 0;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
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
            padding: 16px;
            background-color: #8B0000;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 25px;
        }

        .btn:hover {
            background-color: #660000;
        }

        /* Login-Register Switch */
        .login-register {
            text-align: center;
            font-size: 15px;
            color: #666;
            padding-top: 20px;
            border-top: 1px solid #eee;
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
            top: 20px;
            right: 20px;
            background-color: transparent;
            color: #666;
            border: none;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            transition: all 0.3s ease;
            border-radius: 50%;
        }

        .close-btn:hover {
            color: #333;
            background-color: #f5f5f5;
        }

        /* reCAPTCHA Styling */
        .g-recaptcha {
            margin-bottom: 25px;
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            .form-box {
                padding: 30px;
                margin: 15px;
            }

            .form-box h2 {
                font-size: 24px;
                margin-bottom: 25px;
            }

            .input-box input {
                padding: 12px;
            }
        }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
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

    <!-- Add this popup form structure -->
    <div class="popup" id="popupForm">
        <div class="form-box login" id="loginForm">
            <button class="close-btn" id="closeBtn">&times;</button>
            <h2>Login</h2>
            <form method="POST" action="login.php">
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
                    <a href="#">Forgot Password?</a>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
                <div class="login-register">
                    <p>Don't have an account? <a href="#" class="register-link">Register</a></p>
                </div>
            </form>
        </div>

        <div class="form-box register" id="registerForm" style="display: none;">
            <button class="close-btn" id="closeRegisterBtn">&times;</button>
            <h2>Register</h2>
            <form method="POST" action="register.php">
                <div class="input-box">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-box">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-box">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="register" class="btn">Register</button>
                <div class="login-register">
                    <p>Already have an account? <a href="#" class="login-link">Login</a></p>
                </div>
            </form>
        </div>
    </div>

    <main>
        <!-- About Hero Section -->
        <section class="about-hero">
            <div class="hero-content">
                <h1>About Balai Batangas</h1>
                <p>Connecting Culture, Commerce, and Community</p>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="mission-vision">
            <div class="mission-vision-grid">
                <div class="mission-vision-card">
                    <h3>Our Mission</h3>
                    <p>To promote and preserve Batangas' cultural heritage by creating a digital marketplace that connects local artisans and producers with customers worldwide.</p>
                </div>
                <div class="mission-vision-card">
                    <h3>Our Vision</h3>
                    <p>To be the premier platform for discovering and experiencing Batangas' rich cultural offerings, fostering economic growth while preserving traditional practices.</p>
                </div>
            </div>
        </section>

        <!-- History Section -->
        <section class="history-section">
            <div class="history-content">
                <h2>Our Journey</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <h3>2024</h3>
                        <p>Launch of Balai Batangas platform, bringing local products and cultural experiences to a digital marketplace.</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Heritage</h3>
                        <p>Batangas has been known for its rich cultural heritage, from traditional crafts to local delicacies. Our platform aims to preserve and promote these treasures.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <h2>Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="img/team-member1.jpg" alt="Team Member 1">
                    <h3>John Doe</h3>
                    <p>Founder & CEO</p>
                </div>
                <div class="team-member">
                    <img src="img/team-member2.jpg" alt="Team Member 2">
                    <h3>Jane Smith</h3>
                    <p>Cultural Director</p>
                </div>
                <div class="team-member">
                    <img src="img/team-member3.jpg" alt="Team Member 3">
                    <h3>Mike Johnson</h3>
                    <p>Community Manager</p>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <h2>Get in Touch</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Location</h3>
                    <p>Batangas City, Philippines</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>contact@balaibatangas.com</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>+63 123 456 7890</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All rights reserved.</p>
            <div class="footer-links">
                <a href="privacyguest.php">Privacy Policy</a> |
                <a href="termsguest.php">Terms of Service</a>
            </div>
        </div>
    </footer>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
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
</body>
</html> 