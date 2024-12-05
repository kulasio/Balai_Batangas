<?php
session_start();
include 'connection.php';

// Your secret key from Google reCAPTCHA
$secretKey = "6LeOP2AqAAAAABbaTFsAFx7eXSc8-LAfc4clfw0X";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CAPTCHA response
    $captcha = $_POST['g-recaptcha-response'];

    if (!$captcha) {
        $_SESSION['error'] = "Please check the reCAPTCHA box.";
        header("Location: index.php");
        exit();
    }
    
    // Send the response to Google's verification server
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
    $responseKeys = json_decode($response, true);

    // Check if reCAPTCHA is successful
    if (intval($responseKeys["success"]) !== 1) {
        $_SESSION['error'] = "CAPTCHA verification failed. Please try again.";
        header("Location: index.php");
        exit();
    } else {
        // CAPTCHA was successful, proceed with login
        $email = $_POST['email'];
        $password = md5($_POST['password']);

        // Check if the email and password match
        $query = "SELECT * FROM users WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Store user info in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['success'] = "Welcome back, " . $user['username'] . "!";

            // Redirect based on the role
            if ($user['role'] == 'admin') {
                header("Location: adminpannel.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password. Please try again.";
            header("Location: index.php");
            exit();
        }
        $stmt->close();
    }
}
