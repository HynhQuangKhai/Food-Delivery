<?php
/**
 * Admin - User Detail View
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    header("Location: users.php");
    exit();
}

// Get user details
$stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address, role, created_at, avatar FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: users.php");
    exit();
}

// Delete user order
if (isset($_GET['delete_order'])) {
    $order_id = intval($_GET['delete_order']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    if ($stmt->execute()) {
        $message = "Order #$order_id deleted successfully!";
    }
    $stmt->close();
}

// Get delivery stats if user is deliverer
$delivery_stats = null;
$delivery_history = [];
$avg_delivery_time = 0;
if ($user['role'] === 'deliverer') {
    // Get stats
    $stats_stmt = $conn->prepare("SELECT 
        COUNT(*) as total_deliveries,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'delivering' THEN 1 ELSE 0 END) as active,
        SUM(total_price) as total_earnings,
        AVG(TIMESTAMPDIFF(MINUTE, order_date, delivered_at)) as avg_delivery_time
        FROM orders WHERE deliverer_id = ? AND status = 'delivered'");
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $delivery_stats = $stats_stmt->get_result()->fetch_assoc();
    $avg_delivery_time = round($delivery_stats['avg_delivery_time'] ?? 0);
    $stats_stmt->close();
    
    // Get delivery history with earnings
    $history_stmt = $conn->prepare("SELECT o.*, f.name as food_name, f.image_url, u.full_name as customer_name
        FROM orders o 
        JOIN food_items f ON o.food_item_id = f.id 
        JOIN users u ON o.user_id = u.id
        WHERE o.deliverer_id = ? AND o.status = 'delivered'
        ORDER BY o.delivered_at DESC LIMIT 10");
    $history_stmt->bind_param("i", $user_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    while ($row = $history_result->fetch_assoc()) {
        $delivery_history[] = $row;
    }
    $history_stmt->close();
}

// Get user's orders if role is user
$user_orders = [];
$user_stats = null;
if ($user['role'] === 'user') {
    // Get order stats
    $stats_stmt = $conn->prepare("SELECT 
        COUNT(*) as total_orders,
        SUM(total_price) as total_spent,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders
        FROM orders WHERE user_id = ?");
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $user_stats = $stats_stmt->get_result()->fetch_assoc();
    $stats_stmt->close();
    
    $orders_stmt = $conn->prepare("SELECT o.*, f.name as food_name, f.image_url
        FROM orders o 
        JOIN food_items f ON o.food_item_id = f.id 
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC LIMIT 10");
    $orders_stmt->bind_param("i", $user_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    while ($row = $orders_result->fetch_assoc()) {
        $user_orders[] = $row;
    }
    $orders_stmt->close();
}

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Detail - Admin</title>
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
        .back-btn {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        .user-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2em;
            font-weight: bold;
        }
        .user-title h2 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .role-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            display: inline-block;
        }
        .role-admin { background: #667eea; color: white; }
        .role-user { background: #95a5a6; color: white; }
        .role-deliverer { background: #f39c12; color: white; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .info-item label {
            color: #666;
            font-size: 0.85em;
            display: block;
            margin-bottom: 5px;
        }
        .info-item value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1em;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2em;
            margin-bottom: 5px;
        }
        .stat-card p {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
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
        .status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85em;
        }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
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
            <a href="users.php" class="active">👥 Users</a>
            <a href="orders.php">📦 Orders</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="contact_messages.php">📧 Contact Messages</a>
        </div>
        
        <div class="main-content">
            <a href="users.php" class="back-btn">← Back to Users</a>
            
            <div class="user-card">
                <div class="user-header">
                    <?php 
                    $avatar_path = !empty($user['avatar']) ? $user['avatar'] : '../images/default-avatar.png';
                    if (!empty($user['avatar']) && substr($avatar_path, 0, 4) !== 'http' && substr($avatar_path, 0, 3) !== '../') {
                        $avatar_path = '../' . $avatar_path;
                    }
                    ?>
                    <div class="user-avatar" style="background: none; overflow: hidden;">
                        <img src="<?php echo $avatar_path; ?>" alt="<?php echo htmlspecialchars($user['full_name']); ?>" 
                             style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;"
                             onerror="this.src='../images/default-avatar.png'">
                    </div>
                    <div class="user-title">
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>User ID</label>
                        <value>#<?php echo $user['id']; ?></value>
                    </div>
                    <div class="info-item">
                        <label>Username</label>
                        <value><?php echo htmlspecialchars($user['username']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <value><?php echo htmlspecialchars($user['email']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <value><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Created At</label>
                        <value><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Address</label>
                        <value><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></value>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'deliverer' && $delivery_stats): ?>
                <h3 class="section-title">📊 Delivery Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $delivery_stats['total_deliveries'] ?? 0; ?></h3>
                        <p>Total Deliveries</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $delivery_stats['completed'] ?? 0; ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $delivery_stats['active'] ?? 0; ?></h3>
                        <p>Active</p>
                    </div>
                    <div class="stat-card">
                        <h3>$<?php echo number_format(($delivery_stats['total_earnings'] ?? 0) * 0.1, 2); ?></h3>
                        <p>Total Earnings (10%)</p>
                    </div>
                </div>
                
                <div class="info-grid" style="margin-top: 20px;">
                    <div class="info-item">
                        <label>Average Delivery Time</label>
                        <value><?php echo $avg_delivery_time; ?> minutes</value>
                    </div>
                    <div class="info-item">
                        <label>Earnings Per Order</label>
                        <value>$<?php echo $delivery_stats['completed'] > 0 ? number_format(($delivery_stats['total_earnings'] * 0.1) / $delivery_stats['completed'], 2) : '0.00'; ?></value>
                    </div>
                </div>
                
                <?php if (!empty($delivery_history)): ?>
                <h3 class="section-title" style="margin-top: 30px;">📜 Recent Delivery History</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Food</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Earned</th>
                            <th>Delivered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($delivery_history as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td style="color: #28a745; font-weight: 600;">$<?php echo number_format($order['total_price'] * 0.1, 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['delivered_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'user' && $user_stats): ?>
                <h3 class="section-title">📊 Order Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo $user_stats['total_orders'] ?? 0; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3>$<?php echo number_format($user_stats['total_spent'] ?? 0, 2); ?></h3>
                        <p>Total Spent</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['delivered_orders'] ?? 0; ?></h3>
                        <p>Delivered</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['pending_orders'] ?? 0; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'user' && !empty($user_orders)): ?>
                <h3 class="section-title">📦 Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Food</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                            <td>
                                <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="view-btn" style="background: #667eea; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em; margin-right: 3px;">View</a>
                                <a href="?id=<?php echo $user_id; ?>&delete_order=<?php echo $order['id']; ?>" class="delete-btn" style="background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em;" onclick="return confirm('Delete order #<?php echo $order['id']; ?>?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
