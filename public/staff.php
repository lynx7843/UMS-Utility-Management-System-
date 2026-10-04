<?php
session_start();
require_once __DIR__ . '/../src/config/db.php';
if ($_SESSION['role'] != 'manager' && $_SESSION['role'] != 'admin') { header("Location: index.php"); exit; }

$staffSql = "SELECT user_id, full_name, role, username FROM Users WHERE role != 'customer' ORDER BY role";
$staff = $pdo->query($staffSql)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Staff List</title><link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js"></script></head>
<body>
    <div class="container">
        <?php include __DIR__ . '/../src/partials/nav.php'; ?>
        <h2>Staff Directory</h2>
        <div class="grid" style="grid-template-columns: 1fr;">
            <table boarder="1" style="width:100%; border-collapse:collapse; color:var(--fg); border-color:var(--fg);">
                <tr style="background:var(--head-bg); color: var(--fg);">
                    <th style="padding:10px;">Job Title</th>
                    <th style="padding:10px;">Name</th>
                    <th style="padding:10px;">Username</th>
                    <th style="padding:10px;">Action</th>
                </tr>
                <?php foreach($staff as $s): ?>
                <tr>
                    <td style="padding:10px; text-transform:uppercase;"><?php echo str_replace('_', ' ', $s['role']); ?></td>
                    <td style="padding:10px;"><?php echo $s['full_name']; ?></td>
                    <td style="padding:10px;"><?php echo $s['username']; ?></td>
                    <td style="padding:10px;">
                        <a href="edit_staff.php?id=<?php echo $s['user_id']; ?>" class="btn" style="text-decoration:none; display:inline-block; text-align:center;padding:5px 10px; border-radius: 0;">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>