<?php
/**
 * Order Controller
 */
namespace App\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\Voucher;

class OrderController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        $userId = $this->getUserId();
        $successMessage = '';
        $errorMessage = '';
        $voucherResult = null;
        
        // Get selected food from URL
        $selectedFood = null;
        if (isset($_GET['food_id'])) {
            $selectedFood = Food::findById(intval($_GET['food_id']));
        }
        
        $quantity = intval($_GET['qty'] ?? 1);
        $originalTotal = $selectedFood ? $selectedFood['price'] * $quantity : 0;
        $finalTotal = $originalTotal;
        $appliedVoucher = null;
        
        // Handle voucher application
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_voucher'])) {
            $voucherCode = trim($_POST['voucher_code'] ?? '');
            
            if ($selectedFood) {
                $voucherResult = Voucher::applyVoucher($voucherCode, $originalTotal);
                
                if ($voucherResult['success']) {
                    $finalTotal = $voucherResult['final_amount'];
                    $appliedVoucher = $voucherCode;
                    $successMessage = $voucherResult['message'];
                } else {
                    $errorMessage = $voucherResult['message'];
                }
            }
        }
        
        // Handle order submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
            $foodId = intval($_POST['food_item_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 1);
            $address = trim($_POST['delivery_address'] ?? '');
            $voucherCode = trim($_POST['voucher_code'] ?? '');
            
            $food = Food::findById($foodId);
            
            if ($food && $quantity > 0 && !empty($address)) {
                // Calculate final price with voucher if applied
                $originalPrice = $food['price'] * $quantity;
                $finalPrice = $originalPrice;
                
                if (!empty($voucherCode)) {
                    $voucherResult = Voucher::applyVoucher($voucherCode, $originalPrice);
                    if ($voucherResult['success']) {
                        $finalPrice = $voucherResult['final_amount'];
                    }
                }
                
                $orderId = Order::create([
                    'user_id' => $userId,
                    'food_item_id' => $foodId,
                    'quantity' => $quantity,
                    'total_price' => $finalPrice,
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
        
        // Get order history
        $page = intval($_GET['page'] ?? 1);
        $orders = Order::getByUserId($userId, $page, 5);
        $totalOrders = Order::getCountByUserId($userId);
        $totalPages = ceil($totalOrders / 5);
        
        $this->view('order/index', [
            'selectedFood' => $selectedFood,
            'quantity' => $quantity,
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'successMessage' => $successMessage,
            'errorMessage' => $errorMessage,
            'userName' => $_SESSION['full_name'],
            'voucherResult' => $voucherResult,
            'finalTotal' => $finalTotal,
            'originalTotal' => $originalTotal
        ]);
    }
}
