<?php
session_start();
require_once 'db.php';
if ($_SESSION['role'] != 'manager') { header("Location: login.php"); exit; }

$custSql = "
    SELECT 
        u.full_name, 
        c.account_number, 
        c.phone_number,
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
";
$customers = $pdo->query($custSql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Customer Report</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Customer Financial Report</h2>
        <div class="grid" style="grid-template-columns: 1fr;">
            <table boarder="1" style="width:100%; border-collapse:collapse; color:white; border-color:white;">
                <tr style="background:#333;">
                    <th style="padding:10px;">Account #</th>
                    <th style="padding:10px;">Customer Name</th>
                    <th style="padding:10px;">Phone</th>
                    <th style="padding:10px;">Balance Status</th>
                </tr>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td style="padding:10px;"><?php echo $c['account_number']; ?></td>
                    <td style="padding:10px;"><?php echo $c['full_name']; ?></td>
                    <td style="padding:10px;"><?php echo $c['phone_number'] ?? '-'; ?></td>
                    <td style="padding:10px;">
                        <?php 
                        if ($c['balance'] > 0) {
                            echo "OUTSTANDING: " . number_format($c['balance'], 2);
                        } elseif ($c['balance'] < 0) {
                            echo "EXCEEDING (Credit): " . number_format(abs($c['balance']), 2);
                        } else {
                            echo "Settled (0.00)";
                        }
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>