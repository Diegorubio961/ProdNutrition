<?php

namespace App\Controllers;
use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        // Constructor del controlador
        parent::__construct();
    }

    public function index()
    {
        $this->json(['message' => 'User index method called']);
    }
}