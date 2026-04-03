<?php
/**
 * Product Controller
 */
namespace App\Controllers;

use App\Models\Food;

class ProductController extends BaseController {
    
    public function show() {
        $this->requireAuth();
        
        $id = intval($_GET['id'] ?? 0);
        $food = Food::findById($id);
        
        if (!$food) {
            $this->redirect('/home');
        }
        
        $this->view('product/show', [
            'food' => $food,
            'userName' => $_SESSION['full_name']
        ]);
    }
}
