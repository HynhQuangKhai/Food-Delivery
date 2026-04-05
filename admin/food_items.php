<?php
/**
 * Admin - Food Items Management
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';
$error = '';

// Delete food item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM food_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Food item deleted successfully!";
    } else {
        $error = "Error deleting food item.";
    }
    $stmt->close();
}

// Add/Edit food item
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;
    $image_url = trim($_POST['image_url'] ?? '');
    $stock = intval($_POST['stock'] ?? 100);
    
    if (empty($name) || $price <= 0 || empty($category)) {
        $error = "Please fill in all required fields.";
    } elseif ($stock < 0) {
        $error = "Stock cannot be negative.";
    } else {
        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE food_items SET name = ?, description = ?, price = ?, category = ?, available = ?, image_url = ?, stock = ? WHERE id = ?");
            $stmt->bind_param("ssdsisii", $name, $description, $price, $category, $available, $image_url, $stock, $id);
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO food_items (name, description, price, category, available, image_url, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdsisi", $name, $description, $price, $category, $available, $image_url, $stock);
        }
        
        if ($stmt->execute()) {
            $message = $id > 0 ? "Food item updated successfully!" : "Food item added successfully!";
        } else {
            $error = "Error saving food item: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get all food items
$result = $conn->query("SELECT * FROM food_items ORDER BY category, name");
$food_items = [];
while ($row = $result->fetch_assoc()) {
    $food_items[] = $row;
}

// Get categories for dropdown
$cat_result = $conn->query("SELECT DISTINCT category FROM food_items ORDER BY category");
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}

closeAdminDBConnection($conn);

// Check if editing
$edit_item = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($food_items as $item) {
        if ($item['id'] == $edit_id) {
            $edit_item = $item;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Items - Admin</title>
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
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 15px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
            margin-left: 10px;
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
        .actions a {
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.85em;
            margin-right: 5px;
        }
        .edit-btn { background: #3498db; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .available { color: #2ecc71; }
        .unavailable { color: #e74c3c; }
    </style>
</head>
<body>
    <div class="admin-header">
        <div style="display: flex; align-items: center; gap: 20px;">
            <h1>🍔 Food Delivery Admin</h1>
            <button id="theme-toggle-btn" style="background: #667eea; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">🌙</button>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
    
    <div class="admin-container">
        <div class="sidebar">
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="food_items.php" class="active">🍽️ Food Items</a>
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
            
            <?php if (!empty($message)): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="form-section">
                <h2><?php echo $edit_item ? '✏️ Edit Food Item' : '➕ Add Food Item'; ?></h2>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $edit_item ? $edit_item['id'] : 0; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text" name="name" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <input type="text" name="category" list="categories" required value="<?php echo $edit_item ? htmlspecialchars($edit_item['category']) : ''; ?>">
                            <datalist id="categories">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label>Price *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="<?php echo $edit_item ? $edit_item['price'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Stock *</label>
                            <input type="number" name="stock" min="0" required value="<?php echo $edit_item ? ($edit_item['stock'] ?? 100) : 100; ?>">
                        </div>
                        <div class="form-group">
                            <label>Available</label>
                            <select name="available">
                                <option value="1" <?php echo ($edit_item && $edit_item['available']) ? 'selected' : ''; ?>>Yes</option>
                                <option value="0" <?php echo ($edit_item && !$edit_item['available']) ? 'selected' : ''; ?>>No</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Image URL (optional)</label>
                        <input type="url" name="image_url" value="<?php echo $edit_item ? htmlspecialchars($edit_item['image_url'] ?? '') : ''; ?>" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo $edit_item ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update' : 'Add'; ?> Food Item</button>
                    <?php if ($edit_item): ?>
                        <a href="food_items.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <h2 style="margin-bottom: 20px; color: #2c3e50;">📋 Food Items List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($food_items as $item): 
                        $item_stock = intval($item['stock'] ?? 0);
                        $stock_class = $item_stock <= 0 ? 'unavailable' : ($item_stock <= 5 ? '' : 'available');
                        $stock_text = $item_stock <= 0 ? '❌ Out' : ($item_stock <= 5 ? '⚠️ ' . $item_stock : '✓ ' . $item_stock);
                    ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td class="<?php echo $stock_class; ?>" style="font-weight: 600;"><?php echo $stock_text; ?></td>
                        <td class="<?php echo $item['available'] ? 'available' : 'unavailable'; ?>">
                            <?php echo $item['available'] ? '✓ Available' : '✗ Unavailable'; ?>
                        </td>
                        <td class="actions">
                            <a href="?edit=<?php echo $item['id']; ?>" class="edit-btn">Edit</a>
                            <a href="?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="../theme.js"></script>
</body>
</html>
