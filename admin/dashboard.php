<?php
/**
 * Admin Dashboard
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();

// Get stats
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as total FROM users");
$stats['users'] = $result->fetch_assoc()['total'];

// Total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $result->fetch_assoc()['total'];

// Total food items
$result = $conn->query("SELECT COUNT(*) as total FROM food_items");
$stats['food_items'] = $result->fetch_assoc()['total'];

// Total revenue
$result = $conn->query("SELECT COALESCE(SUM(total_price), 0) as total FROM orders");
$stats['revenue'] = $result->fetch_assoc()['total'];

// Total vouchers
$result = $conn->query("SELECT COUNT(*) as total FROM vouchers");
$stats['vouchers'] = $result->fetch_assoc()['total'];

// Recent orders
$recent_orders = $conn->query("SELECT o.id, o.total_price, o.status, o.order_date, f.name as food_name, u.username 
    FROM orders o 
    JOIN food_items f ON o.food_item_id = f.id 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.order_date DESC LIMIT 5");

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Food Delivery</title>
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
        .admin-header h1 { font-size: 1.5em; }
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .stat-card h3 {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #2c3e50;
        }
        .stat-card.users { border-left: 4px solid #3498db; }
        .stat-card.orders { border-left: 4px solid #e74c3c; }
        .stat-card.food { border-left: 4px solid #2ecc71; }
        .stat-card.revenue { border-left: 4px solid #f39c12; }
        .stat-card.vouchers { border-left: 4px solid #9b59b6; }
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .recent-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
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
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🍔 Food Delivery Admin</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
    <div class="admin-container">
        <div class="sidebar">
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="food_items.php">🍽️ Food Items</a>
            <a href="users.php">👥 Users</a>
            <a href="orders.php">📦 Orders</a>
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
            
            <h2 style="margin-bottom: 25px; color: #2c3e50;">Dashboard Overview</h2>
            
            <div class="stats-grid">
                <div class="stat-card users">
                    <h3>Total Users</h3>
                    <div class="stat-value"><?php echo $stats['users']; ?></div>
                </div>
                <div class="stat-card orders">
                    <h3>Total Orders</h3>
                    <div class="stat-value"><?php echo $stats['orders']; ?></div>
                </div>
                <div class="stat-card food">
                    <h3>Food Items</h3>
                    <div class="stat-value"><?php echo $stats['food_items']; ?></div>
                </div>
                <div class="stat-card revenue">
                    <h3>Total Revenue</h3>
                    <div class="stat-value">$<?php echo number_format($stats['revenue'], 2); ?></div>
                </div>
                <div class="stat-card vouchers">
                    <h3>Vouchers</h3>
                    <div class="stat-value"><?php echo $stats['vouchers']; ?></div>
                </div>
            </div>
            
            <div class="recent-section">
                <h2>📦 Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Food</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                            <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                            <td><span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
