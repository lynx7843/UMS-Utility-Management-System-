<?php
session_start();
require_once 'db.php';

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'manager' && strtolower($role) !== 'admin') {
    header("Location: login.php");
    exit;
}

$custSql = "
    SELECT 
        u.full_name, 
        c.account_number, 
        c.phone_number,
        c.address,
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
    JOIN Users u ON c.user_id = u.user_id
    ORDER BY c.account_number
";
$customers = $pdo->query($custSql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Customer Financial Report</h2>
        
        <div style="margin-bottom:20px; padding:10px; background:#1a1a1a; color: solid #666;">
            <strong>Total Customers :</strong> <?php echo count($customers); ?>
        </div>

        <div class="grid" style="grid-template-columns: 1fr;">
            <table  style="width:100%; border-collapse:collapse; color:white;">
                <tr style="background:#333;">
                    <th style="padding:10px;">Account #</th>
                    <th style="padding:10px;">Customer Name</th>
                    <th style="padding:10px;">Phone</th>
                    <th style="padding:10px;">Address</th>
                    <th style="padding:10px;">Balance Status</th>
                    <th style="padding:10px;">Actions</th>
                </tr>
                <?php foreach($customers as $c): ?>
                <tr >
                    <td style="padding:10px;"><?php echo htmlspecialchars($c['account_number']); ?></td>
                    <td style="padding:10px;"><?php echo htmlspecialchars($c['full_name']); ?></td>
                    <td style="padding:10px;"><?php echo htmlspecialchars($c['phone_number'] ?? '-'); ?></td>
                    <td style="padding:10px; font-size:0.9rem;"><?php echo htmlspecialchars($c['address'] ?? '-'); ?></td>
                    <td style="padding:10px; font-weight:bold;">
                        <?php 
                        if ($c['balance'] > 0) {
                            echo "<span style='color:#ff9999'>OUTSTANDING: " . number_format($c['balance'], 2) . "</span>";
                        } elseif ($c['balance'] < 0) {
                            echo "<span style='color:#99ff99'>EXCEEDING: " . number_format(abs($c['balance']), 2) . "</span>";
                        } else {
                            echo "<span style='color:#9999ff'>Settled (0.00)</span>";
                        }
                        ?>
                    </td>
                    <td >
                        <a href="edit_customer.php?account_number=<?php echo htmlspecialchars($c['account_number']); ?>" class="btn" style="text-decoration:none; display:inline-block; text-align:center;color:white;padding:5px 10px; border-radius:5px;" >Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>