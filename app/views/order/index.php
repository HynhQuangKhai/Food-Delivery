<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
    <link rel="stylesheet" href="/food_delivery/order-style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($userName); ?></strong>!</span>
            </div>
            <div class="nav-actions">
                <a href="/food_delivery/home" class="btn btn-home">Home</a>
                <a href="/food_delivery/logout" class="btn btn-logout">Logout</a>
            </div>
        </div>
        
        <h1 style="text-align: center; color: #2c3e50; margin-bottom: 25px;">🛒 Confirm Order</h1>
        
        <!-- Messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <!-- Order Form -->
        <?php if ($selectedFood): ?>
        <div class="order-confirm-section">
            <div class="order-confirm-card">
                <div class="confirm-image">
                    <img src="<?php echo \App\Models\Food::getImageUrl($selectedFood['id']); ?>" 
                         alt="<?php echo htmlspecialchars($selectedFood['name']); ?>"
                         onerror="this.src='images/placeholder.jpg'">
                </div>
                
                <div class="confirm-details">
                    <span class="confirm-category"><?php echo htmlspecialchars($selectedFood['category']); ?></span>
                    <h2 class="confirm-name"><?php echo htmlspecialchars($selectedFood['name']); ?></h2>
                    <p class="confirm-price">$<?php echo number_format($selectedFood['price'], 2); ?> each</p>
                    
                    <form method="POST" action="/food_delivery/order" class="confirm-form">
                        <input type="hidden" name="food_item_id" value="<?php echo $selectedFood['id']; ?>">
                        
                        <div class="confirm-quantity">
                            <label>Quantity:</label>
                            <div class="qty-display-box"><?php echo $quantity; ?></div>
                            <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                        </div>
                        
                        <div class="confirm-total">
                            <span>Total:</span>
                            <span class="total-amount">
                                $<?php echo number_format($selectedFood['price'] * $quantity, 2); ?>
                            </span>
                        </div>
                        
                        <!-- Voucher Section -->
                        <div class="voucher-section" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                            <label>🎫 Voucher Code:</label>
                            <div style="display: flex; gap: 10px; margin-top: 8px;">
                                <input type="text" name="voucher_code" id="voucher_code" 
                                       value="<?php echo htmlspecialchars($_POST['voucher_code'] ?? ''); ?>"
                                       placeholder="Enter voucher code (e.g., SAVE10)"
                                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                <button type="submit" name="apply_voucher" 
                                        style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                    Apply
                                </button>
                            </div>
                            <?php if (!empty($voucherResult)): ?>
                                <?php if ($voucherResult['success']): ?>
                                    <div style="margin-top: 10px; padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; font-size: 0.9em;">
                                        ✅ Voucher applied! You saved $<?php echo number_format($voucherResult['discount_amount'], 2); ?> 
                                        (<?php echo $voucherResult['discount_percent']; ?>% off)<br>
                                        <strong>New Total: $<?php echo number_format($voucherResult['final_amount'], 2); ?></strong>
                                    </div>
                                <?php else: ?>
                                    <div style="margin-top: 10px; padding: 10px; background: #f8d7da; color: #721c24; border-radius: 5px; font-size: 0.9em;">
                                        ❌ <?php echo htmlspecialchars($voucherResult['message']); ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="confirm-address">
                            <label>📍 Delivery Address:</label>
                            <textarea name="delivery_address" rows="3" required 
                                      placeholder="Enter delivery address"></textarea>
                        </div>
                        
                        <button type="submit" name="place_order" class="btn-confirm-order">
                            ✅ Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div class="error-message" style="text-align: center;">
                No product selected. <a href="/food_delivery/home">Go to Home</a>
            </div>
        <?php endif; ?>
        
        <!-- Order History -->
        <div class="order-history">
            <h2>📦 Order History</h2>
            
            <?php if (empty($orders)): ?>
                <p style="text-align: center;">No orders yet.</p>
            <?php else: ?>
                <div class="order-cards">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" onclick="showOrderDetail(<?php echo $order['id']; ?>)" style="cursor: pointer;">
                            <div class="order-image-section">
                                <img src="<?php echo \App\Models\Food::getImageUrl($order['food_item_id']); ?>" 
                                     alt="<?php echo htmlspecialchars($order['food_name']); ?>">
                            </div>
                            <div class="order-details-section">
                                <div class="order-header">
                                    <span class="order-id">#<?php echo $order['id']; ?></span>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <h3 class="order-food-name"><?php echo htmlspecialchars($order['food_name']); ?></h3>
                                <p class="order-date"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                                <div class="order-mini-summary">
                                    <span class="mini-qty"><?php echo $order['quantity']; ?>x</span>
                                    <span class="mini-total">
                                        $<?php echo number_format($order['total_price'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Detail Modal -->
                        <div id="order-modal-<?php echo $order['id']; ?>" class="order-detail-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
                            <div class="modal-content" style="background: white; border-radius: 12px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; position: relative;">
                                <span class="close-modal" onclick="closeOrderDetail(event, <?php echo $order['id']; ?>)" style="position: absolute; top: 15px; right: 20px; font-size: 28px; cursor: pointer; color: #666;">&times;</span>
                                
                                <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #eee; display: flex; gap: 15px; align-items: center;">
                                    <img src="<?php echo \App\Models\Food::getImageUrl($order['food_item_id']); ?>" 
                                         alt="<?php echo htmlspecialchars($order['food_name']); ?>"
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    <div>
                                        <h3 style="margin: 0 0 8px 0; color: #2c3e50;"><?php echo htmlspecialchars($order['food_name']); ?></h3>
                                        <span class="status-<?php echo $order['status']; ?>" style="padding: 5px 12px; border-radius: 15px; font-size: 0.85em; font-weight: 500;">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="modal-body" style="padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                        <span style="color: #666;">Order ID:</span>
                                        <span style="font-weight: 500; color: #2c3e50;">#<?php echo $order['id']; ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                        <span style="color: #666;">Date:</span>
                                        <span style="font-weight: 500; color: #2c3e50;"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                        <span style="color: #666;">Quantity:</span>
                                        <span style="font-weight: 500; color: #2c3e50;"><?php echo $order['quantity']; ?> items</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 2px solid #e74c3c;">
                                        <span style="color: #666; font-size: 1.1em;">Total:</span>
                                        <span style="font-weight: bold; color: #e74c3c; font-size: 1.3em;">$<?php echo number_format($order['total_price'], 2); ?></span>
                                    </div>
                                    <div style="padding-top: 15px;">
                                        <span style="color: #666; display: block; margin-bottom: 8px;">📍 Delivery Address:</span>
                                        <span style="color: #2c3e50; line-height: 1.5;"><?php echo htmlspecialchars($order['delivery_address']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?php echo $currentPage - 1; ?>" class="page-link">← Prev</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $currentPage): ?>
                            <span class="page-current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>" class="page-link"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?php echo $currentPage + 1; ?>" class="page-link">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <script>
                    function showOrderDetail(orderId) {
                        document.getElementById('order-modal-' + orderId).style.display = 'flex';
                    }
                    
                    function closeOrderDetail(event, orderId) {
                        event.stopPropagation();
                        document.getElementById('order-modal-' + orderId).style.display = 'none';
                    }
                    
                    window.onclick = function(event) {
                        if (event.target.classList.contains('order-detail-modal')) {
                            event.target.style.display = 'none';
                        }
                    }
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
