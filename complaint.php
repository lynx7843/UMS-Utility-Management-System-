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


$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_complaint'])) {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($subject) && !empty($description)) {
        $insertSql = "INSERT INTO Complaints (customer_id, subject, description, status, created_at) 
                      VALUES (?, ?, ?, 'Pending', GETDATE())";
        $insertStmt = $pdo->prepare($insertSql);
        if ($insertStmt->execute([$cust['customer_id'], $subject, $description])) {
            $success_message = "Complaint submitted successfully!";
        } else {
            $error_message = "Failed to submit complaint. Please try again.";
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}


$complaints = [];
if ($cust) {
    $cSql = "SELECT * FROM Complaints 
             WHERE customer_id = ? 
             ORDER BY created_at DESC";
    $cStmt = $pdo->prepare($cSql);
    $cStmt->execute([$cust['customer_id']]);
    $complaints = $cStmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Complaints</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background: #ffd43b;
            color: #1c1c1e;
        }
        .status-resolved {
            background: #51cf66;
            color: #1c1c1e;
        }
        .status-in-progress {
            background: #74c0fc;
            color: #1c1c1e;
        }
        .complaint-card {
            background: rgba(28, 28, 30, 0.6);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .complaint-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .complaint-subject {
            font-weight: 600;
            font-size: 1.1rem;
        }
        .complaint-date {
            color: #999;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        
        <h1>My Complaints</h1>

    
        <h3>Submit a New Complaint</h3>
        
        <?php if ($success_message): ?>
            <div class="alert success" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert error" style="margin-bottom: 20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" style="margin-bottom: 40px; max-width: 600px;">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Brief description of the issue">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required placeholder="Provide detailed information about your complaint"></textarea>
            </div>
            
            <button type="submit" name="submit_complaint" class="btn">Submit Complaint</button>
        </form>

   
        <h3>Complaint History</h3>
        <?php if($complaints): ?>
            <div style="margin-top: 20px;">
                <?php foreach($complaints as $complaint): ?>
                    <div class="complaint-card">
                        <div class="complaint-header">
                            <div>
                                <div class="complaint-subject"><?php echo htmlspecialchars($complaint['subject']); ?></div>
                                <div class="complaint-date">Submitted: <?php echo htmlspecialchars($complaint['created_at']); ?></div>
                            </div>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $complaint['status'])); ?>">
                                <?php echo htmlspecialchars($complaint['status']); ?>
                            </span>
                        </div>
                        <p style="margin: 10px 0; color: #ccc;"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></p>
                        <?php if (!empty($complaint['response'])): ?>
                            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border);">
                                <strong style="color: #74c0fc;">Response:</strong>
                                <p style="margin: 5px 0; color: #ccc;"><?php echo nl2br(htmlspecialchars($complaint['response'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert" style="margin-top: 20px; padding: 20px; text-align: center; border: 1px solid var(--border); border-radius: 8px;">
                <p>No complaints submitted yet.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>