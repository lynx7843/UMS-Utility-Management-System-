<?php
session_start();
require_once 'db.php';


if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'manager')) { 
    header("Location: login.php"); 
    exit; 
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_complaint'])) {
    $complaint_id = $_POST['complaint_id'] ?? 0;
    $status = $_POST['status'] ?? '';
    $response = trim($_POST['response'] ?? '');
    
    $updateSql = "UPDATE Complaints SET status = ?, response = ? WHERE complaint_id = ?";
    $pdo->prepare($updateSql)->execute([$status, $response, $complaint_id]);
    header("Location: complaint_show.php");
    exit;
}


$sql = "SELECT c.*, u.full_name 
        FROM Complaints c
        JOIN Customers cu ON c.customer_id = cu.customer_id
        JOIN Users u ON cu.user_id = u.user_id
        ORDER BY c.created_at DESC";
$complaints = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Complaints</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending { background: #ffd43b; color: #1c1c1e; }
        .status-resolved { background: #51cf66; color: #1c1c1e; }
        .status-in-progress { background: #74c0fc; color: #1c1c1e; }
        
        .complaint-card {
            background: rgba(28, 28, 30, 0.6);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        
        <h1>All Complaints</h1>

        <?php if($complaints): ?>
            <?php foreach($complaints as $c): ?>
                <div class="complaint-card">
                    <div class="complaint-header">
                        <div>
                            <h3><?php echo htmlspecialchars($c['subject']); ?></h3>
                            <p style="color: #999; margin: 5px 0;">
                                Customer: <?php echo htmlspecialchars($c['full_name']); ?><br>
                                Date: <?php echo htmlspecialchars($c['created_at']); ?>
                            </p>
                        </div>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $c['status'])); ?>">
                            <?php echo htmlspecialchars($c['status']); ?>
                        </span>
                    </div>
                    
                    <p style="color: #ccc; margin: 15px 0;">
                        <?php echo nl2br(htmlspecialchars($c['description'])); ?>
                    </p>
                    
                    <?php if ($c['response']): ?>
                        <div style="margin-top: 15px; padding: 15px; background: rgba(116, 192, 252, 0.1); border-left: 3px solid #74c0fc; border-radius: 4px;">
                            <strong style="color: #74c0fc;">Response:</strong>
                            <p style="margin: 10px 0; color: #ccc;"><?php echo nl2br(htmlspecialchars($c['response'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                        <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">
                        
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="Pending" style="background-color: #2a2a2c; color: #ffffff;" <?php echo $c['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="In Progress" style="background-color: #2a2a2c; color: #ffffff;" <?php echo $c['status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                <option value="Resolved" style="background-color: #2a2a2c; color: #ffffff;" <?php echo $c['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Response</label>
                            <textarea name="response" rows="3" placeholder="Enter your response"><?php echo htmlspecialchars($c['response'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_complaint" class="btn">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #999;">No complaints found.</p>
        <?php endif; ?>
    </div>
</body>
</html>