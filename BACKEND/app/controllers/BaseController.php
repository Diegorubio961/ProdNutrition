<?php

namespace App\Controllers;

use Core\Request;


class BaseController
{
    protected Request $request;

    
    public function __construct() {
        $this->request = Request::getInstance();
    }
    
    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function print($obj){
        print_r($this->json($obj));
    }
}
