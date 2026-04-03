<?php
/**
 * Order Model
 */
namespace App\Models;

class Order extends BaseModel {
    
    public static function create($data) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO orders (user_id, food_item_id, quantity, total_price, delivery_address, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iiids", 
            $data['user_id'], 
            $data['food_item_id'], 
            $data['quantity'], 
            $data['total_price'], 
            $data['delivery_address']
        );
        $success = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $success ? $id : false;
    }
    
    public static function getByUserId($userId, $page = 1, $perPage = 5) {
        $conn = self::getConnection();
        $offset = ($page - 1) * $perPage;
        
        $stmt = $conn->prepare("SELECT o.*, f.name as food_name FROM orders o JOIN food_items f ON o.food_item_id = f.id WHERE o.user_id = ? ORDER BY o.order_date DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $userId, $perPage, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $orders;
    }
    
    public static function getCountByUserId($userId) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'];
    }
    
    public static function findById($id, $userId) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT o.*, f.name as food_name FROM orders o JOIN food_items f ON o.food_item_id = f.id WHERE o.id = ? AND o.user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        return $order;
    }
}
