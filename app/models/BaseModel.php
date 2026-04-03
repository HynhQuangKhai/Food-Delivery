<?php
/**
 * Base Model - Database Connection
 */
namespace App\Models;

require_once __DIR__ . '/../../config/database.php';

class BaseModel {
    protected static $conn = null;
    
    protected static function getConnection() {
        if (self::$conn === null) {
            self::$conn = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (self::$conn->connect_error) {
                die("Connection failed: " . self::$conn->connect_error);
            }
            self::$conn->set_charset("utf8mb4");
        }
        return self::$conn;
    }
    
    protected static function closeConnection() {
        if (self::$conn !== null) {
            self::$conn->close();
            self::$conn = null;
        }
    }
}
