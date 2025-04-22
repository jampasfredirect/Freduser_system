<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get current user data
$stmt = $db->prepare("SELECT username, email, first_name, last_name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    
    // Validate input
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    if (empty($errors)) {
        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already in use by another account";
        } else {
            // Update user
            $stmt = $db->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ? WHERE id = ?");
            $stmt->bind_param("sssi", $email, $first_name, $last_name, $user_id);
            
            if ($stmt->execute()) {
                // Update session data
                $_SESSION['email'] = $email;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                
                $success = true;
            } else {
                $errors[] = "Failed to update profile. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>
        
        <?php if ($success): ?>
            <div class="success">Profile updated successfully!</div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div>
                <label>Username:</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                <small>Username cannot be changed</small>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
            </div>
            <div>
                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
            </div>
            <button type="submit">Update Profile</button>
        </form>
        
        <p><a href="home.php">Back to Home</a></p>
    </div>
</body>
</html>