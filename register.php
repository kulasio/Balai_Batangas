<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Start transaction
    $conn->begin_transaction();

    try {
        // Basic user information
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = md5($_POST['password']); // Consider using password_hash() instead
        $role = 'user';
        $phone_number = $_POST['phone_number'];

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone_number, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $password, $phone_number, $role);
        $stmt->execute();
        
        // Get the user_id of the newly created user
        $user_id = $conn->insert_id;

        // Additional user details
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $date_of_birth = $_POST['date_of_birth'];
        $gender = $_POST['gender'];
        $address = $_POST['address'];

        // Insert into user_details table
        $stmt = $conn->prepare("INSERT INTO user_details (user_id, first_name, last_name, date_of_birth, gender, phone_number, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $date_of_birth, $gender, $phone_number, $address);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Registration successful! Please login to continue.";
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
}

$conn->close();
