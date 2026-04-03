<?php
/**
 * Food Model
 */
namespace App\Models;

class Food extends BaseModel {
    
    // Product images mapping
    private static $productImages = [
        1 => 'https://www.themealdb.com/images/media/meals/x0lk931587671540.jpg',
        2 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTHA83KUkZL_J-d9CZ8t6MOJ_G7p-svPKKn0g&s',
        3 => 'https://www.themealdb.com/images/media/meals/urzj1d1587670726.jpg',
        4 => 'https://www.recipetineats.com/tachyon/2023/09/Crispy-fried-chicken-burgers_5.jpg',
        5 => 'https://www.themealdb.com/images/media/meals/llcbn01574260722.jpg',
        6 => 'https://www.themealdb.com/images/media/meals/wvqpwt1468339226.jpg',
        7 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQi-ZlEpiXF_m9KKdeH0qhr-CEyc18mIVg5lw&s',
        8 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQHSUxI-tULvmJFO_8Otx3-UeeuKJvGV8vMiQ&s',
        9 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcToiYRmShJapV_O0JJgU3ygOnOQVgnu7rL10A&s',
        10 => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQHDS4k_XMABZYxPrA05U30Q20YXqsf_CqQA&s',
    ];
    
    public static function getImageUrl($foodId) {
        return self::$productImages[$foodId] ?? 'images/placeholder.jpg';
    }
    
    public static function getAll($filters = []) {
        $conn = self::getConnection();
        $sql = "SELECT * FROM food_items WHERE available = 1";
        $params = [];
        $types = "";
        
        if (!empty($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
            $types .= "s";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND name LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }
        
        $sql .= " ORDER BY category, name";
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $items;
    }
    
    public static function findById($id) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM food_items WHERE id = ? AND available = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();
        return $item;
    }
    
    public static function getCategories() {
        $conn = self::getConnection();
        $result = $conn->query("SELECT DISTINCT category FROM food_items WHERE available = 1 ORDER BY category");
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        return array_column($categories, 'category');
    }
}
