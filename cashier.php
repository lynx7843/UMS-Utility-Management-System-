<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] != 'cashier') { header("Location: login.php"); exit; }
$msg = "";

$prefillAcc = $_GET['acc'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acc = $_POST['account_number'];
    $amt = $_POST['amount'];
    $method = $_POST['method'];

    $stmt = $pdo->prepare("SELECT customer_id FROM Customers WHERE account_number = ?");
    $stmt->execute([$acc]);
    $cust = $stmt->fetch();

    if ($cust) {
        $sql = "INSERT INTO Payments (customer_id, amount, payment_method) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$cust['customer_id'], $amt, $method]);
        $msg = "<div class='alert success'>Payment Recorded for Account: $acc</div>";
    } else {
        $msg = "<div class='alert error'>Account Not Found.</div>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Cashier</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Record Payment</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <div class="form-group">
                <label>Customer Account #</label>
                <input type="text" name="account_number" value="<?php echo htmlspecialchars($prefillAcc); ?>" required>
            </div>
            <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount" required></div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="method">
                    <option>Cash</option>
                    <option>Card</option>
                </select>
            </div>
            <button class="btn">Process Payment</button>
        </form>
    </div>
</body>
</html>