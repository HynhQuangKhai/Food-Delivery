<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
    <style>
        .profile-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        
        .profile-info h1 {
            margin: 0 0 10px 0;
            font-size: 1.8em;
        }
        
        .profile-info p {
            margin: 5px 0;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 1.4em;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        
        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-label {
            width: 150px;
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            flex: 1;
            color: #2c3e50;
        }
        
        .btn-edit {
            display: inline-block;
            padding: 10px 25px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .btn-edit:hover {
            background: #5a6fd6;
        }
        
        /* Order history in profile */
        .order-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .profile-order-card {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            align-items: center;
        }
        
        .profile-order-card img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .order-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        
        .order-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .order-price {
            text-align: right;
        }
        
        .order-price .price {
            font-size: 1.3em;
            font-weight: bold;
            color: #e74c3c;
        }
        
        .order-price .status {
            display: block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.75em;
            margin-top: 5px;
        }
        
        @media (max-width: 600px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-order-card {
                grid-template-columns: 60px 1fr;
            }
            
            .order-price {
                grid-column: 1 / -1;
                text-align: left;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" style="background: #e74c3c; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; color: white;">
        <div style="font-size: 1.3em; font-weight: bold;">🍔 Food Delivery</div>
        <div style="display: flex; gap: 20px; align-items: center;">
            <a href="/food_delivery/home" style="color: white; text-decoration: none;">Home</a>
            <a href="/food_delivery/order" style="color: white; text-decoration: none;">Orders</a>
            <a href="/food_delivery/profile" style="color: white; text-decoration: none; font-weight: bold;">Profile</a>
            <span>Hello, <?php echo htmlspecialchars($user['full_name']); ?>!</span>
            <a href="/food_delivery/logout" style="background: white; color: #e74c3c; padding: 8px 15px; border-radius: 5px; text-decoration: none;">Logout</a>
        </div>
    </nav>

    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">👤</div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p>📧 <?php echo htmlspecialchars($user['email']); ?></p>
                <p>👤 @<?php echo htmlspecialchars($user['username']); ?></p>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">$<?php echo number_format($totalSpent, 0); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></div>
                <div class="stat-label">Phone</div>
            </div>
        </div>
        
        <!-- User Info -->
        <div class="profile-section">
            <h2 class="section-title">📋 Profile Information</h2>
            <div class="info-row">
                <div class="info-label">Username</div>
                <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Full Name</div>
                <div class="info-value"><?php echo htmlspecialchars($user['full_name']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone</div>
                <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Address</div>
                <div class="info-value"><?php echo htmlspecialchars($user['address'] ?: 'Not set'); ?></div>
            </div>
            <a href="/food_delivery/profile/edit" class="btn-edit">✏️ Edit Profile</a>
        </div>
        
        <!-- Order History -->
        <div class="profile-section">
            <h2 class="section-title">📦 Order History</h2>
            
            <?php if (empty($orders)): ?>
                <p style="text-align: center; color: #666; padding: 30px;">No orders yet. <a href="/food_delivery/home">Start ordering!</a></p>
            <?php else: ?>
                <div class="order-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="profile-order-card" onclick="showOrderDetail(<?php echo $order['id']; ?>)">
                            <img src="<?php echo \App\Models\Food::getImageUrl($order['food_item_id']); ?>" 
                                 alt="<?php echo htmlspecialchars($order['food_name']); ?>">
                            <div class="order-info">
                                <h4><?php echo htmlspecialchars($order['food_name']); ?></h4>
                                <p>
                                    Qty: <?php echo $order['quantity']; ?> • 
                                    <?php echo date('M d, Y', strtotime($order['order_date'])); ?>
                                </p>
                                <p style="font-size: 0.85em; color: #999;">#<?php echo $order['id']; ?></p>
                            </div>
                            <div class="order-price">
                                <span class="price">$<?php echo number_format($order['total_price'], 2); ?></span>
                                <span class="status status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Order Detail Modal -->
                        <div id="order-modal-<?php echo $order['id']; ?>" class="order-modal">
                            <div class="modal-content">
                                <span class="close-btn" onclick="closeOrderDetail(event, <?php echo $order['id']; ?>)">&times;</span>
                                <div class="modal-header">
                                    <img src="<?php echo \App\Models\Food::getImageUrl($order['food_item_id']); ?>" 
                                         alt="<?php echo htmlspecialchars($order['food_name']); ?>">
                                    <div class="modal-title">
                                        <h3><?php echo htmlspecialchars($order['food_name']); ?></h3>
                                        <span class="modal-status status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="modal-body">
                                    <div class="detail-row">
                                        <span class="label">Order ID:</span>
                                        <span class="value">#<?php echo $order['id']; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Date:</span>
                                        <span class="value"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Quantity:</span>
                                        <span class="value"><?php echo $order['quantity']; ?> items</span>
                                    </div>
                                    <div class="detail-row total">
                                        <span class="label">Total:</span>
                                        <span class="value">$<?php echo number_format($order['total_price'], 2); ?></span>
                                    </div>
                                    <div class="detail-row address">
                                        <span class="label">📍 Delivery Address:</span>
                                        <span class="value"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <script>
                    function showOrderDetail(orderId) {
                        document.getElementById('order-modal-' + orderId).style.display = 'flex';
                    }
                    
                    function closeOrderDetail(event, orderId) {
                        event.stopPropagation();
                        document.getElementById('order-modal-' + orderId).style.display = 'none';
                    }
                    
                    window.onclick = function(event) {
                        if (event.target.classList.contains('order-modal')) {
                            event.target.style.display = 'none';
                        }
                    }
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
