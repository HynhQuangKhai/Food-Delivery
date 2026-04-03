<?php
/**
 * Order Controller
 */
namespace App\Controllers;

use App\Models\Food;
use App\Models\Order;

class OrderController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        $userId = $this->getUserId();
        $successMessage = '';
        $errorMessage = '';
        
        // Handle order submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
            $foodId = intval($_POST['food_item_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            $address = trim($_POST['delivery_address'] ?? '');
            
            $food = Food::findById($foodId);
            
            if ($food && $quantity > 0 && !empty($address)) {
                $orderId = Order::create([
                    'user_id' => $userId,
                    'food_item_id' => $foodId,
                    'quantity' => $quantity,
                    'total_price' => $food['price'] * $quantity,
                    'delivery_address' => $address
                ]);
                
                if ($orderId) {
                    $successMessage = "Order placed successfully! Your order ID is #" . $orderId;
                } else {
                    $errorMessage = "Failed to place order. Please try again.";
                }
            } else {
                $errorMessage = "Please fill in all required fields.";
            }
        }
        
        // Get selected food from URL
        $selectedFood = null;
        if (isset($_GET['food_id'])) {
            $selectedFood = Food::findById(intval($_GET['food_id']));
        }
        
        // Get order history
        $page = intval($_GET['page'] ?? 1);
        $orders = Order::getByUserId($userId, $page, 5);
        $totalOrders = Order::getCountByUserId($userId);
        $totalPages = ceil($totalOrders / 5);
        
        $this->view('order/index', [
            'selectedFood' => $selectedFood,
            'quantity' => intval($_GET['qty'] ?? 1),
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'userName' => $_SESSION['full_name']
        ]);
    }
}
