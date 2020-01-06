<?php

namespace App\Controllers;

class ProductSubTypeController extends Controller
{    
    public function index($request, $response){         
        $product_subtypes = $this->c->db->query("SELECT product_type.name as product_type, product_subtype.* FROM product_subtype 
            INNER JOIN product_type ON product_type_id = product_type.id
            WHERE product_subtype.deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_subtype_index.twig', compact('product_subtypes'));        
    }

    public function create($request, $response){  
        $action= $this->c->get('router')->pathFor('product_subtypes.store');
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_subtype_form.twig', compact('action', 'product_types'));                          
    }

    public function store($request, $response){         
        $params = $request->getParams();    
        $product_subtype = $this->c->db->prepare("INSERT INTO product_subtype (name, product_type_id, color, created_at) VALUES (:name, :product_type_id, :color, NOW())");
        $product_subtype->execute(array(
            ':name' => $params['name'],
            ':product_type_id' => $params['product_type_id'],
            ':color' => $params['color']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_subtypes.index'));           
    }

    public function edit($request, $response, $args){ 
        $id = isset($args["id"]) ? $args["id"] : "";
        $action= $this->c->get('router')->pathFor('product_subtypes.update');
        $product_subtype = $this->c->db->query("SELECT * FROM product_subtype WHERE id='$id'")->fetch(\PDO::FETCH_OBJ);
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_subtype_form.twig', compact('product_subtype', 'action', 'product_types'));                
    }

    public function update($request, $response){        
        $params = $request->getParams();  
        $product_subtype = $this->c->db->prepare("UPDATE product_subtype SET name=:name, product_type_id=:product_type_id, color=:color WHERE id=:id");
        $product_subtype->execute(array(
            ':name' => $params['name'],
            ':product_type_id' => $params['product_type_id'],
            ':color' => $params['color'],
            ':id' => $params['id']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_subtypes.index'));              
    }

    public function delete($request, $response, $args){   
        $id = isset($args["id"]) ? $args["id"] : "";
        $product_subtype = $this->c->db->prepare("UPDATE product_subtype SET deleted_at=:date WHERE id=:id");
        $product_subtype->execute(array(
            ':date' => date("Y-m-d H:i"),
            ':id' => $id
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_subtypes.index'));
    }

    public function getByType($request, $response){   
        $id = isset($args["id"]) ? $args["id"] : "";
        $product_subtypes = $this->c->db->query("SELECT * FROM product_subtype WHERE product_type_id='$id' AND deleted_at IS NULL")->fetch(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_subtype_form.twig', compact('action', 'product_types'));                          
    }
}