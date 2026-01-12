<?php
session_start();
require_once 'db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $type = $_POST['login_type']; 

    $stmt = $pdo->prepare("SELECT * FROM Users WHERE username = ?");
    $stmt->execute([$user]);
    $row = $stmt->fetch();

    if ($row && password_verify($pass, $row['password_hash'])) {
        if ($type === 'customer' && $row['role'] !== 'customer') {
            $error = "Access Denied: Please use Staff Login.";
        } elseif ($type === 'staff' && $row['role'] === 'customer') {
            $error = "Access Denied: Please use Customer Login.";
        } else {
            
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            
            if ($row['role'] == 'manager') header("Location: manager.php");
            elseif ($row['role'] == 'admin') header("Location: admin.php");
            elseif ($row['role'] == 'field_officer') header("Location: meter_reading.php");
            elseif ($row['role'] == 'cashier') header("Location: cashier.php");
            elseif ($row['role'] == 'customer') header("Location: customer_dashboard.php");
            exit;
        }
    } else {
        $error = "Invalid Credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="center-box">
        <h1>Utility System</h1>
        <form method="POST">
            <div class="toggle-container">
                <div id="btn-cust" class="toggle-btn active" onclick="setMode('customer')">Customer</div>
                <div id="btn-staff" class="toggle-btn" onclick="setMode('staff')">Staff</div>
            </div>
            <input type="hidden" name="login_type" id="login_type" value="customer">
            
            <h3 id="lbl-title">Customer Login</h3>
            <?php if($error) echo "<div class='alert error'>$error</div>"; ?>

            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn">Login</button>
        </form>
    </div>
    <script>
        function setMode(m) {
            document.getElementById('login_type').value = m;
            document.getElementById('btn-cust').className = (m=='customer')?'toggle-btn active':'toggle-btn';
            document.getElementById('btn-staff').className = (m=='staff')?'toggle-btn active':'toggle-btn';
            document.getElementById('lbl-title').innerText = (m=='customer')?'Customer Login':'Staff Login';
        }
    </script>
</body>
</html>