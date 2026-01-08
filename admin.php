<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager')) {
    header("Location: login.php"); exit;
}

$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = $_POST['username'];
    $p = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fn = $_POST['full_name'];
    $r = $_POST['role'];
    $addr = $_POST['address'];
    $phone = $_POST['phone_number']; 
    try {
        $pdo->beginTransaction();

        $sql1 = "INSERT INTO Users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$u, $p, $fn, $r]);
        
        $new_user_id = $pdo->lastInsertId();

        if ($r == 'customer') {
            $acc = "ACC-" . rand(1000, 9999);
            $sql2 = "INSERT INTO Customers (user_id, account_number, address, phone_number) VALUES (?, ?, ?, ?)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([$new_user_id, $acc, $addr, $phone]);
            $msg = "<div class='alert success'>Customer Created. Account #: $acc</div>";
        } else {
            $msg = "<div class='alert success'>Staff Member Created.</div>";
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $msg = "<div class='alert error'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Register User</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="role" onchange="toggleFields()">
                    <option value="customer" style="background-color: #2a2a2c; color: #ffffff;">Customer</option>
                    <option value="field_officer" style="background-color: #2a2a2c; color: #ffffff;">Field Officer</option>
                    <option value="cashier" style="background-color: #2a2a2c; color: #ffffff;">Cashier</option>
                    <option value="manager" style="background-color: #2a2a2c; color: #ffffff;">Manager</option>
                </select>
            </div>
            <div class="form-group"><label>Full Name</label><input type="text" name="full_name" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            
            <div id="cust-fields">
                <div class="form-group"><label>Address</label><input type="text" name="address"></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="phone_number"></div>
            </div>

            <button class="btn">Register</button>
        </form>
        
    </div>
    <script>
        function toggleFields() {
            var r = document.getElementById('role').value;
            document.getElementById('cust-fields').style.display = (r == 'customer') ? 'block' : 'none';
        }
    </script>
</body>
</html>