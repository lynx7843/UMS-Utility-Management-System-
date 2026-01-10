<?php
session_start();
require_once 'db.php';

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'manager' && strtolower($role) !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['account_number'])) {
    die("Customer account number is required.");
}

$account_number = $_GET['account_number'];

$sql = "SELECT c.customer_id, c.user_id, u.full_name, u.username, c.account_number, c.phone_number, c.address
        FROM Customers c 
        JOIN Users u ON c.user_id = u.user_id 
        WHERE c.account_number = :account_number";
$stmt = $pdo->prepare($sql);
$stmt->execute(['account_number' => $account_number]);
$customer = $stmt->fetch();

if (!$customer) {
    die("Customer not found.");
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $phone_number = trim($_POST['phone_number']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($username)) {
        $error = "Full name and username are required.";
    } else {
        try {
            // Check if username already exists for another user
            $checkSql = "SELECT user_id FROM Users WHERE username = :username AND user_id != :user_id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute(['username' => $username, 'user_id' => $customer['user_id']]);
            
            if ($checkStmt->fetch()) {
                $error = "Username already exists. Please choose another.";
            } else {
                // Update user details with or without password
                if (!empty($password)) {
                    // Update with new password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateUserSql = "UPDATE Users 
                                      SET full_name = :full_name, 
                                          username = :username, 
                                          password_hash = :password_hash 
                                      WHERE user_id = :user_id";
                    $stmtUser = $pdo->prepare($updateUserSql);
                    $stmtUser->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'password_hash' => $hashedPassword,
                        'user_id' => $customer['user_id']
                    ]);
                } else {
                    // Update without changing password
                    $updateUserSql = "UPDATE Users 
                                      SET full_name = :full_name, 
                                          username = :username 
                                      WHERE user_id = :user_id";
                    $stmtUser = $pdo->prepare($updateUserSql);
                    $stmtUser->execute([
                        'full_name' => $full_name,
                        'username' => $username,
                        'user_id' => $customer['user_id']
                    ]);
                }

                // Update customer details
                $updateCustSql = "UPDATE Customers SET phone_number = :phone_number, address = :address WHERE customer_id = :customer_id";
                $stmtCust = $pdo->prepare($updateCustSql);
                $stmtCust->execute([
                    'phone_number' => $phone_number, 
                    'address' => $address,
                    'customer_id' => $customer['customer_id']
                ]);

                $success = "Customer details updated successfully!";
                
                // Refresh customer data
                $stmt->execute(['account_number' => $account_number]);
                $customer = $stmt->fetch();
            }
        } catch (Exception $e) {
            $error = "Error updating customer: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php include 'nav.php'; ?>
    <h2>Edit Customer: <?php echo htmlspecialchars($customer['account_number']); ?></h2>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" style="margin-top:30px; solid white; padding:20px;">
        <div style="margin-bottom:20px;">
            <label>Account Number (Read-only)</label>
            <input type="text" value="<?php echo htmlspecialchars($customer['account_number']); ?>" disabled style="background-color:#555; cursor:not-allowed;">
        </div>

        <div style="margin-bottom:20px;">
            <label for="full_name">Full Name *</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required>
        </div>

        <div style="margin-bottom:20px;">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($customer['username']); ?>" required>
        </div>

        <div style="margin-bottom:20px;">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($customer['address'] ?? ''); ?>">
        </div>

        <div style="margin-bottom:20px;">
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($customer['phone_number'] ?? ''); ?>">
        </div>

        <div style="margin-bottom:20px;">
            <label for="password">New Password (leave blank to keep current)</label>
            <input type="password" id="password" name="password" placeholder="Enter new password or leave blank">
            <small style="display:block; margin-top:5px; color:#aaa;">Only fill this if you want to change the password</small>
        </div>

        <div style="display:flex; gap:10px; margin-top:30px;">
            <button type="submit" class="btn">Save Changes</button>
            <a href="cust_list.php" class="btn" style="background-color:#666; text-decoration:none; display:inline-block; text-align:center;">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>