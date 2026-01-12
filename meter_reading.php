<?php
session_start();
require_once 'db.php';

if (strtolower($_SESSION['role']) != 'field_officer') { header("Location: login.php"); exit; }

$msg = "";
$utils = $pdo->query("SELECT * FROM UtilityTypes")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['account_number'])) {
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


$readingsSql = "
    SELECT 
        mr.reading_id,
        c.account_number,
        u.full_name AS customer_name,
        ut.type_name,
        mr.previous_reading,
        mr.current_reading,
        mr.reading_date
    FROM MeterReadings mr
    JOIN Customers c ON mr.customer_id = c.customer_id
    JOIN Users u ON c.user_id = u.user_id
    JOIN UtilityTypes ut ON mr.utility_id = ut.utility_id
    ORDER BY mr.reading_date DESC";
$readings = $pdo->query($readingsSql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Readings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Add Meter Reading</h2>
        <?php echo $msg; ?>
        <form method="POST">
            <div class="form-group"><label>Account #</label><input type="text" name="account_number" required></div>
            <div class="form-group">
                <label>Utility Type</label>
                <select name="utility_id" style="background-color: #2a2a2c; color: #ffffff;">
                    <?php foreach($utils as $u) echo "<option value='{$u['utility_id']}' style='background-color: #2a2a2c; color: #ffffff;'>{$u['type_name']}</option>"; ?>
                </select>
            </div>
            <div class="form-group"><label>Previous Reading</label><input type="number" step="0.01" name="previous" required></div>
            <div class="form-group"><label>Current Reading</label><input type="number" step="0.01" name="current" required></div>
            <div class="form-group"><label>Date</label><input type="date" name="date" required></div>
            <button type="submit" class="btn">Submit</button>
        </form>

        <div class="grid" style="grid-template-columns: 1fr;">           
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const accountNumberInput = document.querySelector('input[name="account_number"]');
        const utilityIdSelect = document.querySelector('select[name="utility_id"]');
        const previousReadingInput = document.querySelector('input[name="previous"]');

        function fetchPreviousReading() {
            const accountNumber = accountNumberInput.value;
            const utilityId = utilityIdSelect.value;

            if (accountNumber && utilityId) {
                fetch(`get_previous_reading.php?account_number=${accountNumber}&utility_id=${utilityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.previous_reading !== undefined) {
                            previousReadingInput.value = data.previous_reading;
                        } else {
                            previousReadingInput.value = 0; 
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching previous reading:', error);
                        previousReadingInput.value = 0; 
                    });
            }
        }

        accountNumberInput.addEventListener('change', fetchPreviousReading);
        utilityIdSelect.addEventListener('change', fetchPreviousReading);
    });
</script>
</body>
</html>