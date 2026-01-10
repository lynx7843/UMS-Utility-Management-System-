<?php
session_start();
require_once 'db.php';

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'manager' && strtolower($role) !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("User ID is required.");
}

$user_id = $_GET['id'];


$sql = "SELECT user_id, full_name, username, role 
        FROM Users 
        WHERE user_id = :user_id AND role != 'customer'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);
$staff = $stmt->fetch();

if (!$staff) {
    die("Staff member not found.");
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $new_role = trim($_POST['role']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($username) || empty($new_role)) {
        $error = "Full name, username, and role are required.";
    } else {
        try {
            
            $checkSql = "SELECT user_id FROM Users WHERE username = :username AND user_id != :user_id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute(['username' => $username, 'user_id' => $user_id]);
            
            if ($checkStmt->fetch()) {
                $error = "Username already exists. Please choose another.";
            } else {
               
                if (!empty($password)) {
                  
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE Users 
                                  SET full_name = :full_name, 
                                      username = :username, 
                                      role = :role, 
                                      password_hash = :password_hash 
                                  WHERE user_id = :user_id";
                    $stmtUpdate = $pdo->prepare($updateSql);
                    $stmtUpdate->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'role' => $new_role,
                        'password_hash' => $hashedPassword,
                        'user_id' => $user_id
                    ]);
                } else {
                   
                    $updateSql = "UPDATE Users 
                                  SET full_name = :full_name, 
                                      username = :username, 
                                      role = :role 
                                  WHERE user_id = :user_id";
                    $stmtUpdate = $pdo->prepare($updateSql);
                    $stmtUpdate->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'role' => $new_role,
                        'user_id' => $user_id
                    ]);
                }

                $success = "Staff member details updated successfully!";
                
            
                $stmt->execute(['user_id' => $user_id]);
                $staff = $stmt->fetch();
            }
        } catch (Exception $e) {
            $error = "Error updating staff member: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php include 'nav.php'; ?>
    <h2>Edit Staff Member: <?php echo htmlspecialchars($staff['full_name']); ?></h2>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-top:30px; solid white; padding:20px;">
        <div style="margin-bottom:20px;">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($staff['full_name']); ?>" required>
        </div>

        <div style="margin-bottom:20px;">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($staff['username']); ?>" required>
        </div>

        <div style="margin-bottom:20px;">
            <label for="role">Role *</label>
            <select id="role" name="role" required style="height:45px;">
                <option value="customer" <?php if (strtolower($staff['role']) === 'customer') echo 'selected'; ?> style="background-color: #2a2a2c; color: #ffffff;">Customer</option>
                <option value="field_officer" <?php if (strtolower($staff['role']) === 'field_officer') echo 'selected'; ?> style="background-color: #2a2a2c; color: #ffffff;">Field Officer</option>
                <option value="cashier" <?php if (strtolower($staff['role']) === 'cashier') echo 'selected'; ?> style="background-color: #2a2a2c; color: #ffffff;">Cashier</option>
                <option value="manager" <?php if (strtolower($staff['role']) === 'manager') echo 'selected'; ?> style="background-color: #2a2a2c; color: #ffffff;">Manager</option>                
            </select>
        </div>

        <div style="margin-bottom:20px;">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" placeholder="Enter new password or leave blank">
            <small style="display:block; margin-top:5px; color:#aaa;">Only fill this if you want to change the password</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:30px;">
            <button type="submit" class="btn">Save Changes</button>
            <a href="staff.php" class="btn" style="background-color:#666; text-decoration:none; display:inline-block; text-align:center;">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>