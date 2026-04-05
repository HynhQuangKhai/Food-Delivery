<?php
/**
 * Shared Database Connection for Admin
 */
function getAdminDBConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'food_delivery';
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    return $conn;
}

/**
 * Check if admin is logged in
 */
function checkAdminLogin() {
    session_start();
    
    // Check if logged in via admin credentials
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        // Ensure is_super_admin is set for admin user
        if (isset($_SESSION['admin_username']) && $_SESSION['admin_username'] === 'admin') {
            $_SESSION['is_super_admin'] = true;
        }
        return;
    }
    
    // Check if user is logged in as admin role
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // Set super admin flag for all admin users from database
        $_SESSION['is_super_admin'] = true;
        return;
    }
    
    // If regular user trying to access admin, redirect to home
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        header("Location: ../home.php");
        exit();
    }
    
    // Not logged in, redirect to admin login
    header("Location: index.php");
    exit();
}

/**
 * Check and update admin role from database
 * Redirects to user login if role changed to user
 */
function checkAdminRole() {
    // Only check for users logged in via database (not hardcoded admin)
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    $conn = getAdminDBConnection();
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $current_role = $row['role'] ?? 'user';
        $_SESSION['role'] = $current_role;
        
        if ($current_role === 'user') {
            // Clear admin session and redirect to user login
            session_destroy();
            header("Location: ../login.php");
            exit();
        }
    }
    
    $stmt->close();
    closeAdminDBConnection($conn);
}

/**
 * Close database connection
 */
function closeAdminDBConnection($conn) {
    $conn->close();
}
