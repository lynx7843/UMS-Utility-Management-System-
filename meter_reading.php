<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] != 'field_officer') { header("Location: login.php"); exit; }

$msg = "";
$utils = $pdo->query("SELECT * FROM UtilityTypes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acc = $_POST['account_number'];
    $uid = $_POST['utility_id'];
    $prev = $_POST['previous'];
    $curr = $_POST['current'];
    $date = $_POST['date'];

    $stmt = $pdo->prepare("SELECT customer_id FROM Customers WHERE account_number = ?");
    $stmt->execute([$acc]);
    $cust = $stmt->fetch();

    if ($cust) {
        try {
            $sql = "INSERT INTO MeterReadings (customer_id, utility_id, previous_reading, current_reading, reading_date) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$cust['customer_id'], $uid, $prev, $curr, $date]);
            $msg = "<div class='alert success'>Reading Saved.</div>";
        } catch (Exception $e) {
            $msg = "<div class='alert error'>DB Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='alert error'>Invalid Account Number.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Readings</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Add Meter Reading</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <div class="form-group"><label>Account #</label><input type="text" name="account_number" required></div>
            <div class="form-group">
                <label>Utility Type</label>
                <select name="utility_id">
                    <?php foreach($utils as $u) echo "<option value='{$u['utility_id']}'>{$u['type_name']}</option>"; ?>
                </select>
            </div>
            <div class="form-group"><label>Previous Reading</label><input type="number" step="0.01" name="previous" required></div>
            <div class="form-group"><label>Current Reading</label><input type="number" step="0.01" name="current" required></div>
            <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
            <button class="btn">Submit</button>
        </form>
    </div>
</body>
</html>