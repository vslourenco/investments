<?php

namespace App\Controllers;

class TradeController extends Controller
{    
    public function index($request, $response){    
        $trades = $this->c->db->query("SELECT trade.*, product.name FROM trade 
            INNER JOIN product ON trade.product_id = product.id
            WHERE trade.deleted_at IS NULL
            ORDER BY trade.date DESC")->fetchAll(\PDO::FETCH_OBJ);

        return $this->c->view->render($response, 'trade_index.twig', compact('trades'));      
    }

    public function create($request, $response){  
        $action= $this->c->get('router')->pathFor('trades.store');
        $products = $this->c->db->query("SELECT * FROM product WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'trade_form.twig', compact('action', 'products'));                          
    }

    public function store($request, $response){         
        $params = $request->getParams();    
        $trade = $this->c->db->prepare("INSERT INTO trade (product_id, value, date, note, created_at) VALUES (:product_id, :value, :date, :note, NOW())");
        $trade->execute(array(
            ':product_id' => $params['product_id'],
            ':value' => $params['value'],
            ':date' => $params['date'],
            ':note' => $params['note']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('trades.index'));           
    }

    public function edit($request, $response, $args){ 
        $id = isset($args["id"]) ? $args["id"] : "";
        $action= $this->c->get('router')->pathFor('trades.update');
        $trade = $this->c->db->query("SELECT * FROM trade WHERE id='$id'")->fetch(\PDO::FETCH_OBJ);      
        $products = $this->c->db->query("SELECT * FROM product WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'trade_form.twig', compact('trade', 'action', 'products'));                
    }

    public function update($request, $response){        
        $params = $request->getParams();  
        $trade = $this->c->db->prepare("UPDATE trade SET product_id=:product_id, value=:value, date=:date, note=:note WHERE id=:id");

        $trade->execute(array(
            ':product_id' => $params['product_id'],
            ':value' => $params['value'],
            ':date' => preg_replace('#(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1 $4', $params['date']),
            ':note' => $params['note'],
            ':id' => $params['id']
          ));

        return $response->withRedirect($this->c->get('router')->pathFor('trades.index'));              
    }

    public function delete($request, $response, $args){   
        $id = isset($args["id"]) ? $args["id"] : "";
        $trade = $this->c->db->prepare("UPDATE trade SET deleted_at=:date WHERE id=:id");
        $trade->execute(array(
            ':date' => date("Y-m-d H:i"),
            ':id' => $id
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('trades.index'));
    }

    public function importForm($request, $response){ 
        return $this->c->view->render($response, 'trade_import_form.twig');        
    }

    public function importList($request, $response){ 
        
        $products = $this->c->db->query("SELECT * FROM product WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);

        $products_by_name = array_column($products, NULL, "name");

        $params = $request->getParams();
        $uploadedFiles = $request->getUploadedFiles();

        $inputFileName = $uploadedFiles["document"]->file;
        /** Create a new Xlsx Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $last_column = $worksheet->getHighestDataColumn();
        $last_row = $worksheet->getHighestDataRow();
       
        //dd($uploadedFiles["document"]->file);
        //dd($spreadsheet);
        //dd($last_column);
        //dd($last_row);
        //dd($products_by_name);
        //dd($products);

        $trades = array();
        for($i=2; $i<=$last_row; $i++){         
            $product_name = $worksheet->getCell("B".$i)->getValue();
            $trades[] = array(
                'date' => $worksheet->getCell("A".$i)->getValue(), 
                'product' => $product_name, 
                'value' => $worksheet->getCell("C".$i)->getValue(), 
                'product_id' => isset($products_by_name[$product_name]) ? $products_by_name[$product_name]->id : 0, 
            );
            //echo "Produto: ".$worksheet->getCell("A".$i)." | Classe: ".$worksheet->getCell("B".$i)." | Valor Aplicado: ".$worksheet->getCell("E".$i)."<br/>";    
        }

        //dd($trades);
        return $this->c->view->render($response, 'trade_import_list.twig', compact('trades', 'products'));     
    }

    public function import($request, $response){         
        $params = $request->getParams();  

        $trade_date = $params['trade_date'];
        $trade_value = $params['trade_value'];
        $product_id = $params['product_id'];
        
        for($i=0; $i<count($trade_date); $i++){
            $product = $this->c->db->prepare("INSERT INTO trade (product_id, value, date, created_at) VALUES (:product, :value, :date, NOW())");
            $product->execute(array(
                ':product' => $product_id[$i],
                ':value' => str_replace(",", ".",str_replace(".", "", $trade_value[$i])),
                ':date' => preg_replace('#(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1 $4', $trade_date[$i])
            ));
        }
        return $response->withRedirect($this->c->get('router')->pathFor('trades.index'));  
    }
}