<?php
/**
 * Profile Controller - User Profile & Order History
 */
namespace App\Controllers;

use App\Models\User;
use App\Models\Order;

class ProfileController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        // Redirect admin to admin dashboard
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $this->redirect('/admin/dashboard.php');
        }
        
        $userId = $this->getUserId();
        $user = User::findById($userId);
        
        // Get all orders (not paginated for profile)
        $orders = Order::getByUserId($userId, 1, 100);
        
        // Calculate stats
        $totalOrders = count($orders);
        $totalSpent = array_sum(array_column($orders, 'total_price'));
        
        $this->view('profile/index', [
            'user' => $user,
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'totalSpent' => $totalSpent
        ]);
    }
    
    public function update() {
        $this->requireAuth();
        
        $userId = $this->getUserId();
        $success = '';
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            
            if (empty($data['full_name']) || empty($data['email'])) {
                $error = "Full name and email are required.";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } else {
                if (User::update($userId, $data)) {
                    $success = "Profile updated successfully!";
                    $_SESSION['full_name'] = $data['full_name'];
                } else {
                    $error = "Update failed. Please try again.";
                }
            }
        }
        
        $user = User::findById($userId);
        $this->view('profile/edit', [
            'user' => $user,
            'success' => $success,
            'error' => $error
        ]);
    }
}
