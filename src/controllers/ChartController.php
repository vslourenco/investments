<?php

namespace App\Controllers;

class ChartController extends Controller
{    
    public function allocationType($request, $response){        
        $products = $this->c->db->query("Select SUM(value) as value, product_type.name, product_type.color From product 
            Left Join product_type on product_type.id = product.product_type_id
            WHERE product.deleted_at IS NULL
            Group By product_type.id
            Order By product_type.name")->fetchAll(\PDO::FETCH_OBJ);

        foreach($products as $product){
            $chart_label[] = $product->{"name"};
            $chart_serie[] = $product->{"value"};
            $chart_color[] = $product->{"color"};
        }

        $total = array_sum($chart_serie);
        foreach ($chart_serie as $key => $value) {
            $chart_serie[$key] = round($chart_serie[$key]/$total, 4)*100;
        }
        
        return $this->c->view->render($response, 'chart_allocation_type.twig', compact('chart_label', 'chart_serie', 'chart_color'));        
    }

    public function allocationSubType($request, $response){    

        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);

        $product_type_id = $request->getQueryParam("product_type");

        if($product_type_id!=""){

            $products_with_subtype = $this->c->db->query("Select SUM(value) as value, product_subtype.name, product_subtype.color From product 
            Inner Join product_subtype on product_subtype.id = product.product_subtype_id
            WHERE product.deleted_at IS NULL AND product.product_type_id = '$product_type_id'
            Group By product_subtype.id")->fetchAll(\PDO::FETCH_OBJ);

            $products_without_subtype = $this->c->db->query("Select SUM(value) as value, 'Sem Sub-Tipo' as name, '#777777' as color From product 
            WHERE product.deleted_at IS NULL AND product.product_type_id = '$product_type_id' AND product.product_subtype_id IS NULL")->fetchAll(\PDO::FETCH_OBJ);

            $products =  array_merge($products_with_subtype, $products_without_subtype);
        }

        foreach($products as $product){
            $chart_label[] = $product->{"name"};
            $chart_serie[] = $product->{"value"};
            $chart_color[] = $product->{"color"};
        }

        $total = array_sum($chart_serie);
        foreach ($chart_serie as $key => $value) {
            $chart_serie[$key] = round($chart_serie[$key]/$total, 4)*100;
        }
        
        return $this->c->view->render($response, 'chart_allocation_subtype.twig', compact('product_types', 'product_type_id', 'chart_label', 'chart_serie', 'chart_color'));        
    }
}