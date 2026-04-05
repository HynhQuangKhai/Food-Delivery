<?php
/**
 * Admin - Order Detail View
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    header("Location: orders.php");
    exit();
}

// Get order details with user and food info
$stmt = $conn->prepare("SELECT o.*, f.name as food_name, f.description as food_description, f.image_url, f.category,
    u.id as user_id, u.username, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone, u.address as customer_address,
    d.username as deliverer_name
    FROM orders o 
    JOIN food_items f ON o.food_item_id = f.id 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN users d ON o.deliverer_id = d.id
    WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: orders.php");
    exit();
}

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> - Admin</title>
    <link rel="stylesheet" href="../theme.css">
    <link rel="stylesheet" href="../responsive.css">
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
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .order-title h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-ready_to_pickup { background: #9b59b6; color: white; }
        .status-delivering { background: #f39c12; color: white; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
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
        .food-section {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .food-image {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            object-fit: cover;
        }
        .food-info h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .food-info p {
            color: #666;
            margin-bottom: 10px;
        }
        .price {
            font-size: 1.5em;
            color: #667eea;
            font-weight: 700;
        }
        .section-title {
            color: #2c3e50;
            margin: 30px 0 15px;
            font-size: 1.2em;
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
        </div>
        
        <div class="main-content">
            <a href="orders.php" class="back-btn">← Back to Orders</a>
            
            <div class="order-card">
                <div class="order-header">
                    <div class="order-title">
                        <h2>Order #<?php echo $order['id']; ?></h2>
                        <span style="color: #666;">Placed on <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
                
                <h3 class="section-title">👤 Customer Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Customer Name</label>
                        <value><?php echo htmlspecialchars($order['customer_name']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Username</label>
                        <value><?php echo htmlspecialchars($order['username']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <value><?php echo htmlspecialchars($order['customer_email']); ?></value>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <value><?php echo htmlspecialchars($order['customer_phone'] ?? 'N/A'); ?></value>
                    </div>
                    <div class="info-item" style="grid-column: span 2;">
                        <label>Address</label>
                        <value><?php echo htmlspecialchars($order['delivery_address'] ?? $order['customer_address'] ?? 'N/A'); ?></value>
                    </div>
                </div>
                
                <h3 class="section-title">🍽️ Food Item</h3>
                <div class="food-section">
                    <img src="<?php echo !empty($order['image_url']) ? '../' . $order['image_url'] : '../images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($order['food_name']); ?>" class="food-image"
                         onerror="this.src='../images/placeholder.jpg'">
                    <div class="food-info">
                        <h3><?php echo htmlspecialchars($order['food_name']); ?></h3>
                        <p><?php echo htmlspecialchars($order['food_description']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($order['category']); ?></p>
                        <p class="price">$<?php echo number_format($order['total_price'], 2); ?></p>
                    </div>
                </div>
                
                <?php if ($order['deliverer_id']): ?>
                <h3 class="section-title">🚚 Delivery Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Deliverer</label>
                        <value><?php echo htmlspecialchars($order['deliverer_name'] ?? 'N/A'); ?></value>
                    </div>
                    <?php if ($order['delivered_at']): ?>
                    <div class="info-item">
                        <label>Delivered At</label>
                        <value><?php echo date('M d, Y H:i', strtotime($order['delivered_at'])); ?></value>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../theme.js"></script>
</body>
</html>
