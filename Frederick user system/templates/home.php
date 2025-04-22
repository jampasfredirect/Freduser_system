<?php
require_once 'config.php';
require_once 'functions.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get all users
$users = [];
$result = $db->query("SELECT id, username, email, first_name, last_name, created_at FROM users ORDER BY created_at DESC");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // If the query fails, display an error or handle it appropriately
    $users = []; // Ensure $users is at least an empty array
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name'] ?? $_SESSION['username']) ?>!</h2>
        
        <a href="?logout" class="logout">Logout</a>
        <a href="edit_profile.php" class="edit-profile">Edit Profile</a>
        
        <h3>All Users</h3>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <a href="edit_profile.php">Edit</a> | 
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete your account?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
