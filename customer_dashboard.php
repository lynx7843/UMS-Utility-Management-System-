<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') { 
    header("Location: login.php"); 
    exit; 
}

$uid = $_SESSION['user_id'];

$sql = "SELECT c.*, u.full_name 
        FROM Customers c 
        JOIN Users u ON c.user_id = u.user_id 
        WHERE c.user_id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$uid]);
$cust = $stmt->fetch();


$rates = $pdo->query("SELECT * FROM InterestRates WHERE id = 1")->fetch();
if (!$rates) {
    $rates = ['rate_14_days' => 1.0, 'rate_30_days' => 1.5];
}


$balance = 0;
$days_overdue = 0;
if ($cust) {
    $balanceSql = "
        SELECT 
            (
                ISNULL((SELECT SUM(mr.units_consumed * ut.unit_rate) 
                 FROM MeterReadings mr 
                 JOIN UtilityTypes ut ON mr.utility_id = ut.utility_id 
                 WHERE mr.customer_id = ?), 0)
                - 
                ISNULL((SELECT SUM(p.amount) 
                 FROM Payments p 
                 WHERE p.customer_id = ?), 0)
            ) as balance,
            DATEDIFF(day, (SELECT MIN(reading_date) FROM MeterReadings WHERE customer_id = ?), GETDATE()) as days_overdue
    ";
    $balanceStmt = $pdo->prepare($balanceSql);
    $balanceStmt->execute([$cust['customer_id'], $cust['customer_id'], $cust['customer_id']]);
    $balanceResult = $balanceStmt->fetch();
    $balance = $balanceResult['balance'] ?? 0;
    $days_overdue = $balanceResult['days_overdue'] ?? 0;
}

$readings = [];
if ($cust) {
    $rSql = "SELECT m.*, ut.type_name, ut.unit_rate,
             (m.units_consumed * ut.unit_rate) as total_cost
             FROM MeterReadings m 
             JOIN UtilityTypes ut ON m.utility_id = ut.utility_id 
             WHERE m.customer_id = ? 
             ORDER BY reading_date DESC";
    $stmt2 = $pdo->prepare($rSql);
    $stmt2->execute([$cust['customer_id']]);
    $readings = $stmt2->fetchAll();
}
?>

<html>
<head>
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        
        <h1>Welcome, <?php echo htmlspecialchars($cust['full_name'] ?? 'User'); ?></h1>
        
        


        <div class="card" style="text-align:left; margin-bottom:30px;">
            <h3>Account Information</h3>
            <p><strong>Account #:</strong> <?php echo htmlspecialchars($cust['account_number'] ?? 'N/A'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($cust['address'] ?? 'N/A'); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($cust['phone_number'] ?? 'N/A'); ?></p>
            <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                <strong>Balance Status:</strong> 
                <?php 
                if ($balance > 0) {
                    echo "<span style='color:#ff6b6b; font-weight:bold;'>Outstanding: Rs. " . number_format($balance, 2) . "</span>";
                    if ($days_overdue > 0) {
                        echo "<br><small style='color:#999;'>(" . $days_overdue . " days overdue)</small>";
                    }
                } elseif ($balance < 0) {
                    echo "<span style='color:#51cf66; font-weight:bold;'>Credit: Rs. " . number_format(abs($balance), 2) . "</span>";
                } else {
                    echo "<span style='color:#74c0fc; font-weight:bold;'>Settled (Rs. 0.00)</span>";
                }
                ?>
            </p>
        </div>
        <div class="info-box">
            <p><strong>Interest Rates:</strong> <?php echo $rates['rate_14_days']; ?>% (14 days) | <?php echo $rates['rate_30_days']; ?>% (30 days)</p>
        </div>

        <h3>Reading History</h3>
        <?php if($readings): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="background: #3a3a3c; border-bottom: 2px solid var(--border);">
                        <th style="padding: 15px; text-align: left; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Date</th>
                        <th style="padding: 15px; text-align: left; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Utility</th>
                        <th style="padding: 15px; text-align: right; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Units Used</th>
                        <th style="padding: 15px; text-align: right; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Unit Price</th>
                        <th style="padding: 15px; text-align: right; text-transform: uppercase; font-weight: 600; letter-spacing: 1px;">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($readings as $r): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 12px;"><?php echo htmlspecialchars($r['reading_date']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($r['type_name']); ?></td>
                        <td style="padding: 12px; text-align: right;"><?php echo htmlspecialchars($r['units_consumed']); ?></td>
                        <td style="padding: 12px; text-align: right;">Rs. <?php echo number_format($r['unit_rate'], 2); ?></td>
                        <td style="padding: 12px; text-align: right; font-weight: bold;">Rs. <?php echo number_format($r['total_cost'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert" style="margin-top: 20px; padding: 20px; text-align: center; border: 1px solid var(--border); border-radius: 8px;">
                <p>No readings found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>