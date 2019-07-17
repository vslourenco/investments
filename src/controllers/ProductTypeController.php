<?php

namespace App\Controllers;

class ProductTypeController extends Controller
{    
    public function index($request, $response){         
        $product_types = $this->c->db->query("SELECT * FROM product_type WHERE deleted_at IS NULL")->fetchAll(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_type_index.twig', compact('product_types'));        
    }

    public function create($request, $response){  
        $action= $this->c->get('router')->pathFor('product_types.store');
        return $this->c->view->render($response, 'product_type_form.twig', compact('action'));                          
    }

    public function store($request, $response){         
        $params = $request->getParams();    
        $product_type = $this->c->db->prepare("INSERT INTO product_type (name, created_at) VALUES (:name, NOW())");
        $product_type->execute(array(
            ':name' => $params['name']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_types.index'));           
    }

    public function edit($request, $response, $args){ 
        $id = isset($args["id"]) ? $args["id"] : "";
        $action= $this->c->get('router')->pathFor('product_types.update');
        $product_type = $this->c->db->query("SELECT * FROM product_type WHERE id='$id'")->fetch(\PDO::FETCH_OBJ);
        return $this->c->view->render($response, 'product_type_form.twig', compact('product_type', 'action'));                
    }

    public function update($request, $response){        
        $params = $request->getParams();  
        $product_type = $this->c->db->prepare("UPDATE product_type SET name=:name WHERE id=:id");
        $product_type->execute(array(
            ':name' => $params['name'],
            ':id' => $params['id']
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_types.index'));              
    }

    public function delete($request, $response, $args){   
        $id = isset($args["id"]) ? $args["id"] : "";
        $product_type = $this->c->db->prepare("UPDATE product_type SET deleted_at=:date WHERE id=:id");
        $product_type->execute(array(
            ':date' => date("Y-m-d H:i"),
            ':id' => $id
          ));
        return $response->withRedirect($this->c->get('router')->pathFor('product_types.index'));              
    }
}