<?php

namespace App\Controllers;

class ProductController extends Controller
{    
    public function importProducts($request, $response){        
       
        
        return $this->c->view->render($response, 'product_import.twig');        
    }
}