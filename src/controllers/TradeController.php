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

    public function edit($request, $response, $args){ 
        $id = isset($args["id"]) ? $args["id"] : "";
        $action= $this->c->get('router')->pathFor('trades.update');
        $trade = $this->c->db->query("SELECT * FROM trade WHERE id='$id'")->fetch(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'trade_form.twig', compact('trade', 'action'));                
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