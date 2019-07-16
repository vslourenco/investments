<?php

namespace App\Controllers;

class ProductController extends Controller
{    
    public function importForm($request, $response){ 
        return $this->c->view->render($response, 'product_import_form.twig');        
    }

    public function importList($request, $response){ 
        echo "<pre>";
        $params = $request->getParams();
        $uploadedFiles = $request->getUploadedFiles();

        $inputFileName = $uploadedFiles["document"]->file;
        /** Create a new Xlsx Reader  **/
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($inputFileName);
        $worksheet = $spreadsheet->getActiveSheet();
        $last_column = $worksheet->getHighestDataColumn();
        $last_row = $worksheet->getHighestDataRow();
       
        var_dump($uploadedFiles["document"]->file);   
        
        echo "<pre>";
        //var_dump($spreadsheet);
        var_dump($last_column);
        var_dump($last_row);

        for($i=2; $i<=$last_row; $i++){
            echo "Produto: ".$worksheet->getCell("A".$i)." | Classe: ".$worksheet->getCell("B".$i)." | Valor Aplicado: ".$worksheet->getCell("E".$i)."<br/>";    
        }
    }
}