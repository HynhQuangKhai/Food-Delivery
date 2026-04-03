<?php
/**
 * Voucher Model
 */
namespace App\Models;

class Voucher extends BaseModel {
    
    /**
     * Apply voucher code and calculate discount
     * 
     * @param string $code Voucher code
     * @param float $totalAmount Original order amount
     * @return array Result with success flag and discount details
     */
    public static function applyVoucher($code, $totalAmount) {
        $conn = self::getConnection();
        
        $result = [
            'success' => false,
            'message' => '',
            'discount_percent' => 0,
            'discount_amount' => 0,
            'final_amount' => $totalAmount
        ];
        
        // Validate inputs
        if (empty($code)) {
            $result['message'] = 'Voucher code is required.';
            return $result;
        }
        
        if ($totalAmount <= 0) {
            $result['message'] = 'Invalid order amount.';
            return $result;
        }
        
        // Prepare and execute query with prepared statement
        $stmt = $conn->prepare("SELECT code, discount_percent, expiry_date, status FROM vouchers WHERE code = ?");
        
        if (!$stmt) {
            $result['message'] = 'Database error: ' . $conn->error;
            return $result;
        }
        
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $queryResult = $stmt->get_result();
        
        // Check if voucher exists
        if ($queryResult->num_rows === 0) {
            $result['message'] = 'Invalid voucher code.';
            $stmt->close();
            return $result;
        }
        
        $voucher = $queryResult->fetch_assoc();
        $stmt->close();
        
        // Check if voucher is active
        if ($voucher['status'] !== 'active') {
            $result['message'] = 'This voucher is no longer active.';
            return $result;
        }
        
        // Check if voucher has expired
        $currentDate = date('Y-m-d H:i:s');
        if ($currentDate > $voucher['expiry_date']) {
            $result['message'] = 'This voucher has expired.';
            return $result;
        }
        
        // Calculate discount
        $discountPercent = (float) $voucher['discount_percent'];
        $discountAmount = ($totalAmount * $discountPercent) / 100;
        $finalAmount = $totalAmount - $discountAmount;
        
        // Ensure final amount is not negative
        if ($finalAmount < 0) {
            $finalAmount = 0;
            $discountAmount = $totalAmount;
        }
        
        // Return success result
        $result['success'] = true;
        $result['discount_percent'] = $discountPercent;
        $result['discount_amount'] = round($discountAmount, 2);
        $result['final_amount'] = round($finalAmount, 2);
        $result['message'] = 'Voucher applied successfully! You saved $' . number_format($discountAmount, 2);
        
        return $result;
    }
    
    /**
     * Find voucher by code
     */
    public static function findByCode($code) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM vouchers WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
        $voucher = $result->fetch_assoc();
        $stmt->close();
        return $voucher;
    }
    
    /**
     * Get all active vouchers
     */
    public static function getActiveVouchers() {
        $conn = self::getConnection();
        $currentDate = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT * FROM vouchers WHERE status = 'active' AND expiry_date > ?");
        $stmt->bind_param("s", $currentDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $vouchers = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $vouchers;
    }
}
