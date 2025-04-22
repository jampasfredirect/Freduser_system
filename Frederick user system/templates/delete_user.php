<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ensure the user is trying to delete their own account
if (isset($_GET['id']) && $_GET['id'] == $_SESSION['user_id']) {
    $user_id = $_SESSION['user_id'];
    
    // Call the delete function
    if (deleteUser($db, $user_id)) {
        // Logout the user after deletion
        session_destroy();
        header("Location: login.php?account_deleted=true");
        exit;
    } else {
        echo "Error deleting account. Please try again.";
    }
} else {
    echo "You are not authorized to delete this account.";
}
?>
