<?php
include 'includes/db_connect.php';

// Check if a user ID is provided
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete the user
    $stmt = $db->prepare("DELETE FROM Users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);

    // Redirect back to the users page
    header('Location: users.php');
    exit;
} else {
    echo "User ID not provided";
    exit;
}

