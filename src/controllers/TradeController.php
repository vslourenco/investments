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
        $trade = $this->c->db->prepare("INSERT INTO trade (product_id, value, date, created_at) VALUES (:product_id, :value, :date, NOW())");
        $trade->execute(array(
            ':product_id' => $params['product_id'],
            ':value' => $params['value'],
            ':date' => $params['date']
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
        $trade = $this->c->db->prepare("UPDATE trade SET product_id=:product_id, value=:value, date=:date WHERE id=:id");

        $trade->execute(array(
            ':product_id' => $params['product_id'],
            ':value' => $params['value'],
            ':date' => preg_replace('#(\d{2})/(\d{2})/(\d{4})#', '$3-$2-$1 $4', $params['date']),
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
}