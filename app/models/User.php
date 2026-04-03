<?php
/**
 * User Model
 */
namespace App\Models;

class User extends BaseModel {
    
    public static function findByUsername($username) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    public static function findByEmail($email) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    public static function findById($id) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    public static function create($data) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", 
            $data['username'], 
            $data['password'], 
            $data['email'], 
            $data['full_name'], 
            $data['phone'], 
            $data['address']
        );
        $success = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $success ? $id : false;
    }
    
    public static function update($id, $data) {
        $conn = self::getConnection();
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", 
            $data['full_name'], 
            $data['email'], 
            $data['phone'], 
            $data['address'],
            $id
        );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    
    public static function verifyPassword($password, $hashedPassword) {
        return md5($password) === $hashedPassword;
    }
}
