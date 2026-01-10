<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager') {
    header("Location: login.php");
    exit;
}


if (isset($_POST['update_rates'])) {
    $sql = "UPDATE InterestRates SET rate_14_days = ?, rate_30_days = ? WHERE id = 1";
    $pdo->prepare($sql)->execute([$_POST['rate_14'], $_POST['rate_30']]);
    $msg = "Rates updated!";
}


$rates = $pdo->query("SELECT * FROM InterestRates WHERE id = 1")->fetch();
if (!$rates) {
    $pdo->exec("INSERT INTO InterestRates (id, rate_14_days, rate_30_days) VALUES (1, 1.0, 1.5)");
    $rates = ['rate_14_days' => 1.0, 'rate_30_days' => 1.5];
}


$sql = "
    SELECT 
        c.customer_id,
        u.full_name, 
        c.account_number,
        (
            ISNULL((SELECT SUM(mr.units_consumed * ut.unit_rate) FROM MeterReadings mr 
                    JOIN UtilityTypes ut ON mr.utility_id = ut.utility_id 
                    WHERE mr.customer_id = c.customer_id), 0)
            + ISNULL((SELECT SUM(amount) FROM InterestCharges WHERE customer_id = c.customer_id), 0)
            - ISNULL((SELECT SUM(amount) FROM Payments WHERE customer_id = c.customer_id), 0)
        ) as balance,
        DATEDIFF(day, (SELECT MIN(reading_date) FROM MeterReadings WHERE customer_id = c.customer_id), GETDATE()) as days_overdue
    FROM Customers c
    JOIN Users u ON c.user_id = u.user_id
";
$customers = $pdo->query($sql)->fetchAll();


$applied_count = 0;
foreach ($customers as $c) {
    if ($c['balance'] <= 0) continue;
    
    $days = $c['days_overdue'];
    $rate = 0;
    if ($days >= 30) $rate = $rates['rate_30_days'];
    elseif ($days >= 14) $rate = $rates['rate_14_days'];
    
    if ($rate > 0) {
        
        $checkSql = "SELECT COUNT(*) as cnt FROM InterestCharges 
                     WHERE customer_id = ? AND CAST(applied_date AS DATE) = CAST(GETDATE() AS DATE)";
        $check = $pdo->prepare($checkSql);
        $check->execute([$c['customer_id']]);
        $exists = $check->fetch()['cnt'];
        
        if (!$exists) {
            $interest = ($c['balance'] * $rate) / 100;
            $sql = "INSERT INTO InterestCharges (customer_id, amount, applied_date) VALUES (?, ?, GETDATE())";
            $pdo->prepare($sql)->execute([$c['customer_id'], $interest]);
            $applied_count++;
        }
    }
}

if ($applied_count > 0) {
    $msg = "Interest automatically applied to $applied_count customers today!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Interest Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        
        <h2>Interest Management</h2>

        <?php if (isset($msg)): ?>
            <div class="alert success"><?php echo $msg; ?></div>
        <?php endif; ?>

        
        <div style="background:#1a1a1a; padding:20px; margin-bottom:20px; border-radius:8px;">
            <h3>Interest Rates</h3>
            <form method="POST">
                <label>14 Days Rate (%): <input type="number" step="0.1" name="rate_14" value="<?php echo $rates['rate_14_days']; ?>" required></label>
                <label>30 Days Rate (%): <input type="number" step="0.1" name="rate_30" value="<?php echo $rates['rate_30_days']; ?>" required></label>
                <button type="submit" name="update_rates" class="btn">Update</button>
            </form>
        </div>

       
        <table>
            <tr>
                <th>Account</th>
                <th>Customer</th>
                <th>Balance</th>
                <th>Days Overdue</th>
                <th>Interest Rate</th>
                <th>Interest</th>
                <th>Total</th>
                <th>Action</th>
            </tr>
            <?php foreach($customers as $c): 
                if ($c['balance'] <= 0) continue;
                
                $days = $c['days_overdue'];
                $rate = 0;
                if ($days >= 30) $rate = $rates['rate_30_days'];
                elseif ($days >= 14) $rate = $rates['rate_14_days'];
                
                $interest = ($c['balance'] * $rate) / 100;
                $total = $c['balance'] + $interest;
            ?>
            <tr>
                <td><?php echo $c['account_number']; ?></td>
                <td><?php echo htmlspecialchars($c['full_name']); ?></td>
                <td><?php echo number_format($c['balance'], 2); ?></td>
                <td><?php echo $days; ?> days</td>
                <td><?php echo $rate; ?>%</td>
                <td style="color:#ff9999;"><?php echo number_format($interest, 2); ?></td>
                <td><strong><?php echo number_format($total, 2); ?></strong></td>
                <td>
                    <span style="color:#999;">Auto Applied</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>