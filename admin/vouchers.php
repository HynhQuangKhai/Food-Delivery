<?php
/**
 * Admin - Vouchers Management
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';
$error = '';

// Delete voucher
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vouchers WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Voucher deleted successfully!";
    } else {
        $error = "Error deleting voucher.";
    }
    $stmt->close();
}

// Add/Edit voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount_percent = floatval($_POST['discount_percent'] ?? 0);
    $expiry_date = $_POST['expiry_date'] ?? '';
    $status = $_POST['status'] ?? 'active';
    
    // Convert datetime-local format to MySQL datetime format
    if (!empty($expiry_date)) {
        $expiry_date = str_replace('T', ' ', $expiry_date) . ':00';
    }
    
    if (empty($code) || $discount_percent <= 0 || empty($expiry_date)) {
        $error = "Please fill in all required fields.";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE vouchers SET code = ?, discount_percent = ?, expiry_date = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sdssi", $code, $discount_percent, $expiry_date, $status, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO vouchers (code, discount_percent, expiry_date, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $code, $discount_percent, $expiry_date, $status);
        }
        
        if ($stmt->execute()) {
            $message = $id > 0 ? "Voucher updated!" : "Voucher added!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get all vouchers
$result = $conn->query("SELECT * FROM vouchers ORDER BY id DESC");
$vouchers = [];
while ($row = $result->fetch_assoc()) {
    $vouchers[] = $row;
}

closeAdminDBConnection($conn);

// Check if editing
$edit_voucher = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($vouchers as $v) {
        if ($v['id'] == $edit_id) {
            $edit_voucher = $v;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vouchers - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        .admin-header {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
        }
        .admin-container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        .sidebar {
            width: 250px;
            background: #34495e;
            color: white;
            padding: 20px 0;
        }
        .sidebar a {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #2c3e50;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
            margin-left: 10px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        .actions a {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85em;
            margin-right: 5px;
        }
        .edit-btn { background: #3498db; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .status-active { color: #2ecc71; }
        .status-inactive { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🍔 Food Delivery Admin</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
    <div class="admin-container">
        <div class="sidebar">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="food_items.php">🍽️ Food Items</a>
            <a href="users.php">👥 Users</a>
            <a href="orders.php">📦 Orders</a>
            <a href="vouchers.php" class="active">🎫 Vouchers</a>
            <a href="contact_messages.php">📧 Contact Messages</a>
        </div>
        
        <div class="main-content">
            <div style="margin-bottom: 20px; padding: 15px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
                👋 Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?></strong>! 
                <?php if (isset($_SESSION['is_super_admin'])): ?>
                    <span style="background: #f39c12; padding: 3px 10px; border-radius: 12px; font-size: 0.85em; margin-left: 10px;">👑 Super Admin</span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-section">
                <h2><?php echo $edit_voucher ? '✏️ Edit Voucher' : '➕ Add Voucher'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $edit_voucher ? $edit_voucher['id'] : 0; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Code *</label>
                            <input type="text" name="code" required value="<?php echo $edit_voucher ? htmlspecialchars($edit_voucher['code']) : ''; ?>" placeholder="SAVE10">
                        </div>
                        <div class="form-group">
                            <label>Discount % *</label>
                            <input type="number" name="discount_percent" step="0.01" min="1" max="100" required value="<?php echo $edit_voucher ? $edit_voucher['discount_percent'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Expiry Date *</label>
                            <input type="datetime-local" name="expiry_date" required value="<?php echo $edit_voucher ? date('Y-m-d\TH:i', strtotime($edit_voucher['expiry_date'])) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?php echo ($edit_voucher && $edit_voucher['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($edit_voucher && $edit_voucher['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $edit_voucher ? 'Update' : 'Add'; ?> Voucher</button>
                    <?php if ($edit_voucher): ?>
                        <a href="vouchers.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <h2 style="margin-bottom: 20px; color: #2c3e50;">🎫 Vouchers List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Discount %</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $voucher): ?>
                    <tr>
                        <td><?php echo $voucher['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($voucher['code']); ?></strong></td>
                        <td><?php echo $voucher['discount_percent']; ?>%</td>
                        <td><?php echo date('M d, Y H:i', strtotime($voucher['expiry_date'])); ?></td>
                        <td class="status-<?php echo $voucher['status']; ?>">
                            <?php echo ucfirst($voucher['status']); ?>
                        </td>
                        <td class="actions">
                            <a href="?edit=<?php echo $voucher['id']; ?>" class="edit-btn">Edit</a>
                            <a href="?delete=<?php echo $voucher['id']; ?>" class="delete-btn" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
