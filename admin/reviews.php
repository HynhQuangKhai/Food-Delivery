<?php
/**
 * Admin - Reviews Management (Modern Design)
 * 
 * Features:
 * - View all product reviews grouped by products
 * - Reply to user reviews
 * - Delete inappropriate reviews
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';
$admin_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;

// Handle admin reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $review_id = intval($_POST['review_id'] ?? 0);
    $reply_text = trim($_POST['admin_reply'] ?? '');
    
    if ($review_id > 0 && !empty($reply_text)) {
        $stmt = $conn->prepare("UPDATE reviews SET admin_reply = ?, admin_reply_at = NOW(), replied_by = ? WHERE id = ?");
        $stmt->bind_param("sii", $reply_text, $admin_id, $review_id);
        if ($stmt->execute()) {
            $message = "Reply submitted successfully!";
        } else {
            $message = "Failed to submit reply.";
        }
        $stmt->close();
    }
}

// Handle delete review
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Review deleted!";
    }
    $stmt->close();
}

// Handle delete reply
if (isset($_GET['delete_reply'])) {
    $id = intval($_GET['delete_reply']);
    $stmt = $conn->prepare("UPDATE reviews SET admin_reply = NULL, admin_reply_at = NULL, replied_by = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Reply deleted!";
    }
    $stmt->close();
}

// Get products with reviews
$product_sql = "SELECT f.id, f.name, f.image_url, 
              COUNT(r.id) as review_count,
              AVG(r.rating) as avg_rating
              FROM food_items f
              JOIN reviews r ON f.id = r.food_item_id
              GROUP BY f.id, f.name, f.image_url
              ORDER BY review_count DESC";
$product_result = $conn->query($product_sql);
$products = [];
while ($row = $product_result->fetch_assoc()) {
    $products[] = $row;
}

// Get all reviews with user info
$selected_product = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
if ($selected_product > 0) {
    $review_sql = "SELECT r.*, u.username, u.full_name as user_full_name, u.avatar as user_avatar,
                   a.username as admin_username
                   FROM reviews r
                   JOIN users u ON r.user_id = u.id
                   LEFT JOIN users a ON r.replied_by = a.id
                   WHERE r.food_item_id = ?
                   ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($review_sql);
    $stmt->bind_param("i", $selected_product);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $review_sql = "SELECT r.*, u.username, u.full_name as user_full_name, u.avatar as user_avatar,
                   a.username as admin_username, f.name as food_name, f.id as food_id
                   FROM reviews r
                   JOIN users u ON r.user_id = u.id
                   JOIN food_items f ON r.food_item_id = f.id
                   LEFT JOIN users a ON r.replied_by = a.id
                   ORDER BY r.created_at DESC";
    $result = $conn->query($review_sql);
}

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
if ($selected_product > 0) {
    $stmt->close();
}

closeAdminDBConnection($conn);

// Calculate stats
$total_reviews = count($reviews);
$total_products = count($products);
$replied_count = count(array_filter($reviews, function($r) { return !empty($r['admin_reply']); }));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #1a1a2e;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px 20px;
            border-bottom: 1px solid #333;
        }
        .sidebar a {
            display: block;
            padding: 15px 25px;
            color: #ccc;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #667eea;
            color: white;
        }
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 1.8em;
        }
        .btn-logout {
            background: #e74c3c;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }
        .message {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card h3 {
            font-size: 2.2em;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-card p {
            color: #666;
            font-size: 0.95em;
        }
        
        .section-title {
            color: white;
            font-size: 1.5em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .product-card.active {
            border: 3px solid #667eea;
        }
        .product-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-info h4 {
            color: #2c3e50;
            font-size: 1em;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .product-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .review-badge {
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
        .rating-badge {
            color: #f39c12;
            font-size: 0.9em;
        }
        
        .reviews-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .reviews-header h2 {
            color: #2c3e50;
        }
        .btn-view-all {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
        }
        
        .review-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .review-item:hover {
            background: #f0f4ff;
        }
        .review-avatar {
            flex-shrink: 0;
        }
        .review-avatar img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .review-content-wrapper {
            flex: 1;
        }
        .review-header-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .user-info-review h4 {
            color: #2c3e50;
            font-size: 1.1em;
            margin-bottom: 3px;
        }
        .user-info-review small {
            color: #999;
        }
        .rating {
            color: #f39c12;
            font-size: 1.1em;
        }
        .review-text {
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
            font-style: italic;
        }
        .review-product {
            color: #667eea;
            font-weight: 500;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        
        .admin-reply {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 0 10px 10px 0;
            margin-top: 10px;
        }
        .admin-reply-header {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .admin-reply-text {
            color: #555;
            margin-bottom: 8px;
        }
        .admin-reply-meta {
            font-size: 0.85em;
            color: #999;
        }
        
        .reply-form {
            margin-top: 15px;
        }
        .reply-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
        }
        .reply-form button {
            margin-top: 10px;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        .reply-form button:hover {
            background: #5a67d8;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-action {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85em;
            transition: all 0.3s;
        }
        .btn-delete {
            background: #ffeaea;
            color: #e74c3c;
        }
        .btn-delete:hover {
            background: #e74c3c;
            color: white;
        }
        .btn-delete-reply {
            background: #f0f0f0;
            color: #666;
        }
        .btn-delete-reply:hover {
            background: #666;
            color: white;
        }
        
        .no-reviews {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
            }
            .admin-wrapper {
                flex-direction: column;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <div class="sidebar">
            <h2>🍔 Admin Panel</h2>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="food_items.php">🍽️ Food Items</a>
            <a href="users.php">👥 Users</a>
            <a href="orders.php">📦 Orders</a>
            <a href="vouchers.php">🎫 Vouchers</a>
            <a href="contact_messages.php">📧 Contact Messages</a>
            <a href="reviews.php" class="active">⭐ Reviews</a>
        </div>
        
        <div class="main-content">
            <div class="header">
                <h1>⭐ Reviews Management</h1>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_products; ?></h3>
                    <p>Products with Reviews</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_reviews; ?></h3>
                    <p>Total Reviews</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $replied_count; ?></h3>
                    <p>Replied</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_reviews - $replied_count; ?></h3>
                    <p>Pending Reply</p>
                </div>
            </div>
            
            <h2 class="section-title">📦 Products with Reviews</h2>
            <div class="products-grid">
                <a href="reviews.php" class="product-card <?php echo $selected_product === 0 ? 'active' : ''; ?>">
                    <div style="height: 140px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 3em;">📋</span>
                    </div>
                    <div class="product-info">
                        <h4>All Products</h4>
                        <div class="product-stats">
                            <span class="review-badge"><?php echo $total_reviews; ?> reviews</span>
                        </div>
                    </div>
                </a>
                <?php foreach ($products as $product): ?>
                <a href="reviews.php?product_id=<?php echo $product['id']; ?>" 
                   class="product-card <?php echo $selected_product == $product['id'] ? 'active' : ''; ?>">
                    <img src="<?php echo !empty($product['image_url']) ? $product['image_url'] : '../images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='../images/placeholder.jpg'">
                    <div class="product-info">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <div class="product-stats">
                            <span class="review-badge"><?php echo $product['review_count']; ?> reviews</span>
                            <span class="rating-badge">★ <?php echo number_format($product['avg_rating'], 1); ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <div class="reviews-container">
                <div class="reviews-header">
                    <h2><?php echo $selected_product > 0 ? 'Reviews for Selected Product' : 'All Reviews'; ?></h2>
                    <a href="../product.php<?php echo $selected_product > 0 ? '?id=' . $selected_product : ''; ?>" class="btn-view-all" target="_blank">
                        View on Site →
                    </a>
                </div>
                
                <?php if (empty($reviews)): ?>
                    <div class="no-reviews">
                        <h3>No reviews found</h3>
                        <p>There are no reviews for this product yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-avatar">
                            <?php 
                            $avatar_path = !empty($review['user_avatar']) ? $review['user_avatar'] : '../images/default-avatar.png';
                            if (!empty($review['user_avatar']) && substr($avatar_path, 0, 4) !== 'http' && substr($avatar_path, 0, 3) !== '../') {
                                $avatar_path = '../' . $avatar_path;
                            }
                            ?>
                            <img src="<?php echo $avatar_path; ?>" 
                                 alt="<?php echo htmlspecialchars($review['user_full_name'] ?? $review['username']); ?>">
                        </div>
                        <div class="review-content-wrapper">
                            <div class="review-header-info">
                                <div class="user-info-review">
                                    <h4><?php echo htmlspecialchars($review['user_full_name'] ?? $review['username']); ?></h4>
                                    <small><?php echo date('M d, Y H:i', strtotime($review['created_at'])); ?></small>
                                </div>
                                <div class="rating">
                                    <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($review['food_name'])): ?>
                                <div class="review-product">📦 <?php echo htmlspecialchars($review['food_name']); ?></div>
                            <?php endif; ?>
                            
                            <div class="review-text">"<?php echo htmlspecialchars($review['comment']); ?>"</div>
                            
                            <?php if (!empty($review['admin_reply'])): ?>
                            <div class="admin-reply">
                                <div class="admin-reply-header">
                                    <span>🛡️</span> Admin Reply
                                </div>
                                <div class="admin-reply-text"><?php echo htmlspecialchars($review['admin_reply']); ?></div>
                                <div class="admin-reply-meta">
                                    Replied by <?php echo htmlspecialchars($review['admin_username'] ?? 'Admin'); ?> 
                                    on <?php echo date('M d, Y H:i', strtotime($review['admin_reply_at'])); ?>
                                </div>
                                <div class="review-actions">
                                    <a href="?delete_reply=<?php echo $review['id']; ?><?php echo $selected_product > 0 ? '&product_id=' . $selected_product : ''; ?>" 
                                       class="btn-action btn-delete-reply" 
                                       onclick="return confirm('Delete this reply?')">Delete Reply</a>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="reply-form">
                                <form method="POST" action="">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <textarea name="admin_reply" placeholder="Write your reply to this review..." required></textarea>
                                    <button type="submit" name="submit_reply">Submit Reply</button>
                                </form>
                            </div>
                            <?php endif; ?>
                            
                            <div class="review-actions">
                                <a href="?delete=<?php echo $review['id']; ?><?php echo $selected_product > 0 ? '&product_id=' . $selected_product : ''; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Delete this review?')">Delete Review</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
