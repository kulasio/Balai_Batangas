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
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="css/responsive.css">
    <title>About - Balai Batangas</title>
    <style>
        /* CSS Variables for common colors */
        :root {
            --main-bg-color: #8B0000;
            --text-color: rgb(51, 51, 51);
            --bg-light: rgb(244, 244, 244);
            --white: rgb(255, 255, 255);
            --black: rgb(0, 0, 0);
        }

        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html, body {
            height: 100%;
            background-color: var(--bg-light);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--main-bg-color);
            padding: 10px 20px;
            color: var(--white);
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
            color: var(--white);
            font-weight: bold;
        }

        header nav ul li a:hover {
            color: var(--black);
        }

        /* User Menu Styles */
        .user-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f0f0f0;
            border: 2px solid var(--white);
        }

        .user-icon img {
            width: 100%;
            height: 100%;
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

        .dropdown-content a, p {
            color: var(--text-color);
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
            background-color: var(--main-bg-color);
            color: var(--white);
            padding: 20px 0;
            text-align: center;
            width: 100%;
            margin-top: auto;
        }

        .footer-content p {
            margin-bottom: 10px;
            color: var(--white);
        }

        .footer-links a {
            color: var(--black);
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
            color: var(--white);
        }

        .about-hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }

        /* Mission & Vision Section */
        .mission-vision {
            padding: 60px 20px;
            background-color: var(--white);
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
            color: var(--main-bg-color);
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
            background: var(--white);
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
            background: var(--white);
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
            background-color: #2c2c2c;
            color: #e0e0e0;
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
            color: #ffffff;
        }

        .contact-info h3 {
            color: #ffffff;
            margin-bottom: 15px;
        }

        .contact-info p {
            color: #b0b0b0;
            line-height: 1.6;
        }

        /* Additional Professional Styles */
        .team-bio {
            font-size: 0.9em;
            color: #666;
            margin-top: 10px;
            line-height: 1.4;
        }

        .timeline-item {
            transition: transform 0.3s ease;
        }

        .timeline-item:hover {
            transform: translateY(-5px);
        }

        .mission-vision-card {
            transition: box-shadow 0.3s ease;
        }

        .mission-vision-card:hover {
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .contact-info {
            transition: transform 0.3s ease;
        }

        .contact-info:hover {
            transform: translateY(-5px);
        }

        .team-member {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        .history-content h2,
        .team-section h2,
        .contact-section h2 {
            font-size: 2.5em;
            margin-bottom: 30px;
            text-align: center;
        }

        .timeline-item h3 {
            color: var(--main-bg-color);
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .timeline-item p {
            line-height: 1.6;
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
        <!-- About Hero Section -->
        <section class="about-hero">
            <div class="hero-content">
                <h1>About Balai Batangas</h1>
                <p>Preserving Heritage, Empowering Communities, Building Futures</p>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="mission-vision">
            <div class="mission-vision-grid">
                <div class="mission-vision-card">
                    <h3>Our Mission</h3>
                    <p>To create a sustainable digital ecosystem that preserves and promotes Batangas' cultural heritage while empowering local artisans, entrepreneurs, and communities through innovative technology solutions and market access.</p>
                </div>
                <div class="mission-vision-card">
                    <h3>Our Vision</h3>
                    <p>To establish Balai Batangas as the definitive platform for authentic Batangueño culture and commerce, setting the standard for digital cultural preservation and sustainable economic development in the Philippines.</p>
                </div>
                <div class="mission-vision-card">
                    <h3>Our Values</h3>
                    <p>• Cultural Authenticity<br>
                       • Community Empowerment<br>
                       • Sustainable Development<br>
                       • Digital Innovation<br>
                       • Excellence in Service</p>
                </div>
            </div>
        </section>

        <!-- History Section -->
        <section class="history-section">
            <div class="history-content">
                <h2>Our Heritage & Innovation</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <h3>Cultural Foundation</h3>
                        <p>Batangas stands as a beacon of Filipino heritage, renowned for its rich traditions, craftsmanship, and cultural significance. From the intricate embroidery of barongs to the distinctive flavor of Kapeng Barako, our province has been a custodian of invaluable cultural treasures.</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Digital Transformation</h3>
                        <p>In 2024, Balai Batangas emerged as a pioneering digital platform, bridging the gap between traditional culture and modern commerce. Our initiative represents a commitment to preserving cultural heritage while embracing technological innovation.</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Community Impact</h3>
                        <p>Through strategic partnerships with local artisans, producers, and cultural organizations, we've created a sustainable ecosystem that supports economic growth while ensuring the authenticity and quality of Batangueño products and experiences.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <h2>Leadership Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="img/nikko.jpg" alt="Nikko Mission">
                    <h3>Nikko Mission</h3>
                    <p>Chief Executive Officer</p>
                    <p class="team-bio">Leading technological innovation and strategic vision for Balai Batangas.</p>
                </div>
                <div class="team-member">
                    <img src="img/mark.jpg" alt="Christian Panopio">
                    <h3>Christian Panopio</h3>
                    <p>Chief Technology Officer</p>
                    <p class="team-bio">Overseeing platform development and technical infrastructure.</p>
                </div>
                <div class="team-member">
                    <img src="img/josh.jpg" alt="Joshua Almario">
                    <h3>Joshua Almario</h3>
                    <p>Chief Design Officer</p>
                    <p class="team-bio">Crafting user experiences and managing digital presence.</p>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section">
            <h2>Connect With Us</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Headquarters</h3>
                    <p>Batangas City Innovation Center<br>
                    Batangas City, 4200<br>
                    Philippines</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-envelope"></i>
                    <h3>Business Inquiries</h3>
                    <p>partnerships@balaibatangas.com<br>
                    support@balaibatangas.com</p>
                </div>
                <div class="contact-info">
                    <i class="fas fa-phone"></i>
                    <h3>Contact Numbers</h3>
                    <p>Main: +63 123 456 7890<br>
                    Support: +63 123 456 7891</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Explore Batangas. All rights reserved.</p>
           
        </div>
    </footer>

    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/your-kit-code.js" crossorigin="anonymous"></script>
</body>
</html> 