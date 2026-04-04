<?php
/**
 * Admin - Users Management
 */
require_once 'includes/db.php';
checkAdminLogin();
checkAdminRole();

$conn = getAdminDBConnection();
$message = '';
$error = '';

// Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'user';
    
    // Validation
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = "Please fill in all required fields (Username, Password, Email, Full Name).";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if username or email exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password with MD5
            $hashed_password = md5($password);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $hashed_password, $email, $full_name, $phone, $address, $role);
            
            if ($stmt->execute()) {
                $message = "User created successfully!";
            } else {
                $error = "Error creating user: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Edit user (super admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user']) && isset($_SESSION['is_super_admin'])) {
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = trim($_POST['password'] ?? '');
    
    // Prevent editing the super admin account
    $check_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $edit_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $user_data = $check_result->fetch_assoc();
    $check_stmt->close();
    
    if ($user_data && $user_data['username'] === 'admin') {
        $error = "Cannot edit the super admin account.";
    } elseif (empty($username) || empty($email) || empty($full_name)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if username or email already exists (excluding current user)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check_stmt->bind_param("ssi", $username, $email, $edit_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            if (!empty($password)) {
                // Update with new password
                $hashed_password = md5($password);
                $stmt = $conn->prepare("UPDATE users SET username = ?, password = ?, email = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $username, $hashed_password, $email, $full_name, $phone, $address, $role, $edit_id);
            } else {
                // Update without changing password
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE id = ?");
                $stmt->bind_param("ssssssi", $username, $email, $full_name, $phone, $address, $role, $edit_id);
            }
            
            if ($stmt->execute()) {
                $message = "User updated successfully!";
            } else {
                $error = "Error updating user: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Prevent deleting the super admin
    $check_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $user_data = $check_result->fetch_assoc();
    $check_stmt->close();
    
    if ($user_data && $user_data['username'] === 'admin') {
        $error = "Cannot delete the super admin account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "User deleted successfully!";
        } else {
            $error = "Error deleting user.";
        }
        $stmt->close();
    }
}

// Get all users
$result = $conn->query("SELECT id, username, email, full_name, phone, address, role, created_at FROM users ORDER BY id DESC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Check if editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($users as $user) {
        if ($user['id'] == $edit_id) {
            $edit_user = $user;
            break;
        }
    }
}

closeAdminDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
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
        .edit-btn { background: #667eea; color: white; }
        .delete-btn { background: #e74c3c; color: white; }
        .role-badge { 
            padding: 4px 10px; 
            border-radius: 12px; 
            font-size: 0.85em; 
            font-weight: 600;
        }
        .role-admin { background: #667eea; color: white; }
        .role-user { background: #95a5a6; color: white; }
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
            
            <h2 style="margin-bottom: 20px; color: #2c3e50;">👥 Users List (<?php echo count($users); ?>)</h2>
            
            <!-- Edit User Form (Super Admin Only) -->
            <?php if (isset($_SESSION['is_super_admin']) && $edit_user): ?>
            <div class="form-section" style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">✏️ Edit User</h3>
                <form method="POST" action="">
                    <input type="hidden" name="edit_user" value="1">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_user['id']; ?>">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Password (leave blank to keep current)</label>
                            <input type="password" name="password" minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role">
                                <option value="user" <?php echo ($edit_user['role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo ($edit_user['role'] ?? 'user') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Address</label>
                        <textarea name="address" rows="2" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;"><?php echo htmlspecialchars($edit_user['address'] ?? ''); ?></textarea>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 25px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer;">Update User</button>
                        <a href="users.php" style="padding: 12px 25px; background: #95a5a6; color: white; border: none; border-radius: 8px; text-decoration: none;">Cancel</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Add User Form -->
            <div class="form-section" style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                <h3 style="color: #2c3e50; margin-bottom: 20px;">➕ Create New User</h3>
                <form method="POST" action="">
                    <input type="hidden" name="add_user" value="1">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div class="form-group">
                            <label>Username *</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password * (min 6 chars)</label>
                            <input type="password" name="password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone">
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Address</label>
                        <textarea name="address" rows="2" style="width: 100%; padding: 10px; border: 2px solid #e0e0e0; border-radius: 8px;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="padding: 12px 25px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; margin-top: 10px;">Create User</button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): 
                        $role_class = ($user['role'] ?? 'user') === 'admin' ? 'role-admin' : 'role-user';
                        $is_super_admin_user = $user['username'] === 'admin';
                    ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?> <?php if ($is_super_admin_user) echo '<span style="color: #f39c12;">👑</span>'; ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="role-badge <?php echo $role_class; ?>"><?php echo ucfirst($user['role'] ?? 'user'); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td class="actions">
                            <?php if (isset($_SESSION['is_super_admin']) && !$is_super_admin_user): ?>
                                <a href="?edit=<?php echo $user['id']; ?>" class="edit-btn">Edit</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="delete-btn" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
