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

$readings = [];
if ($cust) {
    $rSql = "SELECT m.*, ut.type_name 
             FROM MeterReadings m 
             JOIN UtilityTypes ut ON m.utility_id = ut.utility_id 
             WHERE m.customer_id = ? 
             ORDER BY reading_date DESC";
    $stmt2 = $pdo->prepare($rSql);
    $stmt2->execute([$cust['customer_id']]);
    $readings = $stmt2->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        
        <h2>Welcome, <?php echo htmlspecialchars($cust['full_name'] ?? 'User'); ?></h2>
        
        <div class="card" style="text-align:left; margin-bottom:20px;">
            <p><strong>Account #:</strong> <?php echo htmlspecialchars($cust['account_number'] ?? 'N/A'); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($cust['address'] ?? 'N/A'); ?></p>
        </div>

        <h3>Reading History</h3>
        <?php if($readings): ?>
            <div style="border:1px solid white; padding:10px;">
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; font-weight:bold; border-bottom:1px solid white; padding-bottom:5px;">
                    <div>Date</div>
                    <div>Utility</div>
                    <div>Units Used</div>
                </div>
                <?php foreach($readings as $r): ?>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; padding-top:5px; border-bottom:1px solid #333;">
                    <div><?php echo htmlspecialchars($r['reading_date']); ?></div>
                    <div><?php echo htmlspecialchars($r['type_name']); ?></div>
                    <div><?php echo htmlspecialchars($r['units_consumed']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No readings found.</p>
        <?php endif; ?>
    </div>
</body>
</html>