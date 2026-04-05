<?php
/**
 * Admin - Orders Management
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Update order status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = $_GET['status'];
    $allowed_statuses = ['pending', 'confirmed', 'ready_to_pickup', 'delivering', 'delivered', 'cancelled'];
    
    if (in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            $message = "Order status updated!";
        }
        $stmt->close();
    }
}

// Delete order
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Order deleted!";
    }
    $stmt->close();
}

// Get all orders - force fresh data with timestamp
$timestamp = time();

// Pagination settings
$per_page = 30;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Get total count
$count_result = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Get orders for current page
$result = $conn->query("SELECT o.id, o.total_price, o.status, o.order_date, o.delivery_address, o.deliverer_id,
    f.name as food_name, u.username, u.full_name, d.username as deliverer_name
    FROM orders o 
    JOIN food_items f ON o.food_item_id = f.id 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN users d ON o.deliverer_id = d.id
    ORDER BY o.order_date DESC
    LIMIT $per_page OFFSET $offset");
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="../theme.css">
    <link rel="stylesheet" href="../responsive.css">
    <script>
        // Force page reload when using back button to ensure fresh data
        if (performance.navigation.type === 2) {
            location.reload(true);
        }
    </script>
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
            background: #d4edda;
            color: #155724;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
        }
        .status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-ready_to_pickup { background: #9b59b6; color: white; }
        .status-delivering { background: #f39c12; color: white; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .actions a {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8em;
            margin-right: 3px;
        }
        .delete-btn { background: #e74c3c; color: white; }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 15px;
        }
        .pagination-link {
            padding: 8px 12px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .pagination-link:hover {
            background: #667eea;
            color: white;
        }
        .pagination-current {
            padding: 8px 12px;
            background: #667eea;
            color: white;
            border-radius: 5px;
            font-weight: 600;
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
            <a href="orders.php" class="active">📦 Orders</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="contact_messages.php">📧 Contact Messages</a>
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
            
            <h2 style="margin-bottom: 20px; color: #2c3e50; display: flex; justify-content: space-between; align-items: center;">
                📦 Orders List (<?php echo $total_orders; ?> total, page <?php echo $page; ?> of <?php echo $total_pages; ?>)
                <a href="orders.php?page=<?php echo $page; ?>&_t=<?php echo time(); ?>" class="btn-refresh" style="background: #667eea; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none; font-size: 0.85em;">🔄 Refresh</a>
            </h2>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Food</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Deliverer ID</th>
                        <th>Deliverer Name</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                        </td>
                        <td><?php echo $order['deliverer_id'] ? '#' . $order['deliverer_id'] : '-'; ?></td>
                        <td><?php echo $order['deliverer_name'] ? htmlspecialchars($order['deliverer_name']) : '-'; ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                        <td class="actions">
                            <select class="status-select" onchange="location.href='?id=<?php echo $order['id']; ?>&status='+this.value">
                                <option value="">Change Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="ready_to_pickup">Ready to Pickup</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <a href="?delete=<?php echo $order['id']; ?>&page=<?php echo $page; ?>" class="delete-btn" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-link">← Previous</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="pagination-current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-link">Next →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../theme.js"></script>
</body>
</html>
