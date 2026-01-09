<?php

namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\DocumentsTypeModel;

class basura extends BaseController
{
    public function __construct()
    {
        // Constructor del controlador
    }

    public function index()
    {
        // echo "Hola desde el controlador basura!";
        $documentsType = DocumentsTypeModel::query()->first();
        echo json_encode($documentsType['id']);
        // foreach ($documentsType as $docType) {
        //     echo "ID: " . $docType['id'] . " - Name: " . $docType['type_name'] . "\n";
        // }
        
    }
}