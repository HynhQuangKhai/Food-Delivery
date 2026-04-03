<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Food Delivery</title>
    <link rel="stylesheet" href="/food_delivery/style.css">
    <link rel="stylesheet" href="/food_delivery/home.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-brand">
            <h1>🍔 Food Delivery</h1>
        </div>
        <div class="nav-user">
            <span>Hello, <?php echo htmlspecialchars($userName); ?>!</span>
            <a href="/food_delivery/profile" class="btn-profile">👤 Profile</a>
            <a href="/food_delivery/logout" class="btn-logout">Logout</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="/food_delivery/home" class="search-form">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search food..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <button type="submit">🔍</button>
                </div>
                
                <select name="category" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" 
                            <?php echo $filters['category'] === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Food Grid -->
        <?php if (empty($foods)): ?>
            <p class="no-results">No food items found.</p>
        <?php else: ?>
            <div class="food-grid">
                <?php foreach ($foods as $food): ?>
                    <a href="/food_delivery/product?id=<?php echo $food['id']; ?>" class="food-card">
                        <div class="food-image">
                            <img src="<?php echo \App\Models\Food::getImageUrl($food['id']); ?>" 
                                 alt="<?php echo htmlspecialchars($food['name']); ?>"
                                 onerror="this.src='images/placeholder.jpg'">
                            <div class="food-category"><?php echo htmlspecialchars($food['category']); ?></div>
                        </div>
                        <div class="food-info">
                            <h3><?php echo htmlspecialchars($food['name']); ?></h3>
                            <p class="food-description"><?php echo htmlspecialchars($food['description']); ?></p>
                            <div class="food-footer">
                                <span class="food-price">$<?php echo number_format($food['price'], 2); ?></span>
                                <span class="order-btn">Order Now →</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
