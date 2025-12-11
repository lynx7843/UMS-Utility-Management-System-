<?php
session_start();
require_once 'db.php';
if ($_SESSION['role'] != 'manager') { header("Location: login.php"); exit; }

$users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$readings = $pdo->query("SELECT COUNT(*) FROM MeterReadings")->fetchColumn();
$payments = $pdo->query("SELECT COUNT(*) FROM Payments")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head><title>Manager</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Overview</h2>
        <div class="grid">
            <div class="card"><h3>Users</h3><div class="big-text"><?php echo $users; ?></div></div>
            <div class="card"><h3>Readings</h3><div class="big-text"><?php echo $readings; ?></div></div>
            <div class="card"><h3>Payments</h3><div class="big-text"><?php echo $payments; ?></div></div>
        </div>
    </div>
</body>
</html>