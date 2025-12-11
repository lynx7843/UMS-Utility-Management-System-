<?php
session_start();
require_once 'db.php';
if ($_SESSION['role'] != 'manager') { header("Location: login.php"); exit; }

$staffSql = "SELECT full_name, role, username FROM Users WHERE role != 'customer' ORDER BY role";
$staff = $pdo->query($staffSql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Staff List</title><link rel="stylesheet" href="style.css"></head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Staff Directory</h2>
        <div class="grid" style="grid-template-columns: 1fr;">
            <table boarder="1" style="width:100%; border-collapse:collapse; color:white; border-color:white;">
                <tr style="background:#333;">
                    <th style="padding:10px;">Job Title</th>
                    <th style="padding:10px;">Name</th>
                    <th style="padding:10px;">Username</th>
                </tr>
                <?php foreach($staff as $s): ?>
                <tr>
                    <td style="padding:10px; text-transform:uppercase;"><?php echo str_replace('_', ' ', $s['role']); ?></td>
                    <td style="padding:10px;"><?php echo $s['full_name']; ?></td>
                    <td style="padding:10px;"><?php echo $s['username']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>