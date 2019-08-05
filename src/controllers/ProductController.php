<?php

namespace App\Controllers;

class ProductController extends Controller
{    
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
        
        for($i=0; $i<count($product_name); $i++){
            $product = $this->c->db->prepare("INSERT INTO product (name, product_type_id, value, created_at) VALUES (:name, :type, :value, NOW())");
            $product->execute(array(
                ':name' => $product_name[$i],
                ':type' => $product_type[$i],
                ':value' => str_replace(",", ".",str_replace(".", "",$product_value[$i]))
            ));
        }
        return $response->withRedirect($this->c->get('router')->pathFor('chart.allocation.type'));  
    }
}