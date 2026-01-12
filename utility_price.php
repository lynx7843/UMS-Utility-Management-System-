<?php
session_start();
require_once 'db.php';

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'manager' && strtolower($role) !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rate'])) {
    $utility_id = $_POST['utility_id'];
    $new_rate = trim($_POST['unit_rate']);

    if (empty($new_rate) || !is_numeric($new_rate) || $new_rate < 0) {
        $error = "Please enter a valid rate (must be a positive number).";
    } else {
        try {
            $updateSql = "UPDATE UtilityTypes SET unit_rate = :unit_rate WHERE utility_id = :utility_id";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute(['unit_rate' => $new_rate, 'utility_id' => $utility_id]);
            $success = "Utility rate updated successfully!";
        } catch (Exception $e) {
            $error = "Error updating rate: " . $e->getMessage();
        }
    }
}

$sql = "SELECT utility_id, type_name, unit_rate FROM UtilityTypes ORDER BY utility_id";
$utilities = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Utility Pricing</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <?php include 'nav.php'; ?>
        <h2>Utility Pricing Management</h2>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div style="margin-top:30px; color:white; padding:10px;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <tr style="background:#333;color: solid white;">
                    <th style="padding:15px; text-align:left;">Utility ID</th>
                    <th style="padding:15px; text-align:left;">Utility Type</th>
                    <th style="padding:15px; text-align:right;">Unit Rate (LKR)</th>
                    <th style="padding:15px; text-align:center;">Action</th>
                </tr>
                <?php foreach($utilities as $utility): ?>
                <tr >
                    <td style="padding:15px;"><?php echo htmlspecialchars($utility['utility_id']); ?></td>
                    <td style="padding:15px; font-weight:bold; text-transform:capitalize;">
                        <?php echo htmlspecialchars($utility['type_name']); ?>
                    </td>
                    <td style="padding:15px; text-align:right; font-size:1.1rem;">
                        <?php echo number_format($utility['unit_rate'], 2); ?>
                    </td>
                    <td style="padding:15px; text-align:center;">
                        <button onclick="showEditForm(<?php echo $utility['utility_id']; ?>, '<?php echo htmlspecialchars($utility['type_name']); ?>', <?php echo $utility['unit_rate']; ?>)" 
                                class="btn" style="padding:8px 15px; font-size:0.9rem;">
                            Edit Rate
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

               <div id="editModal" style="display:none; margin-top:30px; border:1px solid white; padding:20px;">
            <h3 style="margin-top:0;">Update Utility Rate</h3>
            <form method="POST">
                <input type="hidden" name="utility_id" id="edit_utility_id">
                
                <div style="margin-bottom:20px;">
                    <label>Utility Type</label>
                    <input type="text" id="edit_type_name" disabled style="background-color:#555; cursor:not-allowed;">
                </div>

                <div style="margin-bottom:20px;">
                    <label for="unit_rate">Unit Rate (LKR) *</label>
                    <input type="number" step="0.01" min="0" name="unit_rate" id="edit_unit_rate" required>
                </div>

                <div style="display:flex; gap:10px;">
                    <button type="submit" name="update_rate" class="btn">Save Changes</button>
                    <button type="button" onclick="hideEditForm()" class="btn" style="background-color:#666;">Cancel</button>
                </div>
            </form>
        </div>

      
        <div style="margin-top:30px; padding:15px; border:1px solid #666; background:#1a1a1a;">
            <h4 style="margin-top:0; color:#4CAF50;">About Utility Rates</h4>
            <p style="margin:5px 0;">• <strong>Electricity:</strong> Rate per kilowatt-hour (kWh)</p>
            <p style="margin:5px 0;">• <strong>Water:</strong> Rate per cubic meter (m³)</p>
            <p style="margin:5px 0;">• <strong>Gas:</strong> Rate per unit consumed</p>
            <p style="margin:10px 0 0 0; color:#ff9999; font-size:0.9rem;">
                ⚠️ Note: Changing these rates will affect all future billing calculations.
            </p>
        </div>
    </div>

    <script>
        function showEditForm(utilityId, typeName, currentRate) {
            document.getElementById('edit_utility_id').value = utilityId;
            document.getElementById('edit_type_name').value = typeName;
            document.getElementById('edit_unit_rate').value = currentRate;
            document.getElementById('editModal').style.display = 'block';
            
           
            document.getElementById('editModal').scrollIntoView({ behavior: 'smooth' });
        }

        function hideEditForm() {
            document.getElementById('editModal').style.display = 'none';
        }

      
        <?php if (!empty($success)): ?>
            hideEditForm();
        <?php endif; ?>
    </script>
</body>
</html>