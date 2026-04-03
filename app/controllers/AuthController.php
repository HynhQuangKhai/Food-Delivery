<?php
/**
 * Auth Controller - Login/Register/Logout
 */
namespace App\Controllers;

use App\Models\User;

class AuthController extends BaseController {
    
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/home');
        }
        
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            
            if (empty($username) || empty($password)) {
                $error = "Please enter both username and password.";
            } else {
                $user = User::findByUsername($username);
                
                if ($user && User::verifyPassword($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $this->redirect('/home');
                } else {
                    $error = "Invalid username or password.";
                }
            }
        }
        
        $this->view('auth/login', ['error' => $error]);
    }
    
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/home');
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'password' => md5(trim($_POST['password'] ?? '')),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            
            // Validation
            if (empty($data['username']) || empty($_POST['password']) || empty($data['full_name']) || empty($data['email'])) {
                $error = "Please fill in all required fields.";
            } elseif (strlen($_POST['password']) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (User::findByUsername($data['username'])) {
                $error = "Username already exists.";
            } elseif (User::findByEmail($data['email'])) {
                $error = "Email already registered.";
            } else {
                $userId = User::create($data);
                if ($userId) {
                    $success = "Account created successfully! Please login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
        
        $this->view('auth/register', ['error' => $error, 'success' => $success]);
    }
    
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }
}
