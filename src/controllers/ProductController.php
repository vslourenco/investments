<?php

namespace App\Controllers;

class ProductController extends Controller
{    
    public function index($request, $response){    

        $sql_comp="";
        $sql_offset="";
        $sql_limit= 10;
        $current_page= 1;
        $uri = $request->getUri()->getPath()."?";

        if($request->getQueryParam("name")!=""){
            $sql_comp .= " AND product.name LIKE '%{$request->getQueryParam("name")}%'";
            $uri .= "name=".$request->getQueryParam("name");
        }
        if($request->getQueryParam("product_type")!=""){
            $sql_comp .= " AND product.product_type_id = {$request->getQueryParam("product_type")}";   
            $uri .= "product_type=".$request->getQueryParam("product_type");         
        }
        if($request->getQueryParam("page")!=""){
            $current_page = $request->getQueryParam("page");
            $offset = $sql_limit*($current_page-1);
            $sql_offset .= " OFFSET $offset";            
        }

        $quantity_products = $this->c->db->query("SELECT product.id FROM product 
            INNER JOIN product_type ON product.product_type_id = product_type.id
            WHERE product.deleted_at IS NULL $sql_comp")->rowCount();
        $quantity_pages = ceil($quantity_products / $sql_limit);

        $products = $this->c->db->query("SELECT product.*, product_type.name as type FROM product 
            INNER JOIN product_type ON product.product_type_id = product_type.id
            WHERE product.deleted_at IS NULL $sql_comp
            LIMIT $sql_limit $sql_offset")->fetchAll(\PDO::FETCH_OBJ);

        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);

        return $this->c->view->render($response, 'product_index.twig', compact('products', 'product_types', 'current_page', 'quantity_pages', 'uri'));        
    }

    public function create($request, $response){  
        $action= $this->c->get('router')->pathFor('products.store');
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_form.twig', compact('action', 'product_types'));                          
    }

    public function store($request, $response){         
        $params = $request->getParams();    
        $product = $this->c->db->prepare("INSERT INTO product (name, product_type_id, quantity, value, note, created_at) VALUES (:name, :product_type_id, :quantity, :value, :note, NOW())");
        $product->execute(array(
            ':name' => $params['name'],
            ':product_type_id' => $params['product_type_id'],
            ':quantity' => $params['quantity'],
            ':value' => $params['value'],
            ':note' => $params['note']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('products.index'));           
    }

    public function edit($request, $response, $args){ 
        $id = isset($args["id"]) ? $args["id"] : "";
        $action= $this->c->get('router')->pathFor('products.update');
        $product = $this->c->db->query("SELECT * FROM product WHERE id='$id'")->fetch(\PDO::FETCH_OBJ);
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL OR id = {$product->product_type_id}")->fetchAll(\PDO::FETCH_OBJ);

        $trades = $this->c->db->query("SELECT * FROM trade 
            WHERE product_id = '$id' AND  deleted_at IS NULL
            ORDER BY date DESC")->fetchAll(\PDO::FETCH_OBJ);

        return $this->c->view->render($response, 'product_form.twig', compact('product', 'action', 'product_types', 'trades'));                
    }

    public function update($request, $response){        
        $params = $request->getParams();  
        $product = $this->c->db->prepare("UPDATE product SET name=:name, product_type_id=:product_type_id, quantity=:quantity, value=:value, note=:note WHERE id=:id");

        $product->execute(array(
            ':name' => $params['name'],
            ':product_type_id' => $params['product_type_id'],
            ':quantity' => $params['quantity'],
            ':value' => $params['value'],
            ':note' => $params['note'],
            ':id' => $params['id']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('products.index'));              
    }

    public function delete($request, $response, $args){   
        $id = isset($args["id"]) ? $args["id"] : "";
        $product = $this->c->db->prepare("UPDATE product SET deleted_at=:date WHERE id=:id");
        $product->execute(array(
            ':date' => date("Y-m-d H:i"),
            ':id' => $id
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('products.index'));              
    }

    public function verifyConcordance($request, $response){ 
        $products = $this->c->db->query("Select SUM(value) as value, product_type.name, product_type.target From product 
            Inner Join product_type on product_type.id = product.product_type_id
            WHERE product.deleted_at IS NULL
            Group By product_type.id")->fetchAll(\PDO::FETCH_OBJ);

        $applications = array();
        $total_value = 0;
        foreach($products as $product){
            $applications[] = array(
                "product" => $product->name,
                "target" => $product->target,
                "value" => round($product->value, 2)
            );
            $total_value+=$product->value;
        }
        
        $max_divergence = 0;
        $adjustment_tax = 0;
        foreach ($applications as $key => $value) {
            $applications[$key]["percentage"] = round($applications[$key]["value"]/$total_value, 4)*100;
            $divergence = $applications[$key]["percentage"]-$applications[$key]["target"];
            if($divergence > $max_divergence){
                $max_divergence = $divergence;
                $adjustment_tax = round($applications[$key]["value"]/$applications[$key]["target"], 4);
            }
        }

        $total_with_adjust = 0;
        foreach ($applications as $key => $value) {
            $total_with_adjust += $applications[$key]["target"] * $adjustment_tax;
        }

        //var_dump($applications); die();

        return $this->c->view->render($response, 'product_verify_concordance.twig', compact('applications', 'adjustment_tax', 'total_value', 'total_with_adjust'));        
    }

    public function importForm($request, $response){ 
        return $this->c->view->render($response, 'product_import_form.twig');        
    }

    public function importList($request, $response){ 
        
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        $stored_products = $this->c->db->query("SELECT * FROM product WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);

        $products_by_name = array_column($stored_products, NULL, "name");

        $params = $request->getParams();
        $uploadedFiles = $request->getUploadedFiles();

        $inputFileName = $uploadedFiles["document"]->file;
        /** Create a new Xlsx Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $last_column = $worksheet->getHighestDataColumn();
        $last_row = $worksheet->getHighestDataRow();
       
        
        //var_dump($uploadedFiles["document"]->file);           
        //echo "<pre>";
        //var_dump($spreadsheet);
        //var_dump($last_column);
        //var_dump($last_row);
        //var_dump($products_by_name);
        //var_dump($products);
        //die();

        $products = array();
        for($i=2; $i<=$last_row; $i++){
            switch ($worksheet->getCell("B".$i)) {
                case 'Fundo':
                    $type = 9;
                    break;

                case 'Renda Fixa Pós':
                    $type = 7;
                    break;

                case 'Tesouro Direto':
                    $type = 2;
                    break;

                case 'Ação':
                    $type = 1;
                    break;

                case 'Debênture':
                    $type = 10;
                    break;

                case 'Fundo Imobiliário':
                    $type = 3;
                    break;
                
                default:
                    $type= 0;
                    break;
            }           
            $product_name = $worksheet->getCell("A".$i)->getValue();
            $products[] = array(
                'name' => $product_name, 
                'type' => $type, 
                'value' => $worksheet->getCell("E".$i)->getValue(), 
                'product_id' => isset($products_by_name[$product_name]) ? $products_by_name[$product_name]->id : 0, 
            );
            //echo "Produto: ".$worksheet->getCell("A".$i)." | Classe: ".$worksheet->getCell("B".$i)." | Valor Aplicado: ".$worksheet->getCell("E".$i)."<br/>";    
        }

        return $this->c->view->render($response, 'product_import_list.twig', compact('products', 'product_types', 'stored_products'));     
    }

    public function import($request, $response){         
        $params = $request->getParams();  
        $product_name = $params['product_name'];
        $product_type = $params['product_type'];
        $product_value = $params['product_value'];
        $stored_product_id = $params['stored_product_id'];
        
        for($i=0; $i<count($product_name); $i++){
            if($stored_product_id[$i]>0){
                $product = $this->c->db->prepare("UPDATE product SET name=:name, product_type_id=:type, value=:value WHERE id=:product_id");
                $product->execute(array(
                    ':name' => $product_name[$i],
                    ':type' => $product_type[$i],
                    ':value' => str_replace(",", ".",str_replace(".", "",$product_value[$i])),
                    ':product_id' => $stored_product_id[$i]
                ));
            }else{
                $product = $this->c->db->prepare("INSERT INTO product (name, product_type_id, value, created_at) VALUES (:name, :type, :value, NOW())");
                $product->execute(array(
                    ':name' => $product_name[$i],
                    ':type' => $product_type[$i],
                    ':value' => str_replace(",", ".",str_replace(".", "",$product_value[$i]))
                ));
            }   
        }
        return $response->withRedirect($this->c->get('router')->pathFor('chart.allocation.type'));  
    }
}