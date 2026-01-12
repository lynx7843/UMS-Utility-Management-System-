<?php
session_start();
require_once 'db.php';

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'cashier') {
    header("Location: login.php");
    exit;
}

$results = [];
$search = "";
$criteria = "name";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $criteria = $_GET['criteria'];

    $sql = "
        SELECT 
            c.account_number, 
            c.address, 
            c.phone_number, 
            u.full_name,
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
        WHERE ";

    $params = [];
    if ($criteria == 'account') {
        $sql .= "c.account_number = ?";
        $params[] = $search;
    } elseif ($criteria == 'phone') {
        $sql .= "c.phone_number LIKE ?";
        $params[] = "%$search%";
    } elseif ($criteria == 'address') {
        $sql .= "c.address LIKE ?";
        $params[] = "%$search%";
    } else {
        $sql .= "u.full_name LIKE ?";
        $params[] = "%$search%";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head><title>Find Customer</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Find Customer Details</h2>
        
        <form method="GET" style="margin-bottom:30px;color: solid white; padding:20px;">
            <div style="display:flex; gap:10px; align-items:flex-end;">
                <div style="flex:1;">
                    <label>Search By</label>
                    <select name="criteria" style="height:45px;">
                        <option value="name" style="background-color: #2a2a2c; color: #ffffff;"<?php if($criteria=='name') echo 'selected'; ?>>Customer Name</option>
                        <option value="address" style="background-color: #2a2a2c; color: #ffffff;" <?php if($criteria=='address') echo 'selected'; ?>>Address</option>
                        <option value="phone" style="background-color: #2a2a2c; color: #ffffff;" <?php if($criteria=='phone') echo 'selected'; ?>>Phone Number</option>
                        <option value="account" style="background-color: #2a2a2c; color: #ffffff;" <?php if($criteria=='account') echo 'selected'; ?>>Account Number</option>
                    </select>
                </div>
                <div style="flex:2;">
                    <label>Enter Search Term</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" required style="height:45px;">
                </div>
                <div style="flex:1;">
                    <button class="btn" style="height:45px; padding:0;">Search</button>
                </div>
            </div>
        </form>

        <?php if(!empty($results)): ?>
            <h3>Search Results</h3>
            <div style=" color: solid white; padding:10px;">
                <div style="display:grid; background:#333; grid-template-columns:1fr 1fr 1fr 1fr 120px; color: solid white; font-weight:bold; padding:5px;">
                    <div>Name</div>
                    <div>Account #</div>
                    <div>Address / Phone</div>
                    <div>Balance Status</div>
                    <div>Action</div>
                </div>
                <?php foreach($results as $row): ?>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr 120px; padding:10px; border-bottom:1px solid #333; align-items:center;">
                    <div><?php echo htmlspecialchars($row['full_name']); ?></div>
                    <div><?php echo htmlspecialchars($row['account_number']); ?></div>
                    <div style="font-size:0.85rem;">
                        <?php echo htmlspecialchars($row['address']); ?><br>
                        <small><?php echo htmlspecialchars($row['phone_number'] ?? ''); ?></small>
                    </div>
                    <div style="font-weight:bold;">
                        <?php 
                        if ($row['balance'] > 0) {
                            echo "<span style='color:#ff9999'>Outstanding: " . number_format($row['balance'], 2) . "</span>";
                        } elseif ($row['balance'] < 0) {
                            echo "<span style='color:#99ff99'>Exceeding: " . number_format(abs($row['balance']), 2) . "</span>";
                        } else {
                            echo "Settled";
                        }
                        ?>
                    </div>
                    <div>
                        <a href="cashier.php?acc=<?php echo $row['account_number']; ?>" class="btn" style="padding:5px 10px; font-size:0.8rem; text-decoration:none; display:inline-block;">
                            PAY
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php elseif(isset($_GET['search'])): ?>
            <div class="alert error">No customers found matching that criteria.</div>
        <?php endif; ?>
    </div>
</body>
</html>