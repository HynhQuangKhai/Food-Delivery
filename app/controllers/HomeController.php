<?php
/**
 * Home Controller
 */
namespace App\Controllers;

use App\Models\Food;

class HomeController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        $filters = [
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $foods = Food::getAll($filters);
        $categories = Food::getCategories();
        
        $this->view('home/index', [
            'foods' => $foods,
            'categories' => $categories,
            'filters' => $filters,
            'userName' => $_SESSION['full_name']
        ]);
    }
}
