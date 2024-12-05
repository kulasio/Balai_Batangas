<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$response = ['success' => false, 'message' => ''];
$user_id = $_SESSION['user_id'];

try {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== 0) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['profile_picture'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        throw new Exception('Invalid file type. Only JPG, PNG and GIF allowed.');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
        throw new Exception('File too large. Maximum size is 5MB.');
    }

    $upload_path = 'uploads/profiles/';
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    
    $new_filename = uniqid() . '.' . $ext;
    $destination = $upload_path . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Delete old profile picture if exists
        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $old_picture = $stmt->get_result()->fetch_assoc()['profile_picture'];
        
        if ($old_picture && file_exists($old_picture)) {
            unlink($old_picture);
        }

        // Update database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $destination, $user_id);
        $stmt->execute();
        
        $response['success'] = true;
        $response['message'] = 'Profile picture updated successfully';
    } else {
        throw new Exception('Error uploading file');
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?> 