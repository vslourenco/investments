<?php

namespace App\Controllers;

class ChartController extends Controller
{    
    public function allocationType($request, $response){        
        $products = $this->c->db->query("Select SUM(value) as value, product_type.name, product_type.color From product 
            Inner Join product_type on product_type.id = product.product_type_id
            WHERE product.deleted_at IS NULL
            Group By product_type.id")->fetchAll(\PDO::FETCH_OBJ);

        foreach($products as $product){
            $chart_label[] = $product->{"name"};
            $chart_serie[] = $product->{"value"};
            $chart_color[] = $product->{"color"};
        }

        $total = array_sum($chart_serie);
        foreach ($chart_serie as $key => $value) {
            $chart_serie[$key] = round($chart_serie[$key]/$total, 2)*100;
        }
        
        return $this->c->view->render($response, 'chart_allocation_type.twig', compact('chart_label', 'chart_serie', 'chart_color'));        
    }
}