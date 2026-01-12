<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$role = $_SESSION['role'] ?? 'Guest';
?>
<nav>
    <div style="font-weight:bold; font-size: 15px;">UTILITY SYSTEM [<?php echo strtoupper($role); ?>]</div>
    <ul>
        <?php if ($role === 'manager'): ?>
            <li><a href="manager.php">Dashboard</a></li>
            <li><a href="staff.php">Staff</a></li>
            <li><a href="cust_list.php">Customer</a></li>
            <li><a href="complaint_show.php">Complaints</a></li>
            <li><a href="admin.php">Create Users</a></li>
            <li><a href="utility_price.php">Utility Price</a></li>
            <li><a href="interest.php">Late Fee</a></li>
        <?php elseif ($role === 'admin'): ?>
            <li><a href="admin.php">Register User</a></li>
            <li><a href="cust_list.php">Customer</a></li>
            <li><a href="complaint_show.php">Complaints</a></li>
            <li><a href="staff.php">Staff</a></li>
            <li><a href="utility_price.php">Utility Pricing</a></li>
            <li><a href="interest.php">Late Fee</a></li>
        <?php elseif ($role === 'field_officer'): ?>
            <li><a href="meter_reading.php">Enter Reading</a></li>
        <?php elseif ($role === 'cashier'): ?>
            <li><a href="cashier.php">Record Payment</a></li>
            <li><a href="find_cust.php">Find Customer</a></li>
        <?php elseif ($role === 'customer'): ?>
            <li><a href="customer_dashboard.php">My Bills</a></li>
            <li><a href="complaint.php">Complaint</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>