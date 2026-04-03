<?php
/**
 * Base Controller
 */
namespace App\Controllers;

class BaseController {
    
    protected function view($viewPath, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Build full path
        $file = __DIR__ . '/../views/' . $viewPath . '.php';
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("View not found: " . $viewPath);
        }
    }
    
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit();
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    protected function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
