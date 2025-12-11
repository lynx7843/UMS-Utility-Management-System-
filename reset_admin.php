<?php
// reset_admin.php
require_once 'db.php';

$username = 'admin';
$password = 'admin123'; // This is the password we are setting
$role = 'admin';
$fullName = 'System Administrator';

// Generate the hash using YOUR server's algorithm
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 1. Delete old admin if exists (to avoid duplicate errors)
    $stmt = $pdo->prepare("DELETE FROM Users WHERE username = ?");
    $stmt->execute([$username]);

    // 2. Insert new Admin User
    $sql = "INSERT INTO Users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hash, $fullName, $role]);

    echo "<h1>Success!</h1>";
    echo "<p>Admin user recreated successfully.</p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> admin</li>";
    echo "<li><strong>Password:</strong> admin123</li>";
    echo "<li><strong>Role:</strong> admin</li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Go to Login Page</a> (Select 'Staff' toggle)</p>";

} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>