<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($food['name']); ?> - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
    <link rel="stylesheet" href="/food_delivery/product.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-brand">
            <a href="/food_delivery/home" class="back-link">← Back</a>
            <h1>🍔 Food Delivery</h1>
        </div>
        <div class="nav-user">
            <span>Hello, <?php echo htmlspecialchars($userName); ?>!</span>
            <a href="/food_delivery/logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Product Detail -->
    <main class="product-container">
        <div class="product-card">
            <div class="product-image-section">
                <img src="<?php echo \App\Models\Food::getImageUrl($food['id']); ?>" 
                     alt="<?php echo htmlspecialchars($food['name']); ?>"
                     onerror="this.src='images/placeholder.jpg'">
            </div>
            
            <div class="product-details-section">
                <span class="product-category"><?php echo htmlspecialchars($food['category']); ?></span>
                <h1 class="product-name"><?php echo htmlspecialchars($food['name']); ?></h1>
                <p class="product-description"><?php echo htmlspecialchars($food['description']); ?></p>
                
                <!-- Quantity -->
                <div class="quantity-section">
                    <label>Quantity:</label>
                    <div class="quantity-selector">
                        <button type="button" onclick="decreaseQty()">−</button>
                        <input type="number" id="quantity" value="1" min="1" max="20" readonly>
                        <button type="button" onclick="increaseQty()">+</button>
                    </div>
                </div>
                
                <!-- Price -->
                <div class="order-summary">
                    <span class="total-price" id="totalPrice">
                        Total: $<span id="total-price"><?php echo number_format($food['price'], 2); ?></span>
                    </span>
                </div>
                
                <!-- Order Button -->
                <a href="/food_delivery/order?food_id=<?php echo $food['id']; ?>&qty=1" 
                   id="orderBtn" class="btn-order">
                    Order Now
                </a>
            </div>
        </div>
    </main>

    <script>
        const unitPrice = <?php echo $food['price']; ?>;
        const foodId = <?php echo $food['id']; ?>;
        
        function updateOrder() {
            const qty = parseInt(document.getElementById('quantity').value);
            const total = (unitPrice * qty).toFixed(2);
            
            document.getElementById('totalPrice').textContent = 'VND ' + total;
            document.getElementById('orderBtn').href = '/food_delivery/order?food_id=' + foodId + '&qty=' + qty;
        }
        
        function increaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) < 20) {
                input.value = parseInt(input.value) + 1;
                updateOrder();
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateOrder();
            }
        }
    </script>
</body>
</html>
