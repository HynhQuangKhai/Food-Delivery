<?php
/**
 * Admin - Contact Messages Management
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';

// Update message status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    $allowed_statuses = ['unread', 'read', 'replied'];
    
    if (in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $message = "Status updated successfully!";
        }
        $stmt->close();
    }
}

// Delete message
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Message deleted successfully!";
    }
    $stmt->close();
}

// Fetch all messages
$result = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin</title>
    <link rel="stylesheet" href="../theme.css">
    <link rel="stylesheet" href="../responsive.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-header h1 {
            font-size: 1.5em;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
        }
        .admin-container {
            display: flex;
            min-height: calc(100vh - 70px);
        }
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar a {
            display: block;
            padding: 12px 15px;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #667eea;
            color: white;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .message {
            padding: 12px 20px;
            background: #d4edda;
            color: #155724;
            border-radius: 8px;
            margin-bottom: 20px;
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
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-unread { background: #ffe0e0; color: #c53030; }
        .status-read { background: #e0e0ff; color: #3030c5; }
        .status-replied { background: #e0ffe0; color: #30c530; }
        .actions a {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85em;
            margin-right: 5px;
        }
        .view-btn { background: #3498db; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
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
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="contact_messages.php" class="active">📧 Contact Messages</a>
            <a href="reviews.php">⭐ Reviews</a>
        </div>
        
        <div class="main-content">
            <div style="margin-bottom: 20px; padding: 15px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
                👋 Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_username'] ?? $_SESSION['username'] ?? 'Admin'); ?></strong>! 
                <?php if (isset($_SESSION['is_super_admin'])): ?>
                    <span style="background: #f39c12; padding: 3px 10px; border-radius: 12px; font-size: 0.85em; margin-left: 10px;">👑 Super Admin</span>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <h2 style="margin-bottom: 20px; color: #2c3e50;">📧 Contact Messages (<?php echo count($messages); ?>)</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): 
                        $status_class = 'status-' . $msg['status'];
                    ?>
                    <tr>
                        <td><?php echo $msg['id']; ?></td>
                        <td><?php echo htmlspecialchars($msg['username']); ?></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><?php echo htmlspecialchars($msg['subject']); ?></td>
                        <td class="message-preview"><?php echo htmlspecialchars($msg['message']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst($msg['status']); ?></span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></td>
                        <td class="actions">
                            <select onchange="if(this.value) location.href='?id=<?php echo $msg['id']; ?>&status='+this.value" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                                <option value="">Mark as...</option>
                                <option value="unread">Unread</option>
                                <option value="read">Read</option>
                                <option value="replied">Replied</option>
                            </select>
                            <a href="?delete=<?php echo $msg['id']; ?>" class="delete-btn" onclick="return confirm('Delete this message?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: #666;">No messages yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../theme.js"></script>
</body>
</html>
