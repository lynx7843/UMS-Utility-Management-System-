<?php
session_start();
require_once 'db.php';
if ($_SESSION['role'] != 'manager') { header("Location: login.php"); exit; }

$users = $pdo->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$readings = $pdo->query("SELECT COUNT(*) FROM MeterReadings")->fetchColumn();
$payments = $pdo->query("SELECT COUNT(*) FROM Payments")->fetchColumn();


$custSql = "
    SELECT 
        (
            ISNULL((SELECT SUM(mr.units_consumed * ut.unit_rate) 
             FROM MeterReadings mr 
             JOIN UtilityTypes ut ON mr.utility_id = ut.utility_id 
             WHERE mr.customer_id = c.customer_id), 0)
            - 
            ISNULL((SELECT SUM(p.amount) 
             FROM Payments p 
             WHERE p.customer_id = c.customer_id), 0)
        ) as balance
    FROM Customers c
";
$balances = $pdo->query($custSql)->fetchAll();


$total_charges = 0;
$total_collected = 0;
$total_outstanding = 0;

foreach ($balances as $b) {
    if ($b['balance'] > 0) {
        $total_outstanding += $b['balance'];
    }
}

$total_charges = $pdo->query("
    SELECT ISNULL(SUM(mr.units_consumed * ut.unit_rate), 0) as total
    FROM MeterReadings mr 
    JOIN UtilityTypes ut ON mr.utility_id = ut.utility_id
")->fetch()['total'];

$total_collected = $pdo->query("
    SELECT ISNULL(SUM(amount), 0) as total
    FROM Payments
")->fetch()['total'];
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

        <h2 style="margin-top: 40px;">Financial Overview</h2>
        <div class="grid">
            <div class="card">
                <h3>Total Charges</h3>
                <div class="big-text">Rs. <?php echo number_format($total_charges, 2); ?></div>
            </div>
            <div class="card">
                <h3>Collected Amount</h3>
                <div class="big-text">Rs. <?php echo number_format($total_collected, 2); ?></div>
            </div>
            <div class="card">
                <h3>Outstanding Balance</h3>
                <div class="big-text">Rs. <?php echo number_format($total_outstanding, 2); ?></div>
            </div>
        </div>
    </div>
</body>
</html>