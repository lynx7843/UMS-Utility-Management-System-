<?php
require_once 'db.php';

if (isset($_GET['account_number']) && isset($_GET['utility_id'])) {
    $account_number = $_GET['account_number'];
    $utility_id = $_GET['utility_id'];

    
    $stmt = $pdo->prepare("SELECT customer_id FROM Customers WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $customer = $stmt->fetch();

    if ($customer) {
        $customer_id = $customer['customer_id'];

      
        $stmt = $pdo->prepare(
            "SELECT TOP 1 current_reading 
             FROM MeterReadings 
             WHERE customer_id = ? AND utility_id = ? 
             ORDER BY reading_date DESC"
        );
        $stmt->execute([$customer_id, $utility_id]);
        $last_reading = $stmt->fetch();

        if ($last_reading) {
            echo json_encode(['previous_reading' => $last_reading['current_reading']]);
        } else {
     
            echo json_encode(['previous_reading' => 0]);
        }
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
} else {
    echo json_encode(['error' => 'Missing parameters']);
}
?>